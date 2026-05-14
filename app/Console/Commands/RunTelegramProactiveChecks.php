<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Seller;
use App\Services\Telegram\AdminTelegramService;
use App\Services\Telegram\SellerTelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Runs proactive checks and fires Telegram alerts when issues are found.
 *
 * Checks performed:
 *   1. Orders stuck in 'processing' for 24+ hours → alerts admins
 *   2. Order items approaching their ready-by deadline (≤24 hrs) → alerts sellers
 *   3. Order items now overdue → alerts sellers AND admins
 *   4. Products with stock ≤ configured threshold → alerts sellers
 *   5. Products that just hit zero stock → alerts sellers
 *
 * Schedule: run hourly
 *   $schedule->command('telegram:proactive-checks')->hourly();
 */
class RunTelegramProactiveChecks extends Command
{
    protected $signature   = 'telegram:proactive-checks';
    protected $description = 'Run proactive platform checks and fire Telegram alerts.';

    // Low-stock threshold — products at or below this trigger a warning
    private const LOW_STOCK_THRESHOLD = 5;

    public function __construct(
        protected AdminTelegramService  $adminTelegram,
        protected SellerTelegramService $sellerTelegram,
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->checkStuckOrders();
        $this->checkStalePayouts();
        $this->checkUnassignedDeliveries();
        $this->checkDeadlineApproaching();
        $this->checkOverdueItems();
        $this->checkLowStock();

        $this->info('✅ Proactive checks complete.');
    }

    private function checkStalePayouts(): void
    {
        $payouts = Payout::where('status', 'processing')
            ->where('processed_at', '<', now()->subHours(24))
            ->with('seller.user')
            ->get();

        foreach ($payouts as $payout) {
            $cacheKey = "tg_stale_payout_{$payout->id}";

            if (cache()->has($cacheKey)) continue;

            $hours = now()->diffInHours($payout->processed_at ?? $payout->updated_at);

            try {
                $this->adminTelegram->notifyPayoutProcessingStale($payout, $hours);
                cache()->put($cacheKey, true, now()->addHours(12));
                $this->line("  -> Stale payout alert: #{$payout->id}");
            } catch (\Exception $e) {
                Log::error('Stale payout Telegram alert failed', [
                    'payout_id' => $payout->id,
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }

    private function checkUnassignedDeliveries(): void
    {
        $deliveries = \App\Models\Delivery::where('status', 'pending')
            ->whereNull('rider_id')
            ->where('created_at', '<', now()->subHours(2))
            ->with(['order', 'seller.shop'])
            ->get();

        foreach ($deliveries as $delivery) {
            $cacheKey = "tg_unassigned_delivery_{$delivery->id}";

            if (cache()->has($cacheKey)) continue;

            $hours = now()->diffInHours($delivery->created_at);

            try {
                $this->adminTelegram->notifyDeliveryUnassignedTooLong($delivery, $hours);
                cache()->put($cacheKey, true, now()->addHours(6));
                $this->line("  -> Unassigned delivery alert: #{$delivery->id}");
            } catch (\Exception $e) {
                Log::error('Unassigned delivery Telegram alert failed', [
                    'delivery_id' => $delivery->id,
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    // ─── 1. Stuck orders ─────────────────────────────────────────────────────

    /**
     * Orders stuck in 'processing' for more than 24 hours.
     * We use a cache key per order to prevent spamming admins every hour.
     */
    private function checkStuckOrders(): void
    {
        $stuckOrders = Order::where('status', 'processing')
            ->where('updated_at', '<', now()->subHours(24))
            ->get();

        foreach ($stuckOrders as $order) {
            $cacheKey = "tg_stuck_order_{$order->id}";

            if (cache()->has($cacheKey)) continue; // already alerted

            $hoursStuck = now()->diffInHours($order->updated_at);

            try {
                $this->adminTelegram->notifyStuckOrder($order, $hoursStuck);
                // Don't re-alert for this order for 12 hours
                cache()->put($cacheKey, true, now()->addHours(12));
                $this->line("  → Stuck order alert: #{$order->order_number}");
            } catch (\Exception $e) {
                Log::error('Stuck order Telegram alert failed', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }
        }
    }

    // ─── 2. Deadline approaching (≤ 24 hrs) ──────────────────────────────────

    /**
     * Items due within the next 24 hours that haven't been marked ready yet.
     */
    private function checkDeadlineApproaching(): void
    {
        $items = OrderItem::whereNotNull('expected_ready_by')
            ->whereDate('expected_ready_by', '>=', now()->toDateString())
            ->whereDate('expected_ready_by', '<=', now()->addHours(24)->toDateString())
            ->whereNotIn('status', ['ready_for_pickup', 'picked_up', 'delivered', 'cancelled'])
            ->with(['order', 'seller'])
            ->get();

        foreach ($items as $item) {
            $cacheKey = "tg_deadline_approaching_{$item->id}";

            if (cache()->has($cacheKey)) continue;

            $seller = $item->seller;
            if (! $seller) continue;

            try {
                $this->sellerTelegram->notifyDeadlineApproaching($seller, $item);
                cache()->put($cacheKey, true, now()->addHours(20));
                $this->line("  → Deadline alert sent for item #{$item->id}");
            } catch (\Exception $e) {
                Log::error('Deadline approaching Telegram alert failed', [
                    'order_item_id' => $item->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }
    }

    // ─── 3. Overdue items ────────────────────────────────────────────────────

    /**
     * Items past their expected_ready_by date and not yet ready.
     */
    private function checkOverdueItems(): void
    {
        $items = OrderItem::whereNotNull('expected_ready_by')
            ->whereDate('expected_ready_by', '<', now()->toDateString())
            ->whereNotIn('status', ['ready_for_pickup', 'picked_up', 'delivered', 'cancelled'])
            ->with(['order', 'seller'])
            ->get();

        foreach ($items as $item) {
            $cacheKey = "tg_overdue_item_{$item->id}";

            if (cache()->has($cacheKey)) continue;

            $seller = $item->seller;
            if (! $seller) continue;

            try {
                $this->sellerTelegram->notifyItemOverdue($seller, $item);
                $this->adminTelegram->notifySellerReadyAfterDeadline($item, $seller);
                // Re-alert every 24 hours
                cache()->put($cacheKey, true, now()->addHours(24));
                $this->line("  → Overdue alert sent for item #{$item->id}");
            } catch (\Exception $e) {
                Log::error('Overdue item Telegram alert failed', [
                    'order_item_id' => $item->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }
    }

    // ─── 4. Low stock ────────────────────────────────────────────────────────

    /**
     * Products at or below the threshold (but not zero — that's a separate alert).
     */
    private function checkLowStock(): void
    {
        $lowStockProducts = \App\Models\Product::where('is_active', true)
            ->where('stock', '>', 0)
            ->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->with('shop.seller')
            ->get();

        foreach ($lowStockProducts as $product) {
            $cacheKey = "tg_low_stock_{$product->id}";

            if (cache()->has($cacheKey)) continue;

            $seller = $product->shop?->seller;
            if (! $seller) continue;

            try {
                $this->sellerTelegram->notifyLowStock($seller, $product);
                // Alert once per 24 hours per product
                cache()->put($cacheKey, true, now()->addHours(24));
                $this->line("  → Low stock alert: {$product->name} (#{$product->id}, stock={$product->stock})");
            } catch (\Exception $e) {
                Log::error('Low stock Telegram alert failed', [
                    'product_id' => $product->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        // Out-of-stock products — separate 48-hour cache window
        $outOfStockProducts = \App\Models\Product::where('is_active', true)
            ->where('stock', 0)
            ->with('shop.seller')
            ->get();

        foreach ($outOfStockProducts as $product) {
            $cacheKey = "tg_out_of_stock_{$product->id}";

            if (cache()->has($cacheKey)) continue;

            $seller = $product->shop?->seller;
            if (! $seller) continue;

            try {
                $this->sellerTelegram->notifyOutOfStock($seller, $product);
                cache()->put($cacheKey, true, now()->addHours(48));
                $this->line("  → Out-of-stock alert: {$product->name} (#{$product->id})");
            } catch (\Exception $e) {
                Log::error('Out-of-stock Telegram alert failed', [
                    'product_id' => $product->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}

<?php

namespace App\Services\Telegram;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Seller;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

/**
 * Admin Bot — message builders and push notification helpers.
 *
 * Broadcasts go to ALL admins who have telegram_chat_id set and
 * whose relevant notification toggle is enabled.
 *
 * Per-category helpers (notifyAdminsAboutPayout, etc.) filter by
 * the relevant column automatically, so callers never need to loop
 * over admins themselves.
 *
 * Bot token: config('services.telegram.admin_bot_token')
 */
class AdminTelegramService extends BaseTelegramService
{
    public function __construct()
    {
        $this->token   = config('services.telegram.admin_bot_token');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    // =========================================================================
    // BROADCAST HELPERS — loop over eligible admins
    // =========================================================================

    /**
     * Send a message to every admin who has Telegram linked
     * and has the given notification column enabled.
     *
     * @param string $notifyColumn  e.g. 'telegram_notify_payouts'
     */
    public function broadcast(string $notifyColumn, string $text, array $markup = []): void
    {
        $admins = Admin::whereNotNull('telegram_chat_id')
            ->where($notifyColumn, true)
            ->get();

        foreach ($admins as $admin) {
            $this->sendMessage($admin->telegram_chat_id, $text, $markup);
        }
    }

    // =========================================================================
    // ORDER NOTIFICATIONS
    // =========================================================================

    /**
     * Large order placed — threshold configurable via config/platform.php.
     */
    public function notifyLargeOrder(Order $order): void
    {
        $threshold = config('platform.large_order_threshold', 50000);

        if ($order->total < $threshold) return;

        $text = "💰 <b>Large Order Alert!</b>\n\n"
              . "Order: <code>#{$order->order_number}</code>\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Total: <b>{$this->naira($order->total)}</b>\n"
              . "Payment: {$this->e($order->payment_method)}\n"
              . "Items: {$order->items->count()}\n"
              . "Sellers: " . $order->items->pluck('seller_id')->unique()->count();

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('🔍 View Order', url("/admin/orders/{$order->id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_orders', $text, $markup);
    }

    /**
     * Order stuck in processing for more than N hours.
     */
    public function notifyStuckOrder(Order $order, int $hoursStuck): void
    {
        $text = "⏳ <b>Order Stuck in Processing</b>\n\n"
              . "Order: <code>#{$order->order_number}</code>\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Total: {$this->naira($order->total)}\n"
              . "Stuck for: <b>{$hoursStuck} hours</b>\n\n"
              . "May need manual intervention.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('🔍 Investigate', url("/admin/orders/{$order->id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_orders', $text, $markup);
    }

    /**
     * Seller marked an item ready AFTER its deadline.
     * (Already logged in OrderController::markItemReady — add Telegram here)
     */
    public function notifySellerReadyAfterDeadline(OrderItem $item, Seller $seller): void
    {
        $daysLate = abs($item->days_until_deadline);

        $text = "🚨 <b>Late Ready-for-Pickup</b>\n\n"
              . "Seller: <b>{$this->e($seller->name)}</b>\n"
              . "Order: <code>#{$item->order->order_number}</code>\n"
              . "Item: {$this->e($item->product_name)}\n"
              . "Deadline was: {$item->expected_ready_by->format('d M Y')}\n"
              . "Late by: <b>{$daysLate} day(s)</b>";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📋 View Order', url("/admin/orders/{$item->order_id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_orders', $text, $markup);
    }

    // =========================================================================
    // PAYOUT NOTIFICATIONS
    // =========================================================================

    /**
     * New seller payout request submitted — needs admin review.
     */
    public function notifyNewPayoutRequest(Payout $payout): void
    {
        $seller   = $payout->seller;
        $shopName = $seller->shop?->shop_name ?? 'Unknown Shop';

        $text = "💳 <b>New Payout Request</b>\n\n"
              . "Seller: {$this->e($seller->user?->name ?? 'N/A')}\n"
              . "Shop: {$this->e($shopName)}\n"
              . "Amount: <b>{$this->naira($payout->amount)}</b>\n"
              . "Method: {$this->e($payout->payout_method ?? 'N/A')}\n"
              . "Bank: {$this->e($seller->bank_name ?? 'N/A')}\n"
              . "Account: <code>{$seller->bank_account}</code>\n"
              . "Account Name: {$this->e($seller->account_holder_name ?? 'N/A')}";

        $markup = [
            'inline_keyboard' => [
                [
                    $this->urlButton('✅ Approve', url("/admin/finance/payouts/{$payout->id}")),
                    $this->urlButton('📊 Queue', url('/admin/finance/payouts?status=pending')),
                ],
            ],
        ];

        $this->broadcast('telegram_notify_payouts', $text, $markup);
    }

    /**
     * Daily payout summary — called by scheduler.
     */
    public function sendPayoutDailySummary(): void
    {
        $pending    = Payout::where('status', 'pending')->count();
        $pendingAmt = Payout::where('status', 'pending')->sum('amount');
        $processing = Payout::where('status', 'processing')->count();
        $todayCount = Payout::where('status', 'completed')
            ->whereDate('processed_at', today())->count();
        $todayAmt   = Payout::where('status', 'completed')
            ->whereDate('processed_at', today())->sum('amount');

        if ($pending === 0 && $todayCount === 0) return; // nothing to report

        $text = "📊 <b>Daily Payout Summary</b> — " . now()->format('d M Y') . "\n\n"
              . "🟡 Pending: <b>{$pending}</b> ({$this->naira($pendingAmt)})\n"
              . "🔵 Processing: <b>{$processing}</b>\n"
              . "✅ Paid today: <b>{$todayCount}</b> ({$this->naira($todayAmt)})";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('💳 Manage Payouts', url('/admin/finance/payouts')),
            ]],
        ];

        $this->broadcast('telegram_notify_payouts', $text, $markup);
    }

    // =========================================================================
    // SELLER MANAGEMENT NOTIFICATIONS
    // =========================================================================

    /**
     * New seller application submitted.
     */
    public function notifyNewSellerApplication(Seller $seller): void
    {
        $user = $seller->user;

        $text = "🏪 <b>New Seller Application</b>\n\n"
              . "Name: {$this->e($user->name)}\n"
              . "Email: {$this->e($user->email)}\n"
              . "Business Type: {$this->e($seller->business_type ?? 'N/A')}\n"
              . "Location: {$this->e(implode(', ', array_filter([$seller->city, $seller->state, $seller->country])))}\n"
              . "Applied: " . $seller->created_at->format('d M Y, g:ia');

        $markup = [
            'inline_keyboard' => [
                [
                    $this->urlButton('👤 Review Application', url("/admin/sellers/{$seller->id}")),
                    $this->urlButton('📋 All Applications', url('/admin/sellers/applications/view')),
                ],
            ],
        ];

        $this->broadcast('telegram_notify_sellers', $text, $markup);
    }

    /**
     * Seller account deactivated by the seller themselves.
     */
    public function notifySellerSelfDeactivated(Seller $seller): void
    {
        $text = "⚠️ <b>Seller Account Self-Deactivated</b>\n\n"
              . "Seller: {$this->e($seller->user?->name ?? 'N/A')}\n"
              . "Shop: {$this->e($seller->shop?->shop_name ?? 'N/A')}\n\n"
              . "They have 30 days to reactivate before permanent closure.";

        $this->broadcast('telegram_notify_sellers', $text);
    }

    // =========================================================================
    // REVIEW MODERATION NOTIFICATIONS
    // =========================================================================

    /**
     * New review pending moderation.
     * Batching: if already ≥5 pending, send a single "queue" alert instead.
     */
    public function notifyNewReviewPending(Review $review): void
    {
        $pendingCount = Review::where('status', 'pending')->count();

        // Send a batch alert if queue is building up (avoid spam)
        if ($pendingCount >= 5) {
            $this->notifyReviewQueueBuilding($pendingCount);
            return;
        }

        $stars   = str_repeat('⭐', (int) $review->rating) . str_repeat('☆', 5 - (int) $review->rating);
        $comment = $review->comment
            ? $this->truncate($this->e($review->comment), 100)
            : '<i>No comment</i>';

        $text = "📝 <b>New Review Pending</b>\n\n"
              . "Product: {$this->e($review->product->name ?? 'N/A')}\n"
              . "Shop: {$this->e($review->product->shop?->shop_name ?? 'N/A')}\n"
              . "Rating: {$stars}\n"
              . "Comment: {$comment}\n"
              . "Verified purchase: " . ($review->is_verified_purchase ? 'Yes ✓' : 'No');

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('🔍 Review', url("/admin/reviews/{$review->id}")),
                $this->urlButton('📋 Queue', url('/admin/reviews?status=pending')),
            ]],
        ];

        $this->broadcast('telegram_notify_reviews', $text, $markup);
    }

    protected function notifyReviewQueueBuilding(int $count): void
    {
        // Only send this once per hour using a simple cache key
        $cacheKey = 'admin_tg_review_queue_alert';
        if (cache()->has($cacheKey)) return;

        cache()->put($cacheKey, true, now()->addHour());

        $text = "📝 <b>Review Queue Alert</b>\n\n"
              . "You have <b>{$count} reviews</b> waiting for approval.\n"
              . "Please review the queue.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📋 Review Queue', url('/admin/reviews?status=pending')),
            ]],
        ];

        $this->broadcast('telegram_notify_reviews', $text, $markup);
    }

    // =========================================================================
    // REFUND NOTIFICATIONS
    // =========================================================================

    /**
     * Customer cancelled a paid order — refund request raised.
     */
    public function notifyRefundRequest(Order $order): void
    {
        $text = "💸 <b>Refund Request</b>\n\n"
              . "Order: <code>#{$order->order_number}</code>\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Amount: <b>{$this->naira($order->total)}</b>\n"
              . "Payment: {$this->e($order->payment_method)}\n"
              . "Cancelled: " . ($order->cancelled_at?->format('d M, g:ia') ?? 'N/A');

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('💸 Process Refund', url("/admin/finance/refunds/{$order->id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_payouts', $text, $markup);
    }

    // =========================================================================
    // SYSTEM / DAILY DIGEST
    // =========================================================================

    /**
     * Daily platform summary — sent every evening by scheduler.
     */
    public function sendDailyDigest(): void
    {
        $ordersToday      = Order::whereDate('created_at', today())->count();
        $revenueToday     = Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total');
        $pendingPayouts   = Payout::where('status', 'pending')->count();
        $pendingReviews   = Review::where('status', 'pending')->count();
        $pendingRefunds   = Order::where('status', 'cancelled')->where('payment_status', 'refund_pending')->count();
        $pendingSellers   = Seller::where('verification_status', 'pending')->count();
        $activeOrders     = Order::whereIn('status', ['pending', 'processing'])->count();

        $text = "📊 <b>Daily Platform Digest</b> — " . now()->format('d M Y') . "\n\n"
              . "🛒 Orders today: <b>{$ordersToday}</b> ({$this->naira($revenueToday)})\n"
              . "📦 Active orders: <b>{$activeOrders}</b>\n\n"
              . "<b>Action Required:</b>\n"
              . "💳 Pending payouts: <b>{$pendingPayouts}</b>\n"
              . "📝 Pending reviews: <b>{$pendingReviews}</b>\n"
              . "💸 Pending refunds: <b>{$pendingRefunds}</b>\n"
              . "🏪 Seller applications: <b>{$pendingSellers}</b>";

        $markup = [
            'inline_keyboard' => [
                [
                    $this->urlButton('📊 Dashboard', url('/admin/dashboard')),
                    $this->urlButton('💳 Payouts', url('/admin/finance/payouts?status=pending')),
                ],
            ],
        ];

        $this->broadcast('telegram_notify_system', $text, $markup);
    }
}
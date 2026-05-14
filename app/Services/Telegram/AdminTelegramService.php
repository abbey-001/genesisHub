<?php

namespace App\Services\Telegram;

use App\Models\Admin;
use App\Models\Delivery;
use App\Models\DeliveryPayout;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\OrderItem;

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
        $this->apiBase = $this->token ? "https://api.telegram.org/bot{$this->token}" : null;
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
    public function broadcast(string $notifyColumn, string $text, array $markup = [], array $requiredPermissions = []): void
    {
        $admins = Admin::whereNotNull('telegram_chat_id')
            ->where('is_active', true)
            ->where($notifyColumn, true)
            ->with('role.permissions')
            ->get();

        foreach ($admins as $admin) {
            if (! $this->adminCanReceive($admin, $notifyColumn, $requiredPermissions)) {
                continue;
            }

            $this->sendMessage($admin->telegram_chat_id, $text, $markup);
        }
    }

    protected function adminCanReceive(Admin $admin, string $notifyColumn, array $requiredPermissions = []): bool
    {
        if ($admin->isSuperAdmin()) {
            return true;
        }

        $permissions = $requiredPermissions ?: $this->permissionsForColumn($notifyColumn);

        return empty($permissions) || $admin->hasAnyPermission($permissions);
    }

    protected function permissionsForColumn(string $notifyColumn): array
    {
        return match ($notifyColumn) {
            'telegram_notify_orders' => ['orders.view', 'orders.edit', 'orders.cancel'],
            'telegram_notify_payouts' => ['finance.view', 'payouts.view', 'payouts.approve', 'payouts.process', 'orders.refund'],
            'telegram_notify_sellers' => ['sellers.view', 'sellers.approve', 'sellers.suspend'],
            'telegram_notify_reviews' => ['products.view', 'products.edit', 'support.view'],
            'telegram_notify_deliveries' => ['deliveries.view', 'deliveries.assign', 'deliveries.manage', 'deliveries.track'],
            'telegram_notify_riders' => ['riders.view', 'riders.approve', 'riders.suspend', 'deliveries.view'],
            'telegram_notify_system' => ['dashboard.view', 'dashboard.analytics'],
            default => [],
        };
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

    public function notifyPaymentFailure(Order $order, string $gateway, string $reason = ''): void
    {
        $text = "Payment failure needs review\n\n"
              . "Order: #{$order->order_number}\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Gateway: {$this->e($gateway)}\n"
              . "Total: {$this->naira($order->total)}"
              . ($reason ? "\nReason: {$this->e($this->truncate($reason, 160))}" : '');

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('View Order', url("/admin/orders/{$order->id}")),
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

    public function notifyPayoutProcessingStale(Payout $payout, int $hours): void
    {
        $seller = $payout->seller;

        $text = "Payout still processing\n\n"
              . "Payout: #{$payout->id}\n"
              . "Seller: {$this->e($seller?->user?->name ?? 'N/A')}\n"
              . "Amount: {$this->naira($payout->amount)}\n"
              . "Processing for: {$hours} hours";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Open Payout', url("/admin/finance/payouts/{$payout->id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_payouts', $text, $markup);
    }

    public function notifySellerWalletNegative(Seller $seller, float $balance): void
    {
        $text = "Seller wallet is negative\n\n"
              . "Seller: {$this->e($seller->user?->name ?? 'N/A')}\n"
              . "Shop: {$this->e($seller->shop?->shop_name ?? 'N/A')}\n"
              . "Balance: {$this->naira($balance)}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Review Wallet', url("/admin/sellers/{$seller->id}/wallet")),
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

    public function notifyNewRiderApplication(Rider $rider): void
    {
        $text = "New rider application\n\n"
              . "Name: {$this->e($rider->full_name)}\n"
              . "Email: {$this->e($rider->user?->email ?? 'N/A')}\n"
              . "Phone: {$this->e($rider->phone_number ?? 'N/A')}\n"
              . "Vehicle: {$this->e($rider->vehicle_type ?? 'N/A')}\n"
              . "Applied: {$rider->created_at->format('d M Y, g:ia')}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Review Rider', url("/admin/riders/{$rider->id}")),
                $this->urlButton('Applications', url('/admin/riders/applications')),
            ]],
        ];

        $this->broadcast('telegram_notify_riders', $text, $markup);
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

        if ((int) $review->rating <= 2 && $review->is_verified_purchase) {
            $this->notifyLowRatingReview($review);
        }
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

    public function notifyLowRatingReview(Review $review): void
    {
        $text = "Low-rating verified review\n\n"
              . "Product: {$this->e($review->product->name ?? 'N/A')}\n"
              . "Rating: {$review->rating}/5\n"
              . "Customer: {$this->e($review->user?->name ?? 'N/A')}\n"
              . "Comment: " . ($review->comment ? $this->truncate($this->e($review->comment), 160) : 'No comment');

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Moderate Review', url("/admin/reviews/{$review->id}")),
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

        $this->broadcast('telegram_notify_payouts', $text, $markup, ['orders.refund', 'finance.view']);
    }

    public function notifyRefundProcessingFailed(Order $order, string $reason): void
    {
        $text = "Refund processing failed\n\n"
              . "Order: #{$order->order_number}\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Amount: {$this->naira($order->total)}\n"
              . "Reason: {$this->e($this->truncate($reason, 180))}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Review Refund', url("/admin/finance/refunds/{$order->id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_payouts', $text, $markup, ['orders.refund', 'finance.view']);
    }

    // =========================================================================
    // DELIVERY / RIDER OPERATIONS
    // =========================================================================

    public function notifyDeliveryAssignmentFailed(Delivery $delivery): void
    {
        $delivery->loadMissing('order', 'seller.shop');

        $text = "Manual rider assignment needed\n\n"
              . "Delivery: #{$delivery->id}\n"
              . "Order: #{$delivery->order?->order_number}\n"
              . "Seller: {$this->e($delivery->seller?->shop?->shop_name ?? 'N/A')}\n"
              . "Delivery address: {$this->e($this->truncate($delivery->delivery_address ?? 'N/A', 140))}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Assign Rider', url('/admin/deliveries/unassigned')),
            ]],
        ];

        $this->broadcast('telegram_notify_deliveries', $text, $markup, ['deliveries.assign', 'deliveries.manage']);
    }

    public function notifyDeliveryUnassignedTooLong(Delivery $delivery, int $hours): void
    {
        $delivery->loadMissing('order', 'seller.shop');

        $text = "Delivery unassigned too long\n\n"
              . "Delivery: #{$delivery->id}\n"
              . "Order: #{$delivery->order?->order_number}\n"
              . "Waiting: {$hours} hours\n"
              . "Seller: {$this->e($delivery->seller?->shop?->shop_name ?? 'N/A')}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Assign Rider', url("/admin/deliveries/{$delivery->id}/assign")),
            ]],
        ];

        $this->broadcast('telegram_notify_deliveries', $text, $markup, ['deliveries.assign', 'deliveries.manage']);
    }

    public function notifyDeliveryFailed(Delivery $delivery, string $reason = ''): void
    {
        $delivery->loadMissing('order', 'rider', 'seller.shop');

        $text = "Delivery failed\n\n"
              . "Delivery: #{$delivery->id}\n"
              . "Order: #{$delivery->order?->order_number}\n"
              . "Rider: {$this->e($delivery->rider?->full_name ?? 'Unassigned')}\n"
              . "Reason: {$this->e($this->truncate($reason ?: ($delivery->failure_reason ?? 'N/A'), 160))}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Review Delivery', url("/admin/deliveries/{$delivery->id}")),
            ]],
        ];

        $this->broadcast('telegram_notify_deliveries', $text, $markup);
    }

    public function notifyNewRiderPayoutRequest(DeliveryPayout $payout): void
    {
        $payout->loadMissing('company.user');

        $text = "New delivery payout request\n\n"
              . "Reference: {$this->e($payout->reference_number)}\n"
              . "Rider/company: {$this->e($payout->company?->full_name ?? 'N/A')}\n"
              . "Amount: {$this->naira($payout->amount)}\n"
              . "Deliveries: {$payout->deliveries_count}";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('Review Payout', url("/admin/delivery/payouts/{$payout->id}")),
                $this->urlButton('Queue', url('/admin/delivery/payouts?status=pending')),
            ]],
        ];

        $this->broadcast('telegram_notify_payouts', $text, $markup, ['payouts.view', 'payouts.approve', 'finance.view']);
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

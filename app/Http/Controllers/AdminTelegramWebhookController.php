<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Seller;
use App\Services\Telegram\AdminTelegramService;
use App\Services\Telegram\SellerTelegramService;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Admin Bot Webhook — handles all incoming Telegram updates for the admin bot.
 *
 * Admins are identified by their telegram_chat_id on the Admin model.
 * There is no linking flow — chat IDs are registered manually by super-admins.
 *
 * Route (add to routes/web.php):
 *   Route::post('/telegram/admin/webhook',
 *       [\App\Http\Controllers\AdminTelegramWebhookController::class, 'handle']
 *   )->name('telegram.admin.webhook');
 */
class AdminTelegramWebhookController extends Controller
{
    public function __construct(
        protected AdminTelegramService $telegram,
        protected SellerWalletService  $walletService,
    ) {}

    // =========================================================================
    // ENTRY POINT
    // =========================================================================

    public function handle(Request $request)
    {
        if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== config('services.telegram.admin_webhook_secret')) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        Log::debug('Admin Telegram update', ['update_id' => $update['update_id'] ?? null]);

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        } elseif (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        return response('OK');
    }

    // =========================================================================
    // MESSAGE HANDLER
    // =========================================================================

    protected function handleMessage(array $message): void
    {
        $chatId = (string) $message['chat']['id'];
        $text   = trim($message['text'] ?? '');

        // /start <token> links an invited admin. Plain /start returns the chat ID.
        if (str_starts_with($text, '/start')) {
            $token = trim(substr($text, 7));

            if ($token !== '') {
                $this->linkInvitedAdmin($chatId, $token);
                return;
            }

            $this->telegram->sendMessage($chatId,
                "🔐 <b>Admin Bot</b>\n\n"
                . "Your Telegram Chat ID is:\n<code>{$chatId}</code>\n\n"
                . "Give this to your super-admin to enable notifications."
            );
            return;
        }

        // All other commands require a registered admin
        $admin = Admin::where('telegram_chat_id', $chatId)->with('role')->first();

        if (! $admin) {
            $this->telegram->sendMessage($chatId,
                "🔐 This bot is for registered administrators only.\n\n"
                . "Your chat ID: <code>{$chatId}</code>\n"
                . "Ask a super-admin to register you."
            );
            return;
        }

        match (true) {
            $text === '/help'           => $this->sendHelp($chatId, $admin),
            $text === '/pending'        => $this->showPendingCounts($chatId),
            $text === '/payouts'        => $this->showPendingPayouts($chatId),
            $text === '/sellers'        => $this->showPendingSellerApps($chatId),
            $text === '/reviews'        => $this->showPendingReviews($chatId),
            $text === '/orders'         => $this->showStuckOrders($chatId),
            $text === '/refunds'        => $this->showPendingRefunds($chatId),
            $text === '/stats'          => $this->showPlatformStats($chatId),
            // /payout 42 — details for payout #42
            preg_match('/^\/payout\s+(\d+)$/i', $text, $m) === 1
                                        => $this->showPayoutDetail($chatId, $admin, (int) $m[1]),
            // /seller 15 — details for seller #15
            preg_match('/^\/seller\s+(\d+)$/i', $text, $m) === 1
                                        => $this->showSellerDetail($chatId, (int) $m[1]),
            // /review 7 — details for review #7
            preg_match('/^\/review\s+(\d+)$/i', $text, $m) === 1
                                        => $this->showReviewDetail($chatId, $admin, (int) $m[1]),
            // /wallet 15 — wallet for seller #15
            preg_match('/^\/wallet\s+(\d+)$/i', $text, $m) === 1
                                        => $this->showSellerWallet($chatId, (int) $m[1]),
            default                     => $this->telegram->sendMessage($chatId,
                "Unknown command. Type /help to see available commands."
            ),
        };
    }

    protected function linkInvitedAdmin(string $chatId, string $token): void
    {
        $admin = Admin::where('telegram_link_token', $token)->with('role')->first();

        if (! $admin) {
            $this->telegram->sendMessage($chatId,
                "Invalid or expired admin invite.\n\nAsk a super-admin to send a new Telegram invite."
            );
            return;
        }

        $conflict = Admin::where('telegram_chat_id', $chatId)
            ->where('id', '!=', $admin->id)
            ->first();

        if ($conflict) {
            $this->telegram->sendMessage($chatId,
                "This Telegram account is already linked to another admin profile."
            );
            return;
        }

        $admin->update([
            'telegram_chat_id' => $chatId,
            'telegram_link_token' => null,
            'telegram_linked_at' => now(),
        ]);

        $this->telegram->sendMessage($chatId,
            "Admin Telegram linked successfully.\n\n"
            . "Hello <b>{$admin->name}</b>.\n"
            . "Role: <b>{$admin->role_name}</b>\n\n"
            . "Notifications will follow your role permissions and enabled preferences.\n"
            . "Type /help for available commands."
        );
    }

    // =========================================================================
    // CALLBACK HANDLER
    // =========================================================================

    protected function handleCallback(array $callbackQuery): void
    {
        $chatId    = (string) $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $cbId      = $callbackQuery['id'];
        $data      = $callbackQuery['data'];

        if ($data === 'noop') {
            $this->telegram->answerCallbackQuery($cbId);
            return;
        }

        $admin = Admin::where('telegram_chat_id', $chatId)->first();

        if (! $admin) {
            $this->telegram->answerCallbackQuery($cbId, 'Not authorized.', true);
            return;
        }

        [$action, $id] = array_pad(explode(':', $data, 2), 2, null);
        $id = $id ? (int) $id : null;

        match ($action) {
            'approve_payout'  => $this->approvePayout($chatId, $messageId, $cbId, $admin, $id),
            'approve_seller'  => $this->approveSeller($chatId, $messageId, $cbId, $admin, $id),
            'approve_review'  => $this->approveReview($chatId, $messageId, $cbId, $admin, $id),
            'reject_review'   => $this->rejectReview($chatId, $messageId, $cbId, $admin, $id),
            default           => $this->telegram->answerCallbackQuery($cbId, 'Unknown action'),
        };
    }

    // =========================================================================
    // COMMANDS
    // =========================================================================

    protected function sendHelp(string $chatId, Admin $admin): void
    {
        $this->telegram->sendMessage($chatId,
            "🔐 <b>Admin Bot Commands</b>\n\n"
            . "/pending — Action required counts\n"
            . "/payouts — Pending payout requests\n"
            . "/payout [id] — Payout details + approve\n"
            . "/sellers — Pending seller applications\n"
            . "/seller [id] — Seller profile\n"
            . "/reviews — Pending review queue\n"
            . "/review [id] — Review details + approve/reject\n"
            . "/orders — Orders stuck in processing\n"
            . "/refunds — Pending refund requests\n"
            . "/wallet [seller_id] — Seller wallet lookup\n"
            . "/stats — Platform snapshot\n"
            . "/help — Show this menu\n\n"
            . "Logged in as: <b>{$admin->name}</b> ({$admin->role_name})"
        );
    }

    /**
     * Quick count of everything requiring action.
     */
    protected function showPendingCounts(string $chatId): void
    {
        $payouts  = Payout::where('status', 'pending')->count();
        $sellers  = Seller::where('verification_status', 'pending')->count();
        $reviews  = Review::where('status', 'pending')->count();
        $refunds  = Order::where('status', 'cancelled')->where('payment_status', 'refund_pending')->count();
        $stuck    = Order::where('status', 'processing')
            ->where('updated_at', '<', now()->subHours(24))->count();

        $text = "📋 <b>Action Required</b>\n\n"
              . "💳 Payout requests: <b>{$payouts}</b>\n"
              . "🏪 Seller applications: <b>{$sellers}</b>\n"
              . "📝 Reviews to moderate: <b>{$reviews}</b>\n"
              . "💸 Refund requests: <b>{$refunds}</b>\n"
              . "⏳ Stuck orders (24h+): <b>{$stuck}</b>";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '💳 Payouts', 'callback_data' => 'noop'],
                ['text' => '📊 Dashboard', 'url' => url('/admin/dashboard')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * List pending payouts (up to 5).
     */
    protected function showPendingPayouts(string $chatId): void
    {
        $payouts = Payout::where('status', 'pending')
            ->with(['seller.user', 'seller.shop'])
            ->latest('requested_at')
            ->take(5)
            ->get();

        $total = Payout::where('status', 'pending')->count();
        $totalAmt = Payout::where('status', 'pending')->sum('amount');

        if ($payouts->isEmpty()) {
            $this->telegram->sendMessage($chatId, "✅ No pending payouts.");
            return;
        }

        $lines = $payouts->map(fn($p) =>
            "• <b>#{$p->id}</b> — {$p->seller->name} — ₦" . number_format($p->amount, 2)
            . "\n  Bank: {$p->seller->bank_name} | " . $p->requested_at->format('d M')
        )->implode("\n\n");

        $text = "💳 <b>Pending Payouts</b> ({$total} total — ₦" . number_format($totalAmt, 2) . ")\n\n"
              . $lines
              . ($total > 5 ? "\n\n<i>...and " . ($total - 5) . " more</i>" : '');

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 Full Queue', 'url' => url('/admin/finance/payouts?status=pending')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Show a single payout with approve button.
     */
    protected function showPayoutDetail(string $chatId, Admin $admin, int $payoutId): void
    {
        $payout = Payout::with(['seller.user', 'seller.shop'])->find($payoutId);

        if (! $payout) {
            $this->telegram->sendMessage($chatId, "❌ Payout #{$payoutId} not found.");
            return;
        }

        $seller = $payout->seller;

        $text = "💳 <b>Payout #{$payout->id}</b>\n\n"
              . "Seller: {$seller->user?->name}\n"
              . "Shop: {$seller->shop?->shop_name}\n"
              . "Amount: <b>₦" . number_format($payout->amount, 2) . "</b>\n"
              . "Net: ₦" . number_format($payout->net_amount ?? $payout->amount, 2) . "\n"
              . "Method: {$payout->payout_method}\n"
              . "Bank: {$seller->bank_name}\n"
              . "Account: <code>{$seller->bank_account}</code>\n"
              . "Account Name: {$seller->account_holder_name}\n"
              . "Status: <b>{$payout->status}</b>\n"
              . "Requested: {$payout->requested_at->format('d M Y, g:ia')}";

        $buttons = [];
        if ($payout->status === 'pending') {
            $buttons[] = ['text' => '✅ Approve (→ Processing)', 'callback_data' => "approve_payout:{$payout->id}"];
        }
        $buttons[] = ['text' => '🔗 Full Details', 'url' => url("/admin/finance/payouts/{$payout->id}")];

        $markup = ['inline_keyboard' => [array_filter($buttons)]];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Approve a payout (pending → processing) from Telegram.
     */
    protected function approvePayout(
        string $chatId, int $messageId, string $cbId, Admin $admin, int $payoutId
    ): void {
        if (! $admin->hasPermission('payouts.approve')) {
            $this->telegram->answerCallbackQuery($cbId, '⛔ You do not have permission to approve payouts.', true);
            return;
        }

        $payout = Payout::with(['seller.user'])->find($payoutId);

        if (! $payout || $payout->status !== 'pending') {
            $this->telegram->answerCallbackQuery($cbId, 'Payout not found or already processed.', true);
            return;
        }

        try {
            DB::beginTransaction();

            $payout->update([
                'status'       => 'processing',
                'processed_at' => now(),
                'notes'        => "Approved via Telegram by {$admin->name}",
            ]);

            DB::commit();

            // Notify seller
            $notifSeller = $payout->seller;
            if ($notifSeller->telegram_chat_id) {
                app(SellerTelegramService::class)->notifyPayoutApproved($notifSeller, $payout);
            }
            try {
                $payout->seller->user?->notify(new \App\Notifications\PayoutApproved($payout));
            } catch (\Exception $e) {
                Log::warning('PayoutApproved notification failed', ['payout_id' => $payout->id]);
            }

            $this->telegram->answerCallbackQuery($cbId, '✅ Payout approved!');
            $this->telegram->editMessageText($chatId, $messageId,
                "✅ <b>Payout #{$payout->id} Approved!</b>\n\n"
                . "Seller: {$payout->seller->user?->name}\n"
                . "Amount: ₦" . number_format($payout->amount, 2) . "\n\n"
                . "Please process the bank transfer and mark as completed on the site."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Telegram approve payout failed', ['payout_id' => $payoutId, 'error' => $e->getMessage()]);
            $this->telegram->answerCallbackQuery($cbId, '❌ Approval failed: ' . $e->getMessage(), true);
        }
    }

    /**
     * List pending seller applications.
     */
    protected function showPendingSellerApps(string $chatId): void
    {
        $applications = Seller::where('verification_status', 'pending')
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        $total = Seller::where('verification_status', 'pending')->count();

        if ($applications->isEmpty()) {
            $this->telegram->sendMessage($chatId, "✅ No pending seller applications.");
            return;
        }

        $lines = $applications->map(fn($s) =>
            "• <b>#{$s->id}</b> " . ($s->user?->name ?? 'N/A') . "\n"
            . "  " . ($s->business_type ?? 'N/A') . " · {$s->city}, {$s->country}\n"
            . "  Applied: " . $s->created_at->format('d M')
        )->implode("\n\n");

        $text = "🏪 <b>Pending Seller Applications</b> ({$total})\n\n{$lines}";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 Review All', 'url' => url('/admin/sellers/applications/view')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Show a single seller with approve button.
     */
    protected function showSellerDetail(string $chatId, int $sellerId): void
    {
        $seller = Seller::with(['user', 'shop'])->find($sellerId);

        if (! $seller) {
            $this->telegram->sendMessage($chatId, "❌ Seller #{$sellerId} not found.");
            return;
        }

        $text = "🏪 <b>Seller #{$seller->id}</b>\n\n"
              . "Name: {$seller->user?->name}\n"
              . "Email: {$seller->user?->email}\n"
              . "Shop: " . ($seller->shop?->shop_name ?? 'N/A') . "\n"
              . "Business: " . ($seller->business_type ?? 'N/A') . "\n"
              . "Location: {$seller->city}, {$seller->state}, {$seller->country}\n"
              . "Status: <b>{$seller->verification_status}</b>\n"
              . "Joined: {$seller->created_at->format('d M Y')}";

        $buttons = [];
        if ($seller->verification_status === 'pending') {
            $buttons[] = ['text' => '✅ Approve', 'callback_data' => "approve_seller:{$seller->id}"];
        }
        $buttons[] = ['text' => '🔗 Full Profile', 'url' => url("/admin/sellers/{$seller->id}")];

        $markup = ['inline_keyboard' => [array_filter($buttons)]];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Approve a seller application from Telegram.
     */
    protected function approveSeller(
        string $chatId, int $messageId, string $cbId, Admin $admin, int $sellerId
    ): void {
        if (! $admin->hasPermission('sellers.approve')) {
            $this->telegram->answerCallbackQuery($cbId, 'You do not have permission to approve sellers.', true);
            return;
        }

        $seller = Seller::with(['user', 'shop'])->find($sellerId);

        if (! $seller || $seller->verification_status !== 'pending') {
            $this->telegram->answerCallbackQuery($cbId, 'Seller not found or already processed.', true);
            return;
        }

        try {
            DB::beginTransaction();

            $seller->update(['verification_status' => 'verified']);
            $seller->shop?->update(['is_active' => true]);

            DB::commit();

            try {
                $seller->user?->notify(new \App\Notifications\SellerApproved());
            } catch (\Exception $e) {
                Log::warning('SellerApproved notification failed', ['seller_id' => $sellerId]);
            }

            $this->telegram->answerCallbackQuery($cbId, '✅ Seller approved!');
            $this->telegram->editMessageText($chatId, $messageId,
                "✅ <b>Seller Approved!</b>\n\n"
                . "Seller: {$seller->user?->name}\n"
                . "Shop: {$seller->shop?->shop_name}\n\n"
                . "They have been notified by email."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->telegram->answerCallbackQuery($cbId, '❌ Failed: ' . $e->getMessage(), true);
        }
    }

    /**
     * List pending reviews.
     */
    protected function showPendingReviews(string $chatId): void
    {
        $reviews = Review::where('status', 'pending')
            ->with(['product', 'user'])
            ->latest()
            ->take(5)
            ->get();

        $total = Review::where('status', 'pending')->count();

        if ($reviews->isEmpty()) {
            $this->telegram->sendMessage($chatId, "✅ No pending reviews.");
            return;
        }

        $lines = $reviews->map(fn($r) =>
            "• <b>#{$r->id}</b> " . str_repeat('⭐', (int) $r->rating)
            . " — {$r->product?->name}\n"
            . "  By: {$r->user?->name}\n"
            . "  <i>" . mb_substr($r->comment ?? 'No comment', 0, 60) . "…</i>"
        )->implode("\n\n");

        $text = "📝 <b>Pending Reviews</b> ({$total})\n\n{$lines}";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 Review Queue', 'url' => url('/admin/reviews?status=pending')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Show a single review with approve/reject buttons.
     */
    protected function showReviewDetail(string $chatId, Admin $admin, int $reviewId): void
    {
        $review = Review::with(['product', 'user'])->find($reviewId);

        if (! $review) {
            $this->telegram->sendMessage($chatId, "❌ Review #{$reviewId} not found.");
            return;
        }

        $stars   = str_repeat('⭐', (int) $review->rating) . str_repeat('☆', 5 - (int) $review->rating);
        $comment = $review->comment ?? 'No comment';

        $text = "📝 <b>Review #{$review->id}</b>\n\n"
              . "Product: {$review->product?->name}\n"
              . "By: {$review->user?->name}\n"
              . "Rating: {$stars}\n"
              . "Verified: " . ($review->is_verified_purchase ? 'Yes ✓' : 'No') . "\n"
              . "Status: <b>{$review->status}</b>\n\n"
              . "Comment:\n<i>\"" . htmlspecialchars(mb_substr($comment, 0, 300), ENT_QUOTES) . "\"</i>";

        $buttons = [];
        if ($review->status === 'pending') {
            $buttons[] = ['text' => '✅ Approve', 'callback_data' => "approve_review:{$review->id}"];
            $buttons[] = ['text' => '❌ Reject',  'callback_data' => "reject_review:{$review->id}"];
        }
        $buttons[] = ['text' => '🔗 Full Details', 'url' => url("/admin/reviews/{$review->id}")];

        $markup = ['inline_keyboard' => [array_filter($buttons)]];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Approve a review from Telegram.
     */
    protected function approveReview(
        string $chatId, int $messageId, string $cbId, Admin $admin, int $reviewId
    ): void {
        if (! $admin->hasAnyPermission(['products.edit', 'support.manage'])) {
            $this->telegram->answerCallbackQuery($cbId, 'You do not have permission to approve reviews.', true);
            return;
        }

        $review = Review::with('product')->find($reviewId);

        if (! $review || $review->status !== 'pending') {
            $this->telegram->answerCallbackQuery($cbId, 'Review not found or already processed.', true);
            return;
        }

        $review->approve($admin->id);

        // Notify seller via Telegram
        $seller = $review->product?->shop?->seller;
        if ($seller?->telegram_chat_id) {
            app(SellerTelegramService::class)->notifyReviewApproved($seller, $review);
        }

        $this->telegram->answerCallbackQuery($cbId, '✅ Review approved!');
        $this->telegram->editMessageText($chatId, $messageId,
            "✅ <b>Review #{$review->id} Approved!</b>\n\n"
            . "Product: {$review->product?->name}\n"
            . "Rating: " . str_repeat('⭐', (int) $review->rating) . "\n\n"
            . "Product rating has been recalculated."
        );
    }

    /**
     * Reject a review from Telegram.
     */
    protected function rejectReview(
        string $chatId, int $messageId, string $cbId, Admin $admin, int $reviewId
    ): void {
        if (! $admin->hasAnyPermission(['products.edit', 'support.manage'])) {
            $this->telegram->answerCallbackQuery($cbId, 'You do not have permission to reject reviews.', true);
            return;
        }

        $review = Review::find($reviewId);

        if (! $review || $review->status !== 'pending') {
            $this->telegram->answerCallbackQuery($cbId, 'Review not found or already processed.', true);
            return;
        }

        $review->reject($admin->id, 'Rejected via Telegram admin bot');

        $this->telegram->answerCallbackQuery($cbId, '❌ Review rejected.');
        $this->telegram->editMessageText($chatId, $messageId,
            "❌ <b>Review #{$review->id} Rejected.</b>\n\nFor full rejection notes, use the admin panel."
        );
    }

    /**
     * Orders stuck in processing for 24+ hours.
     */
    protected function showStuckOrders(string $chatId): void
    {
        $orders = Order::where('status', 'processing')
            ->where('updated_at', '<', now()->subHours(24))
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        $total = Order::where('status', 'processing')->where('updated_at', '<', now()->subHours(24))->count();

        if ($orders->isEmpty()) {
            $this->telegram->sendMessage($chatId, "✅ No orders stuck in processing.");
            return;
        }

        $lines = $orders->map(fn($o) =>
            "• <code>#{$o->order_number}</code> — ₦" . number_format($o->total, 2)
            . "\n  Stuck: " . $o->updated_at->diffForHumans()
        )->implode("\n\n");

        $text = "⏳ <b>Stuck Orders ({$total})</b>\n\n{$lines}";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 View All Orders', 'url' => url('/admin/orders?status=processing')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Pending refund requests.
     */
    protected function showPendingRefunds(string $chatId): void
    {
        $refunds = Order::where('status', 'cancelled')
            ->where('payment_status', 'refund_pending')
            ->with('user')
            ->latest('cancelled_at')
            ->take(5)
            ->get();

        $total    = Order::where('status', 'cancelled')->where('payment_status', 'refund_pending')->count();
        $totalAmt = Order::where('status', 'cancelled')->where('payment_status', 'refund_pending')->sum('total');

        if ($refunds->isEmpty()) {
            $this->telegram->sendMessage($chatId, "✅ No pending refunds.");
            return;
        }

        $lines = $refunds->map(fn($o) =>
            "• <code>#{$o->order_number}</code> — ₦" . number_format($o->total, 2)
            . "\n  {$o->customer_name} · " . ($o->cancelled_at?->format('d M') ?? 'N/A')
        )->implode("\n\n");

        $text = "💸 <b>Pending Refunds ({$total}) — ₦" . number_format($totalAmt, 2) . "</b>\n\n{$lines}";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 Process Refunds', 'url' => url('/admin/finance/refunds?status=refund_pending')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Full platform snapshot.
     */
    protected function showPlatformStats(string $chatId): void
    {
        $ordersToday  = Order::whereDate('created_at', today())->count();
        $revenueToday = Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total');
        $ordersMonth  = Order::whereMonth('created_at', now()->month)->count();
        $revenueMonth = Order::whereMonth('created_at', now()->month)->where('payment_status', 'paid')->sum('total');
        $totalSellers = Seller::where('verification_status', 'verified')->count();
        $activeOrders = Order::whereIn('status', ['pending', 'processing'])->count();
        $pendingPayouts = Payout::where('status', 'pending')->sum('amount');

        $text = "📊 <b>Platform Statistics</b>\n"
              . now()->format('d M Y, g:ia') . "\n\n"
              . "🛒 Orders today: <b>{$ordersToday}</b>\n"
              . "💰 Revenue today: <b>₦" . number_format($revenueToday, 2) . "</b>\n\n"
              . "📅 Orders this month: {$ordersMonth}\n"
              . "💰 Revenue this month: ₦" . number_format($revenueMonth, 2) . "\n\n"
              . "📦 Active orders: {$activeOrders}\n"
              . "🏪 Active sellers: {$totalSellers}\n"
              . "💳 Pending payouts: ₦" . number_format($pendingPayouts, 2);

        $markup = [
            'inline_keyboard' => [[
                ['text' => '📊 Full Dashboard', 'url' => url('/admin/dashboard')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Look up a seller's wallet by seller ID.
     */
    protected function showSellerWallet(string $chatId, int $sellerId): void
    {
        $seller = Seller::with(['user', 'shop', 'wallet'])->find($sellerId);

        if (! $seller) {
            $this->telegram->sendMessage($chatId, "❌ Seller #{$sellerId} not found.");
            return;
        }

        $wallet = $seller->wallet;

        if (! $wallet) {
            $this->telegram->sendMessage($chatId,
                "❌ Seller #{$sellerId} ({$seller->name}) has no wallet yet."
            );
            return;
        }

        $text = "💳 <b>Wallet — {$seller->name}</b>\n\n"
              . "Available: <b>₦" . number_format($wallet->balance, 2) . "</b>\n"
              . "Pending: ₦" . number_format($wallet->pending_balance, 2) . "\n"
              . "Reserved: ₦" . number_format($wallet->reserved_balance, 2) . "\n\n"
              . "Total earned: ₦" . number_format($wallet->total_earned, 2) . "\n"
              . "Total withdrawn: ₦" . number_format($wallet->total_withdrawn, 2) . "\n\n"
              . ($wallet->hasNegativeBalance() ? "⚠️ <b>Negative balance!</b>\n\n" : '')
              . "Last tx: " . ($wallet->last_transaction_at?->format('d M, g:ia') ?? 'N/A');

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 Full Wallet', 'url' => url("/admin/sellers/{$seller->id}/wallet")],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }
}

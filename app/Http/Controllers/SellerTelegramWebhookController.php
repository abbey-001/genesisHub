<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Seller;
use App\Services\Telegram\SellerTelegramService;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Seller Bot Webhook — handles all incoming Telegram updates for the seller bot.
 *
 * Flow:
 *   Telegram → POST /telegram/seller/webhook → handle()
 *     → handleMessage()   for text commands
 *     → handleCallback()  for inline button taps
 *
 * Route (add to routes/web.php):
 *   Route::post('/telegram/seller/webhook',
 *       [\App\Http\Controllers\SellerTelegramWebhookController::class, 'handle']
 *   )->name('telegram.seller.webhook');
 */
class SellerTelegramWebhookController extends Controller
{
    public function __construct(
        protected SellerTelegramService $telegram,
        protected SellerWalletService   $walletService,
    ) {}

    // =========================================================================
    // ENTRY POINT
    // =========================================================================

    public function handle(Request $request)
    {
        
        // Verify the webhook secret header
        if ($request->header('X-Telegram-Bot-Api-Secret-Token') !== config('services.telegram.seller_webhook_secret')) {
            return response('Unauthorized', 401);
        }

        $update = $request->all();
        Log::debug('Seller Telegram update', ['type' => array_key_first(array_diff_key($update, ['update_id' => 1]))]);

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

        // /start — account linking flow
        if (str_starts_with($text, '/start')) {
            $token = trim(substr($text, 7)); // everything after "/start "
            $token ? $this->linkAccount($chatId, $token) : $this->sendWelcomeOrStatus($chatId);
            return;
        }

        // Look up the seller by chat ID
        $seller = Seller::where('telegram_chat_id', $chatId)->with(['user', 'shop', 'wallet'])->first();

        if (! $seller) {
            $this->telegram->sendMessage($chatId,
                "👋 Hi! Please link your Telegram account first.\n\n"
                . "Go to <b>Seller Settings → Connect Telegram</b> on the website."
            );
            return;
        }

        // Route to the appropriate command handler
        match (true) {
            $text === '/help'    => $this->sendHelp($chatId),
            $text === '/orders'  => $this->showRecentOrders($chatId, $seller),
            $text === '/wallet'  => $this->showWallet($chatId, $seller),
            $text === '/stats'   => $this->showStats($chatId, $seller),
            $text === '/reviews' => $this->showPendingReviews($chatId, $seller),
            $text === '/payout'  => $this->showPayoutMenu($chatId, $seller),
            $text === '/cancel'  => $this->cancelState($chatId),
            // Handle /order <number> — order detail lookup
            preg_match('/^\/order\s+(.+)$/i', $text, $m) === 1
                        => $this->showOrderDetail($chatId, $seller, trim($m[1])),
            default     => $this->telegram->sendMessage($chatId,
                "I didn't understand that command. Type /help to see what I can do."
            ),
        };
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

        $seller = Seller::where('telegram_chat_id', $chatId)->with(['user', 'shop', 'wallet'])->first();

        if (! $seller) {
            $this->telegram->answerCallbackQuery($cbId, 'Account not linked.', true);
            return;
        }

        [$action, $id, $extra] = array_pad(explode(':', $data, 3), 3, null);
        $id = $id ? (int) $id : null;

        match ($action) {
            'payout_confirm'   => $this->confirmPayout($chatId, $messageId, $cbId, $seller, (float) ($extra ?? 0)),
            'payout_cancel'    => $this->cancelPayoutRequest($chatId, $messageId, $cbId, $seller, $id),
            'orders_page'      => $this->showRecentOrders($chatId, $seller, (int) ($extra ?? 1)),
            default            => $this->telegram->answerCallbackQuery($cbId, 'Unknown action'),
        };
    }

    // =========================================================================
    // ACCOUNT LINKING
    // =========================================================================

    protected function linkAccount(string $chatId, string $token): void
    {
        $seller = Seller::where('telegram_link_token', $token)->with('user')->first();

        if (! $seller) {
            $this->telegram->sendMessage($chatId,
                "❌ Invalid or expired link.\n\nPlease generate a new one from <b>Seller Settings → Telegram</b>."
            );
            return;
        }

        // Prevent one chat from linking to two seller accounts
        if ($seller->telegram_chat_id && $seller->telegram_chat_id !== $chatId) {
            $this->telegram->sendMessage($chatId,
                "⚠️ This account is already linked to a different Telegram account.\n"
                . "Unlink it first from your seller settings."
            );
            return;
        }
        
        

        $seller->update([
            'telegram_chat_id'    => $chatId,
            'telegram_link_token' => null,
            'telegram_linked_at'  => now(),
        ]);

        $this->telegram->sendWelcomeMessage($chatId, $seller->user->name ?? $seller->name);
    }

    protected function sendWelcomeOrStatus(string $chatId): void
    {
        $seller = Seller::where('telegram_chat_id', $chatId)->first();

        if ($seller) {
            $this->telegram->sendMessage($chatId,
                "👋 Welcome back, <b>{$seller->name}</b>!\n\n"
                . "/orders — Recent orders\n"
                . "/wallet — Wallet summary\n"
                . "/stats — Today's snapshot\n"
                . "/reviews — Pending reviews\n"
                . "/payout — Request withdrawal\n"
                . "/help — Full help"
            );
        } else {
            $this->telegram->sendMessage($chatId,
                "👋 Hi! Link your account from <b>Seller Settings → Connect Telegram</b>."
            );
        }
    }

    // =========================================================================
    // COMMANDS
    // =========================================================================

    protected function sendHelp(string $chatId): void
    {
        $this->telegram->sendMessage($chatId,
            "📖 <b>Seller Bot Commands</b>\n\n"
            . "/orders — Your last 10 orders\n"
            . "/order [#number] — Details for a specific order\n"
            . "/wallet — Balance, pending & earnings\n"
            . "/payout — Request a withdrawal\n"
            . "/stats — Today's performance snapshot\n"
            . "/reviews — Reviews waiting for your response\n"
            . "/cancel — Cancel a pending action\n"
            . "/help — Show this menu\n\n"
            . "<i>You'll also receive push alerts for orders, payouts, reviews, and stock.</i>"
        );
    }

    /**
     * Show the last 10 orders for this seller, with item status per order.
     */
    protected function showRecentOrders(string $chatId, Seller $seller, int $page = 1): void
    {
        $perPage = 5;
        $skip    = ($page - 1) * $perPage;

        $orders = Order::whereHas('items', fn($q) => $q->where('seller_id', $seller->id))
            ->with(['items' => fn($q) => $q->where('seller_id', $seller->id)])
            ->latest()
            ->skip($skip)
            ->take($perPage + 1) // +1 to detect if there's a next page
            ->get();

        if ($orders->isEmpty() && $page === 1) {
            $this->telegram->sendMessage($chatId, "📭 You have no orders yet.");
            return;
        }

        $hasMore      = $orders->count() > $perPage;
        $displayOrders = $orders->take($perPage);

        $lines = $displayOrders->map(function (Order $order) use ($seller) {
            $items       = $order->items->where('seller_id', $seller->id);
            $total       = $items->sum('total_price');
            $statuses    = $items->pluck('status')->unique()->implode(', ');
            $statusEmoji = match ($order->status) {
                'pending'    => '🟡',
                'processing' => '🔵',
                'shipped'    => '🚚',
                'delivered'  => '✅',
                'cancelled'  => '❌',
                default      => '⚪',
            };

            return "{$statusEmoji} <code>#{$order->order_number}</code> — ₦" . number_format($total, 2)
                 . "\n   <i>{$statuses}</i> · " . $order->created_at->format('d M');
        })->implode("\n\n");

        $text = "📦 <b>Recent Orders</b> (page {$page})\n\n{$lines}";

        $buttons = [];
        if ($page > 1) {
            $buttons[] = ['text' => '⬅️ Previous', 'callback_data' => "orders_page:0:" . ($page - 1)];
        }
        if ($hasMore) {
            $buttons[] = ['text' => 'Next ➡️', 'callback_data' => "orders_page:0:" . ($page + 1)];
        }

        $markup = $buttons ? ['inline_keyboard' => [$buttons]] : [];
        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Show details for a specific order by number.
     */
    protected function showOrderDetail(string $chatId, Seller $seller, string $orderNumber): void
    {
        $order = Order::where('order_number', $orderNumber)
            ->whereHas('items', fn($q) => $q->where('seller_id', $seller->id))
            ->with(['items' => fn($q) => $q->where('seller_id', $seller->id)->with('product')])
            ->first();

        if (! $order) {
            $this->telegram->sendMessage($chatId,
                "❌ Order <code>#{$orderNumber}</code> not found or doesn't belong to your shop."
            );
            return;
        }

        $items    = $order->items->where('seller_id', $seller->id);
        $total    = $items->sum('total_price');
        $commission = $seller->commission_rate ?? config('platform.commission_rate', 10);
        $net      = round($total * (1 - $commission / 100), 2);

        $itemLines = $items->map(fn($i) =>
            "• {$i->product_name} × {$i->quantity} — ₦" . number_format($i->total_price, 2)
            . "\n  Status: <i>{$i->status}</i>"
            . ($i->expected_ready_by ? "\n  Ready by: {$i->expected_ready_by->format('d M Y')}" : '')
        )->implode("\n\n");

        $text = "📋 <b>Order #{$order->order_number}</b>\n\n"
              . "Customer: {$order->customer_name}\n"
              . "Placed: {$order->created_at->format('d M Y, g:ia')}\n"
              . "Order status: <b>{$order->status}</b>\n"
              . "Payment: {$order->payment_status}\n\n"
              . "<b>Your Items:</b>\n{$itemLines}\n\n"
              . "Gross: ₦" . number_format($total, 2) . "\n"
              . "Net: <b>₦" . number_format($net, 2) . "</b>";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🔗 View on Site', 'url' => url("/seller/orders/{$order->id}")],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Show wallet summary with available, pending, and lifetime stats.
     */
    protected function showWallet(string $chatId, Seller $seller): void
    {
        $summary = $this->walletService->getWalletSummary($seller);

        $text = "💳 <b>Wallet Summary</b>\n\n"
              . "✅ Available: <b>₦" . number_format($summary['balance'], 2) . "</b>\n"
              . "⏳ Pending (on hold): ₦" . number_format($summary['pending_balance'], 2) . "\n"
              . "🔒 Reserved: ₦" . number_format($summary['reserved_balance'], 2) . "\n\n"
              . "📈 Total earned: ₦" . number_format($summary['total_earned'], 2) . "\n"
              . "📤 Total withdrawn: ₦" . number_format($summary['total_withdrawn'], 2) . "\n\n"
              . ($summary['has_negative_balance'] ? "⚠️ <b>Your balance is negative due to a refund.</b>\n\n" : '')
              . ($summary['last_transaction_at']
                  ? "<i>Last transaction: " . $summary['last_transaction_at']->format('d M, g:ia') . "</i>"
                  : '<i>No transactions yet</i>');

        $markup = [
            'inline_keyboard' => [[
                ['text' => '💸 Request Payout', 'callback_data' => 'noop'],
                ['text' => '📊 Full History', 'url' => url('/seller/payouts/transactions/history')],
            ]],
        ];

        // If available balance is sufficient, offer a payout button
        $minimum = $seller->payoutSettings?->minimum_payout ?? 10.00;
        if ($summary['balance'] >= $minimum) {
            $markup = [
                'inline_keyboard' => [[
                    ['text' => '💸 Request Payout', 'callback_data' => "payout_confirm:0:{$summary['balance']}"],
                    ['text' => '📊 Full History', 'url' => url('/seller/payouts/transactions/history')],
                ]],
            ];
        }

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Today's quick performance snapshot.
     */
    protected function showStats(string $chatId, Seller $seller): void
    {
        $shop = $seller->shop;

        $todayOrders  = Order::whereHas('items', fn($q) => $q->where('seller_id', $seller->id))
            ->whereDate('created_at', today())->count();
        $todayRevenue = OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', fn($q) => $q->whereDate('created_at', today())->where('payment_status', 'paid'))
            ->sum('total_price');
        $weekRevenue  = OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', fn($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()])->where('payment_status', 'paid'))
            ->sum('total_price');
        $pendingItems = OrderItem::where('seller_id', $seller->id)
            ->whereIn('status', ['pending', 'processing'])->count();
        $readyItems   = OrderItem::where('seller_id', $seller->id)
            ->where('status', 'ready_for_pickup')->count();
        $activeProds  = $seller->products()->where('products.is_active', true)->count(); 
        $lowStock     = $seller->products()->where('products.is_active', true)->where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $outOfStock   = $seller->products()->where('stock', 0)->count();

        $text = "📊 <b>Your Dashboard Snapshot</b>\n\n"
              . "🛒 Orders today: <b>{$todayOrders}</b>\n"
              . "💰 Revenue today: <b>₦" . number_format($todayRevenue, 2) . "</b>\n"
              . "💰 Revenue this week: ₦" . number_format($weekRevenue, 2) . "\n\n"
              . "📦 Items to prepare: <b>{$pendingItems}</b>\n"
              . "✅ Ready for pickup: <b>{$readyItems}</b>\n\n"
              . "🏷️ Active products: {$activeProds}\n"
              . "⚠️ Low stock (≤5): {$lowStock}\n"
              . "🚫 Out of stock: {$outOfStock}";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '🏪 Seller Dashboard', 'url' => url('/seller/dashboard')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Reviews that need the seller's response.
     */
    protected function showPendingReviews(string $chatId, Seller $seller): void
    {
        $productIds = $seller->products()->pluck('id');

        $reviews = \App\Models\Review::whereIn('product_id', $productIds)
            ->where('is_approved', true)
            ->whereNull('seller_response')
            ->with('product')
            ->latest()
            ->take(5)
            ->get();

        if ($reviews->isEmpty()) {
            $this->telegram->sendMessage($chatId,
                "✅ No reviews waiting for your response!"
            );
            return;
        }

        $lines = $reviews->map(function ($review) {
            $stars = str_repeat('⭐', (int) $review->rating);
            $comment = $review->comment
                ? mb_substr($review->comment, 0, 80) . (mb_strlen($review->comment) > 80 ? '…' : '')
                : 'No comment';
            return "{$stars} <b>{$review->product->name}</b>\n<i>\"{$comment}\"</i>";
        })->implode("\n\n");

        $total = \App\Models\Review::whereIn('product_id', $productIds)
            ->where('is_approved', true)->whereNull('seller_response')->count();

        $text = "📝 <b>Reviews Awaiting Response</b>\n"
              . "({$total} total, showing latest 5)\n\n{$lines}";

        $markup = [
            'inline_keyboard' => [[
                ['text' => '💬 Respond on Site', 'url' => url('/seller/reviews')],
            ]],
        ];

        $this->telegram->sendMessage($chatId, $text, $markup);
    }

    /**
     * Payout menu — shows balance and requests confirmation.
     */
    protected function showPayoutMenu(string $chatId, Seller $seller): void
    {
        $summary = $this->walletService->getWalletSummary($seller);
        $available = $summary['balance'];
        $minimum   = $seller->payoutSettings?->minimum_payout ?? 10.00;

        if ($available < $minimum) {
            $this->telegram->sendMessage($chatId,
                "💳 <b>Payout</b>\n\n"
                . "Available balance: ₦" . number_format($available, 2) . "\n"
                . "Minimum payout: ₦" . number_format($minimum, 2) . "\n\n"
                . "You need at least ₦" . number_format($minimum, 2) . " to request a payout."
            );
            return;
        }

        $this->telegram->sendMessage($chatId,
            "💸 <b>Request Payout</b>\n\n"
            . "Available balance: <b>₦" . number_format($available, 2) . "</b>\n"
            . "Bank: {$seller->bank_name}\n"
            . "Account: {$seller->bank_account}\n\n"
            . "Tap <b>Confirm</b> to request your full available balance.\n"
            . "For a custom amount, use the website.",
            [
                'inline_keyboard' => [[
                    ['text' => '✅ Confirm', 'callback_data' => "payout_confirm:0:{$available}"],
                    ['text' => '❌ Cancel',  'callback_data' => 'noop'],
                ]],
            ]
        );
    }

    /**
     * Process payout confirmation from inline button.
     */
    protected function confirmPayout(
        string $chatId, int $messageId, string $cbId, Seller $seller, float $amount
    ): void {
        $minimum = $seller->payoutSettings?->minimum_payout ?? 10.00;

        if ($amount < $minimum) {
            $this->telegram->answerCallbackQuery($cbId, 'Insufficient balance.', true);
            return;
        }

        try {
            $payout = $this->walletService->requestPayout($seller, [
                'amount'        => $amount,
                'payout_method' => $seller->payoutSettings?->preferred_method ?? 'bank_transfer',
                'notes'         => 'Requested via Telegram',
            ]);

            $this->telegram->answerCallbackQuery($cbId, 'Payout requested!');
            $this->telegram->editMessageText($chatId, $messageId,
                "✅ <b>Payout Request Submitted!</b>\n\n"
                . "Amount: ₦" . number_format($payout->amount, 2) . "\n"
                . "Net: ₦" . number_format($payout->net_amount, 2) . "\n\n"
                . "You'll be notified here when it's approved."
            );

        } catch (\Exception $e) {
            $this->telegram->answerCallbackQuery($cbId, $e->getMessage(), true);
        }
    }

    /**
     * Cancel an existing pending payout from Telegram.
     */
    protected function cancelPayoutRequest(
        string $chatId, int $messageId, string $cbId, Seller $seller, ?int $payoutId
    ): void {
        if (! $payoutId) {
            $this->telegram->answerCallbackQuery($cbId, 'Payout ID missing.', true);
            return;
        }

        $payout = \App\Models\Payout::where('id', $payoutId)
            ->where('seller_id', $seller->id)
            ->first();

        if (! $payout || ! $payout->canBeCancelled()) {
            $this->telegram->answerCallbackQuery($cbId, 'Cannot cancel this payout.', true);
            return;
        }

        try {
            $this->walletService->failPayout($payout, 'Cancelled by seller via Telegram');

            $this->telegram->answerCallbackQuery($cbId, 'Payout cancelled');
            $this->telegram->editMessageText($chatId, $messageId,
                "✅ Payout cancelled. ₦" . number_format($payout->amount, 2) . " returned to your wallet."
            );
        } catch (\Exception $e) {
            $this->telegram->answerCallbackQuery($cbId, $e->getMessage(), true);
        }
    }

    protected function cancelState(string $chatId): void
    {
        $this->telegram->sendMessage($chatId, "✅ OK, nothing to cancel.");
    }
}
<?php

namespace App\Services\Telegram;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Seller;

/**
 * Seller Bot — message builders and push notification helpers.
 *
 * All "notify*" methods are called from existing notification classes
 * (or directly from controllers/services) when a relevant event fires.
 * They handle the null-check on telegram_chat_id so callers don't need to.
 *
 * Bot token: config('services.telegram.seller_bot_token')
 */
class SellerTelegramService extends BaseTelegramService
{
    public function __construct()
    {
        $this->token   = config('services.telegram.seller_bot_token');
        $this->apiBase = $this->token ? "https://api.telegram.org/bot{$this->token}" : null;
    }

    // =========================================================================
    // GUARD — skip silently if seller has no chat ID or has opted out
    // =========================================================================

    /**
     * Returns true if the seller should receive Telegram messages.
     */
    public function isReachable(Seller $seller): bool
    {
        return ! empty($seller->telegram_chat_id)
            && $seller->telegram_notifications_enabled;
    }

    // =========================================================================
    // PUSH NOTIFICATIONS — called from notification classes / event listeners
    // =========================================================================

    /**
     * New order placed — sent immediately after payment is confirmed.
     * Shows items belonging to this seller only.
     */
    public function notifyNewOrder(Seller $seller, Order $order): void
    {
        if (! $this->isReachable($seller)) return;

        $sellerItems = $order->items->where('seller_id', $seller->id);
        $itemCount   = $sellerItems->count();
        $gross       = $sellerItems->sum('total_price');
        $commission  = $seller->commission_rate ?? config('platform.commission_rate', 10);
        $net         = round($gross * (1 - $commission / 100), 2);

        $itemLines = $sellerItems->map(
            fn($i) => "• {$this->e($i->product_name)} × {$i->quantity}"
        )->implode("\n");

        $fulfillmentNote = $sellerItems->contains(
            fn($i) => in_array($i->fulfillment_type, ['pre_order', 'made_to_order'])
        ) ? "\n⚠️ <i>Contains pre-order / made-to-order item(s)</i>" : '';

        $deadline = $sellerItems->first()?->expected_ready_by
            ? "\n⏰ Ready by: <b>" . $sellerItems->first()->expected_ready_by->format('d M Y') . '</b>'
            : '';

        $text = "🛒 <b>New Order!</b>\n\n"
              . "Order: <code>#{$order->order_number}</code>\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Zone: {$this->e($order->shipping_zone ?? 'N/A')}\n\n"
              . "<b>Your Items ({$itemCount}):</b>\n{$itemLines}\n\n"
              . "Gross: {$this->naira($gross)}\n"
              . "Net (after {$commission}% commission): <b>{$this->naira($net)}</b>"
              . $fulfillmentNote
              . $deadline;

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📋 View Order', url("/seller/orders/{$order->id}")),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Order item is approaching its ready-by deadline (24 hrs warning).
     */
    public function notifyDeadlineApproaching(Seller $seller, OrderItem $item): void
    {
        if (! $this->isReachable($seller)) return;

        $hoursLeft = now()->diffInHours($item->expected_ready_by, false);

        $text = "⏰ <b>Deadline Approaching!</b>\n\n"
              . "Order: <code>#{$item->order->order_number}</code>\n"
              . "Item: {$this->e($item->product_name)}\n"
              . "Ready by: <b>{$item->expected_ready_by->format('d M Y, g:ia')}</b>\n"
              . "Time left: <b>~{$hoursLeft} hours</b>\n\n"
              . "Please pack and mark as ready before the deadline.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📦 Mark Ready', url("/seller/orders/{$item->order_id}")),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Item is now overdue — seller missed the ready-by deadline.
     */
    public function notifyItemOverdue(Seller $seller, OrderItem $item): void
    {
        if (! $this->isReachable($seller)) return;

        $daysLate = abs($item->days_until_deadline);

        $text = "🚨 <b>Item Overdue!</b>\n\n"
              . "Order: <code>#{$item->order->order_number}</code>\n"
              . "Item: {$this->e($item->product_name)}\n"
              . "Was due: {$item->expected_ready_by->format('d M Y')}\n"
              . "Overdue by: <b>{$daysLate} day(s)</b>\n\n"
              . "Please mark as ready immediately to avoid further issues.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('⚠️ Handle Now', url("/seller/orders/{$item->order_id}")),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Customer cancelled an order that contained this seller's items.
     */
    public function notifyOrderCancelled(Seller $seller, Order $order): void
    {
        if (! $this->isReachable($seller)) return;

        $sellerItems = $order->items->where('seller_id', $seller->id);
        $gross       = $sellerItems->sum('total_price');

        $text = "❌ <b>Order Cancelled</b>\n\n"
              . "Order: <code>#{$order->order_number}</code>\n"
              . "Customer: {$this->e($order->customer_name)}\n"
              . "Your items: {$sellerItems->count()}\n"
              . "Value lost: {$this->naira($gross)}";

        $this->sendMessage($seller->telegram_chat_id, $text);
    }

    /**
     * Payout request approved (→ processing).
     */
    public function notifyPayoutApproved(Seller $seller, Payout $payout): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "✅ <b>Payout Approved!</b>\n\n"
              . "Amount: {$this->naira($payout->amount)}\n"
              . "Net (you receive): <b>{$this->naira($payout->net_amount)}</b>\n"
              . "Method: {$this->e($payout->payout_method_label)}\n\n"
              . "Your transfer is being processed. You'll be notified when it lands.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📊 View Payout', url("/seller/payouts/{$payout->id}")),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Payout completed — money has been sent.
     */
    public function notifyPayoutCompleted(Seller $seller, Payout $payout): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "💸 <b>Payout Sent!</b>\n\n"
              . "{$this->naira($payout->net_amount)} has been sent to your bank.\n\n"
              . "Bank: {$this->e($seller->bank_name ?? 'N/A')}\n"
              . "Account: {$this->e($seller->bank_account ?? 'N/A')}\n"
              . "Ref: <code>{$payout->transaction_id}</code>";

        $this->sendMessage($seller->telegram_chat_id, $text);
    }

    /**
     * Payout rejected — funds returned to wallet.
     */
    public function notifyPayoutRejected(Seller $seller, Payout $payout): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "❌ <b>Payout Rejected</b>\n\n"
              . "Amount: {$this->naira($payout->amount)}\n"
              . "Reason: {$this->e($payout->failure_reason ?? 'No reason provided')}\n\n"
              . "{$this->naira($payout->amount)} has been returned to your wallet balance.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('💳 View Wallet', url('/seller/payouts')),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Pending balance released to available (after hold period).
     */
    public function notifyPendingReleased(Seller $seller, float $amount): void
    {
        if (! $this->isReachable($seller)) return;

        $wallet = $seller->wallet;

        $text = "💰 <b>Earnings Available!</b>\n\n"
              . "{$this->naira($amount)} has moved from pending to your available balance.\n\n"
              . "Available balance: <b>{$this->naira($wallet->balance ?? 0)}</b>\n\n"
              . "You can now request a withdrawal.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('💳 Request Payout', url('/seller/payouts')),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * New review received on one of the seller's products.
     */
    public function notifyNewReview(Seller $seller, Review $review): void
    {
        if (! $this->isReachable($seller)) return;

        $stars   = str_repeat('⭐', (int) $review->rating) . str_repeat('☆', 5 - (int) $review->rating);
        $comment = $review->comment ? $this->truncate($this->e($review->comment), 150) : '<i>No comment</i>';

        $text = "📝 <b>New Review!</b>\n\n"
              . "Product: {$this->e($review->product->name ?? 'Unknown')}\n"
              . "Rating: {$stars} ({$review->rating}/5)\n"
              . "Review: {$comment}\n\n"
              . "<i>Reviews go live after admin approval.</i>";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('💬 View Reviews', url('/seller/reviews')),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Review approved — now visible to buyers.
     */
    public function notifyReviewApproved(Seller $seller, Review $review): void
    {
        if (! $this->isReachable($seller)) return;

        $stars = str_repeat('⭐', (int) $review->rating);

        $text = "✅ <b>Review Approved</b>\n\n"
              . "A {$stars} review for <b>{$this->e($review->product->name ?? 'your product')}</b> "
              . "is now live on your shop.";

        $this->sendMessage($seller->telegram_chat_id, $text);
    }

    /**
     * Low stock warning.
     */
    public function notifyLowStock(Seller $seller, \App\Models\Product $product): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "⚠️ <b>Low Stock Alert</b>\n\n"
              . "Product: <b>{$this->e($product->name)}</b>\n"
              . "Remaining: <b>{$product->stock} unit(s)</b>\n\n"
              . "Update your stock before you run out.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📦 Update Stock', url("/seller/products/{$product->id}/edit")),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Product is now completely out of stock.
     */
    public function notifyOutOfStock(Seller $seller, \App\Models\Product $product): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "🚫 <b>Out of Stock!</b>\n\n"
              . "Product: <b>{$this->e($product->name)}</b>\n\n"
              . "This product has been automatically deactivated from search results.\n"
              . "Restock to make it visible again.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('📦 Restock Now', url("/seller/products/{$product->id}/edit")),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    /**
     * Shop suspended by admin.
     */
    public function notifyShopSuspended(Seller $seller, string $reason): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "🔴 <b>Your Shop Has Been Suspended</b>\n\n"
              . "Reason: {$this->e($reason)}\n\n"
              . "Your shop and all product listings are now hidden from buyers.\n"
              . "Please contact support to resolve this.";

        $this->sendMessage($seller->telegram_chat_id, $text);
    }

    /**
     * Shop reactivated by admin.
     */
    public function notifyShopReactivated(Seller $seller): void
    {
        if (! $this->isReachable($seller)) return;

        $text = "🟢 <b>Your Shop Is Live Again!</b>\n\n"
              . "Your seller account has been reactivated.\n"
              . "Your shop and products are now visible to buyers.";

        $markup = [
            'inline_keyboard' => [[
                $this->urlButton('🏪 Go to Dashboard', url('/seller/dashboard')),
            ]],
        ];

        $this->sendMessage($seller->telegram_chat_id, $text, $markup);
    }

    // =========================================================================
    // LINKING FLOW
    // =========================================================================

    /**
     * Sent after a seller successfully links their Telegram account.
     */
    public function sendWelcomeMessage(string $chatId, string $sellerName): void
    {
        $text = "🎉 <b>Account Linked Successfully!</b>\n\n"
              . "Welcome, <b>{$this->e($sellerName)}</b>!\n\n"
              . "You'll now receive real-time alerts for:\n"
              . "🛒 New orders\n"
              . "💰 Wallet & payout updates\n"
              . "📝 New reviews\n"
              . "⚠️ Stock alerts\n\n"
              . "<b>Commands:</b>\n"
              . "/orders — Recent orders\n"
              . "/wallet — Wallet summary\n"
              . "/stats — Today's snapshot\n"
              . "/help — Full command list";

        $this->sendMessage($chatId, $text);
    }
}

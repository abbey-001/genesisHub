<?php
// app/Services/TelegramService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected string $token;
    protected string $apiBase;

    public function __construct()
    {
        $this->token   = config('services.telegram.bot_token');
        $this->apiBase = "https://api.telegram.org/bot{$this->token}";
    }

    // ── Core send methods ─────────────────────────────────────────

    public function sendMessage(string $chatId, string $text, array $replyMarkup = []): void
    {
        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            Http::post("{$this->apiBase}/sendMessage", $payload);
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage failed', ['error' => $e->getMessage()]);
        }
    }

    public function sendPhoto(string $chatId, string $photoUrl, string $caption = '', array $replyMarkup = []): void
    {
        $payload = [
            'chat_id'    => $chatId,
            'photo'      => $photoUrl,
            'caption'    => $caption,
            'parse_mode' => 'HTML',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            Http::post("{$this->apiBase}/sendPhoto", $payload);
        } catch (\Exception $e) {
            Log::error('Telegram sendPhoto failed', ['error' => $e->getMessage()]);
        }
    }

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): void
    {
        Http::post("{$this->apiBase}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ]);
    }

    public function editMessageText(string $chatId, int $messageId, string $text, array $replyMarkup = []): void
    {
        $payload = [
            'chat_id'    => $chatId,
            'message_id' => $messageId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if (!empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        try {
            Http::post("{$this->apiBase}/editMessageText", $payload);
        } catch (\Exception $e) {
            Log::error('Telegram editMessageText failed', ['error' => $e->getMessage()]);
        }
    }

    // ── Notification helpers ──────────────────────────────────────

    /**
     * Notify a rider of a new broadcast (single delivery or bundle)
     */
    public function notifyNewBroadcast(string $chatId, \App\Models\DeliveryBroadcast $broadcast, int $fee): void
    {
        if ($broadcast->is_bundle) {
            $bundle    = $broadcast->bundle;
            $stopCount = $bundle->deliveries()->count();
            $partial   = $broadcast->is_partial ? "\n⚠️ <i>More stops may be added</i>" : '';

            $text = "🚀 <b>New Bundle Delivery Available!</b>\n\n"
                  . "📦 <b>Stops:</b> {$stopCount} seller(s) in <b>{$bundle->pickup_zone}</b>{$partial}\n"
                  . "📍 <b>Deliver to:</b> {$bundle->order->shipping_address}, {$bundle->order->shipping_city}\n"
                  . "💰 <b>Earnings:</b> ₦" . number_format($fee) . "\n\n"
                  . "⏰ Respond quickly before another rider accepts!";
        } else {
            $delivery = $broadcast->delivery;
            $text = "🚀 <b>New Delivery Available!</b>\n\n"
                  . "📦 <b>Pickup:</b> {$delivery->pickup_address}\n"
                  . "📍 <b>Deliver to:</b> {$delivery->delivery_address}\n"
                  . "💰 <b>Earnings:</b> ₦" . number_format($delivery->delivery_fee) . "\n\n"
                  . "⏰ Respond quickly before another rider accepts!";
        }

        $markup = [
            'inline_keyboard' => [[
                ['text' => '✅ Accept', 'callback_data' => "accept_broadcast:{$broadcast->id}"],
                ['text' => '❌ Reject', 'callback_data' => "reject_broadcast:{$broadcast->id}"],
            ]],
        ];

        $this->sendMessage($chatId, $text, $markup);
    }

    /**
     * Notify rider when assigned to a delivery
     */
    public function notifyAssigned(string $chatId, \App\Models\Delivery $delivery): void
    {
        $text = "✅ <b>Delivery Assigned!</b>\n\n"
              . "🆔 Order: <code>{$delivery->order->order_number}</code>\n"
              . "🏪 Pickup from: {$delivery->pickup_address}\n"
              . "📍 Deliver to: {$delivery->delivery_address}\n"
              . "💰 Fee: ₦" . number_format($delivery->delivery_fee);

        $markup = [
            'inline_keyboard' => [[
                ['text' => '📷 Confirm Pickup', 'callback_data' => "pickup:{$delivery->id}"],
            ]],
        ];

        $this->sendMessage($chatId, $text, $markup);
    }

    /**
     * Notify payout status change
     */
    public function notifyPayoutStatus(string $chatId, \App\Models\DeliveryPayout $payout): void
    {
        $statusEmoji = match($payout->status) {
            'approved' => '✅',
            'paid'     => '💸',
            'rejected' => '❌',
            default    => 'ℹ️',
        };

        $text = "{$statusEmoji} <b>Payout Update</b>\n\n"
              . "Reference: <code>{$payout->reference_number}</code>\n"
              . "Amount: ₦" . number_format($payout->amount, 2) . "\n"
              . "Status: <b>{$payout->status_label}</b>";

        if ($payout->rejection_reason) {
            $text .= "\nReason: {$payout->rejection_reason}";
        }

        $this->sendMessage($chatId, $text);
    }
}
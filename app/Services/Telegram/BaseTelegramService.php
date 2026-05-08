<?php

namespace App\Services\Telegram;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Base Telegram API wrapper.
 *
 * Both SellerTelegramService and AdminTelegramService extend this class.
 * The token is injected by the child constructor so each bot uses its
 * own credentials while sharing all the low-level API methods.
 *
 * All public methods are intentionally void / return-typed so callers
 * never depend on Telegram's raw response structure.
 */
abstract class BaseTelegramService
{
    protected string $token;
    protected string $apiBase;

    // ── Core API methods ──────────────────────────────────────────────────────

    /**
     * Send a plain HTML text message.
     */
    public function sendMessage(string $chatId, string $text, array $replyMarkup = []): void
    {
        $payload = [
            'chat_id'                  => $chatId,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if (! empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $this->post('sendMessage', $payload);
    }

    /**
     * Edit an existing message's text.
     */
    public function editMessageText(string $chatId, int $messageId, string $text, array $replyMarkup = []): void
    {
        $payload = [
            'chat_id'                  => $chatId,
            'message_id'               => $messageId,
            'text'                     => $text,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if (! empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $this->post('editMessageText', $payload);
    }

    /**
     * Answer an inline keyboard callback so the loading spinner stops.
     */
    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): void
    {
        $this->post('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ]);
    }

    /**
     * Send a photo with optional caption and buttons.
     */
    public function sendPhoto(string $chatId, string $photoUrl, string $caption = '', array $replyMarkup = []): void
    {
        $payload = [
            'chat_id'    => $chatId,
            'photo'      => $photoUrl,
            'caption'    => $caption,
            'parse_mode' => 'HTML',
        ];

        if (! empty($replyMarkup)) {
            $payload['reply_markup'] = json_encode($replyMarkup);
        }

        $this->post('sendPhoto', $payload);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Build a standard "noop" inline keyboard button.
     * Useful as a placeholder or disabled state.
     */
    protected function noopButton(string $label): array
    {
        return ['text' => $label, 'callback_data' => 'noop'];
    }

    /**
     * Build a URL button that opens the web app.
     */
    protected function urlButton(string $label, string $url): array
    {
        return ['text' => $label, 'url' => $url];
    }

    /**
     * Format a Nigerian naira amount with commas.
     */
    protected function naira(float $amount): string
    {
        return '₦' . number_format($amount, 2);
    }

    /**
     * Truncate text to $max chars and append ellipsis if needed.
     * Prevents Telegram's 4096-char message limit from being hit.
     */
    protected function truncate(string $text, int $max = 200): string
    {
        return mb_strlen($text) > $max
            ? mb_substr($text, 0, $max - 3) . '…'
            : $text;
    }

    /**
     * Escape special HTML characters for Telegram's HTML parse mode.
     * Only escapes the three characters Telegram cares about.
     */
    protected function e(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    // ── Low-level HTTP ────────────────────────────────────────────────────────

    protected function post(string $method, array $payload): ?array
    {
        try {
            $response = Http::timeout(8)->post("{$this->apiBase}/{$method}", $payload);

            if (! $response->successful()) {
                Log::warning("Telegram {$method} failed", [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'chat_id' => $payload['chat_id'] ?? null,
                ]);
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error("Telegram {$method} exception", [
                'error'   => $e->getMessage(),
                'chat_id' => $payload['chat_id'] ?? null,
            ]);

            return null;
        }
    }
}
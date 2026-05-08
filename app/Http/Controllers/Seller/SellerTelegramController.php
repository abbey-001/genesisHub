<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Services\Telegram\SellerTelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Handles Telegram account linking for sellers.
 *
 * Mirrors the rider Telegram flow exactly:
 *   1. Seller clicks "Connect Telegram" in Settings
 *   2. JS calls POST /seller/settings/telegram/link → gets a deep-link URL
 *   3. Seller taps the link, opens the seller bot, sends /start <token>
 *   4. SellerTelegramWebhookController::linkAccount() verifies the token
 *      and stores the chat_id on the seller record
 *   5. Seller clicks "Disconnect" → DELETE /seller/settings/telegram/unlink
 *
 * Routes (add to routes/seller.php inside the middleware group):
 *
 *   Route::post('/settings/telegram/link',   [SellerTelegramController::class, 'generateLink'])
 *       ->name('seller.settings.telegram.link');
 *   Route::delete('/settings/telegram/unlink', [SellerTelegramController::class, 'unlink'])
 *       ->name('seller.settings.telegram.unlink');
 */
class SellerTelegramController extends Controller
{
    public function __construct(
        protected SellerTelegramService $telegram
    ) {}

    /**
     * Generate a one-time deep-link for the seller to connect their Telegram.
     *
     * Returns JSON so it can be called via fetch() from the settings page JS,
     * identical to the rider implementation in the provided blade templates.
     */
    public function generateLink(Request $request): JsonResponse
    {
        $seller = Auth::guard('seller')->user()->seller;

        if ($seller->telegram_chat_id) {
            return response()->json([
                'message' => 'Telegram is already connected.',
            ], 409);
        }

        // Generate a cryptographically secure token — expires in 15 minutes
        $token = Str::random(48);

        $seller->update([
            'telegram_link_token' => $token,
        ]);

        // Build the deep-link: https://t.me/BotUsername?start=TOKEN
        $botUsername = config('services.telegram.seller_bot_username');
        $link        = "https://t.me/{$botUsername}?start={$token}";

        return response()->json([
            'link'       => $link,
            'expires_in' => 900, // 15 minutes in seconds
        ]);
    }

    /**
     * Unlink the seller's Telegram account.
     */
    public function unlink(Request $request): \Illuminate\Http\RedirectResponse
    {
        $seller = Auth::guard('seller')->user()->seller;

        $seller->update([
            'telegram_chat_id'    => null,
            'telegram_link_token' => null,
            'telegram_linked_at'  => null,
        ]);

        return back()->with('success', 'Telegram account disconnected.');
    }
}
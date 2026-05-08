<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Services\Telegram\AdminTelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Admin Telegram registration (manual — no self-service linking).
 *
 * Super-admins register any admin's Telegram chat_id directly from
 * the admin panel. The admin being registered must:
 *   1. Open the admin bot on Telegram
 *   2. Send /start — the bot replies with their unique chat ID
 *   3. They give that ID to the super-admin, who enters it here
 *
 * Routes (add to routes/admin.php inside the admin middleware group):
 *
 *   Route::prefix('settings/telegram')->name('admin.telegram.')->group(function () {
 *       Route::get('/',                     [AdminTelegramController::class, 'index'])->name('index');
 *       Route::post('/register/{admin}',    [AdminTelegramController::class, 'register'])->name('register');
 *       Route::delete('/unregister/{admin}',[AdminTelegramController::class, 'unregister'])->name('unregister');
 *       Route::post('/test/{admin}',        [AdminTelegramController::class, 'sendTest'])->name('test');
 *       Route::put('/preferences/{admin}',  [AdminTelegramController::class, 'updatePreferences'])->name('preferences');
 *   });
 */
class AdminTelegramController extends Controller
{
    public function __construct(
        protected AdminTelegramService $telegram
    ) {}

    /**
     * List all admins with their Telegram status.
     * Only super-admins can access this page.
     */
    public function index()
    {
        $this->authorizeSuper();

        $admins = Admin::with('role')->get();

        return view('admin.settings.telegram.index', compact('admins'));
    }

    /**
     * Register a Telegram chat ID for an admin.
     */
    public function register(Request $request, Admin $admin): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeSuper();

        $request->validate([
            'telegram_chat_id' => 'required|string|max:50',
        ]);

        // Ensure no other admin is using this chat ID
        $conflict = Admin::where('telegram_chat_id', $request->telegram_chat_id)
            ->where('id', '!=', $admin->id)
            ->first();

        if ($conflict) {
            return back()->with('error', "That chat ID is already registered to admin: {$conflict->name}");
        }

        $admin->update([
            'telegram_chat_id'  => $request->telegram_chat_id,
            'telegram_linked_at'=> now(),
        ]);

        // Send a confirmation message to the newly registered admin
        $this->telegram->sendMessage(
            $request->telegram_chat_id,
            "✅ <b>Telegram notifications enabled!</b>\n\n"
            . "Hello <b>{$admin->name}</b>!\n"
            . "You'll now receive platform alerts on this bot.\n\n"
            . "Type /help for available commands."
        );

        return back()->with('success', "Telegram registered for {$admin->name}.");
    }

    /**
     * Remove a Telegram chat ID from an admin.
     */
    public function unregister(Admin $admin): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeSuper();

        $admin->update([
            'telegram_chat_id'  => null,
            'telegram_linked_at'=> null,
        ]);

        return back()->with('success', "Telegram unregistered for {$admin->name}.");
    }

    /**
     * Send a test message to an admin's Telegram.
     */
    public function sendTest(Admin $admin): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeSuper();

        if (! $admin->telegram_chat_id) {
            return back()->with('error', 'This admin has no Telegram account registered.');
        }

        $this->telegram->sendMessage(
            $admin->telegram_chat_id,
            "🔔 <b>Test Notification</b>\n\n"
            . "Hello {$admin->name}! Your Telegram notifications are working correctly.\n\n"
            . "Sent by: " . Auth::guard('admin')->user()->name
        );

        return back()->with('success', "Test message sent to {$admin->name}.");
    }

    /**
     * Update notification category preferences for an admin.
     */
    public function updatePreferences(Request $request, Admin $admin): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeSuper();

        $request->validate([
            'telegram_notify_orders'  => 'boolean',
            'telegram_notify_payouts' => 'boolean',
            'telegram_notify_sellers' => 'boolean',
            'telegram_notify_reviews' => 'boolean',
            'telegram_notify_system'  => 'boolean',
        ]);

        $admin->update([
            'telegram_notify_orders'  => $request->boolean('telegram_notify_orders'),
            'telegram_notify_payouts' => $request->boolean('telegram_notify_payouts'),
            'telegram_notify_sellers' => $request->boolean('telegram_notify_sellers'),
            'telegram_notify_reviews' => $request->boolean('telegram_notify_reviews'),
            'telegram_notify_system'  => $request->boolean('telegram_notify_system'),
        ]);

        return back()->with('success', "Notification preferences updated for {$admin->name}.");
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function authorizeSuper(): void
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin->isSuperAdmin()) {
            abort(403, 'Only super-admins can manage Telegram registrations.');
        }
    }
}
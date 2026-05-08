<?php

// routes/web.php - Complete Rider Routes

use App\Http\Controllers\Rider\DashboardController;
use App\Http\Controllers\Rider\DeliveryController;
use App\Http\Controllers\Rider\ProfileController;
use App\Http\Controllers\Rider\EarningsController;
use App\Http\Controllers\Rider\BroadcastController;

Route::middleware(['auth', 'rider', 'verified.rider'])->prefix('rider')->name('rider.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── Deliveries ────────────────────────────────────────────────
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/',                                   [DeliveryController::class, 'index'])->name('index');
        Route::get('/active',                             [DeliveryController::class, 'active'])->name('active');
        Route::get('/completed',                          [DeliveryController::class, 'completed'])->name('completed');
        Route::get('/failed',                             [DeliveryController::class, 'failed'])->name('failed');
        Route::get('/available/now',                      [DeliveryController::class, 'available'])->name('available');
        Route::get('/available/poll',                     [DeliveryController::class, 'availablePoll'])->name('available.poll');

        // Accept is keyed on a DeliveryBroadcast (handles both single + bundle)
        Route::post('/broadcast/{broadcast}/accept',      [DeliveryController::class, 'accept'])->name('accept');

        Route::get('/{delivery}',                         [DeliveryController::class, 'show'])->name('show');
        Route::post('/{delivery}/confirm-pickup',         [DeliveryController::class, 'confirmPickup'])->name('confirm-pickup');
        Route::post('/{delivery}/complete',               [DeliveryController::class, 'complete'])->name('complete');
        Route::post('/{delivery}/fail',                   [DeliveryController::class, 'fail'])->name('fail');
    });

    // ── Broadcasts (viewed from notification / broadcasts menu) ───
    Route::prefix('broadcasts')->name('broadcasts.')->group(function () {
        Route::get('/',                                   [BroadcastController::class, 'index'])->name('index');
        Route::get('/{broadcast}',                        [BroadcastController::class, 'show'])->name('show');
        Route::post('/{broadcast}/accept',                [DeliveryController::class, 'accept'])->name('accept');
        Route::post('/{broadcast}/reject',                [BroadcastController::class, 'reject'])->name('reject');
    });

    // ── Earnings ──────────────────────────────────────────────────
    Route::get('/earnings',                               [EarningsController::class, 'index'])->name('earnings.index');
    Route::get('/earnings/payout',                        [EarningsController::class, 'payoutForm'])->name('earnings.payout-form');
    Route::post('/earnings/payout',                       [EarningsController::class, 'requestPayout'])->name('earnings.request-payout');
    Route::get('/earnings/payout-history',                [EarningsController::class, 'payoutHistory'])->name('earnings.payout-history');
    Route::get('/earnings/payout/{payout}',               [EarningsController::class, 'showPayout'])->name('earnings.payout-show');

    // ── Profile ───────────────────────────────────────────────────
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',                                   [ProfileController::class, 'index'])->name('index');
        Route::put('/update',                             [ProfileController::class, 'update'])->name('update');
        Route::put('/bank',                               [ProfileController::class, 'updateBank'])->name('bank');
        Route::put('/password',                           [ProfileController::class, 'updatePassword'])->name('password');
    });

    // ── Notifications ─────────────────────────────────────────────
    Route::get('/notifications',                          [DashboardController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/read',               [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all',                [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
    
    Route::post('profile/telegram/link',   [ProfileController::class, 'generateTelegramLink'])->name('profile.telegram.link');
    Route::delete('profile/telegram/unlink', [ProfileController::class, 'unlinkTelegram'])->name('profile.telegram.unlink');
});
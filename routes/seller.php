<?php
// routes/seller.php - Seller Portal Routes

use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\OrderController;
use App\Http\Controllers\Seller\PayoutController;
use App\Http\Controllers\Seller\ShopController;
use App\Http\Controllers\Seller\ReviewController;
use App\Http\Controllers\Seller\SettingsController;
use App\Http\Controllers\Seller\SellerTelegramController;
use App\Models\Subcategory;
use App\Http\Controllers\Seller\CouponController;

// -----------------------------------------------------------------------
// All seller portal routes require:
//   auth           → user must be logged in
//   seller         → user must have user_type = 'seller'
//   seller.verified → seller's verification_status must be 'verified'
// -----------------------------------------------------------------------
Route::prefix('seller')->middleware(['seller', 'seller.verified'])->name('seller.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shop Management
    Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
    Route::put('/shop', [ShopController::class, 'update'])->name('shop.update');

    // Product Management
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/images',          [ProductController::class, 'uploadImages'])->name('products.images.upload');
    Route::delete('products/images/{image}',           [ProductController::class, 'deleteImage'])->name('products.images.delete');
    Route::post('products/{product}/set-primary-image', [ProductController::class, 'setPrimaryImage'])->name('products.set-primary');
    Route::get('brands/search', [ProductController::class, 'search'])->name('brands.search');
    Route::post('products/{product}/images/chunked', [ProductController::class, 'uploadImagesChunked'])->name('products.images.chunked');
 
// Fuzzy-check before creation (non-destructive)
Route::post('brands/check', [ProductController::class, 'check'])->name('brands.check');
 
// Create a new brand
Route::post('brands', [ProductController::class, 'storeBrand'])->name('brands.store');

    // Order Management
    Route::get('/orders',                        [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}',                [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}/status',         [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/invoice',        [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::post('/order-items/{item}/ready',     [OrderController::class, 'markItemReady'])->name('orders.items.ready');

    // Payouts
    Route::get('/payouts',                          [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/request',                 [PayoutController::class, 'request'])->name('payouts.request');
    Route::get('/payouts/transactions/history',     [PayoutController::class, 'transactions'])->name('payouts.transactions');
    Route::get('/payouts/transactions/export',      [PayoutController::class, 'exportTransactions'])->name('payouts.transactions.export');
    Route::get('/payouts/settings/pay',             [PayoutController::class, 'settings'])->name('payouts.settings');
    Route::post('/payouts/settings',                [PayoutController::class, 'updateSettings'])->name('payouts.settings.update');
    Route::get('/payouts/{payout}',                 [PayoutController::class, 'show'])->name('payouts.show');
    Route::delete('/payouts/{payout}/cancel',       [PayoutController::class, 'cancel'])->name('payouts.cancel');

    Route::post('/account/sessions/revoke', [SellerAccountSecurityController::class, 'revokeOtherSessions'])
        ->name('sessions.revoke');

    // ── Security: deactivate account ─────────────────────────────────────────
    Route::post('/account/deactivate', [SellerAccountSecurityController::class, 'deactivate'])
        ->name('account.deactivate');

    // Reviews Management
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/',                   [ReviewController::class, 'index'])->name('index');
        Route::get('/analytics',          [ReviewController::class, 'analytics'])->name('analytics');
        Route::post('/{review}/respond',  [ReviewController::class, 'respond'])->name('respond');
        Route::put('/{review}/response',  [ReviewController::class, 'updateResponse'])->name('response.update');
        Route::delete('/{review}/response', [ReviewController::class, 'deleteResponse'])->name('response.delete');
    });
    
    Route::prefix('coupons')->name('coupons.')->group(function () {
    Route::get('/',                  [CouponController::class, 'index'])->name('index');
    Route::get('/create',            [CouponController::class, 'create'])->name('create');
    Route::post('/',                 [CouponController::class, 'store'])->name('store');
    Route::get('/{coupon}/edit',     [CouponController::class, 'edit'])->name('edit');
    Route::put('/{coupon}',          [CouponController::class, 'update'])->name('update');
    Route::delete('/{coupon}',       [CouponController::class, 'destroy'])->name('destroy');
    Route::post('/{coupon}/toggle',  [CouponController::class, 'toggleStatus'])->name('toggle');
});

    // Settings
    Route::get('/settings',              [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings/profile',      [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/bank',         [SettingsController::class, 'updateBank'])->name('settings.bank');
    Route::put('/settings/password',     [SettingsController::class, 'updatePassword'])->name('settings.password');

    // Internal API — subcategories by category
    Route::get('/categories/{category}/subcategories', function ($categoryId) {
        return Subcategory::where('category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'name']);
    });
    
     Route::post('/settings/telegram/link',   [SellerTelegramController::class, 'generateLink'])
        ->name('settings.telegram.link');
    Route::delete('/settings/telegram/unlink', [SellerTelegramController::class, 'unlink'])
       ->name('settings.telegram.unlink');
});
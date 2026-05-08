<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Api\TrackingController;
use App\Http\Controllers\Api\CronController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\AccountSecurityController;
use App\Http\Controllers\NewsletterController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home
Route::get('/', [HomeController::class, 'index'])->middleware('redirect.by.type')->name('home');

Route::get('/search/suggest', [SearchController::class, 'suggest'])
    ->name('api.search.suggest')
    ->middleware('throttle:120,1');

Route::get('/search', [SearchController::class, 'index'])
    ->name('search.index');

// Product Routes
Route::prefix('products')->name('product.')->group(function () {
    // Main listing page - supports all filters
    Route::get('/', [ProductController::class, 'index'])->name('index');
    
    // AJAX filter endpoint (no page reload)
    Route::get('/filter', [ProductController::class, 'filterProducts'])->name('filter');
    
    // Product detail
    Route::get('/{id}', [ProductController::class, 'show'])->name('show');
    
    // Quick view (AJAX)
    Route::get('/{id}/quick-view', [ProductController::class, 'quickView'])->name('quick-view');
    
    // Track view (AJAX)
    Route::post('/track-view', [ProductController::class, 'trackView'])->name('track-view');
});

// Category Routes
Route::prefix('category')->name('category.')->group(function () {
    // All categories
    Route::get('/', [CategoryController::class, 'index'])->name('index');
    
    // Category products
    Route::get('/{slug}', [ProductController::class, 'category'])->name('show');
});

// Brand Routes
Route::prefix('brand')->name('brand.')->group(function () {
    // All brands
    Route::get('/', [BrandController::class, 'index'])->name('index');
    
    // Brand products
    Route::get('/{slug}', [ProductController::class, 'brand'])->name('show');
});

// Cart Routes
Route::prefix('cart')->name('cart.')->group(function () {
    // Cart page
    Route::get('/', [CartController::class, 'index'])->name('index');
    
    // Cart actions
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    
    // Cart data (AJAX)
    Route::get('/sidebar', [CartController::class, 'sidebar'])->name('sidebar');
    Route::get('/count', [CartController::class, 'count'])->name('count');
    
    // Coupon
    Route::post('/apply-coupon', [CartController::class, 'applyCoupon'])->name('apply-coupon');
    Route::post('/remove-coupon', [CartController::class, 'removeCoupon'])->name('remove-coupon');

    Route::middleware('auth')->group(function () {
        Route::post('/update-address', [CartController::class, 'updateAddress'])->name('update-address');
        Route::get('/selected-address', [CartController::class, 'getSelectedAddress'])->name('selected-address');
    });
});

// Wishlist Routes (Protected)
Route::middleware(['auth'])->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add', [WishlistController::class, 'add'])->name('add');
    Route::post('/remove', [WishlistController::class, 'remove'])->name('remove');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
});

// Compare Routes
Route::prefix('compare')->name('compare.')->group(function () {
    Route::get('/', [ProductController::class, 'compare'])->name('index');
    Route::post('/add', [ProductController::class, 'addToCompare'])->name('add');
    Route::post('/remove', [ProductController::class, 'removeFromCompare'])->name('remove');
});

// Checkout Routes (Protected)
Route::middleware(['auth', 'verified'])->prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/success/{orderId}', [CheckoutController::class, 'success'])->name('success');
});

// Add these routes to routes/web.php



// ============================================
// CUSTOMER ORDER TRACKING ROUTES
// ============================================

// Public tracking (no auth required)
Route::prefix('track')->name('track.')->group(function () {
    
    // Show tracking form
    Route::get('/', [OrderTrackingController::class, 'index'])->name('index');
    
    // Track order by order number
    Route::post('/order', [OrderTrackingController::class, 'trackOrder'])->name('order');
    
    // Track order by order number (GET - for direct links)
    Route::get('/order/{orderNumber}', [OrderTrackingController::class, 'showOrder'])->name('show');
    
    // Verify OTP and show full tracking
    Route::post('/verify', [OrderTrackingController::class, 'verifyOTP'])->name('verify');
    
    // Live tracking data (AJAX)
    Route::get('/live/{orderId}', [OrderTrackingController::class, 'getLiveData'])->name('live-data');
});

// Authenticated customer routes
Route::middleware(['auth', 'verified'])->group(function () {
    // My orders
    Route::get('/my-orders', [OrderTrackingController::class, 'myOrders'])->name('orders.my');
    
    // Single order tracking (authenticated)
    Route::get('/orders/{order}/track', [OrderTrackingController::class, 'trackMyOrder'])->name('orders.track');
});


// Update your web.php routes to this structure:

Route::middleware(['auth', 'verified', 'redirect.by.type'])->prefix('account')->name('account.')->group(function () {
    // Main account page
    Route::get('/', [AccountController::class, 'index'])->name('index');
    Route::get('/orders/{id}', [AccountController::class, 'showOrder'])->name('orders.show');
    
    // API endpoints - note the change in structure
    Route::prefix('api')->name('api.')->group(function () {
        // Profile
        Route::get('/profile', [AccountController::class, 'getProfile'])->name('profile');
        Route::post('/profile/update', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::post('/password/update', [AccountController::class, 'updatePassword'])->name('password.update');
        
        // Orders
        Route::get('/orders', [AccountController::class, 'getOrders'])->name('orders');
        Route::post('/orders/{id}/cancel', [PaymentController::class, 'cancelOrder'])->name('orders.cancel');
        Route::get('/orders/{id}/track', [AccountController::class, 'trackOrder'])->name('orders.track');
        
        // Reviews
        Route::get('/reviews/reviewable', [AccountController::class, 'getReviewableItems'])->name('reviews.reviewable');
        Route::get('/reviews', [AccountController::class, 'getReviews'])->name('reviews');
        Route::post('/reviews/submit', [AccountController::class, 'submitReview'])->name('reviews.submit');
        
        // Addresses
        Route::get('/addresses', [AccountController::class, 'getAddresses'])->name('addresses');
        Route::post('/addresses/store', [AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [AccountController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [AccountController::class, 'deleteAddress'])->name('addresses.delete');
        Route::post('/addresses/{address}/set-default', [AccountController::class, 'setDefaultAddress'])->name('addresses.default');
    });
});

// Payment Routes
Route::prefix('payment')->name('payment.')->middleware('auth')->group(function () {
    // Initialize payment
    Route::post('/initialize', [PaymentController::class, 'initialize'])->name('initialize');
    
    // Callbacks (GET — browser redirect)
Route::get('/paystack/callback',     [PaymentController::class, 'paystackCallback'])->name('paystack.callback');
Route::get('/flutterwave/callback',  [PaymentController::class, 'flutterwaveCallback'])->name('flutterwave.callback');

// Webhooks (POST — exclude from CSRF)
Route::post('/paystack/webhook',     [PaymentController::class, 'paystackWebhook'])->name('paystack.webhook');
Route::post('/flutterwave/webhook',  [PaymentController::class, 'flutterwaveWebhook'])->name('flutterwave.webhook');
    
    // Payment callback (after Paystack redirect)
    // Route::get('/callback', [PaymentController::class, 'callback'])->name('callback');
    
    // Success and failure pages
    Route::get('/success/{order}', [PaymentController::class, 'success'])->name('success');
    Route::get('/failed', [PaymentController::class, 'failed'])->name('failed');
});

// Webhook route (no auth middleware - Paystack will call this)
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');

// Contact
Route::get('/contact', function () {
    return view('contact');
})->name('contact');

// Static Pages
Route::get('/about', function () {
    return view('pages.about');
})->name('about');

Route::get('/become-vendor', function () {
    return view('seller.become-vendor');
})->name('become-vendor');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

Route::get('/privacy', function () {
    return view('pages.privacy');
})->name('privacy');

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

// Legacy routes (redirects)
Route::get('/shop', function () {
    return redirect()->route('product.index');
});

Route::get('/categories', function () {
    return redirect()->route('category.index');
})->name('categories.index');

Route::get('/brands', function () {
    return redirect()->route('brand.index');
})->name('brands.index');

Route::get('/data-deletion', fn() => view('pages.data-deletion'))->name('data-deletion');

Route::view('/about', 'pages.about')->name('about');
Route::view('/contact', 'pages.contact')->name('contact');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');
Route::view('/returns', 'pages.returns')->name('returns');
Route::view('/help', 'pages.help')->name('help');
Route::view('/stores', 'pages.stores')->name('stores');
Route::view('/guides', 'pages.guides')->name('guides');
Route::view('/financing', 'pages.financing')->name('financing');
Route::view('/gift-card', 'pages.gift-card')->name('gift-card');
Route::get('/sitemap.xml', function () {
    return response()->file(public_path('sitemap.xml'));
});


/*
|--------------------------------------------------------------------------
| Newsletter & Language/Currency
|--------------------------------------------------------------------------
*/
Route::post('/currency/change', [HomeController::class, 'changeCurrency'])->name('currency.change');
Route::post('/language/change', [HomeController::class, 'changeLanguage'])->name('language.change');
Route::post('/newsletter/subscribe',   [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

Route::middleware('auth')->group(function () {
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    });

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/track', [OrderController::class, 'track'])->name('track');
    });
});


/*
 API ROUTES
*/
Route::middleware('api')->prefix('api')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        
        // Tracking endpoints
        Route::get('/deliveries/{delivery}/tracking', [TrackingController::class, 'getDeliveryTracking']);
        Route::get('/orders/{orderNumber}/track', [TrackingController::class, 'trackOrder']);
        
        // Rider endpoints
        Route::post('/rider/location', [TrackingController::class, 'updateRiderLocation']);
        Route::get('/rider/stats', [TrackingController::class, 'getRiderStats']);
        Route::get('/rider/available-deliveries', [TrackingController::class, 'getAvailableDeliveries']);
    });


    // Add these routes to routes/web.php (after Product Routes section, around line 50)

// Shop/Seller Store Routes
Route::prefix('shops')->name('shop.')->group(function () {
    // All shops listing
    Route::get('/', [App\Http\Controllers\HomeController::class, 'indexsellers'])->name('index');
    
    // Individual shop page
    Route::get('/{slugOrId}', [App\Http\Controllers\ShopController::class, 'show'])->name('show');
    
    // Shop reviews page
    Route::get('/{slugOrId}/reviews', [App\Http\Controllers\ShopController::class, 'reviews'])->name('reviews');
});

// ============================================
// CUSTOMER REVIEW ROUTES
// ============================================

// Customer Review Routes
Route::middleware(['auth', 'verified'])->prefix('reviews')->name('reviews.')->group(function () {
    // My Reviews
    Route::get('/my-reviews', [App\Http\Controllers\ReviewController::class, 'myReviews'])->name('my');
    
    // Submit Review (with rate limiting)
    Route::post('/submit', [App\Http\Controllers\ReviewController::class, 'store'])
        ->name('submit')
        ->middleware('throttle:5,60'); // 5 submissions per hour
    
    // Check Eligibility
    Route::get('/check-eligibility/{product}', [App\Http\Controllers\ReviewController::class, 'checkEligibility'])
        ->name('check');
    
    // Update Review (edit pending reviews)
    Route::put('/{review}', [App\Http\Controllers\ReviewController::class, 'update'])->name('update');
    
    // Delete Review (delete pending reviews)
    Route::delete('/{review}', [App\Http\Controllers\ReviewController::class, 'destroy'])->name('destroy');
    
    // Mark as Helpful
    Route::post('/{review}/helpful', [App\Http\Controllers\ReviewController::class, 'markHelpful'])->name('helpful');
});

// Alternative route naming (for backward compatibility)
Route::get('/seller/{slugOrId}', [App\Http\Controllers\ShopController::class, 'show'])->name('seller.shop');
});



// ── Account security — customers ─────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Security overview page (sessions + login log + deactivation + email change)
    Route::get('/account/security', [AccountSecurityController::class, 'index'])
        ->name('account.security');

    // Revoke all other active sessions
    Route::post('/account/security/sessions/revoke', [AccountSecurityController::class, 'revokeOtherSessions'])
        ->name('account.sessions.revoke');

    // Request email change (sends confirmation to new email)
    Route::post('/account/security/email/change', [AccountSecurityController::class, 'requestEmailChange'])
        ->name('account.email.change');

    // Cancel pending email change
    Route::delete('/account/security/email/change', [AccountSecurityController::class, 'cancelEmailChange'])
        ->name('account.email.cancel');

    // Deactivate account
    Route::post('/account/security/deactivate', [AccountSecurityController::class, 'deactivate'])
        ->name('account.deactivate');
});

// Email change confirmation — no 'verified' middleware (user may have new email)
// No auth middleware either — the token IS the authentication
Route::get('/account/email/confirm/{token}', [AccountSecurityController::class, 'confirmEmailChange'])
    ->name('account.email.confirm');

// Reactivate account — user is logged in (deactivated users CAN log in within window)
Route::post('/account/reactivate', [AccountSecurityController::class, 'reactivate'])
    ->middleware('auth')
    ->name('account.reactivate');

// routes/web.php
Route::get('/run-schedule', function () {
    if (request('token') !== config('app.cron_token')) {
        abort(403);
    }
    Artisan::call('schedule:run');
    return 'OK';
})->middleware('throttle:1,1');

Route::get('/debug-token', function () {
    return [
        'from_env' => env('CRON_TOKEN'),
        'from_config' => config('app.cron_token'),
    ];
});


Route::get('/bank/resolve', [PaymentController::class, 'resolve'])->name('bank.resolve');
Route::get('/bank/list',    [PaymentController::class, 'list'])->name('bank.list');

    
// routes/web.php
Route::post('/telegram/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle']); 
Route::post('/telegram/seller/webhook',[\App\Http\Controllers\SellerTelegramWebhookController::class, 'handle'])->name('telegram.seller.webhook');
Route::post('/telegram/admin/webhook',[\App\Http\Controllers\AdminTelegramWebhookController::class, 'handle'])->name('telegram.admin.webhook'); 

require __DIR__ . '/auth.php';
require __DIR__ . '/seller.php';
require __DIR__ . '/rider.php';
require __DIR__ . '/admin.php';

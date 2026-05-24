<?php

use App\Http\Controllers\Auth\SellerLoginController;
use App\Http\Controllers\Auth\SellerRegisterController;
use App\Http\Controllers\Auth\SellerEmailVerificationController;
use App\Http\Controllers\Auth\RiderLoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\SellerSocialCompleteController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Seller\SellerAccountSecurityController;


// ── Seller password reset ─────────────────────────────────────────────────────
Route::middleware('guest:seller')->group(function () {
    Route::get('/seller/forgot-password', [PasswordResetController::class, 'sellerForgotForm'])
        ->name('seller.password.request');

    Route::post('/seller/forgot-password', [PasswordResetController::class, 'sellerSendLink'])
        ->name('seller.password.email');

    Route::get('/seller/reset-password/{token}', [PasswordResetController::class, 'sellerResetForm'])
        ->name('seller.password.reset');

    Route::post('/seller/reset-password', [PasswordResetController::class, 'sellerReset'])
        ->name('seller.password.update');
});

// ── Rider password reset ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/rider/forgot-password', [PasswordResetController::class, 'riderForgotForm'])
        ->name('rider.password.request');

    Route::post('/rider/forgot-password', [PasswordResetController::class, 'riderSendLink'])
        ->name('rider.password.email');

    Route::get('/rider/reset-password/{token}', [PasswordResetController::class, 'riderResetForm'])
        ->name('rider.password.reset');

    Route::post('/rider/reset-password', [PasswordResetController::class, 'riderReset'])
        ->name('rider.password.update');
});


Route::post('/forgot-password', function (\Illuminate\Http\Request $request) {
    $request->validate(['email' => ['required', 'email']]);

    $user = \App\Models\User::where('email', $request->email)
                            ->where(function ($query) {
                                $query->where('user_type', 'customer')
                                    ->orWhereNull('user_type');
                            })
                            ->first();

    // Social-only — no password was ever set
    if (! $user) {
        return back()->with('status', __('passwords.sent'));
    }

    if (is_null($user->password)) {
        return back()->with('social_only', true);
    }

    // Hand off to Fortify's default broker (sends email or silently fails)
    $status = \Illuminate\Support\Facades\Password::broker()->sendResetLink(
        ['email' => $user->email]
    );

    return back()->with('status', __($status));
})->middleware('guest')->name('password.email');

// =============================================================================
// SELLER AUTH ROUTES
// =============================================================================

// ── Unauthenticated seller routes ─────────────────────────────────────────────
Route::middleware('guest:seller')->group(function () {
    Route::get('/seller/register', [SellerRegisterController::class, 'showForm'])
        ->name('seller.register.form');
    Route::post('/seller/register', [SellerRegisterController::class, 'register'])
        ->name('seller.register');

    Route::get('/seller/login', [SellerLoginController::class, 'showForm'])
        ->name('seller.login.form');
    Route::post('/seller/login', [SellerLoginController::class, 'login'])
        ->name('seller.login');
});

// ── Email verification link — NO seller middleware ────────────────────────────
// This route must be publicly reachable because the seller clicks it from their
// mail client, potentially in a fresh browser with no active session.
// The 'signed' middleware is the only authentication needed — it validates the
// tamper-proof signed URL. The controller logs the seller in after verifying.
Route::get('/seller/verify-email/{id}/{hash}', [SellerEmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('seller.verification.verify');

// ── Authenticated seller routes (logged in, but email/approval not required) ──
Route::middleware('seller')->group(function () {
    Route::post('/seller/logout', [SellerLoginController::class, 'logout'])
        ->name('seller.logout');

    // "Please check your email" / "pending approval" holding page
    Route::get('/seller/verify-email', [SellerEmailVerificationController::class, 'notice'])
        ->name('seller.verification.notice');

    // Resend verification email
    Route::post('/seller/verify-email/resend', [SellerEmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('seller.verification.resend');
        
            Route::post('/seller/account/reactivate', [SellerAccountSecurityController::class, 'reactivate'])
        ->name('seller.account.reactivate');
});

// ── Protected seller routes (email-verified AND admin-approved) ───────────────
Route::middleware(['seller', 'seller.verified'])->group(function () {
    // Route::get('/seller/dashboard', [SellerDashboardController::class, 'index'])
    //     ->name('seller.dashboard');
    //
    // All other seller routes go here...
});


Route::middleware('guest')->group(function () {
    Route::get('/rider/login', [RiderLoginController::class, 'showRiderLoginForm'])
        ->name('rider.login.form');
    Route::post('/rider/login', [RiderLoginController::class, 'login'])
        ->name('rider.login');
});

Route::middleware('auth')->group(function () {
    Route::post('/rider/logout', [RiderLoginController::class, 'logout'])
        ->name('rider.logout');
});


// ── Social OAuth routes (Google + Facebook) ───────────────────────────────
// ?type=customer  → customer flow
// ?type=seller    → seller flow
Route::get('/auth/{provider}/redirect', [SocialLoginController::class, 'redirect'])
    ->name('social.redirect');

Route::get('/auth/{provider}/callback', [SocialLoginController::class, 'callback'])
    ->name('social.callback');

// ── Seller social registration completion form ────────────────────────────
// Shown after a new seller authenticates via Google/Facebook.
// Protected only by the session key 'social_seller_pending_id' —
// NOT behind the seller guard because they aren't logged in yet.
Route::get('/seller/register/complete', [SellerSocialCompleteController::class, 'showForm'])
    ->name('seller.social.complete.form');

Route::post('/seller/register/complete', [SellerSocialCompleteController::class, 'complete'])
    ->name('seller.social.complete');
    
    
    

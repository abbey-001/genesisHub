<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate 2 — Is this seller both email-verified AND admin-approved?
 *
 * Applied AFTER the 'seller' middleware, so auth()->guard('seller')->user()
 * is always available here.
 *
 * Two-stage check:
 *   1. Email verified   → if not, redirect to verification notice (stay logged in)
 *   2. Admin approved   → if not, redirect to notice with pending/rejected status
 *
 * Register alias:
 *   Laravel 11 → $middleware->alias(['seller.verified' => SellerVerified::class])
 *   Laravel 10 → $routeMiddleware['seller.verified'] = SellerVerified::class
 *
 * Usage:
 *   Route::middleware(['seller', 'seller.verified'])->group(function () {
 *       Route::get('/seller/dashboard', ...)->name('seller.dashboard');
 *   });
 */
class SellerVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->guard('seller')->user();
        

        // ── Stage 1: Email verification ───────────────────────────────────────
        if (!$user->hasVerifiedEmail()) {
           
            return redirect()->route('seller.verification.notice');
        }

        // ── Stage 2: Admin approval ───────────────────────────────────────────
        if (!$user->seller) {
            // Seller user_type set but no seller profile — shouldn't happen,
            // but handle gracefully rather than crashing.
            auth()->guard('seller')->logout();
            $request->session()->regenerateToken();

            return redirect()->route('seller.login.form')
                ->with('error', 'Seller profile not found. Please contact support.');
        }
        
        $status = $user->seller->verification_status;
      
        if (!$user->seller->is_verified) {
           auth()->guard('seller')->logout();
            $request->session()->regenerateToken();

            return redirect()->route('seller.login.form')->with('verification_pending', $user->seller->verification_status);
        }

        return $next($request);
    }
}

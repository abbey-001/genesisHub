<?php

namespace App\Actions\Fortify;

use App\Services\LoginActivityService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Fortify pipeline step — runs after AttemptToAuthenticate.
 *
 * 1. Blocks non-customers from using the customer login form.
 * 2. Blocks deactivated customers — reactivation window users get redirected
 *    to a reactivation confirmation page; expired window gets hard-blocked.
 * 3. Records login activity for the login log.
 * 4. Handles unverified customers: keeps them logged in and redirects
 *    to the verification notice page (which is behind auth middleware).
 */
class EnsureUserIsCustomer
{
    public function handle(Request $request, $next)
    {
        $user = auth()->user();

        // ── 1. Wrong user type ────────────────────────────────────────────────
        if ($user && $user->user_type !== 'customer') {
            auth()->logout();
            LoginActivityService::record($request, $user, $user->user_type, false, 'wrong_guard');

            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        // ── 2. Deactivated account ────────────────────────────────────────────
        if ($user && $user->isDeactivated()) {
            if ($user->canReactivate()) {
                // Within 30-day window — let them in to reactivate
                $request->session()->regenerate();
                LoginActivityService::record($request, $user, 'customer', true);
                return redirect()->route('account.reactivate');
            }

            // Window expired — block entirely
            auth()->logout();
            LoginActivityService::record($request, $user, 'customer', false, 'account_deactivated');

            throw ValidationException::withMessages([
                'email' => ['This account has been permanently deactivated. Please contact support@genesishub.ng.'],
            ]);
        }

        // ── 3. Unverified customer ────────────────────────────────────────────
        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
            $request->session()->regenerate();
            LoginActivityService::record($request, $user, 'customer', true);

            return redirect()->route('verification.notice')
                ->with('status', 'verification-link-sent');
        }

        // ── 4. Successful verified login ──────────────────────────────────────
        LoginActivityService::record($request, $user, 'customer', true);

        return $next($request);
    }
}
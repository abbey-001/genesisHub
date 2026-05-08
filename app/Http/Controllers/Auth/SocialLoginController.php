<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

/**
 * Handles Google and Facebook OAuth for both customers and sellers.
 *
 * Flow:
 *   redirect()  → sends user to provider
 *   callback()  → handles return from provider
 *
 * The ?type= query param (customer|seller) is stored in the session
 * during redirect so it survives the OAuth round-trip.
 *
 * Customer flow:
 *   find/create/merge → log in on web guard → redirect to home
 *
 * Seller flow (new account):
 *   create partial User (no Seller/Shop yet) → store user_id in session
 *   → redirect to seller social completion form
 *
 * Seller flow (existing account):
 *   find/merge → log in on seller guard → redirect to dashboard
 */
class SocialLoginController extends Controller
{
    private const ALLOWED_PROVIDERS = ['google', 'facebook'];

    // ─────────────────────────────────────────────────────────────────────────
    // REDIRECT
    // ─────────────────────────────────────────────────────────────────────────

    public function redirect(Request $request, string $provider)
    {
        $this->abortIfInvalidProvider($provider);

        $type = $request->query('type', 'customer');

        if (! in_array($type, ['customer', 'seller'])) {
            $type = 'customer';
        }

        // Store type in session so callback knows which flow to run
        session(['social_login_type' => $type]);

        Log::debug('[SocialLogin] Redirecting to provider.', [
            'provider' => $provider,
            'type'     => $type,
        ]);

        return Socialite::driver($provider)->redirect();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CALLBACK
    // ─────────────────────────────────────────────────────────────────────────

    public function callback(Request $request, string $provider)
    {
        $this->abortIfInvalidProvider($provider);

        $type = session('social_login_type', 'customer');

        Log::debug('[SocialLogin] Callback received.', [
            'provider' => $provider,
            'type'     => $type,
        ]);

        // ── Retrieve the social user from the provider ────────────────────────
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            Log::error('[SocialLogin] Failed to retrieve social user.', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);

            $loginRoute = $type === 'seller' ? 'seller.login.form' : 'login';

            return redirect()->route($loginRoute)
                ->withErrors(['email' => 'Social login failed. Please try again.']);
        }

        Log::debug('[SocialLogin] Social user retrieved.', [
            'provider'    => $provider,
            'provider_id' => $socialUser->getId(),
            'email'       => $socialUser->getEmail(),
            'name'        => $socialUser->getName(),
        ]);

        return $type === 'seller'
            ? $this->handleSeller($socialUser, $provider)
            : $this->handleCustomer($socialUser, $provider);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CUSTOMER HANDLER
    // ─────────────────────────────────────────────────────────────────────────

    private function handleCustomer($socialUser, string $provider)
    {
        $providerIdField = $provider . '_id';

        // 1. Find by provider ID (returning social user)
        $user = User::where($providerIdField, $socialUser->getId())
                    ->where('user_type', 'customer')
                    ->first();

        // 2. Find by email (existing email/password account — merge)
        if (! $user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())
                        ->where('user_type', 'customer')
                        ->first();

            if ($user) {
                Log::debug('[SocialLogin] Merging social ID into existing customer.', [
                    'user_id'  => $user->id,
                    'provider' => $provider,
                ]);
                $user->update([$providerIdField => $socialUser->getId()]);
            }
        }

        // 3. No match — create a new customer account
        if (! $user) {
            Log::debug('[SocialLogin] Creating new customer via social login.', [
                'provider' => $provider,
                'email'    => $socialUser->getEmail(),
            ]);

            $user = User::create([
                'name'              => $socialUser->getName() ?? 'User',
                'email'             => $socialUser->getEmail(),
                'password'          => null,           // no password for social accounts
                'user_type'         => 'customer',
                'email_verified_at' => now(),          // trusted — came from Google/Facebook
                $providerIdField    => $socialUser->getId(),
            ]);
        }

        // Ensure existing accounts get verified if they weren't already
        if (! $user->hasVerifiedEmail()) {
            $user->update(['email_verified_at' => now()]);
        }

        Auth::guard('web')->login($user);

        Log::debug('[SocialLogin] Customer logged in.', ['user_id' => $user->id]);

        return redirect()->intended('/');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SELLER HANDLER
    // ─────────────────────────────────────────────────────────────────────────

    private function handleSeller($socialUser, string $provider)
    {
        $providerIdField = $provider . '_id';

        // 1. Find by provider ID (returning social seller)
        $user = User::where($providerIdField, $socialUser->getId())
                    ->where('user_type', 'seller')
                    ->first();

        // 2. Find by email (existing seller account — merge)
        if (! $user && $socialUser->getEmail()) {
            $user = User::where('email', $socialUser->getEmail())
                        ->where('user_type', 'seller')
                        ->first();

            if ($user) {
                Log::debug('[SocialLogin] Merging social ID into existing seller.', [
                    'user_id'  => $user->id,
                    'provider' => $provider,
                ]);
                $user->update([$providerIdField => $socialUser->getId()]);
            }
        }

        // 3. Existing seller found — log them in and send to dashboard
        if ($user) {
            if (! $user->hasVerifiedEmail()) {
                $user->update(['email_verified_at' => now()]);
            }

            Auth::guard('seller')->login($user);

            Log::debug('[SocialLogin] Existing seller logged in.', ['user_id' => $user->id]);

            return redirect()->route('seller.dashboard');
        }

        // 4. No existing seller — create a partial User record and send to
        //    the completion form so they can fill in shop/business/bank details.
        Log::debug('[SocialLogin] New seller via social — creating partial user and redirecting to completion form.', [
            'provider' => $provider,
            'email'    => $socialUser->getEmail(),
        ]);

        $user = User::create([
            'name'              => $socialUser->getName() ?? 'Seller',
            'email'             => $socialUser->getEmail(),
            'password'          => null,
            'user_type'         => 'seller',
            'email_verified_at' => now(),
            $providerIdField    => $socialUser->getId(),
        ]);

        // Store the new user's ID in session so the completion controller
        // can retrieve it without requiring a full login yet.
        session(['social_seller_pending_id' => $user->id]);

        return redirect()->route('seller.social.complete.form');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function abortIfInvalidProvider(string $provider): void
    {
        if (! in_array($provider, self::ALLOWED_PROVIDERS)) {
            abort(404);
        }
    }
}
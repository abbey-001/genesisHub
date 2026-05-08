<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

/**
 * Handles password reset for Sellers and Riders.
 *
 * Customers use Fortify's built-in password reset pipeline
 * (FortifyServiceProvider already registers the views for them).
 *
 * Sellers and Riders are handled here because they sit outside
 * Fortify's guard and need their own views and redirect paths.
 *
 * Both use Laravel's built-in Password broker — we just pass
 * the correct broker name ('sellers' or 'users') so tokens are
 * scoped to the right user type.
 *
 * Social-only accounts (null password) are detected and shown
 * a friendly message instead of a reset link.
 */
class PasswordResetController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // SELLER — Forgot Password
    // ─────────────────────────────────────────────────────────────────────────

    public function sellerForgotForm()
    {
        return view('auth.seller-forgot-password');
    }

    public function sellerSendLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Check if the account exists and is a seller
        $user = User::where('email', $request->email)
                    ->where('user_type', 'seller')
                    ->first();

        if (! $user) {
            // Generic message — don't reveal whether email exists
            return back()->with('status', 'If a seller account with that email exists, we have sent a reset link.');
        }

        // Social-only account — no password was ever set
        if (is_null($user->password)) {
            return back()->with('social_only', true);
        }

        // Send the reset link using the default 'users' broker
        Password::broker()->sendResetLink(
            ['email' => $request->email, 'user_type' => 'seller']
        );

        return back()->with('status', 'If a seller account with that email exists, we have sent a reset link.');
    }

    public function sellerResetForm(Request $request, string $token)
    {
        return view('auth.seller-reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function sellerReset(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', 'min:8'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('seller.login.form')
                ->with('success', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RIDER — Forgot Password
    // ─────────────────────────────────────────────────────────────────────────

    public function riderForgotForm()
    {
        return view('auth.rider-forgot-password');
    }

    public function riderSendLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)
                    ->where('user_type', 'rider')
                    ->first();

        if (! $user) {
            return back()->with('status', 'If a rider account with that email exists, we have sent a reset link.');
        }

        // Social-only account check (future-proof — riders may get social login later)
        if (is_null($user->password)) {
            return back()->with('social_only', true);
        }

        Password::broker()->sendResetLink(
            ['email' => $request->email, 'user_type' => 'rider']
        );

        return back()->with('status', 'If a rider account with that email exists, we have sent a reset link.');
    }

    public function riderResetForm(Request $request, string $token)
    {
        return view('auth.rider-reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function riderReset(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', 'min:8'],
            'password_confirmation' => ['required'],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('rider.login.form')
                ->with('success', 'Your password has been reset. You can now sign in.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}
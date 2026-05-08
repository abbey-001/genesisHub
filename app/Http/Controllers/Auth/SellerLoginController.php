<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SellerLoginController extends Controller
{
public function showForm()
{
    // If already logged in as a seller, redirect to dashboard
    if (auth('seller')->check()) {
        return redirect()->route('seller.dashboard');
    }

    return view('auth.seller-login'); // your login view
}

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Find the user first so we can log activity even on failure
        $user = \App\Models\User::where('email', $request->email)
                                ->where('user_type', 'seller')
                                ->first();

        // Block deactivated accounts before attempting login
        if ($user && $user->isDeactivated()) {
            LoginActivityService::record($request, $user, 'seller', false, 'account_deactivated');

            return back()->withErrors([
                'email' => $user->canReactivate()
                    ? 'Your account has been deactivated. Log in and confirm reactivation before ' . $user->reactivation_deadline->format('d M Y') . '.'
                    : 'Your account has been permanently deactivated. Contact support@genesishub.ng.',
            ])->onlyInput('email');
        }

        if (Auth::guard('seller')->attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::guard('seller')->user();

            if ($user->user_type !== 'seller') {
                Auth::guard('seller')->logout();
                LoginActivityService::record($request, $user, 'seller', false, 'wrong_user_type');
                return back()->withErrors(['email' => 'This account is not registered as a seller.']);
            }

            $request->session()->regenerate();

            // ── Record successful login ───────────────────────────────────────
            LoginActivityService::record($request, $user, 'seller', true);

            return redirect()->route('seller.verification.notice');
        }

        // Record failed attempt
        if ($user) {
            LoginActivityService::record($request, $user, 'seller', false, 'invalid_password');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('seller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login.form')->with('success', 'You have been logged out.');
    }
}
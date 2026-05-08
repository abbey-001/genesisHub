<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiderLoginController extends Controller
{
public function showRiderLoginForm()
{
    if (auth()->check() && auth()->user()->user_type === 'rider') {
        return redirect()->route('rider.dashboard');
    }

    return view('auth.rider-login'); // your login view
}
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            if ($user->user_type !== 'rider') {
                Auth::logout();
                return back()->withErrors(['email' => 'This account is not registered as a rider.']);
            }

            $request->session()->regenerate();

            return redirect()->route('rider.dashboard')->with('success', 'Login successful!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('rider.login.form')->with('success', 'You have been logged out.');
    }
}

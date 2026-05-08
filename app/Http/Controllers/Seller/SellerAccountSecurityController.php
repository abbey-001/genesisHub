<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Handles account security actions for SELLERS.
 *
 * Routes use the 'seller' guard throughout — never auth() / Auth::user().
 */
class SellerAccountSecurityController extends Controller
{
    // ── Revoke all other active sessions ─────────────────────────────────────

    public function revokeOtherSessions(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password:seller'],
        ]);

        // logoutOtherDevices rehashes the password and invalidates all
        // other sessions stored in the sessions table.
        Auth::guard('seller')->logoutOtherDevices($request->password);

        return back()->with('security_success', 'All other sessions have been revoked.');
    }

    // ── Deactivate seller account ─────────────────────────────────────────────

    public function deactivate(Request $request)
    {
        $user = Auth::guard('seller')->user();

        $request->validate([
            'confirm_deactivate' => ['required', 'in:DEACTIVATE'],
        ], [
            'confirm_deactivate.in' => 'Please type DEACTIVATE exactly to confirm.',
        ]);

        if (! $user->isSocialOnly()) {
            $request->validate([
                'password' => ['required', 'current_password:seller'],
            ]);
        }

        $user->update([
            'deactivated_at'        => now(),
            'reactivation_deadline' => now()->addDays(30),
        ]);

        Auth::guard('seller')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('seller.login.form')
            ->with('status', 'Your account has been deactivated. You have 30 days to reactivate it by logging back in.');
    }

    // ── Reactivate (called right after a deactivated seller logs in) ──────────

    public function reactivate(Request $request)
    {
        $user = Auth::guard('seller')->user();

        if (! $user?->isDeactivated()) {
            return redirect()->route('seller.dashboard');
        }

        if (! $user->canReactivate()) {
            return back()->withErrors([
                'error' => 'Your reactivation window has expired. Please contact support@genesishub.ng.',
            ]);
        }

        $user->update([
            'deactivated_at'        => null,
            'reactivation_deadline' => null,
        ]);

        return redirect()->route('seller.dashboard')
            ->with('success', 'Welcome back! Your seller account has been reactivated.');
    }
}
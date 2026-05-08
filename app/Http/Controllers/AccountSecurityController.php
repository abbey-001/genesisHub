<?php

namespace App\Http\Controllers;

use App\Models\EmailChangeRequest;
use App\Models\LoginActivity;
use App\Notifications\EmailChangeConfirmation;
use App\Notifications\EmailChanged;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Handles all account security features for CUSTOMERS.
 *
 * Seller equivalents are in SellerAccountSecurityController.
 *
 * Features:
 *  - Security overview page (sessions + login log + deactivation + email change)
 *  - Revoke other sessions
 *  - Request email change (sends confirmation link to new email)
 *  - Confirm email change (user clicks link in email)
 *  - Cancel pending email change
 *  - Deactivate account (30-day reactivation window)
 *  - Reactivate account
 */
class AccountSecurityController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // SECURITY PAGE
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user = $request->user();

        $loginActivities = LoginActivity::forUser($user->id, $user->user_type)
            ->latest('logged_in_at')
            ->limit(10)
            ->get();

        $pendingEmailChange = EmailChangeRequest::where('user_id', $user->id)
            ->where('confirmed', false)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $sessionCount = $this->getActiveSessionCount($user->id);

        return view('account.security', compact(
            'user',
            'loginActivities',
            'pendingEmailChange',
            'sessionCount'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // SESSION MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function revokeOtherSessions(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        Auth::guard('web')->logoutOtherDevices($request->password);

        return back()->with('security_success', 'All other sessions have been revoked.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EMAIL CHANGE
    // ─────────────────────────────────────────────────────────────────────────

    public function requestEmailChange(Request $request)
    {
        $user = $request->user();

        if ($user->isSocialOnly()) {
            return back()->withErrors(['new_email' => 'Social login accounts cannot change their email address directly.']);
        }

        $request->validate([
            'new_email'        => ['required', 'email', 'unique:users,email'],
            'current_password' => ['required', 'current_password'],
        ]);

        // Cancel any existing pending request
        EmailChangeRequest::where('user_id', $user->id)
            ->where('confirmed', false)
            ->delete();

        $token = Str::random(64);

        $changeRequest = EmailChangeRequest::create([
            'user_id'    => $user->id,
            'old_email'  => $user->email,
            'new_email'  => $request->new_email,
            'token'      => $token,
            'expires_at' => now()->addHours(24),
        ]);

        $user->notify(new EmailChangeConfirmation($changeRequest));

        return back()->with('security_success', 'A confirmation link has been sent to ' . $request->new_email . '. Click it within 24 hours to complete the change.');
    }

    public function confirmEmailChange(string $token)
    {
        $changeRequest = EmailChangeRequest::where('token', $token)
            ->where('confirmed', false)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $user = $changeRequest->user;

        DB::transaction(function () use ($user, $changeRequest) {
            $oldEmail = $user->email;

            $user->update([
                'email'             => $changeRequest->new_email,
                'email_verified_at' => now(),
            ]);

            $changeRequest->update([
                'confirmed'    => true,
                'confirmed_at' => now(),
            ]);

            // Security alert to old address
            $user->notify(new EmailChanged($oldEmail, $changeRequest->new_email));
        });

        return redirect()->route('account.security')
            ->with('security_success', 'Your email address has been updated to ' . $changeRequest->new_email . '.');
    }

    public function cancelEmailChange(Request $request)
    {
        EmailChangeRequest::where('user_id', $request->user()->id)
            ->where('confirmed', false)
            ->delete();

        return back()->with('security_success', 'Email change request cancelled.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ACCOUNT DEACTIVATION
    // ─────────────────────────────────────────────────────────────────────────

    public function deactivate(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'confirm_deactivate' => ['required', 'in:DEACTIVATE'],
        ], [
            'confirm_deactivate.in' => 'Please type DEACTIVATE exactly to confirm.',
        ]);

        if (! $user->isSocialOnly()) {
            $request->validate([
                'password' => ['required', 'current_password'],
            ]);
        }

        $user->update([
            'deactivated_at'        => now(),
            'reactivation_deadline' => now()->addDays(30),
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'Your account has been deactivated. You have 30 days to reactivate it by logging back in.');
    }

    public function reactivate(Request $request)
    {
        $user = $request->user();

        if (! $user->isDeactivated()) {
            return redirect()->route('account.index');
        }

        if (! $user->canReactivate()) {
            return back()->withErrors(['error' => 'Your reactivation window has expired. Please contact support@genesishub.ng.']);
        }

        $user->update([
            'deactivated_at'        => null,
            'reactivation_deadline' => null,
        ]);

        return redirect()->route('account.index')
            ->with('success', 'Welcome back! Your account has been reactivated.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function getActiveSessionCount(int $userId): int
    {
        try {
            return DB::table('sessions')
                ->where('user_id', $userId)
                ->count();
        } catch (\Throwable) {
            return 1;
        }
    }
}
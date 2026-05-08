<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Handles all three seller email verification actions:
 *
 *  notice  — the "please check your email" holding page
 *  verify  — the signed URL clicked from the email (no prior session required)
 *  resend  — the "resend email" button on the notice page
 */
class SellerEmailVerificationController extends Controller
{
    /**
     * Show the "check your email" notice page.
     * If already verified, send them straight to the dashboard.
     */
    public function notice()
    {
        $user = auth()->guard('seller')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('seller.dashboard');
        }

        return view('auth.seller-verify-email');
    }

    /**
     * Handle the signed verification URL clicked from the email.
     *
     * IMPORTANT: This route does NOT sit behind the 'seller' middleware.
     * The seller may click the link in a fresh browser tab with no active
     * session — the signed URL itself is the proof of identity.
     * Only the 'signed' middleware is needed here.
     */
    public function verify(Request $request)
    {
        // Find user directly from the URL — no session dependency
        $user = User::findOrFail($request->route('id'));

        // Must be a seller
        if ($user->user_type !== 'seller') {
            abort(403, 'This verification link is not valid for this account type.');
        }

        // Validate the hash against the user's email
        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            (string) $request->route('hash')
        )) {
            abort(403, 'Invalid verification link.');
        }

        // Mark verified if not already
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Log them into the seller guard then send straight to dashboard.
        // SellerVerified middleware will take over from here.
        Auth::guard('seller')->login($user);

        return redirect()->route('seller.dashboard')
            ->with('status', 'email-verified');
    }

    /**
     * Resend the verification email.
     */
    public function resend(Request $request)
    {
        $user = auth()->guard('seller')->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('seller.dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
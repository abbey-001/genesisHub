<?php

namespace App\Http\Middleware;

use App\Models\LoginActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Blocks deactivated accounts from accessing the application.
 *
 * Register globally or per-guard in Kernel.php under $middlewareGroups['web'].
 * This runs AFTER authentication so Auth::user() is available.
 *
 * For the seller guard, register in the 'seller' middleware group.
 */
class PreventDeactivatedLogin
{
    public function handle(Request $request, Closure $next, string $guard = 'web')
    {
        $user = Auth::guard($guard)->user();

        if ($user && $user->isDeactivated()) {
            // Log them out immediately
            Auth::guard($guard)->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Record the blocked attempt
            LoginActivity::create([
                'user_id'        => $user->id,
                'user_type'      => $user->user_type,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
                'device'         => LoginActivity::parseDevice($request->userAgent() ?? ''),
                'successful'     => false,
                'failure_reason' => 'account_deactivated',
                'logged_in_at'   => now(),
            ]);

            $loginRoute = match($guard) {
                'seller' => route('seller.login.form'),
                default  => route('login'),
            };

            return redirect($loginRoute)->withErrors([
                'email' => $user->canReactivate()
                    ? 'Your account has been deactivated. You can reactivate it by logging in and confirming reactivation before ' . $user->reactivation_deadline->format('d M Y') . '.'
                    : 'Your account has been permanently deactivated. Please contact support@genesishub.ng.',
            ]);
        }

        return $next($request);
    }
}
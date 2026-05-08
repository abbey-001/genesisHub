<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate 1 — Is this person authenticated as a seller?
 *
 * Checks the 'seller' guard session. Completely separate from the
 * customer 'web' guard, so sellers and customers never bleed into
 * each other's sessions.
 *
 * Register alias:
 *   Laravel 11 bootstrap/app.php → $middleware->alias(['seller' => EnsureSeller::class])
 *   Laravel 10 Kernel.php        → $routeMiddleware['seller'] = EnsureSeller::class
 */
class EnsureSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->guard('seller')->check()) {
            return redirect()->route('seller.login.form');
        }

        $user = auth()->guard('seller')->user();

        if ($user->user_type !== 'seller') {
            auth()->guard('seller')->logout();
            return redirect()->route('seller.login.form')
                ->withErrors(['email' => __('auth.failed')]);
        }

        return $next($request);
    }
}
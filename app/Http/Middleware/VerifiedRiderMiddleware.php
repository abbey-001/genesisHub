<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifiedRiderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $rider = auth()->user()->rider;

        if (!$rider) {
            abort(403, 'Rider account required.');
        }

        if (!$rider->is_verified) {
            return redirect()->route('rider.verification.pending')
                ->with('warning', 'Your rider account is pending verification.');
        }

        if (!$rider->is_active) {
            return redirect()->route('rider.account.suspended')
                ->with('error', 'Your rider account has been suspended.');
        }

        return $next($request);
    }
}

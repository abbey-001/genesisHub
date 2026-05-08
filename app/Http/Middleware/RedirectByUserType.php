<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectByUserType
{
    public function handle(Request $request, Closure $next)
    {
        if (auth('seller')->check()) {
            return redirect()->route('seller.dashboard');
        }

        if (auth()->check()) {
            $user = auth()->user();

            if ($user->user_type === 'rider') {
                return redirect()->route('rider.dashboard');
            }
        }

        return $next($request);
    }
}
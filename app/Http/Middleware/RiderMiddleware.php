<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RiderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->user_type === 'rider') {
            return $next($request);
        }

        return redirect('/rider/login')->with('error', 'Unauthorized access.');
    }
}

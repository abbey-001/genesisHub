<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SellerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->user_type === 'seller') {
            return $next($request);
        }

        return redirect('/seller/login')->with('error', 'Unauthorized access.');
    }
}

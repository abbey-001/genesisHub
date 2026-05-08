<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if admin is authenticated
        if (!auth()->guard('admin')->check()) {
            return redirect()->route('admin.login')->with('error', 'Please login to access admin panel.');
        }

        // Check if admin is active
        $admin = auth()->guard('admin')->user();
        if (!$admin->isActive()) {
            auth()->guard('admin')->logout();
            return redirect()->route('admin.login')->with('error', 'Your account has been deactivated. Contact support.');
        }

        return $next($request);
    }
}
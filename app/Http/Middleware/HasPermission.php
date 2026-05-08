<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermission
{
    /**
     * Handle an incoming request.
     *
     * @param array|string $permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $admin = auth()->guard('admin')->user();

        if (!$admin) {
            abort(403, 'Unauthorized access.');
        }

        // Super Admin bypasses permission checks
        if ($admin->isSuperAdmin()) {
            return $next($request);
        }

        // Check if admin has any of the required permissions
        if (!$admin->hasAnyPermission($permissions)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
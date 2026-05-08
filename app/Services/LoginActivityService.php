<?php

namespace App\Services;

use App\Models\LoginActivity;
use Illuminate\Http\Request;

/**
 * Records login events for customers, sellers, and riders.
 *
 * Usage — in any login controller after a successful/failed attempt:
 *
 *   LoginActivityService::record($request, $user, 'seller', true);
 *   LoginActivityService::record($request, null, 'customer', false, 'invalid_password');
 */
class LoginActivityService
{
    public static function record(
        Request $request,
        ?object $user,
        string  $userType,
        bool    $successful,
        ?string $failureReason = null
    ): void {
        if (! $user) return;

        LoginActivity::create([
            'user_id'        => $user->id,
            'user_type'      => $userType,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'device'         => LoginActivity::parseDevice($request->userAgent() ?? ''),
            'successful'     => $successful,
            'failure_reason' => $successful ? null : $failureReason,
            'logged_in_at'   => now(),
        ]);
    }
}
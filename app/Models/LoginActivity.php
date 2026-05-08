<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginActivity extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'ip_address',
        'user_agent',
        'device',
        'location',
        'successful',
        'failure_reason',
        'logged_in_at',
    ];

    protected $casts = [
        'successful'   => 'boolean',
        'logged_in_at' => 'datetime',
    ];

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId, string $userType)
    {
        return $query->where('user_id', $userId)->where('user_type', $userType);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Parse a user-agent string into a human-readable device label.
     * e.g. "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/..."
     *   → "Chrome on Windows"
     */
    public static function parseDevice(string $userAgent): string
    {
        $browser = 'Unknown Browser';
        $os      = 'Unknown OS';

        // Browser detection
        if (str_contains($userAgent, 'Edg/'))        $browser = 'Edge';
        elseif (str_contains($userAgent, 'OPR/'))    $browser = 'Opera';
        elseif (str_contains($userAgent, 'Chrome'))  $browser = 'Chrome';
        elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
        elseif (str_contains($userAgent, 'Safari'))  $browser = 'Safari';

        // OS detection
        if (str_contains($userAgent, 'Windows'))     $os = 'Windows';
        elseif (str_contains($userAgent, 'Mac OS'))  $os = 'Mac';
        elseif (str_contains($userAgent, 'Android')) $os = 'Android';
        elseif (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad')) $os = 'iOS';
        elseif (str_contains($userAgent, 'Linux'))   $os = 'Linux';

        return "{$browser} on {$os}";
    }
}
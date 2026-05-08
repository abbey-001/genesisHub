<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailChangeRequest extends Model
{
    protected $fillable = [
        'user_id',
        'old_email',
        'new_email',
        'token',
        'confirmed',
        'expires_at',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed'    => 'boolean',
        'expires_at'   => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isPending(): bool
    {
        return ! $this->confirmed && ! $this->isExpired();
    }
}
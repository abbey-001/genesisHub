<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscription extends Model
{
    protected $fillable = ['email', 'token', 'is_active', 'subscribed_at'];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'is_active'     => 'boolean',
    ];

    // Auto-generate a unique token before creating
    protected static function booted(): void
    {
        static::creating(function ($sub) {
            $sub->token = Str::random(64);
        });
    }
}
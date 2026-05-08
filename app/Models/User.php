<?php

namespace App\Models;

use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, TwoFactorAuthenticatable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'google_id',
        'facebook_id',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    /**
     * Override the default notification so ALL users (customer and seller)
     * receive our custom branded email that generates the correct signed URL
     * based on user_type (see VerifyEmailNotification).
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }
    
        // ─── Account state helpers ────────────────────────────────────────────────

    public function isDeactivated(): bool
    {
        return ! is_null($this->deactivated_at);
    }

    public function canReactivate(): bool
    {
        return $this->isDeactivated()
            && $this->reactivation_deadline
            && $this->reactivation_deadline->isFuture();
    }

    public function isSocialOnly(): bool
    {
        return is_null($this->password)
            && (! is_null($this->google_id) || ! is_null($this->facebook_id));
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function shippingAddresses()
    {
        return $this->addresses()->whereIn('type', ['shipping', 'both']);
    }

    public function defaultBillingAddress()
    {
        return $this->billingAddresses()->where('is_default', true)->first();
    }

    public function defaultShippingAddress()
    {
        return $this->shippingAddresses()->where('is_default', true)->first();
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function rider()
    {
        return $this->hasOne(Rider::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
        public function loginActivities()
    {
        return $this->hasMany(LoginActivity::class)
                    ->where('user_type', $this->user_type)
                    ->latest('logged_in_at');
    }

    public function pendingEmailChange()
    {
        return $this->hasOne(EmailChangeRequest::class)
                    ->where('confirmed', false)
                    ->where('expires_at', '>', now());
    }
}
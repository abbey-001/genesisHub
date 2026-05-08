<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_type',
        'tax_id',
        'bank_account',
        'bank_name',
        'account_holder_name',
        'phone_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'is_verified',
        'verification_status',
        'commission_rate',
        'telegram_chat_id',
        'telegram_link_token',
        'telegram_linked_at',
        'telegram_notifications_enabled',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'commission_rate' => 'decimal:2',
         'telegram_linked_at' => 'datetime',
    ];

    // Existing relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shop()
    {
        return $this->hasOne(Shop::class);
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,  // final model
            Shop::class,     // intermediate model
            'seller_id',     // Foreign key on Shop table
            'shop_id',       // Foreign key on Product table
            'id',            // Local key on Seller table
            'id'             // Local key on Shop table
        );
    }


    public function orders()
    {
        return $this->hasManyThrough(Order::class, Product::class);
    }

    // New wallet-related relationships
    public function wallet()
    {
        return $this->hasOne(SellerWallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(SellerWalletTransaction::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function payoutSettings()
    {
        return $this->hasOne(SellerPayoutSettings::class);
    }

    // Accessor to get shop name
    public function getNameAttribute()
    {
        return $this->shop?->shop_name ?? 'Unknown Shop';
    }

    // Accessor to get shop logo
    public function getLogoAttribute()
    {
        return $this->shop?->shop_logo ?? null;
    }

    // Helper methods
    public function getOrCreateWallet()
    {
        return $this->wallet ?? $this->wallet()->create([
            'balance' => 0,
            'pending_balance' => 0,
            'total_earned' => 0,
            'total_withdrawn' => 0,
            'reserved_balance' => 0,
        ]);
    }

    public function hasInsufficientBalance($amount)
    {
        return $this->wallet ? $this->wallet->balance < $amount : true;
    }

    public function canRequestPayout($amount)
    {
        if (!$this->wallet) {
            return false;
        }

        $minimumPayout = $this->payoutSettings?->minimum_payout ?? 10.00;
        
        return $this->wallet->balance >= $amount && $amount >= $minimumPayout;
    }
    
}
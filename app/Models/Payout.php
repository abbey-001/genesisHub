<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'wallet_transaction_id',
        'amount',
        'fee_amount',
        'net_amount',
        'status',
        'payout_method',
        'transaction_id',
        'notes',
        'failure_reason',
        'requested_at',
        'processed_at',
        'failed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'fee_amount'   => 'decimal:2',
        'net_amount'   => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'failed_at'    => 'datetime',
    ];
 
    // ─── Relationships ───────────────────────────────────────────
 
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
 
    public function walletTransaction()
    {
        return $this->belongsTo(SellerWalletTransaction::class, 'wallet_transaction_id');
    }
 
    // ─── Status helpers ──────────────────────────────────────────
 
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
 
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
 
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
 
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
 
    /**
     * Only pending payouts can be cancelled by the seller.
     * Processing payouts are in the admin's hands.
     */
    public function canBeCancelled(): bool
    {
        return $this->status === 'pending';
    }
 
    // ─── Accessors ───────────────────────────────────────────────
 
    /**
     * Computed fee percentage — used in show.blade.php.
     * fee_percentage is NOT stored in the DB; it is derived from
     * fee_amount / amount so it is always consistent.
     */
    public function getFeePercentageAttribute(): float
    {
        if (!$this->amount || $this->amount == 0) {
            return 0.0;
        }
 
        return round(($this->fee_amount / $this->amount) * 100, 2);
    }
 
    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'warning',
            'processing' => 'info',
            'completed'  => 'success',
            'failed'     => 'danger',
            default      => 'secondary',
        };
    }
 
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'Pending',
            'processing' => 'Processing',
            'completed'  => 'Completed',
            'failed'     => 'Failed / Cancelled',
            default      => ucfirst($this->status),
        };
    }
 
    public function getPayoutMethodLabelAttribute(): string
    {
        return match ($this->payout_method) {
            'bank_transfer'   => 'Bank Transfer',
            'online_transfer' => 'Online Transfer',
            'paypal'          => 'PayPal',
            'stripe'          => 'Stripe',
            default           => ucfirst(str_replace('_', ' ', $this->payout_method ?? '')),
        };
    }
 
    // ─── Scopes ──────────────────────────────────────────────────
 
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
 
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }
 
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
 
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
 
    public function scopeForSeller($query, int $sellerId)
    {
        return $query->where('seller_id', $sellerId);
    }
}
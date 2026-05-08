<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerWalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'wallet_id',
        'type',
        'source',
        'amount',
        'balance_before',
        'balance_after',
        'transactable_type',
        'transactable_id',
        'transaction_id',
        'description',
        'metadata',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function wallet()
    {
        return $this->belongsTo(SellerWallet::class, 'wallet_id');
    }

    public function transactable()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeCredits($query)
    {
        return $query->where('type', 'credit');
    }

    public function scopeDebits($query)
    {
        return $query->where('type', 'debit');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReversed($query)
    {
        return $query->where('status', 'reversed');
    }

    public function scopeForSource($query, $source)
    {
        return $query->where('source', $source);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Attributes
    public function getIsDebitAttribute()
    {
        return in_array($this->type, ['debit', 'reserve']);
    }

    public function getIsCreditAttribute()
    {
        return in_array($this->type, ['credit', 'release']);
    }

    public function getFormattedAmountAttribute()
    {
        $sign = $this->is_debit ? '-' : '+';
        return $sign . '₦' . number_format($this->amount, 2);
    }

    public function getTypeBadgeAttribute()
    {
        $badges = [
            'credit' => 'success',
            'debit' => 'danger',
            'reserve' => 'warning',
            'release' => 'info',
        ];
        return $badges[$this->type] ?? 'secondary';
    }

    /**
     * Get user-friendly source label
     */
    public function getSourceLabelAttribute()
    {
        $labels = [
            'order' => 'Sales Earnings',
            'order_pending' => 'Pending Earnings',
            'pending_release' => 'Earnings Released',
            'payout' => 'Withdrawal Request',
            'payout_refund' => 'Withdrawal Cancelled',
            'refund' => 'Customer Refund',
            'manual_adjustment' => 'Admin Adjustment',
            'manual_pending_release' => 'Admin Pending Release',
        ];

        return $labels[$this->source] ?? ucfirst(str_replace('_', ' ', $this->source));
    }

    /**
     * Get transaction type icon
     */
    public function getTypeIconAttribute()
    {
        $icons = [
            'credit' => 'arrow-down-circle',
            'debit' => 'arrow-up-circle',
            'reserve' => 'lock',
            'release' => 'unlock',
        ];
        return $icons[$this->type] ?? 'activity';
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'completed' => 'success',
            'pending' => 'warning',
            'reversed' => 'danger',
            'processing' => 'info',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Check if transaction can be reversed
     */
    public function canBeReversed()
    {
        return $this->status === 'completed' 
            && in_array($this->source, ['order', 'manual_adjustment']);
    }
}
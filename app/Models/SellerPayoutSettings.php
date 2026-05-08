<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerPayoutSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'minimum_payout',
        'preferred_method',
        'payout_schedule',
        'payout_day',
        'auto_payout_enabled',
        'auto_payout_threshold',
        'hold_period_days',
    ];

    protected $casts = [
        'minimum_payout' => 'decimal:2',
        'auto_payout_enabled' => 'boolean',
        'auto_payout_threshold' => 'decimal:2',
        'payout_day' => 'integer',
        'hold_period_days' => 'integer',
    ];

    // Relationships
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    // Accessors
    public function getPayoutScheduleTextAttribute()
    {
        return match($this->payout_schedule) {
            'manual' => 'Manual requests only',
            'weekly' => 'Weekly on ' . $this->getWeekdayName(),
            'biweekly' => 'Bi-weekly on ' . $this->getWeekdayName(),
            'monthly' => 'Monthly on day ' . $this->payout_day,
            default => 'Not configured',
        };
    }

    public function getPreferredMethodTextAttribute()
    {
        return match($this->preferred_method) {
            'bank_transfer' => 'Bank Transfer',
            'paypal' => 'PayPal',
            'stripe' => 'Stripe',
            default => 'Not set',
        };
    }

    // Helper methods
    protected function getWeekdayName()
    {
        if (!$this->payout_day) {
            return 'Not set';
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        return $days[$this->payout_day] ?? 'Invalid';
    }

    public function isPayoutDue()
    {
        if ($this->payout_schedule === 'manual') {
            return false;
        }

        $today = now();

        return match($this->payout_schedule) {
            'weekly' => $today->dayOfWeek === $this->payout_day,
            'biweekly' => $today->dayOfWeek === $this->payout_day && $today->weekOfYear % 2 === 0,
            'monthly' => $today->day === $this->payout_day,
            default => false,
        };
    }
}
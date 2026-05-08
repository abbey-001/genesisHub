<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Admin;
use App\Models\Rider;
use App\Models\Delivery;
use Illuminate\Support\Facades\DB;
use App\Notifications\RiderPayoutRequested;
use App\Notifications\RiderPayoutApproved;
use App\Notifications\RiderPayoutPaid;
use App\Notifications\RiderPayoutRejected;

class DeliveryPayout extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID     = 'paid';
    public const STATUS_REJECTED = 'rejected';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'rider_id',
        'reference_number',
        'amount',
        'status',
        'requested_at',
        'approved_at',
        'approved_by_user_id',
        'paid_at',
        'paid_by_user_id',
        'rejected_at',
        'rejected_by_user_id',
        'bank_name',
        'account_number',
        'account_name',
        'deliveries_count',
        'period_from',
        'period_to',
        'transaction_reference',
        'payment_method',
        'notes',
        'rejection_reason',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'amount'       => 'decimal:2',
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
        'paid_at'      => 'datetime',
        'rejected_at'  => 'datetime',
        'period_from'  => 'datetime',
        'period_to'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot Defaults
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($payout) {
            if (!$payout->reference_number) {
                $payout->reference_number = self::generateReference();
            }

            if (!$payout->status) {
                $payout->status = self::STATUS_PENDING;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reference Generator
    |--------------------------------------------------------------------------
    */

    public static function generateReference(): string
    {
        do {
            $reference = 'PO-' . strtoupper(uniqid());
        } while (self::where('reference_number', $reference)->exists());

        return $reference;
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // public function rider()
    // {
    //     return $this->belongsTo(Rider::class, 'rider_id');
    // }

    public function company()
{
    return $this->belongsTo(Rider::class, 'rider_id');
}


    public function deliveries()
    {
        return $this->belongsToMany(
            Delivery::class,
            'payout_deliveries',
            'payout_id',
            'delivery_id'
        );
    }

    public function approvedBy()
    {
        return $this->belongsTo(Admin::class, 'approved_by_user_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(Admin::class, 'paid_by_user_id');
    }

    public function rejectedBy()
    {
        return $this->belongsTo(Admin::class, 'rejected_by_user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /*
    |--------------------------------------------------------------------------
    | State Checks
    |--------------------------------------------------------------------------
    */

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function canApprove(): bool
    {
        return $this->isPending();
    }

    public function canPay(): bool
    {
        return $this->isApproved();
    }

    public function canReject(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_APPROVED
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function approve(Admin $admin, ?string $notes = null): self
    {
        if (!$this->canApprove()) {
            throw new \LogicException('This payout cannot be approved.');
        }

        $this->update([
            'status'                => self::STATUS_APPROVED,
            'approved_at'           => now(),
            'approved_by_user_id'   => $admin->id,
            'notes'                 => $notes,
        ]);
        
        $this->company->user?->notify(new RiderPayoutApproved($this));                // ← ADD

        return $this;
    }

    

    public function markAsPaid(
        Admin $admin,
        string $transactionRef,
        string $paymentMethod = 'bank_transfer'
    ): self {
        if (!$this->canPay()) {
            throw new \LogicException('This payout cannot be marked as paid.');
        }
    
        // Start transaction
        DB::beginTransaction();
        try {
            // 1️⃣ Create a payout batch in DB directly
            $batchId = DB::table('payout_batches')->insertGetId([
                'batch_number'     => 'PB-' . strtoupper(uniqid()),
                'total_amount'     => $this->amount,
                'total_riders'     => 1, // this payout is for 1 rider
                'total_deliveries' => $this->deliveries()->count(),
                'status'           => 'completed', // <-- use ENUM value
                'notes'            => null,
                'processed_at'     => now(),
                'processed_by'     => $admin->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
            
    
            // 2️⃣ Update the payout itself
            $this->update([
                'status'               => self::STATUS_PAID,
                'paid_at'              => now(),
                'paid_by_user_id'      => $admin->id,
                'transaction_reference'=> $transactionRef,
                'payment_method'       => $paymentMethod,
                'payout_batch_id'      => $batchId, // <-- store batch id here if you have the column
            ]);
    
            // 3️⃣ Update all attached deliveries
            $this->deliveries()->update([
                'paid_to_rider_at' => now(),
                'payout_batch_id'  => $batchId,
            ]);
    
            DB::commit();
            $this->company->user?->notify(new RiderPayoutPaid($this));                    // ← ADD

            return $this;
    
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    

    public function reject(Admin $admin, string $reason): self
    {
        if (!$this->canReject()) {
            throw new \LogicException('This payout cannot be rejected.');
        }

        $this->update([
            'status'          => self::STATUS_REJECTED,
            'rejected_at'     => now(),
            'rejected_by_user_id'     => $admin->id,
            'rejection_reason'=> $reason,
        ]);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusBadgeAttribute(): string
    {
        return [
            self::STATUS_PENDING  => 'warning',
            self::STATUS_APPROVED => 'info',
            self::STATUS_PAID     => 'success',
            self::STATUS_REJECTED => 'danger',
        ][$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return [
            self::STATUS_PENDING  => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_PAID     => 'Paid',
            self::STATUS_REJECTED => 'Rejected',
        ][$this->status] ?? 'Unknown';
    }

    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount, 2);
    }
}

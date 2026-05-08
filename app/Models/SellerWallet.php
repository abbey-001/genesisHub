<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'reserved_balance',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance'             => 'decimal:2',
        'pending_balance'     => 'decimal:2',
        'total_earned'        => 'decimal:2',
        'total_withdrawn'     => 'decimal:2',
        'reserved_balance'    => 'decimal:2',
        'last_transaction_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function transactions()
    {
        return $this->hasMany(SellerWalletTransaction::class, 'wallet_id');
    }

    // ─── Balance operations ──────────────────────────────────────

    /**
     * Credit the AVAILABLE balance (immediate earnings or refund reversals).
     *
     * @param bool $updateTotalEarned  Pass false for payout reversals that
     *                                 should NOT count as new income.
     */
    public function credit(
        $amount,
        $source,
        $transactable = null,
        $description  = null,
        $metadata     = [],
        $updateTotalEarned = true
    ) {
        return DB::transaction(function () use (
            $amount, $source, $transactable, $description, $metadata, $updateTotalEarned
        ) {
            $this->lockForUpdate();

            $balanceBefore  = $this->balance;
            $this->balance  = round($this->balance + $amount, 2);

            if ($updateTotalEarned) {
                $this->total_earned = round($this->total_earned + $amount, 2);
            }

            $this->last_transaction_at = now();
            $this->save();

            return $this->transactions()->create([
                'seller_id'         => $this->seller_id,
                'wallet_id'         => $this->id,
                'type'              => 'credit',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $this->balance,
                'transactable_type' => $transactable ? get_class($transactable) : null,
                'transactable_id'   => $transactable?->id,
                'description'       => $description,
                'metadata'          => $metadata,
                'status'            => 'completed',
                'transaction_id'    => null,
            ]);
        });
    }

    /**
     * Debit the AVAILABLE balance (payout withdrawals, refunds).
     */
    public function debit(
        $amount,
        $source,
        $transactable = null,
        $description  = null,
        $metadata     = []
    ) {
        return DB::transaction(function () use (
            $amount, $source, $transactable, $description, $metadata
        ) {
            $this->lockForUpdate();

            if ($this->balance < $amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            $balanceBefore         = $this->balance;
            $this->balance         = round($this->balance - $amount, 2);
            $this->total_withdrawn = round($this->total_withdrawn + $amount, 2);
            $this->last_transaction_at = now();
            $this->save();

            return $this->transactions()->create([
                'seller_id'         => $this->seller_id,
                'wallet_id'         => $this->id,
                'type'              => 'debit',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $this->balance,
                'transactable_type' => $transactable ? get_class($transactable) : null,
                'transactable_id'   => $transactable?->id,
                'transaction_id'    => null,
                'description'       => $description,
                'metadata'          => $metadata,
                'status'            => 'completed',
            ]);
        });
    }

    /**
     * Reserve funds for a potential dispute (balance → reserved_balance).
     */
    public function reserve($amount, $source, $transactable = null, $description = null)
    {
        return DB::transaction(function () use ($amount, $source, $transactable, $description) {
            $this->lockForUpdate();

            if ($this->balance < $amount) {
                throw new \Exception('Insufficient balance to reserve');
            }

            $balanceBefore           = $this->balance;
            $this->balance           = round($this->balance - $amount, 2);
            $this->reserved_balance  = round($this->reserved_balance + $amount, 2);
            $this->last_transaction_at = now();
            $this->save();

            return $this->transactions()->create([
                'seller_id'         => $this->seller_id,
                'wallet_id'         => $this->id,
                'type'              => 'reserve',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $this->balance,
                'transactable_type' => $transactable ? get_class($transactable) : null,
                'transactable_id'   => $transactable?->id,
                'description'       => $description ?? 'Funds reserved',
                'status'            => 'completed',
                'transaction_id'    => null,
            ]);
        });
    }

    /**
     * Release reserved funds back to available balance.
     */
    public function release($amount, $source, $transactable = null, $description = null)
    {
        return DB::transaction(function () use ($amount, $source, $transactable, $description) {
            $this->lockForUpdate();

            if ($this->reserved_balance < $amount) {
                throw new \Exception('Insufficient reserved balance');
            }

            $balanceBefore          = $this->balance;
            $this->balance          = round($this->balance + $amount, 2);
            $this->reserved_balance = round($this->reserved_balance - $amount, 2);
            $this->last_transaction_at = now();
            $this->save();

            return $this->transactions()->create([
                'seller_id'         => $this->seller_id,
                'wallet_id'         => $this->id,
                'type'              => 'release',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $this->balance,
                'transactable_type' => $transactable ? get_class($transactable) : null,
                'transactable_id'   => $transactable?->id,
                'description'       => $description ?? 'Reserved funds released',
                'status'            => 'completed',
                'transaction_id'    => null,
            ]);
        });
    }

    /**
     * Increment pending_balance and total_earned when a delivered item's
     * earnings enter the hold queue.
     */
    public function addPending($amount)
    {
        return DB::transaction(function () use ($amount) {
            $this->lockForUpdate();

            $this->pending_balance    = round($this->pending_balance + $amount, 2);
            $this->total_earned       = round($this->total_earned + $amount, 2);
            $this->last_transaction_at = now();
            $this->save();

            return $this;
        });
    }

    /**
     * Move $amount from pending_balance to available balance.
     * Returns the before/after pair so callers can build an accurate transaction
     * record without a separate (potentially stale) balance read.
     *
     * This method is called by releasePendingFunds() which has already locked
     * the wallet row from the outside — the inner savepoint is a no-op lock-
     * wise but is kept for safety when called in other contexts.
     *
     * @return array{before: string, after: string}
     */
    public function releasePendingWithSnapshot($amount): array
    {
        return DB::transaction(function () use ($amount) {
            $this->lockForUpdate();

            if ($this->pending_balance < $amount) {
                throw new \Exception(
                    "Insufficient pending balance (have ₦{$this->pending_balance}, need ₦{$amount})"
                );
            }

            $balanceBefore = $this->balance;

            $this->pending_balance    = round($this->pending_balance - $amount, 2);
            $this->balance            = round($this->balance + $amount, 2);
            $this->last_transaction_at = now();
            $this->save();

            return ['before' => $balanceBefore, 'after' => $this->balance];
        });
    }

    /**
     * Legacy wrapper — kept so any code that calls releasePending() still works.
     * New code should prefer releasePendingWithSnapshot().
     */
    public function releasePending($amount)
    {
        $this->releasePendingWithSnapshot($amount);
        return $this;
    }

    /**
     * Decrement total_withdrawn when a payout is cancelled or rejected.
     * Does NOT touch balance — the credit() call in failPayout() handles that.
     */
    public function reverseWithdrawal($amount)
    {
        return DB::transaction(function () use ($amount) {
            $this->lockForUpdate();

            $this->total_withdrawn = max(0, round($this->total_withdrawn - $amount, 2));
            $this->save();

            return $this;
        });
    }

    // ─── Computed attributes ─────────────────────────────────────

    public function getAvailableForPayoutAttribute()
    {
        return max(0, $this->balance);
    }

    public function getTotalBalanceAttribute()
    {
        return $this->balance + $this->pending_balance + $this->reserved_balance;
    }

    public function canRequestPayout($amount): bool
    {
        return $this->balance >= $amount;
    }

    public function hasNegativeBalance(): bool
    {
        return $this->balance < 0;
    }
}
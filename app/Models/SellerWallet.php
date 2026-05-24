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
            $wallet = $this->lockedFresh();

            $balanceBefore  = $wallet->balance;
            $wallet->balance  = round($wallet->balance + $amount, 2);

            if ($updateTotalEarned) {
                $wallet->total_earned = round($wallet->total_earned + $amount, 2);
            }

            $wallet->last_transaction_at = now();
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

            return $wallet->transactions()->create([
                'seller_id'         => $wallet->seller_id,
                'wallet_id'         => $wallet->id,
                'type'              => 'credit',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $wallet->balance,
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
            $wallet = $this->lockedFresh();

            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            $balanceBefore         = $wallet->balance;
            $wallet->balance         = round($wallet->balance - $amount, 2);
            $wallet->total_withdrawn = round($wallet->total_withdrawn + $amount, 2);
            $wallet->last_transaction_at = now();
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

            return $wallet->transactions()->create([
                'seller_id'         => $wallet->seller_id,
                'wallet_id'         => $wallet->id,
                'type'              => 'debit',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $wallet->balance,
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
            $wallet = $this->lockedFresh();

            if ($wallet->balance < $amount) {
                throw new \Exception('Insufficient balance to reserve');
            }

            $balanceBefore           = $wallet->balance;
            $wallet->balance           = round($wallet->balance - $amount, 2);
            $wallet->reserved_balance  = round($wallet->reserved_balance + $amount, 2);
            $wallet->last_transaction_at = now();
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

            return $wallet->transactions()->create([
                'seller_id'         => $wallet->seller_id,
                'wallet_id'         => $wallet->id,
                'type'              => 'reserve',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $wallet->balance,
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
            $wallet = $this->lockedFresh();

            if ($wallet->reserved_balance < $amount) {
                throw new \Exception('Insufficient reserved balance');
            }

            $balanceBefore          = $wallet->balance;
            $wallet->balance          = round($wallet->balance + $amount, 2);
            $wallet->reserved_balance = round($wallet->reserved_balance - $amount, 2);
            $wallet->last_transaction_at = now();
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

            return $wallet->transactions()->create([
                'seller_id'         => $wallet->seller_id,
                'wallet_id'         => $wallet->id,
                'type'              => 'release',
                'source'            => $source,
                'amount'            => $amount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $wallet->balance,
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
            $wallet = $this->lockedFresh();

            $wallet->pending_balance    = round($wallet->pending_balance + $amount, 2);
            $wallet->total_earned       = round($wallet->total_earned + $amount, 2);
            $wallet->last_transaction_at = now();
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

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
            $wallet = $this->lockedFresh();

            if ($wallet->pending_balance < $amount) {
                throw new \Exception(
                    "Insufficient pending balance (have ₦{$this->pending_balance}, need ₦{$amount})"
                );
            }

            $balanceBefore = $wallet->balance;

            $wallet->pending_balance    = round($wallet->pending_balance - $amount, 2);
            $wallet->balance            = round($wallet->balance + $amount, 2);
            $wallet->last_transaction_at = now();
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

            return ['before' => $balanceBefore, 'after' => $wallet->balance];
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
            $wallet = $this->lockedFresh();

            $wallet->total_withdrawn = max(0, round($wallet->total_withdrawn - $amount, 2));
            $wallet->save();
            $this->setRawAttributes($wallet->getAttributes(), true);

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

    protected function lockedFresh(): self
    {
        return static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();
    }
}

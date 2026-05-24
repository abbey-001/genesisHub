<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SellerWalletService
{
    // =========================================================================
    // WALLET BOOTSTRAP
    // =========================================================================

    /**
     * Get or create a seller's wallet.
     * Safe to call multiple times — idempotent.
     */
    public function getWallet(Seller $seller): SellerWallet
    {
        return SellerWallet::firstOrCreate(
            ['seller_id' => $seller->id],
            [
                'balance'          => 0,
                'pending_balance'  => 0,
                'total_earned'     => 0,
                'total_withdrawn'  => 0,
                'reserved_balance' => 0,
            ]
        );
    }

    // =========================================================================
    // EARNINGS FLOW
    //
    // Correct lifecycle:
    //   1. Order item delivered  → processItemDelivered()
    //                           → pending_balance  +earnings
    //                           → total_earned     +earnings
    //                           → transaction (source=order_pending, status=pending)
    //
    //   2. Hold period passes   → releasePendingFunds()  [daily cron]
    //                           → pending_balance  -earnings
    //                           → balance          +earnings
    //                           → original tx → status=completed
    //                           → new tx (source=pending_release, type=release)
    //
    //   3. Seller withdraws     → requestPayout()
    //                           → balance          -amount
    //                           → total_withdrawn  +amount
    //                           → Payout record (status=pending)
    //                           → tx (source=payout, type=debit)
    //
    // NOTE: The old processOrderPayment() (triggered on payment) is REMOVED.
    //       Earnings only enter the wallet when an item is DELIVERED.
    //       Delivery::markAsDelivered() calls processItemDelivered() automatically.
    // =========================================================================

    /**
     * Credit a seller's PENDING balance when one of their items is delivered.
     *
     * This is the ONLY place seller earnings enter the wallet.
     * Hold timer starts at DELIVERY TIME — not payment time.
     *
     * @throws \Exception  Rethrows after logging so the caller can decide how to handle it.
     */
    public function processItemDelivered(OrderItem $item): ?SellerWalletTransaction
    {
        // ── Double-credit guard ──────────────────────────────────────────────
        $alreadyCredited = SellerWalletTransaction::where('transactable_type', OrderItem::class)
            ->where('transactable_id', $item->id)
            ->whereIn('source', ['order_pending', 'order'])
            ->exists();

        if ($alreadyCredited) {
            Log::warning('processItemDelivered: item already credited — skipping', [
                'order_item_id' => $item->id,
                'order_id'      => $item->order_id,
            ]);
            return null;
        }

        try {
            $seller = Seller::findOrFail($item->seller_id);
            $wallet = $this->getWallet($seller);

            $commissionRate = $seller->commission_rate ?? config('platform.commission_rate');
            $commission     = round((float) $item->total_price * ($commissionRate / 100), 2);
            $sellerEarnings = round((float) $item->total_price - $commission, 2);

            if ($sellerEarnings <= 0) {
                throw new \Exception(
                    "Earnings for OrderItem #{$item->id} resolved to zero or negative " .
                    "(item total={$item->total_price}, commission_rate={$commissionRate})"
                );
            }

            $holdPeriodDays = (int) ($seller->payoutSettings?->hold_period_days ?? 7);

            // Wrap both the balance update AND the transaction record in one
            // DB::transaction so they can never get out of sync.
            return DB::transaction(function () use (
                $wallet, $seller, $item,
                $sellerEarnings, $commissionRate, $commission, $holdPeriodDays
            ) {
                if ($holdPeriodDays > 0) {
                    $pendingBefore = $wallet->pending_balance;

                    // addPending() has lockForUpdate internally (nested savepoint).
                    $wallet->addPending($sellerEarnings);
                    $wallet->refresh();

                    return $wallet->transactions()->create([
                        'seller_id'         => $seller->id,
                        'wallet_id'         => $wallet->id,   // ← required for the wallet->transactions() relationship
                        'type'              => 'credit',
                        'source'            => 'order_pending',
                        'amount'            => $sellerEarnings,
                        // balance_before/after reflect the pending_balance column,
                        // not the available balance (which is unchanged here).
                        'balance_before'    => $pendingBefore,
                        'balance_after'     => $wallet->pending_balance,
                        'transactable_type' => OrderItem::class,
                        'transactable_id'   => $item->id,
                        'transaction_id'    => null,
                        'description'       => sprintf(
                            'Earnings for "%s" (Order #%d) — hold %d day(s)',
                            $item->product_name,
                            $item->order_id,
                            $holdPeriodDays
                        ),
                        'metadata' => [
                            'order_id'          => $item->order_id,
                            'order_item_id'     => $item->id,
                            'item_total'        => $item->total_price,
                            'commission_rate'   => $commissionRate,
                            'commission_amount' => $commission,
                            'net_earnings'      => $sellerEarnings,
                            // Hold timer starts at DELIVERY time, not payment time.
                            'available_at'      => now()->addDays($holdPeriodDays)->toIso8601String(),
                        ],
                        'status' => 'pending',
                    ]);
                }

                // No hold period — credit straight to available balance.
                return $wallet->credit(
                    $sellerEarnings,
                    'order',
                    $item,
                    sprintf('Earnings for "%s" (Order #%d)', $item->product_name, $item->order_id),
                    [
                        'order_id'          => $item->order_id,
                        'order_item_id'     => $item->id,
                        'item_total'        => $item->total_price,
                        'commission_rate'   => $commissionRate,
                        'commission_amount' => $commission,
                        'net_earnings'      => $sellerEarnings,
                    ]
                );
            });

        } catch (\Exception $e) {
            Log::error('processItemDelivered: failed', [
                'order_item_id' => $item->id,
                'order_id'      => $item->order_id,
                'seller_id'     => $item->seller_id,
                'error'         => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Convenience: credit all delivered items in an order for one seller.
     * Each item is processed independently so one failure does not block others.
     */
    public function processOrderDelivered(Order $order, int $sellerId): void
    {
        $items = $order->items()
            ->where('seller_id', $sellerId)
            ->where('status', 'delivered')
            ->get();

        foreach ($items as $item) {
            try {
                $this->processItemDelivered($item);
            } catch (\Exception $e) {
                // Already logged inside processItemDelivered.
            }
        }
    }

    // =========================================================================
    // RELEASE PENDING FUNDS  (daily cron: wallet:release-pending)
    // =========================================================================

    /**
     * Move pending funds whose hold period has expired into available balance.
     *
     * Race-safe: each row is re-fetched with lockForUpdate() inside its own
     * DB::transaction so two concurrent cron runs cannot double-release.
     *
     * @return array{
     *     success: bool,
     *     released_count: int,
     *     skipped_count: int,
     *     total_checked: int,
     *     errors: array
     * }
     */
    public function releasePendingFunds(): array
    {
        $pendingIds   = SellerWalletTransaction::where('status', 'pending')
            ->where('source', 'order_pending')
            ->pluck('id');

        $releasedCount = 0;
        $skippedCount  = 0;
        $totalChecked  = $pendingIds->count();
        $errors        = [];

        foreach ($pendingIds as $txId) {
            try {
                $released = DB::transaction(function () use ($txId) {
                    $tx = SellerWalletTransaction::lockForUpdate()->find($txId);

                    // Another concurrent run already processed this — skip.
                    if (!$tx || $tx->status !== 'pending') {
                        return false;
                    }

                    $availableAt = $tx->metadata['available_at'] ?? null;

                    // Hold period not yet expired.
                    if (!$availableAt || now()->lt(\Carbon\Carbon::parse($availableAt))) {
                        return false;
                    }

                    // wallet_id is set by Eloquent on create; fall back to a
                    // direct lookup for any legacy rows that predate this fix.
                    $wallet = $tx->wallet
                        ?? SellerWallet::where('seller_id', $tx->seller_id)->firstOrFail();

                    // releasePendingWithSnapshot() acquires its own lockForUpdate
                    // and returns the before/after balance pair captured INSIDE
                    // that lock — so the snapshot is never stale, even if another
                    // concurrent operation modified the wallet between our read
                    // and the lock inside the method.
                    ['before' => $balanceBefore, 'after' => $balanceAfter]
                        = $wallet->releasePendingWithSnapshot($tx->amount);

                    // Finalise the original pending transaction record.
                    $tx->update([
                        'status'        => 'completed',
                        'balance_after' => $balanceAfter,
                    ]);

                    // Build a readable label for the release transaction.
                    $label = ($tx->transactable_type === OrderItem::class)
                        ? 'item #' . $tx->transactable_id
                        : 'order #' . $tx->transactable_id;

                    $wallet->transactions()->create([
                        'seller_id'         => $tx->seller_id,
                        'wallet_id'         => $wallet->id,   // ← required for relationship
                        'type'              => 'release',
                        'source'            => 'pending_release',
                        'amount'            => $tx->amount,
                        'balance_before'    => $balanceBefore,
                        'balance_after'     => $balanceAfter,
                        'transactable_type' => $tx->transactable_type,
                        'transactable_id'   => $tx->transactable_id,
                        'transaction_id'    => null,
                        'description'       => "Pending funds released for {$label}",
                        'metadata'          => $tx->metadata,
                        'status'            => 'completed',
                    ]);

                    return true;
                });

                $released ? $releasedCount++ : $skippedCount++;

            } catch (\Exception $e) {
                $errors[] = ['transaction_id' => $txId, 'error' => $e->getMessage()];
                Log::error('releasePendingFunds: failed for transaction', [
                    'transaction_id' => $txId,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        return [
            'success'        => true,
            'released_count' => $releasedCount,
            'skipped_count'  => $skippedCount,
            'total_checked'  => $totalChecked,
            'errors'         => $errors,
        ];
    }

    // =========================================================================
    // PAYOUT REQUEST / LIFECYCLE
    // =========================================================================

    /**
     * Request a payout withdrawal.
     *
     * Pre-flight checks run BEFORE opening a DB transaction (fail fast).
     * The debit() call inside uses lockForUpdate to prevent race conditions.
     *
     * @throws \Exception  User-friendly message safe to display in the UI.
     */
    public function requestPayout(Seller $seller, array $data): Payout
    {
        // ── Pre-flight (no transaction needed here) ───────────────────────────
        if (!$seller->is_verified) {
            throw new \Exception('Only verified sellers can request payouts.');
        }

        $hasPendingPayout = Payout::where('seller_id', $seller->id)
            ->whereIn('status', ['pending', 'processing'])
            ->exists();

        if ($hasPendingPayout) {
            throw new \Exception(
                'You already have a payout in progress. ' .
                'Please wait for it to complete before requesting another.'
            );
        }

        $wallet = $this->getWallet($seller);

        if (!$wallet->canRequestPayout($data['amount'])) {
            throw new \Exception(
                'Insufficient available balance. ' .
                'Available: ₦' . number_format($wallet->balance, 2) . ', ' .
                'Requested: ₦' . number_format($data['amount'], 2) . '.'
            );
        }

        $minimumPayout = (float) ($seller->payoutSettings?->minimum_payout ?? 10.00);

        if ($data['amount'] < $minimumPayout) {
            throw new \Exception(
                'Minimum withdrawal is ₦' . number_format($minimumPayout, 2) . '.'
            );
        }

        // ── Atomic: create Payout + debit wallet ─────────────────────────────
        $payout = DB::transaction(function () use ($wallet, $seller, $data) {
            $feeAmount      = $this->calculatePayoutFee($data['amount'], $data['payout_method']);
            $netAmount      = $data['amount'] - $feeAmount;

            $payout = Payout::create([
                'seller_id'             => $seller->id,
                'wallet_transaction_id' => null,   // filled below
                'amount'                => $data['amount'],
                'fee_amount'            => $feeAmount,
                'net_amount'            => $netAmount,
                'payout_method'         => $data['payout_method'],
                'notes'                 => $data['notes'] ?? null,
                'status'                => 'pending',
                'requested_at'          => now(),
                'processed_at'          => null,
                'failed_at'             => null,
                'failure_reason'        => null,
                'transaction_id'        => null,
            ]);

            // debit() uses lockForUpdate — protects against concurrent requests.
            $walletTx = $wallet->debit(
                $data['amount'],
                'payout',
                $payout,
                'Withdrawal request via ' . $data['payout_method'],
                [
                    'payout_id'     => $payout->id,
                    'payout_method' => $data['payout_method'],
                    'fee_amount'    => $feeAmount,
                    'net_amount'    => $netAmount,
                ]
            );

            $payout->update(['wallet_transaction_id' => $walletTx->id]);

            return $payout;
        });

        try {
            app(\App\Services\Telegram\SellerTelegramService::class)
                ->notifyPayoutRequested($seller->fresh(['wallet', 'shop', 'user']), $payout);
        } catch (\Exception $e) {
            Log::warning('Seller Telegram payout request alert failed', [
                'seller_id' => $seller->id,
                'payout_id' => $payout->id,
                'error'     => $e->getMessage(),
            ]);
        }

        return $payout;
    }

    /**
     * Move a payout from pending → processing.
     * Called by the admin when they have dispatched the payment to the provider.
     */
    public function markPayoutProcessing(Payout $payout): array
    {
        return DB::transaction(function () use ($payout) {
            if ($payout->status !== 'pending') {
                throw new \Exception(
                    'Only pending payouts can be moved to processing (current: ' . $payout->status . ').'
                );
            }

            $payout->update(['status' => 'processing']);

            return ['success' => true, 'payout' => $payout];
        });
    }

    /**
     * Complete a payout once the payment provider confirms funds were sent.
     * processing → completed
     */
    public function completePayout(Payout $payout, array $data): array
    {
        return DB::transaction(function () use ($payout, $data) {
            if ($payout->status !== 'processing') {
                throw new \Exception(
                    'Only processing payouts can be completed (current: ' . $payout->status . ').'
                );
            }

            $notes = $payout->notes;
            if (!empty($data['notes'])) {
                $notes = $notes
                    ? $notes . "\n\nAdmin notes:\n" . $data['notes']
                    : $data['notes'];
            }

            $payout->update([
                'status'         => 'completed',
                'transaction_id' => $data['transaction_reference'] ?? null,
                'processed_at'   => now(),
                'notes'          => $notes,
            ]);

            return [
                'success' => true,
                'message' => 'Payout completed successfully.',
                'payout'  => $payout,
            ];
        });
    }

    /**
     * Fail/cancel a payout and return the full amount to the seller's wallet.
     *
     * Handles both:
     *   - Seller-initiated cancel (status=pending)
     *   - Admin-initiated fail    (status=pending or processing)
     *
     * What happens to the wallet:
     *   balance          +amount   (funds returned to available)
     *   total_withdrawn  -amount   (lifetime stat corrected)
     *   original debit tx → status=reversed
     *
     * Status check is done BEFORE opening the transaction (fail fast).
     */
    public function failPayout(Payout $payout, string $reason): array
    {
        if (!in_array($payout->status, ['pending', 'processing'])) {
            throw new \Exception(
                'Cannot reverse a payout with status "' . $payout->status . '".'
            );
        }

        return DB::transaction(function () use ($payout, $reason) {
            $wallet = $this->getWallet($payout->seller);

            // Return funds — updateTotalEarned=false because this is a reversal.
            $wallet->credit(
                $payout->amount,
                'payout_refund',
                $payout,
                'Payout reversed: ' . $reason,
                ['original_payout_id' => $payout->id],
                false
            );

            // Correct the total_withdrawn counter.
            $wallet->reverseWithdrawal($payout->amount);

            // Mark the original debit as reversed for audit trail clarity.
            if ($payout->walletTransaction) {
                $payout->walletTransaction->update(['status' => 'reversed']);
            }

            $payout->update([
                'status'         => 'failed',
                'failure_reason' => $reason,
                'failed_at'      => now(),
            ]);

            return [
                'success' => true,
                'message' => '₦' . number_format($payout->amount, 2) .
                             ' returned to seller wallet.',
                'payout'  => $payout,
            ];
        });
    }

    /** Backwards-compatible alias. */
    public function markPayoutAsFailed(Payout $payout, string $reason): array
    {
        return $this->failPayout($payout, $reason);
    }

    // =========================================================================
    // REFUNDS
    // =========================================================================

    /**
     * Deduct a customer refund from a seller's available balance.
     * Creates a negative balance (overdraft) if the seller doesn't have enough.
     *
     * FIX applied: the original overdraft path subtracted refundAmount twice
     * from balance_after. Corrected by computing $newBalance once.
     */
    public function processRefund(Order $order, int $sellerId, float $refundAmount): SellerWalletTransaction
    {
        $seller = Seller::findOrFail($sellerId);
        $wallet = $this->getWallet($seller);

        return DB::transaction(function () use ($wallet, $seller, $order, $refundAmount) {
            if ($wallet->balance >= $refundAmount) {
                return $wallet->debit(
                    $refundAmount,
                    'refund',
                    $order,
                    "Customer refund for Order #{$order->id}",
                    ['order_id' => $order->id]
                );
            }

            // Overdraft — compute once, use in both the update and the record.
            $balanceBefore = $wallet->balance;
            $newBalance    = $balanceBefore - $refundAmount;  // intentionally negative

            $wallet->update(['balance' => $newBalance]);

            return $wallet->transactions()->create([
                'seller_id'         => $seller->id,
                'wallet_id'         => $wallet->id,   // ← required for relationship
                'type'              => 'debit',
                'source'            => 'refund',
                'amount'            => $refundAmount,
                'balance_before'    => $balanceBefore,
                'balance_after'     => $newBalance,
                'transactable_type' => Order::class,
                'transactable_id'   => $order->id,
                'transaction_id'    => null,
                'description'       => "Customer refund for Order #{$order->id} [overdraft]",
                'metadata'          => ['order_id' => $order->id, 'overdraft' => true],
                'status'            => 'completed',
            ]);
        });
    }

    // =========================================================================
    // HELPERS / REPORTING
    // =========================================================================

    protected function calculatePayoutFee(float $amount, string $method): float
    {
        $fees = [
            'bank_transfer'   => 0.0,
            'online_transfer' => 0.0,
            'paypal'          => round($amount * 0.029 + 0.30, 2),
            'stripe'          => round($amount * 0.025, 2),
        ];

        return $fees[$method] ?? 0.0;
    }

    public function getWalletSummary(Seller $seller): array
    {
        $wallet = $this->getWallet($seller);

        return [
            'balance'              => $wallet->balance,
            'pending_balance'      => $wallet->pending_balance,
            'reserved_balance'     => $wallet->reserved_balance,
            'total_balance'        => $wallet->total_balance,
            'total_earned'         => $wallet->total_earned,
            'total_withdrawn'      => $wallet->total_withdrawn,
            'available_for_payout' => $wallet->available_for_payout,
            'last_transaction_at'  => $wallet->last_transaction_at,
            'has_negative_balance' => $wallet->hasNegativeBalance(),
        ];
    }

    public function getPayoutFeePreview(float $amount, string $method): array
    {
        $feeAmount     = $this->calculatePayoutFee($amount, $method);
        $netAmount     = $amount - $feeAmount;
        $feePercentage = $amount > 0 ? round(($feeAmount / $amount) * 100, 2) : 0;

        return [
            'amount'         => $amount,
            'fee_amount'     => $feeAmount,
            'fee_percentage' => $feePercentage,
            'net_amount'     => $netAmount,
            'method'         => $method,
        ];
    }
}

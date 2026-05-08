<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payout;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialService
{
    /**
     * Calculate seller earnings from order
     */
    public function calculateSellerEarnings(Order $order, Seller $seller): float
    {
        $sellerItems = $order->items()->where('seller_id', $seller->id)->get();
        $subtotal = $sellerItems->sum('total_price');
        
        // Deduct platform commission (default 10%)
        $commissionRate = $seller->commission_rate ?? 0.10;
        $commission = $subtotal * $commissionRate;
        
        return $subtotal - $commission;
    }

    /**
     * Credit seller wallet after successful order
     */
    public function creditSellerFromOrder(Order $order, Seller $seller): ?SellerWalletTransaction
    {
        try {
            $earnings = $this->calculateSellerEarnings($order, $seller);
            
            if ($earnings <= 0) {
                return null;
            }

            $wallet = $seller->wallet ?? $seller->getOrCreateWallet();
            
            // Get hold period from settings (default 7 days)
            $holdDays = $seller->payoutSettings?->hold_period_days ?? 7;
            
            if ($holdDays > 0) {
                // Add to pending balance
                $wallet->addPending($earnings);
                
                // Schedule release after hold period
                // This would be handled by a scheduled job
                
                return null;
            } else {
                // Credit directly to available balance
                return $wallet->credit(
                    $earnings,
                    'order_sale',
                    $order,
                    "Earnings from Order #{$order->order_number}",
                    [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'commission_rate' => $seller->commission_rate ?? 0.10
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to credit seller from order', [
                'order_id' => $order->id,
                'seller_id' => $seller->id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Process refund and deduct from seller wallet
     */
    public function processRefund(Order $order, float $refundAmount, string $reason): array
    {
        try {
            DB::beginTransaction();

            $results = [];

            // Calculate proportional refund for each seller
            foreach ($order->items->groupBy('seller_id') as $sellerId => $sellerItems) {
                $sellerTotal = $sellerItems->sum('total_price');
                $sellerRefund = ($sellerTotal / $order->subtotal) * $refundAmount;

                if ($sellerRefund > 0) {
                    $seller = Seller::find($sellerId);
                    
                    if ($seller && $seller->wallet) {
                        // Try to debit available balance first
                        if ($seller->wallet->balance >= $sellerRefund) {
                            $transaction = $seller->wallet->debit(
                                $sellerRefund,
                                'refund',
                                $order,
                                "Refund for Order #{$order->order_number}: {$reason}",
                                [
                                    'order_id' => $order->id,
                                    'refund_reason' => $reason,
                                    'original_amount' => $sellerTotal
                                ]
                            );

                            $results[$sellerId] = [
                                'success' => true,
                                'amount' => $sellerRefund,
                                'transaction_id' => $transaction->id
                            ];
                        } else {
                            // Reserve from pending if available balance is insufficient
                            $results[$sellerId] = [
                                'success' => false,
                                'amount' => $sellerRefund,
                                'error' => 'Insufficient balance'
                            ];
                        }
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'results' => $results,
                'total_refunded' => $refundAmount
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Refund processing failed', [
                'order_id' => $order->id,
                'refund_amount' => $refundAmount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Release pending balance after hold period
     */
    public function releasePendingBalance(SellerWallet $wallet, ?float $amount = null): bool
    {
        try {
            $releaseAmount = $amount ?? $wallet->pending_balance;

            if ($releaseAmount <= 0 || $releaseAmount > $wallet->pending_balance) {
                return false;
            }

            $wallet->releasePending($releaseAmount);

            activity()
                ->performedOn($wallet)
                ->log("Released ₦{$releaseAmount} from pending to available balance");

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to release pending balance', [
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get financial summary for date range
     */
    public function getFinancialSummary($dateFrom, $dateTo): array
    {
        return [
            'revenue' => [
                'total_orders' => Order::whereBetween('created_at', [$dateFrom, $dateTo])->sum('total'),
                'total_paid' => Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('total'),
                'total_refunded' => Order::where('payment_status', 'refunded')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum('total'),
            ],
            'commissions' => [
                'total' => Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->sum(DB::raw('total * 0.10')),
            ],
            'payouts' => [
                'requested' => Payout::whereBetween('requested_at', [$dateFrom, $dateTo])->sum('amount'),
                'paid' => Payout::where('status', 'completed')
                    ->whereBetween('processed_at', [$dateFrom, $dateTo])
                    ->sum('amount'),
                'pending' => Payout::where('status', 'pending')->sum('amount'),
            ],
            'wallets' => [
                'total_balance' => SellerWallet::sum('balance'),
                'total_pending' => SellerWallet::sum('pending_balance'),
                'total_reserved' => SellerWallet::sum('reserved_balance'),
            ]
        ];
    }

    /**
     * Validate payout request
     */
    public function validatePayoutRequest(Seller $seller, float $amount): array
    {
        $wallet = $seller->wallet;

        if (!$wallet) {
            return [
                'valid' => false,
                'error' => 'No wallet found for this seller'
            ];
        }

        if ($amount <= 0) {
            return [
                'valid' => false,
                'error' => 'Payout amount must be greater than zero'
            ];
        }

        $minimumPayout = $seller->payoutSettings?->minimum_payout ?? 10.00;

        if ($amount < $minimumPayout) {
            return [
                'valid' => false,
                'error' => "Minimum payout amount is ₦{$minimumPayout}"
            ];
        }

        if ($amount > $wallet->balance) {
            return [
                'valid' => false,
                'error' => 'Insufficient available balance'
            ];
        }

        return [
            'valid' => true,
            'wallet' => $wallet
        ];
    }

    /**
     * Calculate platform revenue
     */
    public function calculatePlatformRevenue($dateFrom, $dateTo): array
    {
        $orderCommissions = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->sum(DB::raw('total * 0.10'));

        $deliveryCommissions = DB::table('deliveries')
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [$dateFrom, $dateTo])
            ->sum(DB::raw('delivery_fee * 0.10'));

        return [
            'order_commissions' => $orderCommissions,
            'delivery_commissions' => $deliveryCommissions,
            'total' => $orderCommissions + $deliveryCommissions
        ];
    }
}
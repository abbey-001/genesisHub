<?php

namespace App\Services;

use App\Models\DeliveryPayout;
use App\Models\Rider;
use App\Models\Delivery;
use App\Models\User;
use App\Models\Admin;
use App\Notifications\RiderPayoutRequested;
use App\Notifications\RiderPayoutApproved;
use App\Notifications\RiderPayoutPaid;
use App\Notifications\RiderPayoutRejected;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    /**
     * Calculate available balance for a company
     */
    public function calculateAvailableBalance(Rider $company)
    {
        $totalEarnings = $company->deliveries()
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        // Paid + approved = already committed
        $totalPaidOut = DeliveryPayout::where('rider_id', $company->id)
            ->whereIn('status', ['paid', 'approved'])
            ->sum('amount');

        // Pending = in-flight requests
        $totalPending = DeliveryPayout::where('rider_id', $company->id)
            ->where('status', 'pending')
            ->sum('amount');

        return [
            'total_earnings'    => $totalEarnings,
            'total_paid_out'    => $totalPaidOut,
            'total_pending'     => $totalPending,
            'available_balance' => $totalEarnings - $totalPaidOut - $totalPending,
        ];
    }

    /**
     * Get unpaid deliveries for a company (not yet included in any payout)
     */
    public function getUnpaidDeliveries(Rider $company)
    {
        $paidDeliveryIds = DB::table('payout_deliveries')
            ->join('delivery_payouts', 'payout_deliveries.payout_id', '=', 'delivery_payouts.id')
            ->where('delivery_payouts.rider_id', $company->id)
            ->whereIn('delivery_payouts.status', ['paid', 'approved', 'pending'])
            ->pluck('payout_deliveries.delivery_id');

        return $company->deliveries()
            ->where('status', 'delivered')
            ->whereNotIn('id', $paidDeliveryIds)
            ->with('order')
            ->latest('delivered_at')
            ->get();
    }

    /**
     * Create a payout request for a company
     */
    public function createPayoutRequest(Rider $company, $amount, array $deliveryIds = null)
    {
        $balance = $this->calculateAvailableBalance($company);

        if ($amount > $balance['available_balance']) {
            throw new \Exception('Requested amount exceeds available balance');
        }

        if ($amount < 1000) {
            throw new \Exception('Minimum payout amount is ₦1,000');
        }

        if (!$company->bank_name || !$company->account_number || !$company->account_name) {
            throw new \Exception('Please update your bank details before requesting a payout');
        }

        DB::beginTransaction();
        try {
            if (!$deliveryIds) {
                $unpaidDeliveries = $this->getUnpaidDeliveries($company);
                $deliveryIds  = [];
                $totalAmount  = 0;

                foreach ($unpaidDeliveries as $delivery) {
                    if ($totalAmount + $delivery->delivery_fee <= $amount) {
                        $deliveryIds[] = $delivery->id;
                        $totalAmount  += $delivery->delivery_fee;
                    } else {
                        break;
                    }
                }

                $amount = $totalAmount;
            }

            $deliveries = Delivery::whereIn('id', $deliveryIds)->get();
            $periodFrom = $deliveries->min('delivered_at');
            $periodTo   = $deliveries->max('delivered_at');

            $payout = DeliveryPayout::create([
                'rider_id'         => $company->id,
                'reference_number' => DeliveryPayout::generateReference(),
                'amount'           => $amount,
                'status'           => 'pending',
                'requested_at'     => now(),
                'bank_name'        => $company->bank_name,
                'account_number'   => $company->account_number,
                'account_name'     => $company->account_name,
                'deliveries_count' => count($deliveryIds),
                'period_from'      => $periodFrom,
                'period_to'        => $periodTo,
            ]);

            $payout->deliveries()->attach($deliveryIds);

            // Notify the company that their request was received
            try {
                $payout->company->user?->notify(new RiderPayoutRequested($payout));
            } catch (\Exception $e) {
                Log::warning('RiderPayoutRequested notification failed: ' . $e->getMessage());
            }

            // Notify all admins
            try {
                $admins = Admin::all();
                foreach ($admins as $admin) {
                    $admin->notify(new RiderPayoutRequested($payout));
                }
            } catch (\Exception $e) {
                Log::warning('Admin RiderPayoutRequested notification failed: ' . $e->getMessage());
            }

            try {
                app(\App\Services\Telegram\AdminTelegramService::class)
                    ->notifyNewRiderPayoutRequest($payout);
            } catch (\Exception $e) {
                Log::warning('Admin Telegram rider payout alert failed', [
                    'payout_id' => $payout->id,
                    'error'     => $e->getMessage(),
                ]);
            }

            DB::commit();
            return $payout;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve payout request (pending → approved)
     */
    public function approvePayout(DeliveryPayout $payout, Admin $admin, $notes = null)
    {
        if (!$payout->canApprove()) {
            throw new \Exception('This payout cannot be approved (status: ' . $payout->status . ')');
        }

        DB::beginTransaction();
        try {
            // DeliveryPayout::approve() handles status update + timestamps
            $payout->approve($admin, $notes);

            DB::commit();

            // Notify company — use RiderPayoutApproved (not PayoutApproved)
            try {
                $payout->company->user?->notify(new RiderPayoutApproved($payout));
            } catch (\Exception $e) {
                Log::warning('RiderPayoutApproved notification failed: ' . $e->getMessage());
            }

            return $payout;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mark payout as paid (approved → paid)
     */
    public function markAsPaid(DeliveryPayout $payout, Admin $admin, $transactionRef, $paymentMethod = 'bank_transfer')
    {
        if (!$payout->canPay()) {
            throw new \Exception('This payout cannot be marked as paid (status: ' . $payout->status . ')');
        }

        DB::beginTransaction();
        try {
            // DeliveryPayout::markAsPaid() handles batch creation, delivery updates, etc.
            $payout->markAsPaid($admin, $transactionRef, $paymentMethod);

            DB::commit();

            // Notify company — use RiderPayoutPaid (not PayoutPaid)
            try {
                $payout->company->user?->notify(new RiderPayoutPaid($payout));
            } catch (\Exception $e) {
                Log::warning('RiderPayoutPaid notification failed: ' . $e->getMessage());
            }

            return $payout;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject payout request
     */
    public function rejectPayout(DeliveryPayout $payout, Admin $admin, $reason)
    {
        if (!$payout->canReject()) {
            throw new \Exception('This payout cannot be rejected (status: ' . $payout->status . ')');
        }

        DB::beginTransaction();
        try {
            // DeliveryPayout::reject() handles status update + timestamps
            $payout->reject($admin, $reason);

            DB::commit();

            // Notify company — use RiderPayoutRejected (not PayoutRejected)
            try {
                $payout->company->user?->notify(new RiderPayoutRejected($payout));
            } catch (\Exception $e) {
                Log::warning('RiderPayoutRejected notification failed: ' . $e->getMessage());
            }

            return $payout;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get payout statistics.
     * Pass a $company to scope to a single company, or null for platform-wide stats.
     */
    public function getPayoutStats(Rider $company = null)
    {
        $query = DeliveryPayout::query();

        if ($company) {
            $query->where('rider_id', $company->id);
        }

        return [
            'total_paid'     => (clone $query)->where('status', 'paid')->sum('amount'),
            'total_pending'  => (clone $query)->where('status', 'pending')->sum('amount'),
            'total_approved' => (clone $query)->where('status', 'approved')->sum('amount'),
            'count_paid'     => (clone $query)->where('status', 'paid')->count(),
            'count_pending'  => (clone $query)->where('status', 'pending')->count(),
            'count_approved' => (clone $query)->where('status', 'approved')->count(),
            'count_rejected' => (clone $query)->where('status', 'rejected')->count(),
        ];
    }

    /**
     * Generate payout report (paid payouts within an optional date range)
     */
    public function generatePayoutReport($startDate = null, $endDate = null)
    {
        $query = DeliveryPayout::with(['company', 'approvedBy', 'paidBy'])
            ->where('status', 'paid');

        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('paid_at', '<=', $endDate);
        }

        return $query->orderBy('paid_at', 'desc')->get();
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Notifications\SellerPayoutRequested;

class PayoutController extends Controller
{
    protected SellerWalletService $walletService;

    public function __construct(SellerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Wallet overview + payout history.
     */
    public function index()
    {
        $seller = Auth::guard('seller')->user()->seller;

        $walletSummary = $this->walletService->getWalletSummary($seller);

        $payouts = Payout::where('seller_id', $seller->id)
            ->with('walletTransaction')
            ->latest('requested_at')
            ->paginate(20);

        $settings = $seller->payoutSettings ?? (object) [
            'minimum_payout'        => 10.00,
            'preferred_method'      => 'bank_transfer',
            'payout_schedule'       => 'manual',
            'hold_period_days'      => 7,
            'auto_payout_enabled'   => false,
            'auto_payout_threshold' => null,
            'payout_day'            => null,
        ];

        return view('seller.payouts.index', compact('payouts', 'walletSummary', 'settings'));
    }

    /**
     * Submit a new withdrawal request.
     */
    public function request(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;

        // Resolve the seller's minimum so we can validate against it here.
        $minimumPayout = $seller->payoutSettings?->minimum_payout ?? 10.00;

        $validated = $request->validate([
            'amount'        => ['required', 'numeric', "min:{$minimumPayout}"],
            'payout_method' => ['required', 'in:bank_transfer,paypal,stripe'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $payout = $this->walletService->requestPayout($seller, $validated);

            $message = "Withdrawal request submitted! Reference #{$payout->id}.";

            if ($payout->fee_amount > 0) {
                $message .= " You will receive ₦" . number_format($payout->net_amount, 2)
                          . " after the ₦" . number_format($payout->fee_amount, 2) . " processing fee.";
            }

            // Fire notification in a separate try/catch so a failed email
            // never rolls back an already-committed payout record.
            try {
                Auth::guard('seller')->user()->notify(new SellerPayoutRequested($payout));
            } catch (\Exception $notifyEx) {
                Log::warning('SellerPayoutRequested notification failed', [
                    'payout_id' => $payout->id,
                    'error'     => $notifyEx->getMessage(),
                ]);
            }
            try {
                 app(\App\Services\Telegram\AdminTelegramService::class)
                      ->notifyNewPayoutRequest($payout);
              } catch (\Exception $e) {
                  \Log::warning('Admin Telegram payout alert failed', ['error' => $e->getMessage()]);
              }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Show a single payout's detail page.
     */
    public function show(Payout $payout)
    {
        $this->authorize('view', $payout);

        $payout->load('walletTransaction', 'seller');

        return view('seller.payouts.show', compact('payout'));
    }

    /**
     * Full transaction history with filters.
     */
    public function transactions(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $wallet = $this->walletService->getWallet($seller);

        $query = $wallet->transactions()->with('transactable');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(50);

        $sources = $wallet->transactions()
            ->select('source')
            ->distinct()
            ->pluck('source')
            ->toArray();

        return view('seller.payouts.transactions', compact('transactions', 'wallet', 'sources'));
    }

    /**
     * Payout settings page.
     */
    public function settings()
    {
        $seller = Auth::guard('seller')->user()->seller;

        $settings = $seller->payoutSettings ?? (object) [
            'minimum_payout'        => 10.00,
            'preferred_method'      => 'bank_transfer',
            'payout_schedule'       => 'manual',
            'hold_period_days'      => 7,
            'auto_payout_enabled'   => false,
            'auto_payout_threshold' => null,
            'payout_day'            => null,
        ];

        return view('seller.payouts.settings', compact('settings'));
    }

    /**
     * Save payout settings.
     */
    public function updateSettings(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;

        $validated = $request->validate([
            'minimum_payout'        => 'required|numeric|min:10',
            'preferred_method'      => 'required|in:bank_transfer,paypal,stripe',
            'payout_schedule'       => 'required|in:manual,weekly,biweekly,monthly',
            'payout_day'            => 'nullable|integer|min:0|max:31',
            'auto_payout_enabled'   => 'boolean',
            'auto_payout_threshold' => 'nullable|numeric|min:10',
            'hold_period_days'      => 'required|integer|min:0|max:30',
        ]);

        $validated['auto_payout_enabled'] = $request->has('auto_payout_enabled');

        $seller->payoutSettings()->updateOrCreate(
            ['seller_id' => $seller->id],
            $validated
        );

        return back()->with('success', 'Payout settings updated successfully.');
    }

    /**
     * Cancel a pending payout (seller action).
     */
    public function cancel(Payout $payout)
    {
        $this->authorize('cancel', $payout);

        if (!$payout->canBeCancelled()) {
            return back()->with('error', 'Only pending payouts can be cancelled.');
        }

        try {
            $result = $this->walletService->failPayout($payout, 'Cancelled by seller');

            return back()->with('success', $result['message']);

        } catch (\Exception $e) {
            return back()->with('error', 'Could not cancel payout: ' . $e->getMessage());
        }
    }

    /**
     * Export transactions as CSV.
     */
    public function exportTransactions(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $wallet = $this->walletService->getWallet($seller);

        $query = $wallet->transactions()->with('transactable');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->get();
        $filename     = 'transactions_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($transactions) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date', 'Time', 'Type', 'Source', 'Description',
                'Amount', 'Balance Before', 'Balance After', 'Status',
            ]);

            foreach ($transactions as $tx) {
                fputcsv($file, [
                    $tx->created_at->format('Y-m-d'),
                    $tx->created_at->format('H:i:s'),
                    ucfirst($tx->type),
                    $tx->source_label,
                    $tx->description,
                    $tx->formatted_amount,
                    number_format($tx->balance_before, 2),
                    number_format($tx->balance_after, 2),
                    ucfirst($tx->status),
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * AJAX — return fee breakdown for a given amount + method.
     */
    public function getFeePreview(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:bank_transfer,paypal,stripe',
        ]);

        return response()->json(
            $this->walletService->getPayoutFeePreview(
                (float) $request->amount,
                $request->method
            )
        );
    }
}
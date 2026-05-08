<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Seller;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\PayoutApproved;
use App\Notifications\PayoutPaid;

class PayoutController extends Controller
{
    protected $walletService;

    public function __construct(SellerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Payout queue
     */
    public function index(Request $request)
    {
        $status   = $request->get('status');        // no default — show all by default
        $sellerId = $request->get('seller_id');
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $query = Payout::with(['seller.user', 'seller.shop', 'seller.wallet'])
            ->latest('requested_at');

        if ($status) {
            $query->where('status', $status);
        }

        if ($sellerId) {
            $query->where('seller_id', $sellerId);
        }

        if ($dateFrom) {
            $query->whereDate('requested_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('requested_at', '<=', $dateTo);
        }

        $payouts = $query->paginate(20);

        // Stats — keys match the blade exactly
        $stats = [
            'pending'               => Payout::where('status', 'pending')->count(),
            'pending_amount'        => Payout::where('status', 'pending')->sum('amount'),
            'processing'            => Payout::where('status', 'processing')->count(),
            'processing_amount'     => Payout::where('status', 'processing')->sum('amount'),
            'completed_today'       => Payout::where('status', 'completed')
                                          ->whereDate('processed_at', today())->count(),
            'completed_amount_today'=> Payout::where('status', 'completed')
                                          ->whereDate('processed_at', today())->sum('amount'),
            'failed'                => Payout::where('status', 'failed')->count(),
        ];

        return view('admin.finance.payouts.index', compact('payouts', 'stats'));
    }

    /**
     * Show payout details
     */
    public function show(Payout $payout)
    {
        $payout->load([
            'seller.user',
            'seller.shop',
            'seller.wallet',
            'walletTransaction',
        ]);

        // Fresh query — not from relation cache
        $recentTransactions = $payout->seller->walletTransactions()
            ->latest()
            ->take(10)
            ->get();

        return view('admin.finance.payouts.show', compact('payout', 'recentTransactions'));
    }

    /**
     * Approve payout (pending → processing)
     */
    public function approve(Request $request, Payout $payout)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        if ($payout->status !== 'pending') {
            return back()->with('error', 'Only pending payouts can be approved.');
        }

        try {
            DB::beginTransaction();

            $payout->update([
                'status'       => 'processing',
                'notes'        => $request->notes,
                'processed_at' => now(),
            ]);

            DB::commit();
            
             $seller = $payout->seller;
             if ($seller->telegram_chat_id) {
                 app(\App\Services\Telegram\SellerTelegramService::class)
                     ->notifyPayoutApproved($seller, $payout);
             }

            try {
                $payout->seller->user?->notify(new PayoutApproved($payout));
            } catch (\Exception $e) {
                \Log::warning('PayoutApproved notification failed: ' . $e->getMessage());
            }

            return redirect()
                ->route('admin.finance.payouts.show', $payout)
                ->with('success', 'Payout approved! Please process the bank transfer and mark as completed.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Mark payout as completed (processing → completed).
     * Uses walletService->completePayout() which sets status = 'completed'.
     */
    public function complete(Request $request, Payout $payout)
    {
        $request->validate([
            'transaction_reference' => 'required|string|max:255',
            'notes'                 => 'nullable|string|max:500',
        ]);

        if ($payout->status !== 'processing') {
            return back()->with('error', 'Only processing payouts can be marked as completed.');
        }

        try {
            $result = $this->walletService->completePayout($payout, [
                'transaction_reference' => $request->transaction_reference,
                'notes'                 => $request->notes,
                'completed_by'          => auth()->guard('admin')->id(),
            ]);

            if ($result['success']) {
                try {
                    $payout->seller->user?->notify(new PayoutPaid($payout));
                } catch (\Exception $e) {
                    \Log::warning('PayoutPaid notification failed: ' . $e->getMessage());
                }
                
                 $seller = $payout->seller;
                 if ($seller->telegram_chat_id) {
                     app(\App\Services\Telegram\SellerTelegramService::class)
                         ->notifyPayoutCompleted($seller, $payout);
                 }

                return redirect()
                    ->route('admin.finance.payouts.index')
                    ->with('success', 'Payout marked as completed successfully!');
            }

            return back()->with('error', $result['message'] ?? 'Completion failed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Completion failed: ' . $e->getMessage());
        }
    }

    /**
     * Reject payout — returns funds to seller wallet via failPayout()
     */
    public function reject(Request $request, Payout $payout)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!in_array($payout->status, ['pending', 'processing'])) {
            return back()->with('error', 'This payout cannot be rejected.');
        }

        try {
            $result = $this->walletService->failPayout($payout, $request->reason);

            if ($result['success']) {
                return back()->with('success', 'Payout rejected and ₦' . number_format($payout->amount, 2) . ' returned to seller wallet.');
            }
            $seller = $payout->seller;
             if ($seller->telegram_chat_id) {
                  app(\App\Services\Telegram\SellerTelegramService::class)
                      ->notifyPayoutRejected($seller, $payout);
             }

            return back()->with('error', $result['message'] ?? 'Rejection failed.');

        } catch (\Exception $e) {
            return back()->with('error', 'Rejection failed: ' . $e->getMessage());
        }
    }

    /**
     * Bulk approve pending payouts (→ processing)
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payout_ids'   => 'required|array|min:1',
            'payout_ids.*' => 'exists:payouts,id',
        ]);

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($request->payout_ids as $payoutId) {
                $payout = Payout::find($payoutId);

                if ($payout && $payout->status === 'pending') {
                    $payout->update([
                        'status'       => 'processing',
                        'processed_at' => now(),
                    ]);

                    try {
                        $payout->seller->user?->notify(new PayoutApproved($payout));
                    } catch (\Exception $e) {
                        \Log::warning('Bulk PayoutApproved notification failed: ' . $e->getMessage());
                    }

                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Approved {$count} payout(s) for processing.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Bulk approval failed: ' . $e->getMessage());
        }
    }

    /**
     * Export payouts to CSV
     */
    public function export(Request $request)
    {
        $query = Payout::with(['seller.user', 'seller.shop']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('requested_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('requested_at', '<=', $request->date_to);
        }

        $payouts  = $query->latest('requested_at')->get();
        $filename = 'seller_payouts_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($payouts) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Payout ID', 'Seller Name', 'Shop Name',
                'Amount', 'Fee', 'Net Amount',
                'Status', 'Method',
                'Requested At', 'Processed At',
                'Transaction Reference',
            ]);

            foreach ($payouts as $payout) {
                fputcsv($file, [
                    $payout->id,
                    $payout->seller->user->name,
                    $payout->seller->shop->shop_name ?? 'N/A',
                    $payout->amount,
                    $payout->fee_amount ?? 0,
                    $payout->net_amount ?? $payout->amount,
                    $payout->status,
                    $payout->payout_method ?? 'N/A',
                    $payout->requested_at->format('Y-m-d H:i'),
                    $payout->processed_at?->format('Y-m-d H:i') ?? 'N/A',
                    $payout->transaction_id ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Payout analytics
     */
    public function analytics(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->subDays(30)->toDateString());
        $dateTo   = $request->get('date_to', now()->toDateString());

        $dailyPayouts = Payout::where('status', 'completed')
            ->whereBetween('processed_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('DATE(processed_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topSellers = Payout::where('status', 'completed')
            ->whereBetween('processed_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->with('seller.user', 'seller.shop')
            ->selectRaw('seller_id, COUNT(*) as payout_count, SUM(amount) as total_amount')
            ->groupBy('seller_id')
            ->orderByDesc('total_amount')
            ->take(10)
            ->get();

        $methodBreakdown = Payout::where('status', 'completed')
            ->whereBetween('processed_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->selectRaw('payout_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payout_method')
            ->get();

        return view('admin.finance.payouts.analytics', compact(
            'dailyPayouts', 'topSellers', 'methodBreakdown', 'dateFrom', 'dateTo'
        ));
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\SellerWalletTransaction;
use App\Services\SellerWalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(SellerWalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * List all seller wallets
     */
    public function index(Request $request)
    {
        

        $search = $request->get('search');
        $minBalance = $request->get('min_balance');
        $maxBalance = $request->get('max_balance');

        $query = SellerWallet::with(['seller.user', 'seller.shop'])
            ->latest();

        if ($search) {
            $query->whereHas('seller.user', function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })->orWhereHas('seller.shop', function($q) use ($search) {
                $q->where('shop_name', 'like', '%' . $search . '%');
            });
        }

        if ($minBalance) {
            $query->where('balance', '>=', $minBalance);
        }

        if ($maxBalance) {
            $query->where('balance', '<=', $maxBalance);
        }

        $wallets = $query->paginate(20);

        // Summary stats
        $stats = [
            'total_wallets' => SellerWallet::count(),
            'total_balance' => SellerWallet::sum('balance'),
            'total_pending' => SellerWallet::sum('pending_balance'),
            'total_reserved' => SellerWallet::sum('reserved_balance'),
            'total_earned' => SellerWallet::sum('total_earned'),
            'total_withdrawn' => SellerWallet::sum('total_withdrawn'),
        ];

        return view('admin.finance.wallets.index', compact('wallets', 'stats'));
    }

    /**
     * Show wallet details
     */
    public function show(SellerWallet $wallet)
    {
        

        $wallet->load(['seller.user', 'seller.shop']);

        // Get transactions
        $transactions = $wallet->transactions()
            ->latest()
            ->paginate(50);

        // Transaction summary
        $transactionStats = [
            'total_credits' => $wallet->transactions()->where('type', 'credit')->sum('amount'),
            'total_debits' => $wallet->transactions()->where('type', 'debit')->sum('amount'),
            'total_reserved' => $wallet->transactions()->where('type', 'reserve')->sum('amount'),
            'total_released' => $wallet->transactions()->where('type', 'release')->sum('amount'),
        ];

        return view('admin.finance.wallets.show', compact('wallet', 'transactions', 'transactionStats'));
    }

    /**
     * Manual adjustment page
     */
    public function adjustPage(SellerWallet $wallet)
    {
        $this->authorize('finance.wallets.adjust');

        $wallet->load(['seller.user', 'seller.shop']);

        return view('admin.finance.wallets.adjust', compact('wallet'));
    }

    /**
     * Process manual adjustment
     */
    public function adjust(Request $request, SellerWallet $wallet)
    {
        $this->authorize('finance.wallets.adjust');

        $request->validate([
            'type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($request->type === 'debit' && $wallet->balance < $request->amount) {
            return back()->with('error', 'Insufficient wallet balance for debit');
        }

        try {
            DB::beginTransaction();

            $description = "Manual {$request->type} by admin: {$request->reason}";
            
            if ($request->type === 'credit') {
                $transaction = $wallet->credit(
                    $request->amount,
                    'manual_adjustment',
                    null,
                    $description,
                    [
                        'reason' => $request->reason,
                        'notes' => $request->notes,
                        'admin_id' => auth()->guard('admin')->id(),
                        'admin_name' => auth()->guard('admin')->user()->name
                    ]
                );
            } else {
                $transaction = $wallet->debit(
                    $request->amount,
                    'manual_adjustment',
                    null,
                    $description,
                    [
                        'reason' => $request->reason,
                        'notes' => $request->notes,
                        'admin_id' => auth()->guard('admin')->id(),
                        'admin_name' => auth()->guard('admin')->user()->name
                    ]
                );
            }

            // Log activity
            activity()
                ->performedOn($wallet)
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'type' => $request->type,
                    'amount' => $request->amount,
                    'reason' => $request->reason
                ])
                ->log("Wallet {$request->type}: ₦{$request->amount}");

            DB::commit();

            return redirect()
                ->route('admin.finance.wallets.show', $wallet)
                ->with('success', "Wallet {$request->type} of ₦" . number_format($request->amount, 2) . " completed successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Adjustment failed: ' . $e->getMessage());
        }
    }

    /**
     * Release pending balance.
     *
     * Creates a wallet transaction record so the admin action appears in the
     * seller's transaction history with a full audit trail.
     */
    public function releasePending(Request $request, SellerWallet $wallet)
    {
        $this->authorize('finance.wallets.adjust');

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $wallet->pending_balance,
            'reason' => 'required|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            // Capture balance before mutation for accurate audit record.
            $balanceBefore = $wallet->balance;

            // Move funds from pending_balance to balance.
            $wallet->releasePending($request->amount);

            // Refresh to get the committed post-save values from the DB.
            $wallet->refresh();

            // Create a transaction record so the movement appears in the
            // seller's history and the admin action has a full audit trail.
            $wallet->transactions()->create([
                'seller_id'      => $wallet->seller_id,
                'type'           => 'release',
                'source'         => 'manual_pending_release',
                'amount'         => $request->amount,
                'balance_before' => $balanceBefore,
                'balance_after'  => $wallet->balance,
                'transaction_id' => null,
                'description'    => 'Admin manual pending release: ' . $request->reason,
                'metadata'       => [
                    'reason'     => $request->reason,
                    'admin_id'   => auth()->guard('admin')->id(),
                    'admin_name' => auth()->guard('admin')->user()->name,
                ],
                'status'         => 'completed',
            ]);

            // Log activity.
            activity()
                ->performedOn($wallet)
                ->causedBy(auth()->guard('admin')->user())
                ->withProperties([
                    'amount' => $request->amount,
                    'reason' => $request->reason
                ])
                ->log("Released ₦{$request->amount} from pending to available balance");

            DB::commit();

            return back()->with('success', "Released ₦" . number_format($request->amount, 2) . " to available balance");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Release failed: ' . $e->getMessage());
        }
    }

    /**
     * Transaction details
     */
    public function transaction(SellerWalletTransaction $transaction)
    {
        

        $transaction->load(['wallet.seller.user', 'wallet.seller.shop', 'transactable']);

        return view('admin.finance.wallets.transaction', compact('transaction'));
    }

    /**
     * Wallet analytics
     */
    public function analytics(Request $request)
    {
        

        $dateFrom = $request->get('date_from', now()->subDays(30));
        $dateTo = $request->get('date_to', now());

        // Daily wallet activity
        $dailyActivity = SellerWalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATE(created_at) as date, type, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date', 'type')
            ->orderBy('date')
            ->get();

        // Top earners
        $topEarners = SellerWallet::with('seller.user', 'seller.shop')
            ->orderByDesc('total_earned')
            ->take(10)
            ->get();

        // Source breakdown
        $sourceBreakdown = SellerWalletTransaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('type', 'credit')
            ->selectRaw('source, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('source')
            ->get();

        return view('admin.finance.wallets.analytics', compact(
            'dailyActivity',
            'topEarners',
            'sourceBreakdown',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Export transactions
     */
    public function exportTransactions(Request $request)
    {
        

        $walletId = $request->get('wallet_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $type = $request->get('type');

        $query = SellerWalletTransaction::with(['wallet.seller.user', 'wallet.seller.shop']);

        if ($walletId) {
            $query->where('wallet_id', $walletId);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->get();

        $filename = 'wallet_transactions_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Transaction ID',
                'Seller Name',
                'Shop Name',
                'Type',
                'Source',
                'Amount',
                'Balance Before',
                'Balance After',
                'Description',
                'Date'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->wallet->seller->user->name,
                    $transaction->wallet->seller->shop->shop_name ?? 'N/A',
                    $transaction->type,
                    $transaction->source,
                    $transaction->amount,
                    $transaction->balance_before,
                    $transaction->balance_after,
                    $transaction->description,
                    $transaction->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
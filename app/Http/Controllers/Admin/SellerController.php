<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Product;
use App\Models\SellerWallet;
use App\Notifications\SellerApproved;
use App\Notifications\SellerRejected;
use App\Notifications\SellerSuspended;
use App\Notifications\SellerReactivated;
use App\Notifications\AdminSellerNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerController extends Controller
{
    /**
     * Display all sellers
     */
    public function index(Request $request)
    {
        $query = Seller::with(['user', 'shop'])
            ->withCount('products');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($q2) use ($search) {
                    $q2->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('shop', function ($q2) use ($search) {
                    $q2->where('shop_name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        $sortBy    = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $sellers = $query->paginate(20);

        $stats = [
            'total'     => Seller::count(),
            'verified'  => Seller::where('verification_status', 'verified')->count(),
            'pending'   => Seller::where('verification_status', 'pending')->count(),
            'rejected'  => Seller::where('verification_status', 'rejected')->count(),
            'suspended' => Seller::where('verification_status', 'suspended')->count(),
            'new_today' => Seller::whereDate('created_at', today())->count(),
        ];

        return view('admin.sellers.index', compact('sellers', 'stats'));
    }

    /**
     * Show pending applications
     */
    public function applications(Request $request)
    {
        $applications = Seller::with(['user', 'shop'])
            ->where('verification_status', 'pending')
            ->latest()
            ->paginate(20);

        return view('admin.sellers.applications', compact('applications'));
    }

    /**
     * Show seller details
     */
    public function show(Seller $seller)
    {
        $seller->load(['user', 'shop', 'products']);

        // Always read wallet fresh from DB — never from the cached relation,
        // which may hold stale balances from a previous eager-load in this request.
        $wallet = SellerWallet::where('seller_id', $seller->id)->first();

        $stats = [
            'total_products'  => $seller->products()->count(),
            'active_products' => $seller->products()->where('products.is_active', true)->count(),
            'total_sales'     => DB::table('order_items')
                ->where('seller_id', $seller->id)
                ->sum('total_price'),
            'total_orders'    => DB::table('order_items')
                ->where('seller_id', $seller->id)
                ->distinct('order_id')
                ->count('order_id'),
            // Pull directly from the freshly-queried wallet row
            'wallet_balance'  => $wallet?->balance ?? 0,
            'pending_balance' => $wallet?->pending_balance ?? 0,
            'total_withdrawn' => $wallet?->total_withdrawn ?? 0,
            'total_earned'    => $wallet?->total_earned ?? 0,
        ];

        $recentProducts = $seller->products()
            ->with('images')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.sellers.show', compact('seller', 'wallet', 'stats', 'recentProducts'));
    }

    /**
     * Approve seller application
     */
    public function approve(Request $request, Seller $seller)
    {
        DB::beginTransaction();
        try {
            $seller->update(['verification_status' => 'verified']);

            if ($seller->shop) {
                $seller->shop->update(['is_active' => true]);
            }

            DB::commit();

            try {
                $seller->user->notify(new SellerApproved());
            } catch (\Exception $e) {
                \Log::warning('SellerApproved notification failed: ' . $e->getMessage());
            }

            return redirect()
                ->route('admin.sellers.index')
                ->with('success', 'Seller application approved! A confirmation email has been sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to approve seller: ' . $e->getMessage());
        }
    }

    /**
     * Reject seller application
     */
    public function reject(Request $request, Seller $seller)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $seller->update(['verification_status' => 'rejected']);

            try {
                $seller->user->notify(new SellerRejected($request->reason ?? ''));
            } catch (\Exception $e) {
                \Log::warning('SellerRejected notification failed: ' . $e->getMessage());
            }

            return redirect()
                ->route('admin.sellers.applications')
                ->with('success', 'Seller application rejected. The seller has been notified.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reject seller: ' . $e->getMessage());
        }
    }

    /**
     * Suspend seller
     */
    public function suspend(Request $request, Seller $seller)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $seller->update(['verification_status' => 'suspended']);

            if ($seller->shop) {
                $seller->shop->update(['is_active' => false]);
            }

            $seller->products()->update(['is_active' => false]);

            DB::commit();
            
            try {
                 app(\App\Services\Telegram\SellerTelegramService::class)
                     ->notifyShopSuspended($seller, $request->reason);
             } catch (\Exception $e) {
                 \Log::warning('Seller suspend Telegram failed', ['error' => $e->getMessage()]);
            } 

            try {
                $seller->user->notify(new SellerSuspended($request->reason));
            } catch (\Exception $e) {
                \Log::warning('SellerSuspended notification failed: ' . $e->getMessage());
            }

            return back()->with('success', 'Seller has been suspended and notified by email.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to suspend seller: ' . $e->getMessage());
        }
    }

    /**
     * Reactivate seller
     */
    public function activate(Seller $seller)
    {
        DB::beginTransaction();
        try {
            $seller->update(['verification_status' => 'verified']);

            if ($seller->shop) {
                $seller->shop->update(['is_active' => true]);
            }

            DB::commit();
            
            try {
                 app(\App\Services\Telegram\SellerTelegramService::class)
                   ->notifyShopReactivated($seller);
             } catch (\Exception $e) {
                 \Log::warning('Seller reactivate Telegram failed', ['error' => $e->getMessage()]);
              }

            try {
                $seller->user->notify(new SellerReactivated());
            } catch (\Exception $e) {
                \Log::warning('SellerReactivated notification failed: ' . $e->getMessage());
            }

            return back()->with('success', 'Seller has been reactivated and notified by email.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to reactivate seller: ' . $e->getMessage());
        }
    }

    /**
     * Send a custom email notification to a seller
     */
    public function notify(Request $request, Seller $seller)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $admin     = auth()->guard('admin')->user();
        $adminName = $admin?->name ?? config('app.name') . ' Support';

        try {
            $seller->user->notify(
                new AdminSellerNotification(
                    $request->subject,
                    $request->message,
                    $adminName
                )
            );

            return back()->with('success', 'Notification email sent to ' . $seller->user->email . '.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send notification: ' . $e->getMessage());
        }
    }

    /**
     * Update commission rate
     */
    public function updateCommission(Request $request, Seller $seller)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
        ]);

        $seller->update(['commission_rate' => $request->commission_rate]);

        return back()->with('success', 'Commission rate updated to ' . $request->commission_rate . '%.');
    }

    /**
     * View seller's products
     */
    public function products(Seller $seller)
    {
        // Compute counts before paginating so stats reflect the full dataset
        $productStats = [
    'total'        => $seller->products()->count(),
    'active'       => $seller->products()->where('products.is_active', true)->count(),
    'inactive'     => $seller->products()->where('products.is_active', false)->count(),
    'out_of_stock' => $seller->products()->where('products.stock', 0)->count(),
];

        $products = $seller->products()
            ->with('images', 'category')
            ->latest()
            ->paginate(20);

        return view('admin.sellers.products', compact('seller', 'products', 'productStats'));
    }

    /**
     * View seller's wallet
     *
     * Always queries the seller_wallets table directly with a fresh SELECT so
     * the displayed balances are never served from an Eloquent relation cache
     * that may have been populated before a recent payout completed.
     */
    public function wallet(Seller $seller)
    {
        // Direct query — bypasses any cached $seller->wallet relation entirely
        $wallet = SellerWallet::where('seller_id', $seller->id)->first();

        if (!$wallet) {
            return back()->with('error', 'Seller does not have a wallet.');
        }

        // Compute transaction summary directly from the DB for accuracy
        $transactionSummary = [
            'total_credits'  => $wallet->transactions()->where('type', 'credit')->sum('amount'),
            'total_debits'   => $wallet->transactions()->where('type', 'debit')->sum('amount'),
            'total_reserved' => $wallet->transactions()->where('type', 'reserve')->sum('amount'),
            'total_released' => $wallet->transactions()->where('type', 'release')->sum('amount'),
        ];

        $transactions = $wallet->transactions()
            ->latest()
            ->paginate(20);

        return view('admin.sellers.wallet', compact('seller', 'wallet', 'transactions', 'transactionSummary'));
    }

    /**
     * Export sellers to CSV
     */
    public function export(Request $request)
    {
        $query = Seller::with(['user', 'shop']);

        if ($request->filled('status')) {
            $query->where('verification_status', $request->status);
        }

        $sellers = $query->get();

        $filename = 'sellers_' . now()->format('Y-m-d_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sellers) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Name', 'Email', 'Shop Name', 'Status', 'Products', 'Commission Rate', 'Joined']);

            foreach ($sellers as $seller) {
                fputcsv($file, [
                    $seller->id,
                    $seller->user->name,
                    $seller->user->email,
                    $seller->shop->shop_name ?? 'N/A',
                    $seller->verification_status,
                    $seller->products()->count(),
                    $seller->commission_rate . '%',
                    $seller->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('seller')->user();
        $seller = $user->seller;

        if (!$seller || !$seller->shop) {
            return redirect()->route('home')
                ->with('error', 'Seller profile or shop not found.');
        }

        // Use these consistently
        $shopId    = $seller->shop->id;
        $sellerId  = $seller->id;

        // Statistics
        $stats = [
            'total_products'  => Product::where('shop_id', $shopId)->count(),
            'active_products' => Product::where('shop_id', $shopId)->where('is_active', true)->count(),
            
            'total_orders'    => Order::whereHas('items', fn($q) => 
                $q->where('seller_id', $sellerId)
            )->count(),
            
            'pending_orders'  => Order::whereHas('items', fn($q) => 
                $q->where('seller_id', $sellerId)
                  ->where('status', 'pending')
            )->count(),
        ];

        // ✅ FIX: Revenue - Calculate ONLY from seller's items, not entire order total
        $revenue = [
            // Total revenue from all paid orders containing this seller's items
            'total' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.seller_id', $sellerId)
                ->where('orders.payment_status', 'paid')
                ->sum('order_items.total_price'),

            // This month's revenue from seller's items
            'this_month' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.seller_id', $sellerId)
                ->where('orders.payment_status', 'paid')
                ->whereMonth('orders.created_at', now()->month)
                ->whereYear('orders.created_at', now()->year)
                ->sum('order_items.total_price'),

            // Today's revenue from seller's items
            'today' => DB::table('order_items')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('order_items.seller_id', $sellerId)
                ->where('orders.payment_status', 'paid')
                ->whereDate('orders.created_at', now())
                ->sum('order_items.total_price'),
        ];

        // Recent orders (with only this seller's items)
        $recentOrders = Order::with(['items' => fn($q) => 
            $q->where('seller_id', $sellerId)->with('product')
        ])
        ->whereHas('items', fn($q) => 
            $q->where('seller_id', $sellerId)
        )
        ->latest()
        ->take(10)
        ->get()
        // Add seller-specific total to each order
        ->map(function($order) use ($sellerId) {
            $order->seller_total = $order->items
                ->where('seller_id', $sellerId)
                ->sum('total_price');
            return $order;
        });

        // Top selling products
        $topProducts = Product::where('shop_id', $shopId)
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        // Recent reviews
        $recentReviews = Review::whereHas('product', fn($q) => 
            $q->where('shop_id', $shopId)
        )
        ->with(['product', 'user'])
        ->latest()
        ->take(5)
        ->get();

        // ✅ FIX: Monthly revenue chart - Calculate from seller's items only
        $monthlyRevenue = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('order_items.seller_id', $sellerId)
            ->where('orders.payment_status', 'paid')
            ->whereYear('orders.created_at', now()->year)
            ->select(
                DB::raw('MONTH(orders.created_at) as month'),
                DB::raw('SUM(order_items.total_price) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Low stock products
        $lowStockProducts = Product::where('shop_id', $shopId)
            ->where('stock', '<=', 10)
            ->where('stock', '>', 0)
            ->orderBy('stock')
            ->take(5)
            ->get();

        return view('seller.dashboard', compact(
            'seller',
            'stats',
            'revenue',
            'recentOrders',
            'topProducts',
            'recentReviews',
            'monthlyRevenue',
            'lowStockProducts'
        ));
    }
}
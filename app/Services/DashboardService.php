<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Seller;
use App\Models\Rider;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\Payout;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get dashboard metrics based on admin role
     */
    public function getMetrics($admin)
    {
        $role = optional($admin->role)->name;

        return match($role) {
            'super_admin', 'administrator' => $this->getSuperAdminMetrics(),
            'finance_manager' => $this->getFinanceMetrics(),
            'operations_manager' => $this->getOperationsMetrics(),
            'content_manager' => $this->getContentMetrics(),
            'support_agent' => $this->getSupportMetrics(),
            'analyst' => $this->getAnalystMetrics(),
            default => $this->getBasicMetrics(),
        };
    }

    /**
     * Super Admin / Administrator Metrics
     */
    private function getSuperAdminMetrics()
    {
        return [
            // Revenue Metrics
            'revenue' => [
                'today' => $this->getRevenue('today'),
                'yesterday' => $this->getRevenue('yesterday'),
                'this_week' => $this->getRevenue('this_week'),
                'this_month' => $this->getRevenue('this_month'),
                'all_time' => $this->getRevenue('all_time'),
            ],

            // Order Metrics
            'orders' => [
                'today' => Order::whereDate('created_at', today())->count(),
                'pending' => Order::where('status', 'pending')->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'completed' => Order::where('status', 'delivered')->count(),
                'total' => Order::count(),
            ],

            // User Metrics
            'users' => [
                'customers' => User::where('user_type', 'customer')->count(),
                'sellers' => Seller::count(),
                'riders' => Rider::count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],

            // Delivery Metrics
            'deliveries' => [
                'active' => Delivery::whereIn('status', ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])->count(),
                'pending_assignment' => Delivery::where('status', 'pending')->count(),
                'completed_today' => Delivery::where('status', 'delivered')->whereDate('delivered_at', today())->count(),
                'failed' => Delivery::where('status', 'failed')->count(),
            ],

            // Financial Metrics
            'finance' => [
                'pending_payouts' => Payout::where('status', 'pending')->count(),
                'pending_amount' => Payout::where('status', 'pending')->sum('amount'),
                'processing_payouts' => Payout::where('status', 'processing')->count(),
            ],

            // Product Metrics
            'products' => [
                'total' => Product::count(),
                'pending_approval' => Product::where('is_active', false)->count(),
                'low_stock' => Product::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
                'out_of_stock' => Product::where('stock', 0)->count(),
            ],

            // Seller Metrics
            'sellers' => [
                'pending_approval' => Seller::where('verification_status', 'pending')->count(),
                'active' => Seller::where('is_verified', true)->count(),
            ],

            // Rider Metrics
            'riders' => [
                'pending_approval' => Rider::where('is_verified', false)->count(),
                'online' => Rider::where('status', 'available')->count(),
                'busy' => Rider::where('status', 'busy')->count(),
            ],
        ];
    }

    /**
     * Finance Manager Metrics
     */
    private function getFinanceMetrics()
    {
        return [
            'revenue' => [
                'today' => $this->getRevenue('today'),
                'this_week' => $this->getRevenue('this_week'),
                'this_month' => $this->getRevenue('this_month'),
                'all_time' => $this->getRevenue('all_time'),
            ],

            'payouts' => [
                'pending' => Payout::where('status', 'pending')->count(),
                'pending_amount' => Payout::where('status', 'pending')->sum('amount'),
                'processing' => Payout::where('status', 'processing')->count(),
                'processing_amount' => Payout::where('status', 'processing')->sum('amount'),
                'completed_today' => Payout::where('status', 'completed')->whereDate('processed_at', today())->count(),
                'completed_amount_today' => Payout::where('status', 'completed')->whereDate('processed_at', today())->sum('amount'),
            ],

            'transactions' => [
                'today_count' => Order::whereDate('created_at', today())->count(),
                'today_amount' => Order::whereDate('created_at', today())->sum('total'),
            ],

            'commission' => [
                'today' => $this->getCommission('today'),
                'this_month' => $this->getCommission('this_month'),
            ],
        ];
    }

    /**
     * Operations Manager Metrics
     */
    private function getOperationsMetrics()
    {
        return [
            'deliveries' => [
                'active' => Delivery::whereIn('status', ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])->count(),
                'pending_assignment' => Delivery::where('status', 'pending')->count(),
                'completed_today' => Delivery::where('status', 'delivered')->whereDate('delivered_at', today())->count(),
                'failed_today' => Delivery::where('status', 'failed')->whereDate('failed_at', today())->count(),
                'success_rate' => $this->getDeliverySuccessRate(),
            ],

            'riders' => [
                'online' => Rider::where('status', 'available')->count(),
                'busy' => Rider::where('status', 'busy')->count(),
                'offline' => Rider::where('status', 'offline')->count(),
                'pending_approval' => Rider::where('is_verified', false)->count(),
            ],

            'performance' => [
                'avg_delivery_time' => $this->getAverageDeliveryTime(),
                'on_time_rate' => $this->getOnTimeDeliveryRate(),
            ],
        ];
    }

    /**
     * Content Manager Metrics
     */
    private function getContentMetrics()
    {
        return [
            'products' => [
                'total' => Product::count(),
                'pending_approval' => Product::where('is_active', false)->count(),
                'approved_today' => Product::where('is_active', true)->whereDate('updated_at', today())->count(),
                'low_stock' => Product::where('stock', '<=', 10)->where('stock', '>', 0)->count(),
            ],

            'sellers' => [
                'total' => Seller::count(),
                'pending_approval' => Seller::where('verification_status', 'pending')->count(),
            ],

            'categories' => [
                'total' => DB::table('categories')->count(),
            ],
        ];
    }

    /**
     * Support Agent Metrics
     */
    private function getSupportMetrics()
    {
        return [
            'users' => [
                'total_customers' => User::where('user_type', 'customer')->count(),
                'new_today' => User::whereDate('created_at', today())->count(),
            ],

            'orders' => [
                'total' => Order::count(),
                'today' => Order::whereDate('created_at', today())->count(),
            ],
        ];
    }

    /**
     * Analyst Metrics
     */
    private function getAnalystMetrics()
    {
        return [
            'revenue' => [
                'today' => $this->getRevenue('today'),
                'this_week' => $this->getRevenue('this_week'),
                'this_month' => $this->getRevenue('this_month'),
            ],

            'orders' => [
                'today' => Order::whereDate('created_at', today())->count(),
                'this_week' => Order::whereBetween('created_at', [now()->startOfWeek(), now()])->count(),
                'this_month' => Order::whereMonth('created_at', now()->month)->count(),
            ],

            'users' => [
                'total' => User::count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
            ],
        ];
    }

    /**
     * Basic Metrics (fallback)
     */
    private function getBasicMetrics()
    {
        return [
            'orders' => [
                'total' => Order::count(),
                'today' => Order::whereDate('created_at', today())->count(),
            ],
            'users' => [
                'total' => User::count(),
            ],
        ];
    }

    /**
     * Get revenue for period
     */
    private function getRevenue($period)
    {
        $query = Order::where('payment_status', 'paid');

        return match($period) {
            'today' => $query->whereDate('created_at', today())->sum('total'),
            'yesterday' => $query->whereDate('created_at', today()->subDay())->sum('total'),
            'this_week' => $query->whereBetween('created_at', [now()->startOfWeek(), now()])->sum('total'),
            'this_month' => $query->whereMonth('created_at', now()->month)->sum('total'),
            'all_time' => $query->sum('total'),
            default => 0,
        };
    }

    /**
     * Get commission earned
     */
    private function getCommission($period)
    {
        $query = OrderItem::leftJoin('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->whereHas('order', fn($q) => $q->where('payment_status', 'paid'));

        match($period) {
            'today' => $query->whereDate('order_items.created_at', today()),
            'this_month' => $query->whereMonth('order_items.created_at', now()->month),
            default => null,
        };

        return $query->sum(DB::raw('order_items.total_price * COALESCE(sellers.commission_rate, 10) / 100'));
    }

    /**
     * Get delivery success rate
     */
    private function getDeliverySuccessRate()
    {
        $total = Delivery::whereIn('status', ['delivered', 'failed'])->count();
        if ($total === 0) return 0;

        $successful = Delivery::where('status', 'delivered')->count();
        return round(($successful / $total) * 100, 2);
    }

    /**
     * Get average delivery time (in minutes)
     */
    private function getAverageDeliveryTime()
    {
        $deliveries = Delivery::where('status', 'delivered')
            ->whereNotNull('assigned_at')
            ->whereNotNull('delivered_at')
            ->select(DB::raw('TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at) as duration'))
            ->get();

        if ($deliveries->isEmpty()) return 0;

        return round($deliveries->avg('duration'), 0);
    }

    /**
     * Get on-time delivery rate
     */
    private function getOnTimeDeliveryRate()
    {
        $total = Delivery::where('status', 'delivered')
            ->whereNotNull('estimated_delivery_time')
            ->count();

        if ($total === 0) return 0;

        $onTime = Delivery::where('status', 'delivered')
            ->whereNotNull('estimated_delivery_time')
            ->whereRaw('delivered_at <= estimated_delivery_time')
            ->count();

        return round(($onTime / $total) * 100, 2);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders($limit = 10)
    {
        return Order::with(['user', 'items'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get pending actions based on role
     */
    public function getPendingActions($admin)
    {
        $role = optional($admin->role)->name;
        $actions = [];

        // Super Admin / Administrator
        if (in_array($role, ['super_admin', 'administrator'])) {
            $actions['seller_applications'] = Seller::where('verification_status', 'pending')->count();
            $actions['rider_applications'] = Rider::where('is_verified', false)->count();
            $actions['pending_payouts'] = Payout::where('status', 'pending')->count();
            $actions['failed_deliveries'] = Delivery::where('status', 'failed')->count();
            $actions['pending_products'] = Product::where('is_active', false)->count();
        }

        // Finance Manager
        if ($role === 'finance_manager') {
            $actions['pending_payouts'] = Payout::where('status', 'pending')->count();
            $actions['processing_payouts'] = Payout::where('status', 'processing')->count();
        }

        // Operations Manager
        if ($role === 'operations_manager') {
            $actions['pending_deliveries'] = Delivery::where('status', 'pending')->count();
            $actions['failed_deliveries'] = Delivery::where('status', 'failed')->count();
            $actions['rider_applications'] = Rider::where('is_verified', false)->count();
        }

        // Content Manager
        if ($role === 'content_manager') {
            $actions['pending_products'] = Product::where('is_active', false)->count();
            $actions['seller_applications'] = Seller::where('verification_status', 'pending')->count();
        }

        return $actions;
    }

    /**
     * Get revenue chart data (last 30 days)
     */
    public function getRevenueChartData()
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('M d');
            
            $revenue = Order::where('payment_status', 'paid')
                ->whereDate('created_at', $date)
                ->sum('total');
            
            $data[] = (float) $revenue;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get order volume chart data (last 30 days)
     */
    public function getOrderVolumeChartData()
    {
        $data = [];
        $labels = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $labels[] = $date->format('M d');
            
            $count = Order::whereDate('created_at', $date)->count();
            $data[] = $count;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get top selling products
     */
    public function getTopProducts($limit = 5)
    {
        return Product::orderBy('sold_count', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get top sellers
     */
    public function getTopSellers($limit = 5)
    {
        return Seller::withCount('products')
            ->with('shop')
            ->orderBy('products_count', 'desc')
            ->take($limit)
            ->get();
    }
    
}

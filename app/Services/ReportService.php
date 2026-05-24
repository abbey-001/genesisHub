<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Seller;
use App\Models\Rider;
use App\Models\Delivery;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class ReportService
{
    /**
     * Get date range from request
     */
    protected function getDateRange($params)
    {
        $period = $params['period'] ?? 'this_month';
        
        switch ($period) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'yesterday':
                $start = now()->subDay()->startOfDay();
                $end = now()->subDay()->endOfDay();
                break;
            case 'this_week':
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
                break;
            case 'last_week':
                $start = now()->subWeek()->startOfWeek();
                $end = now()->subWeek()->endOfWeek();
                break;
            case 'this_month':
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
                break;
            case 'last_month':
                $start = now()->subMonth()->startOfMonth();
                $end = now()->subMonth()->endOfMonth();
                break;
            case 'this_year':
                $start = now()->startOfYear();
                $end = now()->endOfYear();
                break;
            case 'custom':
                $start = Carbon::parse($params['date_from'] ?? now()->subMonth());
                $end = Carbon::parse($params['date_to'] ?? now());
                break;
            default:
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
        }
        
        return [$start, $end];
    }
    
    /**
     * Revenue Analytics
     */
    public function getRevenueAnalytics($params = [])
    {
        [$start, $end] = $this->getDateRange($params);
        
        $query = Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$start, $end]);
        
        if (!empty($params['seller_id'])) {
            $query->whereHas('items', fn($q) => $q->where('seller_id', $params['seller_id']));
        }
        
        if (!empty($params['category_id'])) {
            $query->whereHas('items.product', fn($q) => $q->where('category_id', $params['category_id']));
        }
        
        // Summary
        $commissionQuery = $this->commissionItemsQuery($start, $end);

        if (!empty($params['seller_id'])) {
            $commissionQuery->where('order_items.seller_id', $params['seller_id']);
        }

        if (!empty($params['category_id'])) {
            $commissionQuery
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('products.category_id', $params['category_id']);
        }

        $summary = [
            'total_revenue' => $query->sum('total'),
            'total_orders' => $query->count(),
            'avg_order_value' => $query->avg('total'),
            'total_commission' => $commissionQuery->sum(DB::raw('order_items.total_price * COALESCE(sellers.commission_rate, 10) / 100')),
        ];
        
        // Revenue by day
        $revenueByDay = $query->clone()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Top sellers
        $topSellers = OrderItem::whereBetween('created_at', [$start, $end])
            ->select('seller_id', DB::raw('SUM(total_price) as revenue'))
            ->groupBy('seller_id')
            ->orderByDesc('revenue')
            ->limit(10)
            ->with('seller.shop')
            ->get();
        
        // Revenue by category
        $revenueByCategory = OrderItem::whereBetween('order_items.created_at', [$start, $end])
        ->join('products', 'order_items.product_id', '=', 'products.id')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select('categories.name', DB::raw('SUM(order_items.total_price) as revenue'))
        ->groupBy('categories.id', 'categories.name')
        ->orderByDesc('revenue')
        ->get();
    
        
        // Growth comparison
        $previousPeriod = $this->getPreviousPeriodRevenue($start, $end);
        $growth = $summary['total_revenue'] > 0 && $previousPeriod > 0
            ? (($summary['total_revenue'] - $previousPeriod) / $previousPeriod) * 100
            : 0;
        
        return [
            'summary' => $summary,
            'growth' => $growth,
            'revenue_by_day' => $revenueByDay,
            'top_sellers' => $topSellers,
            'revenue_by_category' => $revenueByCategory,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * Sales Analytics
     */
    public function getSalesAnalytics($params = [])
    {
        [$start, $end] = $this->getDateRange($params);
        
        $orders = Order::whereBetween('created_at', [$start, $end]);
        
        $summary = [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->clone()->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->clone()->where('status', 'cancelled')->count(),
            'pending_orders' => $orders->clone()->whereIn('status', ['pending', 'processing'])->count(),
            'avg_order_value' => $orders->clone()->where('payment_status', 'paid')->avg('total'),
            'total_items_sold' => OrderItem::whereBetween('created_at', [$start, $end])->sum('quantity'),
        ];
        
        // Order status breakdown
        $ordersByStatus = $orders->clone()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Sales by hour (for today/yesterday)
        if (in_array($params['period'] ?? '', ['today', 'yesterday'])) {
            $salesByHour = $orders->clone()
                ->select(
                    DB::raw('HOUR(created_at) as hour'),
                    DB::raw('COUNT(*) as orders'),
                    DB::raw('SUM(total) as revenue')
                )
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
        } else {
            $salesByHour = null;
        }
        
        // Top products
        $topProducts = OrderItem::whereBetween('created_at', [$start, $end])
            ->select('product_id', 'product_name', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();
        
        // Conversion funnel
        $totalViews = DB::table('products')
            ->whereBetween('created_at', [$start, $end])
            ->count();
        
        $totalOrders = $summary['total_orders'];
        $conversionRate = $totalViews > 0 ? ($totalOrders / $totalViews) * 100 : 0;
        
        return [
            'summary' => $summary,
            'orders_by_status' => $ordersByStatus,
            'sales_by_hour' => $salesByHour,
            'top_products' => $topProducts,
            'conversion_rate' => $conversionRate,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * User Analytics
     */
    public function getUserAnalytics($params = [])
    {
        [$start, $end] = $this->getDateRange($params);
        
        $userType = $params['user_type'] ?? 'all';
        
        // Customer metrics
        $customerMetrics = [
            'total' => User::where('user_type', 'customer')->count(),
            'new' => User::where('user_type', 'customer')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'active' => User::where('user_type', 'customer')
                ->whereHas('orders', fn($q) => $q->whereBetween('created_at', [$start, $end]))
                ->count(),
        ];
        
        // Seller metrics
        $sellerMetrics = [
            'total' => Seller::count(),
            'new' => Seller::whereBetween('created_at', [$start, $end])->count(),
            'active' =>  Seller::whereHas('products', function ($q) use ($start, $end) {
                $q->whereHas('orderItems', function ($q2) use ($start, $end) {
                    $q2->whereBetween('order_items.created_at', [$start, $end]);
                });
            })->count(),
            'verified' => Seller::where('is_verified', true)->count(),
        ];
        
        // Rider metrics
        $riderMetrics = [
            'total' => Rider::count(),
            'new' => Rider::whereBetween('created_at', [$start, $end])->count(),
            'active' => Rider::whereHas('deliveries', fn($q) => 
                $q->whereBetween('created_at', [$start, $end])
            )->count(),
            'verified' => Rider::where('is_verified', true)->count(),
        ];
        
        // User growth by day
        $userGrowth = User::whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                'user_type',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date', 'user_type')
            ->orderBy('date')
            ->get()
            ->groupBy('user_type');
        
        // Active users trend
        $activeUsersTrend = Order::whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT user_id) as active_users')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Top customers by spend
        $topCustomers = User::where('user_type', 'customer')
            ->withSum(['orders' => fn($q) => 
                $q->where('payment_status', 'paid')
                    ->whereBetween('created_at', [$start, $end])
            ], 'total')
            ->orderByDesc('orders_sum_total')
            ->limit(10)
            ->get();
        
        return [
            'customer_metrics' => $customerMetrics,
            'seller_metrics' => $sellerMetrics,
            'rider_metrics' => $riderMetrics,
            'user_growth' => $userGrowth,
            'active_users_trend' => $activeUsersTrend,
            'top_customers' => $topCustomers,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * Delivery Analytics
     */
    public function getDeliveryAnalytics($params = [])
    {
        [$start, $end] = $this->getDateRange($params);
        
        $query = Delivery::whereBetween('created_at', [$start, $end]);
        
        if (!empty($params['rider_id'])) {
            $query->where('rider_id', $params['rider_id']);
        }
        
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }
        
        // Summary
        $summary = [
            'total_deliveries' => $query->count(),
            'delivered' => $query->clone()->where('status', 'delivered')->count(),
            'failed' => $query->clone()->where('status', 'failed')->count(),
            'pending' => $query->clone()->where('status', 'pending')->count(),
            'in_progress' => $query->clone()->whereIn('status', ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])->count(),
        ];
        
        $summary['success_rate'] = $summary['total_deliveries'] > 0
            ? ($summary['delivered'] / $summary['total_deliveries']) * 100
            : 0;
        
        // Average delivery time
        $avgDeliveryTime = Delivery::whereBetween('created_at', [$start, $end])
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->whereNotNull('assigned_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at)) as avg_time')
            ->value('avg_time');
        
        // Deliveries by status
        $deliveriesByStatus = $query->clone()
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
        
        // Top riders
        $topRiders = Delivery::whereBetween('created_at', [$start, $end])
            ->where('status', 'delivered')
            ->select('rider_id', DB::raw('COUNT(*) as deliveries'), DB::raw('SUM(delivery_fee) as earnings'))
            ->groupBy('rider_id')
            ->orderByDesc('deliveries')
            ->limit(10)
            ->with('rider')
            ->get();
        
        // Failed delivery reasons
        $failureReasons = Delivery::whereBetween('created_at', [$start, $end])
            ->where('status', 'failed')
            ->select('failure_reason', DB::raw('COUNT(*) as count'))
            ->groupBy('failure_reason')
            ->orderByDesc('count')
            ->get();
        
        // Delivery time distribution
        $deliveryTimeDistribution = Delivery::whereBetween('created_at', [$start, $end])
            ->where('status', 'delivered')
            ->whereNotNull('delivered_at')
            ->whereNotNull('assigned_at')
            ->selectRaw('
                CASE
                    WHEN TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at) <= 30 THEN "0-30 mins"
                    WHEN TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at) <= 60 THEN "31-60 mins"
                    WHEN TIMESTAMPDIFF(MINUTE, assigned_at, delivered_at) <= 90 THEN "61-90 mins"
                    ELSE "90+ mins"
                END as time_range,
                COUNT(*) as count
            ')
            ->groupBy('time_range')
            ->get();
        
        return [
            'summary' => $summary,
            'avg_delivery_time' => $avgDeliveryTime,
            'deliveries_by_status' => $deliveriesByStatus,
            'top_riders' => $topRiders,
            'failure_reasons' => $failureReasons,
            'delivery_time_distribution' => $deliveryTimeDistribution,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * Product Analytics
     */
    public function getProductAnalytics($params = [])
    {
        [$start, $end] = $this->getDateRange($params);
        
        $query = Product::query();
        
        if (!empty($params['category_id'])) {
            $query->where('category_id', $params['category_id']);
        }
        
        if (!empty($params['seller_id'])) {
            $query->where('shop_id', $params['seller_id']);
        }
        
        // Summary
        $summary = [
            'total_products' => $query->count(),
            'active_products' => $query->clone()->where('is_active', true)->count(),
            'out_of_stock' => $query->clone()->where('stock', 0)->count(),
            'low_stock' => $query->clone()->where('stock', '>', 0)->where('stock', '<=', 10)->count(),
        ];
        
        // Best sellers
        $sortBy = $params['sort_by'] ?? 'revenue';
        
        $bestSellers = OrderItem::whereBetween('created_at', [$start, $end])
            ->select(
                'product_id',
                'product_name',
                DB::raw('SUM(quantity) as total_sold'),
                DB::raw('SUM(total_price) as revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count')
            )
            ->groupBy('product_id', 'product_name')
            ->orderByDesc($sortBy === 'orders' ? 'order_count' : 'revenue')
            ->limit(20)
            ->with('product.images')
            ->get();
        
        // Products by category
        $productsByCategory = Product::select('category_id')
            ->with('category:id,name')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('category_id')
            ->orderByDesc('count')
            ->get();
        
        // New products
        $newProducts = Product::whereBetween('created_at', [$start, $end])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        // Product ratings
        $avgRating = Product::avg('rating');
        $ratingDistribution = Product::selectRaw('
                CASE
                    WHEN rating >= 4.5 THEN "5 stars"
                    WHEN rating >= 3.5 THEN "4 stars"
                    WHEN rating >= 2.5 THEN "3 stars"
                    WHEN rating >= 1.5 THEN "2 stars"
                    ELSE "1 star"
                END as rating_range,
                COUNT(*) as count
            ')
            ->groupBy('rating_range')
            ->get();
        
        return [
            'summary' => $summary,
            'best_sellers' => $bestSellers,
            'products_by_category' => $productsByCategory,
            'new_products' => $newProducts,
            'avg_rating' => $avgRating,
            'rating_distribution' => $ratingDistribution,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * Commission Analytics
     */
    public function getCommissionAnalytics($params = [])
    {
        [$start, $end] = $this->getDateRange($params);
        
        $query = OrderItem::whereBetween('created_at', [$start, $end]);
        
        if (!empty($params['seller_id'])) {
            $query->where('seller_id', $params['seller_id']);
        }
        
        $commissionBase = $this->commissionItemsQuery($start, $end);
        if (!empty($params['seller_id'])) {
            $commissionBase->where('order_items.seller_id', $params['seller_id']);
        }

        $totalSales = (clone $query)->sum('total_price');
        $totalCommission = $commissionBase->sum(DB::raw('order_items.total_price * COALESCE(sellers.commission_rate, 10) / 100'));

        $summary = [
            'total_sales' => $totalSales,
            'total_commission' => $totalCommission,
            'seller_earnings' => $totalSales - $totalCommission,
        ];
        
        // Commission by seller
        $commissionBySeller = $this->commissionItemsQuery($start, $end)
            ->select('order_items.seller_id', DB::raw('SUM(order_items.total_price) as sales'), DB::raw('SUM(order_items.total_price * COALESCE(sellers.commission_rate, 10) / 100) as commission'))
            ->groupBy('order_items.seller_id')
            ->orderByDesc('commission')
            ->with('seller.shop')
            ->get();
        
        // Commission by category
        $commissionByCategory = OrderItem::whereBetween('created_at', [$start, $end])
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('sellers', 'order_items.seller_id', '=', 'sellers.id')
            ->select('categories.name', DB::raw('SUM(order_items.total_price * COALESCE(sellers.commission_rate, 10) / 100) as commission'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('commission')
            ->get();
        
        // Daily commission
        $dailyCommission = $this->commissionItemsQuery($start, $end)
            ->select(
                DB::raw('DATE(order_items.created_at) as date'),
                DB::raw('SUM(order_items.total_price * COALESCE(sellers.commission_rate, 10) / 100) as commission')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        return [
            'summary' => $summary,
            'commission_by_seller' => $commissionBySeller,
            'commission_by_category' => $commissionByCategory,
            'daily_commission' => $dailyCommission,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * Get chart data for dashboard
     */
    public function getChartData($params)
    {
        $type = $params['type'];
        $period = $params['period'];
        
        // Determine date range
        $days = match($period) {
            '7days' => 7,
            '30days' => 30,
            '90days' => 90,
            '12months' => 365,
            default => 30,
        };
        
        $end = now();
        $start = now()->subDays($days);
        
        $data = [];
        
        switch ($type) {
            case 'revenue':
                $data = Order::where('payment_status', 'paid')
                    ->whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as value'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
                
            case 'orders':
                $data = Order::whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as value'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
                
            case 'users':
                $data = User::whereBetween('created_at', [$start, $end])
                    ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as value'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
                
            case 'deliveries':
                $data = Delivery::where('status', 'delivered')
                    ->whereBetween('delivered_at', [$start, $end])
                    ->select(DB::raw('DATE(delivered_at) as date'), DB::raw('COUNT(*) as value'))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
                break;
        }
        
        // Format for Chart.js
        return [
            'labels' => $data->pluck('date')->map(fn($d) => Carbon::parse($d)->format('M d')),
            'data' => $data->pluck('value'),
        ];
    }
    
    /**
     * Export report
     */
    public function exportReport($params)
    {
        $type = $params['type'];
        $format = $params['format'];
        
        // Get data based on type
        $data = match($type) {
            'revenue' => $this->getRevenueAnalytics($params),
            'sales' => $this->getSalesAnalytics($params),
            'users' => $this->getUserAnalytics($params),
            'deliveries' => $this->getDeliveryAnalytics($params),
            'products' => $this->getProductAnalytics($params),
            'commission' => $this->getCommissionAnalytics($params),
        };
        
        // Export based on format
        if ($format === 'pdf') {
            $pdf = Pdf::loadView("admin.reports.exports.{$type}", compact('data'));
            return $pdf->download("{$type}-report-" . now()->format('Y-m-d') . ".pdf");
        }
        
        // Excel/CSV export would use Maatwebsite\Excel package
        // Implementation depends on your setup
        
        return response()->json(['message' => 'Export functionality coming soon']);
    }
    
    /**
     * Generate custom report
     */
    public function generateCustomReport($params)
    {
        [$start, $end] = $this->getDateRange(['date_from' => $params['date_from'], 'date_to' => $params['date_to']]);
        
        $results = [];
        
        foreach ($params['metrics'] as $metric) {
            $results[$metric] = match($metric) {
                'revenue' => $this->getRevenueAnalytics(['date_from' => $start, 'date_to' => $end]),
                'orders' => $this->getSalesAnalytics(['date_from' => $start, 'date_to' => $end]),
                'users' => $this->getUserAnalytics(['date_from' => $start, 'date_to' => $end]),
                'products' => $this->getProductAnalytics(['date_from' => $start, 'date_to' => $end]),
                'deliveries' => $this->getDeliveryAnalytics(['date_from' => $start, 'date_to' => $end]),
                'commission' => $this->getCommissionAnalytics(['date_from' => $start, 'date_to' => $end]),
            };
        }
        
        return [
            'name' => $params['name'],
            'metrics' => $results,
            'period' => [
                'start' => $start,
                'end' => $end,
            ],
        ];
    }
    
    /**
     * Schedule report
     */
    public function scheduleReport($params)
    {
        // This would integrate with Laravel's task scheduling
        // Store in database and create a scheduled job
        
        DB::table('scheduled_reports')->insert([
            'type' => $params['type'],
            'frequency' => $params['frequency'],
            'recipients' => json_encode($params['recipients']),
            'format' => $params['format'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return true;
    }
    
    /**
     * Helper: Get previous period revenue for growth calculation
     */
    protected function getPreviousPeriodRevenue($start, $end)
    {
        $days = $start->diffInDays($end);
        $previousStart = $start->copy()->subDays($days + 1);
        $previousEnd = $start->copy()->subDay();
        
        return Order::where('payment_status', 'paid')
            ->whereBetween('created_at', [$previousStart, $previousEnd])
            ->sum('total');
    }

    protected function commissionItemsQuery($start, $end)
    {
        return OrderItem::whereBetween('order_items.created_at', [$start, $end])
            ->leftJoin('sellers', 'order_items.seller_id', '=', 'sellers.id');
    }
}

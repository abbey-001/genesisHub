<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        $admin = auth()->guard('admin')->user();
        
        // Get role-specific metrics
        $metrics = $this->dashboardService->getMetrics($admin);
        
        // Get pending actions
        $pendingActions = $this->dashboardService->getPendingActions($admin);
        
        // Get recent orders (if permission allows)
        $recentOrders = [];
        if ($admin->hasPermission('orders.view')) {
            $recentOrders = $this->dashboardService->getRecentOrders(10);
        }
        
        // Get chart data (if permission allows)
        $revenueChart = [];
        $orderVolumeChart = [];
        if ($admin->hasPermission('analytics.view')) {
            $revenueChart = $this->dashboardService->getRevenueChartData();
            $orderVolumeChart = $this->dashboardService->getOrderVolumeChartData();
        }
        
        // Get top products (if permission allows)
        $topProducts = [];
        if ($admin->hasPermission('products.view')) {
            $topProducts = $this->dashboardService->getTopProducts(5);
        }
        
        // Get top sellers (if permission allows)
        $topSellers = [];
        if ($admin->hasPermission('sellers.view')) {
            $topSellers = $this->dashboardService->getTopSellers(5);
        }

        return view('admin.dashboard', compact(
            'metrics',
            'pendingActions',
            'recentOrders',
            'revenueChart',
            'orderVolumeChart',
            'topProducts',
            'topSellers'
        ));
    }

    /**
     * Get dashboard data via AJAX (for real-time updates)
     */
    public function refresh()
    {
        $admin = auth()->guard('admin')->user();
        
        $metrics = $this->dashboardService->getMetrics($admin);
        $pendingActions = $this->dashboardService->getPendingActions($admin);

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'pendingActions' => $pendingActions,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
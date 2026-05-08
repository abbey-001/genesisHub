<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $reportService;
    
    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }
    
    /**
     * Analytics Dashboard
     */
    public function index()
    {
        // Check permission
        if (!Auth::guard('admin')->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized access');
        }
        
        return view('admin.reports.index');
    }
    
    /**
     * Revenue Analytics
     */
    public function revenue(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.view')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom',
            'seller_id' => 'nullable|exists:sellers,id',
            'category_id' => 'nullable|exists:categories,id',
        ]);
        
        $data = $this->reportService->getRevenueAnalytics($validated);
        
        return view('admin.reports.revenue', compact('data'));
    }
    
    /**
     * Sales Analytics
     */
    public function sales(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.view')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom',
            'group_by' => 'nullable|in:day,week,month,category,seller',
        ]);
        
        $data = $this->reportService->getSalesAnalytics($validated);
        
        return view('admin.reports.sales', compact('data'));
    }
    
    /**
     * User Analytics
     */
    public function users(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.view')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom',
            'user_type' => 'nullable|in:customer,seller,rider,all',
        ]);
        
        $data = $this->reportService->getUserAnalytics($validated);
        
        return view('admin.reports.users', compact('data'));
    }
    
    /**
     * Delivery Analytics
     */
    public function deliveries(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.view')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom',
            'rider_id' => 'nullable|exists:riders,id',
            'status' => 'nullable|in:pending,assigned,picked_up,delivered,failed',
        ]);
        
        $data = $this->reportService->getDeliveryAnalytics($validated);
        
        return view('admin.reports.deliveries', compact('data'));
    }
    
    /**
     * Product Analytics
     */
    public function products(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.view')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom',
            'category_id' => 'nullable|exists:categories,id',
            'seller_id' => 'nullable|exists:sellers,id',
            'sort_by' => 'nullable|in:revenue,orders,views,rating',
        ]);
        
        $data = $this->reportService->getProductAnalytics($validated);
        
        return view('admin.reports.products', compact('data'));
    }
    
    /**
     * Commission Analytics
     */
    public function commission(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('finance.view')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'period' => 'nullable|in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom',
            'seller_id' => 'nullable|exists:sellers,id',
        ]);
        
        $data = $this->reportService->getCommissionAnalytics($validated);
        
        return view('admin.reports.commission', compact('data'));
    }
    
    /**
     * Export Report
     */
    public function export(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.export')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'type' => 'required|in:revenue,sales,users,deliveries,products,commission',
            'format' => 'required|in:pdf,excel,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);
        
        return $this->reportService->exportReport($validated);
    }
    
    /**
     * Custom Report Builder
     */
    public function custom()
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.custom')) {
            abort(403);
        }
        
        return view('admin.reports.custom');
    }
    
    /**
     * Generate Custom Report
     */
    public function generateCustom(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.custom')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'metrics' => 'required|array',
            'metrics.*' => 'in:revenue,orders,users,products,deliveries,commission',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'group_by' => 'nullable|in:day,week,month,category,seller',
            'filters' => 'nullable|array',
        ]);
        
        $data = $this->reportService->generateCustomReport($validated);
        
        return view('admin.reports.custom-result', compact('data'));
    }
    
    /**
     * Schedule Report
     */
    public function schedule(Request $request)
    {
        if (!Auth::guard('admin')->user()->hasPermission('reports.schedule')) {
            abort(403);
        }
        
        $validated = $request->validate([
            'type' => 'required|in:revenue,sales,users,deliveries,products,commission',
            'frequency' => 'required|in:daily,weekly,monthly',
            'recipients' => 'required|array',
            'recipients.*' => 'email',
            'format' => 'required|in:pdf,excel',
        ]);
        
        $this->reportService->scheduleReport($validated);
        
        return back()->with('success', 'Report scheduled successfully!');
    }
    
    /**
     * Get Chart Data (AJAX)
     */
    public function chartData(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:revenue,orders,users,deliveries',
            'period' => 'required|in:7days,30days,90days,12months',
            'compare' => 'nullable|boolean',
        ]);
        
        $data = $this->reportService->getChartData($validated);
        
        return response()->json($data);
    }
}
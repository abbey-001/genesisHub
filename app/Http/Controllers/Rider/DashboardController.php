<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $rider = Auth::user()->rider;
        
        if (!$rider) {
            return redirect()->route('home')->with('error', 'Company account not found');
        }
        
        $today = now()->startOfDay();
        
        // Statistics
        $stats = [
            'active_deliveries' => $rider->activeDeliveries()->count(),
            'pending_pickups' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'assigned')
                ->count(),
            'completed_today' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $today)
                ->count(),
            'earnings_today' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $today)
                ->sum('delivery_fee'),
            'total_completed' => $rider->completed_deliveries,
            'total_failed' => $rider->failed_deliveries,
            'success_rate' => $rider->success_rate,
            'rating' => 0, // Removed rating system
        ];
        
        // Current active deliveries
        $activeDeliveries = $rider->activeDeliveries()
            ->with(['order.user', 'seller.shop', 'items.product'])
            ->orderByRaw("FIELD(status, 'picked_up', 'assigned')")
            ->get();
        
        // Recent completed (last 10)
        $recentCompleted = Delivery::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->with(['order', 'seller.shop'])
            ->latest('delivered_at')
            ->take(10)
            ->get();
        
        // Weekly earnings chart data
        $weeklyEarnings = $this->getWeeklyEarnings($rider);
        
        return view('rider.dashboard', compact(
            'rider',
            'stats',
            'activeDeliveries',
            'recentCompleted',
            'weeklyEarnings'
        ));
    }
    
    public function notifications()
    {
        $notifications = Auth::user()->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('rider.notifications', compact('notifications'));
    }
    
    public function markNotificationRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }
    
    public function markAllNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        
        return back()->with('success', 'All notifications marked as read');
    }
    
    protected function getWeeklyEarnings($rider)
    {
        $days = [];
        $earnings = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('D');
            
            $dayEarnings = Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $date)
                ->sum('delivery_fee');
            
            $earnings[] = $dayEarnings;
        }
        
        return [
            'labels' => $days,
            'data' => $earnings,
        ];
    }
}
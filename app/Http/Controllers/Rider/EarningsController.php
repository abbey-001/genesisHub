<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryPayout;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EarningsController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function index()
    {
        $rider = Auth::user()->rider;
        
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();
        
        // Earnings Summary
        $earnings = [
            'today' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $today)
                ->sum('delivery_fee'),
            
            'yesterday' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', now()->subDay())
                ->sum('delivery_fee'),
            
            'this_week' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', '>=', $thisWeek)
                ->sum('delivery_fee'),
            
            'this_month' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', '>=', $thisMonth)
                ->sum('delivery_fee'),
            
            'all_time' => Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->sum('delivery_fee'),
        ];

        // Get balance information
        $balance = $this->payoutService->calculateAvailableBalance($rider);
        $earnings = array_merge($earnings, $balance);
        
        // Recent Deliveries
        $recentDeliveries = Delivery::where('rider_id', $rider->id)
            ->where('status', 'delivered')
            ->with('order')
            ->latest('delivered_at')
            ->take(20)
            ->get();
        
        // Daily Earnings Chart (Last 30 days)
        $dailyEarnings = $this->getDailyEarnings($rider);

        // Payout History
        $payouts = DeliveryPayout::where('rider_id', $rider->id)
            ->latest()
            ->take(5)
            ->get();
        
        return view('rider.earnings.index', compact('earnings', 'recentDeliveries', 'dailyEarnings', 'payouts'));
    }
    
    /**
     * Show payout request form
     */
    public function payoutForm()
    {
        $rider = Auth::user()->rider;

        // Get balance info
        $balance = $this->payoutService->calculateAvailableBalance($rider);

        // Get unpaid deliveries
        $unpaidDeliveries = $this->payoutService->getUnpaidDeliveries($rider);

        return view('rider.earnings.payout', compact('balance', 'unpaidDeliveries'));
    }

    /**
     * Request payout
     */
    public function requestPayout(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'delivery_ids' => 'nullable|array',
            'delivery_ids.*' => 'exists:deliveries,id',
        ]);

        $rider = Auth::user()->rider;

        try {
            $payout = $this->payoutService->createPayoutRequest(
                $rider,
                $request->amount,
                $request->delivery_ids
            );
            
            Auth::user()->notify(new \App\Notifications\RiderPayoutRequested($payout));

            return redirect()->route('rider.earnings.index')
                ->with('success', "Payout request submitted successfully! Reference: {$payout->reference_number}");

        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * View payout history
     */
    public function payoutHistory()
    {
        $rider = Auth::user()->rider;

        $payouts = DeliveryPayout::where('rider_id', $rider->id)
            ->with(['approvedBy', 'paidBy'])
            ->latest()
            ->paginate(20);

        $stats = $this->payoutService->getPayoutStats($rider);
        
        // Get balance info
        $balance = $this->payoutService->calculateAvailableBalance($rider);
        
        // Get unpaid deliveries
        $unpaidDeliveries = $this->payoutService->getUnpaidDeliveries($rider);

        return view('rider.earnings.payout-history', compact('payouts', 'stats', 'balance', 'unpaidDeliveries'));
    }

    /**
     * Show payout details
     */
    public function showPayout(DeliveryPayout $payout)
    {
        // Ensure company owns this payout
        if ($payout->rider_id !== Auth::user()->rider->id) {
            abort(403);
        }

        $payout->load(['deliveries.order', 'approvedBy', 'paidBy', 'rejectedBy']);

        return view('rider.earnings.payout-show', compact('payout'));
    }
    
    protected function getDailyEarnings($rider)
    {
        $days = [];
        $earnings = [];
        
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->format('M d');
            
            $dayEarnings = Delivery::where('rider_id', $rider->id)
                ->where('status', 'delivered')
                ->whereDate('delivered_at', $date)
                ->sum('delivery_fee');
            
            $earnings[] = (float) $dayEarnings;
        }
        
        return [
            'labels' => $days,
            'data' => $earnings,
        ];
    }
}
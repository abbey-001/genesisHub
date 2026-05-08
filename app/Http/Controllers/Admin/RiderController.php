<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiderController extends Controller
{
    /**
     * Display all riders
     */
    public function index(Request $request)
    {
        $query = Rider::with('user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_registration', 'like', "%{$search}%")
                  ->orWhereHas('user', function($q2) use ($search) {
                      $q2->where('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_verified', false);
            } elseif ($request->status === 'online') {
                $query->where('status', 'available');
            } elseif ($request->status === 'busy') {
                $query->where('status', 'busy');
            }
        }

        // Filter by vehicle type
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $riders = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => Rider::count(),
            'verified' => Rider::where('is_verified', true)->count(),
            'pending' => Rider::where('is_verified', false)->count(),
            'online' => Rider::where('status', 'available')->count(),
            'busy' => Rider::where('status', 'busy')->count(),
            'offline' => Rider::where('status', 'offline')->count(),
        ];

        return view('admin.riders.index', compact('riders', 'stats'));
    }

    /**
     * Show pending applications
     */
    public function applications()
    {
        $applications = Rider::with('user')
            ->where('is_verified', false)
            ->latest()
            ->paginate(20);

        return view('admin.riders.applications', compact('applications'));
    }

    /**
     * Show rider details
     */
    public function show(Rider $rider)
    {
        $rider->load('user');

        // Rider statistics
        $stats = [
            'total_deliveries' => $rider->deliveries()->count(),
            'completed' => $rider->deliveries()->where('status', 'delivered')->count(),
            'failed' => $rider->deliveries()->where('status', 'failed')->count(),
            'active' => $rider->activeDeliveries()->count(),
            'total_earnings' => $rider->deliveries()
                ->where('status', 'delivered')
                ->sum('delivery_fee'),
            'success_rate' => $this->calculateSuccessRate($rider),
            'avg_rating' => round($rider->rating, 2),
        ];

        // Recent deliveries
        $recentDeliveries = $rider->deliveries()
            ->with('order')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.riders.show', compact('rider', 'stats', 'recentDeliveries'));
    }

    /**
     * Approve rider application
     */
    public function approve(Request $request, Rider $rider)
    {
        $request->validate([
            'notes' => 'nullable|string|max:500',
        ]);

        $rider->update([
            'is_verified' => true,
            'is_active' => true,
        ]);

        // TODO: Send approval email
        // $rider->user->notify(new RiderApproved());

        return redirect()->route('admin.riders.index')
            ->with('success', 'Rider application approved successfully!');
    }

    /**
     * Reject rider application
     */
    public function reject(Request $request, Rider $rider)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $rider->update([
            'is_verified' => false,
            'is_active' => false,
        ]);

        // TODO: Send rejection email
        // $rider->user->notify(new RiderRejected($request->reason));

        return redirect()->route('admin.riders.applications')
            ->with('success', 'Rider application rejected.');
    }

    /**
     * Suspend rider
     */
    public function suspend(Request $request, Rider $rider)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $rider->update([
                'is_active' => false,
                'status' => 'offline',
            ]);

            // Reassign active deliveries if any
            $activeDeliveries = $rider->activeDeliveries()->get();
            foreach ($activeDeliveries as $delivery) {
                $delivery->update([
                    'rider_id' => null,
                    'status' => 'pending',
                ]);
            }

            // TODO: Send suspension email
            // $rider->user->notify(new RiderSuspended($request->reason));

            DB::commit();

            return back()->with('success', 'Rider has been suspended and active deliveries reassigned.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to suspend rider: ' . $e->getMessage());
        }
    }

    /**
     * Reactivate rider
     */
    public function activate(Rider $rider)
    {
        $rider->update([
            'is_active' => true,
            'is_verified' => true,
        ]);

        // TODO: Send reactivation email
        // $rider->user->notify(new RiderReactivated());

        return back()->with('success', 'Rider has been reactivated.');
    }

    /**
     * View rider's deliveries
     */
    public function deliveries(Rider $rider)
    {
        $deliveries = $rider->deliveries()
            ->with('order')
            ->latest()
            ->paginate(20);

        return view('admin.riders.deliveries', compact('rider', 'deliveries'));
    }

    /**
     * View rider's earnings
     */
    public function earnings(Rider $rider)
    {
        $deliveries = $rider->deliveries()
            ->where('status', 'delivered')
            ->with('order')
            ->latest('delivered_at')
            ->paginate(20);

        $totalEarnings = $rider->deliveries()
            ->where('status', 'delivered')
            ->sum('delivery_fee');

        $earningsByMonth = $rider->deliveries()
            ->where('status', 'delivered')
            ->selectRaw('DATE_FORMAT(delivered_at, "%Y-%m") as month, SUM(delivery_fee) as total')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->take(12)
            ->get();

        return view('admin.riders.earnings', compact('rider', 'deliveries', 'totalEarnings', 'earningsByMonth'));
    }

    /**
     * Live tracking map
     */
    public function map()
    {
        $onlineRiders = Rider::where('status', 'available')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get(['id', 'full_name', 'current_latitude', 'current_longitude', 'status']);

        $busyRiders = Rider::where('status', 'busy')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->with('activeDeliveries')
            ->get(['id', 'full_name', 'current_latitude', 'current_longitude', 'status']);

        return view('admin.riders.map', compact('onlineRiders', 'busyRiders'));
    }

    /**
     * Export riders
     */
    public function export(Request $request)
    {
        $query = Rider::with('user');

        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->status === 'pending') {
                $query->where('is_verified', false);
            }
        }

        $riders = $query->get();

        $filename = 'riders_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($riders) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ['ID', 'Name', 'Email', 'Phone', 'Vehicle Type', 'Status', 'Deliveries', 'Rating', 'Joined']);
            
            foreach ($riders as $rider) {
                fputcsv($file, [
                    $rider->id,
                    $rider->full_name,
                    $rider->user->email,
                    $rider->phone_number,
                    ucfirst($rider->vehicle_type),
                    $rider->is_verified ? 'Verified' : 'Pending',
                    $rider->completed_deliveries,
                    number_format($rider->rating, 2),
                    $rider->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(Rider $rider)
    {
        $total = $rider->deliveries()
            ->whereIn('status', ['delivered', 'failed'])
            ->count();

        if ($total === 0) {
            return 0;
        }

        $successful = $rider->deliveries()
            ->where('status', 'delivered')
            ->count();

        return round(($successful / $total) * 100, 2);
    }
}
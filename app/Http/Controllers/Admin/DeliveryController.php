<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Rider;
use App\Models\Order;
use App\Services\AdvancedRiderAssignmentService;
use App\Services\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    protected $assignmentService;
    protected $deliveryService;
    
    public function __construct(
        AdvancedRiderAssignmentService $assignmentService,
        DeliveryService $deliveryService
    ) {
        $this->assignmentService = $assignmentService;
        $this->deliveryService = $deliveryService;
    }
    
    /**
     * Dashboard with live map
     */
    public function index(Request $request)
    {
       
        
        // Get filter parameters
        $status = $request->get('status');
        $riderId = $request->get('rider_id');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        
        // Build query
        $query = Delivery::with(['order', 'rider', 'seller.shop', 'items'])
            ->latest();
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($riderId) {
            $query->where('rider_id', $riderId);
        }
        
        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
        
        $deliveries = $query->paginate(20);
        
        // Get statistics
        $stats = [
            'total' => Delivery::count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'active' => Delivery::whereIn('status', [
                'assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'
            ])->count(),
            'completed_today' => Delivery::where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->count(),
            'failed' => Delivery::where('status', 'failed')->count(),
        ];
        
        // Get active riders for map
        $activeRiders = Rider::with('activeDeliveries')
            ->whereIn('status', ['available', 'busy'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();
        
        return view('admin.deliveries.index', compact(
            'deliveries',
            'stats',
            'activeRiders'
        ));
    }
    
    /**
     * Live map view
     */
    public function map()
    {
       
        
        // Get all active deliveries
        $activeDeliveries = Delivery::with(['order', 'rider', 'seller.shop'])
            ->whereIn('status', [
                'assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'
            ])
            ->get();
        
        // Get all active riders
        $activeRiders = Rider::with('activeDeliveries')
            ->whereIn('status', ['available', 'busy'])
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->get();
        
        return view('admin.deliveries.map', compact(
            'activeDeliveries',
            'activeRiders'
        ));
    }
    
    /**
     * Unassigned deliveries queue
     */
    public function unassigned()
    {
        
        
        $deliveries = Delivery::with(['order', 'seller.shop', 'items'])
            ->where('status', 'pending')
            ->whereNull('rider_id')
            ->latest()
            ->paginate(20);
        
        // Get available riders
        $availableRiders = Rider::available()
            ->with('activeDeliveries')
            ->get();
        
        return view('admin.deliveries.unassigned', compact(
            'deliveries',
            'availableRiders'
        ));
    }
    
    /**
     * Failed deliveries
     */
    public function failed()
    {
       
        
        $deliveries = Delivery::with(['order', 'rider', 'seller.shop', 'items'])
            ->where('status', 'failed')
            ->latest('failed_at')
            ->paginate(20);
        
        return view('admin.deliveries.failed', compact('deliveries'));
    }
    
    /**
     * Show delivery details
     */
    public function show(Delivery $delivery)
    {
       
        
        $delivery->load([
            'order.user',
            'rider',
            'seller.shop',
            'items.product',   // items = OrderItem
            'assignmentHistory.rider',
            'broadcast.riders'
        ]);
        
        
        return view('admin.deliveries.show', compact('delivery'));
    }
    
    /**
     * Manual assignment page
     */
    public function assignPage(Delivery $delivery)
    {
        
        
        if ($delivery->status !== 'pending') {
            return back()->with('error', 'This delivery is not available for assignment');
        }
        
        $delivery->load(['order', 'seller.shop', 'items']);
        
        // Get suitable riders
        $riders = $this->assignmentService->findSuitableRiders($delivery, [
            'status' => 'available',
            'max_active_deliveries' => 3,
            'radius_km' => 50
        ]);
        
        return view('admin.deliveries.assign', compact('delivery', 'riders'));
    }
    
    /**
     * Manually assign rider
     */
    public function assign(Request $request, Delivery $delivery)
    {
        
        
        $request->validate([
            'rider_id' => 'required|exists:riders,id',
            'notes' => 'nullable|string|max:500'
        ]);
        
        try {
            $rider = Rider::findOrFail($request->rider_id);
            
            if (!$rider->canAcceptDelivery()) {
                return back()->with('error', 'Selected rider cannot accept deliveries at this time');
            }
            
            DB::beginTransaction();
            
            $result = $this->assignmentService->manualAssignment($delivery, $rider, [
                'notes' => $request->notes,
                'assigned_by' => auth()->guard('admin')->id()
            ]);
            
            if ($result['success']) {
                DB::commit();
                return redirect()
                    ->route('admin.deliveries.show', $delivery)
                    ->with('success', 'Rider assigned successfully!');
            }
            
            DB::rollBack();
            return back()->with('error', $result['message']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Assignment failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Broadcast to riders
     */
    public function broadcastPage(Delivery $delivery)
    {
        
        
        if ($delivery->status !== 'pending') {
            return back()->with('error', 'This delivery is not available for broadcast');
        }
        
        $delivery->load(['order', 'seller.shop', 'items']);
        
        // Get riders in area
        $riders = Rider::available()
            ->when($delivery->pickup_latitude && $delivery->pickup_longitude, function($q) use ($delivery) {
                return $q->nearLocation(
                    $delivery->pickup_latitude,
                    $delivery->pickup_longitude,
                    30
                );
            })
            ->get();
        
        return view('admin.deliveries.broadcast', compact('delivery', 'riders'));
    }
    
    /**
     * Send broadcast
     */
    public function sendBroadcast(Request $request, Delivery $delivery)
    {
        
        
        $request->validate([
            'rider_ids' => 'required|array|min:1',
            'rider_ids.*' => 'exists:riders,id',
            'expires_in_minutes' => 'required|integer|min:5|max:60'
        ]);
        
        try {
            $riders = Rider::whereIn('id', $request->rider_ids)->get();
            
            $broadcast = $this->assignmentService->broadcastDelivery(
                $delivery,
                $riders,
                $request->expires_in_minutes
            );
            
            return redirect()
                ->route('admin.deliveries.show', $delivery)
                ->with('success', "Broadcast sent to {$riders->count()} riders!");
                
        } catch (\Exception $e) {
            return back()->with('error', 'Broadcast failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Reassign delivery
     */
    public function reassign(Request $request, Delivery $delivery)
    {
        
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldRider = $delivery->rider;
            
            // Unassign current rider
            $delivery->update([
                'rider_id' => null,
                'status' => 'pending',
                'assigned_at' => null
            ]);
            
            // Log history
            if ($oldRider) {
                \App\Models\RiderAssignmentHistory::logUnassignment(
                    $delivery,
                    $oldRider,
                    'Admin reassignment: ' . $request->reason
                );
                
                // Update rider status if no other deliveries
                if ($oldRider->activeDeliveries()->count() === 0) {
                    $oldRider->update(['status' => 'available']);
                }
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.deliveries.assignPage', $delivery)
                ->with('success', 'Delivery unassigned. Please assign a new rider.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Reassignment failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Update delivery status
     */
    public function updateStatus(Request $request, Delivery $delivery)
    {
        $this->authorize('deliveries.update');
        
        $request->validate([
            'status' => 'required|in:pending,assigned,en_route_pickup,picked_up,en_route_delivery,delivered,failed',
            'notes' => 'nullable|string|max:500'
        ]);
        
        try {
            $oldStatus = $delivery->status;
            
            $delivery->update([
                'status' => $request->status,
                'notes' => $request->notes
            ]);
            
            event(new \App\Events\DeliveryStatusUpdated($delivery, $oldStatus, $request->status));
            
            return back()->with('success', 'Delivery status updated successfully!');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Status update failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancel delivery
     */
    public function cancel(Request $request, Delivery $delivery)
    {
        $this->authorize('deliveries.cancel');
        
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);
        
        try {
            DB::beginTransaction();
            
            $delivery->update([
                'status' => 'failed',
                'failure_reason' => 'cancelled_by_admin',
                'failure_notes' => $request->reason,
                'failed_at' => now()
            ]);
            
            // Release rider
            if ($delivery->rider) {
                if ($delivery->rider->activeDeliveries()->count() === 0) {
                    $delivery->rider->update(['status' => 'available']);
                }
            }
            
            DB::commit();
            
            return back()->with('success', 'Delivery cancelled successfully');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Cancellation failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get live data (AJAX)
     */
    public function liveData()
    {
       
        
        $activeDeliveries = Delivery::with(['order', 'rider'])
            ->whereIn('status', [
                'assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'
            ])
            ->get()
            ->map(function($delivery) {
                return [
                    'id' => $delivery->id,
                    'order_number' => $delivery->order->order_number,
                    'status' => $delivery->status,
                    'status_label' => $delivery->status_label,
                    'rider' => $delivery->rider ? [
                        'id' => $delivery->rider->id,
                        'name' => $delivery->rider->full_name,
                        'latitude' => $delivery->rider->current_latitude,
                        'longitude' => $delivery->rider->current_longitude,
                        'last_update' => $delivery->rider->last_location_update
                    ] : null,
                    'pickup' => [
                        'latitude' => $delivery->pickup_latitude,
                        'longitude' => $delivery->pickup_longitude,
                        'address' => $delivery->pickup_address
                    ],
                    'delivery' => [
                        'latitude' => $delivery->delivery_latitude,
                        'longitude' => $delivery->delivery_longitude,
                        'address' => $delivery->delivery_address
                    ]
                ];
            });
        
        $stats = [
            'active' => Delivery::whereIn('status', [
                'assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'
            ])->count(),
            'pending' => Delivery::where('status', 'pending')->count(),
            'completed_today' => Delivery::where('status', 'delivered')
                ->whereDate('delivered_at', today())
                ->count()
        ];
        
        return response()->json([
            'deliveries' => $activeDeliveries,
            'stats' => $stats,
            'timestamp' => now()->toISOString()
        ]);
    }
}
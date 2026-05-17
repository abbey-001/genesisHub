<?php

// ============================================
// Rider Broadcast Controller
// ============================================
namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Rider\DeliveryController as RiderDeliveryController;
use App\Models\DeliveryBroadcast;
use App\Models\Delivery;
use App\Services\AdvancedRiderAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    protected $assignmentService;
    protected $deliveryController;

    public function __construct(
        AdvancedRiderAssignmentService $assignmentService,
        RiderDeliveryController        $deliveryController
    ) {
        $this->assignmentService   = $assignmentService;
        $this->deliveryController  = $deliveryController;
    }
    
    /**
     * List available broadcast deliveries
     */
    public function index()
    {
        $rider = Auth::user()->rider;

        if (! $rider) {
            abort(403, 'Rider profile is required to view broadcasts');
        }
        
        // Get active broadcasts sent to this rider
        $broadcasts = $rider->broadcasts()
            ->with([
                'delivery.order',
                'delivery.seller.shop',
                'delivery.items',
                'bundle.deliveries.seller.shop',
                'bundle.deliveries.items',
                'bundle.order',
            ])
            ->where('status', 'active')
            ->wherePivot('response', 'pending')
            ->latest()
            ->get();

        return view('rider.broadcasts.index', compact('broadcasts'));
    }
    
    /**
     * View broadcast details
     */
    public function show(DeliveryBroadcast $broadcast)
    {
        $rider = Auth::user()->rider;
        
        // Check if rider has access to this broadcast
        if (!$broadcast->riders()->where('rider_id', $rider->id)->exists()) {
            abort(403, 'You do not have access to this broadcast');
        }
        
        $broadcast->load([
            'delivery.order',
            'delivery.seller.shop',
            'delivery.items',
            'bundle.deliveries.seller.shop',
            'bundle.deliveries.items',
            'bundle.order',
        ]);
        
        // Mark as viewed
        $broadcast->riders()->updateExistingPivot($rider->id, [
            'response' => 'viewed',
            'viewed_at' => now(),
        ]);
        
        $broadcast->incrementViewCount();

        // Compute the correct display fee using the same zone-matrix logic as the
        // available page. Summing delivery_fee rows is wrong for growing bundles
        // because not all delivery rows exist yet when is_partial=true.
        $feesMap      = $this->deliveryController->computeDisplayFees(collect([$broadcast]));
        $broadcastFee = $feesMap[$broadcast->id] ?? 0;

        return view('rider.broadcasts.show', compact('broadcast', 'broadcastFee'));
    }
    
    /**
     * Accept broadcast delivery
     */
public function accept(DeliveryBroadcast $broadcast)
{
    $rider = Auth::user()->rider;

    // Verify rider has access
    if (!$broadcast->riders()->where('rider_id', $rider->id)->exists()) {
        return back()->with('error', 'Invalid broadcast');
    }

    // Check broadcast is still available (isAvailable() checks locked_at too)
    if (!$broadcast->isAvailable()) {
        return back()->with('error', 'This delivery is no longer available.');
    }

    if ($broadcast->is_bundle) {
        $broadcast->load('bundle.deliveries');
        $bundle = $broadcast->bundle;

        // Accept is valid for all live bundle states — 'ready' (all sellers done),
        // 'growing' (broadcast live, more stops may appear), 'partial' (legacy).
        if (!in_array($bundle->status, ['ready', 'growing', 'partial'])) {
            return back()->with('error', 'This bundle has already been accepted or is no longer available.');
        }

        // Assign only currently-pending deliveries. Any sellers who mark ready
        // after this lock will get their own solo broadcast via handleSellerReady().
        $bundle->deliveries()->where('status', 'pending')->update([
            'rider_id'    => $rider->id,
            'status'      => 'assigned',
            'assigned_at' => now(),
        ]);

        $bundle->update(['status' => 'accepted']);

        // markAsAccepted() sets accepted_at AND locked_at atomically — this is
        // the gate that tells handleSellerReady() to solo-broadcast late sellers.
        $broadcast->markAsAccepted($rider);

        $broadcast->riders()->updateExistingPivot($rider->id, [
            'response'     => 'accepted',
            'responded_at' => now(),
        ]);

        $firstDelivery = $broadcast->bundle->deliveries->first();
        $stopCount     = $bundle->deliveries()->where('status', 'assigned')->count();

        return redirect()->route('rider.deliveries.show', $firstDelivery)
            ->with('success', "Bundle accepted! You have been assigned {$stopCount} pickup stop(s) in {$bundle->pickup_zone}.");
    }

    // Single delivery broadcast
    $result = $this->assignmentService->acceptBroadcastedDelivery(
        $broadcast->delivery,
        $rider
    );

    if ($result['success']) {
        // Lock the broadcast so it disappears from the available list for others.
        $broadcast->markAsAccepted($rider);

        $broadcast->riders()->updateExistingPivot($rider->id, [
            'response'     => 'accepted',
            'responded_at' => now(),
        ]);

        return redirect()->route('rider.deliveries.show', $broadcast->delivery)
            ->with('success', $result['message']);
    }

    return back()->with('error', $result['message']);
}
    
    /**
     * Reject broadcast delivery
     */
    public function reject(Request $request, DeliveryBroadcast $broadcast)
    {
        $request->validate([
            'reason' => 'required|in:too_far,too_busy,vehicle_issue,other',
            'notes' => 'nullable|string|max:200',
        ]);
        
        $rider = Auth::user()->rider;
        
        // Update pivot table
        $broadcast->riders()->updateExistingPivot($rider->id, [
            'response' => 'rejected',
            'responded_at' => now(),
            'rejection_reason' => $request->reason . ($request->notes ? ': ' . $request->notes : ''),
        ]);
        
        $broadcast->incrementRejectCount();
        
        return redirect()->route('rider.broadcasts.index')
            ->with('info', 'Broadcast rejected');
    }
}

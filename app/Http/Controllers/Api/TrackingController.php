<?php

// app/Http/Controllers/Api/TrackingController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    /**
     * Get delivery tracking information
     */
    public function getDeliveryTracking($deliveryId)
    {
        $delivery = Delivery::with(['order', 'rider', 'items.product', 'seller.shop'])
            ->findOrFail($deliveryId);
        
        // Check authorization
        $user = Auth::user();
        if ($user->id !== $delivery->order->user_id && 
            (!$user->rider || $user->rider->id !== $delivery->rider_id) &&
            (!$user->seller || $user->seller->id !== $delivery->seller_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'delivery' => [
                'id' => $delivery->id,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'pickup_address' => $delivery->pickup_address,
                'delivery_address' => $delivery->delivery_address,
                'estimated_pickup_time' => $delivery->estimated_pickup_time,
                'estimated_delivery_time' => $delivery->estimated_delivery_time,
                'delivered_at' => $delivery->delivered_at,
                'delivery_otp' => in_array($delivery->status, ['picked_up', 'en_route_delivery']) ? $delivery->delivery_otp : null,
            ],
            'rider' => $delivery->rider ? [
                'id' => $delivery->rider->id,
                'name' => $delivery->rider->full_name,
                'phone' => $delivery->rider->phone_number,
                'rating' => $delivery->rider->rating,
                'current_location' => [
                    'latitude' => $delivery->current_latitude ?? $delivery->rider->current_latitude,
                    'longitude' => $delivery->current_longitude ?? $delivery->rider->current_longitude,
                ],
            ] : null,
            'items' => $delivery->items->map(fn($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'status' => $item->status,
            ]),
            'timeline' => $this->getTimeline($delivery),
        ]);
    }
    
    /**
     * Update rider location (for active deliveries)
     */
    public function updateRiderLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'delivery_id' => 'nullable|exists:deliveries,id',
        ]);
        
        $rider = Auth::user()->rider;
        
        if (!$rider) {
            return response()->json(['error' => 'Not a rider'], 403);
        }
        
        // Update rider's general location
        $rider->update([
            'current_latitude' => $request->latitude,
            'current_longitude' => $request->longitude,
        ]);
        
        // If updating for specific delivery, update that too
        if ($request->delivery_id) {
            $delivery = Delivery::where('id', $request->delivery_id)
                ->where('rider_id', $rider->id)
                ->first();
            
            if ($delivery && in_array($delivery->status, ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])) {
                $delivery->update([
                    'current_latitude' => $request->latitude,
                    'current_longitude' => $request->longitude,
                    'last_location_update' => now(),
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Location updated',
        ]);
    }
    
    /**
     * Get order tracking by order number
     */
    public function trackOrder($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items', 'deliveries.rider'])
            ->firstOrFail();
        
        // Check if user can track this order
        $user = Auth::user();
        if ($user && $user->id !== $order->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $deliveries = $order->deliveries->map(function ($delivery) {
            return [
                'id' => $delivery->id,
                'status' => $delivery->status,
                'status_label' => $delivery->status_label,
                'seller' => $delivery->seller->shop->shop_name ?? 'Seller',
                'rider' => $delivery->rider ? [
                    'name' => $delivery->rider->full_name,
                    'phone' => $delivery->rider->phone_number,
                    'location' => [
                        'latitude' => $delivery->current_latitude ?? $delivery->rider->current_latitude,
                        'longitude' => $delivery->current_longitude ?? $delivery->rider->current_longitude,
                    ],
                ] : null,
                'items_count' => $delivery->items->count(),
                'estimated_delivery' => $delivery->estimated_delivery_time,
                'delivered_at' => $delivery->delivered_at,
            ];
        });
        
        return response()->json([
            'order' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'total' => $order->total,
                'created_at' => $order->created_at,
            ],
            'deliveries' => $deliveries,
        ]);
    }
    
    /**
     * Get available deliveries for riders
     */
    public function getAvailableDeliveries(Request $request)
    {
        $rider = Auth::user()->rider;
        
        if (!$rider) {
            return response()->json(['error' => 'Not a rider'], 403);
        }
        
        $query = Delivery::where('status', 'pending')
            ->with(['order', 'seller.shop', 'items']);
        
        // Filter by location if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $query->whereNotNull('pickup_latitude')
                ->whereNotNull('pickup_longitude')
                ->selectRaw("*, 
                    (6371 * acos(cos(radians(?)) 
                    * cos(radians(pickup_latitude)) 
                    * cos(radians(pickup_longitude) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(pickup_latitude)))) AS distance", 
                    [$request->latitude, $request->longitude, $request->latitude])
                ->having('distance', '<', 20)
                ->orderBy('distance');
        } else {
            $query->latest();
        }
        
        $deliveries = $query->limit(20)->get();
        
        return response()->json([
            'deliveries' => $deliveries->map(fn($d) => [
                'id' => $d->id,
                'order_number' => $d->order->order_number,
                'pickup_address' => $d->pickup_address,
                'delivery_address' => $d->delivery_address,
                'delivery_fee' => $d->delivery_fee,
                'package_weight' => $d->package_weight,
                'items_count' => $d->items->count(),
                'distance' => $d->distance ?? null,
                'created_at' => $d->created_at,
            ]),
        ]);
    }
    
    /**
     * Get rider statistics
     */
    public function getRiderStats()
    {
        $rider = Auth::user()->rider;
        
        if (!$rider) {
            return response()->json(['error' => 'Not a rider'], 403);
        }
        
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();
        $thisMonth = now()->startOfMonth();
        
        return response()->json([
            'stats' => [
                'rating' => $rider->rating,
                'total_completed' => $rider->completed_deliveries,
                'total_failed' => $rider->failed_deliveries,
                'success_rate' => $rider->completed_deliveries > 0 
                    ? round(($rider->completed_deliveries / ($rider->completed_deliveries + $rider->failed_deliveries)) * 100, 2)
                    : 100,
                'today' => [
                    'completed' => Delivery::where('rider_id', $rider->id)
                        ->where('status', 'delivered')
                        ->whereDate('delivered_at', $today)
                        ->count(),
                    'earnings' => Delivery::where('rider_id', $rider->id)
                        ->where('status', 'delivered')
                        ->whereDate('delivered_at', $today)
                        ->sum('delivery_fee'),
                ],
                'this_week' => [
                    'completed' => Delivery::where('rider_id', $rider->id)
                        ->where('status', 'delivered')
                        ->whereDate('delivered_at', '>=', $thisWeek)
                        ->count(),
                    'earnings' => Delivery::where('rider_id', $rider->id)
                        ->where('status', 'delivered')
                        ->whereDate('delivered_at', '>=', $thisWeek)
                        ->sum('delivery_fee'),
                ],
                'this_month' => [
                    'completed' => Delivery::where('rider_id', $rider->id)
                        ->where('status', 'delivered')
                        ->whereDate('delivered_at', '>=', $thisMonth)
                        ->count(),
                    'earnings' => Delivery::where('rider_id', $rider->id)
                        ->where('status', 'delivered')
                        ->whereDate('delivered_at', '>=', $thisMonth)
                        ->sum('delivery_fee'),
                ],
                'active_deliveries' => Delivery::where('rider_id', $rider->id)
                    ->whereIn('status', ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])
                    ->count(),
            ],
        ]);
    }
    
    /**
     * Build timeline for delivery
     */
    protected function getTimeline(Delivery $delivery)
    {
        $timeline = [];
        
        $timeline[] = [
            'status' => 'created',
            'label' => 'Delivery Created',
            'timestamp' => $delivery->created_at,
            'completed' => true,
        ];
        
        if ($delivery->assigned_at) {
            $timeline[] = [
                'status' => 'assigned',
                'label' => 'Rider Assigned',
                'timestamp' => $delivery->assigned_at,
                'completed' => true,
            ];
        }
        
        if ($delivery->picked_up_at) {
            $timeline[] = [
                'status' => 'picked_up',
                'label' => 'Package Picked Up',
                'timestamp' => $delivery->picked_up_at,
                'completed' => true,
            ];
        }
        
        if ($delivery->delivered_at) {
            $timeline[] = [
                'status' => 'delivered',
                'label' => 'Delivered',
                'timestamp' => $delivery->delivered_at,
                'completed' => true,
            ];
        } elseif ($delivery->failed_at) {
            $timeline[] = [
                'status' => 'failed',
                'label' => 'Delivery Failed',
                'timestamp' => $delivery->failed_at,
                'completed' => true,
                'reason' => $delivery->failure_reason,
            ];
        } else {
            $timeline[] = [
                'status' => 'pending_delivery',
                'label' => 'Pending Delivery',
                'timestamp' => $delivery->estimated_delivery_time,
                'completed' => false,
            ];
        }
        
        return $timeline;
    }
}


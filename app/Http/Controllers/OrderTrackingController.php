<?php

// app/Http/Controllers/OrderTrackingController.php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class OrderTrackingController extends Controller
{
    /**
     * Show tracking form
     */
    public function index()
    {
        return view('tracking.index');
    }
    
    /**
     * Track order by order number (POST)
     */
    public function trackOrder(Request $request)
    {
        $request->validate([
            'order_number' => 'required|string',
        ]);
        
        $order = Order::where('order_number', $request->order_number)->first();
        
        if (!$order) {
            return back()->with('error', 'Order not found. Please check your order number and try again.');
        }
        
        // Store order in session for OTP verification
        Session::put('tracking_order_id', $order->id);
        
        return redirect()->route('track.show', $order->order_number);
    }
    
    /**
     * Show order tracking page with OTP verification
     */
    public function showOrder($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->first();
        
        if (!$order) {
            return redirect()->route('track.index')
                ->with('error', 'Order not found.');
        }
        
        // Check if user is authenticated and owns the order
        if (Auth::check() && Auth::id() === $order->user_id) {
            return $this->showFullTracking($order);
        }
        
        // Check if OTP is verified in session
        if (Session::get('verified_order_' . $order->id)) {
            return $this->showFullTracking($order);
        }
        
        // Show OTP verification page
        return view('tracking.verify-otp', compact('order'));
    }
    
    /**
     * Verify OTP and show tracking
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'verification_code' => 'required|string',
        ]);
        
        $order = Order::findOrFail($request->order_id);
        
        // Get active delivery OTP
        $delivery = Delivery::where('order_id', $order->id)
            ->whereIn('status', ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])
            ->first();
        
        // Verify against delivery OTP or customer phone last 4 digits
        $isValidOTP = false;
        
        if ($delivery && $delivery->delivery_otp === $request->verification_code) {
            $isValidOTP = true;
        } elseif (substr($order->customer_phone, -4) === $request->verification_code) {
            $isValidOTP = true;
        }
        
        if (!$isValidOTP) {
            return back()->with('error', 'Invalid verification code. Please try again.');
        }
        
        // Store verification in session
        Session::put('verified_order_' . $order->id, true);
        
        return redirect()->route('track.show', $order->order_number)
            ->with('success', 'Verification successful!');
    }
    
    /**
     * Show full tracking information
     */
    protected function showFullTracking(Order $order)
    {
        $order->load(['items', 'deliveries.rider', 'deliveries.items']);
        
        return view('tracking.show', compact('order'));
    }
    
    /**
     * Get live tracking data (AJAX)
     */
    public function getLiveData($orderId)
    {
        $order = Order::findOrFail($orderId);
        
        // Check authorization
        if (!Auth::check() || Auth::id() !== $order->user_id) {
            if (!Session::get('verified_order_' . $order->id)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }
        
        $deliveries = Delivery::where('order_id', $order->id)
            ->with('rider')
            ->get()
            ->map(function($delivery) {
                return [
                    'id' => $delivery->id,
                    'status' => $delivery->status,
                    'status_label' => $delivery->status_label,
                    'rider' => $delivery->rider ? [
                        'name' => $delivery->rider->full_name,
                        'phone' => $delivery->rider->phone_number,
                        'location' => [
                            'latitude' => $delivery->current_latitude ?? $delivery->rider->current_latitude,
                            'longitude' => $delivery->current_longitude ?? $delivery->rider->current_longitude,
                        ],
                    ] : null,
                    'estimated_delivery_time' => $delivery->estimated_delivery_time,
                    'delivered_at' => $delivery->delivered_at,
                ];
            });
        
        return response()->json([
            'order' => [
                'status' => $order->status,
                'order_number' => $order->order_number,
            ],
            'deliveries' => $deliveries,
            'timestamp' => now()->toISOString(),
        ]);
    }
    
    /**
     * My orders (authenticated)
     */
    public function myOrders()
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to view your orders.');
        }
        
        $orders = Order::where('user_id', Auth::id())
            ->with(['items', 'deliveries'])
            ->latest()
            ->paginate(10);
        
        return view('tracking.my-orders', compact('orders'));
    }
    
    /**
     * Track my order (authenticated)
     */
    public function trackMyOrder(Order $order)
    {
        if (!Auth::check() || Auth::id() !== $order->user_id) {
            abort(403, 'Unauthorized access to this order.');
        }
        
        return $this->showFullTracking($order);
    }
}
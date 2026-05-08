<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnhancedDeliveryController extends Controller
{
    /**
     * Enhanced tracking page with smart navigation
     */
    public function track(Delivery $delivery)
    {
        $this->authorize('view', $delivery);
        
        if (!in_array($delivery->status, ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])) {
            return redirect()->route('rider.deliveries.show', $delivery)
                ->with('info', 'Live tracking is only available for active deliveries');
        }
        
        $delivery->load(['order', 'seller.shop', 'items']);
        
        // Determine current destination based on status
        $destination = $this->getCurrentDestination($delivery);
        
        // Prepare navigation data
        $navigationData = [
            'destination' => $destination,
            'delivery_id' => $delivery->id,
            'rider_location' => [
                'lat' => Auth::user()->rider->current_latitude,
                'lng' => Auth::user()->rider->current_longitude,
            ],
        ];
        
        return view('rider.deliveries.enhanced-track', compact('delivery', 'navigationData'));
    }
    
    /**
     * Get current destination based on delivery status
     */
    private function getCurrentDestination(Delivery $delivery)
    {
        if (in_array($delivery->status, ['assigned', 'en_route_pickup'])) {
            return [
                'type' => 'pickup',
                'address' => $delivery->pickup_address,
                'lat' => $delivery->pickup_latitude,
                'lng' => $delivery->pickup_longitude,
                'label' => 'Pickup Location',
            ];
        }
        
        return [
            'type' => 'delivery',
            'address' => $delivery->delivery_address,
            'lat' => $delivery->delivery_latitude,
            'lng' => $delivery->delivery_longitude,
            'label' => 'Customer Location',
        ];
    }
    
    /**
     * Generate navigation URL based on device
     */
    public function getNavigationUrl(Delivery $delivery)
    {
        $destination = $this->getCurrentDestination($delivery);
        $rider = Auth::user()->rider;
        
        // Get user agent
        $userAgent = request()->header('User-Agent');
        
        // Detect platform
        $isIOS = preg_match('/iPhone|iPad|iPod/i', $userAgent);
        $isAndroid = preg_match('/Android/i', $userAgent);
        
        // Build navigation URL
        if ($rider->current_latitude && $rider->current_longitude) {
            $origin = "{$rider->current_latitude},{$rider->current_longitude}";
        } else {
            $origin = ''; // Will use device's current location
        }
        
        $destCoords = "{$destination['lat']},{$destination['lng']}";
        
        // iOS - Try Apple Maps first, fallback to Google Maps
        if ($isIOS) {
            $appleMapsUrl = "https://maps.apple.com/?daddr={$destCoords}&dirflg=d";
            if ($origin) {
                $appleMapsUrl .= "&saddr={$origin}";
            }
            
            return [
                'primary' => $appleMapsUrl,
                'fallback' => "https://www.google.com/maps/dir/?api=1&destination={$destCoords}&travelmode=driving" . ($origin ? "&origin={$origin}" : ''),
                'platform' => 'ios',
            ];
        }
        
        // Android - Use Google Maps
        if ($isAndroid) {
            $googleMapsUrl = "google.navigation:q={$destCoords}&mode=d";
            
            return [
                'primary' => $googleMapsUrl,
                'fallback' => "https://www.google.com/maps/dir/?api=1&destination={$destCoords}&travelmode=driving" . ($origin ? "&origin={$origin}" : ''),
                'platform' => 'android',
            ];
        }
        
        // Desktop/Other - Use Google Maps web
        return [
            'primary' => "https://www.google.com/maps/dir/?api=1&destination={$destCoords}&travelmode=driving" . ($origin ? "&origin={$origin}" : ''),
            'fallback' => null,
            'platform' => 'web',
        ];
    }
    
    /**
     * API endpoint for navigation URLs
     */
    public function navigationUrl(Delivery $delivery)
    {
        $this->authorize('view', $delivery);
        
        $urls = $this->getNavigationUrl($delivery);
        $destination = $this->getCurrentDestination($delivery);
        
        return response()->json([
            'success' => true,
            'navigation' => $urls,
            'destination' => $destination,
        ]);
    }

    public function updateLocation(Request $request)
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
}
{{-- resources/views/rider/deliveries/track.blade.php --}}
@extends('rider.layouts.app')

@section('title', 'Track Delivery')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
    #tracking-map {
        height: calc(100vh - 180px);
        width: 100%;
        position: relative;
    }
    
    .delivery-info-card {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 20px;
        max-width: 350px;
    }

    .delivery-actions-card {
        position: absolute;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 15px 20px;
    }

    .location-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.7; }
        100% { transform: scale(1); opacity: 1; }
    }

    .eta-badge {
        display: inline-block;
        padding: 8px 16px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        font-weight: 600;
    }

    .speed-indicator {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        background: white;
        border-radius: 50%;
        width: 80px;
        height: 80px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')
<div class="position-relative">
    {{-- Delivery Info Card --}}
    <div class="delivery-info-card">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h5 class="mb-1">{{ $delivery->order->order_number }}</h5>
                <span class="badge bg-{{ $delivery->status_badge }}">{{ $delivery->status_label }}</span>
            </div>
            <button class="btn btn-sm btn-icon btn-label-primary" onclick="recenterMap()">
                <i class="bx bx-current-location"></i>
            </button>
        </div>

        @if($delivery->status === 'en_route_pickup' || $delivery->status === 'assigned')
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="bx bx-map-pin text-primary me-2"></i>
                    <div class="flex-grow-1">
                        <small class="text-muted d-block">Pickup Location</small>
                        <div class="fw-medium" id="pickup-address">{{ $delivery->pickup_address }}</div>
                    </div>
                </div>
                <div class="eta-badge" id="eta-pickup">
                    <i class="bx bx-time-five me-1"></i>
                    <span id="eta-pickup-time">Calculating...</span>
                </div>
            </div>
        @endif

        @if($delivery->status === 'en_route_delivery' || $delivery->status === 'picked_up')
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <i class="bx bx-map text-success me-2"></i>
                    <div class="flex-grow-1">
                        <small class="text-muted d-block">Delivery Location</small>
                        <div class="fw-medium" id="delivery-address">{{ $delivery->delivery_address }}</div>
                    </div>
                </div>
                <div class="eta-badge" id="eta-delivery">
                    <i class="bx bx-time-five me-1"></i>
                    <span id="eta-delivery-time">Calculating...</span>
                </div>
            </div>
        @endif

        <div class="mt-3 pt-3 border-top">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">Distance</span>
                <span class="fw-medium" id="distance">--</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Customer</span>
                <span class="fw-medium">{{ $delivery->order->customer_name }}</span>
            </div>
        </div>

        <div class="mt-3">
            <a href="tel:{{ $delivery->order->customer_phone }}" class="btn btn-success btn-sm w-100">
                <i class="bx bx-phone me-1"></i> Call Customer
            </a>
        </div>
    </div>

    {{-- Speed Indicator --}}
    <div class="speed-indicator">
        <h4 class="mb-0" id="speed">0</h4>
        <small class="text-muted">km/h</small>
    </div>

    {{-- Map --}}
    <div id="tracking-map"></div>

    {{-- Action Buttons --}}
    <div class="delivery-actions-card">
        <div class="d-flex gap-2 justify-content-center">
            @if($delivery->status === 'assigned')
                <button onclick="startJourney('pickup')" class="btn btn-primary">
                    <i class="bx bx-run me-1"></i> Start to Pickup
                </button>
            @elseif($delivery->status === 'en_route_pickup')
                <button onclick="confirmArrival('pickup')" class="btn btn-success">
                    <i class="bx bx-check me-1"></i> Arrived at Pickup
                </button>
            @elseif($delivery->status === 'picked_up')
                <button onclick="startJourney('delivery')" class="btn btn-primary">
                    <i class="bx bx-run me-1"></i> Start to Customer
                </button>
            @elseif($delivery->status === 'en_route_delivery')
                <button onclick="confirmArrival('delivery')" class="btn btn-success">
                    <i class="bx bx-check me-1"></i> Arrived at Destination
                </button>
            @endif
            
            <button onclick="openNavigationApp()" class="btn btn-label-primary">
                <i class="bx bx-navigation me-1"></i> Open Maps
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>
let map, riderMarker, pickupMarker, deliveryMarker, routingControl;
let currentPosition = null;
let watchId = null;
let lastUpdateTime = Date.now();

const delivery = @json($delivery);

// Initialize map
function initMap() {
    map = L.map('tracking-map').setView([9.0579, 7.4951], 13);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Custom marker icons
    const riderIcon = L.divIcon({
        className: 'location-pulse',
        html: '<div style="background: #667eea; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    const pickupIcon = L.divIcon({
        className: 'pickup-marker',
        html: '<div style="background: #28c76f; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bx bx-package" style="color: white; font-size: 18px;"></i></div>',
        iconSize: [35, 35],
        iconAnchor: [17.5, 17.5]
    });

    const deliveryIcon = L.divIcon({
        className: 'delivery-marker',
        html: '<div style="background: #ff9f43; width: 35px; height: 35px; border-radius: 50%; border: 3px solid white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"><i class="bx bx-home" style="color: white; font-size: 18px;"></i></div>',
        iconSize: [35, 35],
        iconAnchor: [17.5, 17.5]
    });

    // Add markers
    if (delivery.pickup_latitude && delivery.pickup_longitude) {
        pickupMarker = L.marker([delivery.pickup_latitude, delivery.pickup_longitude], {
            icon: pickupIcon
        }).addTo(map).bindPopup('Pickup Location');
    }

    if (delivery.delivery_latitude && delivery.delivery_longitude) {
        deliveryMarker = L.marker([delivery.delivery_latitude, delivery.delivery_longitude], {
            icon: deliveryIcon
        }).addTo(map).bindPopup('Delivery Location');
    }

    // Start tracking rider location
    startTracking();
}

// Track rider location
function startTracking() {
    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            (position) => {
                currentPosition = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                    speed: position.coords.speed || 0
                };

                updateRiderPosition(currentPosition);
                updateSpeedIndicator(currentPosition.speed);
                
                // Update server every 5 seconds
                if (Date.now() - lastUpdateTime > 5000) {
                    updateServerLocation(currentPosition);
                    lastUpdateTime = Date.now();
                }
            },
            (error) => {
                console.error('Geolocation error:', error);
            },
            {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            }
        );
    }
}

// Update rider marker position
function updateRiderPosition(position) {
    if (!riderMarker) {
        const riderIcon = L.divIcon({
            className: 'location-pulse',
            html: '<div style="background: #667eea; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.3);"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        });
        
        riderMarker = L.marker([position.lat, position.lng], {
            icon: riderIcon
        }).addTo(map).bindPopup('Your Location');
    } else {
        riderMarker.setLatLng([position.lat, position.lng]);
    }

    // Update route
    updateRoute();
}

// Update route on map
function updateRoute() {
    if (!currentPosition) return;

    let destination;
    if (['assigned', 'en_route_pickup'].includes(delivery.status)) {
        destination = [delivery.pickup_latitude, delivery.pickup_longitude];
    } else if (['picked_up', 'en_route_delivery'].includes(delivery.status)) {
        destination = [delivery.delivery_latitude, delivery.delivery_longitude];
    }

    if (!destination) return;

    if (routingControl) {
        map.removeControl(routingControl);
    }

    routingControl = L.Routing.control({
        waypoints: [
            L.latLng(currentPosition.lat, currentPosition.lng),
            L.latLng(destination[0], destination[1])
        ],
        routeWhileDragging: false,
        show: false,
        addWaypoints: false,
        lineOptions: {
            styles: [{color: '#667eea', opacity: 0.8, weight: 6}]
        },
        createMarker: function() { return null; }
    }).on('routesfound', function(e) {
        const route = e.routes[0];
        const distance = (route.summary.totalDistance / 1000).toFixed(2);
        const time = Math.round(route.summary.totalTime / 60);
        
        document.getElementById('distance').textContent = `${distance} km`;
        
        if (['assigned', 'en_route_pickup'].includes(delivery.status)) {
            document.getElementById('eta-pickup-time').textContent = `${time} min`;
        } else {
            document.getElementById('eta-delivery-time').textContent = `${time} min`;
        }
    }).addTo(map);
}

// Update speed indicator
function updateSpeedIndicator(speed) {
    const speedKmh = Math.round((speed || 0) * 3.6);
    document.getElementById('speed').textContent = speedKmh;
}

// Update server with location
async function updateServerLocation(position) {
    try {
        await fetch('{{ route("rider.location.update") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                latitude: position.lat,
                longitude: position.lng
            })
        });
    } catch (error) {
        console.error('Failed to update location:', error);
    }
}

// Re-center map on rider location
function recenterMap() {
    if (currentPosition && riderMarker) {
        map.setView([currentPosition.lat, currentPosition.lng], 16);
    }
}

// Start journey
function startJourney(type) {
    const status = type === 'pickup' ? 'en_route_pickup' : 'en_route_delivery';

    fetch(`{{ route('rider.status.update', $delivery) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    }).then(response => {
        if (response.ok) {
            location.reload();
        }
    });
}

// Confirm arrival
function confirmArrival(type) {
    if (type === 'pickup') {
        window.location.href = '{{ route("rider.deliveries.show", $delivery) }}#pickup';
    } else {
        window.location.href = '{{ route("rider.deliveries.show", $delivery) }}#deliver';
    }
}

// Open in navigation app
function openNavigationApp() {
    let destination;
    if (['assigned', 'en_route_pickup'].includes(delivery.status)) {
        destination = `${delivery.pickup_latitude},${delivery.pickup_longitude}`;
    } else {
        destination = `${delivery.delivery_latitude},${delivery.delivery_longitude}`;
    }
    
    // Try Google Maps first, fallback to Apple Maps
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    if (isMobile) {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${destination}`, '_blank');
    } else {
        window.open(`https://www.google.com/maps/search/?api=1&query=${destination}`, '_blank');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', initMap);

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
    }
});
</script>
@endpush

@extends('rider.layouts.app')

@section('title', 'Navigate to Destination')

@push('styles')
<style>
    .navigation-container {
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .nav-header {
        background: rgba(255, 255, 255, 0.95);
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .destination-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        margin: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    
    .destination-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
    }
    
    .nav-button {
        padding: 18px 30px;
        border-radius: 15px;
        font-size: 18px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    
    .nav-button-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .nav-button-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
    }
    
    .nav-button-secondary {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
    }
    
    .quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 20px;
    }
    
    .quick-action-btn {
        padding: 15px;
        border-radius: 12px;
        background: #f8f9fa;
        border: none;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .quick-action-btn:hover {
        background: #e9ecef;
        transform: translateY(-2px);
    }
    
    .distance-info {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 15px;
        border-radius: 12px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .platform-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        background: #e3f2fd;
        color: #1976d2;
        font-size: 12px;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .destination-card {
            margin: 10px;
            padding: 20px;
        }
    }
</style>
@endpush

@section('content')
<div class="navigation-container">
    <!-- Header -->
    <div class="nav-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Order #{{ $delivery->order->order_number }}</h5>
                <span class="badge bg-{{ $delivery->status_badge }}">{{ $delivery->status_label }}</span>
            </div>
            <button class="btn btn-sm btn-light" onclick="window.history.back()">
                <i class="bx bx-x"></i>
            </button>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="destination-card">
        <div>
            <!-- Destination Icon -->
            <div class="destination-icon">
                <i class="bx bx-{{ $navigationData['destination']['type'] === 'pickup' ? 'package' : 'home' }} bx-lg" style="color: white;"></i>
            </div>
            
            <!-- Destination Info -->
            <div class="text-center mb-4">
                <h4 class="mb-2">{{ $navigationData['destination']['label'] }}</h4>
                <p class="text-muted mb-0" id="destination-address">
                    {{ $navigationData['destination']['address'] }}
                </p>
                <span class="platform-badge mt-2" id="platform-badge">Detecting device...</span>
            </div>
            
            <!-- Distance Info -->
            <div class="distance-info" id="distance-card" style="display: none;">
                <div class="row">
                    <div class="col-6">
                        <small>Distance</small>
                        <h4 class="mb-0" id="distance-value">--</h4>
                    </div>
                    <div class="col-6">
                        <small>Est. Time</small>
                        <h4 class="mb-0" id="eta-value">--</h4>
                    </div>
                </div>
            </div>
            
            <!-- Delivery Info -->
            <div class="alert alert-info">
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bx bx-package me-1"></i> Items:</span>
                    <strong>{{ $delivery->items->count() }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span><i class="bx bx-money me-1"></i> Fee:</span>
                    <strong class="text-success">₦{{ number_format($delivery->delivery_fee, 0) }}</strong>
                </div>
                @if($delivery->delivery_otp && in_array($delivery->status, ['picked_up', 'en_route_delivery']))
                <div class="d-flex justify-content-between">
                    <span><i class="bx bx-lock me-1"></i> OTP:</span>
                    <strong>{{ $delivery->delivery_otp }}</strong>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Navigation Buttons -->
        <div>
            <button onclick="startNavigation()" class="nav-button nav-button-primary w-100 mb-3" id="nav-button">
                <i class="bx bx-navigation bx-md"></i>
                <span>Start Navigation</span>
            </button>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <button class="quick-action-btn" onclick="callCustomer()">
                    <i class="bx bx-phone bx-lg text-success"></i>
                    <small>Call Customer</small>
                </button>
                
                <button class="quick-action-btn" onclick="shareLocation()">
                    <i class="bx bx-current-location bx-lg text-primary"></i>
                    <small>Share Location</small>
                </button>
                
                <button class="quick-action-btn" onclick="viewOnMap()">
                    <i class="bx bx-map bx-lg text-info"></i>
                    <small>View on Map</small>
                </button>
                
                <button class="quick-action-btn" onclick="reportIssue()">
                    <i class="bx bx-error bx-lg text-danger"></i>
                    <small>Report Issue</small>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const deliveryId = {{ $delivery->id }};
const destination = @json($navigationData['destination']);
const customerPhone = '{{ $delivery->order->customer_phone }}';

let navigationUrls = null;
let currentPosition = null;

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    detectPlatform();
    fetchNavigationUrls();
    startLocationTracking();
});

// Detect platform and update UI
function detectPlatform() {
    const userAgent = navigator.userAgent;
    let platform = 'Web Browser';
    let icon = 'bx-globe';
    
    if (/iPhone|iPad|iPod/i.test(userAgent)) {
        platform = 'iOS Device';
        icon = 'bxl-apple';
    } else if (/Android/i.test(userAgent)) {
        platform = 'Android Device';
        icon = 'bxl-android';
    }
    
    document.getElementById('platform-badge').innerHTML = 
        `<i class="bx ${icon} me-1"></i>${platform}`;
}

// Fetch navigation URLs from server
async function fetchNavigationUrls() {
    try {
        const response = await fetch(`/rider/deliveries/${deliveryId}/navigation-url`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const data = await response.json();
        navigationUrls = data.navigation;
        
        console.log('Navigation URLs:', navigationUrls);
    } catch (error) {
        console.error('Failed to fetch navigation URLs:', error);
    }
}

// Start location tracking
function startLocationTracking() {
    if (navigator.geolocation) {
        navigator.geolocation.watchPosition(
            updateLocation,
            handleLocationError,
            {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            }
        );
    }
}

// Update location
function updateLocation(position) {
    currentPosition = {
        lat: position.coords.latitude,
        lng: position.coords.longitude
    };
    
    // Calculate distance and ETA
    calculateDistanceAndETA();
    
    // Update server
    updateServerLocation(currentPosition);
}

// Calculate distance and ETA
function calculateDistanceAndETA() {
    if (!currentPosition) return;
    
    const R = 6371; // Earth's radius in km
    const dLat = toRad(destination.lat - currentPosition.lat);
    const dLon = toRad(destination.lng - currentPosition.lng);
    
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(toRad(currentPosition.lat)) * Math.cos(toRad(destination.lat)) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    const distance = R * c;
    
    // Estimate time (assuming 30 km/h average in city)
    const timeInMinutes = Math.round((distance / 30) * 60);
    
    // Update UI
    document.getElementById('distance-value').textContent = distance.toFixed(1) + ' km';
    document.getElementById('eta-value').textContent = timeInMinutes + ' min';
    document.getElementById('distance-card').style.display = 'block';
}

function toRad(degrees) {
    return degrees * (Math.PI / 180);
}

// Update server location
async function updateServerLocation(position) {
    try {
        await fetch('/rider/location/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                latitude: position.lat,
                longitude: position.lng,
                delivery_id: deliveryId
            })
        });
    } catch (error) {
        console.error('Failed to update location:', error);
    }
}

// Handle location errors
function handleLocationError(error) {
    console.error('Location error:', error);
}

// Start Navigation
function startNavigation() {
    if (!navigationUrls) {
        alert('Loading navigation data...');
        return;
    }
    
    const button = document.getElementById('nav-button');
    button.disabled = true;
    button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Opening...';
    
    // Try primary URL first
    window.location.href = navigationUrls.primary;
    
    // Reset button after delay
    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = '<i class="bx bx-navigation bx-md"></i> <span>Start Navigation</span>';
        
        // If primary failed and fallback exists, try fallback
        if (navigationUrls.fallback) {
            if (confirm('Open in web browser instead?')) {
                window.open(navigationUrls.fallback, '_blank');
            }
        }
    }, 2000);
}

// Call customer
function callCustomer() {
    window.location.href = `tel:${customerPhone}`;
}

// Share location
function shareLocation() {
    if (navigator.share && currentPosition) {
        navigator.share({
            title: 'My Location',
            text: 'I am currently here:',
            url: `https://maps.google.com/?q=${currentPosition.lat},${currentPosition.lng}`
        });
    } else {
        alert('Location sharing not supported on this device');
    }
}

// View on map
function viewOnMap() {
    if (currentPosition) {
        window.open(`https://www.google.com/maps/dir/${currentPosition.lat},${currentPosition.lng}/${destination.lat},${destination.lng}`, '_blank');
    } else {
        window.open(`https://www.google.com/maps/search/?api=1&query=${destination.lat},${destination.lng}`, '_blank');
    }
}

// Report issue
function reportIssue() {
    window.location.href = '{{ route("rider.deliveries.show", $delivery) }}#fail';
}
</script>
@endpush
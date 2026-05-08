@extends('tracking.layouts.app')

@section('title', 'Track Order #' . $order->order_number)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .tracking-map {
        height: 400px;
        width: 100%;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .timeline-wrapper {
        position: relative;
        padding-left: 40px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 30px;
    }
    
    .timeline-item:before {
        content: '';
        position: absolute;
        left: -28px;
        top: 8px;
        bottom: -22px;
        width: 2px;
        background: #e0e0e0;
    }
    
    .timeline-item:last-child:before {
        display: none;
    }
    
    .timeline-marker {
        position: absolute;
        left: -35px;
        top: 0;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    }
    
    .timeline-marker.completed {
        background: #28c76f;
    }
    
    .timeline-marker.active {
        background: #ff9f43;
        animation: pulse 2s infinite;
    }
    
    .timeline-marker.pending {
        background: #e0e0e0;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.2); }
    }
    
    .delivery-card {
        transition: all 0.3s ease;
    }
    
    .delivery-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .otp-display {
        font-size: 2.5rem;
        letter-spacing: 0.5rem;
        font-weight: bold;
        color: #667eea;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <h2 class="mb-2">Track Your Order</h2>
                <p class="text-muted">Order #{{ $order->order_number }}</p>
            </div>

            <!-- Order Summary Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h5 class="mb-3">Order Information</h5>
                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Order Date</small>
                                    <span class="fw-medium">{{ $order->created_at->format('M d, Y h:i A') }}</span>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Order Status</small>
                                    <span class="badge bg-{{ $order->status_badge }} fs-6">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Items</small>
                                    <span class="fw-medium">{{ $order->items->count() }} items</span>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <small class="text-muted d-block">Total Amount</small>
                                    <span class="fw-bold text-primary">₦{{ number_format($order->total, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            @if($order->status === 'delivered')
                                <div class="text-success">
                                    <i class="bx bx-check-circle" style="font-size: 64px;"></i>
                                    <h6 class="mt-2">Delivered!</h6>
                                </div>
                            @elseif($order->status === 'shipped')
                                <div class="text-info">
                                    <i class="bx bx-package" style="font-size: 64px;"></i>
                                    <h6 class="mt-2">In Transit</h6>
                                </div>
                            @else
                                <div class="text-warning">
                                    <i class="bx bx-time" style="font-size: 64px;"></i>
                                    <h6 class="mt-2">Processing</h6>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Deliveries -->
            @foreach($order->deliveries as $delivery)
            <div class="card mb-4 delivery-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h5 class="mb-1">
                                <i class="bx bx-package me-2"></i>
                                Delivery #{{ $delivery->id }}
                            </h5>
                            <p class="text-muted mb-0">From {{ $delivery->seller->shop->shop_name ?? 'Seller' }}</p>
                        </div>
                        <span class="badge bg-{{ $delivery->status_badge }} fs-6">
                            {{ $delivery->status_label }}
                        </span>
                    </div>

                    <div class="row">
                        <!-- Timeline -->
                        <div class="col-md-6">
                            <h6 class="mb-3">Delivery Progress</h6>
                            <div class="timeline-wrapper">
                                <div class="timeline-item">
                                    <div class="timeline-marker completed"></div>
                                    <div>
                                        <strong>Order Placed</strong>
                                        <p class="text-muted small mb-0">{{ $order->created_at->format('M d, h:i A') }}</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $delivery->assigned_at ? 'completed' : 'pending' }}"></div>
                                    <div>
                                        <strong>Rider Assigned</strong>
                                        @if($delivery->assigned_at)
                                        <p class="text-muted small mb-0">{{ $delivery->assigned_at->format('M d, h:i A') }}</p>
                                        @else
                                        <p class="text-muted small mb-0">Waiting for assignment</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $delivery->picked_up_at ? 'completed' : ($delivery->status === 'en_route_pickup' ? 'active' : 'pending') }}"></div>
                                    <div>
                                        <strong>Package Picked Up</strong>
                                        @if($delivery->picked_up_at)
                                        <p class="text-muted small mb-0">{{ $delivery->picked_up_at->format('M d, h:i A') }}</p>
                                        @elseif($delivery->estimated_pickup_time)
                                        <p class="text-muted small mb-0">Est: {{ $delivery->estimated_pickup_time->format('h:i A') }}</p>
                                        @else
                                        <p class="text-muted small mb-0">Pending pickup</p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-marker {{ $delivery->delivered_at ? 'completed' : ($delivery->status === 'en_route_delivery' ? 'active' : 'pending') }}"></div>
                                    <div>
                                        <strong>Delivered</strong>
                                        @if($delivery->delivered_at)
                                        <p class="text-muted small mb-0">{{ $delivery->delivered_at->format('M d, h:i A') }}</p>
                                        @elseif($delivery->estimated_delivery_time)
                                        <p class="text-muted small mb-0">Est: {{ $delivery->estimated_delivery_time->format('h:i A') }}</p>
                                        @else
                                        <p class="text-muted small mb-0">Pending delivery</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Delivery Details -->
                        <div class="col-md-6">
                            <!-- Rider Info -->
                            @if($delivery->rider)
                            <div class="card bg-light mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Your Rider</h6>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar avatar-sm me-3">
                                            <img src="{{ $delivery->rider->profile_photo ? asset('storage/' . $delivery->rider->profile_photo) : 'https://ui-avatars.com/api/?name='.urlencode($delivery->rider->full_name) }}" 
                                                 alt="{{ $delivery->rider->full_name }}" 
                                                 class="rounded-circle">
                                        </div>
                                        <div>
                                            <div class="fw-medium">{{ $delivery->rider->full_name }}</div>
                                            <small class="text-muted">
                                                <i class="bx bx-star text-warning"></i>
                                                {{ number_format($delivery->rider->rating, 1) }}
                                            </small>
                                        </div>
                                    </div>
                                    <a href="tel:{{ $delivery->rider->phone_number }}" class="btn btn-sm btn-primary w-100">
                                        <i class="bx bx-phone me-1"></i>Call Rider
                                    </a>
                                </div>
                            </div>
                            @endif

                            <!-- Delivery OTP -->
                            @if(in_array($delivery->status, ['picked_up', 'en_route_delivery']) && $delivery->delivery_otp)
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h6 class="text-white mb-2">Your Delivery Code</h6>
                                    <div class="otp-display text-white">{{ $delivery->delivery_otp }}</div>
                                    <p class="small mb-0 mt-2">Share this code with the rider upon delivery</p>
                                </div>
                            </div>
                            @endif

                            <!-- Items -->
                            <div class="mt-3">
                                <h6 class="mb-2">Items in this delivery</h6>
                                <ul class="list-unstyled mb-0">
                                    @foreach($delivery->items as $item)
                                    <li class="mb-2 d-flex align-items-center">
                                        @if($item->product && $item->product->main_image)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}" 
                                             alt="{{ $item->product_name }}"
                                             class="rounded me-2"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                        <i class="bx bx-package text-muted me-2" style="font-size: 40px;"></i>
                                        @endif
                                        <div>
                                            <div>{{ $item->product_name }}</div>
                                            <small class="text-muted">Qty: {{ $item->quantity }}</small>
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Live Map -->
                    @if($delivery->rider && in_array($delivery->status, ['en_route_pickup', 'picked_up', 'en_route_delivery']))
                    <div class="mt-4">
                        <h6 class="mb-3">Live Tracking</h6>
                        <div id="map-{{ $delivery->id }}" class="tracking-map"></div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            <!-- Delivery Address -->
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Delivery Address</h6>
                    <p class="mb-2">
                        <i class="bx bx-map me-2"></i>
                        {{ $order->shipping_address }}
                    </p>
                    <p class="mb-2">
                        <i class="bx bx-map-pin me-2"></i>
                        {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}
                    </p>
                    <p class="mb-0">
                        <i class="bx bx-phone me-2"></i>
                        {{ $order->customer_phone }}
                    </p>
                </div>
            </div>

            <!-- Help Section -->
            <div class="text-center mt-4">
                <p class="text-muted">Need help with your order?</p>
                <a href="#" class="btn btn-outline-primary">
                    <i class="bx bx-support me-1"></i>Contact Support
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Initialize maps for active deliveries with live tracking
@foreach($order->deliveries as $delivery)
    @if($delivery->rider && in_array($delivery->status, ['en_route_pickup', 'picked_up', 'en_route_delivery']))
    (function() {
        const deliveryId = {{ $delivery->id }};
        const mapElement = document.getElementById('map-' + deliveryId);
        
        if (!mapElement) return;
        
        const map = L.map(mapElement).setView([9.0579, 7.4951], 13);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        let riderMarker, destinationMarker;

        // Add destination marker
        @if($delivery->delivery_latitude && $delivery->delivery_longitude)
        destinationMarker = L.marker([{{ $delivery->delivery_latitude }}, {{ $delivery->delivery_longitude }}])
            .addTo(map)
            .bindPopup('Delivery Location');
        @endif

        // Update rider location
        function updateRiderLocation() {
            fetch(`{{ route('track.live-data', $order->id) }}`)
            .then(response => response.json())
            .then(data => {
                const deliveryData = data.deliveries.find(d => d.id === deliveryId);
                
                if (deliveryData && deliveryData.rider && deliveryData.rider.location) {
                    const location = deliveryData.rider.location;
                    
                    if (location.latitude && location.longitude) {
                        if (!riderMarker) {
                            riderMarker = L.marker([location.latitude, location.longitude])
                                .addTo(map)
                                .bindPopup('Rider Location');
                        } else {
                            riderMarker.setLatLng([location.latitude, location.longitude]);
                        }
                        
                        // Center map to show both markers
                        if (destinationMarker) {
                            const bounds = L.latLngBounds([
                                [location.latitude, location.longitude],
                                destinationMarker.getLatLng()
                            ]);
                            map.fitBounds(bounds, { padding: [50, 50] });
                        }
                    }
                }
            })
            .catch(error => console.error('Error fetching location:', error));
        }

        // Initial load
        updateRiderLocation();
        
        // Update every 10 seconds
        setInterval(updateRiderLocation, 10000);
    })();
    @endif
@endforeach
</script>
@endpush

@endsection
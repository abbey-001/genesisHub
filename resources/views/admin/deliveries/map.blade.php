@extends('admin.layouts.app')

@section('title', 'Live Delivery Map')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#deliveryMap {
    height: calc(100vh - 200px);
    border-radius: 0.5rem;
}

.rider-marker {
    background: #3b82f6;
    border: 3px solid white;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

.rider-marker.busy {
    background: #f59e0b;
}

.rider-marker.available {
    background: #10b981;
}

.pickup-marker {
    background: #8b5cf6;
    border: 3px solid white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.delivery-marker {
    background: #ef4444;
    border: 3px solid white;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}

.legend-dot {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}

.stats-sidebar {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">🗺️ Live Delivery Map</h4>
                <p class="text-muted mb-0">Real-time tracking of all active deliveries</p>
            </div>
            <div class="d-flex gap-2">
                <button id="autoRefreshToggle" class="btn btn-outline-primary">
                    <i data-lucide="refresh-cw" class="me-1"></i>
                    <span>Auto-refresh: ON</span>
                </button>
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-secondary">
                    <i data-lucide="list" class="me-1"></i>
                    List View
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Map -->
    <div class="col-lg-9">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">
                        <i data-lucide="map" class="me-2"></i>
                        Live Map
                    </h5>
                    <small class="text-muted">
                        Last updated: <span id="lastUpdate" class="pulse">Just now</span>
                    </small>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="map.setView([9.0820, 8.6753], 6)">
                        <i data-lucide="globe"></i> Nigeria
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="fitBounds">
                        <i data-lucide="maximize"></i> Fit All
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="deliveryMap"></div>
            </div>
        </div>

        <!-- Legend -->
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="mb-3">Map Legend</h6>
                <div class="row">
                    <div class="col-md-3">
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #10b981;"></div>
                            <span>Available Rider</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #f59e0b;"></div>
                            <span>Busy Rider</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #8b5cf6;"></div>
                            <span>Pickup Location</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="legend-item">
                            <div class="legend-dot" style="background: #ef4444;"></div>
                            <span>Delivery Location</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Sidebar -->
    <div class="col-lg-3">
        <div class="stats-sidebar">
            <!-- Live Stats -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i data-lucide="activity" class="me-2"></i>
                        Live Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Active Deliveries</span>
                            <span class="fw-bold" id="activeCount">{{ $activeDeliveries->count() }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: 70%;"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Active Riders</span>
                            <span class="fw-bold" id="ridersCount">{{ $activeRiders->count() }}</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 85%;"></div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Available Riders</span>
                            <span class="fw-bold text-success">
                                {{ $activeRiders->where('status', 'available')->count() }}
                            </span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 60%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Deliveries List -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i data-lucide="truck" class="me-2"></i>
                        Active Deliveries
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="activeDeliveriesList" style="max-height: 400px; overflow-y: auto;">
                        @forelse($activeDeliveries as $delivery)
                        <div class="p-3 border-bottom delivery-item" 
                             data-delivery-id="{{ $delivery->id }}"
                             style="cursor: pointer;"
                             onclick="focusDelivery({{ $delivery->id }})">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <div class="fw-medium">{{ $delivery->order->order_number }}</div>
                                    <small class="text-muted">ID: #{{ $delivery->id }}</small>
                                </div>
                                <span class="badge bg-{{ $delivery->status_badge }}">
                                    {{ $delivery->status_label }}
                                </span>
                            </div>
                            
                            @if($delivery->rider)
                            <div class="d-flex align-items-center mb-2">
                                <img src="{{ $delivery->rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($delivery->rider->full_name) }}" 
                                     class="rounded-circle me-2" 
                                     width="24" height="24"
                                     alt="{{ $delivery->rider->full_name }}">
                                <small>{{ $delivery->rider->full_name }}</small>
                            </div>
                            @endif

                            <div class="small text-muted">
                                <div class="mb-1">
                                    <i data-lucide="map-pin" style="width: 12px; height: 12px;"></i>
                                    {{ Str::limit($delivery->pickup_address, 30) }}
                                </div>
                                <div>
                                    <i data-lucide="navigation" style="width: 12px; height: 12px;"></i>
                                    {{ Str::limit($delivery->delivery_address, 30) }}
                                </div>
                            </div>

                            <div class="mt-2 d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    {{ $delivery->created_at->diffForHumans() }}
                                </small>
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" 
                                   class="btn btn-sm btn-outline-primary"
                                   onclick="event.stopPropagation();">
                                    <i data-lucide="eye"></i>
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center">
                            <i data-lucide="inbox" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                            <p class="text-muted mb-0 small">No active deliveries</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    lucide.createIcons();

    // Initialize map centered on Nigeria
    const map = L.map('deliveryMap').setView([9.0820, 8.6753], 6);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Store markers
    const markers = {
        riders: {},
        pickups: {},
        deliveries: {}
    };

    // Add deliveries to map
    const deliveries = @json($activeDeliveries);
    const riders = @json($activeRiders);

    // Function to create rider marker
    function createRiderMarker(rider) {
        if (!rider.current_latitude || !rider.current_longitude) return;

        const icon = L.divIcon({
            className: 'custom-marker',
            html: `<div class="rider-marker ${rider.status}">🏍️</div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        const marker = L.marker([rider.current_latitude, rider.current_longitude], { icon })
            .addTo(map)
            .bindPopup(`
                <div>
                    <strong>${rider.full_name}</strong><br>
                    Status: <span class="badge bg-${rider.status === 'available' ? 'success' : 'warning'}">${rider.status}</span><br>
                    Rating: ⭐ ${rider.rating}<br>
                    Active: ${rider.active_deliveries ? rider.active_deliveries.length : 0} deliveries
                </div>
            `);

        markers.riders[rider.id] = marker;
    }

    // Function to create delivery markers
    function createDeliveryMarkers(delivery) {
        // Pickup marker
        if (delivery.pickup_latitude && delivery.pickup_longitude) {
            const pickupIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div class="pickup-marker">📦</div>',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            const pickupMarker = L.marker(
                [delivery.pickup_latitude, delivery.pickup_longitude], 
                { icon: pickupIcon }
            )
            .addTo(map)
            .bindPopup(`
                <div>
                    <strong>Pickup Location</strong><br>
                    Order: ${delivery.order.order_number}<br>
                    Address: ${delivery.pickup_address}
                </div>
            `);

            markers.pickups[delivery.id] = pickupMarker;
        }

        // Delivery marker
        if (delivery.delivery_latitude && delivery.delivery_longitude) {
            const deliveryIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div class="delivery-marker">📍</div>',
                iconSize: [32, 32],
                iconAnchor: [16, 16]
            });

            const deliveryMarker = L.marker(
                [delivery.delivery_latitude, delivery.delivery_longitude],
                { icon: deliveryIcon }
            )
            .addTo(map)
            .bindPopup(`
                <div>
                    <strong>Delivery Location</strong><br>
                    Order: ${delivery.order.order_number}<br>
                    Address: ${delivery.delivery_address}
                </div>
            `);

            markers.deliveries[delivery.id] = deliveryMarker;
        }

        // Draw route if rider and locations exist
        if (delivery.rider && delivery.rider.current_latitude && delivery.pickup_latitude) {
            const route = L.polyline([
                [delivery.rider.current_latitude, delivery.rider.current_longitude],
                [delivery.pickup_latitude, delivery.pickup_longitude],
                [delivery.delivery_latitude, delivery.delivery_longitude]
            ], {
                color: '#3b82f6',
                weight: 3,
                opacity: 0.5,
                dashArray: '10, 10'
            }).addTo(map);
        }
    }

    // Add all markers
    riders.forEach(rider => createRiderMarker(rider));
    deliveries.forEach(delivery => createDeliveryMarkers(delivery));

    // Fit bounds to show all markers
    document.getElementById('fitBounds').addEventListener('click', function() {
        const allLatLngs = [];
        
        Object.values(markers.riders).forEach(marker => allLatLngs.push(marker.getLatLng()));
        Object.values(markers.pickups).forEach(marker => allLatLngs.push(marker.getLatLng()));
        Object.values(markers.deliveries).forEach(marker => allLatLngs.push(marker.getLatLng()));

        if (allLatLngs.length > 0) {
            const bounds = L.latLngBounds(allLatLngs);
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    });

    // Focus on specific delivery
    function focusDelivery(deliveryId) {
        const delivery = deliveries.find(d => d.id === deliveryId);
        if (delivery && delivery.pickup_latitude && delivery.pickup_longitude) {
            map.setView([delivery.pickup_latitude, delivery.pickup_longitude], 14);
            
            if (markers.pickups[deliveryId]) {
                markers.pickups[deliveryId].openPopup();
            }
        }
    }

    // Auto-refresh functionality
    let autoRefreshEnabled = true;
    let refreshInterval;

    function updateMap() {
        fetch('{{ route("admin.deliveries.liveData") }}')
            .then(response => response.json())
            .then(data => {
                console.log('Map updated:', data.timestamp);
                
                // Update stats
                document.getElementById('activeCount').textContent = data.stats.active;
                document.getElementById('lastUpdate').textContent = 'Just now';
                
                // TODO: Update marker positions
                // This would require comparing new data with existing markers
                // and updating positions smoothly
            })
            .catch(error => console.error('Update failed:', error));
    }

    function startAutoRefresh() {
        refreshInterval = setInterval(updateMap, 10000); // 10 seconds
    }

    function stopAutoRefresh() {
        clearInterval(refreshInterval);
    }

    // Toggle auto-refresh
    document.getElementById('autoRefreshToggle').addEventListener('click', function() {
        autoRefreshEnabled = !autoRefreshEnabled;
        
        if (autoRefreshEnabled) {
            startAutoRefresh();
            this.querySelector('span').textContent = 'Auto-refresh: ON';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-primary');
        } else {
            stopAutoRefresh();
            this.querySelector('span').textContent = 'Auto-refresh: OFF';
            this.classList.remove('btn-primary');
            this.classList.add('btn-outline-primary');
        }
        
        lucide.createIcons();
    });

    // Start auto-refresh
    startAutoRefresh();

    // Cleanup on page leave
    window.addEventListener('beforeunload', function() {
        stopAutoRefresh();
    });
</script>
@endpush
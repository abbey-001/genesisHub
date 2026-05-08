@extends('admin.layouts.app')

@section('title', 'Live Rider Tracking')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back to Riders
                    </a>
                    <button type="button" class="btn btn-info" id="refreshMap">
                        <i data-lucide="refresh-cw" class="me-1"></i>Refresh
                    </button>
                </div>
            </div>
            <h4 class="page-title">Live Rider Tracking Map</h4>
            <p class="text-muted">Real-time location of all active riders</p>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                                <i data-lucide="radio" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Online Riders</p>
                        <h4 class="mb-0">{{ $onlineRiders->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                                <i data-lucide="truck" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Busy Riders</p>
                        <h4 class="mb-0">{{ $busyRiders->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="package" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Active Deliveries</p>
                        <h4 class="mb-0">{{ $busyRiders->sum(fn($r) => $r->activeDeliveries->count()) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map and Rider List -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="map" class="me-2"></i>Rider Locations
                </h5>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 600px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Online Riders -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i data-lucide="radio" class="me-2"></i>Online Riders ({{ $onlineRiders->count() }})
                </h5>
            </div>
            <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
                @forelse($onlineRiders as $rider)
                <div class="d-flex align-items-center p-3 border-bottom rider-item" 
                     data-lat="{{ $rider->current_latitude }}" 
                     data-lng="{{ $rider->current_longitude }}"
                     data-name="{{ $rider->full_name }}"
                     style="cursor: pointer;">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-success bg-opacity-10 text-success rounded-circle">
                            {{ strtoupper(substr($rider->full_name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-0">{{ $rider->full_name }}</h6>
                        <small class="text-muted">{{ ucfirst($rider->vehicle_type) }}</small>
                    </div>
                    <div>
                        <span class="badge bg-success">Available</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i data-lucide="users" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                    <p class="text-muted mb-0 small">No riders online</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Busy Riders -->
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i data-lucide="truck" class="me-2"></i>Busy Riders ({{ $busyRiders->count() }})
                </h5>
            </div>
            <div class="card-body p-0" style="max-height: 280px; overflow-y: auto;">
                @forelse($busyRiders as $rider)
                <div class="d-flex align-items-start p-3 border-bottom rider-item" 
                     data-lat="{{ $rider->current_latitude }}" 
                     data-lng="{{ $rider->current_longitude }}"
                     data-name="{{ $rider->full_name }}"
                     style="cursor: pointer;">
                    <div class="avatar-sm me-3 flex-shrink-0">
                        <div class="avatar-title bg-info bg-opacity-10 text-info rounded-circle">
                            {{ strtoupper(substr($rider->full_name, 0, 1)) }}
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $rider->full_name }}</h6>
                        <small class="text-muted d-block mb-2">{{ ucfirst($rider->vehicle_type) }}</small>
                        @if($rider->activeDeliveries->count() > 0)
                        <div class="mt-2">
                            @foreach($rider->activeDeliveries as $delivery)
                            <div class="mb-1">
                                <span class="badge bg-warning text-dark small">
                                    {{ $delivery->order->order_number }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div>
                        <span class="badge bg-info">Busy</span>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <i data-lucide="truck" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                    <p class="text-muted mb-0 small">No busy riders</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .rider-item:hover {
        background-color: #f8f9fa;
    }
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
    }
    .leaflet-popup-content {
        margin: 13px 15px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    lucide.createIcons();

    // Initialize map centered on Abuja
    const map = L.map('map').setView([9.0765, 7.3986], 12);

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Define custom icons
    const onlineIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background-color: #28a745; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><i class="bi bi-bicycle" style="color: white; font-size: 16px;"></i></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    const busyIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background-color: #17a2b8; width: 30px; height: 30px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><i class="bi bi-bicycle" style="color: white; font-size: 16px;"></i></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 15]
    });

    const markers = [];

    // Add online riders
    @foreach($onlineRiders as $rider)
    const onlineMarker{{ $rider->id }} = L.marker([{{ $rider->current_latitude }}, {{ $rider->current_longitude }}], { icon: onlineIcon })
        .addTo(map)
        .bindPopup(`
            <div class="text-center">
                <h6 class="mb-2">{{ $rider->full_name }}</h6>
                <span class="badge bg-success mb-2">Available</span>
                <p class="mb-1 small"><strong>Vehicle:</strong> {{ ucfirst($rider->vehicle_type) }}</p>
                <p class="mb-2 small"><strong>Rating:</strong> ⭐ {{ number_format($rider->rating, 1) }}</p>
                <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-sm btn-primary">View Profile</a>
            </div>
        `);
    markers.push(onlineMarker{{ $rider->id }});
    @endforeach

    // Add busy riders
    @foreach($busyRiders as $rider)
    const busyMarker{{ $rider->id }} = L.marker([{{ $rider->current_latitude }}, {{ $rider->current_longitude }}], { icon: busyIcon })
        .addTo(map)
        .bindPopup(`
            <div class="text-center">
                <h6 class="mb-2">{{ $rider->full_name }}</h6>
                <span class="badge bg-info mb-2">Busy</span>
                <p class="mb-1 small"><strong>Vehicle:</strong> {{ ucfirst($rider->vehicle_type) }}</p>
                <p class="mb-1 small"><strong>Active Deliveries:</strong> {{ $rider->activeDeliveries->count() }}</p>
                <p class="mb-2 small"><strong>Rating:</strong> ⭐ {{ number_format($rider->rating, 1) }}</p>
                <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-sm btn-primary">View Profile</a>
            </div>
        `);
    markers.push(busyMarker{{ $rider->id }});
    @endforeach

    // Fit map to show all markers
    if (markers.length > 0) {
        const group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }

    // Click on rider in list to center map
    document.querySelectorAll('.rider-item').forEach(item => {
        item.addEventListener('click', function() {
            const lat = parseFloat(this.dataset.lat);
            const lng = parseFloat(this.dataset.lng);
            map.setView([lat, lng], 15);
            
            // Find and open the corresponding marker popup
            markers.forEach(marker => {
                const markerLatLng = marker.getLatLng();
                if (markerLatLng.lat === lat && markerLatLng.lng === lng) {
                    marker.openPopup();
                }
            });
        });
    });

    // Refresh map
    document.getElementById('refreshMap').addEventListener('click', function() {
        const btn = this;
        const icon = btn.querySelector('i');
        icon.style.animation = 'spin 1s linear infinite';
        
        setTimeout(() => {
            location.reload();
        }, 500);
    });

    // Add rotation animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Auto-refresh every 30 seconds
    setInterval(() => {
        location.reload();
    }, 30000);
</script>
@endpush
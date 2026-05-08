@extends('admin.layouts.app')

@section('title', 'Unassigned Deliveries')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">⚠️ Unassigned Delivery Queue</h4>
                <p class="text-muted mb-0">Deliveries waiting for rider assignment</p>
            </div>
            <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="me-1"></i>
                Back to All Deliveries
            </a>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Unassigned Deliveries</p>
                        <h4 class="fw-bold text-warning mb-0">{{ $deliveries->total() }}</h4>
                        <small class="text-muted">Needs immediate attention</small>
                    </div>
                    <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                        <i data-lucide="alert-circle" class="text-warning fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Available Riders</p>
                        <h4 class="fw-bold text-success mb-0">{{ $availableRiders->count() }}</h4>
                        <small class="text-muted">Ready to accept</small>
                    </div>
                    <div class="avatar-sm bg-success bg-opacity-10 rounded">
                        <i data-lucide="users" class="text-success fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Avg Wait Time</p>
                        <h4 class="fw-bold text-info mb-0">
                            @if($deliveries->count() > 0)
                                {{ number_format($deliveries->avg(function($d) { return $d->created_at->diffInMinutes(now()); }), 0) }} min
                            @else
                                0 min
                            @endif
                        </h4>
                        <small class="text-muted">Time waiting for assignment</small>
                    </div>
                    <div class="avatar-sm bg-info bg-opacity-10 rounded">
                        <i data-lucide="clock" class="text-info fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deliveries List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Pending Assignments ({{ $deliveries->total() }})
                </h5>
            </div>
            <div class="card-body p-0">
                @forelse($deliveries as $delivery)
                <div class="border-bottom p-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-warning bg-opacity-10 rounded me-3 flex-shrink-0 d-flex align-items-center justify-content-center">
                                    <i data-lucide="package" class="text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-dark">
                                            Delivery #{{ $delivery->id }}
                                        </a>
                                        <span class="badge bg-warning ms-2">Unassigned</span>
                                    </h6>
                                    <p class="text-muted mb-1 small">
                                        Order: {{ $delivery->order->order_number }}
                                    </p>
                                    <p class="mb-0 small">
                                        <i data-lucide="clock" style="width: 14px; height: 14px;"></i>
                                        Waiting {{ $delivery->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div>
                                <small class="text-muted d-block">Seller</small>
                                <div class="fw-medium">{{ $delivery->seller->shop->shop_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ Str::limit($delivery->pickup_address, 30) }}</small>
                            </div>
                        </div>

                        <div class="col-md-3 text-end">
                            <div class="mb-2">
                                <div class="text-success fw-bold">₦{{ number_format($delivery->delivery_fee, 0) }}</div>
                                <small class="text-muted">{{ $delivery->items->count() }} item(s)</small>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                @can('deliveries.assign')
                                <a href="{{ route('admin.deliveries.assignPage', $delivery) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i data-lucide="user-plus" class="me-1"></i>
                                    Assign
                                </a>
                                <a href="{{ route('admin.deliveries.broadcastPage', $delivery) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i data-lucide="radio" class="me-1"></i>
                                    Broadcast
                                </a>
                                @endcan
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i data-lucide="eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Info (Expandable) -->
                    <div class="collapse mt-3 pt-3 border-top" id="details{{ $delivery->id }}">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-muted small mb-2">PICKUP DETAILS</h6>
                                <p class="mb-1"><strong>{{ $delivery->seller->shop->shop_name ?? 'N/A' }}</strong></p>
                                <p class="text-muted small mb-0">{{ $delivery->pickup_address }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted small mb-2">DELIVERY DETAILS</h6>
                                <p class="mb-1"><strong>{{ $delivery->order->customer_name }}</strong></p>
                                <p class="text-muted small mb-0">{{ $delivery->delivery_address }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted small mb-2">PACKAGE INFO</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="small"><strong>Weight:</strong> {{ $delivery->package_weight ?? 'N/A' }} kg</li>
                                    <li class="small"><strong>Items:</strong> {{ $delivery->items->count() }}</li>
                                    <li class="small"><strong>Fee:</strong> ₦{{ number_format($delivery->delivery_fee, 0) }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-sm btn-link text-muted p-0 mt-2" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#details{{ $delivery->id }}">
                        <i data-lucide="chevron-down"></i>
                        <span>Show/Hide Details</span>
                    </button>
                </div>
                @empty
                <div class="text-center py-5">
                    <i data-lucide="check-circle" class="text-success mb-3" style="width: 64px; height: 64px;"></i>
                    <h5 class="mb-2">All Caught Up! 🎉</h5>
                    <p class="text-muted mb-0">There are no unassigned deliveries at the moment.</p>
                </div>
                @endforelse
            </div>

            @if($deliveries->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} 
                        of {{ $deliveries->total() }} deliveries
                    </div>
                    {{ $deliveries->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Available Riders Quick View -->
@if($availableRiders->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="users" class="me-2"></i>
                    Available Riders ({{ $availableRiders->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($availableRiders->take(6) as $rider)
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-2 border rounded">
                            <img src="{{ $rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($rider->full_name) }}" 
                                 class="rounded-circle me-3" 
                                 width="50" height="50"
                                 alt="{{ $rider->full_name }}">
                            <div class="flex-grow-1">
                                <h6 class="mb-0">{{ $rider->full_name }}</h6>
                                <small class="text-muted">
                                    <i data-lucide="star" class="text-warning" style="width: 12px; height: 12px;"></i>
                                    {{ number_format($rider->rating, 1) }}
                                </small>
                                <br>
                                <span class="badge bg-success badge-sm">Available</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    lucide.createIcons();

    // Auto-refresh every 30 seconds
    setInterval(function() {
        location.reload();
    }, 30000);
</script>
@endpush
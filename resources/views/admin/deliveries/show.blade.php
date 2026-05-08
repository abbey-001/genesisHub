@extends('admin.layouts.app')

@section('title', 'Delivery Details')

@section('content')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    margin-bottom: 25px;
    opacity: 0.5;
}

.timeline-item.active {
    opacity: 1;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-marker i {
    width: 16px;
    height: 16px;
}

.timeline-content {
    padding-left: 20px;
}
</style>
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.deliveries.index') }}">Deliveries</a></li>
                <li class="breadcrumb-item active">Delivery #{{ $delivery->id }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">📦 Delivery #{{ $delivery->id }}</h4>
                <p class="text-muted mb-0">Order: {{ $delivery->order->order_number }}</p>
            </div>
            <div class="d-flex gap-2">
                @if($delivery->status === 'pending' && !$delivery->rider_id)
                    @can('deliveries.assign')
                    <a href="{{ route('admin.deliveries.assignPage', $delivery) }}" class="btn btn-primary">
                        <i data-lucide="user-plus" class="me-1"></i>
                        Assign Rider
                    </a>
                    <a href="{{ route('admin.deliveries.broadcastPage', $delivery) }}" class="btn btn-outline-primary">
                        <i data-lucide="radio" class="me-1"></i>
                        Broadcast
                    </a>
                    @endcan
                @endif
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>
                    Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Status Timeline -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="activity" class="me-2"></i>
                    Delivery Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item {{ $delivery->status === 'delivered' ? 'active' : '' }}">
                        <div class="timeline-marker {{ $delivery->delivered_at ? 'bg-success' : 'bg-secondary' }}">
                            <i data-lucide="check-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Delivered</h6>
                            @if($delivery->delivered_at)
                                <p class="text-muted mb-0">{{ $delivery->delivered_at->format('M d, Y h:i A') }}</p>
                            @else
                                <p class="text-muted mb-0">Not yet delivered</p>
                            @endif
                        </div>
                    </div>

                    <div class="timeline-item {{ in_array($delivery->status, ['en_route_delivery', 'delivered']) ? 'active' : '' }}">
                        <div class="timeline-marker {{ in_array($delivery->status, ['en_route_delivery', 'delivered']) ? 'bg-primary' : 'bg-secondary' }}">
                            <i data-lucide="truck"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">En Route to Customer</h6>
                            <p class="text-muted mb-0">
                                @if($delivery->status === 'en_route_delivery')
                                    In progress
                                @elseif($delivery->delivered_at)
                                    Completed
                                @else
                                    Pending
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item {{ in_array($delivery->status, ['picked_up', 'en_route_delivery', 'delivered']) ? 'active' : '' }}">
                        <div class="timeline-marker {{ in_array($delivery->status, ['picked_up', 'en_route_delivery', 'delivered']) ? 'bg-warning' : 'bg-secondary' }}">
                            <i data-lucide="package-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Picked Up from Seller</h6>
                            @if($delivery->picked_up_at)
                                <p class="text-muted mb-0">{{ $delivery->picked_up_at->format('M d, Y h:i A') }}</p>
                            @else
                                <p class="text-muted mb-0">Not yet picked up</p>
                            @endif
                        </div>
                    </div>

                    <div class="timeline-item {{ in_array($delivery->status, ['en_route_pickup', 'picked_up', 'en_route_delivery', 'delivered']) ? 'active' : '' }}">
                        <div class="timeline-marker {{ in_array($delivery->status, ['en_route_pickup', 'picked_up', 'en_route_delivery', 'delivered']) ? 'bg-info' : 'bg-secondary' }}">
                            <i data-lucide="navigation"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">En Route to Pickup</h6>
                            <p class="text-muted mb-0">
                                @if(in_array($delivery->status, ['en_route_pickup', 'picked_up', 'en_route_delivery', 'delivered']))
                                    Completed
                                @else
                                    Pending
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="timeline-item active">
                        <div class="timeline-marker {{ $delivery->assigned_at ? 'bg-success' : 'bg-secondary' }}">
                            <i data-lucide="user-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Rider Assigned</h6>
                            @if($delivery->assigned_at)
                                <p class="text-muted mb-0">{{ $delivery->assigned_at->format('M d, Y h:i A') }}</p>
                            @else
                                <p class="text-muted mb-0">Not assigned</p>
                            @endif
                        </div>
                    </div>

                    <div class="timeline-item active">
                        <div class="timeline-marker bg-primary">
                            <i data-lucide="plus-circle"></i>
                        </div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Delivery Created</h6>
                            <p class="text-muted mb-0">{{ $delivery->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Items -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="package" class="me-2"></i>
                    Package Items ({{ $delivery->items->count() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($delivery->items as $item)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($item->product)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}" 
                                            class="rounded me-2" 
                                            width="40" height="40"
                                            alt="{{ $item->product_name }}">
                                    @endif
                                    <div>{{ $item->product_name }}</div>
                                </div>
                            </td>

                            <td>{{ $item->product_sku ?? 'N/A' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₦{{ number_format($item->price ?? 0, 2) }}</td>
                        </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Assignment History -->
        @if($delivery->assignmentHistory->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="history" class="me-2"></i>
                    Assignment History
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Rider</th>
                                <th>Method</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($delivery->assignmentHistory as $history)
                            <tr>
                                <td>{{ $history->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <span class="badge bg-{{ $history->action === 'assigned' ? 'success' : ($history->action === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($history->action) }}
                                    </span>
                                </td>
                                <td>{{ $history->rider->full_name ?? 'N/A' }}</td>
                                <td>{{ ucfirst($history->method) }}</td>
                                <td>{{ $history->reason ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Current Status -->
        <div class="card mb-4">
            <div class="card-header bg-{{ $delivery->status_badge }}">
                <h5 class="mb-0 text-white">
                    <i data-lucide="info" class="me-2"></i>
                    Current Status
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h3 class="text-{{ $delivery->status_badge }} mb-2">{{ $delivery->status_label }}</h3>
                    <p class="text-muted mb-0">Last updated: {{ $delivery->updated_at->diffForHumans() }}</p>
                </div>

                @can('deliveries.update')
                <button type="button" class="btn btn-outline-primary w-100" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                    <i data-lucide="edit" class="me-1"></i>
                    Update Status
                </button>
                @endcan
            </div>
        </div>

        <!-- Rider Info -->
        @if($delivery->rider)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="user" class="me-2"></i>
                    Assigned Rider
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="{{ $delivery->rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($delivery->rider->full_name) }}" 
                         class="rounded-circle mb-2" 
                         width="80" height="80"
                         alt="{{ $delivery->rider->full_name }}">
                    <h6 class="mb-1">{{ $delivery->rider->full_name }}</h6>
                    <p class="text-muted mb-2">{{ $delivery->rider->phone_number }}</p>
                    <div class="mb-2">
                        <i data-lucide="star" class="text-warning"></i>
                        <span>{{ number_format($delivery->rider->rating, 1) }}</span>
                    </div>
                    <span class="badge bg-{{ $delivery->rider->status === 'available' ? 'success' : 'warning' }}">
                        {{ ucfirst($delivery->rider->status) }}
                    </span>
                </div>

                <div class="d-grid gap-2">
                    <a href="tel:{{ $delivery->rider->phone_number }}" class="btn btn-outline-primary btn-sm">
                        <i data-lucide="phone" class="me-1"></i>
                        Call Rider
                    </a>
                    @can('deliveries.assign')
                    <button type="button" class="btn btn-outline-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reassignModal">
                        <i data-lucide="refresh-cw" class="me-1"></i>
                        Reassign
                    </button>
                    @endcan
                </div>
            </div>
        </div>
        @else
        <div class="card mb-4 border-warning">
            <div class="card-body text-center">
                <i data-lucide="alert-circle" class="text-warning mb-3" style="width: 48px; height: 48px;"></i>
                <h6 class="mb-2">No Rider Assigned</h6>
                <p class="text-muted mb-3">This delivery needs a rider</p>
                @can('deliveries.assign')
                <a href="{{ route('admin.deliveries.assignPage', $delivery) }}" class="btn btn-warning w-100">
                    <i data-lucide="user-plus" class="me-1"></i>
                    Assign Rider
                </a>
                @endcan
            </div>
        </div>
        @endif

        <!-- Delivery Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="info" class="me-2"></i>
                    Delivery Details
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Delivery Fee</small>
                    <h5 class="text-success mb-0">₦{{ number_format($delivery->delivery_fee, 2) }}</h5>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Package Weight</small>
                    <div>{{ $delivery->package_weight ?? 'N/A' }} kg</div>
                </div>

                @if($delivery->delivery_otp)
                <div class="mb-3">
                    <small class="text-muted d-block">Delivery OTP</small>
                    <h4 class="mb-0 font-monospace">{{ $delivery->delivery_otp }}</h4>
                </div>
                @endif

                @if($delivery->estimated_pickup_time)
                <div class="mb-3">
                    <small class="text-muted d-block">Est. Pickup Time</small>
                    <div>{{ $delivery->estimated_pickup_time->format('M d, Y h:i A') }}</div>
                </div>
                @endif

                @if($delivery->estimated_delivery_time)
                <div class="mb-3">
                    <small class="text-muted d-block">Est. Delivery Time</small>
                    <div>{{ $delivery->estimated_delivery_time->format('M d, Y h:i A') }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Locations -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="map-pin" class="me-2"></i>
                    Locations
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">
                        <i data-lucide="store" class="me-1"></i>
                        Pickup (Seller)
                    </small>
                    <p class="mb-1">{{ $delivery->seller->shop->shop_name ?? 'N/A' }}</p>
                    <p class="text-muted small mb-0">{{ $delivery->pickup_address }}</p>
                </div>

                <div class="mb-0">
                    <small class="text-muted d-block mb-2">
                        <i data-lucide="home" class="me-1"></i>
                        Delivery (Customer)
                    </small>
                    <p class="mb-1">{{ $delivery->order->customer_name }}</p>
                    <p class="text-muted small mb-0">{{ $delivery->delivery_address }}</p>
                </div>
            </div>
        </div>

        <!-- Proof Images -->
        @if($delivery->pickup_photo || $delivery->delivery_proof)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="image" class="me-2"></i>
                    Proof Photos
                </h5>
            </div>
            <div class="card-body">
                @if($delivery->pickup_photo)
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">Pickup Photo</small>
                    <img src="{{ asset('storage/' . $delivery->pickup_photo) }}" class="img-fluid rounded" alt="Pickup proof">
                </div>
                @endif

                @if($delivery->delivery_proof)
                <div>
                    <small class="text-muted d-block mb-2">Delivery Proof</small>
                    <img src="{{ asset('storage/' . $delivery->delivery_proof) }}" class="img-fluid rounded" alt="Delivery proof">
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Delivery Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.deliveries.updateStatus', $delivery) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">New Status</label>
                        <select name="status" class="form-select" required>
                            <option value="pending" {{ $delivery->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="assigned" {{ $delivery->status === 'assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="en_route_pickup" {{ $delivery->status === 'en_route_pickup' ? 'selected' : '' }}>En Route to Pickup</option>
                            <option value="picked_up" {{ $delivery->status === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                            <option value="en_route_delivery" {{ $delivery->status === 'en_route_delivery' ? 'selected' : '' }}>En Route to Delivery</option>
                            <option value="delivered" {{ $delivery->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ $delivery->status === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reassign Modal -->
<div class="modal fade" id="reassignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Reassign Delivery</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.deliveries.reassign', $delivery) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        This will unassign the current rider and make the delivery available for reassignment.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Reassignment *</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Reassign</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection



@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
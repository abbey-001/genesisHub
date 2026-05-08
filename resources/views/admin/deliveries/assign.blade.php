@extends('admin.layouts.app')

@section('title', 'Assign Rider')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.deliveries.index') }}">Deliveries</a></li>
                <li class="breadcrumb-item active">Assign Rider</li>
            </ol>
        </nav>
        <h4 class="mb-0">👤 Assign Rider to Delivery</h4>
        <p class="text-muted mb-0">Select the best rider for this delivery</p>
    </div>
</div>

<div class="row">
    <!-- Delivery Details -->
    <div class="col-lg-4">
        <div class="card sticky-top" style="top: 20px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i data-lucide="package" class="me-2"></i>
                    Delivery Details
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Delivery ID</small>
                    <div class="fw-bold">#{{ $delivery->id }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Order Number</small>
                    <div class="fw-bold">{{ $delivery->order->order_number }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Seller</small>
                    <div>{{ $delivery->seller->shop->shop_name ?? 'N/A' }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Items</small>
                    <div>{{ $delivery->items->count() }} item(s)</div>
                    <ul class="list-unstyled mb-0 mt-2">
                        @foreach($delivery->items->take(3) as $item)
                        <li class="small">
                            <i data-lucide="package" class="text-primary" style="width: 14px; height: 14px;"></i>
                            {{ $item->product_name }} ({{ $item->quantity }})
                        </li>
                        @endforeach
                        @if($delivery->items->count() > 3)
                        <li class="small text-muted">
                            +{{ $delivery->items->count() - 3 }} more items
                        </li>
                        @endif
                    </ul>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Package Weight</small>
                    <div>{{ $delivery->package_weight ?? 'N/A' }} kg</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Delivery Fee</small>
                    <div class="fw-bold text-success fs-5">₦{{ number_format($delivery->delivery_fee, 0) }}</div>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2">
                        <i data-lucide="map-pin" class="me-1"></i>
                        Pickup Location
                    </small>
                    <div class="small">{{ $delivery->pickup_address }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2">
                        <i data-lucide="navigation" class="me-1"></i>
                        Delivery Location
                    </small>
                    <div class="small">{{ $delivery->delivery_address }}</div>
                </div>

                @if($delivery->package_notes)
                <div class="mb-0">
                    <small class="text-muted d-block mb-2">
                        <i data-lucide="file-text" class="me-1"></i>
                        Package Notes
                    </small>
                    <div class="small">{{ $delivery->package_notes }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Available Riders -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i data-lucide="users" class="me-2"></i>
                    Available Riders ({{ $riders->count() }})
                </h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.deliveries.broadcastPage', $delivery) }}" 
                       class="btn btn-outline-primary">
                        <i data-lucide="radio" class="me-1"></i>
                        Broadcast Instead
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if($riders->isEmpty())
                <div class="text-center py-5">
                    <i data-lucide="user-x" class="text-muted mb-3" style="width: 64px; height: 64px;"></i>
                    <h6 class="mb-2">No Available Riders</h6>
                    <p class="text-muted mb-3">There are no riders available for assignment at this time.</p>
                    <a href="{{ route('admin.deliveries.broadcastPage', $delivery) }}" class="btn btn-primary">
                        <i data-lucide="radio" class="me-1"></i>
                        Try Broadcasting
                    </a>
                </div>
                @else
                <div class="row g-3">
                    @foreach($riders as $rider)
                    <div class="col-md-6">
                        <div class="card border h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-start mb-3">
                                    <img src="{{ $rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($rider->full_name) }}" 
                                         class="rounded-circle me-3" 
                                         width="60" height="60"
                                         alt="{{ $rider->full_name }}">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $rider->full_name }}</h6>
                                        <div class="mb-2">
                                            <span class="badge bg-{{ $rider->status === 'available' ? 'success' : 'warning' }}">
                                                {{ ucfirst($rider->status) }}
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <div>
                                                <i data-lucide="star" class="text-warning" style="width: 14px; height: 14px;"></i>
                                                <span class="small">{{ number_format($rider->rating, 1) }}</span>
                                            </div>
                                            <div>
                                                <i data-lucide="check-circle" class="text-success" style="width: 14px; height: 14px;"></i>
                                                <span class="small">{{ $rider->completed_deliveries }} completed</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">Vehicle</small>
                                            <div class="fw-medium">{{ ucfirst($rider->vehicle_type) }}</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">Distance</small>
                                            <div class="fw-medium">
                                                @if(isset($rider->distance))
                                                    {{ number_format($rider->distance, 1) }} km
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">Active</small>
                                            <div class="fw-medium">{{ $rider->activeDeliveries->count() }}/3</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light p-2 rounded">
                                            <small class="text-muted d-block">Success Rate</small>
                                            <div class="fw-medium text-success">
                                                {{ $rider->completed_deliveries > 0 ? number_format(($rider->completed_deliveries / ($rider->completed_deliveries + $rider->failed_deliveries)) * 100, 0) : 100 }}%
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if($rider->activeDeliveries->count() > 0)
                                <div class="alert alert-info py-2 mb-3">
                                    <small>
                                        <i data-lucide="truck" style="width: 14px; height: 14px;"></i>
                                        Currently handling {{ $rider->activeDeliveries->count() }} 
                                        {{ Str::plural('delivery', $rider->activeDeliveries->count()) }}
                                    </small>
                                </div>
                                @endif

                                <button type="button" 
                                        class="btn btn-primary w-100" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#assignModal{{ $rider->id }}">
                                    <i data-lucide="user-check" class="me-1"></i>
                                    Assign This Rider
                                </button>
                            </div>
                        </div>

                        <!-- Assignment Modal -->
                        <div class="modal fade" id="assignModal{{ $rider->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i data-lucide="user-check" class="me-2"></i>
                                            Confirm Assignment
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('admin.deliveries.assign', $delivery) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="rider_id" value="{{ $rider->id }}">
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <i data-lucide="info" class="me-2"></i>
                                                You are about to assign <strong>{{ $rider->full_name }}</strong> to this delivery.
                                            </div>

                                            <div class="mb-3">
                                                <h6>Delivery Summary</h6>
                                                <ul class="list-unstyled mb-0">
                                                    <li>Order: <strong>{{ $delivery->order->order_number }}</strong></li>
                                                    <li>Fee: <strong class="text-success">₦{{ number_format($delivery->delivery_fee, 0) }}</strong></li>
                                                    <li>Items: <strong>{{ $delivery->items->count() }}</strong></li>
                                                </ul>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Assignment Notes (Optional)</label>
                                                <textarea name="notes" 
                                                          class="form-control" 
                                                          rows="3"
                                                          placeholder="Add any special instructions for the rider..."></textarea>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="confirmAssign{{ $rider->id }}" 
                                                       required>
                                                <label class="form-check-label" for="confirmAssign{{ $rider->id }}">
                                                    I confirm this assignment is correct
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i data-lucide="check" class="me-1"></i>
                                                Confirm Assignment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- Suggestions -->
        @if($riders->isNotEmpty())
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i data-lucide="lightbulb" class="me-2"></i>
                    Assignment Tips
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Riders closer to the pickup location will have faster pickup times</li>
                    <li>Consider the rider's current workload (active deliveries)</li>
                    <li>Higher-rated riders generally provide better service</li>
                    <li>If no suitable rider is available, try broadcasting to multiple riders</li>
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
@extends('rider.layouts.app')

@section('title', 'Failed Deliveries')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-x-circle me-2"></i>Failed Deliveries
            </h4>
            <p class="text-muted mb-0">Review unsuccessful delivery attempts</p>
        </div>
        <span class="badge bg-label-danger fs-6">{{ $deliveries->total() }} Failed</span>
    </div>

    @if($deliveries->count() > 0)
    <div class="row g-4">
        @foreach($deliveries as $delivery)
        <div class="col-lg-6">
            <div class="card border-danger">
                <div class="card-body">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1">Order #{{ $delivery->order->order_number }}</h6>
                            <span class="badge bg-danger">Failed</span>
                        </div>
                        <small class="text-muted">
                            {{ $delivery->failed_at->format('M d, Y h:i A') }}
                        </small>
                    </div>

                    <!-- Failure Info -->
                    <div class="alert alert-danger mb-3">
                        <div class="d-flex align-items-start">
                            <i class="bx bx-error-circle bx-sm me-2"></i>
                            <div>
                                <strong>Reason:</strong> {{ ucfirst(str_replace('_', ' ', $delivery->failure_reason)) }}<br>
                                <small>{{ $delivery->failure_notes }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Addresses -->
                    <div class="mb-3">
                        <div class="d-flex align-items-start mb-2">
                            <i class="bx bx-store text-muted me-2"></i>
                            <div>
                                <small class="text-muted d-block">Pickup</small>
                                <span class="small">{{ Str::limit($delivery->pickup_address, 50) }}</span>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <i class="bx bx-map text-muted me-2"></i>
                            <div>
                                <small class="text-muted d-block">Delivery</small>
                                <span class="small">{{ Str::limit($delivery->delivery_address, 50) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <i class="bx bx-package text-muted"></i>
                            <div class="small text-muted">Items</div>
                            <strong>{{ $delivery->items->count() }}</strong>
                        </div>
                        <div class="col-4">
                            <i class="bx bx-money text-muted"></i>
                            <div class="small text-muted">Fee</div>
                            <strong>₦{{ number_format($delivery->delivery_fee, 0) }}</strong>
                        </div>
                        <div class="col-4">
                            <i class="bx bx-user text-muted"></i>
                            <div class="small text-muted">Customer</div>
                            <strong class="small">{{ Str::limit($delivery->order->customer_name, 10) }}</strong>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <a href="{{ route('rider.deliveries.show', $delivery) }}" class="btn btn-sm btn-label-primary flex-fill">
                            <i class="bx bx-show me-1"></i>View Details
                        </a>
                        @if($delivery->failure_photo)
                        <a href="{{ asset('storage/' . $delivery->failure_photo) }}" target="_blank" class="btn btn-sm btn-label-secondary flex-fill">
                            <i class="bx bx-image me-1"></i>View Photo
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $deliveries->links() }}
    </div>

    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-check-shield bx-lg text-success mb-3"></i>
            <h5 class="mb-2">No Failed Deliveries</h5>
            <p class="text-muted mb-0">Great job! You haven't had any failed deliveries.</p>
        </div>
    </div>
    @endif

</div>
@endsection
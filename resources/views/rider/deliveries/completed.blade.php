@extends('rider.layouts.app')

@section('title', 'Completed Deliveries')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-check-circle me-2"></i>Completed Deliveries
            </h4>
            <p class="text-muted mb-0">Your delivery history and earnings</p>
        </div>
        <div class="text-end">
            <div class="mb-1">
                <span class="badge bg-label-success fs-6">{{ $deliveries->total() }} Completed</span>
            </div>
            <div>
                <small class="text-muted">Total Earnings: </small>
                <strong class="text-success">₦{{ number_format($totalEarnings, 0) }}</strong>
            </div>
        </div>
    </div>

    @if($deliveries->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Pickup Location</th>
                            <th>Delivery Location</th>
                            <th>Items</th>
                            <th>Delivered At</th>
                            <th>Earnings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deliveries as $delivery)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ $delivery->order->order_number }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ substr($delivery->order->customer_name, 0, 1) }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="small">{{ $delivery->order->customer_name }}</div>
                                        <small class="text-muted">{{ $delivery->order->customer_phone }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 150px;">
                                    @if($delivery->bundle_id)
                                        <span class="badge bg-label-primary d-block mb-1">
                                            <i class="bx bx-package me-1"></i>Bundle — {{ $delivery->bundle->pickup_zone ?? '' }}
                                        </span>
                                    @else
                                        <i class="bx bx-store text-muted me-1"></i>
                                        <small>{{ Str::limit($delivery->pickup_address, 30) }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div style="max-width: 150px;">
                                    <i class="bx bx-map text-muted me-1"></i>
                                    <small>{{ Str::limit($delivery->delivery_address, 30) }}</small>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $delivery->items->count() }}</span>
                            </td>
                            <td>
                                <div>{{ $delivery->delivered_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $delivery->delivered_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <strong class="text-success">₦{{ number_format($delivery->delivery_fee, 0) }}</strong>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('rider.deliveries.show', $delivery) }}">
                                                <i class="bx bx-show me-2"></i>View Details
                                            </a>
                                        </li>
                                        @if($delivery->delivery_proof)
                                        <li>
                                            <a class="dropdown-item" href="{{ asset('storage/' . $delivery->delivery_proof) }}" target="_blank">
                                                <i class="bx bx-image me-2"></i>View Proof
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} of {{ $deliveries->total() }} deliveries
                </div>
                <div>
                    {{ $deliveries->links() }}
                </div>
            </div>
        </div>
    </div>

    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-history bx-lg text-muted mb-3"></i>
            <h5 class="mb-2">No Completed Deliveries Yet</h5>
            <p class="text-muted mb-3">Start accepting and completing deliveries to see your history here.</p>
            <a href="{{ route('rider.deliveries.available') }}" class="btn btn-primary">
                <i class="bx bx-package me-1"></i>View Available Deliveries
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
@extends('rider.layouts.app')

@section('title', 'My Deliveries')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">My Deliveries</h4>
            <p class="text-muted mb-0">Manage all your delivery tasks</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('rider.deliveries.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="en_route_pickup" {{ request('status') === 'en_route_pickup' ? 'selected' : '' }}>En Route Pickup</option>
                            <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                            <option value="en_route_delivery" {{ request('status') === 'en_route_delivery' ? 'selected' : '' }}>En Route Delivery</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-filter-alt me-1"></i> Filter
                            </button>
                            <a href="{{ route('rider.deliveries.index') }}" class="btn btn-label-secondary">
                                <i class="bx bx-reset me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Deliveries List -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Deliveries</h5>
            <span class="badge bg-label-primary">{{ $deliveries->total() }} Total</span>
        </div>
        <div class="card-datatable table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Pickup Location</th>
                        <th>Delivery Address</th>
                        <th>Status</th>
                        <th>Fee</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td>
                            <span class="fw-medium">{{ $delivery->order->order_number }}</span>
                            @if($delivery->bundle_id)
                                <span class="badge bg-label-primary d-block mt-1" style="width:fit-content;">
                                    <i class="bx bx-package me-1"></i>Bundle
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ substr($delivery->order->customer_name, 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="d-block">{{ $delivery->order->customer_name }}</span>
                                    <small class="text-muted">{{ $delivery->order->customer_phone }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="{{ $delivery->pickup_address }}">
                                <i class="bx bx-store text-muted me-1"></i>
                                {{ Str::limit($delivery->pickup_address, 30) }}
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 200px;" title="{{ $delivery->delivery_address }}">
                                <i class="bx bx-map text-muted me-1"></i>
                                {{ Str::limit($delivery->delivery_address, 30) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $delivery->status_badge }}">
                                {{ $delivery->status_label }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-medium">₦{{ number_format($delivery->delivery_fee, 0) }}</span>
                        </td>
                        <td>
                            <span class="text-nowrap">{{ $delivery->created_at->format('M d, Y') }}</span>
                            <small class="text-muted d-block">{{ $delivery->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('rider.deliveries.show', $delivery) }}" class="btn btn-sm btn-icon btn-label-primary">
                                    <i class="bx bx-show"></i>
                                </a>
                                
                                @if(in_array($delivery->status, ['assigned', 'en_route_pickup']))
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($delivery->pickup_address) }}" 
                                   target="_blank" class="btn btn-sm btn-icon btn-label-info" title="Navigate">
                                    <i class="bx bx-navigation"></i>
                                </a>
                                @endif
                                
                                @if(in_array($delivery->status, ['picked_up', 'en_route_delivery']))
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ urlencode($delivery->delivery_address) }}" 
                                   target="_blank" class="btn btn-sm btn-icon btn-label-info" title="Navigate">
                                    <i class="bx bx-navigation"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <div class="mb-3">
                                <i class="bx bx-package icon-lg text-muted"></i>
                            </div>
                            <h6 class="mb-1">No Deliveries Found</h6>
                            <p class="text-muted mb-0">You don't have any deliveries matching the selected filters</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($deliveries->hasPages())
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
        @endif
    </div>

</div>
@endsection
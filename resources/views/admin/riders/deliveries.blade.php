@extends('admin.layouts.app')

@section('title', 'Rider Deliveries')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back to Profile
                    </a>
                    <a href="{{ route('admin.riders.earnings', $rider) }}" class="btn btn-success">
                        <i data-lucide="dollar-sign" class="me-1"></i>View Earnings
                    </a>
                </div>
            </div>
            <h4 class="page-title">Deliveries - {{ $rider->full_name }}</h4>
        </div>
    </div>
</div>

<!-- Rider Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-auto">
                        <div class="avatar-md">
                            @if($rider->profile_photo)
                                <img src="{{ asset('storage/' . $rider->profile_photo) }}" 
                                     class="rounded-circle" alt="{{ $rider->full_name }}">
                            @else
                                <div class="avatar-title bg-primary rounded-circle">
                                    {{ strtoupper(substr($rider->full_name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md">
                        <h5 class="mb-1">{{ $rider->full_name }}</h5>
                        <p class="text-muted mb-0">
                            <i data-lucide="phone" class="me-1" style="width: 14px; height: 14px;"></i>
                            {{ $rider->phone_number }}
                        </p>
                    </div>
                    <div class="col-md-auto text-end">
                        <div class="d-flex align-items-center justify-content-end mb-1">
                            <i data-lucide="star" class="text-warning me-1"></i>
                            <span class="fw-bold">{{ number_format($rider->rating, 1) }}</span>
                        </div>
                        <span class="badge bg-{{ $rider->status === 'available' ? 'success' : ($rider->status === 'busy' ? 'info' : 'secondary') }}">
                            {{ ucfirst($rider->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row">
    <div class="col-xl-3 col-sm-6">
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
                        <p class="text-muted mb-1">Total</p>
                        <h4 class="mb-0">{{ $deliveries->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                                <i data-lucide="check-circle" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Completed</p>
                        <h4 class="mb-0">{{ $deliveries->where('status', 'delivered')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
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
                        <p class="text-muted mb-1">Active</p>
                        <h4 class="mb-0">{{ $deliveries->whereIn('status', ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery'])->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded">
                                <i data-lucide="x-circle" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Failed</p>
                        <h4 class="mb-0">{{ $deliveries->where('status', 'failed')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Deliveries Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Deliveries</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Pickup</th>
                                <th>Delivery</th>
                                <th>Date</th>
                                <th>Fee</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deliveries as $delivery)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="fw-medium">
                                        {{ $delivery->order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $delivery->order->customer_name }}</div>
                                        <small class="text-muted">{{ $delivery->order->customer_phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($delivery->pickup_address, 30) }}</small>
                                </td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($delivery->delivery_address, 30) }}</small>
                                </td>
                                <td>
                                    <div>
                                        {{ $delivery->created_at->format('d M, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $delivery->created_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                                <td class="fw-medium">₦{{ number_format($delivery->delivery_fee, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $delivery->status_badge }}">
                                        {{ $delivery->status_label }}
                                    </span>
                                    @if($delivery->status === 'delivered' && $delivery->delivered_at)
                                        <br>
                                        <small class="text-muted">{{ $delivery->delivered_at->format('h:i A') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" 
                                       class="btn btn-sm btn-soft-primary">
                                        <i data-lucide="eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No deliveries found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($deliveries->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} of {{ $deliveries->total() }}
                    </div>
                    {{ $deliveries->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
@extends('admin.layouts.app')

@section('title', 'Company Deliveries')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $company->full_name }} - Deliveries</h4>
            <p class="text-muted mb-0">All deliveries handled by this company</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Company
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Total Deliveries</p>
                            <h3 class="mb-0">{{ $deliveries->total() }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-package bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Completed</p>
                            <h3 class="mb-0 text-success">{{ $company->completed_deliveries }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-circle bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Active</p>
                            <h3 class="mb-0 text-warning">{{ $company->activeDeliveries()->count() }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-cycling bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Failed</p>
                            <h3 class="mb-0 text-danger">{{ $company->failed_deliveries }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-x-circle bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Delivery History</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-label-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bx bx-filter me-1"></i>
                        {{ request('status') ? ucfirst(str_replace('_', ' ', request('status'))) : 'All Status' }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.companies.deliveries', $company) }}">All Status</a></li>
                        <li><a class="dropdown-item" href="?status=assigned">Assigned</a></li>
                        <li><a class="dropdown-item" href="?status=picked_up">Picked Up</a></li>
                        <li><a class="dropdown-item" href="?status=delivered">Delivered</a></li>
                        <li><a class="dropdown-item" href="?status=failed">Failed</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Pickup</th>
                        <th>Delivery</th>
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
                            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-primary fw-medium">
                                {{ $delivery->order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $delivery->order->customer_name }}</div>
                            <small class="text-muted">{{ $delivery->order->customer_phone }}</small>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $delivery->pickup_address }}">
                                <i class="bx bx-store text-muted me-1"></i>
                                {{ Str::limit($delivery->pickup_address, 25) }}
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 150px;" title="{{ $delivery->delivery_address }}">
                                <i class="bx bx-map text-muted me-1"></i>
                                {{ Str::limit($delivery->delivery_address, 25) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $delivery->status_badge }}">
                                {{ $delivery->status_label }}
                            </span>
                        </td>
                        <td>
                            <strong>₦{{ number_format($delivery->delivery_fee, 0) }}</strong>
                        </td>
                        <td>
                            <div>{{ $delivery->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $delivery->created_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.deliveries.show', $delivery) }}">
                                            <i class="bx bx-show me-2"></i>View Details
                                        </a>
                                    </li>
                                    @if($delivery->pickup_photo)
                                    <li>
                                        <a class="dropdown-item" href="{{ asset('storage/' . $delivery->pickup_photo) }}" target="_blank">
                                            <i class="bx bx-image me-2"></i>Pickup Photo
                                        </a>
                                    </li>
                                    @endif
                                    @if($delivery->delivery_proof)
                                    <li>
                                        <a class="dropdown-item" href="{{ asset('storage/' . $delivery->delivery_proof) }}" target="_blank">
                                            <i class="bx bx-image me-2"></i>Delivery Proof
                                        </a>
                                    </li>
                                    @endif
                                    @if($delivery->failure_photo)
                                    <li>
                                        <a class="dropdown-item" href="{{ asset('storage/' . $delivery->failure_photo) }}" target="_blank">
                                            <i class="bx bx-image me-2"></i>Failure Photo
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bx bx-package bx-lg text-muted mb-2"></i>
                            <p class="text-muted mb-0">No deliveries found</p>
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
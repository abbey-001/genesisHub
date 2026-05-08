@extends('admin.layouts.app')

@section('title', 'Deliveries Management')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Deliveries Management</h4>
            <p class="text-muted mb-0">Track and manage all deliveries</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.deliveries.queue') }}" class="btn btn-warning">
                <i class="bx bx-time me-1"></i>Unassigned Queue
                @if($stats['pending'] > 0)
                    <span class="badge bg-white text-warning ms-1">{{ $stats['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('admin.deliveries.map') }}" class="btn btn-primary">
                <i class="bx bx-map me-1"></i>Live Map
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Total Deliveries</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
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
                            <p class="mb-1">Pending Assignment</p>
                            <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time bx-md"></i>
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
                            <h3 class="mb-0 text-info">{{ $stats['active'] }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
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
                            <p class="mb-1">Completed Today</p>
                            <h3 class="mb-0 text-success">{{ $stats['completed_today'] }}</h3>
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
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.deliveries.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned</option>
                            <option value="picked_up" {{ request('status') === 'picked_up' ? 'selected' : '' }}>Picked Up</option>
                            <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Company</label>
                        <select name="rider_id" class="form-select">
                            <option value="">All Companies</option>
                            @foreach(\App\Models\Rider::orderBy('full_name')->get() as $company)
                                <option value="{{ $company->id }}" {{ request('rider_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-search me-1"></i>Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Deliveries Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Deliveries</h5>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Order</th>
                        <th>Company</th>
                        <th>Pickup</th>
                        <th>Delivery</th>
                        <th>Status</th>
                        <th>Fee</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td><strong>#{{ $delivery->id }}</strong></td>
                        <td>
                            <div>{{ $delivery->order->order_number }}</div>
                            <small class="text-muted">{{ $delivery->order->customer_name }}</small>
                        </td>
                        <td>
                            @if($delivery->rider)
                                <div>{{ $delivery->rider->full_name }}</div>
                                <small class="text-muted">{{ $delivery->rider->phone_number }}</small>
                            @else
                                <span class="badge bg-warning">Unassigned</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 150px;">
                                <i class="bx bx-store text-muted me-1"></i>
                                {{ Str::limit($delivery->pickup_address, 25) }}
                            </div>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 150px;">
                                <i class="bx bx-map text-muted me-1"></i>
                                {{ Str::limit($delivery->delivery_address, 25) }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $delivery->status_badge }}">
                                {{ $delivery->status_label }}
                            </span>
                        </td>
                        <td><strong>₦{{ number_format($delivery->delivery_fee, 0) }}</strong></td>
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
                                    @if($delivery->status === 'pending')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.deliveries.assignPage', $delivery) }}">
                                                <i class="bx bx-user-plus me-2"></i>Assign Company
                                            </a>
                                        </li>
                                    @endif
                                    @if($delivery->rider_id)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('admin.companies.show', $delivery->rider) }}">
                                                <i class="bx bx-building me-2"></i>View Company
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
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
            {{ $deliveries->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
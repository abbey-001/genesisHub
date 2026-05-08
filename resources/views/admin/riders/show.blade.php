@extends('admin.layouts.app')

@section('title', 'Rider Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back
                    </a>
                    <a href="{{ route('admin.riders.deliveries', $rider) }}" class="btn btn-info">
                        <i data-lucide="truck" class="me-1"></i>Deliveries
                    </a>
                    <a href="{{ route('admin.riders.earnings', $rider) }}" class="btn btn-success">
                        <i data-lucide="dollar-sign" class="me-1"></i>Earnings
                    </a>
                </div>
            </div>
            <h4 class="page-title">Rider Profile</h4>
        </div>
    </div>
</div>

<!-- Rider Header -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-auto text-center text-md-start">
                        <div class="avatar-lg">
                            @if($rider->profile_photo)
                                <img src="{{ asset('storage/' . $rider->profile_photo) }}" 
                                     class="rounded-circle" alt="{{ $rider->full_name }}">
                            @else
                                <div class="avatar-title bg-primary rounded-circle fs-24">
                                    {{ strtoupper(substr($rider->full_name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md">
                        <h4 class="mb-1">{{ $rider->full_name }}</h4>
                        <p class="text-muted mb-2">
                            <i data-lucide="mail" class="me-1" style="width: 14px; height: 14px;"></i>
                            {{ $rider->user->email }}
                        </p>
                        <p class="text-muted mb-2">
                            <i data-lucide="phone" class="me-1" style="width: 14px; height: 14px;"></i>
                            {{ $rider->phone_number }}
                        </p>
                        <div class="mt-2">
                            @if($rider->is_verified)
                                <span class="badge bg-success me-2">Verified</span>
                            @else
                                <span class="badge bg-warning me-2">Pending</span>
                            @endif

                            @if($rider->status === 'available')
                                <span class="badge bg-success me-2">
                                    <i data-lucide="radio" class="me-1" style="width: 12px; height: 12px;"></i>Online
                                </span>
                            @elseif($rider->status === 'busy')
                                <span class="badge bg-info me-2">
                                    <i data-lucide="truck" class="me-1" style="width: 12px; height: 12px;"></i>Busy
                                </span>
                            @else
                                <span class="badge bg-secondary me-2">Offline</span>
                            @endif

                            <span class="badge bg-secondary">{{ ucfirst($rider->vehicle_type) }}</span>
                        </div>
                    </div>
                    <div class="col-md-auto text-center text-md-end mt-3 mt-md-0">
                        <div class="d-flex align-items-center justify-content-center justify-content-md-end mb-2">
                            <i data-lucide="star" class="text-warning me-1"></i>
                            <h4 class="mb-0">{{ number_format($stats['avg_rating'], 1) }}</h4>
                        </div>
                        <p class="text-muted small mb-0">Average Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
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
                        <p class="text-muted mb-1">Total Deliveries</p>
                        <h4 class="mb-0">{{ number_format($stats['total_deliveries']) }}</h4>
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
                        <h4 class="mb-0">{{ number_format($stats['completed']) }}</h4>
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
                        <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
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
                                <i data-lucide="dollar-sign" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Earnings</p>
                        <h4 class="mb-0">₦{{ number_format($stats['total_earnings'], 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Performance Metrics</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Success Rate</span>
                        <span class="fw-bold text-success">{{ $stats['success_rate'] }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $stats['success_rate'] }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Completion Rate</span>
                        <span class="fw-bold text-primary">
                            {{ $stats['total_deliveries'] > 0 ? number_format(($stats['completed'] / $stats['total_deliveries']) * 100, 1) : 0 }}%
                        </span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" 
                             style="width: {{ $stats['total_deliveries'] > 0 ? ($stats['completed'] / $stats['total_deliveries']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>Failed Deliveries</span>
                        <span class="fw-bold text-danger">{{ $stats['failed'] }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" 
                             style="width: {{ $stats['total_deliveries'] > 0 ? ($stats['failed'] / $stats['total_deliveries']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Member Since</span>
                    <span class="fw-medium">{{ $rider->created_at->format('d M, Y') }}</span>
                </div>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Average Rating</span>
                    <div>
                        <i data-lucide="star" class="text-warning" style="width: 14px; height: 14px;"></i>
                        <span class="fw-medium">{{ number_format($stats['avg_rating'], 1) }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between">
                    <span class="text-muted">Total Earnings</span>
                    <span class="fw-medium text-success">₦{{ number_format($stats['total_earnings'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Vehicle Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Vehicle Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Vehicle Type</small>
                    <span class="fw-medium">{{ ucfirst($rider->vehicle_type) }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Registration Number</small>
                    <span class="fw-medium">{{ $rider->vehicle_registration }}</span>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">License Number</small>
                    <span class="fw-medium">{{ $rider->license_number }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                @if(!$rider->is_active)
                    <form action="{{ route('admin.riders.activate', $rider) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i data-lucide="check-circle" class="me-1"></i>Reactivate Rider
                        </button>
                    </form>
                @else
                    <button type="button" class="btn btn-danger w-100 mb-2"
                            data-bs-toggle="modal" 
                            data-bs-target="#suspendModal">
                        <i data-lucide="x-circle" class="me-1"></i>Suspend Rider
                    </button>
                @endif

                <a href="mailto:{{ $rider->user->email }}" class="btn btn-secondary w-100">
                    <i data-lucide="mail" class="me-1"></i>Send Email
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Deliveries</h5>
                <a href="{{ route('admin.riders.deliveries', $rider) }}" class="btn btn-sm btn-link">
                    View All <i data-lucide="arrow-right" class="ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Fee</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeliveries as $delivery)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="fw-medium">
                                        {{ $delivery->order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $delivery->order->customer_name }}</td>
                                <td>
                                    {{ $delivery->created_at->format('d M, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $delivery->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="fw-medium">₦{{ number_format($delivery->delivery_fee, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $delivery->status_badge }}">
                                        {{ $delivery->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i data-lucide="inbox" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                                    <p class="text-muted mb-0">No deliveries yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Suspend Rider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.riders.suspend', $rider) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        Active deliveries ({{ $stats['active'] }}) will be reassigned to other riders.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Suspension *</label>
                        <textarea name="reason" class="form-control" rows="3" 
                                  placeholder="Explain why this rider is being suspended..."
                                  required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i data-lucide="x-circle" class="me-1"></i>Suspend Rider
                    </button>
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
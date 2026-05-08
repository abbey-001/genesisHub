@extends('admin.layouts.app')

@section('title', 'Riders')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Rider Management</h4>
            <p class="text-muted">Manage all rider accounts and applications</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="bike" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Riders</p>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-sm-6">
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
                        <p class="text-muted mb-1">Verified</p>
                        <h4 class="mb-0">{{ number_format($stats['verified']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-sm-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="clock" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Pending</p>
                        <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-sm-6">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                                <i data-lucide="radio" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Online</p>
                        <h4 class="mb-0">{{ number_format($stats['online']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-sm-6">
        <div class="card border-info">
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
                        <p class="text-muted mb-1">Busy</p>
                        <h4 class="mb-0">{{ number_format($stats['busy']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-secondary bg-opacity-10 text-secondary rounded">
                                <i data-lucide="power" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Offline</p>
                        <h4 class="mb-0">{{ number_format($stats['offline']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
@if($stats['pending'] > 0)
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center">
            <i data-lucide="alert-circle" class="me-3 fs-24"></i>
            <div class="flex-grow-1">
                <strong>{{ $stats['pending'] }} pending applications</strong> require your review
            </div>
            <a href="{{ route('admin.riders.applications') }}" class="btn btn-warning ms-3">
                Review Applications
            </a>
        </div>
    </div>
</div>
@endif

<!-- Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Riders</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.riders.map') }}" class="btn btn-sm btn-info">
                            <i data-lucide="map" class="me-1"></i>Live Map
                        </a>
                        <a href="{{ route('admin.riders.applications') }}" class="btn btn-sm btn-warning">
                            <i data-lucide="clock" class="me-1"></i>Pending ({{ $stats['pending'] }})
                        </a>
                        <a href="{{ route('admin.riders.export', request()->query()) }}" class="btn btn-sm btn-success">
                            <i data-lucide="download" class="me-1"></i>Export
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.riders.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search riders..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>Online</option>
                            <option value="busy" {{ request('status') === 'busy' ? 'selected' : '' }}>Busy</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="vehicle_type" class="form-select">
                            <option value="">All Vehicles</option>
                            <option value="bicycle" {{ request('vehicle_type') === 'bicycle' ? 'selected' : '' }}>Bicycle</option>
                            <option value="motorcycle" {{ request('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                            <option value="car" {{ request('vehicle_type') === 'car' ? 'selected' : '' }}>Car</option>
                        </select>
                    </div>

                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="filter" class="me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.riders.index') }}" class="btn btn-secondary">
                            <i data-lucide="x" class="me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rider</th>
                                <th>Phone</th>
                                <th>Vehicle</th>
                                <th>Deliveries</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Online Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($riders as $rider)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle">
                                                {{ strtoupper(substr($rider->full_name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $rider->full_name }}</h6>
                                            <small class="text-muted">ID: {{ $rider->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $rider->phone_number }}</td>
                                <td>
                                    <div>
                                        <span class="badge bg-secondary">{{ ucfirst($rider->vehicle_type) }}</span>
                                        <br>
                                        <small class="text-muted">{{ $rider->vehicle_registration }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $rider->completed_deliveries ?? 0 }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i data-lucide="star" class="text-warning me-1" style="width: 16px; height: 16px;"></i>
                                        <span>{{ number_format($rider->rating ?? 0, 1) }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($rider->is_verified)
                                        <span class="badge bg-success">Verified</span>
                                    @else
                                        <span class="badge bg-warning">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($rider->status === 'available')
                                        <span class="badge bg-success">
                                            <i data-lucide="radio" class="me-1" style="width: 12px; height: 12px;"></i>Online
                                        </span>
                                    @elseif($rider->status === 'busy')
                                        <span class="badge bg-info">
                                            <i data-lucide="truck" class="me-1" style="width: 12px; height: 12px;"></i>Busy
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Offline</span>
                                    @endif
                                </td>
                                <td>{{ $rider->created_at->format('d M, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.riders.show', $rider) }}">
                                                    <i data-lucide="eye" class="me-2 fs-16"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.riders.deliveries', $rider) }}">
                                                    <i data-lucide="truck" class="me-2 fs-16"></i>View Deliveries
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.riders.earnings', $rider) }}">
                                                    <i data-lucide="dollar-sign" class="me-2 fs-16"></i>View Earnings
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if(!$rider->is_active)
                                                <li>
                                                    <form action="{{ route('admin.riders.activate', $rider) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i data-lucide="check-circle" class="me-2 fs-16"></i>Reactivate
                                                        </button>
                                                    </form>
                                                </li>
                                            @elseif($rider->is_verified)
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#suspendModal{{ $rider->id }}">
                                                        <i data-lucide="x-circle" class="me-2 fs-16"></i>Suspend Rider
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                    <!-- Suspend Modal -->
                                    <div class="modal fade" id="suspendModal{{ $rider->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Suspend Rider</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.riders.suspend', $rider) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i data-lucide="alert-triangle" class="me-2"></i>
                                                            Active deliveries will be reassigned.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason *</label>
                                                            <textarea name="reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Suspend Rider</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i data-lucide="bike" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mt-2">No riders found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($riders->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $riders->firstItem() }} to {{ $riders->lastItem() }} of {{ $riders->total() }}
                    </div>
                    {{ $riders->links() }}
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
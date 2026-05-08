@extends('admin.layouts.app')

@section('title', 'Delivery Companies')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Delivery Companies</h4>
            <p class="text-muted mb-0">Manage all delivery companies</p>
        </div>
        <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i>Add New Company
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Total Companies</p>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-building bx-md"></i>
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
                            <h3 class="mb-0 text-success">{{ $stats['active'] }}</h3>
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
                            <p class="mb-1">Suspended</p>
                            <h3 class="mb-0 text-warning">{{ $stats['suspended'] }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-error-circle bx-md"></i>
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
                            <p class="mb-1">Pending Verification</p>
                            <h3 class="mb-0 text-info">{{ $stats['pending_verification'] }}</h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-time bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.companies.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Company name, email, phone...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Verification</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Sort By</label>
                        <select name="sort_by" class="form-select">
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Join Date</option>
                            <option value="full_name" {{ request('sort_by') === 'full_name' ? 'selected' : '' }}>Name</option>
                            <option value="completed_deliveries" {{ request('sort_by') === 'completed_deliveries' ? 'selected' : '' }}>Deliveries</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.companies.index') }}" class="btn btn-label-secondary">
                                <i class="bx bx-reset me-1"></i>Reset
                            </a>
                            <a href="{{ route('admin.companies.export', request()->all()) }}" class="btn btn-label-success">
                                <i class="bx bx-download me-1"></i>Export
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">All Companies</h5>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Fleet Type</th>
                        <th>Status</th>
                        <th>Deliveries</th>
                        <th>Success Rate</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm me-2">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        {{ substr($company->full_name, 0, 2) }}
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-0">{{ $company->full_name }}</h6>
                                    <small class="text-muted">ID: #{{ $company->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>{{ $company->user->email }}</div>
                            <small class="text-muted">{{ $company->phone_number }}</small>
                        </td>
                        <td>{{ $company->vehicle_type ?? 'Not specified' }}</td>
                        <td>
                            @if($company->is_active && $company->is_verified)
                                <span class="badge bg-success">Active</span>
                            @elseif(!$company->is_verified)
                                <span class="badge bg-warning">Pending Verification</span>
                            @else
                                <span class="badge bg-danger">Suspended</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-label-success">{{ $company->completed_deliveries }}</span>
                            <span class="badge bg-label-danger">{{ $company->failed_deliveries }}</span>
                        </td>
                        <td>
                            <strong class="text-{{ $company->success_rate >= 90 ? 'success' : ($company->success_rate >= 70 ? 'warning' : 'danger') }}">
                                {{ $company->success_rate }}%
                            </strong>
                        </td>
                        <td>{{ $company->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.companies.show', $company) }}">
                                            <i class="bx bx-show me-2"></i>View Details
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.companies.edit', $company) }}">
                                            <i class="bx bx-edit me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.companies.deliveries', $company) }}">
                                            <i class="bx bx-package me-2"></i>View Deliveries
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.companies.earnings', $company) }}">
                                            <i class="bx bx-money me-2"></i>View Earnings
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    @if($company->is_active)
                                        <li>
                                            <a class="dropdown-item text-warning" 
                                               href="#" 
                                               data-bs-toggle="modal" 
                                               data-bs-target="#suspendModal{{ $company->id }}">
                                                <i class="bx bx-error-circle me-2"></i>Suspend
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <form action="{{ route('admin.companies.activate', $company) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-success">
                                                    <i class="bx bx-check-circle me-2"></i>Activate
                                                </button>
                                            </form>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>

                    <!-- Suspend Modal -->
                    <div class="modal fade" id="suspendModal{{ $company->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('admin.companies.suspend', $company) }}" method="POST">
                                @csrf
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Suspend Company</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="alert alert-warning">
                                            <strong>Warning:</strong> This will suspend the company and reassign all active deliveries.
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Reason for Suspension *</label>
                                            <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning">Suspend Company</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bx bx-building bx-lg text-muted mb-2"></i>
                            <p class="text-muted mb-0">No companies found</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($companies->hasPages())
        <div class="card-footer">
            {{ $companies->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
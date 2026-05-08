@extends('admin.layouts.app')

@section('title', 'Company Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $company->full_name }}</h4>
            <p class="text-muted mb-0">Company ID: #{{ $company->id }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-primary">
                <i class="bx bx-edit me-1"></i>Edit
            </a>
            @if($company->is_active)
                <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#suspendModal">
                    <i class="bx bx-error-circle me-1"></i>Suspend
                </button>
            @else
                <form action="{{ route('admin.companies.activate', $company) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bx bx-check-circle me-1"></i>Activate
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.companies.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Company Info -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar avatar-xl mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary fs-3">
                                {{ substr($company->full_name, 0, 2) }}
                            </span>
                        </div>
                        <h5 class="mb-1">{{ $company->full_name }}</h5>
                        @if($company->is_active && $company->is_verified)
                            <span class="badge bg-success">Active & Verified</span>
                        @elseif(!$company->is_verified)
                            <span class="badge bg-warning">Pending Verification</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </div>

                    <div class="d-flex justify-content-around mt-4 pt-3 border-top">
                        <div>
                            <h4 class="mb-0 text-success">{{ $company->completed_deliveries }}</h4>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div>
                            <h4 class="mb-0 text-danger">{{ $company->failed_deliveries }}</h4>
                            <small class="text-muted">Failed</small>
                        </div>
                        <div>
                            <h4 class="mb-0 text-primary">{{ $stats['active'] }}</h4>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $company->user->email }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ $company->phone_number }}</strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Fleet Type</small>
                        <strong>{{ $company->vehicle_type ?? 'Not specified' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Bank Info -->
            @if($company->bank_name)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Bank Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Bank Name</small>
                        <strong>{{ $company->bank_name }}</strong>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Account Number</small>
                        <strong>{{ $company->account_number }}</strong>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Account Name</small>
                        <strong>{{ $company->account_name }}</strong>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Stats & Activity -->
        <div class="col-lg-8">
            <!-- Performance Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bx bx-package bx-lg text-primary mb-2"></i>
                            <h4 class="mb-0">{{ $stats['total_deliveries'] }}</h4>
                            <small class="text-muted">Total Deliveries</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bx bx-cycling bx-lg text-warning mb-2"></i>
                            <h4 class="mb-0">{{ $stats['active'] }}</h4>
                            <small class="text-muted">Active Now</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bx bx-trending-up bx-lg text-success mb-2"></i>
                            <h4 class="mb-0">{{ $stats['success_rate'] }}%</h4>
                            <small class="text-muted">Success Rate</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="bx bx-money bx-lg text-info mb-2"></i>
                            <h4 class="mb-0">₦{{ number_format($stats['total_earnings'], 0) }}</h4>
                            <small class="text-muted">Total Earnings</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Deliveries -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Deliveries</h5>
                    <a href="{{ route('admin.companies.deliveries', $company) }}" class="btn btn-sm btn-label-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Status</th>
                                <th>Fee</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentDeliveries as $delivery)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-primary">
                                        {{ $delivery->order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $delivery->status_badge }}">
                                        {{ $delivery->status_label }}
                                    </span>
                                </td>
                                <td>₦{{ number_format($delivery->delivery_fee, 0) }}</td>
                                <td>{{ $delivery->created_at->format('M d, Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-3">
                                    <small class="text-muted">No recent deliveries</small>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('admin.companies.deliveries', $company) }}" class="btn btn-label-primary">
                            <i class="bx bx-package me-1"></i>View All Deliveries
                        </a>
                        <a href="{{ route('admin.companies.earnings', $company) }}" class="btn btn-label-success">
                            <i class="bx bx-money me-1"></i>View Earnings
                        </a>
                        <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-label-info">
                            <i class="bx bx-edit me-1"></i>Edit Company
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
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
                        <strong>Warning:</strong> This will suspend the company and reassign {{ $stats['active'] }} active deliveries.
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

@endsection
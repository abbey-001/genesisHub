@extends('rider.layouts.app')

@section('title', 'Company Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header with Greeting -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h4 class="mb-1">
                @php
                    $hour = now()->hour;
                    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 18 ? 'Good Afternoon' : 'Good Evening');
                @endphp
                {{ $greeting }}, {{ $rider->full_name }}! 👋
            </h4>
            <p class="text-muted mb-0">
                @if($stats['active_deliveries'] > 0)
                    You have {{ $stats['active_deliveries'] }} active {{ $stats['active_deliveries'] == 1 ? 'delivery' : 'deliveries' }}
                @else
                    All deliveries are up to date
                @endif
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-flex flex-column align-items-md-end gap-2">
                <div>
                    <span class="text-muted">Success Rate:</span>
                    <span class="badge bg-label-success">{{ $stats['success_rate'] }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Active Deliveries -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-package bx-lg"></i>
                            </span>
                        </div>
                        <a href="{{ route('rider.deliveries.active') }}" class="btn btn-sm btn-icon btn-label-primary">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <h3 class="mb-1">{{ $stats['active_deliveries'] }}</h3>
                    <p class="mb-0 text-muted">Active Deliveries</p>
                    @if($stats['active_deliveries'] > 0)
                        <small class="text-primary">In progress</small>
                    @else
                        <small class="text-muted">No active tasks</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Pickups -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time bx-lg"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-warning">Waiting</span>
                    </div>
                    <h3 class="mb-1">{{ $stats['pending_pickups'] }}</h3>
                    <p class="mb-0 text-muted">Pending Pickups</p>
                    <small class="text-warning">Ready for collection</small>
                </div>
            </div>
        </div>

        <!-- Completed Today -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-double bx-lg"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-success">Today</span>
                    </div>
                    <h3 class="mb-1">{{ $stats['completed_today'] }}</h3>
                    <p class="mb-0 text-muted">Completed</p>
                    <small class="text-success">
                        +{{ $stats['completed_today'] }} deliveries
                    </small>
                </div>
            </div>
        </div>

        <!-- Earnings Today -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-money bx-lg"></i>
                            </span>
                        </div>
                        <a href="{{ route('rider.earnings.index') }}" class="btn btn-sm btn-icon btn-label-info">
                            <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <h3 class="mb-1">₦{{ number_format($stats['earnings_today'], 0) }}</h3>
                    <p class="mb-0 text-muted">Today's Earnings</p>
                    <small class="text-info">From {{ $stats['completed_today'] }} deliveries</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Deliveries Section -->
    @if($activeDeliveries->count() > 0)
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-package me-2"></i>Active Deliveries
            </h5>
            <span class="badge bg-primary">{{ $activeDeliveries->count() }} Active</span>
        </div>
        <div class="card-body">
            <div class="row g-4">
                @foreach($activeDeliveries as $delivery)
                <div class="col-md-6">
                    <div class="card border border-primary">
                        <div class="card-body">
                            <!-- Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bx bx-receipt me-1"></i>
                                        Order #{{ $delivery->order->order_number }}
                                    </h6>
                                    <span class="badge bg-{{ $delivery->status_badge }}">
                                        {{ $delivery->status_label }}
                                    </span>
                                </div>
                                <a href="{{ route('rider.deliveries.show', $delivery) }}" class="btn btn-sm btn-icon btn-label-primary">
                                    <i class="bx bx-show"></i>
                                </a>
                            </div>

                            <!-- Addresses -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-initial rounded-circle {{ $delivery->status === 'picked_up' ? 'bg-success' : 'bg-secondary' }}">
                                                <i class="bx bx-check bx-xs"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">Pickup: {{ Str::limit($delivery->pickup_address, 40) }}</small>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <div class="avatar avatar-xs">
                                            <span class="avatar-initial rounded-circle bg-secondary">
                                                <i class="bx bx-map bx-xs"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-2">
                                        <small class="text-muted">Delivery: {{ Str::limit($delivery->delivery_address, 40) }}</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <small class="text-muted d-block">Items</small>
                                    <span class="fw-medium">{{ $delivery->items->count() }}</span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Delivery Fee</small>
                                    <span class="fw-bold text-success">₦{{ number_format($delivery->delivery_fee, 0) }}</span>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <a href="{{ route('rider.deliveries.show', $delivery) }}" class="btn btn-primary w-100">
                                <i class="bx bx-edit me-1"></i> Manage Delivery
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Completed -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bx bx-check-circle me-2"></i>Recent Completed
            </h5>
            <a href="{{ route('rider.deliveries.completed') }}" class="btn btn-sm btn-label-success">View All</a>
        </div>
        <div class="card-body">
            @if($recentCompleted->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($recentCompleted->take(5) as $delivery)
                    <div class="list-group-item px-0">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-check"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <h6 class="mb-0">{{ $delivery->order->order_number }}</h6>
                                        <small class="text-muted">{{ $delivery->delivered_at->format('M d, h:i A') }}</small>
                                    </div>
                                    <span class="text-success fw-bold">₦{{ number_format($delivery->delivery_fee, 0) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-4">
                    <i class="bx bx-history bx-lg text-muted mb-2"></i>
                    <p class="text-muted mb-0">No completed deliveries yet</p>
                    <small class="text-muted">Start completing deliveries to see your history</small>
                </div>
            @endif
        </div>
    </div>

    <!-- Performance Stats -->
    <div class="row g-4 mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-line-chart me-2"></i>Performance Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="mb-2">
                                <i class="bx bx-check-circle text-success bx-lg"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['total_completed'] }}</h3>
                            <p class="text-muted mb-0">Total Deliveries</p>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="mb-2">
                                <i class="bx bx-trending-up text-info bx-lg"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['success_rate'] }}%</h3>
                            <p class="text-muted mb-0">Success Rate</p>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-2">
                                <i class="bx bx-error-circle text-danger bx-lg"></i>
                            </div>
                            <h3 class="mb-0">{{ $stats['total_failed'] }}</h3>
                            <p class="text-muted mb-0">Failed</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('styles')
<style>
    .stat-card {
        border-radius: 12px;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
</style>
@endpush
@endsection
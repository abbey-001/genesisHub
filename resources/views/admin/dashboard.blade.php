@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
@php
    $admin = auth()->guard('admin')->user();
    $role = $admin?->role;
@endphp
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h4 class="mb-0">Welcome back, {{ $admin->name }}!</h4>
            <p class="text-muted">Here's what's happening on your platform today.</p>
        </div>
    </div>
</div>

<!-- Role Badge -->
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-primary d-flex align-items-center">
            <i data-lucide="shield" class="me-3 fs-24"></i>
            <div>
                <strong>Your Role:</strong> {{ $admin->role_name }}
                @if($role)
                    <span class="badge bg-white text-primary ms-2">Level {{ $role->level }}</span>
                @endif
                <br>
                <small class="text-muted">
                    {{ $role?->description ?? 'No role has been assigned to this admin account.' }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Include role-specific dashboard -->
@if($admin->hasAnyRole(['super_admin', 'administrator']))
    @include('admin.partials.dashboards.super-admin', ['metrics' => $metrics])
@elseif($admin->hasRole('finance_manager'))
    @include('admin.partials.dashboards.finance-manager', ['metrics' => $metrics])
@elseif($admin->hasRole('operations_manager'))
    @include('admin.partials.dashboards.operations-manager', ['metrics' => $metrics])
@elseif($admin->hasRole('content_manager'))
    @include('admin.partials.dashboards.content-manager', ['metrics' => $metrics])
@elseif($admin->hasRole('support_agent'))
    @include('admin.partials.dashboards.support-agent', ['metrics' => $metrics])
@elseif($admin->hasRole('analyst'))
    @include('admin.partials.dashboards.analyst', ['metrics' => $metrics])
@else
    <div class="alert alert-warning">
        This admin account does not have a dashboard role assigned.
    </div>
@endif

<!-- Pending Actions -->
@if(!empty($pendingActions) && array_sum($pendingActions) > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-warning">
            <div class="card-header bg-warning bg-opacity-10">
                <h5 class="mb-0 text-warning">
                    <i data-lucide="alert-circle" class="me-2"></i>
                    Pending Actions ({{ array_sum($pendingActions) }})
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($pendingActions as $action => $count)
                        @if($count > 0)
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                                    <i data-lucide="{{ match($action) {
                                        'seller_applications' => 'user-plus',
                                        'rider_applications' => 'bike',
                                        'pending_payouts' => 'wallet',
                                        'failed_deliveries' => 'alert-triangle',
                                        'pending_products' => 'package',
                                        'processing_payouts' => 'credit-card',
                                        'pending_deliveries' => 'truck',
                                        default => 'alert-circle'
                                    } }}" class="text-warning fs-20"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">{{ $count }}</h6>
                                    <p class="text-muted mb-0">{{ ucwords(str_replace('_', ' ', $action)) }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Recent Orders -->
@if(!empty($recentOrders) && $recentOrders->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i data-lucide="shopping-cart" class="me-2"></i>
                    Recent Orders
                </h5>
                @if($admin->hasPermission('orders.view'))
                <a href="#" class="btn btn-sm btn-primary">
                    View All <i data-lucide="arrow-right" class="ms-1"></i>
                </a>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="#" class="fw-medium text-primary">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->items->count() }}</td>
                                <td class="fw-bold">₦{{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Auto-refresh dashboard every 30 seconds
    setInterval(function() {
        fetch('{{ route('admin.dashboard.refresh') }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Dashboard refreshed:', data.timestamp);
                    // You can update specific metrics here without reloading
                }
            })
            .catch(error => console.error('Refresh failed:', error));
    }, 30000); // 30 seconds
</script>
@endpush

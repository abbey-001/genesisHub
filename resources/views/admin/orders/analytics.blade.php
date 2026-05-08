@extends('admin.layouts.app')

@section('title', 'Order Analytics')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back to Orders
                    </a>
                    <div class="btn-group">
                        <a href="{{ route('admin.orders.analytics', ['period' => 'day']) }}" 
                           class="btn btn-{{ $period === 'day' ? 'primary' : 'outline-primary' }}">Day</a>
                        <a href="{{ route('admin.orders.analytics', ['period' => 'week']) }}" 
                           class="btn btn-{{ $period === 'week' ? 'primary' : 'outline-primary' }}">Week</a>
                        <a href="{{ route('admin.orders.analytics', ['period' => 'month']) }}" 
                           class="btn btn-{{ $period === 'month' ? 'primary' : 'outline-primary' }}">Month</a>
                        <a href="{{ route('admin.orders.analytics', ['period' => 'year']) }}" 
                           class="btn btn-{{ $period === 'year' ? 'primary' : 'outline-primary' }}">Year</a>
                    </div>
                </div>
            </div>
            <h4 class="page-title">Order Analytics</h4>
            <p class="text-muted">Period: {{ ucfirst($period) }}</p>
        </div>
    </div>
</div>

<!-- Key Metrics -->
<div class="row">
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="shopping-cart" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Orders</p>
                        <h4 class="mb-0">{{ number_format($analytics['total_orders']) }}</h4>
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
                        <p class="text-muted mb-1">Total Revenue</p>
                        <h4 class="mb-0">₦{{ number_format($analytics['total_revenue'], 2) }}</h4>
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
                                <i data-lucide="trending-up" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Avg Order Value</p>
                        <h4 class="mb-0">₦{{ number_format($analytics['average_order_value'], 2) }}</h4>
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
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="check-circle" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Completed</p>
                        <h4 class="mb-0">{{ number_format($analytics['completed_orders']) }}</h4>
                        <small class="text-muted">
                            {{ $analytics['total_orders'] > 0 ? number_format(($analytics['completed_orders'] / $analytics['total_orders']) * 100, 1) : 0 }}% completion rate
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row">
    <div class="col-lg-8">
        <!-- Revenue Chart -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daily Revenue (Last 30 Days)</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Order Status Distribution -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Customers & Additional Stats -->
<div class="row">
    <div class="col-lg-6">
        <!-- Top Customers -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Customers</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['top_customers'] as $index => $customer)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $customer->customer_name }}</div>
                                        <small class="text-muted">{{ $customer->customer_email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $customer->order_count }}</span>
                                </td>
                                <td class="fw-bold text-success">₦{{ number_format($customer->total_spent, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <p class="text-muted mb-0">No customer data available</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <!-- Additional Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="clock" class="text-warning me-2"></i>
                                <h6 class="mb-0">Pending Orders</h6>
                            </div>
                            <h3 class="mb-0">{{ number_format($analytics['pending_orders']) }}</h3>
                            <small class="text-muted">Awaiting processing</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="check-circle" class="text-success me-2"></i>
                                <h6 class="mb-0">Completed</h6>
                            </div>
                            <h3 class="mb-0">{{ number_format($analytics['completed_orders']) }}</h3>
                            <small class="text-muted">Successfully delivered</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="x-circle" class="text-danger me-2"></i>
                                <h6 class="mb-0">Cancelled</h6>
                            </div>
                            <h3 class="mb-0">{{ number_format($analytics['cancelled_orders']) }}</h3>
                            <small class="text-muted">
                                {{ $analytics['total_orders'] > 0 ? number_format(($analytics['cancelled_orders'] / $analytics['total_orders']) * 100, 1) : 0 }}% cancel rate
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <div class="d-flex align-items-center mb-2">
                                <i data-lucide="trending-up" class="text-info me-2"></i>
                                <h6 class="mb-0">Average Value</h6>
                            </div>
                            <h3 class="mb-0">₦{{ number_format($analytics['average_order_value'], 2) }}</h3>
                            <small class="text-muted">Per order</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Export Analytics</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.orders.export', ['period' => $period]) }}" class="btn btn-success">
                        <i data-lucide="download" class="me-2"></i>Export to CSV
                    </a>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i data-lucide="printer" class="me-2"></i>Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    lucide.createIcons();

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($analytics['daily_revenue']['labels']),
            datasets: [{
                label: 'Revenue',
                data: @json($analytics['daily_revenue']['data']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₦' + context.parsed.y.toLocaleString('en-NG', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $analytics['pending_orders'] }},
                    {{ $analytics['completed_orders'] }},
                    {{ $analytics['cancelled_orders'] }}
                ],
                backgroundColor: [
                    'rgb(255, 193, 7)',
                    'rgb(25, 135, 84)',
                    'rgb(220, 53, 69)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
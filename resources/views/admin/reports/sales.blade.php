@extends('admin.layouts.app')

@section('title', 'Sales Analytics')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.reports.index') }}">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">Sales Analytics</li>
                </ol>
            </nav>
            <h4 class="mb-1">🛍️ Sales Analytics</h4>
            <p class="text-muted mb-0">Order trends and sales performance analysis</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="exportReport('pdf')">
                <i class="bx bx-download me-1"></i> Export PDF
            </button>
            <button type="button" class="btn btn-label-success" onclick="exportReport('excel')">
                <i class="bx bx-file me-1"></i> Export Excel
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.sales') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('period', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
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
                <div class="col-md-3">
                    <label class="form-label">Group By</label>
                    <select name="group_by" class="form-select">
                        <option value="day" {{ request('group_by', 'day') == 'day' ? 'selected' : '' }}>Daily</option>
                        <option value="week" {{ request('group_by') == 'week' ? 'selected' : '' }}>Weekly</option>
                        <option value="month" {{ request('group_by') == 'month' ? 'selected' : '' }}>Monthly</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-primary">
                            <i class="bx bx-shopping-bag bx-md"></i>
                        </div>
                        <div class="progress" style="width: 60px; height: 4px;">
                            <div class="progress-bar bg-primary" style="width: {{ ($data['summary']['completed_orders'] / max($data['summary']['total_orders'], 1)) * 100 }}%"></div>
                        </div>
                    </div>
                    <h5 class="mb-0 text-primary">{{ number_format($data['summary']['total_orders']) }}</h5>
                    <p class="mb-0 text-muted small">Total Orders</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-check-circle bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-success">{{ number_format($data['summary']['completed_orders']) }}</h5>
                    <p class="mb-0 text-muted small">Completed Orders</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-warning">
                            <i class="bx bx-time bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-warning">{{ number_format($data['summary']['pending_orders']) }}</h5>
                    <p class="mb-0 text-muted small">Pending Orders</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-info">
                            <i class="bx bx-bar-chart bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-info">₦{{ number_format($data['summary']['avg_order_value'], 2) }}</h5>
                    <p class="mb-0 text-muted small">Avg Order Value</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Status & Conversion -->
    <div class="row g-4 mb-4">
        <!-- Order Status Distribution -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Order Status Distribution</h5>
                </div>
                <div class="card-body">
                <div class="chart-container">
                    <canvas id="orderStatusChart" height="280"></canvas>
                </div>
                </div>
            </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Conversion Funnel</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Product Views</span>
                                <strong class="text-muted">100%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: 100%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Add to Cart</span>
                                <strong class="text-info">{{ number_format($data['conversion_rate'] * 0.4, 1) }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" style="width: {{ $data['conversion_rate'] * 0.4 }}%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Checkout Started</span>
                                <strong class="text-warning">{{ number_format($data['conversion_rate'] * 0.7, 1) }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-warning" style="width: {{ $data['conversion_rate'] * 0.7 }}%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Orders Completed</span>
                                <strong class="text-success">{{ number_format($data['conversion_rate'], 1) }}%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $data['conversion_rate'] }}%"></div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Conversion Rate:</strong> {{ number_format($data['conversion_rate'], 2) }}%
                            <br>
                            <small class="text-muted">From product view to completed order</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales by Hour (if today/yesterday) -->
    @if($data['sales_by_hour'])
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Sales by Hour</h5>
            <small class="text-muted">Hourly order distribution</small>
        </div>
        <div class="card-body">
        <div class="chart-container">
            <canvas id="salesByHourChart" height="80"></canvas>
        </div>
        </div>
    </div>
    @endif

    <!-- Top Products -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Top 10 Best Selling Products</h5>
                <small class="text-muted">By quantity sold</small>
            </div>
            <a href="{{ route('admin.reports.products') }}" class="btn btn-sm btn-label-primary">View All Products</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th class="text-center">Quantity Sold</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-center">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_products'] as $index => $product)
                        <tr>
                            <td>
                                <span class="badge bg-label-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                    #{{ $index + 1 }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <span class="badge bg-label-primary rounded-pill">{{ $product->total_sold }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ Str::limit($product->product_name, 50) }}</div>
                                        <small class="text-muted">SKU: {{ $product->product_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <strong>{{ number_format($product->total_sold) }}</strong> units
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">₦{{ number_format($product->revenue, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-info">{{ $product->order_count }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No product sales data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Total Items Sold</h6>
                        <i class="bx bx-package bx-lg text-primary"></i>
                    </div>
                    <h3 class="mb-0 text-primary">{{ number_format($data['summary']['total_items_sold']) }}</h3>
                    <small class="text-muted">Across all orders</small>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Cancelled Orders</h6>
                        <i class="bx bx-x-circle bx-lg text-danger"></i>
                    </div>
                    <h3 class="mb-0 text-danger">{{ number_format($data['summary']['cancelled_orders']) }}</h3>
                    <small class="text-muted">
                        {{ $data['summary']['total_orders'] > 0 ? number_format(($data['summary']['cancelled_orders'] / $data['summary']['total_orders']) * 100, 1) : 0 }}% of total
                    </small>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="mb-0">Success Rate</h6>
                        <i class="bx bx-trending-up bx-lg text-success"></i>
                    </div>
                    <h3 class="mb-0 text-success">
                        {{ $data['summary']['total_orders'] > 0 ? number_format(($data['summary']['completed_orders'] / $data['summary']['total_orders']) * 100, 1) : 0 }}%
                    </h3>
                    <small class="text-muted">Orders completed successfully</small>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Order Status Chart
    const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['orders_by_status']->pluck('status')->map(fn($s) => ucfirst($s))) !!},
            datasets: [{
                data: {!! json_encode($data['orders_by_status']->pluck('count')) !!},
                backgroundColor: [
                    '#71dd37', // Delivered - Success
                    '#696cff', // Processing - Primary
                    '#ffab00', // Pending - Warning
                    '#ff3e1d', // Cancelled - Danger
                    '#03c3ec', // Shipped - Info
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    @if($data['sales_by_hour'])
    // Sales by Hour Chart
    const hourCtx = document.getElementById('salesByHourChart').getContext('2d');
    new Chart(hourCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($data['sales_by_hour']->pluck('hour')->map(fn($h) => $h . ':00')) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode($data['sales_by_hour']->pluck('orders')) !!},
                backgroundColor: 'rgba(105, 108, 255, 0.8)',
                borderColor: '#696cff',
                borderWidth: 1,
                yAxisID: 'y',
            }, {
                label: 'Revenue',
                data: {!! json_encode($data['sales_by_hour']->pluck('revenue')) !!},
                backgroundColor: 'rgba(113, 221, 55, 0.8)',
                borderColor: '#71dd37',
                borderWidth: 1,
                type: 'line',
                yAxisID: 'y1',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.datasetIndex === 1) {
                                label += '₦' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Orders'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    },
                    title: {
                        display: true,
                        text: 'Revenue'
                    }
                }
            }
        }
    });
    @endif

    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('format', format);
        params.set('type', 'sales');
        
        window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
    }
</script>
@endpush
@endsection
@extends('admin.layouts.app')

@section('title', 'Revenue Analytics')

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
                    <li class="breadcrumb-item active">Revenue Analytics</li>
                </ol>
            </nav>
            <h4 class="mb-1">💰 Revenue Analytics</h4>
            <p class="text-muted mb-0">Comprehensive revenue analysis and trends</p>
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
            <form method="GET" action="{{ route('admin.reports.revenue') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="last_week" {{ request('period') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                        <option value="this_month" {{ request('period', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
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
                    <label class="form-label">Seller</label>
                    <select name="seller_id" class="form-select">
                        <option value="">All Sellers</option>
                        @foreach(\App\Models\Seller::with('shop')->get() as $seller)
                            <option value="{{ $seller->id }}" {{ request('seller_id') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->shop->shop_name ?? 'Seller #' . $seller->id }}
                            </option>
                        @endforeach
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
                            <i class="bx bx-dollar bx-md"></i>
                        </div>
                        <span class="badge bg-label-{{ $data['growth'] >= 0 ? 'success' : 'danger' }}">
                            <i class="bx bx-{{ $data['growth'] >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                            {{ number_format(abs($data['growth']), 1) }}%
                        </span>
                    </div>
                    <h5 class="mb-0 text-primary">₦{{ number_format($data['summary']['total_revenue'], 2) }}</h5>
                    <p class="mb-0 text-muted small">Total Revenue</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-shopping-bag bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-success">{{ number_format($data['summary']['total_orders']) }}</h5>
                    <p class="mb-0 text-muted small">Total Orders</p>
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

        <div class="col-xl-3 col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-warning">
                            <i class="bx bx-wallet bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-warning">₦{{ number_format($data['summary']['total_commission'], 2) }}</h5>
                    <p class="mb-0 text-muted small">Platform Commission</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Revenue Trend</h5>
            <small class="text-muted">
                {{ $data['period']['start']->format('M d, Y') }} - {{ $data['period']['end']->format('M d, Y') }}
            </small>
        </div>
        <div class="card-body">
        <div class="chart-container">
            <canvas id="revenueTrendChart" height="80"></canvas>
        </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4 mb-4">
        <!-- Revenue by Category -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Revenue by Category</h5>
                </div>
                <div class="card-body">
                <div class="chart-container">
                    <canvas id="revenueByCategoryChart" height="300"></canvas>
                </div>
                </div>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top 10 Sellers</h5>
                    <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-label-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Seller</th>
                                    <th class="text-end">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['top_sellers'] as $index => $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                            #{{ $index + 1 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <img src="{{ $item->seller->shop->shop_logo ?? 'https://ui-avatars.com/api/?name='.urlencode($item->seller->shop->shop_name ?? 'Seller') }}" 
                                                     alt="" class="rounded-circle">
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $item->seller->shop->shop_name ?? 'Seller #' . $item->seller_id }}</div>
                                                <small class="text-muted">{{ $item->seller->user->email ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success">₦{{ number_format($item->revenue, 2) }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4">No data available</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Revenue Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daily Revenue Breakdown</h5>
            <button type="button" class="btn btn-sm btn-label-primary" onclick="toggleTable()">
                <i class="bx bx-collapse" id="toggleIcon"></i> <span id="toggleText">Collapse</span>
            </button>
        </div>
        <div class="card-body p-0" id="dailyRevenueTable">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Orders</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-end">Avg Order Value</th>
                            <th class="text-center">Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $previousRevenue = 0; @endphp
                        @forelse($data['revenue_by_day'] as $day)
                        <tr>
                            <td>
                                <span class="fw-medium">{{ \Carbon\Carbon::parse($day->date)->format('M d, Y') }}</span>
                                <br>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($day->date)->format('l') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $day->orders }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">₦{{ number_format($day->revenue, 2) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-muted">₦{{ number_format($day->orders > 0 ? $day->revenue / $day->orders : 0, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $growth = $previousRevenue > 0 ? (($day->revenue - $previousRevenue) / $previousRevenue) * 100 : 0;
                                    $previousRevenue = $day->revenue;
                                @endphp
                                @if($growth != 0)
                                <span class="badge bg-label-{{ $growth >= 0 ? 'success' : 'danger' }}">
                                    <i class="bx bx-{{ $growth >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                                    {{ number_format(abs($growth), 1) }}%
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No revenue data for this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($data['revenue_by_day']->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th>{{ $data['summary']['total_orders'] }} orders</th>
                            <th class="text-end">₦{{ number_format($data['summary']['total_revenue'], 2) }}</th>
                            <th class="text-end">₦{{ number_format($data['summary']['avg_order_value'], 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    const revenueTrendChart = new Chart(revenueTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['revenue_by_day']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode($data['revenue_by_day']->pluck('revenue')) !!},
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }, {
                label: 'Orders',
                data: {!! json_encode($data['revenue_by_day']->pluck('orders')) !!},
                borderColor: '#71dd37',
                backgroundColor: 'rgba(113, 221, 55, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
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
                            if (context.datasetIndex === 0) {
                                label += '₦' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y + ' orders';
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
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
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
                }
            }
        }
    });

    // Revenue by Category Chart
    const categoryCtx = document.getElementById('revenueByCategoryChart').getContext('2d');
    const categoryChart = new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['revenue_by_category']->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($data['revenue_by_category']->pluck('revenue')) !!},
                backgroundColor: [
                    '#696cff',
                    '#71dd37',
                    '#ffab00',
                    '#ff3e1d',
                    '#03c3ec',
                    '#8592a3',
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
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = '₦' + context.parsed.toLocaleString();
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    function toggleTable() {
        const table = document.getElementById('dailyRevenueTable');
        const icon = document.getElementById('toggleIcon');
        const text = document.getElementById('toggleText');
        
        if (table.style.display === 'none') {
            table.style.display = 'block';
            icon.className = 'bx bx-collapse';
            text.textContent = 'Collapse';
        } else {
            table.style.display = 'none';
            icon.className = 'bx bx-expand';
            text.textContent = 'Expand';
        }
    }

    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('format', format);
        params.set('type', 'revenue');
        
        window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
    }
</script>
@endpush
@endsection
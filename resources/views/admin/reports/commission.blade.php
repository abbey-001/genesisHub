@extends('admin.layouts.app')

@section('title', 'Commission Analytics')

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
                    <li class="breadcrumb-item active">Commission Analytics</li>
                </ol>
            </nav>
            <h4 class="mb-1">💳 Commission Analytics</h4>
            <p class="text-muted mb-0">Platform earnings and commission breakdown</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="exportReport('pdf')">
                <i class="bx bx-download me-1"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.commission') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('period', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
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
        <div class="col-xl-4">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-primary">
                            <i class="bx bx-dollar bx-md"></i>
                        </div>
                        <span class="badge bg-label-primary">10% Rate</span>
                    </div>
                    <h5 class="mb-0 text-primary">₦{{ number_format($data['summary']['total_commission'], 2) }}</h5>
                    <p class="mb-0 text-muted small">Total Platform Commission</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-trending-up bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-success">₦{{ number_format($data['summary']['total_sales'], 2) }}</h5>
                    <p class="mb-0 text-muted small">Total Sales Volume</p>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-info">
                            <i class="bx bx-wallet bx-md"></i>
                        </div>
                        <span class="badge bg-label-info">90%</span>
                    </div>
                    <h5 class="mb-0 text-info">₦{{ number_format($data['summary']['seller_earnings'], 2) }}</h5>
                    <p class="mb-0 text-muted small">Total Seller Earnings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Trend Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Daily Commission Trend</h5>
            <small class="text-muted">
                {{ $data['period']['start']->format('M d, Y') }} - {{ $data['period']['end']->format('M d, Y') }}
            </small>
        </div>
        <div class="card-body">
            <canvas id="commissionTrendChart" height="80"></canvas>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="row g-4 mb-4">
        <!-- Commission by Category -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Commission by Category</h5>
                    <small class="text-muted">Top earning categories</small>
                </div>
                <div class="card-body">
                    <canvas id="commissionByCategoryChart" height="280"></canvas>
                </div>
            </div>
        </div>

        <!-- Commission Breakdown -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Commission Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-4">
                        <!-- Platform -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">Platform Commission (10%)</h6>
                                    <small class="text-muted">Our earnings</small>
                                </div>
                                <h5 class="mb-0 text-primary">₦{{ number_format($data['summary']['total_commission'], 2) }}</h5>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-primary" style="width: 10%"></div>
                            </div>
                        </div>

                        <!-- Sellers -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">Seller Earnings (90%)</h6>
                                    <small class="text-muted">Paid to sellers</small>
                                </div>
                                <h5 class="mb-0 text-success">₦{{ number_format($data['summary']['seller_earnings'], 2) }}</h5>
                            </div>
                            <div class="progress" style="height: 12px;">
                                <div class="progress-bar bg-success" style="width: 90%"></div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="bg-label-secondary p-3 rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">Total Sales Volume</h6>
                                    <small class="text-muted">Platform + Sellers</small>
                                </div>
                                <h4 class="mb-0">₦{{ number_format($data['summary']['total_sales'], 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission by Seller -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Commission by Seller</h5>
                <small class="text-muted">Top revenue generating sellers</small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Seller</th>
                            <th class="text-end">Total Sales</th>
                            <th class="text-end">Platform Commission</th>
                            <th class="text-end">Seller Earnings</th>
                            <th class="text-center">Orders</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['commission_by_seller'] as $index => $item)
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
                                <span class="fw-bold">₦{{ number_format($item->sales, 2) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-primary fw-bold">₦{{ number_format($item->commission, 2) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="text-success">₦{{ number_format($item->sales * 0.9, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-info">
                                    {{ \App\Models\OrderItem::where('seller_id', $item->seller_id)->whereBetween('created_at', [$data['period']['start'], $data['period']['end']])->distinct('order_id')->count() }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No commission data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($data['commission_by_seller']->count() > 0)
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2">Total</th>
                            <th class="text-end">₦{{ number_format($data['summary']['total_sales'], 2) }}</th>
                            <th class="text-end text-primary">₦{{ number_format($data['summary']['total_commission'], 2) }}</th>
                            <th class="text-end text-success">₦{{ number_format($data['summary']['seller_earnings'], 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Commission Trend Chart
    const trendCtx = document.getElementById('commissionTrendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['daily_commission']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
            datasets: [{
                label: 'Commission',
                data: {!! json_encode($data['daily_commission']->pluck('commission')) !!},
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Commission: ₦' + context.parsed.y.toLocaleString();
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

    // Commission by Category Chart
    const categoryCtx = document.getElementById('commissionByCategoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['commission_by_category']->pluck('name')) !!},
            datasets: [{
                data: {!! json_encode($data['commission_by_category']->pluck('commission')) !!},
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
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
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

    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('format', format);
        params.set('type', 'commission');
        
        window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
    }
</script>
@endpush
@endsection
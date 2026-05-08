@extends('admin.layouts.app')

@section('title', 'User Analytics')

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
                    <li class="breadcrumb-item active">User Analytics</li>
                </ol>
            </nav>
            <h4 class="mb-1">👥 User Analytics</h4>
            <p class="text-muted mb-0">User growth, engagement and activity analysis</p>
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
            <form method="GET" action="{{ route('admin.reports.users') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
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
                    <label class="form-label">User Type</label>
                    <select name="user_type" class="form-select">
                        <option value="all" {{ request('user_type', 'all') == 'all' ? 'selected' : '' }}>All Users</option>
                        <option value="customer" {{ request('user_type') == 'customer' ? 'selected' : '' }}>Customers</option>
                        <option value="seller" {{ request('user_type') == 'seller' ? 'selected' : '' }}>Sellers</option>
                        <option value="rider" {{ request('user_type') == 'rider' ? 'selected' : '' }}>Riders</option>
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

    <!-- User Type Cards -->
    <div class="row g-4 mb-4">
        <!-- Customer Metrics -->
        <div class="col-xl-4">
            <div class="card border-primary">
                <div class="card-header bg-label-primary">
                    <h6 class="mb-0 text-primary">
                        <i class="bx bx-user me-2"></i>Customers
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0 fw-bold">{{ number_format($data['customer_metrics']['total']) }}</h4>
                            <small class="text-muted">Total Customers</small>
                        </div>
                        <div class="avatar avatar-lg bg-label-primary">
                            <i class="bx bx-user bx-lg"></i>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="fw-bold text-success">{{ number_format($data['customer_metrics']['new']) }}</div>
                            <small class="text-muted">New</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-info">{{ number_format($data['customer_metrics']['active']) }}</div>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning">
                                {{ $data['customer_metrics']['total'] > 0 ? number_format(($data['customer_metrics']['active'] / $data['customer_metrics']['total']) * 100, 1) : 0 }}%
                            </div>
                            <small class="text-muted">Retention</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seller Metrics -->
        <div class="col-xl-4">
            <div class="card border-success">
                <div class="card-header bg-label-success">
                    <h6 class="mb-0 text-success">
                        <i class="bx bx-store me-2"></i>Sellers
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0 fw-bold">{{ number_format($data['seller_metrics']['total']) }}</h4>
                            <small class="text-muted">Total Sellers</small>
                        </div>
                        <div class="avatar avatar-lg bg-label-success">
                            <i class="bx bx-store bx-lg"></i>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="fw-bold text-success">{{ number_format($data['seller_metrics']['new']) }}</div>
                            <small class="text-muted">New</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-info">{{ number_format($data['seller_metrics']['active']) }}</div>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning">{{ number_format($data['seller_metrics']['verified']) }}</div>
                            <small class="text-muted">Verified</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rider Metrics -->
        <div class="col-xl-4">
            <div class="card border-info">
                <div class="card-header bg-label-info">
                    <h6 class="mb-0 text-info">
                        <i class="bx bx-cycling me-2"></i>Riders
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="mb-0 fw-bold">{{ number_format($data['rider_metrics']['total']) }}</h4>
                            <small class="text-muted">Total Riders</small>
                        </div>
                        <div class="avatar avatar-lg bg-label-info">
                            <i class="bx bx-cycling bx-lg"></i>
                        </div>
                    </div>
                    <hr>
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="fw-bold text-success">{{ number_format($data['rider_metrics']['new']) }}</div>
                            <small class="text-muted">New</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-info">{{ number_format($data['rider_metrics']['active']) }}</div>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-warning">{{ number_format($data['rider_metrics']['verified']) }}</div>
                            <small class="text-muted">Verified</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- User Growth Trend -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">User Growth Trend</h5>
                    <small class="text-muted">New user registrations over time</small>
                </div>
                <div class="card-body">
                    <canvas id="userGrowthChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Active Users Trend -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Active Users</h5>
                    <small class="text-muted">Users who made purchases</small>
                </div>
                <div class="card-body">
                      <div class="chart-container">
                    <canvas id="activeUsersChart" height="300"></canvas>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Top 10 Customers by Spend</h5>
                <small class="text-muted">Highest value customers in selected period</small>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-label-primary">View All Customers</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th class="text-center">Orders</th>
                            <th class="text-end">Total Spent</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_customers'] as $index => $customer)
                        <tr>
                            <td>
                                <span class="badge bg-label-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                    #{{ $index + 1 }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($customer->name) }}&background=random" 
                                             alt="" class="rounded-circle">
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $customer->name }}</div>
                                        <small class="text-muted">ID: {{ $customer->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td class="text-center">
                                <span class="badge bg-label-info">{{ $customer->orders->count() }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">₦{{ number_format($customer->orders_sum_total ?? 0, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-success">Active</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No customer data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // User Growth Chart
    const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
    
    // Prepare data
    const customerData = @json($data['user_growth']->get('customer', collect())->pluck('count'));
    const sellerData = @json($data['user_growth']->get('seller', collect())->pluck('count'));
    const riderData = @json($data['user_growth']->get('rider', collect())->pluck('count'));
    const labels = @json($data['user_growth']->get('customer', collect())->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d')));

    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Customers',
                data: customerData,
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.1)',
                tension: 0.4,
                fill: true,
            }, {
                label: 'Sellers',
                data: sellerData,
                borderColor: '#71dd37',
                backgroundColor: 'rgba(113, 221, 55, 0.1)',
                tension: 0.4,
                fill: true,
            }, {
                label: 'Riders',
                data: riderData,
                borderColor: '#03c3ec',
                backgroundColor: 'rgba(3, 195, 236, 0.1)',
                tension: 0.4,
                fill: true,
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
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Active Users Chart
    const activeCtx = document.getElementById('activeUsersChart').getContext('2d');
    new Chart(activeCtx, {
        type: 'line',
        data: {
            labels: @json($data['active_users_trend']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))),
            datasets: [{
                label: 'Active Users',
                data: @json($data['active_users_trend']->pluck('active_users')),
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.8)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('format', format);
        params.set('type', 'users');
        
        window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
    }
</script>
@endpush
@endsection
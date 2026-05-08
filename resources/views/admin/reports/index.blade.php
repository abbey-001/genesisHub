@extends('admin.layouts.app')

@section('title', 'Analytics & Reports')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">📊 Analytics & Reports</h4>
            <p class="text-muted mb-0">Comprehensive insights and reporting dashboard</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bx bx-download me-1"></i> Export Report
            </button>
            <a href="{{ route('admin.reports.custom') }}" class="btn btn-label-primary">
                <i class="bx bx-customize me-1"></i> Custom Report
            </a>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row g-4 mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-primary cursor-pointer" onclick="location.href='{{ route('admin.reports.revenue') }}'">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-primary">
                        <i class="bx bx-dollar bx-md"></i>
                    </div>
                    <h6 class="mb-0">Revenue</h6>
                    <small class="text-muted">Analytics</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-success cursor-pointer" onclick="location.href='{{ route('admin.reports.sales') }}'">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-success">
                        <i class="bx bx-shopping-bag bx-md"></i>
                    </div>
                    <h6 class="mb-0">Sales</h6>
                    <small class="text-muted">Analytics</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-info cursor-pointer" onclick="location.href='{{ route('admin.reports.users') }}'">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-info">
                        <i class="bx bx-user bx-md"></i>
                    </div>
                    <h6 class="mb-0">Users</h6>
                    <small class="text-muted">Analytics</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-warning cursor-pointer" onclick="location.href='{{ route('admin.reports.deliveries') }}'">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-warning">
                        <i class="bx bx-package bx-md"></i>
                    </div>
                    <h6 class="mb-0">Deliveries</h6>
                    <small class="text-muted">Analytics</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-danger cursor-pointer" onclick="location.href='{{ route('admin.reports.products') }}'">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-danger">
                        <i class="bx bx-box bx-md"></i>
                    </div>
                    <h6 class="mb-0">Products</h6>
                    <small class="text-muted">Analytics</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6">
            <div class="card border-secondary cursor-pointer" onclick="location.href='{{ route('admin.reports.commission') }}'">
                <div class="card-body text-center">
                    <div class="avatar avatar-md mx-auto mb-3 bg-label-secondary">
                        <i class="bx bx-wallet bx-md"></i>
                    </div>
                    <h6 class="mb-0">Commission</h6>
                    <small class="text-muted">Analytics</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="dateRangeForm" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select" id="periodSelect">
                        <option value="today">Today</option>
                        <option value="yesterday">Yesterday</option>
                        <option value="this_week">This Week</option>
                        <option value="last_week">Last Week</option>
                        <option value="this_month" selected>This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_year">This Year</option>
                        <option value="custom">Custom Range</option>
                    </select>
                </div>
                <div class="col-md-3" id="customDateFrom" style="display: none;">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control">
                </div>
                <div class="col-md-3" id="customDateTo" style="display: none;">
                    <label class="form-label">To Date</label>
                    <input type="date" name="date_to" class="form-control">
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" onclick="updateDashboard()">
                        <i class="bx bx-refresh me-1"></i> Update
                    </button>
                    <button type="button" class="btn btn-label-secondary" onclick="resetFilters()">
                        <i class="bx bx-x me-1"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Total Revenue</p>
                            <h4 class="mb-0 fw-bold text-primary" id="totalRevenue">₦0</h4>
                            <small class="text-success" id="revenueGrowth">
                                <i class="bx bx-trending-up"></i> 0%
                            </small>
                        </div>
                        <div class="avatar avatar-lg bg-label-primary">
                            <i class="bx bx-dollar bx-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Total Orders</p>
                            <h4 class="mb-0 fw-bold text-success" id="totalOrders">0</h4>
                            <small class="text-success" id="ordersGrowth">
                                <i class="bx bx-trending-up"></i> 0%
                            </small>
                        </div>
                        <div class="avatar avatar-lg bg-label-success">
                            <i class="bx bx-shopping-bag bx-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Active Users</p>
                            <h4 class="mb-0 fw-bold text-info" id="activeUsers">0</h4>
                            <small class="text-success" id="usersGrowth">
                                <i class="bx bx-trending-up"></i> 0%
                            </small>
                        </div>
                        <div class="avatar avatar-lg bg-label-info">
                            <i class="bx bx-user bx-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <p class="mb-2 text-muted">Deliveries</p>
                            <h4 class="mb-0 fw-bold text-warning" id="totalDeliveries">0</h4>
                            <small class="text-success" id="deliveriesSuccess">
                                <i class="bx bx-check-circle"></i> 0% Success
                            </small>
                        </div>
                        <div class="avatar avatar-lg bg-label-warning">
                            <i class="bx bx-package bx-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Revenue Chart -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Revenue Trend</h5>
                        <small class="text-muted">Daily revenue for selected period</small>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="updateChart('revenue', '7days')">7D</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('revenue', '30days')">30D</button>
                        <button type="button" class="btn btn-outline-primary" onclick="updateChart('revenue', '90days')">90D</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Orders Chart -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Status</h5>
                    <small class="text-muted">Current distribution</small>
                </div>
                <div class="card-body">
                    <canvas id="orderStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Analytics -->
    <div class="row g-4">
        <!-- Top Products -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Products</h5>
                    <a href="{{ route('admin.reports.products') }}" class="btn btn-sm btn-label-primary">
                        View All <i class="bx bx-chevron-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="topProductsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Sales</th>
                                    <th>Revenue</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Top Sellers</h5>
                    <a href="{{ route('admin.sellers.index') }}" class="btn btn-sm btn-label-primary">
                        View All <i class="bx bx-chevron-right"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover" id="topSellersTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Seller</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="4" class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Report</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reports.export') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Report Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="revenue">Revenue Analytics</option>
                            <option value="sales">Sales Analytics</option>
                            <option value="users">User Analytics</option>
                            <option value="deliveries">Delivery Analytics</option>
                            <option value="products">Product Analytics</option>
                            <option value="commission">Commission Analytics</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <select name="format" class="form-select" required>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="date" name="date_from" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <input type="date" name="date_to" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-download me-1"></i> Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    let revenueChart, orderStatusChart;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        loadDashboardData();
        
        // Period select handler
        document.getElementById('periodSelect').addEventListener('change', function() {
            if (this.value === 'custom') {
                document.getElementById('customDateFrom').style.display = 'block';
                document.getElementById('customDateTo').style.display = 'block';
            } else {
                document.getElementById('customDateFrom').style.display = 'none';
                document.getElementById('customDateTo').style.display = 'none';
            }
        });
    });

    function initializeCharts() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        revenueChart = new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Revenue',
                    data: [],
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
                                return '₦' + context.parsed.y.toLocaleString();
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

        // Order Status Chart
        const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
        orderStatusChart = new Chart(orderStatusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Delivered', 'Processing', 'Pending', 'Cancelled'],
                datasets: [{
                    data: [0, 0, 0, 0],
                    backgroundColor: [
                        '#71dd37',
                        '#ffab00',
                        '#696cff',
                        '#ff3e1d'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    function loadDashboardData() {
        // This would make AJAX calls to load actual data
        // For now, showing loading state
        
        // Simulate data loading
        setTimeout(() => {
            updateChart('revenue', '30days');
            // Update other elements
        }, 1000);
    }

    function updateChart(type, period) {
        // Make AJAX call to get chart data
        fetch(`{{ route('admin.reports.chart-data') }}?type=${type}&period=${period}`)
            .then(response => response.json())
            .then(data => {
                if (type === 'revenue') {
                    revenueChart.data.labels = data.labels;
                    revenueChart.data.datasets[0].data = data.data;
                    revenueChart.update();
                }
            })
            .catch(error => {
                console.error('Error loading chart data:', error);
            });
    }

    function updateDashboard() {
        const formData = new FormData(document.getElementById('dateRangeForm'));
        // Make AJAX call to refresh dashboard with new filters
        console.log('Updating dashboard with filters:', Object.fromEntries(formData));
    }

    function resetFilters() {
        document.getElementById('periodSelect').value = 'this_month';
        document.getElementById('customDateFrom').style.display = 'none';
        document.getElementById('customDateTo').style.display = 'none';
        updateDashboard();
    }
</script>
@endpush

@push('styles')
<style>
    .cursor-pointer {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .cursor-pointer:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush
{{-- Analyst Dashboard --}}

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Today's Revenue</h6>
                <h4 class="fw-bold mb-0">₦{{ number_format($metrics['revenue']['today'], 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">This Week</h6>
                <h4 class="fw-bold mb-0">₦{{ number_format($metrics['revenue']['this_week'], 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">This Month</h6>
                <h4 class="fw-bold mb-0">₦{{ number_format($metrics['revenue']['this_month'], 2) }}</h4>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Users</h6>
                <h4 class="fw-bold mb-0">{{ number_format($metrics['users']['total']) }}</h4>
            </div>
        </div>
    </div>
</div>

@if(!empty($revenueChart))
<div class="row mt-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="trending-up" class="me-2"></i>
                    Revenue Analytics (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="bar-chart-2" class="me-2"></i>
                    Order Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Today</span>
                        <span class="fw-bold">{{ $metrics['orders']['today'] }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">This Week</span>
                        <span class="fw-bold">{{ $metrics['orders']['this_week'] }}</span>
                    </div>
                </div>
                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">This Month</span>
                        <span class="fw-bold">{{ $metrics['orders']['this_month'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="file-text" class="me-2"></i>
                    Report Generation
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-primary">
                                <i data-lucide="trending-up" class="me-2"></i>
                                Revenue Report
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-success">
                                <i data-lucide="shopping-cart" class="me-2"></i>
                                Sales Report
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-info">
                                <i data-lucide="users" class="me-2"></i>
                                User Report
                            </button>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-grid">
                            <button class="btn btn-warning">
                                <i data-lucide="download" class="me-2"></i>
                                Export All
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    @if(!empty($revenueChart))
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChart['labels']) !!},
            datasets: [{
                label: 'Revenue (₦)',
                data: {!! json_encode($revenueChart['data']) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
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
    @endif
</script>
@endpush
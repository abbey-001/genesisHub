{{-- Finance Manager Dashboard --}}

<!-- Revenue Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Today's Revenue</p>
                        <h4 class="fw-bold text-primary mb-0">
                            ₦{{ number_format($metrics['revenue']['today'], 2) }}
                        </h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-primary bg-opacity-10 rounded">
                            <i data-lucide="trending-up" class="text-primary fs-32"></i>
                        </div>
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
                        <p class="mb-2 text-muted">This Week</p>
                        <h4 class="fw-bold text-success mb-0">
                            ₦{{ number_format($metrics['revenue']['this_week'], 2) }}
                        </h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-success bg-opacity-10 rounded">
                            <i data-lucide="calendar" class="text-success fs-32"></i>
                        </div>
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
                        <p class="mb-2 text-muted">This Month</p>
                        <h4 class="fw-bold text-info mb-0">
                            ₦{{ number_format($metrics['revenue']['this_month'], 2) }}
                        </h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-info bg-opacity-10 rounded">
                            <i data-lucide="bar-chart-2" class="text-info fs-32"></i>
                        </div>
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
                        <p class="mb-2 text-muted">All Time</p>
                        <h4 class="fw-bold text-warning mb-0">
                            ₦{{ number_format($metrics['revenue']['all_time'], 2) }}
                        </h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-warning bg-opacity-10 rounded">
                            <i data-lucide="dollar-sign" class="text-warning fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payout Stats -->
<div class="row mt-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pending Payouts</h6>
                <h3 class="fw-bold text-warning mb-1">{{ $metrics['payouts']['pending'] }}</h3>
                <p class="mb-0 text-muted">₦{{ number_format($metrics['payouts']['pending_amount'], 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="text-muted mb-2">Processing</h6>
                <h3 class="fw-bold text-info mb-1">{{ $metrics['payouts']['processing'] }}</h3>
                <p class="mb-0 text-muted">₦{{ number_format($metrics['payouts']['processing_amount'], 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Completed Today</h6>
                <h3 class="fw-bold text-success mb-1">{{ $metrics['payouts']['completed_today'] }}</h3>
                <p class="mb-0 text-muted">₦{{ number_format($metrics['payouts']['completed_amount_today'], 2) }}</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="text-muted mb-2">Today's Commission</h6>
                <h3 class="fw-bold text-primary mb-1">₦{{ number_format($metrics['commission']['today'], 2) }}</h3>
                <p class="mb-0 text-muted">10% of revenue</p>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
@if(!empty($revenueChart))
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="trending-up" class="me-2"></i>
                    Revenue Trend (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Monthly Summary -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="calendar" class="me-2"></i>
                    This Month Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Total Revenue</td>
                                <td class="text-end fw-bold">₦{{ number_format($metrics['revenue']['this_month'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Commission Earned (10%)</td>
                                <td class="text-end fw-bold text-primary">₦{{ number_format($metrics['commission']['this_month'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Payouts Processed</td>
                                <td class="text-end fw-bold">{{ $metrics['payouts']['completed_today'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Transactions</td>
                                <td class="text-end fw-bold">{{ number_format($metrics['transactions']['today_count']) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="alert-circle" class="me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-warning">
                        <i data-lucide="wallet" class="me-2"></i>
                        Process Pending Payouts ({{ $metrics['payouts']['pending'] }})
                    </a>
                    <a href="#" class="btn btn-info">
                        <i data-lucide="credit-card" class="me-2"></i>
                        Review Processing Payouts ({{ $metrics['payouts']['processing'] }})
                    </a>
                    <a href="#" class="btn btn-primary">
                        <i data-lucide="file-text" class="me-2"></i>
                        Generate Financial Report
                    </a>
                    <a href="#" class="btn btn-secondary">
                        <i data-lucide="download" class="me-2"></i>
                        Export Transactions
                    </a>
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
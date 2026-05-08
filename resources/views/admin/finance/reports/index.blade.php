@extends('admin.layouts.app')

@section('title', 'Financial Reports')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">📊 Financial Reports</h4>
                <p class="text-muted mb-0">Comprehensive financial analytics and insights</p>
            </div>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i data-lucide="download" class="me-1"></i>
                        Export Report
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.finance.reports.export', array_merge(request()->query(), ['type' => 'revenue'])) }}">
                                <i data-lucide="file-text" class="me-2"></i>
                                Revenue Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.finance.reports.export', array_merge(request()->query(), ['type' => 'payouts'])) }}">
                                <i data-lucide="wallet" class="me-2"></i>
                                Payouts Report
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.finance.reports.export', array_merge(request()->query(), ['type' => 'commissions'])) }}">
                                <i data-lucide="percent" class="me-2"></i>
                                Commissions Report
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.finance.reports.index') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Date Range</label>
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from', $dateFrom->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to', $dateTo->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i data-lucide="search" class="me-1"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Overview -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="dollar-sign" class="me-2"></i>
                    Revenue Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center p-3 border-end">
                            <small class="text-muted d-block mb-2">Total Orders Value</small>
                            <h4 class="mb-0">₦{{ number_format($revenue['total_orders'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border-end">
                            <small class="text-muted d-block mb-2">Total Paid</small>
                            <h4 class="text-success mb-0">₦{{ number_format($revenue['total_paid'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3 border-end">
                            <small class="text-muted d-block mb-2">Total Refunded</small>
                            <h4 class="text-danger mb-0">₦{{ number_format($revenue['total_refunded'], 2) }}</h4>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center p-3">
                            <small class="text-muted d-block mb-2">Net Revenue</small>
                            <h4 class="text-primary mb-0">₦{{ number_format($revenue['net_revenue'], 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Commission & Payouts -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="percent" class="me-2"></i>
                    Commission Earned
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">From Orders</span>
                        <span class="fw-bold text-success">₦{{ number_format($commission['total'], 2) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: 70%;"></div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">From Deliveries</span>
                        <span class="fw-bold text-info">₦{{ number_format($commission['from_deliveries'], 2) }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: 30%;"></div>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <h6 class="mb-0">Total Commission</h6>
                    <h5 class="mb-0 text-primary">₦{{ number_format($commission['total'] + $commission['from_deliveries'], 2) }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="wallet" class="me-2"></i>
                    Payout Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Requested</span>
                        <span class="fw-bold">₦{{ number_format($payouts['total_requested'], 2) }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Paid Out</span>
                        <span class="fw-bold text-danger">₦{{ number_format($payouts['total_paid'], 2) }}</span>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pending Payouts</span>
                        <span class="fw-bold text-warning">₦{{ number_format($payouts['pending'], 2) }}</span>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between">
                    <h6 class="mb-0">Net Position</h6>
                    <h5 class="mb-0 {{ $payouts['total_requested'] - $payouts['total_paid'] > 0 ? 'text-success' : 'text-danger' }}">
                        ₦{{ number_format($payouts['total_requested'] - $payouts['total_paid'], 2) }}
                    </h5>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Daily Revenue Trend -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="trending-up" class="me-2"></i>
                    Daily Revenue Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Top Earning Sellers -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="award" class="me-2"></i>
                    Top Earning Sellers
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Seller</th>
                                <th>Shop</th>
                                <th>Total Earned</th>
                                <th>Commission (10%)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellers as $index => $seller)
                            <tr>
                                <td>
                                    @if($index === 0)
                                        <span class="badge bg-warning">🥇 #1</span>
                                    @elseif($index === 1)
                                        <span class="badge bg-secondary">🥈 #2</span>
                                    @elseif($index === 2)
                                        <span class="badge bg-info">🥉 #3</span>
                                    @else
                                        <span class="badge bg-light text-dark">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $seller->seller->user->name }}</div>
                                    <small class="text-muted">{{ $seller->seller->user->email }}</small>
                                </td>
                                <td>{{ $seller->seller->shop->shop_name ?? 'N/A' }}</td>
                                <td class="fw-bold text-success">₦{{ number_format($seller->total_earned, 2) }}</td>
                                <td class="text-primary">₦{{ number_format($seller->total_earned * 0.10, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i data-lucide="inbox" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                                    <p class="text-muted mb-0">No data available for selected period</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    lucide.createIcons();

    // Revenue Trend Chart
    const dailyData = @json($dailyRevenue);
    
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            }),
            datasets: [{
                label: 'Daily Revenue',
                data: dailyData.map(d => d.total),
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
                            return '₦' + context.parsed.y.toLocaleString('en-US', {
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
</script>
@endpush
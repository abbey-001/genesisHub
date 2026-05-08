@extends('admin.layouts.app')

@section('title', 'Payout Analytics')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">📊 Payout Analytics</h4>
                <p class="text-muted mb-0">Analyze payout trends and performance</p>
            </div>
            <a href="{{ route('admin.finance.payouts.index') }}" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="me-1"></i>
                Back to Payouts
            </a>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.finance.payouts.analytics') }}" method="GET" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" class="form-control" value="{{ $dateFrom->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="date_to" class="form-control" value="{{ $dateTo->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="filter" class="me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.finance.payouts.analytics') }}" class="btn btn-outline-secondary">
                                <i data-lucide="x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Daily Payout Trend Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="line-chart" class="me-2"></i>
                    Daily Payout Trend
                </h5>
            </div>
            <div class="card-body">
                <canvas id="dailyPayoutsChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Breakdown -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="pie-chart" class="me-2"></i>
                    Payment Method Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="methodChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Payment Methods Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Method</th>
                                <th>Count</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($methodBreakdown as $method)
                            <tr>
                                <td class="fw-medium">{{ ucwords(str_replace('_', ' ', $method->payout_method)) }}</td>
                                <td>{{ number_format($method->count) }}</td>
                                <td class="text-success fw-bold">₦{{ number_format($method->total, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Sellers by Payout Amount -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="users" class="me-2"></i>
                    Top Sellers by Payout Amount
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Seller</th>
                                <th>Shop</th>
                                <th>Payout Count</th>
                                <th>Total Amount</th>
                                <th>Average Per Payout</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSellers as $index => $seller)
                            <tr>
                                <td class="fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-medium">{{ $seller->seller->user->name }}</div>
                                    <small class="text-muted">{{ $seller->seller->user->email }}</small>
                                </td>
                                <td>
                                    @if($seller->seller->shop)
                                        {{ $seller->seller->shop->shop_name }}
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $seller->payout_count }}</span>
                                </td>
                                <td class="text-success fw-bold">₦{{ number_format($seller->total_amount, 2) }}</td>
                                <td>₦{{ number_format($seller->total_amount / $seller->payout_count, 2) }}</td>
                                <td>
                                    <a href="{{ route('admin.finance.payouts.index', ['seller_id' => $seller->seller_id]) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i data-lucide="eye" class="me-1"></i>
                                        View Payouts
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No payout data available for the selected period</p>
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    lucide.createIcons();

    // Daily Payouts Chart
    const dailyPayoutsData = @json($dailyPayouts);
    const dailyLabels = dailyPayoutsData.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
    const dailyAmounts = dailyPayoutsData.map(d => parseFloat(d.total));

    new Chart(document.getElementById('dailyPayoutsChart'), {
        type: 'line',
        data: {
            labels: dailyLabels,
            datasets: [{
                label: 'Daily Payout Amount (₦)',
                data: dailyAmounts,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
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

    // Payment Method Chart
    const methodData = @json($methodBreakdown);
    const methodLabels = methodData.map(m => m.payout_method.replace('_', ' ').toUpperCase());
    const methodCounts = methodData.map(m => m.count);
    const methodColors = ['#0d6efd', '#198754', '#ffc107', '#fd7e14', '#dc3545'];

    new Chart(document.getElementById('methodChart'), {
        type: 'doughnut',
        data: {
            labels: methodLabels,
            datasets: [{
                data: methodCounts,
                backgroundColor: methodColors.slice(0, methodLabels.length),
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush

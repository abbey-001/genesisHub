@extends('admin.layouts.app')

@section('title', 'Wallet Analytics')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">📊 Wallet Analytics</h4>
                <p class="text-muted mb-0">Monitor seller earnings and wallet activity</p>
            </div>
            <a href="{{ route('admin.finance.wallets.index') }}" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="me-1"></i>
                Back to Wallets
            </a>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.finance.wallets.analytics') }}" method="GET" class="row g-3 align-items-end">
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
                            <a href="{{ route('admin.finance.wallets.analytics') }}" class="btn btn-outline-secondary">
                                <i data-lucide="x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Daily Activity Chart -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="bar-chart-2" class="me-2"></i>
                    Daily Wallet Activity
                </h5>
            </div>
            <div class="card-body">
                <canvas id="dailyActivityChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Source Breakdown -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="pie-chart" class="me-2"></i>
                    Earnings Source Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="sourceChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Source Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Source</th>
                                <th>Count</th>
                                <th>Total Amount</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $totalAmount = $sourceBreakdown->sum('total');
                            @endphp
                            @forelse($sourceBreakdown as $source)
                            <tr>
                                <td class="fw-medium">{{ ucwords(str_replace('_', ' ', $source->source)) }}</td>
                                <td>{{ number_format($source->count) }}</td>
                                <td class="text-success fw-bold">₦{{ number_format($source->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-info">
                                        {{ round(($source->total / $totalAmount) * 100, 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Earners -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="award" class="me-2"></i>
                    Top 10 Earning Sellers
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
                                <th>Total Earned</th>
                                <th>Available Balance</th>
                                <th>Total Withdrawn</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topEarners as $index => $earner)
                            <tr>
                                <td class="fw-bold">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-medium">{{ $earner->seller->user->name }}</div>
                                    <small class="text-muted">{{ $earner->seller->user->email }}</small>
                                </td>
                                <td>
                                    @if($earner->seller->shop)
                                        <div>{{ $earner->seller->shop->shop_name }}</div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success">₦{{ number_format($earner->total_earned, 2) }}</td>
                                <td>
                                    <div>₦{{ number_format($earner->balance, 2) }}</div>
                                    @if($earner->balance > 50000)
                                        <small class="text-success">✓ Good balance</small>
                                    @elseif($earner->balance < 10000)
                                        <small class="text-warning">⚠ Low balance</small>
                                    @endif
                                </td>
                                <td>₦{{ number_format($earner->total_withdrawn, 2) }}</td>
                                <td>
                                    @php
                                    $withdrawRate = ($earner->total_withdrawn / $earner->total_earned) * 100;
                                    @endphp
                                    <span class="badge bg-{{ $withdrawRate > 80 ? 'danger' : ($withdrawRate > 50 ? 'warning' : 'success') }}">
                                        {{ round($withdrawRate, 0) }}% withdrawn
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.finance.wallets.show', $earner) }}" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i data-lucide="eye" class="me-1"></i>
                                        View
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No wallet data available</p>
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

    // Daily Activity Chart
    const dailyActivityData = @json($dailyActivity);
    const activityByDate = {};
    
    dailyActivityData.forEach(record => {
        const date = new Date(record.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        if (!activityByDate[date]) {
            activityByDate[date] = { credit: 0, debit: 0, reserve: 0, release: 0 };
        }
        activityByDate[date][record.type] = parseFloat(record.total) || 0;
    });

    const activityLabels = Object.keys(activityByDate);
    const creditData = activityLabels.map(label => activityByDate[label].credit);
    const debitData = activityLabels.map(label => activityByDate[label].debit);

    new Chart(document.getElementById('dailyActivityChart'), {
        type: 'bar',
        data: {
            labels: activityLabels,
            datasets: [
                {
                    label: 'Credits (₦)',
                    data: creditData,
                    backgroundColor: '#198754',
                    borderColor: '#198754',
                    borderWidth: 1
                },
                {
                    label: 'Debits (₦)',
                    data: debitData,
                    backgroundColor: '#dc3545',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
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

    // Source Chart
    const sourceData = @json($sourceBreakdown);
    const sourceLabels = sourceData.map(s => s.source.replace('_', ' ').toUpperCase());
    const sourceAmounts = sourceData.map(s => parseFloat(s.total));
    const sourceColors = ['#0d6efd', '#198754', '#ffc107', '#fd7e14', '#dc3545', '#6f42c1'];

    new Chart(document.getElementById('sourceChart'), {
        type: 'doughnut',
        data: {
            labels: sourceLabels,
            datasets: [{
                data: sourceAmounts,
                backgroundColor: sourceColors.slice(0, sourceLabels.length),
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

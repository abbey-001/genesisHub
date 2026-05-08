@extends('rider.layouts.app')

@section('title', 'My Earnings')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-money me-2"></i>My Earnings
            </h4>
            <p class="text-muted mb-0">Track your delivery income and request payouts</p>
        </div>
        <div class="d-flex gap-2">
           
            @if($earnings['available_balance'] >= 1000)
                 <a href="{{ route('rider.earnings.payout-history') }}" class="btn btn-label-success">
                <i class="bx bx-wallet me-1"></i>Request Payout
            </a>
            @endif
        </div>
    </div>

    <!-- Balance Overview -->
    <div class="row g-4 mb-4">
        <!-- Available Balance -->
        <div class="col-xl-3 col-sm-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-wallet bx-lg"></i>
                            </span>
                        </div>
                        @if($earnings['available_balance'] >= 1000)
                            <a href="{{ route('rider.earnings.payout-form') }}" class="btn btn-sm btn-success">
                                <i class="bx bx-send"></i>
                            </a>
                        @endif
                    </div>
                    <h3 class="mb-1 text-success">₦{{ number_format($earnings['available_balance'], 0) }}</h3>
                    <p class="mb-0 text-muted small">Available for Payout</p>
                    @if($earnings['available_balance'] < 1000)
                        <small class="text-warning">Minimum: ₦1,000</small>
                    @else
                        <small class="text-success">Ready to withdraw</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Earnings -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-trending-up bx-lg"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-primary">All Time</span>
                    </div>
                    <h3 class="mb-1">₦{{ number_format($earnings['total_earnings'], 0) }}</h3>
                    <p class="mb-0 text-muted small">Total Earnings</p>
                </div>
            </div>
        </div>

        <!-- Total Paid Out -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-info">
                                <i class="bx bx-check-circle bx-lg"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-info">Paid</span>
                    </div>
                    <h3 class="mb-1">₦{{ number_format($earnings['total_paid_out'], 0) }}</h3>
                    <p class="mb-0 text-muted small">Total Paid Out</p>
                </div>
            </div>
        </div>

        <!-- Pending Payout -->
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time bx-lg"></i>
                            </span>
                        </div>
                        <span class="badge bg-label-warning">Pending</span>
                    </div>
                    <h3 class="mb-1">₦{{ number_format($earnings['total_pending'], 0) }}</h3>
                    <p class="mb-0 text-muted small">Pending Approval</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Earnings -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-1">₦{{ number_format($earnings['today'], 0) }}</h3>
                    <p class="mb-0 text-muted small">Today's Earnings</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-1">₦{{ number_format($earnings['this_week'], 0) }}</h3>
                    <p class="mb-0 text-muted small">This Week</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-1">₦{{ number_format($earnings['this_month'], 0) }}</h3>
                    <p class="mb-0 text-muted small">This Month</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="mb-1">₦{{ number_format($earnings['all_time'], 0) }}</h3>
                    <p class="mb-0 text-muted small">All Time</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Earnings Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-line-chart me-2"></i>Earnings Trend
                    </h5>
                    <small class="text-muted">Last 30 Days</small>
                </div>
                <div class="card-body">
                    <canvas id="earningsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Payouts -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-history me-2"></i>Recent Payouts
                    </h5>
                    <a href="{{ route('rider.earnings.payout-history') }}" class="btn btn-sm btn-label-primary">View All</a>
                </div>
                <div class="card-body">
                    @if($payouts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($payouts as $payout)
                            <a href="{{ route('rider.earnings.payout-show', $payout) }}" class="list-group-item list-group-item-action px-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <div class="fw-medium">₦{{ number_format($payout->amount, 0) }}</div>
                                        <small class="text-muted">{{ $payout->reference_number }}</small>
                                    </div>
                                    <span class="badge bg-{{ $payout->status_badge }}">{{ $payout->status_label }}</span>
                                </div>
                                <small class="text-muted">{{ $payout->requested_at->format('M d, Y') }}</small>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bx bx-history bx-lg text-muted mb-2"></i>
                            <p class="text-muted mb-0">No payout requests yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bx bx-package me-2"></i>Recent Earnings
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Route</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDeliveries as $delivery)
                        <tr>
                            <td>
                                <div>{{ $delivery->delivered_at->format('M d, Y') }}</div>
                                <small class="text-muted">{{ $delivery->delivered_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('rider.deliveries.show', $delivery) }}" class="text-primary">
                                    {{ $delivery->order->order_number }}
                                </a>
                            </td>
                            <td>{{ $delivery->order->customer_name }}</td>
                            <td>
                                <small class="text-muted">
                                    {{ Str::limit($delivery->pickup_address, 20) }} → {{ Str::limit($delivery->delivery_address, 20) }}
                                </small>
                            </td>
                            <td>
                                <strong class="text-success">₦{{ number_format($delivery->delivery_fee, 0) }}</strong>
                            </td>
                            <td>
                                @if($delivery->paid_to_rider_at)
                                    <span class="badge bg-success">Paid Out</span>
                                @else
                                    <span class="badge bg-warning">Unpaid</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="bx bx-info-circle bx-lg text-muted mb-2"></i>
                                <p class="text-muted mb-0">No earnings yet. Start completing deliveries!</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Earnings Chart
const ctx = document.getElementById('earningsChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($dailyEarnings['labels']),
        datasets: [{
            label: 'Daily Earnings (₦)',
            data: @json($dailyEarnings['data']),
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
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
</script>
@endpush

@endsection
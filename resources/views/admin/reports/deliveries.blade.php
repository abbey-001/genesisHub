@extends('admin.layouts.app')

@section('title', 'Delivery Analytics')

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
                    <li class="breadcrumb-item active">Delivery Analytics</li>
                </ol>
            </nav>
            <h4 class="mb-1">🚚 Delivery Analytics</h4>
            <p class="text-muted mb-0">Delivery performance, success rates and rider analytics</p>
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
            <form method="GET" action="{{ route('admin.reports.deliveries') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('period') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('period', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
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
                    <label class="form-label">Rider</label>
                    <select name="rider_id" class="form-select">
                        <option value="">All Riders</option>
                        @foreach(\App\Models\Rider::all() as $rider)
                            <option value="{{ $rider->id }}" {{ request('rider_id') == $rider->id ? 'selected' : '' }}>
                                {{ $rider->full_name }}
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
                            <i class="bx bx-package bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-primary">{{ number_format($data['summary']['total_deliveries']) }}</h5>
                    <p class="mb-0 text-muted small">Total Deliveries</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-check-circle bx-md"></i>
                        </div>
                        <span class="badge bg-label-success">
                            {{ number_format($data['summary']['success_rate'], 1) }}%
                        </span>
                    </div>
                    <h5 class="mb-0 text-success">{{ number_format($data['summary']['delivered']) }}</h5>
                    <p class="mb-0 text-muted small">Successfully Delivered</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-danger">
                            <i class="bx bx-x-circle bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-danger">{{ number_format($data['summary']['failed']) }}</h5>
                    <p class="mb-0 text-muted small">Failed Deliveries</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-info">
                            <i class="bx bx-time bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-info">{{ number_format($data['avg_delivery_time'] ?? 0) }} min</h5>
                    <p class="mb-0 text-muted small">Avg Delivery Time</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Delivery Status Distribution -->
        <div class="col-xl-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Delivery Status Distribution</h5>
                </div>
                <div class="card-body">
                <div class="chart-container">
                    <canvas id="deliveryStatusChart" height="280"></canvas>
                </div>
                </div>
            </div>
        </div>

        <!-- Delivery Time Distribution -->
        <div class="col-xl-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Delivery Time Distribution</h5>
                    <small class="text-muted">Time taken from assignment to delivery</small>
                </div>
                <div class="card-body">
                <div class="chart-container">
                    <canvas id="deliveryTimeChart" height="280"></canvas>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Failure Analysis -->
    @if($data['failure_reasons']->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Failed Delivery Reasons</h5>
            <small class="text-muted">Top reasons for delivery failures</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach($data['failure_reasons'] as $reason)
                <div class="col-md-6">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-capitalize">{{ str_replace('_', ' ', $reason->failure_reason) }}</span>
                        <span class="fw-bold">{{ $reason->count }}</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-danger" 
                             style="width: {{ ($reason->count / $data['summary']['failed']) * 100 }}%">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Top Riders -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Top 10 Performing Riders</h5>
                <small class="text-muted">By successful deliveries</small>
            </div>
            <a href="{{ route('admin.riders.index') }}" class="btn btn-sm btn-label-primary">View All Riders</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Rider</th>
                            <th class="text-center">Deliveries</th>
                            <th class="text-end">Earnings</th>
                            <th class="text-center">Success Rate</th>
                            <th class="text-center">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['top_riders'] as $index => $item)
                        <tr>
                            <td>
                                <span class="badge bg-label-{{ $index < 3 ? 'warning' : 'secondary' }}">
                                    #{{ $index + 1 }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <img src="{{ $item->rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($item->rider->full_name) }}" 
                                             alt="" class="rounded-circle">
                                    </div>
                                    <div>
                                        <div class="fw-medium">{{ $item->rider->full_name }}</div>
                                        <small class="text-muted">{{ $item->rider->phone_number }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-primary">{{ $item->deliveries }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">₦{{ number_format($item->earnings, 2) }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                    $successRate = $item->rider->completed_deliveries > 0 
                                        ? ($item->rider->completed_deliveries / ($item->rider->completed_deliveries + ($item->rider->failed_deliveries ?? 0))) * 100 
                                        : 100;
                                @endphp
                                <span class="badge bg-label-{{ $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger') }}">
                                    {{ number_format($successRate, 1) }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="bx bx-star text-warning me-1"></i>
                                    <span class="fw-bold">{{ number_format($item->rider->rating, 1) }}</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">No delivery data available</td>
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
    // Delivery Status Chart
    const statusCtx = document.getElementById('deliveryStatusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['deliveries_by_status']->pluck('status')->map(fn($s) => ucfirst(str_replace('_', ' ', $s)))) !!},
            datasets: [{
                data: {!! json_encode($data['deliveries_by_status']->pluck('count')) !!},
                backgroundColor: [
                    '#71dd37', // Delivered
                    '#696cff', // Assigned
                    '#ffab00', // Pending
                    '#ff3e1d', // Failed
                    '#03c3ec', // En route
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
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Delivery Time Distribution Chart
    const timeCtx = document.getElementById('deliveryTimeChart').getContext('2d');
    new Chart(timeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($data['delivery_time_distribution']->pluck('time_range')) !!},
            datasets: [{
                label: 'Number of Deliveries',
                data: {!! json_encode($data['delivery_time_distribution']->pluck('count')) !!},
                backgroundColor: [
                    '#71dd37',
                    '#696cff',
                    '#ffab00',
                    '#ff3e1d',
                ],
                borderWidth: 0
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
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed.y / total) * 100).toFixed(1);
                            return `${context.parsed.y} deliveries (${percentage}%)`;
                        }
                    }
                }
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
        params.set('type', 'deliveries');
        
        window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
    }
</script>
@endpush
@endsection
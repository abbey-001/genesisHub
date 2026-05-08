@extends('admin.layouts.app')

@section('title', 'Rider Earnings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.riders.show', $rider) }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back to Profile
                    </a>
                    <a href="{{ route('admin.riders.deliveries', $rider) }}" class="btn btn-info">
                        <i data-lucide="truck" class="me-1"></i>View Deliveries
                    </a>
                </div>
            </div>
            <h4 class="page-title">Earnings - {{ $rider->full_name }}</h4>
        </div>
    </div>
</div>

<!-- Rider Summary -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-auto">
                        <div class="avatar-md">
                            @if($rider->profile_photo)
                                <img src="{{ asset('storage/' . $rider->profile_photo) }}" 
                                     class="rounded-circle" alt="{{ $rider->full_name }}">
                            @else
                                <div class="avatar-title bg-primary rounded-circle">
                                    {{ strtoupper(substr($rider->full_name, 0, 2)) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md">
                        <h5 class="mb-1">{{ $rider->full_name }}</h5>
                        <p class="text-muted mb-0">
                            <i data-lucide="phone" class="me-1" style="width: 14px; height: 14px;"></i>
                            {{ $rider->phone_number }}
                        </p>
                    </div>
                    <div class="col-md-auto text-end">
                        <p class="text-muted mb-1">Total Earnings</p>
                        <h3 class="text-success mb-0">₦{{ number_format($totalEarnings, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings Statistics -->
<div class="row">
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                                <i data-lucide="dollar-sign" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Earnings</p>
                        <h4 class="mb-0">₦{{ number_format($totalEarnings, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="package" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Completed Deliveries</p>
                        <h4 class="mb-0">{{ $deliveries->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                                <i data-lucide="trending-up" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Average per Delivery</p>
                        <h4 class="mb-0">₦{{ $deliveries->count() > 0 ? number_format($totalEarnings / $deliveries->count(), 2) : '0.00' }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="calendar" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">This Month</p>
                        <h4 class="mb-0">₦{{ number_format($earningsByMonth->first()->total ?? 0, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Earnings Chart -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Earnings (Last 12 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="earningsChart" height="80"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Earnings History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Earnings History</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Route</th>
                                <th>Delivered</th>
                                <th>Delivery Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deliveries as $delivery)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.deliveries.show', $delivery) }}" class="fw-medium">
                                        {{ $delivery->order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $delivery->order->customer_name }}</div>
                                        <small class="text-muted">{{ $delivery->order->customer_phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <small class="text-muted d-block">
                                            <i data-lucide="map-pin" class="me-1" style="width: 12px; height: 12px;"></i>
                                            {{ Str::limit($delivery->pickup_address, 25) }}
                                        </small>
                                        <small class="text-muted">
                                            <i data-lucide="navigation" class="me-1" style="width: 12px; height: 12px;"></i>
                                            {{ Str::limit($delivery->delivery_address, 25) }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        {{ $delivery->delivered_at->format('d M, Y') }}
                                        <br>
                                        <small class="text-muted">{{ $delivery->delivered_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">₦{{ number_format($delivery->delivery_fee, 2) }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No earnings yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($deliveries->count() > 0)
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th class="text-success">₦{{ number_format($totalEarnings, 2) }}</th>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            @if($deliveries->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} of {{ $deliveries->total() }}
                    </div>
                    {{ $deliveries->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    lucide.createIcons();

    // Prepare chart data
    const monthLabels = @json($earningsByMonth->pluck('month')->reverse());
    const earningsData = @json($earningsByMonth->pluck('total')->reverse());

    // Create chart
    const ctx = document.getElementById('earningsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Earnings',
                data: earningsData,
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
                            return '₦' + context.parsed.y.toLocaleString('en-NG', {
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
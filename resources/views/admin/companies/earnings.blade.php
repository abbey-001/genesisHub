@extends('admin.layouts.app')

@section('title', 'Company Earnings')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $company->full_name }} - Earnings</h4>
            <p class="text-muted mb-0">Complete earnings breakdown</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to Company
            </a>
        </div>
    </div>

    <!-- Total Earnings Card -->
    <div class="card mb-4 bg-primary text-white">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="text-white mb-2">₦{{ number_format($totalEarnings, 0) }}</h3>
                    <p class="mb-0 opacity-75">Total Lifetime Earnings</p>
                    <small class="opacity-75">From {{ $company->completed_deliveries }} completed deliveries</small>
                </div>
                <div class="col-md-4 text-end">
                    <div class="avatar avatar-xl">
                        <span class="avatar-initial rounded bg-white text-primary">
                            <i class="bx bx-money bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Earnings by Month</h5>
        </div>
        <div class="card-body">
            @if($earningsByMonth->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Deliveries</th>
                            <th>Total Earnings</th>
                            <th>Average per Delivery</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($earningsByMonth as $earning)
                        @php
                            $deliveryCount = $company->deliveries()
                                ->where('status', 'delivered')
                                ->whereRaw('DATE_FORMAT(delivered_at, "%Y-%m") = ?', [$earning->month])
                                ->count();
                            $avgPerDelivery = $deliveryCount > 0 ? $earning->total / $deliveryCount : 0;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($earning->month . '-01')->format('F Y') }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ $deliveryCount }}</span>
                            </td>
                            <td>
                                <strong class="text-success">₦{{ number_format($earning->total, 0) }}</strong>
                            </td>
                            <td>
                                <span class="text-muted">₦{{ number_format($avgPerDelivery, 0) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th>Total</th>
                            <th>{{ $company->completed_deliveries }}</th>
                            <th class="text-success">₦{{ number_format($totalEarnings, 0) }}</th>
                            <th>
                                ₦{{ $company->completed_deliveries > 0 ? number_format($totalEarnings / $company->completed_deliveries, 0) : 0 }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="bx bx-bar-chart bx-lg text-muted mb-2"></i>
                <p class="text-muted mb-0">No earnings data yet</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Earnings -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Recent Earnings History</h5>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                    <tr>
                        <td>
                            <div>{{ $delivery->delivered_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $delivery->delivered_at->format('h:i A') }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-primary">
                                {{ $delivery->order->order_number }}
                            </a>
                        </td>
                        <td>
                            <div>{{ $delivery->order->customer_name }}</div>
                            <small class="text-muted">{{ $delivery->order->customer_phone }}</small>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ Str::limit($delivery->pickup_address, 20) }} → {{ Str::limit($delivery->delivery_address, 20) }}
                            </small>
                        </td>
                        <td>
                            <strong class="text-success">₦{{ number_format($delivery->delivery_fee, 0) }}</strong>
                        </td>
                        <td>
                            <a href="{{ route('admin.deliveries.show', $delivery) }}" class="btn btn-sm btn-icon btn-label-primary">
                                <i class="bx bx-show"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bx bx-money bx-lg text-muted mb-2"></i>
                            <p class="text-muted mb-0">No earnings yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
        <div class="card-footer">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} of {{ $deliveries->total() }} transactions
                </div>
                <div>
                    {{ $deliveries->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
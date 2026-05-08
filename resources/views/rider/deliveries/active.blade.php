@extends('rider.layouts.app')

@section('title', 'Active Deliveries')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-package me-2"></i>Active Deliveries
            </h4>
            <p class="text-muted mb-0">Manage your ongoing delivery tasks</p>
        </div>
        <span class="badge bg-primary fs-6">
            {{ $deliveries->count() }} Active
        </span>
    </div>

    @if($deliveries->count() > 0)

    {{-- ── Solo deliveries (no bundle) ─────────────────────── --}}
    @foreach($soloDeliveries as $delivery)
    <div class="col-lg-6">
        <div class="card border-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h6 class="mb-1">
                            <i class="bx bx-receipt me-1"></i>Order #{{ $delivery->order->order_number }}
                        </h6>
                        <span class="badge bg-{{ $delivery->status_badge }}">{{ $delivery->status_label }}</span>
                    </div>
                    <a href="{{ route('rider.deliveries.show', $delivery) }}" class="btn btn-sm btn-icon btn-label-primary">
                        <i class="bx bx-show"></i>
                    </a>
                </div>

                <div class="mb-3">
                    <ul class="timeline timeline-sm mb-0">
                        <li class="timeline-item">
                            <span class="timeline-point {{ in_array($delivery->status, ['assigned','picked_up']) ? 'timeline-point-success' : 'timeline-point-secondary' }}"></span>
                            <div class="timeline-event">
                                <h6 class="mb-0 small">Pickup</h6>
                                <p class="mb-0 text-muted small">{{ Str::limit($delivery->pickup_address, 50) }}</p>
                            </div>
                        </li>
                        <li class="timeline-item">
                            <span class="timeline-point {{ $delivery->status === 'picked_up' ? 'timeline-point-warning' : 'timeline-point-secondary' }}"></span>
                            <div class="timeline-event">
                                <h6 class="mb-0 small">Deliver To</h6>
                                <p class="mb-0 text-muted small">{{ Str::limit($delivery->delivery_address, 50) }}</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Items</small>
                        <strong>{{ $delivery->items->count() }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Fee</small>
                        <strong class="text-success">&#x20A6;{{ number_format($delivery->delivery_fee, 0) }}</strong>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('rider.deliveries.show', $delivery) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i>Manage Delivery
                    </a>
                    <a href="tel:{{ $delivery->order->customer_phone }}" class="btn btn-label-success">
                        <i class="bx bx-phone me-1"></i>Call Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- ── Bundle groups ──────────────────────────────────── --}}
    @foreach($bundleGroups as $bundleId => $bundleDeliveries)
    @php
        $firstDelivery = $bundleDeliveries->first();
        $bundle        = $firstDelivery->bundle;
        // Use the controller-computed fee (zone matrix + discount ÷ sellers).
        // This is correct even for deliveries created before the fee fix was deployed.
        $totalFee      = $bundleFees[$bundleId] ?? $bundleDeliveries->sum('delivery_fee');
        $allPickedUp   = $bundleDeliveries->every(fn($d) => $d->status === 'picked_up');
        $bundleStatus  = $allPickedUp ? 'picked_up' : 'assigned';
    @endphp
    <div class="col-lg-6">
        <div class="card border-primary h-100" style="border-width: 2px !important;">
            <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center py-2">
                <div>
                    <span class="badge bg-primary me-2">BUNDLE</span>
                    <strong class="small">{{ $bundle->pickup_zone }} Zone</strong>
                </div>
                <span class="badge bg-{{ $allPickedUp ? 'success' : 'warning' }}">
                    {{ $allPickedUp ? 'Picked Up' : 'Assigned' }}
                </span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    <i class="bx bx-info-circle me-1"></i>
                    One trip collects from <strong>{{ $bundleDeliveries->count() }}</strong> shop(s) — all for Order #{{ $firstDelivery->order->order_number }}
                    @if($bundle->ready_count < $bundle->expected_count)
                        <br><span class="text-warning">
                            <i class="bx bx-loader-alt bx-spin me-1"></i>
                            {{ $bundle->expected_count - $bundle->ready_count }} seller(s) in this zone were still preparing when you accepted —
                            they'll appear as separate deliveries in your list when they're ready.
                        </span>
                    @endif
                </p>

                {{-- Pickup stops --}}
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">Pickup Stops</small>
                    @foreach($bundleDeliveries as $bDelivery)
                    <div class="d-flex align-items-start mb-2 p-2 bg-light rounded">
                        <i class="bx bx-store text-primary me-2 mt-1"></i>
                        <div class="flex-grow-1">
                            <strong class="small d-block">{{ $bDelivery->seller->shop->shop_name ?? 'Shop' }}</strong>
                            <small class="text-muted">{{ Str::limit($bDelivery->pickup_address, 45) }}</small>
                        </div>
                        <span class="badge bg-{{ $bDelivery->status === 'picked_up' ? 'success' : 'warning' }} ms-2">
                            {{ $bDelivery->status === 'picked_up' ? '✓' : 'Pending' }}
                        </span>
                    </div>
                    @endforeach
                </div>

                {{-- Deliver to --}}
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Deliver To</small>
                    <div class="d-flex align-items-start">
                        <i class="bx bx-map text-success me-2 mt-1"></i>
                        <span class="small">{{ Str::limit($firstDelivery->delivery_address, 55) }}</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Total Items</small>
                        <strong>{{ $bundleDeliveries->sum(fn($d) => $d->items->count()) }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Total Earnings</small>
                        <strong class="text-success">&#x20A6;{{ number_format($totalFee, 0) }}</strong>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    {{-- Link to first delivery in bundle; the show page shows all siblings --}}
                    <a href="{{ route('rider.deliveries.show', $firstDelivery) }}" class="btn btn-primary">
                        <i class="bx bx-edit me-1"></i>Manage Bundle
                    </a>
                    <a href="tel:{{ $firstDelivery->order->customer_phone }}" class="btn btn-label-success">
                        <i class="bx bx-phone me-1"></i>Call Customer
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-package bx-lg text-muted mb-3"></i>
            <h5 class="mb-2">No Active Deliveries</h5>
            <p class="text-muted mb-3">You don't have any active deliveries at the moment.</p>
            <a href="{{ route('rider.deliveries.index') }}" class="btn btn-primary">
                <i class="bx bx-package me-1"></i>View All Deliveries
            </a>
        </div>
    </div>
    @endif

</div>
@endsection
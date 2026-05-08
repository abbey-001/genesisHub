{{-- ============================================ --}}
{{-- resources/views/rider/broadcasts/show.blade.php --}}
{{-- ============================================ --}}

@extends('rider.layouts.app')

@section('title', 'Broadcast Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    @php
        $isBundle    = $broadcast->is_bundle;
        $bundle      = $isBundle ? $broadcast->bundle : null;
        $delivery    = $isBundle ? $broadcast->bundle->deliveries->first() : $broadcast->delivery;
        // For growing bundles not all delivery rows exist yet, so summing delivery_fee
        // is always short. The controller passes $broadcastFee as the correct zone-matrix
        // total. Fall back to the sum only if the variable isn't available.
        $totalFee    = $broadcastFee ?? ($isBundle ? $bundle->deliveries->sum('delivery_fee') : $delivery->delivery_fee);
        $totalItems  = $isBundle ? $bundle->deliveries->sum(fn($d) => $d->items->count()) : $delivery->items->count();
        $totalWeight = $isBundle ? $bundle->deliveries->sum('package_weight') : $delivery->package_weight;
    @endphp

    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('rider.broadcasts.index') }}" class="btn btn-sm btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i>Back to Broadcasts
        </a>
    </div>

    <!-- Info Banner -->
    <div class="alert {{ $isBundle && $broadcast->is_partial ? 'alert-warning' : 'alert-info' }} mb-4">
        <div class="d-flex align-items-center">
            <i class="bx {{ $isBundle && $broadcast->is_partial ? 'bx-loader-alt bx-spin' : 'bx-broadcast' }} me-2 fs-5"></i>
            <div>
                @if($isBundle)
                    @if($broadcast->is_partial)
                        <h5 class="mb-0">Growing Bundle — Accept Now or Wait for More Stops</h5>
                        <p class="mb-0 mt-1">
                            <strong>{{ $bundle->ready_count }}</strong> of <strong>{{ $bundle->expected_count }}</strong> sellers in
                            <strong>{{ $bundle->pickup_zone }}</strong> are ready now.
                            If you accept, you get these <strong>{{ $bundle->deliveries->count() }}</strong> stop(s) immediately —
                            any sellers who mark ready later will be broadcast separately.
                            If you wait, you may see more stops added to this job.
                        </p>
                    @else
                        <h5 class="mb-0">Bundle Pickup Available</h5>
                        <p class="mb-0 mt-1">
                            All <strong>{{ $bundle->deliveries->count() }} shops</strong> in the
                            <strong>{{ $bundle->pickup_zone }}</strong> zone are ready — one trip, full payout!
                        </p>
                    @endif
                @else
                    <h5 class="mb-0">New Delivery Available</h5>
                    <p class="mb-0 mt-1">First rider to accept gets the delivery!</p>
                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Delivery Details -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Delivery Information</h5>
                </div>
                <div class="card-body">
                    <!-- Order Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Details</h6>
                            <p class="mb-1"><strong>Order #:</strong> {{ $delivery->order->order_number }}</p>
                            <p class="mb-1"><strong>Total Fee:</strong> <span class="text-success fs-4">₦{{ number_format($totalFee, 0) }}</span></p>
                            <p class="mb-1"><strong>Total Items:</strong> {{ $totalItems }}</p>
                            <p class="mb-0"><strong>Total Weight:</strong> {{ $totalWeight ? number_format($totalWeight, 1) . ' kg' : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Broadcast Info</h6>
                            @if($isBundle)
                                <p class="mb-1"><strong>Type:</strong>
                                    <span class="badge bg-primary">Bundle Pickup</span>
                                    @if($broadcast->is_partial)
                                        <span class="badge bg-warning ms-1">Partial</span>
                                    @endif
                                </p>
                                <p class="mb-1"><strong>Zone:</strong> {{ $bundle->pickup_zone }}</p>
                                <p class="mb-1"><strong>Shops:</strong> {{ $bundle->deliveries->count() }}</p>
                            @else
                                <p class="mb-1"><strong>Broadcasted to:</strong> {{ $broadcast->broadcast_to_count ?? '—' }} riders</p>
                                <p class="mb-1"><strong>Views:</strong> {{ $broadcast->view_count ?? 0 }} riders</p>
                            @endif
                            <p class="mb-0"><strong>Posted:</strong> {{ $broadcast->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if($isBundle)
                    {{-- ── Bundle: show each seller as a separate pickup stop ── --}}
                    <h6 class="mb-3">Pickup Stops</h6>
                    @foreach($bundle->deliveries as $bDelivery)
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary bg-opacity-10 py-2 d-flex justify-content-between">
                            <strong class="small">
                                <i class="bx bx-store me-1"></i>
                                {{ $bDelivery->seller->shop->shop_name ?? 'Seller Shop' }}
                            </strong>
                            <small class="text-muted">Fee: ₦{{ number_format($bDelivery->delivery_fee, 0) }}</small>
                        </div>
                        <div class="card-body py-2">
                            <p class="mb-1 small">{{ $bDelivery->pickup_address }}</p>
                            @if($bDelivery->seller->shop->phone_number)
                            <a href="tel:{{ $bDelivery->seller->shop->phone_number }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-phone me-1"></i>Call Shop
                            </a>
                            @endif
                            @if($bDelivery->package_notes)
                            <p class="mb-0 mt-2 text-muted small">
                                <i class="bx bx-note me-1"></i>{{ $bDelivery->package_notes }}
                            </p>
                            @endif

                            {{-- Items for this seller --}}
                            <div class="mt-2">
                                <small class="text-muted d-block mb-1">Items from this shop:</small>
                                <ul class="list-unstyled mb-0">
                                    @foreach($bDelivery->items as $bItem)
                                    <li class="small"><i class="bx bx-package text-primary me-1"></i>{{ $bItem->product_name }} (×{{ $bItem->quantity }})</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endforeach

                    @if($broadcast->is_partial)
                    <div class="alert alert-warning mb-4">
                        <i class="bx bx-loader-alt bx-spin me-1"></i>
                        <strong>Growing Bundle:</strong> {{ $bundle->ready_count }} of {{ $bundle->expected_count }} sellers are ready now.
                        Accepting locks you into these {{ $bundle->deliveries->count() }} stop(s). Sellers who mark ready after you accept will receive separate solo broadcasts.
                    </div>
                    @endif

                    @else
                    {{-- ── Single delivery: existing items table ── --}}
                    <h6 class="mb-3">Items to Deliver</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>SKU</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($delivery->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td><small class="text-muted">{{ $item->product_sku }}</small></td>
                                    <td><span class="badge bg-label-primary">{{ $item->quantity }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Pickup & Delivery Locations -->
                    <h6 class="mb-3">{{ $isBundle ? 'Delivery Destination' : 'Pickup & Delivery Locations' }}</h6>
                    <div class="row">
                        @if(!$isBundle)
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="bx bx-store fs-4 text-primary me-2"></i>
                                        <div>
                                            <h6 class="mb-1">Pickup From</h6>
                                            <p class="mb-1"><strong>{{ $delivery->seller->shop->shop_name ?? 'Seller Shop' }}</strong></p>
                                            <p class="mb-1 small">{{ $delivery->pickup_address }}</p>
                                            @if($delivery->seller->shop->phone_number)
                                            <p class="mb-0">
                                                <a href="tel:{{ $delivery->seller->shop->phone_number }}" class="btn btn-sm btn-primary">
                                                    <i class="bx bx-phone me-1"></i>Call Shop
                                                </a>
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="{{ $isBundle ? 'col-12' : 'col-md-6' }} mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-2">
                                        <i class="bx bx-map fs-4 text-success me-2"></i>
                                        <div>
                                            <h6 class="mb-1">Deliver To</h6>
                                            <p class="mb-1"><strong>{{ $delivery->order->customer_name }}</strong></p>
                                            <p class="mb-1 small">{{ $delivery->delivery_address }}</p>
                                            <p class="mb-0">
                                                <a href="tel:{{ $delivery->order->customer_phone }}" class="btn btn-sm btn-success">
                                                    <i class="bx bx-phone me-1"></i>Call Customer
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(!$isBundle && $delivery->package_notes)
                    <div class="alert alert-info">
                        <h6 class="mb-2"><i class="bx bx-info-circle me-1"></i>Package Notes</h6>
                        <p class="mb-0">{{ $delivery->package_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Actions -->
        <div class="col-lg-4">
            <!-- Accept Card -->
            <div class="card border-success mb-4">
                <div class="card-body text-center">
                    <i class="bx bx-check-shield bx-lg text-success mb-3"></i>
                    <h5 class="mb-3">
                        @if($isBundle && $broadcast->is_partial)
                            Accept {{ $bundle->ready_count }} Stop(s) Now?
                        @elseif($isBundle)
                            Ready to Accept Bundle?
                        @else
                            Ready to Accept?
                        @endif
                    </h5>
                    @if($isBundle)
                        @if($broadcast->is_partial)
                            <p class="text-muted mb-2">
                                Lock in <strong>{{ $bundle->deliveries->count() }} confirmed stop(s)</strong> in
                                <strong>{{ $bundle->pickup_zone }}</strong> and earn at least
                            </p>
                            <p class="text-success fs-4 fw-bold mb-1">₦{{ number_format($totalFee, 0) }}</p>
                            <p class="text-muted small mb-4">
                                <i class="bx bx-info-circle me-1"></i>
                                {{ $bundle->expected_count - $bundle->ready_count }} more seller(s) still preparing —
                                if you wait they may add more stops and increase your payout.
                            </p>
                        @else
                            <p class="text-muted mb-2">
                                Pick up from <strong>{{ $bundle->deliveries->count() }} shops</strong> in
                                <strong>{{ $bundle->pickup_zone }}</strong> and earn
                            </p>
                            <p class="text-success fs-4 fw-bold mb-4">₦{{ number_format($totalFee, 0) }}</p>
                        @endif
                    @else
                        <p class="text-muted mb-4">Be the first to accept and earn <strong class="text-success">₦{{ number_format($totalFee, 0) }}</strong></p>
                    @endif

                    <form action="{{ route('rider.broadcasts.accept', $broadcast) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-lg w-100 mb-3 pulse-btn">
                            <i class="bx bx-check-circle me-2"></i>
                            @if($isBundle && $broadcast->is_partial)
                                Accept {{ $bundle->ready_count }} Stop(s) Now
                            @elseif($isBundle)
                                Accept Bundle
                            @else
                                Accept This Delivery
                            @endif
                        </button>
                    </form>

                    <button type="button" class="btn btn-label-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bx bx-x-circle me-1"></i>Not Interested
                    </button>
                </div>
            </div>

            <!-- Stats Card -->
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">{{ $isBundle ? 'Bundle Summary' : 'Broadcast Statistics' }}</h6>
                    @if($isBundle)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Zone:</span>
                        <strong>{{ $bundle->pickup_zone }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pickup Stops:</span>
                        <strong>{{ $bundle->deliveries->count() }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Items:</span>
                        <strong>{{ $totalItems }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Weight:</span>
                        <strong>{{ number_format($totalWeight, 1) }} kg</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Earnings:</span>
                        <strong class="text-success">₦{{ number_format($totalFee, 0) }}</strong>
                    </div>
                    @else
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Notified:</span>
                        <strong>{{ $broadcast->broadcast_to_count ?? '—' }} riders</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Viewed:</span>
                        <strong>{{ $broadcast->view_count ?? 0 }} riders</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Rejected:</span>
                        <strong>{{ $broadcast->reject_count ?? 0 }} riders</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rider.broadcasts.reject', $broadcast) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Not Interested in This Delivery?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <select name="reason" class="form-select" required>
                            <option value="">Select reason</option>
                            <option value="too_far">Too far from my location</option>
                            <option value="too_busy">Currently too busy</option>
                            <option value="vehicle_issue">Vehicle issue</option>
                            <option value="other">Other reason</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="200"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Broadcast</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
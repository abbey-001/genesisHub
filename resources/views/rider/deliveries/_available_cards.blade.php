{{--
    Partial: resources/views/rider/deliveries/_available_cards.blade.php
    Receives: $broadcasts (Collection), $fees (array keyed by broadcast.id)
    Rendered by available() on full page load AND by availablePoll() for AJAX.
--}}
@if($broadcasts->count() > 0)
<div class="row g-4">
@foreach($broadcasts as $broadcast)
    @php
        $isBundle        = $broadcast->is_bundle;
        $bundle          = $isBundle ? $broadcast->bundle : null;
        $delivery        = $isBundle ? null : $broadcast->delivery;
        $order           = $isBundle ? $bundle->order : $delivery->order;
        $deliveryAddress = $isBundle
            ? collect([$order->shipping_address, $order->shipping_city, $order->shipping_state])->filter()->implode(', ')
            : ($delivery->delivery_address ?? '');
        $totalFee        = $fees[$broadcast->id] ?? 0;
        $totalItems      = $isBundle
            ? $bundle->deliveries->sum(fn($d) => $d->relationLoaded('items') ? $d->items->count() : 0)
            : $delivery->items->count();
    @endphp

    <div class="col-lg-6">
        <div class="card h-100 {{ $isBundle ? 'border-primary' : 'border-info' }}">
            <div class="card-body">

                <!-- Type badge + fee -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    @if($isBundle)
                        @if($broadcast->is_partial)
                            <span class="badge bg-warning">
                                <i class="bx bx-loader-alt bx-spin me-1"></i>GROWING — More Stops Possible
                            </span>
                        @else
                            <span class="badge bg-primary">BUNDLE PICKUP</span>
                        @endif
                    @else
                        <span class="badge bg-label-warning">BROADCAST</span>
                    @endif
                    <span class="badge bg-success fs-6">&#x20A6;{{ number_format($totalFee, 0) }}</span>
                </div>

                <!-- Order header -->
                <div class="mb-3">
                    <h6 class="mb-1">Order #{{ $order->order_number }}</h6>
                    @if($isBundle)
                        <small class="text-muted">
                            <i class="bx bx-store me-1"></i>
                            {{ $bundle->deliveries->count() }} stop(s) confirmed in <strong>{{ $bundle->pickup_zone }}</strong>
                            @if($broadcast->is_partial)
                                &mdash; {{ $bundle->ready_count }}/{{ $bundle->expected_count }} sellers ready
                                <span class="text-warning">(more may be added before you accept)</span>
                            @endif
                        </small>
                    @endif
                </div>

                <!-- Pickup stops / seller -->
                @if($isBundle)
                <div class="mb-3">
                    <small class="text-muted d-block mb-2">Pickup Stops</small>
                    @foreach($bundle->deliveries as $bDelivery)
                    <div class="p-2 bg-light rounded mb-2">
                        <div class="d-flex align-items-center mb-1">
                            <i class="bx bx-store text-primary me-2"></i>
                            <strong class="small">{{ $bDelivery->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                        </div>
                        <small class="text-muted ms-4">{{ Str::limit($bDelivery->pickup_address, 50) }}</small>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="mb-3 p-2 bg-light rounded">
                    <small class="text-muted d-block">Pickup From</small>
                    <div class="d-flex align-items-center">
                        <i class="bx bx-store text-primary me-2"></i>
                        <strong>{{ $delivery->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                    </div>
                    <small class="text-muted ms-4">{{ Str::limit($delivery->pickup_address, 50) }}</small>
                </div>
                @endif

                <!-- Delivery address -->
                <div class="mb-3">
                    <div class="d-flex align-items-start">
                        <div class="avatar avatar-xs flex-shrink-0 me-2">
                            <span class="avatar-initial rounded-circle bg-label-primary">
                                <i class="bx bx-map bx-xs"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Delivery Address</small>
                            <span class="small">{{ Str::limit($deliveryAddress, 50) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stats row -->
                <div class="d-flex justify-content-between mb-3 text-center">
                    <div>
                        <i class="bx bx-package text-muted"></i>
                        <div class="small text-muted">Items</div>
                        <strong>{{ $totalItems }}</strong>
                    </div>
                    <div>
                        <i class="bx bx-store text-muted"></i>
                        <div class="small text-muted">{{ $isBundle ? 'Shops' : 'Seller' }}</div>
                        <strong>{{ $isBundle ? $bundle->deliveries->count() : 1 }}</strong>
                    </div>
                    <div>
                        <i class="bx bx-money text-muted"></i>
                        <div class="small text-muted">Earn</div>
                        <strong class="text-success">&#x20A6;{{ number_format($totalFee, 0) }}</strong>
                    </div>
                </div>

                @if(!$isBundle && $delivery->package_notes)
                <div class="alert alert-info mb-3 py-2">
                    <small>
                        <i class="bx bx-info-circle me-1"></i>
                        <strong>Note:</strong> {{ Str::limit($delivery->package_notes, 60) }}
                    </small>
                </div>
                @endif

                <!-- Actions -->
                <div class="d-flex gap-2">
                    <button type="button"
                            class="btn btn-label-primary btn-sm flex-fill"
                            data-bs-toggle="modal"
                            data-bs-target="#deliveryModal{{ $broadcast->id }}">
                        <i class="bx bx-show me-1"></i>View Details
                    </button>
                    <form action="{{ route('rider.deliveries.accept', $broadcast) }}"
                          method="POST"
                          class="flex-fill accept-form"
                          data-confirm="{{ $isBundle
                              ? 'Accept bundle? You will collect from '.$bundle->deliveries->count().' confirmed stop(s) in '.$bundle->pickup_zone.'.'.($broadcast->is_partial ? ' More stops may still be added by other sellers.' : '')
                              : 'Accept this delivery? First to accept wins!' }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm w-100 pulse-btn">
                            <i class="bx bx-check-circle me-1"></i>
                            {{ $isBundle ? ($broadcast->is_partial ? 'Accept Now ('.($bundle->ready_count).' stops)' : 'Accept Bundle') : 'Accept Now!' }}
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Details Modal -->
    <div class="modal fade" id="deliveryModal{{ $broadcast->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isBundle ? 'Bundle Pickup Details' : 'Delivery Details' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p class="mb-1"><strong>Order #:</strong> {{ $order->order_number }}</p>
                            <p class="mb-1"><strong>Total Fee:</strong> <span class="text-success">&#x20A6;{{ number_format($totalFee, 0) }}</span></p>
                            <p class="mb-1"><strong>Total Items:</strong> {{ $totalItems }}</p>
                        </div>
                        <div class="col-md-6">
                            @if($isBundle)
                            <h6>Bundle Info</h6>
                            <p class="mb-1"><strong>Zone:</strong> {{ $bundle->pickup_zone }}</p>
                            <p class="mb-1"><strong>Shops:</strong> {{ $bundle->deliveries->count() }}</p>
                            @if($broadcast->is_partial)
                            <p class="mb-0">
                                <span class="badge bg-warning">Partial &mdash; {{ $bundle->ready_count }}/{{ $bundle->expected_count }} ready</span>
                            </p>
                            @endif
                            @else
                            <h6>Seller Information</h6>
                            <p class="mb-1"><strong>Shop:</strong> {{ $delivery->seller->shop->shop_name ?? 'Seller Shop' }}</p>
                            <p class="mb-0"><strong>Phone:</strong> {{ $delivery->seller->shop->phone_number ?? 'N/A' }}</p>
                            @endif
                        </div>
                    </div>

                    @if($isBundle)
                    <h6>Pickup Stops</h6>
                    @foreach($bundle->deliveries as $bDelivery)
                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary bg-opacity-10 py-2 d-flex justify-content-between">
                            <strong class="small">{{ $bDelivery->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                            <small>&#x20A6;{{ number_format($bDelivery->delivery_fee, 0) }}</small>
                        </div>
                        <div class="card-body py-2">
                            <p class="mb-1 small">{{ $bDelivery->pickup_address }}</p>
                            @if($bDelivery->seller->shop?->phone_number)
                            <a href="tel:{{ $bDelivery->seller->shop->phone_number }}" class="btn btn-xs btn-primary btn-sm">
                                <i class="bx bx-phone me-1"></i>Call
                            </a>
                            @endif
                            <ul class="list-unstyled mt-2 mb-0">
                                @foreach($bDelivery->items as $bItem)
                                <li class="small">
                                    <i class="bx bx-package text-primary me-1"></i>{{ $bItem->product_name }} (&#xd7;{{ $bItem->quantity }})
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <h6>Items</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm">
                            <thead><tr><th>Product</th><th>Qty</th></tr></thead>
                            <tbody>
                                @foreach($delivery->items as $item)
                                <tr><td>{{ $item->product_name }}</td><td>{{ $item->quantity }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <h6>Delivery Address</h6>
                    <div class="p-3 bg-light rounded mb-3">
                        <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                        <p class="mb-0">{{ $deliveryAddress }}</p>
                    </div>

                    @if(!$isBundle && $delivery->package_notes)
                    <div class="alert alert-info">
                        <strong>Package Notes:</strong><br>{{ $delivery->package_notes }}
                    </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="{{ route('rider.deliveries.accept', $broadcast) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-check-circle me-1"></i>{{ $isBundle ? 'Accept Bundle' : 'Accept Delivery' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endforeach
</div>

@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bx bx-package bx-lg text-muted mb-3"></i>
        <h5 class="mb-2">No Available Deliveries</h5>
        <p class="text-muted mb-3">
            There are no broadcast deliveries at the moment.<br>
            You will be notified when new deliveries are available.
        </p>
        <a href="{{ route('rider.dashboard') }}" class="btn btn-primary">
            <i class="bx bx-home me-1"></i>Go to Dashboard
        </a>
    </div>
</div>
@endif
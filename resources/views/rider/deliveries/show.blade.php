@extends('rider.layouts.app')

@section('title', 'Delivery Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Delivery Details</h4>
            <p class="text-muted mb-0">Order #{{ $delivery->order->order_number }}</p>
        </div>
        <div>
            <a href="{{ route('rider.deliveries.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row g-6">
        <!-- Delivery Status & Actions -->
        <div class="col-lg-8">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Delivery Status</h5>
                        <span class="badge bg-{{ $delivery->status_badge }} fs-6">{{ $delivery->status_label }}</span>
                    </div>

                    <!-- Timeline -->
                    <ul class="timeline mb-4">
                        <li class="timeline-item {{ in_array($delivery->status, ['assigned', 'picked_up', 'delivered']) ? 'timeline-item-transparent' : '' }}">
                            <span class="timeline-point {{ in_array($delivery->status, ['assigned', 'picked_up', 'delivered']) ? 'timeline-point-success' : 'timeline-point-secondary' }}"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Assigned to Company</h6>
                                    @if($delivery->assigned_at)
                                    <small class="text-muted">{{ $delivery->assigned_at->format('M d, Y h:i A') }}</small>
                                    @endif
                                </div>
                            </div>
                        </li>
                        
                        <li class="timeline-item {{ in_array($delivery->status, ['picked_up', 'delivered']) ? 'timeline-item-transparent' : '' }}">
                            <span class="timeline-point {{ in_array($delivery->status, ['picked_up', 'delivered']) ? 'timeline-point-success' : 'timeline-point-secondary' }}"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Package Picked Up</h6>
                                    @if($delivery->picked_up_at)
                                    <small class="text-muted">{{ $delivery->picked_up_at->format('M d, Y h:i A') }}</small>
                                    @endif
                                </div>
                            </div>
                        </li>
                        
                        <li class="timeline-item {{ $delivery->status === 'delivered' ? 'timeline-item-transparent' : '' }}">
                            <span class="timeline-point {{ $delivery->status === 'delivered' ? 'timeline-point-success' : 'timeline-point-secondary' }}"></span>
                            <div class="timeline-event">
                                <div class="timeline-header mb-1">
                                    <h6 class="mb-0">Delivered</h6>
                                    @if($delivery->delivered_at)
                                    <small class="text-muted">{{ $delivery->delivered_at->format('M d, Y h:i A') }}</small>
                                    @endif
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Action Buttons -->
                    @if($delivery->status === 'assigned')

                    @if($delivery->bundle_id && $bundleSiblings->count() > 0)
                    {{-- Bundle: show all pickup stops so the rider knows they must
                         collect from every shop before confirming pickup. --}}
                    <div class="alert alert-primary mb-3">
                        <h6 class="alert-heading mb-2">
                            <i class="bx bx-store me-1"></i>
                            Bundle Pickup — {{ $bundleSiblings->count() + 1 }} shops to collect from
                        </h6>
                        <p class="mb-2 small">Pick up all packages before confirming. One confirmation covers all shops in this zone.</p>
                        <ul class="list-unstyled mb-0">
                            {{-- Current delivery's shop --}}
                            <li class="d-flex align-items-start mb-2">
                                <span class="badge bg-success me-2 mt-1">1</span>
                                <div>
                                    <strong class="small">{{ $delivery->seller->shop->shop_name ?? 'Your Shop' }}</strong>
                                    <div class="small text-muted">{{ $delivery->pickup_address }}</div>
                                    @if($delivery->seller->shop?->phone_number)
                                    <a href="tel:{{ $delivery->seller->shop->phone_number }}" class="small">
                                        <i class="bx bx-phone me-1"></i>{{ $delivery->seller->shop->phone_number }}
                                    </a>
                                    @endif
                                </div>
                            </li>
                            {{-- Sibling deliveries' shops --}}
                            @foreach($bundleSiblings as $i => $sibling)
                            <li class="d-flex align-items-start mb-2">
                                <span class="badge bg-success me-2 mt-1">{{ $i + 2 }}</span>
                                <div>
                                    <strong class="small">{{ $sibling->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                                    <div class="small text-muted">{{ $sibling->pickup_address }}</div>
                                    @if($sibling->seller->shop?->phone_number)
                                    <a href="tel:{{ $sibling->seller->shop->phone_number }}" class="small">
                                        <i class="bx bx-phone me-1"></i>{{ $sibling->seller->shop->phone_number }}
                                    </a>
                                    @endif
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#pickupModal">
                        <i class="bx bx-check-circle me-1"></i>
                        {{ $delivery->bundle_id ? 'Confirm All Packages Picked Up' : 'Confirm Pickup' }}
                    </button>
                    @endif

                    @if($delivery->status === 'picked_up')
                    @php
                        // For bundle deliveries, the rider must have picked up every
                        // sibling delivery before they can mark the order as delivered.
                        // confirmPickup() already bulk-marks siblings as picked_up, so
                        // this check is a safety guard against any edge case where a
                        // sibling was not updated (e.g. a mid-transaction failure).
                        $siblingsAllPickedUp = $bundleSiblings->isEmpty()
                            || $bundleSiblings->every(fn($s) => $s->status === 'picked_up');
                    @endphp

                    @if(!$siblingsAllPickedUp)
                    <div class="alert alert-warning mb-3">
                        <i class="bx bx-info-circle me-1"></i>
                        <strong>Not all packages collected yet.</strong>
                        You must pick up from all shops in this bundle before completing delivery.
                        <ul class="mt-2 mb-0 small">
                            @foreach($bundleSiblings->where('status', '!=', 'picked_up') as $pending)
                            <li>
                                {{ $pending->seller->shop->shop_name ?? 'Seller Shop' }}
                                &mdash; {{ $pending->pickup_address }}
                                <span class="badge bg-warning ms-1">{{ $pending->status }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-success flex-fill" data-bs-toggle="modal" data-bs-target="#deliverModal">
                            <i class="bx bx-check-circle me-1"></i> Complete Delivery
                        </button>
                        <button type="button" class="btn btn-danger flex-fill" data-bs-toggle="modal" data-bs-target="#failModal">
                            <i class="bx bx-x-circle me-1"></i> Mark Failed
                        </button>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Locations -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-4">Locations</h5>

                    @php
                        // Full ordered list of pickup stops: current delivery first, then siblings.
                        // For a single delivery $bundleSiblings is an empty collection,
                        // so $allDeliveries will just contain [$delivery].
                        $allDeliveries = collect([$delivery])->merge($bundleSiblings);
                    @endphp

                    @foreach($allDeliveries as $i => $stop)
                    <div class="d-flex align-items-start {{ $loop->last ? '' : 'mb-4' }}">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-store"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                Pickup Stop {{ $allDeliveries->count() > 1 ? ($i + 1) : '' }}
                                @if($stop->seller->shop)
                                    &mdash; {{ $stop->seller->shop->shop_name }}
                                @endif
                            </h6>
                            <p class="mb-0 text-muted">{{ $stop->pickup_address }}</p>
                            @if($stop->seller->shop?->phone_number)
                            <a href="tel:{{ $stop->seller->shop->phone_number }}" class="small">
                                <i class="bx bx-phone me-1"></i>{{ $stop->seller->shop->phone_number }}
                            </a>
                            @endif
                        </div>
                    </div>
                    @if(!$loop->last)
                    <div class="border-dashed border-bottom my-3"></div>
                    @endif
                    @endforeach

                    <div class="border-dashed border-bottom my-3"></div>

                    <!-- Drop-off (same for all deliveries in the bundle — same customer) -->
                    <div class="d-flex align-items-start">
                        <div class="avatar flex-shrink-0 me-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-map"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Delivery Location</h6>
                            <p class="mb-0 text-muted">{{ $delivery->delivery_address }}</p>
                            <p class="mb-0 small text-muted">{{ $delivery->order->customer_name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Package Items -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        Package Items
                        @if($bundleSiblings->count() > 0)
                        <small class="text-muted fw-normal">— all {{ $allDeliveries->count() }} shops</small>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    @foreach($allDeliveries as $stop)

                    @if($allDeliveries->count() > 1)
                    {{-- Shop sub-header so the rider can see which items belong to which shop --}}
                    <div class="px-4 py-2 bg-light border-bottom d-flex align-items-center">
                        <i class="bx bx-store text-primary me-2"></i>
                        <strong class="small">{{ $stop->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                    </div>
                    @endif

                    <div class="px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stop->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($item->product && $item->product->main_image)
                                                <img src="{{ asset('public/storage/'.$item->product->main_image) }}"
                                                     alt="{{ $item->product_name }}" class="me-2 rounded"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                                <span>{{ $item->product_name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($stop->package_notes)
                        <div class="alert alert-info mt-3 mb-0 py-2">
                            <small>
                                <strong>Note:</strong> {{ $stop->package_notes }}
                            </small>
                        </div>
                        @endif
                    </div>

                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Delivery Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Delivery Information</h6>

                    @if($bundleSiblings->count() > 0)
                    {{-- Bundle: sum fee and items across all deliveries --}}
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Fee:</span>
                        <span class="fw-medium">₦{{ number_format($bundleTotalFee, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shops:</span>
                        <span>{{ $allDeliveries->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Items:</span>
                        <span>{{ $allDeliveries->sum(fn($d) => $d->items->count()) }}</span>
                    </div>
                    @else
                    {{-- Single delivery --}}
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery Fee:</span>
                        <span class="fw-medium">₦{{ number_format($delivery->delivery_fee, 0) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Package Weight:</span>
                        <span>{{ $delivery->package_weight ?? 'N/A' }} kg</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Items:</span>
                        <span>{{ $delivery->items->count() }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Customer Contact -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3">Customer Contact</h6>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bx bx-user me-2"></i>
                        <span>{{ $delivery->order->customer_name }}</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bx bx-phone me-2"></i>
                        <span>{{ $delivery->order->customer_phone }}</span>
                    </div>
                    
                    <div class="mt-3">
                        <a href="tel:{{ $delivery->order->customer_phone }}" class="btn btn-primary w-100">
                            <i class="bx bx-phone me-1"></i> Call Customer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Proof Photos -->
            @if($delivery->pickup_photo || $delivery->delivery_proof || $delivery->failure_photo)
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3">Proof Photos</h6>
                    
                    @if($delivery->pickup_photo)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Pickup Photo</small>
                        <img src="{{ asset('public/storage/'. $delivery->pickup_photo) }}" 
                             alt="Pickup Proof" class="img-fluid rounded">
                    </div>
                    @endif
                    
                    @if($delivery->delivery_proof)
                    <div class="mb-3">
                        <small class="text-muted d-block mb-1">Delivery Proof</small>
                        <img src="{{ asset('public/storage/'.$delivery->delivery_proof) }}" 
                             alt="Delivery Proof" class="img-fluid rounded">
                    </div>
                    @endif
                    
                    @if($delivery->failure_photo)
                    <div>
                        <small class="text-muted d-block mb-1">Failure Evidence</small>
                        <img src="{{ asset('public/storage/'. $delivery->failure_photo) }}" 
                             alt="Failure Evidence" class="img-fluid rounded">
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Pickup Modal -->
<div class="modal fade" id="pickupModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rider.deliveries.confirm-pickup', $delivery) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $delivery->bundle_id ? 'Confirm All Packages Picked Up' : 'Confirm Pickup' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @if($delivery->bundle_id && $bundleSiblings->count() > 0)
                    <div class="alert alert-info py-2 mb-3">
                        <small>
                            <i class="bx bx-info-circle me-1"></i>
                            This confirms pickup from <strong>all {{ $bundleSiblings->count() + 1 }} shops</strong>
                            in zone <strong>{{ $delivery->bundle->pickup_zone }}</strong>.
                            Only submit once you have collected every package.
                        </small>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Upload Package Photo *</label>
                        <input type="file" name="pickup_photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">
                            {{ $delivery->bundle_id ? 'Take a photo showing all collected packages' : 'Take a clear photo of the package' }}
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        {{ $delivery->bundle_id ? 'Confirm All Picked Up' : 'Confirm Pickup' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Deliver Modal -->
<div class="modal fade" id="deliverModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rider.deliveries.complete', $delivery) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complete Delivery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Delivery Proof Photo *</label>
                        <input type="file" name="delivery_photo" class="form-control" accept="image/*" required>
                        <small class="text-muted">Photo of delivered package or customer receiving</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Delivery</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Fail Modal -->
<div class="modal fade" id="failModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('rider.deliveries.fail', $delivery) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark Delivery Failed</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Failure Reason *</label>
                        <select name="failure_reason" class="form-select" required>
                            <option value="">Select reason</option>
                            <option value="customer_unavailable">Customer Unavailable</option>
                            <option value="wrong_address">Wrong Address</option>
                            <option value="refused">Customer Refused</option>
                            <option value="access_issue">Access Issue</option>
                            <option value="damaged">Package Damaged</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Details *</label>
                        <textarea name="failure_notes" class="form-control" rows="3" required placeholder="Explain what happened..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Photo Evidence *</label>
                        <input type="file" name="failure_photo" class="form-control" accept="image/*" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Mark as Failed</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
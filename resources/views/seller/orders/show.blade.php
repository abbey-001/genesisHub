@extends('seller.layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Order #{{ $order->order_number }}</h4>
            <p class="text-muted mb-0">
                <i class="bx bx-calendar me-1"></i>
                Placed on {{ $order->created_at->format('F d, Y h:i A') }}
            </p>
        </div>
        <div>
            <a href="{{ route('seller.orders.invoice', $order) }}" class="btn btn-primary" target="_blank">
                <i class="bx bx-printer me-1"></i> Print Invoice
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-package me-2"></i>Your Items in this Order
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>z
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($item->product)
                                                <img src="{{ asset('public/storage/'.$item->product->main_image) }}" 
                                                     alt="{{ $item->product_name }}" 
                                                     class="rounded me-3" 
                                                     style="width: 60px; height: 60px; object-fit: cover;">
                                            @endif
                                            <div>
                                                <div class="fw-medium mb-1">{{ $item->product_name }}</div>
                                                @if(!empty($item->variant_options))
                                                  <div class="mt-1 mb-1">
                                                    @foreach($item->variant_options as $vName => $vValue)
                                                      <span style="display:inline-flex;align-items:center;gap:3px;font-size:11px;font-weight:600;color:#714e32;background:#fdf1e8;border:1px solid #e8d5c4;border-radius:4px;padding:2px 7px;margin-right:4px;">
                                                        {{ $vName }}: {{ $vValue }}
                                                      </span>
                                                    @endforeach
                                                  </div>
                                                @endif
                                                @if($item->product_sku)
                                                    <small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="">{{ $item->quantity }}</span>
                                    </td>
                                    <td>₦{{ number_format($item->price, 2) }}</td>
                                    <td class="fw-bold">₦{{ number_format($item->total_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $item->status === 'pending' ? 'warning' : 
                                            ($item->status === 'processing' ? 'info' : 
                                            ($item->status === 'ready_for_pickup' ? 'primary' :
                                            ($item->status === 'in_transit' ? 'info' :
                                            ($item->status === 'delivered' ? 'success' : 'secondary')))) 
                                        }}">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                    </td>
                                   <td>
                                        @if($order->payment_status === 'paid')
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    @if(in_array($item->status, ['pending', 'processing']))
                                                        <li>
                                                            <button type="button" 
                                                                    class="dropdown-item" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#statusModal{{ $item->id }}">
                                                                <i class="bx bx-edit me-2"></i>Update Status
                                                            </button>
                                                        </li>
                                                        @if($item->status === 'processing')
                                                        <li>
                                                            <button type="button" 
                                                                    class="dropdown-item text-success" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#readyModal{{ $item->id }}">
                                                                <i class="bx bx-check-circle me-2"></i>Mark Ready
                                                            </button>
                                                        </li>
                                                        @endif
                                                    @endif
                                                </ul>
                                            </div>
                                        @else
                                            <div class="text-muted" data-bs-toggle="tooltip" 
                                                 title="This order is still awaiting payment confirmation from the customer. No action is needed or possible yet. Once payment succeeds, the status will update automatically and actions will become available.">
                                                <i class="bx bx-info-circle me-1"></i>Awaiting Payment
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">Your Total:</th>
                                    <th colspan="3" class="text-success fs-5">₦{{ number_format($sellerTotal, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Delivery Status -->
            @php
                $deliveries = $order->deliveries()->where('seller_id', Auth::guard('seller')->user()->seller->id)->with('rider', 'items')->get();
            @endphp

            @if($deliveries->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-truck me-2"></i>Delivery Tracking
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($deliveries as $delivery)
                    <div class="card border {{ $loop->last ? '' : 'mb-3' }}">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="bx bx-package me-1"></i>
                                        Delivery #{{ $delivery->id }}
                                    </h6>
                                    <span class="badge bg-{{ $delivery->status_badge }} mb-2">
                                        {{ $delivery->status_label }}
                                    </span>
                                </div>
                                @if($delivery->rider)
                                <div class="text-end">
                                    <div class="d-flex align-items-center mb-1">
                                        <div class="avatar avatar-sm me-2">
                                            <img src="{{ $delivery->rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($delivery->rider->full_name) }}" 
                                                 alt="{{ $delivery->rider->full_name }}"
                                                 class="rounded-circle">
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-medium small">{{ $delivery->rider->full_name }}</div>
                                            <small class="text-muted">{{ $delivery->rider->phone_number }}</small>
                                        </div>
                                    </div>
                                    <div class="mt-1">
                                        <i class="bx bx-star text-warning"></i>
                                        <span class="small">{{ number_format($delivery->rider->rating, 1) }}</span>
                                    </div>
                                </div>
                                @else
                                <span class="badge bg-label-warning">
                                    <i class="bx bx-time me-1"></i>Assigning Rider...
                                </span>
                                @endif
                            </div>

                            <!-- Timeline -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-2">Items</small>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($delivery->items as $dItem)
                                        <li class="mb-1">
                                            <i class="bx bx-package text-primary me-1"></i>
                                            <span class="small">{{ $dItem->product_name }} ({{ $dItem->quantity }})</span>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-2">Timeline</small>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-1">
                                            <i class="bx bx-time text-muted me-1"></i>
                                            <small>Created: {{ $delivery->created_at->format('M d, h:i A') }}</small>
                                        </li>
                                        @if($delivery->assigned_at)
                                        <li class="mb-1">
                                            <i class="bx bx-check text-success me-1"></i>
                                            <small>Assigned: {{ $delivery->assigned_at->format('M d, h:i A') }}</small>
                                        </li>
                                        @endif
                                        @if($delivery->picked_up_at)
                                        <li class="mb-1">
                                            <i class="bx bx-check text-success me-1"></i>
                                            <small>Picked Up: {{ $delivery->picked_up_at->format('M d, h:i A') }}</small>
                                        </li>
                                        @endif
                                        @if($delivery->delivered_at)
                                        <li class="mb-1">
                                            <i class="bx bx-check-double text-success me-1"></i>
                                            <small>Delivered: {{ $delivery->delivered_at->format('M d, h:i A') }}</small>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>

                            <!-- Actions -->
                            @if($delivery->status === 'delivered' && $delivery->delivery_proof)
                            <div class="mt-3">
                                <a href="{{ asset('public/storage/' . $delivery->delivery_proof) }}" 
                                   target="_blank" 
                                   class="btn btn-sm btn-label-success">
                                    <i class="bx bx-image me-1"></i>View Delivery Proof
                                </a>
                            </div>
                            @endif

                            @if(in_array($delivery->status, ['assigned', 'en_route_pickup', 'picked_up', 'en_route_delivery']))
                            <div class="mt-3">
                                <div class="progress" style="height: 6px;">
                                    @php
                                        $progress = match($delivery->status) {
                                            'assigned' => 25,
                                            'en_route_pickup' => 40,
                                            'picked_up' => 60,
                                            'en_route_delivery' => 80,
                                            default => 0
                                        };
                                    @endphp
                                    <div class="progress-bar bg-primary" style="width: {{ $progress }}%"></div>
                                </div>
                                <small class="text-muted d-block mt-1">{{ $progress }}% Complete</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-package bx-lg text-muted mb-3"></i>
                    <h6 class="mb-2">No Delivery Created Yet</h6>
                    <p class="text-muted mb-0">Mark items as "Processing" then "Ready for Pickup" to create a delivery request.</p>
                </div>
            </div>
            @endif

            {{-- ── Bundle Status Panel ─────────────────────────────────────── --}}
            {{-- Shows when this seller is part of a multi-seller zone bundle   --}}
            @php
                $sellerZone    = Auth::guard('seller')->user()->seller->shop->delivery_zone ?? 'Not Included';
                $activeBundles = $order->deliveryBundles()
                    ->where('pickup_zone', $sellerZone)
                    ->whereIn('status', ['waiting', 'growing'])
                    ->get();
            @endphp

            @foreach($activeBundles as $bundle)
            @php
                $pct = $bundle->expected_count > 0
                    ? round(($bundle->ready_count / $bundle->expected_count) * 100)
                    : 0;
                $remaining = $bundle->expected_count - $bundle->ready_count;
            @endphp
            <div class="card mt-4 {{ $bundle->status === 'growing' ? 'border-success' : 'border-warning' }}">
                <div class="card-header {{ $bundle->status === 'growing' ? 'bg-success bg-opacity-10' : 'bg-warning bg-opacity-10' }} d-flex align-items-center justify-content-between">
                    <h6 class="mb-0">
                        <i class="bx {{ $bundle->status === 'growing' ? 'bx-broadcast text-success' : 'bx-group text-warning' }} me-2"></i>
                        Zone Bundle — {{ $bundle->pickup_zone }}
                        @if($bundle->status === 'growing')
                            <span class="badge bg-success ms-2">Live — Riders Notified</span>
                        @else
                            <span class="badge bg-label-warning ms-2">Waiting for First Seller</span>
                        @endif
                    </h6>
                    <small class="text-muted">Order {{ $order->order_number }}</small>
                </div>
                <div class="card-body">

                    {{-- Progress bar --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-muted">Sellers ready in your zone</small>
                            <small class="fw-medium">{{ $bundle->ready_count }} / {{ $bundle->expected_count }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $bundle->status === 'growing' ? 'bg-success' : 'bg-warning' }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    @if($bundle->status === 'growing')
                        <div class="alert alert-success py-2 mb-0">
                            <i class="bx bx-broadcast me-1"></i>
                            Riders are being notified now.
                            @if($remaining > 0)
                                <strong>{{ $remaining }}</strong> more seller(s) still preparing —
                                their stop(s) will be added to the live broadcast automatically.
                                If a rider accepts before they're ready, they'll get a separate delivery.
                            @else
                                All sellers in <strong>{{ $bundle->pickup_zone }}</strong> are ready.
                            @endif
                        </div>

                    @elseif($bundle->status === 'waiting')
                        <div class="alert alert-info py-2 mb-0">
                            <i class="bx bx-time me-1"></i>
                            Waiting for the first seller in <strong>{{ $bundle->pickup_zone }}</strong> to mark ready.
                            Riders will be notified as soon as any seller is ready.
                        </div>
                    @endif

                </div>
            </div>
            @endforeach

        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-receipt me-2"></i>Order Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Number:</span>
                        <span class="fw-medium">{{ $order->order_number }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Date:</span>
                        <span>{{ $order->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Method:</span>
                        <span>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Status:</span>
                        <span class="badge bg-{{ $order->payment_status_badge }}">
                            {{ ucfirst($order->payment_status) }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Status:</span>
                        <span class="badge bg-{{ $order->status_badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Your Total:</span>
                        <span class="fw-bold text-success fs-5">₦{{ number_format($sellerTotal, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-user me-2"></i>Customer Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Name</small>
                        <span class="fw-medium">{{ $order->customer_name }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email</small>
                        <a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <a href="tel:{{ $order->customer_phone }}">{{ $order->customer_phone }}</a>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Shipping Address</small>
                        <p class="mb-0">
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }}<br>
                            {{ $order->shipping_postal_code }}, {{ $order->shipping_country }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Order Notes -->
            @if($order->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-note me-2"></i>Order Notes
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $order->notes }}</p>
                </div>
            </div>
            @endif

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('seller.orders.invoice', $order) }}" 
                       class="btn btn-primary w-100 mb-2" 
                       target="_blank">
                        <i class="bx bx-printer me-1"></i>Print Invoice
                    </a>
                    <a href="mailto:{{ $order->customer_email }}" class="btn btn-label-secondary w-100">
                        <i class="bx bx-envelope me-1"></i>Contact Customer
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ============================================================ --}}
{{-- ALL MODALS — rendered outside the table / td elements       --}}
{{-- iOS Safari/Chrome clip modals that live inside table cells  --}}
{{-- because of overflow/stacking context bugs. Keep them here.  --}}
{{-- ============================================================ --}}
@foreach($order->items as $item)

    {{-- Status Update Modal --}}
    <div class="modal fade" id="statusModal{{ $item->id }}" tabindex="-1" aria-labelledby="statusModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="statusModalLabel{{ $item->id }}">
                        <i class="bx bx-edit me-2"></i>Update Item Status
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('seller.orders.update-status', $order) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $item->status)) }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="pending">Pending</option>
                                <option value="processing" selected>Processing</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <div class="form-text">
                                Mark as "Processing" when you start preparing the item
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save me-1"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Mark Ready Modal (only rendered when item is in processing state) --}}
    @if($item->status === 'processing')
    @php
        $sellerZoneModal   = Auth::guard('seller')->user()->seller->shop->delivery_zone ?? 'Not Included';
        $bundleForZoneModal = $order->deliveryBundles()
            ->where('pickup_zone', $sellerZoneModal)
            ->first();
        $bundleIsGrowingModal = $bundleForZoneModal
            && $bundleForZoneModal->status === 'growing'
            && $bundleForZoneModal->expected_count > 1;
        $bundleIsWaitingModal = $bundleForZoneModal
            && $bundleForZoneModal->status === 'waiting'
            && $bundleForZoneModal->expected_count > 1;
    @endphp
    <div class="modal fade" id="readyModal{{ $item->id }}" tabindex="-1" aria-labelledby="readyModalLabel{{ $item->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="readyModalLabel{{ $item->id }}">
                        <i class="bx bx-package me-2"></i>Mark Item Ready for Pickup
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('seller.orders.items.ready', $item) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info d-flex align-items-center mb-4">
                            <i class="bx bx-info-circle bx-lg me-3"></i>
                            <div>
                                <strong>What happens next:</strong> Riders are notified immediately when you mark ready. If other sellers in your zone are still preparing, riders will see your stop first and watch the job grow as others join — giving them a better multi-stop trip. If a rider accepts before all sellers are ready, remaining sellers get their own separate delivery. You'll be notified once a rider is assigned.
                            </div>
                        </div>

                        <!-- Item Details -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="mb-3">Item Details</h6>
                                <div class="row">
                                    <div class="col-sm-6 mb-2">
                                        <small class="text-muted d-block">Product</small>
                                        <strong>{{ $item->product_name }}</strong>
                                    </div>
                                    <div class="col-sm-6 mb-2">
                                        <small class="text-muted d-block">Quantity</small>
                                        <strong>{{ $item->quantity }}</strong>
                                    </div>
                                    <div class="col-sm-6">
                                        <small class="text-muted d-block">Value</small>
                                        <strong class="text-success">₦{{ number_format($item->total_price, 2) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Package Information -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Package Weight (kg) *</label>
                                <div class="input-group">
                                    <input type="number"
                                           name="package_weight"
                                           class="form-control"
                                           step="0.1"
                                           min="0.1"
                                           max="100"
                                           placeholder="e.g., 2.5"
                                           required>
                                    <span class="input-group-text">kg</span>
                                </div>
                                <small class="text-muted">Approximate weight of the packaged item</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Delivery Fee (Zone-Based)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₦</span>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ 
                                               \App\Services\DeliveryService::lookupFee(
                                                   Auth::guard('seller')->user()->seller->shop->delivery_zone ?? 'Not Included',
                                                   $order->shipping_zone ?? 'Not Included'
                                               ) 
                                               ? number_format(\App\Services\DeliveryService::lookupFee(
                                                   Auth::guard('seller')->user()->seller->shop->delivery_zone ?? 'Not Included',
                                                   $order->shipping_zone ?? 'Not Included'
                                               ), 2)
                                               : 'N/A'
                                           }}"
                                           disabled>
                                </div>
                                <small class="text-muted">
                                    From <strong>{{ Auth::guard('seller')->user()->seller->shop->delivery_zone ?? 'Not Included' }}</strong>
                                    → <strong>{{ $order->shipping_zone ?? 'N/A' }}</strong>
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Package Notes (Optional)</label>
                                <textarea name="package_notes"
                                          class="form-control"
                                          rows="3"
                                          maxlength="500"
                                          placeholder="e.g., 'Fragile - Handle with care' or 'Keep upright'"></textarea>
                                <small class="text-muted">Special handling instructions for the rider</small>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="confirmReady{{ $item->id }}"
                                           required>
                                    <label class="form-check-label" for="confirmReady{{ $item->id }}">
                                        I confirm the item is properly packaged and ready for pickup
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Expected Timeline -->
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6 class="mb-3">
                                <i class="bx bx-time-five me-2"></i>Expected Timeline
                            </h6>
                            @if($bundleIsGrowingModal)
                            <div class="alert alert-success py-2 mb-3">
                                <small>
                                    <i class="bx bx-broadcast me-1"></i>
                                    <strong>Riders are already being notified</strong> for your zone —
                                    {{ $bundleForZoneModal->ready_count }} of {{ $bundleForZoneModal->expected_count }} sellers ready.
                                    Your stop will be added to the live broadcast immediately.
                                </small>
                            </div>
                            @elseif($bundleIsWaitingModal)
                            <div class="alert alert-info py-2 mb-3">
                                <small>
                                    <i class="bx bx-broadcast me-1"></i>
                                    You'll be the first seller ready in your zone — riders will be notified
                                    right away and will see more stops as the other
                                    {{ $bundleForZoneModal->expected_count - 1 }} seller(s) prepare.
                                </small>
                            </div>
                            @endif
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <i class="bx bx-broadcast text-success me-2"></i>
                                    <strong>Rider Notification:</strong> Sent immediately when you mark ready
                                </li>
                                <li class="mb-2">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    <strong>Pickup:</strong> Within 30 minutes of a rider accepting
                                </li>
                                <li class="mb-0">
                                    <i class="bx bx-check-circle text-success me-2"></i>
                                    <strong>Delivery:</strong> Within 45–60 minutes after pickup
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                            <i class="bx bx-x me-1"></i>Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-check-circle me-1"></i>Mark Ready & Create Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@endforeach

@push('scripts')
<script>
// Initialise Bootstrap tooltips
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
</script>
@endpush
@endsection
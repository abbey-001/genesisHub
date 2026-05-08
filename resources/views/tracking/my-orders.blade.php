@extends('tracking.layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1">My Orders</h3>
                    <p class="text-muted mb-0">Track and manage your orders</p>
                </div>
                <a href="{{ route('track.index') }}" class="btn btn-outline-primary">
                    <i class="bx bx-search-alt me-1"></i>Track by Order Number
                </a>
            </div>

            @if($orders->count() > 0)
            <!-- Orders List -->
            <div class="row g-4">
                @foreach($orders as $order)
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1">Order #{{ $order->order_number }}</h5>
                                            <p class="text-muted mb-0">
                                                <i class="bx bx-calendar me-1"></i>
                                                {{ $order->created_at->format('M d, Y h:i A') }}
                                            </p>
                                        </div>
                                        <span class="badge bg-{{ $order->status_badge }} fs-6">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-6 col-sm-4">
                                            <small class="text-muted d-block">Items</small>
                                            <strong>{{ $order->items->count() }}</strong>
                                        </div>
                                        <div class="col-6 col-sm-4">
                                            <small class="text-muted d-block">Total Amount</small>
                                            <strong class="text-primary">₦{{ number_format($order->total, 2) }}</strong>
                                        </div>
                                        <div class="col-12 col-sm-4 mt-2 mt-sm-0">
                                            <small class="text-muted d-block">Payment</small>
                                            <span class="badge bg-{{ $order->payment_status_badge }}">
                                                {{ ucfirst($order->payment_status) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Deliveries Status -->
                                    @if($order->deliveries->count() > 0)
                                    <div>
                                        <small class="text-muted d-block mb-2">Delivery Status:</small>
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($order->deliveries as $delivery)
                                            <span class="badge bg-{{ $delivery->status_badge }}">
                                                <i class="bx bx-package me-1"></i>
                                                Delivery #{{ $delivery->id }}: {{ $delivery->status_label }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                                    @if($order->status === 'delivered')
                                        <i class="bx bx-check-circle text-success mb-2" style="font-size: 48px;"></i>
                                        <div class="text-success fw-medium">Delivered</div>
                                    @elseif($order->status === 'shipped')
                                        <i class="bx bx-package text-info mb-2" style="font-size: 48px;"></i>
                                        <div class="text-info fw-medium">In Transit</div>
                                    @else
                                        <i class="bx bx-time text-warning mb-2" style="font-size: 48px;"></i>
                                        <div class="text-warning fw-medium">Processing</div>
                                    @endif

                                    <a href="{{ route('orders.track', $order) }}" class="btn btn-primary w-100 mt-3">
                                        <i class="bx bx-navigation me-1"></i>Track Order
                                    </a>
                                </div>
                            </div>

                            <!-- Items Preview -->
                            <hr>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <small class="text-muted">Items:</small>
                                @foreach($order->items->take(3) as $item)
                                <div class="d-flex align-items-center gap-1">
                                    @if($item->product && $item->product->main_image)
                                    <img src="{{ asset('storage/' . $item->product->main_image) }}" 
                                         alt="{{ $item->product_name }}"
                                         class="rounded"
                                         style="width: 30px; height: 30px; object-fit: cover;">
                                    @endif
                                    <small>{{ Str::limit($item->product_name, 20) }}</small>
                                </div>
                                @endforeach
                                @if($order->items->count() > 3)
                                <small class="text-muted">+{{ $order->items->count() - 3 }} more</small>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $orders->links() }}
            </div>

            @else
            <!-- Empty State -->
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-shopping-bag bx-lg text-muted mb-3" style="font-size: 64px;"></i>
                    <h5 class="mb-2">No Orders Yet</h5>
                    <p class="text-muted mb-4">Start shopping to see your orders here</p>
                    <a href="/" class="btn btn-primary">
                        <i class="bx bx-store me-1"></i>Start Shopping
                    </a>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
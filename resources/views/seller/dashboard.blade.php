@extends('seller.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="mb-4">
            <h4 class="mb-0">Welcome back, {{ auth('seller')->user()->name }}!</h4>
            <p class="text-muted">Here's what's happening with your store today.</p>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title">Total Revenue</p>
                        <h4 class="fw-bold text-primary mb-0">₦{{ number_format($revenue['total'], 2) }}</h4>
                        <small class="text-muted">Today: ₦{{ number_format($revenue['today'], 2) }}</small>
                    </div>
                    <div>
                        <i data-lucide="wallet" class="fs-32 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title">Total Orders</p>
                        <h4 class="fw-bold mb-0">{{ $stats['total_orders'] }}</h4>
                        <small class="text-warning">{{ $stats['pending_orders'] }} Pending</small>
                    </div>
                    <div>
                        <i data-lucide="shopping-cart" class="fs-32 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title">Total Products</p>
                        <h4 class="fw-bold mb-0">{{ $stats['total_products'] }}</h4>
                        <small class="text-success">{{ $stats['active_products'] }} Active</small>
                    </div>
                    <div>
                        <i data-lucide="package" class="fs-32 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title">This Month</p>
                        <h4 class="fw-bold text-primary mb-0">₦{{ number_format($revenue['this_month'], 2) }}</h4>
                        <small class="text-muted">Revenue</small>
                    </div>
                    <div>
                        <i data-lucide="trending-up" class="fs-32 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Top Products -->
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Recent Orders</h4>
                <a href="{{ route('seller.orders.index') }}" class="btn btn-sm btn-link text-uppercase">
                    View All <i data-lucide="arrow-right"></i>
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('seller.orders.show', $order) }}" class="fw-medium">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->customer_name }}</td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>₦{{ number_format($order->items->sum('total_price'), 2) }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('seller.orders.show', $order) }}" 
                                       class="btn btn-sm btn-soft-primary">
                                        <i data-lucide="eye" class="fs-16"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">No orders yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Top Selling Products</h4>
            </div>
            <div style="height: 400px;" data-simplebar>
                @forelse($topProducts as $product)
                <div class="d-flex gap-3 border-bottom p-3">
                    <img src="{{ asset('public/storage/'.$product->main_image) }}" 
                         alt="{{ $product->name }}" 
                         class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ Str::limit($product->name, 40) }}</h6>
                        <p class="mb-1 text-muted">Sold: {{ $product->sold_count }}</p>
                        <p class="mb-0 fw-semibold text-primary">₦{{ number_format($product->price, 2) }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No products yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Low Stock Alert & Recent Reviews -->
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Low Stock Alert</h4>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Stock</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockProducts as $product)
                            <tr>
                                <td>{{ Str::limit($product->name, 40) }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $product->stock <= 5 ? 'danger' : 'warning' }}">
                                        {{ $product->stock }} left
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('seller.products.edit', $product) }}" 
                                       class="btn btn-sm btn-soft-primary">
                                        Update Stock
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-4">All products well stocked!</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">Recent Reviews</h4>
                <a href="{{ route('seller.reviews.index') }}" class="btn btn-sm btn-link text-uppercase">
                    View All <i data-lucide="arrow-right"></i>
                </a>
            </div>
            <div style="height: 300px;" data-simplebar>
                @forelse($recentReviews as $review)
                <div class="border-bottom p-3">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="mb-0">{{ $review->user->name }}</h6>
                        <div>
                            @for($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="fs-12 {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <p class="text-muted mb-1 small">{{ Str::limit($review->product->name, 40) }}</p>
                    <p class="mb-0">{{ Str::limit($review->comment, 100) }}</p>
                </div>
                @empty
                <div class="text-center py-4">
                    <p class="text-muted mb-0">No reviews yet</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endpush
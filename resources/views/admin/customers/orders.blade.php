@extends('admin.layouts.app')

@section('title', 'Customer Orders')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title">Orders — {{ $customer->name }}</h4>
                    <p class="text-muted mb-0">{{ $customer->email }}</p>
                </div>
                <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Customer
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Orders ({{ $orders->total() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Subtotal</th>
                                <th>Shipping</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>
                                    {{-- Link directly to the order detail page --}}
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-medium text-primary">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    {{ $order->created_at->format('d M, Y') }}<br>
                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $order->items->count() }} items</span>
                                </td>
                                <td>₦{{ number_format($order->subtotal, 2) }}</td>
                                <td>₦{{ number_format($order->shipping_fee, 2) }}</td>
                                <td class="fw-bold">₦{{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status_badge }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        {{-- Expand inline items preview --}}
                                        <button class="btn btn-sm btn-light"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#order{{ $order->id }}"
                                                title="Preview items">
                                            <i data-lucide="list"></i>
                                        </button>
                                        {{-- Navigate to full order detail --}}
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="btn btn-sm btn-light" title="View full order">
                                            <i data-lucide="external-link"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            {{-- Inline items preview (collapsible) --}}
                            <tr class="collapse" id="order{{ $order->id }}">
                                <td colspan="9" class="bg-light">
                                    <div class="p-3">
                                        <h6 class="mb-3">Order Items</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>SKU</th>
                                                        <th>Qty</th>
                                                        <th>Unit Price</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($order->items as $item)
                                                    <tr>
                                                        <td>{{ $item->product_name }}</td>
                                                        <td class="text-muted">{{ $item->product_sku ?? '—' }}</td>
                                                        <td>{{ $item->quantity }}</td>
                                                        <td>₦{{ number_format($item->price, 2) }}</td>
                                                        <td class="fw-bold">₦{{ number_format($item->total_price, 2) }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="4" class="text-end fw-bold">Order Total:</td>
                                                        <td class="fw-bold text-primary">₦{{ number_format($order->total, 2) }}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        @if($order->shipping_address)
                                        <div class="mt-3">
                                            <h6>Shipping Address</h6>
                                            <p class="mb-0 text-muted">
                                                {{ $order->shipping_address }}<br>
                                                {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif
                                                @if($order->shipping_postal_code) {{ $order->shipping_postal_code }}@endif
                                            </p>
                                        </div>
                                        @endif

                                        @if($order->notes)
                                        <div class="mt-2">
                                            <h6>Notes</h6>
                                            <p class="mb-0 text-muted">{{ $order->notes }}</p>
                                        </div>
                                        @endif

                                        <div class="mt-3">
                                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-primary">
                                                <i data-lucide="external-link" class="me-1"></i>View Full Order
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i data-lucide="shopping-cart" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mt-2 mb-0">No orders found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($orders->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }}
                    </div>
                    {{ $orders->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
{{-- Order Detail View --}}
@extends('seller.layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">Order #{{ $order->order_number }}</h4>
                        <p class="text-muted mb-0">Placed on {{ $order->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <a href="{{ route('seller.orders.invoice', $order) }}" class="btn btn-primary" target="_blank">
                            <i data-lucide="printer" class="fs-16"></i> Print Invoice
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Customer Information -->
                    <div class="col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="fw-bold mb-3">Customer Information</h6>
                            <p class="mb-1"><strong>Name:</strong> {{ $order->customer_name }}</p>
                            <p class="mb-1"><strong>Email:</strong> {{ $order->customer_email }}</p>
                            <p class="mb-0"><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="fw-bold mb-3">Shipping Address</h6>
                            <p class="mb-1">{{ $order->shipping_address }}</p>
                            <p class="mb-0">{{ $order->shipping_city }}, {{ $order->shipping_state }}</p>
                            <p class="mb-0">{{ $order->shipping_postal_code }}, {{ $order->shipping_country }}</p>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="col-md-4">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="fw-bold mb-3">Order Summary</h6>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Payment Status:</span>
                                <span class="badge badge-soft-{{ $order->payment_status_badge }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Order Status:</span>
                                <span class="badge badge-soft-{{ $order->status_badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span>Payment Method:</span>
                                <span>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="table-responsive mt-3">
                    <h6 class="fw-bold mb-3">Your Items in this Order</h6>
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product)
                                            <img src="{{ asset($item->product->main_image) }}" 
                                                 alt="{{ $item->product_name }}" 
                                                 class="rounded me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <div class="fw-medium">{{ $item->product_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $item->product_sku ?? 'N/A' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₦{{ number_format($item->price, 2) }}</td>
                                <td class="fw-bold">₦{{ number_format($item->total, 2) }}</td>
                                <td>
                                    <span class="badge badge-soft-{{ $item->status == 'pending' ? 'warning' : ($item->status == 'delivered' ? 'success' : 'info') }}">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" data-bs-target="#statusModal{{ $item->id }}">
                                        Update Status
                                    </button>

                                    <!-- Status Update Modal -->
                                    <div class="modal fade" id="statusModal{{ $item->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Item Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('seller.orders.update-status', $order) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="item_id" value="{{ $item->id }}">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Select Status</label>
                                                            <select name="status" class="form-select" required>
                                                                <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                <option value="processing" {{ $item->status == 'processing' ? 'selected' : '' }}>Processing</option>
                                                                <option value="shipped" {{ $item->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                                                <option value="delivered" {{ $item->status == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                                <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Your Total:</th>
                                <th colspan="3" class="text-primary">₦{{ number_format($sellerTotal, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($order->notes)
                <div class="mt-3">
                    <h6 class="fw-bold">Order Notes</h6>
                    <p class="text-muted">{{ $order->notes }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
lucide.createIcons();
</script>
@endpush

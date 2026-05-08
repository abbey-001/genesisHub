@extends('admin.layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back
                    </a>
                    <a href="{{ route('admin.orders.invoice', $order) }}" class="btn btn-primary" target="_blank">
                        <i data-lucide="printer" class="me-1"></i>Print Invoice
                    </a>
                </div>
            </div>
            <h4 class="page-title">Order #{{ $order->order_number }}</h4>
            <p class="text-muted">Placed on {{ $order->created_at->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i data-lucide="alert-circle" class="me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Order Status Cards -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Order Status</h6>
                <span class="badge bg-{{ $order->status_badge }} fs-14">{{ ucfirst($order->status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Payment Status</h6>
                <span class="badge bg-{{ $order->payment_status_badge }} fs-14">{{ ucfirst($order->payment_status) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Total Amount</h6>
                <h4 class="mb-0 text-primary">₦{{ number_format($order->total, 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h6 class="text-muted mb-2">Platform Commission</h6>
                <h4 class="mb-0 text-success">₦{{ number_format($platformCommission, 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Order Items -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Items ({{ $order->items->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Shop</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product)
                                        <img src="{{ asset('storage/' . $item->product->main_image) }}"
                                             alt="{{ $item->product_name }}"
                                             class="rounded me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;"
                                             onerror="this.src='{{ asset('img/default-product.jpg') }}'">
                                        @endif
                                        <div>
                                            <h6 class="mb-0">{{ $item->product_name }}</h6>
                                            <small class="text-muted">SKU: {{ $item->product_sku ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {{-- seller on OrderItem returns a Seller model; access shop_name via the shop relation --}}
                                    <small>{{ $item->seller->shop->shop_name ?? ($item->seller->user->name ?? 'N/A') }}</small>
                                </td>
                                <td>₦{{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="fw-bold">₦{{ number_format($item->total_price, 2) }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($item->status ?? 'pending') }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="4" class="text-end">Subtotal:</th>
                                <th colspan="2">₦{{ number_format($order->subtotal, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Tax:</th>
                                <th colspan="2">₦{{ number_format($order->tax, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="4" class="text-end">Shipping:</th>
                                <th colspan="2">₦{{ number_format($order->shipping_fee, 2) }}</th>
                            </tr>
                            @if($order->discount > 0)
                            <tr>
                                <th colspan="4" class="text-end">Discount:</th>
                                <th colspan="2" class="text-danger">-₦{{ number_format($order->discount, 2) }}</th>
                            </tr>
                            @endif
                            <tr>
                                <th colspan="4" class="text-end">Total:</th>
                                <th colspan="2" class="text-success fs-5">₦{{ number_format($order->total, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Deliveries -->
        @if($order->deliveries->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Delivery Information</h5>
            </div>
            <div class="card-body">
                @foreach($order->deliveries as $delivery)
                <div class="card border mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h6 class="mb-1">Delivery #{{ $delivery->id }}</h6>
                                <span class="badge bg-{{ $delivery->status_badge }}">{{ $delivery->status_label }}</span>
                            </div>
                            @if($delivery->rider)
                            <div class="text-end">
                                <div class="fw-medium">{{ $delivery->rider->full_name }}</div>
                                <small class="text-muted">{{ $delivery->rider->phone_number }}</small>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Pickup</small>
                                <p class="mb-0">{{ $delivery->pickup_address }}</p>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Delivery</small>
                                <p class="mb-0">{{ $delivery->delivery_address }}</p>
                            </div>
                        </div>
                        @if($delivery->delivery_fee)
                        <div class="mt-2">
                            <small class="text-muted">Delivery Fee: </small>
                            <strong>₦{{ number_format($delivery->delivery_fee, 2) }}</strong>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Order Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline-alt pb-0">
                    @foreach($timeline as $event)
                    <div class="timeline-item">
                        <i data-lucide="{{ $event['icon'] }}" class="text-{{ $event['color'] }} timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">{{ $event['label'] }}</h6>
                            <small class="text-muted">{{ $event['timestamp']->format('F d, Y h:i A') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Customer Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                @if($order->user)
                <div class="mb-2">
                    <a href="{{ route('admin.customers.show', $order->user) }}" class="btn btn-sm btn-outline-primary w-100">
                        <i data-lucide="user" class="me-1"></i>View Customer Profile
                    </a>
                </div>
                @endif
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
                        @if($order->shipping_postal_code) {{ $order->shipping_postal_code }}<br> @endif
                        {{ $order->shipping_country }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Payment Method</small>
                    <span class="fw-medium">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Payment Status</small>
                    <span class="badge bg-{{ $order->payment_status_badge }}">{{ ucfirst($order->payment_status) }}</span>
                </div>
                @if($order->paid_at)
                <div class="mb-3">
                    <small class="text-muted d-block">Paid At</small>
                    <span class="fw-medium">{{ $order->paid_at->format('d M Y, h:i A') }}</span>
                </div>
                @endif
                @if($order->payment_reference)
                <div class="mb-0">
                    <small class="text-muted d-block">Reference</small>
                    <span class="fw-medium font-monospace">{{ $order->payment_reference }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <button type="button" class="btn btn-info w-100"
                        data-bs-toggle="modal" data-bs-target="#updateStatusModal">
                    <i data-lucide="edit" class="me-1"></i>Update Status
                </button>

                <button type="button" class="btn btn-warning w-100"
                        data-bs-toggle="modal" data-bs-target="#updatePaymentModal">
                    <i data-lucide="credit-card" class="me-1"></i>Update Payment
                </button>

                {{-- Refund: link to the dedicated refund page — no inline modal needed --}}
                @if($order->payment_status === 'paid')
                <a href="{{ route('admin.finance.refunds.show', $order) }}" class="btn btn-secondary w-100">
                    <i data-lucide="rotate-ccw" class="me-1"></i>Process Refund
                </a>
                @endif

                @if($order->canBeCancelled())
                <button type="button" class="btn btn-danger w-100"
                        data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i data-lucide="x-circle" class="me-1"></i>Cancel Order
                </button>
                @endif
            </div>
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Order Notes</h5></div>
            <div class="card-body">
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════ --}}

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select" required>
                            <option value="pending"    {{ $order->status === 'pending'    ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped"    {{ $order->status === 'shipped'    ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered"  {{ $order->status === 'delivered'  ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled"  {{ $order->status === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add notes about this status change..."></textarea>
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

<!-- Update Payment Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Payment Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.orders.update-payment', $order) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        Changing payment status manually will not trigger any payment gateway actions.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Status *</label>
                        <select name="payment_status" class="form-select" required>
                            <option value="pending"  {{ $order->payment_status === 'pending'  ? 'selected' : '' }}>Pending</option>
                            <option value="paid"     {{ $order->payment_status === 'paid'     ? 'selected' : '' }}>Paid</option>
                            <option value="failed"   {{ $order->payment_status === 'failed'   ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ $order->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Reason for manual payment status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Update Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Modal -->
@if($order->canBeCancelled())
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Cancel Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        This will cancel order <strong>{{ $order->order_number }}</strong> and restore product stock.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Cancellation *</label>
                        <textarea name="reason" class="form-control" rows="3" required
                                  placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">
                        <i data-lucide="x-circle" class="me-1"></i>Cancel Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
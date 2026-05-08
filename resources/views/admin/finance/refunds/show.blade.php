@extends('admin.layouts.app')

@section('title', 'Refund Request — Order #' . $order->order_number)

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.finance.refunds.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Refunds
                </a>
            </div>
            <h4 class="page-title">Refund Request</h4>
            <p class="text-muted mb-0">Order #{{ $order->order_number }}</p>
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

<div class="row">

    {{-- ── Left column: order detail ── --}}
    <div class="col-lg-8">

        {{-- Status Hero --}}
        <div class="card mb-4
            @if($order->payment_status === 'refund_pending') border-warning
            @elseif($order->payment_status === 'refunded')   border-success
            @else border-danger @endif">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h2 class="mb-1">₦{{ number_format($order->total, 2) }}</h2>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-secondary">Order #{{ $order->order_number }}</span>
                            @if($order->payment_status === 'refund_pending')
                                <span class="badge bg-warning text-dark">Refund Pending</span>
                            @elseif($order->payment_status === 'refunded')
                                <span class="badge bg-success">Refunded</span>
                            @elseif($order->payment_status === 'refund_rejected')
                                <span class="badge bg-danger">Refund Rejected</span>
                            @endif
                        </div>
                        <p class="text-muted mt-2 mb-0 small">
                            Cancelled {{ $order->cancelled_at?->format('d M Y, h:i A') }}
                        </p>
                    </div>
                    <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-column gap-2 align-items-md-end">
                        @if($order->payment_status === 'refund_pending')
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#processModal">
                                <i data-lucide="rotate-ccw" class="me-1"></i>Process Refund
                            </button>
                            <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i data-lucide="x-circle" class="me-1"></i>Reject Refund
                            </button>
                        @elseif($order->payment_status === 'refunded')
                            <div class="alert alert-success py-2 mb-0">
                                <i data-lucide="check-circle" class="me-1"></i>
                                Refund of <strong>₦{{ number_format($order->refund_amount ?? $order->total, 2) }}</strong> processed
                            </div>
                        @elseif($order->payment_status === 'refund_rejected')
                            <div class="alert alert-danger py-2 mb-0">
                                <i data-lucide="x-circle" class="me-1"></i>
                                Refund request was rejected
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer Information --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Customer Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Name</small>
                        <strong>{{ $order->customer_name }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $order->customer_email }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ $order->customer_phone ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Payment Method</small>
                        <strong>{{ ucfirst($order->payment_method ?? 'N/A') }}</strong>
                    </div>
                    @if($order->payment_reference)
                    <div class="col-12">
                        <small class="text-muted d-block">Paystack Reference</small>
                        <code>{{ $order->payment_reference }}</code>
                    </div>
                    @endif
                </div>
                @if($order->user)
                <div class="mt-2">
                    <a href="{{ route('admin.customers.show', $order->user) }}"
                       class="btn btn-sm btn-outline-primary">
                        <i data-lucide="user" class="me-1"></i>View Customer Profile
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Order Items --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Order Items</h5>
                <small class="text-muted">{{ $order->items->count() }} item{{ $order->items->count() != 1 ? 's' : '' }}</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Seller</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <div class="fw-medium">{{ $item->product_name }}</div>
                                @if($item->product_sku)
                                <small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $item->seller?->shop?->shop_name ?? 'N/A' }}
                                @if($item->seller?->wallet)
                                <br><small class="text-muted">
                                    Wallet balance: ₦{{ number_format($item->seller->wallet->balance, 2) }}
                                </small>
                                @endif
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">₦{{ number_format($item->price, 2) }}</td>
                            <td class="text-end fw-bold">₦{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="4" class="text-end">Subtotal</td>
                            <td class="text-end fw-bold">₦{{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        @if($order->shipping_fee > 0)
                        <tr>
                            <td colspan="4" class="text-end text-muted">Shipping</td>
                            <td class="text-end">₦{{ number_format($order->shipping_fee, 2) }}</td>
                        </tr>
                        @endif
                        @if($order->discount > 0)
                        <tr>
                            <td colspan="4" class="text-end text-muted">Discount</td>
                            <td class="text-end text-success">−₦{{ number_format($order->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="fw-bold">
                            <td colspan="4" class="text-end">Order Total</td>
                            <td class="text-end text-success fs-5">₦{{ number_format($order->total, 2) }}</td>
                        </tr>
                        @if($order->refund_amount)
                        <tr class="table-info">
                            <td colspan="4" class="text-end text-info fw-bold">Refund Amount</td>
                            <td class="text-end text-info fw-bold">₦{{ number_format($order->refund_amount, 2) }}</td>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Seller Wallet Impact --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Seller Wallet Impact</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    When the refund is processed, each seller's wallet will be debited
                    proportionally to their share of the order subtotal.
                </p>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Seller / Shop</th>
                                <th class="text-end">Items Total</th>
                                <th class="text-end">% of Order</th>
                                <th class="text-end">Est. Deduction</th>
                                <th class="text-end">Current Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items->groupBy('seller_id') as $sellerId => $sellerItems)
                            @php
                                $sellerTotal   = $sellerItems->sum('total_price');
                                $pct           = $order->subtotal > 0
                                    ? round(($sellerTotal / $order->subtotal) * 100, 1)
                                    : 0;
                                $estDeduction  = $order->subtotal > 0
                                    ? round(($sellerTotal / $order->subtotal) * $order->total, 2)
                                    : 0;
                                $seller        = $sellerItems->first()->seller;
                                $walletBalance = $seller?->wallet?->balance ?? 0;
                                $wouldOverdraft= $walletBalance < $estDeduction;
                            @endphp
                            <tr class="{{ $wouldOverdraft && $order->payment_status === 'refund_pending' ? 'table-warning' : '' }}">
                                <td>
                                    {{ $seller?->shop?->shop_name ?? 'Unknown Shop' }}<br>
                                    <small class="text-muted">{{ $seller?->user?->name ?? 'N/A' }}</small>
                                </td>
                                <td class="text-end">₦{{ number_format($sellerTotal, 2) }}</td>
                                <td class="text-end">{{ $pct }}%</td>
                                <td class="text-end">₦{{ number_format($estDeduction, 2) }}</td>
                                <td class="text-end {{ $wouldOverdraft ? 'text-warning fw-bold' : '' }}">
                                    ₦{{ number_format($walletBalance, 2) }}
                                    @if($wouldOverdraft && $order->payment_status === 'refund_pending')
                                    <br><small class="text-warning">
                                        <i data-lucide="alert-triangle" style="width:12px;height:12px;"></i>
                                        Insufficient — overdraft will be created
                                    </small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>{{-- /col-lg-8 --}}

    {{-- ── Right column: summary & timeline ── --}}
    <div class="col-lg-4">

        {{-- Refund Summary --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Refund Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Order Total:</td>
                        <td class="fw-bold text-end">₦{{ number_format($order->total, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Paid At:</td>
                        <td class="text-end">{{ $order->paid_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cancelled At:</td>
                        <td class="text-end">{{ $order->cancelled_at?->format('d M Y, h:i A') ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Ref:</td>
                        <td class="text-end">
                            <code class="small">{{ $order->payment_reference ? Str::limit($order->payment_reference, 20) : 'N/A' }}</code>
                        </td>
                    </tr>
                    @if($order->refund_amount)
                    <tr class="table-success">
                        <td class="fw-bold">Refunded:</td>
                        <td class="fw-bold text-end text-success">₦{{ number_format($order->refund_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if(isset($order->refunded_at))
                    <tr>
                        <td class="text-muted">Refunded At:</td>
                        <td class="text-end">{{ $order->refunded_at->format('d M Y, h:i A') }}</td>
                    </tr>
                    @endif
                </table>

                @if($order->payment_status === 'refund_pending')
                <hr>
                <div class="alert alert-info py-2 mb-0 small">
                    <i data-lucide="info" class="me-1"></i>
                    Processing the refund will call the Paystack API and deduct
                    each seller's wallet proportionally.
                </div>
                @endif
            </div>
        </div>

        {{-- Shipping Address --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Shipping Address</h5>
            </div>
            <div class="card-body">
                <address class="mb-0">
                    <strong>{{ $order->customer_name }}</strong><br>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}@if($order->shipping_state), {{ $order->shipping_state }}@endif<br>
                    {{ $order->shipping_country }}
                </address>
            </div>
        </div>

        {{-- Notes --}}
        @if($order->notes)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Order Notes</h5>
            </div>
            <div class="card-body">
                <p class="mb-0 small text-muted" style="white-space:pre-line;">{{ $order->notes }}</p>
            </div>
        </div>
        @endif

        {{-- Quick Links --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Links</h5>
            </div>
            <div class="card-body d-flex flex-column gap-2">
                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                    <i data-lucide="eye" class="me-1"></i>View Full Order
                </a>
                @if($order->user)
                <a href="{{ route('admin.customers.show', $order->user) }}" class="btn btn-outline-secondary btn-sm">
                    <i data-lucide="user" class="me-1"></i>View Customer
                </a>
                @endif
            </div>
        </div>

    </div>{{-- /col-lg-4 --}}
</div>{{-- /row --}}

{{-- ══════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════ --}}

{{-- Process Refund Modal --}}
@if($order->payment_status === 'refund_pending')
<div class="modal fade" id="processModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.refunds.process', $order) }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="alert alert-info small">
                        <strong>Order:</strong> #{{ $order->order_number }}<br>
                        <strong>Customer:</strong> {{ $order->customer_name }}
                            ({{ $order->customer_email }})<br>
                        <strong>Order Total:</strong> ₦{{ number_format($order->total, 2) }}<br>
                        @if($order->payment_method === 'paystack' && $order->payment_reference)
                        <strong>Paystack Ref:</strong> <code>{{ $order->payment_reference }}</code>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Refund Amount *
                            <span class="text-muted fw-normal">(max ₦{{ number_format($order->total, 2) }})</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number"
                                   name="refund_amount"
                                   class="form-control"
                                   value="{{ $order->total }}"
                                   min="0.01"
                                   max="{{ $order->total }}"
                                   step="0.01"
                                   required>
                        </div>
                        <div class="form-text">
                            Leave as-is for a full refund, or reduce for a partial refund.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Admin Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Internal notes about this refund..."></textarea>
                    </div>

                    @if($order->payment_method === 'paystack')
                    <div class="alert alert-warning small py-2">
                        <i data-lucide="alert-triangle" class="me-1"></i>
                        This will call the <strong>Paystack Refund API</strong> and
                        <strong>deduct seller wallets</strong> proportionally.
                        This action cannot be undone.
                    </div>
                    @else
                    <div class="alert alert-secondary small py-2">
                        <i data-lucide="info" class="me-1"></i>
                        The refund will be recorded internally (no Paystack call — payment
                        method was {{ ucfirst($order->payment_method ?? 'N/A') }}).
                    </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i data-lucide="rotate-ccw" class="me-1"></i>Confirm & Process Refund
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Refund Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Refund Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.refunds.reject', $order) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-1"></i>
                        Rejecting this request will mark the refund as declined.
                        The order will remain cancelled and <strong>no money will be returned</strong>.
                        The customer will not be automatically notified.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rejection Reason *</label>
                        <textarea name="reason" class="form-control" rows="4" required
                                  placeholder="Explain why this refund is being rejected..."></textarea>
                        <div class="form-text">This is recorded internally for audit purposes.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i data-lucide="x-circle" class="me-1"></i>Reject Refund Request
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
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
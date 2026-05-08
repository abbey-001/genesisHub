<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->order_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('seller/assets/css/vendor.min.css') }}" rel="stylesheet">
    <link href="{{ asset('seller/assets/css/app.min.css') }}" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .card { box-shadow: none !important; border: 1px solid #dee2e6; }
        }
        body { background: #f8f9fa; }
        .invoice-container { max-width: 900px; margin: 2rem auto; }
        .invoice-header { border-bottom: 3px solid #0d6efd; padding-bottom: 1rem; margin-bottom: 2rem; }
        .invoice-info { background: #f8f9fa; padding: 1rem; border-radius: 0.375rem; }
        .table-invoice { font-size: 0.875rem; }
        .invoice-total { background: #0d6efd; color: white; padding: 1rem; border-radius: 0.375rem; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="card">
            <div class="card-body p-5">
                <!-- Header -->
                <div class="invoice-header row">
                    <div class="col-md-6">
                        <h2 class="mb-0 text-primary">INVOICE</h2>
                        <p class="text-muted mb-0">Order #{{ $order->order_number }}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="mb-0">{{ config('app.name') }}</h3>
                        <p class="text-muted mb-0">
                            Platform Invoice<br>
                            Abuja, Nigeria<br>
                            support@genesishub.com
                        </p>
                    </div>
                </div>

                <!-- Invoice Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="invoice-info">
                            <h6 class="mb-3 text-uppercase">Bill To:</h6>
                            <p class="mb-1"><strong>{{ $order->customer_name }}</strong></p>
                            <p class="mb-1">{{ $order->customer_email }}</p>
                            <p class="mb-1">{{ $order->customer_phone }}</p>
                            <p class="mb-0">
                                {{ $order->shipping_address }}<br>
                                {{ $order->shipping_city }}, {{ $order->shipping_state }}<br>
                                @if($order->shipping_postal_code) {{ $order->shipping_postal_code }}<br> @endif
                                {{ $order->shipping_country }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="invoice-info">
                            <h6 class="mb-3 text-uppercase">Invoice Details:</h6>
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted">Invoice Number:</td>
                                    <td class="text-end"><strong>{{ $order->order_number }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Invoice Date:</td>
                                    <td class="text-end">{{ $order->created_at->format('F d, Y') }}</td>
                                </tr>
                                @if($order->paid_at)
                                <tr>
                                    <td class="text-muted">Paid On:</td>
                                    <td class="text-end">{{ $order->paid_at->format('F d, Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Payment Method:</td>
                                    <td class="text-end">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Payment Status:</td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $order->payment_status_badge }}">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($order->payment_reference)
                                <tr>
                                    <td class="text-muted">Reference:</td>
                                    <td class="text-end font-monospace small">{{ $order->payment_reference }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-invoice table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Product Description</th>
                                <th>Shop</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-medium">{{ $item->product_name }}</div>
                                    @if($item->product_sku)
                                    <small class="text-muted">SKU: {{ $item->product_sku }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{-- seller returns a Seller model; shop_name lives on the shop relation --}}
                                    <small class="text-muted">
                                        {{ $item->seller->shop->shop_name ?? ($item->seller->user->name ?? 'N/A') }}
                                    </small>
                                </td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-end">₦{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-medium">₦{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Subtotal:</td>
                                <td class="text-end">₦{{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            @if($order->tax > 0)
                            <tr>
                                <td class="text-muted">
                                    Tax
                                    @if($order->subtotal > 0)
                                        ({{ number_format(($order->tax / $order->subtotal) * 100, 1) }}%)
                                    @endif
                                    :
                                </td>
                                <td class="text-end">₦{{ number_format($order->tax, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Shipping Fee:</td>
                                <td class="text-end">₦{{ number_format($order->shipping_fee, 2) }}</td>
                            </tr>
                            @if($order->discount > 0)
                            <tr>
                                <td class="text-muted">Discount:</td>
                                <td class="text-end text-danger">-₦{{ number_format($order->discount, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="2"><hr class="my-2"></td>
                            </tr>
                        </table>
                        <div class="invoice-total text-center">
                            <h5 class="mb-0">TOTAL AMOUNT</h5>
                            <h2 class="mb-0 mt-2">₦{{ number_format($order->total, 2) }}</h2>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                @if($order->notes)
                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="mb-2">Order Notes:</h6>
                    <p class="mb-0 text-muted">{{ $order->notes }}</p>
                </div>
                @endif

                <!-- Footer -->
                <div class="mt-5 pt-4 border-top text-center text-muted">
                    <p class="mb-1"><strong>Thank you for your business!</strong></p>
                    <p class="mb-0 small">
                        This is a computer-generated invoice and does not require a signature.<br>
                        For any queries, please contact us at support@genesishub.com
                    </p>
                </div>

                <!-- Print Button -->
                <div class="mt-4 text-center no-print">
                    <button onclick="window.print()" class="btn btn-primary btn-lg">
                        <i class="bi bi-printer me-2"></i>Print Invoice
                    </button>
                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-lg">
                        <i class="bi bi-arrow-left me-2"></i>Back to Order
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
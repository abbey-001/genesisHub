{{-- Invoice View (invoice.blade.php) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Invoice #{{ $order->order_number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="{{ asset('public/seller/assets/css/vendor.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/seller/assets/css/app.min.css') }}" rel="stylesheet" />
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <div class="card">
            <div class="card-body">
                <!-- Logo & Title -->
                <div class="clearfix mb-4">
                    <div class="float-end">
                        <h5 class="fw-bold">{{ $seller->shop->shop_name ?? 'Your Shop Name' }}</h5>
                        <address>
                            {{ $seller->address }}<br>
                            {{ $seller->city }}, {{ $seller->state }}<br>
                            {{ $seller->postal_code }}, {{ $seller->country }}<br>
                            <abbr title="Phone">P:</abbr> {{ $seller->phone_number }}
                        </address>
                    </div>
                    <div class="float-start">
                        <h5 class="card-title mb-2">Invoice: #{{ $order->order_number }}</h5>
                        <p>{{ $order->created_at->format('d M, Y') }}</p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6 class="fw-normal text-muted">Customer</h6>
                        <h6 class="fs-14 fw-bold">{{ $order->customer_name }}</h6>
                        <address>
                            {{ $order->shipping_address }}<br>
                            {{ $order->shipping_city }}, {{ $order->shipping_state }}<br>
                            {{ $order->shipping_postal_code }}<br>
                            <abbr title="Phone">P:</abbr> {{ $order->customer_phone }}
                        </address>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="table-responsive mt-3">
                    <table class="table mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>₦{{ number_format($item->price, 2) }}</td>
                                <td class="text-end">₦{{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="row mt-3">
                    <div class="col-sm-7">
                        <div class="clearfix pt-3">
                            <h6 class="text-muted">Notes:</h6>
                            <small class="text-muted">
                                {{ $order->notes ?? 'Thank you for your order!' }}
                            </small>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="float-end">
                            <h3>₦{{ number_format($sellerTotal, 2) }}</h3>
                        </div>
                    </div>
                </div>

                <!-- Print Button -->
                <div class="mt-5 mb-1 no-print">
                    <div class="text-end">
                        <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
                        <button onclick="window.close()" class="btn btn-outline-primary">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('public/seller/assets/js/vendor.js') }}"></script>
    <script src="{{ asset('public/seller/assets/js/app.js') }}"></script>
</body>
</html>
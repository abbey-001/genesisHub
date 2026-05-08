<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.5;
        }
        .invoice-container {
            padding: 30px;
        }
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
        }
        .header-left {
            float: left;
            width: 50%;
        }
        .header-right {
            float: right;
            width: 45%;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .info-section {
            margin: 30px 0;
        }
        .info-box {
            float: left;
            width: 48%;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-box:last-child {
            float: right;
        }
        .info-box h3 {
            color: #667eea;
            font-size: 12px;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .info-box p {
            margin: 5px 0;
            font-size: 11px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        table thead {
            background-color: #667eea;
            color: white;
        }
        table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #dee2e6;
            font-size: 11px;
        }
        table tbody tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-section {
            float: right;
            width: 45%;
            margin-top: 20px;
        }
        .totals-table {
            width: 100%;
        }
        .totals-table td {
            padding: 8px 12px;
            border: none;
        }
        .totals-table .total-row {
            background-color: #667eea;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .payment-info {
            clear: both;
            margin-top: 40px;
            padding: 15px;
            background-color: #e8f5e9;
            border-left: 4px solid #28a745;
            border-radius: 5px;
        }
        .payment-info h3 {
            color: #28a745;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .notes {
            margin-top: 30px;
            padding: 15px;
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            border-radius: 5px;
        }
        .notes h3 {
            color: #856404;
            font-size: 12px;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
        }
        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header clearfix">
            <div class="header-left">
                <div class="company-name">{{ config('app.name') }}</div>
                <p>123 Business Street</p>
                <p>Abuja, FCT, Nigeria</p>
                <p>Email: {{ config('mail.from.address') }}</p>
                <p>Phone: +234 XXX XXX XXXX</p>
            </div>
            <div class="header-right">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-number">#{{ $order->order_number }}</div>
                <p style="margin-top: 10px;">
                    <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}
                </p>
                <p>
                    <strong>Status:</strong> 
                    <span class="status-badge status-{{ $order->payment_status }}">
                        {{ strtoupper($order->payment_status) }}
                    </span>
                </p>
            </div>
        </div>

        <!-- Billing and Shipping Information -->
        <div class="info-section clearfix">
            <div class="info-box">
                <h3>Bill To</h3>
                <p><strong>{{ $order->customer_name }}</strong></p>
                <p>{{ $order->customer_email }}</p>
                <p>{{ $order->customer_phone }}</p>
            </div>
            <div class="info-box">
                <h3>Ship To</h3>
                <p>{{ $order->shipping_address }}</p>
                <p>{{ $order->shipping_city }}, {{ $order->shipping_state }}</p>
                <p>{{ $order->shipping_postal_code }}</p>
                <p>{{ $order->shipping_country }}</p>
            </div>
        </div>

        <!-- Order Items -->
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th class="text-center">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                        @if($item->product_sku)
                        <br><small style="color: #666;">SKU: {{ $item->product_sku }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">₦{{ number_format($item->price, 2) }}</td>
                    <td class="text-right">₦{{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal:</td>
                    <td class="text-right">₦{{ number_format($order->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>Shipping Fee:</td>
                    <td class="text-right">₦{{ number_format($order->shipping_fee, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax (10%):</td>
                    <td class="text-right">₦{{ number_format($order->tax, 2) }}</td>
                </tr>
                @if($order->discount > 0)
                <tr>
                    <td>Discount:</td>
                    <td class="text-right">-₦{{ number_format($order->discount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td><strong>Grand Total:</strong></td>
                    <td class="text-right"><strong>₦{{ number_format($order->total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Payment Information -->
        <div class="payment-info clearfix">
            <h3>✓ Payment Information</h3>
            <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status) }}</p>
            @if($order->paid_at)
            <p><strong>Paid On:</strong> {{ $order->paid_at->format('M d, Y h:i A') }}</p>
            @endif
            @if($order->payment_reference)
            <p><strong>Transaction Reference:</strong> {{ $order->payment_reference }}</p>
            @endif
        </div>

        <!-- Notes -->
        @if($order->notes)
        <div class="notes">
            <h3>Notes</h3>
            <p>{{ $order->notes }}</p>
        </div>
        @endif

        <!-- Terms -->
        <div class="notes" style="background-color: #f8f9fa; border-left-color: #667eea;">
            <h3>Terms & Conditions</h3>
            <p style="font-size: 10px;">
                • All sales are final unless the product is defective.<br>
                • Returns must be made within 14 days of purchase with original packaging.<br>
                • Please contact customer service for any questions or concerns.<br>
                • Thank you for your business!
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>This is a computer-generated invoice and does not require a signature.</p>
            <p>{{ config('app.name') }} © {{ date('Y') }} - All Rights Reserved</p>
        </div>
    </div>
</body>
</html>
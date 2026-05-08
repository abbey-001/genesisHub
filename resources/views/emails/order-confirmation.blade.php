<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .email-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px;
        }
        .success-icon {
            text-align: center;
            margin: 20px 0;
        }
        .success-icon svg {
            width: 60px;
            height: 60px;
        }
        .order-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .order-info p {
            margin: 8px 0;
        }
        .order-info strong {
            color: #667eea;
        }
        .order-items {
            margin: 25px 0;
        }
        .order-items table {
            width: 100%;
            border-collapse: collapse;
        }
        .order-items th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .order-items td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        .order-total {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
        }
        .order-total table {
            width: 100%;
            margin-top: 10px;
        }
        .order-total td {
            padding: 8px 12px;
        }
        .order-total .total-row {
            font-weight: 700;
            font-size: 18px;
            color: #667eea;
            border-top: 2px solid #667eea;
        }
        .shipping-address {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .shipping-address h3 {
            margin-top: 0;
            color: #667eea;
            font-size: 16px;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: 600;
            text-align: center;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .social-links {
            margin: 15px 0;
        }
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #667eea;
            text-decoration: none;
        }
        @media only screen and (max-width: 600px) {
            body {
                padding: 10px;
            }
            .email-body {
                padding: 20px;
            }
            .order-items table {
                font-size: 14px;
            }
            .order-items th, .order-items td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>🎉 Order Confirmed!</h1>
            <p>Thank you for your purchase</p>
        </div>
        
        <div class="email-body">
            <div class="success-icon">
                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#28a745" stroke-width="2" fill="#e8f5e9"/>
                    <path d="M8 12L11 15L16 9" stroke="#28a745" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            
            <p>Hi <strong>{{ $order->customer_name }}</strong>,</p>
            
            <p>Great news! Your order has been confirmed and is being processed. We'll send you another email when your order has been shipped.</p>
            
            <div class="order-info">
                <p><strong>Order Number:</strong> {{ $order->order_number }}</p>
                <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y h:i A') }}</p>
                <p><strong>Payment Status:</strong> <span style="color: #28a745;">Paid</span></p>
            </div>
            
            <div class="order-items">
                <h3>Order Items</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₦{{ number_format($item->price, 2) }}</td>
                            <td>₦{{ number_format($item->total, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="order-total">
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td align="right">₦{{ number_format($order->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Shipping:</td>
                        <td align="right">₦{{ number_format($order->shipping_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td align="right">₦{{ number_format($order->tax, 2) }}</td>
                    </tr>
                    @if($order->discount > 0)
                    <tr>
                        <td>Discount:</td>
                        <td align="right">-₦{{ number_format($order->discount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>Total:</td>
                        <td align="right">₦{{ number_format($order->total, 2) }}</td>
                    </tr>
                </table>
            </div>
            
            <div class="shipping-address">
                <h3>📦 Shipping Address</h3>
                <p>
                    {{ $order->shipping_address }}<br>
                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }}<br>
                    {{ $order->shipping_country }}
                </p>
            </div>
            
            <center>
                <a href="{{ route('orders.show', $order->id) }}" class="cta-button">Track Your Order</a>
            </center>
            
            <p style="margin-top: 30px;">If you have any questions about your order, please don't hesitate to contact our customer support team.</p>
            
            <p>Thank you for shopping with us!</p>
        </div>
        
        <div class="email-footer">
            <p><strong>{{ config('app.name') }}</strong></p>
            <p>Need help? Contact us at {{ config('mail.from.address') }}</p>
            
            <div class="social-links">
                <a href="#">Facebook</a> | 
                <a href="#">Twitter</a> | 
                <a href="#">Instagram</a>
            </div>
            
            <p style="font-size: 12px; margin-top: 15px;">
                You're receiving this email because you placed an order on our website.<br>
                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
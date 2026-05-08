{{-- resources/views/emails/orders/confirmation.blade.php --}}
@php
    $itemCount   = $order->items->count();
    $sellerCount = $order->items->pluck('seller_id')->unique()->count();
    $paidAt      = $order->paid_at ? $order->paid_at->format('d M Y, g:i A') : now()->format('d M Y, g:i A');
@endphp

<x-emails.layouts.base
    subject="Order Confirmed — #{{ $order->order_number }}"
    tagline="Order Confirmation">

    {{-- Greeting --}}
    <p class="greeting">Thank you, {{ $order->customer_name ?? 'valued customer' }}!</p>
    <p class="email-tagline">Your order has been confirmed &amp; is being prepared</p>

    <p>
        Great news — your payment was successful and we've notified your seller(s)
        to start preparing your items. You'll receive another email once your order
        is on its way.
    </p>

    {{-- Order summary card --}}
    <div class="info-card">
        <div class="info-card-title">Order Summary</div>
        <div class="info-row">
            <div class="info-label">Order number</div>
            <div class="info-value">#{{ $order->order_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date placed</div>
            <div class="info-value">{{ $paidAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Payment method</div>
            <div class="info-value">{{ ucwords(str_replace('_', ' ', $order->payment_method ?? 'Card')) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Payment status</div>
            <div class="info-value">
                <span class="status-badge success">Paid</span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Items ordered</div>
            <div class="info-value">{{ $itemCount }} item{{ $itemCount !== 1 ? 's' : '' }} from {{ $sellerCount }} seller{{ $sellerCount !== 1 ? 's' : '' }}</div>
        </div>
    </div>

    {{-- Items table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:50%;">Item</th>
                <th>Qty</th>
                <th style="text-align:right;">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>
                    <strong style="color:#1a1a1a;font-size:13px;">{{ $item->product_name }}</strong>
                    @if($item->seller?->shop)
                        <br><span style="font-size:11px;color:#9ca3af;">by {{ $item->seller->shop->shop_name }}</span>
                    @endif
                </td>
                <td style="color:#555e68;">× {{ $item->quantity }}</td>
                <td style="text-align:right;font-weight:600;color:#1a1a1a;">
                    ₦{{ number_format($item->total_price, 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            @if($order->discount > 0)
            <tr>
                <td colspan="2" style="text-align:right;color:#555e68;font-size:12.5px;">Discount</td>
                <td style="text-align:right;color:#166534;font-weight:600;">−₦{{ number_format($order->discount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2" style="text-align:right;color:#555e68;font-size:12.5px;">Subtotal</td>
                <td style="text-align:right;color:#555e68;">₦{{ number_format($order->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:right;color:#555e68;font-size:12.5px;">Delivery fee</td>
                <td style="text-align:right;color:#555e68;">₦{{ number_format($order->shipping_fee, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Total paid</td>
                <td style="text-align:right;font-size:15px;">₦{{ number_format($order->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Shipping address --}}
    <div class="info-card" style="margin-top:20px;">
        <div class="info-card-title">Delivering to</div>
        <div style="font-size:13.5px;color:#1a1a1a;line-height:1.7;">
            <strong>{{ $order->customer_name }}</strong><br>
            {{ $order->shipping_address }},
            {{ $order->shipping_city }},
            {{ $order->shipping_state }}
            @if($order->shipping_postal_code)
                {{ $order->shipping_postal_code }}
            @endif
            <br>{{ $order->shipping_country }}
        </div>
        @if($order->customer_phone)
        <div style="font-size:12.5px;color:#9ca3af;margin-top:8px;">
            <strong style="color:#555e68;">Phone:</strong> {{ $order->customer_phone }}
        </div>
        @endif
    </div>

    <div class="cta-wrapper">
        <a href="{{ url('/account/orders/' . $order->id) }}" class="cta-button">
            Track Your Order
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-info">
        <strong>What happens next?</strong><br>
        Your seller{{ $sellerCount !== 1 ? 's are' : ' is' }} preparing your item{{ $itemCount !== 1 ? 's' : '' }}.
        Once ready for pickup, a rider will be assigned and you'll receive a shipping
        confirmation email with a delivery OTP.
    </div>

</x-emails.layouts.base>
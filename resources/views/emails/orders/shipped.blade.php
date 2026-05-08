{{-- resources/views/emails/orders/shipped.blade.php --}}
@php
    $order        = $delivery->order;
    $customerName = $notifiable->name ?? $order->customer_name ?? 'valued customer';
    $riderName    = $delivery->rider?->full_name ?? 'your rider';
    $pickedUpAt   = $delivery->picked_up_at?->format('g:i A') ?? now()->format('g:i A');
    $itemCount    = $order->items->count();
@endphp

<x-emails.layouts.base
    subject="Your order is on its way! — #{{ $order->order_number }}"
    tagline="Out for Delivery">

    <p class="greeting">It's on the way, {{ $customerName }}!</p>
    <p class="email-tagline">Your order has been picked up &amp; is heading to you</p>

    <p>
        Good news — your order has been picked up by a rider and is now
        out for delivery. Keep your phone close; you'll need to share your
        delivery OTP to receive your package.
    </p>

    {{-- OTP highlight --}}
    @if($delivery->delivery_otp ?? null)
    <div style="background:#714e32;border-radius:10px;padding:24px;text-align:center;margin:24px 0;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at 20% 20%,rgba(245,195,75,.18) 0%,transparent 60%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;">
            <p style="font-size:11px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.55);margin-bottom:8px;">
                Your Delivery OTP
            </p>
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:40px;font-weight:800;color:#ffffff;letter-spacing:10px;line-height:1;margin-bottom:8px;">
                {{ $delivery->delivery_otp }}
            </p>
            <p style="font-size:12px;color:rgba(255,255,255,.5);margin:0;">
                Share this code with the rider when your order arrives
            </p>
        </div>
    </div>
    @endif

    {{-- Delivery details --}}
    <div class="info-card">
        <div class="info-card-title">Delivery Details</div>
        <div class="info-row">
            <div class="info-label">Order number</div>
            <div class="info-value">#{{ $order->order_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Picked up at</div>
            <div class="info-value">{{ $pickedUpAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Items</div>
            <div class="info-value">{{ $itemCount }} item{{ $itemCount !== 1 ? 's' : '' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Delivery status</div>
            <div class="info-value">
                <span class="status-badge info">Out for Delivery</span>
            </div>
        </div>
        @if($delivery->estimated_delivery_time ?? null)
        <div class="info-row">
            <div class="info-label">Est. arrival</div>
            <div class="info-value">{{ \Carbon\Carbon::parse($delivery->estimated_delivery_time)->format('g:i A') }}</div>
        </div>
        @endif
    </div>

    {{-- Items being delivered --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Items in this delivery</th>
                <th style="text-align:right;">Qty</th>
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
                <td style="text-align:right;color:#555e68;">× {{ $item->quantity }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Shipping address --}}
    <div class="info-card">
        <div class="info-card-title">Delivering to</div>
        <div style="font-size:13.5px;color:#1a1a1a;line-height:1.7;">
            {{ $order->shipping_address }},
            {{ $order->shipping_city }},
            {{ $order->shipping_state }}
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ url('/account/orders/' . $order->id) }}" class="cta-button">
            Track Your Order
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box">
        <strong>Important:</strong> When your rider arrives, verify their identity
        and share your OTP code <strong>{{ $delivery->delivery_otp ?? '——' }}</strong> to confirm delivery.
        Do not share this code with anyone else.
    </div>

</x-emails.layouts.base>
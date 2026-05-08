{{-- resources/views/emails/orders/delivered.blade.php --}}
@php
    $order        = $delivery->order;
    $customerName = $notifiable->name ?? $order->customer_name ?? 'valued customer';
    $deliveredAt  = $delivery->delivered_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
    $itemCount    = $order->items->count();
    $sellerCount  = $order->items->pluck('seller_id')->unique()->count();
@endphp

<x-emails.layouts.base
    subject="Your order has been delivered! — #{{ $order->order_number }}"
    tagline="Delivery Confirmed">

    <p class="greeting">Delivered! Enjoy your order, {{ $customerName }}.</p>
    <p class="email-tagline">Your package has arrived</p>

    <p>
        Your order has been successfully delivered. We hope everything arrived
        in perfect condition. If you have any issues with your order, please
        reach out to us within 48 hours.
    </p>

    {{-- Delivery confirmed banner --}}
    <div style="background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1px solid #86efac;border-radius:10px;padding:20px 24px;margin:22px 0;display:flex;align-items:center;gap:14px;">
        <div style="flex-shrink:0;width:44px;height:44px;background:#166534;border-radius:50%;display:flex;align-items:center;justify-content:center;">
            <span style="font-size:20px;">✓</span>
        </div>
        <div>
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:15px;font-weight:700;color:#14532d;margin-bottom:2px;">
                Order Delivered Successfully
            </p>
            <p style="font-size:12.5px;color:#166534;margin:0;">
                {{ $deliveredAt }}
            </p>
        </div>
    </div>

    {{-- Order summary --}}
    <div class="info-card">
        <div class="info-card-title">Delivery Summary</div>
        <div class="info-row">
            <div class="info-label">Order number</div>
            <div class="info-value">#{{ $order->order_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Delivered on</div>
            <div class="info-value">{{ $deliveredAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Items received</div>
            <div class="info-value">{{ $itemCount }} item{{ $itemCount !== 1 ? 's' : '' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Order total</div>
            <div class="info-value" style="color:#714e32;font-size:14px;">₦{{ number_format($order->total, 2) }}</div>
        </div>
    </div>

    {{-- Items delivered --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>Items delivered</th>
                <th>Qty</th>
                <th style="text-align:right;">Amount</th>
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
    </table>

    {{-- Review CTA --}}
    <div style="background:#fef9f5;border:1px solid #eedecf;border-radius:10px;padding:20px 24px;margin:22px 0;text-align:center;">
        <p style="font-family:'Poppins','Inter',sans-serif;font-size:15px;font-weight:700;color:#1a1a1a;margin-bottom:6px;">
            How was your order?
        </p>
        <p style="font-size:13.5px;color:#555e68;margin-bottom:18px;">
            Your feedback helps other shoppers and rewards great sellers.
        </p>
        <a href="{{ url('/account/orders/' . $order->id . '/review') }}" class="cta-button">
            Leave a Review
        </a>
    </div>

    <div class="cta-wrapper" style="margin-top:16px;">
        <a href="{{ url('/account/orders/' . $order->id) }}" class="cta-button" style="background:#f9fafb;color:#714e32 !important;border:1px solid #e0e4ea;border-bottom:3px solid #e0e4ea;">
            View Order Details
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-info">
        <strong>Something not right?</strong><br>
        If any item is missing or damaged, please contact our support team
        within 48 hours of delivery so we can resolve it promptly.
        <br><br>
        <a href="{{ url('/contact') }}" style="color:#714e32;font-weight:600;">Contact Support →</a>
    </div>

</x-emails.layouts.base>
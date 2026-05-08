{{-- resources/views/emails/seller/new-order.blade.php --}}
@php
    $sellerItems  = $order->items->where('seller_id', $seller->id);
    $grossTotal   = $sellerItems->sum('total_price');
    $commission   = $seller->commission_rate ?? 10;
    $commissionAmt= $grossTotal * ($commission / 100);
    $netEarnings  = $grossTotal - $commissionAmt;
    $shopName     = $seller->shop->shop_name ?? 'Your Shop';
    $sellerName   = $notifiable->name ?? 'Seller';
    $paidAt       = $order->paid_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
    $itemCount    = $sellerItems->count();
@endphp

<x-emails.layouts.base
    subject="New order received — #{{ $order->order_number }}"
    tagline="New Order">

    {{-- Greeting --}}
    <p class="greeting">New order, {{ $sellerName }}!</p>
    <p class="email-tagline">A customer just placed an order from {{ $shopName }}</p>

    <p>
        Great news — a customer has paid for
        {{ $itemCount }} item{{ $itemCount !== 1 ? 's' : '' }} from your shop.
        Please prepare {{ $itemCount !== 1 ? 'them' : 'it' }} for pickup as soon as possible.
    </p>

    {{-- Earnings highlight --}}
    <div style="background:#714e32;border-radius:10px;padding:22px 26px;margin:22px 0;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at 15% 15%,rgba(245,195,75,.16) 0%,transparent 55%),radial-gradient(circle at 85% 85%,rgba(255,255,255,.05) 0%,transparent 50%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;">
            <p style="font-size:10.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:6px;">Your Net Earnings</p>
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:34px;font-weight:800;color:#ffffff;line-height:1;margin-bottom:4px;">
                ₦{{ number_format($netEarnings, 2) }}
            </p>
            <p style="font-size:12px;color:rgba(255,255,255,.45);margin:0;">
                After {{ $commission }}% platform commission (₦{{ number_format($commissionAmt, 2) }})
            </p>
        </div>
    </div>

    {{-- Order details --}}
    <div class="info-card">
        <div class="info-card-title">Order Details</div>
        <div class="info-row">
            <div class="info-label">Order number</div>
            <div class="info-value">#{{ $order->order_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Paid at</div>
            <div class="info-value">{{ $paidAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Items to prepare</div>
            <div class="info-value">{{ $itemCount }} item{{ $itemCount !== 1 ? 's' : '' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Gross sales</div>
            <div class="info-value">₦{{ number_format($grossTotal, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Net earnings</div>
            <div class="info-value" style="color:#714e32;">₦{{ number_format($netEarnings, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Payment status</div>
            <div class="info-value"><span class="status-badge success">Paid</span></div>
        </div>
    </div>

    {{-- Items table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:55%;">Item</th>
                <th>Qty</th>
                <th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sellerItems as $item)
            <tr>
                <td>
                    <strong style="color:#1a1a1a;font-size:13px;">{{ $item->product_name }}</strong>
                    @if($item->product_sku)
                        <br><span style="font-size:11px;color:#9ca3af;">SKU: {{ $item->product_sku }}</span>
                    @endif
                </td>
                <td style="color:#555e68;">× {{ $item->quantity }}</td>
                <td style="text-align:right;font-weight:600;color:#1a1a1a;">₦{{ number_format($item->total_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Total (gross)</td>
                <td style="text-align:right;">₦{{ number_format($grossTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Deliver to --}}
    <div class="info-card" style="margin-top:20px;">
        <div class="info-card-title">Ship to customer</div>
        <div style="font-size:13.5px;color:#1a1a1a;line-height:1.7;">
            <strong>{{ $order->customer_name }}</strong><br>
            {{ $order->shipping_address }},
            {{ $order->shipping_city }},
            {{ $order->shipping_state }}
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ route('seller.orders.show', $order) }}" class="cta-button">
            View &amp; Prepare Order
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-info">
        <strong>Next step:</strong> Mark your items as ready once they're packaged.
        A rider will be assigned to collect from your shop. You'll receive a notification
        when a rider is on the way.
    </div>

</x-emails.layouts.base>
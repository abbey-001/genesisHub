{{-- resources/views/emails/rider/delivery-available.blade.php --}}
@php
    $isBundle    = !is_null($bundle);
    $riderName   = $notifiable->name ?? 'Rider';

    if ($isBundle) {
        $deliveries  = $bundle->deliveries;
        $totalFee    = $deliveries->sum('delivery_fee');
        $totalItems  = $deliveries->sum(fn($d) => $d->relationLoaded('items') ? $d->items->count() : 0);
        $sellerCount = $deliveries->count();
        $orderNum    = $bundle->order->order_number ?? 'N/A';
        $zone        = $bundle->pickup_zone ?? 'N/A';
    } else {
        $orderNum    = $delivery?->order->order_number ?? 'N/A';
        $totalFee    = $delivery?->delivery_fee ?? 0;
        $totalItems  = $delivery?->relationLoaded('items') ? $delivery->items->count() : 0;
        $shopName    = $delivery?->seller?->shop?->shop_name ?? 'Seller';
    }

    $actionUrl = route('rider.broadcasts.show', $broadcast);
@endphp

<x-emails.layouts.base
    subject="{{ $isBundle ? 'New bundle delivery available — '.$zone : 'New delivery available — #'.$orderNum }}"
    tagline="{{ $isBundle ? 'Bundle Delivery' : 'New Delivery' }}">

    <p class="greeting">New {{ $isBundle ? 'bundle ' : '' }}delivery for you, {{ $riderName }}!</p>
    <p class="email-tagline">
        {{ $isBundle ? 'A multi-pickup bundle is ready in your zone' : 'A delivery is ready and waiting for acceptance' }}
    </p>

    <p>
        @if($isBundle)
            A bundle of <strong>{{ $sellerCount }} seller{{ $sellerCount !== 1 ? 's' : '' }}</strong>
            in zone <strong>{{ $zone }}</strong> is ready for pickup.
            First company to accept gets all {{ $sellerCount }} pickups!
        @else
            Order <strong>#{{ $orderNum }}</strong> from
            <strong>{{ $shopName }}</strong> is ready for pickup.
            First to accept gets this delivery!
        @endif
    </p>

    {{-- Earnings highlight --}}
    <div style="background:#714e32;border-radius:10px;padding:22px 26px;margin:22px 0;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at 15% 15%,rgba(245,195,75,.16) 0%,transparent 55%),radial-gradient(circle at 85% 85%,rgba(255,255,255,.05) 0%,transparent 50%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;">
            <p style="font-size:10.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:6px;">
                {{ $isBundle ? 'Total Bundle Fee' : 'Delivery Fee' }}
            </p>
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:36px;font-weight:800;color:#ffffff;line-height:1;margin-bottom:4px;">
                ₦{{ number_format($totalFee, 2) }}
            </p>
            @if($isBundle)
            <p style="font-size:12px;color:rgba(255,255,255,.45);margin:0;">
                {{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }} across {{ $sellerCount }} seller{{ $sellerCount !== 1 ? 's' : '' }}
            </p>
            @else
            <p style="font-size:12px;color:rgba(255,255,255,.45);margin:0;">
                {{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }} · Order #{{ $orderNum }}
            </p>
            @endif
        </div>
    </div>

    {{-- Delivery details --}}
    <div class="info-card">
        <div class="info-card-title">{{ $isBundle ? 'Bundle Details' : 'Delivery Details' }}</div>

        @if($isBundle)
            <div class="info-row">
                <div class="info-label">Order number</div>
                <div class="info-value">#{{ $orderNum }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pickup zone</div>
                <div class="info-value">{{ $zone }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sellers to pickup</div>
                <div class="info-value">{{ $sellerCount }} seller{{ $sellerCount !== 1 ? 's' : '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total items</div>
                <div class="info-value">{{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total fee</div>
                <div class="info-value" style="color:#714e32;">₦{{ number_format($totalFee, 2) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Deliver to</div>
                <div class="info-value">{{ $bundle->order->shipping_address ?? 'See app for details' }}</div>
            </div>
        @else
            <div class="info-row">
                <div class="info-label">Order number</div>
                <div class="info-value">#{{ $orderNum }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Pickup from</div>
                <div class="info-value">{{ $delivery?->pickup_address ?? 'See app for details' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Deliver to</div>
                <div class="info-value">{{ $delivery?->delivery_address ?? 'See app for details' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Items</div>
                <div class="info-value">{{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Delivery fee</div>
                <div class="info-value" style="color:#714e32;">₦{{ number_format($totalFee, 2) }}</div>
            </div>
        @endif
    </div>

    {{-- Bundle seller breakdown --}}
    @if($isBundle)
    <table class="items-table">
        <thead>
            <tr>
                <th>Shop</th>
                <th>Items</th>
                <th style="text-align:right;">Fee share</th>
            </tr>
        </thead>
        <tbody>
            @foreach($deliveries as $del)
            <tr>
                <td><strong style="color:#1a1a1a;font-size:13px;">{{ $del->seller?->shop?->shop_name ?? 'Shop' }}</strong></td>
                <td style="color:#555e68;">{{ $del->relationLoaded('items') ? $del->items->count() : '—' }}</td>
                <td style="text-align:right;font-weight:600;color:#1a1a1a;">₦{{ number_format($del->delivery_fee, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" style="text-align:right;">Total fee</td>
                <td style="text-align:right;">₦{{ number_format($totalFee, 2) }}</td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="cta-wrapper">
        <a href="{{ $actionUrl }}" class="cta-button">
            <i class="fa-solid fa-bolt"></i>
            {{ $isBundle ? 'Accept Bundle Now' : 'Accept Delivery Now' }}
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box">
        <strong>Act fast!</strong>
        {{ $isBundle ? 'Bundle deliveries' : 'Deliveries' }} are offered to multiple riders simultaneously.
        The first company to accept secures {{ $isBundle ? 'all pickups in this bundle' : 'this delivery' }}.
        Open the app or click above to accept.
    </div>

</x-emails.layouts.base>
{{-- resources/views/emails/rider/delivery-assigned.blade.php --}}
@php
    $order       = $delivery->order;
    $riderName   = $notifiable->name ?? 'Rider';
    $isBundle    = !is_null($delivery->bundle_id);
    $bundle      = $delivery->bundle;

    // For bundles, collect all sibling deliveries
    $allDeliveries = $isBundle
        ? $bundle->deliveries->loadMissing(['seller.shop', 'items'])
        : collect([$delivery]);

    $totalFee    = $isBundle
        ? $allDeliveries->sum('delivery_fee')
        : $delivery->delivery_fee;

    $totalItems  = $allDeliveries->sum(fn($d) =>
        $d->relationLoaded('items') ? $d->items->count() : 0
    );

    $assignedAt  = $delivery->assigned_at?->format('g:i A, d M Y') ?? now()->format('g:i A, d M Y');

    // Google Maps link for pickup
    $mapsUrl = $delivery->pickup_latitude && $delivery->pickup_longitude
        ? 'https://www.google.com/maps/search/?api=1&query=' . $delivery->pickup_latitude . ',' . $delivery->pickup_longitude
        : 'https://www.google.com/maps/search/?api=1&query=' . urlencode($delivery->pickup_address ?? '');
@endphp

<x-emails.layouts.base
    subject="Delivery assigned — #{{ $order->order_number }}"
    tagline="Delivery Assigned">

    <p class="greeting">You're on, {{ $riderName }}!</p>
    <p class="email-tagline">
        {{ $isBundle ? 'Bundle delivery assigned — multiple pickups ready' : 'A delivery has been assigned to you' }}
    </p>

    <p>
        You've accepted
        @if($isBundle)
            a bundle of <strong>{{ $allDeliveries->count() }} pickups</strong>.
            Please proceed to each shop in order to collect all packages.
        @else
            order <strong>#{{ $order->order_number }}</strong>.
            Please proceed to the pickup address to collect the package.
        @endif
    </p>

    {{-- Fee highlight --}}
    <div style="background:#714e32;border-radius:10px;padding:22px 26px;margin:22px 0;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at 15% 15%,rgba(245,195,75,.16) 0%,transparent 55%),radial-gradient(circle at 85% 85%,rgba(255,255,255,.05) 0%,transparent 50%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;display:table;width:100%;">
            <div style="display:table-cell;vertical-align:middle;">
                <p style="font-size:10px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:5px;">
                    {{ $isBundle ? 'Total Bundle Earnings' : 'Your Delivery Fee' }}
                </p>
                <p style="font-family:'Poppins','Inter',sans-serif;font-size:34px;font-weight:800;color:#ffffff;line-height:1;margin-bottom:4px;">
                    ₦{{ number_format($totalFee, 2) }}
                </p>
                <p style="font-size:12px;color:rgba(255,255,255,.45);margin:0;">
                    {{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }}
                    @if($isBundle) across {{ $allDeliveries->count() }} pickups @endif
                </p>
            </div>
            <div style="display:table-cell;vertical-align:middle;text-align:right;padding-left:16px;">
                <div style="display:inline-flex;align-items:center;gap:5px;background:rgba(245,195,75,.2);border:1px solid rgba(245,195,75,.35);color:#f5c34b;padding:4px 12px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">
                    Assigned
                </div>
            </div>
        </div>
    </div>

    {{-- Assignment details --}}
    <div class="info-card">
        <div class="info-card-title">Assignment Details</div>
        <div class="info-row">
            <div class="info-label">Order number</div>
            <div class="info-value">#{{ $order->order_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Assigned at</div>
            <div class="info-value">{{ $assignedAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Deliver to</div>
            <div class="info-value">{{ $delivery->delivery_address ?? $order->shipping_city }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total items</div>
            <div class="info-value">{{ $totalItems }} item{{ $totalItems !== 1 ? 's' : '' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value"><span class="status-badge info">Assigned</span></div>
        </div>
    </div>

    {{-- Pickup locations --}}
    <table class="items-table">
        <thead>
            <tr>
                <th>{{ $isBundle ? 'Pickup stop' : 'Pickup from' }}</th>
                <th style="text-align:right;">Items</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allDeliveries as $i => $del)
            <tr>
                <td>
                    <strong style="color:#1a1a1a;font-size:13px;">
                        {{ $isBundle ? 'Stop ' . ($i + 1) . ' — ' : '' }}{{ $del->seller?->shop?->shop_name ?? 'Shop' }}
                    </strong>
                    <br>
                    <span style="font-size:11.5px;color:#9ca3af;">{{ $del->pickup_address ?? 'Address in app' }}</span>
                </td>
                <td style="text-align:right;color:#555e68;font-weight:600;">
                    {{ $del->relationLoaded('items') ? $del->items->count() : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Map CTA --}}
    <div style="background:#f9fafb;border:1px solid #e0e4ea;border-radius:8px;padding:16px 20px;margin:18px 0;display:table;width:100%;">
        <div style="display:table-cell;vertical-align:middle;">
            <p style="font-size:13px;font-weight:600;color:#1a1a1a;margin-bottom:2px;">Get directions</p>
            <p style="font-size:12px;color:#9ca3af;margin:0;">Open in Google Maps</p>
        </div>
        <div style="display:table-cell;vertical-align:middle;text-align:right;width:120px;">
            <a href="{{ $mapsUrl }}" style="display:inline-block;background:#714e32;color:#fff;text-decoration:none;font-family:'Poppins','Inter',sans-serif;font-size:12px;font-weight:700;padding:9px 18px;border-radius:7px;border-bottom:2px solid #f5c34b;">
                Navigate
            </a>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ route('rider.deliveries.show', $delivery) }}" class="cta-button">
            View Full Delivery Details
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-info">
        <strong>Reminder:</strong> Take a photo when picking up each package and again
        upon delivery. The customer will share an OTP — you'll need it to confirm delivery.
    </div>

</x-emails.layouts.base>
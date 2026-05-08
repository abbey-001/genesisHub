{{-- resources/views/emails/seller/payout-requested.blade.php --}}
@php
    $sellerName  = $notifiable->name ?? 'Seller';
    $shopName    = $payout->seller->shop->shop_name ?? 'Your Shop';
    $requestedAt = $payout->requested_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
    $methodLabel = match($payout->payout_method) {
        'bank_transfer' => 'Bank Transfer',
        'paypal'        => 'PayPal',
        'stripe'        => 'Stripe',
        default         => ucwords(str_replace('_', ' ', $payout->payout_method ?? 'Bank Transfer')),
    };
@endphp

<x-emails.layouts.base
    subject="Payout request received — ₦{{ number_format($payout->amount, 2) }}"
    tagline="Payout Request">

    <p class="greeting">Request received, {{ $sellerName }}.</p>
    <p class="email-tagline">Your payout request is under review</p>

    <p>
        We've received your payout request and it's now in the queue for admin review.
        You'll be notified by email once it's approved and funds are on their way.
    </p>

    {{-- Amount card --}}
    <div style="background:#f9fafb;border:1px solid #e0e4ea;border-radius:10px;padding:24px;margin:22px 0;text-align:center;">
        <p style="font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#9ca3af;margin-bottom:8px;">Payout Requested</p>
        <p style="font-family:'Poppins','Inter',sans-serif;font-size:38px;font-weight:800;color:#714e32;line-height:1;margin-bottom:6px;">
            ₦{{ number_format($payout->amount, 2) }}
        </p>
        @if($payout->fee_amount > 0)
        <p style="font-size:12.5px;color:#9ca3af;margin:0;">
            You'll receive <strong style="color:#555e68;">₦{{ number_format($payout->net_amount, 2) }}</strong>
            after ₦{{ number_format($payout->fee_amount, 2) }} processing fee
        </p>
        @else
        <p style="font-size:12.5px;color:#9ca3af;margin:0;">No processing fee — you'll receive the full amount</p>
        @endif
    </div>

    {{-- Request details --}}
    <div class="info-card">
        <div class="info-card-title">Request Details</div>
        <div class="info-row">
            <div class="info-label">Reference</div>
            <div class="info-value">#{{ $payout->id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Requested at</div>
            <div class="info-value">{{ $requestedAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Payment method</div>
            <div class="info-value">{{ $methodLabel }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Gross amount</div>
            <div class="info-value">₦{{ number_format($payout->amount, 2) }}</div>
        </div>
        @if($payout->fee_amount > 0)
        <div class="info-row">
            <div class="info-label">Processing fee</div>
            <div class="info-value" style="color:#854d0e;">−₦{{ number_format($payout->fee_amount, 2) }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">You will receive</div>
            <div class="info-value" style="color:#714e32;font-size:14px;">₦{{ number_format($payout->net_amount, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value"><span class="status-badge pending">Pending Review</span></div>
        </div>
    </div>

    @if($payout->notes)
    <div class="info-card" style="border-left-color:#f5c34b;">
        <div class="info-card-title">Your Notes</div>
        <p style="font-size:13.5px;color:#555e68;margin:0;line-height:1.6;">{{ $payout->notes }}</p>
    </div>
    @endif

    <div class="cta-wrapper">
        <a href="{{ route('seller.payouts.show', $payout) }}" class="cta-button">
            View Payout Details
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box">
        <strong>What happens next?</strong><br>
        Our admin team will review and approve your request. Processing typically
        takes 1–3 business days. You'll receive an email the moment your request
        is approved and again when funds are transferred.
    </div>

</x-emails.layouts.base>
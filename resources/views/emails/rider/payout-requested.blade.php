{{-- resources/views/emails/rider/payout-requested.blade.php --}}
@php
    $riderName   = $notifiable->name ?? 'Rider';
    $company     = $payout->company;
    $requestedAt = $payout->requested_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
    $periodFrom  = $payout->period_from?->format('d M Y') ?? '—';
    $periodTo    = $payout->period_to?->format('d M Y') ?? '—';
@endphp

<x-emails.layouts.base
    subject="Payout request received — {{ $payout->reference_number }}"
    tagline="Payout Request">

    <p class="greeting">Request received, {{ $riderName }}.</p>
    <p class="email-tagline">Your payout request is under review</p>

    <p>
        We've received your payout request covering
        <strong>{{ $payout->deliveries_count }} completed {{ $payout->deliveries_count === 1 ? 'delivery' : 'deliveries' }}</strong>.
        Our admin team will review and process it shortly.
    </p>

    {{-- Amount card --}}
    <div style="background:#f9fafb;border:1px solid #e0e4ea;border-radius:10px;padding:24px;margin:22px 0;text-align:center;">
        <p style="font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#9ca3af;margin-bottom:8px;">Payout Requested</p>
        <p style="font-family:'Poppins','Inter',sans-serif;font-size:38px;font-weight:800;color:#714e32;line-height:1;margin-bottom:6px;">
            ₦{{ number_format($payout->amount, 2) }}
        </p>
        <p style="font-size:12.5px;color:#9ca3af;margin:0;">
            for {{ $payout->deliveries_count }} completed {{ $payout->deliveries_count === 1 ? 'delivery' : 'deliveries' }}
        </p>
    </div>

    {{-- Request details --}}
    <div class="info-card">
        <div class="info-card-title">Request Details</div>
        <div class="info-row">
            <div class="info-label">Reference</div>
            <div class="info-value">{{ $payout->reference_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Requested at</div>
            <div class="info-value">{{ $requestedAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Period covered</div>
            <div class="info-value">{{ $periodFrom }} – {{ $periodTo }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Deliveries</div>
            <div class="info-value">{{ $payout->deliveries_count }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Amount</div>
            <div class="info-value" style="color:#714e32;font-size:14px;">₦{{ number_format($payout->amount, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Bank</div>
            <div class="info-value">{{ $payout->bank_name ?? $company?->bank_name ?? '——' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Account name</div>
            <div class="info-value">{{ $payout->account_name ?? $company?->account_name ?? '——' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value"><span class="status-badge pending">Pending Review</span></div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ route('rider.earnings.index') }}" class="cta-button">
            View Earnings Dashboard
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box">
        <strong>What happens next?</strong><br>
        Our team will review your request within 1–3 business days. You'll receive
        an email when it's approved and again when funds are transferred to your
        bank account.
    </div>

</x-emails.layouts.base>
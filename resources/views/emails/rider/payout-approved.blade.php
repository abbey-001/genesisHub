{{-- resources/views/emails/rider/payout-approved.blade.php --}}
@php
    $riderName  = $notifiable->name ?? 'Rider';
    $company    = $payout->company;
    $approvedAt = $payout->approved_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
@endphp

<x-emails.layouts.base
    subject="Payout approved — ₦{{ number_format($payout->amount, 2) }} is being processed"
    tagline="Payout Approved">

    <p class="greeting">Approved, {{ $riderName }}!</p>
    <p class="email-tagline">Your payout request has been approved</p>

    <p>
        Your payout request has been reviewed and approved by our admin team.
        Your funds are now being processed and will be transferred to your
        bank account shortly.
    </p>

    {{-- Approval banner --}}
    <div style="background:linear-gradient(135deg,#fef9f0,#fef3e2);border:1px solid #fde68a;border-radius:10px;padding:20px 24px;margin:22px 0;display:table;width:100%;">
        <div style="display:table-cell;vertical-align:middle;width:52px;">
            <div style="width:44px;height:44px;background:#f5c34b;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:20px;font-weight:800;">✓</div>
        </div>
        <div style="display:table-cell;vertical-align:middle;padding-left:14px;">
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:14px;font-weight:700;color:#92400e;margin-bottom:2px;">Payout Approved</p>
            <p style="font-size:12.5px;color:#b45309;margin:0;">{{ $approvedAt }}</p>
        </div>
    </div>

    {{-- Amount --}}
    <div style="background:#f9fafb;border:1px solid #e0e4ea;border-radius:10px;padding:22px;margin:20px 0;text-align:center;">
        <p style="font-size:10.5px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#9ca3af;margin-bottom:8px;">Amount Being Transferred</p>
        <p style="font-family:'Poppins','Inter',sans-serif;font-size:36px;font-weight:800;color:#714e32;line-height:1;margin-bottom:4px;">
            ₦{{ number_format($payout->amount, 2) }}
        </p>
        <p style="font-size:12px;color:#9ca3af;margin:0;">
            for {{ $payout->deliveries_count }} {{ $payout->deliveries_count === 1 ? 'delivery' : 'deliveries' }}
        </p>
    </div>

    {{-- Payout details --}}
    <div class="info-card">
        <div class="info-card-title">Payout Details</div>
        <div class="info-row">
            <div class="info-label">Reference</div>
            <div class="info-value">{{ $payout->reference_number }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Approved at</div>
            <div class="info-value">{{ $approvedAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Deliveries covered</div>
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
            <div class="info-value"><span class="status-badge pending">Processing</span></div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ route('rider.earnings.index') }}" class="cta-button">
            View Earnings Dashboard
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-info">
        <strong>Transfer in progress.</strong><br>
        Bank transfers typically arrive within 1–2 business days. You'll receive
        a final confirmation email once the funds have been sent to your account.
    </div>

</x-emails.layouts.base>
{{-- resources/views/emails/rider/payout-paid.blade.php --}}
@php
    $riderName  = $notifiable->name ?? 'Rider';
    $company    = $payout->company;
    $paidAt     = $payout->paid_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
    $methodLabel = match($payout->payment_method) {
        'bank_transfer' => 'Bank Transfer',
        'paypal'        => 'PayPal',
        default         => ucwords(str_replace('_', ' ', $payout->payment_method ?? 'Bank Transfer')),
    };
    // Mask account number
    $acct   = $payout->account_number ?? $company?->account_number ?? '';
    $masked = strlen($acct) > 4
        ? str_repeat('•', strlen($acct) - 4) . substr($acct, -4)
        : '••••';
@endphp

<x-emails.layouts.base
    subject="Payout sent — ₦{{ number_format($payout->amount, 2) }} is on its way"
    tagline="Payment Sent">

    <p class="greeting">Money's on the way, {{ $riderName }}!</p>
    <p class="email-tagline">Your payout has been successfully transferred</p>

    <p>
        Your earnings have been processed and the funds have been sent to your
        bank account. Depending on your bank, it may take up to 1–2 business
        days to appear in your balance.
    </p>

    {{-- Paid block --}}
    <div style="background:#714e32;border-radius:10px;padding:26px;margin:22px 0;text-align:center;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at 15% 15%,rgba(245,195,75,.18) 0%,transparent 60%),radial-gradient(circle at 85% 85%,rgba(255,255,255,.05) 0%,transparent 50%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;">
            <p style="font-size:10px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:8px;">Amount Transferred</p>
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:42px;font-weight:800;color:#ffffff;line-height:1;margin-bottom:8px;">
                ₦{{ number_format($payout->amount, 2) }}
            </p>
            <span style="display:inline-block;background:rgba(245,195,75,.2);border:1px solid rgba(245,195,75,.35);color:#f5c34b;padding:3px 12px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;margin-right:6px;">Sent</span>
            <span style="font-size:12px;color:rgba(255,255,255,.5);">{{ $paidAt }}</span>
        </div>
    </div>

    {{-- Transaction details --}}
    <div class="info-card">
        <div class="info-card-title">Transaction Details</div>
        <div class="info-row">
            <div class="info-label">Reference</div>
            <div class="info-value">{{ $payout->reference_number }}</div>
        </div>
        @if($payout->transaction_reference)
        <div class="info-row">
            <div class="info-label">Transaction ref</div>
            <div class="info-value" style="font-size:12px;word-break:break-all;">{{ $payout->transaction_reference }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Paid on</div>
            <div class="info-value">{{ $paidAt }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Payment method</div>
            <div class="info-value">{{ $methodLabel }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Deliveries covered</div>
            <div class="info-value">{{ $payout->deliveries_count }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Amount paid</div>
            <div class="info-value" style="color:#714e32;font-size:15px;font-weight:700;">₦{{ number_format($payout->amount, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value"><span class="status-badge success">Paid</span></div>
        </div>
    </div>

    {{-- Bank details --}}
    <div class="info-card" style="border-left-color:#f5c34b;">
        <div class="info-card-title">Sent to</div>
        <div class="info-row">
            <div class="info-label">Bank</div>
            <div class="info-value">{{ $payout->bank_name ?? $company?->bank_name ?? '——' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Account name</div>
            <div class="info-value">{{ $payout->account_name ?? $company?->account_name ?? '——' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Account number</div>
            <div class="info-value">{{ $masked }}</div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ route('rider.earnings.index') }}" class="cta-button">
            View Earnings Dashboard
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-success">
        <strong>Keep delivering!</strong><br>
        Great work completing those deliveries. If you don't see the funds within
        3 business days, contact your bank with the transaction reference above.
        For other issues, our support team is here to help.
    </div>

</x-emails.layouts.base>
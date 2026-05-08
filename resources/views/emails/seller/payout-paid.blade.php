{{-- resources/views/emails/seller/payout-paid.blade.php --}}
@php
    $sellerName  = $notifiable->name ?? 'Seller';
    $processedAt = $payout->processed_at?->format('d M Y, g:i A') ?? now()->format('d M Y, g:i A');
    $methodLabel = match($payout->payout_method) {
        'bank_transfer' => 'Bank Transfer',
        'paypal'        => 'PayPal',
        'stripe'        => 'Stripe',
        default         => ucwords(str_replace('_', ' ', $payout->payout_method ?? 'Bank Transfer')),
    };
    $seller      = $payout->seller;
    $shopName    = $seller->shop->shop_name ?? 'Your Shop';
@endphp

<x-emails.layouts.base
    subject="Funds sent — ₦{{ number_format($payout->net_amount, 2) }} is on its way"
    tagline="Payment Sent">

    <p class="greeting">Money's on the way, {{ $sellerName }}!</p>
    <p class="email-tagline">Your payout has been successfully transferred</p>

    <p>
        Your payout has been processed and the funds have been sent to your
        {{ $methodLabel }} account. Depending on your bank, it may take up to
        1–2 business days to reflect.
    </p>

    {{-- Money sent banner --}}
    <div style="background:#714e32;border-radius:10px;padding:26px;margin:22px 0;text-align:center;position:relative;overflow:hidden;">
        <div style="position:absolute;inset:0;background:radial-gradient(circle at 15% 15%,rgba(245,195,75,.18) 0%,transparent 60%),radial-gradient(circle at 85% 85%,rgba(255,255,255,.05) 0%,transparent 50%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;">
            <p style="font-size:10.5px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.5);margin-bottom:8px;">Amount Transferred</p>
            <p style="font-family:'Poppins','Inter',sans-serif;font-size:42px;font-weight:800;color:#ffffff;line-height:1;margin-bottom:6px;">
                ₦{{ number_format($payout->net_amount, 2) }}
            </p>
            <p style="font-size:12.5px;color:rgba(255,255,255,.55);margin:0;">
                <span style="display:inline-block;background:rgba(245,195,75,.2);border:1px solid rgba(245,195,75,.35);color:#f5c34b;padding:2px 10px;border-radius:100px;font-size:11px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;">Sent</span>
                &nbsp; {{ $processedAt }}
            </p>
        </div>
    </div>

    {{-- Transaction details --}}
    <div class="info-card">
        <div class="info-card-title">Transaction Details</div>
        <div class="info-row">
            <div class="info-label">Payout reference</div>
            <div class="info-value">#{{ $payout->id }}</div>
        </div>
        @if($payout->transaction_id)
        <div class="info-row">
            <div class="info-label">Transaction ID</div>
            <div class="info-value" style="font-size:12px;word-break:break-all;">{{ $payout->transaction_id }}</div>
        </div>
        @endif
        <div class="info-row">
            <div class="info-label">Transferred on</div>
            <div class="info-value">{{ $processedAt }}</div>
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
            <div class="info-label">Amount received</div>
            <div class="info-value" style="color:#714e32;font-size:15px;font-weight:700;">₦{{ number_format($payout->net_amount, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status</div>
            <div class="info-value"><span class="status-badge success">Completed</span></div>
        </div>
    </div>

    {{-- Bank details reminder --}}
    <div class="info-card" style="border-left-color:#f5c34b;">
        <div class="info-card-title">Sent to</div>
        <div class="info-row">
            <div class="info-label">Bank</div>
            <div class="info-value">{{ $seller->bank_name ?? '——' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Account name</div>
            <div class="info-value">{{ $seller->account_holder_name ?? '——' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Account number</div>
            <div class="info-value">
                {{-- Mask all but last 4 digits --}}
                @php
                    $acct = $seller->bank_account ?? '';
                    $masked = strlen($acct) > 4
                        ? str_repeat('•', strlen($acct) - 4) . substr($acct, -4)
                        : '••••';
                @endphp
                {{ $masked }}
            </div>
        </div>
    </div>

    <div class="cta-wrapper">
        <a href="{{ route('seller.payouts.index') }}" class="cta-button">
            View Payout History
        </a>
    </div>

    <hr class="divider">

    <div class="alert-box alert-success">
        <strong>Funds on the way!</strong><br>
        If you don't see the funds within 3 business days, please contact your bank
        with the transaction reference above. For any other issues, reach out to our
        support team and we'll help resolve it promptly.
    </div>

</x-emails.layouts.base>
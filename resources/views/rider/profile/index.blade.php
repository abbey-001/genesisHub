@extends('rider.layouts.app')

@section('title', 'Company Profile')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <h4 class="mb-4">
        <i class="bx bx-user me-2"></i>Company Profile
    </h4>

    <div class="row g-4">
        <!-- Company Info Card -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="avatar avatar-xl mb-3">
                            <span class="avatar-initial rounded-circle bg-label-primary" style="width: 120px; height: 120px; font-size: 48px;">
                                {{ substr($rider->full_name, 0, 2) }}
                            </span>
                        </div>
                    </div>
                    <h5 class="mb-1">{{ $rider->full_name }}</h5>
                    <p class="text-muted mb-3">Delivery Company</p>
                    
                    <div class="d-flex justify-content-center gap-3 mb-3">
                        <div>
                            <i class="bx bx-check-circle text-success"></i>
                            <strong>{{ $rider->completed_deliveries }}</strong>
                            <div class="small text-muted">Completed</div>
                        </div>
                        <div>
                            <i class="bx bx-trending-up text-info"></i>
                            <strong>{{ $rider->success_rate }}%</strong>
                            <div class="small text-muted">Success Rate</div>
                        </div>
                    </div>

                    @if($rider->is_verified)
                        <span class="badge bg-success">
                            <i class="bx bx-check-shield me-1"></i>Verified Company
                        </span>
                    @else
                        <span class="badge bg-warning">
                            <i class="bx bx-time me-1"></i>Pending Verification
                        </span>
                    @endif
                </div>
            </div>

            <!-- Fleet Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-car me-2"></i>Fleet Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($rider->vehicle_type)
                    <div class="mb-0">
                        <small class="text-muted d-block">Fleet Type</small>
                        <strong>{{ $rider->vehicle_type }}</strong>
                    </div>
                    @else
                    <p class="text-muted mb-0 small">No fleet information added yet</p>
                    @endif
                </div>
            </div>

            <!-- Telegram Connect Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-send me-2"></i>Telegram Notifications
                    </h6>
                </div>
                <div class="card-body">
                    @if($rider->telegram_chat_id)
                        {{-- Already linked --}}
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-success p-2">
                                <i class="bx bx-check me-1"></i>Connected
                            </span>
                            <small class="text-muted">Linked {{ $rider->telegram_linked_at?->diffForHumans() }}</small>
                        </div>
                        <p class="small text-muted mb-3">
                            You'll receive delivery alerts, broadcast notifications, and payout updates directly on Telegram.
                        </p>
                        <form action="{{ route('rider.profile.telegram.unlink') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                    onclick="return confirm('Disconnect your Telegram account?')">
                                <i class="bx bx-unlink me-1"></i>Disconnect Telegram
                            </button>
                        </form>
                    @else
                        {{-- Not linked --}}
                        <p class="small text-muted mb-3">
                            Connect Telegram to receive delivery broadcasts, pickup alerts, and payout updates without logging in.
                        </p>
                        <button type="button" class="btn btn-primary w-100" id="btn-connect-telegram">
                            <i class="bx bx-send me-1"></i>Connect Telegram
                        </button>

                        {{-- Link modal --}}
                        <div class="modal fade" id="telegramLinkModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bx bx-send me-2 text-primary"></i>Connect Telegram
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">

                                        {{-- Loading state --}}
                                        <div id="tg-loading">
                                            <div class="spinner-border text-primary mb-3" role="status"></div>
                                            <p class="text-muted">Generating your link…</p>
                                        </div>

                                        {{-- Link ready state --}}
                                        <div id="tg-ready" class="d-none">
                                            <div class="mb-3">
                                                <i class="bx bxl-telegram" style="font-size: 3rem; color: #0088cc;"></i>
                                            </div>
                                            <p class="mb-1">Tap the button below to open Telegram and activate the bot.</p>
                                            <p class="small text-muted mb-4">
                                                The link expires in <strong>15 minutes</strong>. You only need to do this once.
                                            </p>
                                            <a href="#" id="tg-deep-link" target="_blank" class="btn btn-primary btn-lg w-100 mb-3">
                                                <i class="bx bxl-telegram me-2"></i>Open in Telegram
                                            </a>
                                            <p class="small text-muted">
                                                After tapping <strong>Start</strong> in Telegram, come back and refresh this page.
                                            </p>
                                        </div>

                                        {{-- Error state --}}
                                        <div id="tg-error" class="d-none">
                                            <i class="bx bx-error-circle text-danger mb-2" style="font-size: 2.5rem;"></i>
                                            <p class="text-danger" id="tg-error-msg">Something went wrong. Please try again.</p>
                                            <button class="btn btn-outline-primary btn-sm" id="btn-retry-tg">
                                                <i class="bx bx-refresh me-1"></i>Try Again
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Profile Forms -->
        <div class="col-lg-8">
            
            <!-- Company Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-building me-2"></i>Company Information
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('rider.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Company Name *</label>
                                <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $rider->full_name) }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $rider->phone_number) }}" required>
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Company ID</label>
                                <input type="text" class="form-control" value="#{{ $rider->id }}" disabled>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Fleet Type/Description</label>
                                <input type="text" name="vehicle_type" class="form-control" value="{{ old('vehicle_type', $rider->vehicle_type) }}" placeholder="e.g., Motorcycles, Vans, Mixed Fleet">
                                <small class="text-muted">Describe your delivery fleet</small>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-credit-card me-2"></i>Bank Information
                    </h6>
                </div>
                <div class="card-body">
                    @if($rider->bank_name)
                    <div class="alert alert-info mb-3">
                        <strong>Current Bank Details:</strong><br>
                        Bank: {{ $rider->bank_name }}<br>
                        Account: {{ $rider->account_number }}<br>
                        Name: {{ $rider->account_name }}
                    </div>
                    @endif
                    
                    <form action="{{ route('rider.profile.bank') }}" method="POST" id="riderBankForm">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            {{-- Bank dropdown --}}
                            <div class="col-md-6">
                                <label class="form-label">Bank Name *</label>
                                <select name="bank_name"
                                        id="riderBankSelect"
                                        class="form-select @error('bank_name') is-invalid @enderror"
                                        required>
                                    <option value="">Loading banks…</option>
                                </select>
                                <input type="hidden" id="riderBankCode">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Account number --}}
                            <div class="col-md-6">
                                <label class="form-label">Account Number *</label>
                                <input type="text"
                                       name="account_number"
                                       id="riderAccountNumber"
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       value="{{ old('account_number', $rider->account_number) }}"
                                       placeholder="0123456789"
                                       maxlength="10"
                                       pattern="[0-9]{10}"
                                       required>
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Account name — auto-filled --}}
                            <div class="col-12">
                                <label class="form-label">Account Name *</label>
                                <div class="input-group">
                                    <input type="text"
                                           name="account_name"
                                           id="riderAccountName"
                                           class="form-control @error('account_name') is-invalid @enderror"
                                           value="{{ old('account_name', $rider->account_name) }}"
                                           placeholder="Auto-filled after verification"
                                           readonly
                                           required>
                                    <span class="input-group-text">
                                        <i class="bx bx-shield-check text-muted" id="riderVerifyIcon"></i>
                                    </span>
                                </div>
                                <div class="form-text" id="riderAccountNameHint">
                                    Select a bank and enter a 10-digit account number to auto-verify.
                                </div>
                                @error('account_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-save me-1"></i>{{ $rider->bank_name ? 'Update' : 'Save' }} Bank Details
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-lock me-2"></i>Security Settings
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('rider.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Current Password *</label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">New Password *</label>
                                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" minlength="8">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password *</label>
                                <input type="password" name="new_password_confirmation" class="form-control">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-key me-1"></i>Update Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@unless($rider->telegram_chat_id)
<script>
(function () {
    const btn        = document.getElementById('btn-connect-telegram');
    const retryBtn   = document.getElementById('btn-retry-tg');
    const modal      = new bootstrap.Modal(document.getElementById('telegramLinkModal'));
    const loading    = document.getElementById('tg-loading');
    const ready      = document.getElementById('tg-ready');
    const error      = document.getElementById('tg-error');
    const errorMsg   = document.getElementById('tg-error-msg');
    const deepLink   = document.getElementById('tg-deep-link');

    function showState(state) {
        loading.classList.add('d-none');
        ready.classList.add('d-none');
        error.classList.add('d-none');
        document.getElementById('tg-' + state).classList.remove('d-none');
    }

    async function fetchLink() {
        showState('loading');
        try {
            const res  = await fetch('{{ route('rider.profile.telegram.link') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
            });
            const json = await res.json();
            if (!res.ok || !json.link) throw new Error(json.message ?? 'Failed to generate link.');
            deepLink.href = json.link;
            showState('ready');
        } catch (e) {
            errorMsg.textContent = e.message ?? 'Something went wrong. Please try again.';
            showState('error');
        }
    }

    btn.addEventListener('click', () => {
        modal.show();
        fetchLink();
    });

    retryBtn.addEventListener('click', fetchLink);
})();
</script>
@endunless

<script>
/* ── Paystack bank account resolver (Rider Profile) ─────────────────────── */
(function () {
    const bankSelect    = document.getElementById('riderBankSelect');
    const bankCodeInput = document.getElementById('riderBankCode');
    const acctInput     = document.getElementById('riderAccountNumber');
    const nameInput     = document.getElementById('riderAccountName');
    const hint          = document.getElementById('riderAccountNameHint');
    const icon          = document.getElementById('riderVerifyIcon');

    const RESOLVE_URL   = '{{ route("bank.resolve") }}';
    const BANK_LIST_URL = '{{ route("bank.list") }}';
    const SAVED_BANK    = '{{ old("bank_name", $rider->bank_name ?? "") }}';
    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let resolveTimer = null;

    const ICON_MAP = {
        ok:      { cls: 'bx bx-shield-check text-success' },
        loading: { cls: 'bx bx-loader-alt text-warning bx-spin' },
        error:   { cls: 'bx bx-shield-x text-danger' },
        idle:    { cls: 'bx bx-shield-check text-muted' },
    };

    function setStatus(state, msg) {
        hint.textContent = msg;
        icon.className   = ICON_MAP[state]?.cls ?? ICON_MAP.idle.cls;
    }

    async function loadBanks() {
        try {
            const res   = await fetch(BANK_LIST_URL, { headers: { 'X-CSRF-TOKEN': CSRF } });
            const json  = await res.json();
            const banks = json.data ?? [];

            bankSelect.innerHTML = '<option value="">— Select Bank —</option>';
            banks.forEach(b => {
                const opt = document.createElement('option');
                opt.value        = b.name;
                opt.dataset.code = b.code;
                opt.textContent  = b.name;
                if (b.name === SAVED_BANK) opt.selected = true;
                bankSelect.appendChild(opt);
            });

            const selected = bankSelect.options[bankSelect.selectedIndex];
            if (selected?.dataset?.code) bankCodeInput.value = selected.dataset.code;
        } catch (e) {
            bankSelect.innerHTML = '<option value="">Failed to load banks — refresh page</option>';
        }
    }

    async function resolveAccount() {
        const accountNumber = acctInput.value.trim();
        const bankCode      = bankCodeInput.value;
        if (accountNumber.length !== 10 || !bankCode) return;

        setStatus('loading', 'Verifying account…');
        nameInput.value = '';

        try {
            const params = new URLSearchParams({ account_number: accountNumber, bank_code: bankCode });
            const res    = await fetch(`${RESOLVE_URL}?${params}`, {
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const json = await res.json();

            if (json.status && json.account_name) {
                nameInput.value = json.account_name;
                setStatus('ok', 'Account verified ✓');
            } else {
                setStatus('error', json.message ?? 'Could not verify account. Check details.');
            }
        } catch (e) {
            setStatus('error', 'Verification failed. Check your connection.');
        }
    }

    acctInput.addEventListener('input', () => {
        clearTimeout(resolveTimer);
        if (acctInput.value.trim().length === 10) {
            resolveTimer = setTimeout(resolveAccount, 500);
        } else {
            nameInput.value = '';
            setStatus('idle', 'Select a bank and enter a 10-digit account number to auto-verify.');
        }
    });

    bankSelect.addEventListener('change', () => {
        const selected = bankSelect.options[bankSelect.selectedIndex];
        bankCodeInput.value = selected?.dataset?.code ?? '';
        clearTimeout(resolveTimer);
        if (acctInput.value.trim().length === 10) {
            resolveTimer = setTimeout(resolveAccount, 300);
        }
    });

    loadBanks();
})();
</script>
@endpush

@endsection
{{-- Account Settings View (resources/views/seller/settings/index.blade.php) --}}
@extends('seller.layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="row">
    <!-- Profile Settings -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Profile Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('seller.settings.profile') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone" class="form-control" 
                                       value="{{ old('phone', $user->phone) }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Business Type</label>
                                <input type="text" name="business_type" class="form-control" 
                                       value="{{ old('business_type', $seller->business_type) }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" 
                                       value="{{ old('address', $seller->address) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" 
                                       value="{{ old('city', $seller->city) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" 
                                       value="{{ old('state', $seller->state) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" 
                                       value="{{ old('postal_code', $seller->postal_code) }}">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" 
                                       value="{{ old('country', $seller->country) }}">
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" class="fs-16"></i> Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bank Information -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Bank Information</h4>
            </div>
            <div class="card-body">
   

                <form action="{{ route('seller.settings.bank') }}" method="POST" id="sellerBankForm">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        {{-- Bank dropdown --}}
                        <div class="col-md-4">
                            <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                            <select name="bank_name"
                                    id="sellerBankSelect"
                                    class="form-select @error('bank_name') is-invalid @enderror"
                                    required>
                                <option value="">Loading banks…</option>
                            </select>
                            {{-- Hidden: store the bank code for the API call --}}
                            <input type="hidden" id="sellerBankCode">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Account number --}}
                        <div class="col-md-4">
                            <label class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="bank_account"
                                   id="sellerAccountNumber"
                                   class="form-control @error('bank_account') is-invalid @enderror"
                                   value="{{ old('bank_account', $seller->bank_account) }}"
                                   placeholder="10-digit account number"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   required>
                            @error('bank_account')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Account holder name — auto-filled by Paystack --}}
                        <div class="col-md-4">
                            <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text"
                                       name="account_holder_name"
                                       id="sellerAccountName"
                                       class="form-control @error('account_holder_name') is-invalid @enderror"
                                       value="{{ old('account_holder_name', $seller->account_holder_name) }}"
                                       placeholder="Auto-filled after verification"
                                       readonly>
                                <span class="input-group-text" id="sellerAccountNameStatus" title="Verification status">
                                    <i data-lucide="shield-check" class="fs-14 text-muted" id="sellerVerifyIcon"></i>
                                </span>
                            </div>
                            <div class="form-text" id="sellerAccountNameHint">
                                Select a bank and enter a 10-digit account number to auto-verify.
                            </div>
                            @error('account_holder_name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" class="fs-16"></i> Update Bank Info
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Change Password</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('seller.settings.password') }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="lock" class="fs-16"></i> Change Password
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!--Telegram-->
    <div class="col-12">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Telegram Notifications</h4>
        </div>
        <div class="card-body">
            @if($seller->telegram_chat_id)
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span class="badge bg-success p-2">
                        <i data-lucide="check" class="fs-14 me-1"></i>Connected
                    </span>
                    <small class="text-muted">Linked {{ $seller->telegram_linked_at?->diffForHumans() }}</small>
                </div>
                <p class="small text-muted mb-3">
                    You'll receive order alerts, payout updates, review notifications, and stock warnings directly on Telegram.
                </p>
                <form action="{{ route('seller.settings.telegram.unlink') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Disconnect your Telegram account?')">
                        <i data-lucide="unlink" class="fs-14 me-1"></i>Disconnect Telegram
                    </button>
                </form>
            @else
                <p class="small text-muted mb-3">
                    Connect Telegram to get real-time alerts for orders, payouts, reviews, and low stock — without needing to log in.
                </p>
                <button type="button" class="btn btn-primary" id="btn-connect-seller-telegram">
                    <i data-lucide="send" class="fs-14 me-1"></i>Connect Telegram
                </button>
            @endif
        </div>
    </div>
</div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         Security & Login Activity
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h4 class="card-title mb-0">
                    <i data-lucide="shield" class="fs-16 me-2"></i>Security &amp; Login Activity
                </h4>
                @php
                    try { $sellerSessionCount = \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', auth('seller')->id())->count(); }
                    catch (\Throwable $e) { $sellerSessionCount = 1; }
                @endphp
                <span class="badge bg-secondary fs-12">
                    {{ $sellerSessionCount }} active {{ \Illuminate\Support\Str::plural('session', $sellerSessionCount) }}
                </span>
            </div>
            <div class="card-body">

                @if(session('security_success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
                    <i data-lucide="circle-check" class="fs-16"></i>
                    {{ session('security_success') }}
                </div>
                @endif

                {{-- ── Revoke Other Sessions ───────────────────────────────── --}}
                <h6 class="fw-semibold mb-2">Revoke Other Sessions</h6>
                <p class="text-muted mb-3" style="font-size:13.5px;">
                    Sign out of all other browsers and devices. Your current session stays active.
                </p>
                @if(auth('seller')->user()->isSocialOnly())
                    <p class="text-muted fst-italic mb-4" style="font-size:13.5px;">
                        <i data-lucide="info" class="fs-14 me-1"></i>
                        Session management is not available for social login accounts.
                    </p>
                @else
                    <form method="POST" action="{{ route('seller.sessions.revoke') }}" class="d-flex gap-2 flex-wrap align-items-start mb-4">
                        @csrf
                        <div>
                            <input type="password" name="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   style="min-width:220px;"
                                   placeholder="Confirm your current password">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="log-out" class="fs-14 me-1"></i>Revoke Other Sessions
                        </button>
                    </form>
                @endif

                <hr class="my-4">

                {{-- ── Login Activity ────────────────────────────────────── --}}
                <h6 class="fw-semibold mb-2">Recent Login Activity</h6>
                <p class="text-muted mb-3" style="font-size:13.5px;">Your last 10 sign-in attempts.</p>
                @php
                    $sellerLoginActivities = \App\Models\LoginActivity::where('user_id', auth('seller')->id())
                        ->where('user_type', 'seller')
                        ->latest('logged_in_at')->limit(10)->get();
                @endphp
                @if($sellerLoginActivities->isEmpty())
                    <p class="text-muted fst-italic" style="font-size:13.5px;">No login activity recorded yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover" style="font-size:13.5px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Date &amp; Time</th>
                                    <th>Device</th>
                                    <th>IP Address</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sellerLoginActivities as $act)
                                <tr>
                                    <td>
                                        {{ $act->logged_in_at->format('d M Y') }}
                                        <br><small class="text-muted">{{ $act->logged_in_at->format('g:ia') }}</small>
                                    </td>
                                    <td>{{ $act->device ?? 'Unknown device' }}</td>
                                    <td style="font-family:monospace; font-size:12px;">{{ $act->ip_address ?? '—' }}</td>
                                    <td>
                                        @if($act->successful)
                                            <span class="badge bg-success">Success</span>
                                        @else
                                            <span class="badge bg-danger"
                                                  title="{{ $act->failure_reason ? str_replace('_',' ',ucfirst($act->failure_reason)) : 'Failed' }}">
                                                Failed
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         Danger Zone — Account Deactivation
    ════════════════════════════════════════════════════════════════════════ --}}
    <div class="col-12">
        <div class="card border-danger">
            <div class="card-header" style="background:#fff5f5; border-color:#f5c6cb;">
                <h4 class="card-title mb-0 text-danger">
                    <i data-lucide="triangle-alert" class="fs-16 me-2"></i>Danger Zone
                </h4>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3" style="font-size:14px;">
                    Deactivating your seller account signs you out immediately and suspends your shop.
                    You have <strong>30 days</strong> to reactivate by logging back in.
                    After that, the account is permanently closed.
                </p>
                <button type="button" class="btn btn-outline-danger"
                        data-bs-toggle="modal" data-bs-target="#sellerDeactivateModal">
                    <i data-lucide="user-x" class="fs-14 me-1"></i>Deactivate Seller Account
                </button>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     Deactivate Account Modal
════════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="sellerDeactivateModal" tabindex="-1"
     aria-labelledby="sellerDeactivateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="border-color:#f5c6cb;">
                <h5 class="modal-title text-danger" id="sellerDeactivateModalLabel">
                    <i data-lucide="user-x" class="fs-16 me-2"></i>Deactivate Seller Account?
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4" style="font-size:14px;">
                    Your shop will be suspended and you'll be signed out immediately.
                    Log back in within <strong>30 days</strong> to reactivate.
                    After that, the account is permanently closed.
                </p>
                <form method="POST" action="{{ route('seller.account.deactivate') }}" id="seller-deactivate-form">
                    @csrf
                    @if(!auth('seller')->user()->isSocialOnly())
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Confirm with your password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Type <strong>DEACTIVATE</strong> to confirm</label>
                        <input type="text" name="confirm_deactivate"
                               class="form-control @error('confirm_deactivate') is-invalid @enderror"
                               placeholder="DEACTIVATE" autocomplete="off">
                        @error('confirm_deactivate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger"
                        onclick="document.getElementById('seller-deactivate-form').submit()">
                    Yes, Deactivate
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
lucide.createIcons();

// Auto-open deactivation modal if it came back with validation errors
@if($errors->has('confirm_deactivate') || ($errors->has('password') && old('confirm_deactivate')))
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('sellerDeactivateModal'));
    modal.show();
});
@endif

/* ── Paystack bank account resolver ─────────────────────────────────────── */
(function () {
    const bankSelect   = document.getElementById('sellerBankSelect');
    const bankCodeInput= document.getElementById('sellerBankCode');
    const acctInput    = document.getElementById('sellerAccountNumber');
    const nameInput    = document.getElementById('sellerAccountName');
    const hint         = document.getElementById('sellerAccountNameHint');
    const icon         = document.getElementById('sellerVerifyIcon');

    const RESOLVE_URL  = '{{ route("bank.resolve") }}';
    const BANK_LIST_URL= '{{ route("bank.list") }}';
    const SAVED_BANK   = '{{ old("bank_name", $seller->bank_name ?? "") }}';
    const CSRF         = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let resolveTimer = null;

    /* Load banks from Paystack via our proxy */
    async function loadBanks() {
        try {
            const res  = await fetch(BANK_LIST_URL, { headers: { 'X-CSRF-TOKEN': CSRF } });
            const json = await res.json();
            const banks = json.data ?? [];

            bankSelect.innerHTML = '<option value="">— Select Bank —</option>';
            banks.forEach(b => {
                const opt = document.createElement('option');
                opt.value         = b.name;
                opt.dataset.code  = b.code;
                opt.textContent   = b.name;
                if (b.name === SAVED_BANK) opt.selected = true;
                bankSelect.appendChild(opt);
            });

            // Restore the bank code for the pre-selected bank
            const selected = bankSelect.options[bankSelect.selectedIndex];
            if (selected && selected.dataset.code) {
                bankCodeInput.value = selected.dataset.code;
            }
        } catch (e) {
            bankSelect.innerHTML = '<option value="">Failed to load banks — refresh page</option>';
        }
    }

    function setStatus(state, msg) {
        hint.textContent = msg;
        const iconEl = document.getElementById('sellerVerifyIcon');
        iconEl.setAttribute('data-lucide',
            state === 'ok'      ? 'shield-check' :
            state === 'loading' ? 'loader'        :
            state === 'error'   ? 'shield-x'      : 'shield-check'
        );
        iconEl.className = 'fs-14 ' + (
            state === 'ok'      ? 'text-success' :
            state === 'loading' ? 'text-warning'  :
            state === 'error'   ? 'text-danger'   : 'text-muted'
        );
        lucide.createIcons();
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
                nameInput.value = '';
                setStatus('error', json.message ?? 'Could not verify account. Check the details and try again.');
            }
        } catch (e) {
            setStatus('error', 'Verification failed. Please check your connection.');
        }
    }

    /* Trigger resolve when account number reaches 10 digits */
    acctInput.addEventListener('input', () => {
        clearTimeout(resolveTimer);
        if (acctInput.value.trim().length === 10) {
            resolveTimer = setTimeout(resolveAccount, 500);
        } else {
            nameInput.value = '';
            setStatus('', 'Select a bank and enter a 10-digit account number to auto-verify.');
        }
    });

    /* Trigger resolve when bank changes (if account number already filled) */
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

@push('scripts')
@unless($seller->telegram_chat_id)
<script>
(function () {
    const btn      = document.getElementById('btn-connect-seller-telegram');
    const LINK_URL = '{{ route("seller.settings.telegram.link") }}';
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
 
    btn.addEventListener('click', async () => {
        btn.disabled = true;
        btn.textContent = 'Generating link…';
        try {
            const res  = await fetch(LINK_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message ?? 'Failed');
            window.open(json.link, '_blank');
            btn.textContent = 'Opened Telegram — tap Start, then refresh this page.';
        } catch (e) {
            btn.disabled = false;
            btn.textContent = 'Connect Telegram';
            alert('Error: ' + e.message);
        }
    });
})();
</script>
@endunless
@endpush
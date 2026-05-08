@extends('admin.layouts.app')

@section('title', 'Create Delivery Company')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Create New Delivery Company</h4>
                <p class="text-muted mb-0">Add a new delivery company to the system</p>
            </div>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('admin.companies.store') }}" method="POST">
                @csrf
                
                <!-- Company Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Company Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Company Name *</label>
                                <input type="text" 
                                       name="full_name" 
                                       class="form-control @error('full_name') is-invalid @enderror" 
                                       value="{{ old('full_name') }}" 
                                       placeholder="e.g., Swift Logistics"
                                       required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       placeholder="company@example.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">This will be used for login</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password *</label>
                                <input type="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="••••••••"
                                       required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" 
                                       name="phone_number" 
                                       class="form-control @error('phone_number') is-invalid @enderror" 
                                       value="{{ old('phone_number') }}" 
                                       placeholder="08012345678"
                                       required>
                                @error('phone_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Fleet Type / Description</label>
                                <input type="text" 
                                       name="vehicle_type" 
                                       class="form-control @error('vehicle_type') is-invalid @enderror" 
                                       value="{{ old('vehicle_type') }}" 
                                       placeholder="e.g., 5 motorcycles, 2 vans">
                                @error('vehicle_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Information (Optional) -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Bank Information <span class="text-muted fw-normal fs-6">(Optional)</span></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <select name="bank_name"
                                        id="adminCreateBankSelect"
                                        class="form-select @error('bank_name') is-invalid @enderror">
                                    <option value="">Loading banks…</option>
                                </select>
                                <input type="hidden" id="adminCreateBankCode">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Account Number</label>
                                <input type="text"
                                       name="account_number"
                                       id="adminCreateAccountNumber"
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       value="{{ old('account_number') }}"
                                       placeholder="0123456789"
                                       maxlength="10">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Account Name</label>
                                <div class="input-group">
                                    <input type="text"
                                           name="account_name"
                                           id="adminCreateAccountName"
                                           class="form-control @error('account_name') is-invalid @enderror"
                                           value="{{ old('account_name') }}"
                                           placeholder="Auto-filled after verification"
                                           readonly>
                                    <span class="input-group-text">
                                        <i class="bx bx-shield-check text-muted" id="adminCreateVerifyIcon"></i>
                                    </span>
                                </div>
                                <div class="form-text" id="adminCreateAccountNameHint">
                                    Enter account number &amp; select bank to auto-verify.
                                </div>
                                @error('account_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-label-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Create Company
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bx bx-info-circle me-2"></i>Information</h6>
                    
                    <div class="alert alert-info">
                        <strong>Default Status:</strong><br>
                        New companies are automatically:
                        <ul class="mb-0 mt-2">
                            <li>Verified ✓</li>
                            <li>Active ✓</li>
                            <li>Ready to receive deliveries ✓</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <strong>Login Credentials:</strong><br>
                        The company will use the email and password provided here to log in to the company portal.
                    </div>

                    <h6 class="mt-4 mb-2">What happens next?</h6>
                    <ol class="ps-3">
                        <li>Company account created</li>
                        <li>Login credentials ready</li>
                        <li>Can receive delivery broadcasts</li>
                        <li>Can accept and manage deliveries</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
/* ── Paystack bank account resolver (Admin Create Company) ─────────────── */
(function () {
    const bankSelect    = document.getElementById('adminCreateBankSelect');
    const bankCodeInput = document.getElementById('adminCreateBankCode');
    const acctInput     = document.getElementById('adminCreateAccountNumber');
    const nameInput     = document.getElementById('adminCreateAccountName');
    const hint          = document.getElementById('adminCreateAccountNameHint');
    const icon          = document.getElementById('adminCreateVerifyIcon');

    const RESOLVE_URL   = '{{ route("bank.resolve") }}';
    const BANK_LIST_URL = '{{ route("bank.list") }}';
    const SAVED_BANK    = '{{ old("bank_name", "") }}';
    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let resolveTimer = null;

    const ICON_MAP = {
        ok:      'bx bx-shield-check text-success',
        loading: 'bx bx-loader-alt text-warning bx-spin',
        error:   'bx bx-shield-x text-danger',
        idle:    'bx bx-shield-check text-muted',
    };

    function setStatus(state, msg) {
        hint.textContent = msg;
        icon.className   = ICON_MAP[state] ?? ICON_MAP.idle;
    }

    async function loadBanks() {
        try {
            const res   = await fetch(BANK_LIST_URL, { headers: { 'X-CSRF-TOKEN': CSRF } });
            const json  = await res.json();
            bankSelect.innerHTML = '<option value="">— Select Bank (optional) —</option>';
            (json.data ?? []).forEach(b => {
                const opt = document.createElement('option');
                opt.value        = b.name;
                opt.dataset.code = b.code;
                opt.textContent  = b.name;
                if (b.name === SAVED_BANK) opt.selected = true;
                bankSelect.appendChild(opt);
            });
            const sel = bankSelect.options[bankSelect.selectedIndex];
            if (sel?.dataset?.code) bankCodeInput.value = sel.dataset.code;
        } catch {
            bankSelect.innerHTML = '<option value="">Failed to load banks — refresh</option>';
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
                setStatus('error', json.message ?? 'Could not verify account.');
            }
        } catch {
            setStatus('error', 'Verification failed. Check your connection.');
        }
    }

    acctInput.addEventListener('input', () => {
        clearTimeout(resolveTimer);
        if (acctInput.value.trim().length === 10) {
            resolveTimer = setTimeout(resolveAccount, 500);
        } else {
            nameInput.value = '';
            setStatus('idle', 'Enter account number & select bank to auto-verify.');
        }
    });

    bankSelect.addEventListener('change', () => {
        const sel = bankSelect.options[bankSelect.selectedIndex];
        bankCodeInput.value = sel?.dataset?.code ?? '';
        clearTimeout(resolveTimer);
        if (acctInput.value.trim().length === 10) resolveTimer = setTimeout(resolveAccount, 300);
    });

    loadBanks();
})();
</script>
@endpush
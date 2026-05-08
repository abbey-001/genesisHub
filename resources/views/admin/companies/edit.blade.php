@extends('admin.layouts.app')

@section('title', 'Edit Company')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Edit Company: {{ $company->full_name }}</h4>
                <p class="text-muted mb-0">Update company information</p>
            </div>
            <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-label-secondary">
                <i class="bx bx-arrow-back me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('admin.companies.update', $company) }}" method="POST">
                @csrf
                @method('PUT')
                
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
                                       value="{{ old('full_name', $company->full_name) }}" 
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
                                       value="{{ old('email', $company->user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Used for login</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Leave blank to keep current">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Only fill if changing password</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="text" 
                                       name="phone_number" 
                                       class="form-control @error('phone_number') is-invalid @enderror" 
                                       value="{{ old('phone_number', $company->phone_number) }}" 
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
                                       value="{{ old('vehicle_type', $company->vehicle_type) }}" 
                                       placeholder="e.g., 5 motorcycles, 2 vans">
                                @error('vehicle_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Bank Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Bank Name</label>
                                <select name="bank_name"
                                        id="adminEditBankSelect"
                                        class="form-select @error('bank_name') is-invalid @enderror">
                                    <option value="">Loading banks…</option>
                                </select>
                                <input type="hidden" id="adminEditBankCode">
                                @error('bank_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Account Number</label>
                                <input type="text"
                                       name="account_number"
                                       id="adminEditAccountNumber"
                                       class="form-control @error('account_number') is-invalid @enderror"
                                       value="{{ old('account_number', $company->account_number) }}"
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
                                           id="adminEditAccountName"
                                           class="form-control @error('account_name') is-invalid @enderror"
                                           value="{{ old('account_name', $company->account_name) }}"
                                           placeholder="Auto-filled after verification"
                                           readonly>
                                    <span class="input-group-text">
                                        <i class="bx bx-shield-check text-muted" id="adminEditVerifyIcon"></i>
                                    </span>
                                </div>
                                <div class="form-text" id="adminEditAccountNameHint">
                                    @if($company->account_name)
                                        Current: <strong>{{ $company->account_name }}</strong>. Change account number or bank to re-verify.
                                    @else
                                        Enter account number &amp; select bank to auto-verify.
                                    @endif
                                </div>
                                @error('account_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-label-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save me-1"></i>Update Company
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Company Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bx bx-info-circle me-2"></i>Company Status</h6>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Current Status</small>
                        @if($company->is_active && $company->is_verified)
                            <span class="badge bg-success">Active & Verified</span>
                        @elseif(!$company->is_verified)
                            <span class="badge bg-warning">Pending Verification</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Joined</small>
                        <strong>{{ $company->created_at->format('M d, Y') }}</strong>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block">Active Deliveries</small>
                        <strong>{{ $company->activeDeliveries()->count() }}</strong>
                    </div>

                    <div class="mb-0">
                        <small class="text-muted d-block">Total Completed</small>
                        <strong>{{ $company->completed_deliveries }}</strong>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bx bx-cog me-2"></i>Quick Actions</h6>
                    
                    @if($company->is_active)
                        <button class="btn btn-warning w-100 mb-2" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i class="bx bx-error-circle me-1"></i>Suspend Company
                        </button>
                    @else
                        <form action="{{ route('admin.companies.activate', $company) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="bx bx-check-circle me-1"></i>Activate Company
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.companies.deliveries', $company) }}" class="btn btn-label-primary w-100 mb-2">
                        <i class="bx bx-package me-1"></i>View Deliveries
                    </a>

                    <a href="{{ route('admin.companies.earnings', $company) }}" class="btn btn-label-success w-100">
                        <i class="bx bx-money me-1"></i>View Earnings
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.companies.suspend', $company) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This will suspend the company and reassign active deliveries.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Suspension *</label>
                        <textarea name="reason" class="form-control" rows="3" required placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Suspend Company</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ── Paystack bank account resolver (Admin Edit Company) ───────────────── */
(function () {
    const bankSelect    = document.getElementById('adminEditBankSelect');
    const bankCodeInput = document.getElementById('adminEditBankCode');
    const acctInput     = document.getElementById('adminEditAccountNumber');
    const nameInput     = document.getElementById('adminEditAccountName');
    const hint          = document.getElementById('adminEditAccountNameHint');
    const icon          = document.getElementById('adminEditVerifyIcon');

    const RESOLVE_URL   = '{{ route("bank.resolve") }}';
    const BANK_LIST_URL = '{{ route("bank.list") }}';
    const SAVED_BANK    = '{{ old("bank_name", $company->bank_name ?? "") }}';
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
            bankSelect.innerHTML = '<option value="">— Select Bank —</option>';
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
{{-- resources/views/seller/payouts/settings.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Payout Settings')

@section('content')
<div class="container-xxl">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">⚙️ Withdrawal Settings</h4>
            <p class="text-muted mb-0">Configure how and when you receive your earnings</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-left" class="fs-16"></i> Back to Wallet
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Settings Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">💰 Withdrawal Preferences</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.payouts.settings.update') }}" method="POST">
                        @csrf
                        
                        <!-- Minimum Payout Amount -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Minimum Withdrawal Amount <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" 
                                       name="minimum_payout" 
                                       class="form-control @error('minimum_payout') is-invalid @enderror" 
                                       value="{{ old('minimum_payout', $settings->minimum_payout ?? 10.00) }}"
                                       min="10"
                                       step="0.01"
                                       required>
                            </div>
                            <div class="form-text">
                                <i data-lucide="info" class="fs-14"></i>
                                The smallest amount you can withdraw at once (minimum ₦10.00)
                            </div>
                            @error('minimum_payout')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Preferred Payout Method -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Preferred Withdrawal Method <span class="text-danger">*</span>
                            </label>
                            
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <div class="form-check form-check-inline card p-3 {{ old('preferred_method', $settings->preferred_method ?? '') === 'bank_transfer' ? 'border-success' : '' }}" style="width: 100%;">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="preferred_method" 
                                               id="method_bank" 
                                               value="bank_transfer"
                                               {{ old('preferred_method', $settings->preferred_method ?? 'bank_transfer') === 'bank_transfer' ? 'checked' : '' }}
                                               required>
                                        <label class="form-check-label w-100" for="method_bank">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>🏦 Bank Transfer</strong>
                                                    <div class="text-muted small">Direct deposit to your bank account</div>
                                                </div>
                                                <span class="badge bg-success">FREE</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check card p-3 {{ old('preferred_method', $settings->preferred_method ?? '') === 'paypal' ? 'border-primary' : '' }}">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="preferred_method" 
                                               id="method_paypal" 
                                               value="paypal"
                                               {{ old('preferred_method', $settings->preferred_method ?? '') === 'paypal' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_paypal">
                                            <div>
                                                <strong>💳 PayPal</strong>
                                                <div class="text-muted small">Fast international transfers</div>
                                                <span class="badge bg-warning text-dark mt-1">2.9% + ₦0.30 fee</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-check card p-3 {{ old('preferred_method', $settings->preferred_method ?? '') === 'stripe' ? 'border-primary' : '' }}">
                                        <input class="form-check-input" 
                                               type="radio" 
                                               name="preferred_method" 
                                               id="method_stripe" 
                                               value="stripe"
                                               {{ old('preferred_method', $settings->preferred_method ?? '') === 'stripe' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_stripe">
                                            <div>
                                                <strong>💰 Stripe</strong>
                                                <div class="text-muted small">Quick processing</div>
                                                <span class="badge bg-info mt-1">2.5% fee</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            @error('preferred_method')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hold Period -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Funds Hold Period <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" 
                                       name="hold_period_days" 
                                       class="form-control @error('hold_period_days') is-invalid @enderror" 
                                       value="{{ old('hold_period_days', $settings->hold_period_days ?? 7) }}"
                                       min="2"
                                       max="30"
                                       required>
                                <span class="input-group-text">days</span>
                            </div>
                            <div class="form-text">
                                <i data-lucide="clock" class="fs-14"></i>
                                How long to hold earnings after a sale before they become available for withdrawal.
                                <br><strong>Set to 2 days</strong> to make funds available 2 days after delivery.
                                <br><strong>Recommended: 7 days</strong> to protect against refunds and chargebacks.
                            </div>
                            @error('hold_period_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Advanced Options -->
                        <div class="mb-3">
                            <button class="btn btn-link text-decoration-none p-0" type="button" data-bs-toggle="collapse" data-bs-target="#advancedOptions">
                                <i data-lucide="settings" class="fs-16"></i> Advanced Options (Optional)
                            </button>
                        </div>

                        <div class="collapse" id="advancedOptions">
                            <!-- Payout Schedule -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">Automatic Withdrawal Schedule</label>
                                <select name="payout_schedule" 
                                        class="form-select @error('payout_schedule') is-invalid @enderror" 
                                        id="payoutSchedule"
                                        required>
                                    <option value="manual" {{ old('payout_schedule', $settings->payout_schedule ?? 'manual') === 'manual' ? 'selected' : '' }}>
                                        Manual - I'll request withdrawals myself (Recommended)
                                    </option>
                                    <option value="weekly" {{ old('payout_schedule', $settings->payout_schedule ?? '') === 'weekly' ? 'selected' : '' }}>
                                        Weekly - Every week on a specific day
                                    </option>
                                    <option value="monthly" {{ old('payout_schedule', $settings->payout_schedule ?? '') === 'monthly' ? 'selected' : '' }}>
                                        Monthly - On a specific day of the month
                                    </option>
                                </select>
                                <div class="form-text">
                                    <i data-lucide="calendar" class="fs-14"></i>
                                    How often should we automatically request withdrawals for you?
                                </div>
                                @error('payout_schedule')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Payout Day (conditional) -->
                            <div id="payoutDayWrapper" style="display: none;">
                                <div class="mb-4">
                                    <label class="form-label fw-bold" id="payoutDayLabel">Withdrawal Day</label>
                                    <select name="payout_day" 
                                            class="form-select @error('payout_day') is-invalid @enderror"
                                            id="payoutDay">
                                        <!-- Will be populated by JavaScript -->
                                    </select>
                                    <div class="form-text" id="payoutDayHelp"></div>
                                    @error('payout_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Auto-Payout Section -->
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <h6 class="mb-3">
                                        <i data-lucide="zap" class="fs-16"></i> Auto-Withdrawal
                                    </h6>
                                    
                                    <!-- Enable Auto-Payout -->
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               name="auto_payout_enabled" 
                                               id="autoPayoutEnabled"
                                               value="1"
                                               {{ old('auto_payout_enabled', $settings->auto_payout_enabled ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold" for="autoPayoutEnabled">
                                            Enable automatic withdrawals when balance reaches a threshold
                                        </label>
                                    </div>

                                    <!-- Auto-Payout Threshold -->
                                    <div id="autoPayoutSettings" style="display: none;">
                                        <div class="alert alert-info py-2 mb-3">
                                            <small>
                                                <i data-lucide="info" class="fs-14"></i>
                                                When enabled, a withdrawal will automatically be requested when your available balance reaches this amount.
                                            </small>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Threshold Amount</label>
                                            <div class="input-group">
                                                <span class="input-group-text">₦</span>
                                                <input type="number" 
                                                       name="auto_payout_threshold" 
                                                       class="form-control @error('auto_payout_threshold') is-invalid @enderror" 
                                                       value="{{ old('auto_payout_threshold', $settings->auto_payout_threshold ?? '') }}"
                                                       min="10"
                                                       step="0.01"
                                                       placeholder="e.g., 10000">
                                            </div>
                                            <div class="form-text">Example: Set to ₦10,000 to auto-withdraw when you reach that amount</div>
                                            @error('auto_payout_threshold')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i data-lucide="save" class="fs-16"></i> Save Settings
                            </button>
                            <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Current Settings Summary -->
            @if(isset($settings->seller_id))
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">📋 Current Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Minimum Withdrawal</small>
                        <strong>₦{{ number_format($settings->minimum_payout, 2) }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Preferred Method</small>
                        <strong>
                            @if($settings->preferred_method === 'bank_transfer')
                                🏦 Bank Transfer
                            @elseif($settings->preferred_method === 'paypal')
                                💳 PayPal
                            @elseif($settings->preferred_method === 'stripe')
                                💰 Stripe
                            @endif
                        </strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Hold Period</small>
                        <strong>{{ $settings->hold_period_days }} days</strong>
                    </div>
                    @if($settings->auto_payout_enabled)
                    <div>
                        <small class="text-muted d-block">Auto-Withdrawal</small>
                        <span class="badge bg-success">Enabled at ₦{{ number_format($settings->auto_payout_threshold, 2) }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Help Card -->
            <div class="card bg-primary text-white shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="text-white mb-3">
                        <i data-lucide="help-circle" class="fs-16"></i> Need Help?
                    </h6>
                    <p class="small mb-3">
                        Understanding your withdrawal settings can help you manage your cash flow better.
                    </p>
                    <ul class="small ps-3 mb-0">
                        <li class="mb-2"><strong>Minimum withdrawal:</strong> The smallest amount you can take out at once</li>
                        <li class="mb-2"><strong>Hold period:</strong> Protects you from refunds and disputes</li>
                        <li class="mb-2"><strong>Auto-withdrawal:</strong> Set it and forget it!</li>
                        <li><strong>Preferred method:</strong> Choose what works best for you</li>
                    </ul>
                </div>
            </div>

            <!-- Fee Comparison -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">💳 Withdrawal Fees</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>🏦 Bank Transfer</strong>
                            <span class="badge bg-success">FREE</span>
                        </div>
                        <small class="text-muted">Best for most sellers</small>
                    </div>
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>💳 PayPal</strong>
                            <span class="badge bg-warning text-dark">2.9% + ₦0.30</span>
                        </div>
                        <small class="text-muted">Good for international payments</small>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong>💰 Stripe</strong>
                            <span class="badge bg-info">2.5%</span>
                        </div>
                        <small class="text-muted">Fast processing</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
lucide.createIcons();

document.addEventListener('DOMContentLoaded', function() {
    const scheduleSelect = document.getElementById('payoutSchedule');
    const dayWrapper = document.getElementById('payoutDayWrapper');
    const daySelect = document.getElementById('payoutDay');
    const dayLabel = document.getElementById('payoutDayLabel');
    const dayHelp = document.getElementById('payoutDayHelp');
    const autoPayoutCheckbox = document.getElementById('autoPayoutEnabled');
    const autoPayoutSettings = document.getElementById('autoPayoutSettings');
    
    // Handle payout schedule change
    function updatePayoutDay() {
        const schedule = scheduleSelect.value;
        
        if (schedule === 'manual') {
            dayWrapper.style.display = 'none';
            daySelect.required = false;
        } else {
            dayWrapper.style.display = 'block';
            daySelect.required = true;
            
            // Clear existing options
            daySelect.innerHTML = '<option value="">Select day...</option>';
            
            if (schedule === 'weekly') {
                dayLabel.textContent = 'Day of Week';
                dayHelp.innerHTML = '<i data-lucide="calendar" class="fs-14"></i> Which day of the week?';
                
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                days.forEach((day, index) => {
                    const option = document.createElement('option');
                    option.value = index + 1;
                    option.textContent = day;
                    option.selected = {{ $settings->payout_day ?? 1 }} == (index + 1);
                    daySelect.appendChild(option);
                });
            } else if (schedule === 'monthly') {
                dayLabel.textContent = 'Day of Month';
                dayHelp.innerHTML = '<i data-lucide="calendar" class="fs-14"></i> Which day of the month? (1-28)';
                
                for (let i = 1; i <= 28; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    const suffix = i === 1 ? 'st' : i === 2 ? 'nd' : i === 3 ? 'rd' : 'th';
                    option.textContent = i + suffix + ' of the month';
                    option.selected = {{ $settings->payout_day ?? 1 }} == i;
                    daySelect.appendChild(option);
                }
            }
            
            lucide.createIcons();
        }
    }
    
    // Handle auto-payout toggle
    function toggleAutoPayout() {
        if (autoPayoutCheckbox.checked) {
            autoPayoutSettings.style.display = 'block';
        } else {
            autoPayoutSettings.style.display = 'none';
        }
        lucide.createIcons();
    }
    
    // Initialize
    updatePayoutDay();
    toggleAutoPayout();
    
    // Event listeners
    scheduleSelect.addEventListener('change', updatePayoutDay);
    autoPayoutCheckbox.addEventListener('change', toggleAutoPayout);
});
</script>
@endpush
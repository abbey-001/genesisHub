{{-- resources/views/seller/payouts/index.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Wallet & Payouts')

@section('content')
<div class="container-xxl">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">💰 My Wallet</h4>
            <p class="text-muted mb-0">Manage your earnings and withdraw funds</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('seller.payouts.transactions') }}" class="btn btn-outline-primary btn-sm me-2">
                <i data-lucide="list" class="fs-16"></i> All Transactions
            </a>
            <a href="{{ route('seller.payouts.settings') }}" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="settings" class="fs-16"></i> Settings
            </a>
        </div>
    </div>

    <!-- Wallet Balance Cards -->
    <div class="row g-3 mb-4">
        <!-- Available Balance -->
        <div class="col-md-4">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <p class="text-muted mb-1 small">💵 Available Balance</p>
                            <h2 class="fw-bold text-success mb-1">₦{{ number_format($walletSummary['balance'], 2) }}</h2>
                            <small class="text-muted">Ready to withdraw</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i data-lucide="wallet" class="text-success" style="width: 32px; height: 32px;"></i>
                        </div>
                    </div>
                    
                    @if($walletSummary['balance'] >= ($settings->minimum_payout ?? 10))
                        <button type="button" class="btn btn-success w-100 mt-2" 
                                data-bs-toggle="modal" data-bs-target="#requestPayoutModal">
                            <i data-lucide="send" class="fs-16"></i> Withdraw Now
                        </button>
                    @else
                        <div class="alert alert-warning py-2 px-3 mb-0 mt-2">
                            <small>
                                <i data-lucide="info" class="fs-14"></i>
                                Minimum: ₦{{ number_format($settings->minimum_payout ?? 10, 2) }}
                            </small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Balance -->
        <div class="col-md-4">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <p class="text-muted mb-1 small">⏳ Pending Balance</p>
                            <h2 class="fw-bold text-warning mb-1">₦{{ number_format($walletSummary['pending_balance'], 2) }}</h2>
                            <small class="text-muted">In {{ $settings->hold_period_days ?? 7 }}-day hold</small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                            <i data-lucide="clock" class="text-warning" style="width: 32px; height: 32px;"></i>
                        </div>
                    </div>
                    
                    @if($walletSummary['pending_balance'] > 0)
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 60%"></div>
                        </div>
                        <small class="text-muted">Will be available soon</small>
                    @else
                        <small class="text-muted d-block mt-2">No pending funds</small>
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Earned -->
        <div class="col-md-4">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <p class="text-muted mb-1 small">📈 Total Earned</p>
                            <h2 class="fw-bold text-info mb-1">₦{{ number_format($walletSummary['total_earned'], 2) }}</h2>
                            <small class="text-muted">All-time earnings</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-2 rounded">
                            <i data-lucide="trending-up" class="text-info" style="width: 32px; height: 32px;"></i>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-2">
                        <small class="text-muted">Withdrawn: ₦{{ number_format($walletSummary['total_withdrawn'], 2) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    @if($walletSummary['reserved_balance'] > 0 || $walletSummary['has_negative_balance'])
    <div class="row g-3 mb-4">
        @if($walletSummary['reserved_balance'] > 0)
        <div class="col-md-6">
            <div class="alert alert-warning d-flex align-items-center mb-0">
                <i data-lucide="lock" class="me-2"></i>
                <div>
                    <strong>Reserved:</strong> ₦{{ number_format($walletSummary['reserved_balance'], 2) }}
                    <br><small>Funds held for pending disputes or refunds</small>
                </div>
            </div>
        </div>
        @endif
        
        @if($walletSummary['has_negative_balance'])
        <div class="col-md-6">
            <div class="alert alert-danger d-flex align-items-center mb-0">
                <i data-lucide="alert-circle" class="me-2"></i>
                <div>
                    <strong>Attention:</strong> Negative balance detected
                    <br><small>Please contact support to resolve this issue</small>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Withdrawal Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">💸 Withdrawal Requests</h5>
                        <small class="text-muted">Track your payout requests</small>
                    </div>
                    <span class="badge bg-secondary">{{ $payouts->total() }} Total</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>You Get</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace">#{{ $payout->id }}</span>
                                </td>
                                <td>
                                    <div>{{ $payout->requested_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $payout->requested_at->format('h:i A') }}</small>
                                </td>
                                <td class="fw-bold">₦{{ number_format($payout->amount, 2) }}</td>
                                <td class="text-danger">
                                    @if($payout->fee_amount > 0)
                                        -₦{{ number_format($payout->fee_amount, 2) }}
                                    @else
                                        <span class="text-success">FREE</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success">₦{{ number_format($payout->net_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $payout->payout_method_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payout->status_badge }}">
                                        {{ $payout->status_label }}
                                    </span>
                                    
                                    @if($payout->isCompleted() && $payout->processed_at)
                                        <br><small class="text-muted">{{ $payout->processed_at->diffForHumans() }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('seller.payouts.show', $payout) }}" 
                                           class="btn btn-outline-primary"
                                           title="View Details">
                                            <i data-lucide="eye" class="fs-14"></i>
                                        </a>
                                        
                                        @if($payout->canBeCancelled())
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                onclick="cancelPayout({{ $payout->id }})"
                                                title="Cancel">
                                            <i data-lucide="x" class="fs-14"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i data-lucide="inbox" style="width: 48px; height: 48px;"></i>
                                        <p class="mt-2 mb-0">No withdrawal requests yet</p>
                                        <small>Click "Withdraw Now" to request your first payout</small>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payouts->hasPages())
                <div class="card-footer bg-white">
                    {{ $payouts->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>

</div>

<!-- Request Payout Modal -->
<div class="modal fade" id="requestPayoutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">💸 Request Withdrawal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('seller.payouts.request') }}" method="POST" id="payoutForm">
                @csrf
                <div class="modal-body">
                    <!-- Balance Info -->
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>💰 Available Balance:</strong>
                            <span class="fw-bold">₦{{ number_format($walletSummary['balance'], 2) }}</span>
                        </div>
                        @if($walletSummary['pending_balance'] > 0)
                        <div class="d-flex justify-content-between">
                            <small class="text-muted">⏳ Pending (on hold):</small>
                            <small class="text-muted">₦{{ number_format($walletSummary['pending_balance'], 2) }}</small>
                        </div>
                        @endif
                    </div>

                    <!-- Amount Input -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">How much do you want to withdraw? <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" 
                                   name="amount" 
                                   id="payoutAmount"
                                   class="form-control form-control-lg" 
                                   min="{{ $settings->minimum_payout ?? 10 }}" 
                                   max="{{ $walletSummary['balance'] }}" 
                                   step="0.01" 
                                   placeholder="Enter amount"
                                   required>
                        </div>
                        <small class="text-muted">
                            Minimum: ₦{{ number_format($settings->minimum_payout ?? 10, 2) }} • 
                            Maximum: ₦{{ number_format($walletSummary['balance'], 2) }}
                        </small>
                        
                        <!-- Quick Amount Buttons -->
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAmount({{ $walletSummary['balance'] * 0.25 }})">25%</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAmount({{ $walletSummary['balance'] * 0.50 }})">50%</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAmount({{ $walletSummary['balance'] * 0.75 }})">75%</button>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="setAmount({{ $walletSummary['balance'] }})">All</button>
                        </div>
                    </div>

                    <!-- Method Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Withdrawal Method <span class="text-danger">*</span></label>
                        <select name="payout_method" id="payoutMethod" class="form-select" required>
                            <option value="">Select method...</option>
                            <option value="bank_transfer" selected>🏦 Bank Transfer (FREE - Recommended)</option>
                            <option value="paypal">💳 PayPal (2.9% + ₦0.30 fee)</option>
                            <option value="stripe">💰 Stripe (2.5% fee)</option>
                        </select>
                    </div>

                    <!-- Fee Preview -->
                    <div id="feePreview" class="alert alert-light border" style="display: none;">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Withdrawal Amount:</span>
                            <strong id="previewAmount">₦0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Processing Fee:</span>
                            <strong id="previewFee" class="text-danger">₦0.00</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <strong>You Will Receive:</strong>
                            <strong id="previewNet" class="text-success fs-5">₦0.00</strong>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label class="form-label">Additional Notes (Optional)</label>
                        <textarea name="notes" 
                                  class="form-control" 
                                  rows="2" 
                                  placeholder="Any special instructions or notes..."></textarea>
                    </div>

                    <!-- Processing Info -->
                    <div class="alert alert-warning mb-0">
                        <small>
                            <i data-lucide="info" class="fs-14"></i>
                            <strong>Processing Time:</strong> Withdrawals are typically processed within 3-5 business days.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i data-lucide="send" class="fs-16"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Payout Form (hidden) -->
<form id="cancelPayoutForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
lucide.createIcons();

// Fee calculation
function updateFeePreview() {
    const amount = parseFloat(document.getElementById('payoutAmount').value) || 0;
    const method = document.getElementById('payoutMethod').value;
    
    if (amount > 0 && method) {
        let fee = 0;
        
        switch(method) {
            case 'bank_transfer':
                fee = 0;
                break;
            case 'paypal':
                fee = (amount * 0.029) + 0.30;
                break;
            case 'stripe':
                fee = amount * 0.025;
                break;
        }
        
        const net = amount - fee;
        
        document.getElementById('previewAmount').textContent = '₦' + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('previewFee').textContent = '₦' + fee.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('previewNet').textContent = '₦' + net.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('feePreview').style.display = 'block';
    } else {
        document.getElementById('feePreview').style.display = 'none';
    }
}

// Set quick amount
function setAmount(amount) {
    document.getElementById('payoutAmount').value = amount.toFixed(2);
    updateFeePreview();
}

// Event listeners
document.getElementById('payoutAmount')?.addEventListener('input', updateFeePreview);
document.getElementById('payoutMethod')?.addEventListener('change', updateFeePreview);

// Form submission
document.getElementById('payoutForm')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
});

// Cancel payout
function cancelPayout(payoutId) {
    if (confirm('Are you sure you want to cancel this withdrawal? The funds will be returned to your wallet.')) {
        const form = document.getElementById('cancelPayoutForm');
        form.action = `/seller/payouts/${payoutId}/cancel`;
        form.submit();
    }
}

// Reinitialize Lucide icons when modal opens
document.getElementById('requestPayoutModal')?.addEventListener('shown.bs.modal', function () {
    lucide.createIcons();
});
</script>
@endpush
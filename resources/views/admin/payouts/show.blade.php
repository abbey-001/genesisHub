@extends('admin.layouts.app')

@section('title', 'Payout Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.finance.payouts.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Payouts
                </a>
            </div>
            <h4 class="page-title">Payout #{{ $payout->id }}</h4>
            <p class="text-muted">Requested {{ $payout->requested_at->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        <i data-lucide="alert-circle" class="me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Status + Amount Hero -->
@php
    $statusBadge = [
        'pending'    => 'warning',
        'processing' => 'info',
        'completed'  => 'success',
        'failed'     => 'danger',
    ][$payout->status] ?? 'secondary';
@endphp
<div class="card border-{{ $statusBadge }} mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h2 class="mb-1">₦{{ number_format($payout->amount, 2) }}</h2>
                @if($payout->fee_amount > 0)
                <p class="text-muted mb-1">
                    Fee: ₦{{ number_format($payout->fee_amount, 2) }} —
                    Net: <strong>₦{{ number_format($payout->net_amount, 2) }}</strong>
                </p>
                @endif
                <span class="badge bg-{{ $statusBadge }} fs-6">{{ $payout->status_label }}</span>
            </div>
            <div class="col-md-5 text-md-end mt-3 mt-md-0">
                <div class="d-flex flex-column gap-2">
                    @if($payout->isPending())
                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i data-lucide="check" class="me-1"></i>Approve Payout
                        </button>
                    @endif
                    @if($payout->isProcessing())
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
                            <i data-lucide="check-circle" class="me-1"></i>Mark as Completed
                        </button>
                    @endif
                    @if($payout->isPending() || $payout->isProcessing())
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i data-lucide="x-circle" class="me-1"></i>Reject / Fail
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left: Seller & Bank Details -->
    <div class="col-lg-8">

        <!-- Seller Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Seller Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Seller Name</small>
                        <strong>{{ $payout->seller->user->name }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $payout->seller->user->email }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Shop</small>
                        <strong>{{ $payout->seller->shop->shop_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Verification Status</small>
                        <span class="badge bg-{{ $payout->seller->verification_status === 'verified' ? 'success' : 'warning' }}">
                            {{ ucfirst($payout->seller->verification_status) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Wallet Balance (current)</small>
                        <strong class="text-primary">
                            ₦{{ number_format($payout->seller->wallet?->balance ?? 0, 2) }}
                        </strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Pending Balance</small>
                        <strong class="text-warning">
                            ₦{{ number_format($payout->seller->wallet?->pending_balance ?? 0, 2) }}
                        </strong>
                    </div>
                </div>
                <a href="{{ route('admin.sellers.show', $payout->seller) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i data-lucide="eye" class="me-1"></i>View Seller Profile
                </a>
                <a href="{{ route('admin.sellers.wallet', $payout->seller) }}"
                   class="btn btn-sm btn-outline-info ms-2">
                    <i data-lucide="wallet" class="me-1"></i>View Wallet
                </a>
            </div>
        </div>

        <!-- Bank Account Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Bank Account Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Bank Name</small>
                        <strong>{{ $payout->seller->bank_name ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Account Number</small>
                        <strong class="font-monospace">{{ $payout->seller->bank_account ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Account Name</small>
                        <strong>{{ $payout->seller->account_holder_name ?? 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payout Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Gross Amount</small>
                        <strong>₦{{ number_format($payout->amount, 2) }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Fee</small>
                        <strong>₦{{ number_format($payout->fee_amount ?? 0, 2) }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Net Amount</small>
                        <strong class="text-success">₦{{ number_format($payout->net_amount ?? $payout->amount, 2) }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Payment Method</small>
                        <strong>{{ ucwords(str_replace('_', ' ', $payout->payout_method ?? 'N/A')) }}</strong>
                    </div>
                    @if($payout->transaction_id)
                    <div class="col-md-8 mb-3">
                        <small class="text-muted d-block">Transaction Reference</small>
                        <strong class="font-monospace">{{ $payout->transaction_id }}</strong>
                    </div>
                    @endif
                </div>
                @if($payout->notes)
                <div class="alert alert-light mt-2 mb-0">
                    <h6 class="mb-1">Notes</h6>
                    <p class="mb-0">{{ $payout->notes }}</p>
                </div>
                @endif
                @if($payout->failure_reason)
                <div class="alert alert-danger mt-2 mb-0">
                    <h6 class="mb-1">Failure Reason</h6>
                    <p class="mb-0">{{ $payout->failure_reason }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Wallet Transactions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Recent Wallet Transactions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTransactions as $txn)
                            <tr>
                                <td>
                                    {{ $txn->created_at->format('d M, Y') }}<br>
                                    <small class="text-muted">{{ $txn->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $txn->type_badge }}">
                                        {{ ucfirst($txn->type) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $txn->source_label }}</small>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $txn->is_debit ? 'text-danger' : 'text-success' }}">
                                        {{ $txn->formatted_amount }}
                                    </span>
                                </td>
                                <td>₦{{ number_format($txn->balance_after, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $txn->status_badge }}">
                                        {{ ucfirst($txn->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">
                                    No transactions yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Timeline & Summary -->
    <div class="col-lg-4">

        <!-- Summary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payout Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Payout ID:</td>
                        <td class="fw-bold">#{{ $payout->id }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="badge bg-{{ $statusBadge }}">{{ $payout->status_label }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount:</td>
                        <td class="fw-bold">₦{{ number_format($payout->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Net Amount:</td>
                        <td class="fw-bold text-success">₦{{ number_format($payout->net_amount ?? $payout->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Requested:</td>
                        <td>{{ $payout->requested_at->format('d M Y') }}</td>
                    </tr>
                    @if($payout->processed_at)
                    <tr>
                        <td class="text-muted">Processed:</td>
                        <td>{{ $payout->processed_at->format('d M Y') }}</td>
                    </tr>
                    @endif
                    @if($payout->failed_at)
                    <tr>
                        <td class="text-muted">Failed:</td>
                        <td class="text-danger">{{ $payout->failed_at->format('d M Y') }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline-alt pb-0">

                    <!-- Requested -->
                    <div class="timeline-item">
                        <i data-lucide="send" class="text-primary timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Requested</h6>
                            <small class="text-muted">
                                {{ $payout->requested_at->format('M d, Y h:i A') }}
                            </small><br>
                            <small class="text-muted">By: {{ $payout->seller->user->name }}</small>
                        </div>
                    </div>

                    <!-- Approved / Processing -->
                    @if($payout->processed_at && in_array($payout->status, ['processing', 'completed']))
                    <div class="timeline-item">
                        <i data-lucide="check" class="text-info timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Approved</h6>
                            <small class="text-muted">
                                {{ $payout->processed_at->format('M d, Y h:i A') }}
                            </small>
                            @if($payout->notes)
                            <br><small class="text-muted">{{ Str::limit($payout->notes, 60) }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Completed -->
                    @if($payout->isCompleted() && $payout->transaction_id)
                    <div class="timeline-item">
                        <i data-lucide="check-circle" class="text-success timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Completed</h6>
                            <small class="text-muted">
                                {{ $payout->processed_at?->format('M d, Y h:i A') }}
                            </small><br>
                            <small class="text-muted">Ref: {{ $payout->transaction_id }}</small>
                            @if($payout->payout_method)
                            <br><small class="text-muted">
                                Method: {{ ucwords(str_replace('_', ' ', $payout->payout_method)) }}
                            </small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Failed -->
                    @if($payout->isFailed())
                    <div class="timeline-item">
                        <i data-lucide="x-circle" class="text-danger timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Failed / Rejected</h6>
                            <small class="text-muted">
                                {{ $payout->failed_at?->format('M d, Y h:i A') ?? 'Unknown' }}
                            </small>
                            @if($payout->failure_reason)
                            <br><small class="text-danger">{{ $payout->failure_reason }}</small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Pending (current) -->
                    @if($payout->isPending())
                    <div class="timeline-item">
                        <i data-lucide="clock" class="text-warning timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Awaiting Approval</h6>
                            <small class="text-muted">Pending admin review</small>
                        </div>
                    </div>
                    @elseif($payout->isProcessing())
                    <div class="timeline-item">
                        <i data-lucide="loader" class="text-info timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Processing</h6>
                            <small class="text-muted">Awaiting bank transfer</small>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════ --}}

<!-- Approve Modal -->
@if($payout->isPending())
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.approve', $payout) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Seller:</strong> {{ $payout->seller->user->name }}<br>
                        <strong>Amount:</strong> ₦{{ number_format($payout->amount, 2) }}<br>
                        <strong>Net:</strong> ₦{{ number_format($payout->net_amount ?? $payout->amount, 2) }}<br>
                        <strong>Bank:</strong> {{ $payout->seller->bank_name ?? 'N/A' }}<br>
                        <strong>Account:</strong> {{ $payout->seller->bank_account ?? 'N/A' }}
                        ({{ $payout->seller->account_holder_name ?? 'N/A' }})
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Approval Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add any notes before approving..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <i data-lucide="check" class="me-1"></i>Approve Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Complete Modal -->
@if($payout->isProcessing())
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark Payout as Completed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.complete', $payout) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-success">
                        <strong>Seller:</strong> {{ $payout->seller->user->name }}<br>
                        <strong>Amount to Pay:</strong> ₦{{ number_format($payout->net_amount ?? $payout->amount, 2) }}<br>
                        <strong>Bank:</strong> {{ $payout->seller->bank_name ?? 'N/A' }}<br>
                        <strong>Account:</strong> {{ $payout->seller->bank_account ?? 'N/A' }}<br>
                        <strong>Account Name:</strong> {{ $payout->seller->account_holder_name ?? 'N/A' }}
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Reference *</label>
                        <input type="text" name="transaction_reference" class="form-control"
                               placeholder="e.g., TXN123456789" required>
                        <small class="text-muted">Enter the bank transaction reference or receipt number.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Any completion notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i data-lucide="check-circle" class="me-1"></i>Confirm Completed
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Reject Modal -->
@if($payout->isPending() || $payout->isProcessing())
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject / Fail Payout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.reject', $payout) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        <strong>₦{{ number_format($payout->amount, 2) }}</strong> will be returned to
                        <strong>{{ $payout->seller->user->name }}'s</strong> wallet.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control" rows="4" required
                                  placeholder="Explain why this payout is being rejected..."></textarea>
                        <small class="text-muted">The seller will be notified with this reason.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i data-lucide="x-circle" class="me-1"></i>Reject & Return Funds
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
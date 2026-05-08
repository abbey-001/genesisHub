@extends('admin.layouts.app')

@section('title', 'Payout Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.finance.payouts.index') }}">Payouts</a></li>
                <li class="breadcrumb-item active">Payout #{{ $payout->id }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">💰 Payout #{{ $payout->id }}</h4>
                <p class="text-muted mb-0">Request details and transaction history</p>
            </div>
            <a href="{{ route('admin.finance.payouts.index') }}" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="me-1"></i>
                Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Status & Actions -->
        <div class="card mb-4">
            <div class="card-header bg-{{ $payout->status_badge }}">
                <h5 class="mb-0 text-white">
                    <i data-lucide="info" class="me-2"></i>
                    Current Status: {{ ucfirst($payout->status) }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted small mb-2">STATUS INFORMATION</h6>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <strong>Status:</strong> 
                                <span class="badge bg-{{ $payout->status_badge }}">{{ ucfirst($payout->status) }}</span>
                            </li>
                            <li class="mb-2">
                                <strong>Requested:</strong> {{ $payout->requested_at->format('M d, Y h:i A') }}
                            </li>
                            @if($payout->processed_at)
                            <li class="mb-2">
                                <strong>Processed:</strong> {{ $payout->processed_at->format('M d, Y h:i A') }}
                            </li>
                            @endif
                            @if($payout->transaction_id)
                            <li class="mb-0">
                                <strong>Transaction Ref:</strong> 
                                <code>{{ $payout->transaction_id }}</code>
                            </li>
                            @endif
                        </ul>
                    </div>

                    <div class="col-md-6">
                        @if($payout->status === 'pending')
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                                    <i data-lucide="check-circle" class="me-1"></i>
                                    Approve Payout
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i data-lucide="x-circle" class="me-1"></i>
                                    Reject Payout
                                </button>
                            </div>
                        @elseif($payout->status === 'processing')
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#completeModal">
                                    <i data-lucide="check-circle-2" class="me-1"></i>
                                    Mark as Completed
                                </button>
                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    <i data-lucide="x-circle" class="me-1"></i>
                                    Mark as Failed
                                </button>
                            </div>
                        @elseif($payout->status === 'completed')
                            <div class="alert alert-success mb-0">
                                <i data-lucide="check-circle" class="me-2"></i>
                                <strong>Payout Completed</strong><br>
                                Funds have been successfully transferred to the seller.
                            </div>
                        @elseif($payout->status === 'failed')
                            <div class="alert alert-danger mb-0">
                                <i data-lucide="x-circle" class="me-2"></i>
                                <strong>Payout Failed</strong><br>
                                {{ $payout->notes ?? 'No reason provided' }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payout Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="file-text" class="me-2"></i>
                    Payout Details
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Amount</small>
                        <h3 class="text-success mb-0">₦{{ number_format($payout->amount, 2) }}</h3>
                    </div>

                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Payout Method</small>
                        <h5 class="mb-0">{{ ucwords(str_replace('_', ' ', $payout->payout_method)) }}</h5>
                    </div>

                    @if($payout->seller->bank_account)
                    <div class="col-md-12">
                        <h6 class="text-muted mb-3">BANK DETAILS</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Bank Name</small>
                                <div class="fw-medium">{{ $payout->seller->bank_name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Account Number</small>
                                <div class="fw-medium font-monospace">{{ $payout->seller->bank_account ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Account Name</small>
                                <div class="fw-medium">{{ $payout->seller->account_holder_name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                @if($payout->notes)
                <hr>
                <div>
                    <small class="text-muted d-block mb-2">Notes</small>
                    <p class="mb-0">{{ $payout->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Wallet Transactions -->
        @if($recentTransactions->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Recent Wallet Activity
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>Balance After</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type_badge }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $transaction->source)) }}</td>
                                <td class="fw-medium {{ $transaction->is_debit ? 'text-danger' : 'text-success' }}">
                                    {{ $transaction->formatted_amount }}
                                </td>
                                <td>₦{{ number_format($transaction->balance_after, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Seller Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="user" class="me-2"></i>
                    Seller Information
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h6 class="mb-1">{{ $payout->seller->user->name }}</h6>
                    <p class="text-muted small mb-2">{{ $payout->seller->user->email }}</p>
                    @if($payout->seller->shop)
                        <span class="badge bg-primary">{{ $payout->seller->shop->shop_name }}</span>
                    @endif
                </div>

                <hr>

                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Phone:</span>
                        <span class="fw-medium">{{ $payout->seller->phone_number ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax ID:</span>
                        <span class="fw-medium">{{ $payout->seller->tax_id ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Verified:</span>
                        <span>
                            @if($payout->seller->is_verified)
                                <i data-lucide="check-circle" class="text-success" style="width: 16px; height: 16px;"></i>
                            @else
                                <i data-lucide="x-circle" class="text-danger" style="width: 16px; height: 16px;"></i>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    <a href="{{ route('admin.finance.wallets.show', $payout->seller->wallet) }}" class="btn btn-outline-primary btn-sm">
                        <i data-lucide="wallet" class="me-1"></i>
                        View Wallet
                    </a>
                </div>
            </div>
        </div>

        <!-- Wallet Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="wallet" class="me-2"></i>
                    Wallet Summary
                </h5>
            </div>
            <div class="card-body">
                @if($payout->seller->wallet)
                <div class="mb-3">
                    <small class="text-muted d-block">Available Balance</small>
                    <h5 class="text-success mb-0">₦{{ number_format($payout->seller->wallet->balance, 2) }}</h5>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Pending Balance</small>
                    <div class="text-warning">₦{{ number_format($payout->seller->wallet->pending_balance, 2) }}</div>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Total Earned</small>
                    <div>₦{{ number_format($payout->seller->wallet->total_earned, 2) }}</div>
                </div>

                <div class="mb-0">
                    <small class="text-muted d-block">Total Withdrawn</small>
                    <div>₦{{ number_format($payout->seller->wallet->total_withdrawn, 2) }}</div>
                </div>
                @else
                <p class="text-muted mb-0">No wallet found</p>
                @endif
            </div>
        </div>

        <!-- Timeline -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="clock" class="me-2"></i>
                    Timeline
                </h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-3">
                        <div class="d-flex align-items-start">
                            <div class="avatar-xs bg-primary bg-opacity-10 rounded me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                <i data-lucide="plus" class="text-primary" style="width: 14px; height: 14px;"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Request Created</div>
                                <small class="text-muted">{{ $payout->requested_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    </li>

                    @if($payout->processed_at)
                    <li class="mb-3">
                        <div class="d-flex align-items-start">
                            <div class="avatar-xs bg-success bg-opacity-10 rounded me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                <i data-lucide="check" class="text-success" style="width: 14px; height: 14px;"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $payout->status === 'completed' ? 'Completed' : 'Processed' }}</div>
                                <small class="text-muted">{{ $payout->processed_at->format('M d, Y h:i A') }}</small>
                            </div>
                        </div>
                    </li>
                    @endif

                    <li>
                        <div class="d-flex align-items-start">
                            <div class="avatar-xs bg-info bg-opacity-10 rounded me-2 flex-shrink-0 d-flex align-items-center justify-content-center">
                                <i data-lucide="clock" class="text-info" style="width: 14px; height: 14px;"></i>
                            </div>
                            <div>
                                <div class="fw-medium">Last Updated</div>
                                <small class="text-muted">{{ $payout->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Payout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.approve', $payout) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        Approving this payout will move it to "Processing" status. You'll need to complete the bank transfer separately.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Mark as Completed</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.complete', $payout) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        Only mark as completed AFTER successfully transferring the funds.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction Reference *</label>
                        <input type="text" name="transaction_reference" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Completed</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">{{ $payout->status === 'processing' ? 'Mark as Failed' : 'Reject Payout' }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.reject', $payout) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        This will return the funds to the seller's wallet.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        {{ $payout->status === 'processing' ? 'Mark as Failed' : 'Reject Payout' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
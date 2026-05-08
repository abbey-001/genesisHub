@extends('admin.layouts.app')

@section('title', 'Wallet Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.finance.wallets.index') }}">Wallets</a></li>
                <li class="breadcrumb-item active">{{ $wallet->seller->user->name }}</li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">👛 {{ $wallet->seller->user->name }}'s Wallet</h4>
                <p class="text-muted mb-0">Complete transaction history and balance management</p>
            </div>
            <div class="d-flex gap-2">
                @can('finance.wallets.adjust')
                <a href="{{ route('admin.finance.wallets.adjustPage', $wallet) }}" class="btn btn-primary">
                    <i data-lucide="edit" class="me-1"></i>
                    Manual Adjustment
                </a>
                @endcan
                <a href="{{ route('admin.finance.wallets.index') }}" class="btn btn-outline-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>
                    Back
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Balance Summary -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <small class="text-muted d-block mb-2">Available Balance</small>
                <h3 class="text-success mb-0">₦{{ number_format($wallet->balance, 2) }}</h3>
                <small class="text-muted">Can be withdrawn</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <small class="text-muted d-block mb-2">Pending Balance</small>
                <h3 class="text-warning mb-0">₦{{ number_format($wallet->pending_balance, 2) }}</h3>
                @if($wallet->pending_balance > 0)
                    @can('finance.wallets.adjust')
                    <button type="button" 
                            class="btn btn-sm btn-warning mt-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#releasePendingModal">
                        <i data-lucide="unlock" class="me-1"></i>
                        Release
                    </button>
                    @endcan
                @else
                    <small class="text-muted">No pending funds</small>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <small class="text-muted d-block mb-2">Reserved Balance</small>
                <h3 class="text-info mb-0">₦{{ number_format($wallet->reserved_balance, 2) }}</h3>
                <small class="text-muted">For refunds/disputes</small>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <small class="text-muted d-block mb-2">Total Balance</small>
                <h3 class="mb-0">₦{{ number_format($wallet->total_balance, 2) }}</h3>
                <small class="text-muted">All balances combined</small>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Earned</p>
                        <h5 class="mb-0">₦{{ number_format($wallet->total_earned, 2) }}</h5>
                    </div>
                    <i data-lucide="trending-up" class="text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Withdrawn</p>
                        <h5 class="mb-0">₦{{ number_format($wallet->total_withdrawn, 2) }}</h5>
                    </div>
                    <i data-lucide="trending-down" class="text-danger"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Credits</p>
                        <h5 class="mb-0 text-success">₦{{ number_format($transactionStats['total_credits'], 2) }}</h5>
                    </div>
                    <i data-lucide="plus-circle" class="text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Total Debits</p>
                        <h5 class="mb-0 text-danger">₦{{ number_format($transactionStats['total_debits'], 2) }}</h5>
                    </div>
                    <i data-lucide="minus-circle" class="text-danger"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Transaction History
                </h5>
                <a href="{{ route('admin.finance.wallets.exportTransactions', ['wallet_id' => $wallet->id]) }}" 
                   class="btn btn-sm btn-outline-secondary">
                    <i data-lucide="download" class="me-1"></i>
                    Export
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Balance Before</th>
                                <th>Balance After</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    {{ $transaction->created_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type_badge }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>{{ ucwords(str_replace('_', ' ', $transaction->source)) }}</td>
                                <td>
                                    <div>{{ Str::limit($transaction->description, 40) }}</div>
                                    @if($transaction->metadata)
                                        <small class="text-muted">
                                            @if(isset($transaction->metadata['admin_name']))
                                                By: {{ $transaction->metadata['admin_name'] }}
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold {{ $transaction->is_debit ? 'text-danger' : 'text-success' }}">
                                        {{ $transaction->formatted_amount }}
                                    </span>
                                </td>
                                <td class="text-muted">₦{{ number_format($transaction->balance_before, 2) }}</td>
                                <td class="fw-medium">₦{{ number_format($transaction->balance_after, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i data-lucide="inbox" class="text-muted mb-2" style="width: 32px; height: 32px;"></i>
                                    <p class="text-muted mb-0">No transactions yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($transactions->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} 
                        of {{ $transactions->total() }} transactions
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Release Pending Modal -->
<div class="modal fade" id="releasePendingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i data-lucide="unlock" class="me-2"></i>
                    Release Pending Balance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.wallets.releasePending', $wallet) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        This will move funds from pending to available balance.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Current Pending Balance</label>
                        <input type="text" class="form-control fw-bold text-warning" 
                               value="₦{{ number_format($wallet->pending_balance, 2) }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount to Release *</label>
                        <input type="number" 
                               name="amount" 
                               class="form-control" 
                               step="0.01"
                               max="{{ $wallet->pending_balance }}"
                               required>
                        <small class="text-muted">Maximum: ₦{{ number_format($wallet->pending_balance, 2) }}</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Release Balance</button>
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
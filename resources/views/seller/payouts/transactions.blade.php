{{-- resources/views/seller/payouts/transactions.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Transaction History')

@section('content')
<div class="container-xxl">
    
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">📜 Transaction History</h4>
            <p class="text-muted mb-0">Complete record of all wallet activities</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('seller.payouts.transactions.export', request()->all()) }}" class="btn btn-success btn-sm me-2">
                <i data-lucide="download" class="fs-16"></i> Export CSV
            </a>
            <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-left" class="fs-16"></i> Back to Wallet
            </a>
        </div>
    </div>

    <!-- Wallet Summary Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient bg-primary text-white shadow-sm">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <p class="mb-1 opacity-75">Current Balance</p>
                            <h3 class="fw-bold mb-0">₦{{ number_format($wallet->balance, 2) }}</h3>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 opacity-75">Pending</p>
                            <h3 class="fw-bold mb-0">₦{{ number_format($wallet->pending_balance, 2) }}</h3>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 opacity-75">Total Earned</p>
                            <h3 class="fw-bold mb-0">₦{{ number_format($wallet->total_earned, 2) }}</h3>
                        </div>
                        <div class="col-md-3">
                            <p class="mb-1 opacity-75">Total Withdrawn</p>
                            <h3 class="fw-bold mb-0">₦{{ number_format($wallet->total_withdrawn, 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">
                        <i data-lucide="filter" class="fs-16"></i> Filter Transactions
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('seller.payouts.transactions') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label small text-muted">Transaction Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="">All Types</option>
                                <option value="credit" {{ request('type') === 'credit' ? 'selected' : '' }}>💰 Money In (Credits)</option>
                                <option value="debit" {{ request('type') === 'debit' ? 'selected' : '' }}>💸 Money Out (Debits)</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small text-muted">Source</label>
                            <select name="source" class="form-select form-select-sm">
                                <option value="">All Sources</option>
                                @foreach($sources as $source)
                                    <option value="{{ $source }}" {{ request('source') === $source ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $source)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                <option value="reversed" {{ request('status') === 'reversed' ? 'selected' : '' }}>🔄 Reversed</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small text-muted">From Date</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label small text-muted">To Date</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i data-lucide="search" class="fs-16"></i> Apply Filters
                            </button>
                            <a href="{{ route('seller.payouts.transactions') }}" class="btn btn-secondary btn-sm">
                                <i data-lucide="x" class="fs-16"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions List -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Transactions</h5>
                    <span class="badge bg-secondary">{{ $transactions->total() }} records</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 140px;">Date & Time</th>
                                <th style="width: 100px;">Type</th>
                                <th style="width: 150px;">Source</th>
                                <th>Description</th>
                                <th class="text-end" style="width: 120px;">Amount</th>
                                <th class="text-end" style="width: 120px;">Balance</th>
                                <th style="width: 100px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    <div class="small">{{ $transaction->created_at->format('M d, Y') }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $transaction->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type_badge }}">
                                        @if($transaction->is_credit)
                                            <i data-lucide="arrow-down-circle" class="fs-12"></i>
                                        @else
                                            <i data-lucide="arrow-up-circle" class="fs-12"></i>
                                        @endif
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ $transaction->source_label }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">{{ $transaction->description }}</div>
                                    @if($transaction->transactable)
                                        <small class="text-muted">
                                            Ref: {{ class_basename($transaction->transactable_type) }} #{{ $transaction->transactable_id }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-end fw-bold {{ $transaction->is_credit ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->formatted_amount }}
                                </td>
                                <td class="text-end">
                                    <div class="small text-muted">₦{{ number_format($transaction->balance_after, 2) }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        from ₦{{ number_format($transaction->balance_before, 2) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status_badge }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        <i data-lucide="inbox" style="width: 48px; height: 48px;"></i>
                                        <p class="mt-2 mb-0">No transactions found</p>
                                        @if(request()->hasAny(['type', 'source', 'status', 'date_from', 'date_to']))
                                            <small>Try adjusting your filters</small>
                                        @else
                                            <small>Your transaction history will appear here</small>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($transactions->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }}
                        </div>
                        {{ $transactions->appends(request()->all())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
lucide.createIcons();
</script>
@endpush
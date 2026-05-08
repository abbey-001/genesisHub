@extends('admin.layouts.app')
@section('title', 'Seller Wallet')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title">Wallet — {{ $seller->shop->shop_name ?? 'Seller' }}</h4>
                    <p class="text-muted mb-0">{{ $seller->user->name }} • {{ $seller->user->email }}</p>
                </div>
                <a href="{{ route('admin.sellers.show', $seller) }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Seller
                </a>
            </div>
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

{{--
    All figures below come from a fresh SELECT on seller_wallets (wallet_id = {{ $wallet->id }}).
    They are NEVER served from an Eloquent relation cache.

    Column meanings:
      balance          → money currently available to withdraw (decremented on each payout)
      pending_balance  → earnings held during the clearance period (not yet withdrawable)
      reserved_balance → funds locked for open disputes / refund holds
      total_earned     → lifetime cumulative credits (never decremented)
      total_withdrawn  → lifetime cumulative debits / payouts (never decremented)
--}}

<!-- Top balance cards -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                            <i data-lucide="wallet" class="fs-24"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Available Balance</p>
                        {{-- $wallet->balance = total_earned - total_withdrawn (+ pending adjustments) --}}
                        <h3 class="mb-0 text-primary">₦{{ number_format($wallet->balance, 2) }}</h3>
                        <small class="text-muted">Ready to withdraw</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                            <i data-lucide="clock" class="fs-24"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Pending Balance</p>
                        <h3 class="mb-0 text-warning">₦{{ number_format($wallet->pending_balance, 2) }}</h3>
                        <small class="text-muted">Awaiting clearance</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                            <i data-lucide="trending-up" class="fs-24"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Earned</p>
                        <h3 class="mb-0 text-info">₦{{ number_format($wallet->total_earned, 2) }}</h3>
                        <small class="text-muted">Lifetime credits</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                            <i data-lucide="arrow-up-circle" class="fs-24"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Withdrawn</p>
                        <h3 class="mb-0 text-success">₦{{ number_format($wallet->total_withdrawn, 2) }}</h3>
                        <small class="text-muted">Lifetime payouts</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Wallet Info & Actions -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i data-lucide="info" class="me-2"></i>Wallet Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Wallet ID:</td>
                        <td class="fw-bold">#{{ $wallet->id }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-muted">Available:</td>
                        <td class="fw-bold text-primary">₦{{ number_format($wallet->balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Pending:</td>
                        <td class="fw-bold text-warning">₦{{ number_format($wallet->pending_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Reserved:</td>
                        <td class="fw-bold text-danger">₦{{ number_format($wallet->reserved_balance, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Balance:</td>
                        <td class="fw-bold">₦{{ number_format($wallet->total_balance, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-muted">Total Earned:</td>
                        <td class="fw-bold text-info">₦{{ number_format($wallet->total_earned, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Withdrawn:</td>
                        <td class="fw-bold text-success">₦{{ number_format($wallet->total_withdrawn, 2) }}</td>
                    </tr>
                    <tr class="border-top">
                        <td class="text-muted">Last Transaction:</td>
                        <td>
                            @if($wallet->last_transaction_at)
                                {{ $wallet->last_transaction_at->diffForHumans() }}<br>
                                <small class="text-muted">{{ $wallet->last_transaction_at->format('d M Y, h:i A') }}</small>
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </td>
                    </tr>
                </table>

                {{-- Sanity check: warn admin if balance looks inconsistent --}}
                @php
                    $computedBalance = $wallet->total_earned - $wallet->total_withdrawn - $wallet->pending_balance - $wallet->reserved_balance;
                    $drift = abs($wallet->balance - $computedBalance);
                @endphp
                @if($drift > 0.01)
                <div class="alert alert-warning mt-3 mb-0 p-2 small">
                    <i data-lucide="alert-triangle" class="me-1"></i>
                    Balance drift detected (₦{{ number_format($drift, 2) }}). May be caused by manual adjustments.
                </div>
                @endif

                <hr>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adjustModal">
                        <i data-lucide="edit" class="me-1"></i>Adjust Balance
                    </button>
                    @if($wallet->pending_balance > 0)
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#releaseModal">
                            <i data-lucide="unlock" class="me-1"></i>Release Pending Funds
                        </button>
                    @else
                        <button type="button" class="btn btn-info" disabled>
                            <i data-lucide="unlock" class="me-1"></i>No Pending Funds
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Transaction Summary card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i data-lucide="bar-chart-2" class="me-2"></i>Transaction Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Credits:</td>
                        <td class="fw-bold text-success">+₦{{ number_format($transactionSummary['total_credits'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Debits:</td>
                        <td class="fw-bold text-danger">−₦{{ number_format($transactionSummary['total_debits'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Reserved:</td>
                        <td class="fw-bold text-warning">₦{{ number_format($transactionSummary['total_reserved'], 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Released:</td>
                        <td class="fw-bold text-info">₦{{ number_format($transactionSummary['total_released'], 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i data-lucide="list" class="me-2"></i>Transaction History</h5>
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
                                <th>Balance Before</th>
                                <th>Balance After</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                            <tr>
                                <td>
                                    {{ $transaction->created_at->format('d M, Y') }}<br>
                                    <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $transaction->type_badge }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small">{{ $transaction->source_label }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $transaction->is_debit ? 'text-danger' : 'text-success' }}">
                                        {{ $transaction->formatted_amount }}
                                    </span>
                                </td>
                                <td>₦{{ number_format($transaction->balance_before, 2) }}</td>
                                <td>₦{{ number_format($transaction->balance_after, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $transaction->status_badge }}">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($transaction->description)
                                    <button class="btn btn-sm btn-light"
                                            data-bs-toggle="tooltip"
                                            title="{{ $transaction->description }}">
                                        <i data-lucide="info"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i data-lucide="file-text" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mt-2 mb-0">No transactions yet</p>
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
                        of {{ $transactions->total() }}
                    </div>
                    {{ $transactions->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Adjust Balance Modal -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adjust Wallet Balance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.wallets.adjust', $wallet) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        <strong>Warning:</strong> Manual adjustments are permanently logged.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Available Balance</label>
                        <input type="text" class="form-control"
                               value="₦{{ number_format($wallet->balance, 2) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type *</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select type</option>
                            <option value="credit">Credit — Add funds to available balance</option>
                            <option value="debit">Debit — Deduct funds from available balance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount *</label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" name="amount" class="form-control"
                                   step="0.01" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control" rows="3" required
                                  placeholder="Explain why you're adjusting the balance..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="2"
                                  placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Release Pending Funds Modal -->
<div class="modal fade" id="releaseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Release Pending Funds</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.wallets.releasePending', $wallet) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i data-lucide="info" class="me-2"></i>
                        This moves funds from pending to the seller's available balance.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Pending Balance</label>
                        <input type="text" class="form-control"
                               value="₦{{ number_format($wallet->pending_balance, 2) }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount to Release *</label>
                        <div class="input-group">
                            <span class="input-group-text">₦</span>
                            <input type="number" name="amount" class="form-control"
                                   step="0.01"
                                   min="0.01"
                                   max="{{ $wallet->pending_balance }}"
                                   required>
                        </div>
                        <small class="text-muted">
                            Maximum: ₦{{ number_format($wallet->pending_balance, 2) }}
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason *</label>
                        <textarea name="reason" class="form-control" rows="2" required
                                  placeholder="Reason for releasing funds..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">Release Funds</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
            .forEach(el => new bootstrap.Tooltip(el));
</script>
@endpush
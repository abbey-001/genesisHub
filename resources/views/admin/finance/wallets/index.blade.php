@extends('admin.layouts.app')

@section('title', 'Wallet Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">👛 Seller Wallet Management</h4>
                <p class="text-muted mb-0">Monitor and manage seller wallet balances</p>
            </div>
            <div class="d-flex gap-2">
                @can('finance.wallets.view')
                <a href="{{ route('admin.finance.wallets.analytics') }}" class="btn btn-outline-primary">
                    <i data-lucide="bar-chart" class="me-1"></i>
                    Analytics
                </a>
                <a href="{{ route('admin.finance.wallets.exportTransactions', request()->query()) }}" class="btn btn-outline-secondary">
                    <i data-lucide="download" class="me-1"></i>
                    Export Transactions
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<!-- Summary Stats -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 card-title text-muted small">Total Wallets</p>
                        <h5 class="fw-bold mb-0">{{ number_format($stats['total_wallets']) }}</h5>
                    </div>
                    <div class="avatar-sm bg-primary bg-opacity-10 rounded">
                        <i data-lucide="wallet" class="text-primary fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 card-title text-muted small">Available Balance</p>
                        <h5 class="fw-bold text-success mb-0">₦{{ number_format($stats['total_balance'], 0) }}</h5>
                    </div>
                    <div class="avatar-sm bg-success bg-opacity-10 rounded">
                        <i data-lucide="credit-card" class="text-success fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 card-title text-muted small">Pending Balance</p>
                        <h5 class="fw-bold text-warning mb-0">₦{{ number_format($stats['total_pending'], 0) }}</h5>
                    </div>
                    <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                        <i data-lucide="clock" class="text-warning fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 card-title text-muted small">Reserved</p>
                        <h5 class="fw-bold text-info mb-0">₦{{ number_format($stats['total_reserved'], 0) }}</h5>
                    </div>
                    <div class="avatar-sm bg-info bg-opacity-10 rounded">
                        <i data-lucide="lock" class="text-info fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 card-title text-muted small">Total Earned</p>
                        <h5 class="fw-bold mb-0">₦{{ number_format($stats['total_earned'], 0) }}</h5>
                    </div>
                    <div class="avatar-sm bg-primary bg-opacity-10 rounded">
                        <i data-lucide="trending-up" class="text-primary fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-2 col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-1 card-title text-muted small">Total Withdrawn</p>
                        <h5 class="fw-bold mb-0">₦{{ number_format($stats['total_withdrawn'], 0) }}</h5>
                    </div>
                    <div class="avatar-sm bg-secondary bg-opacity-10 rounded">
                        <i data-lucide="trending-down" class="text-secondary fs-20"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Wallets Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Seller Wallets
                </h5>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.finance.wallets.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="search" name="search" class="form-control" 
                               placeholder="Search seller or shop..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="min_balance" class="form-control" 
                               placeholder="Min Balance" 
                               value="{{ request('min_balance') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="max_balance" class="form-control" 
                               placeholder="Max Balance" 
                               value="{{ request('max_balance') }}">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i data-lucide="search" class="me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.finance.wallets.index') }}" class="btn btn-outline-secondary">
                                <i data-lucide="x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Seller</th>
                                <th>Shop</th>
                                <th>Available Balance</th>
                                <th>Pending</th>
                                <th>Reserved</th>
                                <th>Total Earned</th>
                                <th>Total Withdrawn</th>
                                <th>Last Transaction</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($wallets as $wallet)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $wallet->seller->user->name }}</div>
                                    <small class="text-muted">{{ $wallet->seller->user->email }}</small>
                                </td>
                                <td>
                                    @if($wallet->seller->shop)
                                        <div>{{ $wallet->seller->shop->shop_name }}</div>
                                    @else
                                        <span class="text-muted">No shop</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold text-success">₦{{ number_format($wallet->balance, 2) }}</div>
                                    @if($wallet->balance > 10000)
                                        <small class="text-muted">High balance</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-warning">₦{{ number_format($wallet->pending_balance, 2) }}</div>
                                    @if($wallet->pending_balance > 0)
                                        <small class="text-muted">
                                            <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                            Pending
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-info">₦{{ number_format($wallet->reserved_balance, 2) }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium">₦{{ number_format($wallet->total_earned, 2) }}</div>
                                </td>
                                <td>
                                    <div>₦{{ number_format($wallet->total_withdrawn, 2) }}</div>
                                </td>
                                <td>
                                    @if($wallet->last_transaction_at)
                                        {{ $wallet->last_transaction_at->diffForHumans() }}
                                    @else
                                        <span class="text-muted">Never</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" 
                                                type="button" 
                                                data-bs-toggle="dropdown">
                                            <i data-lucide="more-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.finance.wallets.show', $wallet) }}">
                                                    <i data-lucide="eye" class="me-2"></i>
                                                    View Details
                                                </a>
                                            </li>
                                            @can('finance.wallets.adjust')
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.finance.wallets.adjustPage', $wallet) }}">
                                                    <i data-lucide="edit" class="me-2"></i>
                                                    Manual Adjustment
                                                </a>
                                            </li>
                                            @if($wallet->pending_balance > 0)
                                            <li>
                                                <a class="dropdown-item text-warning" 
                                                   href="#"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#releasePendingModal{{ $wallet->id }}">
                                                    <i data-lucide="unlock" class="me-2"></i>
                                                    Release Pending
                                                </a>
                                            </li>
                                            @endif
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Release Pending Modal -->
                            @if($wallet->pending_balance > 0)
                            <div class="modal fade" id="releasePendingModal{{ $wallet->id }}" tabindex="-1">
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
                                                    <i data-lucide="info" class="me-2"></i>
                                                    This will move funds from pending to available balance, making them withdrawable.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Seller</label>
                                                    <input type="text" class="form-control" value="{{ $wallet->seller->user->name }}" disabled>
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
                                                           required
                                                           placeholder="Enter amount">
                                                    <small class="text-muted">Maximum: ₦{{ number_format($wallet->pending_balance, 2) }}</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Reason *</label>
                                                    <textarea name="reason" class="form-control" rows="2" required
                                                              placeholder="Why are you releasing this balance?"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i data-lucide="unlock" class="me-1"></i>
                                                    Release Balance
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No wallets found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($wallets->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $wallets->firstItem() }} to {{ $wallets->lastItem() }} 
                        of {{ $wallets->total() }} wallets
                    </div>
                    {{ $wallets->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Info Cards -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i data-lucide="info" class="me-2"></i>
                    Balance Types Explained
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">
                        <strong class="text-success">Available Balance:</strong> 
                        Funds that can be withdrawn by the seller
                    </li>
                    <li class="mb-2">
                        <strong class="text-warning">Pending Balance:</strong> 
                        Funds from recent orders, subject to hold period
                    </li>
                    <li class="mb-2">
                        <strong class="text-info">Reserved Balance:</strong> 
                        Funds reserved for potential refunds or disputes
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i data-lucide="shield" class="me-2"></i>
                    Wallet Management Guidelines
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Always verify seller identity before manual adjustments</li>
                    <li class="mb-2">Document all manual transactions with clear reasons</li>
                    <li class="mb-2">Release pending balances only after hold period</li>
                    <li class="mb-2">Monitor high-value wallets for unusual activity</li>
                </ul>
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
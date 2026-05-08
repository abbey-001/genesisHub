@extends('admin.layouts.app')

@section('title', 'Seller Payout Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right d-flex gap-2">
                <a href="{{ route('admin.finance.payouts.analytics') }}" class="btn btn-info">
                    <i data-lucide="bar-chart" class="me-1"></i>Analytics
                </a>
                <a href="{{ route('admin.finance.payouts.export', request()->all()) }}" class="btn btn-success">
                    <i data-lucide="download" class="me-1"></i>Export
                </a>
            </div>
            <h4 class="page-title">Seller Payout Management</h4>
            <p class="text-muted">Review and process seller withdrawal requests</p>
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

<!-- Statistics Cards -->
{{--
    Controller keys:
      pending          → count of pending payouts
      pending_amount   → sum of pending payout amounts
      processing       → count of processing payouts
      processing_amount → sum of processing payout amounts
      completed_today  → count completed today
      completed_amount_today → sum completed today
      failed           → count of failed payouts
--}}
<div class="row">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                            <i data-lucide="clock" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Pending Approval</p>
                        <h3 class="mb-0 text-warning">₦{{ number_format($stats['pending_amount'], 0) }}</h3>
                        <small class="text-muted">{{ $stats['pending'] }} request{{ $stats['pending'] != 1 ? 's' : '' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                            <i data-lucide="loader" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Processing</p>
                        <h3 class="mb-0 text-info">₦{{ number_format($stats['processing_amount'], 0) }}</h3>
                        <small class="text-muted">{{ $stats['processing'] }} payout{{ $stats['processing'] != 1 ? 's' : '' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                            <i data-lucide="check-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Completed Today</p>
                        <h3 class="mb-0 text-success">₦{{ number_format($stats['completed_amount_today'], 0) }}</h3>
                        <small class="text-muted">{{ $stats['completed_today'] }} completed</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded">
                            <i data-lucide="x-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Failed</p>
                        <h3 class="mb-0 text-danger">{{ $stats['failed'] }}</h3>
                        <small class="text-muted">all time</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.finance.payouts.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed"  {{ request('status') === 'completed'  ? 'selected' : '' }}>Completed</option>
                    <option value="failed"     {{ request('status') === 'failed'     ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Seller</label>
                <input type="text" name="seller_id" class="form-control"
                       placeholder="Seller ID (optional)" value="{{ request('seller_id') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i data-lucide="filter" class="me-1"></i>Filter
                </button>
                <a href="{{ route('admin.finance.payouts.index') }}" class="btn btn-secondary">
                    <i data-lucide="x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Payouts Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payout Requests</h5>
        @if($payouts->total() > 0 && (request('status') === 'pending' || !request('status')))
        <form action="{{ route('admin.finance.payouts.bulkApprove') }}" method="POST" id="bulkForm">
            @csrf
            <button type="submit" class="btn btn-sm btn-info" id="bulkApproveBtn" disabled>
                <i data-lucide="check-circle" class="me-1"></i>
                Bulk Approve (<span id="bulkCount">0</span>)
            </button>
        </form>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    @if(request('status') === 'pending' || !request('status'))
                    <th width="40">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </th>
                    @endif
                    <th>Payout ID</th>
                    <th>Seller</th>
                    <th>Shop</th>
                    <th>Amount</th>
                    <th>Net Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                <tr>
                    @if(request('status') === 'pending' || !request('status'))
                    <td>
                        @if($payout->isPending())
                        <input type="checkbox" class="form-check-input payout-checkbox"
                               value="{{ $payout->id }}">
                        @endif
                    </td>
                    @endif
                    <td>
                        <a href="{{ route('admin.finance.payouts.show', $payout) }}"
                           class="fw-medium text-primary">
                            #{{ $payout->id }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $payout->seller->user->name }}</div>
                        <small class="text-muted">{{ $payout->seller->user->email }}</small>
                    </td>
                    <td>{{ $payout->seller->shop->shop_name ?? 'N/A' }}</td>
                    <td>
                        <strong class="text-primary">₦{{ number_format($payout->amount, 2) }}</strong>
                    </td>
                    <td>
                        <span class="text-success">₦{{ number_format($payout->net_amount ?? $payout->amount, 2) }}</span>
                        @if($payout->fee_amount > 0)
                            <br><small class="text-muted">Fee: ₦{{ number_format($payout->fee_amount, 2) }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-secondary">
                            {{ ucwords(str_replace('_', ' ', $payout->payout_method ?? 'N/A')) }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusBadge = [
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'completed'  => 'success',
                                'failed'     => 'danger',
                            ][$payout->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $statusBadge }}">
                            {{ $payout->status_label }}
                        </span>
                    </td>
                    <td>
                        {{ $payout->requested_at->format('d M, Y') }}<br>
                        <small class="text-muted">{{ $payout->requested_at->format('h:i A') }}</small>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown">Actions</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item"
                                       href="{{ route('admin.finance.payouts.show', $payout) }}">
                                        <i data-lucide="eye" class="me-2"></i>View Details
                                    </a>
                                </li>
                                @if($payout->isPending())
                                <li>
                                    <button class="dropdown-item text-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $payout->id }}">
                                        <i data-lucide="check" class="me-2"></i>Approve
                                    </button>
                                </li>
                                @endif
                                @if($payout->isProcessing())
                                <li>
                                    <button class="dropdown-item text-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#completeModal{{ $payout->id }}">
                                        <i data-lucide="check-circle" class="me-2"></i>Mark Completed
                                    </button>
                                </li>
                                @endif
                                @if($payout->isPending() || $payout->isProcessing())
                                <li>
                                    <button class="dropdown-item text-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $payout->id }}">
                                        <i data-lucide="x" class="me-2"></i>Reject / Fail
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                        <p class="text-muted mb-0">No payout requests found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payouts->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted">
                Showing {{ $payouts->firstItem() }} to {{ $payouts->lastItem() }} of {{ $payouts->total() }}
            </div>
            {{ $payouts->links() }}
        </div>
    </div>
    @endif
</div>

{{--
    MODALS — rendered outside the table so the DOM is valid and Bootstrap
    can reliably find and open them.
--}}
@foreach($payouts as $payout)

    {{-- Approve Modal --}}
    @if($payout->isPending())
    <div class="modal fade" id="approveModal{{ $payout->id }}" tabindex="-1">
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
                            <strong>Shop:</strong> {{ $payout->seller->shop->shop_name ?? 'N/A' }}<br>
                            <strong>Amount:</strong> ₦{{ number_format($payout->amount, 2) }}<br>
                            <strong>Method:</strong> {{ ucwords(str_replace('_', ' ', $payout->payout_method)) }}<br>
                            <strong>Bank:</strong> {{ $payout->seller->bank_name ?? 'N/A' }}<br>
                            <strong>Account:</strong> {{ $payout->seller->bank_account ?? 'N/A' }}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes <span class="text-muted">(optional)</span></label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Add any approval notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Approve Payout</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Complete (Mark as Completed) Modal --}}
    @if($payout->isProcessing())
    <div class="modal fade" id="completeModal{{ $payout->id }}" tabindex="-1">
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
                            <strong>Amount:</strong> ₦{{ number_format($payout->amount, 2) }}<br>
                            <strong>Bank:</strong> {{ $payout->seller->bank_name ?? 'N/A' }}<br>
                            <strong>Account:</strong> {{ $payout->seller->bank_account ?? 'N/A' }} — {{ $payout->seller->account_holder_name ?? 'N/A' }}
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
                        <button type="submit" class="btn btn-success">Confirm Completed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Reject Modal --}}
    @if($payout->isPending() || $payout->isProcessing())
    <div class="modal fade" id="rejectModal{{ $payout->id }}" tabindex="-1">
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
                            The payout will be rejected and <strong>₦{{ number_format($payout->amount, 2) }}</strong>
                            will be returned to the seller's wallet.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason *</label>
                            <textarea name="reason" class="form-control" rows="3" required
                                      placeholder="Explain why this payout is being rejected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject & Refund</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

@endforeach

@endsection

@push('scripts')
<script>
    lucide.createIcons();

    const selectAll      = document.getElementById('selectAll');
    const checkboxes     = document.querySelectorAll('.payout-checkbox');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const bulkCountSpan  = document.getElementById('bulkCount');
    const bulkForm       = document.getElementById('bulkForm');

    function updateBulk() {
        const checked = document.querySelectorAll('.payout-checkbox:checked');
        const count   = checked.length;
        if (bulkCountSpan)  bulkCountSpan.textContent  = count;
        if (bulkApproveBtn) bulkApproveBtn.disabled     = count === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulk();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulk));

    if (bulkForm) {
        bulkForm.addEventListener('submit', function () {
            this.querySelectorAll('input[name="payout_ids[]"]').forEach(i => i.remove());
            document.querySelectorAll('.payout-checkbox:checked').forEach(cb => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'payout_ids[]';
                input.value = cb.value;
                this.appendChild(input);
            });
        });
    }

    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
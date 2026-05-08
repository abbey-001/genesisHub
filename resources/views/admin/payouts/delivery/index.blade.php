@extends('admin.layouts.app')

@section('title', 'Delivery Company Payout Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right d-flex gap-2">
                <a href="{{ route('admin.delivery.payouts.export', request()->all()) }}"
                   class="btn btn-success">
                    <i data-lucide="download" class="me-1"></i>Export Report
                </a>
            </div>
            <h4 class="page-title">Delivery Company Payout Management</h4>
            <p class="text-muted">Review and process delivery company payout requests</p>
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
    PayoutService::getPayoutStats() keys:
      total_paid, total_pending, total_approved
      count_paid, count_pending, count_approved, count_rejected
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
                        <h3 class="mb-0 text-warning">₦{{ number_format($stats['total_pending'], 0) }}</h3>
                        <small class="text-muted">{{ $stats['count_pending'] }} request{{ $stats['count_pending'] != 1 ? 's' : '' }}</small>
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
                            <i data-lucide="check" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Approved (Unpaid)</p>
                        <h3 class="mb-0 text-info">₦{{ number_format($stats['total_approved'], 0) }}</h3>
                        <small class="text-muted">{{ $stats['count_approved'] }} payout{{ $stats['count_approved'] != 1 ? 's' : '' }}</small>
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
                        <p class="text-muted mb-1">Total Paid</p>
                        <h3 class="mb-0 text-success">₦{{ number_format($stats['total_paid'], 0) }}</h3>
                        <small class="text-muted">{{ $stats['count_paid'] }} payout{{ $stats['count_paid'] != 1 ? 's' : '' }}</small>
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
                        <p class="text-muted mb-1">Rejected</p>
                        <h3 class="mb-0 text-danger">{{ $stats['count_rejected'] }}</h3>
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
        <form method="GET" action="{{ route('admin.delivery.payouts.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search Reference</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Reference number..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid"     {{ request('status') === 'paid'     ? 'selected' : '' }}>Paid</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Company</label>
                <select name="company_id" class="form-select">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}"
                                {{ request('company_id') == $company->id ? 'selected' : '' }}>
                            {{ $company->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-1 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i data-lucide="filter" class="me-1"></i>Filter
                </button>
            </div>
            <div class="col-12">
                <a href="{{ route('admin.delivery.payouts.index') }}" class="btn btn-sm btn-secondary">
                    <i data-lucide="x" class="me-1"></i>Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Payouts Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payout Requests</h5>
        <div class="d-flex gap-2 align-items-center">
            <form action="{{ route('admin.delivery.payouts.batch-approve') }}" method="POST"
                  id="batchForm">
                @csrf
                <button type="submit" class="btn btn-sm btn-info" id="batchApproveBtn" disabled>
                    <i data-lucide="check-circle" class="me-1"></i>
                    Batch Approve (<span id="batchCount">0</span>)
                </button>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="40">
                        <input type="checkbox" class="form-check-input" id="selectAll">
                    </th>
                    <th>Reference</th>
                    <th>Company</th>
                    <th>Amount</th>
                    <th>Deliveries</th>
                    <th>Period</th>
                    <th>Status</th>
                    <th>Requested</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payouts as $payout)
                <tr>
                    <td>
                        @if($payout->isPending())
                        <input type="checkbox" class="form-check-input payout-checkbox"
                               value="{{ $payout->id }}">
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.delivery.payouts.show', $payout) }}"
                           class="text-primary fw-medium">
                            {{ $payout->reference_number }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $payout->company->full_name }}</div>
                        <small class="text-muted">{{ $payout->company->user->email }}</small>
                    </td>
                    <td>
                        <strong class="text-success">₦{{ number_format($payout->amount, 2) }}</strong>
                    </td>
                    <td>
                        <span class="badge bg-primary">{{ $payout->deliveries_count }} deliveries</span>
                    </td>
                    <td>
                        @if($payout->period_from && $payout->period_to)
                            <small class="text-muted">
                                {{ $payout->period_from->format('M d') }} –
                                {{ $payout->period_to->format('M d, Y') }}
                            </small>
                        @else
                            <small class="text-muted">N/A</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-{{ $payout->status_badge }}">
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
                                       href="{{ route('admin.delivery.payouts.show', $payout) }}">
                                        <i data-lucide="eye" class="me-2"></i>View Details
                                    </a>
                                </li>
                                @if($payout->canApprove())
                                <li>
                                    <button class="dropdown-item text-info"
                                            data-bs-toggle="modal"
                                            data-bs-target="#approveModal{{ $payout->id }}">
                                        <i data-lucide="check" class="me-2"></i>Approve
                                    </button>
                                </li>
                                @endif
                                @if($payout->canPay())
                                <li>
                                    <button class="dropdown-item text-success"
                                            data-bs-toggle="modal"
                                            data-bs-target="#payModal{{ $payout->id }}">
                                        <i data-lucide="banknote" class="me-2"></i>Mark as Paid
                                    </button>
                                </li>
                                @endif
                                @if($payout->canReject())
                                <li>
                                    <button class="dropdown-item text-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#rejectModal{{ $payout->id }}">
                                        <i data-lucide="x" class="me-2"></i>Reject
                                    </button>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
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
                Showing {{ $payouts->firstItem() }} to {{ $payouts->lastItem() }}
                of {{ $payouts->total() }}
            </div>
            {{ $payouts->links() }}
        </div>
    </div>
    @endif
</div>

{{--
    MODALS — rendered OUTSIDE the <table> so the DOM is valid and Bootstrap
    can reliably find and open them. Never place modals inside <tbody>/<tr>.
--}}
@foreach($payouts as $payout)

    {{-- Approve Modal --}}
    @if($payout->canApprove())
    <div class="modal fade" id="approveModal{{ $payout->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Payout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.delivery.payouts.approve', $payout) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <strong>Company:</strong> {{ $payout->company->full_name }}<br>
                            <strong>Amount:</strong> ₦{{ number_format($payout->amount, 2) }}<br>
                            <strong>Deliveries:</strong> {{ $payout->deliveries_count }}<br>
                            <strong>Bank:</strong> {{ $payout->bank_name }} — {{ $payout->account_number }}<br>
                            <strong>Account Name:</strong> {{ $payout->account_name }}
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

    {{-- Pay Modal --}}
    @if($payout->canPay())
    <div class="modal fade" id="payModal{{ $payout->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Mark as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.delivery.payouts.pay', $payout) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-success">
                            <strong>Company:</strong> {{ $payout->company->full_name }}<br>
                            <strong>Amount:</strong> ₦{{ number_format($payout->amount, 2) }}<br>
                            <strong>Bank:</strong> {{ $payout->bank_name }}<br>
                            <strong>Account:</strong> {{ $payout->account_number }} — {{ $payout->account_name }}
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="">Select method</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online_transfer">Online Transfer</option>
                                <option value="cheque">Cheque</option>
                                <option value="cash">Cash</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction Reference *</label>
                            <input type="text" name="transaction_reference" class="form-control"
                                   placeholder="e.g., TXN123456789" required>
                            <small class="text-muted">Enter the bank transaction reference or receipt number.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Reject Modal --}}
    @if($payout->canReject())
    <div class="modal fade" id="rejectModal{{ $payout->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Reject Payout</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.delivery.payouts.reject', $payout) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i data-lucide="alert-triangle" class="me-2"></i>
                            This payout will be rejected and the company will be notified.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason *</label>
                            <textarea name="rejection_reason" class="form-control" rows="3"
                                      required placeholder="Explain why this payout is being rejected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Payout</button>
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
    const batchBtn       = document.getElementById('batchApproveBtn');
    const batchCountSpan = document.getElementById('batchCount');
    const batchForm      = document.getElementById('batchForm');

    function updateBatch() {
        const checked = document.querySelectorAll('.payout-checkbox:checked');
        const count   = checked.length;
        if (batchCountSpan) batchCountSpan.textContent = count;
        if (batchBtn)       batchBtn.disabled           = count === 0;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBatch();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateBatch));

    if (batchForm) {
        batchForm.addEventListener('submit', function (e) {
            const checked = document.querySelectorAll('.payout-checkbox:checked');
            if (checked.length === 0) { e.preventDefault(); return; }
            // Remove previously appended inputs
            this.querySelectorAll('input[name="payout_ids[]"]').forEach(i => i.remove());
            checked.forEach(cb => {
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
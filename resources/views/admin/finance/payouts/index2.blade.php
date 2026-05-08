@extends('admin.layouts.app')

@section('title', 'Payout Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">💰 Payout Management</h4>
                <p class="text-muted mb-0">Approve and process seller payouts</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.finance.payouts.analytics') }}" class="btn btn-outline-primary">
                    <i data-lucide="bar-chart" class="me-1"></i>
                    Analytics
                </a>
                <a href="{{ route('admin.finance.payouts.export', request()->query()) }}" class="btn btn-outline-secondary">
                    <i data-lucide="download" class="me-1"></i>
                    Export
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Pending Approval</p>
                        <h4 class="fw-bold text-warning mb-0">{{ number_format($stats['pending']) }}</h4>
                        <small class="text-muted">₦{{ number_format($stats['pending_amount'], 2) }}</small>
                    </div>
                    <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                        <i data-lucide="clock" class="text-warning fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Processing</p>
                        <h4 class="fw-bold text-info mb-0">{{ number_format($stats['processing']) }}</h4>
                        <small class="text-muted">₦{{ number_format($stats['processing_amount'], 2) }}</small>
                    </div>
                    <div class="avatar-sm bg-info bg-opacity-10 rounded">
                        <i data-lucide="refresh-cw" class="text-info fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Completed Today</p>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($stats['completed_today']) }}</h4>
                        <small class="text-muted">₦{{ number_format($stats['completed_amount_today'], 2) }}</small>
                    </div>
                    <div class="avatar-sm bg-success bg-opacity-10 rounded">
                        <i data-lucide="check-circle" class="text-success fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Failed</p>
                        <h4 class="fw-bold text-danger mb-0">{{ number_format($stats['failed']) }}</h4>
                        <small class="text-muted">Needs attention</small>
                    </div>
                    <div class="avatar-sm bg-danger bg-opacity-10 rounded">
                        <i data-lucide="x-circle" class="text-danger fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.finance.payouts.index', ['status' => 'pending']) }}" 
                       class="btn btn-outline-warning {{ request('status') === 'pending' ? 'active' : '' }}">
                        <i data-lucide="clock" class="me-1"></i>
                        Pending ({{ $stats['pending'] }})
                    </a>
                    <a href="{{ route('admin.finance.payouts.index', ['status' => 'processing']) }}" 
                       class="btn btn-outline-info {{ request('status') === 'processing' ? 'active' : '' }}">
                        <i data-lucide="refresh-cw" class="me-1"></i>
                        Processing ({{ $stats['processing'] }})
                    </a>
                    <a href="{{ route('admin.finance.payouts.index', ['status' => 'completed']) }}" 
                       class="btn btn-outline-success {{ request('status') === 'completed' ? 'active' : '' }}">
                        <i data-lucide="check-circle" class="me-1"></i>
                        Completed
                    </a>
                    <a href="{{ route('admin.finance.payouts.index', ['status' => 'failed']) }}" 
                       class="btn btn-outline-danger {{ request('status') === 'failed' ? 'active' : '' }}">
                        <i data-lucide="x-circle" class="me-1"></i>
                        Failed
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payouts Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i data-lucide="list" class="me-2"></i>
                        Payout Requests
                    </h5>
                    @if(request('status') === 'pending')
                        <button type="button" 
                                class="btn btn-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#bulkApproveModal"
                                id="bulkApproveBtn"
                                disabled>
                            <i data-lucide="check-circle" class="me-1"></i>
                            Bulk Approve (<span id="selectedCount">0</span>)
                        </button>
                    @endif
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.finance.payouts.index') }}" method="GET" class="row g-3">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <div class="col-md-3">
                        <input type="search" name="search" class="form-control" 
                               placeholder="Search seller..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to') }}" placeholder="To">
                    </div>
                    <div class="col-md-2">
                        <select name="payout_method" class="form-select">
                            <option value="">All Methods</option>
                            <option value="bank_transfer" {{ request('payout_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="paypal" {{ request('payout_method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                            <option value="stripe" {{ request('payout_method') === 'stripe' ? 'selected' : '' }}>Stripe</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i data-lucide="search" class="me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.finance.payouts.index') }}" class="btn btn-outline-secondary">
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
                                @if(request('status') === 'pending')
                                    <th width="40">
                                        <input type="checkbox" class="form-check-input" id="selectAll">
                                    </th>
                                @endif
                                <th>ID</th>
                                <th>Seller</th>
                                <th>Shop</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payouts as $payout)
                            <tr>
                                @if(request('status') === 'pending')
                                    <td>
                                        <input type="checkbox" 
                                               class="form-check-input payout-checkbox" 
                                               value="{{ $payout->id }}">
                                    </td>
                                @endif
                                <td class="fw-medium">#{{ $payout->id }}</td>
                                <td>
                                    <div class="fw-medium">{{ $payout->seller->user->name }}</div>
                                    <small class="text-muted">{{ $payout->seller->user->email }}</small>
                                </td>
                                <td>
                                    @if($payout->seller->shop)
                                        <div>{{ $payout->seller->shop->shop_name }}</div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success fs-6">₦{{ number_format($payout->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucwords(str_replace('_', ' ', $payout->payout_method)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payout->status_badge }}">
                                        {{ ucfirst($payout->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $payout->requested_at->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $payout->requested_at->diffForHumans() }}</small>
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
                                                <a class="dropdown-item" href="{{ route('admin.finance.payouts.show', $payout) }}">
                                                    <i data-lucide="eye" class="me-2"></i>
                                                    View Details
                                                </a>
                                            </li>
                                            @if($payout->status === 'pending')
                                                <li>
                                                    <a class="dropdown-item text-success" 
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#approveModal{{ $payout->id }}">
                                                        <i data-lucide="check" class="me-2"></i>
                                                        Approve
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#rejectModal{{ $payout->id }}">
                                                        <i data-lucide="x" class="me-2"></i>
                                                        Reject
                                                    </a>
                                                </li>
                                            @endif
                                            @if($payout->status === 'processing')
                                                <li>
                                                    <a class="dropdown-item text-success" 
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#completeModal{{ $payout->id }}">
                                                        <i data-lucide="check-circle" class="me-2"></i>
                                                        Mark Completed
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" 
                                                       href="#"
                                                       data-bs-toggle="modal"
                                                       data-bs-target="#rejectModal{{ $payout->id }}">
                                                        <i data-lucide="x" class="me-2"></i>
                                                        Mark Failed
                                                    </a>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>

                            <!-- Approve Modal -->
                            <div class="modal fade" id="approveModal{{ $payout->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">
                                                <i data-lucide="check-circle" class="me-2"></i>
                                                Approve Payout
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.finance.payouts.approve', $payout) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-info">
                                                    <i data-lucide="info" class="me-2"></i>
                                                    This will approve the payout and mark it as "Processing". 
                                                    You'll need to process the actual bank transfer separately.
                                                </div>

                                                <div class="mb-3">
                                                    <h6>Payout Details</h6>
                                                    <ul class="list-unstyled mb-0">
                                                        <li>Seller: <strong>{{ $payout->seller->user->name }}</strong></li>
                                                        <li>Amount: <strong class="text-success">₦{{ number_format($payout->amount, 2) }}</strong></li>
                                                        <li>Method: <strong>{{ ucwords(str_replace('_', ' ', $payout->payout_method)) }}</strong></li>
                                                    </ul>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Notes (Optional)</label>
                                                    <textarea name="notes" class="form-control" rows="2" 
                                                              placeholder="Add any notes about this approval..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i data-lucide="check" class="me-1"></i>
                                                    Approve Payout
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Complete Modal -->
                            <div class="modal fade" id="completeModal{{ $payout->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">
                                                <i data-lucide="check-circle-2" class="me-2"></i>
                                                Mark Payout as Completed
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.finance.payouts.complete', $payout) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i data-lucide="alert-triangle" class="me-2"></i>
                                                    Only mark as completed AFTER you've successfully transferred the funds to the seller.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Transaction Reference *</label>
                                                    <input type="text" 
                                                           name="transaction_reference" 
                                                           class="form-control" 
                                                           required
                                                           placeholder="Bank transaction reference number">
                                                    <small class="text-muted">The reference from your bank transfer</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Completion Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2" 
                                                              placeholder="Add completion notes..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i data-lucide="check-circle" class="me-1"></i>
                                                    Mark as Completed
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $payout->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">
                                                <i data-lucide="x-circle" class="me-2"></i>
                                                {{ $payout->status === 'processing' ? 'Mark as Failed' : 'Reject Payout' }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.finance.payouts.reject', $payout) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="alert alert-warning">
                                                    <i data-lucide="alert-triangle" class="me-2"></i>
                                                    This will {{ $payout->status === 'processing' ? 'mark the payout as failed' : 'reject the payout request' }} 
                                                    and return the funds to the seller's wallet.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Reason *</label>
                                                    <textarea name="reason" 
                                                              class="form-control" 
                                                              rows="3" 
                                                              required
                                                              placeholder="Provide a clear reason for {{ $payout->status === 'processing' ? 'failure' : 'rejection' }}..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i data-lucide="x" class="me-1"></i>
                                                    {{ $payout->status === 'processing' ? 'Mark as Failed' : 'Reject Payout' }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No payouts found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($payouts->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $payouts->firstItem() }} to {{ $payouts->lastItem() }} 
                        of {{ $payouts->total() }} payouts
                    </div>
                    {{ $payouts->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i data-lucide="check-circle" class="me-2"></i>
                    Bulk Approve Payouts
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.finance.payouts.bulkApprove') }}" method="POST" id="bulkApproveForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i data-lucide="info" class="me-2"></i>
                        You are about to approve <strong id="bulkCount">0</strong> payout(s).
                    </div>
                    <p>This will move all selected payouts to "Processing" status. You'll need to process the actual bank transfers separately.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i data-lucide="check-circle" class="me-1"></i>
                        Approve Selected
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

    // Bulk selection
    const selectAllCheckbox = document.getElementById('selectAll');
    const payoutCheckboxes = document.querySelectorAll('.payout-checkbox');
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkCountSpan = document.getElementById('bulkCount');
    const bulkApproveForm = document.getElementById('bulkApproveForm');

    function updateBulkButton() {
        const selected = document.querySelectorAll('.payout-checkbox:checked');
        const count = selected.length;
        
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        if (bulkCountSpan) bulkCountSpan.textContent = count;
        
        if (bulkApproveBtn) {
            bulkApproveBtn.disabled = count === 0;
        }
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            payoutCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkButton();
        });
    }

    payoutCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkButton();
            
            // Update select all checkbox
            if (selectAllCheckbox) {
                const allChecked = Array.from(payoutCheckboxes).every(cb => cb.checked);
                selectAllCheckbox.checked = allChecked;
            }
        });
    });

    // Handle bulk approve form submission
    if (bulkApproveForm) {
        bulkApproveForm.addEventListener('submit', function(e) {
            const selected = document.querySelectorAll('.payout-checkbox:checked');
            
            // Remove existing hidden inputs
            const existingInputs = bulkApproveForm.querySelectorAll('input[name="payout_ids[]"]');
            existingInputs.forEach(input => input.remove());
            
            // Add selected IDs
            selected.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payout_ids[]';
                input.value = checkbox.value;
                bulkApproveForm.appendChild(input);
            });
        });
    }
</script>
@endpush
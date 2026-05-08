@extends('admin.layouts.app')

@section('title', 'Payout Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.delivery.payouts.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Payouts
                </a>
            </div>
            <h4 class="page-title">Payout Details</h4>
            <p class="text-muted">Reference: <strong>{{ $payout->reference_number }}</strong></p>
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

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">

        <!-- Status & Action Hero -->
        <div class="card mb-4 border-{{ $payout->status_badge }}">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="mb-1">₦{{ number_format($payout->amount, 2) }}</h2>
                        <span class="badge bg-{{ $payout->status_badge }} fs-6">
                            {{ $payout->status_label }}
                        </span>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-column gap-2">
                        @if($payout->canApprove())
                            <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i data-lucide="check" class="me-1"></i>Approve
                            </button>
                        @endif
                        @if($payout->canPay())
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payModal">
                                <i data-lucide="banknote" class="me-1"></i>Mark as Paid
                            </button>
                        @endif
                        @if($payout->canReject())
                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i data-lucide="x-circle" class="me-1"></i>Reject
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Company Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Company Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Company Name</small>
                        <strong>{{ $payout->company->full_name }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Email</small>
                        <strong>{{ $payout->company->user->email }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <strong>{{ $payout->company->phone_number }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-muted d-block">Total Completed Deliveries</small>
                        <strong>{{ number_format($payout->company->completed_deliveries) }}</strong>
                    </div>
                </div>
                <a href="{{ route('admin.companies.show', $payout->company) }}"
                   class="btn btn-sm btn-outline-primary">
                    <i data-lucide="eye" class="me-1"></i>View Company Profile
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
                        <strong>{{ $payout->bank_name ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Account Number</small>
                        <strong class="font-monospace">{{ $payout->account_number ?: 'N/A' }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted d-block">Account Name</small>
                        <strong>{{ $payout->account_name ?: 'N/A' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Included Deliveries -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    Included Deliveries ({{ $payout->deliveries_count }})
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Delivered At</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payout->deliveries as $delivery)
                        <tr>
                            <td>
                                <a href="{{ route('admin.deliveries.show', $delivery) }}"
                                   class="text-primary fw-medium">
                                    {{ $delivery->order->order_number }}
                                </a>
                            </td>
                            <td>{{ $delivery->order->customer_name }}</td>
                            <td>
                                @if($delivery->delivered_at)
                                    {{ $delivery->delivered_at->format('M d, Y h:i A') }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><strong>₦{{ number_format($delivery->delivery_fee, 2) }}</strong></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-3 text-muted">
                                No deliveries attached
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>₦{{ number_format($payout->amount, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>

    <!-- Right Column: Timeline & Info -->
    <div class="col-lg-4">

        <!-- Additional Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Reference:</td>
                        <td class="fw-bold font-monospace">{{ $payout->reference_number }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            <span class="badge bg-{{ $payout->status_badge }}">
                                {{ $payout->status_label }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Amount:</td>
                        <td class="fw-bold">₦{{ number_format($payout->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deliveries:</td>
                        <td>{{ $payout->deliveries_count }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Avg / Delivery:</td>
                        <td>₦{{ number_format($payout->amount / max($payout->deliveries_count, 1), 2) }}</td>
                    </tr>
                    @if($payout->period_from && $payout->period_to)
                    <tr>
                        <td class="text-muted">Period:</td>
                        <td>
                            {{ $payout->period_from->format('M d') }} –
                            {{ $payout->period_to->format('M d, Y') }}
                        </td>
                    </tr>
                    @endif
                    @if($payout->payment_method)
                    <tr>
                        <td class="text-muted">Method:</td>
                        <td>{{ ucwords(str_replace('_', ' ', $payout->payment_method)) }}</td>
                    </tr>
                    @endif
                    @if($payout->transaction_reference)
                    <tr>
                        <td class="text-muted">Txn Ref:</td>
                        <td class="font-monospace small">{{ $payout->transaction_reference }}</td>
                    </tr>
                    @endif
                </table>

                @if($payout->notes)
                <div class="alert alert-info py-2 mt-3 mb-0">
                    <small><strong>Notes:</strong> {{ $payout->notes }}</small>
                </div>
                @endif

                @if($payout->rejection_reason)
                <div class="alert alert-danger py-2 mt-3 mb-0">
                    <small><strong>Rejection Reason:</strong> {{ $payout->rejection_reason }}</small>
                </div>
                @endif
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
                            <small class="text-muted">
                                By: {{ $payout->company->full_name }}
                            </small>
                        </div>
                    </div>

                    <!-- Approved -->
                    @if($payout->approved_at)
                    <div class="timeline-item">
                        <i data-lucide="check" class="text-info timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Approved</h6>
                            <small class="text-muted">
                                {{ $payout->approved_at->format('M d, Y h:i A') }}
                            </small><br>
                            {{-- Null-safe: approvedBy may not always be loaded --}}
                            <small class="text-muted">
                                By: {{ $payout->approvedBy?->name ?? 'Admin' }}
                            </small>
                        </div>
                    </div>
                    @endif

                    <!-- Paid -->
                    @if($payout->paid_at)
                    <div class="timeline-item">
                        <i data-lucide="check-circle" class="text-success timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Paid</h6>
                            <small class="text-muted">
                                {{ $payout->paid_at->format('M d, Y h:i A') }}
                            </small><br>
                            {{-- Null-safe: paidBy may not always be loaded --}}
                            <small class="text-muted">
                                By: {{ $payout->paidBy?->name ?? 'Admin' }}
                            </small>
                            @if($payout->payment_method)
                            <br><small class="text-muted">
                                Method: {{ ucwords(str_replace('_', ' ', $payout->payment_method)) }}
                            </small>
                            @endif
                            @if($payout->transaction_reference)
                            <br><small class="text-muted">
                                Ref: {{ $payout->transaction_reference }}
                            </small>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Rejected -->
                    @if($payout->rejected_at)
                    <div class="timeline-item">
                        <i data-lucide="x-circle" class="text-danger timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Rejected</h6>
                            <small class="text-muted">
                                {{ $payout->rejected_at->format('M d, Y h:i A') }}
                            </small><br>
                            {{-- Null-safe --}}
                            <small class="text-muted">
                                By: {{ $payout->rejectedBy?->name ?? 'Admin' }}
                            </small>
                            @if($payout->rejection_reason)
                            <div class="alert alert-danger py-1 mt-1 mb-0">
                                <small>{{ $payout->rejection_reason }}</small>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Current pending state indicator -->
                    @if($payout->isPending())
                    <div class="timeline-item">
                        <i data-lucide="clock" class="text-warning timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Awaiting Approval</h6>
                            <small class="text-muted">Pending admin review</small>
                        </div>
                    </div>
                    @elseif($payout->isApproved())
                    <div class="timeline-item">
                        <i data-lucide="loader" class="text-info timeline-icon"></i>
                        <div class="timeline-item-info">
                            <h6 class="mb-1">Awaiting Payment</h6>
                            <small class="text-muted">Bank transfer pending</small>
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
@if($payout->canApprove())
<div class="modal fade" id="approveModal" tabindex="-1">
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
                        <label class="form-label">Approval Notes <span class="text-muted">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="3"
                                  placeholder="Add any notes..."></textarea>
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

<!-- Pay Modal -->
@if($payout->canPay())
<div class="modal fade" id="payModal" tabindex="-1">
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
                        <strong>Amount to Pay:</strong> ₦{{ number_format($payout->amount, 2) }}<br>
                        <strong>Bank:</strong> {{ $payout->bank_name }}<br>
                        <strong>Account:</strong> {{ $payout->account_number }}<br>
                        <strong>Account Name:</strong> {{ $payout->account_name }}
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
                    <button type="submit" class="btn btn-success">
                        <i data-lucide="check-circle" class="me-1"></i>Confirm Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Reject Modal -->
@if($payout->canReject())
<div class="modal fade" id="rejectModal" tabindex="-1">
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
                        This payout will be rejected and
                        <strong>{{ $payout->company->full_name }}</strong> will be notified.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason *</label>
                        <textarea name="rejection_reason" class="form-control" rows="4"
                                  required placeholder="Explain why this payout is being rejected..."></textarea>
                        <small class="text-muted">This reason will be sent to the company.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i data-lucide="x-circle" class="me-1"></i>Reject Payout
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<style>
    .timeline-alt { position: relative; padding: 0; }
    .timeline-item {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
        position: relative;
    }
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 28px;
        bottom: -12px;
        width: 2px;
        background: #dee2e6;
    }
    .timeline-icon {
        width: 24px;
        height: 24px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .timeline-item-info { padding-top: 2px; }
</style>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
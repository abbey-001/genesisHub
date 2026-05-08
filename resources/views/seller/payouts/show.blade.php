{{-- resources/views/seller/payouts/show.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Withdrawal Details')

@section('content')

@push('styles')
<style>
.timeline {
    list-style: none;
    position: relative;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 6px;
    top: 20px;
    bottom: -20px;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: 0;
    top: 5px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px currentColor;
}

.timeline-content {
    padding-top: 2px;
}
</style>
@endpush
<div class="container-xxl">
    
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h4 class="mb-1">💸 Withdrawal Request Details</h4>
            <p class="text-muted mb-0">Reference #{{ $payout->id }}</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('seller.payouts.index') }}" class="btn btn-outline-secondary btn-sm">
                <i data-lucide="arrow-left" class="fs-16"></i> Back to Wallet
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Details -->
        <div class="col-md-8">
            <!-- Status Card -->
            <div class="card shadow-sm border-{{ $payout->status_badge }} mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="mb-2">
                                <span class="badge bg-{{ $payout->status_badge }} fs-6 px-3 py-2">
                                    {{ $payout->status_label }}
                                </span>
                            </div>
                            <h2 class="mb-1">₦{{ number_format($payout->amount, 2) }}</h2>
                            <p class="text-muted mb-0">
                                Requested on {{ $payout->requested_at->format('F d, Y \a\t h:i A') }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            @if($payout->canBeCancelled())
                                <button type="button" 
                                        class="btn btn-danger w-100"
                                        onclick="cancelPayout()">
                                    <i data-lucide="x-circle" class="fs-16"></i> Cancel Request
                                </button>
                            @elseif($payout->isCompleted())
                                <div class="text-success">
                                    <i data-lucide="check-circle" style="width: 48px; height: 48px;"></i>
                                    <div class="mt-2 fw-bold">Completed</div>
                                </div>
                            @elseif($payout->isFailed())
                                <div class="text-danger">
                                    <i data-lucide="x-circle" style="width: 48px; height: 48px;"></i>
                                    <div class="mt-2 fw-bold">Failed</div>
                                </div>
                            @else
                                <div class="text-warning">
                                    <i data-lucide="clock" style="width: 48px; height: 48px;"></i>
                                    <div class="mt-2 fw-bold">Processing</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amount Breakdown -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">💰 Amount Breakdown</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Withdrawal Amount:</td>
                                <td class="text-end fw-bold">₦{{ number_format($payout->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Processing Fee ({{ $payout->fee_percentage }}%):</td>
                                <td class="text-end text-danger">
                                    @if($payout->fee_amount > 0)
                                        -₦{{ number_format($payout->fee_amount, 2) }}
                                    @else
                                        <span class="text-success fw-bold">FREE</span>
                                    @endif
                                </td>
                            </tr>
                            <tr class="border-top">
                                <td class="fw-bold fs-5">You Receive:</td>
                                <td class="text-end fw-bold fs-5 text-success">₦{{ number_format($payout->net_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">📋 Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Payment Method</small>
                            <strong>{{ $payout->payout_method_label }}</strong>
                        </div>
                        <div class="col-md-6 mb-3">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge bg-{{ $payout->status_badge }}">{{ $payout->status_label }}</span>
                        </div>
                        
                        @if($payout->transaction_id)
                        <div class="col-md-12 mb-3">
                            <small class="text-muted d-block">Transaction Reference</small>
                            <code class="bg-light px-2 py-1 rounded">{{ $payout->transaction_id }}</code>
                        </div>
                        @endif
                        
                        @if($payout->notes)
                        <div class="col-md-12">
                            <small class="text-muted d-block">Notes</small>
                            <div class="alert alert-info py-2 mb-0">
                                <small>{{ $payout->notes }}</small>
                            </div>
                        </div>
                        @endif
                        
                        @if($payout->failure_reason)
                        <div class="col-md-12 mt-3">
                            <div class="alert alert-danger">
                                <strong><i data-lucide="alert-circle" class="fs-16"></i> Failure Reason:</strong>
                                <p class="mb-0 mt-2">{{ $payout->failure_reason }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Timeline -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">⏱️ Timeline</h6>
                </div>
                <div class="card-body">
                    <ul class="timeline ps-0">
                        <li class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Requested</h6>
                                <small class="text-muted">
                                    {{ $payout->requested_at->format('M d, Y') }}<br>
                                    {{ $payout->requested_at->format('h:i A') }}
                                </small>
                            </div>
                        </li>

                        @if($payout->isCompleted())
                        <li class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Completed</h6>
                                <small class="text-muted">
                                    {{ $payout->processed_at->format('M d, Y') }}<br>
                                    {{ $payout->processed_at->format('h:i A') }}
                                </small>
                            </div>
                        </li>
                        @elseif($payout->isFailed())
                        <li class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1 text-danger">Failed</h6>
                                <small class="text-muted">
                                    {{ $payout->failed_at->format('M d, Y') }}<br>
                                    {{ $payout->failed_at->format('h:i A') }}
                                </small>
                            </div>
                        </li>
                        @else
                        <li class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">In Progress</h6>
                                <small class="text-muted">Being processed...</small>
                            </div>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i data-lucide="help-circle" class="fs-16"></i> Processing Time
                    </h6>
                    <p class="small mb-0">
                        Withdrawals are typically processed within <strong>3-5 business days</strong>.
                        You'll receive a notification once your payment has been sent.
                    </p>
                    
                    @if($payout->isPending())
                    <hr class="my-3">
                    <p class="small mb-0">
                        <i data-lucide="info" class="fs-14"></i>
                        Your request is being reviewed by our team. We'll notify you once it's approved.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Cancel Form (hidden) -->
<form id="cancelForm" action="{{ route('seller.payouts.cancel', $payout) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection



@push('scripts')
<script>
lucide.createIcons();

function cancelPayout() {
    if (confirm('Are you sure you want to cancel this withdrawal request?\n\nThe ₦{{ number_format($payout->amount, 2) }} will be returned to your available balance.')) {
        document.getElementById('cancelForm').submit();
    }
}
</script>
@endpush
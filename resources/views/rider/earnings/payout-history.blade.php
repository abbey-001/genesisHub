@extends('rider.layouts.app')

@section('title', 'Request Payout')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Request Payout</h4>
            <p class="text-muted mb-0">Withdraw your available earnings</p>
        </div>
        <a href="{{ route('rider.earnings.index') }}" class="btn btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i>Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('rider.earnings.request-payout') }}" method="POST">
                @csrf
                
                <!-- Balance Card -->
                <div class="card mb-4 border-success">
                    <div class="card-body">
                        <h3 class="text-success mb-2">₦{{ number_format($balance['available_balance'], 2) }}</h3>
                        <p class="mb-0">Available for Payout</p>
                    </div>
                </div>

                <!-- Payout Amount -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payout Amount</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Amount to Withdraw *</label>
                            <div class="input-group">
                                <span class="input-group-text">₦</span>
                                <input type="number" 
                                       name="amount" 
                                       class="form-control @error('amount') is-invalid @enderror" 
                                       value="{{ old('amount', $balance['available_balance']) }}"
                                       min="1000" 
                                       max="{{ $balance['available_balance'] }}"
                                       step="100"
                                       placeholder="Enter amount"
                                       required>
                            </div>
                            @error('amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                Minimum: ₦1,000 | Maximum: ₦{{ number_format($balance['available_balance'], 2) }}
                            </div>
                        </div>

                        <!-- Quick Amount Buttons -->
                        <div class="d-flex gap-2 flex-wrap">
                            @php
                                $quickAmounts = [5000, 10000, 20000, 50000];
                            @endphp
                            @foreach($quickAmounts as $quick)
                                @if($quick <= $balance['available_balance'])
                                    <button type="button" class="btn btn-sm btn-label-primary" onclick="document.querySelector('input[name=amount]').value = {{ $quick }}">
                                        ₦{{ number_format($quick, 0) }}
                                    </button>
                                @endif
                            @endforeach
                            <button type="button" class="btn btn-sm btn-label-success" onclick="document.querySelector('input[name=amount]').value = {{ $balance['available_balance'] }}">
                                All (₦{{ number_format($balance['available_balance'], 0) }})
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bank Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Bank Account Details</h5>
                    </div>
                    <div class="card-body">
                        @if(Auth::user()->rider->bank_name)
                            <div class="alert alert-info">
                                <strong>Payment will be sent to:</strong><br>
                                Bank: {{ Auth::user()->rider->bank_name }}<br>
                                Account: {{ Auth::user()->rider->account_number }}<br>
                                Name: {{ Auth::user()->rider->account_name }}
                            </div>
                            <a href="{{ route('rider.profile.index') }}" class="btn btn-sm btn-label-primary">
                                <i class="bx bx-edit me-1"></i>Update Bank Details
                            </a>
                        @else
                            <div class="alert alert-warning">
                                <i class="bx bx-error-circle me-2"></i>
                                <strong>Bank details not set!</strong> Please add your bank account details to proceed.
                            </div>
                            <a href="{{ route('rider.profile.index') }}" class="btn btn-primary">
                                <i class="bx bx-plus me-1"></i>Add Bank Details
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Unpaid Deliveries Selection -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Unpaid Deliveries ({{ $unpaidDeliveries->count() }})</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-label-primary" onclick="selectAll()">Select All</button>
                            <button type="button" class="btn btn-sm btn-label-secondary" onclick="deselectAll()">Deselect All</button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox" onchange="toggleAll(this)" checked>
                                        </th>
                                        <th>Date</th>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($unpaidDeliveries as $delivery)
                                    <tr>
                                        <td>
                                            <input type="checkbox" 
                                                   name="delivery_ids[]" 
                                                   value="{{ $delivery->id }}" 
                                                   class="form-check-input delivery-checkbox"
                                                   data-amount="{{ $delivery->delivery_fee }}"
                                                   onchange="updateTotal()"
                                                   checked>
                                        </td>
                                        <td>{{ $delivery->delivered_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('rider.deliveries.show', $delivery) }}" target="_blank">
                                                {{ $delivery->order->order_number }}
                                            </a>
                                        </td>
                                        <td>{{ $delivery->order->customer_name }}</td>
                                        <td><strong>₦{{ number_format($delivery->delivery_fee, 2) }}</strong></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <i class="bx bx-info-circle bx-lg text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No unpaid deliveries</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I confirm that the bank details are correct and I understand that payouts are processed within 3-5 business days. *
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                @if(Auth::user()->rider->bank_name)
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('rider.earnings.index') }}" class="btn btn-label-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="bx bx-send me-1"></i>Submit Payout Request
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <!-- Info Sidebar -->
        <div class="col-lg-4">
            <!-- Balance Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-info-circle me-2"></i>Balance Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Earnings:</span>
                        <strong>₦{{ number_format($balance['total_earnings'], 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Paid Out:</span>
                        <strong class="text-success">₦{{ number_format($balance['total_paid_out'], 0) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pending:</span>
                        <strong class="text-warning">₦{{ number_format($balance['total_pending'], 0) }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Available:</strong>
                        <strong class="text-success">₦{{ number_format($balance['available_balance'], 0) }}</strong>
                    </div>
                </div>
            </div>

            <!-- Payout Info -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bx bx-help-circle me-2"></i>Payout Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Processing Time</strong>
                        <p class="mb-0 small text-muted">Payouts are processed within 3-5 business days after approval</p>
                    </div>
                    <div class="mb-3">
                        <strong>Minimum Amount</strong>
                        <p class="mb-0 small text-muted">₦1,000</p>
                    </div>
                    <div class="mb-3">
                        <strong>Payout Fees</strong>
                        <p class="mb-0 small text-muted">No fees charged</p>
                    </div>
                    <div class="mb-0">
                        <strong>Working Hours</strong>
                        <p class="mb-0 small text-muted">Requests submitted on weekends will be processed on the next business day</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleAll(checkbox) {
    document.querySelectorAll('.delivery-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateTotal();
}

function selectAll() {
    document.querySelectorAll('.delivery-checkbox').forEach(cb => {
        cb.checked = true;
    });
    document.getElementById('selectAllCheckbox').checked = true;
    updateTotal();
}

function deselectAll() {
    document.querySelectorAll('.delivery-checkbox').forEach(cb => {
        cb.checked = false;
    });
    document.getElementById('selectAllCheckbox').checked = false;
    updateTotal();
}

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.delivery-checkbox:checked').forEach(cb => {
        total += parseFloat(cb.dataset.amount);
    });
    document.querySelector('input[name="amount"]').value = total.toFixed(2);
}

// Initialize
updateTotal();
</script>
@endpush

@endsection
@extends('admin.layouts.app')

@section('title', 'Refund Requests')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Refund Requests</h4>
            <p class="text-muted">Review and process customer refund requests</p>
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
    Stats keys from RefundController::index():
      pending, completed, rejected, total_refunded
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
                        <p class="text-muted mb-1">Pending Review</p>
                        <h3 class="mb-0 text-warning">{{ $stats['pending'] }}</h3>
                        <small class="text-muted">require action</small>
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
                        <p class="text-muted mb-1">Completed</p>
                        <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                        <small class="text-muted">refunds processed</small>
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
                        <h3 class="mb-0 text-danger">{{ $stats['rejected'] }}</h3>
                        <small class="text-muted">declined requests</small>
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
                            <i data-lucide="banknote" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Refunded</p>
                        <h3 class="mb-0 text-info">₦{{ number_format($stats['total_refunded'], 0) }}</h3>
                        <small class="text-muted">all time</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.finance.refunds.index') }}" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Order #, customer name or email..."
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="refund_pending"
                        {{ request('status') === 'refund_pending'  ? 'selected' : '' }}>
                        Pending Review
                    </option>
                    <option value="refunded"
                        {{ request('status') === 'refunded'        ? 'selected' : '' }}>
                        Completed
                    </option>
                    <option value="refund_rejected"
                        {{ request('status') === 'refund_rejected' ? 'selected' : '' }}>
                        Rejected
                    </option>
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
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i data-lucide="filter" class="me-1"></i>Filter
                </button>
            </div>
            <div class="col-12">
                <a href="{{ route('admin.finance.refunds.index') }}" class="btn btn-sm btn-secondary">
                    <i data-lucide="x" class="me-1"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Refund Requests</h5>
        @if($stats['pending'] > 0)
        <span class="badge bg-warning text-dark">
            {{ $stats['pending'] }} pending action
        </span>
        @endif
    </div>

    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Order #</th>
                    <th>Customer</th>
                    <th>Order Total</th>
                    <th>Payment Ref</th>
                    <th>Cancelled</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $order)
                <tr class="{{ $order->payment_status === 'refund_pending' ? 'table-warning bg-opacity-25' : '' }}">
                    <td>
                        <a href="{{ route('admin.finance.refunds.show', $order) }}"
                           class="text-primary fw-bold">
                            #{{ $order->order_number }}
                        </a>
                    </td>
                    <td>
                        <div class="fw-medium">{{ $order->customer_name }}</div>
                        <small class="text-muted">{{ $order->customer_email }}</small>
                    </td>
                    <td>
                        <strong>₦{{ number_format($order->total, 2) }}</strong>
                        @if($order->refund_amount && $order->payment_status === 'refunded')
                        <br><small class="text-success">
                            Refunded: ₦{{ number_format($order->refund_amount, 2) }}
                        </small>
                        @endif
                    </td>
                    <td>
                        @if($order->payment_reference)
                        <code class="small">{{ Str::limit($order->payment_reference, 18) }}</code>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($order->cancelled_at)
                        {{ $order->cancelled_at->format('d M, Y') }}<br>
                        <small class="text-muted">{{ $order->cancelled_at->format('h:i A') }}</small>
                        @else
                        <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($order->payment_status === 'refund_pending')
                            <span class="badge bg-warning text-dark">Pending Review</span>
                        @elseif($order->payment_status === 'refunded')
                            <span class="badge bg-success">Refunded</span>
                        @elseif($order->payment_status === 'refund_rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.finance.refunds.show', $order) }}"
                           class="btn btn-sm {{ $order->payment_status === 'refund_pending' ? 'btn-warning' : 'btn-outline-secondary' }}">
                            @if($order->payment_status === 'refund_pending')
                                <i data-lucide="eye" class="me-1"></i>Review
                            @else
                                <i data-lucide="eye" class="me-1"></i>View
                            @endif
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i data-lucide="inbox" class="text-muted mb-3" style="width:48px;height:48px;"></i>
                        <p class="text-muted mb-0">No refund requests found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($refunds->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $refunds->firstItem() }} to {{ $refunds->lastItem() }}
                of {{ $refunds->total() }}
            </div>
            {{ $refunds->links() }}
        </div>
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
@extends('admin.layouts.app')

@section('title', 'Orders')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.orders.analytics') }}" class="btn btn-info">
                        <i data-lucide="bar-chart" class="me-1"></i>Analytics
                    </a>
                    <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn btn-success">
                        <i data-lucide="download" class="me-1"></i>Export
                    </a>
                </div>
            </div>
            <h4 class="page-title">Order Management</h4>
            <p class="text-muted">Manage all customer orders</p>
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

<!-- Statistics -->
<div class="row">
    <div class="col-xl-2 col-sm-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                            <i data-lucide="shopping-cart" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Orders</p>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                            <i data-lucide="clock" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Pending</p>
                        <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                            <i data-lucide="loader" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Processing</p>
                        <h4 class="mb-0">{{ number_format($stats['processing']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                            <i data-lucide="truck" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Shipped</p>
                        <h4 class="mb-0">{{ number_format($stats['shipped']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4">
        <div class="card border-success">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                            <i data-lucide="dollar-sign" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Revenue</p>
                        <h4 class="mb-0">₦{{ number_format($stats['total_revenue'], 0) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-sm-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                            <i data-lucide="calendar" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Today</p>
                        <h4 class="mb-0">{{ number_format($stats['today_orders']) }}</h4>
                        <small class="text-success">₦{{ number_format($stats['today_revenue'], 0) }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orders Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Orders</h5>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search order #, name, email, phone..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped"    {{ request('status') === 'shipped'    ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered"  {{ request('status') === 'delivered'  ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled"  {{ request('status') === 'cancelled'  ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_status" class="form-select">
                            <option value="">Payment Status</option>
                            <option value="pending"  {{ request('payment_status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                            <option value="paid"     {{ request('payment_status') === 'paid'     ? 'selected' : '' }}>Paid</option>
                            <option value="failed"   {{ request('payment_status') === 'failed'   ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('payment_status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i data-lucide="filter" class="me-1"></i>Filter
                        </button>
                    </div>
                    <div class="col-12">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary">
                            <i data-lucide="x" class="me-1"></i>Clear Filters
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="card-body border-bottom">
                <form id="bulkActionForm" action="{{ route('admin.orders.bulk-action') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <select name="action" class="form-select" required>
                            <option value="">Bulk Actions</option>
                            <option value="mark_processing">Mark as Processing</option>
                            <option value="mark_shipped">Mark as Shipped</option>
                            <option value="mark_delivered">Mark as Delivered</option>
                            <option value="cancel">Cancel Orders</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="zap" class="me-1"></i>Apply
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td>
                                    <input type="checkbox" name="order_ids[]" value="{{ $order->id }}"
                                           class="form-check-input order-checkbox">
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-medium text-primary">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-medium">{{ $order->customer_name }}</div>
                                    <small class="text-muted">{{ $order->customer_email }}</small>
                                </td>
                                <td>
                                    {{ $order->created_at->format('d M, Y') }}<br>
                                    <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $order->items->count() }}</span>
                                </td>
                                <td class="fw-bold">₦{{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status_badge }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span><br>
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.orders.show', $order) }}">
                                                    <i data-lucide="eye" class="me-2 fs-16"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.orders.invoice', $order) }}"
                                                   target="_blank">
                                                    <i data-lucide="printer" class="me-2 fs-16"></i>Print Invoice
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            {{-- Refund: navigate to the dedicated refund page --}}
                                            @if($order->payment_status === 'paid')
                                            <li>
                                                <a class="dropdown-item text-info"
                                                   href="{{ route('admin.finance.refunds.show', $order) }}">
                                                    <i data-lucide="rotate-ccw" class="me-2 fs-16"></i>Process Refund
                                                </a>
                                            </li>
                                            @endif
                                            @if($order->canBeCancelled())
                                            <li>
                                                <button type="button" class="dropdown-item text-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#cancelModal{{ $order->id }}">
                                                    <i data-lucide="x-circle" class="me-2 fs-16"></i>Cancel Order
                                                </button>
                                            </li>
                                            @endif
                                        </ul>
                                    </div>

                                    @if($order->canBeCancelled())
                                    <div class="modal fade" id="cancelModal{{ $order->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Cancel Order</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i data-lucide="alert-triangle" class="me-2"></i>
                                                            Cancel <strong>{{ $order->order_number }}</strong>? Product stock will be restored.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason *</label>
                                                            <textarea name="reason" class="form-control" rows="3"
                                                                      required placeholder="Enter reason..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i data-lucide="x-circle" class="me-1"></i>Cancel Order
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No orders found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($orders->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }}
                    </div>
                    {{ $orders->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();

    document.getElementById('selectAll').addEventListener('change', function () {
        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
    });

    document.getElementById('bulkActionForm').addEventListener('submit', function (e) {
        const checked = document.querySelectorAll('.order-checkbox:checked');
        if (checked.length === 0) {
            e.preventDefault();
            alert('Please select at least one order.');
            return;
        }
        const action = this.querySelector('[name="action"]').value;
        if (!action) {
            e.preventDefault();
            alert('Please choose a bulk action.');
            return;
        }
        if (!confirm(`Apply "${action.replace(/_/g, ' ')}" to ${checked.length} order(s)?`)) {
            e.preventDefault();
            return;
        }
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'order_ids[]';
            input.value = cb.value;
            this.appendChild(input);
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
@extends('admin.layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title">Customer Details</h4>
                    <p class="text-muted mb-0">View and manage customer information</p>
                </div>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Customers
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <!-- Customer Profile -->
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-xl mx-auto mb-3">
                    <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fs-32">
                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                    </div>
                </div>
                <h4 class="mb-1">{{ $customer->name }}</h4>
                <p class="text-muted mb-3">{{ $customer->email }}</p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if($customer->deleted_at)
                        <span class="badge bg-danger fs-14 px-3 py-2">Blocked</span>
                    @else
                        <span class="badge bg-success fs-14 px-3 py-2">Active</span>
                    @endif
                </div>

                <div class="d-flex justify-content-center gap-2">
                    @if($customer->deleted_at)
                        {{--
                            Pass raw ID — soft-deleted users won't resolve via
                            normal route model binding.
                        --}}
                        <form action="{{ route('admin.customers.unblock', $customer->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">
                                <i data-lucide="check-circle" class="me-1"></i>Unblock Customer
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#blockModal">
                            <i data-lucide="x-circle" class="me-1"></i>Block Customer
                        </button>
                    @endif
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notifyModal">
                        <i data-lucide="bell" class="me-1"></i>Send Notification
                    </button>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="user" class="me-2"></i>Customer Information
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted">Customer ID:</td>
                            <td class="fw-bold">#{{ $customer->id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td>{{ $customer->email }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Phone:</td>
                            <td>{{ $customer->phone ?? 'Not provided' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Joined:</td>
                            <td>{{ $customer->created_at->format('d M, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Order:</td>
                            <td>
                                @if($stats['last_order_date'])
                                    {{ $stats['last_order_date']->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email Verified:</td>
                            <td>
                                @if($customer->email_verified_at)
                                    <span class="badge bg-success">Verified</span>
                                @else
                                    <span class="badge bg-warning">Unverified</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Addresses -->
        @if($customer->addresses->count() > 0)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="map-pin" class="me-2"></i>Saved Addresses
                </h5>
            </div>
            <div class="card-body">
                @foreach($customer->addresses as $address)
                <div class="mb-3 {{ $loop->last ? '' : 'pb-3 border-bottom' }}">
                    @if($address->is_default)
                        <span class="badge bg-primary mb-2">Default</span>
                    @endif
                    <p class="mb-1">{{ $address->address }}</p>
                    <p class="text-muted mb-0 small">
                        {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Statistics & Orders -->
    <div class="col-xl-8">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                        <i data-lucide="shopping-cart" class="fs-20"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Orders</p>
                                <h4 class="mb-0">{{ $stats['total_orders'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                                        <i data-lucide="check-circle" class="fs-20"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Completed</p>
                                <h4 class="mb-0">{{ $stats['completed_orders'] }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                                        <i data-lucide="dollar-sign" class="fs-20"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Total Spent</p>
                                <h4 class="mb-0">₦{{ number_format($stats['total_spent'], 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 me-3">
                                <div class="avatar-sm">
                                    <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                        <i data-lucide="trending-up" class="fs-20"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <p class="text-muted mb-1">Avg Order</p>
                                <h4 class="mb-0">₦{{ number_format($stats['avg_order_value'], 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i data-lucide="list" class="me-2"></i>Recent Orders
                    </h5>
                    <a href="{{ route('admin.customers.orders', $customer->id) }}" class="btn btn-sm btn-primary">
                        View All Orders
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="fw-medium text-primary">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->created_at->format('d M, Y') }}</td>
                                <td>{{ $order->items->count() }}</td>
                                <td class="fw-bold">₦{{ number_format($order->total, 2) }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status_badge }}">
                                        {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->status_badge }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-light">
                                        <i data-lucide="eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i data-lucide="shopping-cart" class="text-muted" style="width: 32px; height: 32px;"></i>
                                    <p class="text-muted mt-2 mb-0">No orders yet</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block Modal -->
@unless($customer->deleted_at)
<div class="modal fade" id="blockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Block Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.block', $customer->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        This will prevent <strong>{{ $customer->name }}</strong> from accessing their account.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for blocking *</label>
                        <textarea name="reason" class="form-control" rows="3" required
                                  placeholder="Enter reason for blocking this customer..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Block Customer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endunless

<!-- Notify Modal -->
<div class="modal fade" id="notifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.notify', $customer->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control" required
                               placeholder="Notification subject">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="4" required
                                  placeholder="Enter your message..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Notification</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
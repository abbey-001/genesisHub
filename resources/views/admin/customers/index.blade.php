@extends('admin.layouts.app')

@section('title', 'Customers')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Customer Management</h4>
            <p class="text-muted">Manage all customer accounts</p>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="users" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Customers</p>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
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
                        <p class="text-muted mb-1">Active</p>
                        <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="user-plus" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">New Today</p>
                        <h4 class="mb-0">{{ number_format($stats['new_today']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded">
                                <i data-lucide="user-x" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Blocked</p>
                        <h4 class="mb-0">{{ number_format($stats['blocked']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Customers</h5>
                    <div>
                        <a href="{{ route('admin.customers.export', request()->query()) }}" class="btn btn-sm btn-success">
                            <i data-lucide="download" class="me-1"></i>Export
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search name, email, phone..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                            <option value="blocked"  {{ request('status') === 'blocked'  ? 'selected' : '' }}>Blocked</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control"
                               placeholder="From Date" value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control"
                               placeholder="To Date" value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="filter" class="me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">
                            <i data-lucide="x" class="me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $customer->name }}</h6>
                                            <small class="text-muted">ID: {{ $customer->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $customer->email }}</td>
                                <td>{{ $customer->phone ?? 'N/A' }}</td>
                                <td>
                                    {{-- orders_count pre-computed via withCount — no extra query --}}
                                    <span class="badge bg-info">{{ $customer->orders_count }}</span>
                                </td>
                                <td class="fw-bold">
                                    {{-- total_spent pre-computed via withSum — no extra query --}}
                                    ₦{{ number_format($customer->total_spent ?? 0, 2) }}
                                </td>
                                <td>{{ $customer->created_at->format('d M, Y') }}</td>
                                <td>
                                    @if($customer->deleted_at)
                                        <span class="badge bg-danger">Blocked</span>
                                    @else
                                        <span class="badge bg-success">Active</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.show', $customer->id) }}">
                                                    <i data-lucide="eye" class="me-2 fs-16"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.customers.orders', $customer->id) }}">
                                                    <i data-lucide="shopping-cart" class="me-2 fs-16"></i>View Orders
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if($customer->deleted_at)
                                                <li>
                                                    {{--
                                                        Unblock uses raw ID in the URL because route model binding
                                                        won't resolve soft-deleted records automatically.
                                                    --}}
                                                    <form action="{{ route('admin.customers.unblock', $customer->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i data-lucide="check-circle" class="me-2 fs-16"></i>Unblock
                                                        </button>
                                                    </form>
                                                </li>
                                            @else
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#blockModal{{ $customer->id }}">
                                                        <i data-lucide="x-circle" class="me-2 fs-16"></i>Block Customer
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                    <!-- Block Modal -->
                                    @unless($customer->deleted_at)
                                    <div class="modal fade" id="blockModal{{ $customer->id }}" tabindex="-1">
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
                                                            <label class="form-label">Reason *</label>
                                                            <textarea name="reason" class="form-control" rows="3" required
                                                                      placeholder="Enter reason for blocking..."></textarea>
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
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i data-lucide="users" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mt-2">No customers found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($customers->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }}
                    </div>
                    {{ $customers->links() }}
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
</script>
@endpush
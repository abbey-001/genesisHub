@extends('admin.layouts.app')

@section('title', 'Sellers')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Seller Management</h4>
            <p class="text-muted">Manage all seller accounts and applications</p>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i data-lucide="alert-circle" class="me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card mb-3">
    <div class="card-body">
        <form action="{{ route('admin.sellers.update-commission-all') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label fw-semibold">Set commission for all sellers</label>
                <div class="input-group">
                    <input type="number"
                           name="commission_rate"
                           class="form-control"
                           value="{{ old('commission_rate', config('platform.commission_rate', 10)) }}"
                           step="0.01"
                           min="0"
                           max="100"
                           required>
                    <span class="input-group-text">%</span>
                </div>
            </div>
            <div class="col-md-auto">
                <button type="submit"
                        class="btn btn-primary"
                        onclick="return confirm('Apply this commission rate to every seller?')">
                    <i data-lucide="percent" class="me-1"></i>Apply to All Sellers
                </button>
            </div>
            <div class="col-md">
                <small class="text-muted">This updates each seller's commission rate. Individual sellers can still be changed from their profile.</small>
            </div>
        </form>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-2 col-sm-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                            <i data-lucide="store" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total</p>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
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
                        <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                            <i data-lucide="check-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Verified</p>
                        <h4 class="mb-0">{{ number_format($stats['verified']) }}</h4>
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
                        <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded">
                            <i data-lucide="x-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Rejected</p>
                        <h4 class="mb-0">{{ number_format($stats['rejected']) }}</h4>
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
                        <div class="avatar-title bg-secondary bg-opacity-10 text-secondary rounded">
                            <i data-lucide="pause-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Suspended</p>
                        <h4 class="mb-0">{{ number_format($stats['suspended']) }}</h4>
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
                            <i data-lucide="user-plus" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">New Today</p>
                        <h4 class="mb-0">{{ number_format($stats['new_today']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($stats['pending'] > 0)
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center">
            <i data-lucide="alert-circle" class="me-3 fs-24"></i>
            <div class="flex-grow-1">
                <strong>{{ $stats['pending'] }} pending application{{ $stats['pending'] > 1 ? 's' : '' }}</strong> require your review.
            </div>
            <a href="{{ route('admin.sellers.applications') }}" class="btn btn-warning ms-3">Review Applications</a>
        </div>
    </div>
</div>
@endif

<!-- Filters & Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Sellers</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.sellers.applications') }}" class="btn btn-sm btn-warning">
                            <i data-lucide="clock" class="me-1"></i>Pending ({{ $stats['pending'] }})
                        </a>
                        <a href="{{ route('admin.sellers.export', request()->query()) }}" class="btn btn-sm btn-success">
                            <i data-lucide="download" class="me-1"></i>Export
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.sellers.index') }}" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search sellers or shops..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="approved"   {{ request('status') === 'approved'   ? 'selected' : '' }}>Verified</option>
                            <option value="pending"    {{ request('status') === 'pending'    ? 'selected' : '' }}>Pending</option>
                            <option value="rejected"   {{ request('status') === 'rejected'   ? 'selected' : '' }}>Rejected</option>
                            <option value="suspended"  {{ request('status') === 'suspended'  ? 'selected' : '' }}>Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="filter" class="me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.sellers.index') }}" class="btn btn-secondary">
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
                                <th>Seller</th>
                                <th>Shop</th>
                                <th>Email</th>
                                <th>Products</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sellers as $seller)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle">
                                                {{ strtoupper(substr($seller->user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $seller->user->name }}</h6>
                                            <small class="text-muted">ID: {{ $seller->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($seller->shop)
                                        <span class="fw-medium">{{ $seller->shop->shop_name }}</span>
                                    @else
                                        <span class="text-muted">No shop</span>
                                    @endif
                                </td>
                                <td>{{ $seller->user->email }}</td>
                                <td><span class="badge bg-info">{{ $seller->products_count }}</span></td>
                                <td>{{ $seller->commission_rate }}%</td>
                                <td>
                                    @php
                                        $statusMap = [
                                            'verified'  => ['bg-success',   'Verified'],
                                            'pending'   => ['bg-warning',   'Pending'],
                                            'rejected'  => ['bg-danger',    'Rejected'],
                                            'suspended' => ['bg-secondary', 'Suspended'],
                                        ];
                                        [$badge, $label] = $statusMap[$seller->verification_status] ?? ['bg-secondary', ucfirst($seller->verification_status)];
                                    @endphp
                                    <span class="badge {{ $badge }}">{{ $label }}</span>
                                </td>
                                <td>{{ $seller->created_at->format('d M, Y') }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.sellers.show', $seller) }}">
                                                    <i data-lucide="eye" class="me-2 fs-16"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.sellers.products', $seller) }}">
                                                    <i data-lucide="package" class="me-2 fs-16"></i>View Products
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.sellers.wallet', $seller) }}">
                                                    <i data-lucide="wallet" class="me-2 fs-16"></i>View Wallet
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            @if($seller->verification_status === 'suspended')
                                                <li>
                                                    <form action="{{ route('admin.sellers.activate', $seller) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-success">
                                                            <i data-lucide="check-circle" class="me-2 fs-16"></i>Reactivate
                                                        </button>
                                                    </form>
                                                </li>
                                            @elseif($seller->verification_status === 'verified')
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#suspendModal{{ $seller->id }}">
                                                        <i data-lucide="x-circle" class="me-2 fs-16"></i>Suspend Seller
                                                    </button>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>

                                    <!-- Suspend Modal -->
                                    @if($seller->verification_status === 'verified')
                                    <div class="modal fade" id="suspendModal{{ $seller->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Suspend Seller</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.sellers.suspend', $seller) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="alert alert-warning">
                                                            <i data-lucide="alert-triangle" class="me-2"></i>
                                                            This will deactivate <strong>{{ $seller->shop->shop_name ?? $seller->user->name }}'s</strong> shop and all products. They will be notified by email.
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Reason *</label>
                                                            <textarea name="reason" class="form-control" rows="3" required
                                                                      placeholder="Explain why the seller is being suspended..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Suspend Seller</button>
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
                                <td colspan="8" class="text-center py-4">
                                    <i data-lucide="store" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mt-2">No sellers found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($sellers->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $sellers->firstItem() }} to {{ $sellers->lastItem() }} of {{ $sellers->total() }}
                    </div>
                    {{ $sellers->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>lucide.createIcons();</script>
@endpush

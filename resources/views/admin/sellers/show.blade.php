@extends('admin.layouts.app')
@section('title', 'Seller Details')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title">Seller Details</h4>
                    <p class="text-muted mb-0">View and manage seller account</p>
                </div>
                <a href="{{ route('admin.sellers.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Sellers
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i data-lucide="alert-circle" class="me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row">
    <!-- Left column -->
    <div class="col-md-4">
        <!-- Profile card -->
        <div class="card">
            <div class="card-body text-center">
                <div class="avatar-xl mx-auto mb-3">
                    <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded-circle fs-32">
                        {{ strtoupper(substr($seller->user->name, 0, 1)) }}
                    </div>
                </div>
                <h4>{{ $seller->user->name }}</h4>
                <p class="text-muted mb-2">{{ $seller->user->email }}</p>

                @php
                    $statusMap = [
                        'approved'  => ['bg-success',   'Verified Seller'],
                        'pending'   => ['bg-warning',   'Pending Approval'],
                        'rejected'  => ['bg-danger',    'Rejected'],
                        'suspended' => ['bg-secondary', 'Suspended'],
                    ];
                    [$badge, $label] = $statusMap[$seller->verification_status] ?? ['bg-secondary', ucfirst($seller->verification_status)];
                @endphp
                <span class="badge {{ $badge }} fs-13 px-3 py-2 mb-3">{{ $label }}</span>

                <div class="d-grid gap-2">
                    @if($seller->verification_status === 'pending')
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i data-lucide="check-circle" class="me-1"></i>Approve Seller
                        </button>
                        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i data-lucide="x-circle" class="me-1"></i>Reject Application
                        </button>
                    @elseif($seller->verification_status === 'approved')
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#suspendModal">
                            <i data-lucide="pause-circle" class="me-1"></i>Suspend Seller
                        </button>
                    @elseif($seller->verification_status === 'suspended')
                        <form action="{{ route('admin.sellers.activate', $seller) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i data-lucide="check-circle" class="me-1"></i>Reactivate Seller
                            </button>
                        </form>
                    @endif

                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#notifyModal">
                        <i data-lucide="mail" class="me-1"></i>Send Notification
                    </button>
                </div>
            </div>
        </div>

        <!-- Seller Information -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Seller Information</h5></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Business Type:</td>
                        <td><strong>{{ ucfirst($seller->business_type ?? 'N/A') }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tax ID:</td>
                        <td>{{ $seller->tax_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Phone:</td>
                        <td>{{ $seller->phone_number ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Address:</td>
                        <td>{{ $seller->address ? $seller->address . ', ' . $seller->city : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Member Since:</td>
                        <td>{{ $seller->created_at->format('d M, Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Bank Details -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Bank Details</h5></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Bank:</td>
                        <td>{{ $seller->bank_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Account Name:</td>
                        <td>{{ $seller->account_holder_name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Account No:</td>
                        <td>{{ $seller->bank_account ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Commission Rate -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0"><i data-lucide="percent" class="me-2"></i>Commission Rate</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.sellers.update-commission', $seller) }}" method="POST">
                    @csrf
                    <div class="input-group">
                        <input type="number" name="commission_rate" class="form-control"
                               value="{{ $seller->commission_rate }}"
                               step="0.01" min="0" max="100" required>
                        <span class="input-group-text">%</span>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                    <small class="text-muted">Platform commission taken from each sale.</small>
                </form>
            </div>
        </div>
    </div>

    <!-- Right column -->
    <div class="col-md-8">
        <!-- Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Products</p>
                        <h3 class="mb-0">{{ $stats['total_products'] }}</h3>
                        <small class="text-success">{{ $stats['active_products'] }} active</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Total Sales</p>
                        <h3 class="mb-0">₦{{ number_format($stats['total_sales'], 0) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Wallet</p>
                        <h3 class="mb-0 text-primary">₦{{ number_format($stats['wallet_balance'], 0) }}</h3>
                        <small class="text-warning">₦{{ number_format($stats['pending_balance'], 0) }} pending</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body text-center">
                        <p class="text-muted mb-1">Orders</p>
                        <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Quick Actions</h5></div>
            <div class="card-body d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.sellers.products', $seller) }}" class="btn btn-outline-primary">
                    <i data-lucide="package" class="me-1"></i>View Products
                </a>
                <a href="{{ route('admin.sellers.wallet', $seller) }}" class="btn btn-outline-info">
                    <i data-lucide="wallet" class="me-1"></i>View Wallet
                </a>
            </div>
        </div>

        <!-- Shop Information -->
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Shop Information</h5></div>
            <div class="card-body">
                @if($seller->shop)
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted" style="width:35%">Shop Name:</td>
                        <td><strong>{{ $seller->shop->shop_name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Description:</td>
                        <td>{{ $seller->shop->shop_description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">City / State:</td>
                        <td>{{ $seller->shop->city ?? 'N/A' }}{{ $seller->shop->state ? ', ' . $seller->shop->state : '' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Rating:</td>
                        <td>{{ number_format($seller->shop->rating ?? 0, 1) }} / 5.0</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status:</td>
                        <td>
                            @if($seller->shop->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </td>
                    </tr>
                </table>
                @else
                <p class="text-muted mb-0">No shop information available.</p>
                @endif
            </div>
        </div>

        <!-- Recent Products -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Recent Products</h5>
                <a href="{{ route('admin.sellers.products', $seller) }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body">
                @if($recentProducts->isEmpty())
                    <p class="text-muted mb-0">No products yet.</p>
                @else
                <div class="row g-3">
                    @foreach($recentProducts as $product)
                    <div class="col-md-4">
                        <div class="border rounded p-2 h-100">
                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                 class="w-100 rounded mb-2"
                                 style="height: 120px; object-fit: cover;"
                                 onerror="this.src='{{ asset('img/default-product.jpg') }}'">
                            <h6 class="mb-1 small">{{ Str::limit($product->name, 30) }}</h6>
                            <p class="mb-1 text-primary fw-bold small">₦{{ number_format($product->price, 2) }}</p>
                            <span class="badge {{ $product->is_active ? 'bg-success' : 'bg-secondary' }} small">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════ --}}

<!-- Approve Modal -->
@if($seller->verification_status === 'pending')
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Approve Seller Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i data-lucide="check-circle" class="me-2"></i>
                    Approving will activate the seller's shop and send a confirmation email to <strong>{{ $seller->user->email }}</strong>.
                </div>
                <p>Are you sure you want to approve <strong>{{ $seller->user->name }}</strong>'s application?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.sellers.approve', $seller) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">Yes, Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sellers.reject', $seller) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        The seller will be notified by email with the reason provided.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason (sent to seller) <span class="text-muted">optional</span></label>
                        <textarea name="reason" class="form-control" rows="3"
                                  placeholder="Explain why the application is being rejected..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Application</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Suspend Modal -->
@if($seller->verification_status === 'approved')
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Suspend Seller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sellers.suspend', $seller) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        This will deactivate the shop and all products. The seller will be notified by email.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason for Suspension *</label>
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

<!-- Notify Modal -->
<div class="modal fade" id="notifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i data-lucide="mail" class="me-2"></i>Send Email Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.sellers.notify', $seller) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">
                        Sending to: <strong>{{ $seller->user->name }}</strong> ({{ $seller->user->email }})
                    </p>
                    <div class="mb-3">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control" required
                               placeholder="Email subject line">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" rows="5" required
                                  placeholder="Enter your message to the seller..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="send" class="me-1"></i>Send Notification
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>lucide.createIcons();</script>
@endpush
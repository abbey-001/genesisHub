@extends('admin.layouts.app')

@section('title', 'Review Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Review Management</h4>
                <p class="text-muted mb-0">Moderate and manage product reviews</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reviews.analytics') }}" class="btn btn-outline-primary">
                    <i data-lucide="bar-chart" class="me-1"></i>Analytics
                </a>
                <a href="{{ route('admin.reviews.export', request()->query()) }}" class="btn btn-outline-secondary">
                    <i data-lucide="download" class="me-1"></i>Export
                </a>
            </div>
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
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">
        <i data-lucide="info" class="me-2"></i>{{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-primary">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Total Reviews</p>
                        <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total']) }}</h4>
                        <small class="text-muted">All time</small>
                    </div>
                    <div class="avatar-sm bg-primary bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                        <i data-lucide="star" class="text-primary fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Pending Approval</p>
                        <h4 class="fw-bold text-warning mb-0">{{ number_format($stats['pending']) }}</h4>
                        <small class="text-muted">Needs moderation</small>
                    </div>
                    <div class="avatar-sm bg-warning bg-opacity-10 rounded d-flex align-items-center justify-content-center">
                        <i data-lucide="clock" class="text-warning fs-24"></i>
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
                        <p class="mb-2 text-muted">Approved</p>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($stats['approved']) }}</h4>
                        <small class="text-muted">Published</small>
                    </div>
                    <div class="avatar-sm bg-success bg-opacity-10 rounded d-flex align-items-center justify-content-center">
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
                        <p class="mb-2 text-muted">Rejected</p>
                        <h4 class="fw-bold text-danger mb-0">{{ number_format($stats['rejected']) }}</h4>
                        <small class="text-muted">All time</small>
                    </div>
                    <div class="avatar-sm bg-danger bg-opacity-10 rounded d-flex align-items-center justify-content-center">
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
            <div class="card-body py-2">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.reviews.index') }}"
                       class="btn btn-sm btn-outline-secondary {{ !request('status') && !request('verified_only') ? 'active' : '' }}">
                        All ({{ $stats['total'] }})
                    </a>
                    <a href="{{ route('admin.reviews.index', ['status' => 'pending']) }}"
                       class="btn btn-sm btn-outline-warning {{ request('status') === 'pending' ? 'active' : '' }}">
                        <i data-lucide="clock" class="me-1"></i>Pending ({{ $stats['pending'] }})
                    </a>
                    <a href="{{ route('admin.reviews.index', ['status' => 'approved']) }}"
                       class="btn btn-sm btn-outline-success {{ request('status') === 'approved' ? 'active' : '' }}">
                        <i data-lucide="check-circle" class="me-1"></i>Approved ({{ $stats['approved'] }})
                    </a>
                    <a href="{{ route('admin.reviews.index', ['status' => 'rejected']) }}"
                       class="btn btn-sm btn-outline-danger {{ request('status') === 'rejected' ? 'active' : '' }}">
                        <i data-lucide="x-circle" class="me-1"></i>Rejected ({{ $stats['rejected'] }})
                    </a>
                    <a href="{{ route('admin.reviews.index', ['verified_only' => '1']) }}"
                       class="btn btn-sm btn-outline-info {{ request('verified_only') ? 'active' : '' }}">
                        <i data-lucide="check" class="me-1"></i>Verified Purchases
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reviews Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i data-lucide="list" class="me-2"></i>Reviews</h5>
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
                <form action="{{ route('admin.reviews.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="search" name="search" class="form-control"
                               placeholder="Search customer or product..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="rating" class="form-select">
                            <option value="">All Ratings</option>
                            @for($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                {{ $i }} Star{{ $i > 1 ? 's' : '' }}
                            </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-select">
                            <option value="latest"        {{ request('sort') === 'latest'        ? 'selected' : '' }}>Latest</option>
                            <option value="oldest"        {{ request('sort') === 'oldest'        ? 'selected' : '' }}>Oldest</option>
                            <option value="highest_rated" {{ request('sort') === 'highest_rated' ? 'selected' : '' }}>Highest Rated</option>
                            <option value="lowest_rated"  {{ request('sort') === 'lowest_rated'  ? 'selected' : '' }}>Lowest Rated</option>
                            <option value="most_helpful"  {{ request('sort') === 'most_helpful'  ? 'selected' : '' }}>Most Helpful</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i data-lucide="search" class="me-1"></i>Filter
                            </button>
                            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                                <i data-lucide="x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Table -->
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
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Verified</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reviews as $review)
                            <tr>
                                @if(request('status') === 'pending')
                                <td>
                                    <input type="checkbox"
                                           class="form-check-input review-checkbox"
                                           value="{{ $review->id }}">
                                </td>
                                @endif
                                <td>
                                    <div class="fw-medium">{{ $review->user->name }}</div>
                                    <small class="text-muted">{{ $review->user->email }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('admin.reviews.show', $review) }}" class="text-decoration-none fw-medium">
                                        {{ Str::limit($review->product->name, 30) }}
                                    </a>
                                    <br>
                                    <small class="text-muted">{{ $review->product->shop->shop_name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    @php $r = $review->rating; @endphp
                                    <span class="text-warning fw-bold">
                                        @for($i = 1; $i <= 5; $i++)
                                            {{ $i <= $r ? '★' : '☆' }}
                                        @endfor
                                    </span>
                                    <small class="text-muted ms-1">{{ $r }}/5</small>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        {{ Str::limit($review->comment, 60) }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusBadge = [
                                            'pending'  => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                        ][$review->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusBadge }}">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($review->is_verified_purchase)
                                        <span class="badge bg-success">
                                            <i data-lucide="check" style="width:12px;height:12px;"></i> Verified
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $review->created_at->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">
                                            <i data-lucide="more-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.reviews.show', $review) }}">
                                                    <i data-lucide="eye" class="me-2"></i>View Details
                                                </a>
                                            </li>
                                            @if($review->status === 'pending')
                                            <li>
                                                <button class="dropdown-item text-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#approveModal{{ $review->id }}">
                                                    <i data-lucide="check" class="me-2"></i>Approve
                                                </button>
                                            </li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#rejectModal{{ $review->id }}">
                                                    <i data-lucide="x" class="me-2"></i>Reject
                                                </button>
                                            </li>
                                            @elseif($review->status === 'approved')
                                            <li>
                                                <form action="{{ route('admin.reviews.toggleStatus', $review) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-warning">
                                                        <i data-lucide="rotate-ccw" class="me-2"></i>Revert to Pending
                                                    </button>
                                                </form>
                                            </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item text-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $review->id }}"
                                                        onclick="return true">
                                                    <i data-lucide="trash-2" class="me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No reviews found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($reviews->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }}
                        of {{ $reviews->total() }} reviews
                    </div>
                    {{ $reviews->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{--
    MODALS — rendered OUTSIDE the <table> so the DOM is valid and Bootstrap
    can reliably find and open them. Placing modals inside <tbody>/<tr> is
    invalid HTML; browsers silently move them to <body> which breaks the
    data-bs-target selectors.
--}}
@foreach($reviews as $review)

    {{-- Approve Modal --}}
    @if($review->status === 'pending')
    <div class="modal fade" id="approveModal{{ $review->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i data-lucide="check-circle" class="me-2"></i>Approve Review
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="fw-medium mb-1">{{ $review->product->name }}</div>
                            <div class="text-muted small">by {{ $review->user->name }}</div>
                            <div class="mt-1 text-warning">
                                @for($i=1;$i<=5;$i++){{ $i<=$review->rating?'★':'☆' }}@endfor
                            </div>
                            <p class="mt-2 mb-0 small">{{ Str::limit($review->comment, 100) }}</p>
                        </div>
                        <div class="alert alert-info">
                            <i data-lucide="info" class="me-2"></i>
                            This review will be published on the product page immediately.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Internal Notes <span class="text-muted">(optional)</span></label>
                            <textarea name="notes" class="form-control" rows="2"
                                      placeholder="For internal use only..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i data-lucide="check" class="me-1"></i>Approve Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal fade" id="rejectModal{{ $review->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i data-lucide="x-circle" class="me-2"></i>Reject Review
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="fw-medium mb-1">{{ $review->product->name }}</div>
                            <div class="text-muted small">by {{ $review->user->name }}</div>
                        </div>
                        <div class="alert alert-warning">
                            <i data-lucide="alert-triangle" class="me-2"></i>
                            The customer will be notified of the rejection.
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Reason *</label>
                            <textarea name="reason" class="form-control" rows="3" required
                                      placeholder="Explain why this review is being rejected..."></textarea>
                            <small class="text-muted">This will be shown to the customer.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i data-lucide="x" class="me-1"></i>Reject Review
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Delete Modal --}}
    <div class="modal fade" id="deleteModal{{ $review->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i data-lucide="trash-2" class="me-2"></i>Delete Review
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <i data-lucide="alert-circle" class="me-2"></i>
                            <strong>This cannot be undone.</strong> The review will be permanently deleted.
                        </div>
                        <p class="mb-0">
                            Delete review by <strong>{{ $review->user->name }}</strong>
                            for <strong>{{ Str::limit($review->product->name, 40) }}</strong>?
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i data-lucide="trash-2" class="me-1"></i>Delete Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endforeach

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i data-lucide="check-circle" class="me-2"></i>Bulk Approve Reviews
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reviews.bulkApprove') }}" method="POST" id="bulkApproveForm">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i data-lucide="info" class="me-2"></i>
                        You are about to approve <strong id="bulkCount">0</strong> review(s).
                        They will be published immediately on product pages.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i data-lucide="check-circle" class="me-1"></i>Approve Selected
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

    const selectAllCheckbox = document.getElementById('selectAll');
    const reviewCheckboxes  = document.querySelectorAll('.review-checkbox');
    const bulkApproveBtn    = document.getElementById('bulkApproveBtn');
    const selectedCountSpan = document.getElementById('selectedCount');
    const bulkCountSpan     = document.getElementById('bulkCount');
    const bulkApproveForm   = document.getElementById('bulkApproveForm');

    function updateBulkButton() {
        const count = document.querySelectorAll('.review-checkbox:checked').length;
        if (selectedCountSpan) selectedCountSpan.textContent = count;
        if (bulkCountSpan)     bulkCountSpan.textContent     = count;
        if (bulkApproveBtn)    bulkApproveBtn.disabled        = count === 0;
    }

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            reviewCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });
    }

    reviewCheckboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            updateBulkButton();
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = Array.from(reviewCheckboxes).every(c => c.checked);
            }
        });
    });

    if (bulkApproveForm) {
        bulkApproveForm.addEventListener('submit', function () {
            // Remove any previously appended IDs to avoid duplicates on re-submit
            this.querySelectorAll('input[name="review_ids[]"]').forEach(i => i.remove());
            document.querySelectorAll('.review-checkbox:checked').forEach(cb => {
                const input = document.createElement('input');
                input.type  = 'hidden';
                input.name  = 'review_ids[]';
                input.value = cb.value;
                this.appendChild(input);
            });
        });
    }

    // Re-init Lucide inside modals (icons injected after page load)
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
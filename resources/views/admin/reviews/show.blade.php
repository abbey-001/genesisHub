@extends('admin.layouts.app')

@section('title', 'Review Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Review Details</h4>
                <p class="text-muted mb-0">Review ID: #{{ $review->id }}</p>
            </div>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">
                <i data-lucide="arrow-left" class="me-1"></i>Back to Reviews
            </a>
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

<div class="row">
    <!-- Review Content -->
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-body">
                <!-- Customer & Rating header -->
                <div class="mb-4 pb-4 border-bottom">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Customer</h6>
                            <div class="fw-medium">{{ $review->user->name }}</div>
                            <small class="text-muted">{{ $review->user->email }}</small>
                            <br>
                            <a href="{{ route('admin.customers.show', $review->user) }}"
                               class="btn btn-sm btn-outline-primary mt-2">
                                <i data-lucide="user" class="me-1"></i>View Profile
                            </a>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Rating</h6>
                            <div class="fs-4 text-warning fw-bold">
                                @for($i = 1; $i <= 5; $i++)
                                    {{ $i <= $review->rating ? '★' : '☆' }}
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->rating }}/5 stars</small>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="text-muted mb-2">Product</h6>
                    <div class="d-flex gap-3 align-items-start">
                        {{--
                            Product uses the main_image accessor (returns a path string).
                            Images are stored in the product_images table — there is no
                            direct 'image' column on the products table.
                        --}}
                        <img src="{{ asset('storage/' . $review->product->main_image) }}"
                             alt="{{ $review->product->name }}"
                             style="width: 64px; height: 64px; object-fit: cover; border-radius: 6px; flex-shrink: 0;"
                             onerror="this.src='{{ asset('img/default-product.jpg') }}'">
                        <div>
                            <div class="fw-medium">{{ $review->product->name }}</div>
                            <small class="text-muted">
                                Shop: {{ $review->product->shop->shop_name ?? 'N/A' }}
                            </small><br>
                            <small class="text-muted">
                                Price: ₦{{ number_format($review->product->price, 2) }}
                            </small>
                            <div class="mt-2">
                                <a href="{{ route('admin.products.show', $review->product) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i data-lucide="package" class="me-1"></i>View Product
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order link (if present) -->
                @if($review->order)
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="text-muted mb-2">From Order</h6>
                    <a href="{{ route('admin.orders.show', $review->order) }}" class="btn btn-sm btn-outline-secondary">
                        <i data-lucide="shopping-cart" class="me-1"></i>{{ $review->order->order_number }}
                    </a>
                </div>
                @endif

                <!-- Review Content -->
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="text-muted mb-2">Review Comment</h6>
                    <p class="mb-0">{{ $review->comment }}</p>
                </div>

                <!-- Review Images -->
                @if($review->images->count() > 0)
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="text-muted mb-3">Review Images ({{ $review->images->count() }})</h6>
                    <div class="row g-2">
                        @foreach($review->images as $image)
                        <div class="col-md-3">
                            <a href="{{ asset('storage/' . $image->image_path) }}" target="_blank">
                                <img src="{{ asset('storage/' . $image->image_path) }}"
                                     alt="Review image"
                                     class="img-fluid rounded"
                                     style="max-height: 150px; width: 100%; object-fit: cover;">
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Seller Response -->
                @if($review->seller_response)
                <div class="mb-4 pb-4 border-bottom">
                    <h6 class="text-muted mb-2">Seller Response</h6>
                    <div class="p-3 bg-light rounded border-start border-primary border-3">
                        <p class="mb-1">{{ $review->seller_response }}</p>
                        @if($review->seller_responded_at)
                        <small class="text-muted">
                            Responded {{ $review->seller_responded_at->diffForHumans() }}
                        </small>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Meta Info -->
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">Date Submitted</h6>
                        <div>{{ $review->created_at->format('M d, Y H:i') }}</div>
                        <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">Verified Purchase</h6>
                        @if($review->is_verified_purchase)
                            <span class="badge bg-success">
                                <i data-lucide="check" style="width:14px;height:14px;"></i> Yes
                            </span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted mb-2">Helpful Votes</h6>
                        <div>
                            <span class="text-success fw-bold">{{ $review->helpful_count ?? 0 }}</span> helpful
                            / <span class="text-danger fw-bold">{{ $review->not_helpful_count ?? 0 }}</span> not helpful
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status History -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i data-lucide="history" class="me-2"></i>Status History</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <strong>Submitted</strong><br>
                            <small class="text-muted">{{ $review->created_at->format('M d, Y H:i') }}</small>
                        </div>
                    </div>

                    @if($review->isApproved())
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <strong>Approved</strong><br>
                            <small class="text-muted">
                                {{ $review->approved_at?->format('M d, Y H:i') ?? 'Unknown date' }}
                            </small><br>
                            <small class="text-muted">By: {{ $review->approvedBy?->name ?? 'System' }}</small>
                            @if($review->admin_notes)
                            <br><small class="text-muted">Notes: {{ $review->admin_notes }}</small>
                            @endif
                        </div>
                    </div>
                    @elseif($review->isRejected())
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <strong>Rejected</strong><br>
                            <small class="text-muted">
                                {{ $review->rejected_at?->format('M d, Y H:i') ?? 'Unknown date' }}
                            </small><br>
                            <small class="text-muted">By: {{ $review->rejectedBy?->name ?? 'System' }}</small><br>
                            <small class="text-danger">Reason: {{ $review->rejection_reason }}</small>
                        </div>
                    </div>
                    @else
                    <div class="timeline-item">
                        <div class="timeline-marker bg-warning"></div>
                        <div class="timeline-content">
                            <strong>Pending Review</strong><br>
                            <small class="text-muted">Awaiting moderation</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Status & Actions Card -->
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Status & Actions</h6></div>
            <div class="card-body">
                <div class="mb-3">
                    @php
                        $statusBadge = [
                            'pending'  => 'warning',
                            'approved' => 'success',
                            'rejected' => 'danger',
                        ][$review->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $statusBadge }} fs-6 px-3 py-2">
                        {{ ucfirst($review->status) }}
                    </span>
                </div>

                <div class="d-grid gap-2">
                    @if($review->isPending())
                        <button type="button" class="btn btn-success"
                                data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i data-lucide="check" class="me-1"></i>Approve Review
                        </button>
                        <button type="button" class="btn btn-danger"
                                data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i data-lucide="x" class="me-1"></i>Reject Review
                        </button>
                    @elseif($review->isApproved())
                        <button type="button" class="btn btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i data-lucide="x" class="me-1"></i>Reject Review
                        </button>
                        {{-- Revert to pending via toggleStatus --}}
                        <form action="{{ route('admin.reviews.toggleStatus', $review) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-warning w-100">
                                <i data-lucide="rotate-ccw" class="me-1"></i>Revert to Pending
                            </button>
                        </form>
                    @elseif($review->isRejected())
                        <button type="button" class="btn btn-success"
                                data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i data-lucide="check" class="me-1"></i>Approve Instead
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Customer History Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i data-lucide="user" class="me-1"></i>Customer History</h6>
            </div>
            <div class="card-body">
                @php
                    $totalUserReviews = $review->user->reviews()->count();
                    // null-safe avg — returns null when count is 0
                    $avgRating = $review->user->reviews()->avg('rating');
                @endphp
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Total Reviews</h6>
                    <div class="fs-5 fw-bold">{{ $totalUserReviews }}</div>
                </div>
                <div class="mb-3">
                    <h6 class="text-muted mb-1">Avg Rating Given</h6>
                    <div class="fs-5 fw-bold">
                        @if($avgRating !== null)
                            {{ number_format($avgRating, 1) }}/5
                        @else
                            N/A
                        @endif
                    </div>
                </div>

                <h6 class="text-muted mb-2">Other Reviews</h6>
                @forelse($userReviews as $otherReview)
                <div class="mb-2 pb-2 border-bottom">
                    <a href="{{ route('admin.reviews.show', $otherReview) }}"
                       class="text-decoration-none text-dark small">
                        <div class="text-truncate fw-medium">{{ Str::limit($otherReview->product->name, 35) }}</div>
                    </a>
                    <div class="text-warning small">
                        @for($i=1;$i<=5;$i++){{ $i<=$otherReview->rating?'★':'☆' }}@endfor
                    </div>
                    <small class="text-muted">{{ $otherReview->created_at->diffForHumans() }}</small>
                    <span class="badge bg-{{ ['pending'=>'warning','approved'=>'success','rejected'=>'danger'][$otherReview->status]??'secondary' }} ms-1" style="font-size:10px;">
                        {{ ucfirst($otherReview->status) }}
                    </span>
                </div>
                @empty
                <small class="text-muted">No other reviews from this customer.</small>
                @endforelse
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="card border-danger">
            <div class="card-header bg-danger bg-opacity-10">
                <h6 class="mb-0 text-danger">
                    <i data-lucide="alert-triangle" class="me-1"></i>Danger Zone
                </h6>
            </div>
            <div class="card-body">
                <button type="button" class="btn btn-danger btn-sm w-100"
                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i data-lucide="trash-2" class="me-1"></i>Delete Review Permanently
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════ --}}

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
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
                        <i data-lucide="check" class="me-1"></i>Approve
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
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
                    <div class="alert alert-warning">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        The customer will be notified of this rejection with the reason provided.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rejection Reason *</label>
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i data-lucide="trash-2" class="me-2"></i>Delete Review Permanently
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i data-lucide="alert-circle" class="me-2"></i>
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <p class="mb-0">
                        Permanently delete this review by <strong>{{ $review->user->name }}</strong>
                        for <strong>{{ $review->product->name }}</strong>?
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i data-lucide="trash-2" class="me-1"></i>Yes, Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .timeline { position: relative; padding: 20px 0; }
    .timeline-item {
        display: flex;
        margin-bottom: 30px;
        position: relative;
    }
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 50px;
        height: 50px;
        width: 2px;
        background-color: #dee2e6;
    }
    .timeline-marker {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-right: 20px;
    }
    .timeline-content { padding-top: 4px; }
</style>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('shown.bs.modal', () => lucide.createIcons());
    });
</script>
@endpush
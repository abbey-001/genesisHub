@extends('seller.layouts.app')

@section('title', 'Product Reviews')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h4>Product Reviews</h4>
                <p class="text-muted mb-0">Manage customer reviews for your products</p>
            </div>
        </div>
    </div>
</div>

{{-- Statistics --}}
<div class="row">
    <div class="col-lg-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                                <i data-lucide="message-square" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Reviews</p>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="clock" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Awaiting Approval</p>
                        <h4 class="mb-0">{{ number_format($stats['pending']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card border-success">
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
                        <p class="text-muted mb-1">Approved</p>
                        <h4 class="mb-0">{{ number_format($stats['approved']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-sm-6">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                                <i data-lucide="message-circle" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Needs Response</p>
                        <h4 class="mb-0">{{ number_format($stats['unanswered']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters & Reviews --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Reviews</h5>
            </div>

            {{-- Filters --}}
            <div class="card-body border-bottom">
                <form action="{{ route('seller.reviews.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
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
                        <select name="has_response" class="form-select">
                            <option value="">All Reviews</option>
                            <option value="0" {{ request('has_response') === '0' ? 'selected' : '' }}>Not Responded</option>
                            <option value="1" {{ request('has_response') === '1' ? 'selected' : '' }}>Responded</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="filter" class="me-1"></i>Filter
                        </button>
                        <a href="{{ route('seller.reviews.index') }}" class="btn btn-secondary">
                            <i data-lucide="x" class="me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            {{-- Reviews List --}}
            <div class="card-body p-0">
                @forelse($reviews as $review)
                <div class="border-bottom p-4">
                    <div class="row">
                        <div class="col-md-8">
                            {{-- Review Header --}}
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-primary rounded-circle">
                                            {{ strtoupper(substr($review->user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1">{{ $review->user->name }}</h6>
                                            <div class="star-rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            @if($review->status === 'pending')
                                                <span class="badge bg-warning mb-1">Pending Admin Approval</span>
                                            @else
                                                <span class="badge bg-success mb-1">Published</span>
                                            @endif
                                            <br>
                                            <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    
                                    {{-- Product Info --}}
                                    <div class="mb-2">
                                        <strong>Product:</strong> 
                                        <a href="{{ route('seller.products.show', $review->product) }}">
                                            {{ $review->product->name }}
                                        </a>
                                    </div>
                                    
                                    {{-- Review Content --}}
                                    <p class="mb-2">{{ $review->comment }}</p>
                                    
                                    {{-- Review Images --}}
                                    @if($review->images->count() > 0)
                                    <div class="d-flex gap-2 mt-2">
                                        @foreach($review->images as $image)
                                        <img src="{{ asset('storage/' . $image->image_path) }}" 
                                             alt="Review image" 
                                             class="rounded"
                                             style="width: 60px; height: 60px; object-fit: cover;">
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Seller Response Section --}}
                            @if($review->seller_response)
                            <div class="bg-light p-3 rounded mt-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="text-primary">Your Response:</strong>
                                        <p class="mb-0 mt-2">{{ $review->seller_response }}</p>
                                    </div>
                                    <small class="text-muted">{{ $review->seller_responded_at->diffForHumans() }}</small>
                                </div>
                            </div>
                            @else
                            {{-- Response Form (only for approved reviews) --}}
                            @if($review->is_approved)
                            <div class="mt-3">
                                <button class="btn btn-primary btn-sm" 
                                        data-bs-toggle="collapse" 
                                        data-bs-target="#responseForm{{ $review->id }}">
                                    <i data-lucide="message-circle" class="me-1"></i>
                                    Respond to Review
                                </button>
                                
                                <div class="collapse mt-3" id="responseForm{{ $review->id }}">
                                    <form action="{{ route('seller.reviews.respond', $review) }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Your Response</label>
                                            <textarea name="response" 
                                                      class="form-control" 
                                                      rows="3" 
                                                      required 
                                                      minlength="10"
                                                      maxlength="1000"
                                                      placeholder="Thank the customer and address their concerns..."></textarea>
                                            <small class="text-muted">Minimum 10 characters, maximum 1000</small>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            Submit Response
                                        </button>
                                        <button type="button" 
                                                class="btn btn-secondary" 
                                                data-bs-toggle="collapse" 
                                                data-bs-target="#responseForm{{ $review->id }}">
                                            Cancel
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @else
                            <div class="alert alert-info mt-3">
                                <i data-lucide="info" class="me-2"></i>
                                You can respond to this review once it's approved by admin.
                            </div>
                            @endif
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            {{-- Quick Stats --}}
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="mb-3">Review Details</h6>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2">
                                            <strong>Order:</strong> 
                                            <a href="{{ route('seller.orders.show', $review->order) }}">
                                                #{{ $review->order->order_number }}
                                            </a>
                                        </li>
                                        <li class="mb-2">
                                            <strong>Verified Purchase:</strong> 
                                            @if($review->is_verified_purchase)
                                                <span class="badge bg-success">Yes</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </li>
                                        <li class="mb-2">
                                            <strong>Helpful Votes:</strong> 
                                            {{ $review->helpful_count }} 👍 / {{ $review->not_helpful_count }} 👎
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i data-lucide="message-square" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                    <p class="text-muted mb-0">No reviews found</p>
                </div>
                @endforelse
            </div>

            @if($reviews->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $reviews->firstItem() }} to {{ $reviews->lastItem() }} of {{ $reviews->total() }}
                    </div>
                    {{ $reviews->links() }}
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
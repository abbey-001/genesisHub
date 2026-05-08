@extends('admin.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i data-lucide="arrow-left" class="me-1"></i>Back
                    </a>
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                        <i data-lucide="edit" class="me-1"></i>Edit
                    </a>
                    <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-{{ $product->is_active ? 'warning' : 'success' }}">
                            <i data-lucide="power" class="me-1"></i>
                            {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
            <h4 class="page-title">Product Details</h4>
        </div>
    </div>
</div>

<!-- Product Header -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-4">
                        <!-- Main Image -->
                        <div class="mb-3">
                            <img src="{{ asset('public/storage/' . $product->main_image) }}" 
                                 alt="{{ $product->name }}" 
                                 class="img-fluid rounded"
                                 style="max-height: 400px; width: 100%; object-fit: cover;">
                        </div>
                        
                        <!-- Image Gallery -->
                        @if($product->images->count() > 1)
                        <div class="row g-2">
                            @foreach($product->images->take(4) as $image)
                            <div class="col-3">
                                <img src="{{ asset('public/storage/' . $image->image_path) }}" 
                                     alt="{{ $product->name }}" 
                                     class="img-fluid rounded"
                                     style="height: 80px; width: 100%; object-fit: cover;">
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <div class="col-lg-8">
                        <h3 class="mb-3">{{ $product->name }}</h3>
                        
                        <div class="d-flex align-items-center gap-2 mb-3">
                            @if($product->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                            
                            @if($product->is_featured)
                                <span class="badge bg-primary">Featured</span>
                            @endif
                            
                            @if($product->stock <= 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @elseif($product->stock <= 10)
                                <span class="badge bg-warning">Low Stock</span>
                            @endif
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Category</label>
                                    <p class="mb-0 fw-medium">
                                        {{ $product->category->name ?? 'N/A' }}
                                        @if($product->subcategory)
                                            / {{ $product->subcategory->name }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Brand</label>
                                    <p class="mb-0 fw-medium">{{ $product->brand->name ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">SKU</label>
                                    <p class="mb-0 fw-medium">{{ $product->sku ?? 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted small">Shop</label>
                                    <p class="mb-0 fw-medium">{{ $product->shop->shop_name ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h4 class="mb-1 text-primary">₦{{ number_format($product->price, 2) }}</h4>
                                    <small class="text-muted">Regular Price</small>
                                </div>
                            </div>
                            @if($product->sale_price)
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h4 class="mb-1 text-success">₦{{ number_format($product->sale_price, 2) }}</h4>
                                    <small class="text-muted">Sale Price</small>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-4">
                                <div class="border rounded p-3 text-center">
                                    <h4 class="mb-1">{{ $product->stock }}</h4>
                                    <small class="text-muted">In Stock</small>
                                </div>
                            </div>
                        </div>

                        @if($product->short_description)
                        <div class="mb-3">
                            <label class="text-muted small">Short Description</label>
                            <p class="mb-0">{{ $product->short_description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row">
    <div class="col-xl-3 col-sm-6">
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
                        <p class="text-muted mb-1">Total Sold</p>
                        <h4 class="mb-0">{{ number_format($stats['total_sold']) }}</h4>
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
                                <i data-lucide="dollar-sign" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Revenue</p>
                        <h4 class="mb-0">₦{{ number_format($stats['total_revenue'], 2) }}</h4>
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
                                <i data-lucide="star" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Average Rating</p>
                        <h4 class="mb-0">{{ number_format($stats['avg_rating'], 1) }} ⭐</h4>
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
                            <div class="avatar-title bg-info bg-opacity-10 text-info rounded">
                                <i data-lucide="message-square" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Reviews</p>
                        <h4 class="mb-0">{{ number_format($stats['total_reviews']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Description & Reviews -->
<div class="row">
    <div class="col-lg-8">
        <!-- Description -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Description</h5>
            </div>
            <div class="card-body">
                {!! nl2br(e($product->description)) !!}
            </div>
        </div>

        <!-- Reviews -->
        @if($product->reviews->count() > 0)
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Customer Reviews ({{ $product->reviews->count() }})</h5>
                <a href="{{ route('admin.reviews.index', ['product' => $product->id]) }}" class="btn btn-sm btn-link">
                    View All <i data-lucide="arrow-right" class="ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                @foreach($product->reviews->take(5) as $review)
                <div class="d-flex mb-3 pb-3 border-bottom">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-primary rounded-circle">
                                {{ strtoupper(substr($review->user->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $review->user->name ?? 'Anonymous' }}</h6>
                        <div class="mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i data-lucide="star" class="{{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}" style="width: 14px; height: 14px;"></i>
                            @endfor
                            <small class="text-muted ms-2">{{ $review->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-0">{{ $review->comment }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Product Info & Actions -->
    <div class="col-lg-4">
        <!-- Product Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Created</small>
                    <span class="fw-medium">{{ $product->created_at->format('d M, Y h:i A') }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Last Updated</small>
                    <span class="fw-medium">{{ $product->updated_at->format('d M, Y h:i A') }}</span>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block">Stock Value</small>
                    <span class="fw-medium text-success">₦{{ number_format($stats['stock_value'], 2) }}</span>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block">Product ID</small>
                    <span class="fw-medium">#{{ $product->id }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-body">
                <form action="{{ route('admin.products.toggle-featured', $product) }}" method="POST" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-{{ $product->is_featured ? 'warning' : 'primary' }} w-100">
                        <i data-lucide="star" class="me-1"></i>
                        {{ $product->is_featured ? 'Unfeature Product' : 'Feature Product' }}
                    </button>
                </form>

                <a href="{{ route('product.show', $product->slug) }}" class="btn btn-secondary w-100 mb-2" target="_blank">
                    <i data-lucide="external-link" class="me-1"></i>View on Store
                </a>

                <form action="{{ route('admin.products.destroy', $product) }}" method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this product?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger w-100">
                        <i data-lucide="trash-2" class="me-1"></i>Delete Product
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
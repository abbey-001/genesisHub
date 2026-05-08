{{-- resources/views/seller/products/show.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Product Details</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-primary">
                    <i data-lucide="edit" class="fs-16"></i> Edit Product
                </a>
                <a href="{{ route('seller.products.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="fs-16"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- ── Left column: images + stats ──────────────────────────────────── --}}
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Product Images</h5>
                @if($product->images->count() > 0)
                    @php $primaryImage = $product->images->where('is_primary', true)->first() ?? $product->images->first(); @endphp
                    <div class="mb-3">
                        <img id="mainImage" src="{{ asset('public/storage/' . $primaryImage->image_path) }}"
                             alt="{{ $product->name }}"
                             class="img-fluid rounded border"
                             style="width: 100%; max-height: 360px; object-fit: contain;">
                    </div>
                    @if($product->images->count() > 1)
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach($product->images as $image)
                            <img src="{{ asset('public/storage/' . $image->image_path) }}" alt="Thumb"
                                 class="rounded border {{ $image->is_primary ? 'border-primary' : '' }}"
                                 style="width:72px; height:72px; object-fit:cover; cursor:pointer;"
                                 onclick="changeMainImage('{{ asset('public/storage/' . $image->image_path) }}')">
                        @endforeach
                    </div>
                    @endif
                @else
                    <div class="text-center py-4 bg-light rounded">
                        <i data-lucide="image-off" class="fs-48 text-muted"></i>
                        <p class="text-muted mt-2">No images</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick stats --}}
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title mb-3">Quick Statistics</h5>
                <div class="row text-center">
                    <div class="col-4">
                        <h4 class="fw-bold text-primary mb-1">{{ $product->sold_count ?? 0 }}</h4>
                        <p class="text-muted mb-0 small">Sold</p>
                    </div>
                    <div class="col-4 border-start border-end">
                        <h4 class="fw-bold text-success mb-1">{{ $product->stock }}</h4>
                        <p class="text-muted mb-0 small">In Stock</p>
                    </div>
                    <div class="col-4">
                        <h4 class="fw-bold text-warning mb-1">{{ number_format($product->rating ?? 0, 1) }}</h4>
                        <p class="text-muted mb-0 small">Rating</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tags chip display --}}
        @if($product->tags_array)
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="card-title mb-2">Tags</h6>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($product->tags_array as $tag)
                        <span class="badge bg-light text-dark border">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- ── Right column: product info ──────────────────────────────────── --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h3 class="mb-1">{{ $product->name }}</h3>
                        @if($product->model_number)
                            <p class="text-muted mb-0 small">Model: {{ $product->model_number }}</p>
                        @endif
                    </div>
                    <div class="d-flex flex-column align-items-end gap-1">
                        <span class="badge badge-soft-{{ $product->is_active ? 'success' : 'secondary' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        @if($product->condition && $product->condition !== 'new')
                        <span class="badge badge-soft-warning">{{ ucfirst($product->condition) }}</span>
                        @endif
                    </div>
                </div>

                {{-- Pricing --}}
                <div class="mb-4">
                    <div class="d-flex align-items-center gap-3">
                        @if($product->sale_price)
                            <h3 class="text-danger mb-0">₦{{ number_format($product->sale_price, 2) }}</h3>
                            <h5 class="text-decoration-line-through text-muted mb-0">₦{{ number_format($product->price, 2) }}</h5>
                            @if($product->discount_percentage)
                                <span class="badge bg-danger">{{ $product->discount_percentage }}% OFF</span>
                            @endif
                        @else
                            <h3 class="text-primary mb-0">₦{{ number_format($product->price, 2) }}</h3>
                        @endif
                    </div>
                </div>

                {{-- Core details table --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr><td class="text-muted">Category:</td><td class="fw-medium">{{ $product->category->name ?? 'N/A' }}</td></tr>
                            <tr><td class="text-muted">Subcategory:</td><td class="fw-medium">{{ $product->subcategory->name ?? 'N/A' }}</td></tr>
                            <tr><td class="text-muted">Brand:</td><td class="fw-medium">{{ $product->brand->name ?? 'N/A' }}</td></tr>
                            @if($product->target_audience)
                            <tr><td class="text-muted">Audience:</td><td class="fw-medium">{{ ucfirst($product->target_audience) }}</td></tr>
                            @endif
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td class="text-muted">Stock:</td>
                                <td>
                                    <span class="badge badge-soft-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }}">
                                        {{ $product->stock > 0 ? 'In Stock ('.$product->stock.')' : 'Out of Stock' }}
                                    </span>
                                </td>
                            </tr>
                            <tr><td class="text-muted">Featured:</td><td>{{ $product->is_featured ? 'Yes' : 'No' }}</td></tr>
                            <tr><td class="text-muted">Condition:</td><td>{{ ucfirst($product->condition ?? 'New') }}</td></tr>
                            <tr><td class="text-muted">Created:</td><td>{{ $product->created_at->format('d M, Y') }}</td></tr>
                        </table>
                    </div>
                </div>

                @if($product->short_description)
                <div class="mb-3">
                    <h6 class="fw-bold mb-1">Short Description</h6>
                    <p class="text-muted">{{ $product->short_description }}</p>
                </div>
                @endif

                <div class="mb-4">
                    <h6 class="fw-bold mb-1">Full Description</h6>
                    <div class="text-muted">{!! nl2br(e($product->description)) !!}</div>
                </div>

                {{-- Use cases --}}
                @if($product->use_cases)
                <div class="mb-3">
                    <h6 class="fw-bold mb-1">Use Cases</h6>
                    <p class="text-muted mb-0">{{ $product->use_cases }}</p>
                </div>
                @endif

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-primary">
                        <i data-lucide="edit" class="fs-16"></i> Edit Product
                    </a>
                    <button type="button" class="btn btn-danger" onclick="deleteProduct({{ $product->id }})">
                        <i data-lucide="trash-2" class="fs-16"></i> Delete
                    </button>
                    <a href="{{ route('product.show', $product->slug) }}" class="btn btn-outline-secondary" target="_blank">
                        <i data-lucide="external-link" class="fs-16"></i> View on Store
                    </a>
                </div>
            </div>
        </div>

        {{-- Specifications --}}
        @if($product->specifications_array)
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0">Specifications</h5></div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <tbody>
                        @foreach($product->specifications_array as $key => $value)
                        <tr>
                            <td class="fw-medium text-muted ps-3" style="width:40%">{{ $key }}</td>
                            <td>{{ $value }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Variants --}}
        @if($product->variants_array)
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0">Variants</h5></div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($product->variants_array as $group => $values)
                    <div class="col-md-6">
                        <p class="fw-semibold mb-1 text-muted small text-uppercase">{{ $group }}</p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach((array)$values as $val)
                                <span class="badge bg-light text-dark border px-2 py-1">{{ $val }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- SEO summary --}}
        @if($product->meta_title || $product->meta_description)
        <div class="card mt-3">
            <div class="card-header"><h5 class="card-title mb-0">SEO Preview</h5></div>
            <div class="card-body">
                <div class="border rounded p-3 bg-white">
                    <div class="text-primary fw-medium" style="max-width:600px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $product->meta_title ?: $product->name . ' — Your Store' }}
                    </div>
                    <div class="text-success small">yourstore.com/products/{{ $product->slug }}</div>
                    <div class="text-muted small mt-1">{{ $product->meta_description ?: $product->short_description }}</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Reviews --}}
        @if($product->reviews->count() > 0)
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Customer Reviews ({{ $product->reviews->count() }})</h5>
                <a href="{{ route('seller.reviews.index') }}" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body">
                @foreach($product->reviews->take(5) as $review)
                <div class="d-flex gap-3 mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <img src="{{ $review->user->avatar ?? asset('assets/images/users/avatar-1.jpg') }}"
                         class="rounded-circle" style="width:40px; height:40px; object-fit:cover;" alt="">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div>
                                <h6 class="mb-0">{{ $review->user->name }}</h6>
                                <small class="text-muted">{{ $review->created_at->format('d M, Y') }}</small>
                            </div>
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    <i data-lucide="star" class="fs-12 {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="mb-0">{{ $review->comment }}</p>
                        @if($review->seller_response)
                        <div class="mt-2 p-2 bg-light rounded">
                            <small class="fw-bold">Your Response:</small>
                            <p class="mb-0 small">{{ $review->seller_response }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

<form id="delete-form" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
function changeMainImage(src) { document.getElementById('mainImage').src = src; }

function deleteProduct(id) {
    if (confirm('Delete this product? This cannot be undone.')) {
        const f = document.getElementById('delete-form');
        f.action = `/seller/products/${id}`;
        f.submit();
    }
}

lucide.createIcons();
</script>
@endpush
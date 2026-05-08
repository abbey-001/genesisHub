{{-- resources/views/seller/products/index.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Products')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title mb-0">My Products</h5>
                    
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <form action="{{ route('seller.products.index') }}" method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                            <input type="search" name="search" class="form-control form-control-sm"
                                   placeholder="Search products…" value="{{ request('search') }}" style="min-width:200px">

                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>

                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>

                            <select name="condition" class="form-select form-select-sm">
                                <option value="">All Conditions</option>
                                <option value="new"         {{ request('condition') == 'new'         ? 'selected' : '' }}>New</option>
                                <option value="used"        {{ request('condition') == 'used'        ? 'selected' : '' }}>Used</option>
                                <option value="refurbished" {{ request('condition') == 'refurbished' ? 'selected' : '' }}>Refurbished</option>
                            </select>

                            <select name="audience" class="form-select form-select-sm">
                                <option value="">All Audiences</option>
                                @foreach(['men'=>'Men','women'=>'Women','kids'=>'Kids','unisex'=>'Unisex','business'=>'Business','all'=>'Everyone'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('audience') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>

                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            <a href="{{ route('seller.products.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                        </form>

                        <a href="{{ route('seller.products.create') }}" class="btn btn-sm btn-success">
                            <i data-lucide="plus" class="fs-14"></i> Add Product
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover text-nowrap mb-0">
                    <thead class="text-uppercase fs-12">
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Tags</th>
                            <th>Status</th>
                            <th>Sold</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>
                                <img src="{{ asset('public/storage/' . $product->main_image) }}"
                                     alt="{{ $product->name }}"
                                     class="rounded" style="width:50px; height:50px; object-fit:cover;">
                            </td>
                            <td>
                                <a href="{{ route('seller.products.show', $product) }}" class="text-dark fw-medium">
                                    {{ Str::limit($product->name, 40) }}
                                </a>
                                @if($product->model_number)
                                    <br><small class="text-muted">{{ $product->model_number }}</small>
                                @endif
                            </td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>
                                @if($product->sale_price)
                                    <span class="text-decoration-line-through text-muted d-block">₦{{ number_format($product->price, 2) }}</span>
                                    <span class="text-danger fw-bold">₦{{ number_format($product->sale_price, 2) }}</span>
                                @else
                                    <span class="fw-bold">₦{{ number_format($product->price, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $product->stock > 10 ? 'success' : ($product->stock > 0 ? 'warning' : 'danger') }}">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                @if($product->tags)
                                    <div class="d-flex flex-wrap gap-1" style="max-width:160px;">
                                        @foreach(array_slice($product->tags_array, 0, 3) as $tag)
                                            <span class="badge bg-light text-dark border" style="font-size:10px;">{{ $tag }}</span>
                                        @endforeach
                                        @if(count($product->tags_array) > 3)
                                            <span class="text-muted" style="font-size:11px;">+{{ count($product->tags_array) - 3 }} more</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-soft-{{ $product->is_active ? 'success' : 'secondary' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $product->sold_count ?? 0 }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('seller.products.show', $product) }}" class="btn btn-sm btn-soft-primary" title="View">
                                        <i data-lucide="eye" class="fs-16"></i>
                                    </a>
                                    <a href="{{ route('seller.products.edit', $product) }}" class="btn btn-sm btn-soft-secondary" title="Edit">
                                        <i data-lucide="square-pen" class="fs-16"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-soft-danger"
                                            onclick="deleteProduct({{ $product->id }})" title="Delete">
                                        <i data-lucide="trash-2" class="fs-16"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <p class="mb-2">No products found</p>
                                <a href="{{ route('seller.products.create') }}" class="btn btn-primary">Add Your First Product</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                    </div>
                    {{ $products->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<form id="delete-form" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
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
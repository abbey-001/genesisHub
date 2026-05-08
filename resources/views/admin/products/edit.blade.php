@extends('admin.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back
                </a>
            </div>
            <h4 class="page-title">Edit Product</h4>
        </div>
    </div>
</div>

<form action="{{ route('admin.products.update', $product) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Basic Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Description</label>
                        <textarea name="short_description" class="form-control @error('short_description') is-invalid @enderror" 
                                  rows="2" maxlength="500">{{ old('short_description', $product->short_description) }}</textarea>
                        <small class="text-muted">Maximum 500 characters</small>
                        @error('short_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="6" required>{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Regular Price (₦) *</label>
                                <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" 
                                       value="{{ old('price', $product->price) }}" step="0.01" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Sale Price (₦)</label>
                                <input type="number" name="sale_price" class="form-control @error('sale_price') is-invalid @enderror" 
                                       value="{{ old('sale_price', $product->sale_price) }}" step="0.01" min="0">
                                <small class="text-muted">Leave empty if no sale</small>
                                @error('sale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                               value="{{ old('stock', $product->stock) }}" min="0" required>
                        @error('stock')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Product Images -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Product Images</h5>
                </div>
                <div class="card-body">
                    @if($product->images->count() > 0)
                    <div class="row g-3 mb-3">
                        @foreach($product->images as $image)
                        <div class="col-md-3">
                            <div class="position-relative">
                                <img src="{{ asset('public/storage/' . $image->image_path) }}" 
                                     alt="Product Image" 
                                     class="img-fluid rounded"
                                     style="height: 150px; width: 100%; object-fit: cover;">
                                @if($image->is_primary)
                                <span class="badge bg-primary position-absolute top-0 end-0 m-2">Primary</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-muted">No images uploaded yet</p>
                    @endif
                    <small class="text-muted">Image management is handled by the seller. Contact them to update images.</small>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Categories -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Organization</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" 
                                id="categorySelect" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" 
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="subcategoryDiv" style="display: {{ $product->subcategory_id ? 'block' : 'none' }}">
                        <label class="form-label">Subcategory</label>
                        <select name="subcategory_id" class="form-select @error('subcategory_id') is-invalid @enderror" 
                                id="subcategorySelect">
                            <option value="">Select Subcategory</option>
                            @if($product->category && $product->category->subcategories)
                                @foreach($product->category->subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" 
                                        {{ old('subcategory_id', $product->subcategory_id) == $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                        @error('subcategory_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Brand</label>
                        <select name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                            <option value="">No Brand</option>
                            @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" 
                                    {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('brand_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Status</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                               {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">
                            Active (Visible on store)
                        </label>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                               {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isFeatured">
                            Featured Product
                        </label>
                    </div>
                </div>
            </div>

            <!-- Shop Info -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Shop Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted d-block">Shop Name</small>
                        <span class="fw-medium">{{ $product->shop->shop_name ?? 'N/A' }}</span>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Seller</small>
                        <span class="fw-medium">{{ $product->shop->seller->user->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i data-lucide="save" class="me-1"></i>Update Product
                    </button>
                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-secondary w-100">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    lucide.createIcons();

    // Category change handler
    document.getElementById('categorySelect').addEventListener('change', function() {
        const categoryId = this.value;
        const subcategoryDiv = document.getElementById('subcategoryDiv');
        const subcategorySelect = document.getElementById('subcategorySelect');

        if (!categoryId) {
            subcategoryDiv.style.display = 'none';
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            return;
        }

        // Fetch subcategories
        fetch(`/seller/categories/${categoryId}/subcategories`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                
                if (data.length > 0) {
                    data.forEach(subcategory => {
                        const option = document.createElement('option');
                        option.value = subcategory.id;
                        option.textContent = subcategory.name;
                        subcategorySelect.appendChild(option);
                    });
                    subcategoryDiv.style.display = 'block';
                } else {
                    subcategoryDiv.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error fetching subcategories:', error);
                subcategoryDiv.style.display = 'none';
            });
    });
</script>
@endpush
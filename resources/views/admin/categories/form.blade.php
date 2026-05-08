@extends('admin.layouts.app')

@section('title', isset($category) ? 'Edit Category' : 'Create Category')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back
                </a>
            </div>
            <h4 class="page-title">{{ isset($category) ? 'Edit' : 'Create' }} Category</h4>
        </div>
    </div>
</div>

<form action="{{ isset($category) ? route('admin.categories.update', $category) : route('admin.categories.store') }}" 
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($category))
        @method('PUT')
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Category Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $category->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="4" maxlength="1000">{{ old('description', $category->description ?? '') }}</textarea>
                        <small class="text-muted">Maximum 1000 characters</small>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
                        <small class="text-muted">Lower numbers appear first</small>
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Category Image</h5>
                </div>
                <div class="card-body">
                    @if(isset($category) && $category->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $category->image) }}" 
                             alt="{{ $category->name }}" 
                             class="img-fluid rounded"
                             style="max-height: 200px;">
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">Upload Image</label>
                        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" 
                               accept="image/*">
                        <small class="text-muted">Recommended: 500x500px, Max 2MB</small>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Settings</h5>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured"
                               {{ old('is_featured', $category->is_featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isFeatured">
                            Featured Category
                        </label>
                        <small class="d-block text-muted">Show on homepage</small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i data-lucide="save" class="me-1"></i>
                        {{ isset($category) ? 'Update Category' : 'Create Category' }}
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary w-100">
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
</script>
@endpush
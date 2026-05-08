@extends('admin.layouts.app')

@section('title', isset($subcategory) ? 'Edit Subcategory' : 'Create Subcategory')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back
                </a>
            </div>
            <h4 class="page-title">{{ isset($subcategory) ? 'Edit' : 'Create' }} Subcategory</h4>
            <p class="text-muted">Parent Category: <strong>{{ $category->name }}</strong></p>
        </div>
    </div>
</div>

<form action="{{ isset($subcategory) ? route('admin.categories.subcategories.update', $subcategory) : route('admin.categories.subcategories.store', $category) }}" 
      method="POST" enctype="multipart/form-data">
    @csrf
    @if(isset($subcategory))
        @method('PUT')
    @endif
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Subcategory Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Parent Category</label>
                        <input type="text" class="form-control" value="{{ $category->name }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Subcategory Name *</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $subcategory->name ?? '') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="4" maxlength="1000">{{ old('description', $subcategory->description ?? '') }}</textarea>
                        <small class="text-muted">Maximum 1000 characters</small>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control @error('sort_order') is-invalid @enderror" 
                               value="{{ old('sort_order', $subcategory->sort_order ?? 0) }}" min="0">
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
                    <h5 class="mb-0">Subcategory Image</h5>
                </div>
                <div class="card-body">
                    @if(isset($subcategory) && $subcategory->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/' . $subcategory->image) }}" 
                             alt="{{ $subcategory->name }}" 
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
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                               {{ old('is_active', $subcategory->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="isActive">
                            Active
                        </label>
                        <small class="d-block text-muted">Show on store</small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i data-lucide="save" class="me-1"></i>
                        {{ isset($subcategory) ? 'Update Subcategory' : 'Create Subcategory' }}
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
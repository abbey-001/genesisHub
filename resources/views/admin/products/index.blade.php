@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.products.export', request()->query()) }}" class="btn btn-success">
                    <i data-lucide="download" class="me-1"></i>Export
                </a>
            </div>
            <h4 class="page-title">Product Management</h4>
            <p class="text-muted">Manage all products across the platform</p>
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
                                <i data-lucide="package" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Products</p>
                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
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
                        <p class="text-muted mb-1">Active</p>
                        <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card border-warning">
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
                        <p class="text-muted mb-1">Featured</p>
                        <h4 class="mb-0">{{ number_format($stats['featured']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-sm-6">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded">
                                <i data-lucide="alert-triangle" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Out of Stock</p>
                        <h4 class="mb-0">{{ number_format($stats['out_of_stock']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters & Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Products</h5>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.products.index') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search products..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <select name="stock_status" class="form-select">
                            <option value="">Stock Status</option>
                            <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                            <option value="low_stock" {{ request('stock_status') === 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            <option value="out_of_stock" {{ request('stock_status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="filter" class="me-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                            <i data-lucide="x" class="me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="card-body border-bottom">
                <form id="bulkActionForm" action="{{ route('admin.products.bulk-action') }}" method="POST" class="row g-3">
                    @csrf
                    <div class="col-md-3">
                        <select name="action" class="form-select" required>
                            <option value="">Bulk Actions</option>
                            <option value="activate">Activate</option>
                            <option value="deactivate">Deactivate</option>
                            <option value="feature">Feature</option>
                            <option value="unfeature">Unfeature</option>
                            <option value="delete">Delete</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="zap" class="me-1"></i>Apply
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Shop</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" 
                                           class="form-check-input product-checkbox">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ asset('public/storage/' . $product->main_image) }}" 
                                             alt="{{ $product->name }}" 
                                             class="rounded me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0">{{ Str::limit($product->name, 40) }}</h6>
                                            <small class="text-muted">{{ $product->sku ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $product->category->name ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <small>{{ $product->shop->shop_name ?? 'N/A' }}</small>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold">₦{{ number_format($product->price, 2) }}</span>
                                        @if($product->sale_price)
                                        <br>
                                        <small class="text-muted"><del>₦{{ number_format($product->sale_price, 2) }}</del></small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($product->stock <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($product->stock <= 10)
                                        <span class="badge bg-warning">{{ $product->stock }} left</span>
                                    @else
                                        <span class="badge bg-success">{{ $product->stock }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                    @if($product->is_featured)
                                        <span class="badge bg-primary">Featured</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.products.show', $product) }}">
                                                    <i data-lucide="eye" class="me-2 fs-16"></i>View Details
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.products.edit', $product) }}">
                                                    <i data-lucide="edit" class="me-2 fs-16"></i>Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i data-lucide="power" class="me-2 fs-16"></i>
                                                        {{ $product->is_active ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li>
                                                <form action="{{ route('admin.products.toggle-featured', $product) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i data-lucide="star" class="me-2 fs-16"></i>
                                                        {{ $product->is_featured ? 'Unfeature' : 'Feature' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.products.destroy', $product) }}" method="POST"
                                                      onsubmit="return confirm('Are you sure?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i data-lucide="trash-2" class="me-2 fs-16"></i>Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No products found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($products->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }}
                    </div>
                    {{ $products->links() }}
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

    // Select all checkbox
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });

    // Bulk action form
    document.getElementById('bulkActionForm').addEventListener('submit', function(e) {
        const checkedBoxes = document.querySelectorAll('.product-checkbox:checked');
        if (checkedBoxes.length === 0) {
            e.preventDefault();
            alert('Please select at least one product');
            return false;
        }

        const action = this.querySelector('[name="action"]').value;
        if (!confirm(`Are you sure you want to ${action} ${checkedBoxes.length} product(s)?`)) {
            e.preventDefault();
            return false;
        }

        // Add selected IDs to form
        checkedBoxes.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'product_ids[]';
            input.value = cb.value;
            this.appendChild(input);
        });
    });
</script>
@endpush
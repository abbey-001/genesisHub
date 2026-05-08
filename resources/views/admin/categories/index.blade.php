@extends('admin.layouts.app')

@section('title', 'Categories')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                    <i data-lucide="plus" class="me-1"></i>Add Category
                </a>
            </div>
            <h4 class="page-title">Category Management</h4>
            <p class="text-muted">Organize products into categories and subcategories</p>
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
                                <i data-lucide="grid" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Categories</p>
                        <h4 class="mb-0">{{ number_format($stats['total_categories']) }}</h4>
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
                                <i data-lucide="list" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Subcategories</p>
                        <h4 class="mb-0">{{ number_format($stats['total_subcategories']) }}</h4>
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
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="avatar-sm">
                            <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                                <i data-lucide="package" class="fs-20"></i>
                            </div>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="text-muted mb-1">Total Products</p>
                        <h4 class="mb-0">{{ number_format($stats['total_products']) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Categories List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Categories</h5>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Name</th>
                                <th>Subcategories</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTable">
                            @forelse($categories as $category)
                            <tr data-id="{{ $category->id }}">
                                <td>
                                    @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" 
                                         alt="{{ $category->name }}" 
                                         class="rounded"
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                    <div class="avatar-sm">
                                        <div class="avatar-title bg-secondary rounded">
                                            {{ strtoupper(substr($category->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0">{{ $category->name }}</h6>
                                        @if($category->description)
                                        <small class="text-muted">{{ Str::limit($category->description, 50) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $category->subcategories_count }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $category->products_count }}</span>
                                </td>
                                <td>
                                    @if($category->is_featured)
                                    <span class="badge bg-warning">Featured</span>
                                    @else
                                    <span class="badge bg-secondary">Regular</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $category->sort_order }}</span>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button" 
                                                data-bs-toggle="dropdown">
                                            Actions
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.categories.edit', $category) }}">
                                                    <i data-lucide="edit" class="me-2 fs-16"></i>Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="{{ route('admin.categories.subcategories.create', $category) }}">
                                                    <i data-lucide="plus" class="me-2 fs-16"></i>Add Subcategory
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                                      onsubmit="return confirm('Are you sure? This will also delete all subcategories.')">
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

                            <!-- Subcategories -->
                            @if($category->subcategories->count() > 0)
                                @foreach($category->subcategories as $subcategory)
                                <tr class="table-secondary">
                                    <td class="ps-5">
                                        @if($subcategory->image)
                                        <img src="{{ asset('storage/' . $subcategory->image) }}" 
                                             alt="{{ $subcategory->name }}" 
                                             class="rounded"
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        @else
                                        <div class="avatar-sm">
                                            <div class="avatar-title bg-light rounded">
                                                {{ strtoupper(substr($subcategory->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">↳</small>
                                        <span>{{ $subcategory->name }}</span>
                                    </td>
                                    <td>-</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $subcategory->products_count ?? 0 }}</span>
                                    </td>
                                    <td>
                                        @if($subcategory->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $subcategory->sort_order }}</span>
                                    </td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light dropdown-toggle" type="button" 
                                                    data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.categories.subcategories.edit', $subcategory) }}">
                                                        <i data-lucide="edit" class="me-2 fs-16"></i>Edit
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.categories.subcategories.destroy', $subcategory) }}" method="POST"
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
                                @endforeach
                            @endif
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i data-lucide="inbox" class="text-muted mb-3" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mb-0">No categories found</p>
                                    <a href="{{ route('admin.categories.create') }}" class="btn btn-sm btn-primary mt-2">
                                        <i data-lucide="plus" class="me-1"></i>Create First Category
                                    </a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
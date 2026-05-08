@extends('admin.layouts.app')
@section('title', 'Seller Products')
@section('content')

<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="page-title">Products — {{ $seller->shop->shop_name ?? 'Seller' }}</h4>
                    <p class="text-muted mb-0">{{ $seller->user->name }} • {{ $seller->user->email }}</p>
                </div>
                <a href="{{ route('admin.sellers.show', $seller) }}" class="btn btn-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>Back to Seller
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i data-lucide="check-circle" class="me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show"><i data-lucide="alert-circle" class="me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<!-- Statistics — sourced from controller, reflects full dataset not just current page -->
<div class="row">
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-primary bg-opacity-10 text-primary rounded">
                            <i data-lucide="package" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Total Products</p>
                        <h4 class="mb-0">{{ $productStats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-success bg-opacity-10 text-success rounded">
                            <i data-lucide="check-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Active</p>
                        <h4 class="mb-0">{{ $productStats['active'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-warning bg-opacity-10 text-warning rounded">
                            <i data-lucide="alert-circle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Inactive</p>
                        <h4 class="mb-0">{{ $productStats['inactive'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="avatar-sm me-3">
                        <div class="avatar-title bg-danger bg-opacity-10 text-danger rounded">
                            <i data-lucide="alert-triangle" class="fs-20"></i>
                        </div>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Out of Stock</p>
                        <h4 class="mb-0">{{ $productStats['out_of_stock'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">All Products ({{ $products->total() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 70px;">Image</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Sold</th>
                                <th>Rating</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                            <tr>
                                <td>
                                    <img src="{{ asset('storage/' . $product->main_image) }}"
                                         alt="{{ $product->name }}"
                                         class="rounded"
                                         style="width: 60px; height: 60px; object-fit: cover;"
                                         onerror="this.src='{{ asset('img/default-product.jpg') }}'">
                                </td>
                                <td>
                                    <h6 class="mb-1">{{ Str::limit($product->name, 50) }}</h6>
                                    @if($product->sku)
                                        <small class="text-muted">SKU: {{ $product->sku }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->category)
                                        <span class="badge bg-info">{{ $product->category->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-bold">₦{{ number_format($product->price, 2) }}</span>
                                    @if($product->sale_price && $product->sale_price < $product->price)
                                        <br><small class="text-danger">₦{{ number_format($product->sale_price, 2) }} sale</small>
                                    @endif
                                </td>
                                <td>
                                    @if($product->stock <= 0)
                                        <span class="badge bg-danger">Out of Stock</span>
                                    @elseif($product->stock <= 10)
                                        <span class="badge bg-warning">{{ $product->stock }} left</span>
                                    @else
                                        <span class="text-success fw-bold">{{ $product->stock }}</span>
                                    @endif
                                </td>
                                <td>{{ $product->sold_count ?? 0 }}</td>
                                <td>
                                    @if($product->rating)
                                        <span class="text-warning">★</span>
                                        {{ number_format($product->rating, 1) }}
                                        <small class="text-muted">({{ $product->review_count ?? 0 }})</small>
                                    @else
                                        <span class="text-muted">No ratings</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                    @if($product->is_featured)
                                        <br><span class="badge bg-primary mt-1">Featured</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-light dropdown-toggle" type="button"
                                                data-bs-toggle="dropdown">Actions</button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.products.show', $product) }}">
                                                    <i data-lucide="external-link" class="me-2 fs-16"></i>View Full Detail
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#viewModal{{ $product->id }}">
                                                    <i data-lucide="eye" class="me-2 fs-16"></i>Quick Preview
                                                </button>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <!-- Real toggle — POST to admin.products.toggle-status -->
                                            <li>
                                                <form action="{{ route('admin.products.toggle-status', $product) }}" method="POST">
                                                    @csrf
                                                    @if($product->is_active)
                                                    <button type="submit" class="dropdown-item text-warning">
                                                        <i data-lucide="x-circle" class="me-2 fs-16"></i>Deactivate
                                                    </button>
                                                    @else
                                                    <button type="submit" class="dropdown-item text-success">
                                                        <i data-lucide="check-circle" class="me-2 fs-16"></i>Activate
                                                    </button>
                                                    @endif
                                                </form>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Quick Preview Modal -->
                                    <div class="modal fade" id="viewModal{{ $product->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">{{ $product->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <img src="{{ asset('storage/' . $product->main_image) }}"
                                                                 alt="{{ $product->name }}"
                                                                 class="img-fluid rounded"
                                                                 onerror="this.src='{{ asset('img/default-product.jpg') }}'">
                                                        </div>
                                                        <div class="col-md-8">
                                                            <p class="text-muted">{{ $product->short_description }}</p>
                                                            <table class="table table-sm table-borderless">
                                                                <tr>
                                                                    <td class="text-muted">Price:</td>
                                                                    <td class="fw-bold">₦{{ number_format($product->price, 2) }}</td>
                                                                </tr>
                                                                @if($product->sale_price)
                                                                <tr>
                                                                    <td class="text-muted">Sale Price:</td>
                                                                    <td class="fw-bold text-danger">₦{{ number_format($product->sale_price, 2) }}</td>
                                                                </tr>
                                                                @endif
                                                                <tr>
                                                                    <td class="text-muted">Stock:</td>
                                                                    <td>{{ $product->stock }} units</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Category:</td>
                                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Brand:</td>
                                                                    <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="text-muted">Status:</td>
                                                                    <td>
                                                                        <span class="badge bg-{{ $product->is_active ? 'success' : 'secondary' }}">
                                                                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            @if($product->description)
                                                            <div class="mt-2">
                                                                <h6>Description</h6>
                                                                <p class="small text-muted">{{ Str::limit(strip_tags($product->description), 300) }}</p>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a href="{{ route('admin.products.show', $product) }}" class="btn btn-primary">
                                                        <i data-lucide="external-link" class="me-1"></i>View Full Detail
                                                    </a>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i data-lucide="package" class="text-muted" style="width: 48px; height: 48px;"></i>
                                    <p class="text-muted mt-2 mb-0">No products found</p>
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
<script>lucide.createIcons();</script>
@endpush
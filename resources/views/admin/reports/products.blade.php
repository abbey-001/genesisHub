@extends('admin.layouts.app')

@section('title', 'Product Analytics')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.reports.index') }}">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">Product Analytics</li>
                </ol>
            </nav>
            <h4 class="mb-1">📦 Product Analytics</h4>
            <p class="text-muted mb-0">Product performance, ratings and inventory insights</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="exportReport('pdf')">
                <i class="bx bx-download me-1"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reports.products') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Period</label>
                    <select name="period" class="form-select">
                        <option value="this_week" {{ request('period') == 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('period', 'this_month') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="this_year" {{ request('period') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\Category::all() as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Seller</label>
                    <select name="seller_id" class="form-select">
                        <option value="">All Sellers</option>
                        @foreach(\App\Models\Seller::with('shop')->get() as $seller)
                            <option value="{{ $seller->shop->id ?? '' }}" {{ request('seller_id') == ($seller->shop->id ?? '') ? 'selected' : '' }}>
                                {{ $seller->shop->shop_name ?? 'Seller #' . $seller->id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sort By</label>
                    <select name="sort_by" class="form-select">
                        <option value="revenue" {{ request('sort_by', 'revenue') == 'revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="orders" {{ request('sort_by') == 'orders' ? 'selected' : '' }}>Orders</option>
                        <option value="views" {{ request('sort_by') == 'views' ? 'selected' : '' }}>Views</option>
                        <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Rating</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label d-block">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bx bx-filter me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-primary">
                            <i class="bx bx-box bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-primary">{{ number_format($data['summary']['total_products']) }}</h5>
                    <p class="mb-0 text-muted small">Total Products</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-success">
                            <i class="bx bx-check-circle bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-success">{{ number_format($data['summary']['active_products']) }}</h5>
                    <p class="mb-0 text-muted small">Active Products</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-danger">
                            <i class="bx bx-error-circle bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-danger">{{ number_format($data['summary']['out_of_stock']) }}</h5>
                    <p class="mb-0 text-muted small">Out of Stock</p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="avatar avatar-md bg-label-warning">
                            <i class="bx bx-info-circle bx-md"></i>
                        </div>
                    </div>
                    <h5 class="mb-0 text-warning">{{ number_format($data['summary']['low_stock']) }}</h5>
                    <p class="mb-0 text-muted small">Low Stock (≤10)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <!-- Products by Category -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Products by Category</h5>
                </div>
                <div class="card-body">
                <div class="chart-container">
                    <canvas id="productsByCategoryChart" height="280"></canvas>
                </div>
                </div>
            </div>
        </div>

        <!-- Rating Distribution -->
        <div class="col-xl-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Product Rating Distribution</h5>
                    <small class="text-muted">Average Rating: {{ number_format($data['avg_rating'] ?? 0, 1) }}/5.0</small>
                </div>
                <div class="card-body">
                <div class="chart-container">
                    <canvas id="ratingDistributionChart" height="280"></canvas>
                </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Products Trend -->
    @if($data['new_products']->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">New Products Added</h5>
            <small class="text-muted">Products added over time</small>
        </div>
        <div class="card-body">
        <div class="chart-container">
            <canvas id="newProductsChart" height="80"></canvas>
        </div>
        </div>
    </div>
    @endif

    <!-- Best Sellers -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Top 20 Best Selling Products</h5>
                <small class="text-muted">
                    Sorted by: {{ ucfirst(request('sort_by', 'revenue')) }}
                </small>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Rank</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-center">Sold</th>
                            <th class="text-end">Revenue</th>
                            <th class="text-center">Orders</th>
                            <th class="text-center">Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data['best_sellers'] as $index => $product)
                        <tr>
                            <td>
                                <span class="badge bg-label-{{ $index < 3 ? 'warning' : ($index < 10 ? 'primary' : 'secondary') }}">
                                    #{{ $index + 1 }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($product->product && $product->product->images->count() > 0)
                                    <img src="{{ asset('storage/'.$product->product->images->first()->image_path) }}" 
                                         alt="" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                    <div class="avatar avatar-sm bg-label-secondary me-2">
                                        <i class="bx bx-package"></i>
                                    </div>
                                    @endif
                                    <div>
                                        <div class="fw-medium">{{ Str::limit($product->product_name, 40) }}</div>
                                        <small class="text-muted">ID: {{ $product->product_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($product->product && $product->product->category)
                                <span class="badge bg-label-info">{{ $product->product->category->name }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong class="text-primary">{{ number_format($product->total_sold) }}</strong>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold text-success">₦{{ number_format($product->revenue, 2) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-label-primary">{{ $product->order_count }}</span>
                            </td>
                            <td class="text-center">
                                @if($product->product)
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="bx bx-star text-warning me-1"></i>
                                    <span>{{ number_format($product->product->rating ?? 0, 1) }}</span>
                                </div>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No product data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Products by Category Chart
    const categoryCtx = document.getElementById('productsByCategoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($data['products_by_category']->pluck('category.name')) !!},
            datasets: [{
                data: {!! json_encode($data['products_by_category']->pluck('count')) !!},
                backgroundColor: [
                    '#696cff',
                    '#71dd37',
                    '#ffab00',
                    '#ff3e1d',
                    '#03c3ec',
                    '#8592a3',
                    '#233446',
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} products (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingDistributionChart').getContext('2d');
    new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($data['rating_distribution']->pluck('rating_range')) !!},
            datasets: [{
                label: 'Number of Products',
                data: {!! json_encode($data['rating_distribution']->pluck('count')) !!},
                backgroundColor: [
                    '#ffab00', // 5 stars
                    '#71dd37', // 4 stars
                    '#696cff', // 3 stars
                    '#ff3e1d', // 2 stars
                    '#8592a3', // 1 star
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    @if($data['new_products']->count() > 0)
    // New Products Chart
    const newProductsCtx = document.getElementById('newProductsChart').getContext('2d');
    new Chart(newProductsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($data['new_products']->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!},
            datasets: [{
                label: 'New Products',
                data: {!! json_encode($data['new_products']->pluck('count')) !!},
                borderColor: '#696cff',
                backgroundColor: 'rgba(105, 108, 255, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif

    function exportReport(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('format', format);
        params.set('type', 'products');
        
        window.location.href = '{{ route("admin.reports.export") }}?' + params.toString();
    }
</script>
@endpush
@endsection
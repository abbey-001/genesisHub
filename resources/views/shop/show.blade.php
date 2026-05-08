@extends('layouts.app')

@section('title', $shop->shop_name . ' - ' . config('app.name'))

@section('content')
<style>
  .shop-banner {
    height: 300px;
    background-size: cover;
    background-position: center;
    position: relative;
    border-radius: 8px;
    overflow: hidden;
  }
  
  .shop-banner::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0.6));
  }

  .shop-info-card {
    position: relative;
    z-index: 10;
    margin-top: -100px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    padding: 30px;
  }

  .shop-logo {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 5px solid white;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  }

  .shop-stats {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
  }

  .stat-item {
    text-align: center;
  }

  .stat-value {
    font-size: 24px;
    font-weight: 700;
    color: #714e32;
    display: block;
  }

  .stat-label {
    font-size: 13px;
    color: #6c757d;
    margin-top: 5px;
  }

  .product-filter-bar {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 30px;
  }

  .category-badge {
    display: inline-block;
    padding: 8px 15px;
    margin: 5px;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.3s;
  }

  .category-badge:hover,
  .category-badge.active {
    background: #714e32;
    color: white;
    border-color: #714e32;
  }

  /* Product card improvements */
  .product-card {
    transition: all 0.3s ease;
    border: 1px solid #dee2e6;
  }

  .product-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-5px);
  }

  .product-card .card-img-top {
    transition: transform 0.3s ease;
  }

  .product-card:hover .card-img-top {
    transform: scale(1.05);
  }

  @media (max-width: 768px) {
    .shop-banner {
      height: 200px;
    }
    
    .shop-info-card {
      margin-top: -60px;
      padding: 20px;
    }
    
    .shop-logo {
      width: 80px;
      height: 80px;
    }
    
    .shop-stats {
      gap: 15px;
    }
  }
</style>

<div class="wrapper ovh">
  
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])
  
  <div class="body_content_wrapper">
    <!-- Shop Banner -->
    <section class="shop-header">
      <div class="container">
        <div class="shop-banner" style="background-image: url('{{ $shop->banner_url }}');">
        </div>
      </div>
    </section>

    <!-- Shop Info Card -->
    <section class="shop-info-section">
      <div class="container">
        <div class="shop-info-card">
          <div class="row align-items-center">
            <div class="col-lg-8">
              <div class="d-flex align-items-center mb-3">
                <img src="{{ $shop->logo_url }}" alt="{{ $shop->shop_name }}" class="shop-logo me-4">
                <div>
                  <h2 class="mb-2">{{ $shop->shop_name }}</h2>
                  @if($stats['avg_rating'])
                  <div class="d-flex align-items-center mb-2">
                    <div class="star-rating me-2">
                      @for($i = 1; $i <= 5; $i++)
                        <i class="fa fa-star {{ $i <= round($stats['avg_rating']) ? 'text-warning' : 'text-muted' }}"></i>
                      @endfor
                    </div>
                    <span class="text-muted">{{ number_format($stats['avg_rating'], 1) }} ({{ $stats['total_reviews'] }} reviews)</span>
                  </div>
                  @endif
                  @if($shop->shop_description)
                    <p class="text-muted mb-0">{{ Str::limit($shop->shop_description, 150) }}</p>
                  @endif
                </div>
              </div>
            </div>
            
            <div class="col-lg-4">
              <div class="shop-stats justify-content-lg-end">
                <div class="stat-item">
                  <span class="stat-value">{{ number_format($stats['total_products']) }}</span>
                  <span class="stat-label">Products</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ number_format($stats['total_sold']) }}</span>
                  <span class="stat-label">Sold</span>
                </div>
                <div class="stat-item">
                  <span class="stat-value">{{ number_format($stats['total_reviews']) }}</span>
                  <span class="stat-label">Reviews</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Contact Info -->
          @if($shop->shop_email || $shop->shop_phone)
          <div class="shop-contact mt-4 pt-4 border-top">
            <div class="row">
              @if($shop->shop_email)
              <div class="col-md-6">
                <i class="fas fa-envelope text-thm me-2"></i>
                <a href="mailto:{{ $shop->shop_email }}">{{ $shop->shop_email }}</a>
              </div>
              @endif
              @if($shop->shop_phone)
              <div class="col-md-6">
                <i class="fas fa-phone text-thm me-2"></i>
                <a href="tel:{{ $shop->shop_phone }}">{{ $shop->shop_phone }}</a>
              </div>
              @endif
            </div>
          </div>
          @endif
        </div>
      </div>
    </section>

    <!-- Shop Products -->
    <section class="shop-products pt-4 pb-5">
      <div class="container">
        <!-- Filters & Search -->
        <div class="product-filter-bar">
          <form method="GET" action="{{ route('shop.show', $shop->slug ?? $shop->id) }}">
            <div class="row g-3 align-items-end">
              <div class="col-md-4">
                <label class="form-label">Search Products</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Search in this shop..." 
                       value="{{ request('search') }}">
              </div>

              <div class="col-md-3">
                <label class="form-label">Price Range</label>
                <div class="d-flex gap-2">
                  <input type="number" name="min_price" class="form-control" 
                         placeholder="Min" value="{{ request('min_price') }}">
                  <input type="number" name="max_price" class="form-control" 
                         placeholder="Max" value="{{ request('max_price') }}">
                </div>
              </div>

              <div class="col-md-3">
                <label class="form-label">Sort By</label>
                <select name="sort" class="form-select">
                  <option value="">Default</option>
                  <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                  <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                  <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>Most Popular</option>
                </select>
              </div>

              <div class="col-md-2">
                <button type="submit" class="btn btn-thm w-100">
                  <i class="fas fa-filter me-1"></i> Filter
                </button>
              </div>
            </div>
          </form>

          <!-- Categories -->
          @if($shopCategories->count() > 0)
          <div class="shop-categories mt-3">
            <label class="form-label d-block">Categories:</label>
            <a href="{{ route('shop.show', $shop->slug ?? $shop->id) }}" 
               class="category-badge {{ !request('category') ? 'active' : '' }}">
              All Products
            </a>
            @foreach($shopCategories as $category)
              <a href="{{ route('shop.show', $shop->slug ?? $shop->id) }}?category={{ $category->id }}" 
                 class="category-badge {{ request('category') == $category->id ? 'active' : '' }}">
                {{ $category->name }} ({{ $category->products_count }})
              </a>
            @endforeach
          </div>
          @endif
        </div>

        <!-- Results Info -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h5 class="mb-0">
            @if(request('category'))
              {{ $shopCategories->find(request('category'))->name ?? 'Products' }}
            @else
              All Products
            @endif
            <span class="text-muted">({{ $products->total() }} items)</span>
          </h5>
        </div>

        <!-- Products Grid -->
        <div class="row">
          @forelse($products as $product)
          <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
            <div class="card product-card h-100">
              <a href="{{ route('product.show', $product->id) }}">
                <div class="position-relative overflow-hidden">
                  <img src="{{ asset('public/storage/' . $product->main_image) }}" 
                       class="card-img-top" 
                       alt="{{ $product->name }}"
                       style="height: 250px; object-fit: cover;">
                  
                  @if($product->discount_percentage)
                  <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                    -{{ $product->discount_percentage }}%
                  </span>
                  @endif
                  
                  @if($product->stock <= 0)
                  <span class="badge bg-secondary position-absolute top-0 start-0 m-2">
                    Out of Stock
                  </span>
                  @endif
                </div>
              </a>
              
              <div class="card-body">
                <h6 class="card-title mb-2">
                  <a href="{{ route('product.show', $product->id) }}" class="text-dark text-decoration-none">
                    {{ Str::limit($product->name, 50) }}
                  </a>
                </h6>
                
                @if($product->rating)
                <div class="mb-2">
                  <div class="star-rating d-inline-block">
                    @for($i = 1; $i <= 5; $i++)
                      <i class="fa fa-star {{ $i <= round($product->rating) ? 'text-warning' : 'text-muted' }}" style="font-size: 12px;"></i>
                    @endfor
                  </div>
                  <span class="text-muted" style="font-size: 12px;">({{ $product->review_count }})</span>
                </div>
                @endif
                
                <div class="price mb-3">
                  @if($product->sale_price && $product->sale_price < $product->price)
                    <span class="text-thm fw-bold fs-5">₦{{ number_format($product->sale_price, 2) }}</span>
                    <del class="text-muted ms-2">₦{{ number_format($product->price, 2) }}</del>
                  @else
                    <span class="text-thm fw-bold fs-5">₦{{ number_format($product->price, 2) }}</span>
                  @endif
                </div>
                
                @if($product->stock > 0)
                {{-- FIXED: Added required classes and data attributes --}}
                <button type="button" 
                        class="btn btn-thm btn-sm w-100 add-to-cart" 
                        data-product-id="{{ $product->id }}">
                  <i class="fas fa-shopping-cart me-1"></i> Add to Cart
                </button>
                @else
                <button class="btn btn-secondary btn-sm w-100" disabled>
                  Out of Stock
                </button>
                @endif
              </div>
            </div>
          </div>
          @empty
          <div class="col-12 text-center py-5">
            <i class="fas fa-box-open text-muted" style="font-size: 64px;"></i>
            <h4 class="mt-3 text-muted">No products found</h4>
            <p class="text-muted">Try adjusting your filters or check back later</p>
          </div>
          @endforelse
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
        <div class="row mt-4">
          <div class="col-12 d-flex justify-content-center">
            {{ $products->withQueryString()->links() }}
          </div>
        </div>
        @endif
      </div>
    </section>

    <!-- Recent Reviews Section -->
    @if($recentReviews->count() > 0)
    <section class="shop-reviews bg-light py-5">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <h4 class="mb-0">Recent Reviews</h4>
          <a href="{{ route('shop.reviews', $shop->slug ?? $shop->id) }}" class="btn btn-outline-primary">
            View All Reviews <i class="fas fa-arrow-right ms-1"></i>
          </a>
        </div>

        <div class="row">
          @foreach($recentReviews as $review)
          <div class="col-md-6 mb-3">
            <div class="card h-100">
              <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar-circle bg-primary text-white d-flex align-items-center justify-content-center"
                         style="width: 40px; height: 40px; border-radius: 50%;">
                      {{ strtoupper(substr($review->user->name, 0, 1)) }}
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h6 class="mb-1">{{ $review->user->name }}</h6>
                        @if($review->is_verified_purchase)
                          <span class="badge bg-success badge-sm">Verified Purchase</span>
                        @endif
                      </div>
                      <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                    </div>
                    
                    <div class="star-rating mt-2 mb-2">
                      @for($i = 1; $i <= 5; $i++)
                        <i class="fa fa-star {{ $i <= $review->rating ? 'text-warning' : 'text-muted' }}"></i>
                      @endfor
                    </div>
                    
                    <p class="mb-2 text-muted">
                      <a href="{{ route('product.show', $review->product->slug) }}" class="text-decoration-none">
                        {{ $review->product->name }}
                      </a>
                    </p>
                    
                    <p class="mb-0">{{ Str::limit($review->comment, 100) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </div>
    </section>
    @endif

    <!-- Shop Description -->
    @if($shop->shop_description)
    <section class="shop-description py-5">
      <div class="container">
        <h4 class="mb-3">About {{ $shop->shop_name }}</h4>
        <div class="row">
          <div class="col-lg-8">
            <p class="text-muted">{{ $shop->shop_description }}</p>
            
            @if($shop->shop_address)
            <div class="mt-4">
              <h6><i class="fas fa-map-marker-alt text-thm me-2"></i>Location</h6>
              <p class="text-muted mb-0">
                {{ $shop->shop_address }}
                @if($shop->shop_city), {{ $shop->shop_city }}@endif
                @if($shop->shop_state), {{ $shop->shop_state }}@endif
                @if($shop->shop_postal_code) {{ $shop->shop_postal_code }}@endif
              </p>
            </div>
            @endif
          </div>
        </div>
      </div>
    </section>
    @endif
  </div>

  @include('partials.footer')
</div>

@endsection
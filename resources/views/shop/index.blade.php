@extends('layouts.app')

@section('title', 'All Shops - ' . config('app.name'))

@section('content')
<style>
  .shop-card {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
  }

  .shop-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
  }

  .hover-shadow {
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  .shop-stats-small > div {
    flex: 1;
  }

  @media (max-width: 768px) {
    .shop-banner-small {
      height: 120px !important;
    }
    
    .shop-stats-small {
      flex-direction: column;
      gap: 15px;
    }
    
    .shop-stats-small > div {
      border: none !important;
      padding: 0 !important;
    }
  }
</style>
<div class="wrapper ovh">
  
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])
  
  <div class="body_content_wrapper">
    <!-- Page Header -->
    <section class="shops-header bg-light py-5">
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <h1 class="mb-2">Explore Our Shops</h1>
            <p class="text-muted mb-0">Discover amazing products from verified sellers</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Shops Section -->
    <section class="shops-listing py-5">
      <div class="container">
        <!-- Search & Filter Bar -->
        <div class="filter-bar bg-light p-4 rounded mb-4">
          <form method="GET" action="{{ route('shop.index') }}">
            <div class="row g-3">
              <div class="col-md-6">
                <input type="text" name="search" class="form-control" 
                       placeholder="Search shops..." 
                       value="{{ request('search') }}">
              </div>
              
              <div class="col-md-4">
                <select name="sort" class="form-select">
                  <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                  <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                  <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
                  <option value="products" {{ request('sort') == 'products' ? 'selected' : '' }}>Most Products</option>
                </select>
              </div>
              
              <div class="col-md-2">
                <button type="submit" class="btn btn-thm w-100">
                  <i class="fas fa-search me-1"></i> Search
                </button>
              </div>
            </div>
          </form>
        </div>

        <!-- Results Info -->
        <div class="mb-4">
          <h5 class="text-muted">{{ $sellers->total() }} shops found</h5>
        </div>

        <!-- Shops Grid -->
        <div class="row">
          @forelse($sellers as $shop)
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card shop-card h-100 hover-shadow">
              <a href="{{ route('shop.show', $shop->slug ?? $shop->id) }}" class="text-decoration-none">
                <!-- Shop Banner -->
                <div class="shop-banner-small position-relative" 
                     style="height: 150px; background: linear-gradient(135deg, #714e32 0%, #5a3e28 100%); overflow: hidden;">
                  @if($shop->shop_banner)
                    <img src="{{ $shop->banner_url }}" 
                         alt="{{ $shop->shop_name }}"
                         style="width: 100%; height: 100%; object-fit: cover;">
                  @endif
                  
                  <!-- Shop Logo Overlay -->
                  <div class="position-absolute bottom-0 start-0 translate-middle-y ms-3" style="bottom: -30px !important;">
                    <img src="{{ $shop->logo_url }}" 
                         alt="{{ $shop->shop_name }}"
                         class="rounded-circle border border-3 border-white"
                         style="width: 70px; height: 70px; object-fit: cover; background: white;">
                  </div>
                </div>
              </a>

              <div class="card-body pt-4 mt-2">
                <h5 class="card-title mb-2">
                  <a href="{{ route('shop.show', $shop->slug ?? $shop->id) }}" class="text-dark text-decoration-none">
                    {{ $shop->shop_name }}
                  </a>
                </h5>
                
                @if($shop->rating)
                <div class="mb-2">
                  <div class="star-rating d-inline-block">
                    @for($i = 1; $i <= 5; $i++)
                      <i class="fa fa-star {{ $i <= round($shop->rating) ? 'text-warning' : 'text-muted' }}" style="font-size: 14px;"></i>
                    @endfor
                  </div>
                  <span class="text-muted ms-1" style="font-size: 13px;">{{ number_format($shop->rating, 1) }}</span>
                </div>
                @endif

                @if($shop->shop_description)
                <p class="text-muted mb-3" style="font-size: 14px;">
                  {{ Str::limit($shop->shop_description, 80) }}
                </p>
                @endif

                <!-- Shop Stats -->
                <div class="shop-stats-small d-flex justify-content-between text-center pt-3 border-top">
                  <div>
                    <div class="fw-bold text-thm">{{ $shop->products_count }}</div>
                    <small class="text-muted">Products</small>
                  </div>
                  <div class="border-start border-end px-3">
                    <div class="fw-bold text-thm">{{ $shop->seller->user->name ?? 'N/A' }}</div>
                    <small class="text-muted">Owner</small>
                  </div>
                  <div>
                    <a href="{{ route('shop.show', $shop->slug ?? $shop->id) }}" class="btn btn-sm btn-outline-primary">
                      Visit Shop
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @empty
          <div class="col-12 text-center py-5">
            <i class="fas fa-store-slash text-muted" style="font-size: 64px;"></i>
            <h4 class="mt-4 text-muted">No shops found</h4>
            <p class="text-muted">Try adjusting your search or filters</p>
          </div>
          @endforelse
        </div>

        <!-- Pagination -->
        @if($sellers->hasPages())
        <div class="row mt-4">
          <div class="col-12 d-flex justify-content-center">
            {{ $sellers->withQueryString()->links() }}
          </div>
        </div>
        @endif
      </div>
    </section>
  </div>

  @include('partials.footer')
</div>



@endsection
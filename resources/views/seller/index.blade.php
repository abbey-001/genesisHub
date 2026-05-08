@extends('layouts.app')

@section('content')

<div class="wrapper ovh bgc-gmart-gray">
  <div class="preloader"></div>
  
  {{-- Desktop Header --}}
  @include('partials.header')
  
  {{-- Main Navigation --}}
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs])
  
  <div class="body_content_wrapper position-relative pt30">
    
    {{-- Page Header --}}
    <section class="sellers-header-section pt40 pb40 bgc-white">
      <div class="container maxw1800">
        <div class="row">
          <div class="col-lg-8">
            <h1 class="mb20">All Shops</h1>
            <p class="para heading-color">Browse and shop from our trusted sellers</p>
          </div>
        </div>
      </div>
    </section>
    
    {{-- Sellers Section --}}
    <section class="sellers-listing pt0">
      <div class="container-fluid maxw1800 p-4 bgc-white bdrs6">
        
        {{-- Search and Filter Bar --}}
        <div class="row bb1 mb40">
          <div class="col-lg-8">
            <form method="GET" action="{{ route('shop.index') }}" class="d-flex gap-3 flex-wrap">
              <div class="flex-grow-1 min-width-250">
                <input 
                  type="text" 
                  name="search" 
                  class="form-control" 
                  placeholder="Search shops..." 
                  value="{{ request('search') }}"
                >
              </div>
              <button type="submit" class="btn btn-thm">Search</button>
              @if(request('search'))
              <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary">Clear</a>
              @endif
            </form>
          </div>
          <div class="col-lg-4">
            <form method="GET" action="{{ route('shop.index') }}" class="d-flex gap-2">
              <input type="hidden" name="search" value="{{ request('search') }}">
              <select name="sort" class="form-select" onchange="this.form.submit()">
                <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                <option value="products" {{ request('sort') === 'products' ? 'selected' : '' }}>Most Products</option>
              </select>
            </form>
          </div>
        </div>

        {{-- Results Count --}}
        <div class="row mb30">
          <div class="col-12">
            <p class="text-muted">
              Showing <strong>{{ $sellers->count() }}</strong> of <strong>{{ $sellers->total() }}</strong> shops
              @if(request('search'))
              for "<strong>{{ request('search') }}</strong>"
              @endif
            </p>
          </div>
        </div>
        
        {{-- Sellers Grid --}}
        @if($sellers->count() > 0)
        <div class="row">
          @foreach($sellers as $seller)
          <div class="col-sm-6 col-lg-4 col-xl-3 mb40">
            <div class="seller-card bdrs6 overflow-hidden transition-all">
              <a href="{{ route('shop.show', $seller->shop->slug) }}" class="text-decoration-none">
                <div class="seller-card-header p20 bgc-light position-relative">
                  <div class="seller-logo-container d-flex align-items-center justify-content-center" style="height: 150px;">
                    <img src="{{ asset('public/storage/'.$seller->logo) }}" alt="{{ $seller->name }}" class="seller-card-logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                  </div>
                </div>
                
                <div class="seller-card-body p20">
                  <h5 class="seller-name mb10 fw600 color-dark">{{ $seller->name }}</h5>
                  
                  @if($seller->description)
                  <p class="seller-description para text-muted mb15 line-clamp-2" title="{{ $seller->description }}">
                    {{ Str::limit($seller->description, 80) }}
                  </p>
                  @endif
                  
                  <div class="seller-stats mb20">
                    <div class="stat-item d-flex align-items-center mb8">
                      <i class="fa fa-box text-primary me-2"></i>
                      <span class="text-muted">{{ $seller->products_count }} products</span>
                    </div>
                    @if($seller->email)
                    <div class="stat-item d-flex align-items-center">
                      <i class="fa fa-envelope text-primary me-2"></i>
                      <span class="text-muted small">{{ $seller->email }}</span>
                    </div>
                    @endif
                  </div>
                  
                  <div class="seller-actions">
                    <button type="button" class="btn btn-thm w100">Visit Shop</button>
                  </div>
                </div>
              </a>
            </div>
          </div>
          @endforeach
        </div>
        
        {{-- Pagination --}}
        <div class="row mt50">
          <div class="col-12">
            <div class="d-flex justify-content-center">
              {{ $sellers->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
          </div>
        </div>
        
        @else
        <div class="row">
          <div class="col-12 text-center py-5">
            <div class="empty-state">
              <i class="fa fa-store fa-3x text-muted mb20"></i>
              <h4 class="mb10">No Shops Found</h4>
              <p class="text-muted mb20">
                @if(request('search'))
                No shops match your search for "<strong>{{ request('search') }}</strong>". Try a different search term.
                @else
                No active shops available at the moment.
                @endif
              </p>
              @if(request('search'))
              <a href="{{ route('shop.index') }}" class="btn btn-thm">View All Shops</a>
              @endif
            </div>
          </div>
        </div>
        @endif
      </div>
    </section>
    
    {{-- Features Section --}}
    @include('partials.features')
    
    {{-- Footer --}}
    @include('partials.footer')
    
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>
@endsection

@push('styles')
<style>
  .seller-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    height: 100%;
    transition: all 0.3s ease;
  }
  
  .seller-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    border-color: #007bff;
    transform: translateY(-2px);
  }
  
  .seller-logo-container {
    background: #f8f9fa;
  }
  
  .seller-card-logo {
    border-radius: 4px;
  }
  
  .seller-card-body {
    flex: 1;
    display: flex;
    flex-direction: column;
  }
  
  .seller-name {
    font-size: 1.1rem;
    line-height: 1.4;
  }
  
  .seller-description {
    font-size: 0.9rem;
    line-height: 1.5;
  }
  
  .line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
  
  .seller-stats {
    flex: 1;
  }
  
  .stat-item {
    font-size: 0.9rem;
  }
  
  .seller-actions {
    margin-top: auto;
  }
  
  .btn-thm {
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    font-weight: 500;
    transition: all 0.3s ease;
  }
  
  .btn-thm:hover {
    background-color: #0056b3;
    color: #fff;
    text-decoration: none;
  }
  
  .min-width-250 {
    min-width: 250px;
  }
  
  .empty-state {
    padding: 60px 20px;
  }
  
  .empty-state i {
    opacity: 0.3;
  }
  
  @media (max-width: 768px) {
    .min-width-250 {
      min-width: 100%;
    }
    
    .seller-card-header {
      min-height: 120px;
    }
  }
</style>
@endpush

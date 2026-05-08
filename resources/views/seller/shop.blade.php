@extends('layouts.app')

@section('content')
<div class="wrapper ovh bgc-gmart-gray">
  <div class="preloader"></div>
  
  {{-- Desktop Header --}}
  @include('partials.header')
  
  {{-- Main Navigation --}}
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? null])
  
  <div class="body_content_wrapper position-relative pt30">
    
    {{-- Shop Header Banner --}}
    <section class="seller-header-banner pt40 pb40">
      <div class="container maxw1800">
        <div class="row align-items-center">
          <div class="col-md-3 text-center mb-4 mb-md-0">
            <div class="seller-logo-wrapper">
<img 
    src="{{ asset('storage/'.$seller->banner) }}" 
    alt="{{ $seller->name }}" 
    class="seller-logo" 
    style="max-width: 200px; height: auto;"
    onerror="this.onerror=null; this.src='{{ asset('storage/'.$seller->logo) }}';">
            </div>
          </div>
          <div class="col-md-9">
            <div class="seller-info">
              <h1 class="mb20">{{ $seller->name }}</h1>
              @if($seller->description)
              <p class="para heading-color mb20">{{ $seller->description }}</p>
              @endif
              <div class="seller-meta mb20">
                <span class="meta-item">
                  <i class="fa fa-box"></i> {{ $products->total() }} Products
                </span>
                @if($seller->email)
                <span class="meta-item">
                  <i class="fa fa-envelope"></i> {{ $seller->email }}
                </span>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    {{-- Products Listing Section --}}
    <section class="seller-products pt0">
      <div class="container-fluid maxw1800 p-4 bgc-white bdrs6">
        <div class="row bb1 mb30">
          <div class="col-md-6">
            <div class="main-title text-center text-md-start">
              <h2 class="mb0">Products from {{ $seller->name }}</h2>
            </div>
          </div>
        </div>
        
        @if($products->count() > 0)
        <div class="row">
          @foreach($products as $product)
          <div class="col-sm-6 col-lg-4 col-xl-3 mb30">
            @include('partials.product-card', ['product' => $product])
          </div>
          @endforeach
        </div>
        
        {{-- Pagination --}}
        <div class="row mt40">
          <div class="col-12">
            <div class="d-flex justify-content-center">
              {{ $products->links('pagination::bootstrap-4') }}
            </div>
          </div>
        </div>
        @else
        <div class="row">
          <div class="col-12 text-center py-5">
            <p class="text-muted">This shop has no products available at the moment.</p>
          </div>
        </div>
        @endif
      </div>
    </section>
    
    {{-- Related Shops Section --}}
    @if($relatedSellers->count() > 0)
    <section class="related-sellers pt0 pb30">
      <div class="container-fluid maxw1800 p-4 bgc-white bdrs6">
        <div class="row bb1 mb30">
          <div class="col-md-6">
            <div class="main-title text-center text-md-start">
              <h2 class="mb0">Other Popular Shops</h2>
            </div>
          </div>
        </div>
        <div class="row">
          @foreach($relatedSellers as $relatedShop)
          <div class="col-sm-6 col-lg-4 col-xl-2">
            <a href="{{ route('seller.shop', $relatedShop->slug) }}" class="text-decoration-none">
              <div class="iconbox home4_style text-center">
                <div class="icon mb3">
                  <img src="{{ asset('public/storage/'.$relatedShop->logo) }}" alt="{{ $relatedShop->name }}" style="max-width: 120px; height: auto;">
                </div>
                <div class="details">
                  <h5 class="title">{{ $relatedShop->name }}</h5>
                  <p class="para text-muted small">{{ $relatedShop->products_count }} products</p>
                </div>
              </div>
            </a>
          </div>
          @endforeach
        </div>
      </div>
    </section>
    @endif
    
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
  .seller-logo {
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  }
  
  .seller-info h1 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #000;
  }
  
  .seller-meta {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
  }
  
  .meta-item {
    font-size: 1rem;
    color: #666;
  }
  
  .meta-item i {
    margin-right: 8px;
    color: #007bff;
  }
  
  @media (max-width: 768px) {
    .seller-info h1 {
      font-size: 1.8rem;
    }
    
    .seller-meta {
      gap: 15px;
    }
  }
</style>
@endpush

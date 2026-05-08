@extends('layouts.app')

@section('title', 'About GenesisHub')

@section('content')
@section('meta_description', 'Learn about GenesisHub — the e-commerce marketplace built exclusively for Obafemi Awolowo University (OAU) students in Ile-Ife, Nigeria.')
<div class="wrapper ovh bgc-gmart-gray">
    @php
   $categoriesWithSubs = App\Models\Category::select('id', 'name', 'slug', 'image')
                    ->with(['subcategories' => fn($q) => 
                        $q->select('id', 'category_id', 'name', 'slug')
                          ->orderBy('sort_order')
                          ->limit(10)
                    ])
                    ->limit(10)
                    ->get();
  @endphp
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])
  
  <div class="body_content_wrapper position-relative">
    
    <!-- Breadcrumb -->
    <section class="breadcumb-section pt30 pb30">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="breadcumb-style1">
              <div class="breadcumb-list">
                <a href="{{ route('home') }}">Home</a>
                <a href="#">About Us</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- About Hero -->
    <section class="our-about bgc-white pt60 pb60">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="main-title mb40">
              <h1>Welcome to GenesisHub — Nigeria's Student E-Commerce Marketplace</h1>
              <p class="lead">Nigeria's Premier E-Commerce Marketplace</p>
            </div>
            <p class="mb20">GenesisHub is your trusted online shopping destination, connecting thousands of quality sellers with millions of satisfied customers across Nigeria and beyond.</p>
            <p class="mb20">Founded in 2026, we've grown from a small startup to become one of the most trusted names in African e-commerce. Our mission is simple: to make online shopping accessible, reliable, and enjoyable for everyone.</p>
            <p class="mb30">Whether you're looking for electronics, fashion, home goods, or anything in between, GenesisHub brings you the best products at competitive prices, backed by our commitment to customer satisfaction.</p>
            <a href="{{ route('product.index') }}" class="btn btn-thm">Start Shopping</a>
          </div>
          <div class="col-lg-6">
            <img src="{{ asset('public/image/auth-logo.png') }}" alt="About GenesisHub" class="img-fluid rounded">
          </div>
        </div>
      </div>
    </section>
    
    <!-- Our Values -->
    <section class="our-values pb60">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-title text-center mb50">
              <h3>Our Core Values</h3>
              <p class="text-muted">The principles that guide everything we do</p>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 col-lg-3">
            <div class="iconbox text-center mb40">
              <span class="icon flaticon-trust mb20" style="font-size: 60px; color: #714e32;"></span>
              <h5 class="mb15">Trust</h5>
              <p>We build trust through transparency, authenticity, and reliability in every transaction.</p>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-3">
            <div class="iconbox text-center mb40">
              <span class="icon flaticon-customer-service mb20" style="font-size: 60px; color: #714e32;"></span>
              <h5 class="mb15">Customer First</h5>
              <p>Your satisfaction is our priority. We're here to make your shopping experience exceptional.</p>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-3">
            <div class="iconbox text-center mb40">
              <span class="icon flaticon-innovation mb20" style="font-size: 60px; color: #714e32;"></span>
              <h5 class="mb15">Innovation</h5>
              <p>We continuously improve our platform with cutting-edge technology and features.</p>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-3">
            <div class="iconbox text-center mb40">
              <span class="icon flaticon-quality mb20" style="font-size: 60px; color: #714e32;"></span>
              <h5 class="mb15">Quality</h5>
              <p>We ensure every product meets our high standards before it reaches you.</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- By The Numbers -->
    <section class="our-stats bgc-white pt60 pb60">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-title text-center mb50">
              <h3>GenesisHub By The Numbers</h3>
            </div>
          </div>
        </div>
        
        <div class="row text-center">
          <div class="col-6 col-md-3">
            <div class="counter-box mb40">
              <h2 class="counter mb10" style="color: #714e32;">5M+</h2>
              <p class="text-muted">Happy Customers</p>
            </div>
          </div>
          
          <div class="col-6 col-md-3">
            <div class="counter-box mb40">
              <h2 class="counter mb10" style="color: #714e32;">50K+</h2>
              <p class="text-muted">Trusted Sellers</p>
            </div>
          </div>
          
          <div class="col-6 col-md-3">
            <div class="counter-box mb40">
              <h2 class="counter mb10" style="color: #714e32;">2M+</h2>
              <p class="text-muted">Products Available</p>
            </div>
          </div>
          
          <div class="col-6 col-md-3">
            <div class="counter-box mb40">
              <h2 class="counter mb10" style="color: #714e32;">10M+</h2>
              <p class="text-muted">Orders Delivered</p>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Our Story -->
    <section class="our-story pb60">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h3>Our Story</h3>
            </div>
            
            <div class="timeline">
              <div class="timeline-item mb40">
                <div class="row align-items-center">
                  <div class="col-md-3">
                    <h4 style="color: #714e32;">{{ date('Y') - 3 }}</h4>
                  </div>
                  <div class="col-md-9">
                    <h5 class="mb10">The Beginning</h5>
                    <p>GenesisHub was founded with a vision to revolutionize online shopping in Nigeria. We started with just 100 products and a handful of dedicated sellers.</p>
                  </div>
                </div>
              </div>
              
              <div class="timeline-item mb40">
                <div class="row align-items-center">
                  <div class="col-md-3">
                    <h4 style="color: #714e32;">{{ date('Y') - 2 }}</h4>
                  </div>
                  <div class="col-md-9">
                    <h5 class="mb10">Rapid Growth</h5>
                    <p>We expanded to 10,000+ products and launched our mobile app, making shopping even more convenient for our customers.</p>
                  </div>
                </div>
              </div>
              
              <div class="timeline-item mb40">
                <div class="row align-items-center">
                  <div class="col-md-3">
                    <h4 style="color: #714e32;">{{ date('Y') - 1 }}</h4>
                  </div>
                  <div class="col-md-9">
                    <h5 class="mb10">Going Nationwide</h5>
                    <p>We extended our delivery network to cover all 36 states in Nigeria, bringing our services to millions more customers.</p>
                  </div>
                </div>
              </div>
              
              <div class="timeline-item mb40">
                <div class="row align-items-center">
                  <div class="col-md-3">
                    <h4 style="color: #714e32;">{{ date('Y') }}</h4>
                  </div>
                  <div class="col-md-9">
                    <h5 class="mb10">Innovation & Excellence</h5>
                    <p>Today, we continue to innovate with features like same-day delivery, AI-powered recommendations, and enhanced buyer protection.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Why Choose Us -->
    <section class="why-choose-us bgc-white pt60 pb60">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-title text-center mb50">
              <h3>Why Shop With GenesisHub?</h3>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb40">
              <div class="icon me-3">
                <span class="flaticon-verified" style="font-size: 40px; color: #714e32;"></span>
              </div>
              <div>
                <h5 class="mb10">Verified Sellers</h5>
                <p>All our sellers are carefully vetted to ensure quality and reliability.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb40">
              <div class="icon me-3">
                <span class="flaticon-shield" style="font-size: 40px; color: #714e32;"></span>
              </div>
              <div>
                <h5 class="mb10">Buyer Protection</h5>
                <p>Shop with confidence knowing your purchases are protected by our buyer guarantee.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb40">
              <div class="icon me-3">
                <span class="flaticon-delivery-truck" style="font-size: 40px; color: #714e32;"></span>
              </div>
              <div>
                <h5 class="mb10">Fast Delivery</h5>
                <p>Get your orders delivered quickly with our efficient logistics network.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb40">
              <div class="icon me-3">
                <span class="flaticon-payment-security" style="font-size: 40px; color: #714e32;"></span>
              </div>
              <div>
                <h5 class="mb10">Secure Payments</h5>
                <p>Your payment information is always secure with our encrypted checkout process.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb40">
              <div class="icon me-3">
                <span class="flaticon-24-hours" style="font-size: 40px; color: #714e32;"></span>
              </div>
              <div>
                <h5 class="mb10">24/7 Support</h5>
                <p>Our customer service team is always available to help with any questions.</p>
              </div>
            </div>
          </div>
          
          <div class="col-md-6 col-lg-4">
            <div class="d-flex mb40">
              <div class="icon me-3">
                <span class="flaticon-return" style="font-size: 40px; color: #714e32;"></span>
              </div>
              <div>
                <h5 class="mb10">Easy Returns</h5>
                <p>Not satisfied? Return items within 30 days for a full refund.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Join Us -->
    <section class="join-us pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-10 offset-lg-1">
            <div class="text-center p-5" style="background: linear-gradient(135deg, #714e32 0%, #8b6239 100%); border-radius: 12px; color: white;">
              <h3 class="mb20" style="color: white;">Join the GenesisHub Community</h3>
              <p class="mb30" style="color: white;">Whether you're a shopper looking for great deals or a seller wanting to grow your business, GenesisHub is the place for you.</p>
              <div class="d-flex gap-3 justify-content-center flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-white">Start Shopping</a>
                <a href="{{ route('become-vendor') }}" class="btn btn-outline-white">Become a Seller</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    @include('partials.footer')
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>

@push('scripts')
<style>
.btn-outline-white {
  color: white;
  border: 2px solid white;
  background: transparent;
}
.btn-outline-white:hover {
  background: white;
  color: #714e32;
}
.btn-white {
  background: white;
  color: #714e32;
}
.btn-white:hover {
  background: #f8f9fa;
  color: #714e32;
}
</style>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Store Locator')

@section('content')
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
                <a href="#">Store Locator</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Store Locator -->
    <section class="our-contact bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h2>Find a GenesisHub Store</h2>
              <p class="text-muted">Visit our retail locations for in-person shopping and support</p>
            </div>
            
            <!-- Search Store -->
            <div class="store_search mb50">
              <form class="row g-3">
                <div class="col-md-8">
                  <input type="text" class="form-control" placeholder="Enter city or postal code">
                </div>
                <div class="col-md-4">
                  <button type="submit" class="btn btn-thm w-100">Search</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        
        <!-- Store Listings -->
        <div class="row">
          <div class="col-lg-10 offset-lg-1">
            
            <!-- Lagos Stores -->
            <div class="store_section mb50">
              <h3 class="mb30">Lagos</h3>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub Victoria Island</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>123 Admiralty Way, Lekki Phase 1, Lagos</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 801 234 5678</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 9:00 AM - 8:00 PM, Sun: 11:00 AM - 6:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub Ikeja City Mall</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>176 Obafemi Awolowo Way, Ikeja, Lagos</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 802 345 6789</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 10:00 AM - 9:00 PM, Sun: 12:00 PM - 7:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub Surulere</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>42 Adeniran Ogunsanya Street, Surulere, Lagos</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 803 456 7890</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 9:00 AM - 8:00 PM, Sun: 11:00 AM - 6:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub Festac</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>21st Avenue, Festac Town, Lagos</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 804 567 8901</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 9:00 AM - 7:00 PM, Sun: 12:00 PM - 5:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Abuja Stores -->
            <div class="store_section mb50">
              <h3 class="mb30">Abuja</h3>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub Wuse 2</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>Plot 1234 Adetokunbo Ademola Crescent, Wuse 2, Abuja</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 805 678 9012</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 9:00 AM - 8:00 PM, Sun: 11:00 AM - 6:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub Jabi Lake Mall</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>Jabi Lake Mall, Jabi, Abuja</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 806 789 0123</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 10:00 AM - 9:00 PM, Sun: 12:00 PM - 7:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Port Harcourt Stores -->
            <div class="store_section mb50">
              <h3 class="mb30">Port Harcourt</h3>
              
              <div class="row">
                <div class="col-md-6">
                  <div class="card mb30">
                    <div class="card-body">
                      <h5 class="card-title mb15">GenesisHub GRA</h5>
                      <p class="mb10"><i class="flaticon-location me-2"></i>234 Aba Road, GRA, Port Harcourt</p>
                      <p class="mb10"><i class="flaticon-phone-call me-2"></i>+234 807 890 1234</p>
                      <p class="mb20"><i class="flaticon-clock me-2"></i>Mon-Sat: 9:00 AM - 7:00 PM, Sun: 11:00 AM - 5:00 PM</p>
                      <div class="d-flex gap-2">
                        <a href="#" class="btn btn-sm btn-outline-thm">Get Directions</a>
                        <a href="#" class="btn btn-sm btn-thm">Call Store</a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
        
        <!-- Store Services -->
        <div class="row mt50">
          <div class="col-lg-10 offset-lg-1">
            <div class="main-title text-center mb40">
              <h3>What You'll Find In-Store</h3>
            </div>
            
            <div class="row">
              <div class="col-md-4">
                <div class="iconbox text-center mb30">
                  <span class="icon flaticon-box mb20" style="font-size: 50px; color: #714e32;"></span>
                  <h5 class="mb15">Click & Collect</h5>
                  <p>Order online and pick up in-store for free</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="iconbox text-center mb30">
                  <span class="icon flaticon-customer-service mb20" style="font-size: 50px; color: #714e32;"></span>
                  <h5 class="mb15">Expert Support</h5>
                  <p>Get help from our trained specialists</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="iconbox text-center mb30">
                  <span class="icon flaticon-refund mb20" style="font-size: 50px; color: #714e32;"></span>
                  <h5 class="mb15">Easy Returns</h5>
                  <p>Return online purchases at any location</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="iconbox text-center mb30">
                  <span class="icon flaticon-product mb20" style="font-size: 50px; color: #714e32;"></span>
                  <h5 class="mb15">Product Demos</h5>
                  <p>Try before you buy with live demonstrations</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="iconbox text-center mb30">
                  <span class="icon flaticon-settings mb20" style="font-size: 50px; color: #714e32;"></span>
                  <h5 class="mb15">Setup Services</h5>
                  <p>Get help setting up your new devices</p>
                </div>
              </div>
              
              <div class="col-md-4">
                <div class="iconbox text-center mb30">
                  <span class="icon flaticon-workshop mb20" style="font-size: 50px; color: #714e32;"></span>
                  <h5 class="mb15">Workshops</h5>
                  <p>Free workshops and training sessions</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Pickup Locations -->
        <div class="row mt50">
          <div class="col-lg-10 offset-lg-1">
            <div class="card p-4" style="background: #f8f9fa;">
              <h4 class="mb20">Pickup Partner Locations</h4>
              <p class="mb20">Can't make it to a GenesisHub store? We also offer pickup at hundreds of partner locations nationwide including:</p>
              <div class="row">
                <div class="col-md-4">
                  <ul class="mb0">
                    <li>Select Shopping Malls</li>
                    <li>Partner Retail Stores</li>
                  </ul>
                </div>
                <div class="col-md-4">
                  <ul class="mb0">
                    <li>Courier Service Points</li>
                    <li>Gas Stations</li>
                  </ul>
                </div>
                <div class="col-md-4">
                  <ul class="mb0">
                    <li>Supermarkets</li>
                    <li>Pharmacies</li>
                  </ul>
                </div>
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
@endsection
@extends('layouts.app')

@section('title', 'Contact Us')

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
                <a href="#">Contact</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Contact Info -->
    <section class="our-contact bgc-white pt60 pb40">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb50">
              <h1>Contact GenesisHub — OAU Student Marketplace Support</h1>
              <p class="text-muted">We're here to help and answer any question you might have</p>
            </div>
          </div>
        </div>
        
        <div class="row mb50">
          <div class="col-md-4">
            <div class="iconbox text-center mb30">
              <span class="icon flaticon-phone-call mb20" style="font-size: 50px; color: #714e32;"></span>
              <h5 class="mb15">Call Us</h5>
              <p class="mb10">Mon-Fri: 8:00 AM - 9:00 PM</p>
              <p class="mb10">Sat-Sun: 10:00 AM - 6:00 PM</p>
              <a href="tel:+11234567890" class="text-thm fw500">+(1) 123 456 7890</a>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="iconbox text-center mb30">
              <span class="icon flaticon-email mb20" style="font-size: 50px; color: #714e32;"></span>
              <h5 class="mb15">Email Us</h5>
              <p class="mb10">General Inquiries</p>
<!-- REPLACE email to match .ng domain -->
<a href="mailto:support@genesishub.ng" class="text-thm fw500 mb10 d-block">support@genesishub.ng</a>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="iconbox text-center mb30">
              <span class="icon flaticon-location mb20" style="font-size: 50px; color: #714e32;"></span>
              <h5 class="mb15">Visit Us</h5>
             <p class="mb0">Obafemi Awolowo University Campus Area,</p>
<p class="mb0">Ile-Ife,</p>
<p>Osun State, Nigeria 220005</p>


            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Contact Form -->
    <section class="our-contact-form bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="card">
              <div class="card-body p-4 p-md-5">
                <h3 class="mb30">Send Us a Message</h3>
                
                <form id="contact-form" method="POST" action="{{ route('contact') }}">
                  @csrf
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group mb25">
                        <label class="form-label">Your Name *</label>
                        <input type="text" name="name" class="form-control" placeholder="John Doe" required>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group mb25">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" placeholder="john@example.com" required>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group mb25">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" placeholder="+234 800 000 0000">
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="form-group mb25">
                        <label class="form-label">Subject *</label>
                        <select name="subject" class="form-control selectpicker" required>
                          <option value="">Select a subject</option>
                          <option value="general">General Inquiry</option>
                          <option value="order">Order Support</option>
                          <option value="technical">Technical Issue</option>
                          <option value="seller">Seller Support</option>
                          <option value="partnership">Partnership Opportunity</option>
                          <option value="feedback">Feedback</option>
                          <option value="other">Other</option>
                        </select>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <div class="form-group mb25">
                        <label class="form-label">Order Number (if applicable)</label>
                        <input type="text" name="order_number" class="form-control" placeholder="GH-12345">
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <div class="form-group mb25">
                        <label class="form-label">Your Message *</label>
                        <textarea name="message" class="form-control" rows="6" placeholder="Tell us how we can help you..." required></textarea>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <button type="submit" class="btn btn-thm">Send Message</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- FAQ Quick Links -->
    <section class="quick-help pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-title text-center mb40">
              <h3>Looking for Quick Help?</h3>
              <p class="text-muted">You might find your answer in our help resources</p>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-4">
            <div class="card text-center mb30">
              <div class="card-body p-4">
                <span class="icon flaticon-faq mb20" style="font-size: 40px; color: #714e32;"></span>
                <h5 class="mb15">Help Centre</h5>
                <p class="mb20">Browse our comprehensive help articles and guides</p>
                <a href="{{ route('help') }}" class="btn btn-sm btn-outline-thm">Visit Help Centre</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card text-center mb30">
              <div class="card-body p-4">
                <span class="icon flaticon-pin mb20" style="font-size: 40px; color: #714e32;"></span>
                <h5 class="mb15">Track Your Order</h5>
                <p class="mb20">Check the status of your order in real-time</p>
                <a href="{{ route('track.index') }}" class="btn btn-sm btn-outline-thm">Track Order</a>
              </div>
            </div>
          </div>
          
          <div class="col-md-4">
            <div class="card text-center mb30">
              <div class="card-body p-4">
                <span class="icon flaticon-refund mb20" style="font-size: 40px; color: #714e32;"></span>
                <h5 class="mb15">Returns & Refunds</h5>
                <p class="mb20">Learn about our return policy and process</p>
                <a href="{{ route('returns') }}" class="btn btn-sm btn-outline-thm">Return Policy</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Social Media -->
    <section class="social-connect bgc-white pt60 pb60">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="text-center">
              <h4 class="mb30">Connect With Us</h4>
              <p class="mb30">Follow us on social media for updates, offers, and more</p>
              <div class="social_icon_list">
                <ul class="mb0 justify-content-center">
                  <li class="list-inline-item">
                    <a href="#" target="_blank" class="social-icon">
                      <i class="fab fa-facebook fa-2x"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" target="_blank" class="social-icon">
                      <i class="fab fa-x-twitter fa-2x"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" target="_blank" class="social-icon">
                      <i class="fab fa-instagram fa-2x"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" target="_blank" class="social-icon">
                      <i class="fab fa-linkedin-in fa-2x"></i>
                    </a>
                  </li>
                  <li class="list-inline-item">
                    <a href="#" target="_blank" class="social-icon">
                      <i class="fab fa-youtube fa-2x"></i>
                    </a>
                  </li>
                </ul>
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
.social-icon {
  color: #714e32;
  transition: all 0.3s;
  margin: 0 10px;
}
.social-icon:hover {
  color: #8b6239;
  transform: translateY(-3px);
}
</style>

<script>
$(document).ready(function() {
  $('#contact-form').on('submit', function(e) {
    e.preventDefault();
    
    // Here you would typically send the form via AJAX
    // For now, we'll just show a success message
    
    alert('Thank you for contacting us! We will get back to you within 24 hours.');
    this.reset();
  });
});
</script>
@endpush
@endsection
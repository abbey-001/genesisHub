@extends('layouts.app')

@section('title', 'Help Centre')

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
                <a href="#">Help Centre</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Help Centre Hero -->
    <section class="our-faq bgc-white pt40 pb40">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h2>How can we help you?</h2>
              <p class="text-muted">Find answers to your questions or get in touch with our support team</p>
            </div>
            
            <!-- Search Box -->
            <div class="header_middle_advnc_search mb50">
              <form action="#" class="form-search">
                <div class="box-search">
                  <input class="form-control" type="text" placeholder="Search for help..." aria-label="Search">
                  <button type="submit" class="btn btn-thm"><i class="flaticon-search"></i></button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Quick Links -->
    <section class="our-faq bgc-white pb40">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-title text-center mb40">
              <h3>Browse by topic</h3>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-6 col-lg-4">
            <div class="iconbox home4_style text-center mb30">
              <span class="icon flaticon-checked-box mb20" style="font-size: 50px; color: #714e32;"></span>
              <div class="details">
                <h5 class="title mb15">Order & Shipping</h5>
                <p class="mb10">Track orders, shipping info</p>
                <a href="#orders" class="text-thm">Learn more →</a>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="iconbox home4_style text-center mb30">
              <span class="icon flaticon-refund mb20" style="font-size: 50px; color: #714e32;"></span>
              <div class="details">
                <h5 class="title mb15">Returns & Refunds</h5>
                <p class="mb10">Return policy, refund status</p>
                <a href="#returns" class="text-thm">Learn more →</a>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="iconbox home4_style text-center mb30">
              <span class="icon flaticon-credit-card mb20" style="font-size: 50px; color: #714e32;"></span>
              <div class="details">
                <h5 class="title mb15">Payment</h5>
                <p class="mb10">Payment methods, security</p>
                <a href="#payment" class="text-thm">Learn more →</a>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="iconbox home4_style text-center mb30">
              <span class="icon flaticon-profile mb20" style="font-size: 50px; color: #714e32;"></span>
              <div class="details">
                <h5 class="title mb15">Account</h5>
                <p class="mb10">Login, profile, security</p>
                <a href="#account" class="text-thm">Learn more →</a>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="iconbox home4_style text-center mb30">
              <span class="icon flaticon-store mb20" style="font-size: 50px; color: #714e32;"></span>
              <div class="details">
                <h5 class="title mb15">Selling</h5>
                <p class="mb10">Become a seller, policies</p>
                <a href="#selling" class="text-thm">Learn more →</a>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-lg-4">
            <div class="iconbox home4_style text-center mb30">
              <span class="icon flaticon-protection mb20" style="font-size: 50px; color: #714e32;"></span>
              <div class="details">
                <h5 class="title mb15">Safety & Privacy</h5>
                <p class="mb10">Security, data protection</p>
                <a href="#safety" class="text-thm">Learn more →</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- FAQ Section -->
    <section class="our-faq bgc-white pb90" id="orders">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            
            <!-- Orders & Shipping -->
            <div class="faq_content mb60">
              <h3 class="mb30">Orders & Shipping</h3>
              <div class="accordion" id="accordionOrders">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#order1">
                      How do I track my order?
                    </button>
                  </h2>
                  <div id="order1" class="accordion-collapse collapse show" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      You can track your order by logging into your account and visiting the Orders section. Each order will have a tracking number that you can use to see real-time updates. You'll also receive email notifications at each stage of delivery.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order2">
                      What are the shipping costs?
                    </button>
                  </h2>
                  <div id="order2" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      Shipping costs vary depending on the seller, item size, weight, and destination. You'll see the exact shipping cost at checkout before completing your purchase. Many sellers offer free shipping on orders over ₦10,000.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order3">
                      How long does delivery take?
                    </button>
                  </h2>
                  <div id="order3" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      Delivery times depend on the seller and your location. Standard delivery typically takes 3-7 business days within major cities and 5-14 days for remote areas. Express shipping options may be available for faster delivery.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#order4">
                      Can I change my delivery address after ordering?
                    </button>
                  </h2>
                  <div id="order4" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      You can change your delivery address before the order ships. Go to your order details and click "Edit Address". Once shipped, contact customer support immediately - we may be able to redirect the package depending on the courier.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Returns & Refunds -->
            <div class="faq_content mb60" id="returns">
              <h3 class="mb30">Returns & Refunds</h3>
              <div class="accordion" id="accordionReturns">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#return1">
                      What is your return policy?
                    </button>
                  </h2>
                  <div id="return1" class="accordion-collapse collapse show" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      Most items can be returned within 30 days of delivery for a full refund. Items must be unused and in original packaging. Some categories like electronics have a 14-day return window. See our <a href="{{ route('returns') }}">Returns Policy</a> for complete details.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#return2">
                      How do I return an item?
                    </button>
                  </h2>
                  <div id="return2" class="accordion-collapse collapse" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      Log in to your account, go to Order History, select the item you want to return, and click "Request Return". Choose whether you want free pickup or drop-off at a partner location. You'll receive a return label via email.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#return3">
                      When will I get my refund?
                    </button>
                  </h2>
                  <div id="return3" class="accordion-collapse collapse" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      Refunds are processed within 2-3 business days after we receive and inspect your return. The refund will appear in your original payment method within 5-10 business days. You'll receive email confirmation at each stage.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Payment -->
            <div class="faq_content mb60" id="payment">
              <h3 class="mb30">Payment</h3>
              <div class="accordion" id="accordionPayment">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#payment1">
                      What payment methods do you accept?
                    </button>
                  </h2>
                  <div id="payment1" class="accordion-collapse collapse show" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      We accept Visa, Mastercard, American Express, bank transfers, and mobile money payments. All transactions are processed securely through our payment partner Paystack.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment2">
                      Is my payment information secure?
                    </button>
                  </h2>
                  <div id="payment2" class="accordion-collapse collapse" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      Yes, we use industry-standard encryption to protect your payment information. We never store your complete card details on our servers. All payments are processed through PCI-compliant payment processors.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#payment3">
                      Why was my payment declined?
                    </button>
                  </h2>
                  <div id="payment3" class="accordion-collapse collapse" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      Payments can be declined for various reasons: insufficient funds, incorrect card details, expired card, or your bank blocking the transaction. Contact your bank or try a different payment method. If problems persist, contact our support team.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Account -->
            <div class="faq_content mb60" id="account">
              <h3 class="mb30">Account</h3>
              <div class="accordion" id="accordionAccount">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#account1">
                      How do I create an account?
                    </button>
                  </h2>
                  <div id="account1" class="accordion-collapse collapse show" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      Click "Sign In" at the top of any page, then select "Create Account". Enter your email, create a password, and provide basic information. You'll receive a verification email to activate your account.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account2">
                      I forgot my password. What do I do?
                    </button>
                  </h2>
                  <div id="account2" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      Click "Sign In" and select "Forgot Password". Enter your email address and we'll send you a password reset link. Check your spam folder if you don't receive it within a few minutes.
                    </div>
                  </div>
                </div>
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#account3">
                      How do I update my account information?
                    </button>
                  </h2>
                  <div id="account3" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      Log in to your account and go to Account Settings. Here you can update your name, email, phone number, password, and saved addresses. Changes are saved automatically.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </section>
    
    <!-- Contact Support -->
    <section class="our-faq bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="text-center p-4" style="background: #f8f9fa; border-radius: 8px;">
              <h4 class="mb20">Still need help?</h4>
              <p class="mb30">Our customer support team is available 24/7 to assist you</p>
              <div class="row">
                <div class="col-md-4 mb20">
                  <div class="iconbox">
                    <span class="flaticon-email mb15" style="font-size: 30px; color: #714e32;"></span>
                    <h6>Email Us</h6>
                    <a href="mailto:support@genesishub.com">support@genesishub.com</a>
                  </div>
                </div>
                <div class="col-md-4 mb20">
                  <div class="iconbox">
                    <span class="flaticon-phone-call mb15" style="font-size: 30px; color: #714e32;"></span>
                    <h6>Call Us</h6>
                    <a href="tel:+11234567890">+(1) 123 456 7890</a>
                  </div>
                </div>
                <div class="col-md-4 mb20">
                  <div class="iconbox">
                    <span class="flaticon-chat mb15" style="font-size: 30px; color: #714e32;"></span>
                    <h6>Live Chat</h6>
                    <a href="{{ route('contact') }}">Start chatting</a>
                  </div>
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
@extends('layouts.app')

@section('title', 'Frequently Asked Questions')

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
                <a href="#">FAQ</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- FAQ Hero -->
    <section class="our-faq bgc-white pt40 pb40">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h2>Frequently Asked Questions</h2>
              <p class="text-muted">Find quick answers to common questions</p>
            </div>
            
            <!-- Search Box -->
            <div class="header_middle_advnc_search mb50">
              <form action="#" class="form-search">
                <div class="box-search">
                  <input class="form-control" type="text" placeholder="Search FAQs..." aria-label="Search" id="faq-search">
                  <button type="button" class="btn btn-thm"><i class="flaticon-search"></i></button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- FAQ Categories -->
    <section class="faq-categories bgc-white pb40">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="text-center mb40">
              <h4>Browse by Category</h4>
            </div>
          </div>
        </div>
        
        <div class="row">
          <div class="col-md-4 col-lg-2">
            <a href="#general" class="category-card text-center d-block mb30 p-3" style="background: #f8f9fa; border-radius: 8px; text-decoration: none;">
              <span class="flaticon-info" style="font-size: 30px; color: #714e32;"></span>
              <p class="mb0 mt10" style="color: #333;">General</p>
            </a>
          </div>
          
          <div class="col-md-4 col-lg-2">
            <a href="#orders" class="category-card text-center d-block mb30 p-3" style="background: #f8f9fa; border-radius: 8px; text-decoration: none;">
              <span class="flaticon-checked-box" style="font-size: 30px; color: #714e32;"></span>
              <p class="mb0 mt10" style="color: #333;">Orders</p>
            </a>
          </div>
          
          <div class="col-md-4 col-lg-2">
            <a href="#shipping" class="category-card text-center d-block mb30 p-3" style="background: #f8f9fa; border-radius: 8px; text-decoration: none;">
              <span class="flaticon-delivery-truck" style="font-size: 30px; color: #714e32;"></span>
              <p class="mb0 mt10" style="color: #333;">Shipping</p>
            </a>
          </div>
          
          <div class="col-md-4 col-lg-2">
            <a href="#returns" class="category-card text-center d-block mb30 p-3" style="background: #f8f9fa; border-radius: 8px; text-decoration: none;">
              <span class="flaticon-refund" style="font-size: 30px; color: #714e32;"></span>
              <p class="mb0 mt10" style="color: #333;">Returns</p>
            </a>
          </div>
          
          <div class="col-md-4 col-lg-2">
            <a href="#payment" class="category-card text-center d-block mb30 p-3" style="background: #f8f9fa; border-radius: 8px; text-decoration: none;">
              <span class="flaticon-credit-card" style="font-size: 30px; color: #714e32;"></span>
              <p class="mb0 mt10" style="color: #333;">Payment</p>
            </a>
          </div>
          
          <div class="col-md-4 col-lg-2">
            <a href="#account" class="category-card text-center d-block mb30 p-3" style="background: #f8f9fa; border-radius: 8px; text-decoration: none;">
              <span class="flaticon-profile" style="font-size: 30px; color: #714e32;"></span>
              <p class="mb0 mt10" style="color: #333;">Account</p>
            </a>
          </div>
        </div>
      </div>
    </section>
    
    <!-- FAQ Content -->
    <section class="our-faq bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            
            <!-- General -->
            <div class="faq-section mb50" id="general">
              <h3 class="mb30">General Questions</h3>
              <div class="accordion" id="accordionGeneral">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#gen1">
                      What is GenesisHub?
                    </button>
                  </h2>
                  <div id="gen1" class="accordion-collapse collapse show" data-bs-parent="#accordionGeneral">
                    <div class="accordion-body">
                      GenesisHub is Nigeria's leading online marketplace connecting buyers with verified sellers across the country. We offer millions of products across categories like electronics, fashion, home goods, and more.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gen2">
                      Is GenesisHub available nationwide?
                    </button>
                  </h2>
                  <div id="gen2" class="accordion-collapse collapse" data-bs-parent="#accordionGeneral">
                    <div class="accordion-body">
                      Yes! We deliver to all 36 states in Nigeria. Delivery times may vary based on your location.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#gen3">
                      How do I know products are genuine?
                    </button>
                  </h2>
                  <div id="gen3" class="accordion-collapse collapse" data-bs-parent="#accordionGeneral">
                    <div class="accordion-body">
                      All our sellers are verified and must meet strict quality standards. We also have a buyer protection program that covers you if you receive counterfeit or significantly misrepresented items.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Orders -->
            <div class="faq-section mb50" id="orders">
              <h3 class="mb30">Orders & Checkout</h3>
              <div class="accordion" id="accordionOrders">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ord1">
                      How do I place an order?
                    </button>
                  </h2>
                  <div id="ord1" class="accordion-collapse collapse show" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      Browse products, add items to your cart, proceed to checkout, enter your shipping information, and complete payment. You'll receive an order confirmation email immediately.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ord2">
                      Can I cancel my order?
                    </button>
                  </h2>
                  <div id="ord2" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      Yes, you can cancel orders before they ship. Go to your order history and click "Cancel Order". Once shipped, you'll need to return the item after delivery.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ord3">
                      Can I modify my order after placing it?
                    </button>
                  </h2>
                  <div id="ord3" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      You can change the delivery address before the order ships. For other modifications, you'll need to cancel and place a new order.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ord4">
                      Will I receive a confirmation?
                    </button>
                  </h2>
                  <div id="ord4" class="accordion-collapse collapse" data-bs-parent="#accordionOrders">
                    <div class="accordion-body">
                      Yes, you'll receive order confirmation via email and SMS. You'll also get updates when your order is processed, shipped, out for delivery, and delivered.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Shipping -->
            <div class="faq-section mb50" id="shipping">
              <h3 class="mb30">Shipping & Delivery</h3>
              <div class="accordion" id="accordionShipping">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ship1">
                      How long does delivery take?
                    </button>
                  </h2>
                  <div id="ship1" class="accordion-collapse collapse show" data-bs-parent="#accordionShipping">
                    <div class="accordion-body">
                      Standard delivery takes 3-7 business days in major cities and 5-14 days in remote areas. Express delivery (1-3 days) is available for select products.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ship2">
                      How much is shipping?
                    </button>
                  </h2>
                  <div id="ship2" class="accordion-collapse collapse" data-bs-parent="#accordionShipping">
                    <div class="accordion-body">
                      Shipping costs vary by seller, item weight, and destination. Many sellers offer free shipping on orders over ₦10,000. You'll see exact costs at checkout.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ship3">
                      Can I track my order?
                    </button>
                  </h2>
                  <div id="ship3" class="accordion-collapse collapse" data-bs-parent="#accordionShipping">
                    <div class="accordion-body">
                      Yes! You can track your order in real-time through your account or using the tracking number sent via email. Visit our <a href="{{ route('track.index') }}">Order Tracking</a> page.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ship4">
                      What if I'm not home for delivery?
                    </button>
                  </h2>
                  <div id="ship4" class="accordion-collapse collapse" data-bs-parent="#accordionShipping">
                    <div class="accordion-body">
                      Our courier will attempt delivery 2-3 times. If unsuccessful, the package will be held at a nearby pickup location for 7 days. You'll be notified via SMS and email.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Returns -->
            <div class="faq-section mb50" id="returns">
              <h3 class="mb30">Returns & Refunds</h3>
              <div class="accordion" id="accordionReturns">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ret1">
                      What is your return policy?
                    </button>
                  </h2>
                  <div id="ret1" class="accordion-collapse collapse show" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      Most items can be returned within 30 days for a full refund. Items must be unused and in original packaging. See our full <a href="{{ route('returns') }}">Returns Policy</a>.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ret2">
                      How do I return an item?
                    </button>
                  </h2>
                  <div id="ret2" class="accordion-collapse collapse" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      Log in to your account, go to Order History, select the item, click "Request Return", and choose pickup or drop-off. We'll email you a return label.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ret3">
                      When will I get my refund?
                    </button>
                  </h2>
                  <div id="ret3" class="accordion-collapse collapse" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      Refunds are processed 2-3 days after we receive and inspect your return. The money will appear in your original payment method within 5-10 business days.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ret4">
                      Who pays for return shipping?
                    </button>
                  </h2>
                  <div id="ret4" class="accordion-collapse collapse" data-bs-parent="#accordionReturns">
                    <div class="accordion-body">
                      If the return is due to our error or a defective product, we provide free return pickup. For other returns, return shipping may be deducted from your refund.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Payment -->
            <div class="faq-section mb50" id="payment">
              <h3 class="mb30">Payment & Security</h3>
              <div class="accordion" id="accordionPayment">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#pay1">
                      What payment methods do you accept?
                    </button>
                  </h2>
                  <div id="pay1" class="accordion-collapse collapse show" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      We accept all major credit/debit cards (Visa, Mastercard, Verve), bank transfers, and mobile money (e.g., M-Pesa, Airtel Money). All payments are processed securely through Paystack.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pay2">
                      Is it safe to use my card?
                    </button>
                  </h2>
                  <div id="pay2" class="accordion-collapse collapse" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      Yes, absolutely. We use bank-level encryption and don't store your complete card details. All transactions are processed through secure, PCI-compliant payment gateways.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pay3">
                      Can I pay on delivery?
                    </button>
                  </h2>
                  <div id="pay3" class="accordion-collapse collapse" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      Cash on delivery may be available for select products and locations. This option will be shown at checkout if available for your order.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pay4">
                      Why was my payment declined?
                    </button>
                  </h2>
                  <div id="pay4" class="accordion-collapse collapse" data-bs-parent="#accordionPayment">
                    <div class="accordion-body">
                      Common reasons include insufficient funds, incorrect card details, expired card, or your bank blocking the transaction. Try another payment method or contact your bank.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Account -->
            <div class="faq-section mb50" id="account">
              <h3 class="mb30">Account & Profile</h3>
              <div class="accordion" id="accordionAccount">
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#acc1">
                      Do I need an account to shop?
                    </button>
                  </h2>
                  <div id="acc1" class="accordion-collapse collapse show" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      While you can browse without an account, you'll need to create one to make purchases, track orders, and access other features.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc2">
                      How do I create an account?
                    </button>
                  </h2>
                  <div id="acc2" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      Click "Sign In" at the top of any page, then "Create Account". Enter your email and create a password. You'll receive a verification email to activate your account.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc3">
                      I forgot my password
                    </button>
                  </h2>
                  <div id="acc3" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      Click "Sign In" and select "Forgot Password". Enter your email and we'll send you a reset link. Check your spam folder if you don't see it.
                    </div>
                  </div>
                </div>
                
                <div class="accordion-item">
                  <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#acc4">
                      Can I delete my account?
                    </button>
                  </h2>
                  <div id="acc4" class="accordion-collapse collapse" data-bs-parent="#accordionAccount">
                    <div class="accordion-body">
                      Yes. Contact our support team at support@genesishub.com to request account deletion. Note that this is permanent and cannot be undone.
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
        
        <!-- Still Have Questions -->
        <div class="row mt50">
          <div class="col-lg-8 offset-lg-2">
            <div class="text-center p-5" style="background: #f8f9fa; border-radius: 12px;">
              <h4 class="mb20">Still have questions?</h4>
              <p class="mb30">Can't find the answer you're looking for? Our customer support team is here to help.</p>
              <a href="{{ route('contact') }}" class="btn btn-thm">Contact Support</a>
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
<script>
$(document).ready(function() {
  // FAQ Search
  $('#faq-search').on('keyup', function() {
    const searchTerm = $(this).val().toLowerCase();
    
    $('.accordion-item').each(function() {
      const question = $(this).find('.accordion-button').text().toLowerCase();
      const answer = $(this).find('.accordion-body').text().toLowerCase();
      
      if (question.includes(searchTerm) || answer.includes(searchTerm)) {
        $(this).show();
      } else {
        $(this).hide();
      }
    });
    
    // Show/hide section headers
    $('.faq-section').each(function() {
      if ($(this).find('.accordion-item:visible').length === 0) {
        $(this).hide();
      } else {
        $(this).show();
      }
    });
  });
  
  // Smooth scroll to sections
  $('a[href^="#"]').on('click', function(e) {
    const target = $(this.getAttribute('href'));
    if (target.length) {
      e.preventDefault();
      $('html, body').stop().animate({
        scrollTop: target.offset().top - 100
      }, 1000);
    }
  });
});
</script>
@endpush
@endsection
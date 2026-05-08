@extends('layouts.app')

@section('title', 'Terms and Conditions')

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
                <a href="#">Terms and Conditions</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Terms Content -->
    <section class="our-terms bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h2>Terms and Conditions</h2>
              <p class="text-muted">Last updated: {{ date('F d, Y') }}</p>
            </div>
            
            <div class="terms_condition_grid">
              
              <div class="grids mb40">
                <h4 class="mb20">1. Introduction</h4>
                <p class="mb15">Welcome to GenesisHub! These terms and conditions outline the rules and regulations for the use of GenesisHub's website and services.</p>
                <p class="mb15">By accessing this website, we assume you accept these terms and conditions. Do not continue to use GenesisHub if you do not agree to all of the terms and conditions stated on this page.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">2. Definitions</h4>
                <ul class="mb20">
                  <li><strong>"Platform"</strong> refers to the GenesisHub website and mobile applications</li>
                  <li><strong>"User"</strong> refers to anyone who accesses or uses our Platform</li>
                  <li><strong>"Buyer"</strong> refers to users who purchase products on our Platform</li>
                  <li><strong>"Seller"</strong> refers to merchants who list and sell products on our Platform</li>
                  <li><strong>"Product"</strong> refers to items listed for sale on the Platform</li>
                  <li><strong>"Order"</strong> refers to a purchase transaction made through the Platform</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">3. Account Registration</h4>
                <p class="mb15">To access certain features of our Platform, you must register for an account. When registering, you agree to:</p>
                <ul class="mb20">
                  <li>Provide accurate, current, and complete information</li>
                  <li>Maintain and promptly update your account information</li>
                  <li>Maintain the security of your password and accept all risks of unauthorized access</li>
                  <li>Immediately notify us of any unauthorized use of your account</li>
                  <li>Be responsible for all activities that occur under your account</li>
                </ul>
                <p class="mb15">You must be at least 18 years old to create an account. By creating an account, you represent that you are of legal age to form a binding contract.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">4. Buying on GenesisHub</h4>
                
                <h5 class="mb15">4.1 Product Listings</h5>
                <p class="mb15">All product listings are created by independent sellers. While we strive to ensure accuracy, GenesisHub does not guarantee that product descriptions, prices, or other content is accurate, complete, reliable, current, or error-free.</p>
                
                <h5 class="mb15">4.2 Pricing and Availability</h5>
                <p class="mb15">All prices are listed in Nigerian Naira (₦) unless otherwise stated. Prices are subject to change without notice. We reserve the right to limit quantities and discontinue products at any time.</p>
                
                <h5 class="mb15">4.3 Order Acceptance</h5>
                <p class="mb15">Your order is an offer to buy products from sellers on our Platform. When you place an order, you will receive an order confirmation email. This does not mean your order has been accepted—it's just confirmation that we received it. Sellers have the right to accept or decline your order.</p>
                
                <h5 class="mb15">4.4 Payment</h5>
                <p class="mb15">Payment must be made at the time of purchase using one of our accepted payment methods. We use secure third-party payment processors. By making a purchase, you authorize us to charge your chosen payment method.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">5. Selling on GenesisHub</h4>
                
                <h5 class="mb15">5.1 Seller Requirements</h5>
                <p class="mb15">To sell on GenesisHub, you must:</p>
                <ul class="mb20">
                  <li>Complete the seller registration process</li>
                  <li>Provide accurate business information</li>
                  <li>Comply with all applicable laws and regulations</li>
                  <li>Pay applicable seller fees and commissions</li>
                  <li>Maintain a minimum seller rating (after initial grace period)</li>
                </ul>
                
                <h5 class="mb15">5.2 Product Listings</h5>
                <p class="mb15">Sellers must ensure that:</p>
                <ul class="mb20">
                  <li>All product information is accurate and complete</li>
                  <li>Products comply with all applicable laws</li>
                  <li>They have the right to sell the products listed</li>
                  <li>Products are not counterfeit, stolen, or otherwise illegal</li>
                  <li>Listings do not infringe on intellectual property rights</li>
                </ul>
                
                <h5 class="mb15">5.3 Order Fulfillment</h5>
                <p class="mb15">Sellers are responsible for:</p>
                <ul class="mb20">
                  <li>Processing orders within the stated timeframe</li>
                  <li>Shipping products to buyers promptly</li>
                  <li>Providing tracking information when available</li>
                  <li>Handling returns and refunds according to their policies</li>
                  <li>Providing customer service to buyers</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">6. Shipping and Delivery</h4>
                <p class="mb15">Shipping times and costs vary by seller and product. Estimated delivery dates are provided at checkout but are not guaranteed. GenesisHub is not responsible for shipping delays caused by:</p>
                <ul class="mb20">
                  <li>Carrier delays</li>
                  <li>Weather conditions</li>
                  <li>Customs processing</li>
                  <li>Incorrect shipping addresses provided by buyers</li>
                  <li>Other circumstances beyond our control</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">7. Returns and Refunds</h4>
                <p class="mb15">Return and refund policies are set by individual sellers. Before making a purchase, please review the seller's return policy. GenesisHub may intervene in disputes between buyers and sellers at our discretion.</p>
                
                <h5 class="mb15">7.1 Buyer Protection</h5>
                <p class="mb15">If you receive a product that is:</p>
                <ul class="mb20">
                  <li>Significantly different from the listing description</li>
                  <li>Damaged or defective upon arrival</li>
                  <li>Not received within the estimated delivery timeframe</li>
                </ul>
                <p class="mb15">You may be eligible for a refund through our buyer protection program. Contact us within 14 days of delivery (or expected delivery date) to file a claim.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">8. Prohibited Activities</h4>
                <p class="mb15">You agree not to:</p>
                <ul class="mb20">
                  <li>Violate any laws or regulations</li>
                  <li>Infringe on intellectual property rights</li>
                  <li>Sell counterfeit, stolen, or illegal items</li>
                  <li>Engage in fraudulent activities</li>
                  <li>Manipulate reviews or ratings</li>
                  <li>Harass or abuse other users</li>
                  <li>Attempt to circumvent platform fees</li>
                  <li>Use automated systems to access the Platform</li>
                  <li>Interfere with the Platform's operation</li>
                  <li>Collect user information without consent</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">9. Intellectual Property</h4>
                <p class="mb15">The Platform and its content (excluding user-generated content) are owned by GenesisHub and protected by copyright, trademark, and other laws. You may not:</p>
                <ul class="mb20">
                  <li>Copy, modify, or distribute our content without permission</li>
                  <li>Use our trademarks without authorization</li>
                  <li>Remove copyright or trademark notices</li>
                  <li>Reverse engineer any part of the Platform</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">10. User Content</h4>
                <p class="mb15">By posting content on GenesisHub (reviews, photos, etc.), you grant us a non-exclusive, worldwide, royalty-free license to use, reproduce, modify, and distribute that content. You retain ownership of your content but agree that:</p>
                <ul class="mb20">
                  <li>Your content does not violate any laws or third-party rights</li>
                  <li>We may remove content that violates our policies</li>
                  <li>You are solely responsible for your content</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">11. Limitation of Liability</h4>
                <p class="mb15">To the fullest extent permitted by law:</p>
                <ul class="mb20">
                  <li>GenesisHub is not liable for indirect, incidental, or consequential damages</li>
                  <li>Our total liability is limited to the amount you paid for the product or service</li>
                  <li>We are not responsible for seller conduct or product quality</li>
                  <li>We do not guarantee uninterrupted or error-free service</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">12. Indemnification</h4>
                <p class="mb15">You agree to indemnify and hold GenesisHub harmless from any claims, damages, or expenses arising from:</p>
                <ul class="mb20">
                  <li>Your use of the Platform</li>
                  <li>Your violation of these terms</li>
                  <li>Your violation of any rights of another party</li>
                  <li>Your product listings (if you're a seller)</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">13. Termination</h4>
                <p class="mb15">We may suspend or terminate your account at any time for:</p>
                <ul class="mb20">
                  <li>Violation of these terms</li>
                  <li>Fraudulent or illegal activity</li>
                  <li>Excessive disputes or chargebacks</li>
                  <li>Any other reason at our discretion</li>
                </ul>
                <p class="mb15">You may close your account at any time by contacting us. Upon termination, your right to use the Platform ends immediately.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">14. Dispute Resolution</h4>
                <p class="mb15">Any disputes arising from these terms or your use of the Platform will be resolved through:</p>
                <ul class="mb20">
                  <li>First, good faith negotiation between the parties</li>
                  <li>If negotiation fails, binding arbitration in Lagos, Nigeria</li>
                  <li>Governed by the laws of Nigeria</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">15. Changes to Terms</h4>
                <p class="mb15">We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of the Platform after changes constitutes acceptance of the new terms.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">16. Contact Information</h4>
                <p class="mb15">If you have questions about these terms, please contact us:</p>
                <ul class="mb20">
                  <li>Email: support@genesishub.com</li>
                  <li>Phone: +(1) 123 456 7890</li>
                  <li>Address: Lagos, Nigeria</li>
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
@endsection
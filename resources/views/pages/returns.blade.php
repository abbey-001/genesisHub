@extends('layouts.app')

@section('title', 'Returns & Exchanges')

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
                <a href="#">Returns & Exchanges</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Returns Content -->
    <section class="our-terms bgc-white pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-8 offset-lg-2">
            <div class="main-title text-center mb40">
              <h2>Returns & Exchanges Policy</h2>
              <p class="text-muted">We want you to be completely satisfied with your purchase</p>
            </div>
            
            <div class="terms_condition_grid">
              
              <div class="grids mb40">
                <h4 class="mb20">Return Window</h4>
                <p class="mb15">You have <strong>30 days</strong> from the date of delivery to return most items for a full refund. Some categories have different return windows:</p>
                <ul class="mb20">
                  <li><strong>Electronics:</strong> 14 days</li>
                  <li><strong>Software & Digital Products:</strong> Non-returnable unless defective</li>
                  <li><strong>Personalized Items:</strong> Non-returnable unless defective</li>
                  <li><strong>Perishable Goods:</strong> Non-returnable</li>
                  <li><strong>Health & Personal Care:</strong> 14 days, unopened only</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Return Conditions</h4>
                <p class="mb15">To be eligible for a return, items must meet the following conditions:</p>
                <ul class="mb20">
                  <li>Item must be in original condition and packaging</li>
                  <li>All accessories, manuals, and free gifts must be included</li>
                  <li>Item must be unused and unworn (unless defective)</li>
                  <li>Original tags and labels must be attached</li>
                  <li>Proof of purchase must be provided</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">How to Initiate a Return</h4>
                
                <h5 class="mb15">Step 1: Start Your Return</h5>
                <p class="mb15">Log in to your account and go to <a href="{{ route('account.index') }}">Order History</a>. Find the order you want to return and click "Request Return".</p>
                
                <h5 class="mb15">Step 2: Select Items</h5>
                <p class="mb15">Choose which items you want to return and select a return reason from the dropdown menu.</p>
                
                <h5 class="mb15">Step 3: Choose Return Method</h5>
                <div class="mb20">
                  <p class="mb10"><strong>Option A: Pickup (Free)</strong></p>
                  <p class="mb15">Schedule a free pickup from your address. Our courier will collect the package within 3-5 business days.</p>
                  
                  <p class="mb10"><strong>Option B: Drop-off</strong></p>
                  <p class="mb15">Drop off your package at any GenesisHub partner location. Find locations <a href="{{ route('stores') }}">here</a>.</p>
                </div>
                
                <h5 class="mb15">Step 4: Pack Your Return</h5>
                <p class="mb15">Pack items securely in their original packaging if possible. Include all accessories and documentation. Print and attach the return label provided in your return confirmation email.</p>
                
                <h5 class="mb15">Step 5: Track Your Return</h5>
                <p class="mb15">Track your return status in your account. You'll receive email updates at each stage of the return process.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Refund Processing</h4>
                
                <h5 class="mb15">Inspection Period</h5>
                <p class="mb15">Once we receive your return, we'll inspect it within 2-3 business days. You'll receive an email confirmation once your return is approved.</p>
                
                <h5 class="mb15">Refund Timeline</h5>
                <ul class="mb20">
                  <li><strong>Original Payment Method:</strong> 5-10 business days after approval</li>
                  <li><strong>Store Credit:</strong> Immediately after approval</li>
                  <li><strong>Bank Transfer:</strong> 3-7 business days after approval</li>
                </ul>
                
                <h5 class="mb15">Refund Amount</h5>
                <p class="mb15">You'll receive a full refund of the purchase price. Original shipping charges are non-refundable unless the return is due to our error or a defective product.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Exchanges</h4>
                <p class="mb15">We currently don't offer direct exchanges. To exchange an item:</p>
                <ol class="mb20">
                  <li>Return your original item for a refund</li>
                  <li>Place a new order for the item you want</li>
                </ol>
                <p class="mb15">This ensures you get your preferred item faster. If you need a different size or color, we recommend placing a new order before returning the original to avoid the item selling out.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Defective or Damaged Items</h4>
                <p class="mb15">If you receive a defective or damaged item:</p>
                <ul class="mb20">
                  <li>Contact us within 48 hours of delivery</li>
                  <li>Provide photos of the damage or defect</li>
                  <li>We'll arrange a free return pickup</li>
                  <li>You'll receive a full refund including shipping costs</li>
                  <li>Or we can send a replacement at no extra charge</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Wrong Item Received</h4>
                <p class="mb15">If you receive the wrong item:</p>
                <ul class="mb20">
                  <li>Contact us immediately</li>
                  <li>We'll arrange a free return pickup</li>
                  <li>We'll ship the correct item at no extra charge</li>
                  <li>You'll receive a refund for any price difference</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Non-Returnable Items</h4>
                <p class="mb15">The following items cannot be returned unless defective:</p>
                <ul class="mb20">
                  <li>Downloadable software and digital products</li>
                  <li>Opened health and personal care items</li>
                  <li>Opened beauty products</li>
                  <li>Intimate apparel (underwear, swimwear)</li>
                  <li>Custom or personalized items</li>
                  <li>Gift cards and vouchers</li>
                  <li>Perishable goods (food, flowers, etc.)</li>
                  <li>Newspapers and magazines</li>
                  <li>Hazardous materials</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Seller-Specific Policies</h4>
                <p class="mb15">Some sellers may have different return policies. Always check the seller's return policy on the product page before purchasing. Seller policies will be clearly displayed and may include:</p>
                <ul class="mb20">
                  <li>Different return windows</li>
                  <li>Restocking fees</li>
                  <li>Return shipping costs</li>
                  <li>Additional conditions</li>
                </ul>
                <p class="mb15">In case of conflict, the seller's policy takes precedence for items sold by third-party sellers.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Cancellations</h4>
                
                <h5 class="mb15">Before Shipment</h5>
                <p class="mb15">You can cancel your order free of charge before it ships. Simply go to your order details and click "Cancel Order".</p>
                
                <h5 class="mb15">After Shipment</h5>
                <p class="mb15">Once an order has shipped, you cannot cancel it. However, you can refuse delivery or return the item once received following our standard return process.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">International Returns</h4>
                <p class="mb15">For international orders:</p>
                <ul class="mb20">
                  <li>Return shipping costs are the customer's responsibility</li>
                  <li>Items must be returned within 14 days</li>
                  <li>Customs fees and duties are non-refundable</li>
                  <li>Contact our international support team for return instructions</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Partial Returns</h4>
                <p class="mb15">You can return part of your order while keeping other items. Each item will be refunded individually based on its purchase price.</p>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Tips for Faster Returns</h4>
                <ul class="mb20">
                  <li>Include your order number inside the package</li>
                  <li>Use trackable shipping if self-shipping</li>
                  <li>Keep your return tracking number</li>
                  <li>Take photos before packing as proof of condition</li>
                  <li>Don't write on or damage the original packaging</li>
                </ul>
              </div>
              
              <div class="grids mb40">
                <h4 class="mb20">Need Help?</h4>
                <p class="mb15">Our customer service team is here to help with any questions about returns or exchanges:</p>
                <ul class="mb20">
                  <li><strong>Email:</strong> returns@genesishub.com</li>
                  <li><strong>Phone:</strong> +(1) 123 456 7890 (Mon-Fri, 8am-9pm)</li>
                  <li><strong>Live Chat:</strong> Available on our <a href="{{ route('contact') }}">Contact page</a></li>
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
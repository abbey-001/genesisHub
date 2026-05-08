@extends('layouts.app')

@section('title', 'Shopping Cart - ' . config('app.name'))

@section('content')
<div class="wrapper ovh">
  
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])
  
  <div class="body_content_wrapper position-relative">

    <!-- Shop Cart Content -->
    <section class="shop-cart pt30">
      <div class="container">
        <div class="row">
          <div class="col-sm-6 col-lg-4 m-auto">
            <div class="main-title text-center mb50">
              <h2 class="title">Shopping Cart</h2>
            </div>
          </div>
        </div>
        
        @if(count($cart) > 0)
        <div class="row mt15">
          <div class="col-lg-8 col-xl-9">
            <!-- Shipping Address Section -->
            @auth
            <div class="shipping_address_widget mb30">
              <div class="order_sidebar_widget style2">
                <div class="d-flex justify-content-between align-items-center mb20">
                  <h4 class="title mb-0">Shipping Address</h4>
                  <a href="javascript:void(0)" class="text-thm fz14" id="change-address-btn">
                    <i class="fas fa-edit me-1"></i>Change
                  </a>
                </div>
                
                @if($defaultAddress)
                <div class="selected_address_display p-3 border rounded" id="selected-address-display">
                  <div class="d-flex align-items-start">
                    <div class="address_icon me-3">
                      <i class="fas fa-map-marker-alt text-thm fz20"></i>
                    </div>
                    <div class="address_details flex-grow-1">
                      <p class="mb-1 fw-bold">{{ $defaultAddress->address }}</p>
                      <p class="mb-0 text-muted fz14">
                        {{ $defaultAddress->city }}, {{ $defaultAddress->state }} {{ $defaultAddress->postal_code }}
                      </p>
                      <p class="mb-0 text-muted fz14">{{ $defaultAddress->country }}</p>
                      <span class="badge bg-success mt-2 fz12">Default Address</span>
                    </div>
                  </div>
                </div>
                @else
                <div class="no_address_display p-4 border rounded text-center bg-light">
                  <i class="fas fa-map-marked-alt text-muted fz40 mb-3"></i>
                  <p class="mb-2 fw-bold">No Shipping Address</p>
                  <p class="mb-3 text-muted fz14">Please add a shipping address to proceed with payment</p>
                  <a href="{{ route('account.index') }}#addresses" class="btn btn-thm btn-sm">Add Address</a>
                </div>
                @endif

                <!-- Address Selection Panel -->
                <div class="address_selection_panel mt-3" id="address-selection-panel" style="display: none;">
                  <div class="addresses_list">
                    @if(isset($userAddresses) && count($userAddresses) > 0)
                    @foreach($userAddresses as $address)
                    <div class="address_card mb-3 p-3 border rounded {{ $address->is_default ? 'border-thm' : '' }}" 
                         style="cursor: pointer; transition: all 0.3s ease;"
                         data-address-id="{{ $address->id }}"
                         onclick="selectAddress(this)">
                      <div class="d-flex align-items-start">
                        <div class="form-check me-3">
                          <input class="form-check-input" type="radio" name="shipping_address" 
                                 id="address-{{ $address->id }}" 
                                 value="{{ $address->id }}"
                                 {{ $address->is_default ? 'checked' : '' }}>
                        </div>
                        <div class="address_content flex-grow-1">
                          <label class="form-check-label w-100" for="address-{{ $address->id }}" style="cursor: pointer;">
                            <p class="mb-1 fw-bold">{{ $address->address }}</p>
                            <p class="mb-0 text-muted fz14">
                              {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}
                            </p>
                            <p class="mb-0 text-muted fz14">{{ $address->country }}</p>
                            @if($address->is_default)
                            <span class="badge bg-success mt-2 fz12">Default</span>
                            @endif
                          </label>
                        </div>
                      </div>
                    </div>
                    @endforeach
                    @else
                    <div class="text-center py-4">
                      <p class="text-muted mb-3">You haven't added any addresses yet</p>
                      <a href="{{ route('account.index') }}#addresses" class="btn btn-thm btn-sm">Add New Address</a>
                    </div>
                    @endif
                  </div>
                  
                  @if(isset($userAddresses) && count($userAddresses) > 0)
                  <div class="address_actions mt-3 d-flex justify-content-between">
                    <a href="{{ route('account.index') }}#addresses" class="btn btn-outline-thm btn-sm">
                      <i class="fas fa-plus me-1"></i>Add New Address
                    </a>
                    <div>
                      <button type="button" class="btn btn-light btn-sm me-2" id="cancel-address-btn">Cancel</button>
                      <button type="button" class="btn btn-thm btn-sm" id="confirm-address-btn">Confirm</button>
                    </div>
                  </div>
                  @endif
                </div>
              </div>
            </div>
            @endauth

            <div class="shopping_cart_table table-responsive">
              <table class="table table-borderless">
                <thead>
                  <tr>
                    <th scope="col">PRODUCT</th>
                    <th scope="col">PRICE</th>
                    <th scope="col">QUANTITY</th>
                    <th scope="col">TOTAL</th>
                    <th scope="col">REMOVE</th>
                  </tr>
                </thead>
                <tbody class="table_body">
                  @foreach($cart as $productId => $item)
                  <tr data-product-id="{{ $productId }}">
                    <th scope="row">
                      <ul class="cart_list d-block d-xl-flex">
                        <li class="ps-1 ps-sm-4 pe-1 pe-sm-4">
                          <a href="{{ route('product.show', $item['slug']) }}">
                            <img src="{{ asset('public/storage/'.$item['image']) }}" alt="{{ $item['name'] }}">
                          </a>
                        </li>
                        <li class="ms-2 ms-md-3">
                          <a class="cart_title" href="{{ route('product.show', $item['slug']) }}">
                            <span class="fz16">{{ $item['name'] }}</span>
                          </a>
                          @if(!empty($item['variant_label']))
                            <div style="margin-top:4px;">
                              <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#714e32;background:#fdf1e8;border:1px solid #e8d5c4;border-radius:4px;padding:2px 7px;">
                                <i class="fas fa-tag" style="font-size:9px;opacity:.7;"></i>
                                {{ $item['variant_label'] }}
                              </span>
                            </div>
                          @endif
                        </li>
                      </ul>
                    </th>
                    <td class="price-cell" data-price="{{ $item['price'] }}">₦ {{ number_format($item['price'], 2) }}</td>
                    <td>
                      <div class="cart_btn">
                        <div class="quantity-block">
                          <button class="quantity-arrow-minus inner_page" data-product-id="{{ $productId }}" type="button">
                            <span class="fa fa-minus"></span>
                          </button>
                          <input class="quantity-num inner_page" type="number" value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] }}" data-product-id="{{ $productId }}">
                          <button class="quantity-arrow-plus inner_page" data-product-id="{{ $productId }}" type="button">
                            <span class="fas fa-plus"></span>
                          </button>
                        </div>
                      </div>
                    </td>
                    <td class="total-cell">₦ {{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                        <td>
                          <button type="button"
                                  class="btn btn-sm btn-soft-danger remove-item"
                                  data-product-id="{{ $productId }}"
                                  title="Remove item"
                                  style="border:none;background:transparent;padding:4px 8px;">
                              <span class="flaticon-close" style="pointer-events:none;font-size:14px;color:#dc3545;"></span>
                          </button>
                        </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
                  <div class="checkout_form mt30">
                      <div class="checkout_coupon posr d-block d-xl-flex">
                        <form class="form_one posr mb10-lg" id="coupon-form" onsubmit="return false;">
                          @csrf
                          <input class="form-control coupon_input"
                                 type="text"
                                 name="coupon_code"
                                 placeholder="Coupon code"
                                 aria-label="Coupon code"
                                 maxlength="50"
                                 style="text-transform:uppercase">
                          <a class="btn apply_count_btn" href="javascript:void(0)" id="apply-coupon">Apply Coupon</a>
                          <a class="btn apply_count_btn btn-danger" href="javascript:void(0)" id="remove-coupon" style="display:none;">
                              ✕ Remove
                          </a>
                        </form>
                        <form class="form_two">
                          <a href="{{ route('product.index') }}" class="btn btn_shopping btn-white me-3">Continue Shopping</a>
                        </form>
                      </div>
                    </div>
            </div>
          </div>
           <div class="col-lg-4 col-xl-3">
            <div class="order_sidebar_widget style2">
              <h4 class="title">Cart Totals</h4>
              <ul>
                <li class="subtitle">
                  <p>Product Subtotal <span class="float-end" id="subtotal">₦ {{ number_format($cartTotal, 2) }}</span></p>
                </li>
                <li class="subtitle" id="discount-row" style="{{ session('applied_coupon') ? '' : 'display:none;' }}">
                  <p>Discount
                    <span class="float-end text-success" id="discount">
                      @if(session('applied_coupon'))
                        <span class="text-success fw-medium">
                          {{ session('applied_coupon.code') }}
                          <small class="badge bg-success ms-1">
                            {{ session('applied_coupon.type') === 'percent'
                                ? session('applied_coupon.value').'% OFF'
                                : '₦'.number_format(session('applied_coupon.value'),2).' OFF' }}
                          </small>
                        </span>
                        <span class="float-end text-success">-₦ {{ number_format(session('applied_coupon.discount'), 2) }}</span>
                      @else
                        -₦ 0.00
                      @endif
                    </span>
                  </p>
                </li>
                <li class="subtitle">
                  <p>Delivery Fee
                    <span class="float-end" id="shipping">
                      @auth
                        @if($deliveryFee !== null)
                          ₦ {{ number_format($deliveryFee, 2) }}
                        @elseif($defaultAddress && !$defaultAddress->delivery_zone)
                          <span class="text-warning fz12">Address zone not set</span>
                        @elseif($defaultAddress)
                          <span class="text-muted fz12">Calculating...</span>
                        @else
                          <span class="text-muted fz12">Add address to see fee</span>
                        @endif
                      @else
                        <span class="text-muted fz12">Login to see fee</span>
                      @endauth
                    </span>
                  </p>
                </li>
                <li class="subtitle"><hr></li>
                <li class="subtitle totals">
                  <p>Total
                    <span class="float-end" id="grand-total">
                        @php
                            $discount     = session('applied_coupon.discount', 0);
                            $displayTotal = $cartTotal - $discount;
                            if ($deliveryFee !== null) {
                                $displayTotal += $deliveryFee;
                            }
                            $displayTotal = max(0, $displayTotal);
                        @endphp
                        @auth
                            ₦ {{ number_format($displayTotal, 2) }}
                        @else
                            ₦ {{ number_format(max(0, $cartTotal - $discount), 2) }}
                        @endauth
                    </span>
                  </p>
                </li>
              </ul>
 
              {{-- ═══════════════════════════════════════════════════════════
                   DELIVERY ESTIMATE WIDGET
                   Shows below the totals list, above the payment gateway picker.
                   $deliveryEstimate is provided by CartController::index().
              ═══════════════════════════════════════════════════════════ --}}
              @if($deliveryEstimate)
              @php
                  $estMin     = $deliveryEstimate['min'];
                  $estMax     = $deliveryEstimate['max'];
                  $hasPreorder= $deliveryEstimate['has_preorder'];
                  $slowestItem= $deliveryEstimate['slowest_item'];
 
                  // Build the human-readable range label shown in the badge.
                  if ($estMin === $estMax) {
                      $estLabel = $estMin === 1 ? '1 day' : "{$estMin} days";
                  } else {
                      $estLabel = "{$estMin}–{$estMax} days";
                  }
              @endphp
 
              <div class="delivery_estimate_widget mt-3 mb-3"
                   style="border-radius:10px; overflow:hidden; border: 1px solid {{ $hasPreorder ? '#f0c080' : '#d4edda' }};">
 
                {{-- Header bar --}}
                <div class="d-flex align-items-center px-3 py-2"
                     style="background: {{ $hasPreorder ? '#fff8ec' : '#f0f9f3' }}; border-bottom: 1px solid {{ $hasPreorder ? '#f0c080' : '#d4edda' }};">
                  <i class="fas {{ $hasPreorder ? 'fa-clock' : 'fa-truck' }} me-2"
                     style="color: {{ $hasPreorder ? '#d97706' : '#28a745' }}; font-size:14px;"></i>
                  <span class="fw-semibold fz13" style="color: {{ $hasPreorder ? '#92400e' : '#166534' }};">
                    Estimated Delivery
                  </span>
                  <span class="ms-auto badge"
                        style="background: {{ $hasPreorder ? '#f59e0b' : '#28a745' }}; font-size:11px; font-weight:700; border-radius:6px; padding:3px 8px;">
                    {{ $estLabel }}
                  </span>
                </div>
 
                {{-- Body --}}
                <div class="px-3 py-2" style="background: {{ $hasPreorder ? '#fffbf2' : '#f8fffe' }};">
                  @if($hasPreorder && $slowestItem)
                    <p class="mb-0 fz12" style="color:#78350f; line-height:1.5;">
                      <strong>{{ $slowestItem }}</strong> is a pre-order or made-to-order item.
                      Your full order ships together once everything is ready.
                    </p>
                  @else
                    <p class="mb-0 fz12" style="color:#166534; line-height:1.5;">
                      All items are in stock. Your order will be dispatched within
                      <strong>{{ $estMax === 1 ? '1 day' : "{$estMax} days" }}</strong> of payment.
                    </p>
                  @endif
                </div>
              </div>
 
              {{-- ═══════════════════════════════════════════════════════════
                   FUNNY APOLOGY MESSAGE — only shown for pre-order carts
                   Randomly picked from CartTotalService::getApologyMessage().
              ═══════════════════════════════════════════════════════════ --}}
              @if($hasPreorder && $slowestItem)
              @php
                  // Instantiate here since we're in a blade — the service is tiny.
                  $apologyText = app(\App\Services\CartTotalService::class)
                      ->getApologyMessage($slowestItem);
              @endphp
              <div class="apology_widget mb-3 p-3"
                   style="background: linear-gradient(135deg, #fff8ec 0%, #fff3dc 100%);
                          border: 1px solid #fcd34d;
                          border-radius: 10px;
                          position: relative;
                          overflow: hidden;">
 
                {{-- Decorative quote mark --}}
                <span style="position:absolute; top:-8px; left:10px; font-size:60px; color:#fcd34d; opacity:.5; font-family:Georgia,serif; line-height:1; pointer-events:none;">&ldquo;</span>
 
                <div class="d-flex align-items-start gap-2" style="position:relative; z-index:1;">
                  <span style="font-size:22px; flex-shrink:0; margin-top:2px;">🙏</span>
                  <div>
                    <p class="mb-1 fz12 fw-semibold" style="color:#92400e;">
                      A word from our logistics department:
                    </p>
                    <p class="mb-0 fz12" style="color:#78350f; line-height:1.6;">
                      {!! $apologyText !!}
                    </p>
                  </div>
                </div>
              </div>
              @endif
              @endif
              {{-- ═══════════════════════════════════════════════════════════ --}}
 
              @auth
              @if($defaultAddress)
              <!-- Gateway Selector -->
              <div class="gateway_selector mb-3">
                <p class="fz13 text-muted mb-2 fw-medium">Choose payment method:</p>
                <div class="gateway_options d-flex flex-column gap-2">
 
                  <!-- Paystack (recommended) -->
                  <label class="gateway_card active" id="gateway-paystack-label" for="gateway-paystack"
                         style="cursor:pointer; border:2px solid var(--thm-color,#714e32); border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between; transition:all .25s;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="radio" name="payment_gateway" id="gateway-paystack" value="paystack" checked
                             style="accent-color:var(--thm-color,#714e32);">
                      <span class="fw-semibold fz14">Paystack</span>
                      <span style="font-size:10px;font-weight:700;background:#28a745;color:#fff;border-radius:4px;padding:1px 6px;letter-spacing:.3px;">RECOMMENDED</span>
                    </div>
                  </label>
 
                  <!-- Flutterwave -->
                  <label class="gateway_card" id="gateway-flutterwave-label" for="gateway-flutterwave"
                         style="cursor:pointer; border:2px solid #e0e0e0; border-radius:10px; padding:10px 14px; display:flex; align-items:center; justify-content:space-between; transition:all .25s;">
                    <div class="d-flex align-items-center gap-2">
                      <input type="radio" name="payment_gateway" id="gateway-flutterwave" value="flutterwave"
                             style="accent-color:#f5a623;">
                      <span class="fw-semibold fz14">Flutterwave</span>
                    </div>
                  </label>
 
                </div>
              </div>
              @endif
              @endauth
 
              <div class="ui_kit_button payment_widget_btn">
                @auth
                  @if($defaultAddress)
                    <button type="button" class="btn btn-thm btn-block" id="proceed-to-payment">
                      <i class="fas fa-lock me-2"></i>Proceed to Payment
                    </button>
                  @else
                    <a href="{{ route('account.index') }}#addresses" class="btn btn-thm btn-block">
                      <i class="fas fa-map-marker-alt me-2"></i>Add Address to Continue
                    </a>
                  @endif
                @else
                  <a href="javascript:void(0)" class="btn btn-thm btn-block" id="login-checkout-btn">
                    <i class="fas fa-sign-in-alt me-2"></i>Login to Continue
                  </a>
                @endauth
              </div>
 
              @auth
              <div class="payment_info mt-3">
                <p class="text-center text-muted fz12 mb-2">
                  <i class="fas fa-shield-alt text-success me-1"></i>
                  Secured &amp; encrypted payment
                </p>
                <div class="d-flex justify-content-center align-items-center gap-2">
                  <img src="{{ asset('public/images/resource/visa-card.png') }}" alt="Visa" style="height: 20px;">
                  <img src="{{ asset('public/images/resource/master-card.png') }}" alt="Mastercard" style="height: 20px;">
                  <img src="{{ asset('public/images/resource/paypal.png') }}" alt="paypal" style="height: 20px;">
                </div>
              </div>
              @endauth
            </div>
          </div>
        </div>
        @else
        <div class="row mt15">
          <div class="col-12">
            <div class="text-center py-5">
              <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
              <h4>Your cart is empty</h4>
              <p>Add some products to your cart to see them here</p>
              <a href="{{ route('product.index') }}" class="btn btn-thm mt-3">Continue Shopping</a>
            </div>
          </div>
        </div>
        @endif
      </div>
    </section>
    
     @php
       $recentlyViewed = \App\Models\Product::with([
           'images' => fn($q) => $q->where('is_primary', true)->limit(1),
           'brand:id,name',
       ])
       ->select('id', 'name', 'slug', 'price', 'sale_price', 'stock', 'brand_id', 'rating', 'review_count')
       ->whereIn('id', array_slice(session('recently_viewed', []), 0, 8))
       ->active()
       ->get();
     @endphp


    @include('partials.footer')
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>

<!-- Payment Processing Modal -->
<div class="modal fade" id="paymentProcessingModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-5">
        <div class="spinner-border text-thm mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <h5>Processing Payment...</h5>
        <p class="text-muted">Please wait while we initialize your payment</p>
      </div>
    </div>
  </div>
</div>

@push('styles')
<style>
  .address_card {
    transition: all 0.3s ease;
  }
  
  .address_card:hover {
    background-color: #f8f9fa;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  }
  
  .address_card.border-thm {
    border-width: 2px !important;
  }
  
  .selected_address_display {
    background-color: #f8f9fa;
  }
  
  .address_icon {
    min-width: 40px;
  }
  
  .payment_info img {
    opacity: 0.7;
    transition: opacity 0.3s;
  }
  
  .payment_info img:hover {
    opacity: 1;
  }
  
  @media (max-width: 767px) {
    .address_actions {
      flex-direction: column;
      gap: 10px;
    }
    
    .address_actions > * {
      width: 100%;
    }
    
    .address_actions .btn {
      width: 100%;
    }
  }
</style>
@endpush

@push('scripts')
<script src="https://js.paystack.co/v1/inline.js"></script>
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script src="{{ asset('public/js/cart-page.js') }}"></script>
<script>
$(document).ready(function() {
    // Listen for cart update events from cart-page.js
    // When quantities change, cart-page.js fires this custom event with the server response
    $(document).on('cart:updated', function(e, response) {
        if (response && response.delivery_fee !== undefined) {
            if (response.delivery_fee !== null) {
                $('#shipping').text('₦ ' + parseFloat(response.delivery_fee).toLocaleString('en-NG', {minimumFractionDigits: 2}));
            }
            if (response.grand_total) {
                $('#grand-total').text('₦ ' + response.grand_total);
            }
            if (response.subtotal) {
                $('#subtotal').text('₦ ' + response.subtotal);
            }
        }
    });

    // Toggle address selection panel
    $('#change-address-btn').on('click', function(e) {
        e.preventDefault();
        $('#address-selection-panel').slideToggle(300);
        $(this).find('i').toggleClass('fa-edit fa-times');
    });
    
    // Cancel address selection
    $('#cancel-address-btn').on('click', function() {
        $('#address-selection-panel').slideUp(300);
        $('#change-address-btn').find('i').removeClass('fa-times').addClass('fa-edit');
        
        const defaultAddressId = $('input[name="shipping_address"]:checked').val();
        $(`input[value="${defaultAddressId}"]`).prop('checked', true);
    });
    
    // Address card click handler
    window.selectAddress = function(card) {
        $('.address_card').removeClass('border-thm').css('border-width', '1px');
        $(card).addClass('border-thm').css('border-width', '2px');
        $(card).find('input[type="radio"]').prop('checked', true);
    };
    
    // Confirm address selection
    $('#confirm-address-btn').on('click', function() {
        const selectedAddressId = $('input[name="shipping_address"]:checked').val();
        
        if (!selectedAddressId) {
            alert('Please select a shipping address');
            return;
        }
        
        const selectedCard = $(`.address_card[data-address-id="${selectedAddressId}"]`);
        const addressHtml = selectedCard.find('.address_content').html();
        
        $('#selected-address-display .address_details').html(addressHtml);
        $('#address-selection-panel').slideUp(300);
        $('#change-address-btn').find('i').removeClass('fa-times').addClass('fa-edit');

        // Show loading state on fee
        $('#shipping').html('<span class="text-muted fz12"><i class="fas fa-spinner fa-spin me-1"></i>Calculating...</span>');
        
        $.ajax({
            url: '{{ route("cart.update-address") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                address_id: selectedAddressId
            },
            success: function(response) {
                if (response.success) {
                    if (response.delivery_fee !== null && response.delivery_fee !== undefined) {
                        $('#shipping').text('₦ ' + parseFloat(response.delivery_fee).toLocaleString('en-NG', {minimumFractionDigits: 2}));
                        $('#grand-total').text('₦ ' + response.grand_total);
                    } else {
                        $('#shipping').html('<span class="text-warning fz12">Address zone not set</span>');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error updating address');
                $('#shipping').html('<span class="text-danger fz12">Error calculating fee</span>');
            }
        });
    });
    
    // Gateway card visual toggle
    $('input[name="payment_gateway"]').on('change', function() {
        const selected = $(this).val();
        $('.gateway_card').css('border-color', '#e0e0e0');
        $('#gateway-' + selected + '-label').css('border-color', 'var(--thm-color, #714e32)');
    });

    // Proceed to Payment
    $('#proceed-to-payment').on('click', function() {
        const btn     = $(this);
        const gateway = $('input[name="payment_gateway"]:checked').val() || 'paystack';

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        $('#paymentProcessingModal').modal('show');

        $.ajax({
            url: '{{ route("payment.initialize") }}',
            method: 'POST',
            data: {
                _token:  '{{ csrf_token() }}',
                gateway: gateway,
            },
            success: function(response) {
                if (response.success) {
                    if (response.gateway === 'flutterwave') {
                        initializeFlutterwave(response.data);
                    } else {
                        initializePaystack(response.data);
                    }
                } else {
                    $('#paymentProcessingModal').modal('hide');
                    alert(response.message || 'Failed to initialize payment');
                    btn.prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Proceed to Payment');
                }
            },
            error: function(xhr) {
                $('#paymentProcessingModal').modal('hide');
                const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                alert(message);
                btn.prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Proceed to Payment');
            }
        });
    });

    // ── Paystack ──────────────────────────────────────────────────────────────
    function initializePaystack(data) {
        const handler = PaystackPop.setup({
            key:      data.public_key,
            email:    data.email,
            amount:   data.amount,       // kobo
            currency: 'NGN',
            ref:      data.reference,
            metadata: {
                custom_fields: [
                    { display_name: 'Order ID', variable_name: 'order_id', value: data.order_id }
                ]
            },
            callback: function(response) {
                $('#paymentProcessingModal').modal('hide');
                window.location.href = '{{ url("payment/paystack/callback") }}?reference=' + response.reference;
            },
            onClose: function() {
                $('#paymentProcessingModal').modal('hide');
                $('#proceed-to-payment').prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Proceed to Payment');
            }
        });
        handler.openIframe();
    }

    // ── Flutterwave ───────────────────────────────────────────────────────────
    function initializeFlutterwave(data) {
        FlutterwaveCheckout({
            public_key:      data.public_key,
            tx_ref:          data.reference,
            amount:          data.amount,   // full Naira
            currency:        'NGN',
            payment_options: 'card, ussd, banktransfer',
            customer: {
                email:        data.email,
                name:         data.name,
                phone_number: data.phone,
            },
            meta: {
                order_id:     data.order_id,
                order_number: data.order_number,
            },
            customizations: {
                title:       '{{ config("app.name") }}',
                description: 'Order Payment',
            },
            callback: function(response) {
                $('#paymentProcessingModal').modal('hide');
                if (response.status === 'successful' || response.status === 'completed') {
                    window.location.href = '{{ url("payment/flutterwave/callback") }}?transaction_id=' + response.transaction_id + '&tx_ref=' + response.tx_ref;
                } else {
                    $('#proceed-to-payment').prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Proceed to Payment');
                    alert('Payment was not successful. Please try again.');
                }
            },
            onclose: function() {
                $('#paymentProcessingModal').modal('hide');
                $('#proceed-to-payment').prop('disabled', false).html('<i class="fas fa-lock me-2"></i>Proceed to Payment');
            },
        });
    }
});
</script>
@endpush
@endsection
@extends('layouts.app')

@section('title', 'Checkout - ' . config('app.name'))

@section('content')
<div class="wrapper ovh">
  
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])
  
  <div class="body_content_wrapper position-relative">

    <!-- Shop Checkouts Content -->
    <section class="shop-checkouts pt30">
      <div class="container">
        <div class="row">
          <div class="col-sm-6 col-lg-4 m-auto">
            <div class="main-title text-center mb50">
              <h2 class="title">Checkout</h2>
            </div>
          </div>
        </div>
        
        @if(count($cart ?? []) === 0)
        <div class="row">
          <div class="col-12">
            <div class="text-center py-5">
              <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
              <h4>Your cart is empty</h4>
              <p>Add some products to your cart before checkout</p>
              <a href="{{ route('product.index') }}" class="btn btn-thm mt-3">Continue Shopping</a>
            </div>
          </div>
        </div>
        @else
        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
          @csrf
          <div class="row">
            <div class="col-lg-8 col-xl-9">
              <div class="checkout_form style2">
                <h4 class="title mb20">Billing details</h4>
                <div class="checkout_coupon ui_kit_button">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="form-label">First name *</label>
                        <input class="form-control form_control" type="text" name="first_name" 
                               value="{{ old('first_name', auth()->user()->name ?? '') }}" required>
                        @error('first_name')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="form-label">Last name *</label>
                        <input class="form-control form_control" type="text" name="last_name" 
                               value="{{ old('last_name') }}" required>
                        @error('last_name')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form-label">Company name (optional)</label>
                        <input class="form-control form_control" type="text" name="company" 
                               value="{{ old('company') }}">
                      </div>
                    </div>
                    <div class="col-lg-12">
                      <div class="form-group">
                        <label class="form-label">Country / Region *</label>
                        <div class="checkout_country_form actegory">
                          <select class="selectpicker show-tick" name="country" required>
                            <option value="">Select Country</option>
                            <option value="Nigeria" {{ old('country') == 'Nigeria' ? 'selected' : '' }}>Nigeria</option>
                            <option value="United States" {{ old('country') == 'United States' ? 'selected' : '' }}>United States</option>
                            <option value="United Kingdom" {{ old('country') == 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                            <option value="Canada" {{ old('country') == 'Canada' ? 'selected' : '' }}>Canada</option>
                            <option value="Australia" {{ old('country') == 'Australia' ? 'selected' : '' }}>Australia</option>
                          </select>
                        </div>
                        @error('country')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form-label">Street address *</label>
                        <input class="form-control form_control mb10" type="text" 
                               name="address_line1" placeholder="House number and street name" 
                               value="{{ old('address_line1') }}" required>
                        <input class="form-control form_control" type="text" 
                               name="address_line2" placeholder="Apartment, suite, unit, etc. (optional)" 
                               value="{{ old('address_line2') }}">
                        @error('address_line1')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form-label">Town / City *</label>
                        <input class="form-control form_control" type="text" name="city" 
                               value="{{ old('city') }}" required>
                        @error('city')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form-label">State *</label>
                        <div class="checkout_country_form">
                          <select class="selectpicker show-tick" name="state" required>
                            <option value="">Select State</option>
                            <option value="Lagos" {{ old('state') == 'Lagos' ? 'selected' : '' }}>Lagos</option>
                            <option value="Abuja" {{ old('state') == 'Abuja' ? 'selected' : '' }}>Abuja</option>
                            <option value="Kano" {{ old('state') == 'Kano' ? 'selected' : '' }}>Kano</option>
                            <option value="Rivers" {{ old('state') == 'Rivers' ? 'selected' : '' }}>Rivers</option>
                          </select>
                        </div>
                        @error('state')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group">
                        <label class="form-label">ZIP / Postal Code *</label>
                        <input class="form-control form_control" type="text" name="postal_code" 
                               value="{{ old('postal_code') }}" required>
                        @error('postal_code')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="form-label">Phone *</label>
                        <input name="phone" class="form-control form_control" type="tel" 
                               value="{{ old('phone', auth()->user()->phone ?? '') }}" required>
                        @error('phone')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input name="email" class="form-control form_control email" type="email" 
                               value="{{ old('email', auth()->user()->email ?? '') }}" required>
                        @error('email')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>
                    </div>
                    <div class="col-sm-12">
                      <div class="ui_kit_checkbox">
                        <label class="custom_checkbox">Ship to a different address?
                          <input type="checkbox" id="different-address">
                          <span class="checkmark"></span>
                        </label>
                      </div>
                    </div>
                    <div class="col-sm-12" id="shipping-address" style="display: none;">
                      <div class="mb35 mt30">
                        <h4 class="fz20">Shipping Details</h4>
                      </div>
                      <!-- Shipping address fields would go here -->
                    </div>
                    <div class="col-sm-12">
                      <div class="form-group mb0">
                        <label class="ai_title">Order notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="6" 
                                  placeholder="Notes about your order, e.g. special notes for delivery">{{ old('notes') }}</textarea>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="col-lg-4 col-xl-3">
              <div class="order_sidebar_widget checkout_page mb30 mb30">
                <h4 class="title">Your Order</h4>
                <ul>
                  @foreach($cart as $item)
                  <li class="{{ $loop->last ? 'pb0' : '' }}">
                    <p class="product_name_qnt">{{ Str::limit($item['name'], 40) }} x {{ $item['quantity'] }}</p>
                    <span class="price">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                  </li>
                  @endforeach
                  <li class="subtitle">
                    <p>Sub Total <span class="float-end totals">${{ number_format($cartTotal, 2) }}</span></p>
                  </li>
                  <li class="subtitle">
                    <p>Shipping 
                      <span class="float-end totals">
                        @php $shipping = $cartTotal >= 200 ? 0 : 10; @endphp
                        {{ $shipping == 0 ? 'Free' : '$' . number_format($shipping, 2) }}
                      </span>
                    </p>
                  </li>
                  <li class="subtitle">
                    <p>Tax (10%) 
                      <span class="float-end totals">
                        ${{ number_format($cartTotal * 0.10, 2) }}
                      </span>
                    </p>
                  </li>
                  <li class="subtitle">
                    <p>Total 
                      <span class="float-end totals">
                        ${{ number_format($cartTotal + $shipping + ($cartTotal * 0.10), 2) }}
                      </span>
                    </p>
                  </li>
                </ul>
              </div>
              
              <div class="order_sidebar_widget checkout_page mb30 mb30">
                <div class="payment_method">
                  <div class="ui_kit_radiobox pm_content bb1">
                    <div class="radio mb10">
                      <input id="radio_bank" name="payment_method" type="radio" value="bank_transfer" checked>
                      <label class="pmtitle" for="radio_bank">
                        <span class="radio-label"></span> Direct bank transfer
                      </label>
                    </div>
                    <div class="pm_details">
                      <p>Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.</p>
                    </div>
                  </div>
                  <div class="ui_kit_radiobox pm_content bb1">
                    <div class="radio mb10">
                      <input id="radio_check" name="payment_method" type="radio" value="check">
                      <label class="pmtitle" for="radio_check">
                        <span class="radio-label"></span> Check Payment
                      </label>
                    </div>
                  </div>
                  <div class="ui_kit_radiobox pm_content">
                    <div class="radio mb10">
                      <input id="radio_cod" name="payment_method" type="radio" value="cash_on_delivery">
                      <label class="pmtitle" for="radio_cod">
                        <span class="radio-label"></span> Cash on Delivery
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="ui_kit_checkbox checkout_pm">
                <label class="custom_checkbox">
                  I have read and agree to the website 
                  <a href="{{ route('terms') }}" target="_blank">terms and conditions</a> *
                  <input type="checkbox" name="terms" required>
                  <span class="checkmark"></span>
                </label>
              </div>
              
              <div class="ui_kit_button payment_widget_btn">
                <button type="submit" class="btn btn-thm btn-block mb0">Place Order</button>
              </div>
            </div>
          </div>
        </form>
        @endif
      </div>
    </section>

    @include('partials.footer')
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
  // Toggle shipping address
  $('#different-address').on('change', function() {
    $('#shipping-address').toggle(this.checked);
  });
  
  // Form validation
  $('#checkout-form').on('submit', function(e) {
    const termsChecked = $('input[name="terms"]').is(':checked');
    
    if (!termsChecked) {
      e.preventDefault();
      showToast('error', 'Please agree to the terms and conditions');
      return false;
    }
    
    // Show loading state
    const $btn = $(this).find('button[type="submit"]');
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
  });
});
</script>
@endpush
@endsection
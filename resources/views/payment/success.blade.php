@extends('layouts.app')
@section('title', 'Payment Successful - ' . config('app.name'))

@section('content')
<style>
.gh-check-circle { stroke-dasharray:166; stroke-dashoffset:166; stroke-width:3; stroke:#16a34a; fill:#f0fdf4; animation:ghStroke .6s cubic-bezier(.65,0,.45,1) forwards; }
.gh-check-tick   { transform-origin:50% 50%; stroke-dasharray:48; stroke-dashoffset:48; stroke:#fff; stroke-width:3; animation:ghStroke .3s cubic-bezier(.65,0,.45,1) .8s forwards; }
.gh-checkmark    { width:96px; height:96px; border-radius:50%; stroke-width:3; stroke:#16a34a; stroke-miterlimit:10; box-shadow:inset 0 0 0 #16a34a; animation:ghFill .4s ease-in-out .4s forwards, ghScale .3s ease-in-out .9s both; }
@keyframes ghFill { 100%{ box-shadow:inset 0 0 0 58px #16a34a; } }
.gh-email-note { display:flex; align-items:center; gap:12px; padding:16px 24px; background:#f0fdf4; border-top:1px solid #f5f0eb; }
.gh-email-note-icon { width:34px; height:34px; border-radius:9px; background:linear-gradient(135deg,#dcfce7,#bbf7d0); display:flex; align-items:center; justify-content:center; color:#16a34a; font-size:14px; flex-shrink:0; }
.gh-email-note span { font-size:13px; color:#374151; }

/* ═══════════════════════════════════════════════════
   GH PAYMENT RESULT — Shared styles (success + fail)
   Consistent with account page aesthetics
═══════════════════════════════════════════════════ */
.gh-result-section { padding: 56px 0 72px; min-height: calc(100vh - 220px); }

/* Anim */
.gh-result-anim { text-align: center; margin-bottom: 32px; }
.gh-check-wrap  { width: 96px; height: 96px; margin: 0 auto 18px; }
@keyframes ghStroke { 100%{ stroke-dashoffset:0; } }
@keyframes ghScale  { 0%,100%{transform:none} 50%{transform:scale3d(1.1,1.1,1)} }
.gh-result-headline { font-size: 28px; font-weight: 800; color: #1a1209; margin: 0 0 8px; }
.gh-result-sub { font-size: 15px; color: #7a6655; margin: 0; }

/* Card */
.gh-card {
  background: #fff; border-radius: 14px;
  border: 1px solid #f0ebe5; overflow: hidden;
  box-shadow: 0 4px 24px rgba(113,78,50,.07);
  margin-bottom: 20px;
}
.gh-card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 24px; border-bottom: 1px solid #f5f0eb; background: #fdf8f4;
}
.gh-card-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-bottom: 4px; }
.gh-card-order-num { font-size: 20px; font-weight: 800; color: #714e32; }

/* Status badges */
.gh-status {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 13px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
}
.gh-status-delivered { background: #dcfce7; color: #15803d; }
.gh-status-cancelled { background: #fee2e2; color: #dc2626; }

/* Details strip */
.gh-card-details { display: grid; grid-template-columns: repeat(3,1fr); border-bottom: 1px solid #f5f0eb; }
.gh-card-detail-item { padding: 14px 24px; }
.gh-card-detail-item:not(:last-child) { border-right: 1px solid #f5f0eb; }
.gh-card-dl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; display: block; margin-bottom: 4px; }
.gh-card-dv { font-size: 14px; font-weight: 600; color: #1a1209; }
.gh-card-total { color: #714e32; font-size: 16px; }

/* Sections */
.gh-card-section { padding: 20px 24px; border-bottom: 1px solid #f5f0eb; }
.gh-card-section:last-child { border-bottom: none; }
.gh-section-title {
  font-size: 13px; font-weight: 700; color: #1a1209;
  margin: 0 0 14px; display: flex; align-items: center; gap: 8px;
}
.gh-order-item-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f9f5f1; }
.gh-order-item-row:last-child { border-bottom: none; }
.gh-order-item-name { font-size: 14px; font-weight: 600; color: #1a1209; margin-bottom: 3px; }
.gh-order-item-meta { font-size: 12px; color: #9ca3af; }
.gh-order-item-total { font-size: 14px; font-weight: 700; color: #1a1209; }
.gh-addr-text { font-size: 13.5px; color: #4b5563; line-height: 1.6; }

/* Steps */
.gh-steps-card .gh-card-section { border-bottom: none; }
.gh-steps-row { display: flex; align-items: center; gap: 14px; }
.gh-step { flex: 1; text-align: center; }
.gh-step-num {
  width: 38px; height: 38px;
  background: linear-gradient(135deg, #714e32, #5a3c24);
  color: #fff; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 800; font-size: 15px; margin: 0 auto 10px;
}
.gh-step-body strong { display: block; font-size: 13px; font-weight: 700; color: #1a1209; }
.gh-step-body span   { font-size: 11.5px; color: #9ca3af; }
.gh-step-arrow { font-size: 16px; color: #d1d5db; flex-shrink: 0; }

/* Actions */
.gh-result-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.gh-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 12px 22px; border-radius: 9px;
  font-size: 14px; font-weight: 600; font-family: inherit;
  cursor: pointer; border: none; transition: all .18s; text-decoration: none; white-space: nowrap; flex: 1; justify-content: center;
}
.gh-btn-primary { background: #714e32; color: #fff; }
.gh-btn-primary:hover { background: #5a3c24; color: #fff; }
.gh-btn-outline { background: transparent; color: #714e32; border: 2px solid #c4956a; }
.gh-btn-outline:hover { background: #714e32; color: #fff; }
.gh-security-note { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280; justify-content: center; padding: 8px 0; }

@media (max-width: 767px) {
  .gh-result-headline { font-size: 22px; }
  .gh-card-details { grid-template-columns: 1fr; }
  .gh-card-detail-item:not(:last-child) { border-right: none; border-bottom: 1px solid #f5f0eb; }
  .gh-steps-row { flex-direction: column; }
  .gh-step-arrow { transform: rotate(90deg); }
  .gh-result-actions { flex-direction: column; }
}
</style>
<div class="wrapper ovh">
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

  <div class="body_content_wrapper position-relative">
    <section class="gh-result-section gh-result-success-bg">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8 col-xl-7">

            {{-- Animated checkmark --}}
            <div class="gh-result-anim">
              <div class="gh-check-wrap">
                <svg class="gh-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                  <circle class="gh-check-circle" cx="26" cy="26" r="25" fill="none"/>
                  <path class="gh-check-tick" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
              </div>
              <h1 class="gh-result-headline">Payment Successful!</h1>
              <p class="gh-result-sub">Thank you for shopping with {{ config('app.name') }}</p>
            </div>

            {{-- Order card --}}
            <div class="gh-card">

              {{-- Header --}}
              <div class="gh-card-header">
                <div>
                  <div class="gh-card-label">Order Number</div>
                  <div class="gh-card-order-num">{{ $order->order_number }}</div>
                </div>
                <span class="gh-status gh-status-delivered">
                  <i class="fas fa-check" style="font-size:9px;"></i> Paid
                </span>
              </div>

              {{-- Key details --}}
              <div class="gh-card-details">
                <div class="gh-card-detail-item">
                  <span class="gh-card-dl">Date</span>
                  <span class="gh-card-dv">{{ $order->created_at->format('M d, Y · h:i A') }}</span>
                </div>
                <div class="gh-card-detail-item">
                  <span class="gh-card-dl">Payment</span>
                  <span class="gh-card-dv">{{ ucfirst($order->payment_method) }}</span>
                </div>
                <div class="gh-card-detail-item">
                  <span class="gh-card-dl">Total</span>
                  <span class="gh-card-dv gh-card-total">₦{{ number_format($order->total, 2) }}</span>
                </div>
              </div>

              {{-- Items --}}
              <div class="gh-card-section">
                <h5 class="gh-section-title"><i class="fas fa-shopping-bag" style="color:#c4956a;"></i> Order Items</h5>
                @foreach($order->items as $item)
                <div class="gh-order-item-row">
                  <div class="gh-order-item-info">
                    <div class="gh-order-item-name">{{ $item->product_name }}</div>
                    <div class="gh-order-item-meta">Qty {{ $item->quantity }} × ₦{{ number_format($item->price, 2) }}</div>
                  </div>
                  <div class="gh-order-item-total">₦{{ number_format($item->total, 2) }}</div>
                </div>
                @endforeach
              </div>

              {{-- Shipping address --}}
              <div class="gh-card-section">
                <h5 class="gh-section-title">
                  <i class="fas fa-map-marker-alt" style="color:#c4956a;"></i> Shipping Address
                </h5>
                <div class="gh-addr-text">
                  {{ $order->shipping_address }},
                  {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_postal_code }},
                  {{ $order->shipping_country }}
                </div>
              </div>

              {{-- Email confirmation note --}}
              <div class="gh-email-note">
                <div class="gh-email-note-icon"><i class="fas fa-envelope"></i></div>
                <span>Confirmation email sent to <strong>{{ $order->customer_email }}</strong></span>
              </div>

            </div>{{-- /gh-card --}}

            {{-- What happens next --}}
            <div class="gh-card gh-steps-card">
              <div class="gh-card-section">
                <h5 class="gh-section-title" style="margin-bottom:20px;">
                  <i class="fas fa-route" style="color:#c4956a;"></i> What happens next?
                </h5>
                <div class="gh-steps-row">
                  <div class="gh-step">
                    <div class="gh-step-num">1</div>
                    <div class="gh-step-body">
                      <strong>Processing</strong>
                      <span>We're preparing your order</span>
                    </div>
                  </div>
                  <div class="gh-step-arrow"><i class="fas fa-chevron-right"></i></div>
                  <div class="gh-step">
                    <div class="gh-step-num">2</div>
                    <div class="gh-step-body">
                      <strong>Shipped</strong>
                      <span>You'll get tracking details</span>
                    </div>
                  </div>
                  <div class="gh-step-arrow"><i class="fas fa-chevron-right"></i></div>
                  <div class="gh-step">
                    <div class="gh-step-num">3</div>
                    <div class="gh-step-body">
                      <strong>Delivered</strong>
                      <span>3–7 business days</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Actions --}}
            <div class="gh-result-actions">
              <a href="{{ route('account.orders.show', $order->id) }}" class="gh-btn gh-btn-primary">
                <i class="fas fa-eye"></i> View Order Details
              </a>
              <a href="{{ route('product.index') }}" class="gh-btn gh-btn-outline">
                Continue Shopping
              </a>
            </div>

            <div class="gh-security-note">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Your payment information is secure and encrypted. We never store card details.
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
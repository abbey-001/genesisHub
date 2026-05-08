{{-- resources/views/partials/features.blade.php --}}
<section class="gh-features">
  <div class="container">
    <div class="gh-features-grid">

      <div class="gh-feature-item">
        <div class="gh-feature-icon"><span class="flaticon-shield"></span></div>
        <div class="gh-feature-text">
          <h4>Money-Back Guarantee</h4>
          <p>30-day hassle-free returns on all orders</p>
        </div>
      </div>

      <div class="gh-feature-divider"></div>

      <div class="gh-feature-item">
        <div class="gh-feature-icon"><span class="flaticon-headphones"></span></div>
        <div class="gh-feature-text">
          <h4>24/7 Online Support</h4>
          <p>Our team is here for you around the clock</p>
        </div>
      </div>

      <div class="gh-feature-divider"></div>

      <div class="gh-feature-item">
        <div class="gh-feature-icon"><span class="flaticon-credit-card"></span></div>
        <div class="gh-feature-text">
          <h4>Flexible Payment</h4>
          <p>Pay with cards, bank transfer, or USSD</p>
        </div>
      </div>

      <div class="gh-feature-divider"></div>

      <div class="gh-feature-item">
        <div class="gh-feature-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <div class="gh-feature-text">
          <h4>Secure Checkout</h4>
          <p>256-bit SSL on every transaction</p>
        </div>
      </div>

    </div>
  </div>
</section>

<style>
.gh-features { padding: 0 0 32px; }
.gh-features-grid {
  display: flex; align-items: center;
  background: #fff;
  border: 1px solid #f0ebe5;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(113,78,50,.06);
}
.gh-feature-item {
  flex: 1; display: flex; align-items: center;
  gap: 14px; padding: 22px 28px;
  transition: background .2s;
}
.gh-feature-item:hover { background: #fdf8f4; }
.gh-feature-divider { width:1px; height:48px; background:#f0ebe5; flex-shrink:0; }
.gh-feature-icon {
  width:46px; height:46px;
  background: linear-gradient(135deg,#f5ede5,#e8d5c4);
  border-radius:10px;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; color:#714e32; font-size:20px;
}
.gh-feature-text h4 { font-size:14px; font-weight:700; color:#1a1209; margin:0 0 3px; white-space:nowrap; }
.gh-feature-text p  { font-size:12px; color:#7a6655; margin:0; line-height:1.4; }
@media(max-width:991px){
  .gh-features-grid{flex-wrap:wrap;}
  .gh-feature-item{flex:0 0 50%;min-width:50%;}
  .gh-feature-divider:nth-child(4){display:none;}
  .gh-feature-divider{width:100%;height:1px;}
}
@media(max-width:575px){
  .gh-feature-item{flex:0 0 100%;padding:18px 20px;}
  .gh-feature-divider{display:block!important;}
}
</style>
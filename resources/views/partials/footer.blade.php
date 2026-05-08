{{-- Newsletter flash messages --}}
@if(session('newsletter_success') || session('newsletter_info'))
  <div id="gh-newsletter-toast" class="gh-toast {{ session('newsletter_success') ? 'gh-toast--success' : 'gh-toast--info' }}">
    <p style="color:white;">{{ session('newsletter_success') ?? session('newsletter_info') }}</p>
  </div>

  <style>
    .gh-toast {
      position: fixed; bottom: 28px; left: 50%; transform: translateX(-50%);
      display: flex; align-items: center; gap: 12px;
      padding: 14px 20px; border-radius: 12px; z-index: 9999;
      font-size: 14px; font-weight: 500; color: #fff;
      box-shadow: 0 8px 30px rgba(0,0,0,.25); max-width: 90vw;
      animation: gh-slide-up .35s ease;
    }
    .gh-toast--success { background: #714e32; }
    .gh-toast--info    { background: #4b5563; }
    .gh-toast p        { margin: 0; flex: 1; }
    .gh-toast button   { background: none; border: none; color: rgba(255,255,255,.7); cursor: pointer; font-size: 16px; line-height:1; }
    .gh-toast button:hover { color: #fff; }
    @keyframes gh-slide-up { from { opacity:0; transform: translateX(-50%) translateY(20px); } to { opacity:1; transform: translateX(-50%) translateY(0); } }
  </style>

  <script>
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      const t = document.getElementById('gh-newsletter-toast');
      if (t) t.remove();
    }, 5000);
  </script>
@endif
<section class="gh-footer">

  {{-- Newsletter bar --}}
  <div class="gh-footer-newsletter">
    <div class="container">
      <div class="gh-nl-inner">
        <div class="gh-nl-copy">
          <span class="gh-nl-icon"><span class="flaticon-email-1"></span></span>
          <div>
            <h4>Get 20% off your first order</h4>
            <p>Subscribe for exclusive deals, new arrivals &amp; style inspiration.</p>
          </div>
        </div>
        <form class="gh-nl-form" action="{{ route('newsletter.subscribe') }}" method="POST">
          @csrf
          <input type="email" name="email" placeholder="Your email address" required>
          <button type="submit">Subscribe</button>
        </form>
      </div>
    </div>
  </div>

  {{-- Main footer --}}
  <div class="gh-footer-main">
    <div class="container">
      <div class="gh-footer-cols">

        {{-- Contact --}}
        <div class="gh-footer-col">
          <h5>Contact Us</h5>
          <div class="gh-contact-item">
            <span class="gh-contact-icon"><span class="flaticon-phone-call"></span></span>
            <div>
              <div class="gh-contact-label">Mon–Fri, 8am–9pm</div>
              <a href="tel:+11234567890">+1 (207) 208 6919</a>
            </div>
          </div>
          <div class="gh-contact-item">
            <span class="gh-contact-icon"><span class="flaticon-email"></span></span>
            <div>
              <div class="gh-contact-label">Need help with an order?</div>
              <a href="mailto:support@genesishub.com">support@genesishub.com</a>
            </div>
          </div>
        </div>

        {{-- About --}}
        <div class="gh-footer-col">
          <h5>GenesisHub</h5>
          <ul>
            <li><a href="{{ route('about') }}">About Us</a></li>
            <li><a href="{{ route('privacy') }}">Privacy Policy</a></li>
            <li><a href="{{ route('terms') }}">Terms of Service</a></li>
            <li><a href="{{ route('shop.index') }}">All Shops</a></li>
          </ul>
        </div>

        {{-- Support --}}
        <div class="gh-footer-col">
          <h5>Customer Support</h5>
          <ul>
            <li><a href="{{ route('contact') }}">Contact Us</a></li>
            <li><a href="{{ route('help') }}">Help Centre</a></li>
            <li><a href="{{ route('faq') }}">FAQ</a></li>
            <li><a href="{{ route('cart.index') }}">Track My Order</a></li>
          </ul>
        </div>

        {{-- Social + Payment --}}
        <div class="gh-footer-col">
          <h5>Follow Us</h5>
          <div class="gh-socials">
            <a href="#" class="gh-social" target="_blank"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="gh-social" target="_blank"><i class="fab fa-x-twitter"></i></a>
            <a href="#" class="gh-social" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="#" class="gh-social" target="_blank"><i class="fab fa-linkedin-in"></i></a>
          </div>

          <h5 style="margin-top:20px;">Mobile App</h5>
          <div class="gh-apps">
            <a href="#" class="gh-app-btn"><span class="flaticon-apple"></span> iOS</a>
            <a href="#" class="gh-app-btn"><span class="flaticon-android"></span> Android</a>
          </div>

          <h5 style="margin-top:20px;">We Accept</h5>
          <div class="gh-payments">
            <img src="{{ asset('public/images/resource/visa-card.png') }}" alt="Visa">
            <img src="{{ asset('public/images/resource/master-card.png') }}" alt="Mastercard">
            <img src="{{ asset('public/images/resource/apple-pay.png') }}" alt="Apple Pay">
            <img src="{{ asset('public/images/resource/paypal.png') }}" alt="PayPal">
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- Bottom bar --}}
  <div class="gh-footer-bottom">
    <div class="container">
      <div class="gh-footer-bottom-inner">
        <p class="gh-copyright">© {{ date('Y') }} GenesisHub · All Rights Reserved</p>
        <div class="gh-footer-selects">
          <select class="gh-select" id="currency-selector">
            <option value="NGN" {{ session('currency','NGN')=='NGN'?'selected':'' }}>₦ NGN</option>
            <option value="USD" {{ session('currency')=='USD'?'selected':'' }}>$ USD</option>
            <option value="EUR" {{ session('currency')=='EUR'?'selected':'' }}>€ EUR</option>
            <option value="GBP" {{ session('currency')=='GBP'?'selected':'' }}>£ GBP</option>
          </select>
          <select class="gh-select" id="language-selector">
            <option value="en" {{ app()->getLocale()=='en'?'selected':'' }}>English</option>
            <option value="fr" {{ app()->getLocale()=='fr'?'selected':'' }}>French</option>
            <option value="es" {{ app()->getLocale()=='es'?'selected':'' }}>Spanish</option>
          </select>
        </div>
      </div>
    </div>
  </div>

</section>

<style>
/* ─── GenesisHub Footer ──────────────────────────────────── */
.gh-footer { background:#1a1209; color:#c4ab94; font-family:inherit; }

/* Newsletter */
.gh-footer-newsletter { background:#714e32; padding:28px 0; }
.gh-nl-inner { display:flex; align-items:center; justify-content:space-between; gap:24px; flex-wrap:wrap; }
.gh-nl-copy { display:flex; align-items:center; gap:14px; }
.gh-nl-icon { width:44px; height:44px; background:rgba(255,255,255,.15); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; flex-shrink:0; }
.gh-nl-copy h4 { font-size:16px; font-weight:700; color:#fff; margin:0 0 2px; }
.gh-nl-copy p  { font-size:13px; color:rgba(255,255,255,.8); margin:0; }
.gh-nl-form { display:flex; gap:0; border-radius:8px; overflow:hidden; flex-shrink:0; }
.gh-nl-form input { width:280px; padding:11px 16px; border:none; font-size:13px; background:#fff; color:#1a1209; outline:none; }
.gh-nl-form input::placeholder { color:#9ca3af; }
.gh-nl-form button { padding:11px 20px; background:#1a1209; color:#fff; border:none; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; white-space:nowrap; }
.gh-nl-form button:hover { background:#000; }

/* Main */
.gh-footer-main { padding:50px 0 40px; }
.gh-footer-cols { display:grid; grid-template-columns:1.4fr 1fr 1fr 1.2fr; gap:40px; }
.gh-footer-col h5 { font-size:13px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.7px; margin:0 0 18px; }
.gh-footer-col ul { list-style:none; margin:0; padding:0; }
.gh-footer-col ul li { margin-bottom:10px; }
.gh-footer-col ul li a { font-size:13px; color:#c4ab94; text-decoration:none; transition:color .15s; }
.gh-footer-col ul li a:hover { color:#f5ede5; }

.gh-contact-item { display:flex; gap:12px; margin-bottom:16px; }
.gh-contact-icon { width:34px; height:34px; background:rgba(255,255,255,.07); border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:16px; color:#c4956a; flex-shrink:0; margin-top:2px; }
.gh-contact-label { font-size:11px; color:#7a6655; margin-bottom:2px; }
.gh-contact-item a { font-size:13px; color:#c4ab94; text-decoration:none; transition:color .15s; }
.gh-contact-item a:hover { color:#fff; }

.gh-socials { display:flex; gap:8px; margin-bottom:4px; }
.gh-social { width:34px; height:34px; background:rgba(255,255,255,.07); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#c4ab94; font-size:14px; text-decoration:none; transition:all .15s; }
.gh-social:hover { background:#714e32; color:#fff; }

.gh-apps { display:flex; gap:8px; }
.gh-app-btn { display:flex; align-items:center; gap:6px; padding:7px 14px; background:rgba(255,255,255,.07); border-radius:8px; color:#c4ab94; font-size:12px; font-weight:600; text-decoration:none; transition:all .15s; }
.gh-app-btn:hover { background:#714e32; color:#fff; }
.gh-app-btn span { font-size:16px; }

.gh-payments { display:flex; gap:8px; flex-wrap:wrap; margin-top:4px; }
.gh-payments img { height:22px; opacity:.65; filter:grayscale(1) brightness(2); transition:opacity .15s; border-radius:3px; }
.gh-payments img:hover { opacity:1; }

/* Bottom */
.gh-footer-bottom { border-top:1px solid rgba(255,255,255,.07); padding:18px 0; }
.gh-footer-bottom-inner { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; }
.gh-copyright { font-size:12px; color:#7a6655; margin:0; }
.gh-footer-selects { display:flex; gap:10px; }
.gh-select { background:rgba(255,255,255,.07); border:1px solid rgba(255,255,255,.1); color:#c4ab94; font-size:12px; padding:6px 10px; border-radius:6px; outline:none; cursor:pointer; }
.gh-select:hover { border-color:rgba(255,255,255,.2); }

@media(max-width:991px){ .gh-footer-cols{ grid-template-columns:1fr 1fr; gap:30px; } }
@media(max-width:575px){
  .gh-footer-cols{ grid-template-columns:1fr; gap:24px; }
  .gh-nl-inner{ flex-direction:column; align-items:flex-start; }
  .gh-nl-form{ width:100%; }
  .gh-nl-form input{ width:100%; }
  .gh-footer-bottom-inner{ flex-direction:column; align-items:flex-start; }
}
</style>

@push('scripts')
<script>
$('#currency-selector').on('change',function(){
  $.post('{{ route("currency.change") }}',{_token:'{{ csrf_token() }}',currency:$(this).val()},function(){ location.reload(); });
});
$('#language-selector').on('change',function(){
  $.post('{{ route("language.change") }}',{_token:'{{ csrf_token() }}',language:$(this).val()},function(){ location.reload(); });
});
</script>
@endpush
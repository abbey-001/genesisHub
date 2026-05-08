@extends('layouts.app')

@section('content')
<style>
/* ════════════════════════════════════════
   LISTING PAGE — SCOPED STYLES
   Every rule is prefixed with .listing-page
   so nothing bleeds into the site header,
   navigation, or footer.
════════════════════════════════════════ */

/* ── Page shell ─────────────────────────────────── */
.listing-page { background: #f3f5f6; min-height: 100vh; }

/* ── Page title strip ───────────────────────────── */
.listing-page .listing-header { background:#fff; border-bottom:1px solid #ebebeb; padding:22px 0 18px; margin-bottom:24px; }
.listing-page .listing-header h1 { font-size:22px; font-weight:700; color:#0a0a0a; margin:0 0 4px; }
.listing-page .listing-header p  { font-size:14px; color:#626974; margin:0; }

/* ── Toolbar ────────────────────────────────────── */
.listing-page .listing-toolbar  { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.listing-page .toolbar-left  { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.listing-page .toolbar-right { display:flex; align-items:center; gap:10px; }

/*
 * FILTER TOGGLE BUTTON
 * ─────────────────────────────────────────────────
 * We deliberately do NOT use the theme's .btn class
 * because that class applies:
 *   overflow: hidden  → clips our icon
 *   position: relative + z-index:1 → stacking issues
 *   border-top: none  → breaks our pill border
 *   ::before pseudo   → animated bg that covers the click area
 * All of these are incompatible with a toggle pill button.
 * We use a standalone class and explicitly override anything
 * Bootstrap or the theme might cascade onto a <button>.
 */
.listing-page .btn-filter-toggle {
    display:inline-flex !important;
    align-items:center !important;
    gap:7px;
    background:#fff;
    border:1.5px solid #ebebeb !important;
    border-top:1.5px solid #ebebeb !important;
    color:#0a0a0a;
    padding:9px 18px;
    border-radius:50px;
    font-size:14px; font-weight:500;
    font-family:"Jost",sans-serif;
    cursor:pointer;
    transition:all .2s;
    white-space:nowrap;
    /* Undo .btn overrides */
    overflow:visible !important;
    position:static !important;
    z-index:auto !important;
    text-align:left !important;
    letter-spacing:0 !important;
    line-height:1.5 !important;
    text-transform:none !important;
}
.listing-page .btn-filter-toggle:hover {
    border-color:#714e32 !important;
    color:#714e32;
    background:#f5ede5;
    box-shadow:none;
}
/* Disable the theme's sliding pseudo-element animation */
.listing-page .btn-filter-toggle::before,
.listing-page .btn-filter-toggle::after { display:none !important; content:none !important; }

.listing-page .filter-badge {
    background:#714e32; color:#fff;
    font-size:11px; font-weight:700;
    width:18px; height:18px;
    border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
}

/* Sort dropdown */
.listing-page .sort-select-wrap { position:relative; }
.listing-page .sort-select-wrap select {
    appearance:none; -webkit-appearance:none;
    background:#fff; border:1.5px solid #ebebeb; border-radius:50px;
    padding:9px 36px 9px 16px; font-size:14px; color:#0a0a0a;
    cursor:pointer; font-weight:500; font-family:"Jost",sans-serif;
    transition:border .2s;
}
.listing-page .sort-select-wrap select:focus { border-color:#714e32; outline:none; }
.listing-page .sort-select-wrap::after { content:"▾"; position:absolute; right:14px; top:50%; transform:translateY(-50%); pointer-events:none; color:#626974; font-size:13px; }

/* Results count */
.listing-page .results-count { font-size:14px; color:#626974; }
.listing-page .results-count strong { color:#0a0a0a; font-weight:600; }

/* ── Active filter chips ─────────────────────────── */
.listing-page .active-filters-bar { background:#f5ede5; border:1px solid #e8d5c0; border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.listing-page .active-filters-label { font-size:13px; font-weight:600; color:#5a3c24; white-space:nowrap; }
.listing-page .filter-chip { display:inline-flex; align-items:center; gap:5px; background:#fff; border:1px solid #d4b896; color:#5a3c24; padding:4px 10px 4px 12px; border-radius:50px; font-size:13px; font-weight:500; }
.listing-page .filter-chip button { background:none; border:none; cursor:pointer; padding:0; color:#c4956a; font-size:15px; line-height:1; transition:color .15s; display:flex; align-items:center; }
.listing-page .filter-chip button:hover { color:#dc2626; }
.listing-page .btn-clear-all { margin-left:auto; background:none; border:none; cursor:pointer; font-size:13px; color:#714e32; font-weight:600; text-decoration:underline; padding:0; }
.listing-page .btn-clear-all:hover { color:#5a3c24; }

/* ── Product grid ───────────────────────────────── */
.listing-page #product-grid-wrapper { position:relative; transition:opacity .25s; }
.listing-page #product-grid-wrapper.loading { opacity:.45; pointer-events:none; }

.listing-page .products-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
@media (max-width:1399px) { .listing-page .products-grid { grid-template-columns:repeat(3,1fr); } }
@media (max-width:991px)  { .listing-page .products-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:480px)  { .listing-page .products-grid { grid-template-columns:repeat(2,1fr); gap:10px; } }

.listing-page .grid-loading-overlay { display:none; position:absolute; inset:0; background:rgba(243,245,246,.75); border-radius:10px; z-index:10; align-items:center; justify-content:center; flex-direction:column; gap:12px; }
.listing-page #product-grid-wrapper.loading .grid-loading-overlay { display:flex; }
.listing-page .grid-spinner { width:40px; height:40px; border:3px solid #e5e7eb; border-top-color:#714e32; border-radius:50%; animation:lp-spin .8s linear infinite; }
@keyframes lp-spin { to { transform:rotate(360deg); } }

/* ── Product card ───────────────────────────────── */
.listing-page .product-card { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); transition:transform .22s,box-shadow .22s; display:flex; flex-direction:column; position:relative; }
.listing-page .product-card:hover { transform:translateY(-4px); box-shadow:0 4px 16px rgba(0,0,0,.10); }

.listing-page .card-img-wrap { position:relative; overflow:hidden; background:#f3f4f6; aspect-ratio:1/1; }
.listing-page .card-img-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
.listing-page .product-card:hover .card-img-wrap img { transform:scale(1.04); }

.listing-page .badge-wrap { position:absolute; top:10px; left:10px; display:flex; flex-direction:column; gap:5px; z-index:2; }
.listing-page .badge-discount { background:#dc2626; color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; }
.listing-page .badge-stock    { background:#f59e0b; color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; }
.listing-page .badge-oos      { background:#6b7280; color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; }

.listing-page .card-actions { position:absolute; top:10px; right:10px; display:flex; flex-direction:column; gap:6px; z-index:2; opacity:0; transform:translateX(8px); transition:all .22s; }
.listing-page .product-card:hover .card-actions { opacity:1; transform:translateX(0); }
.listing-page .card-action-btn { width:34px; height:34px; background:#fff; border:none; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.12); color:#626974; font-size:14px; transition:all .18s; text-decoration:none; }
.listing-page .card-action-btn:hover { background:#714e32; color:#fff; }

.listing-page .card-cart-btn { position:absolute; bottom:0; left:0; right:0; background:#714e32; color:#fff; border:none; padding:10px; font-size:13px; font-weight:600; cursor:pointer; opacity:0; transform:translateY(100%); transition:all .22s; letter-spacing:.3px; text-align:center; text-decoration:none; display:block; }
.listing-page .product-card:hover .card-cart-btn { opacity:1; transform:translateY(0); }
.listing-page .card-cart-btn:hover { background:#5a3c24; color:#fff; }

.listing-page .card-body { padding:13px 13px 14px; flex:1; display:flex; flex-direction:column; }
.listing-page .card-brand { font-size:11px; font-weight:600; color:#c4956a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
.listing-page .card-name a { font-size:14px; font-weight:600; color:#0a0a0a; text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; line-height:1.4; transition:color .15s; }
.listing-page .card-name a:hover { color:#714e32; }

/* Condition / Company / Perfect tags row */
.listing-page .card-meta-tags { display:flex; flex-wrap:wrap; gap:4px; margin:5px 0 4px; }
.listing-page .tag-condition {
    display:inline-flex; align-items:center;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:4px;
    letter-spacing:.3px; text-transform:uppercase;
}
.listing-page .tag-company {
    display:inline-flex; align-items:center; gap:3px;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:4px;
    background:#eff6ff; color:#1d4ed8;
    letter-spacing:.2px; text-transform:uppercase;
}
.listing-page .tag-perfect {
    display:inline-flex; align-items:center; gap:3px;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:4px;
    background:#fff7ed; color:#c2410c;
    letter-spacing:.2px; text-transform:uppercase;
}
/* Perfect rating image overlay badge */
.listing-page .badge-perfect {
    position:absolute; top:10px; right:10px;
    display:flex; align-items:center; gap:2px;
    background:rgba(250,204,21,.93);
    color:#78350f;
    font-size:11px; font-weight:800;
    padding:3px 8px; border-radius:20px;
    backdrop-filter:blur(4px);
    z-index:3;
    box-shadow:0 1px 4px rgba(0,0,0,.12);
    pointer-events:none;
}

.listing-page .card-rating { display:flex; align-items:center; gap:5px; margin:7px 0; }
.listing-page .lp-stars { display:flex; gap:1px; }
.listing-page .lp-stars i { font-size:11px; color:#f59e0b; }
.listing-page .lp-stars i.empty { color:#d1d5db; }
.listing-page .rating-count { font-size:12px; color:#626974; }
.listing-page .card-price { margin-top:auto; padding-top:8px; display:flex; align-items:baseline; gap:7px; flex-wrap:wrap; }
.listing-page .price-current  { font-size:18px; font-weight:700; color:#5a3c24; }
.listing-page .price-original { font-size:13px; color:#9ca3af; text-decoration:line-through; }
.listing-page .price-save     { font-size:11px; color:#16a34a; font-weight:600; background:#dcfce7; padding:2px 6px; border-radius:4px; }
.listing-page .card-sold      { font-size:11px; color:#626974; margin-top:5px; }
.listing-page .card-sold i    { color:#f87171; margin-right:2px; }

/* ── Empty state ─────────────────────────────────── */
.listing-page .empty-state { text-align:center; padding:80px 20px; grid-column:1/-1; }
.listing-page .empty-state-icon { width:72px; height:72px; background:#f5ede5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:28px; color:#c4956a; }
.listing-page .empty-state h4 { font-size:18px; color:#0a0a0a; margin-bottom:8px; }
.listing-page .empty-state p  { color:#626974; font-size:14px; margin-bottom:20px; }

/* ── Pagination ──────────────────────────────────── */
.listing-page .listing-pagination { margin-top:32px; display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:6px; }
.listing-page .pg-btn { min-width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border:1.5px solid #ebebeb; border-radius:6px; background:#fff; color:#0a0a0a; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none; padding:0 10px; font-family:"Jost",sans-serif; }
.listing-page .pg-btn:hover    { border-color:#714e32; color:#714e32; background:#f5ede5; }
.listing-page .pg-btn.active   { background:#714e32; border-color:#714e32; color:#fff; font-weight:700; }
.listing-page .pg-btn.disabled { opacity:.4; pointer-events:none; }
.listing-page .pg-info         { font-size:13px; color:#626974; margin-top:12px; text-align:center; width:100%; }

@media (max-width:576px) {
    .listing-page .listing-toolbar { flex-direction:column; align-items:stretch; }
    .listing-page .toolbar-left, .listing-page .toolbar-right { justify-content:space-between; }
}
.best_item_slider_shop_lising_page .owl-item{
    padding: 0 12px;
}
</style>
<div class="wrapper listing-page ovh bgc-gmart-gray">
  <div class="preloader"></div>
  
  {{-- Desktop Header - Shows only on desktop --}}
  @include('partials.header')
  
  {{-- Main Navigation --}}
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs])
  
  <div class="body_content_wrapper position-relative pt30">
    
    {{-- Hero Banners Section --}}
    <section class="home-one">
      <div class="container maxw1800">
        <div class="row">
          <div class="col-xl-8 col-xxl-9">
            <div class="banner_one home1_style title_wa home4_main_banner mb30">
              <div class="thumb"><img src="{{ asset('public/images/banner/home4-hero-banner.png') }}" alt="Banner"></div>
              <div class="details">
                <h3 class="title">MacBook Pro</h3>
                <h4 class="subtitle mb20">Supercharged for pros</h4>
                <p class="para heading-color mb20">from <span class="fw500">₦2,200.</span></p>
                <a href="/products" class="btn btn-thm">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="col-xl-4 col-xxl-3">
            {{-- Side banners --}}
            <div class="banner_one home1_style title_wa mb30">
              <div class="thumb"><img class="float-end" src="{{ asset('public/images/banner/banner-iphone.png') }}" alt="banner iphone"></div>
              <div class="details">
                <p class="para text-thm1">Starting from <span class="fw500">₦899.</span></p>
                <h3 class="title">iPhone 12 Pro<br>128GB</h3>
                <a href="{{ route('category.show', 'electronics') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
            <div class="banner_one home1_style title_wa mb30">
              <div class="thumb"><img class="float-end" src="{{ asset('public/images/banner/banner-belt.png') }}" alt="banner belt"></div>
              <div class="details">
                <p class="para text-thm1">Starting from <span class="fw500">₦899.</span></p>
                <h3 class="title">Big Discount<br>on Belts</h3>
                <a href="{{ route('category.show', 'accessories') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
          </div>
        </div>
        
        {{-- Bottom 4 banners grid --}}
        <div class="row">
          <div class="col-lg-6 col-xl-3">
            <div class="banner_one home1_style title_wa mb30">
              <div class="thumb"><img class="float-end" src="{{ asset('public/images/banner/toilet-paper.png') }}" alt="toilet paper"></div>
              <div class="details">
                <p class="para text-thm1">Starting from <span class="fw500">₦899.</span></p>
                <h3 class="title">Special Discounts<br>on Toilet Paper</h3>
                <a href="{{ route('category.show', 'grocery') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-3">
            <div class="banner_one home1_style title_wa mb30">
              <div class="thumb"><img class="float-end" src="{{ asset('public/images/banner/shoe.png') }}" alt="shoe"></div>
              <div class="details">
                <p class="para text-thm1">Starting from <span class="fw500">₦899.</span></p>
                <h3 class="title">Shoes 20%<br>Off</h3>
                <a href="{{ route('category.show', 'clothing') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-3">
            <div class="banner_one home1_style title_wa mb30">
              <div class="thumb"><img class="float-end" src="{{ asset('public/images/banner/bag.png') }}" alt="bag"></div>
              <div class="details">
                <p class="para text-thm1">Starting from <span class="fw500">₦899.</span></p>
                <h3 class="title">Super Deal in<br>Bags</h3>
                <a href="{{ route('category.show', 'accessories') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-xl-3">
            <div class="banner_one home1_style title_wa">
              <div class="thumb"><img class="float-end" src="{{ asset('public/images/banner/women-sports-product.png') }}" alt="women sports product"></div>
              <div class="details">
                <p class="para text-thm1">Starting from <span class="fw500">₦899.</span></p>
                <h3 class="title">Women's Sports<br>Products</h3>
                <a href="{{ route('category.show', 'sports') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    {{-- Top Shops Section --}}
    <section class="explore-popular-brands pb30 pt20">
      <div class="container-fluid maxw1800 p-4 bgc-white bdrs6">
        <div class="row bb1">
          <div class="col-sm-6">
            <div class="main-title text-center text-sm-start mb-3">
              <h2>Top Shops</h2>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="main-title text-center text-sm-end">
              <a class="title_more_btn mt10" href="{{ route('shop.index') }}">See All</a>
            </div>
          </div>
        </div>
        <div class="row mt30">
          <div class="col-lg-12">
            <div class="shop_item_10grid_slider slider_dib_sm nav_none dots_none owl-theme owl-carousel">
              @forelse($topShops as $shop)
              <div class="item">
                <a href="{{ route('seller.shop', $shop->slug) }}">
                  <div class="iconbox home4_style">
                    <div class="icon">
                      <img src="{{ asset('public/storage/'.$shop->logo) }}" alt="{{ $shop->name }}">
                    </div>
                    <div class="details">
                      <h5 class="title">{{ $shop->name ?? 'Shop Name' }}</h5>
                      <p class="para text-muted">{{ $shop->products_count }} products</p>
                    </div>
                  </div>
                </a>
              </div>
              @empty
              <div class="col-12 text-center py-5">
                <p>No shops available at the moment.</p>
              </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </section>
    
    {{-- Best Sellers Section --}}
    <section class="featured-product pt0">
      <div class="container-fluid maxw1800 p-4 bgc-white bdrs6">
        <div class="row">
          <div class="col-lg-6">
            <div class="main-title text-center text-lg-start">
              <h2>Best seller in the last month</h2>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-12">
            <div class="best_item_slider_shop_lising_page shop_item_6grid_slider dots_none owl-theme owl-carousel">
              @forelse($bestSellers as $product)
                @include('partials.product-card', ['product' => $product])
              @empty
              <div class="col-12 text-center py-5">
                <p>No products available.</p>
              </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </section>
    
    {{-- Electronics Products Section --}}
    @if($electronicsProducts->isNotEmpty())
    <section class="clothing-product pt0">
      <div class="container-fluid maxw1800 p-4 bgc-white bdrs6">
        <div class="row bb1">
          <div class="col-md-6">
            <div class="main-title text-center text-md-start mb20">
              <h2 class="mb0">Electronics products</h2>
            </div>
          </div>
        </div>
        <div class="row mt30">
          <div class="col-sm-5 col-lg-4 col-xl-2">
            <div class="banner_one home1_style color2 home4_style twoimg h450">
              <div class="thumb t0"><img class="h100p" src="{{ asset('public/images/banner/banner7-home4.png') }}" alt="Electronics"></div>
              <div class="details">
                <p class="para color-light-blue fw500">From $1299</p>
                <h3 class="title">Beats Studio Buds</h3>
                <a href="{{ route('category.show', 'electronics') }}" class="shop_btn">Shop Now</a>
              </div>
            </div>
          </div>
          @foreach($electronicsProducts as $product)
          <div class="col-sm-7 col-lg-4 col-xl-2">
            @include('partials.product-card', ['product' => $product])
          </div>
          @endforeach
        </div>
      </div>
    </section>
    @endif
    
    {{-- Other sections remain the same... --}}
    @include('partials.recently-viewed', ['recentlyViewed' => $recentlyViewed])

    {{-- Features Section --}}
    @include('partials.features')
    
    {{-- Footer --}}
    @include('partials.footer')
    
    <!--<a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>-->
  </div>
</div>
@endsection

@push('scripts')
<script>
  // Initialize carousels after page load
  $(document).ready(function() {
    $('.owl-carousel').owlCarousel({
      loop: true,
      margin: 30,
      nav: true,
      dots: false,
      responsive: {
        0: { items: 2 },
        600: { items: 3 },
        1000: { items: 6 }
      }
    });
    
    $('.best_item_slider_shop_lising_page').owlCarousel({
    loop: true,
    margin: 24, // space between cards
    nav: true,
    dots: false,
    responsive:{
        0:{ items:1 },
        576:{ items:2 },
        768:{ items:3 },
        992:{ items:4 },
        1200:{ items:6 }
    }
});
  });
  

  
</script>
@endpush
{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>{{ config('app.name', 'GenesisHub') }} - @yield('title', 'E-commerce Store')</title>

  <!-- SEO Meta Tags -->
  <meta name="description" content="@yield('meta_description', 'Shop the latest products from top brands at GenesisHub. Free shipping on orders over $200.')">
  <meta name="keywords" content="@yield('meta_keywords', 'ecommerce, online shopping, electronics, fashion, home goods')">
  <meta name="author" content="GenesisHub">

  <!-- Open Graph -->
  <meta property="og:type" content="website">
  <meta property="og:url" content="{{ url()->current() }}">
  <meta property="og:title" content="@yield('og_title', config('app.name'))">
  <meta property="og:description" content="@yield('og_description', 'Shop the latest products from top brands')">
  <meta property="og:image" content="@yield('og_image', asset('public/image/auth-logo.png'))">

  <!-- Twitter -->
  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="{{ url()->current() }}">
  <meta property="twitter:title" content="@yield('twitter_title', config('app.name'))">
  <meta property="twitter:description" content="@yield('twitter_description', 'Shop the latest products from top brands')">
  <meta property="twitter:image" content="@yield('twitter_image', asset('public/image/auth-logo.png'))">

  <!-- Favicon -->
  <link href="{{ asset('public/image/auth-logo.png') }}" sizes="128x128" rel="shortcut icon" type="image/x-icon" />
  <link href="{{ asset('public/image/auth-logo.png') }}" sizes="60x60"  rel="apple-touch-icon">
  <link href="{{ asset('public/image/auth-logo.png') }}" sizes="72x72"  rel="apple-touch-icon">
  <link href="{{ asset('public/image/auth-logo.png') }}" sizes="114x114" rel="apple-touch-icon">
  <link href="{{ asset('public/image/auth-logo.png') }}" sizes="180x180" rel="apple-touch-icon">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500&family=Poppins:wght@700&display=swap" rel="stylesheet">

  <!-- CSS Files -->
  <link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/ace-responsive-menu.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/menu.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/fontawesome.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/fontawesome-free.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/flaticon.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/bootstrap-select.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/animate.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/jquery-ui.min.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/slider.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('public/css/responsive.css') }}">

  
<style>
/* Cart sidebar item styles */
.gh-csi { padding: 0 !important; border-bottom: 1px solid #f5f0eb !important; }
.gh-csi:last-child { border-bottom: none !important; }

.gh-csi-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 16px;
  position: relative;
}

.gh-csi-img-link { flex-shrink: 0; }
.gh-csi-img {
  width: 60px; height: 60px;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #f0ebe5;
}

.gh-csi-info { flex: 1; min-width: 0; }
.gh-csi-name {
  display: block;
  font-size: 13px;
  font-weight: 600;
  color: #1a1209;
  text-decoration: none;
  line-height: 1.35;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  transition: color .15s;
}
.gh-csi-name:hover { color: #714e32; }

/* Variant badge */
.gh-csi-variant {
  font-size: 11px;
  font-weight: 600;
  color: #714e32;
  background: #fdf1e8;
  border: 1px solid #e8d5c4;
  border-radius: 4px;
  padding: 2px 6px;
  display: inline-block;
  margin-bottom: 6px;
  max-width: 100%;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.gh-csi-controls { display: flex; align-items: center; justify-content: space-between; }

.gh-csi-qty {
  display: flex;
  align-items: center;
  gap: 0;
  background: #f9f5f1;
  border-radius: 6px;
  border: 1px solid #f0ebe5;
  overflow: hidden;
}
.gh-csi-btn {
  width: 26px; height: 26px;
  border: none; background: transparent;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #714e32;
  transition: background .15s;
}
.gh-csi-btn:hover { background: #f0ebe5; }
.gh-csi-num { font-size: 12px; font-weight: 700; color: #1a1209; padding: 0 8px; min-width: 24px; text-align: center; }

.gh-csi-price { font-size: 13px; font-weight: 700; color: #714e32; }

.gh-csi-remove {
  position: absolute; top: 10px; right: 12px;
  width: 22px; height: 22px;
  background: #fef2f2; border: none; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #dc2626;
  opacity: 0; transition: opacity .2s;
}
.gh-csi-wrap:hover .gh-csi-remove { opacity: 1; }

/* Total row */
.gh-csi-total {
  display: flex !important;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px !important;
  background: #fdf8f4;
  border-top: 2px solid #f0ebe5 !important;
  font-size: 15px;
  font-weight: 700;
  color: #1a1209;
}
.gh-csi-total .total_price { color: #714e32; font-size: 16px; }

/* Empty state */
.gh-csi-empty {
  display: flex !important;
  flex-direction: column;
  align-items: center;
  padding: 40px 20px !important;
  text-align: center;
}
.gh-csi-empty-icon {
  width: 72px; height: 72px;
  background: #fdf8f4;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px;
}
.gh-csi-empty p { font-size: 14px; color: #7a6655; margin-bottom: 16px; }
.gh-csi-shop-btn {
  padding: 9px 22px;
  background: #714e32; color: #fff;
  font-size: 13px; font-weight: 600;
  border-radius: 6px; text-decoration: none;
  transition: background .15s;
}
.gh-csi-shop-btn:hover { background: #5a3c24; color: #fff; }
</style>
<style>
    /* ═══════════════════════════════════════════════════════════════
       GH CART DRAWER
       Fixed panel that slides in from the right.
    ═══════════════════════════════════════════════════════════════ */

    #gh-cart-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 99998;
    }

    #gh-cart-drawer {
      position: fixed;
      top: 0;
      right: -420px;
      width: 380px;
      max-width: 95vw;
      height: 100vh;
      height: 100dvh; /* dynamic viewport height — respects mobile browser chrome */
      background: #fff;
      z-index: 99999;
      box-shadow: -6px 0 32px rgba(0, 0, 0, 0.15);
      transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
      overflow: hidden; /* prevent drawer itself from scrolling */
    }

    #gh-cart-drawer.gh-cart-open {
      right: 0;
    }

    /* ── Header ─────────────────────────────────────────────────── */
    .gh-cart-drawer-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 18px 20px;
      border-bottom: 1px solid #f0ebe5;
      background: #fdf8f4;
      flex-shrink: 0;
    }

    .gh-cart-drawer-title {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: #1a1209;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .gh-cart-drawer-title i { color: #714e32; }

    #gh-cart-drawer-count {
      background: #714e32;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      border-radius: 20px;
      padding: 2px 8px;
    }

    #gh-cart-drawer-close {
      width: 32px;
      height: 32px;
      border: none;
      background: #f0ebe5;
      border-radius: 50%;
      font-size: 20px;
      line-height: 1;
      color: #714e32;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: background 0.15s;
      flex-shrink: 0;
    }

    #gh-cart-drawer-close:hover { background: #714e32; color: #fff; }

    /* ── Scrollable item list ───────────────────────────────────── */
    #gh-cart-items {
      flex: 1 1 0%;        /* take ALL remaining space between header & footer */
      min-height: 0;       /* critical for flex overflow to work */
      overflow-y: auto;
      overflow-x: hidden;
      margin: 0;
      padding: 0;
      list-style: none;
      -webkit-overflow-scrolling: touch; /* smooth scroll on iOS */
    }

    #gh-cart-items::-webkit-scrollbar { width: 4px; }
    #gh-cart-items::-webkit-scrollbar-track { background: #fdf8f4; }
    #gh-cart-items::-webkit-scrollbar-thumb { background: #c4956a; border-radius: 2px; }

    /* ── Footer — always visible, never cut off ─────────────────── */
    .gh-cart-drawer-footer {
      padding: 16px 20px;
      border-top: 2px solid #f0ebe5;
      background: #fdf8f4;
      flex-shrink: 0;       /* NEVER compress */
      /* Safe area inset for phones with home indicator (iPhone X+) */
      padding-bottom: calc(16px + env(safe-area-inset-bottom, 0px));
    }

    .gh-cart-drawer-subtotal {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 14px;
      font-size: 15px;
      font-weight: 700;
      color: #1a1209;
    }

    #gh-cart-drawer-total { color: #714e32; font-size: 17px; }

    .gh-cart-drawer-checkout {
      display: block;
      text-align: center;
      background: #714e32;
      color: #fff;
      padding: 13px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 14px;
      text-decoration: none;
      transition: background 0.15s;
    }

    .gh-cart-drawer-checkout:hover { background: #5a3c24; color: #fff; }

    /* Loading state inside the list */
    .gh-cart-loading {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      color: #714e32;
      font-size: 13px;
      gap: 8px;
    }

    /* Hide theme's built-in cart sidebar */
    .shoping__cart__inner,
    .shoping__cart__wrapper,
    .shop_cart_sidebar,
    .cart_sidebar_wrapper,
    .side-bar-cart {
      display: none !important;
    }

    /* ═══════════════════════════════════════════════════════════════
       MOBILE RESPONSIVE — Cart Drawer
    ═══════════════════════════════════════════════════════════════ */
    @media (max-width: 576px) {
      #gh-cart-drawer {
        width: 100vw;       /* full width on small phones */
        max-width: 100vw;
        right: -105vw;      /* hide offscreen */
      }

      .gh-cart-drawer-header {
        padding: 14px 16px;
      }

      .gh-cart-drawer-footer {
        padding: 14px 16px;
        padding-bottom: calc(14px + env(safe-area-inset-bottom, 0px));
      }

      .gh-cart-drawer-subtotal {
        margin-bottom: 10px;
        font-size: 14px;
      }

      #gh-cart-drawer-total { font-size: 16px; }

      .gh-cart-drawer-checkout {
        padding: 14px;
        font-size: 14px;
        border-radius: 10px;
      }
    }

    @media (max-width: 390px) {
      .gh-csi-img {
        width: 50px;
        height: 50px;
      }

      .gh-csi-wrap {
        gap: 10px;
        padding: 12px 14px;
      }

      .gh-csi-name { font-size: 12px; }
      .gh-csi-price { font-size: 12px; }
      .gh-csi-variant { font-size: 10px; }
    }
  </style>
  @yield('head')
</head>
<body>

  <!-- Preloader -->
  <div class="preloader" id="preloader"></div>

  <!-- Main Content -->
  @yield('content')

  {{--
    ═══════════════════════════════════════════════════════════════
    CART DRAWER
    Must come AFTER @yield('content') and be a direct child of
    <body> so it is never trapped inside a page wrapper element.
    ═══════════════════════════════════════════════════════════════
  --}}
  <div id="gh-cart-overlay"></div>

  <div id="gh-cart-drawer" role="dialog" aria-modal="true" aria-label="Shopping cart">

    <div class="gh-cart-drawer-header">
      <h6 class="gh-cart-drawer-title">
        <i class="fas fa-shopping-cart"></i>
        Your Cart
        <span id="gh-cart-drawer-count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
      </h6>
      <button id="gh-cart-drawer-close" aria-label="Close cart">&times;</button>
    </div>

    {{-- Populated by loadCartSidebar() via AJAX --}}
    <ul id="gh-cart-items">
      <li class="gh-cart-loading">
        <i class="fas fa-spinner fa-spin"></i>&nbsp;Loading…
      </li>
    </ul>

    <div class="gh-cart-drawer-footer">
      <div class="gh-cart-drawer-subtotal">
        <span>Subtotal</span>
        <span id="gh-cart-drawer-total">₦{{ number_format(session('cart_total', 0), 2) }}</span>
      </div>
      <a href="{{ route('cart.index') }}" class="gh-cart-drawer-checkout">
        <i class="fas fa-lock me-2"></i>View Cart &amp; Checkout
      </a>
    </div>

  </div>

  <!-- ── Flash Messages ────────────────────────────────────────────────── -->
  @if(session('success'))
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:100000;">
    <div class="toast show bg-success text-white" role="alert">
      <div class="toast-body">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
      </div>
    </div>
  </div>
  @endif

  @if(session('error'))
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:100000;">
    <div class="toast show bg-danger text-white" role="alert">
      <div class="toast-body">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
      </div>
    </div>
  </div>
  @endif

  @if($errors->any())
  <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:100000;">
    <div class="toast show bg-danger text-white" role="alert">
      <div class="toast-body">
        <i class="fas fa-exclamation-circle me-2"></i>
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
  @endif

  <!-- ── Core Libraries ────────────────────────────────────────────────── -->
  <script src="{{ asset('public/js/jquery-3.6.0.js') }}"></script>
  <script src="{{ asset('public/js/jquery-migrate-3.0.0.min.js') }}"></script>
  <script src="{{ asset('public/js/popper.min.js') }}"></script>
  <script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('public/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('public/js/jquery.mmenu.all.js') }}"></script>
  <script src="{{ asset('public/js/ace-responsive-menu.js') }}"></script>
  <script src="{{ asset('public/js/wow.min.js') }}"></script>
  <script src="{{ asset('public/js/slider.js') }}"></script>
  <script src="{{ asset('public/js/script.js') }}"></script>
  <script src="{{ asset('public/js/parallax.js') }}"></script>
  <script src="{{ asset('public/js/pricing-slider.js') }}"></script>

  <!-- ── Cart JS ───────────────────────────────────────────────────────── -->


  <script src="{{ asset('public/js/fastsearch.js') }}"></script>

  <!-- ═══════════════════════════════════════════════════════════════════
       GLOBAL UTILITIES
       Declared outside $(document).ready() → true globals.
       cart.js and every @push('scripts') block can call these freely.
  ═══════════════════════════════════════════════════════════════════ -->
  <script>

    /* ── CSRF header for all $.ajax calls ───────────────────────────── */
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    /* ── Preloader ──────────────────────────────────────────────────── */
    $(window).on('load', function () {
      $('#preloader').fadeOut('slow', function () { $(this).remove(); });
    });

    /* ── Auto-dismiss server-rendered flash toasts after 5 s ────────── */
    setTimeout(function () {
      $('.toast').fadeOut('slow', function () { $(this).remove(); });
    }, 5000);

    /* ──────────────────────────────────────────────────────────────────
       updateCartUI
       Syncs every count/total element on the page after any
       cart mutation (add, update, remove, clear).
    ────────────────────────────────────────────────────────────────── */
    function updateCartUI(data) {
      // Header icon
      $('#cart-count').text(data.cart_count);
      $('#cart-total').text('₦' + data.cart_total);
      // New drawer
      $('#gh-cart-drawer-count').text(data.cart_count);
      $('#gh-cart-drawer-total').text('₦' + data.cart_total);
      // Theme misc selectors
      $('.cart-badge').text(data.cart_count);
      $('.total_price').text('₦' + data.cart_total);
      $('.cart-subtotal').text('₦' + data.cart_total);
      $('.mobile_menu_widget_icons .badge').text(data.cart_count);
      $('#mobile-cart-count').text(data.cart_count);
    }

    /* ──────────────────────────────────────────────────────────────────
       loadCartSidebar
       Fetches the rendered <li> items from /cart/sidebar and injects
       them into #cart-sidebar-items inside the drawer.
    ────────────────────────────────────────────────────────────────── */
function loadCartSidebar() {
  var $list = $('#gh-cart-items');  // ← was #cart-sidebar-items
  $list.html('<li class="gh-cart-loading"><i class="fas fa-spinner fa-spin"></i>&nbsp;Loading…</li>');
  
  $.ajax({
    url: '/cart/sidebar',
    method: 'GET',
    success: function (html) {
      $list.html(html);
    },
    error: function (xhr) {
      console.error('loadCartSidebar failed:', xhr.status, xhr.responseText);
      $list.html('<li class="gh-cart-loading">Could not load cart. Please refresh.</li>');
    },
  });
}

    /* ──────────────────────────────────────────────────────────────────
       openCartDrawer / closeCartDrawer
    ────────────────────────────────────────────────────────────────── */
    function openCartDrawer() {
      loadCartSidebar();
      $('#gh-cart-drawer').addClass('gh-cart-open');
      $('#gh-cart-overlay').fadeIn(200);
      $('body').css('overflow', 'hidden');
    }

    function closeCartDrawer() {
      $('#gh-cart-drawer').removeClass('gh-cart-open');
      $('#gh-cart-overlay').fadeOut(200);
      $('body').css('overflow', '');
    }

    /* ──────────────────────────────────────────────────────────────────
       showToast
       JS-triggered toast notifications (success / error).
    ────────────────────────────────────────────────────────────────── */
    function showToast(type, message) {
      var bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
      var icon    = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

      var toast = $(
        '<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index:100000;">' +
          '<div class="toast show ' + bgClass + ' text-white" role="alert">' +
            '<div class="toast-body">' +
              '<i class="fas ' + icon + ' me-2"></i>' + message +
            '</div>' +
          '</div>' +
        '</div>'
      );

      $('body').append(toast);
      setTimeout(function () {
        toast.fadeOut('slow', function () { $(this).remove(); });
      }, 3000);
    }

    /* ──────────────────────────────────────────────────────────────────
       trackRecentlyViewed
    ────────────────────────────────────────────────────────────────── */
    function trackRecentlyViewed(productId) {
      $.post('{{ route("product.track-view") }}', { product_id: productId });
    }

    /* ──────────────────────────────────────────────────────────────────
       Cart drawer open / close event bindings.

       stopImmediatePropagation() is critical here:
       script.js (the theme file loaded above) also binds a click
       handler to .cart-filter-btn and opens the theme's old sidebar.
       Because our handler is registered AFTER script.js runs, it fires
       second — but stopImmediatePropagation() prevents script.js's
       handler from doing anything, so only our drawer opens.
    ────────────────────────────────────────────────────────────────── */
    $(document).on('click', '.cart-filter-btn', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      openCartDrawer();
    });

    $(document).on('click', '#gh-cart-drawer-close, #gh-cart-overlay', function () {
      closeCartDrawer();
    });

    $(document).on('keydown', function (e) {
      if (e.key === 'Escape') closeCartDrawer();
    });

    /* ── Wishlist ────────────────────────────────────────────────────── */
    $(document).on('click', '.add-to-wishlist', function (e) {
      e.preventDefault();
      var productId = $(this).data('product-id');
      var btn = $(this);

      $.ajax({
        url:    '{{ route("wishlist.add") }}',
        method: 'POST',
        data:   { product_id: productId },
        success: function () {
          showToast('success', 'Product added to wishlist!');
          btn.toggleClass('active');
        },
        error: function (xhr) {
          if (xhr.status === 401) {
            window.location.href = '{{ route("login") }}';
          } else {
            showToast('error', 'Failed to add product to wishlist');
          }
        },
      });
    });

    /* ── Compare ─────────────────────────────────────────────────────── */
    $(document).on('click', '.compare-product', function (e) {
      e.preventDefault();
      var productId = $(this).data('product-id');

      $.ajax({
        url:    '{{ route("compare.add") }}',
        method: 'POST',
        data:   { product_id: productId },
        success: function () { showToast('success', 'Product added to compare!'); },
        error:   function () { showToast('error',   'Failed to add product to compare'); },
      });
    });

  </script>
  @if (!request()->routeIs('cart.index'))
  <script src="{{ asset('public/js/cart.js') }}"></script>
  @endif
  <!-- ── Page-specific scripts ─────────────────────────────────────────── -->
  @stack('scripts')

</body>
</html>
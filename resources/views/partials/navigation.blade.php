{{-- resources/views/partials/navigation.blade.php - FULLY UPDATED --}}

<!-- Body Overlay Behind Sidebar -->
<div class="hiddenbar-body-ovelay"></div>

<!-- Sign In Hidden SideBar -->
<div class="signin-hidden-sbar">
  <div class="hsidebar-header">
    <div class="sidebar-close-icon"><span class="flaticon-close"></span></div>
    <h4 class="title">Sign-In</h4>
  </div>
  <div class="hsidebar-content">
    <div class="log_reg_form sidebar_area">
      <div class="login_form">
        <form action="{{ route('login') }}" method="POST">
          @csrf
          <div class="mb-2 mr-sm-2">
            <label class="form-label">Username or email address</label>
            <input type="email" name="email" class="form-control" placeholder="Email" required>
          </div>
          <div class="form-group mb5">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="exampleCheck3" name="remember">
            <label class="custom-control-label" for="exampleCheck3">Remember me</label>
            <a class="btn-fpswd float-end" href="{{ route('password.request') }}">Lost your password?</a>
          </div>
          <button type="submit" class="btn btn-log btn-thm mt20">Login</button>
          <p class="text-center mb25 mt10">Don't have an account? <a class="signup-filter-btn" href="{{ route('register') }}">Create account</a></p>
        </form>

        {{-- ── Social login divider ───────────────────────────── --}}
        <div style="display:flex; align-items:center; gap:10px; margin:4px 0 16px;">
          <div style="flex:1; height:1px; background:#e8eaed;"></div>
          <span style="font-size:11px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#b0b8c4; white-space:nowrap;">or continue with</span>
          <div style="flex:1; height:1px; background:#e8eaed;"></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
          <a href="{{ route('social.redirect', 'google') }}?type=customer"
             style="display:flex; align-items:center; justify-content:center; gap:7px;
                    height:42px; border:1.5px solid #e0e4ea; border-radius:8px;
                    background:#fff; font-size:13px; font-weight:600;
                    color:#2d2d2d; text-decoration:none;
                    transition:border-color .2s, background .2s;">
            <i class="fa-brands fa-google" style="font-size:15px; color:#EA4335;"></i> Google
          </a>
          <a href="{{ route('social.redirect', 'facebook') }}?type=customer"
             style="display:flex; align-items:center; justify-content:center; gap:7px;
                    height:42px; border:1.5px solid #e0e4ea; border-radius:8px;
                    background:#fff; font-size:13px; font-weight:600;
                    color:#2d2d2d; text-decoration:none;
                    transition:border-color .2s, background .2s;">
            <i class="fa-brands fa-facebook" style="font-size:15px; color:#1877F2;"></i> Facebook
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Your Cart Hidden SideBar -->
<div class="cart-hidden-sbar">
  <div class="hsidebar-header">
    <div class="sidebar-close-icon"><span class="flaticon-close"></span></div>
    <h4 class="title">Your Cart</h4>
  </div>
  <div class="hsidebar-content">
    <div class="log_fav_cart_widget hsidebar_home_page">
      <div class="wrapper">
        <ul class="cart">
          <li class="list-inline-item">
            <ul class="dropdown_content" id="cart-sidebar-items">
              {{-- Cart items will be loaded dynamically --}}
              @if(session('cart') && count(session('cart')) > 0)
                @foreach(session('cart') as $id => $item)
                <li class="list_content" data-product-id="{{ $id }}">
                  <div>
                    <a href="{{ route('product.show', $item['slug']) }}">
                      <img class="float-start mt10" src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" width="75" height="75">
                    </a>
                    <p>
                      <a href="{{ route('product.show', $item['slug']) }}" class="text-dark">
                        {{ Str::limit($item['name'], 40) }}
                      </a>
                    </p>
                    <div class="cart_btn home_page_sidebar mt10">
                      <div class="quantity-block home_page_sidebar">
                        <button class="quantity-arrow-minus home_page_sidebar update-cart-quantity" 
                                data-id="{{ $id }}" 
                                data-action="decrease">
                          <img src="{{ asset('public/images/icons/minus.svg') }}" alt="-">
                        </button>
                        <input class="quantity-num home_page_sidebar quantity-input-{{ $id }}" 
                               type="number" 
                               value="{{ $item['quantity'] }}" 
                               readonly>
                        <button class="quantity-arrow-plus home_page_sidebar update-cart-quantity" 
                                data-id="{{ $id }}" 
                                data-action="increase">
                           <span class="fas fa-plus"></span>
                        </button>
                      </div>
                      <span class="home_page_sidebar price item-total-{{ $id }}">
                        ₦{{ number_format($item['price'] * $item['quantity'], 2) }}
                      </span>
                    </div>
                    <span class="close_icon remove-from-cart" data-id="{{ $id }}" style="cursor: pointer;">
                      <i class="flaticon-close"></i>
                    </span>
                  </div>
                </li>
                @endforeach
                
                <li class="list_content_total_price">
                  <h5>Total: <span class="total_price float-end">₦{{ number_format(session('cart_total', 0), 2) }}</span></h5>
                </li>
              @else
                <li class="text-center py-5">
                  <div class="empty-cart">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Your cart is empty</p>
                    <a href="{{ route('product.index') }}" class="btn btn-thm btn-sm mt-2">Continue Shopping</a>
                  </div>
                </li>
              @endif
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="hsidebar_footer_content">
    <div class="list_last_content">
      <div class="lc">
        <a href="{{ route('cart.index') }}" class="cart_btns btn btn-white">View Cart</a>
        <!--<a href="{{ route('checkout.index') }}" class="checkout_btns btn btn-thm">Checkout</a>-->
      </div>
    </div>
  </div>
</div>

<!-- Sign Up Hidden SideBar -->
<div class="signup-hidden-sbar">
  <div class="hsidebar-header">
    <div class="sidebar-close-icon"><span class="flaticon-close"></span></div>
    <h4 class="title">Create Your Account</h4>
  </div>
  <div class="hsidebar-content">
    <div class="log_reg_form sidebar_area">
      <div class="sign_up_form">
        <form action="{{ route('register') }}" method="POST">
          @csrf
          <div class="form-group">
            <label class="form-label">Your Name</label>
            <input type="text" name="name" class="form-control" placeholder="Full Name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Your Email</label>
            <input type="email" name="email" class="form-control" placeholder="email@example.com" required>
          </div>
          <div class="form-group mb20">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="******************" required>
          </div>
          <div class="form-group mb20">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" placeholder="******************" required>
          </div>
          <button type="submit" class="btn btn-signup btn-thm">Create Account</button>
          <p class="text-center mb25 mt10">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
        </form>

        {{-- ── Social sign-up divider ─────────────────────────── --}}
        <div style="display:flex; align-items:center; gap:10px; margin:4px 0 16px;">
          <div style="flex:1; height:1px; background:#e8eaed;"></div>
          <span style="font-size:11px; font-weight:700; letter-spacing:.07em; text-transform:uppercase; color:#b0b8c4; white-space:nowrap;">or sign up with</span>
          <div style="flex:1; height:1px; background:#e8eaed;"></div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
          <a href="{{ route('social.redirect', 'google') }}?type=customer"
             style="display:flex; align-items:center; justify-content:center; gap:7px;
                    height:42px; border:1.5px solid #e0e4ea; border-radius:8px;
                    background:#fff; font-size:13px; font-weight:600;
                    color:#2d2d2d; text-decoration:none;
                    transition:border-color .2s, background .2s;">
            <i class="fa-brands fa-google" style="font-size:15px; color:#EA4335;"></i> Google
          </a>
          <a href="{{ route('social.redirect', 'facebook') }}?type=customer"
             style="display:flex; align-items:center; justify-content:center; gap:7px;
                    height:42px; border:1.5px solid #e0e4ea; border-radius:8px;
                    background:#fff; font-size:13px; font-weight:600;
                    color:#2d2d2d; text-decoration:none;
                    transition:border-color .2s, background .2s;">
            <i class="fa-brands fa-facebook" style="font-size:15px; color:#1877F2;"></i> Facebook
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Mobile Menu -->
<div id="page" class="stylehome1">
    <div class="mobile-menu">
      <div class="header stylehome1 home4_style">
        <div class="menu_and_widgets">
          <div class="mobile_menu_bar float-start">
            <a class="menubar" href="#menu"><span></span></a>
            <div class="mobile-logo" style="margin-left: 25px; margin-top: 15px;">
            <a href="{{ route('home') }}">
              <img 
                style="height: 30px;width: auto;"
                src="{{ asset('public/image/genehub.png') }}" 
                alt="Zeomart Logo"
                class="img-fluid"
              >
            </a>
          </div>
          </div>
          <div class="mobile_menu_widget_icons">
            <ul class="cart mt15">
              <li class="list-inline-item">
                @auth
                  <a class="cart_btn" href="{{ route('account.index') }}">
                    <span class="icon flaticon-profile"></span>
                  </a>
                @else
                  <a class="cart_btn signin-filter-btn" href="#" onclick="event.preventDefault(); document.querySelector('.signin-filter-btn').click();">
                    <span class="icon flaticon-profile"></span>
                  </a>
                @endauth
              </li>
              <li class="list-inline-item">
                <a class="cart_btn cart-filter-btn" href="#" onclick="event.preventDefault();">
                  <span class="icon">
                    <img src="{{ asset('public/images/icons/flaticon-shopping-cart-white.svg') }}" alt="">
                  </span>
                  <span class="badge bgc-thm1 color-white" id="mobile-cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
                </a>
              </li>
            </ul>
          </div>
        </div>
        
        <!-- Mobile Search -->
        <div class="mobile_menu_search_widget">
          <div class="header_middle_advnc_search">
            <div class="container search_form_wrapper">
              <div class="row">
                <div>
                  <div class="top-search text-start">
                    <form action="{{ route('search.index') }}" method="GET" class="form-search" accept-charset="utf-8">
                      <div class="box-search">
                        <input class="form_control" type="text" name="search" id="mobile-search-input" placeholder="Search products…" autocomplete="off">
                        <div class="search-suggestions text-start" id="mobile-search-suggestions" style="display: none;">
                          <div class="box-suggestions">
                            <ul id="mobile-suggestion-list">
                              {{-- Dynamic suggestions will be loaded here via AJAX --}}
                            </ul>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
                <div>
                  <div class="advscrh_frm_btn">
                    <button type="submit" class="btn search-btn"><span class="flaticon-search"></span></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="posr">
          <div class="mobile_menu_close_btn"><span class="flaticon-close"></span></div>
        </div>
      </div>
    </div>
    <!-- /.mobile-menu -->
    
    <!-- Mobile Navigation Menu -->
    <nav id="menu" class="stylehome1">
      <ul>
        <li><a href="{{ route('home') }}">Home</a></li>
        
        <li><span>Shop</span>
          <ul>
            <li><a href="{{ route('product.index') }}">All Products</a></li>
            <li><a href="{{ route('cart.index') }}">Shopping Cart</a></li>
            <li><a href="{{ route('shop.index') }}">Shops</a></li>
            <!--<li><a href="{{ route('wishlist.index') }}">Wishlist</a></li>-->
            @auth
            <li><a href="{{ route('account.index') }}">My Orders</a></li>
            @endauth
          </ul>
        </li>
        
        <li><span>Account</span>
          <ul>
            @auth
              <li><a href="{{ route('account.index') }}">My Profile</a></li>
              <li><a href="{{ route('account.index') }}">My Orders</a></li>
              <!--<li><a href="{{ route('wishlist.index') }}">Wishlist</a></li>-->
              <li>
                <a href="{{ route('logout') }}" 
                   onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();">
                  Logout
                </a>
                <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  @csrf
                </form>
              </li>
            @else
              <li><a href="{{ route('login') }}">Login</a></li>
              <li><a href="{{ route('register') }}">Register</a></li>
              <li>
                <a href="{{ route('social.redirect', 'google') }}?type=customer">
                  <i class="fa-brands fa-google" style="color:#EA4335; margin-right:6px;"></i> Continue with Google
                </a>
              </li>
              <li>
                <a href="{{ route('social.redirect', 'facebook') }}?type=customer">
                  <i class="fa-brands fa-facebook" style="color:#1877F2; margin-right:6px;"></i> Continue with Facebook
                </a>
              </li>
            @endauth
          </ul>
        </li>
        
        <!--<li><a href="{{ route('brands.index') }}">Brands</a></li>-->
        <!--<li><a href="{{ route('contact') }}">Contact</a></li>-->
        
        @if(isset($categoriesWithSubs) && $categoriesWithSubs->isNotEmpty())
        <li class="title my-3 bb1 pl20 fz20 fw500 pb-3">Departments</li>
        @foreach($categoriesWithSubs as $category)
        <li>
          <span>
            @if($category->icon)
              <i class="{{ $category->icon }} mr20"></i>
            @else
              <i class="flaticon-groceries mr20"></i>
            @endif
            {{ $category->name }}
          </span>
          <ul>
            <li><a href="{{ route('category.show', $category->slug) }}">All {{ $category->name }}</a></li>
            @if($category->children && $category->children->isNotEmpty())
              @foreach($category->children as $subcategory)
                <li><a href="{{ route('category.show', $subcategory->slug) }}">{{ $subcategory->name }}</a></li>
              @endforeach
            @endif
          </ul>
        </li>
        @endforeach
        @endif
      </ul>
    </nav>
</div>
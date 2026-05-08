{{-- resources/views/partials/header.blade.php - FIXED --}}

<!-- Desktop Header Middle -->
<div class="header_middle home4_style pt20 pb20 dn-992">
  <div class="container-fluid maxw1800">
    <div class="row">
      <div class="col-xxl-2">
        <div class="header_top_logo_home4 text-center text-xxl-start mb-3 mb-xxl-0">
          <div class="logo">
            <a href="{{ route('home') }}">
              <img 
                style="height: 40px;width: auto;"
                src="{{ asset('public/image/genehub.png') }}" 
                alt="GenesisHub Logo"
                class="img-fluid"
              >
            </a>
          </div>
        </div>
      </div>
      
      <div class="col-xl-5 col-xxl-6">
        <div class="header_middle_advnc_search home2_style at_home4">
          <div class="search_form_wrapper">
            <div class="row justify-content-center justify-content-xl-start mb-4 mb-xl-0">
              <div class="col-auto pe-0">
                <div class="top-search home2_style at_home4">
                  <form action="{{ route('search.index') }}" method="GET" class="form-search" accept-charset="utf-8">
                    <div class="box-search pre_line">
                      <input class="form_control" type="text" name="search" id="search-input" placeholder="Search products…" autocomplete="off">
                      <div class="search-suggestions" id="search-suggestions" style="display: none;">
                        <div class="box-suggestions">
                          <ul id="suggestion-list">
                            {{-- Dynamic suggestions will be loaded here via AJAX --}}
                          </ul>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
              
              <div class="col-auto bgc-white">
                <div class="actegory home2_style">
                  <select class="selectpicker" id="selectbox_alCategory" name="category">
                    <option value="">All Category</option>
                    @foreach($categoriesWithSubs ?? [] as $category)
                      <option value="{{ $category->slug }}">{{ $category->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-auto p0">
                <div class="advscrh_frm_btn home4_style">
                  <button type="submit" class="btn search-btn text-thm">
                    <span class="flaticon-search"></span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <div class="col-xl-7 col-xxl-4">
        <div class="hm_log_fav_cart_widget text-center text-xxl-start">
          <div class="wrapper">
            <ul class="mb0">
              <li class="list-inline-item mb-1">
                <a class="header_top_iconbox home2_style at_home4 text-start" href="{{ route('become-vendor') }}">
                  <div class="d-block d-md-flex">
                    <div class="details">
                      <h5 class="sutitle" style="color: #ffffff;">Become a seller</h5>
                    </div>
                  </div>
                </a>
              </li>
              
              <li class="list-inline-item">
                @auth
                {{-- Logged in user - show profile link --}}
                <a class="header_top_iconbox home2_style at_home4 text-start" href="{{ route('account.index') }}">
                  <div class="d-block d-md-flex">
                    <div class="icon"><span class="flaticon-profile"></span></div>
                    <div class="details">
                      <p class="subtitle">Welcome</p>
                      <h5 class="title">{{ Str::limit(Auth::user()->name, 12) }}</h5>
                    </div>
                  </div>
                </a>
                @else
                {{-- Guest user - show sign in link --}}
                <a class="header_top_iconbox home2_style at_home4 text-start signin-filter-btn" href="{{ route('login') }}">
                  <div class="d-block d-md-flex">
                    <div class="icon"><span class="flaticon-profile"></span></div>
                    <div class="details">
                      <p class="subtitle">Sign In</p>
                      <h5 class="title">Account</h5>
                    </div>
                  </div>
                </a>
                @endauth
              </li>
              
              <li class="list-inline-item">
<a class="header_top_iconbox home2_style at_home4 text-start cart-filter-btn" 
   href="javascript:void(0)">
                  <div class="d-block d-md-flex">
                    <div class="icon">
                      <span><img src="{{ asset('public/images/icons/flaticon-shopping-cart-white.svg') }}" alt=""></span>
                      <span class="badge" id="cart-count">{{ session('cart') ? count(session('cart')) : 0 }}</span>
                    </div>
                    <div class="details">
                      <p class="subtitle" id="cart-total">₦{{ number_format(session('cart_total', 0), 2) }}</p>
                      <h5 class="title">Total</h5>
                    </div>
                  </div>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('app.name', 'Genesishub') }} - @yield('title', 'E-commerce Store')</title>

<!-- SEO Meta Tags -->
<meta name="description" content="@yield('meta_description', 'Shop the latest products from top brands at Genesishub. Free shipping on orders over   NGN200.')">
<meta name="keywords" content="@yield('meta_keywords', 'ecommerce, online shopping, electronics, fashion, home goods')">
<meta name="author" content="Genesishub">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('og_title', config('app.name'))">
<meta property="og:description" content="@yield('og_description', 'Shop the latest products from top brands')">
<meta property="og:image" content="@yield('og_image', asset('public/images/og-image.jpg'))">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="@yield('twitter_title', config('app.name'))">
<meta property="twitter:description" content="@yield('twitter_description', 'Shop the latest products from top brands')">
<meta property="twitter:image" content="@yield('twitter_image', asset('public/images/og-image.jpg'))">

<!-- Favicon -->
<link href="{{ asset('public/images/favicon.ico') }}" sizes="128x128" rel="shortcut icon" type="image/x-icon" />
<link href="{{ asset('public/images/favicon.ico') }}" sizes="128x128" rel="shortcut icon" />
<!-- Apple Touch Icon -->
<link href="{{ asset('public/images/apple-touch-icon-60x60.png') }}" sizes="60x60" rel="apple-touch-icon">
<link href="{{ asset('public/images/apple-touch-icon-72x72.png') }}" sizes="72x72" rel="apple-touch-icon">
<link href="{{ asset('public/images/apple-touch-icon-114x114.png') }}" sizes="114x114" rel="apple-touch-icon">
<link href="{{ asset('public/images/apple-touch-icon-180x180.png') }}" sizes="180x180" rel="apple-touch-icon">z

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
<link rel="stylesheet" href="{{ asset('public/css/slider.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/responsive.css') }}">

@yield('head')
</head>
<body data-spy="scroll">
<div class="wrapper ovh">
  <div class="preloader"></div>
  
  <!-- header middle -->
  <div class="header_middle pt20 pb20 dn-992">
    <div class="container">
      <div class="row">
        <div class="col-lg-2 col-xxl-2">
          <div class="header_top_logo_home1">
            <div class="logo">Genesishub</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!--End Sign Up Hiddn SideBar -->
  
  <!-- Main Header Nav For Mobile -->
  <div id="page" class="stylehome1">
    <div class="mobile-menu">
      <div class="header stylehome1" style="background-color:#714e32;">
        <div class="menu_and_widgets">
          <div class="mobile_menu_bar float-start">
            <a class="mobile_logo" href="/">Genesishub</a>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <div class="body_content_wrapper position-relative">
    <!-- Inner Page Breadcrumb -->
  	<section class="inner_page_breadcrumb style4">
  		<div class="container">
  			<div class="row">
          <div class="col-xl-7">
            <div class="breadcrumb_content style4">
              <h2 class="breadcrumb_title">Become an GenesisHub seller</h2>
              <p>More than half the units sold in our stores are from independent sellers.</p>
             <a class="btn btn-thm" href="{{ route('seller.register.form') }}">
    Sign up
</a>

            </div>
          </div>
        </div>
      </div>
    </section>
    
    <!-- Become A Vendor -->
    <section class="our-vendor pb35">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 col-xl-5 offset-xl-1">
            <div class="main-title">
              <h2 class="mtitle">Why become a seller on <br class="d-none d-md-block"> GenesisHub?</h2>
              <p>Join thousands of sellers already growing their business on GenesisHub<br class="d-none d-xxl-block"> reach more customers, sell more products, and keep more of what you earn.</p>
            </div>
          </div>
          <div class="col-lg-6 col-xl-5 offset-xl-1">
            <div class="vendor_iconbox">
              <span class="icon"><img src="public/images/icons/parcel-box.svg" alt="parcel-box"></span>
              <div class="details">
                <h3 class="title">Free Shipping</h3>
                <p>We handle logistics partnerships so your customers get their orders fast. Offer free shipping <br class="d-none d-md-block"> and watch your conversion rate thank you.</p>
              </div>
            </div>
            <div class="vendor_iconbox">
              <span class="icon"><img src="public/images/icons/payment-card.svg" alt="payment-card"></span>
              <div class="details">
                <h3 class="title">Flexible Payment</h3>
                <p>Get paid on your schedule via bank transfer, Paystack, or Flutterwave. <br class="d-none d-md-block"> No hidden fees, no funny business just your money, reliably.</p>
              </div>
            </div>
            <div class="vendor_iconbox">
              <span class="icon"><img src="public/images/icons/online-support.svg" alt="online-support"></span>
              <div class="details">
                <h3 class="title">Online Support</h3>
                <p>Our dedicated seller support team is available 7 days a week to help you <br class="d-none d-md-block"> resolve issues, optimise listings, and grow faster.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="funfact_content mb50">
          <div class="row">
            <div class="col-sm-6 col-lg-3 text-center">
              <div class="funfact_one">
                <div class="details">
                  <ul>
                    <li class="list-inline-item"><div class="timer">120</div></li>
                    <li class="list-inline-item"><span>+</span></li>
                  </ul>
                  <h5>Stores around the world</h5>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3 text-center">
              <div class="funfact_one">
                <div class="details">
                  <ul>
                    <li class="list-inline-item"><div class="timer">15</div></li>
                    <li class="list-inline-item"><span>M</span></li>
                  </ul>
                  <h5>Products sold till date</h5>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3 text-center">
              <div class="funfact_one">
                <div class="details">
                  <ul>
                    <li class="list-inline-item"><div class="timer">200</div></li>
                    <li class="list-inline-item"><span>K</span></li>
                  </ul>
                  <h5>Registered users</h5>
                </div>
              </div>
            </div>
            <div class="col-sm-6 col-lg-3 text-center">
              <div class="funfact_one">
                <div class="details">
                  <ul>
                    <li class="list-inline-item"><div class="timer">300</div></li>
                    <li class="list-inline-item"><span>+</span></li>
                  </ul>
                  <h5>Brands available in store</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-lg-6 col-xl-5 offset-xl-1">
            <div class="main-title">
              <h2 class="mtitle">Over   NGN50K in potential <br class="d-none d-md-block"> benefits</h2>
              <p>Ready to sell? Launch your brand today with a powerful playbook for new sellers <br class="d-none d-xxl-block"> and over   NGN50K in potential benefits.</p>
            </div>
          </div>
          <div class="col-lg-6 col-xl-5 offset-xl-1">
            <div class="vendor_iconbox style2 d-block d-sm-flex">
              <span class="icon mt10 me-3"><img src="public/images/icons/step-1.svg" alt="step-1"></span>
              <div class="details ms-0 ms-sm-4 mt-2 mt-sm-0">
                <h3 class="title">Step 1</h3>
                <p>Create an account on our website. It is fast and free.</p>
              </div>
            </div>
            <div class="vendor_iconbox style2 d-block d-sm-flex">
              <span class="icon mt10 me-3"><img src="public/images/icons/step-2.svg" alt="step-2"></span>
              <div class="details ms-0 ms-sm-4 mt-2 mt-sm-0">
                <h3 class="title">Step 2</h3>
                <p>Upload your products.</p>
              </div>
            </div>
            <div class="vendor_iconbox style2 d-block d-sm-flex">
              <span class="icon mt10 me-3"><img src="public/images/icons/step-3.svg" alt="step-3"></span>
              <div class="details ms-0 ms-sm-4 mt-2 mt-sm-0">
                <h3 class="title">Step 3</h3>
                <p>We will verify your account and then you can start <br class="d-none d-md-block"> selling!</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
    

    
    {{-- Footer --}}
    @include('partials.footer')
    
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>
<!-- Wrapper End --> 
  <!-- jQuery (required) -->


  <!-- JavaScript Files -->
  <script src="{{ asset('public/js/jquery-migrate-3.0.0.min.js') }}"></script>
  <script src="{{ asset('public/js/popper.min.js') }}"></script>
  <script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
  <script src="{{ asset('public/js/bootstrap-select.min.js') }}"></script>
  <script src="{{ asset('public/js/jquery.mmenu.all.js') }}"></script>
  <script src="{{ asset('public/js/ace-responsive-menu.js') }}"></script>
  <script src="{{ asset('public/js/jquery-scrolltofixed-min.js') }}"></script>
  <script src="{{ asset('public/js/wow.min.js') }}"></script>
  <script src="{{ asset('public/js/slider.js') }}"></script>
  <script src="{{ asset('public/js/script.js') }}"></script>
</body>

</html>
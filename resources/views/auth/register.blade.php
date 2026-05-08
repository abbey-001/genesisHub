<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="GenesisHub - Multi-Vendor & Marketplace">
<title>GenesisHub Register</title>

<!-- Favicon -->
<link href="{{ asset('public/image/auth-logo.png') }}" rel="shortcut icon" type="image/png">
<link href="{{ asset('public/image/auth-logo.png') }}" rel="apple-touch-icon">

<!-- CSS -->
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/ace-responsive-menu.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/menu.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/fontawesome.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/fontawesome-free.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/flaticon.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/animate.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/style.css') }}">
<link rel="stylesheet" href="{{ asset('public/css/responsive.css') }}">

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

<style>
/* GenesisHub Auth Design System */
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: #f3f5f6; color: #1a1a1a; }

.our-log-reg { background: #f3f5f6 !important; padding: 70px 0; }

.auth-card {
    background: #ffffff; border-radius: 20px;
    box-shadow: 0 12px 60px rgba(4,30,66,.09);
    padding: 48px 44px; margin-top: 0 !important;
}

@media (max-width: 575px) { .auth-card { padding: 36px 24px; } }

.auth-card__header {
    text-align: center; margin-bottom: 34px;
    padding-bottom: 26px; border-bottom: 1.5px solid #f0f1f3;
}

.auth-card__logo-wrap {
    display: inline-flex; align-items: center; justify-content: center;
    width: 68px; height: 68px; border-radius: 18px;
    background: rgba(113,78,50,.07);
    border: 1.5px solid rgba(113,78,50,.12); margin-bottom: 14px;
}

.auth-card__logo-wrap img { width: 42px; height: 42px; object-fit: contain; display: block; }

.auth-card__brand {
    font-family: 'Poppins', sans-serif;
    font-size: 22px; font-weight: 800; color: #714e32;
    letter-spacing: -.01em; margin-bottom: 4px; line-height: 1.2;
}

.auth-card__sub { font-size: 14px; color: #555e68; margin: 0; line-height: 1.5; }

/* Notices */
.notice {
    display: flex; gap: 12px; align-items: flex-start;
    border-radius: 12px; padding: 14px 16px; margin-bottom: 22px;
}
.notice__icon {
    width: 32px; height: 32px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.notice__icon i { font-size: 13px; }
.notice__title { font-size: 12.5px; font-weight: 700; margin-bottom: 2px; }
.notice__text  { font-size: 12px; line-height: 1.5; margin: 0; }

.notice--danger { background:#fff4f4; border:1px solid #ffc2c2; }
.notice--danger .notice__icon { background:rgba(229,62,62,.1); }
.notice--danger .notice__icon i { color:#c53030; }
.notice--danger .notice__title { color:#822020; }
.notice--danger .notice__text  { color:#9b2626; }

/* Form controls */
.auth-form-group { margin-bottom: 20px; }

.auth-label {
    display: block; font-size: 13px; font-weight: 600;
    color: #2d2d2d; margin-bottom: 7px;
}

.input-wrap { position: relative; }

.auth-input {
    display: block; width: 100%; height: 50px;
    padding: 0 14px; font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: #1a1a1a; background: #ffffff;
    border: 1.5px solid #e0e4ea; border-radius: 10px;
    transition: border-color .2s, box-shadow .2s;
    -webkit-appearance: none; outline: none;
}
.auth-input::placeholder { color: #b0b8c4; font-size: 13.5px; }
.auth-input:focus { border-color: #714e32; box-shadow: 0 0 0 3.5px rgba(113,78,50,.11); }
.auth-input.is-invalid { border-color: #e53e3e; }

.invalid-feedback { font-size: 12px; color: #e53e3e; margin-top: 5px; display: block; }

.pw-toggle {
    position: absolute; right: 13px; top: 50%;
    transform: translateY(-50%);
    background: none; border: none; padding: 0;
    cursor: pointer; color: #b0b8c4;
    font-size: 15px; line-height: 1; transition: color .2s; z-index: 2;
}
.pw-toggle:hover { color: #714e32; }
.input-wrap .auth-input { padding-right: 44px; }

/* Two-col row */
.form-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 480px) { .form-row-2 { grid-template-columns: 1fr; } }

/* Terms */
.terms-row {
    display: flex; align-items: flex-start; gap: 10px;
    background: rgba(113,78,50,.04);
    border: 1px solid rgba(113,78,50,.12);
    border-radius: 10px; padding: 14px 16px; margin-bottom: 22px;
}
.terms-row input[type="checkbox"] {
    width: 16px; height: 16px;
    border: 1.5px solid #c8cdd5; border-radius: 4px;
    cursor: pointer; accent-color: #714e32;
    flex-shrink: 0; margin-top: 2px;
}
.terms-row label { font-size: 13px; color: #555e68; line-height: 1.55; cursor: pointer; }
.terms-row a { color: #714e32; font-weight: 600; text-decoration: none; }
.terms-row a:hover { text-decoration: underline; }

/* Submit button */
.btn-auth {
    display: flex; align-items: center;
    justify-content: center; gap: 9px;
    width: 100%; height: 52px;
    background: #714e32; color: #ffffff;
    border: none; border-radius: 10px;
    font-size: 14.5px; font-weight: 600;
    font-family: 'Inter', sans-serif; cursor: pointer;
    letter-spacing: .025em;
    transition: background .2s, box-shadow .2s, transform .1s;
}
.btn-auth:hover { background: #5a3d26; box-shadow: 0 8px 24px rgba(113,78,50,.3); }
.btn-auth:active { transform: scale(0.985); }

/* Divider */
.auth-divider {
    display: flex; align-items: center; gap: 12px;
    margin: 24px 0 20px;
    font-size: 11.5px; font-weight: 600;
    letter-spacing: .07em; text-transform: uppercase; color: #b0b8c4;
}
.auth-divider::before, .auth-divider::after { content: ''; flex: 1; height: 1px; background: #e8eaed; }

/* Social */
.social-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 24px; }
.btn-social {
    display: flex; align-items: center; justify-content: center; gap: 8px;
    height: 46px; border: 1.5px solid #e0e4ea;
    border-radius: 10px; background: #ffffff;
    font-size: 13.5px; font-weight: 600;
    font-family: 'Inter', sans-serif;
    color: #2d2d2d; text-decoration: none; cursor: pointer;
    transition: border-color .2s, background .2s, box-shadow .2s;
}
.btn-social:hover {
    border-color: #714e32; background: rgba(113,78,50,.03);
    box-shadow: 0 2px 10px rgba(113,78,50,.1); color: #714e32;
}
.btn-social i { font-size: 16px; }
.btn-social .fa-google   { color: #EA4335; }
.btn-social .fa-facebook { color: #1877F2; }

/* Footer links */
.auth-foot { display: flex; flex-direction: column; gap: 10px; align-items: center; margin-top: 6px; }
.auth-foot p { font-size: 13.5px; color: #555e68; margin: 0; }
.auth-foot a { color: #714e32; font-weight: 600; text-decoration: none; transition: color .2s; }
.auth-foot a:hover { color: #5a3d26; text-decoration: underline; }
</style>
</head>

<body data-spy="scroll">
<div class="wrapper ovh">
  <div class="preloader"></div>

  <div class="body_content_wrapper position-relative">

    <section class="our-log-reg bgc-f5">
      <div class="container">
        <div class="row">
          <div class="col-lg-6 col-xl-5 col-xxl-5 m-auto">
            <div class="auth-card">

              <!-- Card header -->
              <div class="auth-card__header">
                <div class="auth-card__logo-wrap">
                  <img src="{{ asset('public/image/auth-logo.png') }}" alt="{{ config('app.name', 'GenesisHub') }}">
                </div>
                <h1 class="auth-card__brand">{{ config('app.name', 'GenesisHub') }}</h1>
                <p class="auth-card__sub">Create your free account and start shopping</p>
              </div>

              <!-- Errors -->
              @if($errors->any())
              <div class="notice notice--danger">
                <div class="notice__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div>
                  <p class="notice__title">Please fix the following</p>
                  <p class="notice__text">{{ $errors->first() }}</p>
                </div>
              </div>
              @endif

              <!-- Register form -->
              <form action="{{ route('register') }}" method="POST" novalidate>
                @csrf

                <div class="auth-form-group">
                  <label class="auth-label" for="name">Full Name</label>
                  <input type="text" id="name" name="name"
                         class="auth-input @error('name') is-invalid @enderror"
                         value="{{ old('name') }}"
                         placeholder="Your full name"
                         autocomplete="name" autofocus required>
                  @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="auth-form-group">
                  <label class="auth-label" for="email">Email Address</label>
                  <input type="email" id="email" name="email"
                         class="auth-input @error('email') is-invalid @enderror"
                         value="{{ old('email') }}"
                         placeholder="you@example.com"
                         autocomplete="email" required>
                  @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <div class="form-row-2">
                  <div class="auth-form-group">
                    <label class="auth-label" for="password">Password</label>
                    <div class="input-wrap">
                      <input type="password" id="password" name="password"
                             class="auth-input @error('password') is-invalid @enderror"
                             placeholder="Create a password"
                             autocomplete="new-password" required>
                      <button type="button" class="pw-toggle" onclick="togglePw('password','pwIcon1')">
                        <i class="fa-regular fa-eye" id="pwIcon1"></i>
                      </button>
                    </div>
                    @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                  </div>

                  <div class="auth-form-group">
                    <label class="auth-label" for="password_confirmation">Confirm Password</label>
                    <div class="input-wrap">
                      <input type="password" id="password_confirmation" name="password_confirmation"
                             class="auth-input"
                             placeholder="Re-enter password"
                             autocomplete="new-password" required>
                      <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','pwIcon2')">
                        <i class="fa-regular fa-eye" id="pwIcon2"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="terms-row">
                  <input type="checkbox" id="terms" required>
                  <label for="terms">
                    I agree to the
                    <a href="#" target="_blank">Terms &amp; Conditions</a>
                    and <a href="#" target="_blank">Privacy Policy</a>
                  </label>
                </div>

                <button type="submit" class="btn-auth">
                  <i class="fa-solid fa-user-plus"></i>
                  Create Account
                </button>
              </form>

              <div class="auth-divider">or sign up with</div>

                  <div class="social-row">
                        <a href="{{ route('social.redirect', 'google') }}?type=customer" class="btn-social">
                            <i class="fa-brands fa-google"></i> Google
                        </a>
                        <a href="{{ route('social.redirect', 'facebook') }}?type=customer" class="btn-social">
                            <i class="fa-brands fa-facebook"></i> Facebook
                        </a>
                    </div>

              <div class="auth-foot">
                <p>Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
                <p>Want to sell on GenesisHub? <a href="{{ route('seller.register.form') }}">Apply as a seller</a></p>
              </div>

            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <section class="footer_one home1 bdrt1">
      <div class="container pb60">
        <div class="row">
          <div class="col-lg-6 offset-lg-3">
            <div class="mailchimp_widget mb30-md text-center">
              <div class="icon float-start"><span class="flaticon-email-1"></span></div>
              <div class="details">
                <h3 class="title">Subscribe and get 20% discount.</h3>
              </div>
            </div>
            <div class="footer_social_widget">
              <form class="footer_mailchimp_form">
                <div class="row align-items-center">
                  <div class="col-auto">
                    <input type="email" class="form-control" placeholder="Your email address">
                    <button class="ms-sm-2 btn-thm" type="submit">Subscribe</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
        <div class="row mt60">
          <div class="col-sm-6 col-md-5 col-lg-3 col-xl-3">
            <div class="footer_contact_widget">
              <h4>Contact Us</h4>
              <div class="footer_contact_iconbox d-flex mb-4">
                <div class="icon"><span class="flaticon-phone-call"></span></div>
                <div class="details ms-4">
                  <h5 class="title">Monday¨CFriday: 08am¨C9pm</h5>
                  <a href="#">+(1) 123 456 7890</a>
                </div>
              </div>
              <div class="footer_contact_iconbox d-flex">
                <div class="icon"><span class="flaticon-email"></span></div>
                <div class="details ms-4">
                  <h5 class="title">Need help with your order?</h5>
                  <a href="#">support@genesishub.com</a>
                </div>
              </div>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-lg-2 col-xl-2">
            <div class="footer_qlink_widget">
              <h4>About GenesisHub</h4>
              <ul class="list-unstyled">
                <li><a href="#">Track Your Order</a></li>
                <li><a href="#">Product Guides</a></li>
                <li><a href="#">Wishlists</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Store Locator</a></li>
              </ul>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-lg-2 col-xl-2">
            <div class="footer_qlink_widget">
              <h4>Customer Support</h4>
              <ul class="list-unstyled">
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">Help Centre</a></li>
                <li><a href="#">Returns &amp; Exchanges</a></li>
                <li><a href="#">Gift Cards</a></li>
                <li><a href="#">Financing</a></li>
              </ul>
            </div>
          </div>
          <div class="col-sm-6 col-md-3 col-lg-2 col-xl-2">
            <div class="footer_qlink_widget">
              <h4>Services</h4>
              <ul class="list-unstyled">
                <li><a href="#">Become a Seller</a></li>
                <li><a href="#">Trade-In Program</a></li>
                <li><a href="#">Electronics Recycling</a></li>
                <li><a href="#">GenesisHub Health</a></li>
              </ul>
            </div>
          </div>
          <div class="col-sm-8 col-md-5 col-lg-3 col-xl-3">
            <div class="footer_social_widget">
              <h4 class="title">Follow us</h4>
              <div class="social_icon_list mt30">
                <ul class="mb20">
                  <li class="list-inline-item"><a href="#"><i class="fab fa-facebook"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fab fa-x-twitter"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fab fa-instagram"></i></a></li>
                  <li class="list-inline-item"><a href="#"><i class="fab fa-linkedin-in"></i></a></li>
                </ul>
              </div>
            </div>
            <div class="footer_mobile_app_widget mb25">
              <h4 class="title mb10">Mobile Apps</h4>
              <div class="mobile_app_list">
                <ul class="mb0">
                  <li><a href="#"><span class="flaticon-apple"></span> iOS App</a></li>
                  <li><a href="#"><span class="flaticon-android"></span> Android App</a></li>
                </ul>
              </div>
            </div>
            <div class="footer_acceped_card_widget">
              <h4 class="title mb20">We accept</h4>
              <div class="acceped_card_list">
                <ul class="d-flex mb-0">
                  <li class="me-2"><a href="#"><img src="{{ asset('public/images/resource/visa-card.png') }}" alt="Visa"></a></li>
                  <li class="me-2"><a href="#"><img src="{{ asset('public/images/resource/master-card.png') }}" alt="Mastercard"></a></li>
                  <li class="me-2"><a href="#"><img src="{{ asset('public/images/resource/apple-pay.png') }}" alt="Apple Pay"></a></li>
                  <li class="me-2"><a href="#"><img src="{{ asset('public/images/resource/paypal.png') }}" alt="PayPal"></a></li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="container bdrt1 pt20 pb20">
        <div class="row">
          <div class="col-lg-6">
            <div class="copyright-widget text-center text-lg-start d-block d-lg-flex mb15-md">
              <p class="me-4">&copy; {{ date('Y') }} GenesisHub. All Rights Reserved</p>
              <p><a href="#">Privacy</a> ¡¤ <a href="#">Terms</a> ¡¤ <a href="#">Sitemap</a></p>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="footer_bottom_right_widgets text-center text-lg-end">
              <ul class="mb0">
                <li class="list-inline-item mb20-340">
                  <select class="selectpicker show-tick">
                    <option>Currency: NGN</option>
                    <option>USD</option>
                    <option>EUR</option>
                  </select>
                </li>
                <li class="list-inline-item">
                  <select class="selectpicker show-tick">
                    <option>Language: English</option>
                    <option>French</option>
                  </select>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>

<script src="{{ asset('public/js/jquery-3.6.0.js') }}"></script>
<script src="{{ asset('public/js/jquery-migrate-3.0.0.min.js') }}"></script>
<script src="{{ asset('public/js/popper.min.js') }}"></script>
<script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('public/js/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('public/js/jquery.mmenu.all.js') }}"></script>
<script src="{{ asset('public/js/ace-responsive-menu.js') }}"></script>
<script src="{{ asset('public/js/jquery-scrolltofixed-min.js') }}"></script>
<script src="{{ asset('public/js/wow.min.js') }}"></script>
<script src="{{ asset('public/js/script.js') }}"></script>
<script>
function togglePw(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
</body>
</html>
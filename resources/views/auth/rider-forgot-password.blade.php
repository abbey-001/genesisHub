{{-- resources/views/auth/rider-forgot-password.blade.php --}}
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password — Rider — {{ config('app.name', 'GenesisHub') }}</title>

<link href="{{ asset('public/image/auth-logo.png') }}" rel="shortcut icon" type="image/png">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">

<style>
*, *::before, *::after { box-sizing: border-box; }
body { font-family: 'Inter', sans-serif; background: #f3f5f6; color: #1a1a1a; }
.our-log-reg { background: #f3f5f6 !important; padding: 70px 0; min-height: 100vh; display: flex; align-items: center; }

.auth-card { background: #fff; border-radius: 20px; box-shadow: 0 12px 60px rgba(4,30,66,.09); padding: 48px 44px; }
@media (max-width: 575px) { .auth-card { padding: 36px 24px; } }

.auth-card__header { text-align: center; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 1.5px solid #f0f1f3; }
.auth-card__icon-wrap { display: inline-flex; align-items: center; justify-content: center; width: 68px; height: 68px; border-radius: 18px; background: rgba(113,78,50,.07); border: 1.5px solid rgba(113,78,50,.12); margin-bottom: 14px; }
.auth-card__icon-wrap i { font-size: 26px; color: #714e32; }
.auth-card__brand { font-family: 'Poppins', sans-serif; font-size: 22px; font-weight: 800; color: #714e32; margin-bottom: 4px; }
.auth-card__sub { font-size: 14px; color: #555e68; margin: 0; line-height: 1.6; }

.rider-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(113,78,50,.08); border: 1px solid rgba(113,78,50,.16); color: #714e32; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; padding: 4px 12px; border-radius: 100px; margin-top: 10px; }
.rider-badge i { font-size: 10px; }

.notice { display: flex; gap: 12px; align-items: flex-start; border-radius: 12px; padding: 14px 16px; margin-bottom: 22px; }
.notice__icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notice__icon i { font-size: 13px; }
.notice__title { font-size: 12.5px; font-weight: 700; margin-bottom: 2px; }
.notice__text  { font-size: 12px; line-height: 1.55; margin: 0; }
.notice--success { background: #f0fdf4; border: 1px solid #86efac; }
.notice--success .notice__icon { background: rgba(34,197,94,.1); }
.notice--success .notice__icon i { color: #15803d; }
.notice--success .notice__title { color: #14532d; }
.notice--success .notice__text  { color: #166534; }
.notice--danger { background: #fff4f4; border: 1px solid #ffc2c2; }
.notice--danger .notice__icon { background: rgba(229,62,62,.1); }
.notice--danger .notice__icon i { color: #c53030; }
.notice--danger .notice__title { color: #822020; }
.notice--danger .notice__text  { color: #9b2626; }
.notice--warning { background: #fdf8ec; border: 1px solid #f0d98a; }
.notice--warning .notice__icon { background: rgba(245,195,75,.18); }
.notice--warning .notice__icon i { color: #b87d00; }
.notice--warning .notice__title { color: #7a5700; }
.notice--warning .notice__text  { color: #886200; }
.notice--info { background: #f0f5ff; border: 1px solid #b3cdff; }
.notice--info .notice__icon { background: rgba(59,130,246,.1); }
.notice--info .notice__icon i { color: #1d4ed8; }
.notice--info .notice__title { color: #1e3a8a; }
.notice--info .notice__text  { color: #1e40af; }

.auth-form-group { margin-bottom: 20px; }
.auth-label { display: block; font-size: 13px; font-weight: 600; color: #2d2d2d; margin-bottom: 7px; }
.auth-input { display: block; width: 100%; height: 50px; padding: 0 14px; font-size: 14px; font-family: 'Inter', sans-serif; color: #1a1a1a; background: #fff; border: 1.5px solid #e0e4ea; border-radius: 10px; transition: border-color .2s, box-shadow .2s; -webkit-appearance: none; outline: none; }
.auth-input::placeholder { color: #b0b8c4; font-size: 13.5px; }
.auth-input:focus { border-color: #714e32; box-shadow: 0 0 0 3.5px rgba(113,78,50,.11); }
.auth-input.is-invalid { border-color: #e53e3e; }
.invalid-feedback { font-size: 12px; color: #e53e3e; margin-top: 5px; display: block; }

.btn-auth { display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 52px; background: #714e32; color: #fff; border: none; border-radius: 10px; font-size: 14.5px; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; letter-spacing: .025em; transition: background .2s, box-shadow .2s, transform .1s; }
.btn-auth:hover { background: #5a3d26; box-shadow: 0 8px 24px rgba(113,78,50,.3); }
.btn-auth:active { transform: scale(0.985); }

.auth-foot { display: flex; flex-direction: column; gap: 10px; align-items: center; margin-top: 22px; }
.auth-foot p { font-size: 13.5px; color: #555e68; margin: 0; }
.auth-foot a { color: #714e32; font-weight: 600; text-decoration: none; transition: color .2s; }
.auth-foot a:hover { color: #5a3d26; text-decoration: underline; }
</style>
</head>
<body>
<section class="our-log-reg">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 col-xl-5 col-xxl-4 m-auto">
        <div class="auth-card">

          <div class="auth-card__header">
            <div class="auth-card__icon-wrap">
              <i class="fa-solid fa-motorcycle"></i>
            </div>
            <h1 class="auth-card__brand">Forgot Password?</h1>
            <p class="auth-card__sub">Enter your rider account email and we'll send you a reset link.</p>
            <div class="rider-badge"><i class="fa-solid fa-circle-dot"></i> Rider Portal</div>
          </div>

          {{-- Success --}}
          @if(session('status'))
          <div class="notice notice--success">
            <div class="notice__icon"><i class="fa-solid fa-circle-check"></i></div>
            <div>
              <p class="notice__title">Reset link sent!</p>
              <p class="notice__text">{{ session('status') }} Check your spam folder if you don't see it within a few minutes.</p>
            </div>
          </div>
          @endif

          {{-- Social-only (future-proof) --}}
          @if(session('social_only'))
          <div class="notice notice--warning">
            <div class="notice__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div>
              <p class="notice__title">No password on this account</p>
              <p class="notice__text">This account was created using a social login. Please sign in with the same method you used when you registered.</p>
            </div>
          </div>
          @endif

          {{-- Errors --}}
          @if($errors->any())
          <div class="notice notice--danger">
            <div class="notice__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div>
              <p class="notice__title">Something went wrong</p>
              <p class="notice__text">{{ $errors->first() }}</p>
            </div>
          </div>
          @endif

          {{-- Info note --}}
          @unless(session('status'))
          <div class="notice notice--info" style="margin-bottom:22px;">
            <div class="notice__icon"><i class="fa-solid fa-circle-info"></i></div>
            <div>
              <p class="notice__title">Rider accounts only</p>
              <p class="notice__text">This page resets passwords for rider accounts. If you're a customer or seller, please use the relevant reset page for your account type.</p>
            </div>
          </div>
          @endunless

          @unless(session('status'))
          <form method="POST" action="{{ route('rider.password.email') }}">
            @csrf
            <div class="auth-form-group">
              <label class="auth-label" for="email">Email address</label>
              <input type="email" id="email" name="email"
                     class="auth-input @error('email') is-invalid @enderror"
                     value="{{ old('email') }}"
                     placeholder="you@example.com"
                     autocomplete="email" autofocus required>
              @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <button type="submit" class="btn-auth">
              <i class="fa-solid fa-paper-plane"></i>
              Send Reset Link
            </button>
          </form>
          @endunless

          <div class="auth-foot">
            <p><a href="{{ route('rider.login.form') }}"><i class="fa-solid fa-arrow-left" style="font-size:11px;margin-right:4px;"></i>Back to rider sign in</a></p>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>
</body>
</html>
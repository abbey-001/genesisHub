{{-- resources/views/auth/reset-password.blade.php --}}
{{-- Rendered by Fortify::resetPasswordView() in FortifyServiceProvider --}}
<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password ¡X {{ config('app.name', 'GenesisHub') }}</title>

<link href="{{ asset('public/image/auth-logo.png') }}" rel="shortcut icon" type="image/png">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="canonical" href="{{ url('register') }}">

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

.notice { display: flex; gap: 12px; align-items: flex-start; border-radius: 12px; padding: 14px 16px; margin-bottom: 22px; }
.notice__icon { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notice__icon i { font-size: 13px; }
.notice__title { font-size: 12.5px; font-weight: 700; margin-bottom: 2px; }
.notice__text  { font-size: 12px; line-height: 1.55; margin: 0; }
.notice--danger { background: #fff4f4; border: 1px solid #ffc2c2; }
.notice--danger .notice__icon { background: rgba(229,62,62,.1); }
.notice--danger .notice__icon i { color: #c53030; }
.notice--danger .notice__title { color: #822020; }
.notice--danger .notice__text  { color: #9b2626; }

.auth-form-group { margin-bottom: 20px; }
.auth-label { display: block; font-size: 13px; font-weight: 600; color: #2d2d2d; margin-bottom: 7px; }
.input-wrap { position: relative; }
.auth-input { display: block; width: 100%; height: 50px; padding: 0 14px; font-size: 14px; font-family: 'Inter', sans-serif; color: #1a1a1a; background: #fff; border: 1.5px solid #e0e4ea; border-radius: 10px; transition: border-color .2s, box-shadow .2s; -webkit-appearance: none; outline: none; }
.auth-input::placeholder { color: #b0b8c4; font-size: 13.5px; }
.auth-input:focus { border-color: #714e32; box-shadow: 0 0 0 3.5px rgba(113,78,50,.11); }
.auth-input.is-invalid { border-color: #e53e3e; }
.input-wrap .auth-input { padding-right: 44px; }
.invalid-feedback { font-size: 12px; color: #e53e3e; margin-top: 5px; display: block; }

.pw-toggle { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); background: none; border: none; padding: 0; cursor: pointer; color: #b0b8c4; font-size: 15px; line-height: 1; transition: color .2s; z-index: 2; }
.pw-toggle:hover { color: #714e32; }

.password-rules { background: #f9fafb; border: 1px solid #e8eaed; border-radius: 8px; padding: 12px 14px; margin-bottom: 20px; }
.password-rules p { font-size: 12px; font-weight: 600; color: #555e68; margin-bottom: 8px; }
.password-rules ul { margin: 0; padding-left: 18px; }
.password-rules li { font-size: 12px; color: #8a94a6; line-height: 1.6; }

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
              <i class="fa-solid fa-lock-open"></i>
            </div>
            <h1 class="auth-card__brand">Set New Password</h1>
            <p class="auth-card__sub">Choose a strong password for your account.</p>
          </div>

          @if($errors->any())
          <div class="notice notice--danger">
            <div class="notice__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
            <div>
              <p class="notice__title">Something went wrong</p>
              <p class="notice__text">{{ $errors->first() }}</p>
            </div>
          </div>
          @endif

          <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div class="auth-form-group">
              <label class="auth-label" for="email">Email address</label>
              <input type="email" id="email" name="email"
                     class="auth-input @error('email') is-invalid @enderror"
                     value="{{ old('email', $request->email) }}"
                     placeholder="you@example.com"
                     autocomplete="email" required>
              @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="auth-form-group">
              <label class="auth-label" for="password">New Password</label>
              <div class="input-wrap">
                <input type="password" id="password" name="password"
                       class="auth-input @error('password') is-invalid @enderror"
                       placeholder="Create a strong password"
                       autocomplete="new-password" required>
                <button type="button" class="pw-toggle" onclick="togglePw('password','pwIcon1')">
                  <i class="fa-regular fa-eye" id="pwIcon1"></i>
                </button>
              </div>
              @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
            </div>

            <div class="auth-form-group">
              <label class="auth-label" for="password_confirmation">Confirm New Password</label>
              <div class="input-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="auth-input"
                       placeholder="Re-enter your new password"
                       autocomplete="new-password" required>
                <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','pwIcon2')">
                  <i class="fa-regular fa-eye" id="pwIcon2"></i>
                </button>
              </div>
            </div>

            <div class="password-rules">
              <p>Password must:</p>
              <ul>
                <li>Be at least 8 characters long</li>
                <li>Contain at least one uppercase letter</li>
                <li>Contain at least one number</li>
              </ul>
            </div>

            <button type="submit" class="btn-auth">
              <i class="fa-solid fa-shield-check"></i>
              Reset Password
            </button>
          </form>

          <div class="auth-foot">
            <p><a href="{{ route('login') }}"><i class="fa-solid fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Back to sign in</a></p>
          </div>

        </div>
      </div>
    </div>
  </div>
</section>

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
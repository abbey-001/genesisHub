{{-- resources/views/auth/seller-forgot-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Forgot Password — Seller Portal — {{ config('app.name', 'GenesisHub') }}</title>

<link rel="icon" type="image/png" href="{{ asset('public/image/auth-logo.png') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">

<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: 'Inter', sans-serif; background: #f3f5f6; color: #1a1a1a; }
.auth-shell { display: flex; min-height: 100vh; }

/* LEFT PANEL */
.auth-panel { flex: 0 0 46%; background: #714e32; position: relative; display: flex; flex-direction: column; justify-content: space-between; padding: 48px 56px; overflow: hidden; }
.auth-panel::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle at 15% 15%, rgba(245,195,75,.14) 0%, transparent 55%), radial-gradient(circle at 85% 85%, rgba(255,255,255,.05) 0%, transparent 50%); pointer-events: none; z-index: 0; }
.auth-panel__ring { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.08); pointer-events: none; z-index: 0; }
.auth-panel__ring--lg { width: 460px; height: 460px; bottom: -140px; right: -130px; }
.auth-panel__ring--sm { width: 200px; height: 200px; top: 90px; right: 50px; border-color: rgba(245,195,75,.12); }
.auth-panel__logo { position: relative; z-index: 1; }
.auth-panel__logo img { height: 42px; display: block; filter: brightness(0) invert(1); }
.auth-panel__body { position: relative; z-index: 1; flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 64px 0 44px; }
.auth-panel__pill { display: inline-flex; align-items: center; gap: 7px; background: rgba(245,195,75,.16); border: 1px solid rgba(245,195,75,.32); color: #f5c34b; font-size: 11.5px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; padding: 5px 14px; border-radius: 100px; margin-bottom: 26px; width: fit-content; }
.auth-panel__heading { font-family: 'Poppins', sans-serif; font-size: clamp(26px, 3vw, 36px); font-weight: 800; line-height: 1.22; color: #fff; margin-bottom: 18px; }
.auth-panel__heading em { font-style: normal; color: #f5c34b; }
.auth-panel__desc { font-size: 14.5px; line-height: 1.72; color: rgba(255,255,255,.62); max-width: 330px; margin-bottom: 36px; }
.auth-panel__tip { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12); border-radius: 12px; padding: 16px 18px; }
.auth-panel__tip-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.4); margin-bottom: 10px; }
.auth-panel__tip-item { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px; }
.auth-panel__tip-item:last-child { margin-bottom: 0; }
.auth-panel__tip-item i { font-size: 12px; color: #f5c34b; margin-top: 3px; flex-shrink: 0; }
.auth-panel__tip-item span { font-size: 13px; color: rgba(255,255,255,.7); line-height: 1.5; }
.auth-panel__footer { position: relative; z-index: 1; font-size: 12px; color: rgba(255,255,255,.35); }

/* RIGHT PANEL */
.auth-form-side { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 40px; overflow-y: auto; background: #f3f5f6; }
.auth-form-box { width: 100%; max-width: 440px; }
.auth-mobile-logo { display: none; text-align: center; margin-bottom: 36px; }
.auth-mobile-logo img { height: 38px; }

.form-header { margin-bottom: 34px; }
.form-header__eyebrow { font-size: 11.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #714e32; margin-bottom: 9px; }
.form-header__title { font-family: 'Poppins', sans-serif; font-size: 27px; font-weight: 700; color: #1a1a1a; line-height: 1.25; margin-bottom: 6px; }
.form-header__sub { font-size: 14px; color: #555e68; line-height: 1.55; }

.notice { display: flex; gap: 13px; align-items: flex-start; border-radius: 12px; padding: 15px 17px; margin-bottom: 24px; }
.notice__icon { width: 34px; height: 34px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.notice__icon i { font-size: 14px; }
.notice__title { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
.notice__text  { font-size: 12.5px; line-height: 1.55; margin: 0; }
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

.form-group { margin-bottom: 20px; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #2d2d2d; margin-bottom: 7px; }
.form-control { display: block; width: 100%; height: 50px; padding: 0 14px; font-size: 14px; font-family: 'Inter', sans-serif; color: #1a1a1a; background: #fff; border: 1.5px solid #e0e4ea; border-radius: 10px; transition: border-color .2s, box-shadow .2s; -webkit-appearance: none; }
.form-control::placeholder { color: #b0b8c4; font-size: 13.5px; }
.form-control:focus { outline: none; border-color: #714e32; box-shadow: 0 0 0 3.5px rgba(113,78,50,.11); }
.form-control.is-invalid { border-color: #e53e3e; }
.invalid-feedback { font-size: 12px; color: #e53e3e; margin-top: 5px; display: block; }

.btn-submit { display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 52px; background: #714e32; color: #fff; border: none; border-radius: 10px; font-size: 14.5px; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; letter-spacing: .025em; transition: background .2s, box-shadow .2s, transform .1s; }
.btn-submit:hover { background: #5a3d26; box-shadow: 0 8px 24px rgba(113,78,50,.3); }
.btn-submit:active { transform: scale(0.985); }

.auth-foot { display: flex; flex-direction: column; gap: 11px; align-items: center; margin-top: 28px; }
.auth-foot p { font-size: 13.5px; color: #555e68; margin: 0; }
.auth-foot a { color: #714e32; font-weight: 600; text-decoration: none; transition: color .2s; }
.auth-foot a:hover { color: #5a3d26; text-decoration: underline; }

@media (max-width: 900px) { .auth-panel { flex: 0 0 40%; padding: 40px 36px; } }
@media (max-width: 720px) { .auth-panel { display: none; } .auth-mobile-logo { display: block; } .auth-form-side { padding: 48px 24px; justify-content: flex-start; } .auth-form-box { max-width: 100%; } }
</style>
</head>
<body>
<div class="auth-shell">

    {{-- LEFT PANEL --}}
    <aside class="auth-panel">
        <div class="auth-panel__ring auth-panel__ring--lg"></div>
        <div class="auth-panel__ring auth-panel__ring--sm"></div>

        <div class="auth-panel__logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('public/image/genehub.png') }}" alt="{{ config('app.name') }}">
            </a>
        </div>

        <div class="auth-panel__body">
            <div class="auth-panel__pill">
                <i class="fa-solid fa-store fa-xs"></i>
                Seller Portal
            </div>
            <h2 class="auth-panel__heading">
                Reset your<br><em>seller account</em><br>password
            </h2>
            <p class="auth-panel__desc">
                We'll send a secure link to your registered email address. Click it to set a new password and regain access to your shop.
            </p>
            <div class="auth-panel__tip">
                <p class="auth-panel__tip-label">Things to check</p>
                <div class="auth-panel__tip-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Make sure you're using the email linked to your seller account</span>
                </div>
                <div class="auth-panel__tip-item">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>Check your spam or junk folder if the email doesn't arrive</span>
                </div>
                <div class="auth-panel__tip-item">
                    <i class="fa-solid fa-clock"></i>
                    <span>The reset link expires after 60 minutes</span>
                </div>
            </div>
        </div>

        <div class="auth-panel__footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'GenesisHub') }}. All rights reserved.
        </div>
    </aside>

    {{-- RIGHT PANEL --}}
    <main class="auth-form-side">
        <div class="auth-form-box">

            <div class="auth-mobile-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('public/image/auth-logo.png') }}" alt="{{ config('app.name') }}">
                </a>
            </div>

            <div class="form-header">
                <p class="form-header__eyebrow">Seller Portal</p>
                <h1 class="form-header__title">Forgot your password?</h1>
                <p class="form-header__sub">Enter your seller account email and we'll send you a reset link.</p>
            </div>

            {{-- Success --}}
            @if(session('status'))
            <div class="notice notice--success">
                <div class="notice__icon"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <p class="notice__title">Reset link sent!</p>
                    <p class="notice__text">{{ session('status') }}</p>
                </div>
            </div>
            @endif

            {{-- Social-only account --}}
            @if(session('social_only'))
            <div class="notice notice--warning">
                <div class="notice__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                <div>
                    <p class="notice__title">No password on this account</p>
                    <p class="notice__text">
                        This seller account was created using Google or Facebook. Please return to the
                        <a href="{{ route('seller.login.form') }}" style="color:inherit;font-weight:700;text-decoration:underline;">seller login page</a>
                        and sign in with the same social login button you used when you registered.
                    </p>
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

            @unless(session('status'))
            <form method="POST" action="{{ route('seller.password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" id="email" name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           autocomplete="email" autofocus required>
                    @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-paper-plane"></i>
                    Send Reset Link
                </button>
            </form>
            @endunless

            <div class="auth-foot">
                <p><a href="{{ route('seller.login.form') }}"><i class="fa-solid fa-arrow-left" style="font-size:11px; margin-right:4px;"></i>Back to seller sign in</a></p>
                <p>Don't have an account? <a href="{{ route('seller.register.form') }}">Create seller account</a></p>
            </div>

        </div>
    </main>

</div>
</body>
</html>
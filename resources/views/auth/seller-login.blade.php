<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Seller Login — {{ config('app.name', 'GenesisHub') }}</title>
<meta name="description" content="Sign in to your GenesisHub seller account.">

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('public/image/auth-logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('public/image/auth-logo.png') }}">

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Google Fonts: Inter + Poppins -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="canonical" href="{{ url('seller/login') }}">

<!-- Bootstrap -->
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">

<style>
/* ─────────────────────────────────────────────────────────
   Design tokens
   Primary  : #714e32  (warm dark brown — brand)
   Primary-D: #5a3d26  (darker hover)
   Primary-L: #f5ede4  (light tint)
   Accent   : #f5c34b  (golden yellow)
   Page-bg  : #f3f5f6
   Text-dark: #1a1a1a
   Text-mid : #555e68
   Text-lite: #8a94a6
   Border   : #e0e4ea
───────────────────────────────────────────────────────── */

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%;
    font-family: 'Inter', sans-serif;
    background: #f3f5f6;
    color: #1a1a1a;
}

/* ── Layout shell ───────────────────────────────────────── */
.auth-shell {
    display: flex;
    min-height: 100vh;
}

/* ══════════════════════════════════════════════════════════
   LEFT PANEL — brand showcase
══════════════════════════════════════════════════════════ */
.auth-panel {
    flex: 0 0 46%;
    background: #714e32;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 56px;
    overflow: hidden;
}

/* Layered radial glow overlays */
.auth-panel::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 15% 15%, rgba(245,195,75,.14) 0%, transparent 55%),
        radial-gradient(circle at 85% 85%, rgba(255,255,255,.05) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

/* Decorative circles */
.auth-panel__ring {
    position: absolute;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.08);
    pointer-events: none;
    z-index: 0;
}

.auth-panel__ring--lg {
    width: 460px;
    height: 460px;
    bottom: -140px;
    right: -130px;
}

.auth-panel__ring--sm {
    width: 200px;
    height: 200px;
    top: 90px;
    right: 50px;
    border-color: rgba(245,195,75,.12);
}

/* ── Logo ── */
.auth-panel__logo {
    position: relative;
    z-index: 1;
}

.auth-panel__logo img {
    height: 42px;
    display: block;
    /* Ensures logo is always visible against dark bg */
    filter: brightness(0) invert(1);
}

/* ── Body copy ── */
.auth-panel__body {
    position: relative;
    z-index: 1;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 64px 0 44px;
}

.auth-panel__pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(245,195,75,.16);
    border: 1px solid rgba(245,195,75,.32);
    color: #f5c34b;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 100px;
    margin-bottom: 26px;
    width: fit-content;
}

.auth-panel__heading {
    font-family: 'Poppins', sans-serif;
    font-size: clamp(26px, 3vw, 36px);
    font-weight: 800;
    line-height: 1.22;
    color: #ffffff;
    margin-bottom: 18px;
}

.auth-panel__heading em {
    font-style: normal;
    color: #f5c34b;
}

.auth-panel__desc {
    font-size: 14.5px;
    line-height: 1.72;
    color: rgba(255,255,255,.62);
    max-width: 330px;
    margin-bottom: 42px;
}

/* Feature list */
.auth-features { display: flex; flex-direction: column; gap: 13px; }

.auth-feature {
    display: flex;
    align-items: center;
    gap: 13px;
}

.auth-feature__dot {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.11);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.auth-feature__dot i {
    font-size: 14px;
    color: #f5c34b;
}

.auth-feature__label {
    font-size: 13.5px;
    color: rgba(255,255,255,.72);
}

/* ── Panel footer ── */
.auth-panel__footer {
    position: relative;
    z-index: 1;
    font-size: 12px;
    color: rgba(255,255,255,.35);
}

/* ══════════════════════════════════════════════════════════
   RIGHT PANEL — form side
══════════════════════════════════════════════════════════ */
.auth-form-side {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 40px;
    overflow-y: auto;
    background: #f3f5f6;
}

.auth-form-box {
    width: 100%;
    max-width: 440px;
}

/* Mobile-only logo */
.auth-mobile-logo {
    display: none;
    text-align: center;
    margin-bottom: 36px;
}

.auth-mobile-logo img { height: 38px; }

/* ── Form header ── */
.form-header { margin-bottom: 34px; }

.form-header__eyebrow {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #714e32;
    margin-bottom: 9px;
}

.form-header__title {
    font-family: 'Poppins', sans-serif;
    font-size: 27px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.25;
    margin-bottom: 6px;
}

.form-header__sub {
    font-size: 14px;
    color: #555e68;
    line-height: 1.55;
}

/* ── Notice cards ───────────────────────────────────────── */
.notice {
    display: flex;
    gap: 13px;
    align-items: flex-start;
    border-radius: 12px;
    padding: 15px 17px;
    margin-bottom: 24px;
}

.notice__icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-top: 1px;
}

.notice__icon i { font-size: 14px; }
.notice__title { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
.notice__text  { font-size: 12.5px; line-height: 1.55; margin: 0; }

/* Variants */
.notice--warning { background:#fdf8ec; border:1px solid #f0d98a; }
.notice--warning .notice__icon { background:rgba(245,195,75,.18); }
.notice--warning .notice__icon i { color:#b87d00; }
.notice--warning .notice__title { color:#7a5700; }
.notice--warning .notice__text  { color:#886200; }

.notice--danger { background:#fff4f4; border:1px solid #ffc2c2; }
.notice--danger .notice__icon { background:rgba(229,62,62,.1); }
.notice--danger .notice__icon i { color:#c53030; }
.notice--danger .notice__title { color:#822020; }
.notice--danger .notice__text  { color:#9b2626; }

.notice--info { background:#f0f5ff; border:1px solid #b3cdff; }
.notice--info .notice__icon { background:rgba(59,130,246,.1); }
.notice--info .notice__icon i { color:#1d4ed8; }
.notice--info .notice__title { color:#1e3a8a; }
.notice--info .notice__text  { color:#1e40af; }

.notice--success { background:#f0fdf4; border:1px solid #86efac; }
.notice--success .notice__icon { background:rgba(34,197,94,.1); }
.notice--success .notice__icon i { color:#15803d; }
.notice--success .notice__title { color:#14532d; }
.notice--success .notice__text  { color:#166534; }

/* ── Form fields ────────────────────────────────────────── */
.form-group { margin-bottom: 20px; }

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #2d2d2d;
    margin-bottom: 7px;
}

.input-wrap { position: relative; }

.form-control {
    display: block;
    width: 100%;
    height: 50px;
    padding: 0 14px;
    font-size: 14px;
    font-family: 'Inter', sans-serif;
    color: #1a1a1a;
    background: #ffffff;
    border: 1.5px solid #e0e4ea;
    border-radius: 10px;
    transition: border-color .2s, box-shadow .2s;
    -webkit-appearance: none;
}

.form-control::placeholder { color: #b0b8c4; font-size: 13.5px; }

.form-control:focus {
    outline: none;
    border-color: #714e32;
    box-shadow: 0 0 0 3.5px rgba(113,78,50,.11);
}

.form-control.is-invalid { border-color: #e53e3e; }
.form-control.is-invalid:focus { box-shadow: 0 0 0 3px rgba(229,62,62,.12); }

.invalid-feedback {
    font-size: 12px;
    color: #e53e3e;
    margin-top: 5px;
    display: block;
}

/* Password toggle */
.pw-toggle {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #b0b8c4;
    font-size: 15px;
    line-height: 1;
    transition: color .2s;
    z-index: 2;
}
.pw-toggle:hover { color: #714e32; }
.input-wrap .form-control { padding-right: 44px; }

/* ── Extras row ─────────────────────────────────────────── */
.form-extras {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 26px;
}

.form-check { display: flex; align-items: center; gap: 8px; }

.form-check-input {
    width: 16px;
    height: 16px;
    border: 1.5px solid #c8cdd5;
    border-radius: 4px;
    cursor: pointer;
    accent-color: #714e32;
    flex-shrink: 0;
}

.form-check-label {
    font-size: 13.5px;
    color: #555e68;
    cursor: pointer;
    user-select: none;
}

.form-forgot {
    font-size: 13.5px;
    font-weight: 600;
    color: #714e32;
    text-decoration: none;
    transition: color .2s;
}
.form-forgot:hover { color: #5a3d26; text-decoration: underline; }

/* ── Submit button ──────────────────────────────────────── */
.btn-submit {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    height: 52px;
    background: #714e32;
    color: #ffffff;
    border: none;
    border-radius: 10px;
    font-size: 14.5px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    letter-spacing: .025em;
    transition: background .2s, box-shadow .2s, transform .1s;
}

.btn-submit:hover {
    background: #5a3d26;
    box-shadow: 0 8px 24px rgba(113,78,50,.3);
}

.btn-submit:active { transform: scale(0.985); }

/* ── Divider ────────────────────────────────────────────── */
.auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 26px 0 22px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: #b0b8c4;
}
.auth-divider::before,
.auth-divider::after { content: ''; flex: 1; height: 1px; background: #e8eaed; }

/* ── Footer links ───────────────────────────────────────── */
.auth-foot {
    display: flex;
    flex-direction: column;
    gap: 11px;
    align-items: center;
}

.auth-foot p { font-size: 13.5px; color: #555e68; margin: 0; }

.auth-foot a {
    color: #714e32;
    font-weight: 600;
    text-decoration: none;
    transition: color .2s;
}
.auth-foot a:hover { color: #5a3d26; text-decoration: underline; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 900px) {
    .auth-panel { flex: 0 0 40%; padding: 40px 36px; }
    .auth-panel__heading { font-size: 24px; }
}

@media (max-width: 720px) {
    .auth-panel { display: none; }
    .auth-mobile-logo { display: block; }
    .auth-form-side { padding: 48px 24px; justify-content: flex-start; }
    .auth-form-box { max-width: 100%; }
}
</style>
</head>
<body>

<div class="auth-shell">

    {{-- ══════════════════════════════════════════════════
         LEFT — Brand panel
    ══════════════════════════════════════════════════ --}}
    <aside class="auth-panel">
        <div class="auth-panel__ring auth-panel__ring--lg"></div>
        <div class="auth-panel__ring auth-panel__ring--sm"></div>

        {{-- Logo --}}
        <div class="auth-panel__logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('public/image/genehub.png') }}"
                     alt="{{ config('app.name', 'GenesisHub') }}">
            </a>
        </div>

        {{-- Headline copy --}}
        <div class="auth-panel__body">
            <div class="auth-panel__pill">
                <i class="fa-solid fa-store fa-xs"></i>
                Seller Portal
            </div>

            <h2 class="auth-panel__heading">
                Grow your business<br>with <em>GenesisHub</em>
            </h2>

            <p class="auth-panel__desc">
                Reach thousands of customers, manage your shop and products, and receive fast payouts — all from one powerful dashboard.
            </p>

            <div class="auth-features">
                <div class="auth-feature">
                    <div class="auth-feature__dot"><i class="fa-solid fa-chart-line"></i></div>
                    <span class="auth-feature__label">Real-time sales analytics &amp; insights</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature__dot"><i class="fa-solid fa-box-open"></i></div>
                    <span class="auth-feature__label">Easy product and inventory management</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature__dot"><i class="fa-solid fa-wallet"></i></div>
                    <span class="auth-feature__label">Fast, secure seller payouts</span>
                </div>
                <div class="auth-feature">
                    <div class="auth-feature__dot"><i class="fa-solid fa-headset"></i></div>
                    <span class="auth-feature__label">Dedicated seller support team</span>
                </div>
            </div>
        </div>

        <div class="auth-panel__footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'GenesisHub') }}. All rights reserved.
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════
         RIGHT — Form side
    ══════════════════════════════════════════════════ --}}
    <main class="auth-form-side">
        <div class="auth-form-box">

            {{-- Mobile logo (left panel hidden on small screens) --}}
            <div class="auth-mobile-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('public/image/auth-logo.png') }}"
                         alt="{{ config('app.name', 'GenesisHub') }}">
                </a>
            </div>

            {{-- Form header --}}
            <div class="form-header">
                <p class="form-header__eyebrow">Seller Portal</p>
                <h1 class="form-header__title">Welcome back</h1>
                <p class="form-header__sub">Sign in to manage your shop and orders</p>
            </div>

            {{-- Verification status notices --}}
            @if(session('verification_pending'))
                @php $vstatus = session('verification_pending'); @endphp
                @if($vstatus === 'pending')
                <div class="notice notice--warning">
                    <div class="notice__icon"><i class="fa-solid fa-clock"></i></div>
                    <div>
                        <p class="notice__title">Account under review</p>
                        <p class="notice__text">Your seller application is being reviewed by our team. You'll receive an email once approved — typically within 1–2 business days.</p>
                    </div>
                </div>
                @elseif($vstatus === 'rejected')
                <div class="notice notice--danger">
                    <div class="notice__icon"><i class="fa-solid fa-ban"></i></div>
                    <div>
                        <p class="notice__title">Application not approved</p>
                        <p class="notice__text">Your seller application was not approved. Please contact <a href="mailto:support@genesishub.com" style="color:inherit;font-weight:700;">support@genesishub.com</a> for further assistance.</p>
                    </div>
                </div>
                @else
                <div class="notice notice--info">
                    <div class="notice__icon"><i class="fa-solid fa-circle-info"></i></div>
                    <div>
                        <p class="notice__title">Access restricted</p>
                        <p class="notice__text">Your account is not currently authorised to access the seller portal.</p>
                    </div>
                </div>
                @endif
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
            <div class="notice notice--danger">
                <div class="notice__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div>
                    <p class="notice__title">Unable to sign in</p>
                    <p class="notice__text">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif

            {{-- Success flash --}}
            @if(session('success'))
            <div class="notice notice--success">
                <div class="notice__icon"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <p class="notice__title">All done!</p>
                    <p class="notice__text">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            {{-- Login form --}}
            <form method="POST" action="{{ route('seller.login') }}" novalidate>
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="you@example.com"
                           autocomplete="email"
                           autofocus
                           required>
                    @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-wrap">
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Enter your password"
                               autocomplete="current-password"
                               required>
                        <button type="button" class="pw-toggle" aria-label="Toggle password visibility"
                                onclick="togglePw()">
                            <i class="fa-regular fa-eye" id="pwIcon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-extras">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="{{ route('seller.password.request') }}" class="form-forgot">Forgot password?</a>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Sign in to your account
                </button>
            </form>

                <div class="auth-divider">or continue with</div>

                <div class="social-row" style="display:flex; gap:12px; margin-bottom:20px;">
                    <a href="{{ route('social.redirect', 'google') }}?type=seller" class="btn-social"
                       style="flex:1; display:flex; align-items:center; justify-content:center; gap:8px;
                              height:46px; border:1.5px solid #e0e4ea; border-radius:10px; background:#fff;
                              font-size:14px; font-weight:600; color:#1a1a1a; text-decoration:none;
                              transition:border-color .2s, box-shadow .2s;">
                        <i class="fa-brands fa-google" style="color:#EA4335;font-size:16px;"></i> Google
                    </a>
                    <a href="{{ route('social.redirect', 'facebook') }}?type=seller" class="btn-social"
                       style="flex:1; display:flex; align-items:center; justify-content:center; gap:8px;
                              height:46px; border:1.5px solid #e0e4ea; border-radius:10px; background:#fff;
                              font-size:14px; font-weight:600; color:#1a1a1a; text-decoration:none;
                              transition:border-color .2s, box-shadow .2s;">
                        <i class="fa-brands fa-facebook" style="color:#1877F2;font-size:16px;"></i> Facebook
                    </a>
                </div>

            <div class="auth-foot">
                <p>New to GenesisHub? <a href="{{ route('seller.register.form') }}">Create a seller account</a></p>
                <p><a href="{{ route('login') }}"><i class="fa-regular fa-user fa-xs" style="margin-right:4px;"></i>Customer login</a></p>
            </div>

        </div>
    </main>

</div>{{-- .auth-shell --}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
<script>
function togglePw() {
    var input = document.getElementById('password');
    var icon  = document.getElementById('pwIcon');
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
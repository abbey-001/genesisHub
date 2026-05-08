{{-- resources/views/auth/seller-verify-email.blade.php --}}
{{-- 
    Route: GET /seller/verify-email → name('seller.verification.notice')
    Redirect to this after SellerRegisterController::register() 
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email — Seller Portal | GenesisHub</title>
    <link rel="icon" type="image/png" href="{{ asset('public/image/auth-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* ── Tokens ──────────────────────────────────────── */
        :root {
            --primary:   #714e32;
            --primary-d: #5a3d26;
            --accent:    #f5c34b;
            --accent-d:  #e8a820;
            --bg:        #f3f5f6;
            --surface:   #ffffff;
            --text:      #1a1a1a;
            --mid:       #555e68;
            --muted:     #9ca3af;
            --border:    #e0e4ea;
        }

        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        /* ── Card ─────────────────────────────────────────── */
        .card {
            width: 100%;
            max-width: 520px;
            background: var(--surface);
            border-radius: 16px;
            overflow: hidden;
            box-shadow:
                0 1px 3px rgba(0,0,0,.06),
                0 12px 40px rgba(113,78,50,.12);
        }

        /* ── Header — two-column like the auth panel ──────── */
        .card-header {
            background: var(--primary);
            padding: 24px 36px;
            display: flex;
            align-items: center;
            gap: 18px;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 15%, rgba(245,195,75,.14) 0%, transparent 55%),
                radial-gradient(circle at 85% 85%, rgba(255,255,255,.05) 0%, transparent 50%);
            pointer-events: none;
        }

        .card-header::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.07);
            bottom: -80px; right: -50px;
        }

        .header-logo-wrap { position: relative; z-index: 1; flex-shrink: 0; }

        .logo {
            height: 36px;
            display: block;
            filter: brightness(0) invert(1);
        }

        .header-text { position: relative; z-index: 1; }

        .brand-name {
            font-family: 'Poppins', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
            line-height: 1;
        }

        .brand-name span { color: var(--accent); }

        .portal-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(245,195,75,.16);
            border: 1px solid rgba(245,195,75,.32);
            color: var(--accent);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            padding: 3px 10px;
            border-radius: 100px;
            margin-top: 7px;
        }

        .portal-pill i { font-size: 9px; }

        /* Gold stripe */
        .header-stripe {
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-d), var(--accent));
        }

        /* ── Body ─────────────────────────────────────────── */
        .card-body { padding: 32px 36px 40px; }

        /* ── Progress stepper ─────────────────────────────── */
        .stepper {
            display: flex;
            align-items: center;
            margin-bottom: 32px;
        }

        .stepper-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
        }

        .stepper-dot {
            width: 32px; height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 700;
            margin-bottom: 7px;
            transition: all .3s;
        }

        .stepper-dot.done {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 2px 8px rgba(113,78,50,.25);
        }

        .stepper-dot.active {
            background: var(--accent);
            color: #5a3d00;
            box-shadow: 0 2px 10px rgba(245,195,75,.40);
        }

        .stepper-dot.upcoming {
            background: #f3f5f6;
            color: var(--muted);
            border: 1.5px solid var(--border);
        }

        .stepper-label {
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: var(--muted);
            text-align: center;
            white-space: nowrap;
        }

        .stepper-label.active-label { color: var(--primary); }

        .stepper-line {
            flex: 1;
            height: 2px;
            background: var(--border);
            margin: 0 6px;
            margin-bottom: 28px;
            transition: background .3s;
        }

        .stepper-line.done-line { background: var(--primary); }

        /* ── Alert ─────────────────────────────────────────── */
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 22px;
        }

        .alert-success { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
        .alert i { flex-shrink: 0; font-size: 14px; }

        /* ── Context pill ──────────────────────────────────── */
        .context-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(113,78,50,.07);
            border: 1px solid rgba(113,78,50,.13);
            color: var(--primary);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 12px;
        }

        .context-chip i { font-size: 9px; color: var(--accent-d); }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .card-desc {
            font-size: 14px;
            color: var(--mid);
            line-height: 1.72;
            margin-bottom: 18px;
        }

        /* Email pill */
        .email-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f9fafb;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 9px 16px;
            font-size: 13.5px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 22px;
        }

        .email-pill i { font-size: 13px; color: var(--muted); }

        /* ── What's next card ──────────────────────────────── */
        .next-card {
            background: #f9fafb;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 18px 20px;
            margin: 6px 0 22px;
        }

        .next-card-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 16px;
        }

        .next-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .next-item:last-child { margin-bottom: 0; }

        .next-icon {
            flex-shrink: 0;
            width: 30px; height: 30px;
            background: var(--primary);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 1px;
        }

        .next-icon i { font-size: 13px; color: var(--accent); }

        .next-text { font-size: 13px; color: var(--mid); line-height: 1.6; }
        .next-text strong { display: block; color: var(--text); font-weight: 600; font-size: 13px; margin-bottom: 1px; }

        /* Divider */
        .divider { border: none; border-top: 1px solid var(--border); margin: 22px 0; }

        /* ── Button ────────────────────────────────────────── */
        .btn {
            display: block;
            width: 100%;
            padding: 13px 24px;
            background: var(--primary);
            color: #fff;
            font-family: 'Poppins', 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            border: none;
            border-radius: 8px;
            border-bottom: 3px solid var(--accent);
            cursor: pointer;
            text-align: center;
            letter-spacing: .02em;
            transition: background .2s, transform .15s;
        }

        .btn:hover { background: var(--primary-d); transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        /* ── Footer link ───────────────────────────────────── */
        .foot-link { text-align: center; margin-top: 18px; }

        .foot-link a {
            font-size: 12.5px;
            color: var(--muted);
            text-decoration: none;
            transition: color .2s;
        }

        .foot-link a:hover { color: var(--primary); }

        /* ── Page footer ───────────────────────────────────── */
        .page-foot {
            margin-top: 20px;
            font-size: 11.5px;
            color: #b0bac4;
            text-align: center;
        }

        .page-foot a { color: #9ca3af; text-decoration: none; }

        /* ── Mobile ────────────────────────────────────────── */
        @media (max-width: 540px) {
            .card-header { padding: 20px 24px; }
            .card-body   { padding: 26px 24px 32px; }
            .stepper-label { display: none; }
        }
    </style>
</head>
<body>

<div class="card">

    {{-- Header --}}
    <div class="card-header">
        <div class="header-logo-wrap">
            <img src="{{ asset('public/image/auth-logo.png') }}"
                 alt="GenesisHub" class="logo" height="36">
        </div>
        <div class="header-text">
            <div class="brand-name">Genesis<span>Hub</span></div>
            <div class="portal-pill">
                <i class="fa-solid fa-store"></i>
                Seller Portal
            </div>
        </div>
    </div>
    <div class="header-stripe"></div>

    <div class="card-body">

        {{-- Progress stepper --}}
        <div class="stepper">
            <div class="stepper-item">
                <div class="stepper-dot done">
                    <i class="fa-solid fa-check" style="font-size:11px;"></i>
                </div>
                <div class="stepper-label">Created</div>
            </div>
            <div class="stepper-line done-line"></div>
            <div class="stepper-item">
                <div class="stepper-dot active">2</div>
                <div class="stepper-label active-label">Verify</div>
            </div>
            <div class="stepper-line"></div>
            <div class="stepper-item">
                <div class="stepper-dot upcoming">3</div>
                <div class="stepper-label">Review</div>
            </div>
            <div class="stepper-line"></div>
            <div class="stepper-item">
                <div class="stepper-dot upcoming">4</div>
                <div class="stepper-label">Live</div>
            </div>
        </div>

        {{-- Success alert --}}
        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                A new verification link has been sent to your email.
            </div>
        @endif

        <div class="context-chip">
            <i class="fa-solid fa-envelope-circle-check"></i>
            Step 2 of 4
        </div>

        <h1 class="card-title">Verify your email</h1>
        <p class="card-desc">
            Your seller account has been created. Before we can review your
            application, please confirm the email address associated with your account.
        </p>

        @auth
            <div class="email-pill">
                <i class="fa-regular fa-envelope"></i>
                {{ auth()->user()->email }}
            </div>
        @endauth

        {{-- What happens next --}}
        <div class="next-card">
            <div class="next-card-title">What happens next</div>

            <div class="next-item">
                <div class="next-icon">
                    <i class="fa-regular fa-envelope-open"></i>
                </div>
                <div class="next-text">
                    <strong>Verify your email</strong>
                    Click the link in our email to confirm your address
                </div>
            </div>

            <div class="next-item">
                <div class="next-icon">
                    <i class="fa-regular fa-clock"></i>
                </div>
                <div class="next-text">
                    <strong>Application review (1–3 business days)</strong>
                    Our team will review your seller documents and application
                </div>
            </div>

            <div class="next-item">
                <div class="next-icon">
                    <i class="fa-solid fa-rocket"></i>
                </div>
                <div class="next-text">
                    <strong>Go live</strong>
                    Once approved, your shop will be live and you can start selling
                </div>
            </div>
        </div>

        <hr class="divider">

        <p style="font-size:13px;color:var(--muted);text-align:center;margin-bottom:14px;">
            Didn't get the email? Check spam or request a fresh link.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn">
                <i class="fa-solid fa-paper-plane" style="margin-right:7px;"></i>
                Resend Verification Email
            </button>
        </form>

        <div class="foot-link">
            <a href="{{ route('seller.logout') }}"
               onclick="event.preventDefault();document.getElementById('seller-logout').submit();">
                <i class="fa-solid fa-arrow-right-from-bracket" style="font-size:10px;margin-right:4px;"></i>
                Sign out
            </a>
            <form id="seller-logout" action="{{ route('seller.logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>

    </div>
</div>

<div class="page-foot">
    GenesisHub Seller Portal &mdash;
    <a href="{{ url('/contact') }}">Get help</a>
</div>

</body>
</html>
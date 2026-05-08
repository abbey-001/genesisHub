{{-- resources/views/auth/verify-email.blade.php --}}
{{-- Fortify renders this via: Fortify::verifyEmailView(fn () => view('auth.verify-email')) --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email — GenesisHub</title>
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

        html, body {
            height: 100%;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        body {
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
            max-width: 480px;
            background: var(--surface);
            border-radius: 16px;
            overflow: hidden;
            box-shadow:
                0 1px 3px rgba(0,0,0,.06),
                0 12px 40px rgba(113,78,50,.11);
        }

        /* ── Header ───────────────────────────────────────── */
        .card-header {
            background: var(--primary);
            padding: 28px 40px 24px;
            text-align: center;
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
            width: 220px; height: 220px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.07);
            bottom: -90px; right: -60px;
        }

        .header-inner { position: relative; z-index: 1; }

        .logo {
            height: 36px;
            display: block;
            margin: 0 auto 8px;
            filter: brightness(0) invert(1);
        }

        .brand-name {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.3px;
            line-height: 1;
        }

        .brand-name span { color: var(--accent); }

        /* Gold stripe */
        .header-stripe {
            height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-d), var(--accent));
        }

        /* ── Body ─────────────────────────────────────────── */
        .card-body { padding: 36px 40px 40px; }

        /* Envelope illustration */
        .icon-wrap {
            width: 72px; height: 72px;
            background: #fef9f5;
            border: 1.5px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            position: relative;
        }

        .icon-wrap::after {
            content: '';
            position: absolute;
            inset: -7px;
            border-radius: 50%;
            border: 1.5px dashed rgba(245,195,75,.5);
            animation: orbit 10s linear infinite;
        }

        @keyframes orbit {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        .icon-wrap i { font-size: 28px; color: var(--primary); }

        /* Badge chip */
        .badge-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(113,78,50,.08);
            border: 1px solid rgba(113,78,50,.15);
            color: var(--primary);
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .09em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 100px;
            margin-bottom: 14px;
        }

        .badge-chip i { font-size: 9px; color: var(--accent-d); }

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
            line-height: 1.7;
            margin-bottom: 20px;
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
            margin-bottom: 24px;
        }

        .email-pill i { font-size: 13px; color: var(--muted); }

        /* Divider */
        .divider { border: none; border-top: 1px solid var(--border); margin: 22px 0; }

        /* Steps */
        .steps { list-style: none; margin-bottom: 24px; }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 14px;
        }

        .step:last-child { margin-bottom: 0; }

        .step-num {
            flex-shrink: 0;
            width: 26px; height: 26px;
            background: var(--primary);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            margin-top: 1px;
        }

        .step-text {
            font-size: 13.5px;
            color: var(--mid);
            line-height: 1.6;
            padding-top: 3px;
        }

        .step-text strong { color: var(--text); font-weight: 600; }

        /* Alert */
        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alert i { font-size: 14px; flex-shrink: 0; }

        /* Button */
        .btn {
            display: block;
            width: 100%;
            padding: 13px 24px;
            background: var(--primary);
            color: #fff;
            font-family: 'Poppins', 'Inter', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            letter-spacing: .02em;
            border: none;
            border-radius: 8px;
            border-bottom: 3px solid var(--accent);
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background .2s, transform .15s;
        }

        .btn:hover { background: var(--primary-d); transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }

        /* Footer link */
        .foot-link {
            text-align: center;
            margin-top: 18px;
        }

        .foot-link a {
            font-size: 12.5px;
            color: var(--muted);
            text-decoration: none;
            transition: color .2s;
        }

        .foot-link a:hover { color: var(--primary); }

        /* Page footer */
        .page-foot {
            margin-top: 20px;
            font-size: 11.5px;
            color: #b0bac4;
            text-align: center;
        }

        .page-foot a { color: #9ca3af; text-decoration: none; }

        @media (max-width: 540px) {
            .card-header { padding: 22px 24px; }
            .card-body   { padding: 28px 24px 32px; }
        }
    </style>
</head>
<body>

<div class="card">

    <div class="card-header">
        <div class="header-inner">
            <img src="{{ asset('public/image/auth-logo.png') }}"
                 alt="GenesisHub" class="logo" height="36">
            <div class="brand-name">Genesis<span>Hub</span></div>
        </div>
    </div>
    <div class="header-stripe"></div>

    <div class="card-body">

        {{-- Envelope icon --}}
        <div class="icon-wrap">
            <i class="fa-regular fa-envelope-open"></i>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                A new verification link has been sent to your email address.
            </div>
        @endif

        <div class="badge-chip">
            <i class="fa-solid fa-shield-halved"></i>
            Email Verification
        </div>

        <h1 class="card-title">Check your inbox</h1>
        <p class="card-desc">
            Thanks for joining GenesisHub! To activate your account,
            please verify your email address.
        </p>

        @auth
            <div class="email-pill">
                <i class="fa-regular fa-envelope"></i>
                {{ auth()->user()->email }}
            </div>
        @endauth

        <hr class="divider">

        <ul class="steps">
            <li class="step">
                <div class="step-num">1</div>
                <div class="step-text">
                    Open the email from <strong>GenesisHub</strong> in your inbox
                </div>
            </li>
            <li class="step">
                <div class="step-num">2</div>
                <div class="step-text">
                    Click the <strong>"Verify Email Address"</strong> button
                </div>
            </li>
            <li class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    You'll be redirected and your account will be fully active
                </div>
            </li>
        </ul>

        <hr class="divider">

        <p style="font-size:13px;color:var(--muted);text-align:center;margin-bottom:14px;">
            Didn't get it? Check your spam folder or request a fresh link.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn">
                <i class="fa-solid fa-paper-plane" style="margin-right:7px;"></i>
                Resend Verification Email
            </button>
        </form>

        <div class="foot-link">
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-arrow-right-from-bracket" style="font-size:10px;margin-right:4px;"></i>
                Sign out and use a different account
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                @csrf
            </form>
        </div>

    </div>
</div>

<div class="page-foot">
    GenesisHub &mdash; Need help?
    <a href="{{ url('/contact') }}">Contact support</a>
</div>

</body>
</html>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $subject ?? config('app.name', 'GenesisHub') }}</title>
    <!--[if mso]>
    <noscript><xml><o:OfficeDocumentSettings><o:PixelsPerInch>96</o:PixelsPerInch></o:OfficeDocumentSettings></xml></noscript>
    <![endif]-->
    <style>
        /* ── Reset ─────────────────────────────────────────── */
        * { margin:0; padding:0; box-sizing:border-box; }
        body, table, td, a { -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; }
        table, td { mso-table-lspace:0pt; mso-table-rspace:0pt; }
        img { -ms-interpolation-mode:bicubic; border:0; outline:none; text-decoration:none; }

        /* ── Base ──────────────────────────────────────────── */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f3f5f6;
            color: #1a1a1a;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Wrapper ───────────────────────────────────────── */
        .email-wrapper {
            width: 100%;
            background-color: #f3f5f6;
            padding: 40px 20px;
        }

        /* ── Card ──────────────────────────────────────────── */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: visible;
            box-shadow:
                0 2px 8px rgba(113,78,50,.06),
                0 16px 48px rgba(113,78,50,.10);
        }

        /* ── Header ────────────────────────────────────────── */
        .email-header {
            background-color: #714e32;
            padding: 32px 48px 26px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-radius: 12px 12px 0 0;
        }

        /* Ambient glow identical to the auth panel */
        .email-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 15%, rgba(245,195,75,.14) 0%, transparent 55%),
                radial-gradient(circle at 85% 85%, rgba(255,255,255,.05) 0%, transparent 50%);
            pointer-events: none;
        }

        /* Decorative ring */
        .email-header::after {
            content: '';
            position: absolute;
            width: 280px; height: 280px;
            border-radius: 50%;
            border: 1.5px solid rgba(255,255,255,.07);
            bottom: -110px; right: -70px;
            pointer-events: none;
        }

        .header-inner { position: relative; z-index: 1; }

        /* Logo — white version */
        .logo-img {
            height: 40px;
            display: block;
            margin: 0 auto 10px;
            filter: brightness(0) invert(1);
        }

        /* Fallback / companion wordmark */
        .brand-wordmark {
            font-family: 'Poppins', 'Inter', -apple-system, sans-serif;
            font-size: 23px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            line-height: 1;
        }

        .brand-wordmark span { color: #f5c34b; }

        .header-tagline {
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: rgba(255,255,255,.40);
            margin-top: 7px;
        }

        /* Gold accent stripe */
        .header-stripe {
            height: 3px;
            background: linear-gradient(90deg, #f5c34b 0%, #e8a820 50%, #f5c34b 100%);
        }

        /* ── Body ──────────────────────────────────────────── */
        .email-body { padding: 40px 48px 36px; }

        .greeting {
            font-family: 'Poppins', 'Inter', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 4px;
        }

        .email-tagline {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #714e32;
            margin-bottom: 22px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e4ea;
        }

        .email-body p {
            font-size: 14.5px;
            color: #555e68;
            margin-bottom: 14px;
            line-height: 1.72;
        }

        /* ── Info card ─────────────────────────────────────── */
        .info-card {
            background-color: #f9fafb;
            border: 1px solid #e0e4ea;
            border-left: 3px solid #714e32;
            border-radius: 8px;
            padding: 18px 22px;
            margin: 22px 0;
        }

        .info-card-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #9ca3af;
            margin-bottom: 14px;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 9px;
        }

        .info-row:last-child { margin-bottom: 0; }

        .info-label {
            display: table-cell;
            font-size: 12.5px;
            color: #9ca3af;
            width: 44%;
            vertical-align: top;
            padding-right: 8px;
            font-weight: 500;
        }

        .info-value {
            display: table-cell;
            font-size: 12.5px;
            color: #1a1a1a;
            font-weight: 600;
            vertical-align: top;
        }

        /* ── Amount ────────────────────────────────────────── */
        .amount-highlight {
            font-family: 'Poppins', sans-serif;
            font-size: 30px;
            font-weight: 700;
            color: #714e32;
            margin: 4px 0 2px;
            line-height: 1.1;
        }

        /* ── Status badge ──────────────────────────────────── */
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .status-badge.success { background:#dcfce7; color:#166534; }
        .status-badge.pending { background:#fef9c3; color:#854d0e; }
        .status-badge.info    { background:#dbeafe; color:#1e40af; }
        .status-badge.danger  { background:#fee2e2; color:#991b1b; }

        /* ── CTA Button ────────────────────────────────────── */
        .cta-wrapper { text-align: center; margin: 28px 0 22px; }

        .cta-button {
            display: inline-block;
            background-color: #714e32;
            color: #ffffff !important;
            text-decoration: none;
            font-family: 'Poppins', 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            padding: 13px 38px;
            border-radius: 8px;
            border-bottom: 3px solid #f5c34b;
        }

        /* ── Secondary link ────────────────────────────────── */
        .secondary-link {
            font-size: 11.5px;
            color: #9ca3af;
            text-align: center;
            margin-top: 10px;
            line-height: 1.6;
        }

        .secondary-link a { color: #714e32; text-decoration: underline; word-break: break-all; }

        /* ── Divider ───────────────────────────────────────── */
        .divider { border: none; border-top: 1px solid #e0e4ea; margin: 26px 0; }

        /* ── Alert box ─────────────────────────────────────── */
        .alert-box {
            background-color: #fef9c3;
            border: 1px solid #fde047;
            border-radius: 8px;
            padding: 14px 18px;
            font-size: 13.5px;
            color: #713f12;
            line-height: 1.6;
        }

        .alert-box.alert-info    { background:#eff6ff; border-color:#bfdbfe; color:#1e3a5f; }
        .alert-box.alert-success { background:#f0fdf4; border-color:#bbf7d0; color:#14532d; }

        /* ── Items table ───────────────────────────────────── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 13px;
        }

        .items-table th {
            text-align: left;
            padding: 10px 14px;
            background-color: #714e32;
            color: #f5c34b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .items-table th:first-child { border-radius: 6px 0 0 0; }
        .items-table th:last-child  { border-radius: 0 6px 0 0; }

        .items-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #e0e4ea;
            color: #555e68;
            vertical-align: top;
        }

        .items-table tr:last-child td { border-bottom: none; }
        .items-table tr:nth-child(even) td { background-color: #f9fafb; }

        .items-table .total-row td {
            background-color: #fef9f5;
            font-weight: 700;
            color: #714e32;
            border-top: 2px solid #e0e4ea;
        }

        /* ── Footer ────────────────────────────────────────── */
        .email-footer {
            background-color: #f9fafb;
            border-top: 1px solid #e0e4ea;
            padding: 24px 48px;
            text-align: center;
            border-radius: 0 0 12px 12px;
        }

        /* ── Scroll fix ────────────────────────────────────── */
        html, body {
            height: auto !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        .footer-logo {
            height: 22px;
            display: block;
            margin: 0 auto 10px;
            opacity: 0.25;
            filter: grayscale(1);
        }

        .footer-wordmark {
            font-family: 'Poppins', 'Inter', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #714e32;
            letter-spacing: -0.2px;
            margin-bottom: 10px;
            opacity: 0.55;
        }

        .footer-wordmark span { color: #f5c34b; }

        .footer-links { margin-bottom: 12px; }

        .footer-links a {
            font-size: 11.5px;
            color: #9ca3af;
            text-decoration: none;
            margin: 0 8px;
            font-weight: 500;
        }

        .footer-text {
            font-size: 11px;
            color: #c8cdd5;
            line-height: 1.7;
        }

        .footer-text a { color: #9ca3af; }

        /* ── Mobile ────────────────────────────────────────── */
        @media only screen and (max-width: 600px) {
            .email-body   { padding: 28px 24px; }
            .email-header { padding: 26px 24px; }
            .email-footer { padding: 20px 24px; }
            .greeting     { font-size: 17px; }
            .info-label, .info-value { display: block; width: 100%; }
            .info-label { margin-bottom: 1px; }
        }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-container">

        {{-- Header --}}
        <div class="email-header">
            <div class="header-inner">
                <img src="{{ asset('public/image/auth-logo.png') }}"
                     alt="GenesisHub"
                     class="logo-img"
                     width="auto"
                     height="40">

                <div class="brand-wordmark">Genesis<span>Hub</span></div>
                <div class="header-tagline">{{ $tagline ?? 'Your trusted marketplace' }}</div>
            </div>
        </div>
        <div class="header-stripe"></div>

        {{-- Body --}}
        <div class="email-body">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        <div class="email-footer">
            <img src="{{ asset('public/image/auth-logo.png') }}"
                 alt="GenesisHub"
                 class="footer-logo"
                 width="auto"
                 height="22">
            <div class="footer-wordmark">Genesis<span>Hub</span></div>
            <div class="footer-links">
                <a href="{{ url('/') }}">Home</a>
                <a href="{{ url('/account') }}">My Account</a>
                <a href="{{ url('/contact') }}">Contact Us</a>
            </div>
            <div class="footer-text">
                You received this email because you have an account with GenesisHub.<br>
                &copy; {{ date('Y') }} GenesisHub. All rights reserved.<br>
                <a href="{{ url('/account') }}">Manage email preferences</a>
            </div>
        </div>

    </div>

    <div style="text-align:center;margin-top:18px;">
        <p style="font-size:11px;color:#b0bac4;font-family:Inter,sans-serif;letter-spacing:.3px;">
            GenesisHub &mdash; {{ url('/') }}
        </p>
    </div>
</div>
</body>
</html>
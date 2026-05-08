<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Become a Seller — {{ config('app.name', 'GenesisHub') }}</title>
<meta name="description" content="Create your GenesisHub seller account and start selling today.">

<!-- Favicon -->
<link rel="icon" type="image/png" href="{{ asset('public/image/auth-logo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('public/image/auth-logo.png') }}">

<!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="canonical" href="{{ url('seller/register') }}">

<!-- Bootstrap -->
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">

<style>
/* ─────────────────────────────────────────────────────────
   Design tokens (identical to login page)
   Primary  : #714e32
   Primary-D: #5a3d26
   Accent   : #f5c34b
   Page-bg  : #f3f5f6
   Text-dark: #1a1a1a
   Text-mid : #555e68
   Border   : #e0e4ea
───────────────────────────────────────────────────────── */

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html, body {
    height: 100%;
    font-family: 'Inter', sans-serif;
    background: #f3f5f6;
    color: #1a1a1a;
}

/* ══════════════════════════════════════════════════════════
   SHELL — same two-column split as login
══════════════════════════════════════════════════════════ */
.auth-shell {
    display: flex;
    min-height: 100vh;
}

/* ══════════════════════════════════════════════════════════
   LEFT PANEL — sticky brand panel (identical to login)
══════════════════════════════════════════════════════════ */
.auth-panel {
    flex: 0 0 42%;
    background: #714e32;
    position: sticky;
    top: 0;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 48px 52px;
    overflow: hidden;
}

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

.auth-panel__ring {
    position: absolute;
    border-radius: 50%;
    border: 1.5px solid rgba(255,255,255,.08);
    pointer-events: none;
    z-index: 0;
}
.auth-panel__ring--lg { width: 460px; height: 460px; bottom: -140px; right: -130px; }
.auth-panel__ring--sm { width: 200px; height: 200px; top: 90px; right: 50px; border-color: rgba(245,195,75,.12); }

.auth-panel__logo { position: relative; z-index: 1; }
.auth-panel__logo img {
    height: 42px;
    display: block;
    filter: brightness(0) invert(1);
}

.auth-panel__body {
    position: relative;
    z-index: 1;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 56px 0 40px;
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
    margin-bottom: 24px;
    width: fit-content;
}

.auth-panel__heading {
    font-family: 'Poppins', sans-serif;
    font-size: clamp(24px, 2.8vw, 34px);
    font-weight: 800;
    line-height: 1.22;
    color: #ffffff;
    margin-bottom: 16px;
}

.auth-panel__heading em { font-style: normal; color: #f5c34b; }

.auth-panel__desc {
    font-size: 14px;
    line-height: 1.72;
    color: rgba(255,255,255,.62);
    max-width: 320px;
    margin-bottom: 36px;
}

/* Step tracker shown in the left panel on desktop */
.panel-steps { display: flex; flex-direction: column; gap: 0; }

.panel-step {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    position: relative;
}

/* Connecting line between steps */
.panel-step:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 17px;
    top: 36px;
    width: 2px;
    height: calc(100% - 4px);
    background: rgba(255,255,255,.12);
}

.panel-step.active:not(:last-child)::after { background: rgba(245,195,75,.3); }

.panel-step__num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,.2);
    background: rgba(255,255,255,.07);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all .3s ease;
    position: relative;
    z-index: 1;
}

.panel-step__num i { font-size: 13px; color: rgba(255,255,255,.5); transition: color .3s; }

.panel-step.active .panel-step__num {
    background: #f5c34b;
    border-color: #f5c34b;
    box-shadow: 0 4px 14px rgba(245,195,75,.35);
}

.panel-step.active .panel-step__num i { color: #714e32; }

.panel-step.done .panel-step__num {
    background: rgba(245,195,75,.2);
    border-color: rgba(245,195,75,.4);
}

.panel-step.done .panel-step__num i { color: #f5c34b; }

.panel-step__text {
    padding: 6px 0 28px;
}

.panel-step__label {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,.5);
    transition: color .3s;
    line-height: 1.3;
}

.panel-step.active .panel-step__label { color: #ffffff; }
.panel-step.done .panel-step__label { color: rgba(255,255,255,.75); }

.panel-step__sub {
    font-size: 12px;
    color: rgba(255,255,255,.35);
    margin-top: 2px;
    transition: color .3s;
}

.panel-step.active .panel-step__sub { color: rgba(255,255,255,.55); }

.auth-panel__footer {
    position: relative;
    z-index: 1;
    font-size: 12px;
    color: rgba(255,255,255,.35);
}

/* ══════════════════════════════════════════════════════════
   RIGHT PANEL — scrollable form side
══════════════════════════════════════════════════════════ */
.auth-form-side {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 60px 40px 80px;
    overflow-y: auto;
    background: #f3f5f6;
    min-height: 100vh;
}

.auth-form-box {
    width: 100%;
    max-width: 560px;
}

/* Mobile-only logo */
.auth-mobile-logo {
    display: none;
    text-align: center;
    margin-bottom: 36px;
}
.auth-mobile-logo img { height: 38px; }

/* ── Form header ── */
.form-header { margin-bottom: 32px; }

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
    font-size: 26px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1.25;
    margin-bottom: 5px;
}

.form-header__sub {
    font-size: 14px;
    color: #555e68;
    line-height: 1.55;
}

/* ── Mobile step indicator (hidden on desktop) ── */
.mobile-steps {
    display: none;
    gap: 6px;
    margin-bottom: 28px;
    align-items: center;
}

.mobile-step-dot {
    height: 4px;
    border-radius: 100px;
    background: #e0e4ea;
    flex: 1;
    transition: background .3s, flex .3s;
}

.mobile-step-dot.active { background: #714e32; flex: 2; }
.mobile-step-dot.done   { background: rgba(113,78,50,.4); }

/* ── Step panels ── */
.step-panel { display: none; }
.step-panel.active {
    display: block;
    animation: stepIn .35s ease both;
}

@keyframes stepIn {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ── Section headings inside steps ── */
.step-heading {
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 1.5px solid #e8eaed;
}

.step-heading__title {
    font-family: 'Poppins', sans-serif;
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin-bottom: 3px;
}

.step-heading__sub {
    font-size: 13.5px;
    color: #555e68;
}

/* ── Form controls (same as login) ── */
.form-row { display: grid; gap: 18px; margin-bottom: 18px; }
.form-row--2 { grid-template-columns: 1fr 1fr; }
.form-row--1 { grid-template-columns: 1fr; }
.form-row--3 { grid-template-columns: 1fr 1fr 1fr; }

.form-group { display: flex; flex-direction: column; }

.form-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #2d2d2d;
    margin-bottom: 7px;
}

.form-label .opt {
    font-weight: 400;
    color: #8a94a6;
    font-size: 12px;
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

textarea.form-control {
    height: auto;
    padding: 12px 14px;
    resize: vertical;
    min-height: 110px;
}

select.form-control {
    cursor: pointer;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238a94a6' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 14px center;
    padding-right: 36px;
}

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

/* ── Upload areas ── */
.upload-zone {
    border: 2px dashed #dde1e8;
    border-radius: 12px;
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .25s, background .25s;
    background: #ffffff;
    position: relative;
}

.upload-zone:hover,
.upload-zone.has-file {
    border-color: #714e32;
    background: rgba(113,78,50,.03);
}

.upload-zone input[type="file"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}

.upload-zone__icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: rgba(113,78,50,.08);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}

.upload-zone__icon i { font-size: 18px; color: #714e32; }

.upload-zone__title {
    font-size: 13.5px;
    font-weight: 600;
    color: #2d2d2d;
    margin-bottom: 4px;
}

.upload-zone__hint {
    font-size: 12px;
    color: #8a94a6;
}

.upload-zone__preview {
    margin-top: 12px;
    display: none;
}

.upload-zone__preview img {
    max-height: 80px;
    max-width: 100%;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,.1);
}

.upload-zone__filename {
    margin-top: 8px;
    font-size: 12px;
    color: #714e32;
    font-weight: 600;
    display: none;
}

/* ── Hint text ── */
.field-hint {
    font-size: 12px;
    color: #8a94a6;
    margin-top: 6px;
    display: flex;
    align-items: flex-start;
    gap: 5px;
    line-height: 1.5;
}

.field-hint i { margin-top: 2px; flex-shrink: 0; }

/* ── Business type cards ── */
.biz-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }

.biz-card {
    border: 1.5px solid #e0e4ea;
    border-radius: 10px;
    padding: 14px 12px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s, box-shadow .2s;
    position: relative;
    background: #ffffff;
}

.biz-card input[type="radio"] {
    position: absolute;
    opacity: 0;
    width: 0;
    height: 0;
}

.biz-card i {
    font-size: 22px;
    color: #8a94a6;
    margin-bottom: 8px;
    display: block;
    transition: color .2s;
}

.biz-card__label {
    font-size: 13px;
    font-weight: 600;
    color: #555e68;
    transition: color .2s;
}

.biz-card:hover {
    border-color: #714e32;
    background: rgba(113,78,50,.03);
}

.biz-card.selected {
    border-color: #714e32;
    background: rgba(113,78,50,.05);
    box-shadow: 0 0 0 3px rgba(113,78,50,.1);
}

.biz-card.selected i { color: #714e32; }
.biz-card.selected .biz-card__label { color: #714e32; }

/* ── Navigation buttons ── */
.step-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1.5px solid #e8eaed;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 50px;
    padding: 0 24px;
    background: #ffffff;
    border: 1.5px solid #e0e4ea;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    color: #555e68;
    cursor: pointer;
    transition: border-color .2s, color .2s, background .2s;
}

.btn-back:hover {
    border-color: #714e32;
    color: #714e32;
    background: rgba(113,78,50,.04);
}

.btn-next {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 50px;
    padding: 0 28px;
    background: #714e32;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    color: #ffffff;
    cursor: pointer;
    letter-spacing: .02em;
    transition: background .2s, box-shadow .2s, transform .1s;
    margin-left: auto;
}

.btn-next:hover {
    background: #5a3d26;
    box-shadow: 0 8px 24px rgba(113,78,50,.3);
}

.btn-next:active { transform: scale(0.985); }

.btn-submit-final {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    height: 52px;
    padding: 0 32px;
    background: #714e32;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    color: #ffffff;
    cursor: pointer;
    letter-spacing: .025em;
    transition: background .2s, box-shadow .2s, transform .1s;
}

.btn-submit-final:hover {
    background: #5a3d26;
    box-shadow: 0 8px 24px rgba(113,78,50,.3);
}

.btn-submit-final:active { transform: scale(0.985); }

/* ── Terms row ── */
.terms-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 16px 18px;
    background: rgba(113,78,50,.04);
    border: 1px solid rgba(113,78,50,.12);
    border-radius: 10px;
    margin-bottom: 0;
}

.terms-row input[type="checkbox"] {
    width: 17px;
    height: 17px;
    margin-top: 2px;
    border: 1.5px solid #c8cdd5;
    border-radius: 4px;
    cursor: pointer;
    accent-color: #714e32;
    flex-shrink: 0;
}

.terms-row label {
    font-size: 13.5px;
    color: #555e68;
    line-height: 1.55;
    cursor: pointer;
}

.terms-row a { color: #714e32; font-weight: 600; text-decoration: none; }
.terms-row a:hover { text-decoration: underline; }

/* ── Notices ── */
.notice {
    display: flex;
    gap: 13px;
    align-items: flex-start;
    border-radius: 12px;
    padding: 15px 17px;
    margin-bottom: 24px;
}

.notice__icon {
    width: 34px; height: 34px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 1px;
}
.notice__icon i { font-size: 14px; }
.notice__title { font-size: 13px; font-weight: 700; margin-bottom: 3px; }
.notice__text  { font-size: 12.5px; line-height: 1.55; margin: 0; }

.notice--danger  { background:#fff4f4; border:1px solid #ffc2c2; }
.notice--danger .notice__icon  { background:rgba(229,62,62,.1); }
.notice--danger .notice__icon i { color:#c53030; }
.notice--danger .notice__title { color:#822020; }
.notice--danger .notice__text  { color:#9b2626; }

/* ── Footer link ── */
.auth-foot {
    display: flex;
    flex-direction: column;
    gap: 10px;
    align-items: center;
    margin-top: 28px;
}

.auth-foot p { font-size: 13.5px; color: #555e68; margin: 0; }

.auth-foot a { color: #714e32; font-weight: 600; text-decoration: none; transition: color .2s; }
.auth-foot a:hover { color: #5a3d26; text-decoration: underline; }

/* ── Responsive ── */
@media (max-width: 900px) {
    .auth-panel { flex: 0 0 38%; padding: 40px 36px; }
    .auth-panel__heading { font-size: 22px; }
    .auth-form-side { padding: 48px 28px 80px; }
}

@media (max-width: 720px) {
    .auth-panel { display: none; }
    .auth-mobile-logo { display: block; }
    .mobile-steps { display: flex; }
    .auth-form-side { padding: 40px 20px 80px; justify-content: flex-start; }
    .auth-form-box { max-width: 100%; }
    .form-row--2 { grid-template-columns: 1fr; }
    .form-row--3 { grid-template-columns: 1fr 1fr; }
    .biz-cards { grid-template-columns: repeat(3, 1fr); }
}

@media (max-width: 480px) {
    .biz-cards { grid-template-columns: 1fr; }
    .form-row--3 { grid-template-columns: 1fr; }
    .step-nav { flex-direction: column-reverse; }
    .btn-back, .btn-next, .btn-submit-final { width: 100%; justify-content: center; }
}
</style>
</head>
<body>

<div class="auth-shell">

    {{-- ══════════════════════════════════════════════════
         LEFT — Sticky brand panel
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

        {{-- Panel body --}}
        <div class="auth-panel__body">
            <div class="auth-panel__pill">
                <i class="fa-solid fa-rocket fa-xs"></i>
                Start selling today
            </div>

            <h2 class="auth-panel__heading">
                Your shop.<br>Your rules.<br><em>Your success.</em>
            </h2>

            <p class="auth-panel__desc">
                Join thousands of sellers already growing with GenesisHub. Set up takes less than 5 minutes.
            </p>

            {{-- Step tracker --}}
            <div class="panel-steps" id="panelSteps">
                <div class="panel-step active" data-step="1">
                    <div class="panel-step__num"><i class="fa-solid fa-user"></i></div>
                    <div class="panel-step__text">
                        <div class="panel-step__label">Personal Info</div>
                        <div class="panel-step__sub">Name, email & password</div>
                    </div>
                </div>
                <div class="panel-step" data-step="2">
                    <div class="panel-step__num"><i class="fa-solid fa-store"></i></div>
                    <div class="panel-step__text">
                        <div class="panel-step__label">Shop Details</div>
                        <div class="panel-step__sub">Logo, name & description</div>
                    </div>
                </div>
                <div class="panel-step" data-step="3">
                    <div class="panel-step__num"><i class="fa-solid fa-briefcase"></i></div>
                    <div class="panel-step__text">
                        <div class="panel-step__label">Business Info</div>
                        <div class="panel-step__sub">Type, address & zone</div>
                    </div>
                </div>
                <div class="panel-step" data-step="4">
                    <div class="panel-step__num"><i class="fa-solid fa-building-columns"></i></div>
                    <div class="panel-step__text">
                        <div class="panel-step__label">Bank Details</div>
                        <div class="panel-step__sub">Payout information</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-panel__footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'GenesisHub') }}. All rights reserved.
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════
         RIGHT — Scrollable form side
    ══════════════════════════════════════════════════ --}}
    <main class="auth-form-side">
        <div class="auth-form-box">

            {{-- Mobile-only logo --}}
            <div class="auth-mobile-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('public/image/auth-logo.png') }}"
                         alt="{{ config('app.name', 'GenesisHub') }}">
                </a>
            </div>

            {{-- Mobile step dots --}}
            <div class="mobile-steps" id="mobileDots">
                <div class="mobile-step-dot active" data-dot="1"></div>
                <div class="mobile-step-dot" data-dot="2"></div>
                <div class="mobile-step-dot" data-dot="3"></div>
                <div class="mobile-step-dot" data-dot="4"></div>
            </div>

            {{-- Form header --}}
            <div class="form-header">
                <p class="form-header__eyebrow" id="stepEyebrow">Step 1 of 4</p>
                <h1 class="form-header__title" id="stepTitle">Personal Information</h1>
                <p class="form-header__sub" id="stepSub">Let's start with your basic account details</p>
            </div>

            {{-- Validation errors (server-side, shown on page load) --}}
            @if($errors->any())
            <div class="notice notice--danger" style="margin-bottom:24px;">
                <div class="notice__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div>
                    <p class="notice__title">Please fix the following</p>
                    <p class="notice__text">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif

            {{-- ════════════════════════════════════════════
                 THE FORM
            ════════════════════════════════════════════ --}}
            <form method="POST"
                  action="{{ route('seller.register') }}"
                  enctype="multipart/form-data"
                  id="regForm"
                  novalidate>
                @csrf

                {{-- ─────────────────────────────────────────
                     STEP 1 — Personal Information
                ───────────────────────────────────────── --}}
                <div class="step-panel active" id="step1">
                    <div class="step-heading">
                        <p class="step-heading__title">Your account</p>
                        <p class="step-heading__sub">This is how you'll log in to GenesisHub</p>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="name">Full Name <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="name" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Your full legal name"
                                   required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--2">
                        <div class="form-group">
                            <label class="form-label" for="email">Email Address <span style="color:#e53e3e">*</span></label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}"
                                   placeholder="you@example.com"
                                   autocomplete="email"
                                   required>
                            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone_number">Phone Number <span style="color:#e53e3e">*</span></label>
                            <input type="tel" id="phone_number" name="phone_number"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   value="{{ old('phone_number') }}"
                                   placeholder="+234 800 000 0000"
                                   required>
                            @error('phone_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--2">
                        <div class="form-group">
                            <label class="form-label" for="password">Password <span style="color:#e53e3e">*</span></label>
                            <div class="input-wrap">
                                <input type="password" id="password" name="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Create a strong password"
                                       autocomplete="new-password"
                                       required>
                                <button type="button" class="pw-toggle" onclick="togglePw('password','pwIcon1')">
                                    <i class="fa-regular fa-eye" id="pwIcon1"></i>
                                </button>
                            </div>
                            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirm Password <span style="color:#e53e3e">*</span></label>
                            <div class="input-wrap">
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                       class="form-control"
                                       placeholder="Re-enter your password"
                                       autocomplete="new-password"
                                       required>
                                <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','pwIcon2')">
                                    <i class="fa-regular fa-eye" id="pwIcon2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-next" data-next="2">
                            Continue <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                
                    <div style="margin-top:24px; padding-top:24px; border-top:1.5px solid #e8eaed;">
                        <p style="font-size:12.5px; color:#8a94a6; text-align:center; margin-bottom:14px; letter-spacing:.04em; text-transform:uppercase; font-weight:600;">
                            Or sign up faster with
                        </p>
                        <div style="display:flex; gap:12px;">
                            <a href="{{ route('social.redirect', 'google') }}?type=seller"
                               style="flex:1; display:flex; align-items:center; justify-content:center; gap:8px;
                                      height:46px; border:1.5px solid #e0e4ea; border-radius:10px; background:#fff;
                                      font-size:14px; font-weight:600; color:#1a1a1a; text-decoration:none;
                                      transition:border-color .2s;">
                                <i class="fa-brands fa-google" style="color:#EA4335;font-size:16px;"></i> Google
                            </a>
                            <a href="{{ route('social.redirect', 'facebook') }}?type=seller"
                               style="flex:1; display:flex; align-items:center; justify-content:center; gap:8px;
                                      height:46px; border:1.5px solid #e0e4ea; border-radius:10px; background:#fff;
                                      font-size:14px; font-weight:600; color:#1a1a1a; text-decoration:none;
                                      transition:border-color .2s;">
                                <i class="fa-brands fa-facebook" style="color:#1877F2;font-size:16px;"></i> Facebook
                            </a>
                        </div>
                        <p style="font-size:12px; color:#8a94a6; text-align:center; margin-top:10px; line-height:1.5;">
                            Social sign-up skips Step 1 — you'll only fill in shop &amp; business details.
                        </p>
                    </div>
                </div>

                {{-- ─────────────────────────────────────────
                     STEP 2 — Shop Information
                ───────────────────────────────────────── --}}
                <div class="step-panel" id="step2">
                    <div class="step-heading">
                        <p class="step-heading__title">Your shop identity</p>
                        <p class="step-heading__sub">This is what customers will see when they visit your shop</p>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="shop_name">Shop Name <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="shop_name" name="shop_name"
                                   class="form-control @error('shop_name') is-invalid @enderror"
                                   value="{{ old('shop_name') }}"
                                   placeholder="e.g. Ade's Fashion Store"
                                   required>
                            @error('shop_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="shop_description">Shop Description <span style="color:#e53e3e">*</span></label>
                            <textarea id="shop_description" name="shop_description"
                                      class="form-control @error('shop_description') is-invalid @enderror"
                                      placeholder="Tell customers what makes your shop unique — what you sell, your story, your values…"
                                      required>{{ old('shop_description') }}</textarea>
                            @error('shop_description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--2" style="margin-bottom:18px;">
                        {{-- Logo upload --}}
                        <div class="form-group">
                            <label class="form-label">
                                Shop Logo <span style="color:#e53e3e">*</span>
                            </label>
                            <div class="upload-zone @error('shop_logo') is-invalid @enderror" id="logoZone">
                                <input type="file" name="shop_logo" id="shop_logo"
                                       accept="image/*"
                                       onchange="handleUpload(this,'logoZone','logoPreview','logoFilename')"
                                       required>
                                <div class="upload-zone__icon">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <p class="upload-zone__title">Upload logo</p>
                                <p class="upload-zone__hint">JPEG, PNG, GIF · Max 2MB</p>
                                <div class="upload-zone__preview" id="logoPreview">
                                    <img src="" alt="Logo preview">
                                </div>
                                <p class="upload-zone__filename" id="logoFilename"></p>
                            </div>
                            @error('shop_logo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <p class="field-hint"><i class="fa-solid fa-circle-info"></i> Recommended: 200×200px square image</p>
                        </div>

                        {{-- Banner upload --}}
                        <div class="form-group">
                            <label class="form-label">
                                Shop Banner <span class="opt">(optional)</span>
                            </label>
                            <div class="upload-zone" id="bannerZone">
                                <input type="file" name="banner" id="banner"
                                       accept="image/*"
                                       onchange="handleUpload(this,'bannerZone','bannerPreview','bannerFilename')">
                                <div class="upload-zone__icon">
                                    <i class="fa-solid fa-image"></i>
                                </div>
                                <p class="upload-zone__title">Upload banner</p>
                                <p class="upload-zone__hint">JPEG, PNG · Max 4MB</p>
                                <div class="upload-zone__preview" id="bannerPreview">
                                    <img src="" alt="Banner preview">
                                </div>
                                <p class="upload-zone__filename" id="bannerFilename"></p>
                            </div>
                            @error('banner')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <p class="field-hint"><i class="fa-solid fa-circle-info"></i> Recommended: 1200×300px</p>
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="website">
                                Website <span class="opt">(optional)</span>
                            </label>
                            <input type="url" id="website" name="website"
                                   class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website') }}"
                                   placeholder="https://yourwebsite.com">
                            @error('website')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-back" data-prev="1">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn-next" data-next="3">
                            Continue <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- ─────────────────────────────────────────
                     STEP 3 — Business Information
                ───────────────────────────────────────── --}}
                <div class="step-panel" id="step3">
                    <div class="step-heading">
                        <p class="step-heading__title">Business &amp; location</p>
                        <p class="step-heading__sub">Help us verify your business and set up delivery zones</p>
                    </div>

                    {{-- Business type cards --}}
                    <div class="form-group" style="margin-bottom:18px;">
                        <label class="form-label">Business Type <span style="color:#e53e3e">*</span></label>
                        <div class="biz-cards" id="bizCards">
                            <label class="biz-card {{ old('business_type') == 'individual' ? 'selected' : '' }}" data-value="individual">
                                <input type="radio" name="business_type" value="individual"
                                       {{ old('business_type') == 'individual' ? 'checked' : '' }} required>
                                <i class="fa-solid fa-person"></i>
                                <span class="biz-card__label">Individual</span>
                            </label>
                            <label class="biz-card {{ old('business_type') == 'company' ? 'selected' : '' }}" data-value="company">
                                <input type="radio" name="business_type" value="company"
                                       {{ old('business_type') == 'company' ? 'checked' : '' }}>
                                <i class="fa-solid fa-building"></i>
                                <span class="biz-card__label">Company</span>
                            </label>
                            <label class="biz-card {{ old('business_type') == 'partnership' ? 'selected' : '' }}" data-value="partnership">
                                <input type="radio" name="business_type" value="partnership"
                                       {{ old('business_type') == 'partnership' ? 'checked' : '' }}>
                                <i class="fa-solid fa-handshake"></i>
                                <span class="biz-card__label">Partnership</span>
                            </label>
                        </div>
                        @error('business_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-row form-row--2">
                        <div class="form-group">
                            <label class="form-label" for="tax_id">Tax ID / VAT Number <span class="opt">(optional)</span></label>
                            <input type="text" id="tax_id" name="tax_id"
                                   class="form-control @error('tax_id') is-invalid @enderror"
                                   value="{{ old('tax_id') }}"
                                   placeholder="e.g. 1234567890">
                            @error('tax_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="delivery_zone">Shop Location Zone <span style="color:#e53e3e">*</span></label>
                            <select id="delivery_zone" name="delivery_zone"
                                    class="form-control @error('delivery_zone') is-invalid @enderror"
                                    required>
                                <option value="">— Select your zone —</option>
                                @foreach(\App\Models\DeliveryZone::pickupZones() as $zone)
                                    <option value="{{ $zone }}" {{ old('delivery_zone') == $zone ? 'selected' : '' }}>
                                        {{ $zone }}
                                    </option>
                                @endforeach
                                <option value="Not Included" {{ old('delivery_zone') == 'Not Included' ? 'selected' : '' }}>
                                    Not listed above
                                </option>
                            </select>
                            @error('delivery_zone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <p class="field-hint"><i class="fa-solid fa-circle-info"></i> Determines pickup &amp; delivery rates for your shop</p>
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="address">Street Address <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="address" name="address"
                                   class="form-control @error('address') is-invalid @enderror"
                                   value="{{ old('address') }}"
                                   placeholder="123 Main Street"
                                   required>
                            @error('address')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--3">
                        <div class="form-group">
                            <label class="form-label" for="city">City <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="city" name="city"
                                   class="form-control @error('city') is-invalid @enderror"
                                   value="{{ old('city') }}" placeholder="City" required>
                            @error('city')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="state">State <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="state" name="state"
                                   class="form-control @error('state') is-invalid @enderror"
                                   value="{{ old('state') }}" placeholder="State" required>
                            @error('state')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="postal_code">Postal Code <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="postal_code" name="postal_code"
                                   class="form-control @error('postal_code') is-invalid @enderror"
                                   value="{{ old('postal_code') }}" placeholder="00000" required>
                            @error('postal_code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="country">Country <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="country" name="country"
                                   class="form-control @error('country') is-invalid @enderror"
                                   value="{{ old('country') }}" placeholder="Country" required>
                            @error('country')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-back" data-prev="2">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="button" class="btn-next" data-next="4">
                            Continue <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- ─────────────────────────────────────────
                     STEP 4 — Bank Information
                ───────────────────────────────────────── --}}
                <div class="step-panel" id="step4">
                    <div class="step-heading">
                        <p class="step-heading__title">Payout details</p>
                        <p class="step-heading__sub">Tell us where to send your earnings</p>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="bank_name">Bank Name <span style="color:#e53e3e">*</span></label>
                            <select id="bank_name" name="bank_name"
                                    class="form-control @error('bank_name') is-invalid @enderror"
                                    required>
                                <option value="">Loading banks…</option>
                            </select>
                            {{-- hidden: carries the Paystack bank code for the API call --}}
                            <input type="hidden" id="regBankCode">
                            @error('bank_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="bank_account">Account Number <span style="color:#e53e3e">*</span></label>
                            <input type="text" id="bank_account" name="bank_account"
                                   class="form-control @error('bank_account') is-invalid @enderror"
                                   value="{{ old('bank_account') }}"
                                   placeholder="10-digit account number"
                                   maxlength="10"
                                   pattern="[0-9]{10}"
                                   inputmode="numeric"
                                   required>
                            @error('bank_account')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="account_holder_name">Account Holder Name <span style="color:#e53e3e">*</span></label>
                            <div class="input-wrap verify-wrap">
                                <input type="text" id="account_holder_name" name="account_holder_name"
                                       class="form-control @error('account_holder_name') is-invalid @enderror"
                                       value="{{ old('account_holder_name') }}"
                                       placeholder="Auto-filled after verification"
                                       readonly
                                       required>
                                <i class="fa-solid fa-shield-halved verify-badge" id="regVerifyIcon"></i>
                            </div>
                            <p class="acct-hint" id="regAcctHint">Select a bank and enter your 10-digit account number to auto-verify.</p>
                            @error('account_holder_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    {{-- Terms --}}
                    <div class="form-row form-row--1" style="margin-top:6px;">
                        <div class="terms-row">
                            <input type="checkbox" id="terms" required>
                            <label for="terms">
                                I have read and agree to the
                                <a href="#" target="_blank">Terms &amp; Conditions</a>
                                and
                                <a href="#" target="_blank">Privacy Policy</a>.
                                I confirm that all information provided is accurate.
                            </label>
                        </div>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-back" data-prev="3">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn-submit-final">
                            <i class="fa-solid fa-store"></i>
                            Create Seller Account
                        </button>
                    </div>
                </div>

            </form>{{-- #regForm --}}

            <div class="auth-foot">
                <p>Already have a seller account? <a href="{{ route('seller.login.form') }}">Sign in</a></p>
                <p><a href="{{ route('login') }}"><i class="fa-regular fa-user fa-xs" style="margin-right:4px;"></i>Customer login</a></p>
            </div>

        </div>
    </main>

</div>{{-- .auth-shell --}}

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
<script>
/* ── Step metadata ─────────────────────────────────────── */
var STEPS = {
    1: { eyebrow: 'Step 1 of 4', title: 'Personal Information',    sub: 'Let\'s start with your basic account details' },
    2: { eyebrow: 'Step 2 of 4', title: 'Shop Details',            sub: 'Set up your shop\'s public identity' },
    3: { eyebrow: 'Step 3 of 4', title: 'Business Information',    sub: 'Help us verify your business and address' },
    4: { eyebrow: 'Step 4 of 4', title: 'Bank Details',            sub: 'Almost there — where should we send your payouts?' }
};

var currentStep = 1;

/* ── Navigate to a step ────────────────────────────────── */
function goToStep(target, skipValidation) {
    if (!skipValidation && target > currentStep) {
        if (!validateStep(currentStep)) return;
    }

    // Hide current, show target
    document.getElementById('step' + currentStep).classList.remove('active');
    document.getElementById('step' + target).classList.add('active');

    // Update header text
    document.getElementById('stepEyebrow').textContent = STEPS[target].eyebrow;
    document.getElementById('stepTitle').textContent   = STEPS[target].title;
    document.getElementById('stepSub').textContent     = STEPS[target].sub;

    // Update panel step indicators
    document.querySelectorAll('#panelSteps .panel-step').forEach(function(el) {
        var s = parseInt(el.getAttribute('data-step'));
        el.classList.remove('active', 'done');
        if (s === target)      el.classList.add('active');
        else if (s < target)   el.classList.add('done');
    });

    // Update mobile dots
    document.querySelectorAll('#mobileDots .mobile-step-dot').forEach(function(el) {
        var d = parseInt(el.getAttribute('data-dot'));
        el.classList.remove('active', 'done');
        if (d === target)    el.classList.add('active');
        else if (d < target) el.classList.add('done');
    });

    currentStep = target;

    // Scroll form side back to top
    document.querySelector('.auth-form-side').scrollTo({ top: 0, behavior: 'smooth' });
}

/* ── Validate required fields in a step ───────────────── */
function validateStep(step) {
    var panel  = document.getElementById('step' + step);
    var fields = panel.querySelectorAll('input[required], select[required], textarea[required]');
    var valid  = true;

    fields.forEach(function(field) {
        field.classList.remove('is-invalid');

        if (field.type === 'radio') return; // handled separately below

        if (field.type === 'file') {
            if (!field.files || field.files.length === 0) {
                field.closest('.upload-zone').style.borderColor = '#e53e3e';
                valid = false;
            }
        } else if (field.type === 'checkbox') {
            if (!field.checked) {
                field.closest('.terms-row').style.borderColor = '#e53e3e';
                valid = false;
            }
        } else {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                valid = false;
            }
        }
    });

    // Check radio groups — check ALL radio groups by name, not just [required]
    var radioGroups = {};
    panel.querySelectorAll('input[type="radio"]').forEach(function(r) {
        // Only include groups that have at least one [required] radio
        if (r.required) {
            radioGroups[r.name] = radioGroups[r.name] || { radios: [], required: false };
            radioGroups[r.name].required = true;
        }
        radioGroups[r.name] = radioGroups[r.name] || { radios: [], required: false };
        radioGroups[r.name].radios.push(r);
    });

    Object.keys(radioGroups).forEach(function(name) {
        var group = radioGroups[name];
        if (!group.required) return;
        var checked = group.radios.some(function(r) { return r.checked; });
        if (!checked) {
            valid = false;
            var bc = document.getElementById('bizCards');
            if (bc) {
                bc.style.outline = '2px solid #e53e3e';
                bc.style.borderRadius = '10px';
            }
        }
    });

    if (!valid) {
        // Scroll to first error
        var firstErr = panel.querySelector('.is-invalid, [style*="border-color: rgb(229"]');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    return valid;
}

/* ── Wire up buttons ───────────────────────────────────── */
document.querySelectorAll('[data-next]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        goToStep(parseInt(this.getAttribute('data-next')), false);
    });
});

document.querySelectorAll('[data-prev]').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        goToStep(parseInt(this.getAttribute('data-prev')), true);
    });
});

/* ── Business type card selection ──────────────────────── */
document.querySelectorAll('.biz-card').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.biz-card').forEach(function(c) { c.classList.remove('selected'); });
        this.classList.add('selected');
        this.querySelector('input[type="radio"]').checked = true;
        // Clear validation outline immediately
        var bc = document.getElementById('bizCards');
        if (bc) {
            bc.style.outline = '';
            bc.style.borderRadius = '';
        }
    });
});

/* ── Upload zone handler ───────────────────────────────── */
function handleUpload(input, zoneId, previewId, filenameId) {
    var zone     = document.getElementById(zoneId);
    var preview  = document.getElementById(previewId);
    var filename = document.getElementById(filenameId);

    if (input.files && input.files[0]) {
        var file   = input.files[0];
        var reader = new FileReader();

        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display   = 'block';
            filename.textContent    = file.name;
            filename.style.display  = 'block';
        };

        reader.readAsDataURL(file);
        zone.classList.add('has-file');
        zone.style.borderColor = ''; // clear any validation red
    }
}

/* ── Password toggle ───────────────────────────────────── */
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

/* ── On server validation error, jump to the right step ── */
@if($errors->any())
    @php
        $errorFields = array_keys($errors->toArray());
        $step1Fields = ['name','email','password','phone_number'];
        $step2Fields = ['shop_name','shop_description','shop_logo','banner','website'];
        $step3Fields = ['business_type','tax_id','address','city','state','postal_code','country','delivery_zone'];

        $jumpTo = 4; // default to last step
        foreach($errorFields as $field) {
            if(in_array($field, $step1Fields)) { $jumpTo = 1; break; }
            if(in_array($field, $step2Fields)) { $jumpTo = 2; break; }
            if(in_array($field, $step3Fields)) { $jumpTo = 3; break; }
        }
    @endphp
    goToStep({{ $jumpTo }}, true);
@endif

/* ── Paystack bank account resolver (Step 4) ───────────── */
(function () {
    var bankSelect    = document.getElementById('bank_name');
    var bankCodeInput = document.getElementById('regBankCode');
    var acctInput     = document.getElementById('bank_account');
    var nameInput     = document.getElementById('account_holder_name');
    var hint          = document.getElementById('regAcctHint');
    var icon          = document.getElementById('regVerifyIcon');

    var RESOLVE_URL   = '{{ route("bank.resolve") }}';
    var BANK_LIST_URL = '{{ route("bank.list") }}';
    var SAVED_BANK    = '{{ old("bank_name", "") }}';
    var CSRF          = document.querySelector('meta[name="csrf-token"]') ?
                        document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';

    var resolveTimer  = null;

    function setStatus(state, msg) {
        hint.textContent = msg;
        hint.className   = 'acct-hint' + (state ? ' ' + state : '');
        icon.className   = 'fa-solid verify-badge ' + state + ' ' + (
            state === 'ok'      ? 'fa-shield-halved' :
            state === 'error'   ? 'fa-circle-xmark'  :
            state === 'loading' ? 'fa-circle-notch'  : 'fa-shield-halved'
        );
    }

    /* Load full Nigerian bank list from our proxy (cached 24h server-side) */
    function loadBanks() {
        fetch(BANK_LIST_URL, { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(json) {
                var banks = json.data || [];
                bankSelect.innerHTML = '<option value="">— Select your bank —</option>';
                banks.forEach(function(b) {
                    var opt = document.createElement('option');
                    opt.value            = b.name;
                    opt.dataset.code     = b.code;
                    opt.textContent      = b.name;
                    if (b.name === SAVED_BANK) opt.selected = true;
                    bankSelect.appendChild(opt);
                });
                /* Restore bank code for pre-selected option (server validation return) */
                var sel = bankSelect.options[bankSelect.selectedIndex];
                if (sel && sel.dataset.code) {
                    bankCodeInput.value = sel.dataset.code;
                    /* If account number also pre-filled, auto-verify on load */
                    if (acctInput.value.trim().length === 10) {
                        resolveAccount();
                    }
                }
            })
            .catch(function() {
                bankSelect.innerHTML = '<option value="">Could not load banks — please refresh</option>';
            });
    }

    function resolveAccount() {
        var accountNumber = acctInput.value.trim();
        var bankCode      = bankCodeInput.value;
        if (accountNumber.length !== 10 || !bankCode) return;

        setStatus('loading', 'Verifying your account…');
        nameInput.value = '';

        var params = 'account_number=' + encodeURIComponent(accountNumber) +
                     '&bank_code='      + encodeURIComponent(bankCode);

        fetch(RESOLVE_URL + '?' + params, {
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(json) {
            if (json.status && json.account_name) {
                nameInput.value = json.account_name;
                setStatus('ok', 'Account verified ✓  ' + json.account_name);
            } else {
                setStatus('error', json.message || 'Could not verify account. Check the number and selected bank.');
            }
        })
        .catch(function() {
            setStatus('error', 'Verification failed. Please check your connection and try again.');
        });
    }

    /* Trigger resolve when account number reaches 10 digits */
    acctInput.addEventListener('input', function() {
        clearTimeout(resolveTimer);
        var val = acctInput.value.trim();
        if (val.length === 10) {
            resolveTimer = setTimeout(resolveAccount, 500);
        } else {
            nameInput.value = '';
            setStatus('', 'Select a bank and enter your 10-digit account number to auto-verify.');
        }
    });

    /* Trigger resolve when bank changes (if account number already filled) */
    bankSelect.addEventListener('change', function() {
        var sel = bankSelect.options[bankSelect.selectedIndex];
        bankCodeInput.value = sel && sel.dataset.code ? sel.dataset.code : '';
        clearTimeout(resolveTimer);
        if (acctInput.value.trim().length === 10) {
            resolveTimer = setTimeout(resolveAccount, 300);
        }
    });

    loadBanks();
})();
</script>
</body>
</html>
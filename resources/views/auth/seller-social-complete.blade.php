{{-- resources/views/auth/seller-social-complete.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Complete Your Seller Profile — {{ config('app.name', 'GenesisHub') }}</title>

<link rel="icon" type="image/png" href="{{ asset('public/image/auth-logo.png') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('public/css/bootstrap.min.css') }}">

<style>
/* ── Identical design tokens & layout to seller-register ── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { height: 100%; font-family: 'Inter', sans-serif; background: #f3f5f6; color: #1a1a1a; }

.auth-shell { display: flex; min-height: 100vh; }

/* LEFT PANEL */
.auth-panel {
    flex: 0 0 42%; background: #714e32; position: sticky; top: 0; height: 100vh;
    display: flex; flex-direction: column; justify-content: space-between;
    padding: 48px 52px; overflow: hidden;
}
.auth-panel::before {
    content: ''; position: absolute; inset: 0; pointer-events: none; z-index: 0;
    background-image:
        radial-gradient(circle at 15% 15%, rgba(245,195,75,.14) 0%, transparent 55%),
        radial-gradient(circle at 85% 85%, rgba(255,255,255,.05) 0%, transparent 50%);
}
.auth-panel__ring { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,.08); pointer-events: none; z-index: 0; }
.auth-panel__ring--lg { width: 460px; height: 460px; bottom: -140px; right: -130px; }
.auth-panel__ring--sm { width: 200px; height: 200px; top: 90px; right: 50px; border-color: rgba(245,195,75,.12); }
.auth-panel__logo { position: relative; z-index: 1; }
.auth-panel__logo img { height: 42px; display: block; filter: brightness(0) invert(1); }
.auth-panel__body { position: relative; z-index: 1; flex: 1; display: flex; flex-direction: column; justify-content: center; padding: 56px 0 40px; }

.auth-panel__pill {
    display: inline-flex; align-items: center; gap: 7px;
    background: rgba(245,195,75,.16); border: 1px solid rgba(245,195,75,.32);
    color: #f5c34b; font-size: 11.5px; font-weight: 700; letter-spacing: .09em;
    text-transform: uppercase; padding: 5px 14px; border-radius: 100px;
    margin-bottom: 24px; width: fit-content;
}
.auth-panel__heading { font-family: 'Poppins', sans-serif; font-size: clamp(22px, 2.6vw, 30px); font-weight: 800; line-height: 1.22; color: #fff; margin-bottom: 16px; }
.auth-panel__heading em { font-style: normal; color: #f5c34b; }
.auth-panel__desc { font-size: 14px; line-height: 1.72; color: rgba(255,255,255,.62); max-width: 320px; margin-bottom: 36px; }

/* Social info card in left panel */
.social-info-card {
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px; padding: 16px 18px; margin-bottom: 28px;
}
.social-info-card__label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.45); margin-bottom: 10px; }
.social-info-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.social-info-row:last-child { margin-bottom: 0; }
.social-info-row i { font-size: 13px; color: #f5c34b; width: 16px; flex-shrink: 0; }
.social-info-row span { font-size: 13.5px; color: rgba(255,255,255,.85); font-weight: 500; }

/* Step tracker */
.panel-steps { display: flex; flex-direction: column; gap: 0; }
.panel-step { display: flex; align-items: flex-start; gap: 14px; position: relative; }
.panel-step:not(:last-child)::after { content: ''; position: absolute; left: 17px; top: 36px; width: 2px; height: calc(100% - 4px); background: rgba(255,255,255,.12); }
.panel-step.active:not(:last-child)::after { background: rgba(245,195,75,.3); }
.panel-step__num { width: 36px; height: 36px; border-radius: 50%; border: 2px solid rgba(255,255,255,.2); background: rgba(255,255,255,.07); display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .3s ease; position: relative; z-index: 1; }
.panel-step__num i { font-size: 13px; color: rgba(255,255,255,.5); transition: color .3s; }
.panel-step.active .panel-step__num { background: #f5c34b; border-color: #f5c34b; box-shadow: 0 4px 14px rgba(245,195,75,.35); }
.panel-step.active .panel-step__num i { color: #714e32; }
.panel-step.done .panel-step__num { background: rgba(245,195,75,.2); border-color: rgba(245,195,75,.4); }
.panel-step.done .panel-step__num i { color: #f5c34b; }
.panel-step__text { padding: 6px 0 28px; }
.panel-step__label { font-size: 13px; font-weight: 600; color: rgba(255,255,255,.5); transition: color .3s; line-height: 1.3; }
.panel-step.active .panel-step__label { color: #fff; }
.panel-step.done .panel-step__label { color: rgba(255,255,255,.75); }
.panel-step__sub { font-size: 12px; color: rgba(255,255,255,.35); margin-top: 2px; }
.panel-step.active .panel-step__sub { color: rgba(255,255,255,.55); }
.auth-panel__footer { position: relative; z-index: 1; font-size: 12px; color: rgba(255,255,255,.35); }

/* RIGHT PANEL */
.auth-form-side { flex: 1; display: flex; flex-direction: column; align-items: center; padding: 60px 40px 80px; overflow-y: auto; background: #f3f5f6; min-height: 100vh; }
.auth-form-box { width: 100%; max-width: 560px; }
.auth-mobile-logo { display: none; text-align: center; margin-bottom: 36px; }
.auth-mobile-logo img { height: 38px; }

.form-header { margin-bottom: 32px; }
.form-header__eyebrow { font-size: 11.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #714e32; margin-bottom: 9px; }
.form-header__title { font-family: 'Poppins', sans-serif; font-size: 26px; font-weight: 700; color: #1a1a1a; line-height: 1.25; margin-bottom: 5px; }
.form-header__sub { font-size: 14px; color: #555e68; line-height: 1.55; }

/* Mobile step dots */
.mobile-steps { display: none; gap: 6px; margin-bottom: 28px; align-items: center; }
.mobile-step-dot { height: 4px; border-radius: 100px; background: #e0e4ea; flex: 1; transition: background .3s, flex .3s; }
.mobile-step-dot.active { background: #714e32; flex: 2; }
.mobile-step-dot.done { background: rgba(113,78,50,.4); }

/* Step panels */
.step-panel { display: none; }
.step-panel.active { display: block; animation: stepIn .35s ease both; }
@keyframes stepIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }

.step-heading { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1.5px solid #e8eaed; }
.step-heading__title { font-family: 'Poppins', sans-serif; font-size: 18px; font-weight: 700; color: #1a1a1a; margin-bottom: 3px; }
.step-heading__sub { font-size: 13.5px; color: #555e68; }

/* Form controls */
.form-row { display: grid; gap: 18px; margin-bottom: 18px; }
.form-row--2 { grid-template-columns: 1fr 1fr; }
.form-row--1 { grid-template-columns: 1fr; }
.form-row--3 { grid-template-columns: 1fr 1fr 1fr; }
.form-group { display: flex; flex-direction: column; }
.form-label { display: block; font-size: 13px; font-weight: 600; color: #2d2d2d; margin-bottom: 7px; }
.form-label .opt { font-weight: 400; color: #8a94a6; font-size: 12px; }
.form-control { display: block; width: 100%; height: 50px; padding: 0 14px; font-size: 14px; font-family: 'Inter', sans-serif; color: #1a1a1a; background: #fff; border: 1.5px solid #e0e4ea; border-radius: 10px; transition: border-color .2s, box-shadow .2s; -webkit-appearance: none; }
.form-control::placeholder { color: #b0b8c4; font-size: 13.5px; }
.form-control:focus { outline: none; border-color: #714e32; box-shadow: 0 0 0 3.5px rgba(113,78,50,.11); }
.form-control.is-invalid { border-color: #e53e3e; }
textarea.form-control { height: auto; padding: 12px 14px; resize: vertical; min-height: 110px; }
select.form-control { cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238a94a6' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 36px; }
.invalid-feedback { font-size: 12px; color: #e53e3e; margin-top: 5px; display: block; }

/* Upload zones */
.upload-zone { border: 2px dashed #dde1e8; border-radius: 12px; padding: 28px 20px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s; position: relative; background: #fff; }
.upload-zone:hover, .upload-zone.has-file { border-color: #714e32; background: rgba(113,78,50,.02); }
.upload-zone input[type="file"] { position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%; }
.upload-zone__icon { width: 44px; height: 44px; border-radius: 10px; background: rgba(113,78,50,.08); display: inline-flex; align-items: center; justify-content: center; margin-bottom: 12px; }
.upload-zone__icon i { font-size: 18px; color: #714e32; }
.upload-zone__title { font-size: 13.5px; font-weight: 600; color: #2d2d2d; margin-bottom: 4px; }
.upload-zone__hint { font-size: 12px; color: #8a94a6; }
.upload-zone__preview { margin-top: 12px; display: none; }
.upload-zone__preview img { max-height: 80px; max-width: 100%; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,.1); }
.upload-zone__filename { margin-top: 8px; font-size: 12px; color: #714e32; font-weight: 600; display: none; }
.field-hint { font-size: 12px; color: #8a94a6; margin-top: 6px; display: flex; align-items: flex-start; gap: 5px; line-height: 1.5; }
.field-hint i { margin-top: 2px; flex-shrink: 0; }

/* Business type cards */
.biz-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
.biz-card { border: 1.5px solid #e0e4ea; border-radius: 10px; padding: 14px 12px; text-align: center; cursor: pointer; transition: border-color .2s, background .2s, box-shadow .2s; position: relative; background: #fff; }
.biz-card input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; }
.biz-card i { font-size: 22px; color: #8a94a6; margin-bottom: 8px; display: block; transition: color .2s; }
.biz-card__label { font-size: 13px; font-weight: 600; color: #555e68; transition: color .2s; }
.biz-card.selected { border-color: #714e32; background: rgba(113,78,50,.05); box-shadow: 0 0 0 3px rgba(113,78,50,.1); }
.biz-card.selected i, .biz-card.selected .biz-card__label { color: #714e32; }

/* Nav buttons */
.step-nav { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 32px; padding-top: 24px; border-top: 1.5px solid #e8eaed; }
.btn-back { display: inline-flex; align-items: center; gap: 8px; height: 50px; padding: 0 24px; background: #fff; border: 1.5px solid #e0e4ea; border-radius: 10px; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif; color: #555e68; cursor: pointer; transition: border-color .2s, color .2s, background .2s; }
.btn-back:hover { border-color: #714e32; color: #714e32; background: rgba(113,78,50,.04); }
.btn-next { display: inline-flex; align-items: center; gap: 8px; height: 50px; padding: 0 28px; background: #714e32; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif; color: #fff; cursor: pointer; letter-spacing: .02em; transition: background .2s, box-shadow .2s, transform .1s; margin-left: auto; }
.btn-next:hover { background: #5a3d26; box-shadow: 0 8px 24px rgba(113,78,50,.3); }
.btn-submit-final { display: flex; align-items: center; justify-content: center; gap: 9px; height: 52px; padding: 0 32px; background: #714e32; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; font-family: 'Inter', sans-serif; color: #fff; cursor: pointer; transition: background .2s, box-shadow .2s; }
.btn-submit-final:hover { background: #5a3d26; box-shadow: 0 8px 24px rgba(113,78,50,.3); }

/* Terms */
.terms-row { display: flex; align-items: flex-start; gap: 10px; padding: 16px 18px; background: rgba(113,78,50,.04); border: 1px solid rgba(113,78,50,.12); border-radius: 10px; }
.terms-row input[type="checkbox"] { width: 17px; height: 17px; margin-top: 2px; border-radius: 4px; cursor: pointer; accent-color: #714e32; flex-shrink: 0; }
.terms-row label { font-size: 13.5px; color: #555e68; line-height: 1.55; cursor: pointer; }
.terms-row a { color: #714e32; font-weight: 600; text-decoration: none; }

/* Notices */
.notice { display: flex; gap: 13px; align-items: flex-start; border-radius: 12px; padding: 15px 17px; margin-bottom: 24px; }
.notice--danger { background: #fff4f4; border: 1px solid #ffc2c2; }
.notice--danger .notice__icon { width: 34px; height: 34px; border-radius: 8px; background: rgba(229,62,62,.1); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.notice--danger .notice__icon i { font-size: 14px; color: #c53030; }
.notice--danger .notice__title { font-size: 13px; font-weight: 700; color: #822020; margin-bottom: 3px; }
.notice--danger .notice__text { font-size: 12.5px; color: #9b2626; line-height: 1.55; margin: 0; }

/* Social account banner */
.social-account-banner {
    display: flex; align-items: center; gap: 14px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 12px; padding: 14px 18px; margin-bottom: 28px;
}
.social-account-banner__icon { width: 40px; height: 40px; border-radius: 10px; background: #dcfce7; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.social-account-banner__icon i { font-size: 16px; color: #16a34a; }
.social-account-banner__text p { margin: 0; font-size: 13px; color: #166534; line-height: 1.5; }
.social-account-banner__text strong { font-weight: 700; }

.auth-foot { display: flex; flex-direction: column; gap: 10px; align-items: center; margin-top: 28px; }
.auth-foot p { font-size: 13.5px; color: #555e68; margin: 0; }
.auth-foot a { color: #714e32; font-weight: 600; text-decoration: none; transition: color .2s; }
.auth-foot a:hover { color: #5a3d26; text-decoration: underline; }

@media (max-width: 900px) { .auth-panel { flex: 0 0 38%; padding: 40px 36px; } .auth-form-side { padding: 48px 28px 80px; } }
@media (max-width: 720px) { .auth-panel { display: none; } .auth-mobile-logo { display: block; } .mobile-steps { display: flex; } .auth-form-side { padding: 40px 20px 80px; } .auth-form-box { max-width: 100%; } .form-row--2 { grid-template-columns: 1fr; } .form-row--3 { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .biz-cards { grid-template-columns: 1fr; } .form-row--3 { grid-template-columns: 1fr; } .step-nav { flex-direction: column-reverse; } .btn-back, .btn-next, .btn-submit-final { width: 100%; justify-content: center; } }
</style>
</head>
<body>

<div class="auth-shell">

    {{-- LEFT PANEL ──────────────────────────────────────────────── --}}
    <aside class="auth-panel">
        <div class="auth-panel__ring auth-panel__ring--lg"></div>
        <div class="auth-panel__ring auth-panel__ring--sm"></div>

        <div class="auth-panel__logo">
            <a href="{{ url('/') }}">
                <img src="{{ asset('public/image/genehub.png') }}"
                     alt="{{ config('app.name', 'GenesisHub') }}">
            </a>
        </div>

        <div class="auth-panel__body">
            <div class="auth-panel__pill">
                <i class="fa-solid fa-rocket fa-xs"></i>
                Almost there
            </div>

            <h2 class="auth-panel__heading">
                One last step to<br><em>open your shop.</em>
            </h2>

            <p class="auth-panel__desc">
                Your account is connected. Just tell us about your shop and we'll get you selling.
            </p>

            {{-- Show the user's info from social provider --}}
            <div class="social-info-card">
                <div class="social-info-card__label">Connected account</div>
                <div class="social-info-row">
                    <i class="fa-solid fa-user"></i>
                    <span>{{ $user->name }}</span>
                </div>
                <div class="social-info-row">
                    <i class="fa-solid fa-envelope"></i>
                    <span>{{ $user->email }}</span>
                </div>
                <div class="social-info-row">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Email verified</span>
                </div>
            </div>

            {{-- Step tracker — shows steps 1,2,3 mapped to shop/business/bank --}}
            <div class="panel-steps" id="panelSteps">
                <div class="panel-step done" data-step="1">
                    <div class="panel-step__num"><i class="fa-solid fa-check"></i></div>
                    <div class="panel-step__text">
                        <div class="panel-step__label">Account Created</div>
                        <div class="panel-step__sub">Via Google / Facebook</div>
                    </div>
                </div>
                <div class="panel-step active" data-step="2">
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

    {{-- RIGHT PANEL ─────────────────────────────────────────────── --}}
    <main class="auth-form-side">
        <div class="auth-form-box">

            <div class="auth-mobile-logo">
                <a href="{{ url('/') }}">
                    <img src="{{ asset('public/image/auth-logo.png') }}"
                         alt="{{ config('app.name', 'GenesisHub') }}">
                </a>
            </div>

            {{-- Mobile step dots (3 remaining steps) --}}
            <div class="mobile-steps" id="mobileDots">
                <div class="mobile-step-dot active" data-dot="2"></div>
                <div class="mobile-step-dot" data-dot="3"></div>
                <div class="mobile-step-dot" data-dot="4"></div>
            </div>

            {{-- Form header --}}
            <div class="form-header">
                <p class="form-header__eyebrow" id="stepEyebrow">Step 1 of 3</p>
                <h1 class="form-header__title" id="stepTitle">Shop Details</h1>
                <p class="form-header__sub" id="stepSub">Set up your shop's public identity</p>
            </div>

            {{-- Social account confirmation banner --}}
            <div class="social-account-banner">
                <div class="social-account-banner__icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="social-account-banner__text">
                    <p>
                        <strong>Account connected — {{ $user->name }}</strong><br>
                        Your email <strong>{{ $user->email }}</strong> is verified. Just fill in your shop details below to finish.
                    </p>
                </div>
            </div>

            {{-- Server validation errors --}}
            @if($errors->any())
            <div class="notice notice--danger">
                <div class="notice__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
                <div>
                    <p class="notice__title">Please fix the following</p>
                    <p class="notice__text">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif

            {{-- THE FORM ─────────────────────────────────────────── --}}
            <form method="POST"
                  action="{{ route('seller.social.complete') }}"
                  enctype="multipart/form-data"
                  id="completeForm"
                  novalidate>
                @csrf

                {{-- ── STEP 2: Shop Information ───────────────────── --}}
                <div class="step-panel active" id="step2">
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
                                   placeholder="e.g. Ade's Fashion Store" required>
                            @error('shop_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--1">
                        <div class="form-group">
                            <label class="form-label" for="shop_description">Shop Description <span style="color:#e53e3e">*</span></label>
                            <textarea id="shop_description" name="shop_description"
                                      class="form-control @error('shop_description') is-invalid @enderror"
                                      placeholder="Tell customers what makes your shop unique…"
                                      required>{{ old('shop_description') }}</textarea>
                            @error('shop_description')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="form-row form-row--2" style="margin-bottom:18px;">
                        <div class="form-group">
                            <label class="form-label">Shop Logo <span style="color:#e53e3e">*</span></label>
                            <div class="upload-zone @error('shop_logo') is-invalid @enderror" id="logoZone">
                                <input type="file" name="shop_logo" id="shop_logo" accept="image/*"
                                       onchange="handleUpload(this,'logoZone','logoPreview','logoFilename')" required>
                                <div class="upload-zone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <p class="upload-zone__title">Upload logo</p>
                                <p class="upload-zone__hint">JPEG, PNG, GIF · Max 2MB</p>
                                <div class="upload-zone__preview" id="logoPreview"><img src="" alt="Logo preview"></div>
                                <p class="upload-zone__filename" id="logoFilename"></p>
                            </div>
                            @error('shop_logo')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <p class="field-hint"><i class="fa-solid fa-circle-info"></i> Recommended: 200×200px square image</p>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Shop Banner <span class="opt">(optional)</span></label>
                            <div class="upload-zone" id="bannerZone">
                                <input type="file" name="banner" id="banner" accept="image/*"
                                       onchange="handleUpload(this,'bannerZone','bannerPreview','bannerFilename')">
                                <div class="upload-zone__icon"><i class="fa-solid fa-image"></i></div>
                                <p class="upload-zone__title">Upload banner</p>
                                <p class="upload-zone__hint">JPEG, PNG · Max 4MB</p>
                                <div class="upload-zone__preview" id="bannerPreview"><img src="" alt="Banner preview"></div>
                                <p class="upload-zone__filename" id="bannerFilename"></p>
                            </div>
                            @error('banner')<span class="invalid-feedback">{{ $message }}</span>@enderror
                            <p class="field-hint"><i class="fa-solid fa-circle-info"></i> Recommended: 1200×300px</p>
                        </div>
                    </div>

                    <div class="form-row form-row--2">
                        <div class="form-group">
                            <label class="form-label" for="phone_number">Phone Number <span style="color:#e53e3e">*</span></label>
                            <input type="tel" id="phone_number" name="phone_number"
                                   class="form-control @error('phone_number') is-invalid @enderror"
                                   value="{{ old('phone_number') }}"
                                   placeholder="+234 800 000 0000" required>
                            @error('phone_number')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="website">Website <span class="opt">(optional)</span></label>
                            <input type="url" id="website" name="website"
                                   class="form-control @error('website') is-invalid @enderror"
                                   value="{{ old('website') }}" placeholder="https://yourwebsite.com">
                            @error('website')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-next" data-next="3">
                            Continue <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                {{-- ── STEP 3: Business Information ───────────────── --}}
                <div class="step-panel" id="step3">
                    <div class="step-heading">
                        <p class="step-heading__title">Business &amp; location</p>
                        <p class="step-heading__sub">Help us verify your business and set up delivery zones</p>
                    </div>

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
       {{ old('business_type') == 'company' ? 'checked' : '' }} required> 
                                <i class="fa-solid fa-building"></i>
                                <span class="biz-card__label">Company</span>
                            </label>
                            <label class="biz-card {{ old('business_type') == 'partnership' ? 'selected' : '' }}" data-value="partnership">
                                <input type="radio" name="business_type" value="partnership"
       {{ old('business_type') == 'partnership' ? 'checked' : '' }} required> 
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
                                   value="{{ old('tax_id') }}" placeholder="e.g. 1234567890">
                            @error('tax_id')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="delivery_zone">Shop Location Zone <span style="color:#e53e3e">*</span></label>
                            <select id="delivery_zone" name="delivery_zone"
                                    class="form-control @error('delivery_zone') is-invalid @enderror" required>
                                <option value="">— Select your zone —</option>
                                @foreach(\App\Models\DeliveryZone::pickupZones() as $zone)
                                    <option value="{{ $zone }}" {{ old('delivery_zone') == $zone ? 'selected' : '' }}>{{ $zone }}</option>
                                @endforeach
                                <option value="Not Included" {{ old('delivery_zone') == 'Not Included' ? 'selected' : '' }}>Not listed above</option>
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
                                   value="{{ old('address') }}" placeholder="123 Main Street" required>
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

                {{-- ── STEP 4: Bank Information ────────────────────── --}}
                {{-- ── STEP 4: Bank Information ────────────────────── --}}
<div class="step-panel" id="step4">
    <div class="step-heading">
        <p class="step-heading__title">Payout details</p>
        <p class="step-heading__sub">Tell us where to send your earnings</p>
    </div>

    {{-- Bank selector --}}
    <div class="form-row form-row--1">
        <div class="form-group">
            <label class="form-label" for="bank_search">Bank Name <span style="color:#e53e3e">*</span></label>
            <input type="text" id="bank_search" autocomplete="off"
                   class="form-control" placeholder="Type to search your bank…">
            <ul id="bank_dropdown" style="
                display:none; position:absolute; z-index:999; background:#fff;
                border:1.5px solid #e0e4ea; border-radius:10px; margin-top:4px;
                max-height:220px; overflow-y:auto; padding:6px 0; width:100%;
                box-shadow:0 8px 24px rgba(0,0,0,.1); list-style:none;
            "></ul>
            {{-- Hidden fields submitted with the form --}}
            <input type="hidden" id="bank_name" name="bank_name"
                   value="{{ old('bank_name') }}" required>
            <input type="hidden" id="bank_code" name="bank_code"
                   value="{{ old('bank_code') }}">
            @error('bank_name')<span class="invalid-feedback" style="display:block">{{ $message }}</span>@enderror
        </div>
    </div>

    {{-- Account number --}}
    <div class="form-row form-row--1">
        <div class="form-group">
            <label class="form-label" for="bank_account">Account Number <span style="color:#e53e3e">*</span></label>
            <input type="text" id="bank_account" name="bank_account" maxlength="10" inputmode="numeric"
                   class="form-control @error('bank_account') is-invalid @enderror"
                   value="{{ old('bank_account') }}"
                   placeholder="Enter 10-digit account number" required>
            @error('bank_account')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>

    {{-- Account holder name — auto-filled, read-only --}}
    <div class="form-row form-row--1">
        <div class="form-group" id="accountNameGroup" style="display:none">
            <label class="form-label" for="account_holder_name">Account Holder Name</label>
            <div style="position:relative">
                <input type="text" id="account_holder_name" name="account_holder_name" readonly
                       class="form-control @error('account_holder_name') is-invalid @enderror"
                       value="{{ old('account_holder_name') }}"
                       style="background:#f8faf8; font-weight:600; color:#166534; padding-right:42px;">
                <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%);">
                    <i class="fa-solid fa-circle-check" style="color:#16a34a; font-size:16px;"></i>
                </span>
            </div>
            @error('account_holder_name')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>

        {{-- Resolving spinner --}}
        <div id="resolveSpinner" style="display:none; align-items:center; gap:10px; padding:10px 14px;
             background:#fffbeb; border:1.5px solid #fde68a; border-radius:10px; font-size:13.5px; color:#92400e;">
            <i class="fa-solid fa-spinner fa-spin"></i> Verifying account name…
        </div>

        {{-- Resolve error --}}
        <div id="resolveError" style="display:none; align-items:center; gap:10px; padding:10px 14px;
             background:#fff4f4; border:1.5px solid #ffc2c2; border-radius:10px; font-size:13.5px; color:#c53030;">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span id="resolveErrorMsg">Could not verify account.</span>
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
        <button type="submit" id="submitBtn" class="btn-submit-final" disabled>
            <i class="fa-solid fa-store"></i>
            Complete Seller Profile
        </button>
    </div>
</div>

            </form>

            <div class="auth-foot">
                <p>Want to use a different account? <a href="{{ route('seller.register.form') }}">Register manually</a></p>
            </div>

        </div>
    </main>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('public/js/bootstrap.min.js') }}"></script>
<script>
// ── Bank auto-resolve ────────────────────────────────────────────
(function () {
    var banks        = [];
    var resolveTimer = null;

    var bankSearch   = document.getElementById('bank_search');
    var bankDropdown = document.getElementById('bank_dropdown');
    var bankNameHid  = document.getElementById('bank_name');
    var bankCodeHid  = document.getElementById('bank_code');
    var acctInput    = document.getElementById('bank_account');
    var acctGroup    = document.getElementById('accountNameGroup');
    var acctField    = document.getElementById('account_holder_name');
    var spinner      = document.getElementById('resolveSpinner');
    var errBox       = document.getElementById('resolveError');
    var errMsg       = document.getElementById('resolveErrorMsg');
    var submitBtn    = document.getElementById('submitBtn');

    // ── 1. Load banks on step 4 entry ──
    function loadBanks() {
        if (banks.length) return;
        fetch('{{ route("bank.list") }}')
            .then(function(r) { return r.json(); })
            .then(function(d) { if (d.status) banks = d.data; });
    }

    // Hook: load when "Continue" from step 3 is clicked
    document.querySelector('[data-next="4"]').addEventListener('click', loadBanks);
    // Also load immediately if coming back on page reload with errors
    if (document.getElementById('step4').classList.contains('active')) loadBanks();

    // ── 2. Bank search / dropdown ──
    bankSearch.addEventListener('input', function () {
        var q = this.value.toLowerCase().trim();
        bankNameHid.value = '';
        bankCodeHid.value = '';
        resetResolve();

        if (!q) { bankDropdown.style.display = 'none'; return; }

        var matches = banks.filter(function(b) {
            return b.name.toLowerCase().includes(q);
        }).slice(0, 10);

        if (!matches.length) { bankDropdown.style.display = 'none'; return; }

        bankDropdown.innerHTML = matches.map(function(b) {
            return '<li data-code="' + b.code + '" data-name="' + b.name + '" style="' +
                'padding:10px 16px; cursor:pointer; font-size:13.5px; color:#1a1a1a;' +
                '">' + b.name + '</li>';
        }).join('');
        bankDropdown.style.display = 'block';
    });

    bankDropdown.addEventListener('click', function(e) {
        var li = e.target.closest('li');
        if (!li) return;
        bankNameHid.value = li.getAttribute('data-name');
        bankCodeHid.value = li.getAttribute('data-code');
        bankSearch.value  = li.getAttribute('data-name');
        bankDropdown.style.display = 'none';
        tryResolve();
    });

    // Hover highlight
    bankDropdown.addEventListener('mouseover', function(e) {
        if (e.target.tagName === 'LI') e.target.style.background = '#f5f0eb';
    });
    bankDropdown.addEventListener('mouseout', function(e) {
        if (e.target.tagName === 'LI') e.target.style.background = '';
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!bankSearch.contains(e.target) && !bankDropdown.contains(e.target)) {
            bankDropdown.style.display = 'none';
        }
    });

    // ── 3. Account number input ──
    acctInput.addEventListener('input', function () {
        // Strip non-digits
        this.value = this.value.replace(/\D/g, '');
        resetResolve();
        clearTimeout(resolveTimer);
        if (this.value.length === 10) {
            resolveTimer = setTimeout(tryResolve, 500); // debounce 500 ms
        }
    });

    // ── 4. Resolve ──
    function tryResolve() {
        var acct = acctInput.value.trim();
        var code = bankCodeHid.value.trim();
        if (acct.length !== 10 || !code) return;

        resetResolve();
        spinner.style.display = 'flex';
        submitBtn.disabled = true;

        fetch('{{ route("bank.resolve") }}?account_number=' + acct + '&bank_code=' + code)
            .then(function(r) { return r.json(); })
            .then(function(d) {
                spinner.style.display = 'none';
                if (d.status && d.account_name) {
                    acctField.value        = d.account_name;
                    acctGroup.style.display = 'block';
                    submitBtn.disabled      = false;
                } else {
                    showError(d.message || 'Could not verify account.');
                }
            })
            .catch(function() {
                spinner.style.display = 'none';
                showError('Network error. Please try again.');
            });
    }

    function resetResolve() {
        acctField.value         = '';
        acctGroup.style.display = 'none';
        spinner.style.display   = 'none';
        errBox.style.display    = 'none';
        submitBtn.disabled      = true;
    }

    function showError(msg) {
        errMsg.textContent    = msg;
        errBox.style.display  = 'flex';
        submitBtn.disabled    = true;
    }

    // ── 5. Re-populate on server validation error (old values) ──
    @if(old('bank_name'))
        bankSearch.value  = '{{ old('bank_name') }}';
        bankNameHid.value = '{{ old('bank_name') }}';
        bankCodeHid.value = '{{ old('bank_code') }}';
        @if(old('account_holder_name'))
            acctGroup.style.display = 'block';
            submitBtn.disabled      = false;
        @endif
    @endif

}());
var STEPS = {
    2: { eyebrow: 'Step 1 of 3', title: 'Shop Details',         sub: "Set up your shop's public identity" },
    3: { eyebrow: 'Step 2 of 3', title: 'Business Information', sub: 'Help us verify your business and address' },
    4: { eyebrow: 'Step 3 of 3', title: 'Bank Details',         sub: 'Almost there — where should we send your payouts?' }
};

var currentStep = 2;

function goToStep(target, skipValidation) {
    if (!skipValidation && target > currentStep) {
        if (!validateStep(currentStep)) return;
    }

    document.getElementById('step' + currentStep).classList.remove('active');
    document.getElementById('step' + target).classList.add('active');

    document.getElementById('stepEyebrow').textContent = STEPS[target].eyebrow;
    document.getElementById('stepTitle').textContent   = STEPS[target].title;
    document.getElementById('stepSub').textContent     = STEPS[target].sub;

    document.querySelectorAll('#panelSteps .panel-step').forEach(function(el) {
        var s = parseInt(el.getAttribute('data-step'));
        el.classList.remove('active', 'done');
        if (s === target)     el.classList.add('active');
        else if (s < target)  el.classList.add('done');
    });

    // Mobile dots (2=dot1, 3=dot2, 4=dot3)
    document.querySelectorAll('#mobileDots .mobile-step-dot').forEach(function(el) {
        var d = parseInt(el.getAttribute('data-dot'));
        el.classList.remove('active', 'done');
        if (d === target)    el.classList.add('active');
        else if (d < target) el.classList.add('done');
    });

    currentStep = target;
    document.querySelector('.auth-form-side').scrollTo({ top: 0, behavior: 'smooth' });
}

function validateStep(step) {
    var panel  = document.getElementById('step' + step);
    var fields = panel.querySelectorAll('input[required], select[required], textarea[required]');
    var valid  = true;

    fields.forEach(function(field) {
        field.classList.remove('is-invalid');
        if (field.type === 'radio') return;
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
            if (!field.value.trim()) { field.classList.add('is-invalid'); valid = false; }
        }
    });

    var radioGroups = {};
    panel.querySelectorAll('input[type="radio"][required]').forEach(function(r) {
        radioGroups[r.name] = radioGroups[r.name] || [];
        radioGroups[r.name].push(r);
    });
    Object.keys(radioGroups).forEach(function(name) {
    var bc = document.getElementById('bizCards');
    if (!radioGroups[name].some(function(r) { return r.checked; })) {
        valid = false;
        if (bc) { bc.style.outline = '2px solid #e53e3e'; bc.style.borderRadius = '10px'; }
    } else {
        if (bc) { bc.style.outline = ''; }
    }
});

    if (!valid) {
        var firstErr = panel.querySelector('.is-invalid');
        if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return valid;
}

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

document.querySelectorAll('.biz-card').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.biz-card').forEach(function(c) { c.classList.remove('selected'); });
        this.classList.add('selected');
        this.querySelector('input[type="radio"]').checked = true;
        var bc = document.getElementById('bizCards');
        if (bc) bc.style.outline = '';
    });
});

function handleUpload(input, zoneId, previewId, filenameId) {
    var zone = document.getElementById(zoneId);
    var preview = document.getElementById(previewId);
    var filename = document.getElementById(filenameId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.style.display = 'block';
            filename.textContent = input.files[0].name;
            filename.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
        zone.classList.add('has-file');
        zone.style.borderColor = '';
    }
}

// On server validation error, jump to the correct step
@if($errors->any())
    @php
        $errorFields = array_keys($errors->toArray());
        $step2Fields = ['shop_name','shop_description','shop_logo','banner','website','phone_number'];
        $step3Fields = ['business_type','tax_id','address','city','state','postal_code','country','delivery_zone'];
        $jumpTo = 4;
        foreach($errorFields as $field) {
            if(in_array($field, $step2Fields)) { $jumpTo = 2; break; }
            if(in_array($field, $step3Fields)) { $jumpTo = 3; break; }
        }
    @endphp
    goToStep({{ $jumpTo }}, true);
@endif
</script>
</body>
</html>
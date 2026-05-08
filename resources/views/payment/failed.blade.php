@extends('layouts.app')
@section('title', 'Payment Failed - ' . config('app.name'))

@section('content')
<style>
/* ═══════════════════════════════════════════════════
   GH PAYMENT RESULT — Shared styles (success + fail)
   Consistent with account page aesthetics
═══════════════════════════════════════════════════ */
.gh-result-section { padding: 56px 0 72px; min-height: calc(100vh - 220px); }

/* Anim */
.gh-result-anim { text-align: center; margin-bottom: 32px; }
.gh-check-wrap  { width: 96px; height: 96px; margin: 0 auto 18px; }
.gh-crossmark { width:96px; height:96px; border-radius:50%; stroke-width:3; stroke:#dc2626; stroke-miterlimit:10; box-shadow:inset 0 0 0 #dc2626; animation:ghFillRed .4s ease-in-out .4s forwards, ghScale .3s ease-in-out .9s both; }
.gh-cross-circle { stroke-dasharray:166; stroke-dashoffset:166; stroke-width:3; stroke:#dc2626; fill:#fee2e2; animation:ghStroke .6s cubic-bezier(.65,0,.45,1) forwards; }
.gh-cross-x { transform-origin:50% 50%; stroke-dasharray:60; stroke-dashoffset:60; stroke:#fff; stroke-width:3; animation:ghStroke .4s cubic-bezier(.65,0,.45,1) .8s forwards; }
@keyframes ghStroke  { 100%{ stroke-dashoffset:0; } }
@keyframes ghFillRed { 100%{ box-shadow:inset 0 0 0 58px #dc2626; } }
@keyframes ghScale   { 0%,100%{transform:none} 50%{transform:scale3d(1.1,1.1,1)} }
.gh-result-headline { font-size: 28px; font-weight: 800; color: #1a1209; margin: 0 0 8px; }
.gh-result-sub { font-size: 15px; color: #7a6655; margin: 0; }

/* Error alert */
.gh-error-alert {
  display: flex; align-items: center; gap: 12px;
  background: #fee2e2; border: 1px solid #fecaca;
  border-radius: 10px; padding: 14px 18px;
  margin-bottom: 16px; font-size: 13.5px; font-weight: 600; color: #dc2626;
}
.gh-error-alert-icon {
  width: 30px; height: 30px; border-radius: 8px;
  background: #dc2626; color: #fff; display: flex; align-items: center; justify-content: center;
  font-size: 13px; flex-shrink: 0;
}

/* Card */
.gh-card {
  background: #fff; border-radius: 14px;
  border: 1px solid #f0ebe5; overflow: hidden;
  box-shadow: 0 4px 24px rgba(113,78,50,.07);
  margin-bottom: 20px;
}
.gh-card-header {
  display: flex; align-items: center; justify-content: space-between;
  padding: 18px 24px; border-bottom: 1px solid #f5f0eb; background: #fdf8f4;
}
.gh-card-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-bottom: 4px; }
.gh-card-order-num { font-size: 20px; font-weight: 800; color: #714e32; }

/* Status badges */
.gh-status {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 5px 13px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .3px;
}
.gh-status-cancelled { background: #fee2e2; color: #dc2626; }

/* Sections */
.gh-card-section { padding: 20px 24px; border-bottom: 1px solid #f5f0eb; }
.gh-section-title { font-size: 13px; font-weight: 700; color: #1a1209; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }

/* Reasons */
.gh-reason-list { display: flex; flex-direction: column; gap: 0; }
.gh-reason-item { display: flex; align-items: center; gap: 13px; padding: 10px 0; border-bottom: 1px solid #f9f5f1; font-size: 13.5px; color: #4b5563; }
.gh-reason-item:last-child { border-bottom: none; }
.gh-reason-icon-wrap { font-size: 18px; width: 34px; height: 34px; border-radius: 8px; background: #fdf8f4; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

/* Tip box */
.gh-tip-box { display: flex; gap: 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 16px; }
.gh-tip-icon { font-size: 22px; flex-shrink: 0; line-height: 1.3; }
.gh-tip-box strong { display: block; font-size: 14px; font-weight: 700; color: #92400e; margin-bottom: 4px; }
.gh-tip-box p { font-size: 13px; color: #78350f; margin: 0; }

/* Support grid */
.gh-support-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
.gh-support-btn {
  display: flex; flex-direction: column; align-items: center; gap: 7px;
  padding: 16px 10px; background: #fdf8f4; border: 1.5px solid #f0ebe5;
  border-radius: 10px; font-size: 12px; font-weight: 700; color: #714e32;
  text-decoration: none; transition: all .18s;
}
.gh-support-btn i { font-size: 20px; }
.gh-support-btn:hover { background: #714e32; color: #fff; border-color: #714e32; }

/* Actions */
.gh-result-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; }
.gh-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 12px 22px; border-radius: 9px;
  font-size: 14px; font-weight: 600; font-family: inherit;
  cursor: pointer; border: none; transition: all .18s; text-decoration: none; white-space: nowrap; flex: 1; justify-content: center;
}
.gh-btn-primary { background: #714e32; color: #fff; }
.gh-btn-primary:hover { background: #5a3c24; color: #fff; }
.gh-btn-outline { background: transparent; color: #714e32; border: 2px solid #c4956a; }
.gh-btn-outline:hover { background: #714e32; color: #fff; }
.gh-security-note { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280; justify-content: center; padding: 8px 0; }

@media (max-width: 767px) {
  .gh-result-headline { font-size: 22px; }
  .gh-support-grid { grid-template-columns: 1fr; }
  .gh-result-actions { flex-direction: column; }
}
</style>
<div class="wrapper ovh">
  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

  <div class="body_content_wrapper position-relative">
    <section class="gh-result-section gh-result-failed-bg">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-7 col-xl-6">

            {{-- Animated X --}}
            <div class="gh-result-anim">
              <div class="gh-check-wrap">
                <svg class="gh-crossmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                  <circle class="gh-cross-circle" cx="26" cy="26" r="25" fill="none"/>
                  <path class="gh-cross-x" fill="none" d="M16 16 36 36 M36 16 16 36"/>
                </svg>
              </div>
              <h1 class="gh-result-headline">Payment Failed</h1>
              <p class="gh-result-sub">We couldn't process your payment</p>
            </div>

            {{-- Error alert --}}
            @if(session('error'))
            <div class="gh-error-alert">
              <div class="gh-error-alert-icon"><i class="fas fa-times-circle"></i></div>
              <span>{{ session('error') }}</span>
            </div>
            @endif

            {{-- Failure card --}}
            <div class="gh-card">
              <div class="gh-card-header">
                <div>
                  <div class="gh-card-label">Payment Status</div>
                  <div class="gh-card-order-num" style="color:#dc2626;">Transaction Declined</div>
                </div>
                <span class="gh-status gh-status-cancelled">
                  <i class="fas fa-times" style="font-size:9px;"></i> Failed
                </span>
              </div>

              <div class="gh-card-section">
                <h5 class="gh-section-title">
                  <i class="fas fa-question-circle" style="color:#c4956a;"></i> Common reasons for failure
                </h5>
                <div class="gh-reason-list">
                  <div class="gh-reason-item">
                    <div class="gh-reason-icon-wrap gh-reason-card">💳</div>
                    <div class="gh-reason-text">Insufficient funds or card limit reached</div>
                  </div>
                  <div class="gh-reason-item">
                    <div class="gh-reason-icon-wrap gh-reason-decline">🚫</div>
                    <div class="gh-reason-text">Card declined by your bank</div>
                  </div>
                  <div class="gh-reason-item">
                    <div class="gh-reason-icon-wrap gh-reason-details">🔢</div>
                    <div class="gh-reason-text">Incorrect card details or expired card</div>
                  </div>
                  <div class="gh-reason-item">
                    <div class="gh-reason-icon-wrap gh-reason-network">📶</div>
                    <div class="gh-reason-text">Network or connectivity issues</div>
                  </div>
                </div>
              </div>

              <div class="gh-card-section">
                <div class="gh-tip-box">
                  <div class="gh-tip-icon">💡</div>
                  <div>
                    <strong>Your cart is still saved.</strong>
                    <p>Try a different card, ensure you have sufficient funds, or contact your bank if the issue persists.</p>
                  </div>
                </div>
              </div>

              <div class="gh-card-section" style="border-bottom:none;">
                <div class="gh-result-actions" style="margin-bottom:0;">
                  <a href="{{ route('cart.index') }}" class="gh-btn gh-btn-primary">
                    <i class="fas fa-redo"></i> Try Again
                  </a>
                  <a href="{{ route('product.index') }}" class="gh-btn gh-btn-outline">
                    Continue Shopping
                  </a>
                </div>
              </div>
            </div>

            {{-- Support card --}}
            <div class="gh-card">
              <div class="gh-card-section" style="border-bottom:none;">
                <h5 class="gh-section-title">
                  <i class="fas fa-headset" style="color:#c4956a;"></i> Need Help?
                </h5>
                <p style="font-size:13px;color:#7a6655;margin-bottom:16px;">Our support team is here to help you resolve payment issues.</p>
                <div class="gh-support-grid">
                  <a href="{{ route('contact') }}" class="gh-support-btn">
                    <i class="fas fa-envelope"></i>
                    <span>Email Us</span>
                  </a>
                  <a href="tel:+234XXXXXXXXX" class="gh-support-btn">
                    <i class="fas fa-phone"></i>
                    <span>Call Us</span>
                  </a>
                  <a href="#" class="gh-support-btn" onclick="if(window.$crisp){window.$crisp.push(['do','chat:open']);}return false;">
                    <i class="fas fa-comment"></i>
                    <span>Live Chat</span>
                  </a>
                </div>
              </div>
            </div>

            <div class="gh-security-note">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              Your payment information is secure and encrypted. We never store card details.
            </div>

          </div>
        </div>
      </div>
    </section>

    @include('partials.footer')
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>



@endsection
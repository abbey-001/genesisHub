{{-- resources/views/account/security.blade.php --}}
@extends('layouts.app')

@section('title', 'Account Security')

@section('content')
<div class="wrapper ovh bgc-gmart-gray">
    @php
        $categoriesWithSubs = App\Models\Category::select('id', 'name', 'slug', 'image')
            ->with(['subcategories' => fn($q) => $q->select('id', 'category_id', 'name', 'slug')->orderBy('sort_order')->limit(10)])
            ->limit(10)->get();
    @endphp
    @include('partials.header')
    @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

    <div class="body_content_wrapper position-relative">

        {{-- Breadcrumb --}}
        <section class="breadcumb-section pt30 pb30">
            <div class="container">
                <div class="breadcumb-style1">
                    <div class="breadcumb-list">
                        <a href="{{ route('home') }}">Home</a>
                        <a href="{{ route('account.index') }}">Account</a>
                        <a href="#">Security</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb90 pt10">
            <div class="container">
                <div class="row">

                    {{-- ── Sidebar ─────────────────────────────────────────── --}}
                    <div class="col-lg-3 col-xl-2">
                        <div class="dash_sidebar" style="background:#fff; border-radius:14px; padding:20px; box-shadow:0 2px 16px rgba(0,0,0,.06);">
                            <ul style="list-style:none; padding:0; margin:0;">
                                @php
                                    $navItems = [
                                        ['route' => 'account.index',    'icon' => 'fa-user',         'label' => 'Profile'],
                                        ['route' => 'account.orders',   'icon' => 'fa-box',          'label' => 'My Orders'],
                                        ['route' => 'account.security', 'icon' => 'fa-shield-halved','label' => 'Security'],
                                    ];
                                @endphp
                                @foreach($navItems as $item)
                                <li style="margin-bottom:4px;">
                                    <a href="{{ route($item['route']) }}"
                                       style="display:flex; align-items:center; gap:10px; padding:10px 12px;
                                              border-radius:8px; font-size:13.5px; font-weight:600; text-decoration:none;
                                              color: {{ request()->routeIs($item['route']) ? '#714e32' : '#555e68' }};
                                              background: {{ request()->routeIs($item['route']) ? 'rgba(113,78,50,.08)' : 'transparent' }};">
                                        <i class="fa-solid {{ $item['icon'] }}" style="font-size:14px; width:16px;"></i>
                                        {{ $item['label'] }}
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    {{-- ── Main content ────────────────────────────────────── --}}
                    <div class="col-lg-9 col-xl-10">

                        {{-- Flash messages --}}
                        @if(session('security_success'))
                        <div style="display:flex; gap:12px; align-items:flex-start; background:#f0fdf4; border:1px solid #86efac; border-radius:12px; padding:14px 16px; margin-bottom:24px;">
                            <div style="width:32px; height:32px; background:rgba(34,197,94,.1); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                <i class="fa-solid fa-circle-check" style="color:#15803d; font-size:13px;"></i>
                            </div>
                            <div>
                                <p style="font-size:12.5px; font-weight:700; color:#14532d; margin-bottom:2px;">Done!</p>
                                <p style="font-size:12px; color:#166534; margin:0;">{{ session('security_success') }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- ════════════════════════════════════════════════════
                             SECTION 1 — Active Sessions
                        ════════════════════════════════════════════════════ --}}
                        <div class="sec-card" style="background:#fff; border-radius:16px; padding:28px 32px; box-shadow:0 2px 16px rgba(0,0,0,.06); margin-bottom:24px;">
                            <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:22px;">
                                <div>
                                    <h5 style="font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:3px;">Active Sessions</h5>
                                    <p style="font-size:13px; color:#8a94a6; margin:0;">
                                        You currently have <strong>{{ $sessionCount }}</strong> active {{ Str::plural('session', $sessionCount) }}.
                                    </p>
                                </div>
                                <div style="display:flex; align-items:center; gap:8px; background:rgba(113,78,50,.07); border-radius:10px; padding:8px 14px;">
                                    <i class="fa-solid fa-desktop" style="color:#714e32; font-size:14px;"></i>
                                    <span style="font-size:13px; font-weight:600; color:#714e32;">{{ $sessionCount }} {{ Str::plural('device', $sessionCount) }}</span>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('account.sessions.revoke') }}">
                                @csrf
                                <div style="margin-bottom:16px;">
                                    <label style="display:block; font-size:13px; font-weight:600; color:#2d2d2d; margin-bottom:7px;">
                                        Confirm your password to revoke all other sessions
                                    </label>
                                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                        <input type="password" name="password"
                                               style="flex:1; min-width:200px; height:46px; padding:0 14px; border:1.5px solid #e0e4ea; border-radius:10px; font-size:14px; font-family:inherit; outline:none; transition:border-color .2s;"
                                               placeholder="Your current password"
                                               onfocus="this.style.borderColor='#714e32'" onblur="this.style.borderColor='#e0e4ea'">
                                        <button type="submit"
                                                style="height:46px; padding:0 20px; background:#714e32; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer; white-space:nowrap; transition:background .2s;"
                                                onmouseover="this.style.background='#5a3d26'" onmouseout="this.style.background='#714e32'">
                                            <i class="fa-solid fa-right-from-bracket" style="margin-right:6px;"></i>Revoke Other Sessions
                                        </button>
                                    </div>
                                    @error('password')
                                    <p style="font-size:12px; color:#e53e3e; margin-top:6px;">{{ $message }}</p>
                                    @enderror
                                </div>
                            </form>

                            @if($user->isSocialOnly())
                            <p style="font-size:12.5px; color:#8a94a6; background:#f9fafb; border-radius:8px; padding:10px 12px; margin:0;">
                                <i class="fa-solid fa-circle-info" style="margin-right:5px;"></i>
                                Social login accounts don't use passwords for session management. Revoking other sessions is not available for your account type.
                            </p>
                            @endif
                        </div>

                        {{-- ════════════════════════════════════════════════════
                             SECTION 2 — Login Activity
                        ════════════════════════════════════════════════════ --}}
                        <div class="sec-card" style="background:#fff; border-radius:16px; padding:28px 32px; box-shadow:0 2px 16px rgba(0,0,0,.06); margin-bottom:24px;">
                            <h5 style="font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:4px;">Login Activity</h5>
                            <p style="font-size:13px; color:#8a94a6; margin-bottom:22px;">Your last 10 sign-in attempts.</p>

                            @if($loginActivities->isEmpty())
                            <p style="font-size:13.5px; color:#8a94a6; text-align:center; padding:32px 0;">
                                <i class="fa-solid fa-clock-rotate-left" style="font-size:24px; display:block; margin-bottom:10px; opacity:.4;"></i>
                                No login activity recorded yet.
                            </p>
                            @else
                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse:collapse; font-size:13px;">
                                    <thead>
                                        <tr style="border-bottom:2px solid #f0f1f3;">
                                            <th style="text-align:left; padding:8px 12px; font-size:11.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#8a94a6;">Date & Time</th>
                                            <th style="text-align:left; padding:8px 12px; font-size:11.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#8a94a6;">Device</th>
                                            <th style="text-align:left; padding:8px 12px; font-size:11.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#8a94a6;">IP Address</th>
                                            <th style="text-align:left; padding:8px 12px; font-size:11.5px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#8a94a6;">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($loginActivities as $activity)
                                        <tr style="border-bottom:1px solid #f8f9fa;">
                                            <td style="padding:12px; color:#1a1a1a; font-weight:500;">
                                                {{ $activity->logged_in_at->format('d M Y') }}
                                                <span style="display:block; font-size:11.5px; color:#8a94a6;">{{ $activity->logged_in_at->format('g:ia') }}</span>
                                            </td>
                                            <td style="padding:12px; color:#555e68;">{{ $activity->device ?? 'Unknown device' }}</td>
                                            <td style="padding:12px; color:#555e68; font-family:monospace; font-size:12.5px;">{{ $activity->ip_address ?? '—' }}</td>
                                            <td style="padding:12px;">
                                                @if($activity->successful)
                                                <span style="display:inline-flex; align-items:center; gap:5px; background:#dcfce7; color:#15803d; font-size:12px; font-weight:700; padding:3px 10px; border-radius:100px;">
                                                    <i class="fa-solid fa-check" style="font-size:9px;"></i> Success
                                                </span>
                                                @else
                                                <span style="display:inline-flex; align-items:center; gap:5px; background:#fee2e2; color:#dc2626; font-size:12px; font-weight:700; padding:3px 10px; border-radius:100px;"
                                                      title="{{ $activity->failure_reason ? str_replace('_', ' ', ucfirst($activity->failure_reason)) : 'Failed' }}">
                                                    <i class="fa-solid fa-xmark" style="font-size:9px;"></i> Failed
                                                </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>

                        {{-- ════════════════════════════════════════════════════
                             SECTION 3 — Email Address
                        ════════════════════════════════════════════════════ --}}
                        <div class="sec-card" style="background:#fff; border-radius:16px; padding:28px 32px; box-shadow:0 2px 16px rgba(0,0,0,.06); margin-bottom:24px;">
                            <h5 style="font-size:16px; font-weight:700; color:#1a1a1a; margin-bottom:4px;">Email Address</h5>
                            <p style="font-size:13px; color:#8a94a6; margin-bottom:22px;">
                                Your current email is <strong style="color:#1a1a1a;">{{ $user->email }}</strong>.
                                A confirmation link will be sent to the new address before the change takes effect.
                            </p>

                            {{-- Pending change notice --}}
                            @if($pendingEmailChange)
                            <div style="display:flex; gap:12px; align-items:flex-start; background:#fdf8ec; border:1px solid #f0d98a; border-radius:12px; padding:14px 16px; margin-bottom:20px;">
                                <div style="width:32px; height:32px; background:rgba(245,195,75,.18); border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                                    <i class="fa-solid fa-clock" style="color:#b87d00; font-size:13px;"></i>
                                </div>
                                <div style="flex:1;">
                                    <p style="font-size:12.5px; font-weight:700; color:#7a5700; margin-bottom:3px;">Email change pending</p>
                                    <p style="font-size:12px; color:#886200; margin-bottom:8px;">
                                        A confirmation link was sent to <strong>{{ $pendingEmailChange->new_email }}</strong>.
                                        Expires {{ $pendingEmailChange->expires_at->diffForHumans() }}.
                                    </p>
                                    <form method="POST" action="{{ route('account.email.cancel') }}" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:none; border:none; font-size:12px; font-weight:700; color:#b87d00; text-decoration:underline; cursor:pointer; padding:0;">
                                            Cancel this request
                                        </button>
                                    </form>
                                </div>
                            </div>
                            @endif

                            @if($user->isSocialOnly())
                            <p style="font-size:13px; color:#8a94a6; background:#f9fafb; border-radius:8px; padding:12px 14px; margin:0;">
                                <i class="fa-solid fa-circle-info" style="margin-right:5px;"></i>
                                Your account uses social login. Email changes are managed through your Google or Facebook account.
                            </p>
                            @else
                            <form method="POST" action="{{ route('account.email.change') }}">
                                @csrf
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                                    <div>
                                        <label style="display:block; font-size:13px; font-weight:600; color:#2d2d2d; margin-bottom:7px;">New Email Address</label>
                                        <input type="email" name="new_email"
                                               value="{{ old('new_email') }}"
                                               style="width:100%; height:46px; padding:0 14px; border:1.5px solid {{ $errors->has('new_email') ? '#e53e3e' : '#e0e4ea' }}; border-radius:10px; font-size:14px; font-family:inherit; outline:none; transition:border-color .2s;"
                                               placeholder="new@example.com"
                                               onfocus="this.style.borderColor='#714e32'" onblur="this.style.borderColor='#e0e4ea'">
                                        @error('new_email')<p style="font-size:12px; color:#e53e3e; margin-top:5px;">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label style="display:block; font-size:13px; font-weight:600; color:#2d2d2d; margin-bottom:7px;">Current Password</label>
                                        <input type="password" name="current_password"
                                               style="width:100%; height:46px; padding:0 14px; border:1.5px solid {{ $errors->has('current_password') ? '#e53e3e' : '#e0e4ea' }}; border-radius:10px; font-size:14px; font-family:inherit; outline:none; transition:border-color .2s;"
                                               placeholder="Confirm with your password"
                                               onfocus="this.style.borderColor='#714e32'" onblur="this.style.borderColor='#e0e4ea'">
                                        @error('current_password')<p style="font-size:12px; color:#e53e3e; margin-top:5px;">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                                <button type="submit"
                                        style="height:46px; padding:0 20px; background:#714e32; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer; transition:background .2s;"
                                        onmouseover="this.style.background='#5a3d26'" onmouseout="this.style.background='#714e32'">
                                    <i class="fa-solid fa-envelope" style="margin-right:6px;"></i>Send Confirmation Link
                                </button>
                            </form>
                            @endif
                        </div>

                        {{-- ════════════════════════════════════════════════════
                             SECTION 4 — Danger Zone (Deactivate)
                        ════════════════════════════════════════════════════ --}}
                        <div class="sec-card" style="background:#fff; border-radius:16px; padding:28px 32px; box-shadow:0 2px 16px rgba(0,0,0,.06); border-top:3px solid #fee2e2;">
                            <h5 style="font-size:16px; font-weight:700; color:#dc2626; margin-bottom:4px;">
                                <i class="fa-solid fa-triangle-exclamation" style="margin-right:6px;"></i>Danger Zone
                            </h5>
                            <p style="font-size:13px; color:#8a94a6; margin-bottom:22px;">
                                Deactivating your account will sign you out immediately. You have <strong>30 days</strong> to reactivate it by logging back in.
                                After 30 days, the account will be permanently closed.
                            </p>

                            <button type="button" onclick="document.getElementById('deactivate-modal').style.display='flex'"
                                    style="height:46px; padding:0 20px; background:#fff; color:#dc2626; border:1.5px solid #fca5a5; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer; transition:all .2s;"
                                    onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff'">
                                <i class="fa-solid fa-user-slash" style="margin-right:6px;"></i>Deactivate My Account
                            </button>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        {{-- ══ DEACTIVATE MODAL ════════════════════════════════════════════════ --}}
        <div id="deactivate-modal"
             style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:9999; align-items:center; justify-content:center; padding:20px;">
            <div style="background:#fff; border-radius:20px; padding:36px; max-width:460px; width:100%; box-shadow:0 24px 80px rgba(0,0,0,.2);">

                <div style="text-align:center; margin-bottom:24px;">
                    <div style="width:60px; height:60px; background:#fee2e2; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px;">
                        <i class="fa-solid fa-user-slash" style="font-size:22px; color:#dc2626;"></i>
                    </div>
                    <h5 style="font-size:18px; font-weight:700; color:#1a1a1a; margin-bottom:6px;">Deactivate Account?</h5>
                    <p style="font-size:13.5px; color:#555e68; line-height:1.6; margin:0;">
                        You'll be signed out immediately. Log back in within <strong>30 days</strong> to reactivate.
                        After that, the account will be permanently closed.
                    </p>
                </div>

                <form method="POST" action="{{ route('account.deactivate') }}">
                    @csrf

                    @unless($user->isSocialOnly())
                    <div style="margin-bottom:16px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#2d2d2d; margin-bottom:7px;">Current Password</label>
                        <input type="password" name="password"
                               style="width:100%; height:46px; padding:0 14px; border:1.5px solid #e0e4ea; border-radius:10px; font-size:14px; font-family:inherit; outline:none;"
                               placeholder="Confirm with your password">
                        @error('password')<p style="font-size:12px; color:#e53e3e; margin-top:5px;">{{ $message }}</p>@enderror
                    </div>
                    @endunless

                    <div style="margin-bottom:20px;">
                        <label style="display:block; font-size:13px; font-weight:600; color:#2d2d2d; margin-bottom:7px;">
                            Type <strong>DEACTIVATE</strong> to confirm
                        </label>
                        <input type="text" name="confirm_deactivate"
                               style="width:100%; height:46px; padding:0 14px; border:1.5px solid #e0e4ea; border-radius:10px; font-size:14px; font-family:inherit; outline:none; letter-spacing:.05em;"
                               placeholder="DEACTIVATE" autocomplete="off">
                        @error('confirm_deactivate')<p style="font-size:12px; color:#e53e3e; margin-top:5px;">{{ $message }}</p>@enderror
                    </div>

                    <div style="display:flex; gap:10px;">
                        <button type="button" onclick="document.getElementById('deactivate-modal').style.display='none'"
                                style="flex:1; height:46px; background:#f3f4f6; color:#555e68; border:none; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer;">
                            Cancel
                        </button>
                        <button type="submit"
                                style="flex:1; height:46px; background:#dc2626; color:#fff; border:none; border-radius:10px; font-size:13.5px; font-weight:600; cursor:pointer;">
                            Yes, Deactivate
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @include('partials.footer')
        <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Close modal on backdrop click
document.getElementById('deactivate-modal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});

// Auto-open modal if there were deactivation validation errors
@if($errors->has('confirm_deactivate') || $errors->has('password') && old('confirm_deactivate'))
document.getElementById('deactivate-modal').style.display = 'flex';
@endif
</script>
@endpush
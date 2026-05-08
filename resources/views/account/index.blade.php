@extends('layouts.app')

@section('title', 'My Account - ' . config('app.name'))

@section('content')
<style>
/* ════════════════════════════════════════════════════════
   ACCOUNT PAGE — Scoped styles prefixed with .acct-page
════════════════════════════════════════════════════════ */

/* ── Page shell ───────────────────────────────────────── */
.acct-page { background: #f5f0eb; min-height: 100vh; }

/* ── Page header strip ───────────────────────────────── */
.acct-page-header {
  background: #fff;
  border-bottom: 1px solid #f0ebe5;
  padding: 20px 0 16px;
  margin-bottom: 28px;
}
.acct-page-header .acct-header-inner {
  display: flex; align-items: center; gap: 14px;
}
.acct-page-header .acct-header-avatar {
  width: 46px; height: 46px; border-radius: 50%;
  border: 2px solid #f0ebe5; flex-shrink: 0;
  object-fit: cover;
}
.acct-page-header h1 {
  font-size: 20px; font-weight: 700; color: #1a1209; margin: 0 0 2px;
}
.acct-page-header p { font-size: 13px; color: #7a6655; margin: 0; }

/* ── Layout grid ─────────────────────────────────────── */
.acct-layout {
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 24px;
  align-items: start;
}
@media (max-width: 991px) { .acct-layout { grid-template-columns: 1fr; } }

/* ── Sidebar ─────────────────────────────────────────── */
.acct-sidebar {
  background: #fff;
  border: 1px solid #f0ebe5;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(113,78,50,.06);
  position: sticky; top: 80px;
}

.acct-sidebar-user {
  padding: 22px 20px 18px;
  background: linear-gradient(135deg, #fdf8f4, #f5ede5);
  border-bottom: 1px solid #f0ebe5;
  display: flex; align-items: center; gap: 13px;
}
.acct-sidebar-user img {
  width: 50px; height: 50px; border-radius: 50%;
  border: 2.5px solid #fff;
  box-shadow: 0 2px 8px rgba(113,78,50,.18);
  object-fit: cover; flex-shrink: 0;
}
.acct-sidebar-user .acct-user-name {
  font-size: 14px; font-weight: 700; color: #1a1209;
  margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.acct-sidebar-user .acct-user-email {
  font-size: 11px; color: #7a6655; text-decoration: none;
  display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.acct-sidebar-nav { padding: 10px 0; }
.acct-sidebar-nav a,
.acct-sidebar-nav button.acct-nav-item {
  display: flex; align-items: center; gap: 11px;
  padding: 11px 20px;
  font-size: 14px; font-weight: 500; color: #4a3728;
  text-decoration: none; width: 100%; text-align: left;
  background: none; border: none; cursor: pointer;
  font-family: inherit; transition: all .18s;
  position: relative;
}
.acct-sidebar-nav a:hover,
.acct-sidebar-nav button.acct-nav-item:hover {
  background: #fdf8f4; color: #714e32;
}
.acct-sidebar-nav a.active,
.acct-sidebar-nav button.acct-nav-item.active {
  background: #fdf1e8; color: #714e32; font-weight: 700;
}
.acct-sidebar-nav a.active::before,
.acct-sidebar-nav button.acct-nav-item.active::before {
  content: '';
  position: absolute; left: 0; top: 6px; bottom: 6px;
  width: 3px; background: #714e32; border-radius: 0 3px 3px 0;
}
.acct-sidebar-nav .acct-nav-icon {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  background: #f5ede5; color: #714e32; font-size: 13px; flex-shrink: 0;
  transition: background .18s;
}
.acct-sidebar-nav a.active .acct-nav-icon,
.acct-sidebar-nav button.acct-nav-item.active .acct-nav-icon {
  background: linear-gradient(135deg, #714e32, #c4956a);
  color: #fff;
}
.acct-sidebar-nav .acct-nav-divider {
  height: 1px; background: #f5f0eb; margin: 6px 16px;
}
.acct-sidebar-nav a.acct-nav-logout { color: #dc2626; }
.acct-sidebar-nav a.acct-nav-logout .acct-nav-icon { background: #fef2f2; color: #dc2626; }
.acct-sidebar-nav a.acct-nav-logout:hover { background: #fef2f2; }

/* ── Main content area ───────────────────────────────── */
.acct-main { min-width: 0; }

/* ── Panel card ──────────────────────────────────────── */
.acct-panel {
  background: #fff;
  border: 1px solid #f0ebe5;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 2px 16px rgba(113,78,50,.05);
  margin-bottom: 20px;
}
.acct-panel-header {
  padding: 18px 24px 16px;
  border-bottom: 1px solid #f5f0eb;
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
}
.acct-panel-title { display: flex; align-items: center; gap: 10px; }
.acct-panel-title-icon {
  width: 34px; height: 34px;
  background: linear-gradient(135deg, #f5ede5, #e8d5c4);
  border-radius: 9px; display: flex; align-items: center; justify-content: center;
  color: #714e32; font-size: 14px;
}
.acct-panel-title h3 { font-size: 16px; font-weight: 700; color: #1a1209; margin: 0; }
.acct-panel-body { padding: 24px; }
.acct-panel-body.p0 { padding: 0; }

/* ── Tabs (profile / password) ───────────────────────── */
.acct-tabs-nav {
  display: flex; gap: 4px;
  background: #f5f0eb; border-radius: 10px;
  padding: 4px; width: fit-content; margin-bottom: 24px;
}
.acct-tabs-nav button {
  padding: 8px 18px; border: none; cursor: pointer;
  border-radius: 7px; font-size: 13px; font-weight: 600;
  font-family: inherit; transition: all .18s;
  background: transparent; color: #7a6655;
}
.acct-tabs-nav button.active {
  background: #fff; color: #714e32;
  box-shadow: 0 2px 8px rgba(113,78,50,.12);
}

/* ── Form fields ─────────────────────────────────────── */
.acct-form-group { margin-bottom: 20px; }
.acct-form-group label { display: block; font-size: 13px; font-weight: 600; color: #4a3728; margin-bottom: 7px; }
.acct-input {
  width: 100%; padding: 10px 14px;
  border: 1.5px solid #e8ddd5; border-radius: 9px;
  font-size: 14px; color: #1a1209; font-family: inherit;
  background: #fdf9f7; transition: border .18s, box-shadow .18s; outline: none;
}
.acct-input:focus {
  border-color: #c4956a;
  box-shadow: 0 0 0 3px rgba(196,149,106,.12);
  background: #fff;
}
select.acct-input { appearance: none; cursor: pointer; }
.acct-input-hint { font-size: 12px; color: #9ca3af; margin-top: 5px; }
.error-text { font-size: 12px; color: #dc2626; margin-top: 4px; display: block; }

/* ── Buttons ─────────────────────────────────────────── */
.acct-btn {
  display: inline-flex; align-items: center; gap: 7px;
  padding: 10px 22px; border-radius: 9px;
  font-size: 14px; font-weight: 600; font-family: inherit;
  cursor: pointer; border: none; transition: all .18s;
  text-decoration: none; white-space: nowrap;
}
.acct-btn-primary { background: #714e32; color: #fff; }
.acct-btn-primary:hover { background: #5a3c24; color: #fff; }
.acct-btn-secondary { background: #f5f0eb; color: #4a3728; border: 1.5px solid #e8ddd5; }
.acct-btn-secondary:hover { background: #ede6dd; }
.acct-btn-danger { background: #fef2f2; color: #dc2626; border: 1.5px solid #fecaca; }
.acct-btn-danger:hover { background: #dc2626; color: #fff; }
.acct-btn-outline { background: transparent; color: #714e32; border: 1.5px solid #c4956a; }
.acct-btn-outline:hover { background: #714e32; color: #fff; }
.acct-btn-sm { padding: 7px 14px; font-size: 12px; }

/* ── Stats bar ───────────────────────────────────────── */
.acct-stats-row { display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
.acct-stat-card {
  flex: 1; min-width: 130px;
  background: #fff; border: 1px solid #f0ebe5; border-radius: 12px;
  padding: 16px 18px; box-shadow: 0 2px 8px rgba(113,78,50,.05);
  display: flex; align-items: center; gap: 13px;
}
.acct-stat-icon {
  width: 40px; height: 40px; border-radius: 10px;
  background: linear-gradient(135deg, #f5ede5, #e8d5c4);
  display: flex; align-items: center; justify-content: center;
  color: #714e32; font-size: 16px; flex-shrink: 0;
}
.acct-stat-label { font-size: 11px; color: #7a6655; margin: 0 0 2px; }
.acct-stat-value { font-size: 22px; font-weight: 700; color: #1a1209; }

/* ── Orders table ────────────────────────────────────── */
.acct-table-wrap { overflow-x: auto; }
.acct-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.acct-table thead th {
  padding: 12px 16px; background: #fdf8f4;
  color: #4a3728; font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .4px;
  border-bottom: 1px solid #f0ebe5; white-space: nowrap;
}
.acct-table tbody td {
  padding: 14px 16px; border-bottom: 1px solid #f5f0eb;
  color: #1a1209; vertical-align: middle;
}
.acct-table tbody tr:last-child td { border-bottom: none; }
.acct-table tbody tr:hover td { background: #fdfaf7; }

/* Order status badges */
.acct-status {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px; border-radius: 20px;
  font-size: 11px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .3px;
}
.acct-status-pending    { background: #fff3cd; color: #92400e; }
.acct-status-processing { background: #dbeafe; color: #1e40af; }
.acct-status-shipped    { background: #e0f2fe; color: #0369a1; }
.acct-status-delivered  { background: #dcfce7; color: #15803d; }
.acct-status-cancelled  { background: #fef2f2; color: #dc2626; }

/* ── Address cards ───────────────────────────────────── */
.acct-address-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
@media (max-width: 767px) { .acct-address-grid { grid-template-columns: 1fr; } }
.acct-address-card {
  background: #fdf8f4; border: 1.5px solid #f0ebe5; border-radius: 12px;
  padding: 18px 20px; transition: box-shadow .2s, border-color .2s;
}
.acct-address-card:hover { box-shadow: 0 4px 16px rgba(113,78,50,.1); border-color: #d4b896; }
.acct-address-type {
  font-size: 11px; font-weight: 700; color: #c4956a;
  text-transform: uppercase; letter-spacing: .5px;
  margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
}
.acct-default-badge {
  background: #dcfce7; color: #15803d; font-size: 10px;
  font-weight: 700; padding: 2px 8px; border-radius: 20px;
}
.acct-address-line { font-size: 13.5px; color: #1a1209; font-weight: 600; margin-bottom: 4px; }
.acct-address-sub  { font-size: 12.5px; color: #7a6655; margin-bottom: 2px; }
.acct-zone-pill {
  display: inline-flex; align-items: center; gap: 5px;
  background: #fff; border: 1px solid #e8ddd5;
  padding: 3px 9px; border-radius: 20px;
  font-size: 11px; font-weight: 600; color: #4a3728; margin-top: 8px;
}
.acct-address-actions { display: flex; gap: 8px; margin-top: 14px; }

/* Toggle switch */
.acct-switch { position: relative; display: inline-block; width: 42px; height: 23px; }
.acct-switch input { opacity: 0; width: 0; height: 0; }
.acct-switch-track {
  position: absolute; cursor: pointer; inset: 0;
  background: #d1c4b9; border-radius: 23px; transition: .3s;
}
.acct-switch-track:before {
  content: ''; position: absolute;
  height: 17px; width: 17px; left: 3px; bottom: 3px;
  background: #fff; border-radius: 50%; transition: .3s;
  box-shadow: 0 1px 4px rgba(0,0,0,.18);
}
.acct-switch input:checked + .acct-switch-track { background: #714e32; }
.acct-switch input:checked + .acct-switch-track:before { transform: translateX(19px); }

/* ── Review cards ────────────────────────────────────── */
.acct-review-card {
  border: 1px solid #f0ebe5; border-radius: 12px;
  padding: 18px 20px; margin-bottom: 14px; transition: box-shadow .2s;
}
.acct-review-card:hover { box-shadow: 0 4px 14px rgba(113,78,50,.08); }
.acct-review-img {
  width: 56px; height: 56px; border-radius: 8px;
  object-fit: cover; border: 1px solid #f0ebe5; flex-shrink: 0;
}

/* ── Security sections ───────────────────────────────── */
.acct-security-section {
  border: 1px solid #f0ebe5; border-radius: 12px;
  overflow: hidden; margin-bottom: 16px;
}
.acct-security-head {
  background: #fdf8f4; padding: 14px 20px;
  border-bottom: 1px solid #f0ebe5;
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.acct-security-head h5 { font-size: 14px; font-weight: 700; color: #1a1209; margin: 0; }
.acct-security-body { padding: 20px; }
.acct-security-danger { border-color: #fecaca; }
.acct-security-danger .acct-security-head { background: #fff5f5; border-bottom-color: #fecaca; }
.acct-security-danger .acct-security-head h5 { color: #dc2626; }

.acct-activity-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.acct-activity-table th {
  padding: 10px 14px; background: #fdf8f4;
  font-size: 11px; font-weight: 700; color: #4a3728;
  text-transform: uppercase; letter-spacing: .4px;
  border-bottom: 1px solid #f0ebe5;
}
.acct-activity-table td { padding: 12px 14px; border-bottom: 1px solid #f5f0eb; color: #1a1209; }
.acct-activity-table tbody tr:last-child td { border-bottom: none; }

/* ── Empty state ─────────────────────────────────────── */
.acct-empty {
  display: flex; flex-direction: column;
  align-items: center; justify-content: center;
  padding: 50px 30px; text-align: center;
}
.acct-empty-icon {
  width: 68px; height: 68px; border-radius: 50%;
  background: linear-gradient(135deg, #f5ede5, #e8d5c4);
  display: flex; align-items: center; justify-content: center;
  color: #c4956a; font-size: 24px; margin-bottom: 14px;
}
.acct-empty h4 { font-size: 16px; font-weight: 700; color: #1a1209; margin-bottom: 6px; }
.acct-empty p  { font-size: 13.5px; color: #7a6655; margin-bottom: 20px; }

/* ── Mobile tab nav ──────────────────────────────────── */
.acct-mobile-nav {
  display: none; background: #fff; border: 1px solid #f0ebe5;
  border-radius: 12px; margin-bottom: 20px; padding: 6px;
  gap: 4px; overflow-x: auto; box-shadow: 0 2px 10px rgba(113,78,50,.06);
}
.acct-mobile-nav::-webkit-scrollbar { height: 0; }
.acct-mobile-nav button {
  flex-shrink: 0; display: flex; flex-direction: column; align-items: center; gap: 4px;
  padding: 9px 12px; border: none; border-radius: 8px;
  background: transparent; cursor: pointer; font-size: 11px; font-weight: 600;
  color: #7a6655; font-family: inherit; transition: all .18s; white-space: nowrap;
}
.acct-mobile-nav button.active { background: #fdf1e8; color: #714e32; }
.acct-mobile-nav button i { font-size: 16px; }

/* ── Order item previews ─────────────────────────────── */
.acct-order-items { display: flex; flex-direction: column; gap: 6px; max-width: 260px; }
.acct-order-item  { display: flex; align-items: center; gap: 8px; }
.acct-order-item-img {
  width: 36px; height: 36px; border-radius: 6px; object-fit: cover;
  border: 1px solid #f0ebe5; flex-shrink: 0;
}
.acct-order-item-name {
  font-size: 12.5px; font-weight: 500; color: #1a1209;
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 200px;
}
.acct-order-item-qty { font-size: 11px; color: #9ca3af; }
.acct-order-more {
  font-size: 11.5px; color: #714e32; font-weight: 600;
  background: #fdf1e8; border-radius: 4px; padding: 2px 8px;
  cursor: pointer; width: fit-content; margin-top: 2px;
}

/* Spinner */
.acct-spinner {
  width: 36px; height: 36px; border: 3px solid #f0ebe5;
  border-top-color: #714e32; border-radius: 50%;
  animation: acct-spin .8s linear infinite;
  margin: 40px auto; display: block;
}
@keyframes acct-spin { to { transform: rotate(360deg); } }

/* Responsive */
@media (max-width: 991px) {
  .acct-sidebar { display: none; }
  .acct-mobile-nav { display: flex; }
}
@media (max-width: 600px) {
  .acct-stats-row .acct-stat-card { min-width: 140px; }
  .acct-panel-body { padding: 16px; }
  .acct-table { font-size: 12px; }
  .acct-table thead th, .acct-table tbody td { padding: 10px 12px; }
}
</style>

<div class="acct-page">

  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

  <div class="body_content_wrapper">

    {{-- Page header --}}
    <div class="acct-page-header">
      <div class="container">
        <div class="acct-header-inner">
          <img class="acct-header-avatar"
               src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f5ede5&color=714e32"
               alt="{{ auth()->user()->name }}">
          <div>
            <h1>My Account</h1>
            <p>Welcome back, {{ auth()->user()->name }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="container pb90">

      {{-- Stats row --}}
      <div class="acct-stats-row">
        <div class="acct-stat-card">
          <div class="acct-stat-icon"><i class="fas fa-shopping-bag"></i></div>
          <div>
            <div class="acct-stat-label">Total Orders</div>
            <div class="acct-stat-value" id="stat-orders">—</div>
          </div>
        </div>
        <div class="acct-stat-card">
          <div class="acct-stat-icon"><i class="fas fa-check-circle"></i></div>
          <div>
            <div class="acct-stat-label">Delivered</div>
            <div class="acct-stat-value" id="stat-delivered">—</div>
          </div>
        </div>
        <div class="acct-stat-card">
          <div class="acct-stat-icon"><i class="fas fa-star"></i></div>
          <div>
            <div class="acct-stat-label">Reviews</div>
            <div class="acct-stat-value" id="stat-reviews">—</div>
          </div>
        </div>
        <div class="acct-stat-card">
          <div class="acct-stat-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div>
            <div class="acct-stat-label">Addresses</div>
            <div class="acct-stat-value" id="stat-addresses">—</div>
          </div>
        </div>
      </div>

      <div class="acct-layout">

        {{-- ══ SIDEBAR ══ --}}
        <aside class="acct-sidebar">
          <div class="acct-sidebar-user">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=f5ede5&color=714e32"
                 alt="{{ auth()->user()->name }}">
            <div style="min-width:0;">
              <p class="acct-user-name" id="sidebar-user-name">{{ auth()->user()->name }}</p>
              <a class="acct-user-email" href="mailto:{{ auth()->user()->email }}">{{ auth()->user()->email }}</a>
            </div>
          </div>
          <nav class="acct-sidebar-nav">
            <button class="acct-nav-item active" data-tab="account-details" onclick="switchTab('account-details', this)">
              <span class="acct-nav-icon"><i class="fas fa-user-circle"></i></span>Account Details
            </button>
            <button class="acct-nav-item" data-tab="orders" onclick="switchTab('orders', this)">
              <span class="acct-nav-icon"><i class="fas fa-shopping-bag"></i></span>My Orders
            </button>
            <button class="acct-nav-item" data-tab="reviews" onclick="switchTab('reviews', this)">
              <span class="acct-nav-icon"><i class="fas fa-star"></i></span>My Reviews
            </button>
            <button class="acct-nav-item" data-tab="address" onclick="switchTab('address', this)">
              <span class="acct-nav-icon"><i class="fas fa-map-marker-alt"></i></span>Addresses
            </button>
            <button class="acct-nav-item" data-tab="security" onclick="switchTab('security', this)">
              <span class="acct-nav-icon"><i class="fas fa-shield-alt"></i></span>Security
            </button>
            <div class="acct-nav-divider"></div>
            <a href="{{ route('logout') }}" class="acct-nav-logout"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <span class="acct-nav-icon"><i class="fas fa-sign-out-alt"></i></span>Logout
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
          </nav>
        </aside>

        {{-- ══ MAIN CONTENT ══ --}}
        <main class="acct-main">

          {{-- Mobile nav --}}
          <div class="acct-mobile-nav" id="acct-mobile-nav">
            <button class="active" data-tab="account-details" onclick="switchTab('account-details', this)">
              <i class="fas fa-user-circle"></i>Account
            </button>
            <button data-tab="orders" onclick="switchTab('orders', this)">
              <i class="fas fa-shopping-bag"></i>Orders
            </button>
            <button data-tab="reviews" onclick="switchTab('reviews', this)">
              <i class="fas fa-star"></i>Reviews
            </button>
            <button data-tab="address" onclick="switchTab('address', this)">
              <i class="fas fa-map-marker-alt"></i>Address
            </button>
            <button data-tab="security" onclick="switchTab('security', this)">
              <i class="fas fa-shield-alt"></i>Security
            </button>
            <button onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
              <i class="fas fa-sign-out-alt"></i>Logout
            </button>
          </div>

          {{-- ── TAB: ACCOUNT DETAILS ── --}}
          <div id="tab-account-details" class="acct-tab">
            <div class="acct-panel">
              <div class="acct-panel-header">
                <div class="acct-panel-title">
                  <div class="acct-panel-title-icon"><i class="fas fa-user-circle"></i></div>
                  <h3>Account Details</h3>
                </div>
              </div>
              <div class="acct-panel-body">
                <div class="acct-tabs-nav" id="acct-subtabs">
                  <button class="active" onclick="switchSubTab('profile', this)">Profile Information</button>
                  <button onclick="switchSubTab('password', this)">Change Password</button>
                </div>

                {{-- Profile --}}
                <div id="subtab-profile">
                  <form id="profile-form" novalidate>
                    @csrf
                    <div class="row">
                      <div class="col-md-12">
                        <div class="acct-form-group">
                          <label>Full Name</label>
                          <input class="acct-input" type="text" name="name" id="name" placeholder="Your Full Name" required>
                          <span class="error-text"></span>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="acct-form-group">
                          <label>Email Address</label>
                          <input class="acct-input email" type="email" name="email" id="email" placeholder="Your Email" required>
                          <span class="error-text"></span>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="acct-form-group">
                          <label>Phone Number</label>
                          <input class="acct-input" type="tel" name="phone" id="phone" placeholder="Phone Number">
                          <span class="error-text"></span>
                        </div>
                      </div>
                      <div class="col-12">
                        <div class="d-flex gap-3">
                          <button type="submit" class="acct-btn acct-btn-primary"><i class="fas fa-save"></i> Save Changes</button>
                          <button type="button" class="acct-btn acct-btn-secondary" onclick="resetProfileForm()">Cancel</button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>

                {{-- Password --}}
                <div id="subtab-password" style="display:none;">
                  <form id="password-form" novalidate>
                    @csrf
                    <div style="max-width:480px;">
                      <div class="acct-form-group">
                        <label>Current Password</label>
                        <input class="acct-input" type="password" name="current_password" placeholder="Current Password" required>
                        <span class="error-text"></span>
                      </div>
                      <div class="acct-form-group">
                        <label>New Password</label>
                        <input class="acct-input" type="password" name="new_password" placeholder="New Password" required>
                        <span class="error-text"></span>
                      </div>
                      <div class="acct-form-group">
                        <label>Confirm New Password</label>
                        <input class="acct-input" type="password" name="new_password_confirmation" placeholder="Confirm New Password" required>
                        <span class="error-text"></span>
                      </div>
                      <div class="d-flex gap-3">
                        <button type="submit" class="acct-btn acct-btn-primary"><i class="fas fa-lock"></i> Update Password</button>
                        <button type="button" class="acct-btn acct-btn-secondary" onclick="resetPasswordForm()">Cancel</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>

          {{-- ── TAB: ORDERS ── --}}
          <div id="tab-orders" class="acct-tab" style="display:none;">
            <div class="acct-panel">
              <div class="acct-panel-header">
                <div class="acct-panel-title">
                  <div class="acct-panel-title-icon"><i class="fas fa-shopping-bag"></i></div>
                  <h3>My Orders</h3>
                </div>
              </div>
              <div class="acct-panel-body p0">
                <div class="acct-table-wrap">
                  <table class="acct-table">
                    <thead>
                      <tr>
                        <th>Order #</th>
                        <th>Items</th>
                        <th>Date</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody id="orders-table-body">
                      <tr><td colspan="7"><div class="acct-spinner"></div></td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          {{-- ── TAB: REVIEWS ── --}}
          <div id="tab-reviews" class="acct-tab" style="display:none;">
            <div class="acct-panel">
              <div class="acct-panel-header">
                <div class="acct-panel-title">
                  <div class="acct-panel-title-icon"><i class="fas fa-star"></i></div>
                  <h3>My Reviews</h3>
                </div>
              </div>
              <div class="acct-panel-body" id="my-reviews-container">
                <div class="acct-spinner"></div>
              </div>
            </div>
          </div>

          {{-- ── TAB: ADDRESSES ── --}}
          <div id="tab-address" class="acct-tab" style="display:none;">
            <div class="acct-panel">
              <div class="acct-panel-header">
                <div class="acct-panel-title">
                  <div class="acct-panel-title-icon"><i class="fas fa-map-marker-alt"></i></div>
                  <h3>Saved Addresses</h3>
                </div>
                <button class="acct-btn acct-btn-primary acct-btn-sm" onclick="openAddAddressModal()">
                  <i class="fas fa-plus"></i> Add Address
                </button>
              </div>
              <div class="acct-panel-body">
                <div class="acct-address-grid" id="addresses-container">
                  <div style="grid-column:1/-1;"><div class="acct-spinner"></div></div>
                </div>
              </div>
            </div>
          </div>

          {{-- ── TAB: SECURITY ── --}}
          <div id="tab-security" class="acct-tab" style="display:none;">

            @if(session('security_success'))
            <div style="border-radius:10px;border:1px solid #bbf7d0;background:#f0fdf4;color:#15803d;padding:14px 16px;display:flex;align-items:center;gap:10px;margin-bottom:16px;font-size:14px;font-weight:600;">
              <i class="fas fa-circle-check"></i> {{ session('security_success') }}
            </div>
            @endif

            {{-- Active Sessions --}}
            <div class="acct-security-section">
              <div class="acct-security-head">
                <h5><i class="fas fa-desktop me-2" style="color:#c4956a;"></i>Active Sessions</h5>
                @php
                  try { $sessionCount = \Illuminate\Support\Facades\DB::table('sessions')->where('user_id', auth()->id())->count(); }
                  catch (\Throwable $e) { $sessionCount = 1; }
                @endphp
                <span style="background:#714e32;color:#fff;font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;">
                  {{ $sessionCount }} {{ \Illuminate\Support\Str::plural('device', $sessionCount) }}
                </span>
              </div>
              <div class="acct-security-body">
                <p style="font-size:13.5px;color:#7a6655;margin-bottom:16px;">
                  Sign out of all other browsers and devices. Your current session stays active.
                </p>
                @if(auth()->user()->isSocialOnly())
                  <p style="font-size:13px;color:#9ca3af;font-style:italic;">
                    <i class="fas fa-info-circle me-1"></i>Session management is not available for social login accounts.
                  </p>
                @else
                  <form method="POST" action="{{ route('account.sessions.revoke') }}" class="d-flex gap-3 flex-wrap align-items-start">
                    @csrf
                    <div>
                      <input type="password" name="password"
                             class="acct-input @error('password') border-danger @enderror"
                             style="min-width:220px;" placeholder="Confirm your current password">
                      @error('password')<div style="color:#dc2626;font-size:12px;margin-top:4px;">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="acct-btn acct-btn-primary">
                      <i class="fas fa-right-from-bracket"></i> Revoke Other Sessions
                    </button>
                  </form>
                @endif
              </div>
            </div>

            {{-- Login Activity --}}
            <div class="acct-security-section">
              <div class="acct-security-head">
                <h5><i class="fas fa-clock-rotate-left me-2" style="color:#c4956a;"></i>Login Activity</h5>
              </div>
              @php
                $loginActivities = \App\Models\LoginActivity::where('user_id', auth()->id())
                  ->where('user_type', 'customer')->latest('logged_in_at')->limit(10)->get();
              @endphp
              @if($loginActivities->isEmpty())
                <div class="acct-empty" style="padding:28px;">
                  <div class="acct-empty-icon"><i class="fas fa-clock-rotate-left"></i></div>
                  <p style="margin:0;color:#9ca3af;">No login activity recorded yet.</p>
                </div>
              @else
                <div class="acct-table-wrap">
                  <table class="acct-activity-table">
                    <thead>
                      <tr>
                        <th>Date &amp; Time</th><th>Device</th><th>IP Address</th><th>Status</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($loginActivities as $act)
                      <tr>
                        <td>{{ $act->logged_in_at->format('d M Y') }}<br><small style="color:#9ca3af;">{{ $act->logged_in_at->format('g:ia') }}</small></td>
                        <td>{{ $act->device ?? 'Unknown device' }}</td>
                        <td style="font-family:monospace;font-size:12px;">{{ $act->ip_address ?? '—' }}</td>
                        <td>
                          @if($act->successful)
                            <span class="acct-status acct-status-delivered"><i class="fas fa-check" style="font-size:9px;"></i> Success</span>
                          @else
                            <span class="acct-status acct-status-cancelled" title="{{ $act->failure_reason ? str_replace('_',' ',ucfirst($act->failure_reason)) : 'Failed' }}">
                              <i class="fas fa-times" style="font-size:9px;"></i> Failed
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

            {{-- Danger Zone --}}
            <div class="acct-security-section acct-security-danger">
              <div class="acct-security-head">
                <h5><i class="fas fa-triangle-exclamation me-2"></i>Danger Zone</h5>
              </div>
              <div class="acct-security-body">
                <p style="font-size:13.5px;color:#7a6655;margin-bottom:16px;">
                  Deactivating your account signs you out immediately. You have <strong>30 days</strong> to
                  reactivate. After that, the account is permanently closed.
                </p>
                <button type="button" class="acct-btn acct-btn-danger"
                        data-bs-toggle="modal" data-bs-target="#deactivateModal">
                  <i class="fas fa-user-slash"></i> Deactivate My Account
                </button>
              </div>
            </div>

          </div>{{-- /tab-security --}}

        </main>
      </div>{{-- /acct-layout --}}
    </div>{{-- /container --}}
  </div>

  @include('partials.footer')
</div>

{{-- ══════════════════════════════════════════
     MODALS
══════════════════════════════════════════ --}}

{{-- Address Modal --}}
<div class="modal fade" id="addressModal" tabindex="-1" aria-labelledby="addressModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:14px;border:1px solid #f0ebe5;overflow:hidden;">
      <div class="modal-header" style="background:#fdf8f4;border-bottom:1px solid #f0ebe5;padding:18px 22px;">
        <h5 class="modal-title" id="addressModalLabel" style="font-weight:700;color:#1a1209;">Add New Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:22px;">
        <form id="address-form">
          <input type="hidden" id="address-id">
          <div class="acct-form-group">
            <label>Street Address</label>
            <input type="text" class="acct-input" id="address-address" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <div class="acct-form-group">
                <label>City</label>
                <input type="text" class="acct-input" id="address-city" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="acct-form-group">
                <label>State / Province</label>
                <input type="text" class="acct-input" id="address-state">
              </div>
            </div>
            <div class="col-md-6">
              <div class="acct-form-group">
                <label>Postal Code</label>
                <input type="text" class="acct-input" id="address-postal_code" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="acct-form-group">
                <label>Country</label>
                <input type="text" class="acct-input" id="address-country" required>
              </div>
            </div>
          </div>
          <div class="acct-form-group">
            <label>Delivery Zone <span style="color:#dc2626;">*</span></label>
            <select class="acct-input" id="address-delivery_zone" required>
              <option value="">— Select your delivery zone —</option>
              @foreach($deliveryZones as $zone)
                <option value="{{ $zone }}">{{ $zone }}</option>
              @endforeach
            </select>
            <div class="acct-input-hint"><i class="fas fa-info-circle me-1"></i>Select the zone closest to your delivery address.</div>
          </div>
          <div style="display:flex;align-items:center;gap:9px;margin-top:4px;">
            <label class="acct-switch">
              <input type="checkbox" id="address-is_default">
              <span class="acct-switch-track"></span>
            </label>
            <span style="font-size:13px;font-weight:600;color:#4a3728;">Set as default address</span>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f0ebe5;padding:16px 22px;gap:10px;">
        <button type="button" class="acct-btn acct-btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="acct-btn acct-btn-primary" onclick="saveAddress()">
          <i class="fas fa-save"></i> Save Address
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Review Modal --}}
<div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:14px;border:1px solid #f0ebe5;overflow:hidden;">
      <div class="modal-header" style="background:#fdf8f4;border-bottom:1px solid #f0ebe5;padding:18px 22px;">
        <h5 class="modal-title" style="font-weight:700;color:#1a1209;">Write a Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:22px;">
        <form id="review-form">
          <input type="hidden" id="review-order-id">
          <input type="hidden" id="review-product-id">
          <input type="hidden" id="review-rating-value" value="5">
          <div id="review-product-info" class="d-flex align-items-center gap-3 mb-4 p-3"
               style="background:#fdf8f4;border-radius:10px;border:1px solid #f0ebe5;"></div>
          <div class="acct-form-group">
            <label>Your Rating</label>
            <div class="d-flex gap-2">
              <i class="fas fa-star star-input active" data-rating="1" style="font-size:28px;cursor:pointer;color:#f59e0b;transition:transform .15s;"></i>
              <i class="fas fa-star star-input active" data-rating="2" style="font-size:28px;cursor:pointer;color:#f59e0b;transition:transform .15s;"></i>
              <i class="fas fa-star star-input active" data-rating="3" style="font-size:28px;cursor:pointer;color:#f59e0b;transition:transform .15s;"></i>
              <i class="fas fa-star star-input active" data-rating="4" style="font-size:28px;cursor:pointer;color:#f59e0b;transition:transform .15s;"></i>
              <i class="fas fa-star star-input active" data-rating="5" style="font-size:28px;cursor:pointer;color:#f59e0b;transition:transform .15s;"></i>
            </div>
          </div>
          <div class="acct-form-group">
            <label>Your Review</label>
            <textarea class="acct-input" id="review-comment" rows="4" required maxlength="1000"
                      placeholder="Share your experience with this product..."
                      style="resize:vertical;min-height:110px;"></textarea>
            <div class="acct-input-hint">Maximum 1000 characters</div>
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top:1px solid #f0ebe5;padding:16px 22px;gap:10px;">
        <button type="button" class="acct-btn acct-btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="acct-btn acct-btn-primary" onclick="submitReview()">
          <i class="fas fa-paper-plane"></i> Submit Review
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Deactivate Modal --}}
<div class="modal fade" id="deactivateModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;border:1px solid #fecaca;overflow:hidden;">
      <div class="modal-header" style="background:#fff5f5;border-bottom:1px solid #fecaca;padding:18px 22px;">
        <h5 class="modal-title" style="color:#dc2626;font-weight:700;">
          <i class="fas fa-user-slash me-2"></i>Deactivate Account?
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:22px;">
        <p style="font-size:13.5px;color:#7a6655;margin-bottom:20px;line-height:1.6;">
          You'll be signed out right away. Log back in within <strong>30 days</strong> to reactivate.
          After that, your account is permanently closed and cannot be recovered.
        </p>
        <form method="POST" action="{{ route('account.deactivate') }}" id="deactivate-form">
          @csrf
          @if(!auth()->user()->isSocialOnly())
          <div class="acct-form-group">
            <label>Current Password</label>
            <input type="password" name="password"
                   class="acct-input @error('password') border-danger @enderror"
                   placeholder="Confirm with your password">
            @error('password')<span class="error-text">{{ $message }}</span>@enderror
          </div>
          @endif
          <div class="acct-form-group">
            <label>Type <strong>DEACTIVATE</strong> to confirm</label>
            <input type="text" name="confirm_deactivate"
                   class="acct-input @error('confirm_deactivate') border-danger @enderror"
                   placeholder="DEACTIVATE" autocomplete="off">
            @error('confirm_deactivate')<span class="error-text">{{ $message }}</span>@enderror
          </div>
        </form>
      </div>
      <div class="modal-footer" style="border-top:1px solid #fecaca;padding:16px 22px;gap:10px;">
        <button type="button" class="acct-btn acct-btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="acct-btn" style="background:#dc2626;color:#fff;"
                onclick="document.getElementById('deactivate-form').submit()">
          Yes, Deactivate
        </button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<script>
const accountAPI = '/account/api';
let addressModal, reviewModal;
let currentAddressId = null;
let ordersLoaded = false, reviewsLoaded = false;

document.addEventListener('DOMContentLoaded', function () {
  addressModal = new bootstrap.Modal(document.getElementById('addressModal'));
  reviewModal  = new bootstrap.Modal(document.getElementById('reviewModal'));
  loadUserData();
  loadStats();

  // Star rating hover & click
  document.querySelectorAll('.star-input').forEach(star => {
    star.addEventListener('click', function () {
      const rating = parseInt(this.dataset.rating);
      document.getElementById('review-rating-value').value = rating;
      document.querySelectorAll('.star-input').forEach((s, i) => {
        s.style.color = i < rating ? '#f59e0b' : '#d1d5db';
      });
    });
    star.addEventListener('mouseenter', function () {
      const rating = parseInt(this.dataset.rating);
      document.querySelectorAll('.star-input').forEach((s, i) => {
        s.style.transform = i < rating ? 'scale(1.18)' : 'scale(1)';
      });
    });
    star.addEventListener('mouseleave', () => {
      document.querySelectorAll('.star-input').forEach(s => s.style.transform = 'scale(1)');
    });
  });
});

/* ── Tab switching ── */
function switchTab(tab, triggerEl) {
  document.querySelectorAll('.acct-tab').forEach(t => t.style.display = 'none');
  document.getElementById('tab-' + tab).style.display = 'block';

  document.querySelectorAll('.acct-sidebar-nav .acct-nav-item').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('#acct-mobile-nav button[data-tab]').forEach(b => b.classList.remove('active'));

  if (triggerEl) {
    triggerEl.classList.add('active');
    const isMobile = triggerEl.closest('#acct-mobile-nav');
    const twin = isMobile
      ? document.querySelector(`.acct-sidebar-nav button[data-tab="${tab}"]`)
      : document.querySelector(`#acct-mobile-nav button[data-tab="${tab}"]`);
    if (twin) twin.classList.add('active');
  }

  if (tab === 'orders')  loadOrders();
  if (tab === 'reviews') loadMyReviews();
  if (tab === 'address') loadAddresses();
}

/* ── Sub-tab switching ── */
function switchSubTab(sub, triggerEl) {
  document.getElementById('subtab-profile').style.display  = sub === 'profile'  ? 'block' : 'none';
  document.getElementById('subtab-password').style.display = sub === 'password' ? 'block' : 'none';
  document.querySelectorAll('#acct-subtabs button').forEach(b => b.classList.remove('active'));
  if (triggerEl) triggerEl.classList.add('active');
}

/* ── Stats ── */
async function loadStats() {
  try {
    const [oRes, rRes, aRes] = await Promise.all([
      fetch(`${accountAPI}/orders`),
      fetch(`${accountAPI}/reviews`),
      fetch(`${accountAPI}/addresses`)
    ]);
    const orders    = await oRes.json();
    const reviews   = await rRes.json();
    const addresses = await aRes.json();
 
    const oArr = orders.data ?? orders;
 
    document.getElementById('stat-orders').textContent    = oArr.length ?? 0;
 
    // BUG 1 FIX: status comes through as "Delivered" (Ucfirst) from
    // AccountController::getOrders() — normalise with toLowerCase()
    document.getElementById('stat-delivered').textContent =
      oArr.filter(o => (o.status ?? '').toLowerCase() === 'delivered').length;
 
    document.getElementById('stat-reviews').textContent   = reviews.length ?? 0;
    document.getElementById('stat-addresses').textContent = addresses.length ?? 0;
  } catch (e) {
    // Silently fail — dashes remain in place
  }
}
/* ── Load user profile ── */
async function loadUserData() {
  try {
    const res  = await fetch(`${accountAPI}/profile`);
    const user = await res.json();
    if (user) {
      document.getElementById('name').value  = user.name  ?? '';
      document.getElementById('email').value = user.email ?? '';
      document.getElementById('phone').value = user.phone ?? '';
      const el = document.getElementById('sidebar-user-name');
      if (el) el.textContent = user.name ?? '';
    }
  } catch (e) {}
}

/* ── Profile form ── */
document.getElementById('profile-form').addEventListener('submit', async function (e) {
  e.preventDefault();
  const btn = this.querySelector('[type=submit]');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…'; btn.disabled = true;
  try {
    const res = await fetch(`${accountAPI}/profile/update`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({ name: document.getElementById('name').value, email: document.getElementById('email').value, phone: document.getElementById('phone').value })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Profile updated successfully', 'success');
      document.getElementById('sidebar-user-name').textContent = document.getElementById('name').value;
    } else { showToast(data.message || 'Error updating profile', 'error'); }
  } catch (e) { showToast('Error updating profile', 'error'); }
  finally { btn.innerHTML = '<i class="fas fa-save"></i> Save Changes'; btn.disabled = false; }
});

/* ── Password form ── */
document.getElementById('password-form').addEventListener('submit', async function (e) {
  e.preventDefault();
  const btn = this.querySelector('[type=submit]');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating…'; btn.disabled = true;
  const fd = {};
  this.querySelectorAll('input').forEach(i => { if (i.name) fd[i.name] = i.value; });
  try {
    const res = await fetch(`${accountAPI}/password/update`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(fd)
    });
    const data = await res.json();
    if (data.success) { showToast('Password updated successfully', 'success'); this.reset(); }
    else { showToast(data.message || 'Error updating password', 'error'); }
  } catch (e) { showToast('Error updating password', 'error'); }
  finally { btn.innerHTML = '<i class="fas fa-lock"></i> Update Password'; btn.disabled = false; }
});

function resetProfileForm() { document.getElementById('profile-form').reset(); loadUserData(); }
function resetPasswordForm() { document.getElementById('password-form').reset(); }

/* ── Load Orders ── */
async function loadOrders() {
  if (ordersLoaded) return;
  try {
    const res  = await fetch(`${accountAPI}/orders`);
    const data = await res.json();
    const orders = Array.isArray(data) ? data : (data.data ?? []);
    const tbody  = document.getElementById('orders-table-body');
 
    if (!orders.length) {
      tbody.innerHTML = `<tr><td colspan="7"><div class="acct-empty">
        <div class="acct-empty-icon"><i class="fas fa-shopping-bag"></i></div>
        <h4>No orders yet</h4><p>You haven't placed any orders. Start shopping!</p>
        <a href="{{ route('product.index') }}" class="acct-btn acct-btn-primary">Shop Now</a>
      </div></td></tr>`;
      ordersLoaded = true; return;
    }
 
    tbody.innerHTML = orders.map(order => {
      // FIX: API keys are product_image / product_name (not image / name)
      const items = (order.items ?? []).slice(0, 2).map(item => `
        <div class="acct-order-item">
          <img src="${item.product_image ?? ''}"
               alt="${item.product_name ?? ''}"
               class="acct-order-item-img"
               onerror="this.style.display='none'">
          <div>
            <div class="acct-order-item-name">${item.product_name ?? 'Product'}</div>
            <div class="acct-order-item-qty">Qty: ${item.quantity ?? 1}</div>
          </div>
        </div>`).join('');
 
      const more = (order.items ?? []).length > 2
        ? `<div class="acct-order-more">+${(order.items ?? []).length - 2} more</div>` : '';
 
      // Status: comes as "Pending", "Processing", etc. (ucfirst from controller)
      const sKey   = (order.status ?? 'pending').toLowerCase();
      const sLabel = order.status ?? 'Pending';
 
      // FIX: API returns `date` not `created_at`
      const dateStr = order.date ?? order.created_at ?? '—';
 
      // FIX: `total` is a formatted string e.g. "125,000.00" — strip commas before Number()
      const totalNum = Number(String(order.total ?? '0').replace(/,/g, ''));
      const totalStr = totalNum.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
 
      // Payment method: API returns payment_method directly on each item,
      // but not on the order object — fall back gracefully
      const payMethod = order.payment_method ?? '—';
 
      return `<tr>
        <td><strong style="color:#714e32;">#${order.order_number ?? order.id}</strong></td>
        <td><div class="acct-order-items">${items}${more}</div></td>
        <td style="white-space:nowrap;">${dateStr}</td>
        <td>${payMethod}</td>
        <td><span class="acct-status acct-status-${sKey}">${sLabel}</span></td>
        <td><strong>₦${totalStr}</strong></td>
        <td>
          <a href="/account/orders/${order.id}" class="acct-btn acct-btn-outline acct-btn-sm">
            <i class="fas fa-eye"></i> View
          </a>
          ${order.can_cancel ? `
          <button onclick="cancelOrder(${order.id})" class="acct-btn acct-btn-danger acct-btn-sm mt-1">
            <i class="fas fa-times"></i> Cancel
          </button>` : ''}
        </td>
      </tr>`;
    }).join('');
 
    ordersLoaded = true;
 
  } catch (e) {
    document.getElementById('orders-table-body').innerHTML =
      '<tr><td colspan="7" style="text-align:center;color:#dc2626;padding:30px;">Error loading orders. Please refresh.</td></tr>';
  }
}
 
/* ── BONUS: cancelOrder() helper (referenced in rows above) ── */
async function cancelOrder(orderId) {
  if (!confirm('Cancel this order? This cannot be undone.')) return;
  try {
    const res  = await fetch(`/account/orders/${orderId}/cancel`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      ordersLoaded = false;
      loadOrders();
    } else {
      showToast(data.message || 'Unable to cancel order', 'error');
    }
  } catch (e) { showToast('Error cancelling order', 'error'); }
}

/* ── Load Reviews ── */
async function loadMyReviews() {
  if (reviewsLoaded) return;
  const container = document.getElementById('my-reviews-container');
  try {
    const res     = await fetch(`${accountAPI}/reviews`);
    const reviews = await res.json();

    if (!reviews.length) {
      container.innerHTML = `<div class="acct-empty">
        <div class="acct-empty-icon"><i class="fas fa-star"></i></div>
        <h4>No reviews yet</h4><p>Reviews you submit will appear here.</p>
      </div>`;
      reviewsLoaded = true; return;
    }

    container.innerHTML = reviews.map(review => {
      const stars = [1,2,3,4,5].map(i =>
        `<i class="fas fa-star" style="color:${i <= (review.rating ?? 5) ? '#f59e0b' : '#e5e7eb'};font-size:13px;"></i>`).join('');
      const status = review.is_approved
        ? `<span class="acct-status acct-status-delivered">Approved</span>`
        : `<span class="acct-status acct-status-pending">Pending</span>`;
      return `
        <div class="acct-review-card">
          <div style="display:flex;align-items:flex-start;gap:14px;margin-bottom:12px;">
            <img src="${review.product_image ?? ''}" alt="" class="acct-review-img" onerror="this.style.display='none'">
            <div style="flex:1;">
              <div style="font-size:14px;font-weight:700;color:#1a1209;margin-bottom:4px;">${review.product_name ?? ''}</div>
              <div style="display:flex;gap:2px;margin-bottom:4px;">${stars}</div>
              <small style="color:#9ca3af;">Order #${review.order_number} · ${review.created_at}</small>
            </div>
            ${status}
          </div>
          <p style="font-size:13.5px;color:#4a3728;margin:0 0 ${review.seller_response ? '10px' : '0'};">${review.comment}</p>
          ${review.seller_response ? `<div style="background:#fdf8f4;border:1px solid #f0ebe5;border-radius:8px;padding:12px 14px;font-size:13px;"><strong style="color:#714e32;">Seller Response:</strong> ${review.seller_response}</div>` : ''}
        </div>`;
    }).join('');
    reviewsLoaded = true;
  } catch (e) {
    container.innerHTML = '<p style="text-align:center;color:#dc2626;padding:30px;">Error loading reviews</p>';
  }
}

/* ── Load Addresses ── */
async function loadAddresses() {
  const container = document.getElementById('addresses-container');
  try {
    const res       = await fetch(`${accountAPI}/addresses`);
    const addresses = await res.json();
    container.innerHTML = '';

    if (!addresses.length) {
      container.innerHTML = `<div style="grid-column:1/-1;"><div class="acct-empty">
        <div class="acct-empty-icon"><i class="fas fa-map-marker-alt"></i></div>
        <h4>No addresses saved</h4><p>Add a delivery address to speed up checkout.</p>
        <button class="acct-btn acct-btn-primary" onclick="openAddAddressModal()"><i class="fas fa-plus"></i> Add Address</button>
      </div></div>`;
      return;
    }

    addresses.forEach(address => {
      const defaultBadge   = address.is_default ? '<span class="acct-default-badge">Default</span>' : '';
      const switchChecked  = address.is_default ? 'checked' : '';
      container.innerHTML += `
        <div class="acct-address-card">
          <div class="acct-address-type"><i class="fas fa-home"></i> Shipping Address ${defaultBadge}</div>
          <div class="acct-address-line">${address.address}</div>
          <div class="acct-address-sub">${address.city}${address.state ? ', ' + address.state : ''} ${address.postal_code ?? ''}</div>
          <div class="acct-address-sub">${address.country}</div>
          <div class="acct-zone-pill"><i class="fas fa-map-marker-alt" style="font-size:10px;color:#c4956a;"></i> ${address.delivery_zone ?? 'Zone not set'}</div>
          <div style="display:flex;align-items:center;gap:9px;margin-top:14px;">
            <label class="acct-switch">
              <input type="checkbox" ${switchChecked} onchange="setDefaultAddress(${address.id})">
              <span class="acct-switch-track"></span>
            </label>
            <span style="font-size:12.5px;font-weight:600;color:#4a3728;">Set as default</span>
          </div>
          <div class="acct-address-actions">
            <button class="acct-btn acct-btn-outline acct-btn-sm" onclick="editAddress(${address.id})"><i class="fas fa-edit"></i> Edit</button>
            <button class="acct-btn acct-btn-danger acct-btn-sm" onclick="deleteAddress(${address.id})"><i class="fas fa-trash"></i> Delete</button>
          </div>
        </div>`;
    });
  } catch (e) {
    container.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#dc2626;padding:30px;">Error loading addresses</div>';
  }
}

function openAddAddressModal() {
  currentAddressId = null;
  document.getElementById('address-form').reset();
  document.getElementById('addressModalLabel').textContent = 'Add New Address';
  document.getElementById('address-id').value = '';
  addressModal.show();
}

async function editAddress(addressId) {
  try {
    const res = await fetch(`${accountAPI}/addresses`);
    const addresses = await res.json();
    const address   = addresses.find(a => a.id === addressId);
    if (!address) return;
    currentAddressId = addressId;
    document.getElementById('address-id').value            = addressId;
    document.getElementById('address-address').value       = address.address;
    document.getElementById('address-city').value          = address.city;
    document.getElementById('address-state').value         = address.state ?? '';
    document.getElementById('address-postal_code').value   = address.postal_code;
    document.getElementById('address-country').value       = address.country;
    document.getElementById('address-delivery_zone').value = address.delivery_zone ?? '';
    document.getElementById('address-is_default').checked  = address.is_default;
    document.getElementById('addressModalLabel').textContent = 'Edit Address';
    addressModal.show();
  } catch (e) { alert('Error loading address'); }
}

async function saveAddress() {
  const form = document.getElementById('address-form');
  const addressId = document.getElementById('address-id').value;
  if (!form.checkValidity()) { form.reportValidity(); return; }
  const formData = {
    address:       document.getElementById('address-address').value,
    city:          document.getElementById('address-city').value,
    state:         document.getElementById('address-state').value,
    postal_code:   document.getElementById('address-postal_code').value,
    country:       document.getElementById('address-country').value,
    delivery_zone: document.getElementById('address-delivery_zone').value,
    is_default:    document.getElementById('address-is_default').checked ? 1 : 0
  };
  try {
    const url    = addressId ? `${accountAPI}/addresses/${addressId}` : `${accountAPI}/addresses/store`;
    const method = addressId ? 'PUT' : 'POST';
    const res    = await fetch(url, {
      method, body: JSON.stringify(formData),
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Content-Type': 'application/json', 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) { addressModal.hide(); form.reset(); loadAddresses(); showToast(data.message, 'success'); }
    else { showToast(data.message || 'Unable to save address', 'error'); }
  } catch (e) { showToast('Error saving address', 'error'); }
}

async function deleteAddress(addressId) {
  if (!confirm('Delete this address?')) return;
  try {
    const res  = await fetch(`${accountAPI}/addresses/${addressId}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
    });
    const data = await res.json();
    if (data.success) { loadAddresses(); showToast(data.message, 'success'); }
    else { showToast(data.message || 'Unable to delete address', 'error'); }
  } catch (e) { showToast('Error deleting address', 'error'); }
}

async function setDefaultAddress(addressId) {
  try {
    const res  = await fetch(`${accountAPI}/addresses/${addressId}/set-default`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Content-Type': 'application/json' }
    });
    const data = await res.json();
    if (data.success) loadAddresses();
    else showToast(data.message || 'Unable to set default address', 'error');
  } catch (e) { showToast('Error setting default address', 'error'); }
}

/* ── Reviews ── */
function openReviewModal(orderId, productId, productName, productImage) {
  document.getElementById('review-order-id').value   = orderId;
  document.getElementById('review-product-id').value = productId;
  document.getElementById('review-comment').value    = '';
  document.getElementById('review-rating-value').value = 5;
  document.querySelectorAll('.star-input').forEach(s => s.style.color = '#f59e0b');
  document.getElementById('review-product-info').innerHTML = `
    <img src="${productImage}" alt="${productName.replace(/'/g,"\\'")} "
         style="width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid #f0ebe5;flex-shrink:0;">
    <div style="font-size:14px;font-weight:700;color:#1a1209;">${productName.replace(/'/g,"\\'")}</div>`;
  reviewModal.show();
}

async function submitReview() {
  const comment = document.getElementById('review-comment').value.trim();
  if (!comment) { showToast('Please write a review', 'error'); return; }
  try {
    const res  = await fetch(`${accountAPI}/reviews/submit`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value, 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        order_id: document.getElementById('review-order-id').value,
        product_id: document.getElementById('review-product-id').value,
        rating: document.getElementById('review-rating-value').value,
        comment
      })
    });
    const data = await res.json();
    if (data.success) {
      reviewModal.hide(); showToast(data.message, 'success');
      reviewsLoaded = false; loadMyReviews();
    } else { showToast('Error: ' + (data.message || 'Unable to submit review'), 'error'); }
  } catch (e) { showToast('Error submitting review', 'error'); }
}

/* ── Toast ── */
function showToast(message, type = 'success') {
  const existing = document.getElementById('acct-toast');
  if (existing) existing.remove();
  const bg    = type === 'success' ? '#dcfce7' : '#fef2f2';
  const color = type === 'success' ? '#15803d' : '#dc2626';
  const icon  = type === 'success' ? 'fa-circle-check' : 'fa-circle-xmark';
  const toast = document.createElement('div');
  toast.id = 'acct-toast';
  toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;background:${bg};color:${color};border:1px solid ${color}30;border-radius:10px;padding:14px 18px 14px 14px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.12);animation:toastIn .3s ease;max-width:320px;`;
  toast.innerHTML = `<i class="fas ${icon}" style="font-size:16px;"></i>${message}`;
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity='0'; toast.style.transition='opacity .3s'; setTimeout(() => toast.remove(), 300); }, 3200);
}
const _toastStyle = document.createElement('style');
_toastStyle.textContent = `@keyframes toastIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}`;
document.head.appendChild(_toastStyle);
</script>
@endpush

@endsection
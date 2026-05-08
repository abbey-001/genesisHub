@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<style>
/* ════════════════════════════════════════
   LISTING PAGE — SCOPED STYLES
   Every rule is prefixed with .listing-page
   so nothing bleeds into the site header,
   navigation, or footer.
════════════════════════════════════════ */

/* ── Page shell ─────────────────────────────────── */
.listing-page { background: #f3f5f6; min-height: 100vh; }

/* ── Page title strip ───────────────────────────── */
.listing-page .listing-header { background:#fff; border-bottom:1px solid #ebebeb; padding:22px 0 18px; margin-bottom:24px; }
.listing-page .listing-header h1 { font-size:22px; font-weight:700; color:#0a0a0a; margin:0 0 4px; }
.listing-page .listing-header p  { font-size:14px; color:#626974; margin:0; }

/* ── Toolbar ────────────────────────────────────── */
.listing-page .listing-toolbar  { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
.listing-page .toolbar-left  { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.listing-page .toolbar-right { display:flex; align-items:center; gap:10px; }

/*
 * FILTER TOGGLE BUTTON
 * ─────────────────────────────────────────────────
 * We deliberately do NOT use the theme's .btn class
 * because that class applies:
 *   overflow: hidden  → clips our icon
 *   position: relative + z-index:1 → stacking issues
 *   border-top: none  → breaks our pill border
 *   ::before pseudo   → animated bg that covers the click area
 * All of these are incompatible with a toggle pill button.
 * We use a standalone class and explicitly override anything
 * Bootstrap or the theme might cascade onto a <button>.
 */
.listing-page .btn-filter-toggle {
    display:inline-flex !important;
    align-items:center !important;
    gap:7px;
    background:#fff;
    border:1.5px solid #ebebeb !important;
    border-top:1.5px solid #ebebeb !important;
    color:#0a0a0a;
    padding:9px 18px;
    border-radius:50px;
    font-size:14px; font-weight:500;
    font-family:"Jost",sans-serif;
    cursor:pointer;
    transition:all .2s;
    white-space:nowrap;
    /* Undo .btn overrides */
    overflow:visible !important;
    position:static !important;
    z-index:auto !important;
    text-align:left !important;
    letter-spacing:0 !important;
    line-height:1.5 !important;
    text-transform:none !important;
}
.listing-page .btn-filter-toggle:hover {
    border-color:#714e32 !important;
    color:#714e32;
    background:#f5ede5;
    box-shadow:none;
}
/* Disable the theme's sliding pseudo-element animation */
.listing-page .btn-filter-toggle::before,
.listing-page .btn-filter-toggle::after { display:none !important; content:none !important; }

.listing-page .filter-badge {
    background:#714e32; color:#fff;
    font-size:11px; font-weight:700;
    width:18px; height:18px;
    border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
}

/* Sort dropdown */
.listing-page .sort-select-wrap { position:relative; }
.listing-page .sort-select-wrap select {
    appearance:none; -webkit-appearance:none;
    background:#fff; border:1.5px solid #ebebeb; border-radius:50px;
    padding:9px 36px 9px 16px; font-size:14px; color:#0a0a0a;
    cursor:pointer; font-weight:500; font-family:"Jost",sans-serif;
    transition:border .2s;
}
.listing-page .sort-select-wrap select:focus { border-color:#714e32; outline:none; }
.listing-page .sort-select-wrap::after { content:"▾"; position:absolute; right:14px; top:50%; transform:translateY(-50%); pointer-events:none; color:#626974; font-size:13px; }

/* Results count */
.listing-page .results-count { font-size:14px; color:#626974; }
.listing-page .results-count strong { color:#0a0a0a; font-weight:600; }

/* ── Active filter chips ─────────────────────────── */
.listing-page .active-filters-bar { background:#f5ede5; border:1px solid #e8d5c0; border-radius:10px; padding:12px 16px; margin-bottom:20px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
.listing-page .active-filters-label { font-size:13px; font-weight:600; color:#5a3c24; white-space:nowrap; }
.listing-page .filter-chip { display:inline-flex; align-items:center; gap:5px; background:#fff; border:1px solid #d4b896; color:#5a3c24; padding:4px 10px 4px 12px; border-radius:50px; font-size:13px; font-weight:500; }
.listing-page .filter-chip button { background:none; border:none; cursor:pointer; padding:0; color:#c4956a; font-size:15px; line-height:1; transition:color .15s; display:flex; align-items:center; }
.listing-page .filter-chip button:hover { color:#dc2626; }
.listing-page .btn-clear-all { margin-left:auto; background:none; border:none; cursor:pointer; font-size:13px; color:#714e32; font-weight:600; text-decoration:underline; padding:0; }
.listing-page .btn-clear-all:hover { color:#5a3c24; }

/* ── Product grid ───────────────────────────────── */
.listing-page #product-grid-wrapper { position:relative; transition:opacity .25s; }
.listing-page #product-grid-wrapper.loading { opacity:.45; pointer-events:none; }

.listing-page .products-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
@media (max-width:1399px) { .listing-page .products-grid { grid-template-columns:repeat(3,1fr); } }
@media (max-width:991px)  { .listing-page .products-grid { grid-template-columns:repeat(2,1fr); } }
@media (max-width:480px)  { .listing-page .products-grid { grid-template-columns:repeat(2,1fr); gap:10px; } }

.listing-page .grid-loading-overlay { display:none; position:absolute; inset:0; background:rgba(243,245,246,.75); border-radius:10px; z-index:10; align-items:center; justify-content:center; flex-direction:column; gap:12px; }
.listing-page #product-grid-wrapper.loading .grid-loading-overlay { display:flex; }
.listing-page .grid-spinner { width:40px; height:40px; border:3px solid #e5e7eb; border-top-color:#714e32; border-radius:50%; animation:lp-spin .8s linear infinite; }
@keyframes lp-spin { to { transform:rotate(360deg); } }

/* ── Product card ───────────────────────────────── */
.listing-page .product-card { background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.08); transition:transform .22s,box-shadow .22s; display:flex; flex-direction:column; position:relative; }
.listing-page .product-card:hover { transform:translateY(-4px); box-shadow:0 4px 16px rgba(0,0,0,.10); }

.listing-page .card-img-wrap { position:relative; overflow:hidden; background:#f3f4f6; aspect-ratio:1/1; }
.listing-page .card-img-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
.listing-page .product-card:hover .card-img-wrap img { transform:scale(1.04); }

.listing-page .badge-wrap { position:absolute; top:10px; left:10px; display:flex; flex-direction:column; gap:5px; z-index:2; }
.listing-page .badge-discount { background:#dc2626; color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; }
.listing-page .badge-stock    { background:#f59e0b; color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; }
.listing-page .badge-oos      { background:#6b7280; color:#fff; font-size:11px; font-weight:700; padding:3px 8px; border-radius:4px; }

.listing-page .card-actions { position:absolute; top:10px; right:10px; display:flex; flex-direction:column; gap:6px; z-index:2; opacity:0; transform:translateX(8px); transition:all .22s; }
.listing-page .product-card:hover .card-actions { opacity:1; transform:translateX(0); }
.listing-page .card-action-btn { width:34px; height:34px; background:#fff; border:none; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,.12); color:#626974; font-size:14px; transition:all .18s; text-decoration:none; }
.listing-page .card-action-btn:hover { background:#714e32; color:#fff; }

.listing-page .card-cart-btn { position:absolute; bottom:0; left:0; right:0; background:#714e32; color:#fff; border:none; padding:10px; font-size:13px; font-weight:600; cursor:pointer; opacity:0; transform:translateY(100%); transition:all .22s; letter-spacing:.3px; text-align:center; text-decoration:none; display:block; }
.listing-page .product-card:hover .card-cart-btn { opacity:1; transform:translateY(0); }
.listing-page .card-cart-btn:hover { background:#5a3c24; color:#fff; }

.listing-page .card-body { padding:13px 13px 14px; flex:1; display:flex; flex-direction:column; }
.listing-page .card-brand { font-size:11px; font-weight:600; color:#c4956a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
.listing-page .card-name a { font-size:14px; font-weight:600; color:#0a0a0a; text-decoration:none; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; line-height:1.4; transition:color .15s; }
.listing-page .card-name a:hover { color:#714e32; }

/* Condition / Company / Perfect tags row */
.listing-page .card-meta-tags { display:flex; flex-wrap:wrap; gap:4px; margin:5px 0 4px; }
.listing-page .tag-condition {
    display:inline-flex; align-items:center;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:4px;
    letter-spacing:.3px; text-transform:uppercase;
}
.listing-page .tag-company {
    display:inline-flex; align-items:center; gap:3px;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:4px;
    background:#eff6ff; color:#1d4ed8;
    letter-spacing:.2px; text-transform:uppercase;
}
.listing-page .tag-perfect {
    display:inline-flex; align-items:center; gap:3px;
    font-size:10px; font-weight:700;
    padding:2px 7px; border-radius:4px;
    background:#fff7ed; color:#c2410c;
    letter-spacing:.2px; text-transform:uppercase;
}
/* Perfect rating image overlay badge */
.listing-page .badge-perfect {
    position:absolute; top:10px; right:10px;
    display:flex; align-items:center; gap:2px;
    background:rgba(250,204,21,.93);
    color:#78350f;
    font-size:11px; font-weight:800;
    padding:3px 8px; border-radius:20px;
    backdrop-filter:blur(4px);
    z-index:3;
    box-shadow:0 1px 4px rgba(0,0,0,.12);
    pointer-events:none;
}

.listing-page .card-rating { display:flex; align-items:center; gap:5px; margin:7px 0; }
.listing-page .lp-stars { display:flex; gap:1px; }
.listing-page .lp-stars i { font-size:11px; color:#f59e0b; }
.listing-page .lp-stars i.empty { color:#d1d5db; }
.listing-page .rating-count { font-size:12px; color:#626974; }
.listing-page .card-price { margin-top:auto; padding-top:8px; display:flex; align-items:baseline; gap:7px; flex-wrap:wrap; }
.listing-page .price-current  { font-size:18px; font-weight:700; color:#5a3c24; }
.listing-page .price-original { font-size:13px; color:#9ca3af; text-decoration:line-through; }
.listing-page .price-save     { font-size:11px; color:#16a34a; font-weight:600; background:#dcfce7; padding:2px 6px; border-radius:4px; }
.listing-page .card-sold      { font-size:11px; color:#626974; margin-top:5px; }
.listing-page .card-sold i    { color:#f87171; margin-right:2px; }

/* ── Empty state ─────────────────────────────────── */
.listing-page .empty-state { text-align:center; padding:80px 20px; grid-column:1/-1; }
.listing-page .empty-state-icon { width:72px; height:72px; background:#f5ede5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; font-size:28px; color:#c4956a; }
.listing-page .empty-state h4 { font-size:18px; color:#0a0a0a; margin-bottom:8px; }
.listing-page .empty-state p  { color:#626974; font-size:14px; margin-bottom:20px; }

/* ── Pagination ──────────────────────────────────── */
.listing-page .listing-pagination { margin-top:32px; display:flex; align-items:center; justify-content:center; flex-wrap:wrap; gap:6px; }
.listing-page .pg-btn { min-width:38px; height:38px; display:inline-flex; align-items:center; justify-content:center; border:1.5px solid #ebebeb; border-radius:6px; background:#fff; color:#0a0a0a; font-size:14px; font-weight:500; cursor:pointer; transition:all .18s; text-decoration:none; padding:0 10px; font-family:"Jost",sans-serif; }
.listing-page .pg-btn:hover    { border-color:#714e32; color:#714e32; background:#f5ede5; }
.listing-page .pg-btn.active   { background:#714e32; border-color:#714e32; color:#fff; font-weight:700; }
.listing-page .pg-btn.disabled { opacity:.4; pointer-events:none; }
.listing-page .pg-info         { font-size:13px; color:#626974; margin-top:12px; text-align:center; width:100%; }

@media (max-width:576px) {
    .listing-page .listing-toolbar { flex-direction:column; align-items:stretch; }
    .listing-page .toolbar-left, .listing-page .toolbar-right { justify-content:space-between; }
}
</style>
<div class="wrapper listing-page">

  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs])

  {{-- Page Header --}}
  <div class="listing-header">
    <div class="container">
      <h1>{{ $pageTitle }}</h1>
      <p>{{ $pageDescription }}</p>
    </div>
  </div>

  <section class="our-listing pb40">
    <div class="container">

      {{-- Active Filters Bar --}}
      @if(!empty($activeFilters) && count($activeFilters) > 0)
      <div class="active-filters-bar" id="active-filters-bar">
        <span class="active-filters-label">
          <i class="fas fa-filter me-1"></i>Filters:
        </span>

        @isset($activeFilters['search'])
          <span class="filter-chip">
            <i class="fas fa-search" style="font-size:11px;color:var(--brand-mid);"></i>
            "{{ $activeFilters['search'] }}"
            <button class="remove-filter" data-filter="search" title="Remove"><i class="fas fa-times"></i></button>
          </span>
        @endisset

        @isset($activeFilters['brands'])
          @foreach($activeFilters['brands'] as $b)
            <span class="filter-chip">
              {{ $b->name }}
              <button class="remove-filter" data-filter="brand" data-value="{{ $b->slug }}" title="Remove"><i class="fas fa-times"></i></button>
            </span>
          @endforeach
        @endisset

        @isset($activeFilters['categories'])
          @foreach($activeFilters['categories'] as $cat)
            <span class="filter-chip">
              {{ $cat->name }}
              <button class="remove-filter" data-filter="category" data-value="{{ $cat->slug }}" title="Remove"><i class="fas fa-times"></i></button>
            </span>
          @endforeach
        @endisset

        @isset($activeFilters['price_range'])
          <span class="filter-chip">
            ₦{{ number_format($activeFilters['price_range']['min']) }} – ₦{{ number_format($activeFilters['price_range']['max']) }}
            <button class="remove-filter" data-filter="price" title="Remove"><i class="fas fa-times"></i></button>
          </span>
        @endisset

        @isset($activeFilters['rating'])
          <span class="filter-chip">
            {{ $activeFilters['rating'] }}+ ★
            <button class="remove-filter" data-filter="min_rating" title="Remove"><i class="fas fa-times"></i></button>
          </span>
        @endisset

        @isset($activeFilters['filters'])
          @foreach($activeFilters['filters'] as $f)
            <span class="filter-chip">
              {{ ucfirst($f) }}
              <button class="remove-filter" data-filter="filter" data-value="{{ $f }}" title="Remove"><i class="fas fa-times"></i></button>
            </span>
          @endforeach
        @endisset

        @if(!empty($activeFilters['conditions']))
          @php
            $conditionLabels = ['new'=>'New','used'=>'Used','refurbished'=>'Refurbished','open_box'=>'Open Box'];
          @endphp
          @foreach($activeFilters['conditions'] as $c)
            <span class="filter-chip">
              {{ $conditionLabels[$c] ?? ucfirst($c) }}
              <button class="remove-filter" data-filter="condition" data-value="{{ $c }}" title="Remove"><i class="fas fa-times"></i></button>
            </span>
          @endforeach
        @endif

        @if(!empty($activeFilters['seller_types']))
          @php
            $sellerTypeLabels = ['individual'=>'Individual Seller','company'=>'Company','partnership'=>'Partnership'];
          @endphp
          @foreach($activeFilters['seller_types'] as $st)
            <span class="filter-chip">
              {{ $sellerTypeLabels[$st] ?? ucfirst($st) }}
              <button class="remove-filter" data-filter="seller_type" data-value="{{ $st }}" title="Remove"><i class="fas fa-times"></i></button>
            </span>
          @endforeach
        @endif

        @if(!empty($activeFilters['delivery_zones']))
          @foreach($activeFilters['delivery_zones'] as $zone)
            <span class="filter-chip">
              <i class="fas fa-map-pin" style="font-size:10px;color:var(--brand-mid);"></i>
              {{ ucwords(str_replace('-', ' ', $zone)) }}
              <button class="remove-filter" data-filter="delivery_zone" data-value="{{ $zone }}" title="Remove"><i class="fas fa-times"></i></button>
            </span>
          @endforeach
        @endif

        <button class="btn-clear-all" id="btn-clear-all">Clear All</button>
      </div>
      @endif

      {{-- Toolbar --}}
      <div class="listing-toolbar">
        <div class="toolbar-left">
          {{-- Filter toggle --}}
          @php
            $filterCount = 0;
            if (!empty($activeFilters['search']))       $filterCount++;
            if (!empty($activeFilters['brands']))       $filterCount += count($activeFilters['brands']);
            if (!empty($activeFilters['categories']))   $filterCount += count($activeFilters['categories']);
            if (!empty($activeFilters['price_range']))  $filterCount++;
            if (!empty($activeFilters['rating']))       $filterCount++;
            if (!empty($activeFilters['filters']))      $filterCount += count($activeFilters['filters']);
            if (!empty($activeFilters['conditions']))   $filterCount += count($activeFilters['conditions']);
            if (!empty($activeFilters['seller_types'])) $filterCount += count($activeFilters['seller_types']);
            if (!empty($activeFilters['delivery_zones'])) $filterCount += count($activeFilters['delivery_zones']);
          @endphp
          <button type="button" class="btn-filter-toggle" id="btn-open-filters">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
              <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/>
            </svg>
            All Filters
            @if($filterCount > 0)
              <span class="filter-badge">{{ $filterCount }}</span>
            @endif
          </button>

          {{-- Results count --}}
          <span class="results-count" id="results-count">
            <strong>{{ number_format($totalResults) }}</strong> products found
          </span>
        </div>

        <div class="toolbar-right">
          {{-- Sort --}}
          <div class="sort-select-wrap">
            <select id="sort-select" class="sort-select">
              <option value="default"      {{ $sortBy === 'default'     ? 'selected' : '' }}>Best Match</option>
              <option value="newest"       {{ $sortBy === 'newest'      ? 'selected' : '' }}>Newest First</option>
              <option value="bestseller"   {{ $sortBy === 'bestseller'  ? 'selected' : '' }}>Best Selling</option>
              <option value="rating"       {{ $sortBy === 'rating'      ? 'selected' : '' }}>Top Rated</option>
              <option value="price_low"    {{ $sortBy === 'price_low'   ? 'selected' : '' }}>Price: Low → High</option>
              <option value="price_high"   {{ $sortBy === 'price_high'  ? 'selected' : '' }}>Price: High → Low</option>
            </select>
          </div>
        </div>
      </div>

      {{-- Product Grid --}}
      <div id="product-grid-wrapper">
        <div class="grid-loading-overlay">
          <div class="grid-spinner"></div>
          <span style="font-size:13px;color:#6b7280;">Loading products…</span>
        </div>

        <div class="products-grid" id="product-grid">
          @forelse($products as $product)
            @include('products.partials.product-card', ['product' => $product])
          @empty
            <div class="empty-state">
              <div class="empty-state-icon"><i class="fas fa-search"></i></div>
              <h4>No products found</h4>
              <p>Try adjusting your filters or search terms</p>
              <a href="{{ route('product.index') }}" class="btn" style="background:var(--brand);color:#fff;padding:10px 24px;border-radius:50px;text-decoration:none;font-weight:600;">
                Clear Filters
              </a>
            </div>
          @endforelse
        </div>
      </div>

      {{-- Pagination --}}
      <div id="pagination-wrap">
        @include('products.partials.pagination', ['products' => $products])
      </div>

    </div>{{-- /container --}}
  </section>

  @include('partials.footer')
  <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
</div>

{{-- Filter Sidebar --}}
@include('products.partials.filter-sidebar', [
    'filterOptions' => $filterOptions,
    'activeFilters' => $activeFilters,
])


@push('scripts')
<script>
$(function () {

    // ─── helpers ─────────────────────────────────────────────────
    function getUrlParams() {
        return new URLSearchParams(window.location.search);
    }

    function setLoading(on) {
        $('#product-grid-wrapper').toggleClass('loading', on);
    }

    // ─── updateFilterBadge ────────────────────────────────────────
    // FIX: badge was server-rendered only; never updated after AJAX.
    function updateFilterBadge(count) {
        const $btn   = $('#btn-open-filters');
        const $badge = $btn.find('.filter-badge');
        if (count > 0) {
            if ($badge.length) { $badge.text(count); }
            else { $btn.append('<span class="filter-badge">' + count + '</span>'); }
        } else {
            $badge.remove();
        }
    }

    // ─── doAjaxFilter ────────────────────────────────────────────
    // FIX: exposed as window.doAjaxFilter so filter-sidebar.blade.php
    //      can call it directly instead of owning its own $.ajax block
    //      (which was causing the page-reload-on-apply bug).
    function doAjaxFilter(params) {
        setLoading(true);

        $.ajax({
            url: '{{ route("product.filter") }}',
            method: 'GET',
            data: params,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (res) {
                if (res.success) {
                    // ── Grid + pagination ──────────────────────────
                    $('#product-grid').html(res.html);
                    $('#pagination-wrap').html(res.pagination);
                    $('#results-count').html(
                        '<strong>' + res.totalResults.toLocaleString() + '</strong> products found'
                    );

                    // ── Active filters bar (FIX: was never re-rendered) ──
                    if (res.activeFiltersHtml !== undefined) {
                        const $bar = $('#active-filters-bar');
                        if ($bar.length) {
                            $bar.replaceWith(res.activeFiltersHtml);
                        } else {
                            // Bar didn't exist yet (no filters were active before) —
                            // inject it before the toolbar
                            $('.listing-toolbar').before(res.activeFiltersHtml);
                        }
                    }

                    // ── Filter badge count (FIX: was never updated) ───────
                    if (res.filterCount !== undefined) {
                        updateFilterBadge(res.filterCount);
                    }

                    // ── URL — full rebuild so no stale params survive ──────
                    const url = new URL(window.location);
                    ['brand','category','min_price','max_price','min_rating',
                     'condition','delivery_zone','seller_type','sort_by',
                     'search','q','filter','page'].forEach(k => url.searchParams.delete(k));
                    Object.entries(params).forEach(([k, v]) => {
                        if (v !== undefined && v !== null && String(v) !== '') {
                            url.searchParams.set(k, v);
                        }
                    });
                    url.searchParams.delete('page');
                    window.history.pushState({}, '', url);

                    // ── Scroll to grid top ────────────────────────────────
                    $('html,body').animate({ scrollTop: $('.listing-toolbar').offset().top - 80 }, 400);
                }
                setLoading(false);
            },
            error: function () {
                setLoading(false);
            }
        });
    }

    // Expose globally so filter-sidebar.blade.php can call it
    window.doAjaxFilter = doAjaxFilter;

    // ─── Sort ─────────────────────────────────────────────────────
    $('#sort-select').on('change', function () {
        const p = getUrlParams();
        p.set('sort_by', $(this).val());
        p.delete('page');
        doAjaxFilter(Object.fromEntries(p));
    });

    // ─── Open filter sidebar ─────────────────────────────────────
    $('#btn-open-filters').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        $('#genesis-filter-sidebar').addClass('genesis-open');
        $('#genesis-filter-backdrop').addClass('genesis-open');
        $('body').addClass('genesis-noscroll');
    });

    // ─── Remove filter chip ───────────────────────────────────────
    $(document).on('click', '.remove-filter', function (e) {
        e.preventDefault();
        const filter = $(this).data('filter');
        const value  = String($(this).data('value') ?? '');
        const p = getUrlParams();

        if (filter === 'price') {
            p.delete('min_price'); p.delete('max_price');
        } else if (filter === 'search') {
            p.delete('search'); p.delete('q');
        } else if (value !== '') {
            const cur = p.get(filter) ? p.get(filter).split(',') : [];
            const upd = cur.filter(v => v !== value);
            upd.length ? p.set(filter, upd.join(',')) : p.delete(filter);
        } else {
            p.delete(filter);
        }
        p.delete('page');

        // Remove chip from DOM immediately for instant feedback
        $(this).closest('.filter-chip').remove();
        if ($('#active-filters-bar .filter-chip').length === 0) {
            $('#active-filters-bar').hide();
        }

        doAjaxFilter(Object.fromEntries(p));
    });

    // ─── Clear all ────────────────────────────────────────────────
    $('#btn-clear-all').on('click', function () {
        window.location.href = '{{ route("product.index") }}';
    });

    // ─── AJAX pagination ─────────────────────────────────────────
    $(document).on('click', '.pg-btn[data-page]', function (e) {
        e.preventDefault();
        const page = $(this).data('page');
        const p = getUrlParams();
        p.set('page', page);
        doAjaxFilter(Object.fromEntries(p));
    });

    // ─── Back/forward support ────────────────────────────────────
    window.addEventListener('popstate', () => location.reload());
});
</script>
@endpush

@endsection
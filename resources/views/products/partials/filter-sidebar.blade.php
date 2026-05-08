{{-- ════════════════════════════════════════
    All Filters Sidebar
    Included from products.index
════════════════════════════════════════ --}}

<style>
/* ── Sidebar shell ── */
#genesis-filter-sidebar {
    position: fixed;
    top: 0; left: -420px;
    width: 400px;
    height: 100%;
    background: #fff;
    z-index: 100000;
    overflow: hidden;
    transition: left .3s cubic-bezier(.4,0,.2,1);
    box-shadow: 4px 0 24px rgba(0,0,0,.18);
    display: flex;
    flex-direction: column;
}
#genesis-filter-sidebar.genesis-open { left: 0; }

/* ── Backdrop ── */
#genesis-filter-backdrop {
    position: fixed; inset: 0;
    background: rgba(0,0,0,.5);
    z-index: 99999;
    display: none;
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
}
#genesis-filter-backdrop.genesis-open { display: block; }
body.genesis-noscroll { overflow: hidden; }

/* ── Header ── */
.fsb-header {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 20px;
    border-bottom: 1px solid #f0f0f0;
    background: #fff;
}
.fsb-title {
    font-size: 17px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0;
}
.fsb-close {
    width: 34px; height: 34px;
    border: none; background: #f3f4f6;
    border-radius: 50%;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #6b7280;
    font-size: 18px;
    transition: all .18s;
}
.fsb-close:hover { background: #fee2e2; color: #dc2626; }

/* ── Scroll body ── */
.fsb-body {
    flex: 1;
    overflow-y: auto;
    padding: 0;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}
.fsb-body::-webkit-scrollbar { width: 4px; }
.fsb-body::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

/* ── Section ── */
.fsb-section {
    border-bottom: 1px solid #f3f4f6;
}
.fsb-section-toggle {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 15px 20px;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    transition: background .15s;
}
.fsb-section-toggle:hover { background: #fafafa; }
.fsb-section-label {
    font-size: 14px;
    font-weight: 600;
    color: #1a1a1a;
}
.fsb-section-icon {
    font-size: 12px;
    color: #9ca3af;
    transition: transform .2s;
}
.fsb-section-toggle[aria-expanded="true"] .fsb-section-icon { transform: rotate(180deg); }

.fsb-section-body {
    padding: 0 20px 18px;
}

/* ── Search input ── */
.fsb-search-input {
    width: 100%;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    padding: 9px 12px;
    font-size: 13px;
    color: #1a1a1a;
    background: #fafafa;
    margin-bottom: 12px;
    transition: border .2s;
}
.fsb-search-input:focus { outline: none; border-color: #714e32; background: #fff; }

/* ── Checkboxes ── */
.fsb-checkbox-list { display: flex; flex-direction: column; gap: 2px; }
.fsb-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 7px;
    cursor: pointer;
    transition: background .15s;
    font-size: 13.5px;
    color: #374151;
    user-select: none;
}
.fsb-checkbox:hover { background: #f5ede5; }
.fsb-checkbox input[type="checkbox"] { display: none; }
.fsb-checkmark {
    flex-shrink: 0;
    width: 18px; height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 5px;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    transition: all .18s;
}
.fsb-checkbox input:checked ~ .fsb-checkmark {
    background: #714e32;
    border-color: #714e32;
}
.fsb-checkmark::after {
    content: '';
    width: 5px; height: 9px;
    border: 2px solid #fff;
    border-top: none; border-left: none;
    transform: rotate(45deg);
    display: none;
    margin-top: -2px;
}
.fsb-checkbox input:checked ~ .fsb-checkmark::after { display: block; }
.fsb-checkbox-text { flex: 1; }
.fsb-count {
    font-size: 12px;
    color: #9ca3af;
    background: #f3f4f6;
    padding: 1px 6px;
    border-radius: 10px;
}
.fsb-show-more {
    background: none; border: none;
    font-size: 13px; color: #714e32;
    font-weight: 600; cursor: pointer;
    padding: 4px 0;
    margin-top: 6px;
    text-decoration: underline;
}

/* ── Price slider ── */
.price-display {
    display: flex; align-items: center; justify-content: space-between;
    font-size: 14px; font-weight: 600; color: #1a1a1a;
    margin-bottom: 16px;
}
.price-display span {
    background: #f5ede5;
    padding: 4px 10px;
    border-radius: 6px;
    color: #714e32;
}
/* jQuery UI slider reskin */
#sidebar-price-slider.ui-slider {
    background: #e5e7eb;
    border: none;
    height: 5px;
    border-radius: 5px;
    margin: 0 8px;
}
#sidebar-price-slider .ui-slider-range {
    background: #714e32;
    border-radius: 5px;
}
#sidebar-price-slider .ui-slider-handle {
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #fff;
    border: 2.5px solid #714e32;
    top: -7px;
    cursor: pointer;
    outline: none;
    transition: transform .15s, box-shadow .15s;
}
#sidebar-price-slider .ui-slider-handle:hover,
#sidebar-price-slider .ui-slider-handle:focus {
    transform: scale(1.2);
    box-shadow: 0 0 0 4px rgba(113,78,50,.15);
}

/* ── Rating ── */
.fsb-rating-list { display: flex; flex-direction: column; gap: 4px; }
.fsb-rating-option {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px;
    border-radius: 7px;
    cursor: pointer;
    transition: background .15s;
}
.fsb-rating-option:hover { background: #f5ede5; }
.fsb-rating-option input[type="radio"] { display: none; }
.fsb-radio {
    width: 18px; height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 50%;
    background: #fff;
    display: flex; align-items: center; justify-content: center;
    transition: all .18s;
    flex-shrink: 0;
}
.fsb-rating-option input:checked ~ .fsb-radio {
    border-color: #714e32;
}
.fsb-radio::after {
    content: '';
    width: 8px; height: 8px;
    border-radius: 50%;
    background: #714e32;
    display: none;
}
.fsb-rating-option input:checked ~ .fsb-radio::after { display: block; }
.fsb-stars { display: flex; gap: 2px; }
.fsb-stars i { font-size: 13px; color: #f59e0b; }
.fsb-stars i.empty { color: #d1d5db; }
.fsb-rating-label { font-size: 13px; color: #374151; }

/* ── Condition chips ── */
.fsb-condition-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}
.fsb-condition-chip {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 9px 10px;
    border: 1.5px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all .18s;
    font-size: 13px; color: #374151;
    user-select: none;
    text-align: center;
    font-weight: 500;
}
.fsb-condition-chip:hover { border-color: #714e32; background: #f5ede5; color: #714e32; }
.fsb-condition-chip input { display: none; }
.fsb-condition-chip.checked { border-color: #714e32; background: #f5ede5; color: #714e32; font-weight: 700; }
.fsb-condition-chip .chip-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}

/* ── Location search ── */
.fsb-location-search-wrap { position: relative; margin-bottom: 10px; }
.fsb-location-search-wrap .fsb-search-input { margin-bottom: 0; padding-left: 32px; }
.fsb-location-search-icon {
    position: absolute; left: 10px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; font-size: 13px;
}
.fsb-location-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
    max-height: 220px;
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db transparent;
}
.fsb-location-grid::-webkit-scrollbar { width: 3px; }
.fsb-location-grid::-webkit-scrollbar-thumb { background: #d1d5db; }
.fsb-location-chip {
    display: flex; align-items: center; gap: 5px;
    padding: 7px 9px;
    border: 1.5px solid #e5e7eb;
    border-radius: 7px;
    cursor: pointer;
    transition: all .15s;
    font-size: 12.5px; color: #374151;
    user-select: none;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.fsb-location-chip:hover { border-color: #714e32; background: #f5ede5; color: #714e32; }
.fsb-location-chip input { display: none; }
.fsb-location-chip.checked { border-color: #714e32; background: #f5ede5; color: #714e32; font-weight: 600; }
.fsb-location-chip i { font-size: 10px; flex-shrink: 0; }

/* ── Footer actions ── */
.fsb-footer {
    flex-shrink: 0;
    padding: 16px 20px;
    border-top: 1px solid #f0f0f0;
    background: #fff;
    display: flex;
    gap: 10px;
}
.fsb-btn-apply {
    flex: 1;
    background: #714e32;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 10px;
    font-size: 14px; font-weight: 700;
    cursor: pointer;
    transition: background .2s;
    letter-spacing: .3px;
}
.fsb-btn-apply:hover { background: #5a3c24; }
.fsb-btn-clear {
    padding: 12px 20px;
    background: none;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px; font-weight: 600; color: #6b7280;
    cursor: pointer;
    transition: all .2s;
}
.fsb-btn-clear:hover { border-color: #dc2626; color: #dc2626; }
</style>

{{-- Sidebar --}}
<div id="genesis-filter-sidebar">

  {{-- Header --}}
  <div class="fsb-header">
    <h2 class="fsb-title">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="vertical-align:-3px;margin-right:7px;color:#714e32;">
        <line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/>
      </svg>
      Filters
    </h2>
    <button type="button" class="fsb-close genesis-sidebar-close" aria-label="Close filters">✕</button>
  </div>

  {{-- Scrollable body --}}
  <div class="fsb-body">

    {{-- ── Categories ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-categories" aria-expanded="true">
        <span class="fsb-section-label">Category</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse show" id="fsb-categories">
        <div class="fsb-section-body">
          <input type="text" class="fsb-search-input fsb-cat-search" placeholder="Search categories…">
          <div class="fsb-checkbox-list fsb-cat-list">
            @foreach($filterOptions['categories'] ?? [] as $i => $cat)
              <label class="fsb-checkbox {{ $i >= 8 ? 'fsb-hidden-cat' : '' }}" style="{{ $i >= 8 ? 'display:none' : '' }}">
                <input type="checkbox" name="category[]" value="{{ $cat->slug }}"
                  {{ isset($activeFilters['categories']) && $activeFilters['categories']->contains('id', $cat->id) ? 'checked' : '' }}>
                <span class="fsb-checkmark"></span>
                <span class="fsb-checkbox-text">{{ $cat->name }}</span>
                <span class="fsb-count">{{ number_format($cat->products_count) }}</span>
              </label>
            @endforeach
          </div>
          @if(($filterOptions['categories'] ?? collect())->count() > 8)
            <button type="button" class="fsb-show-more" data-target=".fsb-hidden-cat" data-more="Show more" data-less="Show less">
              + Show more
            </button>
          @endif
        </div>
      </div>
    </div>

    {{-- ── Brands ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-brands" aria-expanded="true">
        <span class="fsb-section-label">Brand</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse show" id="fsb-brands">
        <div class="fsb-section-body">
          <input type="text" class="fsb-search-input fsb-brand-search" placeholder="Search brands…">
          <div class="fsb-checkbox-list fsb-brand-list">
            @foreach($filterOptions['brands'] ?? [] as $i => $brand)
              <label class="fsb-checkbox {{ $i >= 8 ? 'fsb-hidden-brand' : '' }}" style="{{ $i >= 8 ? 'display:none' : '' }}">
                <input type="checkbox" name="brand[]" value="{{ $brand->slug }}"
                  {{ isset($activeFilters['brands']) && $activeFilters['brands']->contains('id', $brand->id) ? 'checked' : '' }}>
                <span class="fsb-checkmark"></span>
                <span class="fsb-checkbox-text">{{ $brand->name }}</span>
                <span class="fsb-count">{{ number_format($brand->products_count) }}</span>
              </label>
            @endforeach
          </div>
          @if(($filterOptions['brands'] ?? collect())->count() > 8)
            <button type="button" class="fsb-show-more" data-target=".fsb-hidden-brand" data-more="Show more" data-less="Show less">
              + Show more
            </button>
          @endif
        </div>
      </div>
    </div>

    {{-- ── Price Range ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-price" aria-expanded="true">
        <span class="fsb-section-label">Price Range</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse show" id="fsb-price">
        <div class="fsb-section-body">
          <div class="price-display">
            <span id="sb-price-min">₦{{ number_format(request('min_price', $filterOptions['price_range']['min'] ?? 0)) }}</span>
            <span style="color:#9ca3af;font-weight:400;font-size:12px;">to</span>
            <span id="sb-price-max">₦{{ number_format(request('max_price', $filterOptions['price_range']['max'] ?? 500000)) }}</span>
          </div>
          <div id="sidebar-price-slider"
               data-min="{{ $filterOptions['price_range']['min'] ?? 0 }}"
               data-max="{{ $filterOptions['price_range']['max'] ?? 500000 }}"
               data-val-min="{{ request('min_price', $filterOptions['price_range']['min'] ?? 0) }}"
               data-val-max="{{ request('max_price', $filterOptions['price_range']['max'] ?? 500000) }}">
          </div>
          <input type="hidden" id="sb-hidden-min" name="min_price" value="{{ request('min_price', $filterOptions['price_range']['min'] ?? 0) }}">
          <input type="hidden" id="sb-hidden-max" name="max_price" value="{{ request('max_price', $filterOptions['price_range']['max'] ?? 500000) }}">
        </div>
      </div>
    </div>

    {{-- ── Rating ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-rating" aria-expanded="false">
        <span class="fsb-section-label">Customer Rating</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse" id="fsb-rating">
        <div class="fsb-section-body">
          <div class="fsb-rating-list">
            @foreach([5,4,3,2,1] as $r)
            <label class="fsb-rating-option">
              <input type="radio" name="min_rating" value="{{ $r }}"
                {{ request('min_rating') == $r ? 'checked' : '' }}>
              <span class="fsb-radio"></span>
              <div class="fsb-stars">
                @for($i = 1; $i <= 5; $i++)
                  <i class="fas fa-star{{ $i <= $r ? '' : ' empty' }}"></i>
                @endfor
              </div>
              <span class="fsb-rating-label">{{ $r == 5 ? 'Only' : ($r . '+ Stars') }}</span>
            </label>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- ── Condition ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-condition" aria-expanded="true">
        <span class="fsb-section-label">Condition</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse show" id="fsb-condition">
        <div class="fsb-section-body">
          @php
            $activeConditions = array_filter(explode(',', request('condition', '')));
            $conditionOptions = [
                'new'         => ['label' => 'New',          'dot' => '#16a34a'],
                'used'        => ['label' => 'Used',          'dot' => '#7c3aed'],
                'refurbished' => ['label' => 'Refurbished',   'dot' => '#d97706'],
                'open_box'    => ['label' => 'Open Box',      'dot' => '#0369a1'],
            ];
          @endphp
          <div class="fsb-condition-grid">
            @foreach($conditionOptions as $val => $opt)
              <label class="fsb-condition-chip {{ in_array($val, $activeConditions) ? 'checked' : '' }}">
                <input type="checkbox" name="condition[]" value="{{ $val }}"
                  {{ in_array($val, $activeConditions) ? 'checked' : '' }}>
                <span class="chip-dot" style="background:{{ $opt['dot'] }};"></span>
                {{ $opt['label'] }}
              </label>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- ── Seller Type ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-seller-type" aria-expanded="false">
        <span class="fsb-section-label">Seller Type</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse" id="fsb-seller-type">
        <div class="fsb-section-body">
          @php
            $activeSellerTypes = array_filter(explode(',', request('seller_type', '')));
            $sellerTypeOptions = [
                'individual'  => ['label' => 'Individual Seller', 'icon' => 'fa-user',      'color' => '#0369a1', 'bg' => '#e0f2fe'],
                'company'     => ['label' => 'Company',           'icon' => 'fa-building',  'color' => '#1d4ed8', 'bg' => '#eff6ff'],
                'partnership' => ['label' => 'Partnership',       'icon' => 'fa-handshake', 'color' => '#7c3aed', 'bg' => '#ede9fe'],
            ];
          @endphp
          <div class="fsb-condition-grid">
            @foreach($sellerTypeOptions as $val => $opt)
              <label class="fsb-condition-chip {{ in_array($val, $activeSellerTypes) ? 'checked' : '' }}"
                     style="{{ in_array($val, $activeSellerTypes) ? "border-color:{$opt['color']};background:{$opt['bg']};color:{$opt['color']};" : '' }}">
                <input type="checkbox" name="seller_type[]" value="{{ $val }}"
                  {{ in_array($val, $activeSellerTypes) ? 'checked' : '' }}>
                <i class="fas {{ $opt['icon'] }}" style="font-size:12px;"></i>
                {{ $opt['label'] }}
              </label>
            @endforeach
          </div>
        </div>
      </div>
    </div>

    {{-- ── Delivery Zone ── --}}
    <div class="fsb-section">
      <button type="button" class="fsb-section-toggle" data-bs-toggle="collapse" data-bs-target="#fsb-location" aria-expanded="true">
        <span class="fsb-section-label">Delivery Zone</span>
        <i class="fas fa-chevron-down fsb-section-icon"></i>
      </button>
      <div class="collapse show" id="fsb-location">
        <div class="fsb-section-body">
          @php
            $rawZone = request('delivery_zone', '');
            $activeZones = array_filter(
                is_array($rawZone) ? $rawZone : explode(',', $rawZone)
            );
            $deliveryZones = [
                'Parakin','Sabo','Aderemi Road','Ondo Road','Lagere','Moore','Opa',
                'Iremo','Ilode','Ajebamidele','Omole','OAU Staff Quarters','OAUTHC',
                'Damico','Eleyele','Asherifa','Oduduwa','Moremi Estate','Power line',
                'Campus gate','Campus','AP','Maintenance','Ojaja hostel','Fashina',
                'Oonilayout','Akarabata','Road 7','Modomo','Nasfat',
            ];
          @endphp
          <div class="fsb-location-search-wrap">
            <i class="fas fa-map-marker-alt fsb-location-search-icon"></i>
            <input type="text" class="fsb-search-input fsb-zone-search" placeholder="Search area…">
          </div>
          <div class="fsb-location-grid">
            @foreach($deliveryZones as $zone)
              @php $zoneSlug = Str::slug($zone); @endphp
              <label class="fsb-location-chip {{ in_array($zoneSlug, $activeZones) ? 'checked' : '' }}">
                <input type="checkbox" name="delivery_zone[]" value="{{ $zoneSlug }}"
                  {{ in_array($zoneSlug, $activeZones) ? 'checked' : '' }}>
                <i class="fas fa-map-pin"></i>
                {{ $zone }}
              </label>
            @endforeach
          </div>
        </div>
      </div>
    </div>

  </div>{{-- /fsb-body --}}

  {{-- Footer --}}
  <div class="fsb-footer">
    <button type="button" class="fsb-btn-apply" id="fsb-apply">
      <i class="fas fa-check me-1"></i>Apply Filters
    </button>
    <button type="button" class="fsb-btn-clear" id="fsb-clear">
      Clear All
    </button>
  </div>
</div>

{{-- Backdrop --}}
<div id="genesis-filter-backdrop"></div>
<script src="{{ asset('public/js/jquery-3.6.0.js') }}"></script>
  <script src="{{ asset('public/js/pricing-slider.js') }}"></script>
<script>
$(function () {

    // ── Price slider ──────────────────────────────────────────────
    const $ps = $('#sidebar-price-slider');
    if ($ps.length && $.fn.slider) {
        const minV  = parseFloat($ps.data('min'))     || 0;
        const maxV  = parseFloat($ps.data('max'))     || 500000;
        const valLo = parseFloat($ps.data('val-min')) || minV;
        const valHi = parseFloat($ps.data('val-max')) || maxV;

        $ps.slider({
            range: true,
            min: minV, max: maxV,
            values: [valLo, valHi],
            slide: function (e, ui) {
                $('#sb-price-min').text('₦' + ui.values[0].toLocaleString());
                $('#sb-price-max').text('₦' + ui.values[1].toLocaleString());
                $('#sb-hidden-min').val(ui.values[0]);
                $('#sb-hidden-max').val(ui.values[1]);
            }
        });
    }

    // ── Category live-search ──────────────────────────────────────
    $('.fsb-cat-search').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('.fsb-cat-list .fsb-checkbox').each(function () {
            const name = $(this).find('.fsb-checkbox-text').text().toLowerCase();
            $(this).toggle(name.includes(q));
        });
    });

    // ── Brand live-search ─────────────────────────────────────────
    $('.fsb-brand-search').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('.fsb-brand-list .fsb-checkbox').each(function () {
            const name = $(this).find('.fsb-checkbox-text').text().toLowerCase();
            $(this).toggle(name.includes(q));
        });
    });

    // ── Show more / less ─────────────────────────────────────────
    $('.fsb-show-more').on('click', function () {
        const $btn    = $(this);
        const target  = $btn.data('target');
        const $hidden = $(target);
        const visible = $hidden.filter(':visible').length > 0;
        $hidden.toggle(!visible);
        $btn.text(visible ? '+ ' + $btn.data('more') : '- ' + $btn.data('less'));
    });

    // ── Zone live-search ──────────────────────────────────────────
    $('.fsb-zone-search').on('input', function () {
        const q = $(this).val().toLowerCase();
        $('.fsb-location-grid .fsb-location-chip').each(function () {
            const name = $(this).text().trim().toLowerCase();
            $(this).toggle(name.includes(q));
        });
    });

    // ── Chip toggles (condition / location / seller-type) ─────────
    // FIX: was using setTimeout on 'click' which races with the browser's
    // native label→checkbox toggle, causing the checked state to flip twice.
    // Using 'change' on the input fires *after* the state is committed.
    $(document).on('change', '.fsb-condition-chip input[type="checkbox"]', function () {
        $(this).closest('.fsb-condition-chip').toggleClass('checked', this.checked);
    });

    $(document).on('change', '.fsb-location-chip input[type="checkbox"]', function () {
        $(this).closest('.fsb-location-chip').toggleClass('checked', this.checked);
    });

    // ── Close sidebar ─────────────────────────────────────────────
    function closeSidebar() {
        $('#genesis-filter-sidebar').removeClass('genesis-open');
        $('#genesis-filter-backdrop').removeClass('genesis-open');
        $('body').removeClass('genesis-noscroll');
    }
    $('#genesis-filter-backdrop').on('click', closeSidebar);
    $('#genesis-filter-sidebar').on('click', '.genesis-sidebar-close', closeSidebar);

    // ── Apply filters ─────────────────────────────────────────────
    // FIX: Previously this block ran $.ajax() then immediately called
    // location.href inside the success callback — killing the AJAX entirely.
    // Now we delegate to doAjaxFilter() (defined & exposed in index.blade.php).
    $('#fsb-apply').on('click', function () {
        closeSidebar();

        const p = new URLSearchParams(window.location.search);

        // Reset all filter params so we rebuild from current sidebar state
        ['brand', 'category', 'min_price', 'max_price', 'min_rating',
         'condition', 'delivery_zone', 'seller_type'].forEach(k => p.delete(k));
        p.delete('page');

        // Brands
        const brands = [];
        $('.fsb-brand-list input:checked').each(function () { brands.push($(this).val()); });
        if (brands.length) p.set('brand', brands.join(','));

        // Categories
        const cats = [];
        $('.fsb-cat-list input:checked').each(function () { cats.push($(this).val()); });
        if (cats.length) p.set('category', cats.join(','));

        // Price — FIX: only send when user actually moved slider away from defaults
        const $slider = $('#sidebar-price-slider');
        const absMin  = parseFloat($slider.data('min')) || 0;
        const absMax  = parseFloat($slider.data('max')) || 500000;
        const minP    = parseFloat($('#sb-hidden-min').val());
        const maxP    = parseFloat($('#sb-hidden-max').val());
        if (!isNaN(minP) && minP > absMin) p.set('min_price', minP);
        if (!isNaN(maxP) && maxP < absMax) p.set('max_price', maxP);

        // Rating
        const rat = $('input[name="min_rating"]:checked').val();
        if (rat) p.set('min_rating', rat);

        // Condition
        const conditions = [];
        $('input[name="condition[]"]:checked').each(function () { conditions.push($(this).val()); });
        if (conditions.length) p.set('condition', conditions.join(','));

        // Delivery zones
        const zones = [];
        $('input[name="delivery_zone[]"]:checked').each(function () { zones.push($(this).val()); });
        if (zones.length) p.set('delivery_zone', zones.join(','));

        // Seller type
        const sellerTypes = [];
        $('input[name="seller_type[]"]:checked').each(function () { sellerTypes.push($(this).val()); });
        if (sellerTypes.length) p.set('seller_type', sellerTypes.join(','));

        // Delegate to the shared handler in index.blade.php (exposed as window.doAjaxFilter)
        if (typeof window.doAjaxFilter === 'function') {
            window.doAjaxFilter(Object.fromEntries(p));
        } else {
            // Hard fallback only if parent page function is unavailable
            window.location.href = '{{ route("product.index") }}?' + p.toString();
        }
    });

    // ── Clear all ─────────────────────────────────────────────────
    $('#fsb-clear').on('click', function () {
        closeSidebar();
        window.location.href = '{{ route("product.index") }}';
    });

    // ── Collapse arrow icon animation ────────────────────────────
    $(document).on('shown.bs.collapse hidden.bs.collapse', '.fsb-section .collapse', function () {
        const $toggle = $(this).closest('.fsb-section').find('.fsb-section-toggle');
        const isOpen  = $(this).hasClass('show');
        $toggle.attr('aria-expanded', isOpen);
    });
});
</script>
@extends('layouts.app')

@section('title', $product->name . ' - ' . config('app.name'))
@section('meta_description', $product->short_description ?? Str::limit($product->description, 160))

@section('content')
<style>
/* ═════════════════════════════════════════════════════
   SSP — Shop Single Product custom styles
   Prefix: ssp-
═════════════════════════════════════════════════════ */

/* Brand badge */
.ssp-brand-badge {
  display: inline-flex; align-items: center; gap: 5px;
  background: #f5f0eb; border: 1px solid #e8ddd5; border-radius: 6px;
  padding: 4px 10px; font-size: 11px; font-weight: 800;
  color: #714e32; text-decoration: none; letter-spacing: .5px;
  text-transform: uppercase; transition: all .15s;
}
.ssp-brand-badge:hover { background: #714e32; color: #fff; border-color: #714e32; }

/* Review count */
.ssp-review-count { font-size: 13px; color: #7a6655; text-decoration: none; }
.ssp-review-count:hover { color: #714e32; }

/* Sold badge */
.ssp-sold-badge { font-size: 12px; font-weight: 600; color: #7a6655; }

/* Model number */
.ssp-model-num { font-size: 12.5px; color: #9ca3af; margin-bottom: 4px; }

/* Price */
#price-display { font-size: 28px; font-weight: 800; color: #714e32; line-height: 1.2; }
#price-display del { font-size: 18px; font-weight: 400; color: #9ca3af; }
.ssp-discount-badge {
  display: inline-flex; align-items: center;
  background: #dc2626; color: #fff;
  font-size: 12px; font-weight: 700; padding: 3px 9px; border-radius: 20px; margin-left: 8px;
}

/* Variant price note */
.ssp-variant-note {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fdf1e8; border: 1px solid #e8d5c4; border-radius: 8px;
  padding: 6px 12px; font-size: 13px; color: #714e32; font-weight: 600;
  margin-bottom: 12px;
}

/* Variants */
.ssp-variants-section {}
.ssp-variant-group { }
.ssp-variant-label {
  font-size: 13px; font-weight: 700; color: #4a3728; margin-bottom: 8px;
  text-transform: uppercase; letter-spacing: .3px;
}
.ssp-selected-value { color: #714e32; font-weight: 800; }
.ssp-variant-options { display: flex; flex-wrap: wrap; gap: 8px; }
.ssp-variant-btn {
  display: inline-flex; flex-direction: column; align-items: center;
  padding: 7px 14px; border: 1.5px solid #e8ddd5; border-radius: 8px;
  background: #fff; font-size: 13px; font-weight: 600; color: #4a3728;
  cursor: pointer; transition: all .18s; font-family: inherit;
}
.ssp-variant-btn:hover { border-color: #c4956a; color: #714e32; background: #fdf8f4; }
.ssp-variant-btn.selected { border-color: #714e32; background: #714e32; color: #fff; }
.ssp-variant-btn.selected .ssp-adj-hint { color: rgba(255,255,255,.7); }
.ssp-adj-hint { font-size: 10px; font-weight: 600; color: #9ca3af; margin-top: 2px; }

/* Stock badges */
.ssp-stock-row { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; }
.ssp-stock-badge {
  display: inline-flex; align-items: center; gap: 5px;
  font-size: 12.5px; font-weight: 700; padding: 5px 12px; border-radius: 20px;
}
.ssp-in-stock  { background: #dcfce7; color: #15803d; }
.ssp-low-stock { background: #fff3cd; color: #92400e; }
.ssp-out-stock { background: #fee2e2; color: #dc2626; }
.ssp-condition-badge {
  display: inline-flex; align-items: center;
  background: #fef3c7; color: #92400e;
  font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;
}

/* Info chips */
.ssp-info-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.ssp-chip {
  display: inline-flex; align-items: center; gap: 5px;
  background: #fdf8f4; border: 1px solid #f0ebe5; border-radius: 20px;
  padding: 4px 11px; font-size: 12px; font-weight: 600; color: #4a3728;
  text-decoration: none; transition: all .15s;
}
.ssp-chip:hover { background: #714e32; color: #fff; border-color: #714e32; }
.ssp-chip-light { color: #7a6655; }
.ssp-chip i { font-size: 10px; }

/* Seller card */
.ssp-seller-card {
  display: flex; align-items: flex-start; gap: 14px;
  background: #fdf8f4; border: 1.5px solid #f0ebe5; border-radius: 10px;
  padding: 14px 16px; margin-bottom: 10px;
}
.ssp-seller-icon { font-size: 22px; color: #714e32; flex-shrink: 0; padding-top: 2px; }
.ssp-seller-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .4px; }
.ssp-seller-name  { font-size: 15px; font-weight: 700; color: #1a1209; }
.ssp-seller-type  { font-size: 12px; color: #7a6655; margin-top: 2px; }
.ssp-verified { color: #16a34a; font-weight: 700; }

/* Trust row */
.ssp-trust-row { display: flex; gap: 16px; flex-wrap: wrap; }
.ssp-trust-item {
  display: flex; align-items: center; gap: 6px;
  font-size: 12px; font-weight: 600; color: #7a6655;
}
.ssp-trust-item i { color: #714e32; font-size: 14px; }

/* Info subhead */
.ssp-info-subhead { font-size: 15px; font-weight: 700; color: #1a1209; display: flex; align-items: center; gap: 7px; }
.ssp-info-subhead i { color: #c4956a; }

/* Use cases */
.ssp-use-cases { background: #fdf8f4; border: 1px solid #f0ebe5; border-radius: 10px; padding: 16px 18px; }
.ssp-use-cases p { font-size: 14px; color: #4a3728; margin: 0; }

/* Tags */
.ssp-tags-row { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.ssp-tags-label { font-size: 13px; font-weight: 700; color: #7a6655; }
.ssp-tag-pill {
  display: inline-block; background: #f5f0eb; border: 1px solid #e8ddd5;
  border-radius: 20px; padding: 3px 10px; font-size: 12px; color: #4a3728; font-weight: 500;
}

/* Specs table */
.ssp-specs-table-wrap, .ssp-variant-table-wrap { overflow-x: auto; border-radius: 10px; border: 1px solid #f0ebe5; }
.ssp-specs-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.ssp-specs-table thead th, .ssp-spec-key {
  padding: 11px 18px; background: #fdf8f4;
  font-size: 12.5px; font-weight: 600; color: #4a3728;
  border-bottom: 1px solid #f0ebe5; width: 40%; white-space: nowrap;
}
.ssp-specs-table thead th { width: auto; }
.ssp-spec-val {
  padding: 11px 18px; background: #fff;
  border-bottom: 1px solid #f5f0eb; color: #1a1209;
}
.ssp-specs-table tbody tr:last-child td { border-bottom: none; }
.ssp-specs-table tbody tr:hover td { background: #fdfaf7; }

/* Rating summary */
.ssp-rating-summary {
  display: flex; gap: 30px; flex-wrap: wrap;
  background: #fdf8f4; border: 1px solid #f0ebe5; border-radius: 12px; padding: 24px;
}
.ssp-rating-score { text-align: center; flex-shrink: 0; min-width: 140px; }
.ssp-big-score { font-size: 60px; font-weight: 800; color: #714e32; line-height: 1; }
.ssp-stars { margin: 6px 0 4px; }
.ssp-review-based { font-size: 12px; color: #9ca3af; }
.ssp-rating-bars { flex: 1; min-width: 200px; display: flex; flex-direction: column; gap: 8px; justify-content: center; }
.ssp-bar-row { display: flex; align-items: center; gap: 10px; }
.ssp-bar-label { font-size: 12px; font-weight: 700; color: #4a3728; min-width: 28px; text-align: right; }
.ssp-bar-track { flex: 1; height: 10px; background: #f0ebe5; border-radius: 5px; overflow: hidden; }
.ssp-bar-fill  { height: 100%; background: linear-gradient(90deg, #f59e0b, #fbbf24); border-radius: 5px; transition: width .5s ease; }
.ssp-bar-count { font-size: 12px; color: #9ca3af; min-width: 60px; }

/* Reviewer avatar */
.ssp-reviewer-avatar {
  width: 44px; height: 44px; border-radius: 50%;
  background: linear-gradient(135deg, #714e32, #c4956a);
  color: #fff; font-size: 18px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* Variant selection nudge */
.ssp-variant-nudge {
  font-size: 12.5px; color: #b45309; font-weight: 500;
  display: flex; align-items: center; gap: 4px;
  animation: nudge-fade-in .3s ease;
}
@keyframes nudge-fade-in { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

/* Highlight unpicked variant group */
.ssp-variant-group.needs-selection .ssp-variant-label { color: #b45309; }
.ssp-variant-group.needs-selection .ssp-variant-options { outline: 2px solid #fbbf24; border-radius: 8px; padding: 4px; animation: shake .35s ease; }
@keyframes shake {
  0%,100% { transform: translateX(0); }
  20%      { transform: translateX(-6px); }
  40%      { transform: translateX(6px); }
  60%      { transform: translateX(-4px); }
  80%      { transform: translateX(4px); }
}
</style>
<div class="wrapper">

  @include('partials.header')
  @include('partials.navigation', ['categoriesWithSubs' => $categoriesWithSubs ?? []])

  <div class="body_content_wrapper position-relative">

    {{-- Category nav strip --}}
    @if($product->category)
    <section class="p0 bb1 overflow-hidden">
      <div class="container">
        <div class="row"><div class="col-lg-12">
          <div class="custom_shop_category_nav_list_menu">
            <ul class="mb0 d-flex">
              <li><a href="{{ route('category.show', $product->category->slug) }}">All {{ $product->category->name }}</a></li>
              @if($product->category->subcategories)
                @foreach($product->category->subcategories->take(10) as $subcategory)
                <li><a class="{{ $product->subcategory_id == $subcategory->id ? 'active' : '' }}"
                       href="{{ route('product.index', ['subcategory' => $subcategory->slug]) }}">{{ $subcategory->name }}</a></li>
                @endforeach
              @endif
            </ul>
          </div>
        </div></div>
      </div>
    </section>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         MAIN PRODUCT SECTION
    ═══════════════════════════════════════════════════════════════ --}}
    <section class="shop-single-content pb80 pt0 mt-3">
      <div class="container">
        <div class="row">

          {{-- ── Left: image gallery ─────────────────────────────── --}}
          <div class="col-xl-6 col-xxl-7">
            <div class="shop_single_natabmenu">
              <div class="d-block">
                <div class="tab-content" id="v-pills-tabContent">
                  @forelse($product->images ?? [] as $index => $image)
                  <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                       id="v-pills-image{{ $index }}" role="tabpanel">
                    <div class="shop_single_navmenu_content mb-3 text-center">
                      <a class="product_popup popup-img" href="{{ asset('public/storage/' . $image->image_path) }}">
                        <span class="flaticon-full-screen"></span>
                      </a>
                      <div class="zoomimg_wrapper">
                        <img class="zoom-img" id="zoom_{{ $index }}"
                             src="{{ asset('public/storage/' . $image->image_path) }}"
                             data-zoom-image="{{ asset('public/storage/' . $image->image_path) }}"
                             width="550" alt="{{ $product->name }}"/>
                      </div>
                    </div>
                  </div>
                  @empty
                  <div class="tab-pane fade show active" role="tabpanel">
                    <div class="shop_single_navmenu_content mb-3 text-center">
                      <img src="{{ asset('public/images/placeholder.png') }}" alt="{{ $product->name }}" width="550"/>
                    </div>
                  </div>
                  @endforelse
                </div>
                <div class="nav d-flex nav-pills me-3 mb-3" id="v-pills-tab2" role="tablist">
                  @forelse($product->images ?? [] as $index => $image)
                  <button class="nav-link mb-0 me-3 {{ $index === 0 ? 'active' : '' }}"
                          id="v-pills-image{{ $index }}-tab"
                          data-bs-toggle="pill" data-bs-target="#v-pills-image{{ $index }}"
                          type="button" role="tab">
                    <img src="{{ asset('public/storage/' . $image->image_path) }}" alt="{{ $product->name }}">
                  </button>
                  @empty @endforelse
                </div>
              </div>
            </div>
          </div>

          {{-- ── Right: product info ──────────────────────────────── --}}
          <div class="col-xl-6 col-xxl-5">
            <div class="shop_single_product_details ps-0 ps-xl-4 mt-4 mt-xl-0">

              {{-- Brand / rating / reviews --}}
              <ul class="d-flex align-items-center flex-wrap gap-2 mb-2">
                @if($product->brand)
                <li>
                  <a href="{{ route('brand.show', $product->brand->slug) }}"
                     class="ssp-brand-badge">
                    @if($product->brand->logo)
                      <img src="{{ asset('public/storage/'.$product->brand->logo) }}" alt="{{ $product->brand->name }}" style="height:18px;object-fit:contain;">
                    @else
                      {{ strtoupper($product->brand->name) }}
                    @endif
                  </a>
                </li>
                @endif
                <li>
                  <div class="d-flex align-items-center gap-1">
                    @for($i = 1; $i <= 5; $i++)
                      <i class="fas fa-star{{ $i <= $product->rating ? '' : '-half-alt' }}"
                         style="color:{{ $i <= round($product->rating) ? '#f59e0b' : '#d1d5db' }};font-size:14px;"></i>
                    @endfor
                    <a href="#customerreview" class="ssp-review-count">({{ number_format($product->review_count) }} reviews)</a>
                  </div>
                </li>
                @if($product->sold_count > 0)
                <li><span class="ssp-sold-badge"><i class="fas fa-fire" style="color:#f97316;"></i> {{ number_format($product->sold_count) }} sold</span></li>
                @endif
              </ul>

              <h4 class="title mb10">{{ $product->name }}</h4>

              @if($product->model_number)
              <div class="ssp-model-num">Model: <strong>{{ $product->model_number }}</strong></div>
             <div class="ssp-model-num">Condition: <strong>{{ strtoupper($product->condition) }}</strong></div>
              @endif

              <hr>

              {{-- Price display — updated dynamically when variant selected --}}
              <div class="sspd_price mb20 mt15" id="price-display">
                @if($product->sale_price && $product->sale_price < $product->price)
                  <span id="effective-price">₦{{ number_format($product->sale_price, 2) }}</span>
                  <small><del class="mr10" id="original-price">₦{{ number_format($product->price, 2) }}</del></small>
                  @if($product->discount_percentage)
                    <span class="ssp-discount-badge">-{{ $product->discount_percentage }}% OFF</span>
                  @endif
                @else
                  <span id="effective-price">₦{{ number_format($product->price, 2) }}</span>
                @endif
              </div>

              {{-- Variant price note --}}
              <div id="variant-price-note" style="display:none;" class="ssp-variant-note">
                <i class="fas fa-tag"></i> <span id="variant-note-text"></span>
              </div>

              @if($product->short_description)
              <div class="shop_single_description mb15">
                <p>{{ $product->short_description }}</p>
              </div>
              @endif

              <hr>

              {{-- ── Variant selectors (from product_variants table) ─── --}}
              @php
                $productVariants = \App\Models\ProductVariant::where('product_id', $product->id)->get();
                $variantGroups   = $productVariants->groupBy('variant_name');
              @endphp

              @if($variantGroups->isNotEmpty())
              <div class="ssp-variants-section mb20" id="variants-section">
                @foreach($variantGroups as $variantName => $variantRows)
                @php
                  $hasAdj = $variantRows->contains(fn($r) => $r->price_adjustment !== null && $r->price_adjustment != 0);
                @endphp
                <div class="ssp-variant-group mb15">
                  <div class="ssp-variant-label">
                    {{ $variantName }}:
                    <strong class="ssp-selected-value ms-1" id="selected-{{ Str::slug($variantName) }}">—</strong>
                  </div>
                  <div class="ssp-variant-options">
                    @foreach($variantRows as $variantRow)
                    <button type="button"
                            class="ssp-variant-btn"
                            data-variant-name="{{ $variantName }}"
                            data-variant-value="{{ $variantRow->variant_value }}"
                            data-price-adjustment="{{ $variantRow->price_adjustment ?? 0 }}"
                            data-stock="{{ $variantRow->stock }}"
                            onclick="selectVariant(this)">
                      {{ $variantRow->variant_value }}
                      @if($hasAdj && $variantRow->price_adjustment && $variantRow->price_adjustment != 0)
                        <small class="ssp-adj-hint">{{ $variantRow->price_adjustment > 0 ? '+' : '' }}₦{{ number_format($variantRow->price_adjustment, 0) }}</small>
                      @endif
                    </button>
                    @endforeach
                  </div>
                </div>
                @endforeach
              </div>
              @endif

              {{-- ── Stock status ─────────────────────────────────── --}}
              <div class="ssp-stock-row mb15">
                @if($product->stock > 10)
                  <span class="ssp-stock-badge ssp-in-stock"><i class="fas fa-check-circle"></i> In Stock ({{ $product->stock }} available)</span>
                @elseif($product->stock > 0)
                  <span class="ssp-stock-badge ssp-low-stock"><i class="fas fa-exclamation-triangle"></i> Only {{ $product->stock }} left!</span>
                @else
                  <span class="ssp-stock-badge ssp-out-stock"><i class="fas fa-times-circle"></i> Out of Stock</span>
                @endif
                @if($product->condition && $product->condition !== 'new')
                  <span class="ssp-condition-badge">{{ ucfirst($product->condition) }}</span>
                @endif
              </div>

              {{-- ── Add to cart controls ─────────────────────────── --}}
              @php $variantGroupCount = $variantGroups->count(); @endphp
              <div class="mb-0">
                <ul class="cart_btn_widget shop_single2_style align-items-center mb-0">
                  <li class="list-inline-item me-3 mb-2">
                    <div class="cart_btn home_page_sidebar d-grid">
                      <div class="quantity-block home_page_sidebar">
                        <button class="quantity-arrow-minus2 shop_single_page_sidebar">
                          <img src="{{ asset('public/images/icons/minus.svg') }}" alt="">
                        </button>
                        <input class="quantity-num2 shop_single_page_sidebar" type="number"
                               value="1" min="1" max="{{ $product->stock }}">
                        <button class="quantity-arrow-plus2 shop_single_page_sidebar">
                          <span class="flaticon-close"></span>
                        </button>
                      </div>
                    </div>
                  </li>
                  <li class="list-inline-item me-3 mb-3">
                    @if($variantGroupCount > 0)
                      {{-- Disabled until all variant groups are chosen --}}
                      <a href="javascript:void(0)"
                         id="add-to-cart-btn"
                         class="btn btn-thm bdrs60 add-to-cart"
                         data-product-id="{{ $product->id }}"
                         data-variant-groups="{{ $variantGroupCount }}"
                         disabled
                         aria-disabled="true"
                         style="opacity:.45;cursor:not-allowed;pointer-events:none;">
                        <i class="fas fa-shopping-cart me-1"></i>Add to cart
                      </a>
                      {{-- Nudge shown below variants until selection is complete --}}
                      <div id="variant-select-nudge" class="ssp-variant-nudge mt-2">
                        <i class="fas fa-arrow-up me-1"></i>
                        <span id="variant-nudge-text">Please select your options above</span>
                      </div>
                    @else
                      <a href="javascript:void(0)"
                         id="add-to-cart-btn"
                         class="btn btn-thm bdrs60 add-to-cart"
                         data-product-id="{{ $product->id }}"
                         data-variant-groups="0">
                        <i class="fas fa-shopping-cart me-1"></i>Add to cart
                      </a>
                    @endif
                  </li>
                </ul>
              </div>

              <hr class="mt-0 mb15">

              {{-- Quick info chips --}}
              <div class="ssp-info-chips mb15">
                @if($product->category)
                <a href="{{ route('category.show', $product->category->slug) }}" class="ssp-chip">
                  <i class="fas fa-tag"></i> {{ $product->category->name }}
                </a>
                @endif
                @if($product->subcategory)
                <span class="ssp-chip ssp-chip-light">
                  <i class="fas fa-layer-group"></i> {{ $product->subcategory->name }}
                </span>
                @endif
                @if($product->brand)
                <a href="{{ route('brand.show', $product->brand->slug) }}" class="ssp-chip">
                  <i class="fas fa-trademark"></i> {{ $product->brand->name }}
                </a>
                @endif
                @if($product->target_audience && $product->target_audience !== 'all')
                <span class="ssp-chip ssp-chip-light">
                  <i class="fas fa-users"></i> {{ ucfirst($product->target_audience) }}
                </span>
                @endif
              </div>

              <hr class="mt-0">

              {{-- Seller info --}}
              @if($product->shop)
              <div class="ssp-seller-card">
                <div class="ssp-seller-icon">
                  <span class="flaticon-shop"></span>
                </div>
                <div class="ssp-seller-info">
                  <div class="ssp-seller-label">Sold by</div>
                  <div class="ssp-seller-name">{{ $product->shop->shop_name }}</div>
                  @if($product->shop->seller)
                    <div class="ssp-seller-type">
                      @if($product->shop->seller->verification_status === 'verified')
                        <span class="ssp-verified"><i class="fas fa-check-circle"></i> Verified {{ ucfirst($product->shop->seller->business_type ?? '') }}</span>
                      @endif
                    </div>
                  @endif
                </div>
              </div>
              @endif

              {{-- Trust badges --}}
              <div class="ssp-trust-row mt15">
                <div class="ssp-trust-item"><i class="fas fa-shield-alt"></i><span>Secure Payment</span></div>
                <div class="ssp-trust-item"><i class="fas fa-undo"></i><span>Easy Returns</span></div>
                <div class="ssp-trust-item"><i class="fas fa-truck"></i><span>Fast Delivery</span></div>
              </div>

            </div>{{-- /shop_single_product_details --}}
          </div>

        </div>{{-- /row --}}

        <hr>

        {{-- ═══════════════════════════════════════════════════════════════
             TABS: Description · Specifications · Reviews
        ═══════════════════════════════════════════════════════════════ --}}
        <div class="row mt50">
          <div class="col-lg-12">
            <div class="shop_single3_style ui_kit_tab style2">

              <ul class="nav nav-tabs mb15" id="myTab" role="tablist">
                <li class="nav-item">
                  <button class="nav-link mt-3 mt-xl-0 mb-0 me-3 me-xl-5 active"
                          data-bs-toggle="tab" data-bs-target="#description" type="button">Description</button>
                </li>
                @if($product->specifications_array ?? $product->specifications)
                <li class="nav-item">
                  <button class="nav-link mt-3 mt-xl-0 mb-0 me-3 me-xl-5"
                          data-bs-toggle="tab" data-bs-target="#specifications" type="button">Specifications</button>
                </li>
                @endif
                <li class="nav-item">
                  <button class="nav-link mt-3 mt-xl-0 mb-0 me-3 me-xl-5"
                          data-bs-toggle="tab" data-bs-target="#customerreview" type="button">
                    Reviews ({{ number_format($product->review_count) }})
                  </button>
                </li>
              </ul>

              <div class="tab-content pt20 row" id="myTabContent">

                {{-- ── Description tab ─────────────────────────────── --}}
                <div class="tab-pane fade show active col-lg-12" id="description">
                  <div class="shop_single_description mb30">
                    {!! nl2br(e($product->description)) !!}
                  </div>

                  @if($product->use_cases)
                  <div class="ssp-use-cases">
                    <h6 class="ssp-info-subhead"><i class="fas fa-lightbulb"></i> Use Cases</h6>
                    <p>{{ $product->use_cases }}</p>
                  </div>
                  @endif

                  {{-- Tags --}}
                  @if($product->tags_array ?? $product->tags)
                  <div class="ssp-tags-row mt20">
                    <span class="ssp-tags-label"><i class="fas fa-hashtag"></i> Tags:</span>
                    @php $tagsArr = is_array($product->tags) ? $product->tags : array_map('trim', explode(',', $product->tags ?? '')); @endphp
                    @foreach(array_filter($tagsArr) as $tag)
                      <span class="ssp-tag-pill">{{ $tag }}</span>
                    @endforeach
                  </div>
                  @endif
                </div>

                {{-- ── Specifications tab ───────────────────────────── --}}
                @php
                  $specs = $product->specifications_array ?? $product->specifications ?? [];
                @endphp
                @if(!empty($specs))
                <div class="tab-pane fade col-lg-12" id="specifications">
                  <div class="ssp-specs-table-wrap">
                    <table class="ssp-specs-table">
                      <tbody>
                        @foreach($specs as $key => $value)
                        <tr>
                          <td class="ssp-spec-key">{{ $key }}</td>
                          <td class="ssp-spec-val">{{ $value }}</td>
                        </tr>
                        @endforeach
                      </tbody>
                    </table>
                  </div>

                  {{-- Also show variant table if variants exist --}}
                  @if(isset($productVariants) && $productVariants->isNotEmpty())
                  <div class="mt30">
                    <h6 class="ssp-info-subhead"><i class="fas fa-sliders-h"></i> Available Variants</h6>
                    <div class="ssp-variant-table-wrap">
                      <table class="ssp-specs-table">
                        <thead>
                          <tr>
                            <th class="ssp-spec-key" style="font-weight:700;color:#714e32;">Option</th>
                            <th class="ssp-spec-val" style="font-weight:700;color:#714e32;">Value</th>
                            <th class="ssp-spec-val" style="font-weight:700;color:#714e32;">Price</th>
                          </tr>
                        </thead>
                        <tbody>
                          @foreach($productVariants as $v)
                          <tr>
                            <td class="ssp-spec-key">{{ $v->variant_name }}</td>
                            <td class="ssp-spec-val">{{ $v->variant_value }}</td>
                            <td class="ssp-spec-val">
                              @if($v->price_adjustment && $v->price_adjustment != 0)
                                @php
                                  $base = $product->sale_price ?? $product->price;
                                  $adj  = (float) $v->price_adjustment;
                                  $final = max(0, $base + $adj);
                                @endphp
                                ₦{{ number_format($final, 2) }}
                                <small style="color:{{ $adj > 0 ? '#16a34a' : '#dc2626' }};font-size:11px;">
                                  ({{ $adj > 0 ? '+' : '' }}₦{{ number_format($adj, 2) }})
                                </small>
                              @else
                                ₦{{ number_format($product->sale_price ?? $product->price, 2) }}
                              @endif
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                  @endif
                </div>
                @endif

                {{-- ── Reviews tab ──────────────────────────────────── --}}
                <div class="tab-pane fade col-xl-12" id="customerreview">
                  <div class="row">
                    <div class="col-lg-12">

                      {{-- Rating summary --}}
                      @if($product->review_count > 0)
                      <div class="ssp-rating-summary mb30">
                        <div class="ssp-rating-score">
                          <div class="ssp-big-score">{{ number_format($product->rating, 1) }}</div>
                          <div class="ssp-stars">
                            @for($i = 1; $i <= 5; $i++)
                              <i class="fas fa-star" style="color:{{ $i <= round($product->rating) ? '#f59e0b' : '#d1d5db' }};font-size:18px;"></i>
                            @endfor
                          </div>
                          <div class="ssp-review-based">Based on {{ number_format($product->review_count) }} reviews</div>
                          @auth
                            @if($canReview)
                            <button class="btn btn-thm btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#reviewModal">
                              <i class="fa fa-edit me-1"></i>Write a Review
                            </button>
                            @endif
                          @else
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm mt-3">Login to Review</a>
                          @endauth
                        </div>
                        <div class="ssp-rating-bars">
                          @foreach($ratingBreakdown as $star => $count)
                          @php $pct = $product->review_count > 0 ? ($count / $product->review_count) * 100 : 0; @endphp
                          <div class="ssp-bar-row">
                            <div class="ssp-bar-label">{{ $star }} <i class="fas fa-star" style="color:#f59e0b;font-size:11px;"></i></div>
                            <div class="ssp-bar-track">
                              <div class="ssp-bar-fill" style="width:{{ $pct }}%"></div>
                            </div>
                            <div class="ssp-bar-count">{{ $count }} <small>({{ number_format($pct, 0) }}%)</small></div>
                          </div>
                          @endforeach
                        </div>
                      </div>
                      @endif

                      {{-- Reviews list --}}
                      <div class="reviews-list-section">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                          <h5 class="mb-0">Customer Reviews ({{ number_format($product->review_count) }})</h5>
                          <select class="form-select form-select-sm" style="width:auto;" id="reviewSort">
                            <option value="latest">Most Recent</option>
                            <option value="highest">Highest Rating</option>
                            <option value="lowest">Lowest Rating</option>
                          </select>
                        </div>

                        <div id="reviewsList">
                          @forelse($product->approvedReviews()->paginate(10) as $review)
                          <div class="review-card mb-4 p-4 border rounded" data-review-id="{{ $review->id }}">
                            <div class="d-flex align-items-start mb-3">
                              <div class="ssp-reviewer-avatar me-3">
                                {{ strtoupper(substr($review->user->name, 0, 1)) }}
                              </div>
                              <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap">
                                  <div>
                                    <h6 class="mb-1">{{ $review->user->name }}</h6>
                                    @if($review->is_verified_purchase)
                                      <span class="badge bg-success mb-2" style="font-size:11px;">
                                        <i class="fa fa-check-circle me-1"></i>Verified Purchase
                                      </span>
                                    @endif
                                  </div>
                                  <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                                </div>
                                <div class="mb-2">
                                  @for($i = 1; $i <= 5; $i++)
                                    <i class="fa fa-star" style="color:{{ $i <= $review->rating ? '#f59e0b' : '#d1d5db' }};font-size:14px;"></i>
                                  @endfor
                                </div>
                              </div>
                            </div>
                            <p class="mb-3">{{ $review->comment }}</p>
                            @if($review->images->count() > 0)
                            <div class="d-flex gap-2 mt-2 flex-wrap mb-3">
                              @foreach($review->images as $image)
                              <img src="{{ asset('public/storage/' . $image->image_path) }}"
                                   class="rounded img-thumbnail"
                                   style="width:72px;height:72px;object-fit:cover;cursor:pointer;"
                                   onclick="openImageModal('{{ asset('public/storage/' . $image->image_path) }}')">
                              @endforeach
                            </div>
                            @endif
                            <div class="review-actions mb-2">
                              <span class="text-muted me-2" style="font-size:13px;">Was this helpful?</span>
                              @auth
                              <button class="btn btn-sm btn-outline-secondary helpful-btn"
                                      data-review-id="{{ $review->id }}" data-helpful="1">
                                <i class="fa fa-thumbs-up"></i> Yes (<span class="helpful-count">{{ $review->helpful_count }}</span>)
                              </button>
                              <button class="btn btn-sm btn-outline-secondary helpful-btn ms-2"
                                      data-review-id="{{ $review->id }}" data-helpful="0">
                                <i class="fa fa-thumbs-down"></i> No (<span class="not-helpful-count">{{ $review->not_helpful_count }}</span>)
                              </button>
                              @else
                              <span class="text-muted" style="font-size:13px;"><i class="fa fa-thumbs-up me-1"></i>{{ $review->helpful_count }} found helpful</span>
                              @endauth
                            </div>
                            @if($review->seller_response)
                            <div class="mt-3 p-3 rounded border-start border-4 border-warning"
                                 style="background:#fdf8f4;">
                              <strong style="color:#714e32;font-size:13px;">
                                <i class="fas fa-store me-1"></i>Response from {{ $product->shop->shop_name ?? 'Seller' }}:
                              </strong>
                              <p class="mb-1 mt-1" style="font-size:13.5px;">{{ $review->seller_response }}</p>
                              <small class="text-muted">{{ $review->seller_responded_at->diffForHumans() }}</small>
                            </div>
                            @endif
                          </div>
                          @empty
                          <div class="text-center py-5 border rounded" style="background:#fdf8f4;">
                            <i class="fa fa-star text-muted" style="font-size:42px;opacity:.3;"></i>
                            <p class="text-muted mt-3 mb-3">No reviews yet. Be the first to review this product!</p>
                            @auth @if($canReview)
                            <button class="btn btn-thm" data-bs-toggle="modal" data-bs-target="#reviewModal">
                              <i class="fa fa-edit me-1"></i>Write the First Review
                            </button>
                            @endif @endauth
                          </div>
                          @endforelse
                        </div>

                        @if($product->approvedReviews()->count() > 10)
                        <div class="mt-4">{{ $product->approvedReviews()->paginate(10)->links() }}</div>
                        @endif
                      </div>

                    </div>
                  </div>
                </div>

              </div>{{-- /tab-content --}}
            </div>
          </div>
        </div>

        {{-- Recently viewed --}}
        @include('partials.recently-viewed', ['recentlyViewed' => $recentlyViewed])

        {{-- Related products --}}
        @if($relatedProducts && $relatedProducts->count() > 0)
        <div class="row mt50">
          <div class="col-lg-12">
            <div class="main-title"><h2 class="title">Related products</h2></div>
            <div class="navi_pagi_top_right related_product_slider slider_dib_sm shop_item_6grid_slider owl-theme owl-carousel">
              @foreach($relatedProducts as $rp)
              <div class="item">
                <div class="shop_item small_style bdr1 px-2 px-sm-3 mx--1">
                  <div class="thumb pb30">
                    <img class="w100" src="{{ asset('public/storage/' . $rp->main_image) }}" alt="{{ $rp->name }}">
                    @if($rp->discount_percentage)
                    <div class="thumb_tag">-{{ $rp->discount_percentage }}%</div>
                    @endif
                    <div class="thumb_info">
                      <ul class="mb0">
                        <li><a href="{{ route('product.show', $rp->slug) }}"><span class="flaticon-show"></span></a></li>
                      </ul>
                    </div>
                    <div class="shop_item_cart_btn d-grid">
                      <a href="javascript:void(0)" class="btn btn-thm add-to-cart" data-product-id="{{ $rp->id }}">Add to Cart</a>
                    </div>
                  </div>
                  <div class="details">
                    @if($rp->brand)<div class="sub_title">{{ $rp->brand->name }}</div>@endif
                    <div class="title"><a href="{{ route('product.show', $rp->slug) }}">{{ Str::limit($rp->name, 80) }}</a></div>
                    <div class="si_footer">
                      <div class="price">
                        @if($rp->sale_price && $rp->sale_price < $rp->price)
                          ₦{{ number_format($rp->sale_price, 2) }} <small><del>₦{{ number_format($rp->price, 2) }}</del></small>
                        @else
                          ₦{{ number_format($rp->price, 2) }}
                        @endif
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        @endif

      </div>{{-- /container --}}
    </section>

    {{-- Review modal --}}
    @auth
    @if($canReview)
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
          <div class="modal-header" style="background:#fdf8f4;border-bottom:1px solid #f0ebe5;">
            <h5 class="modal-title fw-bold" id="reviewModalLabel"><i class="fa fa-edit me-2"></i>Write a Review</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form id="reviewForm" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
              <input type="hidden" name="product_id" value="{{ $product->id }}">
              <input type="hidden" name="order_id" value="{{ $eligibleOrderItem->order_id ?? '' }}">
              <input type="hidden" name="order_item_id" value="{{ $eligibleOrderItem->id ?? '' }}">
              <div class="alert alert-light border mb-3">
                <strong>Reviewing:</strong> {{ $product->name }}<br>
                <small class="text-muted">Order: #{{ $eligibleOrderItem->order->order_number ?? 'N/A' }}</small>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Your Rating *</label>
                <div id="starRatingInput" class="d-flex gap-1">
                  @for($i = 5; $i >= 1; $i--)
                    <i class="fa fa-star text-warning star-input" data-rating="{{ $i }}"
                       style="font-size:28px;cursor:pointer;" onclick="setRating({{ $i }})"></i>
                  @endfor
                </div>
                <input type="hidden" id="review-rating" name="rating" value="5">
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Your Review *</label>
                <textarea name="comment" class="form-control" rows="5" required minlength="10" maxlength="1000"
                          placeholder="Share your experience with this product…"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Add Photos (Optional)</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*" id="reviewImages" onchange="previewImages(this)">
                <small class="text-muted">Up to 5 images (max 2MB each)</small>
                <div id="imagePreview" class="mt-2 d-flex gap-2 flex-wrap"></div>
              </div>
              <div class="alert alert-warning py-2" style="font-size:13px;">
                <i class="fa fa-info-circle me-1"></i>Your review will appear after admin approval (24–48 hours).
              </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #f0ebe5;">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-thm"><i class="fa fa-paper-plane me-1"></i>Submit Review</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endif
    @endauth

    {{-- Image lightbox modal --}}
    <div class="modal fade" id="imageViewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
          <div class="modal-header" style="border-bottom:1px solid #f0ebe5;">
            <h5 class="modal-title">Review Image</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body text-center">
            <img id="modalImage" src="" alt="Review image" class="img-fluid">
          </div>
        </div>
      </div>
    </div>

    @include('partials.footer')
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>





  <script src="{{ asset('public/js/jquery-3.6.0.js') }}"></script>
<script>
$(document).ready(function() {

  // ── Track product view ──────────────────────────────────────────────────
  trackRecentlyViewed({{ $product->id }});

  // ── Qty controls ────────────────────────────────────────────────────────
  $('.quantity-arrow-minus2').on('click', function() {
    const input = $(this).siblings('.quantity-num2');
    const v = parseInt(input.val()) || 1;
    if (v > 1) input.val(v - 1);
  });
  $('.quantity-arrow-plus2').on('click', function() {
    const input = $(this).siblings('.quantity-num2');
    const v = parseInt(input.val()) || 1;
    const max = parseInt(input.attr('max')) || 9999;
    if (v < max) input.val(v + 1);
  });



});

// ── Variant selection & price update ───────────────────────────────────────
const BASE_PRICE        = {{ (float)($product->price) }};
const EFFECTIVE_PRICE   = {{ (float)($product->sale_price && $product->sale_price < $product->price ? $product->sale_price : $product->price) }};
const ON_SALE           = {{ ($product->sale_price && $product->sale_price < $product->price) ? 'true' : 'false' }};
const TOTAL_VARIANT_GROUPS = {{ $variantGroupCount ?? 0 }};

let selectedVariants = {}; // { variantName: { value, priceAdj } }

function selectVariant(btn) {
  const name  = btn.dataset.variantName;
  const value = btn.dataset.variantValue;
  const adj   = parseFloat(btn.dataset.priceAdjustment) || 0;

  // Deselect siblings, select this
  document.querySelectorAll(`.ssp-variant-btn[data-variant-name="${name}"]`)
    .forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');

  // Clear shake highlight on this group now that it's been picked
  btn.closest('.ssp-variant-group')?.classList.remove('needs-selection');

  // Update label
  const labelEl = document.getElementById('selected-' + slugify(name));
  if (labelEl) labelEl.textContent = value;

  selectedVariants[name] = { value, adj };
  updatePrice();
  updateCartButton();
}

function updateCartButton() {
  if (TOTAL_VARIANT_GROUPS === 0) return; // no variants — button always active

  const cartBtn = document.getElementById('add-to-cart-btn');
  const nudge   = document.getElementById('variant-select-nudge');
  const nudgeText = document.getElementById('variant-nudge-text');
  if (!cartBtn) return;

  const selectedCount = Object.keys(selectedVariants).length;
  const remaining     = TOTAL_VARIANT_GROUPS - selectedCount;

  if (remaining <= 0) {
    // All groups selected — unlock button
    cartBtn.removeAttribute('disabled');
    cartBtn.removeAttribute('aria-disabled');
    cartBtn.style.opacity      = '';
    cartBtn.style.cursor       = '';
    cartBtn.style.pointerEvents = '';
    if (nudge) nudge.style.display = 'none';
  } else {
    // Still unselected groups
    cartBtn.setAttribute('disabled', true);
    cartBtn.setAttribute('aria-disabled', 'true');
    cartBtn.style.opacity       = '.45';
    cartBtn.style.cursor        = 'not-allowed';
    cartBtn.style.pointerEvents = 'none';
    if (nudge) {
      nudge.style.display = '';
      const label = remaining === 1
        ? `Select 1 more option above to continue`
        : `Select ${remaining} more options above to continue`;
      if (nudgeText) nudgeText.textContent = label;
    }
  }
}

// Initialise button state on page load
document.addEventListener('DOMContentLoaded', updateCartButton);

function updatePrice() {
  // Total adjustment = sum of all selected variant price adjustments
  const totalAdj = Object.values(selectedVariants).reduce((sum, v) => sum + (v.adj || 0), 0);
  const newPrice = Math.max(0, EFFECTIVE_PRICE + totalAdj);

  document.getElementById('effective-price').textContent = '₦' + numberFormat(newPrice);

  const noteEl = document.getElementById('variant-price-note');
  const noteText = document.getElementById('variant-note-text');

  if (totalAdj !== 0) {
    noteEl.style.display = 'inline-flex';
    const sign = totalAdj > 0 ? '+' : '';
    noteText.textContent = `${sign}₦${numberFormat(Math.abs(totalAdj))} variant adjustment applied`;
  } else {
    noteEl.style.display = 'none';
  }
}

function slugify(str) {
  return str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
}

function numberFormat(n) {
  return Number(n).toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ── Review form ─────────────────────────────────────────────────────────────
function setRating(rating) {
  document.getElementById('review-rating').value = rating;
  document.querySelectorAll('.star-input').forEach(star => {
    const r = parseInt(star.getAttribute('data-rating'));
    star.style.color = r <= rating ? '#f59e0b' : '#d1d5db';
  });
}
document.addEventListener('DOMContentLoaded', () => setRating(5));

function previewImages(input) {
  const preview = document.getElementById('imagePreview');
  preview.innerHTML = '';
  if (input.files) {
    Array.from(input.files).slice(0, 5).forEach((file, index) => {
      const reader = new FileReader();
      reader.onload = function(e) {
        const div = document.createElement('div');
        div.className = 'position-relative';
        div.innerHTML = `<img src="${e.target.result}" class="rounded border" style="width:72px;height:72px;object-fit:cover;">`;
        preview.appendChild(div);
      };
      reader.readAsDataURL(file);
    });
  }
}

function openImageModal(src) {
  document.getElementById('modalImage').src = src;
  new bootstrap.Modal(document.getElementById('imageViewModal')).show();
}

document.getElementById('reviewForm')?.addEventListener('submit', async function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  const btn = this.querySelector('button[type="submit"]');
  const orig = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Submitting…';
  try {
    const res    = await fetch('{{ route("reviews.submit") }}', {
      method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: formData
    });
    const result = await res.json();
    if (res.ok) {
      bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
      showToast('success', result.message || 'Review submitted!');
      setTimeout(() => location.reload(), 1200);
    } else {
      showToast('error', result.error || 'Error submitting review.');
      btn.disabled = false; btn.innerHTML = orig;
    }
  } catch (err) {
    showToast('error', 'An error occurred. Please try again.');
    btn.disabled = false; btn.innerHTML = orig;
  }
});

// Helpful votes
document.querySelectorAll('.helpful-btn').forEach(btn => {
  btn.addEventListener('click', async function() {
    const reviewId = this.dataset.reviewId;
    const helpful  = this.dataset.helpful === '1';
    try {
      const res    = await fetch(`/reviews/${reviewId}/helpful`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ helpful })
      });
      const result = await res.json();
      if (res.ok) {
        const card = this.closest('.review-card');
        card.querySelector('.helpful-count').textContent     = result.helpful_count;
        card.querySelector('.not-helpful-count').textContent = result.not_helpful_count;
      }
    } catch (e) {}
  });
});

// ── CART SIDEBAR DEBUG ──────────────────────────────────────────
$(document).ready(function() {

    console.log('=== CART SIDEBAR DEBUG ===');

    // 1. Is the cart-filter-btn found?
    console.log('cart-filter-btn found:', $('.cart-filter-btn').length);

    // 2. Is cart-sidebar-items found?
    console.log('cart-sidebar-items found:', $('#cart-sidebar-items').length);

    // 3. Is loadCartSidebar defined?
    console.log('loadCartSidebar defined:', typeof loadCartSidebar);

    // 4. Is updateCartUI defined?
    console.log('updateCartUI defined:', typeof updateCartUI);

    // 5. What does the sidebar container look like?
    const sidebar = $('#cart-sidebar-items');
    if (sidebar.length) {
        console.log('sidebar parent:', sidebar.parent()[0]);
        console.log('sidebar CSS display:', sidebar.css('display'));
        console.log('sidebar CSS visibility:', sidebar.css('visibility'));
        console.log('sidebar CSS opacity:', sidebar.css('opacity'));
        console.log('sidebar CSS overflow:', sidebar.css('overflow'));
        console.log('sidebar CSS z-index:', sidebar.css('z-index'));
        console.log('sidebar CSS position:', sidebar.css('position'));
        console.log('sidebar offset:', sidebar.offset());
        console.log('sidebar width/height:', sidebar.width(), sidebar.height());
    }

    // 6. Manually trigger the sidebar click and watch what happens
    $('.cart-filter-btn').on('click', function(e) {
        console.log('cart-filter-btn CLICKED');
        console.log('default prevented?', e.isDefaultPrevented());
        console.log('propagation stopped?', e.isPropagationStopped());
    });

    // 7. Check what element actually opens the sidebar panel
    // (offcanvas, custom drawer, etc.)
    console.log('offcanvas elements:', $('[class*="offcanvas"]').length);
    console.log('sidebar panel elements:', $('[class*="sidebar"]').length);
    console.log('cart panel id:', $('#cart-panel').length, $('#cartSidebar').length, $('#cart_sidebar').length);

    // 8. Check for any elements with data-bs-toggle
    $('[data-bs-toggle]').each(function() {
        console.log('bs-toggle element:', $(this).attr('class'), '→', $(this).data('bs-toggle'));
    });
});
</script>

@endsection
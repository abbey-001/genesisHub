{{--
    Reusable Product Card
    Usage: @include('products.partials.product-card', ['product' => $product])

    What changed:
    - Removed wishlist + compare hover buttons (only View remains)
    - Added condition badge (New / Used / Refurbished / Open Box)
    - Added "Company" seller tag when shop.is_company is true
    - Added "Perfect Rating" badge when rating == 5 and review_count >= 3
--}}
@php
    $shop       = $product->shop ?? null;
    $seller     = $shop?->seller ?? null;          // Seller model (has business_type)

    $businessType = $seller?->business_type ?? null;   // 'individual', 'company', 'partnership'
    $isCompany    = $businessType === 'company';
    $isPartnership = $businessType === 'partnership';

    $isPerfect  = ($product->rating ?? 0) >= 5 && ($product->review_count ?? 0) >= 3;
    $condition  = $product->condition ?? null;

    $conditionLabels = [
        'new'         => ['label' => 'New',         'color' => '#16a34a', 'bg' => '#dcfce7'],
        'used'        => ['label' => 'Used',         'color' => '#7c3aed', 'bg' => '#ede9fe'],
        'refurbished' => ['label' => 'Refurbished',  'color' => '#d97706', 'bg' => '#fef3c7'],
        'open_box'    => ['label' => 'Open Box',     'color' => '#0369a1', 'bg' => '#e0f2fe'],
    ];
    $conditionInfo = $condition
        ? ($conditionLabels[strtolower($condition)] ?? ['label' => ucfirst($condition), 'color' => '#6b7280', 'bg' => '#f3f4f6'])
        : null;
        
@endphp

<div class="product-card">

  {{-- Image block --}}
  <div class="card-img-wrap">
    <a href="{{ route('product.show', $product->id) }}">
      <img
        src="{{ asset('public/storage/' . $product->main_image) }}"
        alt="{{ $product->name }}"
        loading="lazy"
      >
    </a>

    {{-- Sale / stock badges (top-left) --}}
    <div class="badge-wrap">
      @if($product->discount_percentage)
        <span class="badge-discount">-{{ $product->discount_percentage }}%</span>
      @endif
      @if($product->stock <= 5 && $product->stock > 0)
        <span class="badge-stock">{{ $product->stock }} left</span>
      @elseif($product->stock === 0)
        <span class="badge-oos">Out of Stock</span>
      @endif
    </div>

    {{-- Perfect 5-star badge (top-right, image overlay) --}}
    @if($isPerfect)
      <div class="badge-perfect" title="{{ $product->review_count }} reviews · perfect score">
        <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        5.0
      </div>
    @endif

  </div>

  {{-- Card body --}}
  <div class="card-body">

    @if(isset($product->brand) && $product->brand)
      <div class="card-brand">{{ $product->brand->name }}</div>
    @endif

    <div class="card-name">
      <a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a>
    </div>

    {{-- Condition + Company + Perfect Rating tags --}}
    @if($conditionInfo || $isCompany || $isPerfect)
    <div class="card-meta-tags">
      @if($conditionInfo)
        <span class="tag-condition" style="color:{{ $conditionInfo['color'] }};background:{{ $conditionInfo['bg'] }};">
          {{ $conditionInfo['label'] }}
        </span>
      @endif

      @if($isCompany)
        <span class="tag-company" title="Sold by a verified company">
          <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 21h18M3 7v14M21 7v14M3 7h18M9 21V11h6v10"/></svg>
          Company
        </span>
      @endif

      @if($isPerfect)
        <span class="tag-perfect">
          <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          Perfect Rating
        </span>
      @endif
    </div>
    @endif

    {{-- Stars --}}
    <div class="card-rating">
      <div class="lp-stars">
        @for($i = 1; $i <= 5; $i++)
          <i class="fas fa-star{{ $i <= round($product->rating ?? 0) ? '' : ' empty' }}"
             style="{{ $i <= round($product->rating ?? 0) ? '' : 'color:#d1d5db;' }}"></i>
        @endfor
      </div>
      <span class="rating-count">({{ number_format($product->review_count ?? 0) }})</span>
    </div>

    {{-- Price --}}
    <div class="card-price">
      @if($product->sale_price && $product->sale_price < $product->price)
        <span class="price-current">₦{{ number_format($product->sale_price, 2) }}</span>
        <span class="price-original">₦{{ number_format($product->price, 2) }}</span>
        <span class="price-save">Save ₦{{ number_format($product->price - $product->sale_price, 2) }}</span>
      @else
        <span class="price-current">₦{{ number_format($product->price, 2) }}</span>
      @endif
    </div>

    @if(($product->sold_count ?? 0) > 50)
      <div class="card-sold">
        <i class="fas fa-fire"></i>{{ number_format($product->sold_count) }} sold
      </div>
    @endif

  </div>
</div>
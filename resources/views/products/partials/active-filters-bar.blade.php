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
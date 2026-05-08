@extends('layouts.app')

@section('title', $pageTitle ?? 'Shop Products')

@section('content')
<div class="wrapper ovh bgc-gmart-gray">
  
  {{-- Desktop Header --}}
  @include('partials.header')
  
  {{-- Navigation --}}
  @include('partials.navigation')
  
  <div class="body_content_wrapper position-relative">
    
    <!-- Category Navigation -->
    @if(isset($category) && $category->subcategories->isNotEmpty())
    <section class="p0 bb1 overflow-hidden">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="custom_shop_category_nav_list_menu">
              <ul class="mb0 d-flex flex-nowrap overflow-auto">
                <li>
                  <a href="{{ route('category.show', $category->slug) }}" 
                     class="{{ !request('subcategory') ? 'active' : '' }}">
                    All {{ $category->name }}
                  </a>
                </li>
                @foreach($category->subcategories->take(10) as $subcategory)
                <li>
                  <a href="{{ route('category.show', [$category->slug, 'subcategory' => $subcategory->slug]) }}"
                     class="{{ request('subcategory') == $subcategory->slug ? 'active' : '' }}">
                    {{ $subcategory->name }}
                  </a>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

    <!-- Main Listing Section -->
    <section class="our-listing pt10 pb90">
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="main-title bb1 pb10 mb20">
              <h2 class="title">{{ $pageTitle ?? 'All Products' }}</h2>
              @if(isset($pageDescription))
              <p>{{ $pageDescription }}</p>
              @endif
            </div>
          </div>
        </div>
        
        <div class="row">
          <!-- Filter Bar -->
          <div class="col-6 col-md-7 col-lg-6">
            <div class="filter_components">
              <ul class="mb0 align-items-center text-start">
                <!-- All Filters Button -->
                <li class="d-block d-sm-inline-block me-2 mb-3">
                  <a class="all-filter-btn flter_btn" href="#" data-bs-toggle="modal" data-bs-target="#filterModal">
                    <span class="flaticon-sort me-2"></span>All Filter
                  </a>
                </li>
                
                <!-- Price Filter -->
                <li class="d-none d-md-inline-block me-2 mb-3">
                  <div class="custom_dropdown_widget">
                    <div class="drop_btn">
                      Price 
                      @if(request('min_price') || request('max_price'))
                      <span class="badge bg-primary ms-1">â€¢</span>
                      @endif
                      <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="drop_content pb20">
                      <div class="price-filter-content p20">
                        <div class="row g-2 mb-3">
                          <div class="col-6">
                            <label class="form-label small">Min Price</label>
                            <input type="number" class="form-control form-control-sm" 
                                   id="min_price" name="min_price" 
                                   placeholder="$0" 
                                   value="{{ request('min_price') }}">
                          </div>
                          <div class="col-6">
                            <label class="form-label small">Max Price</label>
                            <input type="number" class="form-control form-control-sm" 
                                   id="max_price" name="max_price" 
                                   placeholder="$10000" 
                                   value="{{ request('max_price') }}">
                          </div>
                        </div>
                        <div class="enable_disable_btns d-grid">
                          <button class="btn btn1 btn-thm mb10" id="apply-price-filter">Apply</button>
                          <button class="btn btn2" id="clear-price-filter">Clear</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </li>
                
                <!-- Brand Filter -->
                <li class="d-none d-md-inline-block me-2 mb-3">
                  <div class="custom_dropdown_widget">
                    <div class="drop_btn2">
                      Brand 
                      @if(request('brand'))
                      <span class="badge bg-primary ms-1">{{ is_array(request('brand')) ? count(request('brand')) : 1 }}</span>
                      @endif
                      <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="drop_content2 pb20 text-start">
                      <div class="blog_search_widget mb15 px20 pt15">
                        <div class="input-group">
                          <input type="text" class="form-control mb15" 
                                 id="brand-search" 
                                 placeholder="Search brands..." 
                                 autocomplete="off">
                        </div>
                      </div>
                      <div class="sidebar_widget_checkbox px20" style="max-height: 300px; overflow-y: auto;">
                        <div class="ui_kit_checkbox mb15" id="brand-list">
                          @foreach($filters['brands'] as $brand)
                          <label class="custom_checkbox">
                            {{ $brand->name }} 
                            <span class="float-end">{{ $brand->products_count }}</span>
                            <input type="checkbox" 
                                   name="brand[]" 
                                   value="{{ $brand->id }}"
                                   {{ in_array($brand->id, (array)request('brand')) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                          </label>
                          @endforeach
                        </div>
                      </div>
                      <div class="enable_disable_btns d-grid mt25 px20">
                        <button class="btn btn1 btn-thm mb10" id="apply-brand-filter">Apply</button>
                        <button class="btn btn2" id="clear-brand-filter">Clear</button>
                      </div>
                    </div>
                  </div>
                </li>
                
                <!-- Category Filter (if not already filtered) -->
                @if(!isset($category) && $filters['categories']->isNotEmpty())
                <li class="d-none d-lg-inline-block me-0 mb-3">
                  <div class="custom_dropdown_widget">
                    <div class="drop_btn3">
                      Category 
                      @if(request('category'))
                      <span class="badge bg-primary ms-1">â€¢</span>
                      @endif
                      <i class="fa fa-angle-down"></i>
                    </div>
                    <div class="drop_content3 pb20" style="width: 280px;">
                      <div class="sidebar_widget_checkbox px20 pt15" style="max-height: 300px; overflow-y: auto;">
                        <div class="ui_kit_checkbox mb15">
                          @foreach($filters['categories'] as $cat)
                          <label class="custom_checkbox">
                            {{ $cat->name }} 
                            <span class="float-end">{{ $cat->products_count }}</span>
                            <input type="checkbox" 
                                   name="category[]" 
                                   value="{{ $cat->id }}"
                                   {{ in_array($cat->id, (array)request('category')) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                          </label>
                          @endforeach
                        </div>
                      </div>
                      <div class="enable_disable_btns d-grid mt25 px20">
                        <button class="btn btn1 btn-thm mb10" id="apply-category-filter">Apply</button>
                        <button class="btn btn2" id="clear-category-filter">Clear</button>
                      </div>
                    </div>
                  </div>
                </li>
                @endif
              </ul>
            </div>
          </div>
          
          <!-- Sort and View Options -->
          <div class="col-6 col-md-5 col-lg-6">
            <div class="filter_components text-end">
              <ul class="mb0">
                <li class="list-inline-item me-0">
                  <div class="page_control_shorting mb20">
                    <select class="selectpicker show-tick" id="sort-select">
                      <option value="" {{ !request('sort_by') ? 'selected' : '' }}>Default sorting</option>
                      <option value="bestseller" {{ request('sort_by') == 'bestseller' ? 'selected' : '' }}>Best Seller</option>
                      <option value="relevance" {{ request('sort_by') == 'relevance' ? 'selected' : '' }}>Best Match</option>
                      <option value="price_low" {{ request('sort_by') == 'price_low' ? 'selected' : '' }}>Price Low</option>
                      <option value="price_high" {{ request('sort_by') == 'price_high' ? 'selected' : '' }}>Price High</option>
                      <option value="rating" {{ request('sort_by') == 'rating' ? 'selected' : '' }}>Top Rated</option>
                      <option value="newest" {{ request('sort_by') == 'newest' ? 'selected' : '' }}>Newest</option>
                    </select>
                  </div>
                </li>
                <li class="d-none d-md-inline-block list px-2">
                  <a href="#" id="view-list"><i class="fas fa-list"></i></a>
                </li>
                <li class="d-none d-md-inline-block gird ps-2">
                  <a href="#" id="view-grid" class="active"><i class="fas fa-th"></i></a>
                </li>
              </ul>
            </div>
          </div>
          
          <!-- Active Filters Display -->
          @if(request()->hasAny(['search', 'brand', 'category', 'min_price', 'max_price', 'subcategory']))
          <div class="col-lg-12 mb-3">
            <div class="active-filters d-flex align-items-center flex-wrap gap-2">
              <span class="text-muted small me-2">Active Filters:</span>
              
              @if(request('search'))
              <span class="badge bg-light text-dark d-flex align-items-center">
                Search: "{{ request('search') }}"
                <button class="btn-close btn-close-sm ms-2" data-filter="search"></button>
              </span>
              @endif
              
              @if(request('brand'))
              @foreach((array)request('brand') as $brandId)
              @php $brand = $filters['brands']->firstWhere('id', $brandId); @endphp
              @if($brand)
              <span class="badge bg-light text-dark d-flex align-items-center">
                {{ $brand->name }}
                <button class="btn-close btn-close-sm ms-2" data-filter="brand" data-value="{{ $brandId }}"></button>
              </span>
              @endif
              @endforeach
              @endif
              
              @if(request('min_price') || request('max_price'))
              <span class="badge bg-light text-dark d-flex align-items-center">
                Price: ${{ request('min_price', 0) }} - ${{ request('max_price', 'âˆž') }}
                <button class="btn-close btn-close-sm ms-2" data-filter="price"></button>
              </span>
              @endif
              
              <button class="btn btn-link btn-sm text-danger p-0" id="clear-all-filters">
                Clear All
              </button>
            </div>
          </div>
          @endif
          
          <!-- Products Grid -->
          <div class="row" id="products-container">
            @forelse($products as $product)
            <div class="col-sm-6 col-lg-4 col-xl p0 pl15-520 product-item">
              @include('partials.product-card', ['product' => $product])
            </div>
            @empty
            <div class="col-lg-12">
              <div class="no-products-found text-center py-5">
                <i class="fas fa-box-open fa-5x text-muted mb-4"></i>
                <h3 class="mb-3">No Products Found</h3>
                <p class="text-muted mb-4">
                  We couldn't find any products matching your filters.<br>
                  Try adjusting your search criteria.
                </p>
                <button class="btn btn-thm" id="clear-all-filters-btn">Clear All Filters</button>
              </div>
            </div>
            @endforelse
          </div>
          
          <!-- Pagination -->
          @if($products->hasPages())
          <div class="row">
            <div class="col-lg-12">
              <div class="mbp_pagination mt30 text-center">
                <ul class="page_navigation">
                  {{-- Previous Page --}}
                  @if ($products->onFirstPage())
                  <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-left"></i></span>
                  </li>
                  @else
                  <li class="page-item">
                    <a class="page-link" href="{{ $products->previousPageUrl() }}">
                      <i class="fas fa-angle-left"></i>
                    </a>
                  </li>
                  @endif

                  {{-- Pagination Elements --}}
                  @foreach(range(1, $products->lastPage()) as $page)
                    @if($page == $products->currentPage())
                    <li class="page-item active">
                      <span class="page-link">{{ $page }}</span>
                    </li>
                    @elseif($page == 1 || $page == $products->lastPage() || abs($page - $products->currentPage()) < 3)
                    <li class="page-item">
                      <a class="page-link" href="{{ $products->url($page) }}">{{ $page }}</a>
                    </li>
                    @elseif(abs($page - $products->currentPage()) == 3)
                    <li class="page-item disabled">
                      <span class="page-link">...</span>
                    </li>
                    @endif
                  @endforeach

                  {{-- Next Page --}}
                  @if ($products->hasMorePages())
                  <li class="page-item">
                    <a class="page-link" href="{{ $products->nextPageUrl() }}">
                      <i class="fas fa-angle-right"></i>
                    </a>
                  </li>
                  @else
                  <li class="page-item disabled">
                    <span class="page-link"><i class="fas fa-angle-right"></i></span>
                  </li>
                  @endif
                </ul>
                <p class="mt20 pagination_page_count text-center">
                  Showing {{ $products->firstItem() ?? 0 }}â€“{{ $products->lastItem() ?? 0 }} 
                  of {{ number_format($products->total()) }} products
                </p>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </section>

    {{-- Footer --}}
    @include('partials.footer')
    
    <a class="scrollToHome" href="#"><i class="fas fa-angle-up"></i></a>
  </div>
</div>

<!-- Filter Modal (Mobile) -->
<div class="modal fade" id="filterModal" tabindex="-1">
  <div class="modal-dialog modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Filter Products</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <!-- Mobile filters content -->
        <div class="mobile-filters">
          <h6 class="mb-3">Price Range</h6>
          <div class="row g-2 mb-4">
            <div class="col-6">
              <input type="number" class="form-control" id="mobile-min-price" placeholder="Min" value="{{ request('min_price') }}">
            </div>
            <div class="col-6">
              <input type="number" class="form-control" id="mobile-max-price" placeholder="Max" value="{{ request('max_price') }}">
            </div>
          </div>
          
          <h6 class="mb-3">Brands</h6>
          <div class="mobile-brand-list mb-4" style="max-height: 200px; overflow-y: auto;">
            @foreach($filters['brands'] as $brand)
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" name="mobile-brand[]" value="{{ $brand->id }}" 
                     id="mobile-brand-{{ $brand->id }}"
                     {{ in_array($brand->id, (array)request('brand')) ? 'checked' : '' }}>
              <label class="form-check-label d-flex justify-content-between w-100" for="mobile-brand-{{ $brand->id }}">
                <span>{{ $brand->name }}</span>
                <span class="badge bg-light text-dark">{{ $brand->products_count }}</span>
              </label>
            </div>
            @endforeach
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="mobile-clear-filters">Clear All</button>
        <button type="button" class="btn btn-thm" id="mobile-apply-filters">Apply Filters</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
  // Build current URL with filters
  function buildFilterUrl(params = {}) {
    const url = new URL(window.location.href);
    const searchParams = new URLSearchParams(url.search);
    
    // Update params
    Object.keys(params).forEach(key => {
      if (params[key] === null || params[key] === '') {
        searchParams.delete(key);
      } else if (Array.isArray(params[key])) {
        searchParams.delete(key);
        params[key].forEach(val => searchParams.append(key + '[]', val));
      } else {
        searchParams.set(key, params[key]);
      }
    });
    
    return url.pathname + '?' + searchParams.toString();
  }
  
  // Apply filters without reload (AJAX)
  function applyFiltersAjax() {
    const params = {
      brand: [],
      category: [],
      min_price: $('#min_price').val() || null,
      max_price: $('#max_price').val() || null,
      sort_by: $('#sort-select').val() || null,
      search: '{{ request("search") }}' || null
    };
    
    // Collect checked brands
    $('input[name="brand[]"]:checked').each(function() {
      params.brand.push($(this).val());
    });
    
    // Collect checked categories
    $('input[name="category[]"]:checked').each(function() {
      params.category.push($(this).val());
    });
    
    // Navigate with filters
    window.location.href = buildFilterUrl(params);
  }
  
  // Price filter
  $('#apply-price-filter').on('click', function(e) {
    e.preventDefault();
    applyFiltersAjax();
  });
  
  $('#clear-price-filter').on('click', function(e) {
    e.preventDefault();
    $('#min_price, #max_price').val('');
    applyFiltersAjax();
  });
  
  // Brand filter
  $('#apply-brand-filter').on('click', function(e) {
    e.preventDefault();
    applyFiltersAjax();
  });
  
  $('#clear-brand-filter').on('click', function(e) {
    e.preventDefault();
    $('input[name="brand[]"]').prop('checked', false);
    applyFiltersAjax();
  });
  
  // Category filter
  $('#apply-category-filter').on('click', function(e) {
    e.preventDefault();
    applyFiltersAjax();
  });
  
  $('#clear-category-filter').on('click', function(e) {
    e.preventDefault();
    $('input[name="category[]"]').prop('checked', false);
    applyFiltersAjax();
  });
  
  // Sort change
  $('#sort-select').on('change', function() {
    applyFiltersAjax();
  });
  
  // Brand search
  $('#brand-search').on('keyup', function() {
    const search = $(this).val().toLowerCase();
    $('#brand-list label').each(function() {
      const text = $(this).text().toLowerCase();
      $(this).toggle(text.indexOf(search) > -1);
    });
  });
  
  // Remove individual filter
  $('.active-filters .btn-close').on('click', function() {
    const filter = $(this).data('filter');
    const value = $(this).data('value');
    
    if (filter === 'search') {
      window.location.href = buildFilterUrl({ search: null });
    } else if (filter === 'price') {
      window.location.href = buildFilterUrl({ min_price: null, max_price: null });
    } else if (filter === 'brand' && value) {
      let brands = {{ json_encode((array)request('brand')) }};
      brands = brands.filter(b => b != value);
      window.location.href = buildFilterUrl({ brand: brands.length ? brands : null });
    }
  });
  
  // Clear all filters
  $('#clear-all-filters, #clear-all-filters-btn').on('click', function(e) {
    e.preventDefault();
    window.location.href = '{{ url()->current() }}';
  });
  
  // Mobile filters
  $('#mobile-apply-filters').on('click', function() {
    const params = {
      min_price: $('#mobile-min-price').val() || null,
      max_price: $('#mobile-max-price').val() || null,
      brand: []
    };
    
    $('input[name="mobile-brand[]"]:checked').each(function() {
      params.brand.push($(this).val());
    });
    
    window.location.href = buildFilterUrl(params);
  });
  
  $('#mobile-clear-filters').on('click', function() {
    $('#mobile-min-price, #mobile-max-price').val('');
    $('input[name="mobile-brand[]"]').prop('checked', false);
  });
  
  // View toggle
  $('#view-list').on('click', function(e) {
    e.preventDefault();
    $('#view-grid').removeClass('active');
    $(this).addClass('active');
    $('.product-item').removeClass('col-xl').addClass('col-lg-12');
    // Could save preference to localStorage
  });
  
  $('#view-grid').on('click', function(e) {
    e.preventDefault();
    $('#view-list').removeClass('active');
    $(this).addClass('active');
    $('.product-item').removeClass('col-lg-12').addClass('col-xl');
  });
  
  // Keep dropdowns open when clicking inside
  $('.drop_content, .drop_content2, .drop_content3').on('click', function(e) {
    e.stopPropagation();
  });
});
</script>
@endpush

<style>
.custom_shop_category_nav_list_menu ul {
  scrollbar-width: thin;
  -ms-overflow-style: none;
}

.custom_shop_category_nav_list_menu ul::-webkit-scrollbar {
  height: 4px;
}

.custom_shop_category_nav_list_menu ul::-webkit-scrollbar-thumb {
  background: #ddd;
  border-radius: 4px;
}

.active-filters .badge {
  padding: 8px 12px;
  font-weight: 500;
}

.btn-close-sm {
  font-size: 0.7rem;
  padding: 0;
  width: 12px;
  height: 12px;
}

.custom_dropdown_widget .drop_content,
.custom_dropdown_widget .drop_content2,
.custom_dropdown_widget .drop_content3 {
  min-width: 280px;
}

.no-products-found {
  background: white;
  border-radius: 8px;
  padding: 60px 20px;
}

@media (max-width: 768px) {
  .custom_shop_category_nav_list_menu ul {
    padding-bottom: 10px;
  }
}
</style>
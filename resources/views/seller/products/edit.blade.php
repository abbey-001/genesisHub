{{-- resources/views/seller/products/edit.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <h4 class="card-title mb-0">Edit Product</h4>
                    <a href="{{ route('seller.products.show', $product) }}" class="btn btn-sm btn-secondary product-header-link">
                        <i data-lucide="arrow-left" class="fs-16"></i> Back to Product
                    </a>
                </div>
            </div>
            <div class="card-body">
                {{-- Standard form — no enctype needed; images are uploaded separately --}}
                <form id="product-edit-form" action="{{ route('seller.products.update', $product) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    {{-- =====================================================
                         TAB NAV
                    ====================================================== --}}
                    <ul class="nav nav-tabs mb-4 product-form-tabs" id="productTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button">
                                <i data-lucide="package" class="fs-14 me-1"></i> Basic Info
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-search" type="button">
                                <i data-lucide="search" class="fs-14 me-1"></i> Search & Discovery
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-specs" type="button">
                                <i data-lucide="list" class="fs-14 me-1"></i> Specs & Variants
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seo" type="button">
                                <i data-lucide="globe" class="fs-14 me-1"></i> SEO
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- =====================================================
                             TAB 1: BASIC INFO
                        ====================================================== --}}
                        <div class="tab-pane fade show active" id="tab-basic">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                               id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="stock" class="form-label">Stock <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                               id="stock" name="stock" value="{{ old('stock', $product->stock) }}" min="0" required>
                                        @error('stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="mb-3">
                                        <label for="condition" class="form-label">Condition</label>
                                        <select class="form-select" id="condition" name="condition">
                                            <option value="new"         {{ old('condition', $product->condition) == 'new'         ? 'selected':'' }}>New</option>
                                            <option value="used"        {{ old('condition', $product->condition) == 'used'        ? 'selected':'' }}>Used</option>
                                            <option value="refurbished" {{ old('condition', $product->condition) == 'refurbished' ? 'selected':'' }}>Refurbished</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                  <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                      Fulfillment Type
                                      <span class="text-danger">*</span>
                                    </label>
                                    <p class="text-muted fz12 mb-2">
                                      How quickly can you have this item ready for a rider to collect after an order is placed?
                                    </p>
                                 
                                    {{-- Three radio cards —————————————————————————————————————————— --}}
                                    <div class="fulfillment_type_cards d-flex flex-column flex-md-row gap-3" id="fulfillment-cards">
                                 
                                      {{-- In Stock --}}
                                      @php $isInStock = old('fulfillment_type', $product->fulfillment_type ?? 'in_stock') === 'in_stock'; @endphp
                                      <label class="fulfillment_card flex-fill {{ $isInStock ? 'selected' : '' }}"
                                             for="ft_in_stock"
                                             style="cursor:pointer; border:2px solid {{ $isInStock ? 'var(--thm-color,#714e32)' : '#e0e0e0' }};
                                                    border-radius:12px; padding:16px 18px; transition:all .2s; background:{{ $isInStock ? '#fdf8f4' : '#fff' }};">
                                        <div class="d-flex align-items-start gap-3">
                                          <input type="radio" name="fulfillment_type" id="ft_in_stock" value="in_stock"
                                                 class="fulfillment-radio mt-1" {{ $isInStock ? 'checked' : '' }}
                                                 style="accent-color:var(--thm-color,#714e32); flex-shrink:0;">
                                          <div>
                                            <p class="mb-1 fw-semibold fz14" style="color:#1a1a1a;">
                                              📦 In Stock
                                            </p>
                                            <p class="mb-0 fz12 text-muted" style="line-height:1.5;">
                                              Item is on the shelf, ready to hand to a rider within
                                              <strong>{{ \App\Models\Product::IN_STOCK_MAX_DAYS }} days</strong> of payment.
                                              No extra setup needed.
                                            </p>
                                          </div>
                                        </div>
                                      </label>
                                 
                                      {{-- Pre-Order --}}
                                      @php $isPreOrder = old('fulfillment_type', $product->fulfillment_type ?? 'in_stock') === 'pre_order'; @endphp
                                      <label class="fulfillment_card flex-fill {{ $isPreOrder ? 'selected' : '' }}"
                                             for="ft_pre_order"
                                             style="cursor:pointer; border:2px solid {{ $isPreOrder ? '#d97706' : '#e0e0e0' }};
                                                    border-radius:12px; padding:16px 18px; transition:all .2s; background:{{ $isPreOrder ? '#fff8ec' : '#fff' }};">
                                        <div class="d-flex align-items-start gap-3">
                                          <input type="radio" name="fulfillment_type" id="ft_pre_order" value="pre_order"
                                                 class="fulfillment-radio mt-1" {{ $isPreOrder ? 'checked' : '' }}
                                                 style="accent-color:#d97706; flex-shrink:0;">
                                          <div>
                                            <p class="mb-1 fw-semibold fz14" style="color:#1a1a1a;">
                                              🔔 Pre-Order
                                            </p>
                                            <p class="mb-0 fz12 text-muted" style="line-height:1.5;">
                                              Item isn't in stock yet but will be available. Buyers know they're ordering
                                              early. You set the number of days until you can ship.
                                            </p>
                                          </div>
                                        </div>
                                      </label>
                                 
                                      {{-- Made to Order --}}
                                      @php $isMto = old('fulfillment_type', $product->fulfillment_type ?? 'in_stock') === 'made_to_order'; @endphp
                                      <label class="fulfillment_card flex-fill {{ $isMto ? 'selected' : '' }}"
                                             for="ft_made_to_order"
                                             style="cursor:pointer; border:2px solid {{ $isMto ? '#7c3aed' : '#e0e0e0' }};
                                                    border-radius:12px; padding:16px 18px; transition:all .2s; background:{{ $isMto ? '#faf5ff' : '#fff' }};">
                                        <div class="d-flex align-items-start gap-3">
                                          <input type="radio" name="fulfillment_type" id="ft_made_to_order" value="made_to_order"
                                                 class="fulfillment-radio mt-1" {{ $isMto ? 'checked' : '' }}
                                                 style="accent-color:#7c3aed; flex-shrink:0;">
                                          <div>
                                            <p class="mb-1 fw-semibold fz14" style="color:#1a1a1a;">
                                              🔨 Made to Order
                                            </p>
                                            <p class="mb-0 fz12 text-muted" style="line-height:1.5;">
                                              Each item is crafted individually when ordered — custom work, baked goods,
                                              tailored clothing, handmade items. You set how many days it takes to make.
                                            </p>
                                          </div>
                                        </div>
                                      </label>
                                 
                                    </div>
                                 
                                    @error('fulfillment_type')
                                      <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                 
                                    {{-- Days input — hidden for in_stock, shown for pre_order / made_to_order --}}
                                    <div id="max_ready_days_wrapper"
                                         style="display: {{ ($isPreOrder || $isMto) ? 'block' : 'none' }}; margin-top:14px;">
                                      <div class="row align-items-end g-3">
                                        <div class="col-md-4">
                                          <label for="max_ready_days" class="form-label fw-medium">
                                            Ready in how many days?
                                            <span class="text-danger">*</span>
                                          </label>
                                          <div class="input-group">
                                            <input type="number"
                                                   id="max_ready_days"
                                                   name="max_ready_days"
                                                   class="form-control @error('max_ready_days') is-invalid @enderror"
                                                   min="1"
                                                   max="365"
                                                   value="{{ old('max_ready_days', $product->max_ready_days ?? '') }}"
                                                   placeholder="e.g. 7">
                                            <span class="input-group-text">days</span>
                                          </div>
                                          @error('max_ready_days')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                          @enderror
                                        </div>
                                        <div class="col-md-8">
                                          <div class="alert alert-warning py-2 px-3 mb-0 fz12" style="border-radius:8px;">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <strong>Promise carefully.</strong> Buyers see this as their delivery estimate.
                                            If you're unsure, add a few extra days as a buffer — under-promising and
                                            over-delivering makes everyone happier.
                                          </div>
                                        </div>
                                      </div>
                                 
                                      {{-- Live preview of what buyers will see --}}
                                      <div class="mt-3 p-3 rounded" id="estimate_preview"
                                           style="background:#f8f9fa; border:1px solid #dee2e6; display:none;">
                                        <p class="mb-1 fw-semibold fz12 text-muted">BUYER WILL SEE:</p>
                                        <div class="d-flex align-items-center gap-2">
                                          <i class="fas fa-clock text-warning"></i>
                                          <span class="fz13 fw-medium" id="estimate_preview_text"></span>
                                        </div>
                                      </div>
                                    </div>
                                 
                                  </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="short_description" class="form-label">Short Description</label>
                                        <textarea class="form-control @error('short_description') is-invalid @enderror"
                                                  id="short_description" name="short_description" rows="2"
                                                  maxlength="500">{{ old('short_description', $product->short_description) }}</textarea>
                                        @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Full Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('description') is-invalid @enderror"
                                                  id="description" name="description" rows="7" required>{{ old('description', $product->description) }}</textarea>
                                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                                id="category_id" name="category_id" required>
                                            <option value="">Select Category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="subcategory_id" class="form-label">Subcategory</label>
                                        <select class="form-select" id="subcategory_id" name="subcategory_id">
                                            <option value="">Select Subcategory</option>
                                            @if($product->subcategory)
                                                <option value="{{ $product->subcategory->id }}" selected>
                                                    {{ $product->subcategory->name }}
                                                </option>
                                            @endif
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3" style="position:relative;">
                                        <label class="form-label">Brand</label>

                                        <input type="hidden" id="brand_id" name="brand_id" value="{{ old('brand_id', $product->brand_id ?? '') }}">

                                        <div class="input-group">
                                            <input type="text" id="brand_search"
                                                   class="form-control @error('brand_id') is-invalid @enderror"
                                                   placeholder="Type to search or add a brand…"
                                                   autocomplete="off"
                                                   value="{{ old('brand_id') ? optional(\App\Models\Brand::find(old('brand_id')))->name : optional($product->brand)->name }}">
                                            <button class="btn btn-outline-secondary" type="button" id="brand_clear"
                                                    title="Clear brand"
                                                    style="{{ $product->brand_id ? '' : 'display:none' }}">
                                                <i data-lucide="x" class="fs-14"></i>
                                            </button>
                                        </div>

                                        <div id="brand_dropdown" class="list-group shadow-sm mt-1"
                                             style="display:none; position:absolute; z-index:1050; width:100%; max-height:240px; overflow-y:auto; border-radius:6px;">
                                        </div>

                                        @error('brand_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                        <div id="brand_feedback" class="mt-2" style="display:none"></div>
                                        <div id="brand_add_section" class="mt-2" style="display:none">
                                            <div class="alert alert-warning py-2 px-3 mb-0 small d-flex align-items-center gap-2">
                                                <i data-lucide="alert-circle" class="fs-14 flex-shrink-0"></i>
                                                <span>No existing brand matches. You can create it — it will be reviewed for quality.</span>
                                            </div>
                                            <button type="button" id="brand_check_btn" class="btn btn-sm btn-outline-primary mt-2">
                                                <i data-lucide="plus" class="fs-13 me-1"></i> Create "<span id="brand_add_label"></span>"
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="model_number" class="form-label">Model / Part Number</label>
                                        <input type="text" class="form-control" id="model_number" name="model_number"
                                               value="{{ old('model_number', $product->model_number) }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Regular Price (₦) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control @error('price') is-invalid @enderror"
                                               id="price" name="price" value="{{ old('price', $product->price) }}" min="0" required>
                                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="sale_price" class="form-label">Sale Price (₦)</label>
                                        <input type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror"
                                               id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}" min="0">
                                        <small class="text-muted">Leave empty if no discount</small>
                                        @error('sale_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_active"
                                               name="is_active" value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="is_featured"
                                               name="is_featured" value="1" {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Featured</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- =====================================================
                             TAB 2: SEARCH & DISCOVERY
                        ====================================================== --}}
                        <div class="tab-pane fade" id="tab-search">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="tags" class="form-label fw-semibold">
                                            Tags <span class="badge bg-success ms-1">High Impact</span>
                                        </label>
                                        <input type="text" class="form-control @error('tags') is-invalid @enderror"
                                               id="tags" name="tags" value="{{ old('tags', $product->tags) }}"
                                               placeholder="wireless, noise-cancelling, over-ear, bluetooth">
                                        <small class="text-muted">Comma-separated. Visible to buyers as filter chips.</small>
                                        @error('tags') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <div id="tags-preview" class="mt-2 d-flex flex-wrap gap-1">
                                            @foreach($product->tags_array as $tag)
                                                <span class="badge bg-light text-dark border">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="search_keywords" class="form-label fw-semibold">
                                            Hidden Search Keywords <span class="badge bg-success ms-1">High Impact</span>
                                        </label>
                                        <textarea class="form-control @error('search_keywords') is-invalid @enderror"
                                                  id="search_keywords" name="search_keywords" rows="3">{{ old('search_keywords', $product->search_keywords) }}</textarea>
                                        <small class="text-muted">Not visible to buyers. Add synonyms, misspellings, alternate names.</small>
                                        @error('search_keywords') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="use_cases" class="form-label fw-semibold">Use Cases / Occasions</label>
                                        <input type="text" class="form-control" id="use_cases" name="use_cases"
                                               value="{{ old('use_cases', $product->use_cases) }}"
                                               placeholder="gifting, office use, travel, gym, outdoor">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="target_audience" class="form-label fw-semibold">Target Audience</label>
                                        <select class="form-select" id="target_audience" name="target_audience">
                                            <option value="">Select audience</option>
                                            @foreach(['all'=>'Everyone','men'=>'Men','women'=>'Women','kids'=>'Kids / Children','unisex'=>'Unisex','business'=>'Business / Professional'] as $val => $label)
                                                <option value="{{ $val }}" {{ old('target_audience', $product->target_audience) == $val ? 'selected':'' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                @if($product->search_vector)
                                <div class="col-12 mt-2">
                                    <details>
                                        <summary class="text-muted small" style="cursor:pointer;">View current search index (debug)</summary>
                                        <div class="mt-2 p-3 bg-light border rounded small text-muted" style="word-break:break-word; max-height:120px; overflow-y:auto;">
                                            {{ $product->search_vector }}
                                        </div>
                                    </details>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- =====================================================
                             TAB 3: SPECS & VARIANTS
                        ====================================================== --}}
                        <div class="tab-pane fade" id="tab-specs">
                            <div class="row g-4">

                                {{-- ── Specifications ──────────────────────────── --}}
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-1">Product Specifications</h6>
                                    <p class="text-muted small mb-3">Key-value pairs shown in a spec table on the product page.</p>

                                    <div id="specs-container">
                                        @php
                                            $oldSpecKeys   = old('spec_keys', []);
                                            $oldSpecValues = old('spec_values', []);
                                            $existingSpecs = $product->specifications ?? [];
                                            if (count($oldSpecKeys)) {
                                                $specPairs = array_map(null, $oldSpecKeys, $oldSpecValues);
                                            } elseif (count($existingSpecs)) {
                                                $specPairs = array_map(fn($k,$v) => [$k,$v], array_keys($existingSpecs), array_values($existingSpecs));
                                            } else {
                                                $specPairs = [['Weight',''],['Dimensions',''],['Material','']];
                                            }
                                        @endphp
                                        @foreach($specPairs as [$sk, $sv])
                                        <div class="d-flex gap-2 mb-2 spec-row">
                                            <input type="text" class="form-control form-control-sm"
                                                   name="spec_keys[]" value="{{ $sk }}" placeholder="e.g. Weight">
                                            <input type="text" class="form-control form-control-sm"
                                                   name="spec_values[]" value="{{ $sv }}" placeholder="Value">
                                            <button type="button" class="btn btn-sm btn-soft-danger remove-spec" title="Remove">
                                                <i data-lucide="x" class="fs-14"></i>
                                            </button>
                                        </div>
                                        @endforeach
                                    </div>

                                    <button type="button" id="add-spec" class="btn btn-sm btn-outline-primary mt-1">
                                        <i data-lucide="plus" class="fs-14"></i> Add Specification
                                    </button>
                                </div>

                                {{-- ── Variants ─────────────────────────────────── --}}
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-1">Product Variants</h6>
                                    <p class="text-muted small mb-3">
                                        Add one row per individual option. Each value can add or subtract from the base price.
                                    </p>

                                    <div id="variants-builder">
                                        @php
                                            $oldVarNames  = old('variant_names', []);
                                            $oldVarValues = old('variant_values', []);
                                            $oldVarAdjs   = old('variant_price_adjustments', []);
                                            if (count($oldVarNames)) {
                                                $varRows = array_map(null, $oldVarNames, $oldVarValues, $oldVarAdjs);
                                            } else {
                                                $dbVariants = \App\Models\ProductVariant::where('product_id', $product->id)->get();
                                                $varRows = $dbVariants->map(fn($v) => [$v->variant_name, $v->variant_value, $v->price_adjustment])->toArray();
                                            }
                                            $varGroups = [];
                                            foreach ($varRows as [$vn, $vv, $va]) {
                                                $varGroups[$vn][] = ['value' => $vv, 'adj' => $va];
                                            }
                                        @endphp

                                        @foreach($varGroups as $groupName => $groupRows)
                                        <div class="variant-group card border mb-3" data-group="{{ $groupName }}">
                                            <div class="card-header py-2 px-3 d-flex align-items-center gap-2 bg-light">
                                                <input type="text" class="form-control form-control-sm variant-group-name fw-semibold"
                                                       value="{{ $groupName }}" placeholder="Group name, e.g. Size"
                                                       style="font-weight:600;">
                                                <span class="text-muted small flex-grow-1">{{ count($groupRows) }} option{{ count($groupRows) !== 1 ? 's' : '' }}</span>
                                                <button type="button" class="btn btn-sm btn-soft-danger remove-variant-group" title="Remove group">
                                                    <i data-lucide="trash-2" class="fs-13"></i>
                                                </button>
                                            </div>
                                            <div class="card-body p-2">
                                                <div class="d-flex gap-2 mb-1 px-1" style="font-size:10px;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.3px;">
                                                    <div class="flex-grow-1">Value</div>
                                                    <div class="variant-price-label">Price Adjustment (₦)</div>
                                                    <div style="width:30px;"></div>
                                                </div>
                                                <div class="variant-rows-container">
                                                    @foreach($groupRows as $row)
                                                    <div class="d-flex gap-2 mb-1 align-items-center variant-value-row">
                                                        <input type="hidden" name="variant_names[]" value="{{ $groupName }}" class="variant-name-hidden">
                                                        <input type="text" class="form-control form-control-sm flex-grow-1"
                                                               name="variant_values[]" value="{{ $row['value'] }}" placeholder="e.g. XL">
                                                        <div class="input-group input-group-sm variant-price-input">
                                                            <span class="input-group-text px-2" style="font-size:11px;">₦</span>
                                                            <input type="number" class="form-control form-control-sm"
                                                                   name="variant_price_adjustments[]" value="{{ $row['adj'] }}"
                                                                   placeholder="0" step="0.01">
                                                        </div>
                                                        <button type="button" class="btn btn-sm btn-soft-danger remove-variant-value flex-shrink-0">
                                                            <i data-lucide="x" class="fs-13"></i>
                                                        </button>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-secondary add-variant-value mt-1" style="font-size:12px;">
                                                    <i data-lucide="plus" class="fs-13 me-1"></i> Add value
                                                </button>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>

                                    <button type="button" id="add-variant-group" class="btn btn-sm btn-outline-primary mt-1">
                                        <i data-lucide="layers" class="fs-14 me-1"></i> Add Variant Group
                                    </button>

                                    <div class="alert alert-light border mt-3 py-2 px-3" style="font-size:12.5px;">
                                        <i data-lucide="info" class="fs-13 me-1 text-primary"></i>
                                        <strong>How it works:</strong> The <strong>Price Adjustment</strong> adds to or subtracts from the base price.
                                        E.g. <code>500</code> for Size XL means XL costs ₦500 more. Leave blank for no change.
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- =====================================================
                             TAB 4: SEO
                        ====================================================== --}}
                        <div class="tab-pane fade" id="tab-seo">
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="meta_title" class="form-label fw-semibold">Meta Title</label>
                                        <input type="text" class="form-control @error('meta_title') is-invalid @enderror"
                                               id="meta_title" name="meta_title"
                                               value="{{ old('meta_title', $product->meta_title) }}" maxlength="160">
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Recommended: 50–60 characters</small>
                                            <small id="meta_title_count" class="text-muted">{{ strlen($product->meta_title ?? '') }} / 160</small>
                                        </div>
                                        @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="meta_description" class="form-label fw-semibold">Meta Description</label>
                                        <textarea class="form-control @error('meta_description') is-invalid @enderror"
                                                  id="meta_description" name="meta_description" rows="3"
                                                  maxlength="320">{{ old('meta_description', $product->meta_description) }}</textarea>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">Recommended: 120–160 characters</small>
                                            <small id="meta_desc_count" class="text-muted">{{ strlen($product->meta_description ?? '') }} / 320</small>
                                        </div>
                                        @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Search Engine Preview</label>
                                    <div class="border rounded p-3 bg-white">
                                        <div id="serp-title" class="text-primary fs-16 fw-medium" style="max-width:600px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                            {{ $product->meta_title ?: ($product->name . ' — Your Store') }}
                                        </div>
                                        <div class="text-success small">yourstore.com/products/{{ $product->slug }}</div>
                                        <div id="serp-desc" class="text-muted small mt-1" style="max-width:600px;">
                                            {{ $product->meta_description ?: $product->short_description }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>{{-- /tab-content --}}

                    <hr class="mt-4">
                    <div class="d-flex gap-2 align-items-center product-form-actions">
                        <button type="submit" id="submit-btn" class="btn btn-primary">
                            <i data-lucide="save" class="fs-16"></i> Update Product
                        </button>
                        <a href="{{ route('seller.products.show', $product) }}" class="btn btn-secondary">Cancel</a>
                        <span id="submit-status" class="text-muted small ms-2" style="display:none"></span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Image Management Card ─────────────────────────────────────────────── --}}
<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Product Images</h4>
                <small class="text-muted">{{ $product->images->count() }} image{{ $product->images->count() !== 1 ? 's' : '' }}</small>
            </div>
            <div class="card-body">

                {{-- Existing images --}}
                <div id="existing-images-grid" class="row g-3 mb-3">
                    @forelse($product->images as $image)
                    <div class="col-md-3 col-sm-4 col-6" id="img-card-{{ $image->id }}">
                        <div class="card h-100">
                            <img src="{{ asset('public/storage/' . $image->image_path) }}" class="card-img-top"
                                 alt="Product Image" style="height:200px;object-fit:cover;">
                            <div class="card-body p-2">
                                <div class="d-flex justify-content-between align-items-center gap-1">
                                    @if($image->is_primary)
                                        <span class="badge badge-soft-success">Primary</span>
                                    @else
                                        <form action="{{ route('seller.products.set-primary', $product) }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="image_id" value="{{ $image->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Set Primary</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('seller.products.images.delete', $image) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-soft-danger"
                                                onclick="return confirm('Delete this image?')">
                                            <i data-lucide="trash-2" class="fs-14"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12" id="no-images-notice">
                        <div class="alert alert-info mb-0">
                            <i data-lucide="info" class="fs-16 me-1"></i> No images yet. Upload some below.
                        </div>
                    </div>
                    @endforelse
                </div>

                {{-- ── Chunked uploader for edit page ─────────────────────── --}}
                <div class="border-top pt-3">
                    <h6 class="mb-3">Upload More Images</h6>

                    <div id="image-drop-zone"
                         class="border border-2 border-dashed rounded p-4 text-center text-muted"
                         style="position:relative; cursor:pointer; transition:.2s;">
                        <i data-lucide="image-plus" class="fs-28 mb-2 d-block mx-auto"></i>
                        <div class="fw-semibold">Drag & drop images here, or <span class="text-primary">browse</span></div>
                        <small>JPEG, PNG, WEBP — max 5 MB each.</small>
                        <input type="file" id="image-file-input" name="images[]" multiple accept="image/*"
                               style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                    </div>

                    <div id="image-preview-grid" class="d-flex flex-wrap gap-2 mt-3"></div>

                    <div id="image-upload-status" class="mt-2" style="display:none">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                            <span id="image-upload-label" class="small text-muted">Uploading…</span>
                        </div>
                        <div class="progress" style="height:6px">
                            <div id="image-upload-bar"
                                 class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                                 style="width:0%"></div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" id="upload-images-btn" class="btn btn-primary" disabled>
                            <i data-lucide="upload" class="fs-16 me-1"></i> Upload Selected Images
                        </button>
                        <span id="upload-count-label" class="text-muted small ms-2"></span>
                    </div>
                </div>
                {{-- ── /Chunked uploader ───────────────────────────────────── --}}

            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.product-form-tabs {
    gap: .75rem;
    border-bottom: 0;
    flex-wrap: wrap;
}

.product-form-tabs .nav-item {
    flex: 1 1 180px;
}

.product-form-tabs .nav-link {
    width: 100%;
    height: 100%;
    border: 1px solid var(--bs-border-color, #dee2e6);
    border-radius: .75rem;
    padding: .85rem 1rem;
}

.product-form-tabs .nav-link.active {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 1px rgba(var(--bs-primary-rgb), .15);
}

.product-header-link {
    white-space: nowrap;
}

.spec-row .form-control:first-child,
.variant-group-name {
    max-width: 170px;
}

.variant-price-label,
.variant-price-input {
    width: 140px;
    flex-shrink: 0;
}

@media (max-width: 767.98px) {
    .product-header-link,
    .product-form-actions > .btn,
    .product-form-actions > a {
        width: 100%;
    }

    .product-form-actions {
        flex-direction: column;
        align-items: stretch !important;
    }

    .product-form-actions #submit-status {
        margin-left: 0 !important;
    }

    .product-form-tabs .nav-item {
        flex: 1 1 100%;
    }

    .product-form-tabs .nav-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        text-align: left;
    }

    .fulfillment_type_cards {
        gap: .75rem !important;
    }

    .spec-row,
    .variant-value-row,
    .variant-group .card-header {
        flex-direction: column;
        align-items: stretch !important;
    }

    .spec-row .form-control:first-child,
    .variant-group-name,
    .variant-price-input,
    .variant-price-label {
        width: 100%;
        max-width: none;
    }

    .variant-group .card-header .text-muted {
        order: 3;
    }

    .variant-group .card-header .remove-variant-group,
    .variant-value-row .remove-variant-value,
    .spec-row .remove-spec {
        align-self: flex-end;
    }

    #image-drop-zone {
        padding: 1.25rem !important;
    }

    #image-preview-grid > div {
        width: calc(50% - .25rem) !important;
        height: 110px !important;
    }

    #existing-images-grid .card-body .d-flex {
        flex-direction: column;
        align-items: stretch !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
// ── Shared utility ────────────────────────────────────────────────────────────
function escHtml(str) {
    return String(str).replace(/[&<>"']/g, c =>
        ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

// ── Category → Subcategory cascade ───────────────────────────────────────────
document.getElementById('category_id').addEventListener('change', function () {
    const id  = this.value;
    const sub = document.getElementById('subcategory_id');
    sub.innerHTML = '<option value="">Loading…</option>';
    if (!id) { sub.innerHTML = '<option value="">Select Subcategory</option>'; return; }
    fetch(`/seller/categories/${id}/subcategories`)
        .then(r => r.json())
        .then(data => {
            sub.innerHTML = '<option value="">Select Subcategory</option>';
            const current = {{ $product->subcategory_id ?? 'null' }};
            data.forEach(s => {
                const sel = s.id == current ? 'selected' : '';
                sub.innerHTML += `<option value="${s.id}" ${sel}>${escHtml(s.name)}</option>`;
            });
        })
        .catch(() => sub.innerHTML = '<option value="">Error</option>');
});

// ── Tags preview ──────────────────────────────────────────────────────────────
document.getElementById('tags').addEventListener('input', function () {
    const preview = document.getElementById('tags-preview');
    const chips   = this.value.split(',').map(t => t.trim()).filter(Boolean);
    preview.innerHTML = chips.map(t => `<span class="badge bg-light text-dark border">${escHtml(t)}</span>`).join('');
});

// ── Spec rows ─────────────────────────────────────────────────────────────────
document.getElementById('add-spec').addEventListener('click', function () {
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-2 spec-row';
    row.innerHTML = `
        <input type="text"  class="form-control form-control-sm" name="spec_keys[]"   placeholder="e.g. Color">
        <input type="text"  class="form-control form-control-sm" name="spec_values[]" placeholder="Value">
        <button type="button" class="btn btn-sm btn-soft-danger remove-spec" title="Remove">
            <i data-lucide="x" class="fs-14"></i>
        </button>`;
    document.getElementById('specs-container').appendChild(row);
    lucide.createIcons();
});
document.getElementById('specs-container').addEventListener('click', function (e) {
    if (e.target.closest('.remove-spec')) e.target.closest('.spec-row').remove();
});

// ── Variant group builder ─────────────────────────────────────────────────────
function makeValueRow(groupNameVal) {
    const row = document.createElement('div');
    row.className = 'd-flex gap-2 mb-1 align-items-center variant-value-row';
    row.innerHTML = `
        <input type="hidden" name="variant_names[]" value="${escHtml(groupNameVal)}" class="variant-name-hidden">
        <input type="text" class="form-control form-control-sm flex-grow-1" name="variant_values[]" placeholder="e.g. XL">
        <div class="input-group input-group-sm variant-price-input">
            <span class="input-group-text px-2" style="font-size:11px;">₦</span>
            <input type="number" class="form-control form-control-sm" name="variant_price_adjustments[]"
                   placeholder="0" step="0.01">
        </div>
        <button type="button" class="btn btn-sm btn-soft-danger remove-variant-value flex-shrink-0" title="Remove value">
            <i data-lucide="x" class="fs-13"></i>
        </button>`;
    return row;
}

document.getElementById('add-variant-group').addEventListener('click', function () {
    const groupCard = document.createElement('div');
    groupCard.className = 'variant-group card border mb-3';
    groupCard.innerHTML = `
        <div class="card-header py-2 px-3 d-flex align-items-center gap-2 bg-light">
            <input type="text" class="form-control form-control-sm variant-group-name fw-semibold"
                   placeholder="Group name, e.g. Color" style="font-weight:600;">
            <span class="text-muted small flex-grow-1">0 options</span>
            <button type="button" class="btn btn-sm btn-soft-danger remove-variant-group" title="Remove group">
                <i data-lucide="trash-2" class="fs-13"></i>
            </button>
        </div>
        <div class="card-body p-2">
            <div class="d-flex gap-2 mb-1 px-1" style="font-size:10px;font-weight:700;color:#6c757d;text-transform:uppercase;letter-spacing:.3px;">
                <div class="flex-grow-1">Value</div>
                <div class="variant-price-label">Price Adjustment (₦)</div>
                <div style="width:30px;"></div>
            </div>
            <div class="variant-rows-container"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary add-variant-value mt-1" style="font-size:12px;">
                <i data-lucide="plus" class="fs-13 me-1"></i> Add value
            </button>
        </div>`;
    document.getElementById('variants-builder').appendChild(groupCard);
    lucide.createIcons();
    groupCard.querySelector('.variant-group-name').focus();
});

document.getElementById('variants-builder').addEventListener('click', function(e) {
    if (e.target.closest('.remove-variant-group')) { e.target.closest('.variant-group').remove(); return; }
    if (e.target.closest('.remove-variant-value')) {
        const row = e.target.closest('.variant-value-row');
        const card = row.closest('.variant-group');
        row.remove(); updateGroupCount(card); return;
    }
    if (e.target.closest('.add-variant-value')) {
        const card = e.target.closest('.variant-group');
        const groupName = card.querySelector('.variant-group-name').value.trim() || 'Variant';
        const container = card.querySelector('.variant-rows-container');
        const newRow = makeValueRow(groupName);
        container.appendChild(newRow);
        lucide.createIcons();
        newRow.querySelector('input[name="variant_values[]"]').focus();
        updateGroupCount(card);
    }
});

document.getElementById('variants-builder').addEventListener('input', function(e) {
    if (e.target.classList.contains('variant-group-name')) {
        const card = e.target.closest('.variant-group');
        card.querySelectorAll('.variant-name-hidden').forEach(h => h.value = e.target.value);
    }
});

function updateGroupCount(card) {
    const count = card.querySelectorAll('.variant-value-row').length;
    const label = card.querySelector('.card-header .text-muted');
    if (label) label.textContent = count + ' option' + (count !== 1 ? 's' : '');
}

// ── SEO counters & SERP preview ───────────────────────────────────────────────
['meta_title','meta_description'].forEach(id => {
    const el = document.getElementById(id);
    const ct = document.getElementById(id === 'meta_title' ? 'meta_title_count' : 'meta_desc_count');
    if (el && ct) el.addEventListener('input', () => ct.textContent = `${el.value.length} / ${el.maxLength}`);
});

function updateSerp() {
    const title = document.getElementById('meta_title').value  || (document.getElementById('name').value + ' — Your Store');
    const desc  = document.getElementById('meta_description').value || document.getElementById('short_description').value;
    document.getElementById('serp-title').textContent = title;
    document.getElementById('serp-desc').textContent  = desc;
}
['name','meta_title','meta_description','short_description'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', updateSerp);
});

// ── Brand typeahead ───────────────────────────────────────────────────────────
(function () {
    'use strict';
    const searchInput  = document.getElementById('brand_search');
    const hiddenInput  = document.getElementById('brand_id');
    const dropdown     = document.getElementById('brand_dropdown');
    const clearBtn     = document.getElementById('brand_clear');
    const feedback     = document.getElementById('brand_feedback');
    const addSection   = document.getElementById('brand_add_section');
    const addLabel     = document.getElementById('brand_add_label');
    const checkBtn     = document.getElementById('brand_check_btn');
    let debounceTimer  = null;
    let currentQuery   = '';

    function showFeedback(html, type = 'info') {
        feedback.innerHTML = `<div class="alert alert-${type} py-2 px-3 mb-0 small">${html}</div>`;
        feedback.style.display = '';
    }
    function clearFeedback() { feedback.style.display = 'none'; feedback.innerHTML = ''; }
    function hideDropdown()  { dropdown.style.display = 'none'; dropdown.innerHTML = ''; }
    function showAddSection(q) { addLabel.textContent = q; addSection.style.display = ''; }
    function hideAddSection()  { addSection.style.display = 'none'; }

    function selectBrand(id, name) {
        hiddenInput.value = id; searchInput.value = name;
        clearBtn.style.display = '';
        hideDropdown(); hideAddSection(); clearFeedback();
        showFeedback(`<i data-lucide="check-circle" class="fs-13 me-1"></i> Brand set to <strong>${escHtml(name)}</strong>`, 'success');
        lucide.createIcons();
    }
    function clearSelection() {
        hiddenInput.value = ''; searchInput.value = '';
        clearBtn.style.display = 'none';
        hideDropdown(); hideAddSection(); clearFeedback();
        searchInput.focus();
    }

    searchInput.addEventListener('input', function () {
        const q = this.value.trim();
        currentQuery = q;
        hiddenInput.value = '';
        clearBtn.style.display = q ? '' : 'none';
        hideAddSection(); clearFeedback();
        if (q.length < 2) { hideDropdown(); return; }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            fetch(`{{ route('seller.brands.search') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(brands => {
                    if (!brands.length) { hideDropdown(); showAddSection(q); return; }
                    dropdown.innerHTML = '';
                    const addItem = document.createElement('button');
                    addItem.type = 'button';
                    addItem.className = 'list-group-item list-group-item-action d-flex align-items-center gap-2 text-primary fw-semibold';
                    addItem.innerHTML = `<i data-lucide="plus-circle" class="fs-14"></i> Add "<span>${escHtml(q)}</span>" as new brand`;
                    addItem.addEventListener('click', () => initiateCreate(q));
                    dropdown.appendChild(addItem);
                    const divider = document.createElement('div');
                    divider.className = 'list-group-item list-group-item-light py-1 small text-muted fw-semibold';
                    divider.textContent = 'Existing brands';
                    dropdown.appendChild(divider);
                    brands.forEach(brand => {
                        const item = document.createElement('button');
                        item.type = 'button';
                        item.className = 'list-group-item list-group-item-action';
                        const escaped = escHtml(brand.name);
                        const re = new RegExp(`(${escHtml(q).replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                        item.innerHTML = escaped.replace(re, '<mark class="p-0 bg-warning-subtle">$1</mark>');
                        item.addEventListener('click', () => selectBrand(brand.id, brand.name));
                        dropdown.appendChild(item);
                    });
                    dropdown.style.display = '';
                    lucide.createIcons();
                })
                .catch(() => hideDropdown());
        }, 280);
    });

    function initiateCreate(q) {
        hideDropdown(); hideAddSection(); clearFeedback();
        showFeedback('<span class="spinner-border spinner-border-sm me-2"></span>Checking for similar brands…');
        fetch('{{ route('seller.brands.check') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name: q }),
        }).then(r => r.json()).then(data => {
            clearFeedback();
            if (data.status === 'exists') {
                selectBrand(data.brand.id, data.brand.name);
                showFeedback(`<i data-lucide="info" class="fs-13 me-1"></i> Already exists as <strong>${escHtml(data.brand.name)}</strong> — selected.`, 'info');
                lucide.createIcons(); return;
            }
            if (data.status === 'similar') {
                const names = data.brands.map(b => `<strong>${escHtml(b.name)}</strong>`).join(', ');
                feedback.innerHTML = `<div class="alert alert-warning py-2 px-3 mb-0 small">
                    <i data-lucide="alert-triangle" class="fs-13 me-1"></i>
                    Similar brand(s) already exist: ${names}.<br>
                    Are you sure <strong>"${escHtml(q)}"</strong> is a completely different brand?
                    <div class="mt-2 d-flex gap-2">
                        ${data.brands.map(b => `<button type="button" class="btn btn-sm btn-outline-secondary use-existing-btn" data-id="${b.id}" data-name="${escHtml(b.name)}">Use "${escHtml(b.name)}"</button>`).join('')}
                        <button type="button" id="force_create_btn" class="btn btn-sm btn-danger">Create anyway</button>
                    </div></div>`;
                feedback.style.display = '';
                lucide.createIcons();
                feedback.querySelectorAll('.use-existing-btn').forEach(btn => {
                    btn.addEventListener('click', () => selectBrand(btn.dataset.id, btn.dataset.name));
                });
                document.getElementById('force_create_btn').addEventListener('click', () => doCreate(q, true));
                return;
            }
            doCreate(q, false);
        }).catch(() => showFeedback('Network error. Please try again.', 'danger'));
    }

    function doCreate(q, force) {
        clearFeedback();
        showFeedback('<span class="spinner-border spinner-border-sm me-2"></span>Creating brand…');
        fetch('{{ route('seller.brands.store') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ name: q, force: force }),
        }).then(r => r.json()).then(data => {
            if (data.status === 'created' || data.status === 'exists') {
                selectBrand(data.brand.id, data.brand.name);
                if (data.status === 'created') { showFeedback(`<i data-lucide="check-circle" class="fs-13 me-1"></i> ${data.message}`, 'success'); lucide.createIcons(); }
            } else { showFeedback(data.message || 'Unexpected error.', 'warning'); }
        }).catch(() => showFeedback('Network error. Please try again.', 'danger'));
    }

    clearBtn.addEventListener('click', clearSelection);
    document.addEventListener('click', e => {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) hideDropdown();
    });
    searchInput.addEventListener('keydown', function (e) {
        const items = [...dropdown.querySelectorAll('button.list-group-item-action:not(.list-group-item-light)')];
        if (!items.length) return;
        const idx = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') { e.preventDefault(); (items[idx + 1] || items[0]).focus(); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); (items[idx - 1] || items[items.length - 1]).focus(); }
        else if (e.key === 'Escape') hideDropdown();
    });
    checkBtn.addEventListener('click', () => initiateCreate(currentQuery));
})();

// ── Edit-page: chunked image uploader (standalone upload button) ──────────────
(function () {
    const CHUNK_SIZE  = 3;
    const PRODUCT_ID  = {{ $product->id }};
    const CHUNKED_URL = '{{ route("seller.products.images.chunked", $product) }}';
    const CSRF        = '{{ csrf_token() }}';

    const dropZone    = document.getElementById('image-drop-zone');
    const fileInput   = document.getElementById('image-file-input');
    const previewGrid = document.getElementById('image-preview-grid');
    const statusBox   = document.getElementById('image-upload-status');
    const uploadLabel = document.getElementById('image-upload-label');
    const uploadBar   = document.getElementById('image-upload-bar');
    const uploadBtn   = document.getElementById('upload-images-btn');
    const countLabel  = document.getElementById('upload-count-label');

    let pendingFiles = [];

    function syncUploadBtn() {
        uploadBtn.disabled = pendingFiles.length === 0;
        countLabel.textContent = pendingFiles.length > 0
            ? `${pendingFiles.length} image${pendingFiles.length !== 1 ? 's' : ''} selected`
            : '';
    }

    // ── Drag & drop ───────────────────────────────────────────
    dropZone.addEventListener('dragover',  e => { e.preventDefault(); dropZone.style.borderColor = 'var(--bs-primary)'; });
    dropZone.addEventListener('dragleave', ()  => { dropZone.style.borderColor = ''; });
    dropZone.addEventListener('drop', e => {
        e.preventDefault(); dropZone.style.borderColor = '';
        addFiles([...e.dataTransfer.files]);
    });
    dropZone.addEventListener('click', e => { if (e.target !== fileInput) fileInput.click(); });
    fileInput.addEventListener('change', () => { addFiles([...fileInput.files]); fileInput.value = ''; });

    const MAX_FILE_BYTES = 5 * 1024 * 1024; // 5 MB

    function addFiles(files) {
        const oversized = [];
        files.filter(f => f.type.startsWith('image/')).forEach(f => {
            if (f.size > MAX_FILE_BYTES) { oversized.push(f.name); return; }
            if (pendingFiles.find(p => p.name === f.name && p.size === f.size)) return;
            pendingFiles.push(f);
            renderPreview(f);
        });
        if (oversized.length) {
            showSizeWarning(oversized);
        }
        syncUploadBtn();
    }

    function showSizeWarning(names) {
        const existing = document.getElementById('img-size-warning');
        if (existing) existing.remove();
        const alert = document.createElement('div');
        alert.id = 'img-size-warning';
        alert.className = 'alert alert-warning alert-dismissible mt-2 mb-0 py-2 px-3 small';
        alert.innerHTML = `<i data-lucide="alert-triangle" class="fs-13 me-1"></i>
            <strong>${names.length} file${names.length > 1 ? 's' : ''} skipped</strong> — exceeds the 5 MB limit:
            ${names.map(n => `<em>${escHtml(n)}</em>`).join(', ')}.
            Please compress or resize before uploading.
            <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="alert"></button>`;
        dropZone.insertAdjacentElement('afterend', alert);
        lucide.createIcons();
    }

    function renderPreview(file) {
        const reader = new FileReader();
        reader.onload = evt => {
            const wrap = document.createElement('div');
            wrap.className = 'position-relative rounded overflow-hidden';
            wrap.style.cssText = 'width:80px;height:80px;flex-shrink:0;';
            wrap.dataset.fileName = file.name;
            wrap.dataset.fileSize = file.size;
            wrap.innerHTML = `
                <img src="${evt.target.result}" class="w-100 h-100" style="object-fit:cover;display:block;">
                <button type="button"
                        class="btn btn-danger position-absolute top-0 end-0 p-0 lh-1 border-0 rounded-0"
                        style="width:20px;height:20px;font-size:12px;line-height:20px;" title="Remove">×</button>
                <div class="position-absolute bottom-0 start-0 end-0 text-white text-center img-status-badge"
                     style="font-size:9px;background:rgba(0,0,0,.55);display:none;padding:2px 0;"></div>`;
            wrap.querySelector('button').addEventListener('click', () => {
                pendingFiles = pendingFiles.filter(p => !(p.name === file.name && p.size == file.size));
                wrap.remove();
                syncUploadBtn();
            });
            previewGrid.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    }

    function setBadge(file, text, color) {
        const wrap = [...previewGrid.children].find(
            w => w.dataset.fileName === file.name && w.dataset.fileSize == file.size
        );
        if (!wrap) return;
        const badge = wrap.querySelector('.img-status-badge');
        badge.textContent = text;
        badge.style.display = '';
        if (color === 'success') badge.style.background = 'rgba(25,135,84,.8)';
        if (color === 'danger')  badge.style.background = 'rgba(220,53,69,.8)';
    }

    // ── Upload button ─────────────────────────────────────────
    uploadBtn.addEventListener('click', async function () {
        if (pendingFiles.length === 0) return;

        uploadBtn.disabled = true;
        statusBox.style.display = '';
        const total  = pendingFiles.length;
        let uploaded = 0;
        let failed   = 0;

        const chunks = [];
        for (let i = 0; i < pendingFiles.length; i += CHUNK_SIZE) {
            chunks.push(pendingFiles.slice(i, i + CHUNK_SIZE));
        }

        for (const chunk of chunks) {
            uploadLabel.textContent = `Uploading… ${uploaded} / ${total}`;
            uploadBar.style.width   = Math.round((uploaded / total) * 100) + '%';
            chunk.forEach(f => setBadge(f, 'uploading…', null));

            const fd = new FormData();
            fd.append('_token', CSRF);
            chunk.forEach(f => fd.append('images[]', f));

            try {
                const res  = await fetch(CHUNKED_URL, {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    fd,
                });
                const json = await res.json();
                if (json.success) {
                    chunk.forEach(f => setBadge(f, '✓', 'success'));
                    uploaded += chunk.length;
                    // Append new image cards to the existing grid (no page reload)
                    json.images.forEach(img => appendImageCard(img));
                    // Remove the "no images" notice if present
                    const notice = document.getElementById('no-images-notice');
                    if (notice) notice.remove();
                } else {
                    chunk.forEach(f => setBadge(f, '✗', 'danger'));
                    failed += chunk.length;
                }
            } catch {
                chunk.forEach(f => setBadge(f, '✗', 'danger'));
                failed += chunk.length;
            }
        }

        uploadLabel.textContent = failed === 0
            ? `Done! ${uploaded} image${uploaded !== 1 ? 's' : ''} uploaded.`
            : `Done. ${uploaded} uploaded, ${failed} failed.`;
        uploadBar.style.width = '100%';
        uploadBar.classList.remove('progress-bar-animated');
        countLabel.textContent = '';

        const failedFiles = [];
        [...previewGrid.children].forEach(wrap => {
            const badge = wrap.querySelector('.img-status-badge');
            if (badge && badge.textContent === 'âœ—') {
                const failedFile = pendingFiles.find(
                    file => file.name === wrap.dataset.fileName && String(file.size) === wrap.dataset.fileSize
                );

                if (failedFile) {
                    failedFiles.push(failedFile);
                }
            }
        });

        pendingFiles = failedFiles;
        syncUploadBtn();
    });

    // Append a newly-uploaded image card to the existing images grid without reload
    function appendImageCard(img) {
        const grid = document.getElementById('existing-images-grid');
        const col  = document.createElement('div');
        col.className = 'col-md-3 col-sm-4 col-6';
        col.id = `img-card-${img.id}`;
        col.innerHTML = `
            <div class="card h-100">
                <img src="${img.url || img.storageUrl || img.path}" class="card-img-top" alt="Product Image" style="height:200px;object-fit:cover;">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center gap-1">
                        <form action="{{ route('seller.products.set-primary', $product) }}" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="${CSRF}">
                            <input type="hidden" name="image_id" value="${img.id}">
                            <button type="submit" class="btn btn-sm btn-outline-primary">Set Primary</button>
                        </form>
                        <form action="/seller/product-images/${img.id}" method="POST" class="d-inline">
                            <input type="hidden" name="_token" value="${CSRF}">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-soft-danger"
                                    onclick="return confirm('Delete this image?')">
                                <i data-lucide="trash-2" class="fs-14"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>`;
        grid.appendChild(col);
        lucide.createIcons();
    }
})();

// ── Edit-page: main form submit (text-only, standard redirect) ────────────────
// The update form has no images so it can submit normally.
// We just show a status label while it processes.
document.getElementById('product-edit-form').addEventListener('submit', function () {
    const btn    = document.getElementById('submit-btn');
    const status = document.getElementById('submit-status');
    btn.disabled = true;
    status.style.display = '';
    status.textContent = 'Saving…';
});

(function () {
    const TRANSIT = 1; // must match Product::TRANSIT_DAYS
 
    const cards      = document.querySelectorAll('.fulfillment_card');
    const radios     = document.querySelectorAll('.fulfillment-radio');
    const wrapper    = document.getElementById('max_ready_days_wrapper');
    const daysInput  = document.getElementById('max_ready_days');
    const preview    = document.getElementById('estimate_preview');
    const previewTxt = document.getElementById('estimate_preview_text');
 
    const CARD_COLORS = {
        in_stock:      { border: 'var(--thm-color,#714e32)', bg: '#fdf8f4' },
        pre_order:     { border: '#d97706',                  bg: '#fff8ec' },
        made_to_order: { border: '#7c3aed',                  bg: '#faf5ff' },
    };
    const DEFAULT_COLOR = { border: '#e0e0e0', bg: '#fff' };
 
    function updateCards(selectedValue) {
        cards.forEach(card => {
            const radio   = card.querySelector('.fulfillment-radio');
            const isSelected = radio.value === selectedValue;
            const colors  = isSelected ? (CARD_COLORS[radio.value] || DEFAULT_COLOR) : DEFAULT_COLOR;
            card.style.borderColor  = colors.border;
            card.style.background   = colors.bg;
        });
 
        // Show/hide the days input
        const needsDays = selectedValue === 'pre_order' || selectedValue === 'made_to_order';
        wrapper.style.display = needsDays ? 'block' : 'none';
 
        if (!needsDays) {
            daysInput.removeAttribute('required');
            preview.style.display = 'none';
        } else {
            daysInput.setAttribute('required', 'required');
            updatePreview();
        }
    }
 
    function updatePreview() {
        const days = parseInt(daysInput.value, 10);
        if (!days || days < 1) {
            preview.style.display = 'none';
            return;
        }
 
        const minDays = TRANSIT + 1;
        const maxDays = days + TRANSIT;
 
        let label;
        if (minDays === maxDays) {
            label = minDays + ' day' + (minDays === 1 ? '' : 's');
        } else {
            label = minDays + '–' + maxDays + ' days';
        }
 
        previewTxt.textContent = 'Estimated delivery: ' + label + ' after payment';
        preview.style.display  = 'block';
    }
 
    // Wire up radio change
    radios.forEach(radio => {
        radio.addEventListener('change', () => updateCards(radio.value));
    });
 
    // Wire up days input change
    if (daysInput) {
        daysInput.addEventListener('input', updatePreview);
    }
 
    // Card click also checks the radio
    cards.forEach(card => {
        card.addEventListener('click', () => {
            const radio = card.querySelector('.fulfillment-radio');
            radio.checked = true;
            updateCards(radio.value);
        });
    });
 
    // Init on page load with current value
    const checked = document.querySelector('.fulfillment-radio:checked');
    if (checked) {
        updateCards(checked.value);
    }
})();
lucide.createIcons();
</script>
@endpush
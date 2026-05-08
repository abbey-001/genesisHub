{{--
    resources/views/seller/coupons/create.blade.php
    AND
    resources/views/seller/coupons/edit.blade.php
    Both extend this same partial form. Just set $coupon = null for create.
--}}
@extends('seller.layouts.app')

@section('title', isset($coupon) ? 'Edit Coupon' : 'Create Coupon')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-10">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ isset($coupon) ? 'Edit Coupon' : 'Create New Coupon' }}</h4>
            <a href="{{ route('seller.coupons.index') }}" class="btn btn-secondary btn-sm">
                <i data-lucide="arrow-left" class="fs-14"></i> Back to Coupons
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ isset($coupon) ? route('seller.coupons.update', $coupon) : route('seller.coupons.store') }}"
                      method="POST">
                    @csrf
                    @isset($coupon)
                        @method('PUT')
                    @endisset

                    {{-- ─────────────── BASIC INFO ─────────────── --}}
                    <h6 class="fw-bold text-uppercase text-muted mb-3 fs-12">Basic Info</h6>
                    <div class="row g-3 mb-4">

                        {{-- Code --}}
                        <div class="col-md-6">
                            <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text"
                                       name="code"
                                       class="form-control text-uppercase @error('code') is-invalid @enderror"
                                       value="{{ old('code', $coupon->code ?? $suggestedCode ?? '') }}"
                                       placeholder="e.g. SAVE20"
                                       maxlength="50"
                                       required
                                       style="text-transform:uppercase">
                                <button type="button" class="btn btn-outline-secondary" id="generate-code-btn" title="Generate random code">
                                    <i data-lucide="refresh-cw" class="fs-14"></i>
                                </button>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Customers enter this at checkout. Must be unique.</small>
                        </div>

                        {{-- Scope --}}
                        <div class="col-md-6">
                            <label class="form-label">Applies To</label>
                            <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
                                <option value="">All My Products</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            {{ old('product_id', $coupon->product_id ?? '') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to apply to all products in your shop.</small>
                        </div>

                        {{-- Description --}}
                        <div class="col-12">
                            <label class="form-label">Description <span class="text-muted">(optional)</span></label>
                            <input type="text"
                                   name="description"
                                   class="form-control @error('description') is-invalid @enderror"
                                   value="{{ old('description', $coupon->description ?? '') }}"
                                   placeholder="e.g. Summer sale 20% off"
                                   maxlength="255">
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ─────────────── DISCOUNT ─────────────── --}}
                    <h6 class="fw-bold text-uppercase text-muted mb-3 fs-12">Discount</h6>
                    <div class="row g-3 mb-4">

                        {{-- Type --}}
                        <div class="col-md-4">
                            <label class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select name="type" id="discount-type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="percent" {{ old('type', $coupon->type ?? 'percent') === 'percent' ? 'selected' : '' }}>
                                    Percentage (%)
                                </option>
                                <option value="fixed" {{ old('type', $coupon->type ?? '') === 'fixed' ? 'selected' : '' }}>
                                    Fixed Amount (₦)
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Value --}}
                        <div class="col-md-4">
                            <label class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="discount-prefix">%</span>
                                <input type="number"
                                       name="value"
                                       class="form-control @error('value') is-invalid @enderror"
                                       value="{{ old('value', $coupon->value ?? '') }}"
                                       min="0.01" step="0.01"
                                       placeholder="20"
                                       required>
                                @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Max Discount (percent only) --}}
                        <div class="col-md-4" id="max-discount-wrapper">
                            <label class="form-label">Max Discount Cap (₦) <span class="text-muted">(optional)</span></label>
                            <input type="number"
                                   name="max_discount_amount"
                                   class="form-control @error('max_discount_amount') is-invalid @enderror"
                                   value="{{ old('max_discount_amount', $coupon->max_discount_amount ?? '') }}"
                                   min="0" step="0.01"
                                   placeholder="e.g. 5000">
                            @error('max_discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Maximum ₦ saved regardless of cart total.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ─────────────── CONSTRAINTS ─────────────── --}}
                    <h6 class="fw-bold text-uppercase text-muted mb-3 fs-12">Constraints</h6>
                    <div class="row g-3 mb-4">

                        {{-- Min order --}}
                        <div class="col-md-4">
                            <label class="form-label">Minimum Order Amount (₦)</label>
                            <input type="number"
                                   name="min_order_amount"
                                   class="form-control @error('min_order_amount') is-invalid @enderror"
                                   value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}"
                                   min="0" step="0.01"
                                   placeholder="0">
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">0 = no minimum.</small>
                        </div>

                        {{-- Max uses total --}}
                        <div class="col-md-4">
                            <label class="form-label">Total Usage Limit</label>
                            <input type="number"
                                   name="max_uses"
                                   class="form-control @error('max_uses') is-invalid @enderror"
                                   value="{{ old('max_uses', $coupon->max_uses ?? '') }}"
                                   min="1"
                                   placeholder="Unlimited">
                            @error('max_uses')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank for unlimited uses.</small>
                        </div>

                        {{-- Max uses per user --}}
                        <div class="col-md-4">
                            <label class="form-label">Uses Per Customer <span class="text-danger">*</span></label>
                            <input type="number"
                                   name="max_uses_per_user"
                                   class="form-control @error('max_uses_per_user') is-invalid @enderror"
                                   value="{{ old('max_uses_per_user', $coupon->max_uses_per_user ?? 1) }}"
                                   min="1" required>
                            @error('max_uses_per_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ─────────────── VALIDITY ─────────────── --}}
                    <h6 class="fw-bold text-uppercase text-muted mb-3 fs-12">Validity Period</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="datetime-local"
                                   name="starts_at"
                                   class="form-control @error('starts_at') is-invalid @enderror"
                                   value="{{ old('starts_at', isset($coupon->starts_at) ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}">
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to activate immediately.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="datetime-local"
                                   name="expires_at"
                                   class="form-control @error('expires_at') is-invalid @enderror"
                                   value="{{ old('expires_at', isset($coupon->expires_at) ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}">
                            @error('expires_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank for no expiry.</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- ─────────────── ACTIVE TOGGLE ─────────────── --}}
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input"
                               type="checkbox"
                               id="is_active"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-medium" for="is_active">Active (visible to customers)</label>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i data-lucide="save" class="fs-16"></i>
                            {{ isset($coupon) ? 'Update Coupon' : 'Create Coupon' }}
                        </button>
                        <a href="{{ route('seller.coupons.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Live Preview Card --}}
        <div class="card mt-3 border-dashed" id="preview-card">
            <div class="card-body">
                <h6 class="card-title mb-3"><i data-lucide="eye" class="fs-16 me-1"></i> Coupon Preview</h6>
                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded">
                    <div class="coupon-tag-icon text-danger fs-32">
                        <i data-lucide="ticket"></i>
                    </div>
                    <div>
                        <div class="fw-bold font-monospace fs-18 text-danger" id="preview-code">—</div>
                        <div class="text-muted" id="preview-label">—</div>
                        <div class="text-muted small" id="preview-scope">—</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Discount type → prefix label & max-cap visibility ──
const typeSelect  = document.getElementById('discount-type');
const prefixSpan  = document.getElementById('discount-prefix');
const maxWrapper  = document.getElementById('max-discount-wrapper');

function syncDiscountType() {
    if (typeSelect.value === 'percent') {
        prefixSpan.textContent = '%';
        maxWrapper.style.display = '';
    } else {
        prefixSpan.textContent = '₦';
        maxWrapper.style.display = 'none';
    }
}
typeSelect.addEventListener('change', syncDiscountType);
syncDiscountType();

// ── Generate random code ──
document.getElementById('generate-code-btn').addEventListener('click', function () {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < 8; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
    document.querySelector('input[name="code"]').value = code;
    updatePreview();
});

// ── Live Preview ──
function updatePreview() {
    const code  = document.querySelector('input[name="code"]').value.toUpperCase() || '—';
    const type  = typeSelect.value;
    const value = parseFloat(document.querySelector('input[name="value"]').value) || 0;
    const scope = document.querySelector('select[name="product_id"]');
    const scopeText = scope.options[scope.selectedIndex]?.text || 'All Products';

    let label = '—';
    if (value > 0) {
        label = type === 'percent' ? `${value}% OFF` : `₦${value.toLocaleString('en-NG', {minimumFractionDigits: 2})} OFF`;
        const maxCap = parseFloat(document.querySelector('input[name="max_discount_amount"]')?.value);
        if (type === 'percent' && !isNaN(maxCap) && maxCap > 0) {
            label += ` (max ₦${maxCap.toLocaleString('en-NG', {minimumFractionDigits: 0})})`;
        }
    }

    document.getElementById('preview-code').textContent  = code;
    document.getElementById('preview-label').textContent = label;
    document.getElementById('preview-scope').textContent = `Applies to: ${scopeText}`;
}

['input', 'change'].forEach(evt => {
    document.querySelector('input[name="code"]').addEventListener(evt, updatePreview);
    document.querySelector('input[name="value"]').addEventListener(evt, updatePreview);
    document.querySelector('input[name="max_discount_amount"]')?.addEventListener(evt, updatePreview);
    document.querySelector('select[name="product_id"]').addEventListener(evt, updatePreview);
    typeSelect.addEventListener(evt, updatePreview);
});
updatePreview();

lucide.createIcons();
</script>
@endpush
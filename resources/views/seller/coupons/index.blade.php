{{-- resources/views/seller/coupons/index.blade.php --}}
@extends('seller.layouts.app')

@section('title', 'My Coupons')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <h5 class="card-title mb-0">My Coupons</h5>

                    <div class="d-flex gap-2 flex-wrap">
                        {{-- Search / filter form --}}
                        <form action="{{ route('seller.coupons.index') }}" method="GET" class="d-flex gap-2">
                            <input type="search" name="search" class="form-control form-control-sm"
                                   placeholder="Search code..." value="{{ request('search') }}">

                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>

                            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                            <a href="{{ route('seller.coupons.index') }}" class="btn btn-sm btn-secondary">Reset</a>
                        </form>

                        <a href="{{ route('seller.coupons.create') }}" class="btn btn-sm btn-success">
                            <i data-lucide="plus" class="fs-14"></i> New Coupon
                        </a>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover text-nowrap mb-0">
                    <thead class="text-uppercase fs-12">
                        <tr>
                            <th>Code</th>
                            <th>Scope</th>
                            <th>Discount</th>
                            <th>Min Order</th>
                            <th>Uses</th>
                            <th>Expires</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($coupons as $coupon)
                        <tr>
                            {{-- Code --}}
                            <td>
                                <span class="fw-bold font-monospace">{{ $coupon->code }}</span>
                                @if($coupon->description)
                                    <br><small class="text-muted">{{ Str::limit($coupon->description, 40) }}</small>
                                @endif
                            </td>

                            {{-- Scope --}}
                            <td>
                                @if($coupon->product_id)
                                    <span class="badge badge-soft-info">
                                        <i data-lucide="package" class="fs-11"></i>
                                        {{ Str::limit($coupon->product->name ?? 'Deleted Product', 30) }}
                                    </span>
                                @else
                                    <span class="badge badge-soft-primary">
                                        <i data-lucide="store" class="fs-11"></i> All Products
                                    </span>
                                @endif
                            </td>

                            {{-- Discount --}}
                            <td>
                                <span class="badge bg-danger">{{ $coupon->discount_label }}</span>
                                @if($coupon->type === 'percent' && $coupon->max_discount_amount)
                                    <br><small class="text-muted">Max ₦{{ number_format($coupon->max_discount_amount, 0) }}</small>
                                @endif
                            </td>

                            {{-- Min Order --}}
                            <td>
                                @if($coupon->min_order_amount > 0)
                                    ₦{{ number_format($coupon->min_order_amount, 0) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            {{-- Uses --}}
                            <td>
                                {{ $coupon->used_count }}
                                @if($coupon->max_uses)
                                    / {{ $coupon->max_uses }}
                                @else
                                    / <span class="text-muted">∞</span>
                                @endif
                            </td>

                            {{-- Expires --}}
                            <td>
                                @if($coupon->expires_at)
                                    <span class="{{ now()->gt($coupon->expires_at) ? 'text-danger' : 'text-muted' }}">
                                        {{ $coupon->expires_at->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>

                            {{-- Status toggle --}}
                            <td>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input coupon-toggle"
                                           type="checkbox"
                                           role="switch"
                                           id="toggle-{{ $coupon->id }}"
                                           data-coupon-id="{{ $coupon->id }}"
                                           {{ $coupon->is_active ? 'checked' : '' }}>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('seller.coupons.edit', $coupon) }}"
                                       class="btn btn-sm btn-soft-secondary" title="Edit">
                                        <i data-lucide="square-pen" class="fs-16"></i>
                                    </a>
                                    <button type="button"
                                            class="btn btn-sm btn-soft-danger"
                                            onclick="deleteCoupon({{ $coupon->id }})"
                                            title="Delete">
                                        <i data-lucide="trash-2" class="fs-16"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i data-lucide="ticket" class="fs-48 text-muted mb-3 d-block mx-auto"></i>
                                <p class="mb-2 text-muted">No coupons yet</p>
                                <a href="{{ route('seller.coupons.create') }}" class="btn btn-primary btn-sm">
                                    Create Your First Coupon
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($coupons->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $coupons->firstItem() }} to {{ $coupons->lastItem() }}
                        of {{ $coupons->total() }} coupons
                    </div>
                    {{ $coupons->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Hidden delete form --}}
<form id="delete-coupon-form" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
// Delete
function deleteCoupon(id) {
    if (!confirm('Delete this coupon? This cannot be undone.')) return;
    const form = document.getElementById('delete-coupon-form');
    form.action = `/seller/coupons/${id}`;
    form.submit();
}

// Toggle active status via AJAX
document.querySelectorAll('.coupon-toggle').forEach(toggle => {
    toggle.addEventListener('change', async function () {
        const id    = this.dataset.couponId;
        const el    = this;
        const token = document.querySelector('meta[name="csrf-token"]').content;

        try {
            const res  = await fetch(`/seller/coupons/${id}/toggle`, {
                method:  'POST',
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            });
            const data = await res.json();

            if (!data.success) {
                el.checked = !el.checked; // revert
                alert('Failed to update coupon status.');
            }
        } catch (e) {
            el.checked = !el.checked;
            alert('Network error. Please try again.');
        }
    });
});

lucide.createIcons();
</script>
@endpush
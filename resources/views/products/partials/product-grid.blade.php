{{-- Product Grid Partial — rendered for AJAX filter/pagination responses --}}
@forelse($products as $product)
    @include('products.partials.product-card', ['product' => $product])
@empty
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-search"></i></div>
        <h4>No products found</h4>
        <p>Try adjusting your filters or search terms</p>
        <a href="{{ route('product.index') }}"
           style="display:inline-block;background:var(--brand);color:#fff;padding:10px 24px;border-radius:50px;text-decoration:none;font-weight:600;margin-top:12px;">
           Clear Filters
        </a>
    </div>
@endforelse
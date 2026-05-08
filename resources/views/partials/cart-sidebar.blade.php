{{-- resources/views/partials/cart-sidebar.blade.php --}}

@if($cart && count($cart) > 0)
  @foreach($cart as $cartKey => $item)
  <li class="list_content gh-csi" data-product-id="{{ $cartKey }}">
    <div class="gh-csi-wrap">
      <a href="{{ route('product.show', $item['id']) }}" class="gh-csi-img-link">
        <img src="{{ asset('public/storage/' . $item['image']) }}" alt="{{ $item['name'] }}" class="gh-csi-img">
      </a>
      <div class="gh-csi-info">
        <a href="{{ route('product.show', $item['id']) }}" class="gh-csi-name">
          {{ Str::limit($item['name'], 36) }}
        </a>

        @if(!empty($item['variant_label']))
          <div class="gh-csi-variant">{{ $item['variant_label'] }}</div>
        @endif

        <div class="gh-csi-controls">
          <div class="gh-csi-qty">
            <button class="gh-csi-btn update-cart-quantity" data-id="{{ $cartKey }}" data-action="decrease" type="button">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
            <span class="gh-csi-num quantity-input-{{ $cartKey }}">{{ $item['quantity'] }}</span>
            <button class="gh-csi-btn update-cart-quantity" data-id="{{ $cartKey }}" data-action="increase" type="button">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </button>
          </div>
          <span class="gh-csi-price item-total-{{ $cartKey }}">₦{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
        </div>
      </div>
      <button type="button" class="gh-csi-remove remove-from-cart" data-id="{{ $cartKey }}" title="Remove">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
  </li>
  @endforeach

@else
  <li class="gh-csi-empty">
    <div class="gh-csi-empty-icon">
      <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#c4956a" stroke-width="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
    </div>
    <p>Your cart is empty</p>
    <a href="{{ route('product.index') }}" class="gh-csi-shop-btn">Shop Now</a>
  </li>
@endif
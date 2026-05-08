@if(isset($recentlyViewed) && $recentlyViewed->isNotEmpty())
<section class="gh-rv pt0 pb30">
  <div class="container-fluid maxw1800">
    <div class="gh-rv-inner">

      <div class="gh-rv-header">
        <div class="gh-rv-title">
          <span class="gh-rv-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </span>
          <h2>Recently Viewed</h2>
        </div>
      </div>

      <div class="gh-rv-slider owl-carousel owl-theme dots_none nav_none">
        @foreach($recentlyViewed as $product)
        <div class="item">
          <div class="gh-rv-card">

            {{-- Image --}}
            <div class="gh-rv-img">
              <a href="{{ route('product.show', $product->slug) }}">
                <img src="{{ asset('public/storage/' . $product->main_image) }}"
                     alt="{{ $product->name }}" loading="lazy">
              </a>

              @if($product->discount_percentage)
              <span class="gh-rv-disc">-{{ $product->discount_percentage }}%</span>
              @endif

              @if($product->stock <= 5 && $product->stock > 0)
              <span class="gh-rv-low">{{ $product->stock }} left</span>
              @elseif($product->stock === 0)
              <span class="gh-rv-oos-badge">Out of Stock</span>
              @endif
            </div>

            {{-- Details --}}
            <div class="gh-rv-details">
              @if($product->brand)
              <div class="gh-rv-brand">{{ $product->brand->name }}</div>
              @endif

              <a href="{{ route('product.show', $product->slug) }}" class="gh-rv-name">
                {{ Str::limit($product->name, 55) }}
              </a>

              <div class="gh-rv-stars">
                @for($i=1;$i<=5;$i++)
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="{{ $i<=round($product->rating??0)?'#f59e0b':'#e5e7eb' }}"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                @endfor
                <span class="gh-rv-rcount">({{ $product->review_count ?? 0 }})</span>
              </div>

              <div class="gh-rv-price">
                @if($product->sale_price && $product->sale_price < $product->price)
                  <span class="gh-rv-curr">₦{{ number_format($product->sale_price,2) }}</span>
                  <del class="gh-rv-orig">₦{{ number_format($product->price,2) }}</del>
                @else
                  <span class="gh-rv-curr">₦{{ number_format($product->price,2) }}</span>
                @endif
              </div>
            </div>

          </div>
        </div>
        @endforeach
      </div>

    </div>
  </div>
</section>

<style>
.gh-rv { padding-bottom:30px; }
.gh-rv-inner {
  background:#fff;
  border-radius:12px;
  padding:24px 28px 28px;
  border:1px solid #f0ebe5;
  box-shadow:0 2px 16px rgba(113,78,50,.05);
}
.gh-rv-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid #f5f0eb; }
.gh-rv-title { display:flex; align-items:center; gap:10px; }
.gh-rv-title h2 { font-size:18px; font-weight:700; color:#1a1209; margin:0; }
.gh-rv-icon { width:34px; height:34px; background:linear-gradient(135deg,#f5ede5,#e8d5c4); border-radius:8px; display:flex; align-items:center; justify-content:center; color:#714e32; }

/* Card */
.gh-rv-card { border-radius:10px; overflow:hidden; border:1px solid #f0ebe5; background:#fff; transition:box-shadow .2s,transform .2s; }
.gh-rv-card:hover { box-shadow:0 6px 24px rgba(113,78,50,.12); transform:translateY(-2px); }

/* Image */
.gh-rv-img { position:relative; overflow:hidden; aspect-ratio:1/1; background:#f9f5f1; }
.gh-rv-img img { width:100%; height:100%; object-fit:cover; transition:transform .35s; }
.gh-rv-card:hover .gh-rv-img img { transform:scale(1.04); }
.gh-rv-disc { position:absolute; top:8px; left:8px; background:#e53935; color:#fff; font-size:11px; font-weight:700; padding:3px 7px; border-radius:4px; z-index:2; }
.gh-rv-low  { position:absolute; top:8px; right:8px; background:#f97316; color:#fff; font-size:11px; font-weight:600; padding:3px 7px; border-radius:4px; z-index:2; }
.gh-rv-oos-badge { position:absolute; top:8px; right:8px; background:#9ca3af; color:#fff; font-size:11px; font-weight:600; padding:3px 7px; border-radius:4px; z-index:2; }

/* Details */
.gh-rv-details { padding:12px 14px 14px; }
.gh-rv-brand { font-size:10px; font-weight:700; color:#c4956a; text-transform:uppercase; letter-spacing:.5px; margin-bottom:4px; }
.gh-rv-name { display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; font-size:13px; font-weight:600; color:#1a1209; text-decoration:none; line-height:1.4; transition:color .15s; }
.gh-rv-name:hover { color:#714e32; }
.gh-rv-stars { display:flex; align-items:center; gap:1px; margin:7px 0 5px; }
.gh-rv-rcount { font-size:11px; color:#9ca3af; margin-left:4px; }
.gh-rv-price { display:flex; align-items:baseline; gap:7px; flex-wrap:wrap; }
.gh-rv-curr { font-size:15px; font-weight:700; color:#714e32; }
.gh-rv-orig { font-size:12px; color:#9ca3af; }
</style>

@push('scripts')
<script>
$(document).ready(function(){
  if($('.gh-rv-slider').data('owl.carousel')===undefined){
    $('.gh-rv-slider').owlCarousel({
      loop:false, margin:12, nav:false, dots:false, autoplay:false,
      responsive:{ 0:{items:2}, 576:{items:3}, 768:{items:4}, 1200:{items:5}, 1600:{items:6} }
    });
  }
});
</script>
@endpush

@endif
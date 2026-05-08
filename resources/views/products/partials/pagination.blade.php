{{-- Pagination Partial — works both inline and via AJAX --}}
@if($products->hasPages())
<div class="listing-pagination" id="pagination-inner">
    {{-- Prev --}}
    @if($products->onFirstPage())
        <span class="pg-btn disabled">
            <i class="fas fa-angle-left"></i>
        </span>
    @else
        <button class="pg-btn" data-page="{{ $products->currentPage() - 1 }}">
            <i class="fas fa-angle-left"></i>
        </button>
    @endif

    {{-- Page numbers with smart ellipsis --}}
    @php
        $current  = $products->currentPage();
        $last     = $products->lastPage();
        $window   = 2; // pages each side of current
        $pages    = [];

        // Always show first
        $pages[] = 1;

        // Left ellipsis zone
        for ($p = max(2, $current - $window); $p <= min($last - 1, $current + $window); $p++) {
            $pages[] = $p;
        }

        // Always show last
        if ($last > 1) $pages[] = $last;

        $pages = array_unique($pages);
        sort($pages);
    @endphp

    @php $prev = 0; @endphp
    @foreach($pages as $page)
        @if($page - $prev > 1)
            <span class="pg-btn disabled" style="border:none;background:transparent;min-width:24px;">…</span>
        @endif
        @if($page === $current)
            <span class="pg-btn active">{{ $page }}</span>
        @else
            <button class="pg-btn" data-page="{{ $page }}">{{ $page }}</button>
        @endif
        @php $prev = $page; @endphp
    @endforeach

    {{-- Next --}}
    @if($products->hasMorePages())
        <button class="pg-btn" data-page="{{ $products->currentPage() + 1 }}">
            <i class="fas fa-angle-right"></i>
        </button>
    @else
        <span class="pg-btn disabled">
            <i class="fas fa-angle-right"></i>
        </span>
    @endif

    {{-- Info --}}
    <p class="pg-info">
        Showing
        <strong>{{ $products->firstItem() }}</strong>–<strong>{{ $products->lastItem() }}</strong>
        of <strong>{{ number_format($products->total()) }}</strong> products
    </p>
</div>
@endif
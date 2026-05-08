<div class="main-nav">
    <div class="d-flex justify-content-between main-logo-box">
        <div class="logo-box">
            <a href="{{ route('seller.dashboard') }}" class="logo-dark">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-sm" alt="logo">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-lg" alt="logo">
            </a>
            <a href="{{ route('seller.dashboard') }}" class="logo-light">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-sm" alt="logo">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-lg" alt="logo">
            </a>
        </div>
    </div>

    <div class="h-100" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" 
                   href="{{ route('seller.dashboard') }}">
                    <span class="nav-icon"><i data-lucide="house"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.products.*') ? 'active' : '' }}" 
                   href="#sidebarProducts" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="package"></i></span>
                    <span class="nav-text">Products</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('seller.products.*') ? 'show' : '' }}" id="sidebarProducts">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('seller.products.index') }}">All Products</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('seller.products.create') }}">Add Product</a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.orders.*') ? 'active' : '' }}" 
                   href="{{ route('seller.orders.index') }}">
                    <span class="nav-icon"><i data-lucide="shopping-cart"></i></span>
                    <span class="nav-text">Orders</span>
                    @if(isset($pendingOrdersCount) && $pendingOrdersCount > 0)
                        <span class="badge bg-danger badge-pill text-end">{{ $pendingOrdersCount }}</span>
                    @endif
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.reviews.*') ? 'active' : '' }}" 
                   href="{{ route('seller.reviews.index') }}">
                    <span class="nav-icon"><i data-lucide="star"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.payouts.*') ? 'active' : '' }}" 
                   href="{{ route('seller.payouts.index') }}">
                    <span class="nav-icon"><i data-lucide="wallet"></i></span>
                    <span class="nav-text">Payouts</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.copons.*') ? 'active' : '' }}" 
                   href="{{ route('seller.coupons.index') }}">
                    <span class="nav-icon"><i data-lucide="ticket"></i></span>
                    <span class="nav-text">Coupons</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.shop.*') ? 'active' : '' }}" 
                   href="{{ route('seller.shop.index') }}">
                    <span class="nav-icon"><i data-lucide="store"></i></span>
                    <span class="nav-text">Shop Settings</span>
                </a>
            </li>

            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('seller.settings.*') ? 'active' : '' }}" 
                   href="{{ route('seller.settings.index') }}">
                    <span class="nav-icon"><i data-lucide="settings"></i></span>
                    <span class="nav-text">Account Settings</span>
                </a>
            </li>

            <li class="menu-title mt-3">Quick Links</li>
           @if(auth('seller')->user()?->seller?->shop)
                <li class="menu-item">
                    <a class="menu-link" 
                       href="{{ route('shop.show', auth('seller')->user()->seller->shop->slug) }}" 
                       target="_blank">
                        <span class="nav-icon"><i data-lucide="external-link"></i></span>
                        <span class="nav-text">View Storefront</span>
                    </a>
                </li>
            @endif

        </ul>
    </div>
</div>
<header class="topbar d-flex">
    <div class="container-fluid">
        <div class="navbar-header">
            <div class="d-flex align-items-center gap-2">
                <!-- Mobile Menu Toggle -->
                <button type="button" class="btn btn-link d-md-none button-sm-hover button-toggle-menu">
                    <i data-lucide="menu" class="button-sm-hover-icon"></i>
                </button>

                <form class="app-search d-none d-md-block me-auto" action="{{ route('seller.products.index') }}" method="GET">
                    <div class="position-relative">
                        <input type="search" name="search" class="form-control" 
                               placeholder="Search products..." autocomplete="off" 
                               value="{{ request('search') }}">
                        <i data-lucide="search" class="search-widget-icon"></i>
                    </div>
                </form>
            </div>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <!-- Theme Toggle -->
                <div class="topbar-item">
                    <button type="button" class="topbar-button fs-24" id="light-dark-mode">
                        <i data-lucide="moon" class="light-mode"></i>
                        <i data-lucide="sun" class="dark-mode"></i>
                    </button>
                </div>

                <!-- Notifications -->
                <div class="dropdown topbar-item">
                    <button type="button" class="topbar-button" id="page-header-notifications-dropdown" 
                            data-bs-toggle="dropdown">
                        <i data-lucide="bell"></i>
                        @if(isset($unreadNotifications) && $unreadNotifications > 0)
                            <span class="topbar-badge text-bg-danger rounded-pill">{{ $unreadNotifications }}</span>
                        @endif
                    </button>
                    <div class="dropdown-menu pt-0 dropdown-lg dropdown-menu-end">
                        <div class="p-3 border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-16 fw-semibold">Notifications</h6>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar style="max-height: 280px;">
                            <a href="{{ route('seller.orders.index', ['status' => 'pending']) }}" 
                               class="dropdown-item py-3 border-bottom">
                                <p class="mb-0 fw-semibold">New Orders</p>
                                <p class="mb-0 text-muted">You have pending orders to process</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown topbar-item">
                    <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" 
                       data-bs-toggle="dropdown">
                        <span class="d-flex align-items-center gap-2">
                            <img class="rounded-circle" width="32" 
                                 src="{{ auth('seller')->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth('seller')->user()->name) }}"
                                 alt="user">
                            <span class="d-lg-flex flex-column gap-1 d-none">
                                <h5 class="my-0 text-reset fs-14">{{ auth('seller')->user()->name }}</h5>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="{{ route('seller.settings.index') }}">
                            <i data-lucide="user" class="fs-16 text-muted align-middle me-2"></i>
                            <span class="align-middle">My Account</span>
                        </a>
                        <a class="dropdown-item" href="{{ route('seller.shop.index') }}">
                            <i data-lucide="store" class="fs-16 text-muted align-middle me-2"></i>
                            <span class="align-middle">Shop Settings</span>
                        </a>
                        <div class="dropdown-divider my-1"></div>
                        <form method="POST" action="{{ route('seller.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i data-lucide="log-out" class="fs-16 text-muted align-middle me-2"></i>
                                <span class="align-middle">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
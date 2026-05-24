<header class="topbar d-flex">
    <div class="container-fluid">
        <div class="navbar-header">
            <div class="d-flex align-items-center gap-2">
                <!-- Mobile Menu Toggle -->
                <button type="button" class="btn btn-link d-md-none button-sm-hover button-toggle-menu">
                    <i data-lucide="menu" class="button-sm-hover-icon"></i>
                </button>

                <!-- Search -->
                <form class="app-search d-none d-md-block me-auto" action="#" method="GET">
                    <div class="position-relative">
                        <input type="search" name="search" class="form-control" 
                               placeholder="Search..." autocomplete="off">
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
                        <span class="topbar-badge text-bg-danger rounded-pill">5</span>
                    </button>
                    <div class="dropdown-menu pt-0 dropdown-lg dropdown-menu-end">
                        <div class="p-3 border-bottom">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-16 fw-semibold">Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="text-muted fs-14">Mark all read</a>
                                </div>
                            </div>
                        </div>
                        <div data-simplebar style="max-height: 280px;">
                            <a href="#" class="dropdown-item py-3 border-bottom">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i data-lucide="shopping-cart" class="fs-20 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-0 fw-semibold">New Order Received</p>
                                        <p class="mb-0 text-muted fs-13">Order #12345 - ₦25,000</p>
                                        <p class="mb-0 text-muted fs-12">2 mins ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item py-3 border-bottom">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i data-lucide="user-plus" class="fs-20 text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-0 fw-semibold">New Seller Application</p>
                                        <p class="mb-0 text-muted fs-13">TechShop Nigeria</p>
                                        <p class="mb-0 text-muted fs-12">15 mins ago</p>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item py-3 border-bottom">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <i data-lucide="alert-circle" class="fs-20 text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <p class="mb-0 fw-semibold">Delivery Failed</p>
                                        <p class="mb-0 text-muted fs-13">Order #12340 requires attention</p>
                                        <p class="mb-0 text-muted fs-12">1 hour ago</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="p-2 border-top text-center">
                            <a href="#" class="text-primary">View All Notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="dropdown topbar-item">
                    <a type="button" class="topbar-button p-0" id="page-header-user-dropdown" 
                       data-bs-toggle="dropdown">
                        <span class="d-flex align-items-center gap-2">
                            <img class="rounded-circle" width="32" 
                                 src="{{ auth()->guard('admin')->user()->avatar_url }}" 
                                 alt="{{ auth()->guard('admin')->user()->name }}">
                            <span class="d-lg-flex flex-column gap-1 d-none">
                                <h5 class="my-0 text-reset fs-14">{{ auth()->guard('admin')->user()->name }}</h5>
                                <span class="text-muted fs-12">{{ auth()->guard('admin')->user()->role_name }}</span>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <h6 class="dropdown-header">Welcome, {{ auth()->guard('admin')->user()->name }}!</h6>
                        
                        <a class="dropdown-item" href="#">
                            <i data-lucide="user" class="fs-16 text-muted align-middle me-2"></i>
                            <span class="align-middle">My Profile</span>
                        </a>
                        @if(auth()->guard('admin')->user()->role_id == 1)
                        <a class="dropdown-item" href="{{ route('admin.admin.telegram.index') }}">
                            <i data-lucide="settings" class="fs-16 text-muted align-middle me-2"></i>
                            <span class="align-middle">Telegram Settings</span>
                        </a>
                        @endif
                        <div class="dropdown-divider my-1"></div>
                        
                        <form method="POST" action="{{ route('admin.logout') }}">
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
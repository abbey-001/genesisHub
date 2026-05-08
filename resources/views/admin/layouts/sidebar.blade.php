<div class="main-nav">
    <div class="d-flex justify-content-between main-logo-box">
        <div class="logo-box">
            <a href="{{ route('admin.dashboard') }}" class="logo-dark">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-sm" alt="logo">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-lg" alt="logo">
            </a>
            <a href="{{ route('admin.dashboard') }}" class="logo-light">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-sm" alt="logo">
                <img src="{{ asset('public/image/auth-logo.png') }}" class="logo-lg" alt="logo">
            </a>
        </div>
    </div>

    <div class="h-100" data-simplebar>
        <ul class="navbar-nav" id="navbar-nav">
            
            <!-- Dashboard -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                   href="{{ route('admin.dashboard') }}">
                    <span class="nav-icon"><i data-lucide="house"></i></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <!-- Users Section -->
            <li class="menu-title mt-3">User Management</li>

            <!-- Customers -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}" 
                   href="#sidebarCustomers" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="users"></i></span>
                    <span class="nav-text">Customers</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.customers.*') ? 'show' : '' }}" id="sidebarCustomers">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.customers.index') }}">All Customers</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.customers.export') }}">Export Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Sellers -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}" 
                   href="#sidebarSellers" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="store"></i></span>
                    <span class="nav-text">Sellers</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.sellers.*') ? 'show' : '' }}" id="sidebarSellers">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.sellers.index') }}">All Sellers</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.sellers.applications') }}">Applications</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.sellers.export') }}">Export Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Riders -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.riders.*') ? 'active' : '' }}" 
                   href="#sidebarRiders" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="bike"></i></span>
                    <span class="nav-text">Delivery Companies</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.companies.*') ? 'show' : '' }}" id="sidebarRiders">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.companies.index') }}">All Companies</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.companies.create') }}">Create Company</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.companies.export') }}">Export Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Catalog Section -->
            <li class="menu-title mt-3">Catalog</li>

            <!-- Products -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" 
                   href="#sidebarProducts" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="package"></i></span>
                    <span class="nav-text">Products</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.products.*') ? 'show' : '' }}" id="sidebarProducts">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.products.index') }}">All Products</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.products.export') }}">Export Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Categories -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" 
                   href="{{ route('admin.categories.index') }}">
                    <span class="nav-icon"><i data-lucide="folder"></i></span>
                    <span class="nav-text">Categories</span>
                </a>
            </li>

            <!-- Operations Section -->
            <li class="menu-title mt-3">Operations</li>

            <!-- Orders -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" 
                   href="#sidebarOrders" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="shopping-cart"></i></span>
                    <span class="nav-text">Orders</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.orders.*') ? 'show' : '' }}" id="sidebarOrders">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.orders.index') }}">All Orders</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.orders.analytics') }}">Analytics</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.orders.export') }}">Export Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Deliveries -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" 
                   href="{{ route('admin.reviews.index') }}">
                    <span class="nav-icon"><i data-lucide="file-heart"></i></span>
                    <span class="nav-text">Reviews</span>
                </a>
            </li>

            <!-- Finance Section -->
            <li class="menu-title mt-3">Finance</li>

            <!-- Payouts -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.finance.payouts.*') ? 'active' : '' }}" 
                   href="#sidebarPayouts" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="wallet"></i></span>
                    <span class="nav-text">Sellers Payouts</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.finance.payouts.*') ? 'show' : '' }}" id="sidebarPayouts">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.finance.payouts.index') }}">All Payouts</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.finance.payouts.analytics') }}">Analytics</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.finance.payouts.export') }}">Export Data</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Wallets -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.finance.wallets.*') ? 'active' : '' }}" 
                   href="#sidebarWallets" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="credit-card"></i></span>
                    <span class="nav-text">Sellers Wallets</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.finance.wallets.*') ? 'show' : '' }}" id="sidebarWallets">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.finance.wallets.index') }}">All Wallets</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.finance.wallets.analytics') }}">Analytics</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.finance.wallets.exportTransactions') }}">Export Transactions</a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="menu-item ">
    <a href="{{ route('admin.delivery.payouts.index') }}" class="menu-link {{ request()->routeIs('admin.delivery.payouts.*') ? 'active' : '' }}">
    <span class="nav-icon"><i data-lucide="wallet"></i></span>
                    <span class="nav-text">Delivery payouts</span>
    </a>
</li>
            <!-- Refunds -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.finance.refunds.*') ? 'active' : '' }}" 
                   href="{{ route('admin.finance.refunds.index') }}">
                    <span class="nav-icon"><i data-lucide="arrow-left-circle"></i></span>
                    <span class="nav-text">Refunds</span>
                </a>
            </li>

            <!-- Financial Reports -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.finance.reports.*') ? 'active' : '' }}" 
                   href="{{ route('admin.finance.reports.index') }}">
                    <span class="nav-icon"><i data-lucide="file-text"></i></span>
                    <span class="nav-text">Financial Reports</span>
                </a>
            </li>

            <!-- Analytics Section -->
            <li class="menu-title mt-3">Analytics</li>

            <!-- Reports -->
            <li class="menu-item">
                <a class="menu-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" 
                   href="#sidebarReports" data-bs-toggle="collapse">
                    <span class="nav-icon"><i data-lucide="bar-chart-2"></i></span>
                    <span class="nav-text">Reports</span>
                    <span class="menu-arrow"><i data-lucide="chevron-down"></i></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="sidebarReports">
                    <ul class="sub-menu-nav">
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.index') }}">Overview</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.revenue') }}">Revenue</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.sales') }}">Sales</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.users') }}">Users</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.deliveries') }}">Deliveries</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.products') }}">Products</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.commission') }}">Commission</a>
                        </li>
                        <li class="sub-menu-item">
                            <a class="sub-menu-link" href="{{ route('admin.reports.custom') }}">Custom Reports</a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Quick Links -->
            <li class="menu-title mt-3">Quick Links</li>

            <li class="menu-item">
                <a class="menu-link" href="{{ route('home') }}" target="_blank">
                    <span class="nav-icon"><i data-lucide="external-link"></i></span>
                    <span class="nav-text">View Website</span>
                </a>
            </li>

        </ul>
    </div>
</div>
<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default" data-template="vertical-menu-template" data-bs-theme="light">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Company Portal') - {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">

    <!-- Core CSS -->
    <link rel="stylesheet" href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/css/core.css" />
    <link rel="stylesheet" href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/css/demo.css" />
    <link rel="stylesheet" href="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />
    
    @stack('styles')

    <!-- Helpers -->
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/js/helpers.js"></script>
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/js/config.js"></script>
</head>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            
            <!-- Menu -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ route('rider.dashboard') }}" class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <i class="bx bx-package bx-lg text-primary"></i>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bold ms-2">Company Portal</span>
                    </a>
                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                        <i class="bx bx-chevron-left"></i>
                    </a>
                </div>

                <div class="menu-inner-shadow"></div>

                <ul class="menu-inner py-1">
                    
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Main</span></li>
                    
                    <!-- Dashboard -->
                    <li class="menu-item {{ request()->routeIs('rider.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('rider.dashboard') }}" class="menu-link">
                            <i class="menu-icon bx bx-home-smile"></i>
                            <div>Dashboard</div>
                        </a>
                    </li>

                    <!-- Deliveries -->
                    <li class="menu-item {{ request()->routeIs('rider.deliveries.*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon bx bx-package"></i>
                            <div>Deliveries</div>
                            @php
                                $activeCount = Auth::user()->rider->activeDeliveries()->count();
                            @endphp
                            @if($activeCount > 0)
                                <span class="badge badge-center rounded-pill bg-danger ms-auto">{{ $activeCount }}</span>
                            @endif
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item {{ request()->routeIs('rider.deliveries.index') && !request()->has('status') ? 'active' : '' }}">
                                <a href="{{ route('rider.deliveries.index') }}" class="menu-link">
                                    <div>All Deliveries</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('rider.deliveries.active') ? 'active' : '' }}">
                                <a href="{{ route('rider.deliveries.active') }}" class="menu-link">
                                    <div>Active</div>
                                    @if($activeCount > 0)
                                        <span class="badge badge-center rounded-pill bg-primary ms-auto">{{ $activeCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('rider.deliveries.completed') ? 'active' : '' }}">
                                <a href="{{ route('rider.deliveries.completed') }}" class="menu-link">
                                    <div>Completed</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('rider.deliveries.failed') ? 'active' : '' }}">
                                <a href="{{ route('rider.deliveries.failed') }}" class="menu-link">
                                    <div>Failed</div>
                                </a>
                            </li>
                            <li class="menu-item {{ request()->routeIs('rider.deliveries.available') ? 'active' : '' }}">
                                <a href="{{ route('rider.deliveries.available') }}" class="menu-link">
                                    <div>Broadcast</div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Earnings -->
                    <li class="menu-item {{ request()->routeIs('rider.earnings.*') ? 'active' : '' }}">
                        <a href="{{ route('rider.earnings.index') }}" class="menu-link">
                            <i class="menu-icon bx bx-money"></i>
                            <div>Earnings</div>
                        </a>
                    </li>

                    <li class="menu-header small text-uppercase mt-3"><span class="menu-header-text">Account</span></li>

                    <!-- Profile -->
                    <li class="menu-item {{ request()->routeIs('rider.profile.*') ? 'active' : '' }}">
                        <a href="{{ route('rider.profile.index') }}" class="menu-link">
                            <i class="menu-icon bx bx-user"></i>
                            <div>Company Profile</div>
                        </a>
                    </li>

                    <!-- Notifications -->
                    <li class="menu-item {{ request()->routeIs('rider.notifications') ? 'active' : '' }}">
                        <a href="{{ route('rider.notifications') }}" class="menu-link">
                            <i class="menu-icon bx bx-bell"></i>
                            <div>Notifications</div>
                            @php
                                $unreadCount = Auth::user()->unreadNotifications()->count();
                            @endphp
                            @if($unreadCount > 0)
                                <span class="badge badge-center rounded-pill bg-danger ms-auto">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- / Menu -->

            <!-- Layout page -->
            <div class="layout-page">
                
                <!-- Navbar -->
                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="bx bx-menu bx-md"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        
                        <!-- Company Name -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <span class="fw-semibold">{{ Auth::user()->rider->full_name }}</span>
                            </div>
                        </div>

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            
                            <!-- Quick Stats -->
                            <li class="nav-item me-3 d-none d-lg-block">
                                <div class="text-end">
                                    <small class="text-muted d-block">Today's Earnings</small>
                                    <span class="fw-bold text-success">
                                        ₦{{ number_format(Auth::user()->rider->deliveries()->where('status', 'delivered')->whereDate('delivered_at', today())->sum('delivery_fee'), 0) }}
                                    </span>
                                </div>
                            </li>

                            <!-- Notification -->
                            <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-4 me-xl-2">
                                <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <i class="bx bx-bell bx-md"></i>
                                    @if(Auth::user()->unreadNotifications->count() > 0)
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                            {{ Auth::user()->unreadNotifications->count() }}
                                        </span>
                                    @endif
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-0" style="width: 360px;">
                                    <li class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Notifications</h6>
                                            @if(Auth::user()->unreadNotifications->count() > 0)
                                                <span class="badge rounded-pill bg-label-primary">{{ Auth::user()->unreadNotifications->count() }} New</span>
                                            @endif
                                        </div>
                                    </li>
                                    <li class="dropdown-notifications-list scrollable-container" style="max-height: 300px;">
                                        <ul class="list-group list-group-flush">
                                            @forelse(Auth::user()->unreadNotifications->take(5) as $notification)
                                            <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                                <div class="d-flex gap-2">
                                                    <div class="flex-shrink-0">
                                                        <div class="avatar">
                                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                                <i class="bx bx-package"></i>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <h6 class="mb-1 small">{{ $notification->data['title'] ?? 'New Notification' }}</h6>
                                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                    </div>
                                                </div>
                                            </li>
                                            @empty
                                            <li class="list-group-item text-center py-4">
                                                <p class="text-muted mb-0">No new notifications</p>
                                            </li>
                                            @endforelse
                                        </ul>
                                    </li>
                                    <li class="border-top">
                                        <div class="d-grid p-3">
                                            <a class="btn btn-sm btn-primary" href="{{ route('rider.notifications') }}">View All</a>
                                        </div>
                                    </li>
                                </ul>
                            </li>

                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                            {{ substr(Auth::user()->name, 0, 2) }}
                                        </span>
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                                    <li>
                                        <a class="dropdown-item pb-2 mb-1" href="{{ route('rider.profile.index') }}">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 me-2">
                                                    <div class="avatar avatar-online">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ substr(Auth::user()->name, 0, 2) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ Auth::user()->name }}</h6>
                                                    <small class="text-muted">Delivery Company</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li><div class="dropdown-divider my-1"></div></li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('rider.profile.index') }}">
                                            <i class="bx bx-user bx-md me-3"></i><span>Company Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('rider.earnings.index') }}">
                                            <i class="bx bx-money bx-md me-3"></i><span>Earnings</span>
                                        </a>
                                    </li>
                                    <li><div class="dropdown-divider my-1"></div></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item">
                                                <i class="bx bx-power-off bx-md me-3"></i><span>Log Out</span>
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    
                    <!-- Alerts -->
                    @if(session('success'))
                    <div class="container-xxl container-p-y pb-0">
                        <div class="alert alert-success alert-dismissible d-flex align-items-center" role="alert">
                            <i class="bx bx-check-circle me-2"></i>
                            <div>{{ session('success') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="container-xxl container-p-y pb-0">
                        <div class="alert alert-danger alert-dismissible d-flex align-items-center" role="alert">
                            <i class="bx bx-error me-2"></i>
                            <div>{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                    @endif

                    @if(session('warning'))
                    <div class="container-xxl container-p-y pb-0">
                        <div class="alert alert-warning alert-dismissible d-flex align-items-center" role="alert">
                            <i class="bx bx-error-circle me-2"></i>
                            <div>{{ session('warning') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                    @endif

                    @if(session('info'))
                    <div class="container-xxl container-p-y pb-0">
                        <div class="alert alert-info alert-dismissible d-flex align-items-center" role="alert">
                            <i class="bx bx-info-circle me-2"></i>
                            <div>{{ session('info') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    </div>
                    @endif

                    <!-- Content -->
                    @yield('content')
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div class="container-xxl">
                            <div class="footer-container d-flex align-items-center justify-content-between py-4 flex-md-row flex-column">
                                <div class="text-body mb-2 mb-md-0">
                                    © {{ date('Y') }} <strong>{{ config('app.name') }}</strong>. All rights reserved.
                                </div>
                                <div class="d-none d-lg-inline-block">
                                    <span class="text-muted">Company ID: #{{ Auth::user()->rider->id }}</span>
                                </div>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>

    <!-- Core JS -->
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/libs/popper/popper.js"></script>
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/js/bootstrap.js"></script>
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/vendor/js/menu.js"></script>
    <script src="https://demos.themeselection.com/sneat-bootstrap-html-admin-template/assets/js/main.js"></script>

    @stack('scripts')
</body>
</html>
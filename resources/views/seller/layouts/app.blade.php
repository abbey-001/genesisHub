<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | Seller Portal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('public/image/auth-logo.png') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link href="{{ asset('public/seller/assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/seller/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="{{ asset('public/seller/assets/js/config.min.js') }}"></script>
    @stack('styles')
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        @include('seller.layouts.sidebar')

        <!-- Topbar -->
        @include('seller.layouts.topbar')

        <!-- Main Content -->
        <div class="page-container">
            <div class="page-content">
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </div>

            <!-- Footer -->
            @include('seller.layouts.footer')
        </div>
    </div>

    <script src="{{ asset('public/seller/assets/js/vendor.js') }}"></script>
    <script src="{{ asset('public/seller/assets/js/app.js') }}"></script>
    <script src="{{ asset('public/seller/assets/js/pages/dashboard.js') }}"></script>
    @stack('scripts')
</body>
</html>
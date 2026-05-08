@extends('admin.layouts.app')

@section('title', 'Custom Report Builder')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-style1 mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.reports.index') }}">Reports</a>
                    </li>
                    <li class="breadcrumb-item active">Custom Report Builder</li>
                </ol>
            </nav>
            <h4 class="mb-1">🎯 Custom Report Builder</h4>
            <p class="text-muted mb-0">Create customized reports with multiple metrics</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Report Builder -->
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Build Your Report</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.custom.generate') }}" method="POST">
                        @csrf

                        <!-- Report Name -->
                        <div class="mb-4">
                            <label class="form-label">Report Name *</label>
                            <input type="text" name="name" class="form-control" 
                                   placeholder="e.g., Monthly Performance Report" required>
                            <small class="text-muted">Give your report a descriptive name</small>
                        </div>

                        <!-- Metrics Selection -->
                        <div class="mb-4">
                            <label class="form-label">Select Metrics *</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" 
                                               value="revenue" id="metric-revenue">
                                        <label class="form-check-label w-100" for="metric-revenue">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-dollar bx-md text-primary me-2"></i>
                                                <div>
                                                    <strong>Revenue Analytics</strong>
                                                    <br>
                                                    <small class="text-muted">Total revenue, growth, trends</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" 
                                               value="orders" id="metric-orders">
                                        <label class="form-check-label w-100" for="metric-orders">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-shopping-bag bx-md text-success me-2"></i>
                                                <div>
                                                    <strong>Sales & Orders</strong>
                                                    <br>
                                                    <small class="text-muted">Order volume, status, performance</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" 
                                               value="users" id="metric-users">
                                        <label class="form-check-label w-100" for="metric-users">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-user bx-md text-info me-2"></i>
                                                <div>
                                                    <strong>User Analytics</strong>
                                                    <br>
                                                    <small class="text-muted">Customers, sellers, riders</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" 
                                               value="products" id="metric-products">
                                        <label class="form-check-label w-100" for="metric-products">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-box bx-md text-warning me-2"></i>
                                                <div>
                                                    <strong>Product Analytics</strong>
                                                    <br>
                                                    <small class="text-muted">Best sellers, ratings, inventory</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" 
                                               value="deliveries" id="metric-deliveries">
                                        <label class="form-check-label w-100" for="metric-deliveries">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-package bx-md text-danger me-2"></i>
                                                <div>
                                                    <strong>Delivery Analytics</strong>
                                                    <br>
                                                    <small class="text-muted">Success rate, riders, performance</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-check card p-3">
                                        <input class="form-check-input" type="checkbox" name="metrics[]" 
                                               value="commission" id="metric-commission">
                                        <label class="form-check-label w-100" for="metric-commission">
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-wallet bx-md text-secondary me-2"></i>
                                                <div>
                                                    <strong>Commission Analytics</strong>
                                                    <br>
                                                    <small class="text-muted">Platform earnings, breakdown</small>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Select one or more metrics to include</small>
                        </div>

                        <!-- Date Range -->
                        <div class="mb-4">
                            <label class="form-label">Date Range *</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small">From Date</label>
                                    <input type="date" name="date_from" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="small">To Date</label>
                                    <input type="date" name="date_to" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Filters -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label mb-0">Advanced Filters (Optional)</label>
                                <button type="button" class="btn btn-sm btn-label-secondary" onclick="toggleAdvanced()">
                                    <i class="bx bx-slider"></i> Toggle
                                </button>
                            </div>
                            <div id="advancedFilters" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="small">Group By</label>
                                        <select name="group_by" class="form-select">
                                            <option value="">No Grouping</option>
                                            <option value="day">Daily</option>
                                            <option value="week">Weekly</option>
                                            <option value="month">Monthly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Category</label>
                                        <select name="filters[category_id]" class="form-select">
                                            <option value="">All Categories</option>
                                            @foreach(\App\Models\Category::all() as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Seller</label>
                                        <select name="filters[seller_id]" class="form-select">
                                            <option value="">All Sellers</option>
                                            @foreach(\App\Models\Seller::with('shop')->get() as $seller)
                                                <option value="{{ $seller->id }}">
                                                    {{ $seller->shop->shop_name ?? 'Seller #' . $seller->id }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small">Order Status</label>
                                        <select name="filters[status]" class="form-select">
                                            <option value="">All Statuses</option>
                                            <option value="pending">Pending</option>
                                            <option value="processing">Processing</option>
                                            <option value="delivered">Delivered</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-bar-chart me-1"></i> Generate Report
                            </button>
                            <button type="reset" class="btn btn-label-secondary">
                                <i class="bx bx-reset me-1"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick Templates -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Templates</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Use these pre-configured templates</p>

                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-label-primary text-start" onclick="loadTemplate('monthly')">
                            <i class="bx bx-calendar me-2"></i>
                            <div>
                                <strong>Monthly Overview</strong>
                                <br>
                                <small class="text-muted">Revenue, Sales, Users - This Month</small>
                            </div>
                        </button>

                        <button type="button" class="btn btn-label-success text-start" onclick="loadTemplate('sales')">
                            <i class="bx bx-trending-up me-2"></i>
                            <div>
                                <strong>Sales Performance</strong>
                                <br>
                                <small class="text-muted">Orders, Products - Last 30 Days</small>
                            </div>
                        </button>

                        <button type="button" class="btn btn-label-info text-start" onclick="loadTemplate('delivery')">
                            <i class="bx bx-package me-2"></i>
                            <div>
                                <strong>Delivery Report</strong>
                                <br>
                                <small class="text-muted">Deliveries, Success Rate - This Week</small>
                            </div>
                        </button>

                        <button type="button" class="btn btn-label-warning text-start" onclick="loadTemplate('financial')">
                            <i class="bx bx-dollar me-2"></i>
                            <div>
                                <strong>Financial Report</strong>
                                <br>
                                <small class="text-muted">Revenue, Commission - This Month</small>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Schedule Report -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Schedule Reports</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.reports.schedule') }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="type" class="form-select" required>
                                <option value="revenue">Revenue</option>
                                <option value="sales">Sales</option>
                                <option value="users">Users</option>
                                <option value="deliveries">Deliveries</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Frequency</label>
                            <select name="frequency" class="form-select" required>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Format</label>
                            <select name="format" class="form-select" required>
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Recipients</label>
                            <input type="email" name="recipients[]" class="form-control mb-2" 
                                   placeholder="email@example.com" required>
                            <button type="button" class="btn btn-sm btn-label-secondary" onclick="addRecipient()">
                                <i class="bx bx-plus"></i> Add Another
                            </button>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bx bx-calendar-check me-1"></i> Schedule Report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>


@push('scripts')
<script>
    function toggleAdvanced() {
        const advanced = document.getElementById('advancedFilters');
        advanced.style.display = advanced.style.display === 'none' ? 'block' : 'none';
    }

    function loadTemplate(type) {
        const today = new Date();
        const form = document.querySelector('form');
        
        // Clear existing selections
        document.querySelectorAll('input[name="metrics[]"]').forEach(cb => cb.checked = false);
        
        switch(type) {
            case 'monthly':
                form.querySelector('input[name="name"]').value = 'Monthly Overview Report';
                form.querySelector('input[name="date_from"]').value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                form.querySelector('input[name="date_to"]').value = today.toISOString().split('T')[0];
                document.getElementById('metric-revenue').checked = true;
                document.getElementById('metric-orders').checked = true;
                document.getElementById('metric-users').checked = true;
                break;
                
            case 'sales':
                form.querySelector('input[name="name"]').value = 'Sales Performance Report';
                form.querySelector('input[name="date_from"]').value = new Date(today.setDate(today.getDate() - 30)).toISOString().split('T')[0];
                form.querySelector('input[name="date_to"]').value = new Date().toISOString().split('T')[0];
                document.getElementById('metric-orders').checked = true;
                document.getElementById('metric-products').checked = true;
                break;
                
            case 'delivery':
                form.querySelector('input[name="name"]').value = 'Delivery Performance Report';
                const weekStart = new Date(today.setDate(today.getDate() - today.getDay()));
                form.querySelector('input[name="date_from"]').value = weekStart.toISOString().split('T')[0];
                form.querySelector('input[name="date_to"]').value = new Date().toISOString().split('T')[0];
                document.getElementById('metric-deliveries').checked = true;
                break;
                
            case 'financial':
                form.querySelector('input[name="name"]').value = 'Financial Report';
                form.querySelector('input[name="date_from"]').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
                form.querySelector('input[name="date_to"]').value = new Date().toISOString().split('T')[0];
                document.getElementById('metric-revenue').checked = true;
                document.getElementById('metric-commission').checked = true;
                break;
        }
    }

    function addRecipient() {
        const container = document.querySelector('input[name="recipients[]"]').parentElement;
        const newInput = document.createElement('input');
        newInput.type = 'email';
        newInput.name = 'recipients[]';
        newInput.className = 'form-control mb-2';
        newInput.placeholder = 'email@example.com';
        container.insertBefore(newInput, container.querySelector('button'));
    }
</script>
@endpush
@endsection
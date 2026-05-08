{{-- Super Admin / Administrator Dashboard --}}

<!-- Revenue Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Today's Revenue</p>
                        <h4 class="fw-bold text-primary mb-0">
                            ₦{{ number_format($metrics['revenue']['today'], 2) }}
                        </h4>
                        <small class="text-muted">
                            Yesterday: ₦{{ number_format($metrics['revenue']['yesterday'], 2) }}
                        </small>
                    </div>
                    <div>
                        <div class="avatar-md bg-primary bg-opacity-10 rounded">
                            <i data-lucide="trending-up" class="text-primary fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">This Month</p>
                        <h4 class="fw-bold text-success mb-0">
                            ₦{{ number_format($metrics['revenue']['this_month'], 2) }}
                        </h4>
                        <small class="text-muted">
                            Total Revenue
                        </small>
                    </div>
                    <div>
                        <div class="avatar-md bg-success bg-opacity-10 rounded">
                            <i data-lucide="dollar-sign" class="text-success fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Total Orders</p>
                        <h4 class="fw-bold mb-0">{{ number_format($metrics['orders']['total']) }}</h4>
                        <small class="text-warning">
                            {{ $metrics['orders']['pending'] }} Pending
                        </small>
                    </div>
                    <div>
                        <div class="avatar-md bg-warning bg-opacity-10 rounded">
                            <i data-lucide="shopping-cart" class="text-warning fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Active Deliveries</p>
                        <h4 class="fw-bold mb-0">{{ $metrics['deliveries']['active'] }}</h4>
                        <small class="text-info">
                            {{ $metrics['deliveries']['pending_assignment'] }} Pending
                        </small>
                    </div>
                    <div>
                        <div class="avatar-md bg-info bg-opacity-10 rounded">
                            <i data-lucide="truck" class="text-info fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Users & Platform Stats -->
<div class="row mt-4">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i data-lucide="users" class="text-primary fs-32"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ number_format($metrics['users']['customers']) }}</h6>
                        <p class="text-muted mb-0">Total Customers</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i data-lucide="store" class="text-success fs-32"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ number_format($metrics['users']['sellers']) }}</h6>
                        <p class="text-muted mb-0">Active Sellers</p>
                        @if($metrics['sellers']['pending_approval'] > 0)
                        <span class="badge bg-warning">{{ $metrics['sellers']['pending_approval'] }} pending</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i data-lucide="bike" class="text-info fs-32"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ number_format($metrics['users']['riders']) }}</h6>
                        <p class="text-muted mb-0">Total Logistics</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i data-lucide="package" class="text-warning fs-32"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ number_format($metrics['products']['total']) }}</h6>
                        <p class="text-muted mb-0">Total Products</p>
                        @if($metrics['products']['pending_approval'] > 0)
                        <span class="badge bg-warning">{{ $metrics['products']['pending_approval'] }} pending</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
@if(!empty($revenueChart))
<div class="row mt-4">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="trending-up" class="me-2"></i>
                    Revenue Trend (Last 30 Days)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="pie-chart" class="me-2"></i>
                    Quick Stats
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Completed Orders</span>
                        <span class="fw-bold">{{ number_format($metrics['orders']['completed']) }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: 75%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Processing Orders</span>
                        <span class="fw-bold">{{ number_format($metrics['orders']['processing']) }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-info" style="width: 60%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Pending Orders</span>
                        <span class="fw-bold">{{ number_format($metrics['orders']['pending']) }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-warning" style="width: 40%"></div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Failed Deliveries</span>
                        <span class="fw-bold">{{ number_format($metrics['deliveries']['failed']) }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-danger" style="width: 15%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Top Products & Sellers -->
<div class="row mt-4">
    @if(!empty($topProducts) && $topProducts->count() > 0)
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="star" class="me-2"></i>
                    Top Selling Products
                </h5>
            </div>
            <div class="card-body">
                @foreach($topProducts as $product)
                <div class="d-flex align-items-center mb-3 {{ $loop->last ? '' : 'pb-3 border-bottom' }}">
                    <img src="{{ asset('public/storage/' . $product->main_image) }}" 
                         alt="{{ $product->name }}" 
                         class="rounded me-3"
                         style="width: 50px; height: 50px; object-fit: cover;">
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ Str::limit($product->name, 40) }}</h6>
                        <p class="text-muted mb-0 small">
                            <span class="badge bg-success">{{ $product->sold_count }} sold</span>
                            <span class="ms-2">₦{{ number_format($product->price, 2) }}</span>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(!empty($topSellers) && $topSellers->count() > 0)
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="award" class="me-2"></i>
                    Top Sellers
                </h5>
            </div>
            <div class="card-body">
                @foreach($topSellers as $seller)
                <div class="d-flex align-items-center mb-3 {{ $loop->last ? '' : 'pb-3 border-bottom' }}">
                    <div class="avatar-sm bg-primary bg-opacity-10 rounded me-3 d-flex align-items-center justify-content-center">
                        <i data-lucide="store" class="text-primary"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $seller->shop->shop_name ?? 'Shop Name' }}</h6>
                        <p class="text-muted mb-0 small">
                            <span class="badge bg-info">{{ $seller->products_count }} products</span>
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Revenue Chart
    @if(!empty($revenueChart))
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChart['labels']) !!},
            datasets: [{
                label: 'Revenue (₦)',
                data: {!! json_encode($revenueChart['data']) !!},
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₦' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
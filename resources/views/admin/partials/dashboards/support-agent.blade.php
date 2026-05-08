{{-- Support Agent Dashboard --}}

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Total Customers</p>
                        <h4 class="fw-bold mb-0">{{ number_format($metrics['users']['total_customers']) }}</h4>
                        <small class="text-success">{{ $metrics['users']['new_today'] }} new today</small>
                    </div>
                    <div>
                        <div class="avatar-md bg-primary bg-opacity-10 rounded">
                            <i data-lucide="users" class="text-primary fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Total Orders</p>
                        <h4 class="fw-bold mb-0">{{ number_format($metrics['orders']['total']) }}</h4>
                        <small class="text-muted">{{ $metrics['orders']['today'] }} today</small>
                    </div>
                    <div>
                        <div class="avatar-md bg-success bg-opacity-10 rounded">
                            <i data-lucide="shopping-cart" class="text-success fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i data-lucide="headphones" class="text-info" style="width: 48px; height: 48px;"></i>
                <h5 class="mt-3">Support Dashboard</h5>
                <p class="text-muted mb-0">Ready to assist customers</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i data-lucide="message-circle" class="text-primary" style="width: 64px; height: 64px;"></i>
                <h5 class="mt-3">Customer Support Tools</h5>
                <p class="text-muted mb-4">Access support tickets and customer inquiries here</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="#" class="btn btn-primary">
                        <i data-lucide="inbox" class="me-2"></i>View Tickets
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i data-lucide="book" class="me-2"></i>Knowledge Base
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
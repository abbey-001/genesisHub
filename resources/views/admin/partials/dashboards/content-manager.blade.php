{{-- Content Manager Dashboard --}}

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Total Products</p>
                        <h4 class="fw-bold mb-0">{{ number_format($metrics['products']['total']) }}</h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-primary bg-opacity-10 rounded">
                            <i data-lucide="package" class="text-primary fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Pending Approval</p>
                        <h4 class="fw-bold text-warning mb-0">{{ $metrics['products']['pending_approval'] }}</h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-warning bg-opacity-10 rounded">
                            <i data-lucide="clock" class="text-warning fs-32"></i>
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
                        <p class="mb-2 text-muted">Approved Today</p>
                        <h4 class="fw-bold text-success mb-0">{{ $metrics['products']['approved_today'] }}</h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-success bg-opacity-10 rounded">
                            <i data-lucide="check-circle" class="text-success fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Low Stock Alert</p>
                        <h4 class="fw-bold text-danger mb-0">{{ $metrics['products']['low_stock'] }}</h4>
                    </div>
                    <div>
                        <div class="avatar-md bg-danger bg-opacity-10 rounded">
                            <i data-lucide="alert-triangle" class="text-danger fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Catalog Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted">Total Products</td>
                                <td class="text-end fw-bold">{{ number_format($metrics['products']['total']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Categories</td>
                                <td class="text-end fw-bold">{{ $metrics['categories']['total'] }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Total Sellers</td>
                                <td class="text-end fw-bold">{{ number_format($metrics['sellers']['total']) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Seller Applications</td>
                                <td class="text-end fw-bold text-warning">{{ $metrics['sellers']['pending_approval'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="zap" class="me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="#" class="btn btn-warning">
                        <i data-lucide="eye" class="me-2"></i>
                        Review Pending Products ({{ $metrics['products']['pending_approval'] }})
                    </a>
                    <a href="#" class="btn btn-primary">
                        <i data-lucide="folder" class="me-2"></i>
                        Manage Categories
                    </a>
                    <a href="#" class="btn btn-info">
                        <i data-lucide="users" class="me-2"></i>
                        Review Seller Applications ({{ $metrics['sellers']['pending_approval'] }})
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
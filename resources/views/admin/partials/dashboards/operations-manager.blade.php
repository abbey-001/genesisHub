{{-- Operations Manager Dashboard --}}

<!-- Delivery Stats -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 text-muted">Active Deliveries</p>
                        <h4 class="fw-bold text-primary mb-0">{{ $metrics['deliveries']['active'] }}</h4>
                        <small class="text-muted">In progress</small>
                    </div>
                    <div>
                        <div class="avatar-md bg-primary bg-opacity-10 rounded">
                            <i data-lucide="truck" class="text-primary fs-32"></i>
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
                        <p class="mb-2 text-muted">Pending Assignment</p>
                        <h4 class="fw-bold text-warning mb-0">{{ $metrics['deliveries']['pending_assignment'] }}</h4>
                        <small class="text-muted">Needs rider</small>
                    </div>
                    <div>
                        <div class="avatar-md bg-warning bg-opacity-10 rounded">
                            <i data-lucide="alert-circle" class="text-warning fs-32"></i>
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
                        <p class="mb-2 text-muted">Completed Today</p>
                        <h4 class="fw-bold text-success mb-0">{{ $metrics['deliveries']['completed_today'] }}</h4>
                        <small class="text-success">Successfully delivered</small>
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
                        <p class="mb-2 text-muted">Failed Today</p>
                        <h4 class="fw-bold text-danger mb-0">{{ $metrics['deliveries']['failed_today'] }}</h4>
                        <small class="text-danger">Needs attention</small>
                    </div>
                    <div>
                        <div class="avatar-md bg-danger bg-opacity-10 rounded">
                            <i data-lucide="x-circle" class="text-danger fs-32"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Rider Stats -->
<div class="row mt-4">
    <div class="col-xl-3 col-md-6">
        <div class="card border-success">
            <div class="card-body">
                <h6 class="text-muted mb-2">Online Riders</h6>
                <h3 class="fw-bold text-success mb-1">{{ $metrics['riders']['online'] }}</h3>
                <p class="mb-0 text-muted">Available for delivery</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-info">
            <div class="card-body">
                <h6 class="text-muted mb-2">Busy Riders</h6>
                <h3 class="fw-bold text-info mb-1">{{ $metrics['riders']['busy'] }}</h3>
                <p class="mb-0 text-muted">Currently delivering</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted mb-2">Offline Riders</h6>
                <h3 class="fw-bold mb-1">{{ $metrics['riders']['offline'] }}</h3>
                <p class="mb-0 text-muted">Not available</p>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card border-warning">
            <div class="card-body">
                <h6 class="text-muted mb-2">Pending Approval</h6>
                <h3 class="fw-bold text-warning mb-1">{{ $metrics['riders']['pending_approval'] }}</h3>
                <p class="mb-0 text-muted">New applications</p>
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="activity" class="me-2"></i>
                    Performance Metrics
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Delivery Success Rate</span>
                        <span class="fw-bold text-success">{{ $metrics['deliveries']['success_rate'] }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $metrics['deliveries']['success_rate'] }}%"></div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Average Delivery Time</span>
                        <span class="fw-bold">{{ $metrics['performance']['avg_delivery_time'] }} mins</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" style="width: 70%"></div>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">On-Time Delivery Rate</span>
                        <span class="fw-bold text-primary">{{ $metrics['performance']['on_time_rate'] }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" style="width: {{ $metrics['performance']['on_time_rate'] }}%"></div>
                    </div>
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
                        <i data-lucide="user-plus" class="me-2"></i>
                        Assign Pending Deliveries ({{ $metrics['deliveries']['pending_assignment'] }})
                    </a>
                    <a href="#" class="btn btn-danger">
                        <i data-lucide="alert-triangle" class="me-2"></i>
                        Review Failed Deliveries ({{ $metrics['deliveries']['failed_today'] }})
                    </a>
                    <a href="#" class="btn btn-primary">
                        <i data-lucide="map" class="me-2"></i>
                        View Live Delivery Map
                    </a>
                    <a href="#" class="btn btn-info">
                        <i data-lucide="users" class="me-2"></i>
                        Review Rider Applications ({{ $metrics['riders']['pending_approval'] }})
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Tracking Preview -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="map-pin" class="me-2"></i>
                    Active Deliveries Overview
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info d-flex align-items-center">
                    <i data-lucide="info" class="me-3 fs-24"></i>
                    <div>
                        <strong>Live Tracking Available</strong><br>
                        <small>{{ $metrics['deliveries']['active'] }} active deliveries are currently being tracked in real-time.</small>
                    </div>
                    <a href="#" class="btn btn-primary ms-auto">
                        View Map <i data-lucide="external-link" class="ms-1"></i>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge bg-warning">Pending Assignment</span>
                                </td>
                                <td class="fw-bold">{{ $metrics['deliveries']['pending_assignment'] }}</td>
                                <td>
                                    @php
                                        $total = $metrics['deliveries']['active'] + $metrics['deliveries']['pending_assignment'];
                                        $percent = $total > 0 ? round(($metrics['deliveries']['pending_assignment'] / $total) * 100) : 0;
                                    @endphp
                                    {{ $percent }}%
                                </td>
                                <td>
                                    <div class="progress" style="height: 6px; width: 100px;">
                                        <div class="progress-bar bg-warning" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">Active</span>
                                </td>
                                <td class="fw-bold">{{ $metrics['deliveries']['active'] }}</td>
                                <td>
                                    @php
                                        $percent = $total > 0 ? round(($metrics['deliveries']['active'] / $total) * 100) : 0;
                                    @endphp
                                    {{ $percent }}%
                                </td>
                                <td>
                                    <div class="progress" style="height: 6px; width: 100px;">
                                        <div class="progress-bar bg-primary" style="width: {{ $percent }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
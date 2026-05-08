@extends('admin.layouts.app')

@section('title', 'Failed Deliveries')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">❌ Failed Deliveries</h4>
                <p class="text-muted mb-0">Review and manage delivery failures</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary">
                    <i data-lucide="arrow-left" class="me-1"></i>
                    Back to All Deliveries
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-danger">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Total Failed</p>
                        <h4 class="fw-bold text-danger mb-0">{{ $deliveries->total() }}</h4>
                        <small class="text-muted">All time</small>
                    </div>
                    <div class="avatar-sm bg-danger bg-opacity-10 rounded">
                        <i data-lucide="x-circle" class="text-danger fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-warning">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Failed Today</p>
                        <h4 class="fw-bold text-warning mb-0">
                            {{ $deliveries->where('failed_at', '>=', now()->startOfDay())->count() }}
                        </h4>
                        <small class="text-muted">{{ now()->format('M d, Y') }}</small>
                    </div>
                    <div class="avatar-sm bg-warning bg-opacity-10 rounded">
                        <i data-lucide="alert-triangle" class="text-warning fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-info">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Reassigned</p>
                        <h4 class="fw-bold text-info mb-0">
                            {{ $deliveries->filter(function($d) { return $d->status !== 'failed'; })->count() }}
                        </h4>
                        <small class="text-muted">Successfully recovered</small>
                    </div>
                    <div class="avatar-sm bg-info bg-opacity-10 rounded">
                        <i data-lucide="refresh-cw" class="text-info fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="mb-2 card-title text-muted">Failure Rate</p>
                        <h4 class="fw-bold mb-0">
                            @php
                                $total = \App\Models\Delivery::count();
                                $rate = $total > 0 ? round(($deliveries->total() / $total) * 100, 1) : 0;
                            @endphp
                            {{ $rate }}%
                        </h4>
                        <small class="text-muted">Of all deliveries</small>
                    </div>
                    <div class="avatar-sm bg-secondary bg-opacity-10 rounded">
                        <i data-lucide="percent" class="text-secondary fs-24"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Failure Reasons Summary -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="pie-chart" class="me-2"></i>
                    Failure Reasons Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @php
                        $reasons = $deliveries->groupBy('failure_reason')->map(fn($group) => $group->count());
                        $reasonLabels = [
                            'customer_unavailable' => ['label' => 'Customer Unavailable', 'icon' => 'user-x', 'color' => 'warning'],
                            'wrong_address' => ['label' => 'Wrong Address', 'icon' => 'map-pin', 'color' => 'info'],
                            'refused' => ['label' => 'Package Refused', 'icon' => 'x-circle', 'color' => 'danger'],
                            'access_issue' => ['label' => 'Access Issue', 'icon' => 'lock', 'color' => 'secondary'],
                            'cancelled_by_rider' => ['label' => 'Cancelled by Rider', 'icon' => 'user-minus', 'color' => 'dark'],
                            'cancelled_by_admin' => ['label' => 'Cancelled by Admin', 'icon' => 'shield', 'color' => 'primary'],
                            'other' => ['label' => 'Other', 'icon' => 'help-circle', 'color' => 'muted'],
                        ];
                    @endphp

                    @forelse($reasons as $reason => $count)
                        @php
                            $info = $reasonLabels[$reason] ?? ['label' => ucfirst(str_replace('_', ' ', $reason)), 'icon' => 'alert-circle', 'color' => 'secondary'];
                        @endphp
                        <div class="col-md-3 mb-3">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="avatar-sm bg-{{ $info['color'] }} bg-opacity-10 rounded me-3 flex-shrink-0 d-flex align-items-center justify-content-center">
                                    <i data-lucide="{{ $info['icon'] }}" class="text-{{ $info['color'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">{{ $count }}</div>
                                    <small class="text-muted">{{ $info['label'] }}</small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-3">
                            <p class="text-muted mb-0">No failure data available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Failed Deliveries List -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i data-lucide="list" class="me-2"></i>
                    Failed Deliveries ({{ $deliveries->total() }})
                </h5>
            </div>

            <!-- Filters -->
            <div class="card-body border-bottom">
                <form action="{{ route('admin.deliveries.failed') }}" method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="search" name="search" class="form-control" 
                               placeholder="Search order #..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="failure_reason" class="form-select">
                            <option value="">All Reasons</option>
                            <option value="customer_unavailable" {{ request('failure_reason') === 'customer_unavailable' ? 'selected' : '' }}>Customer Unavailable</option>
                            <option value="wrong_address" {{ request('failure_reason') === 'wrong_address' ? 'selected' : '' }}>Wrong Address</option>
                            <option value="refused" {{ request('failure_reason') === 'refused' ? 'selected' : '' }}>Package Refused</option>
                            <option value="access_issue" {{ request('failure_reason') === 'access_issue' ? 'selected' : '' }}>Access Issue</option>
                            <option value="cancelled_by_rider" {{ request('failure_reason') === 'cancelled_by_rider' ? 'selected' : '' }}>Cancelled by Rider</option>
                            <option value="cancelled_by_admin" {{ request('failure_reason') === 'cancelled_by_admin' ? 'selected' : '' }}>Cancelled by Admin</option>
                            <option value="other" {{ request('failure_reason') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" 
                               value="{{ request('date_from') }}" placeholder="From">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" 
                               value="{{ request('date_to') }}" placeholder="To">
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i data-lucide="search" class="me-1"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.deliveries.failed') }}" class="btn btn-outline-secondary">
                                <i data-lucide="x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                @forelse($deliveries as $delivery)
                <div class="border-bottom p-3">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <div class="d-flex align-items-start">
                                <div class="avatar-sm bg-danger bg-opacity-10 rounded me-3 flex-shrink-0 d-flex align-items-center justify-content-center">
                                    <i data-lucide="x-circle" class="text-danger"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">
                                        <a href="{{ route('admin.deliveries.show', $delivery) }}" class="text-dark">
                                            Delivery #{{ $delivery->id }}
                                        </a>
                                        <span class="badge bg-danger ms-2">Failed</span>
                                    </h6>
                                    <p class="text-muted mb-1 small">
                                        Order: {{ $delivery->order->order_number }}
                                    </p>
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $reasonLabels[$delivery->failure_reason]['color'] ?? 'secondary' }}">
                                            <i data-lucide="{{ $reasonLabels[$delivery->failure_reason]['icon'] ?? 'alert-circle' }}" style="width: 12px; height: 12px;"></i>
                                            {{ $reasonLabels[$delivery->failure_reason]['label'] ?? ucfirst(str_replace('_', ' ', $delivery->failure_reason)) }}
                                        </span>
                                    </div>
                                    <p class="text-muted small mb-0">
                                        <i data-lucide="clock" style="width: 12px; height: 12px;"></i>
                                        Failed {{ $delivery->failed_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small">
                                @if($delivery->rider)
                                <div class="mb-2">
                                    <strong class="text-muted d-block">Rider:</strong>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $delivery->rider->profile_photo ?? 'https://ui-avatars.com/api/?name='.urlencode($delivery->rider->full_name) }}" 
                                             class="rounded-circle me-2" 
                                             width="24" height="24"
                                             alt="{{ $delivery->rider->full_name }}">
                                        {{ $delivery->rider->full_name }}
                                    </div>
                                </div>
                                @endif

                                <div class="mb-2">
                                    <strong class="text-muted d-block">Customer:</strong>
                                    {{ $delivery->order->customer_name }}
                                </div>

                                @if($delivery->failure_notes)
                                <div>
                                    <strong class="text-muted d-block">Notes:</strong>
                                    <p class="mb-0 text-muted">{{ Str::limit($delivery->failure_notes, 60) }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3 text-end">
                            <div class="mb-2">
                                <div class="text-success fw-bold">₦{{ number_format($delivery->delivery_fee, 0) }}</div>
                                <small class="text-muted">{{ $delivery->items->count() }} item(s)</small>
                            </div>
                            <div class="d-flex gap-2 justify-content-end">
                                @can('deliveries.assign')
                                <button type="button" 
                                        class="btn btn-sm btn-warning" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#reassignModal{{ $delivery->id }}">
                                    <i data-lucide="refresh-cw" class="me-1"></i>
                                    Retry
                                </button>
                                @endcan
                                <a href="{{ route('admin.deliveries.show', $delivery) }}" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i data-lucide="eye"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Expandable Details -->
                    <div class="collapse mt-3 pt-3 border-top" id="details{{ $delivery->id }}">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-muted small mb-2">PICKUP LOCATION</h6>
                                <p class="mb-1"><strong>{{ $delivery->seller->shop->shop_name ?? 'N/A' }}</strong></p>
                                <p class="text-muted small mb-0">{{ $delivery->pickup_address }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted small mb-2">DELIVERY LOCATION</h6>
                                <p class="mb-1"><strong>{{ $delivery->order->customer_name }}</strong></p>
                                <p class="text-muted small mb-0">{{ $delivery->delivery_address }}</p>
                            </div>
                            <div class="col-md-4">
                                <h6 class="text-muted small mb-2">FAILURE DETAILS</h6>
                                <ul class="list-unstyled mb-0 small">
                                    <li><strong>Reason:</strong> {{ $reasonLabels[$delivery->failure_reason]['label'] ?? 'Unknown' }}</li>
                                    <li><strong>Failed At:</strong> {{ $delivery->failed_at->format('M d, Y h:i A') }}</li>
                                    @if($delivery->failure_photo)
                                    <li>
                                        <a href="{{ asset('storage/' . $delivery->failure_photo) }}" target="_blank" class="text-primary">
                                            <i data-lucide="image" style="width: 12px; height: 12px;"></i>
                                            View Failure Photo
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        @if($delivery->failure_notes)
                        <div class="mt-3">
                            <h6 class="text-muted small mb-2">FULL NOTES</h6>
                            <p class="mb-0 small">{{ $delivery->failure_notes }}</p>
                        </div>
                        @endif
                    </div>

                    <button class="btn btn-sm btn-link text-muted p-0 mt-2" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#details{{ $delivery->id }}">
                        <i data-lucide="chevron-down"></i>
                        <span>Show/Hide Details</span>
                    </button>
                </div>

                <!-- Retry/Reassign Modal -->
                <div class="modal fade" id="reassignModal{{ $delivery->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-warning">
                                <h5 class="modal-title">
                                    <i data-lucide="refresh-cw" class="me-2"></i>
                                    Retry Failed Delivery
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('admin.deliveries.reassign', $delivery) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i data-lucide="info" class="me-2"></i>
                                        This will unassign the current rider and make the delivery available for reassignment.
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Previous Failure</label>
                                        <div class="p-2 bg-light rounded">
                                            <strong>Reason:</strong> {{ $reasonLabels[$delivery->failure_reason]['label'] ?? 'Unknown' }}<br>
                                            @if($delivery->failure_notes)
                                                <strong>Notes:</strong> {{ $delivery->failure_notes }}
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Reason for Retry *</label>
                                        <textarea name="reason" class="form-control" rows="3" required 
                                                  placeholder="Explain why this delivery should be retried...">Issue resolved, ready for reassignment</textarea>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirmRetry{{ $delivery->id }}" required>
                                        <label class="form-check-label" for="confirmRetry{{ $delivery->id }}">
                                            I confirm this delivery should be retried with a new rider
                                        </label>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-warning">
                                        <i data-lucide="refresh-cw" class="me-1"></i>
                                        Retry Delivery
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i data-lucide="check-circle" class="text-success mb-3" style="width: 64px; height: 64px;"></i>
                    <h5 class="mb-2">No Failed Deliveries! 🎉</h5>
                    <p class="text-muted mb-0">All deliveries are being completed successfully.</p>
                </div>
                @endforelse
            </div>

            @if($deliveries->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $deliveries->firstItem() }} to {{ $deliveries->lastItem() }} 
                        of {{ $deliveries->total() }} failed deliveries
                    </div>
                    {{ $deliveries->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Common Failure Prevention Tips -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i data-lucide="lightbulb" class="me-2"></i>
                    Common Causes of Failure
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">
                        <strong>Customer Unavailable:</strong> Customer not at delivery location or not answering calls
                    </li>
                    <li class="mb-2">
                        <strong>Wrong Address:</strong> Incorrect or incomplete address provided by customer
                    </li>
                    <li class="mb-2">
                        <strong>Access Issues:</strong> Restricted areas, gated communities, security problems
                    </li>
                    <li class="mb-0">
                        <strong>Package Refused:</strong> Customer refuses to accept the package
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i data-lucide="shield-check" class="me-2"></i>
                    Prevention Strategies
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li class="mb-2">Verify customer contact information before dispatch</li>
                    <li class="mb-2">Send SMS/email notifications with delivery ETA</li>
                    <li class="mb-2">Validate addresses using geocoding services</li>
                    <li class="mb-0">Allow customers to provide delivery instructions</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    lucide.createIcons();
</script>
@endpush
{{-- resources/views/rider/broadcasts/index.blade.php --}}
@extends('rider.layouts.app')

@section('title', 'Delivery Broadcasts')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">
                <i class="bx bx-broadcast me-2"></i>Delivery Broadcasts
            </h4>
            <p class="text-muted mb-0">First to accept gets the delivery!</p>
        </div>
        <span class="badge bg-label-primary fs-6">
            <i class="bx bx-broadcast me-1"></i>{{ $broadcasts->count() }} Active
        </span>
    </div>

    @if(Auth::user()->rider->status !== 'available')
    <div class="alert alert-warning d-flex align-items-center mb-4">
        <i class="bx bx-info-circle me-2"></i>
        <div>
            You are currently <strong>offline</strong>. Go online to see and accept broadcast deliveries.
        </div>
    </div>
    @endif

    @if($broadcasts->count() > 0)
    <div class="row g-4">
    @foreach($broadcasts as $broadcast)
        @php
            $isBundle  = $broadcast->is_bundle;
            $delivery  = $isBundle ? $broadcast->bundle->deliveries->first() : $broadcast->delivery;
            $bundle    = $isBundle ? $broadcast->bundle : null;
        @endphp
        <div class="col-lg-6">
            <div class="card h-100 {{ $isBundle ? 'border-primary' : '' }}">
                <div class="card-body">
                    <!-- Top badges -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="badge bg-label-secondary">
                            <i class="bx bx-show me-1"></i>{{ $broadcast->view_count ?? 0 }} viewed
                        </span>
                        @if($isBundle)
                            @if($broadcast->is_partial)
                                <span class="badge bg-warning">
                                    <i class="bx bx-loader-alt bx-spin me-1"></i>GROWING BUNDLE
                                </span>
                            @else
                                <span class="badge bg-primary">BUNDLE PICKUP</span>
                            @endif
                        @else
                            <span class="badge bg-label-warning">BROADCAST</span>
                        @endif
                    </div>

                    <!-- Order + fee header -->
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="mb-1">
                                <i class="bx bx-broadcast me-1"></i>
                                Order #{{ $delivery->order->order_number }}
                            </h6>
                            @if($isBundle)
                                <small class="text-muted">
                                    <i class="bx bx-store me-1"></i>
                                    {{ $bundle->deliveries->count() }} shops &mdash; {{ $bundle->pickup_zone }} zone
                                </small>
                            @endif
                        </div>
                        <div class="text-end">
                            @php
                                $totalFee = $isBundle
                                    ? $bundle->deliveries->sum('delivery_fee')
                                    : $delivery->delivery_fee;
                            @endphp
                            <div class="badge bg-success mb-1 fs-5">
                                &#x20A6;{{ number_format($totalFee, 0) }}
                            </div>
                        </div>
                    </div>

                    @if($isBundle)
                    {{-- Bundle: list all pickup shops --}}
                    <div class="mb-3">
                        <small class="text-muted d-block mb-2">Pickup Stops ({{ $bundle->deliveries->count() }})</small>
                        @foreach($bundle->deliveries as $bDelivery)
                        <div class="p-2 bg-light rounded mb-2">
                            <div class="d-flex align-items-center mb-1">
                                <i class="bx bx-store text-primary me-2"></i>
                                <strong class="small">{{ $bDelivery->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                            </div>
                            <small class="text-muted ms-4">{{ Str::limit($bDelivery->pickup_address, 55) }}</small>
                        </div>
                        @endforeach
                        @if($broadcast->is_partial)
                        <div class="alert alert-warning py-2 mb-0">
                            <small>
                                <i class="bx bx-loader-alt bx-spin me-1"></i>
                                <strong>Growing:</strong> {{ $bundle->ready_count }} of {{ $bundle->expected_count }} sellers confirmed so far.
                                More stops may be added before you accept — accepting now locks in the current {{ $bundle->ready_count }} stop(s).
                            </small>
                        </div>
                        @endif
                    </div>
                    @else
                    {{-- Single delivery --}}
                    <div class="mb-3 p-2 bg-light rounded">
                        <small class="text-muted d-block">Pickup From</small>
                        <div class="d-flex align-items-center">
                            <i class="bx bx-store text-primary me-2"></i>
                            <strong>{{ $delivery->seller->shop->shop_name ?? 'Seller Shop' }}</strong>
                        </div>
                    </div>
                    @endif

                    <!-- Delivery address -->
                    <div class="mb-3">
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-xs flex-shrink-0 me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="bx bx-map bx-xs"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <small class="text-muted d-block">Deliver To</small>
                                <span class="small">{{ Str::limit($delivery->delivery_address, 55) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Stats row -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <i class="bx bx-package text-muted"></i>
                            <div class="small text-muted">Items</div>
                            <strong>
                                {{ $isBundle ? $bundle->deliveries->sum(fn($d) => $d->items->count()) : $delivery->items->count() }}
                            </strong>
                        </div>
                        <div class="col-4">
                            <i class="bx bx-cube text-muted"></i>
                            <div class="small text-muted">Weight</div>
                            <strong>
                                {{ $isBundle ? number_format($bundle->deliveries->sum('package_weight'), 1) : ($delivery->package_weight ?? 'N/A') }} kg
                            </strong>
                        </div>
                        <div class="col-4">
                            <i class="bx bx-store text-muted"></i>
                            <div class="small text-muted">{{ $isBundle ? 'Shops' : 'Notified' }}</div>
                            <strong>{{ $isBundle ? $bundle->deliveries->count() : ($broadcast->broadcast_to_count ?? '&mdash;') }}</strong>
                        </div>
                    </div>

                    @if(($broadcast->view_count ?? 0) > 3)
                    <div class="alert alert-warning py-2 mb-3">
                        <small>
                            <i class="bx bx-error-circle me-1"></i>
                            <strong>High Interest!</strong> {{ $broadcast->view_count }} riders viewing this
                        </small>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex gap-2">
                        <a href="{{ route('rider.broadcasts.show', $broadcast) }}"
                           class="btn btn-label-primary btn-sm flex-fill">
                            <i class="bx bx-show me-1"></i>View Details
                        </a>
                        <form action="{{ route('rider.broadcasts.accept', $broadcast) }}"
                              method="POST"
                              class="flex-fill"
                              onsubmit="return confirm('{{ $isBundle
                                  ? ($broadcast->is_partial
                                      ? 'Accept now? You\'ll collect from '.$bundle->ready_count.' confirmed stop(s) in '.$bundle->pickup_zone.'. More sellers may still join — they\'ll get separate deliveries if you accept now.'
                                      : 'Accept bundle? You\'ll pick up from '.$bundle->deliveries->count().' shops in '.$bundle->pickup_zone.'.')
                                  : 'Accept this delivery? First to accept wins!' }}')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-sm w-100 pulse-btn">
                                <i class="bx bx-check-circle me-1"></i>
                                {{ $isBundle ? ($broadcast->is_partial ? 'Accept ('.$bundle->ready_count.' stops)' : 'Accept Bundle') : 'Accept Now!' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bx bx-broadcast bx-lg text-muted mb-3"></i>
            <h5 class="mb-2">No Active Broadcasts</h5>
            <p class="text-muted mb-3">
                @if(Auth::user()->rider->status !== 'available')
                    Go online to receive broadcast notifications.
                @else
                    You'll be notified when deliveries are broadcasted to your area.
                @endif
            </p>
            <a href="{{ route('rider.deliveries.available') }}" class="btn btn-primary">
                <i class="bx bx-package me-1"></i>View Available Deliveries
            </a>
        </div>
    </div>
    @endif

</div>

@push('styles')
<style>
.pulse-btn:hover {
    transform: scale(1.05);
    transition: all 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
// Auto-refresh every 30 seconds to check for new broadcasts
setTimeout(() => {
    location.reload();
}, 30000);

// Show notification sound
@if($broadcasts->count() > 0)
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBCuBzvLZiT...');
    audio.play().catch(() => {});
@endif
</script>
@endpush

@endsection
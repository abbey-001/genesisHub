@extends('tracking.layouts.app')

@section('title', 'Verify Order')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            
            <!-- Header -->
            <div class="text-center mb-4">
                <i class="bx bx-shield-quarter bx-lg text-warning mb-3" style="font-size: 64px;"></i>
                <h3 class="mb-2">Verify Your Order</h3>
                <p class="text-muted">Enter verification code to track your order</p>
            </div>

            <!-- Order Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="mb-1">Order #{{ $order->order_number }}</h6>
                            <small class="text-muted">{{ $order->created_at->format('M d, Y') }}</small>
                        </div>
                        <span class="badge bg-label-{{ $order->status_badge }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <p class="mb-0 small text-muted">
                        <i class="bx bx-user me-1"></i>{{ $order->customer_name }}
                    </p>
                </div>
            </div>

            <!-- Verification Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <i class="bx bx-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('track.verify') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $order->id }}">

                        <div class="mb-4">
                            <label class="form-label">Verification Code</label>
                            <input type="text" 
                                   name="verification_code" 
                                   class="form-control form-control-lg text-center @error('verification_code') is-invalid @enderror" 
                                   placeholder="Enter code"
                                   maxlength="6"
                                   required
                                   autofocus
                                   style="letter-spacing: 0.5rem; font-size: 1.5rem; font-weight: bold;">
                            @error('verification_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bx bx-check-circle me-2"></i>Verify & Track Order
                        </button>
                    </form>

                    <hr>

                    <!-- Verification Options -->
                    <div class="alert alert-info mb-0">
                        <h6 class="alert-heading mb-2">
                            <i class="bx bx-info-circle me-2"></i>Verification Options:
                        </h6>
                        <ul class="mb-0 small">
                            <li class="mb-1">
                                <strong>Delivery OTP:</strong> Check your SMS or email for the 6-digit delivery code
                            </li>
                            <li class="mb-0">
                                <strong>Phone Verification:</strong> Enter the last 4 digits of your phone number ({{ substr($order->customer_phone, -4) }})
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center mt-4">
                <a href="{{ route('track.index') }}" class="btn btn-label-secondary">
                    <i class="bx bx-arrow-back me-1"></i>Track Different Order
                </a>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-format and limit input
document.querySelector('input[name="verification_code"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});
</script>
@endpush

@endsection
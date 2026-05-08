@extends('tracking.layouts.app')

@section('title', 'Track Your Order')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            
            <!-- Header -->
            <div class="text-center mb-5">
                <i class="bx bx-package bx-lg text-primary mb-3" style="font-size: 64px;"></i>
                <h2 class="mb-2">Track Your Order</h2>
                <p class="text-muted">Enter your order number to track your delivery</p>
            </div>

            <!-- Tracking Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <i class="bx bx-error-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('track.order') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label">Order Number</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">
                                    <i class="bx bx-receipt"></i>
                                </span>
                                <input type="text" 
                                       name="order_number" 
                                       class="form-control @error('order_number') is-invalid @enderror" 
                                       placeholder="e.g., ORD-123456"
                                       value="{{ old('order_number') }}"
                                       required
                                       autofocus>
                            </div>
                            @error('order_number')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                You can find your order number in your order confirmation email
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bx bx-search-alt me-2"></i>Track Order
                        </button>
                    </form>
                </div>
            </div>

            <!-- Help Section -->
            <div class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="bx bx-help-circle me-2"></i>Need Help?
                    </h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            Order numbers are sent to your email after placing an order
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            You can track multiple orders by entering different order numbers
                        </li>
                        <li class="mb-0">
                            <i class="bx bx-check-circle text-success me-2"></i>
                            For security, you'll need to verify with a code
                        </li>
                    </ul>
                </div>
            </div>

            @auth
            <!-- My Orders Link -->
            <div class="text-center mt-4">
                <a href="{{ route('orders.my') }}" class="btn btn-label-primary">
                    <i class="bx bx-list-ul me-1"></i>View All My Orders
                </a>
            </div>
            @else
            <!-- Login Prompt -->
            <div class="text-center mt-4">
                <p class="text-muted mb-2">Have an account?</p>
                <a href="{{ route('login') }}" class="btn btn-label-secondary">
                    <i class="bx bx-log-in me-1"></i>Login to View All Orders
                </a>
            </div>
            @endauth

        </div>
    </div>
</div>

@push('styles')
<style>
    .bx {
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@endsection
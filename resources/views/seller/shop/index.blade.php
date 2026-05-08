{{-- Shop Settings View (resources/views/seller/shop/index.blade.php) --}}
@extends('seller.layouts.app')

@section('title', 'Shop Settings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Shop Information</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('seller.shop.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Shop Name -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shop Name <span class="text-danger">*</span></label>
                                <input type="text" name="shop_name" class="form-control @error('shop_name') is-invalid @enderror" 
                                       value="{{ old('shop_name', $shop->shop_name) }}" required>
                                @error('shop_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" 
                                       value="{{ old('phone_number', $shop->phone_number) }}">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" 
                                       value="{{ old('email', $shop->email) }}">
                            </div>
                        </div>

                        <!-- Website -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Website</label>
                                <input type="url" name="website" class="form-control" 
                                       value="{{ old('website', $shop->website) }}">
                            </div>
                        </div>

                        <!-- Shop Description -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Shop Description</label>
                                <textarea name="shop_description" class="form-control" rows="4">{{ old('shop_description', $shop->shop_description) }}</textarea>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" 
                                       value="{{ old('address', $shop->address) }}">
                            </div>
                        </div>

                        <!-- City -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" 
                                       value="{{ old('city', $shop->city) }}">
                            </div>
                        </div>

                        <!-- State -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" 
                                       value="{{ old('state', $shop->state) }}">
                            </div>
                        </div>

                        <!-- Postal Code -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Postal Code</label>
                                <input type="text" name="postal_code" class="form-control" 
                                       value="{{ old('postal_code', $shop->postal_code) }}">
                            </div>
                        </div>

                        <!-- Country -->
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="country" class="form-control" 
                                       value="{{ old('country', $shop->country) }}">
                            </div>
                        </div>

                        <!-- Delivery / Pickup Zone -->
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Shop Location Zone</label>
                                <select name="delivery_zone" class="form-control @error('delivery_zone') is-invalid @enderror">
                                    <option value="">— Select the zone where your shop is located —</option>
                                    @foreach(\App\Models\DeliveryZone::pickupZones() as $zone)
                                        <option value="{{ $zone }}"
                                            {{ old('delivery_zone', $shop->delivery_zone) == $zone ? 'selected' : '' }}>
                                            {{ $zone }}
                                        </option>
                                    @endforeach
                                    <option value="Not Included"
                                        {{ old('delivery_zone', $shop->delivery_zone) == 'Not Included' ? 'selected' : '' }}>
                                        My location is not listed above
                                    </option>
                                </select>
                                <small class="text-muted">
                                    <i data-lucide="info" class="fs-14 me-1"></i>
                                    This controls which pickup zone applies to orders from your shop.
                                </small>
                                @error('delivery_zone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Shop Logo -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shop Logo</label>
                                @if($shop->shop_logo)
                                    <div class="mb-2">
                                        <img src="{{ asset('public/storage/' . $shop->shop_logo) }}" 
                                             alt="Shop Logo" class="rounded" style="max-width: 150px;">
                                    </div>
                                @endif
                                <input type="file" name="shop_logo" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended size: 200x200px</small>
                            </div>
                        </div>

                        <!-- Shop Banner -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Shop Banner</label>
                                @if($shop->banner)
                                    <div class="mb-2">
                                        <img src="{{ asset('public/storage/' . $shop->banner) }}" 
                                             alt="Shop Banner" class="rounded" style="max-width: 100%; max-height: 150px;">
                                    </div>
                                @endif
                                <input type="file" name="banner" class="form-control" accept="image/*">
                                <small class="text-muted">Recommended size: 1200x300px</small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save" class="fs-16"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
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
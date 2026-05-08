<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;

class SellerRegisterController extends Controller
{
    public function showForm()
    {
        return view('auth.seller-register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            // Account
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Shop
            'shop_name'        => ['required', 'string', 'max:255', 'unique:shops'],
            'shop_description' => ['required', 'string', 'max:1000'],
            'shop_logo'        => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'banner'           => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:4096'],
            'phone_number'     => ['required', 'string', 'max:20'],
            'website'          => ['nullable', 'url', 'max:255'],

            // Business
            'business_type'       => ['required', 'string', 'in:individual,company,partnership'],
            'tax_id'              => ['nullable', 'string', 'unique:sellers', 'max:50'],
            'bank_name'           => ['required', 'string', 'max:255'],
            'bank_account'        => ['required', 'string', 'max:50'],
            'account_holder_name' => ['required', 'string', 'max:255'],

            // Address
            'address'       => ['required', 'string', 'max:500'],
            'city'          => ['required', 'string', 'max:100'],
            'state'         => ['required', 'string', 'max:100'],
            'postal_code'   => ['required', 'string', 'max:20'],
            'country'       => ['required', 'string', 'max:100'],
            'delivery_zone' => [
                'required', 'string',
                Rule::in(array_merge(
                    (array) \App\Models\DeliveryZone::pickupZones(),
                    ['Not Included']
                )),
            ],
        ]);

        // ── 1. Create the User record ─────────────────────────────────────────
        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'user_type' => 'seller',
        ]);

        // ── 2. Store uploaded files ───────────────────────────────────────────
        $logoPath   = $request->hasFile('shop_logo')
            ? $request->file('shop_logo')->store('shops/logos', 'public')
            : null;

        $bannerPath = $request->hasFile('banner')
            ? $request->file('banner')->store('shops/banners', 'public')
            : null;

        // ── 3. Create Shop (seller_id set after seller is created) ────────────
        $shop = Shop::create([
            'seller_id'        => null,
            'shop_name'        => $validated['shop_name'],
            'shop_description' => $validated['shop_description'],
            'shop_logo'        => $logoPath,
            'banner'           => $bannerPath,
            'phone_number'     => $validated['phone_number'],
            'email'            => $validated['email'],
            'website'          => $validated['website'] ?? null,
            'address'          => $validated['address'],
            'city'             => $validated['city'],
            'state'            => $validated['state'],
            'postal_code'      => $validated['postal_code'],
            'country'          => $validated['country'],
            'delivery_zone'    => $validated['delivery_zone'],
        ]);

        // ── 4. Create Seller ──────────────────────────────────────────────────
        // Note: no shop_id here — the relationship is seller_id on the shops table
        $seller = Seller::create([
            'user_id'             => $user->id,
            'business_type'       => $validated['business_type'],
            'tax_id'              => $validated['tax_id'],
            'bank_account'        => $validated['bank_account'],
            'bank_name'           => $validated['bank_name'],
            'account_holder_name' => $validated['account_holder_name'],
            'phone_number'        => $validated['phone_number'],
            'address'             => $validated['address'],
            'city'                => $validated['city'],
            'state'               => $validated['state'],
            'postal_code'         => $validated['postal_code'],
            'country'             => $validated['country'],
            'verification_status' => 'pending',
        ]);

        // ── 5. Link shop → seller ─────────────────────────────────────────────
        $shop->update(['seller_id' => $seller->id]);

        // ── 6. Log in on the seller guard ─────────────────────────────────────
        // Important: use the seller guard, NOT auth()->login(), which would
        // log them in on the web/customer guard instead.
        Auth::guard('seller')->login($user);

        // ── 7. Fire Registered event — this triggers the verification email ───
        event(new Registered($user));
        try {
              app(\App\Services\Telegram\AdminTelegramService::class)
                  ->notifyNewSellerApplication($seller);
          } catch (\Exception $e) {
              \Log::warning('New seller app Telegram failed', ['error' => $e->getMessage()]);
         }

        return redirect()->route('seller.verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
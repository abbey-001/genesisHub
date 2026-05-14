<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Handles Phase 2 of the seller social registration flow.
 *
 * After Google/Facebook login creates a partial User record,
 * this controller shows and processes the completion form —
 * Steps 2, 3, and 4 of the normal seller registration
 * (shop info, business info, bank details).
 *
 * The pending user is identified via session('social_seller_pending_id').
 * We do NOT log the seller in until they have completed the form,
 * to prevent half-registered sellers accessing the dashboard.
 */
class SellerSocialCompleteController extends Controller
{
    /**
     * Show the completion form.
     */
    public function showForm()
    {
        $userId = session('social_seller_pending_id');

        if (! $userId) {
            return redirect()->route('seller.register.form')
                ->withErrors(['email' => 'Your session has expired. Please sign up again.']);
        }

        $user = User::find($userId);

        if (! $user || $user->user_type !== 'seller') {
            session()->forget('social_seller_pending_id');
            return redirect()->route('seller.register.form');
        }

        // If they somehow already have a Seller record, go straight to login
        if ($user->seller) {
            session()->forget('social_seller_pending_id');
            Auth::guard('seller')->login($user);
            return redirect()->route('seller.verification.notice');
        }

        return view('auth.seller-social-complete', compact('user'));
    }

    /**
     * Process the completion form submission.
     */
    public function complete(Request $request)
    {
        $userId = session('social_seller_pending_id');

        if (! $userId) {
            return redirect()->route('seller.register.form')
                ->withErrors(['email' => 'Your session has expired. Please sign up again.']);
        }

        $user = User::findOrFail($userId);

        $validated = $request->validate([
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

        // ── Store uploaded files ──────────────────────────────────────────────
        $logoPath   = $request->hasFile('shop_logo')
            ? $request->file('shop_logo')->store('shops/logos', 'public')
            : null;

        $bannerPath = $request->hasFile('banner')
            ? $request->file('banner')->store('shops/banners', 'public')
            : null;

        // ── Create Shop ───────────────────────────────────────────────────────
        $shop = Shop::create([
            'seller_id'        => null,
            'shop_name'        => $validated['shop_name'],
            'shop_description' => $validated['shop_description'],
            'shop_logo'        => $logoPath,
            'banner'           => $bannerPath,
            'phone_number'     => $validated['phone_number'],
            'email'            => $user->email,
            'website'          => $validated['website'] ?? null,
            'address'          => $validated['address'],
            'city'             => $validated['city'],
            'state'            => $validated['state'],
            'postal_code'      => $validated['postal_code'],
            'country'          => $validated['country'],
            'delivery_zone'    => $validated['delivery_zone'],
        ]);

        // ── Create Seller ─────────────────────────────────────────────────────
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

        // ── Link shop → seller ────────────────────────────────────────────────
        $shop->update(['seller_id' => $seller->id]);

        try {
            app(\App\Services\Telegram\AdminTelegramService::class)
                ->notifyNewSellerApplication($seller->loadMissing('user', 'shop'));
        } catch (\Exception $e) {
            \Log::warning('New seller app Telegram failed', ['error' => $e->getMessage()]);
        }

        // ── Update phone on user record ───────────────────────────────────────
        $user->update(['phone' => $validated['phone_number']]);

        // ── Clear session and log in ──────────────────────────────────────────
        session()->forget('social_seller_pending_id');

        Auth::guard('seller')->login($user);

        return redirect()->route('seller.verification.notice')
            ->with('status', 'registration-complete');
    }
}

<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// ========== Shop Controller ==========
class ShopController extends Controller
{
    public function index()
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        return view('seller.shop.index', compact('shop'));
    }

    public function update(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;

        $validated = $request->validate([
            'shop_name'        => 'required|string|max:255',
            'shop_description' => 'nullable|string',
            'phone_number'     => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:255',
            'website'          => 'nullable|url|max:255',
            'address'          => 'nullable|string|max:500',
            'city'             => 'nullable|string|max:100',
            'state'            => 'nullable|string|max:100',
            'postal_code'      => 'nullable|string|max:20',
            'country'          => 'nullable|string|max:100',
            'delivery_zone'    => [
                'nullable',
                'string',
                Rule::in(
                    array_merge(
                        (array) \App\Models\DeliveryZone::pickupZones(),
                        ['Not Included']
                    )
                ),
            ],
            'shop_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner'    => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        // Handle logo upload
        if ($request->hasFile('shop_logo')) {
            if ($shop->shop_logo) {
                Storage::disk('public')->delete($shop->shop_logo);
            }
            $validated['shop_logo'] = $request->file('shop_logo')->store('shops/logos', 'public');
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            if ($shop->banner) {
                Storage::disk('public')->delete($shop->banner);
            }
            $validated['banner'] = $request->file('banner')->store('shops/banners', 'public');
        }

        $shop->update($validated);

        return back()->with('success', 'Shop information updated successfully.');
    }
}
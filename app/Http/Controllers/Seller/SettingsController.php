<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Review;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Services\SellerSecurityAlertService;

// ========== Settings Controller ==========
class SettingsController extends Controller
{
    public function index()
    {
        $seller = Auth::guard('seller')->user()->seller;
        $user = Auth::guard('seller')->user();
        
        return view('seller.settings.index', compact('seller', 'user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('seller')->user();
        $seller = $user->seller;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);
        
   SellerSecurityAlertService::checkSellerChanges($user, $seller, $request->all());
   SellerSecurityAlertService::checkUserChanges($user, $request->all());

        $seller->update([
            'business_type' => $validated['business_type'] ?? $seller->business_type,
            'address' => $validated['address'] ?? $seller->address,
            'city' => $validated['city'] ?? $seller->city,
            'state' => $validated['state'] ?? $seller->state,
            'postal_code' => $validated['postal_code'] ?? $seller->postal_code,
            'country' => $validated['country'] ?? $seller->country,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updateBank(Request $request)
    {
         $user = Auth::guard('seller')->user();
        $seller = Auth::guard('seller')->user()->seller;

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_holder_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:50',
        ]);
   SellerSecurityAlertService::checkSellerChanges($user, $seller, $request->all());
   SellerSecurityAlertService::checkUserChanges($user, $request->all());
        $seller->update($validated);

        return back()->with('success', 'Bank information updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], Auth::guard('seller')->user()->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        Auth::guard('seller')->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
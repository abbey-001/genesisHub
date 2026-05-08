<?php

// app/Http/Controllers/Seller/PayoutController.php
namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class OtherController extends Controller
{
    public function index()
    {
        $seller = Auth::guard('seller')->user()->seller;
        
        $payouts = Payout::where('seller_id', $seller->id)
            ->latest()
            ->paginate(20);
        
        // Calculate available balance
        $totalEarnings = OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function($q) {
                $q->where('payment_status', 'paid');
            })
            ->sum('total');
        
        $totalPayouts = Payout::where('seller_id', $seller->id)
            ->whereIn('status', ['completed', 'processing'])
            ->sum('amount');
        
        $availableBalance = $totalEarnings - $totalPayouts;
        
        $pendingPayouts = Payout::where('seller_id', $seller->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        return view('seller.payouts.index', compact(
            'payouts',
            'availableBalance',
            'totalEarnings',
            'totalPayouts',
            'pendingPayouts'
        ));
    }
    
    public function request(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:10'
        ]);
        
        // Calculate available balance
        $totalEarnings = OrderItem::where('seller_id', $seller->id)
            ->whereHas('order', function($q) {
                $q->where('payment_status', 'paid');
            })
            ->sum('total');
        
        $totalPayouts = Payout::where('seller_id', $seller->id)
            ->whereIn('status', ['completed', 'processing', 'pending'])
            ->sum('amount');
        
        $availableBalance = $totalEarnings - $totalPayouts;
        
        if ($validated['amount'] > $availableBalance) {
            return back()->with('error', 'Insufficient balance for payout request.');
        }
        
        Payout::create([
            'seller_id' => $seller->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
            'requested_at' => now()
        ]);
        
        return back()->with('success', 'Payout request submitted successfully!');
    }

    public function edit()
    {
        $shop = Auth::guard('seller')->user()->seller->shop;
        
        if (!$shop) {
            $shop = Auth::guard('seller')->user()->seller->shop()->create([
                'shop_name' => Auth::guard('seller')->user()->name . "'s Shop",
                'is_active' => true
            ]);
        }
        
        return view('seller.shop.edit', compact('shop'));
    }
    
    public function update(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        $shop = $seller->shop;
        
        $validated = $request->validate([
            'shop_name' => 'required|max:255',
            'shop_description' => 'nullable',
            'phone_number' => 'nullable|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'address' => 'nullable',
            'city' => 'nullable|max:100',
            'state' => 'nullable|max:100',
            'postal_code' => 'nullable|max:20',
            'country' => 'nullable|max:100',
            'shop_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
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
        
        return back()->with('success', 'Shop settings updated successfully!');
    }

    public function settingsIndex()
    {
        $user = Auth::guard('seller')->user();
        $seller = $user->seller;
        
        return view('seller.settings.index', compact('user', 'seller'));
    }
    
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('seller')->user();
        
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);
        
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $path;
        }
        
        $user->update($validated);
        
        return back()->with('success', 'Profile updated successfully!');
    }
    
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        
        Auth::guard('seller')->user()->update([
            'password' => Hash::make($validated['password'])
        ]);
        
        return back()->with('success', 'Password updated successfully!');
    }
    
    public function updateBank(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        
        $validated = $request->validate([
            'bank_account' => 'required',
            'bank_name' => 'required|max:255',
            'account_holder_name' => 'required|max:255',
        ]);
        
        $seller->update($validated);
        
        return back()->with('success', 'Bank details updated successfully!');
    }
    
    public function updateNotifications(Request $request)
    {
        $seller = Auth::guard('seller')->user()->seller;
        
        $settings = [
            'email_order_notifications' => $request->has('email_order_notifications'),
            'email_review_notifications' => $request->has('email_review_notifications'),
            'email_low_stock_alerts' => $request->has('email_low_stock_alerts'),
            'email_payout_notifications' => $request->has('email_payout_notifications'),
        ];
        
        $seller->update(['notification_settings' => json_encode($settings)]);
        
        return back()->with('success', 'Notification preferences updated successfully!');
    }
}
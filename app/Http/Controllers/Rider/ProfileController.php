<?php

namespace App\Http\Controllers\Rider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $rider = Auth::user()->rider;
        $user = Auth::user();
        
        return view('rider.profile.index', compact('rider', 'user'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore(Auth::id())
            ],
            'vehicle_type' => 'nullable|string|max:100',
        ]);
        
        try {
            $rider = Auth::user()->rider;
            $user = Auth::user();
            
            // Update company info
            $rider->update([
                'full_name' => $request->full_name,
                'phone_number' => $request->phone_number,
                'vehicle_type' => $request->vehicle_type,
            ]);
            
            // Update user email and name
            $user->update([
                'email' => $request->email,
                'name' => $request->full_name,
            ]);
            
            return back()->with('success', 'Profile updated successfully');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }
    
    public function updateBank(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:20|regex:/^[0-9]+$/',
            'account_name' => 'required|string|max:255',
        ]);
        
        try {
            $rider = Auth::user()->rider;
            
            $rider->update([
                'bank_name' => $request->bank_name,
                'account_number' => $request->account_number,
                'account_name' => $request->account_name,
            ]);
            
            return back()->with('success', 'Bank information updated successfully');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update bank information: ' . $e->getMessage());
        }
    }
    
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        $user = Auth::user();
        
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect');
        }
        
        try {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
            
            return back()->with('success', 'Password updated successfully');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update password: ' . $e->getMessage());
        }
    }
    
    public function generateTelegramLink(): \Illuminate\Http\JsonResponse
{
    $rider = Auth::user()->rider;
    
    $token = \Illuminate\Support\Str::random(32);

    $rider->update(['telegram_link_token' => $token]);

    $botUsername = 'genesishub_delivery_bot'; // replace with your bot's @username
    $link = "https://t.me/{$botUsername}?start={$token}";

    return response()->json(['link' => $link]);
}
}
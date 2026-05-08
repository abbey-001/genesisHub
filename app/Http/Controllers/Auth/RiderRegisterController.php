<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RiderRegisterController extends Controller
{
    public function showRiderRegistrationForm()
    {
        return view('auth.rider-register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => ['required', 'string', 'max:20'],
            'vehicle_type' => ['required', 'in:motorcycle,bicycle,car,van'],
            'vehicle_registration' => ['required', 'string', 'max:50'],
            'license_number' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    
        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'user_type' => 'rider',
            ]);
    
            Rider::create([
                'user_id' => $user->id,
                'full_name' => $validated['name'],
                'phone_number' => $validated['phone'],
                'vehicle_type' => $validated['vehicle_type'],
                'vehicle_registration' => $validated['vehicle_registration'],
                'license_number' => $validated['license_number'],
                'status' => 'offline',
                'is_verified' => false,
                'is_active' => false,
            ]);
    
            return $user;
        });
    }
    
}

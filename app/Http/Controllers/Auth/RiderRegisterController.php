<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Rider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
    
            $rider = Rider::create([
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

            try {
                app(\App\Services\Telegram\AdminTelegramService::class)
                    ->notifyNewRiderApplication($rider->loadMissing('user'));
            } catch (\Exception $e) {
                \Log::warning('Admin Telegram rider application alert failed', [
                    'rider_id' => $rider->id,
                    'error'    => $e->getMessage(),
                ]);
            }
    
            return $user;
        });
    }
    
}

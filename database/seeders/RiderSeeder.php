<?php
// database/seeders/RiderSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RiderSeeder extends Seeder
{
    public function run(): void
    {
        $riders = [
            [
                'name' => 'John Rider',
                'email' => 'rider1@example.com',
                'full_name' => 'John Rider',
                'phone' => '08012345678',
                'vehicle_type' => 'motorcycle',
            ],
            [
                'name' => 'Jane Rider',
                'email' => 'rider2@example.com',
                'full_name' => 'Jane Rider',
                'phone' => '08012345679',
                'vehicle_type' => 'bicycle',
            ],
        ];

        foreach ($riders as $riderData) {
            $user = User::create([
                'name' => $riderData['name'],
                'email' => $riderData['email'],
                'password' => Hash::make('password'),
                'user_type' => 'rider',
            ]);

            Rider::create([
                'user_id' => $user->id,
                'full_name' => $riderData['full_name'],
                'phone_number' => $riderData['phone'],
                'vehicle_type' => $riderData['vehicle_type'],
                'vehicle_registration' => 'ABC-' . rand(100, 999) . 'XY',
                'license_number' => 'LIC' . rand(100000, 999999),
                'status' => 'available',
                'is_verified' => true,
                'is_active' => true,
                'rating' => 4.5,
            ]);
        }
    }
}
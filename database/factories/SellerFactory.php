<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition(): array
    {
        return [
            'user_id'             => User::factory()->state(['user_type' => 'seller']),
            'business_type'       => 'individual',
            'bank_name'           => 'Test Bank',
            'bank_account'        => fake()->numerify('##########'),
            'account_holder_name' => fake()->name(),
            'phone_number'        => fake()->numerify('080########'),
            'address'             => fake()->streetAddress(),
            'city'                => 'Lagos',
            'state'               => 'Lagos',
            'postal_code'         => '100001',
            'country'             => 'Nigeria',
            'is_verified'         => true,
            'verification_status' => 'verified',
            'commission_rate'     => 10,
            'telegram_notifications_enabled' => false,
        ];
    }

    public function unverified(): static
    {
        return $this->state(['is_verified' => false, 'verification_status' => 'pending']);
    }
}

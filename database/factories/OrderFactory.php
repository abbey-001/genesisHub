<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'              => User::factory(),
            'customer_name'        => fake()->name(),
            'customer_email'       => fake()->unique()->safeEmail(),
            'customer_phone'       => fake()->numerify('080########'),
            'shipping_address'     => fake()->streetAddress(),
            'shipping_city'        => 'Lagos',
            'shipping_state'       => 'Lagos',
            'shipping_postal_code' => '100001',
            'shipping_country'     => 'Nigeria',
            'subtotal'             => 10000,
            'shipping_fee'         => 0,
            'total'                => 10000,
            'status'               => 'pending',
            'payment_status'       => 'pending',
            'payment_method'       => 'paystack',
            'payment_reference'    => 'GH-' . Str::upper(Str::random(12)),
        ];
    }

    public function paid(): static
    {
        return $this->state(['payment_status' => 'paid', 'status' => 'processing', 'paid_at' => now()]);
    }
}

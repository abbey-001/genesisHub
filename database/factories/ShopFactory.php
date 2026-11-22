<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'seller_id'    => Seller::factory(),
            'shop_name'    => $name,
            'slug'         => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'email'        => fake()->unique()->companyEmail(),
            'phone_number' => fake()->numerify('080########'),
            'is_active'    => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name'             => ucfirst($name),
            'slug'             => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'price'            => 10000,
            'stock'            => 20,
            'is_active'        => true,
            'is_featured'      => false,
            'category_id'      => Category::factory(),
            'shop_id'          => Shop::factory(),
            'condition'        => 'new',
            'fulfillment_type' => 'in_stock',
        ];
    }
}

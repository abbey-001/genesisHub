<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        // The model derives the slug from the name, so names must be unique.
        return [
            'name'        => fake()->unique()->words(2, true),
            'is_featured' => false,
        ];
    }
}

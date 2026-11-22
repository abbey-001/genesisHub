<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id'         => Order::factory(),
            'product_id'       => Product::factory(),
            'seller_id'        => Seller::factory(),
            'product_name'     => fake()->words(3, true),
            'quantity'         => 1,
            'price'            => 10000,
            'total_price'      => 10000,
            'status'           => 'pending',
            'fulfillment_type' => 'in_stock',
        ];
    }
}

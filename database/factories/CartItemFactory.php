<?php

namespace Database\Factories;

use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'shopping_cart_id' => ShoppingCart::inRandomOrder()->first()->id,
            'product_id' => fake()->numberBetween(1, 5),
            'product_type' => fake()->randomElement(['case','cooler','cpu','motherboard','psu','ram','storage']),
            'quantity' => fake()->numberBetween(1,5),
            'total_price' => fake()->randomFloat(2,1000,50000),
            'processed' => true
        ];
    }
}

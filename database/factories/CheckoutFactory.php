<?php

namespace Database\Factories;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Checkout>
 */
class CheckoutFactory extends Factory
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
            'cart_item_id' => CartItem::inRandomOrder()->first()->id,
            'checkout_date' => $this->faker->dateTimeBetween(
                '2025-11-25 23:59:59',
                '2025-12-01 00:00:00'   // End: November 25th (this will wrap to next year)
            ),
            'total_cost' => fake()->randomFloat(2,1000,50000),
            'payment_method' => 'Paypal',
            'payment_status' => 'Paid',
            'pickup_status' => null,
            'pickup_date' => null,
        ];
    }
}

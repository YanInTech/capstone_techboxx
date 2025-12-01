<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserBuild;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderedBuild>
 */
class OrderedBuildFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'created_at' => $this->faker->dateTimeBetween(
                '2025-11-25 23:59:59',
                '2025-12-01 00:00:00'   // End: November 25th (this will wrap to next year)
            ),
            'user_build_id' => UserBuild::inRandomOrder()->first()->id,
            'status' => 'Pending',
            // 'user_id' => User::inRandomOrder()->first()->id,
            'payment_status' => 'Paid',
            'payment_method' => fake()->randomElement(['Paypal']),
            'pickup_status' => null,
        ];
    }
}

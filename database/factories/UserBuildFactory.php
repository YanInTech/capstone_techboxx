<?php

namespace Database\Factories;

use App\Models\Hardware\Cooler;
use App\Models\Hardware\Cpu;
use App\Models\Hardware\Gpu;
use App\Models\Hardware\Motherboard;
use App\Models\Hardware\PcCase;
use App\Models\Hardware\Psu;
use App\Models\Hardware\Ram;
use App\Models\Hardware\Storage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserBuild>
 */
class UserBuildFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'build_name' => fake()->word(),
            'pc_case_id' => PcCase::query()->inRandomOrder()->value('id'),
            'motherboard_id' => Motherboard::query()->inRandomOrder()->value('id'),
            'cpu_id' => Cpu::query()->inRandomOrder()->value('id'),
            'storage_id' => Storage::query()->inRandomOrder()->value('id'),
            'ram_id' => Ram::query()->inRandomOrder()->value('id'),
            'gpu_id' => Gpu::query()->inRandomOrder()->value('id'),
            'psu_id' => Psu::query()->inRandomOrder()->value('id'),
            'cooler_id' => Cooler::query()->inRandomOrder()->value('id'),
            'total_price' => fake()->randomFloat(2,1000,50000),
            'status' => 'Ordered',
        ];
    }
}

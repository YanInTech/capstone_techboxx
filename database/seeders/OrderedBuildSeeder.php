<?php

namespace Database\Seeders;

use App\Models\OrderedBuild;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderedBuildSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        OrderedBuild::factory()->count(10)->create();

    }
}

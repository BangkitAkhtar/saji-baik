<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OutletFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->company(), 'area' => 'Alam Sutera', 'address' => 'Jl. Contoh No. 12', 'description' => 'Outlet uji', 'opens_at' => '10:00', 'closes_at' => '20:00', 'is_active' => true];
    }
}

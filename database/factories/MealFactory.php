<?php

namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MealFactory extends Factory
{
    public function definition(): array
    {
        return ['outlet_id' => Outlet::factory(), 'name' => 'Nasi Ayam Uji', 'slug' => Str::uuid()->toString(), 'description' => 'Nasi ayam dan sayur', 'category' => 'Nasi rumahan', 'price' => 12000, 'stock' => 10, 'available_date' => today(), 'pickup_start' => '10:00', 'pickup_end' => '20:00', 'components' => ['Nasi', 'Ayam', 'Sayur'], 'allergens' => 'Kedelai', 'calories' => 550, 'protein' => 25, 'image' => 'nasi-ayam.jpg', 'is_active' => true];
    }
}

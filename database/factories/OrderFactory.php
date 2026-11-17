<?php

namespace Database\Factories;

use App\Models\Meal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'meal_id' => Meal::factory(), 'outlet_id' => fn (array $data) => Meal::findOrFail($data['meal_id'])->outlet_id, 'code' => 'SB-'.Str::upper(Str::random(8)), 'meal_name' => 'Nasi Ayam Uji', 'customer_name' => 'Teman Uji', 'phone' => '081234567890', 'quantity' => 1, 'unit_price' => 12000, 'total_price' => 12000, 'pickup_at' => today()->setTime(13, 0), 'status' => 'pending'];
    }
}

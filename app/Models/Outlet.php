<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'area', 'address', 'description', 'opens_at', 'closes_at', 'is_active', 'contact_note', 'image', 'pickup_grace_minutes'])]
class Outlet extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function completedOrders(): HasMany
    {
        return $this->hasMany(Order::class)->where('status', 'completed');
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'meal_id', 'outlet_id', 'code', 'meal_name', 'customer_name', 'phone', 'quantity', 'unit_price', 'total_price', 'pickup_at', 'notes', 'status'])]
class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending' => 'Dipesan', 'ready' => 'Siap diambil', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];

    protected function casts(): array
    {
        return ['pickup_at' => 'datetime', 'quantity' => 'integer', 'total_price' => 'integer'];
    }

    public function meal(): BelongsTo
    {
        return $this->belongsTo(Meal::class);
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['outlet_id', 'name', 'slug', 'description', 'category', 'price', 'stock', 'available_date', 'pickup_start', 'pickup_end', 'components', 'allergens', 'calories', 'protein', 'image', 'is_active'])]
class Meal extends Model
{
    use HasFactory;

    public const CATEGORIES = ['Nasi rumahan', 'Rice bowl', 'Tanpa daging'];

    public const IMAGES = ['nasi-ayam.jpg', 'nasi-lengkap.jpg', 'nasi-goreng.jpg', 'rice-bowl.jpg'];

    protected function casts(): array
    {
        return ['components' => 'array', 'available_date' => 'date', 'is_active' => 'boolean', 'price' => 'integer', 'stock' => 'integer'];
    }

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_active', true)->whereDate('available_date', today())->whereHas('outlet', fn (Builder $outlet) => $outlet->where('is_active', true));
    }

    public function pickupSlots(): array
    {
        $start = Carbon::parse($this->available_date->format('Y-m-d').' '.$this->pickup_start);
        $end = Carbon::parse($this->available_date->format('Y-m-d').' '.$this->pickup_end);
        $slots = [];
        while ($start->lte($end)) {
            if ($start->gt(now()->addMinutes(15))) {
                $slots[] = $start->format('H:i');
            }
            $start->addMinutes(30);
        }

        return $slots;
    }

    public function canOrder(): bool
    {
        return $this->is_active && $this->outlet->is_active && $this->available_date->isToday() && $this->stock > 0 && count($this->pickupSlots()) > 0;
    }
}

<?php

namespace App\Http\Requests;

use App\Models\Meal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'outlet_id' => ['required', Rule::exists('outlets', 'id')->where('is_active', true)],
            'description' => ['required', 'string', 'max:1000'],
            'category' => ['required', Rule::in(Meal::CATEGORIES)],
            'price' => ['required', 'integer', 'min:1000', 'max:100000'],
            'stock' => ['required', 'integer', 'min:0', 'max:500'],
            'available_date' => ['required', 'date', 'after_or_equal:today'],
            'pickup_start' => ['required', 'date_format:H:i'],
            'pickup_end' => ['required', 'date_format:H:i', 'after:pickup_start'],
            'components_text' => ['required', 'string', 'max:500'],
            'allergens' => ['required', 'string', 'max:200'],
            'calories' => ['required', 'integer', 'min:1', 'max:2000'],
            'protein' => ['required', 'integer', 'min:0', 'max:200'],
            'image' => ['required', Rule::in(Meal::IMAGES)],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

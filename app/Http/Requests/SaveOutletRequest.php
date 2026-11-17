<?php

namespace App\Http\Requests;

use App\Models\Outlet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOutletRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:80'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'contact_note' => ['required', 'string', 'max:200'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i', 'after:opens_at'],
            'pickup_grace_minutes' => ['required', 'integer', 'min:0', 'max:60'],
            'image' => ['required', Rule::in(array_keys(Outlet::IMAGE_OPTIONS))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

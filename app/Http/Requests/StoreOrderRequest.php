<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:2'],
            'customer_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^(\+62|0)[0-9]{8,13}$/'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:400'],
            'needs_support' => ['accepted'],
            'pickup_agreement' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.max' => 'Maksimal 2 porsi per pesanan agar lebih banyak orang kebagian.',
            'phone.regex' => 'Gunakan nomor Indonesia, contoh 081234567890.',
            'needs_support.accepted' => 'Mohon setujui pernyataan kebutuhan terlebih dahulu.',
            'pickup_agreement.accepted' => 'Mohon setujui pengambilan langsung di outlet.',
            'pickup_time.required' => 'Pilih waktu pengambilan.',
            'customer_name.required' => 'Nama pengambil perlu diisi.',
        ];
    }
}

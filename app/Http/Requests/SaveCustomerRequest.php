<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SaveCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($customer instanceof User ? $customer->id : null)],
            'phone' => ['nullable', 'regex:/^(\+62|0)[0-9]{8,13}$/'],
            'role' => ['required', Rule::in(['customer', 'admin'])],
            'account_status' => ['required', Rule::in(['active', 'inactive'])],
            'password' => [$customer ? 'nullable' : 'required', 'string', 'max:255', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Email sudah digunakan, termasuk oleh akun yang diarsipkan.',
            'password.required' => 'Kata sandi diperlukan untuk akun baru.',
            'password.confirmed' => 'Konfirmasi kata sandi belum sama.',
            'phone.regex' => 'Gunakan nomor Indonesia, contoh 081234567890.',
            'role.in' => 'Pilih peran Admin atau Pelanggan.',
        ];
    }
}

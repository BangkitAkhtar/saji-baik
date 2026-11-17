<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.form', ['register' => false]);
    }

    public function register(): View
    {
        return view('auth.form', ['register' => true]);
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($data, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi belum sesuai.']);
        }
        $request->session()->regenerate();

        return redirect()->intended($request->user()->is_admin ? route('admin.index') : route('menu.index'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(\+62|0)[0-9]{8,13}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], ['password.confirmed' => 'Konfirmasi kata sandi belum sama.', 'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.']);
        $user = User::create($data);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('menu.index'))->with('success', 'Selamat datang! Semoga hari ini terasa lebih ringan.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Kamu sudah keluar dari akun.');
    }
}

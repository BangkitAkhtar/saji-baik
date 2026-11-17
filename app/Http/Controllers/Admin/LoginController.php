<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);
        if (! Auth::attempt([...$data, 'is_admin' => true, 'is_active' => true])) {
            throw ValidationException::withMessages(['email' => 'Email atau kata sandi admin belum sesuai.']);
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.index'));
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveOutletRequest;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OutletController extends Controller
{
    public function index(): View
    {
        return view('admin.outlets.index', ['outlets' => Outlet::withCount('meals')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        return view('admin.outlets.form', ['outlet' => new Outlet(['opens_at' => '10:00', 'closes_at' => '20:00', 'pickup_grace_minutes' => 30, 'image' => 'outlet-illustration.png', 'contact_note' => 'Hubungi petugas outlet saat pengambilan (demo).', 'is_active' => true])]);
    }

    public function store(SaveOutletRequest $request): RedirectResponse
    {
        Outlet::create([...$request->validated(), 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.outlets.index')->with('success', 'Outlet baru berhasil ditambahkan.');
    }

    public function edit(Outlet $outlet): View
    {
        return view('admin.outlets.form', compact('outlet'));
    }

    public function update(SaveOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        $outlet->update([...$request->validated(), 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.outlets.index')->with('success', 'Informasi outlet sudah diperbarui.');
    }
}

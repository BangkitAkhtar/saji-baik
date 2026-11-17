<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meal;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MealCopyController extends Controller
{
    public function create(Request $request): View
    {
        $data = $request->validate(['source_date' => ['nullable', 'date'], 'outlet' => ['nullable', 'integer', 'exists:outlets,id']]);
        $sourceDate = $data['source_date'] ?? today()->format('Y-m-d');
        $meals = Meal::with('outlet')->whereDate('available_date', $sourceDate)->when($request->filled('outlet'), fn ($q) => $q->where('outlet_id', $data['outlet']))->orderBy('id')->get();

        return view('admin.copy', ['meals' => $meals, 'sourceDate' => $sourceDate, 'outlets' => Outlet::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'meal_ids' => ['required', 'array', 'min:1', 'max:100'],
            'meal_ids.*' => ['required', 'integer', 'distinct', Rule::exists('meals', 'id')],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
            'stock' => ['required', 'integer', 'min:1', 'max:500'],
        ], ['meal_ids.required' => 'Pilih minimal satu menu untuk disalin.']);
        $count = DB::transaction(function () use ($data) {
            $sources = Meal::with('outlet')->whereKey($data['meal_ids'])->lockForUpdate()->get();
            if ($sources->contains(fn (Meal $meal) => $meal->available_date->format('Y-m-d') === $data['target_date'])) {
                throw ValidationException::withMessages(['target_date' => 'Tanggal tujuan harus berbeda dari tanggal menu sumber.']);
            }
            $created = 0;
            foreach ($sources as $source) {
                if (! $source->outlet->is_active) {
                    throw ValidationException::withMessages(['meal_ids' => 'Aktifkan outlet sumber sebelum menyalin menu.']);
                }
                $origin = $source->source_meal_id ?? $source->id;
                if (Meal::where('source_meal_id', $origin)->whereDate('available_date', $data['target_date'])->exists() || Meal::whereKey($origin)->whereDate('available_date', $data['target_date'])->exists()) {
                    continue;
                }
                $copy = $source->replicate(['slug', 'source_meal_id']);
                $copy->slug = Str::slug($source->name).'-'.Str::lower(Str::random(8));
                $copy->source_meal_id = $origin;
                $copy->available_date = $data['target_date'];
                $copy->stock = $data['stock'];
                $copy->is_active = true;
                $copy->save();
                $created++;
            }

            return $created;
        }, 3);

        return redirect()->route('admin.meals', ['date' => $data['target_date']])->with('success', $count.' menu berhasil disalin. Menu yang sudah ada pada tanggal tujuan dilewati.');
    }
}

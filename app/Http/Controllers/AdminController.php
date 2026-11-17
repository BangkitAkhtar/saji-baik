<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveMealRequest;
use App\Models\Meal;
use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['status' => ['nullable', Rule::in(array_keys(Order::STATUSES))]]);
        $orders = Order::with('outlet')->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))->latest('id')->paginate(15)->withQueryString();
        $stats = [
            'orders' => Order::whereDate('pickup_at', today())->count(),
            'pending' => Order::whereIn('status', ['pending', 'ready'])->count(),
            'revenue' => Order::whereDate('pickup_at', today())->where('status', 'completed')->sum('total_price'),
            'stock' => Meal::published()->sum('stock'),
        ];

        return view('admin.index', compact('orders', 'stats'));
    }

    public function meals(): View
    {
        $meals = Meal::with('outlet')->orderByDesc('available_date')->orderBy('id')->paginate(15);

        return view('admin.meals', compact('meals'));
    }

    public function create(): View
    {
        return $this->form(new Meal(['available_date' => today(), 'pickup_start' => '11:00', 'pickup_end' => '19:00', 'is_active' => true, 'image' => 'nasi-ayam.jpg']));
    }

    public function edit(Meal $meal): View
    {
        return $this->form($meal);
    }

    private function form(Meal $meal): View
    {
        return view('admin.form', ['meal' => $meal, 'outlets' => Outlet::where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(SaveMealRequest $request): RedirectResponse
    {
        $data = $this->mealData($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        Meal::create($data);

        return redirect()->route('admin.meals')->with('success', 'Menu baru sudah ditambahkan.');
    }

    public function update(SaveMealRequest $request, Meal $meal): RedirectResponse
    {
        $meal->update($this->mealData($request));

        return redirect()->route('admin.meals')->with('success', 'Perubahan menu tersimpan.');
    }

    private function mealData(SaveMealRequest $request): array
    {
        $data = $request->safe()->except('components_text');
        $data['components'] = array_values(array_filter(array_map('trim', explode(',', $request->validated('components_text')))));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    public function status(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['ready', 'completed', 'cancelled'])]]);
        DB::transaction(function () use ($order, $data) {
            $fresh = Order::lockForUpdate()->findOrFail($order->id);
            $allowed = match ($fresh->status) {
                'pending' => ['ready', 'cancelled'], 'ready' => ['completed', 'cancelled'], default => []
            };
            if (! in_array($data['status'], $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status tidak valid. Muat ulang halaman.']);
            }
            $fresh->update(['status' => $data['status']]);
            if ($data['status'] === 'cancelled') {
                Meal::whereKey($fresh->meal_id)->increment('stock', $fresh->quantity);
            }
        }, 3);

        return back()->with('success','Status pesanan diperbarui.');
    }
}

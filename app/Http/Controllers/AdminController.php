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
    public function index(): View
    {
        $stats = [
            'orders' => Order::whereDate('pickup_at', today())->count(),
            'pending' => Order::whereDate('pickup_at', today())->whereIn('status', ['pending', 'ready'])->count(),
            'portions' => Order::where('status', 'completed')->whereDate('completed_at', today())->sum('quantity'),
            'stock' => Meal::published()->sum('stock'),
            'revenue' => Order::where('status', 'completed')->whereDate('completed_at', today())->sum('total_price'),
        ];
        $orders = Order::with('outlet')->latest('id')->limit(5)->get();
        $daily = Order::where('status', 'completed')->whereBetween('completed_at', [today()->subDays(6), now()])->selectRaw('DATE(completed_at) as day, SUM(quantity) as portions')->groupByRaw('DATE(completed_at)')->pluck('portions', 'day');
        $week = collect(range(6, 0))->map(fn (int $ago) => ['date' => today()->subDays($ago), 'portions' => (int) ($daily[today()->subDays($ago)->toDateString()] ?? 0)]);
        $outlets = Outlet::where('is_active', true)->withSum(['meals as available_stock' => fn ($q) => $q->published()], 'stock')->orderBy('name')->get();

        return view('admin.dashboard', compact('stats', 'orders', 'week', 'outlets'));
    }

    public function orders(Request $request): View
    {
        $data = $request->validate(['status' => ['nullable', Rule::in(array_keys(Order::STATUSES))], 'q' => ['nullable', 'string', 'max:80'], 'date' => ['nullable', 'date'], 'outlet' => ['nullable', 'integer', 'exists:outlets,id']]);
        $orders = Order::with('outlet')->when($request->filled('status'), fn ($q) => $q->where('status', $data['status']))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('pickup_at', $data['date']))
            ->when($request->filled('outlet'), fn ($q) => $q->where('outlet_id', $data['outlet']))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($nested) => $nested->where('code', 'like', '%'.$data['q'].'%')->orWhere('customer_name', 'like', '%'.$data['q'].'%')))
            ->latest('id')->paginate(15)->withQueryString();

        return view('admin.index', ['orders' => $orders, 'outlets' => Outlet::orderBy('name')->get()]);
    }

    public function meals(Request $request): View
    {
        $data = $request->validate(['date' => ['nullable', 'date'], 'outlet' => ['nullable', 'integer', 'exists:outlets,id']]);
        $date = $data['date'] ?? today()->toDateString();
        $meals = Meal::with('outlet')->whereDate('available_date', $date)->when($request->filled('outlet'), fn ($q) => $q->where('outlet_id', $data['outlet']))->orderBy('id')->paginate(15)->withQueryString();

        return view('admin.meals', ['meals' => $meals, 'date' => $date, 'outlets' => Outlet::orderBy('name')->get()]);
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
        $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(8));
        Meal::create($data);

        return redirect()->route('admin.meals', ['date' => $data['available_date']])->with('success', 'Menu baru sudah ditambahkan.');
    }

    public function update(SaveMealRequest $request, Meal $meal): RedirectResponse
    {
        $data = $this->mealData($request);
        if ($meal->available_date->toDateString() !== $data['available_date'] && Order::where('meal_id', $meal->id)->exists()) {
            throw ValidationException::withMessages(['available_date' => 'Menu ini sudah memiliki pesanan. Gunakan Salin menu untuk tanggal lain.']);
        }
        $meal->update($data);

        return redirect()->route('admin.meals', ['date' => $data['available_date']])->with('success', 'Perubahan menu tersimpan.');
    }

    private function mealData(SaveMealRequest $request): array
    {
        $data = $request->safe()->except('components_text');
        $data['components'] = array_values(array_filter(array_map('trim', explode(',', $request->validated('components_text')))));
        $data['is_active'] = $request->boolean('is_active');
        $outlet = Outlet::findOrFail($data['outlet_id']);
        if ($data['pickup_start'] < $outlet->opens_at || $data['pickup_end'] > $outlet->closes_at) {
            throw ValidationException::withMessages(['pickup_start' => 'Waktu ambil harus berada dalam jam operasional outlet.']);
        }

        return $data;
    }

    public function status(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['ready', 'completed', 'cancelled', 'missed'])]]);
        DB::transaction(function () use ($order, $data): void {
            $fresh = Order::lockForUpdate()->findOrFail($order->id);
            $allowed = match ($fresh->status) {
                'pending' => ['ready', 'cancelled', 'missed'],'ready' => ['completed', 'cancelled', 'missed'],default => []
            };
            if (! in_array($data['status'], $allowed, true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status tidak valid. Muat ulang halaman.']);
            }
            $deadline = $fresh->pickup_deadline_at ?? $fresh->pickup_at->copy()->addMinutes(30);
            if ($data['status'] === 'missed' && now()->lte($deadline)) {
                throw ValidationException::withMessages(['status' => 'Tunggu sampai batas pengambilan lewat sebelum menandai Tidak diambil.']);
            }
            $fresh->update(['status' => $data['status'], 'completed_at' => $data['status'] === 'completed' ? now() : null, 'missed_at' => $data['status'] === 'missed' ? now() : null]);
            if ($data['status'] === 'cancelled') {
                Meal::whereKey($fresh->meal_id)->whereDate('available_date',$fresh->pickup_at->toDateString())->increment('stock',$fresh->quantity);
            }
        }, 3);

        return back()->with('success','Status pesanan diperbarui.');
    }
}

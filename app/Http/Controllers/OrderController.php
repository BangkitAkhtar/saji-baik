<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function create(Meal $meal): View
    {
        $meal->load('outlet');
        abort_unless($meal->is_active, 404);

        return view('orders.create', ['meal' => $meal, 'slots' => $meal->pickupSlots()]);
    }

    public function store(StoreOrderRequest $request, Meal $meal): RedirectResponse
    {
        $data = $request->validated();
        $order = DB::transaction(function () use ($request, $meal, $data) {
            User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $fresh = Meal::with('outlet')->lockForUpdate()->findOrFail($meal->id);
            if (! $fresh->canOrder() || ! in_array($data['pickup_time'], $fresh->pickupSlots(), true)) {
                throw ValidationException::withMessages(['pickup_time' => 'Menu atau waktu ambil ini sudah tidak tersedia. Pilih waktu lain.']);
            }
            $reserved = Meal::whereKey($fresh->id)->where('stock', '>=', $data['quantity'])->decrement('stock', $data['quantity']);
            if (! $reserved) {
                throw ValidationException::withMessages(['quantity' => 'Stok tidak mencukupi. Silakan kurangi jumlah porsi.']);
            }

            return Order::create([
                'user_id' => $request->user()->id, 'meal_id' => $fresh->id, 'outlet_id' => $fresh->outlet_id,
                'code' => 'SB-'.Str::upper(Str::random(8)), 'meal_name' => $fresh->name,
                'customer_name' => $data['customer_name'], 'phone' => $data['phone'], 'quantity' => $data['quantity'],
                'unit_price' => $fresh->price, 'total_price' => $fresh->price * $data['quantity'],
                'pickup_at' => Carbon::parse($fresh->available_date->format('Y-m-d').' '.$data['pickup_time']),
                'notes' => $data['notes'] ?? null, 'status' => 'pending',
            ]);
        }, 3);

        return redirect()->route('orders.show', $order->code)->with('success', 'Pesanan berhasil! Simpan kode ambilmu, ya.');
    }

    public function index(Request $request): View
    {
        $orders = Order::whereBelongsTo($request->user())->with('outlet', 'meal')->latest('id')->paginate(8);

        return view('orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        $order->load('meal', 'outlet');

        return view('orders.show', compact('order'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()->id, 404);
        DB::transaction(function () use ($order) {
            $fresh = Order::lockForUpdate()->findOrFail($order->id);
            if ($fresh->status !== 'pending' || $fresh->pickup_at->lte(now())) {
                throw ValidationException::withMessages(['order' => 'Pesanan ini tidak dapat dibatalkan. Hubungi petugas outlet.']);
            }
            $fresh->update(['status' => 'cancelled']);
            Meal::whereKey($fresh->meal_id)->increment('stock', $fresh->quantity);
        }, 3);

        return back()->with('success','Pesanan dibatalkan. Porsinya kembali tersedia untuk orang lain.');
    }
}

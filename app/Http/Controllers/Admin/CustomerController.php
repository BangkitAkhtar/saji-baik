<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveCustomerRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'role' => ['nullable', Rule::in(['admin', 'customer'])], 'status' => ['nullable', Rule::in(['all', 'active', 'inactive', 'archived'])]]);
        $status = $data['status'] ?? 'all';
        $query = User::query()->withCount('orders');
        if ($status === 'archived') {
            $query->onlyTrashed();
        } elseif ($status === 'all') {
            $query->withTrashed();
        } else {
            $query->where('is_active', $status === 'active');
        }
        if ($request->filled('role')) {
            $query->where('is_admin', $data['role'] === 'admin');
        }
        if ($request->filled('q')) {
            $query->where(fn ($nested) => $nested->where('name', 'like', '%'.$data['q'].'%')->orWhere('email', 'like', '%'.$data['q'].'%')->orWhere('phone', 'like', '%'.$data['q'].'%'));
        }
        $customers = $query->latest('id')->paginate(12)->withQueryString();
        $stats = ['accounts' => User::count(), 'customers' => User::where('is_admin', false)->count(), 'admins' => User::where('is_admin', true)->where('is_active', true)->count(), 'archived' => User::onlyTrashed()->count()];

        return view('admin.customers.index', compact('customers', 'stats'));
    }

    public function create(): View
    {
        return view('admin.customers.form', ['customer' => new User(['is_active' => true])]);
    }

    public function store(SaveCustomerRequest $request): RedirectResponse
    {
        $customer = new User($request->safe()->only(['name', 'email', 'phone', 'password']));
        $customer->is_admin = $request->validated('role') === 'admin';
        $customer->is_active = $request->validated('account_status') === 'active';
        $customer->save();

        return redirect()->route('admin.customers.index')->with('success', 'Akun '.$customer->name.' berhasil ditambahkan.');
    }

    public function edit(User $customer): View
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(SaveCustomerRequest $request, User $customer): RedirectResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($request, $customer, $data): void {
            $fresh = User::lockForUpdate()->findOrFail($customer->id);
            if ($fresh->id === $request->user()->id && ($data['role'] !== 'admin' || $data['account_status'] !== 'active')) {
                throw ValidationException::withMessages(['role' => 'Akun admin yang sedang digunakan harus tetap aktif dengan peran Admin.']);
            }
            $fresh->fill($request->safe()->only(['name', 'email', 'phone']));
            $fresh->is_admin = $data['role'] === 'admin';
            $fresh->is_active = $data['account_status'] === 'active';
            $credentialsChanged = ! empty($data['password']);
            if ($credentialsChanged) {
                $fresh->password = $data['password'];
            }
            if ($credentialsChanged || ! $fresh->is_active) {
                $fresh->remember_token = Str::random(60);
            }
            $fresh->save();
            if ($credentialsChanged || ! $fresh->is_active) {
                DB::table('sessions')->where('user_id', $fresh->id)->when($fresh->id === $request->user()->id, fn ($query) => $query->where('id', '!=', $request->session()->getId()))->delete();
            }
        }, 3);

        return redirect()->route('admin.customers.index')->with('success', 'Data akun '.$customer->name.' sudah diperbarui.');
    }

    public function destroy(Request $request, User $customer): RedirectResponse
    {
        if ($customer->id === $request->user()->id) {
            throw ValidationException::withMessages(['account' => 'Akun admin yang sedang digunakan tidak dapat dihapus.']);
        }
        DB::transaction(function () use ($customer): void {
            $fresh = User::lockForUpdate()->findOrFail($customer->id);
            $fresh->is_active = false;
            $fresh->remember_token = Str::random(60);
            $fresh->save();
            $fresh->delete();
            DB::table('sessions')->where('user_id', $fresh->id)->delete();
        }, 3);

        return back()->with('success', 'Akun diarsipkan. Riwayat pesanan tetap tersimpan dan akun bisa dipulihkan.');
    }

    public function restore(User $customer): RedirectResponse
    {
        abort_unless($customer->trashed(), 404);
        $customer->restore();
        $customer->is_active = true;
        $customer->save();

        return back()->with('success','Akun '.$customer->name.' sudah dipulihkan.');
    }
}

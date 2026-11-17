<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminOperationsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_is_sent_to_dedicated_admin_login(): void
    {
        $this->get(route('admin.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.login'))->assertOk()->assertSee('Masuk ke dashboard');
    }

    public function test_customer_cannot_authenticate_as_admin(): void
    {
        $user = User::factory()->create(['password' => 'Password!123']);
        $this->post(route('admin.login.store'), ['email' => $user->email, 'password' => 'Password!123'])->assertSessionHasErrors(['email' => 'Email atau kata sandi admin belum sesuai.']);
        $this->assertGuest();
    }

    public function test_admin_login_opens_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'Password!123']);
        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'Password!123'])->assertRedirect(route('admin.index'));
        $this->assertAuthenticatedAs($admin);
        $this->get(route('admin.index'))->assertOk()->assertSee('Porsi sudah diambil');
    }

    public function test_new_admin_pages_are_protected_from_customers(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['admin.orders', 'admin.outlets.index', 'admin.reports', 'admin.reports.export', 'admin.copy.create'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
        $this->post(route('admin.copy.store'), [])->assertForbidden();
        $this->post(route('admin.outlets.store'), [])->assertForbidden();
    }

    public function test_admin_pages_render_with_no_demo_orders(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        foreach (['admin.orders', 'admin.outlets.index', 'admin.outlets.create', 'admin.reports', 'admin.copy.create'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_copy_creates_new_daily_stock_without_touching_original_orders(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $meal = Meal::factory()->create(['stock' => 5]);
        $order = Order::factory()->for($meal)->create(['outlet_id' => $meal->outlet_id]);
        $admin = User::factory()->create(['is_admin' => true]);
        $target = today()->addDay()->toDateString();
        $this->actingAs($admin)->post(route('admin.copy.store'), ['meal_ids' => [$meal->id], 'target_date' => $target, 'stock' => 20])->assertRedirect(route('admin.meals', ['date' => $target]));
        $this->assertDatabaseHas('meals', ['source_meal_id' => $meal->id, 'stock' => 20, 'available_date' => $target.' 00:00:00', 'price' => 12000]);
        $this->assertDatabaseHas('meals', ['id' => $meal->id, 'stock' => 5]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'meal_id' => $meal->id, 'status' => 'pending']);
        $this->post(route('admin.copy.store'), ['meal_ids' => [$meal->id], 'target_date' => $target, 'stock' => 99])->assertRedirect();
        $this->assertDatabaseCount('meals', 2);
        $this->assertDatabaseHas('meals', ['source_meal_id' => $meal->id, 'stock' => 20]);
    }

    public function test_copy_requires_selection_and_different_target_date(): void
    {
        $meal = Meal::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('admin.copy.store'), ['target_date' => today()->toDateString(), 'stock' => 20])->assertSessionHasErrors('meal_ids');
        $this->post(route('admin.copy.store'), ['meal_ids' => [$meal->id], 'target_date' => today()->toDateString(), 'stock' => 20])->assertSessionHasErrors('target_date');
        $this->assertDatabaseCount('meals', 1);
    }

    public function test_completed_order_records_actual_completion_time(): void
    {
        $this->travelTo(today()->setTime(14, 0));
        $order = Order::factory()->create(['status' => 'ready']);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('admin.status', $order), ['status' => 'completed'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed', 'completed_at' => today()->setTime(14, 0)->format('Y-m-d H:i:s')]);
    }

    public function test_missed_is_allowed_only_after_deadline_and_does_not_restore_stock(): void
    {
        $this->travelTo(today()->setTime(13, 15));
        $order = Order::factory()->create(['status' => 'ready', 'pickup_deadline_at' => today()->setTime(13, 30)]);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('admin.status', $order), ['status' => 'missed'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'ready']);
        $this->travelTo(today()->setTime(13, 31));
        $this->post(route('admin.status', $order), ['status' => 'missed'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'missed', 'completed_at' => null]);
        $this->assertDatabaseHas('meals', ['id' => $order->meal_id, 'stock' => 10]);
    }

    public function test_report_counts_only_completed_orders_in_completion_period(): void
    {
        $this->travelTo('2026-11-17 14:00:00');
        $meal = Meal::factory()->create();
        $user = User::factory()->create();
        Order::factory()->for($meal)->for($user)->create(['outlet_id' => $meal->outlet_id, 'quantity' => 2, 'total_price' => 24000, 'status' => 'completed', 'completed_at' => now()]);
        Order::factory()->for($meal)->for($user)->create(['outlet_id' => $meal->outlet_id, 'quantity' => 1, 'total_price' => 12000, 'status' => 'completed', 'completed_at' => now()]);
        Order::factory()->for($meal)->create(['outlet_id' => $meal->outlet_id, 'quantity' => 2, 'status' => 'cancelled']);
        Order::factory()->for($meal)->create(['outlet_id' => $meal->outlet_id, 'quantity' => 2, 'status' => 'missed', 'missed_at' => now()]);
        Order::factory()->for($meal)->create(['outlet_id' => $meal->outlet_id, 'quantity' => 2, 'status' => 'completed', 'completed_at' => now()->subMonth()]);
        $response = $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('admin.reports', ['from' => '2026-11-17', 'to' => '2026-11-17']));
        $response->assertOk()->assertViewHas('metrics', fn (array $metrics) => (int) $metrics['portions'] === 3 && $metrics['orders'] === 2 && $metrics['accounts'] === 1 && (int) $metrics['revenue'] === 36000)->assertViewHas('missed', fn ($missed) => (int) $missed === 2);
        $this->get(route('admin.reports.export', ['from' => '2026-11-17', 'to' => '2026-11-17']))->assertOk()->assertDownload('laporan-sdg2-2026-11-17-2026-11-17.csv')->assertStreamedContent("\xEF\xBB\xBF\"Tanggal selesai\",\"Pesanan selesai\",\"Porsi diambil\",\"Penerimaan (Rp)\"\n2026-11-17,2,3,36000\n");
    }

    public function test_report_rejects_reversed_dates(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('admin.reports', ['from' => '2026-11-17', 'to' => '2026-11-01']))->assertRedirect()->assertSessionHasErrors('to');
    }

    public function test_admin_can_update_outlet_and_public_page_reflects_changes(): void
    {
        $outlet = Outlet::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->put(route('admin.outlets.update', $outlet), [
            'name' => 'Dapur Kampus Baru', 'area' => 'Kemanggisan', 'address' => 'Alamat outlet contoh', 'description' => 'Tunjukkan kode ke kasir.', 'contact_note' => 'Kontak petugas (demo).', 'opens_at' => '10:00', 'closes_at' => '20:00', 'pickup_grace_minutes' => 20, 'image' => 'outlet-illustration.png', 'is_active' => 1,
        ])->assertRedirect(route('admin.outlets.index'));
        $this->assertDatabaseHas('outlets', ['id' => $outlet->id, 'name' => 'Dapur Kampus Baru', 'pickup_grace_minutes' => 20]);
        $this->get(route('outlets'))->assertOk()->assertSee('Dapur Kampus Baru')->assertSee('Toleransi ambil 20 menit');
    }

    public function test_new_orders_receive_deadline_capped_to_outlet_close(): void
    {
        $this->travelTo(today()->setTime(17, 0));
        $outlet = Outlet::factory()->create(['opens_at' => '10:00', 'closes_at' => '19:40', 'pickup_grace_minutes' => 30]);
        $meal = Meal::factory()->for($outlet)->create(['pickup_end' => '19:40']);
        $this->actingAs(User::factory()->create())->post(route('orders.store', $meal->slug), [
            'quantity' => 1, 'customer_name' => 'Teman', 'phone' => '081234567890', 'pickup_time' => '19:30', 'needs_support' => 1, 'pickup_agreement' => 1,
        ])->assertRedirect();
        $this->assertDatabaseHas('orders',['pickup_deadline_at' => today()->setTime(19,40)->format('Y-m-d H:i:s')]);
    }
}

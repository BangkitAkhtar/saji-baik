<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class OrderingTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function payload(array $changes = []): array
    {
        return array_replace(['quantity' => 2, 'customer_name' => 'Teman Uji', 'phone' => '081234567890', 'pickup_time' => '13:00', 'needs_support' => '1', 'pickup_agreement' => '1'], $changes);
    }

    public function test_order_uses_server_price_and_reserves_stock(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $user = User::factory()->create();
        $meal = Meal::factory()->create(['stock' => 3]);
        $response = $this->actingAs($user)->post(route('orders.store', $meal->slug), $this->payload(['total_price' => 1, 'status' => 'completed', 'user_id' => 999]));
        $order = Order::firstOrFail();
        $response->assertRedirect(route('orders.show', $order->code));
        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'quantity' => 2, 'total_price' => 24000, 'status' => 'pending']);
        $this->assertDatabaseHas('meals', ['id' => $meal->id, 'stock' => 1]);
    }

    public function test_sold_out_order_does_not_create_records(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $meal = Meal::factory()->create(['stock' => 1]);
        $this->actingAs(User::factory()->create())->post(route('orders.store', $meal->slug), $this->payload())->assertSessionHasErrors(['quantity' => 'Stok tidak mencukupi. Silakan kurangi jumlah porsi.']);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('meals', ['id' => $meal->id, 'stock' => 1]);
    }

    public function test_support_declaration_is_required(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $meal = Meal::factory()->create();
        $this->actingAs(User::factory()->create())->post(route('orders.store', $meal->slug), $this->payload(['needs_support' => 0]))->assertSessionHasErrors(['needs_support' => 'Mohon setujui pernyataan kebutuhan terlebih dahulu.']);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('meals', ['id' => $meal->id, 'stock' => 10]);
    }

    public function test_past_or_unlisted_pickup_slot_is_rejected(): void
    {
        $this->travelTo(today()->setTime(14, 0));
        $meal = Meal::factory()->create();
        $this->actingAs(User::factory()->create())->post(route('orders.store', $meal->slug), $this->payload())->assertSessionHasErrors('pickup_time');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_yesterdays_menu_cannot_be_ordered(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $meal = Meal::factory()->create(['available_date' => today()->subDay()]);
        $this->actingAs(User::factory()->create())->post(route('orders.store', $meal->slug), $this->payload())->assertSessionHasErrors('pickup_time');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_only_two_portions_can_be_ordered(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $meal = Meal::factory()->create();
        $this->actingAs(User::factory()->create())->post(route('orders.store', $meal->slug), $this->payload(['quantity' => 3]))->assertSessionHasErrors(['quantity' => 'Maksimal 2 porsi per pesanan agar lebih banyak orang kebagian.']);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cancellation_restores_stock_once(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $order = Order::factory()->create();
        $this->actingAs($order->user)->post(route('orders.cancel', $order->code))->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('meals', ['id' => $order->meal_id, 'stock' => 11]);
        $this->post(route('orders.cancel', $order->code))->assertSessionHasErrors('order');
        $this->assertDatabaseHas('meals', ['id' => $order->meal_id, 'stock' => 11]);
    }

    public function test_customer_cannot_view_or_cancel_another_order(): void
    {
        $order = Order::factory()->create();
        $this->actingAs(User::factory()->create())->get(route('orders.show', $order->code))->assertNotFound();
        $this->post(route('orders.cancel', $order->code))->assertNotFound();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->assertDatabaseHas('meals', ['id' => $order->meal_id, 'stock' => 10]);
    }

    public function test_ready_order_cannot_be_cancelled_by_customer(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        $order = Order::factory()->create(['status' => 'ready']);
        $this->actingAs($order->user)->post(route('orders.cancel', $order->code))->assertSessionHasErrors('order');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'ready']);
        $this->assertDatabaseHas('meals', ['id' => $order->meal_id, 'stock' => 10]);
    }

    public function test_admin_must_follow_status_transitions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = Order::factory()->create();
        $this->actingAs($admin)->post(route('admin.status', $order), ['status' => 'completed'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'pending']);
        $this->post(route('admin.status', $order), ['status' => 'ready'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'ready']);
        $this->post(route('admin.status', $order), ['status' => 'completed'])->assertRedirect();
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->post(route('admin.status',$order),['status' => 'cancelled'])->assertSessionHasErrors('status');
        $this->assertDatabaseHas('meals',['id' => $order->meal_id, 'stock' => 10]);
    }
}

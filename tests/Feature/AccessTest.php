<?php

namespace Tests\Feature;

use App\Models\Meal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_public_pages_render_and_filter_real_menu(): void
    {
        $this->travelTo(today()->setTime(10, 0));
        Meal::factory()->create(['name' => 'Nasi Telur Hemat', 'price' => 8000]);
        Meal::factory()->create(['name' => 'Ayam Spesial', 'price' => 15000]);
        $this->get('/')->assertOk()->assertSee('Saji Baik');
        $this->get('/menu?max_price=10000')->assertOk()->assertSee('Nasi Telur Hemat')->assertDontSee('Ayam Spesial');
        $this->get('/outlet')->assertOk();
        $this->get('/tentang')->assertOk();
        $this->get('/masuk')->assertOk();
        $this->get('/daftar')->assertOk();
        $this->get('/menu?sort=price%20desc;DROP')->assertRedirect()->assertSessionHasErrors('sort');
    }

    public function test_ordering_requires_login_and_admin_requires_admin_role(): void
    {
        $meal = Meal::factory()->create();
        $this->get(route('orders.create', $meal->slug))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.index'))->assertForbidden();
        $this->get(route('admin.meals'))->assertForbidden();
        $this->post(route('admin.store'), [])->assertForbidden();
    }

    public function test_registration_cannot_grant_admin_access(): void
    {
        $this->post(route('register.store'), ['name' => 'Teman Baru', 'email' => 'baru@example.test', 'phone' => '081234567890', 'password' => 'KataSandi!123', 'password_confirmation' => 'KataSandi!123', 'is_admin' => 1])->assertRedirect(route('menu.index'));
        $this->assertDatabaseHas('users', ['email' => 'baru@example.test', 'is_admin' => false]);
        $this->assertAuthenticated();
        $this->get(route('admin.index'))->assertForbidden();
    }

    public function test_login_and_logout_work(): void
    {
        $user = User::factory()->create(['password' => 'KataSandi!123']);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'salah'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'KataSandi!123'])->assertRedirect(route('menu.index'));
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_admin_pages_render_and_menu_can_be_created(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $meal = Meal::factory()->create();
        $this->actingAs($admin)->get(route('admin.index'))->assertOk();
        $this->get(route('admin.meals'))->assertOk();
        $this->get(route('admin.create'))->assertOk();
        $this->get(route('admin.edit', $meal))->assertOk();
        $this->post(route('admin.store'), ['name' => 'Nasi Tempe Baru', 'outlet_id' => $meal->outlet_id, 'description' => 'Pilihan nabati', 'category' => 'Tanpa daging', 'price' => 9000, 'stock' => 25, 'available_date' => today()->format('Y-m-d'), 'pickup_start' => '11:00', 'pickup_end' => '19:00', 'components_text' => 'Nasi, Tempe, Sayur', 'allergens' => 'Kedelai', 'calories' => 450, 'protein' => 18, 'image' => 'nasi-lengkap.jpg', 'is_active' => 1])->assertRedirect(route('admin.meals'));
        $this->assertDatabaseHas('meals', ['name' => 'Nasi Tempe Baru', 'price' => 9000, 'stock' => 25, 'is_active' => true]);
    }

    public function test_order_free_text_is_escaped(): void
    {
        $order = Order::factory()->create(['customer_name' => '<script>alert(1)</script>', 'notes' => '<img src=x onerror=alert(1)>']);
        $this->actingAs($order->user)->get(route('orders.show', $order->code))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false)->assertSee('&lt;img', false)->assertDontSee('<img src=x',false);
    }
}

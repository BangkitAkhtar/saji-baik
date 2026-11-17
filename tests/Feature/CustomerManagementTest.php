<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function payload(array $changes = []): array
    {
        return array_replace(['name' => 'Pelanggan Baru', 'email' => 'baru@example.test', 'phone' => '081234567890', 'role' => 'customer', 'account_status' => 'active', 'password' => 'Password!123', 'password_confirmation' => 'Password!123'], $changes);
    }

    public function test_customer_pages_are_admin_only(): void
    {
        $this->get(route('admin.customers.index'))->assertRedirect(route('admin.login'));
        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('admin.customers.index'))->assertForbidden();
        $this->post(route('admin.customers.store'), $this->payload())->assertForbidden();
        $this->put(route('admin.customers.update', $customer), $this->payload())->assertForbidden();
        $this->delete(route('admin.customers.destroy', $customer))->assertForbidden();
    }

    public function test_admin_can_view_account_roles_and_filter_customers(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'name' => 'Pengelola Utama']);
        User::factory()->create(['name' => 'Teman Pelanggan']);
        $this->actingAs($admin)->get(route('admin.customers.index'))->assertOk()->assertSee('Pengelola Utama')->assertSee('Teman Pelanggan')->assertSee('Admin')->assertSee('Pelanggan');
        $this->get(route('admin.customers.index', ['role' => 'customer', 'q' => 'Teman']))->assertOk()->assertSee('Teman Pelanggan')->assertViewHas('customers', fn ($customers) => $customers->total() === 1 && $customers->first()->name === 'Teman Pelanggan');
        $this->get(route('admin.customers.create'))->assertOk();
        $this->get(route('admin.customers.edit', $admin))->assertOk();
    }

    public function test_admin_can_create_account_with_admin_role_and_hashed_password(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('admin.customers.store'), $this->payload(['role' => 'admin']))->assertRedirect(route('admin.customers.index'));
        $account = User::where('email', 'baru@example.test')->firstOrFail();
        $this->assertDatabaseHas('users', ['id' => $account->id, 'is_admin' => true, 'is_active' => true]);
        $this->assertTrue(Hash::check('Password!123', $account->password));
        $this->assertArrayNotHasKey('password', $account->toArray());
    }

    public function test_admin_can_edit_role_and_keep_existing_password_when_blank(): void
    {
        $customer = User::factory()->create();
        $hash = $customer->password;
        $this->actingAs(User::factory()->create(['is_admin' => true]))->put(route('admin.customers.update', $customer), $this->payload(['email' => $customer->email, 'name' => 'Nama Diperbarui', 'role' => 'admin', 'password' => null, 'password_confirmation' => null]))->assertRedirect(route('admin.customers.index'));
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Nama Diperbarui', 'is_admin' => true, 'password' => $hash]);
    }

    public function test_regular_registration_always_creates_active_customer_and_is_listed(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->post(route('register.store'), $this->payload(['role' => 'admin', 'is_admin' => true, 'is_active' => false]))->assertRedirect(route('menu.index'));
        $this->assertDatabaseHas('users', ['email' => 'baru@example.test', 'is_admin' => false, 'is_active' => true]);
        $this->actingAs($admin)->get(route('admin.customers.index'))->assertOk()->assertSee('baru@example.test');
    }

    public function test_admin_cannot_demote_disable_or_delete_own_current_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->put(route('admin.customers.update', $admin), $this->payload(['email' => $admin->email, 'role' => 'customer']))->assertSessionHasErrors('role');
        $this->put(route('admin.customers.update', $admin), $this->payload(['email' => $admin->email, 'role' => 'admin', 'account_status' => 'inactive']))->assertSessionHasErrors('role');
        $this->delete(route('admin.customers.destroy', $admin))->assertSessionHasErrors('account');
        $this->assertDatabaseHas('users', ['id' => $admin->id, 'is_admin' => true, 'is_active' => true, 'deleted_at' => null]);
    }

    public function test_archiving_preserves_orders_and_restoring_reactivates_account(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer, 'user')->create();
        $this->actingAs($admin)->delete(route('admin.customers.destroy', $customer))->assertRedirect()->assertSessionHas('success');
        $this->assertSoftDeleted($customer);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $customer->id]);
        $this->assertSame($customer->id, $order->fresh()->user->id);
        $this->get(route('admin.customers.index', ['status' => 'archived']))->assertOk()->assertSee($customer->email)->assertSee('Pulihkan');
        $this->post(route('admin.customers.restore', $customer))->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['id' => $customer->id, 'is_active' => true, 'deleted_at' => null]);
    }

    public function test_inactive_and_archived_accounts_cannot_sign_in(): void
    {
        $inactive = User::factory()->create(['is_active' => false, 'password' => 'Password!123']);
        $archived = User::factory()->create(['password' => 'Password!123']);
        $archived->delete();
        $this->post(route('login.store'), ['email' => $inactive->email, 'password' => 'Password!123'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post(route('login.store'), ['email' => $archived->email, 'password' => 'Password!123'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_existing_session_is_removed_when_account_becomes_inactive(): void
    {
        $customer = User::factory()->create(['is_active' => false]);
        $this->actingAs($customer)->get(route('orders.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_admin_password_change_is_hashed_and_remember_token_rotated(): void
    {
        $customer = User::factory()->create(['remember_token' => 'old-token']);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->put(route('admin.customers.update', $customer), $this->payload(['email' => $customer->email, 'password' => 'NewPassword!456', 'password_confirmation' => 'NewPassword!456']))->assertRedirect();
        $customer->refresh();
        $this->assertTrue(Hash::check('NewPassword!456', $customer->password));
        $this->assertNotSame('old-token', $customer->remember_token);
    }

    public function test_duplicate_email_invalid_role_and_unconfirmed_password_are_rejected(): void
    {
        $existing = User::factory()->create();
        $this->actingAs(User::factory()->create(['is_admin' => true]))->post(route('admin.customers.store'), $this->payload(['email' => $existing->email, 'role' => 'owner', 'password_confirmation' => 'different']))->assertSessionHasErrors(['email' => 'Email sudah digunakan, termasuk oleh akun yang diarsipkan.', 'role' => 'Pilih peran Admin atau Pelanggan.', 'password' => 'Konfirmasi kata sandi belum sama.']);
        $this->assertDatabaseCount('users', 2);
    }

    public function test_customer_names_are_escaped_in_admin_list(): void
    {
        User::factory()->create(['name' => '<script>alert(1)</script>']);
        $this->actingAs(User::factory()->create(['is_admin' => true]))->get(route('admin.customers.index'))->assertOk()->assertSee('&lt;script&gt;',false)->assertDontSee('<script>alert(1)</script>',false);
    }
}

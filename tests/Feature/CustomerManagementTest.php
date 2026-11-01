<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $subadmin;

    protected Customer $regularCustomer;

    protected Customer $shopCustomer;

    protected Category $category;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'full_name' => 'بەڕێوەبەر',
            'email' => 'admin@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->subadmin = User::create([
            'full_name' => 'یاریدەدەر',
            'email' => 'subadmin@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'subadmin',
            'is_active' => true,
        ]);

        $this->regularCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'عەلی حەمە',
            'phone' => '07501112244',
            'email' => 'ali@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'هەولێر',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->shopCustomer = Customer::create([
            'type' => 'shop',
            'full_name' => 'سەردار قادر',
            'shop_name' => 'کۆمپیوتەر سێنتەر',
            'phone' => '07705556677',
            'email' => 'sardar@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'سلێمانی',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->product = Product::create([
            'customer_id' => $this->shopCustomer->id,
            'category_id' => $this->category->id,
            'title' => 'MacBook Pro M3 Max',
            'description' => 'وەسف',
            'price' => 2500000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);
    }

    public function test_ban_hides_seller_products_from_public_and_logs_them_out_on_next_request(): void
    {
        // 1. Initially product is visible
        $this->get(route('products.show', $this->product->id))->assertStatus(200);

        // 2. Admin bans customer
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.customers.ban', $this->shopCustomer->id))
            ->assertSessionHas('success');

        $this->assertTrue($this->shopCustomer->fresh()->is_banned);

        // 3. Product is now hidden from public pages (returns 404)
        $this->get(route('products.show', $this->product->id))->assertStatus(404);

        // Browse page does not show it
        $this->get(route('products.index'))->assertDontSee('MacBook Pro M3 Max');

        // Shop page returns 404
        $this->get(route('shops.show', $this->shopCustomer->id))->assertStatus(404);

        // 4. Banned customer is logged out on next request by EnsureCustomerNotBanned middleware
        $response = $this->actingAs($this->shopCustomer->fresh(), 'customer')
            ->get(route('customer.products.index'));

        $response->assertRedirect(route('login'));
        $this->assertGuest('customer');
    }

    public function test_unban_restores_products_to_public_pages(): void
    {
        // Customer starts banned
        $this->shopCustomer->update(['is_banned' => true]);
        $this->get(route('products.show', $this->product->id))->assertStatus(404);

        // Admin unbans
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.customers.unban', $this->shopCustomer->id))
            ->assertSessionHas('success');

        $this->assertFalse($this->shopCustomer->fresh()->is_banned);

        // Product is visible again
        $this->get(route('products.show', $this->product->id))->assertStatus(200);
        $this->get(route('products.index'))->assertSee('MacBook Pro M3 Max');
    }

    public function test_verify_works_for_shops_and_fails_for_regular_customers(): void
    {
        // 1. Verify shop customer -> succeeds
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.customers.verify', $this->shopCustomer->id))
            ->assertSessionHas('success');

        $this->assertTrue($this->shopCustomer->fresh()->is_verified);

        // 2. Unverify shop customer -> succeeds
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.customers.unverify', $this->shopCustomer->id))
            ->assertSessionHas('success');

        $this->assertFalse($this->shopCustomer->fresh()->is_verified);

        // 3. Verify regular customer -> fails
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.customers.verify', $this->regularCustomer->id))
            ->assertSessionHas('error');

        $this->assertFalse($this->regularCustomer->fresh()->is_verified);
    }

    public function test_subadmin_gets_403_on_customer_management_routes(): void
    {
        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.customers.index'))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.customers.show', $this->shopCustomer->id))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->post(route('staff.customers.ban', $this->shopCustomer->id))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->post(route('staff.customers.unban', $this->shopCustomer->id))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->post(route('staff.customers.verify', $this->shopCustomer->id))
            ->assertStatus(403);
    }
}

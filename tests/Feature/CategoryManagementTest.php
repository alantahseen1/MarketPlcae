<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $subadmin;

    protected Customer $customer;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'بەهمەن عەلی',
            'phone' => '07501112255',
            'email' => 'bahman@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'هەولێر',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->category = Category::create([
            'name' => 'ئامێری زیرەک',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_create_and_update_category(): void
    {
        // 1. Create category
        $createRes = $this->actingAs($this->admin, 'staff')
            ->post(route('staff.categories.store'), [
                'name' => 'کامێرا و وێنەگری',
                'is_active' => '1',
            ]);

        $createRes->assertRedirect(route('staff.categories.index'));
        $createRes->assertSessionHas('success');

        $this->assertDatabaseHas('category', [
            'name' => 'کامێرا و وێنەگری',
            'is_active' => true,
        ]);

        $newCat = Category::where('name', 'کامێرا و وێنەگری')->first();

        // 2. Update category
        $updateRes = $this->actingAs($this->admin, 'staff')
            ->put(route('staff.categories.update', $newCat->id), [
                'name' => 'کامێرای دیجیتاڵ',
                'is_active' => '1',
            ]);

        $updateRes->assertRedirect(route('staff.categories.index'));
        $this->assertDatabaseHas('category', [
            'id' => $newCat->id,
            'name' => 'کامێرای دیجیتاڵ',
        ]);
    }

    public function test_category_name_is_required_and_unique(): void
    {
        // Duplicate name fails
        $dupRes = $this->actingAs($this->admin, 'staff')
            ->post(route('staff.categories.store'), [
                'name' => 'ئامێری زیرەک', // already exists
                'is_active' => '1',
            ]);

        $dupRes->assertSessionHasErrors('name');

        // Empty name fails
        $emptyRes = $this->actingAs($this->admin, 'staff')
            ->post(route('staff.categories.store'), [
                'name' => '',
            ]);

        $emptyRes->assertSessionHasErrors('name');
    }

    public function test_deactivating_category_hides_it_from_public_and_product_form(): void
    {
        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'Smart Watch زیرەک',
            'description' => 'وەسف',
            'price' => 300000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);

        // Initially visible on public home, browse, and product detail
        $this->get(route('home'))->assertSee('ئامێری زیرەک');
        $this->get(route('products.index'))->assertSee('Smart Watch زیرەک');
        $this->get(route('products.show', $product->id))->assertStatus(200);

        // Visible in customer product create form
        $this->actingAs($this->customer, 'customer')
            ->get(route('customer.products.create'))
            ->assertSee('ئامێری زیرەک');

        // Admin deactivates category
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.categories.toggle', $this->category->id))
            ->assertSessionHas('success');

        $this->assertFalse($this->category->fresh()->is_active);

        // Now category is hidden from public home
        $this->get(route('home'))->assertDontSee('ئامێری زیرەک');

        // Products of deactivated category are hidden from public browse and return 404 on show
        $this->get(route('products.index'))->assertDontSee('Smart Watch زیرەک');
        $this->get(route('products.show', $product->id))->assertStatus(404);

        // Deactivated category is hidden from customer product create form
        $this->actingAs($this->customer, 'customer')
            ->get(route('customer.products.create'))
            ->assertDontSee('ئامێری زیرەک');

        // Old product in customer's "My Products" still works safely and displays the category name
        $myProdsRes = $this->actingAs($this->customer, 'customer')
            ->get(route('customer.products.index'));

        $myProdsRes->assertStatus(200);
        $myProdsRes->assertSee('Smart Watch زیرەک');
        $myProdsRes->assertSee('ئامێری زیرەک');

        // Staff product review also still works safely
        $staffShowRes = $this->actingAs($this->admin, 'staff')
            ->get(route('staff.products.show', $product->id));

        $staffShowRes->assertStatus(200);
        $staffShowRes->assertSee('Smart Watch زیرەک');
        $staffShowRes->assertSee('ئامێری زیرەک');
    }

    public function test_subadmin_gets_403_on_category_management_routes(): void
    {
        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.categories.index'))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.categories.create'))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->post(route('staff.categories.store'), [
                'name' => 'تێست',
            ])->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->post(route('staff.categories.toggle', $this->category->id))
            ->assertStatus(403);
    }
}

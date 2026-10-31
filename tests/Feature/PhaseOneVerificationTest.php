<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhaseOneVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_configuration_and_guards(): void
    {
        $this->assertSame('customer', config('auth.defaults.guard'));
        $this->assertArrayHasKey('customer', config('auth.guards'));
        $this->assertArrayHasKey('staff', config('auth.guards'));
    }

    public function test_staff_authentication_with_password_hash(): void
    {
        $staff = User::create([
            'full_name' => 'Admin Test',
            'email' => 'admin.test@example.com',
            'password_hash' => Hash::make('secret123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertSame('password_hash', $staff->getAuthPasswordName());
        $this->assertTrue(Hash::check('secret123', $staff->getAuthPassword()));

        $attempt = Auth::guard('staff')->attempt([
            'email' => 'admin.test@example.com',
            'password' => 'secret123',
        ]);

        $this->assertTrue($attempt);
        $this->assertSame($staff->id, Auth::guard('staff')->id());
    }

    public function test_customer_authentication_with_password_hash(): void
    {
        $customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'Kurdish Customer',
            'phone' => '07501112233',
            'email' => 'customer.test@example.com',
            'password_hash' => Hash::make('customerpass'),
            'address' => 'Erbil',
        ]);

        $this->assertSame('password_hash', $customer->getAuthPasswordName());
        $this->assertTrue(Hash::check('customerpass', $customer->getAuthPassword()));

        $attempt = Auth::guard('customer')->attempt([
            'email' => 'customer.test@example.com',
            'password' => 'customerpass',
        ]);

        $this->assertTrue($attempt);
        $this->assertSame($customer->id, Auth::guard('customer')->id());
    }

    public function test_model_relationships(): void
    {
        $staff = User::create([
            'full_name' => 'Reviewer Staff',
            'email' => 'reviewer@example.com',
            'password_hash' => Hash::make('pass'),
            'role' => 'subadmin',
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'type' => 'shop',
            'full_name' => 'Shop Owner',
            'shop_name' => 'Kurdistan Tech',
            'phone' => '07502223344',
            'email' => 'shop@example.com',
            'password_hash' => Hash::make('pass'),
            'address' => 'Sulaymaniyah',
            'is_verified' => true,
        ]);

        $category = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);

        $product = Product::create([
            'customer_id' => $customer->id,
            'category_id' => $category->id,
            'title' => 'iPhone 15 Pro',
            'description' => 'Clean condition',
            'price' => 1250000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $staff->id,
            'reviewed_at' => now(),
        ]);

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/iphone.jpg',
            'is_main' => true,
            'sort_order' => 1,
        ]);

        $reporter = Customer::create([
            'type' => 'regular',
            'full_name' => 'Reporter Person',
            'phone' => '07503334455',
            'email' => 'reporter@example.com',
            'password_hash' => Hash::make('pass'),
            'address' => 'Duhok',
        ]);

        $report = Report::create([
            'reporter_id' => $reporter->id,
            'reported_product_id' => $product->id,
            'reason' => 'fake_item',
            'details' => 'Fake battery status',
            'status' => 'open',
        ]);

        // Assert relationships
        $this->assertTrue($customer->products->contains($product));
        $this->assertTrue($category->products->contains($product));
        $this->assertSame($customer->id, $product->customer->id);
        $this->assertSame($category->id, $product->category->id);
        $this->assertSame($staff->id, $product->reviewer->id);
        $this->assertTrue($product->images->contains($image));
        $this->assertSame($product->id, $image->product->id);
        $this->assertTrue($product->reports->contains($report));
        $this->assertSame($reporter->id, $report->reporter->id);
        $this->assertTrue($reporter->reports->contains($report));
    }

    public function test_product_scope_publicly_visible(): void
    {
        $category = Category::create(['name' => 'ئەوانی تر', 'is_active' => true]);

        $normalCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'Active Seller',
            'phone' => '07501002030',
            'email' => 'active@example.com',
            'password_hash' => Hash::make('pass'),
            'address' => 'Erbil',
            'is_banned' => false,
        ]);

        $bannedCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'Banned Seller',
            'phone' => '07501002031',
            'email' => 'banned@example.com',
            'password_hash' => Hash::make('pass'),
            'address' => 'Erbil',
            'is_banned' => true,
        ]);

        // 1. Approved by active customer -> SHOULD be visible
        $approvedActive = Product::create([
            'customer_id' => $normalCustomer->id,
            'category_id' => $category->id,
            'title' => 'Visible Item',
            'description' => 'Test',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'approved',
        ]);

        // 2. Pending by active customer -> NOT visible
        $pendingActive = Product::create([
            'customer_id' => $normalCustomer->id,
            'category_id' => $category->id,
            'title' => 'Pending Item',
            'description' => 'Test',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        // 3. Rejected by active customer -> NOT visible
        $rejectedActive = Product::create([
            'customer_id' => $normalCustomer->id,
            'category_id' => $category->id,
            'title' => 'Rejected Item',
            'description' => 'Test',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'rejected',
        ]);

        // 4. Approved by banned customer -> NOT visible
        $approvedBanned = Product::create([
            'customer_id' => $bannedCustomer->id,
            'category_id' => $category->id,
            'title' => 'Banned User Item',
            'description' => 'Test',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'approved',
        ]);

        $visibleIds = Product::publiclyVisible()->pluck('id')->all();

        $this->assertContains($approvedActive->id, $visibleIds);
        $this->assertNotContains($pendingActive->id, $visibleIds);
        $this->assertNotContains($rejectedActive->id, $visibleIds);
        $this->assertNotContains($approvedBanned->id, $visibleIds);
    }
}


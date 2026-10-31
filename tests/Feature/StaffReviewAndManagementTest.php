<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffReviewAndManagementTest extends TestCase
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
            'full_name' => 'بەڕێوەبەری سەرەکی',
            'email' => 'admin@test.com',
            'password_hash' => Hash::make('admin1234'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->subadmin = User::create([
            'full_name' => 'یاریدەدەر',
            'email' => 'subadmin@test.com',
            'password_hash' => Hash::make('subadmin1234'),
            'role' => 'subadmin',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'کڕیار',
            'phone' => '07501112233',
            'email' => 'customer@test.com',
            'password_hash' => Hash::make('cust1234'),
            'address' => 'هەولێر',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->category = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);
    }

    public function test_staff_login_works_with_correct_credentials(): void
    {
        $response = $this->post('/staff/login', [
            'email' => 'admin@test.com',
            'password' => 'admin1234',
        ]);

        $response->assertRedirect('/staff');
        $this->assertAuthenticatedAs($this->admin, 'staff');
    }

    public function test_staff_login_fails_with_wrong_password(): void
    {
        $response = $this->post('/staff/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('staff');
    }

    public function test_inactive_staff_cannot_login(): void
    {
        $inactive = User::create([
            'full_name' => 'ناچالاک',
            'email' => 'inactive@test.com',
            'password_hash' => Hash::make('pass1234'),
            'role' => 'subadmin',
            'is_active' => false,
        ]);

        $response = $this->post('/staff/login', [
            'email' => 'inactive@test.com',
            'password' => 'pass1234',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('staff');
    }

    public function test_inactive_staff_with_open_session_is_logged_out(): void
    {
        $this->actingAs($this->subadmin, 'staff');

        $this->subadmin->update(['is_active' => false]);

        $response = $this->get('/staff');

        $response->assertRedirect('/staff/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest('staff');
    }

    public function test_customer_cannot_access_staff_routes(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->get('/staff')
            ->assertRedirect('/staff/login');
    }

    public function test_staff_cannot_access_customer_routes(): void
    {
        $this->actingAs($this->admin, 'staff')
            ->get('/my/products')
            ->assertRedirect('/login');
    }

    public function test_subadmin_can_review_products_but_gets_403_on_staff_management(): void
    {
        // Review products is allowed
        $this->actingAs($this->subadmin, 'staff')
            ->get('/staff/products')
            ->assertStatus(200);

        // Staff management routes are 403 forbidden for subadmin
        $this->actingAs($this->subadmin, 'staff')
            ->get('/staff/users')
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->get('/staff/users/create')
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->post('/staff/users', [
                'full_name' => 'New User',
                'email' => 'new@test.com',
                'password' => 'secret123',
                'role' => 'subadmin',
            ])
            ->assertStatus(403);
    }

    public function test_approve_sets_status_reviewed_by_and_reviewed_at(): void
    {
        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەم بۆ پەسەندکردن',
            'description' => 'وەسف',
            'price' => 200000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->subadmin, 'staff')
            ->post("/staff/products/{$product->id}/approve");

        $response->assertRedirect('/staff/products');

        $product->refresh();
        $this->assertSame('approved', $product->status);
        $this->assertSame($this->subadmin->id, $product->reviewed_by);
        $this->assertNotNull($product->reviewed_at);
        $this->assertNull($product->rejection_reason);
    }

    public function test_reject_requires_reason_and_saves_it(): void
    {
        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەم بۆ ڕەتکردنەوە',
            'description' => 'وەسف',
            'price' => 200000,
            'condition' => 'used',
            'status' => 'pending',
        ]);

        // 1. Empty reason fails
        $emptyResponse = $this->actingAs($this->subadmin, 'staff')
            ->post("/staff/products/{$product->id}/reject", [
                'rejection_reason' => '',
            ]);

        $emptyResponse->assertSessionHasErrors('rejection_reason');
        $this->assertSame('pending', $product->fresh()->status);

        // 2. Too short reason fails (< 5 chars)
        $shortResponse = $this->actingAs($this->subadmin, 'staff')
            ->post("/staff/products/{$product->id}/reject", [
                'rejection_reason' => 'کەم',
            ]);

        $shortResponse->assertSessionHasErrors('rejection_reason');
        $this->assertSame('pending', $product->fresh()->status);

        // 3. Valid reason succeeds
        $validResponse = $this->actingAs($this->subadmin, 'staff')
            ->post("/staff/products/{$product->id}/reject", [
                'rejection_reason' => 'وێنەکان ڕوون نین و بەرهەمەکە کێشەی هەیە',
            ]);

        $validResponse->assertRedirect('/staff/products');

        $product->refresh();
        $this->assertSame('rejected', $product->status);
        $this->assertSame($this->subadmin->id, $product->reviewed_by);
        $this->assertNotNull($product->reviewed_at);
        $this->assertSame('وێنەکان ڕوون نین و بەرهەمەکە کێشەی هەیە', $product->rejection_reason);
    }

    public function test_approving_or_rejecting_non_pending_product_fails_safely(): void
    {
        $approvedProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی پێشتر پەسەندکراو',
            'description' => 'وەسف',
            'price' => 200000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);

        // Attempting to approve an already approved product
        $approveResponse = $this->actingAs($this->subadmin, 'staff')
            ->post("/staff/products/{$approvedProduct->id}/approve");

        $approveResponse->assertSessionHas('error');
        $this->assertSame('approved', $approvedProduct->fresh()->status);

        // Attempting to reject an already approved product
        $rejectResponse = $this->actingAs($this->subadmin, 'staff')
            ->post("/staff/products/{$approvedProduct->id}/reject", [
                'rejection_reason' => 'ڕەتکردنەوەی درەنگوەخت',
            ]);

        $rejectResponse->assertSessionHas('error');
        $this->assertSame('approved', $approvedProduct->fresh()->status);
    }

    public function test_admin_can_create_subadmin(): void
    {
        $response = $this->actingAs($this->admin, 'staff')
            ->post('/staff/users', [
                'full_name' => 'کارمەندی نوێ',
                'email' => 'new.staff@test.com',
                'password' => 'secret1234',
                'role' => 'subadmin',
                'is_active' => '1',
            ]);

        $response->assertRedirect('/staff/users');

        $this->assertDatabaseHas('user', [
            'email' => 'new.staff@test.com',
            'full_name' => 'کارمەندی نوێ',
            'role' => 'subadmin',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'new.staff@test.com')->first();
        $this->assertTrue(Hash::check('secret1234', $newUser->password_hash));
    }

    public function test_admin_cannot_deactivate_or_demote_themselves(): void
    {
        // 1. Attempt self-demotion
        $demoteResponse = $this->actingAs($this->admin, 'staff')
            ->put("/staff/users/{$this->admin->id}", [
                'full_name' => $this->admin->full_name,
                'email' => $this->admin->email,
                'role' => 'subadmin',
                'is_active' => '1',
            ]);

        $demoteResponse->assertSessionHasErrors('role');
        $this->assertSame('admin', $this->admin->fresh()->role);

        // 2. Attempt self-deactivation
        $deactivateResponse = $this->actingAs($this->admin, 'staff')
            ->put("/staff/users/{$this->admin->id}", [
                'full_name' => $this->admin->full_name,
                'email' => $this->admin->email,
                'role' => 'admin',
                'is_active' => '0',
            ]);

        $deactivateResponse->assertSessionHasErrors('is_active');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_review_queue_tabs_filter_correctly(): void
    {
        $pendingProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی تەنها چاوەڕوانکراو',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $approvedProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی تەنها پەسەندکراو',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);

        $rejectedProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی تەنها ڕەتکراوە',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'rejected',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
            'rejection_reason' => 'کوالێتی نزم',
        ]);

        // Pending tab
        $pendingTab = $this->actingAs($this->admin, 'staff')
            ->get('/staff/products?status=pending');
        $pendingTab->assertStatus(200);
        $pendingTab->assertSee('بەرهەمی تەنها چاوەڕوانکراو');
        $pendingTab->assertDontSee('بەرهەمی تەنها پەسەندکراو');
        $pendingTab->assertDontSee('بەرهەمی تەنها ڕەتکراوە');

        // Approved tab
        $approvedTab = $this->actingAs($this->admin, 'staff')
            ->get('/staff/products?status=approved');
        $approvedTab->assertStatus(200);
        $approvedTab->assertSee('بەرهەمی تەنها پەسەندکراو');
        $approvedTab->assertDontSee('بەرهەمی تەنها چاوەڕوانکراو');

        // Rejected tab
        $rejectedTab = $this->actingAs($this->admin, 'staff')
            ->get('/staff/products?status=rejected');
        $rejectedTab->assertStatus(200);
        $rejectedTab->assertSee('بەرهەمی تەنها ڕەتکراوە');
        $rejectedTab->assertDontSee('بەرهەمی تەنها چاوەڕوانکراو');
    }
}

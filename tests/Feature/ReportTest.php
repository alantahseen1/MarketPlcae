<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $reporter;

    protected Customer $seller;

    protected Product $product;

    protected Category $category;

    protected User $admin;

    protected User $subadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'full_name' => 'سەرپەرشتیاری سەرەکی',
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

        $this->reporter = Customer::create([
            'type' => 'regular',
            'full_name' => 'ئارام کەریم',
            'phone' => '07501112233',
            'email' => 'aram@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'هەولێر',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->seller = Customer::create([
            'type' => 'shop',
            'full_name' => 'پێشەوا عومەر',
            'shop_name' => 'ئای تی پڵەس',
            'phone' => '07704445566',
            'email' => 'itplus@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'سلێمانی',
            'is_verified' => true,
            'is_banned' => false,
        ]);

        $this->product = Product::create([
            'customer_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'iPhone 15 Pro Max',
            'description' => 'وەسفی بەرهەم',
            'price' => 1400000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);
    }

    public function test_guest_cannot_report_product_or_customer_and_is_redirected_to_login(): void
    {
        $this->get(route('reports.product.create', $this->product->id))
            ->assertRedirect(route('login'));

        $this->post(route('reports.product.store', $this->product->id), [
            'reason' => 'scam',
        ])->assertRedirect(route('login'));

        $this->get(route('reports.customer.create', $this->seller->id))
            ->assertRedirect(route('login'));

        $this->post(route('reports.customer.store', $this->seller->id), [
            'reason' => 'scam',
        ])->assertRedirect(route('login'));
    }

    public function test_customer_can_report_product_and_customer(): void
    {
        // 1. Report product
        $prodResponse = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'fake_item',
                'details' => 'بەرهەمەکە ئەسڵی نییە',
            ]);

        $prodResponse->assertRedirect(route('products.show', $this->product->id));
        $prodResponse->assertSessionHas('success');

        $this->assertDatabaseHas('report', [
            'reporter_id' => $this->reporter->id,
            'reported_product_id' => $this->product->id,
            'reported_customer_id' => null,
            'reason' => 'fake_item',
            'details' => 'بەرهەمەکە ئەسڵی نییە',
            'status' => 'open',
        ]);

        // 2. Report customer
        $custResponse = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.customer.store', $this->seller->id), [
                'reason' => 'scam',
                'details' => 'مامەڵەی دروستی نەکرد',
            ]);

        $custResponse->assertRedirect(route('shops.show', $this->seller->id));
        $custResponse->assertSessionHas('success');

        $this->assertDatabaseHas('report', [
            'reporter_id' => $this->reporter->id,
            'reported_product_id' => null,
            'reported_customer_id' => $this->seller->id,
            'reason' => 'scam',
            'status' => 'open',
        ]);
    }

    public function test_exactly_one_target_is_stored_never_both(): void
    {
        $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'spam',
            ]);

        $report = Report::latest('id')->first();
        $this->assertNotNull($report->reported_product_id);
        $this->assertNull($report->reported_customer_id);
    }

    public function test_cannot_report_own_product(): void
    {
        $response = $this->actingAs($this->seller, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'spam',
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('report', [
            'reporter_id' => $this->seller->id,
            'reported_product_id' => $this->product->id,
        ]);
    }

    public function test_cannot_report_self(): void
    {
        $response = $this->actingAs($this->seller, 'customer')
            ->post(route('reports.customer.store', $this->seller->id), [
                'reason' => 'scam',
            ]);

        $response->assertSessionHasErrors('error');
        $this->assertDatabaseMissing('report', [
            'reporter_id' => $this->seller->id,
            'reported_customer_id' => $this->seller->id,
        ]);
    }

    public function test_cannot_report_non_visible_product(): void
    {
        $pendingProduct = Product::create([
            'customer_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Pending Phone',
            'description' => 'desc',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $pendingProduct->id), [
                'reason' => 'spam',
            ]);

        $response->assertStatus(404);
    }

    public function test_cannot_file_duplicate_open_report_on_same_target(): void
    {
        // First report
        $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'scam',
            ])->assertSessionHas('success');

        // Second report on same product while first is open
        $dupResponse = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'spam',
            ]);

        $dupResponse->assertSessionHasErrors('error');
        $this->assertCount(1, Report::where('reported_product_id', $this->product->id)->get());
    }

    public function test_daily_report_limit_works(): void
    {
        // Create 10 dummy products to report
        for ($i = 1; $i <= 10; $i++) {
            $prod = Product::create([
                'customer_id' => $this->seller->id,
                'category_id' => $this->category->id,
                'title' => "Product {$i}",
                'description' => 'desc',
                'price' => 100000 + $i,
                'condition' => 'new',
                'status' => 'approved',
                'reviewed_by' => $this->admin->id,
                'reviewed_at' => now(),
            ]);

            Report::create([
                'reporter_id' => $this->reporter->id,
                'reported_product_id' => $prod->id,
                'reported_customer_id' => null,
                'reason' => 'spam',
                'status' => 'open',
                'created_at' => Carbon::now(),
            ]);
        }

        // 11th report should be blocked
        $anotherProd = Product::create([
            'customer_id' => $this->seller->id,
            'category_id' => $this->category->id,
            'title' => 'Product 11',
            'description' => 'desc',
            'price' => 200000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->admin->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $anotherProd->id), [
                'reason' => 'scam',
            ]);

        $response->assertSessionHasErrors('error');
    }

    public function test_details_required_when_reason_is_other(): void
    {
        // Reason = other without details -> fails
        $failRes = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'other',
                'details' => '',
            ]);

        $failRes->assertSessionHasErrors('details');

        // Reason = other with details -> succeeds
        $passRes = $this->actingAs($this->reporter, 'customer')
            ->post(route('reports.product.store', $this->product->id), [
                'reason' => 'other',
                'details' => 'ئەمە ڕوونکردنەوەی زیاترە بۆ هۆکاری تر',
            ]);

        $passRes->assertSessionHas('success');
    }

    public function test_admin_can_resolve_and_dismiss_open_reports(): void
    {
        $report1 = Report::create([
            'reporter_id' => $this->reporter->id,
            'reported_product_id' => $this->product->id,
            'reason' => 'scam',
            'status' => 'open',
        ]);

        $report2 = Report::create([
            'reporter_id' => $this->reporter->id,
            'reported_customer_id' => $this->seller->id,
            'reason' => 'spam',
            'status' => 'open',
        ]);

        // Resolve report1
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.reports.resolve', $report1->id))
            ->assertSessionHas('success');

        $this->assertEquals('resolved', $report1->fresh()->status);
        $this->assertEquals($this->admin->id, $report1->fresh()->handled_by);
        $this->assertNotNull($report1->fresh()->handled_at);

        // Dismiss report2
        $this->actingAs($this->admin, 'staff')
            ->post(route('staff.reports.dismiss', $report2->id))
            ->assertSessionHas('success');

        $this->assertEquals('dismissed', $report2->fresh()->status);

        // Cannot change a closed report
        $closedRes = $this->actingAs($this->admin, 'staff')
            ->post(route('staff.reports.resolve', $report2->id));

        $closedRes->assertSessionHas('error');
    }

    public function test_subadmin_gets_403_on_reports_customers_and_categories(): void
    {
        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.reports.index'))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.customers.index'))
            ->assertStatus(403);

        $this->actingAs($this->subadmin, 'staff')
            ->get(route('staff.categories.index'))
            ->assertStatus(403);
    }
}


<?php

namespace Tests\Feature;

use App\Helpers\MarketplaceHelper;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PublicBrowsingTest extends TestCase
{
    use RefreshDatabase;

    protected Category $activeCategory;

    protected Category $secondCategory;

    protected Customer $regularCustomer;

    protected Customer $shopCustomer;

    protected Customer $bannedCustomer;

    protected Customer $bannedShop;

    protected User $staffUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activeCategory = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);

        $this->secondCategory = Category::create([
            'name' => 'لاپتۆپ',
            'is_active' => true,
        ]);

        $this->staffUser = User::create([
            'full_name' => 'سەرپەرشتیار',
            'email' => 'staff@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->regularCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'ئاسۆ عەلی',
            'phone' => '07501112233',
            'email' => 'aso@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'هەولێر - بەختیاری',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->shopCustomer = Customer::create([
            'type' => 'shop',
            'full_name' => 'دیاری کەریم',
            'shop_name' => 'تێک کۆرنەر',
            'logo' => null,
            'phone' => '07704445566',
            'email' => 'shop@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'سلێمانی - ڕاپەڕین',
            'is_verified' => true,
            'is_banned' => false,
        ]);

        $this->bannedCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'باندکراو یەک',
            'phone' => '07809990011',
            'email' => 'banned1@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'کەرکووک',
            'is_verified' => false,
            'is_banned' => true,
        ]);

        $this->bannedShop = Customer::create([
            'type' => 'shop',
            'full_name' => 'باندکراو دوو',
            'shop_name' => 'شۆپی بلۆککراو',
            'phone' => '07809990022',
            'email' => 'banned2@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'دهۆک',
            'is_verified' => false,
            'is_banned' => true,
        ]);
    }

    public function test_home_page_displays_active_categories_and_approved_products(): void
    {
        $inactiveCategory = Category::create([
            'name' => 'بەشی ناچالاک',
            'is_active' => false,
        ]);

        $approvedProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'iPhone 15 Pro Max ئایفۆن',
            'description' => 'وەسفی بەرهەم',
            'price' => 1500000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $pendingProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Pending Phone هێشتا پەسەند نەکراو',
            'description' => 'وەسف',
            'price' => 500000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $bannedCustomerProduct = Product::create([
            'customer_id' => $this->bannedCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Banned User Phone مۆبایلی کەسی بلۆککراو',
            'description' => 'وەسف',
            'price' => 600000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($this->activeCategory->name);
        $response->assertDontSee($inactiveCategory->name);
        $response->assertSee('iPhone 15 Pro Max ئایفۆن');
        $response->assertDontSee('Pending Phone هێشتا پەسەند نەکراو');
        $response->assertDontSee('Banned User Phone مۆبایلی کەسی بلۆککراو');
        $response->assertSee('تێک کۆرنەر');
    }

    public function test_product_browse_page_only_shows_approved_products_of_unbanned_customers(): void
    {
        $approvedProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Galaxy S24 Ultra سامسۆنگ',
            'description' => 'بەرهەمی پەسەندکراو',
            'price' => 1400000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $rejectedProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Rejected Laptop ڕەتکراوە',
            'description' => 'وەسف',
            'price' => 800000,
            'condition' => 'used',
            'status' => 'rejected',
            'rejection_reason' => 'وێنە ڕوون نییە',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $soldProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Sold Product فرۆشراو',
            'description' => 'وەسف',
            'price' => 300000,
            'condition' => 'used',
            'status' => 'sold',
            'sold_at' => now(),
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $bannedProduct = Product::create([
            'customer_id' => $this->bannedCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Banned Seller Item بەرهەمی فرۆشیاری باندکراو',
            'description' => 'وەسف',
            'price' => 400000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertSee('Galaxy S24 Ultra سامسۆنگ');
        $response->assertDontSee('Rejected Laptop ڕەتکراوە');
        $response->assertDontSee('Sold Product فرۆشراو');
        $response->assertDontSee('Banned Seller Item بەرهەمی فرۆشیاری باندکراو');
    }

    public function test_product_browse_filtering_by_query_search(): void
    {
        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Apple MacBook Pro M3',
            'description' => 'لاپتۆپێکی نایاب',
            'price' => 2500000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Sony PlayStation 5',
            'description' => 'کۆنسۆڵی یاری',
            'price' => 650000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->get(route('products.index', ['q' => 'MacBook']));

        $response->assertStatus(200);
        $response->assertSee('Apple MacBook Pro M3');
        $response->assertDontSee('Sony PlayStation 5');
    }

    public function test_product_browse_filtering_by_category(): void
    {
        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'مۆبایلی ئایفۆن ١٥',
            'description' => 'وەسف',
            'price' => 1200000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->secondCategory->id,
            'title' => 'لاپتۆپی دێڵ ئێکس پی ئێس',
            'description' => 'وەسف',
            'price' => 1800000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->get(route('products.index', ['category' => $this->activeCategory->id]));

        $response->assertStatus(200);
        $response->assertSee('مۆبایلی ئایفۆن ١٥');
        $response->assertDontSee('لاپتۆپی دێڵ ئێکس پی ئێس');
    }

    public function test_product_browse_filtering_by_condition_and_price_range(): void
    {
        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'مۆبایلی نوێ هەرزان',
            'description' => 'وەسف',
            'price' => 200000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'مۆبایلی دەستی دوو گران',
            'description' => 'وەسف',
            'price' => 800000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        // Filter condition = new
        $resNew = $this->get(route('products.index', ['condition' => 'new']));
        $resNew->assertSee('مۆبایلی نوێ هەرزان');
        $resNew->assertDontSee('مۆبایلی دەستی دوو گران');

        // Filter min_price = 500000
        $resPrice = $this->get(route('products.index', ['min_price' => 500000]));
        $resPrice->assertSee('مۆبایلی دەستی دوو گران');
        $resPrice->assertDontSee('مۆبایلی نوێ هەرزان');
    }

    public function test_product_browse_sorting_by_price(): void
    {
        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'بەرهەمی نرخ کەم',
            'description' => 'وەسف',
            'price' => 50000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'بەرهەمی نرخ بەرز',
            'description' => 'وەسف',
            'price' => 950000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $resAsc = $this->get(route('products.index', ['sort' => 'price_asc']));
        $resAsc->assertStatus(200);
        $resAsc->assertSeeInOrder(['بەرهەمی نرخ کەم', 'بەرهەمی نرخ بەرز']);

        $resDesc = $this->get(route('products.index', ['sort' => 'price_desc']));
        $resDesc->assertStatus(200);
        $resDesc->assertSeeInOrder(['بەرهەمی نرخ بەرز', 'بەرهەمی نرخ کەم']);
    }

    public function test_invalid_filter_inputs_do_not_crash(): void
    {
        $response = $this->get(route('products.index', [
            'category' => 'broken_string',
            'condition' => 'invalid_cond',
            'min_price' => 'negative_or_text',
            'max_price' => 'invalid_chars!@#',
            'sort' => 'unsupported_sort',
        ]));

        $response->assertStatus(200);
    }

    public function test_product_detail_page_loads_for_approved_product(): void
    {
        $product = Product::create([
            'customer_id' => $this->shopCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'iPad Air M2 256GB تابلێت',
            'description' => "تابلێتی ئایپاد ئەیر نوێیە.\nزۆر خاوێن بەکارهاتووە.",
            'price' => 850000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/test/img1.jpg',
            'is_main' => true,
            'sort_order' => 1,
        ]);

        $response = $this->get(route('products.show', $product->id));

        $response->assertStatus(200);
        $response->assertSee('iPad Air M2 256GB تابلێت');
        $response->assertSee('850,000 د.ع');
        $response->assertSee('تێک کۆرنەر');
        $response->assertSee('tel:07704445566');
        $response->assertSee('https://wa.me/9647704445566');
    }

    public function test_product_detail_page_returns_404_for_unapproved_or_banned_customer_products(): void
    {
        $pendingProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Pending Phone',
            'description' => 'desc',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $rejectedProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Rejected Phone',
            'description' => 'desc',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'rejected',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $soldProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Sold Phone',
            'description' => 'desc',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'sold',
            'sold_at' => now(),
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $removedProduct = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Removed Phone',
            'description' => 'desc',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'removed',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $bannedCustomerProduct = Product::create([
            'customer_id' => $this->bannedCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Banned User Phone',
            'description' => 'desc',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $this->get(route('products.show', $pendingProduct->id))->assertStatus(404);
        $this->get(route('products.show', $rejectedProduct->id))->assertStatus(404);
        $this->get(route('products.show', $soldProduct->id))->assertStatus(404);
        $this->get(route('products.show', $removedProduct->id))->assertStatus(404);
        $this->get(route('products.show', $bannedCustomerProduct->id))->assertStatus(404);
        $this->get(route('products.show', 999999))->assertStatus(404);
    }

    public function test_shop_page_loads_for_active_shop(): void
    {
        $shopProduct = Product::create([
            'customer_id' => $this->shopCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Shop Approved Product',
            'description' => 'desc',
            'price' => 250000,
            'condition' => 'new',
            'status' => 'approved',
            'reviewed_by' => $this->staffUser->id,
            'reviewed_at' => now(),
        ]);

        $shopPendingProduct = Product::create([
            'customer_id' => $this->shopCustomer->id,
            'category_id' => $this->activeCategory->id,
            'title' => 'Shop Pending Product',
            'description' => 'desc',
            'price' => 250000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $response = $this->get(route('shops.show', $this->shopCustomer->id));

        $response->assertStatus(200);
        $response->assertSee('تێک کۆرنەر');
        $response->assertSee('Shop Approved Product');
        $response->assertDontSee('Shop Pending Product');
    }

    public function test_shop_page_returns_404_for_regular_customer_or_banned_shop(): void
    {
        // Regular customer should return 404
        $this->get(route('shops.show', $this->regularCustomer->id))->assertStatus(404);

        // Banned shop should return 404
        $this->get(route('shops.show', $this->bannedShop->id))->assertStatus(404);

        // Non-existent shop should return 404
        $this->get(route('shops.show', 888888))->assertStatus(404);
    }

    public function test_marketplace_helper_methods(): void
    {
        $this->assertSame('1,500,000 د.ع', MarketplaceHelper::formatPrice(1500000));
        $this->assertSame('https://wa.me/9647501234567', MarketplaceHelper::whatsappUrl('07501234567'));
        $this->assertSame('https://wa.me/9647701234567', MarketplaceHelper::whatsappUrl('+9647701234567'));
    }

    public function test_demo_data_seeder_runs_cleanly_and_respects_constraints(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DemoDataSeeder::class);

        $this->assertGreaterThanOrEqual(4, User::count());
        $this->assertGreaterThanOrEqual(5, Category::count());
        $this->assertGreaterThanOrEqual(25, Customer::count());
        $this->assertGreaterThanOrEqual(80, Product::count());
        $this->assertGreaterThanOrEqual(80, ProductImage::count());
        $this->assertGreaterThanOrEqual(10, Report::count());

        // Validate chk_one_target constraint across all reports
        foreach (Report::all() as $report) {
            $hasProduct = $report->reported_product_id !== null;
            $hasCustomer = $report->reported_customer_id !== null;
            $this->assertTrue(
                ($hasProduct && ! $hasCustomer) || (! $hasProduct && $hasCustomer),
                "Report #{$report->id} violates chk_one_target (product_id: {$report->reported_product_id}, customer_id: {$report->reported_customer_id})"
            );
        }
    }
}

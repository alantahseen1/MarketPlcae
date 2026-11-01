<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhaseSevenTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    protected Customer $regularCustomer;

    protected Customer $shopCustomer;

    protected User $adminUser;

    protected User $subadminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);

        $this->regularCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'کاروان محەمەد',
            'phone' => '07501112233',
            'email' => 'karwan@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'هەولێر - ٤٠ مەتری',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $this->shopCustomer = Customer::create([
            'type' => 'shop',
            'shop_name' => 'کۆشکی ئەلیکترۆنی',
            'full_name' => 'ڕێبین ئەحمەد',
            'phone' => '07701112233',
            'email' => 'koshk@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'سلێمانی - شەقامی سالم',
            'logo' => 'logos/old_logo.png',
            'is_verified' => true,
            'is_banned' => false,
        ]);

        $this->adminUser = User::create([
            'full_name' => 'بەڕێوەبەری گشتی',
            'email' => 'admin_test@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->subadminUser = User::create([
            'full_name' => 'یاریدەدەر',
            'email' => 'subadmin_test@example.com',
            'password_hash' => Hash::make('password123'),
            'role' => 'subadmin',
            'is_active' => true,
        ]);
    }

    public function test_customer_can_update_profile_regular(): void
    {
        $response = $this->actingAs($this->regularCustomer, 'customer')
            ->put('/my/profile', [
                'full_name' => 'کاروان محەمەد نوێکراوە',
                'phone' => '07509998877',
                'address' => 'هەولێر - بەختیاری',
                'email' => 'hacked@example.com', // Should be ignored
            ]);

        $response->assertRedirect('/my/profile');
        $response->assertSessionHas('success');

        $this->regularCustomer->refresh();
        $this->assertEquals('کاروان محەمەد نوێکراوە', $this->regularCustomer->full_name);
        $this->assertEquals('07509998877', $this->regularCustomer->phone);
        $this->assertEquals('هەولێر - بەختیاری', $this->regularCustomer->address);
        $this->assertEquals('karwan@example.com', $this->regularCustomer->email);

        // Attempting to provide shop_name as regular customer must fail validation
        $failResponse = $this->actingAs($this->regularCustomer, 'customer')
            ->put('/my/profile', [
                'full_name' => 'کاروان محەمەد',
                'phone' => '07509998877',
                'address' => 'هەولێر',
                'shop_name' => 'فرۆشگای ساختە',
            ]);

        $failResponse->assertSessionHasErrors('shop_name');
    }

    public function test_shop_customer_can_update_profile_and_logo(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('logos/old_logo.png', 'fake old logo');

        $newLogo = UploadedFile::fake()->image('new_logo.png', 200, 200);

        $response = $this->actingAs($this->shopCustomer, 'customer')
            ->put('/my/profile', [
                'full_name' => 'ڕێبین ئەحمەد گۆڕاو',
                'shop_name' => 'کۆشکی ئەلیکترۆنی پڵەس',
                'phone' => '07705554433',
                'address' => 'سلێمانی - ڕاپەڕین',
                'logo' => $newLogo,
            ]);

        $response->assertRedirect('/my/profile');
        $response->assertSessionHas('success');

        $this->shopCustomer->refresh();
        $this->assertEquals('کۆشکی ئەلیکترۆنی پڵەس', $this->shopCustomer->shop_name);
        $this->assertEquals('07705554433', $this->shopCustomer->phone);

        // Old logo must be deleted
        Storage::disk('public')->assertMissing('logos/old_logo.png');
        // New logo must exist
        $this->assertNotNull($this->shopCustomer->logo);
        Storage::disk('public')->assertExists($this->shopCustomer->logo);
    }

    public function test_customer_phone_update_immediately_reflects_on_product_page(): void
    {
        $product = Product::create([
            'customer_id' => $this->regularCustomer->id,
            'category_id' => $this->category->id,
            'title' => 'iPhone 15 Pro Max',
            'description' => 'مۆبایلی زۆر پاک',
            'price' => 1200000,
            'condition' => 'used',
            'status' => 'approved',
        ]);

        $viewResponse = $this->get("/products/{$product->id}");
        $viewResponse->assertOk();
        $viewResponse->assertSee('07501112233');

        // Customer updates phone
        $this->actingAs($this->regularCustomer, 'customer')
            ->put('/my/profile', [
                'full_name' => $this->regularCustomer->full_name,
                'phone' => '07708889900',
                'address' => $this->regularCustomer->address,
            ]);

        // Product page reflects updated phone immediately
        $viewResponseAfter = $this->get("/products/{$product->id}");
        $viewResponseAfter->assertOk();
        $viewResponseAfter->assertSee('07708889900');
        $viewResponseAfter->assertDontSee('07501112233');
    }

    public function test_customer_can_change_password(): void
    {
        // 1. Wrong current password fails
        $failResponse = $this->actingAs($this->regularCustomer, 'customer')
            ->put('/my/password', [
                'current_password' => 'wrongpass',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $failResponse->assertSessionHasErrors('current_password');

        // 2. Mismatching confirmation fails
        $mismatchResponse = $this->actingAs($this->regularCustomer, 'customer')
            ->put('/my/password', [
                'current_password' => 'password123',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'mismatchpass',
            ]);

        $mismatchResponse->assertSessionHasErrors('new_password');

        // 3. Valid change succeeds and keeps customer authenticated
        $successResponse = $this->actingAs($this->regularCustomer, 'customer')
            ->put('/my/password', [
                'current_password' => 'password123',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $successResponse->assertRedirect('/my/password');
        $successResponse->assertSessionHas('success');
        $this->assertAuthenticatedAs($this->regularCustomer, 'customer');

        $this->regularCustomer->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->regularCustomer->password_hash));
    }

    public function test_kurdish_error_pages_render(): void
    {
        $response = $this->get('/non-existent-random-route');
        $response->assertStatus(404);
        $response->assertSee('404');
        $response->assertSee('پەڕەکە نەدۆزرایەوە');
        $response->assertSee('dir="rtl"', false);
    }

    public function test_sitemap_xml_and_robots_txt(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
        $response->assertSee('<urlset', false);
        $response->assertSee(url('/'), false);

        $robotsPath = public_path('robots.txt');
        $this->assertFileExists($robotsPath);
        $robotsContent = file_get_contents($robotsPath);
        $this->assertStringContainsString('Disallow: /staff/', $robotsContent);
        $this->assertStringContainsString('Disallow: /my/', $robotsContent);
        $this->assertStringContainsString('Disallow: /login', $robotsContent);
        $this->assertStringContainsString('Disallow: /register', $robotsContent);
        $this->assertStringContainsString('Disallow: /reports/', $robotsContent);
        $this->assertStringContainsString('Sitemap:', $robotsContent);
    }

    public function test_image_upload_resized_to_1600_and_thumbnail_generated(): void
    {
        Storage::fake('public');

        // Fake image 1800x1200
        $photo = UploadedFile::fake()->image('large_test.jpg', 1800, 1200);

        $response = $this->actingAs($this->regularCustomer, 'customer')
            ->post('/my/products', [
                'category_id' => $this->category->id,
                'title' => 'گالاکسی ئێس ٢٤ ئەڵترا',
                'description' => 'مۆبایلی نایاب و کەم بەکارهاتوو',
                'price' => 1350000,
                'condition' => 'used',
                'photos' => [$photo],
            ]);

        $response->assertRedirect('/my/products');

        $product = Product::where('title', 'گالاکسی ئێس ٢٤ ئەڵترا')->first();
        $this->assertNotNull($product);

        $mainImg = $product->images()->first();
        $this->assertNotNull($mainImg);

        // Verify image on disk
        Storage::disk('public')->assertExists($mainImg->image_path);

        $diskPath = Storage::disk('public')->path($mainImg->image_path);
        [$w, $h] = getimagesize($diskPath);
        $this->assertLessThanOrEqual(1600, max($w, $h));

        // Verify thumbnail
        $thumbPath = dirname($mainImg->image_path).'/thumb_'.basename($mainImg->image_path);
        Storage::disk('public')->assertExists($thumbPath);

        $thumbDiskPath = Storage::disk('public')->path($thumbPath);
        [$tw, $th] = getimagesize($thumbDiskPath);
        $this->assertLessThanOrEqual(400, max($tw, $th));
    }

    public function test_rate_limiting_registration(): void
    {
        // 10 attempts allowed per hour
        for ($i = 1; $i <= 10; $i++) {
            $this->post('/register', [
                'type' => 'regular',
                'full_name' => "بەکارهێنەر {$i}",
                'phone' => sprintf('075012345%02d', $i),
                'email' => "user{$i}@example.com",
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'address' => 'هەولێر',
            ]);
        }

        // 11th request must be throttled with 429
        $response = $this->post('/register', [
            'type' => 'regular',
            'full_name' => 'بەکارهێنەر زیادە',
            'phone' => '07509999999',
            'email' => 'user_excess@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'address' => 'هەولێر',
        ]);

        $response->assertStatus(429);
    }

    public function test_rate_limiting_product_creation(): void
    {
        Storage::fake('public');
        $photo = UploadedFile::fake()->image('thumb.jpg', 400, 400);

        // 20 product creations allowed per day per customer
        for ($i = 1; $i <= 20; $i++) {
            $this->actingAs($this->regularCustomer, 'customer')
                ->post('/my/products', [
                    'category_id' => $this->category->id,
                    'title' => "بەرهەمی ژمارە {$i}",
                    'description' => 'وەسفکردنی کورت بۆ بەرهەم',
                    'price' => 50000 + ($i * 1000),
                    'condition' => 'used',
                    'photos' => [$photo],
                ]);
        }

        // 21st attempt must be throttled with 429
        $response = $this->actingAs($this->regularCustomer, 'customer')
            ->post('/my/products', [
                'category_id' => $this->category->id,
                'title' => 'بەرهەمی زیادە لە سنوور',
                'description' => 'وەسفکردنی کورت بۆ بەرهەم',
                'price' => 99000,
                'condition' => 'used',
                'photos' => [$photo],
            ]);

        $response->assertStatus(429);
    }

    public function test_security_headers_present(): void
    {
        $response = $this->get('/');
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_visitor_tracking_logic(): void
    {
        $today = Carbon::today()->toDateString();

        // 1. First public GET visit creates record
        $this->get('/', [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 TestBrowser/1.0',
        ])->assertOk();

        $this->assertEquals(1, Visit::where('visit_date', $today)->count());
        $firstVisit = Visit::where('visit_date', $today)->first();
        $this->assertEquals(1, $firstVisit->page_views);
        $this->assertEquals(64, strlen($firstVisit->visitor_hash));

        // 2. Repeat visit from same device on same day increments page_views
        $this->get('/products', [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 TestBrowser/1.0',
        ])->assertOk();

        $this->assertEquals(1, Visit::where('visit_date', $today)->count());
        $this->assertEquals(2, $firstVisit->fresh()->page_views);

        // 3. Different visitor creates a new row
        $this->get('/', [
            'REMOTE_ADDR' => '203.0.113.20',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 DifferentBrowser/2.0',
        ])->assertOk();

        $this->assertEquals(2, Visit::where('visit_date', $today)->count());

        // 4. Bot UA is NOT counted
        $this->get('/', [
            'REMOTE_ADDR' => '203.0.113.30',
            'HTTP_USER_AGENT' => 'Googlebot/2.1 (+http://www.google.com/bot.html)',
        ])->assertOk();

        $this->assertEquals(2, Visit::where('visit_date', $today)->count());

        // 5. Staff route is NOT counted
        $this->get('/staff/login', [
            'REMOTE_ADDR' => '203.0.113.40',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 StaffBrowser/1.0',
        ])->assertOk();

        $this->assertEquals(2, Visit::where('visit_date', $today)->count());

        // 6. Logged-in staff visiting public pages is NOT counted
        $this->actingAs($this->adminUser, 'staff')->get('/', [
            'REMOTE_ADDR' => '203.0.113.50',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 AdminStaffBrowser/1.0',
        ])->assertOk();

        $this->assertEquals(2, Visit::where('visit_date', $today)->count());

        // 7. Error response (404) is NOT counted
        $this->get('/some-nonexistent-page', [
            'REMOTE_ADDR' => '203.0.113.60',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 ErrorBrowser/1.0',
        ])->assertStatus(404);

        $this->assertEquals(2, Visit::where('visit_date', $today)->count());
    }

    public function test_admin_and_subadmin_dashboard_visitor_stats_visibility(): void
    {
        // Admin can see visitor stats section
        $adminResponse = $this->actingAs($this->adminUser, 'staff')->get('/staff');
        $adminResponse->assertOk();
        $adminResponse->assertSee('ئاماری سەردانکەران');
        $adminResponse->assertSee('سەردانکەرانی ئەمڕۆ');

        // Subadmin cannot see visitor stats section
        $subadminResponse = $this->actingAs($this->subadminUser, 'staff')->get('/staff');
        $subadminResponse->assertOk();
        $subadminResponse->assertDontSee('ئاماری سەردانکەران');
        $subadminResponse->assertDontSee('سەردانکەرانی ئەمڕۆ');
    }

    public function test_prune_visits_command(): void
    {
        $oldDate = Carbon::today()->subDays(410)->toDateString();
        $recentDate = Carbon::today()->subDays(10)->toDateString();

        DB::table('visit')->insert([
            [
                'visitor_hash' => hash('sha256', 'old_visitor_1'),
                'visit_date' => $oldDate,
                'page_views' => 3,
                'created_at' => Carbon::now()->subDays(410),
                'updated_at' => Carbon::now()->subDays(410),
            ],
            [
                'visitor_hash' => hash('sha256', 'recent_visitor_1'),
                'visit_date' => $recentDate,
                'page_views' => 2,
                'created_at' => Carbon::now()->subDays(10),
                'updated_at' => Carbon::now()->subDays(10),
            ],
        ]);

        $this->assertEquals(1, Visit::where('visit_date', $oldDate)->count());
        $this->assertEquals(1, Visit::where('visit_date', $recentDate)->count());

        $this->artisan('visits:prune', ['--days' => 400])
            ->expectsOutputToContain('Pruned 1 visitor records')
            ->assertExitCode(0);

        $this->assertEquals(0, Visit::where('visit_date', $oldDate)->count());
        $this->assertEquals(1, Visit::where('visit_date', $recentDate)->count());
    }
}

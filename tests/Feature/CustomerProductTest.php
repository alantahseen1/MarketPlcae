<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerProductTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'مۆبایل',
            'is_active' => true,
        ]);

        $this->customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'ئاراس ئەحمەد',
            'phone' => '07501234567',
            'email' => 'aras@example.com',
            'password_hash' => Hash::make('password123'),
            'address' => 'هەولێر',
            'is_verified' => false,
            'is_banned' => false,
        ]);
    }

    public function test_can_create_product_with_images(): void
    {
        Storage::fake('public');

        $photo1 = UploadedFile::fake()->image('phone1.jpg', 600, 600);
        $photo2 = UploadedFile::fake()->image('phone2.png', 600, 600);

        $response = $this->actingAs($this->customer, 'customer')
            ->post('/my/products/create', [
                'category_id' => $this->category->id,
                'title' => 'سامسۆنگ گالاکسی S24',
                'description' => 'زۆر پاکە و هیچ کێشەی نییە',
                'price' => '1,100,000',
                'condition' => 'used',
                'photos' => [$photo1, $photo2],
            ]);

        $response->assertRedirect('/my/products');

        $this->assertDatabaseHas('product', [
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'سامسۆنگ گالاکسی S24',
            'price' => 1100000,
            'condition' => 'used',
            'status' => 'pending',
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ]);

        $product = Product::where('title', 'سامسۆنگ گالاکسی S24')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);

        $firstImage = $product->images()->where('sort_order', 0)->first();
        $secondImage = $product->images()->where('sort_order', 1)->first();

        $this->assertTrue($firstImage->is_main);
        $this->assertFalse($secondImage->is_main);

        Storage::disk('public')->assertExists($firstImage->image_path);
        Storage::disk('public')->assertExists($secondImage->image_path);
    }

    public function test_more_than_5_images_rejected(): void
    {
        Storage::fake('public');

        $photos = [
            UploadedFile::fake()->image('1.jpg'),
            UploadedFile::fake()->image('2.jpg'),
            UploadedFile::fake()->image('3.jpg'),
            UploadedFile::fake()->image('4.jpg'),
            UploadedFile::fake()->image('5.jpg'),
            UploadedFile::fake()->image('6.jpg'),
        ];

        $response = $this->actingAs($this->customer, 'customer')
            ->post('/my/products/create', [
                'category_id' => $this->category->id,
                'title' => 'بەرهەمی تاقیکاری',
                'description' => 'وەسف',
                'price' => 50000,
                'condition' => 'new',
                'photos' => $photos,
            ]);

        $response->assertSessionHasErrors('photos');
        $this->assertDatabaseMissing('product', ['title' => 'بەرهەمی تاقیکاری']);
    }

    public function test_image_over_4mb_rejected(): void
    {
        Storage::fake('public');

        // 4097 KB exceeds 4096 KB (4MB) limit
        $largePhoto = UploadedFile::fake()->create('large.jpg', 4097, 'image/jpeg');

        $response = $this->actingAs($this->customer, 'customer')
            ->post('/my/products/create', [
                'category_id' => $this->category->id,
                'title' => 'بەرهەمی گەورە',
                'description' => 'وەسف',
                'price' => 50000,
                'condition' => 'new',
                'photos' => [$largePhoto],
            ]);

        $response->assertSessionHasErrors('photos.0');
        $this->assertDatabaseMissing('product', ['title' => 'بەرهەمی گەورە']);
    }

    public function test_no_image_rejected(): void
    {
        $response = $this->actingAs($this->customer, 'customer')
            ->post('/my/products/create', [
                'category_id' => $this->category->id,
                'title' => 'بێ وێنە',
                'description' => 'وەسف',
                'price' => 50000,
                'condition' => 'new',
            ]);

        $response->assertSessionHasErrors('photos');
        $this->assertDatabaseMissing('product', ['title' => 'بێ وێنە']);
    }

    public function test_editing_approved_product_resets_it_to_pending(): void
    {
        Storage::fake('public');

        $staff = User::create([
            'full_name' => 'بەڕێوەبەر',
            'email' => 'reviewer@example.com',
            'password_hash' => Hash::make('pass'),
            'role' => 'subadmin',
            'is_active' => true,
        ]);

        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'پەسەندکراوی پێشوو',
            'description' => 'وەسفی پێشوو',
            'price' => 300000,
            'condition' => 'used',
            'status' => 'approved',
            'reviewed_by' => $staff->id,
            'reviewed_at' => now(),
            'rejection_reason' => null,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/sample.jpg',
            'is_main' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->put("/my/products/{$product->id}", [
                'category_id' => $this->category->id,
                'title' => 'ناونیشانی دەستکاریکراو',
                'description' => 'وەسفی نوێ',
                'price' => 350000,
                'condition' => 'used',
            ]);

        $response->assertRedirect('/my/products');

        $product->refresh();
        $this->assertSame('ناونیشانی دەستکاریکراو', $product->title);
        $this->assertSame(350000, $product->price);
        $this->assertSame('pending', $product->status);
        $this->assertNull($product->reviewed_by);
        $this->assertNull($product->reviewed_at);
        $this->assertNull($product->rejection_reason);
    }

    public function test_customer_cannot_edit_or_delete_another_customers_product(): void
    {
        $otherCustomer = Customer::create([
            'type' => 'regular',
            'full_name' => 'کەسێکی تر',
            'phone' => '07509998877',
            'email' => 'other@example.com',
            'password_hash' => Hash::make('pass'),
            'address' => 'سلێمانی',
        ]);

        $otherProduct = Product::create([
            'customer_id' => $otherCustomer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی کەسێکی تر',
            'description' => 'وەسف',
            'price' => 200000,
            'condition' => 'new',
            'status' => 'approved',
        ]);

        ProductImage::create([
            'product_id' => $otherProduct->id,
            'image_path' => 'products/other.jpg',
            'is_main' => true,
            'sort_order' => 0,
        ]);

        // Attempt edit view -> 403
        $this->actingAs($this->customer, 'customer')
            ->get("/my/products/{$otherProduct->id}/edit")
            ->assertStatus(403);

        // Attempt update -> 403
        $this->actingAs($this->customer, 'customer')
            ->put("/my/products/{$otherProduct->id}", [
                'category_id' => $this->category->id,
                'title' => 'هێرش',
                'description' => 'وەسف',
                'price' => 100000,
                'condition' => 'new',
            ])
            ->assertStatus(403);

        // Attempt delete -> 403
        $this->actingAs($this->customer, 'customer')
            ->delete("/my/products/{$otherProduct->id}")
            ->assertStatus(403);

        // Attempt mark sold -> 403
        $this->actingAs($this->customer, 'customer')
            ->post("/my/products/{$otherProduct->id}/sold")
            ->assertStatus(403);
    }

    public function test_deleting_sets_status_to_removed(): void
    {
        Storage::fake('public');

        $imagePath = 'products/123/img.jpg';
        Storage::disk('public')->put($imagePath, 'fake-content');

        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەم بۆ لابردن',
            'description' => 'وەسف',
            'price' => 150000,
            'condition' => 'new',
            'status' => 'approved',
        ]);

        $image = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $imagePath,
            'is_main' => true,
            'sort_order' => 0,
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->delete("/my/products/{$product->id}");

        $response->assertRedirect('/my/products');

        $product->refresh();
        $this->assertSame('removed', $product->status);

        // Row and image records must NOT be erased
        $this->assertDatabaseHas('product', ['id' => $product->id, 'status' => 'removed']);
        $this->assertDatabaseHas('product_image', ['id' => $image->id]);
        Storage::disk('public')->assertExists($imagePath);
    }

    public function test_mark_sold_only_works_on_approved_products(): void
    {
        $pendingProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی چاوەڕوانکراو',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'used',
            'status' => 'pending',
        ]);

        $this->actingAs($this->customer, 'customer')
            ->post("/my/products/{$pendingProduct->id}/sold")
            ->assertStatus(403);

        $approvedProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی پەسەندکراو',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'used',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->post("/my/products/{$approvedProduct->id}/sold");

        $response->assertRedirect('/my/products');

        $approvedProduct->refresh();
        $this->assertSame('sold', $approvedProduct->status);
        $this->assertNotNull($approvedProduct->sold_at);
    }

    public function test_main_photo_reassignment_when_main_photo_is_deleted(): void
    {
        Storage::fake('public');

        $path1 = 'products/999/p1.jpg';
        $path2 = 'products/999/p2.jpg';
        Storage::disk('public')->put($path1, 'img1');
        Storage::disk('public')->put($path2, 'img2');

        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'سڕینەوەی وێنەی سەرەکی',
            'description' => 'وەسف',
            'price' => 80000,
            'condition' => 'new',
            'status' => 'pending',
        ]);

        $img1 = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path1,
            'is_main' => true,
            'sort_order' => 0,
        ]);

        $img2 = ProductImage::create([
            'product_id' => $product->id,
            'image_path' => $path2,
            'is_main' => false,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->put("/my/products/{$product->id}", [
                'category_id' => $this->category->id,
                'title' => 'سڕینەوەی وێنەی سەرەکی',
                'description' => 'وەسف',
                'price' => 80000,
                'condition' => 'new',
                'delete_photos' => [$img1->id],
            ]);

        $response->assertRedirect('/my/products');

        // Image 1 must be erased from DB and disk
        $this->assertDatabaseMissing('product_image', ['id' => $img1->id]);
        Storage::disk('public')->assertMissing($path1);

        // Image 2 must now be marked as is_main = true
        $img2->refresh();
        $this->assertTrue($img2->is_main);
    }

    public function test_banned_customer_is_blocked_from_products(): void
    {
        $this->customer->update(['is_banned' => true]);

        $this->actingAs($this->customer, 'customer')
            ->get('/my/products')
            ->assertRedirect('/login');

        $this->actingAs($this->customer, 'customer')
            ->get('/my/products/create')
            ->assertRedirect('/login');
    }

    public function test_shared_form_view_renders_correctly_in_both_create_and_edit_mode(): void
    {
        // 1. Create mode
        $createResponse = $this->actingAs($this->customer, 'customer')
            ->get('/my/products/create');

        $createResponse->assertStatus(200);
        $createResponse->assertSee(__('products.create_title'));
        $createResponse->assertSee(__('products.save_btn'));
        $createResponse->assertDontSee(__('products.current_photos'));

        // 2. Edit mode
        $product = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'لاپتۆپ دێڵ ئێکس پی ئێس',
            'description' => 'زۆر خاوێنە',
            'price' => 950000,
            'condition' => 'used',
            'status' => 'pending',
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/sample-dell.jpg',
            'is_main' => true,
            'sort_order' => 0,
        ]);

        $editResponse = $this->actingAs($this->customer, 'customer')
            ->get("/my/products/{$product->id}/edit");

        $editResponse->assertStatus(200);
        $editResponse->assertSee(__('products.edit_title'));
        $editResponse->assertSee(__('products.update_btn'));
        $editResponse->assertSee('لاپتۆپ دێڵ ئێکس پی ئێس');
        $editResponse->assertSee(__('products.current_photos'));
    }

    public function test_my_products_list_excludes_removed_products(): void
    {
        $approvedProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی پەسەندکراو لە لیست',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'approved',
        ]);

        $removedProduct = Product::create([
            'customer_id' => $this->customer->id,
            'category_id' => $this->category->id,
            'title' => 'بەرهەمی لابروا لە لیست',
            'description' => 'وەسف',
            'price' => 100000,
            'condition' => 'new',
            'status' => 'removed',
        ]);

        $response = $this->actingAs($this->customer, 'customer')
            ->get('/my/products');

        $response->assertStatus(200);
        $response->assertSee('بەرهەمی پەسەندکراو لە لیست');
        $response->assertDontSee('بەرهەمی لابروا لە لیست');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_registration_page(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee(__('app.register'));
    }

    public function test_can_register_as_regular_customer(): void
    {
        $response = $this->post('/register', [
            'type' => 'regular',
            'full_name' => 'کاروان ئەحمەد',
            'phone' => ' 07501234567 ',
            'email' => 'karwan@example.com',
            'address' => 'هەولێر، بەختیاری',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/');

        $this->assertAuthenticatedAs(
            Customer::where('email', 'karwan@example.com')->first(),
            'customer'
        );

        $this->assertDatabaseHas('customer', [
            'type' => 'regular',
            'full_name' => 'کاروان ئەحمەد',
            'shop_name' => null,
            'logo' => null,
            'phone' => '07501234567',
            'email' => 'karwan@example.com',
            'address' => 'هەولێر، بەختیاری',
            'is_verified' => false,
            'is_banned' => false,
        ]);

        $customer = Customer::where('email', 'karwan@example.com')->first();
        $this->assertTrue(Hash::check('password123', $customer->password_hash));
    }

    public function test_can_register_as_shop_customer_with_logo(): void
    {
        Storage::fake('public');

        $logo = UploadedFile::fake()->image('shop_logo.png', 200, 200);

        $response = $this->post('/register', [
            'type' => 'shop',
            'full_name' => 'هێمن سەردار',
            'shop_name' => 'تەکنەلۆجیای کوردستان',
            'logo' => $logo,
            'phone' => '+9647509876543',
            'email' => 'shop@example.com',
            'address' => 'سلێمانی، شەقامی سەهۆڵەکە',
            'password' => 'securepass123',
            'password_confirmation' => 'securepass123',
        ]);

        $response->assertRedirect('/');

        $customer = Customer::where('email', 'shop@example.com')->first();
        $this->assertNotNull($customer);
        $this->assertSame('shop', $customer->type);
        $this->assertSame('تەکنەلۆجیای کوردستان', $customer->shop_name);
        $this->assertNotNull($customer->logo);
        $this->assertAuthenticatedAs($customer, 'customer');

        Storage::disk('public')->assertExists($customer->logo);
    }

    public function test_shop_registration_without_shop_name_fails(): void
    {
        $response = $this->post('/register', [
            'type' => 'shop',
            'full_name' => 'کاوە حەسەن',
            'shop_name' => '',
            'phone' => '07701234567',
            'email' => 'kawa@example.com',
            'address' => 'دهۆک',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $this->assertDatabaseMissing('customer', ['email' => 'kawa@example.com']);
    }

    public function test_regular_registration_with_shop_name_fails(): void
    {
        $response = $this->post('/register', [
            'type' => 'regular',
            'full_name' => 'ئاسۆ مەحمود',
            'shop_name' => 'فرۆشگای نایاسایی',
            'phone' => '07701234567',
            'email' => 'aso@example.com',
            'address' => 'هەولێر',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('shop_name');
        $this->assertDatabaseMissing('customer', ['email' => 'aso@example.com']);
    }

    public function test_duplicate_email_registration_fails(): void
    {
        Customer::create([
            'type' => 'regular',
            'full_name' => 'بەکاربەری یەکەم',
            'phone' => '07501111111',
            'email' => 'duplicate@example.com',
            'password_hash' => Hash::make('secret123'),
            'address' => 'هەولێر',
        ]);

        $response = $this->post('/register', [
            'type' => 'regular',
            'full_name' => 'بەکاربەری دووەم',
            'phone' => '07502222222',
            'email' => 'duplicate@example.com',
            'address' => 'سلێمانی',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_invalid_phone_number_fails(): void
    {
        $response = $this->post('/register', [
            'type' => 'regular',
            'full_name' => 'تەها عەلی',
            'phone' => '07501234', // too short
            'email' => 'taha@example.com',
            'address' => 'هەولێر',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('phone');
    }

    public function test_can_view_login_page(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee(__('app.login'));
    }

    public function test_can_login_with_correct_credentials(): void
    {
        $customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'دانا ڕەحیم',
            'phone' => '07503333333',
            'email' => 'dana@example.com',
            'password_hash' => Hash::make('mypassword'),
            'address' => 'سلێمانی',
        ]);

        $response = $this->post('/login', [
            'email' => 'dana@example.com',
            'password' => 'mypassword',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($customer, 'customer');
    }

    public function test_cannot_login_with_invalid_password(): void
    {
        Customer::create([
            'type' => 'regular',
            'full_name' => 'دانا ڕەحیم',
            'phone' => '07503333333',
            'email' => 'dana@example.com',
            'password_hash' => Hash::make('mypassword'),
            'address' => 'سلێمانی',
        ]);

        $response = $this->post('/login', [
            'email' => 'dana@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest('customer');
    }

    public function test_banned_customer_cannot_login_even_with_correct_password(): void
    {
        Customer::create([
            'type' => 'regular',
            'full_name' => 'باندکراو',
            'phone' => '07504444444',
            'email' => 'banned@example.com',
            'password_hash' => Hash::make('correctpassword'),
            'address' => 'هەولێر',
            'is_banned' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'banned@example.com',
            'password' => 'correctpassword',
        ]);

        $response->assertSessionHasErrors(['email' => __('auth.banned')]);
        $this->assertGuest('customer');
    }

    public function test_customer_logout_works(): void
    {
        $customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'سەردار فەتاح',
            'phone' => '07505555555',
            'email' => 'sardar@example.com',
            'password_hash' => Hash::make('secret123'),
            'address' => 'دهۆک',
        ]);

        $this->actingAs($customer, 'customer');
        $this->assertAuthenticated('customer');

        $response = $this->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest('customer');
    }

    public function test_authenticated_customer_visiting_guest_routes_is_redirected_to_home(): void
    {
        $customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'بەختیار کەریم',
            'phone' => '07506666666',
            'email' => 'bakhtiar@example.com',
            'password_hash' => Hash::make('secret123'),
            'address' => 'هەولێر',
        ]);

        $this->actingAs($customer, 'customer');

        $loginResponse = $this->get('/login');
        $loginResponse->assertRedirect('/');

        $registerResponse = $this->get('/register');
        $registerResponse->assertRedirect('/');
    }

    public function test_banned_customer_is_logged_out_by_middleware_on_protected_routes(): void
    {
        $customer = Customer::create([
            'type' => 'regular',
            'full_name' => 'مەریوان سەعید',
            'phone' => '07507777777',
            'email' => 'mariwan@example.com',
            'password_hash' => Hash::make('secret123'),
            'address' => 'سلێمانی',
            'is_banned' => true, // Banned while having session
        ]);

        $this->actingAs($customer, 'customer');

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest('customer');
    }
}

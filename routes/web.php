<?php

use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ProductBrowseController;
use App\Http\Controllers\Public\ShopController;
use App\Http\Controllers\Staff\AuthController as StaffAuthController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\ProductReviewController as StaffProductReviewController;
use App\Http\Controllers\Staff\StaffUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductBrowseController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductBrowseController::class, 'show'])->name('products.show');
Route::get('/shops/{customerId}', [ShopController::class, 'show'])->name('shops.show');

/*
|--------------------------------------------------------------------------
| Customer Guest Routes (Login & Registration)
|--------------------------------------------------------------------------
*/
Route::middleware('customer.guest')->group(function () {
    Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register']);

    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Customer Authenticated Routes (Customer Guard + Active / Not Banned)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:customer', 'customer.not_banned'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');

    Route::prefix('my/products')->name('customer.products.')->group(function () {
        Route::get('/', [CustomerProductController::class, 'index'])->name('index');
        Route::get('/create', [CustomerProductController::class, 'create'])->name('create');
        Route::post('/create', [CustomerProductController::class, 'store'])->name('store.create');
        Route::post('/', [CustomerProductController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [CustomerProductController::class, 'edit'])->name('edit');
        Route::put('/{id}/edit', [CustomerProductController::class, 'update'])->name('update.edit');
        Route::put('/{id}', [CustomerProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [CustomerProductController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/sold', [CustomerProductController::class, 'markSold'])->name('sold');
    });
});

/*
|--------------------------------------------------------------------------
| Staff Guest Routes (Login)
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->middleware('staff.guest')->group(function () {
    Route::get('/login', [StaffAuthController::class, 'showLoginForm'])->name('staff.login');
    Route::post('/login', [StaffAuthController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Staff Authenticated Routes (Staff Guard + Active Account)
|--------------------------------------------------------------------------
*/
Route::prefix('staff')->middleware(['auth:staff', 'staff.active'])->name('staff.')->group(function () {
    Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');
    Route::get('/', [StaffDashboardController::class, 'index'])->name('dashboard');

    // Listing Reviews (Admin & Subadmin)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [StaffProductReviewController::class, 'index'])->name('index');
        Route::get('/{id}', [StaffProductReviewController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [StaffProductReviewController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [StaffProductReviewController::class, 'reject'])->name('reject');
    });

    // Staff Management (Admin only)
    Route::prefix('users')->name('users.')->middleware('staff.role:admin')->group(function () {
        Route::get('/', [StaffUserController::class, 'index'])->name('index');
        Route::get('/create', [StaffUserController::class, 'create'])->name('create');
        Route::post('/', [StaffUserController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [StaffUserController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StaffUserController::class, 'update'])->name('update');
    });
});

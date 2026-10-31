<?php

use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
use App\Http\Controllers\Customer\ProductController as CustomerProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('public.home');
})->name('home');

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

<?php

use App\Http\Controllers\Customer\AuthController as CustomerAuthController;
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
| Customer Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:customer', 'customer.not_banned'])->group(function () {
    Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
});

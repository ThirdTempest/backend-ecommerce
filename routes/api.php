<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OtpController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\WebhookController; // Import this

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES (No Login Required)
|--------------------------------------------------------------------------
*/

// Shop & Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/new-arrivals', [ProductController::class, 'newArrivals']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/otp/verify', [OtpController::class, 'verify']);

// Contact
Route::post('/contact', [ContactController::class, 'store']);

// PAYMONGO WEBHOOK (Must be public)
Route::post('/paymongo/webhook', [WebhookController::class, 'handle']);
Route::post('/otp/resend', [App\Http\Controllers\Api\OtpController::class, 'resend']);

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES (Login Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum'])->group(function () {

    // User Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Checkout
    Route::post('/checkout', [CheckoutController::class, 'store']);

    // User Orders & History
    Route::get('/user/orders', [App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::get('/user/orders/recent', [App\Http\Controllers\Api\OrderController::class, 'recent']);
    Route::post('/user/orders/{id}/cancel', [App\Http\Controllers\Api\OrderController::class, 'cancel']);

    // User Addresses
    Route::get('/user/addresses', [App\Http\Controllers\Api\AddressController::class, 'index']);

    // ADMIN ROUTES
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\Api\Admin\DashboardController::class, 'stats']);

        // Products
        Route::get('/products', [App\Http\Controllers\Api\Admin\ProductController::class, 'index']);
        Route::post('/products', [App\Http\Controllers\Api\Admin\ProductController::class, 'store']);
        Route::get('/products/low-stock', [App\Http\Controllers\Api\Admin\ProductController::class, 'lowStock']);
        Route::get('/products/{id}', [App\Http\Controllers\Api\Admin\ProductController::class, 'show']);
        // Use match to allow POST or PUT for updates (files require POST with _method:PUT)
        Route::match(['put', 'post'], '/products/{id}', [App\Http\Controllers\Api\Admin\ProductController::class, 'update']);

        // Orders
        Route::get('/orders', [App\Http\Controllers\Api\Admin\OrderController::class, 'index']);
        Route::get('/orders/{id}', [App\Http\Controllers\Api\Admin\OrderController::class, 'show']);
        Route::put('/orders/{id}/status', [App\Http\Controllers\Api\Admin\OrderController::class, 'updateStatus']);
    });
});

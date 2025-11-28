<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\AdminController; 

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- Public/User Routes ---
Route::controller(ShopController::class)->group(function () {
    // Home Page
    Route::get('/', 'index')->name('home');

    // Shop Pages
    Route::get('/shop', 'shopAll')->name('shop');
    Route::get('/sale', 'saleProducts')->name('sale');
    Route::get('/new-arrivals', 'newArrivalsProducts')->name('new.arrivals');

    // Utility Pages (Contact Routes)
    Route::get('/contact', 'contact')->name('contact'); 
    Route::post('/contact', 'storeContact')->name('contact.store'); 
    Route::get('/terms', 'termsOfService')->name('legal.terms');
    Route::get('/privacy', 'privacyPolicy')->name('legal.privacy');
    Route::get('/accessibility', 'accessibilityStatement')->name('legal.accessibility'); 

    // Authentication Views (GET)
    Route::middleware('guest')->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::get('/register', 'register')->name('register');
        
        // OTP Verification Routes
        Route::get('/verify-otp', 'showOtpForm')->name('otp.verify');
        Route::post('/verify-otp', 'verifyOtp')->name('otp.check');
    });
    
    // Protected User Routes (Requires Auth)
    Route::middleware('auth')->group(function () {
        Route::get('/profile', 'profile')->name('profile');
        Route::post('/logout', 'logout')->name('logout');
        
        // --- PROFILE SUB-PAGES ---
        Route::get('/profile/orders', 'orderHistory')->name('profile.orders');
        Route::get('/profile/addresses', 'savedAddresses')->name('profile.addresses');
        
        // Cart Functionality Routes (UPDATED)
        Route::post('/cart/add', 'addToCart')->name('cart.add'); 
        Route::post('/cart/remove', 'removeFromCart')->name('cart.remove'); 
        Route::post('/cart/update', 'updateCartQuantity')->name('cart.update');
        Route::get('/cart', 'viewCart')->name('cart.view'); 
        
        // Checkout Flow
        Route::get('/checkout/shipping', 'showShippingForm')->name('checkout.showForm'); 
        Route::post('/checkout/initiate', 'initiatePayment')->name('checkout.initiate'); 
        Route::get('/checkout/success', 'checkoutSuccess')->name('checkout.success'); 
        Route::get('/checkout/failure', 'checkoutFailure')->name('checkout.failure'); 
        
        // Order Cancellation (NEW ROUTE)
        Route::post('/order/{order}/cancel', 'cancelOrder')->name('order.cancel'); 
    });

    // Authentication Logic (POST)
    Route::post('/login', 'storeLogin')->name('login.store');
    Route::post('/register', 'storeRegister')->name('register.store');
});


// --- Admin Panel Routes (Requires Auth) ---
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard');
        
        // Product Management
        Route::get('/products', 'indexProducts')->name('products.index'); 
        Route::get('/products/create', 'createProduct')->name('products.create');
        Route::post('/products', 'storeProduct')->name('products.store');
        Route::get('/products/{product}/edit', 'editProduct')->name('products.edit'); 
        Route::put('/products/{product}', 'updateProduct')->name('products.update'); 
        
        // Low Stock Alerts
        Route::get('/products/low-stock', 'lowStockAlerts')->name('products.lowStock'); 
        
        // Order Management (UPDATED)
        Route::get('/orders', 'indexOrders')->name('orders.index');
        Route::get('/orders/{order}', 'showOrder')->name('orders.show'); 
        Route::put('/orders/{order}/status', 'updateOrderStatus')->name('orders.updateStatus');
    });
});
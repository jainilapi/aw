<?php

use Illuminate\Support\Facades\Route;

Route::match(['GET', 'POST'], 'login', [\App\Http\Controllers\Frontend\AuthController::class, 'login'])->name('login');
Route::match(['GET', 'POST'], 'register', [\App\Http\Controllers\Frontend\AuthController::class, 'register'])->name('register');
Route::get('verify-email/{token}', [\App\Http\Controllers\Frontend\AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('logout', [\App\Http\Controllers\Frontend\AuthController::class, 'logout'])->name('logout');

Route::get('/', [\App\Http\Controllers\Frontend\HomeController::class, 'index'])->name('home');
Route::get('categories', [\App\Http\Controllers\Frontend\HomeController::class, 'categories'])->name('categories');
Route::get('products', [\App\Http\Controllers\Frontend\HomeController::class, 'products'])->name('products');
Route::get('p/{id}/{slug}/{variant?}', [\App\Http\Controllers\Frontend\HomeController::class, 'details'])->name('product.detail');
Route::get('cart', [\App\Http\Controllers\Frontend\HomeController::class, 'cart'])->name('cart');
Route::get('/s', [\App\Http\Controllers\Frontend\HomeController::class, 'search'])->name('search');

Route::get('api/product-pricing', [\App\Http\Controllers\Frontend\HomeController::class, 'productPricing'])->name('api.product.pricing');
Route::post('api/cart/add', [\App\Http\Controllers\Frontend\HomeController::class, 'addToCart'])->name('api.cart.add');
Route::post('api/cart/update', [\App\Http\Controllers\Frontend\HomeController::class, 'updateCartItem'])->name('api.cart.update');
Route::post('api/cart/remove', [\App\Http\Controllers\Frontend\HomeController::class, 'removeFromCart'])->name('api.cart.remove');
Route::post('api/wishlist/add', [\App\Http\Controllers\Frontend\HomeController::class, 'addToWishlist'])->name('api.wishlist.add');
Route::post('api/wishlist/remove', [\App\Http\Controllers\Frontend\HomeController::class, 'removeFromWishlist'])->name('api.wishlist.remove');
Route::get('api/wishlist/check', [\App\Http\Controllers\Frontend\HomeController::class, 'checkWishlist'])->name('api.wishlist.check');
Route::get('api/cart/data', [\App\Http\Controllers\Frontend\HomeController::class, 'getCartData'])->name('api.cart.data');
Route::get('api/cart/items', [\App\Http\Controllers\Frontend\HomeController::class, 'getCartItems'])->name('api.cart.items');
Route::get('api/cart/coupons', [\App\Http\Controllers\Frontend\HomeController::class, 'getAvailableCoupons'])->name('api.cart.coupons');
Route::post('api/cart/apply-coupon', [\App\Http\Controllers\Frontend\HomeController::class, 'applyCoupon'])->name('api.cart.apply-coupon');
Route::post('api/cart/remove-coupon', [\App\Http\Controllers\Frontend\HomeController::class, 'removeCoupon'])->name('api.cart.remove-coupon');
Route::post('api/sync', [\App\Http\Controllers\Frontend\HomeController::class, 'syncCartAndWishlist'])->name('api.sync');
Route::post('checkout/place-order', [\App\Http\Controllers\Frontend\HomeController::class, 'placeOrder'])->name('checkout.place-order');
Route::get('order/success/{id}', [\App\Http\Controllers\Frontend\HomeController::class, 'orderSuccess'])->name('order.success');
Route::get('order/invoice/{id}', [\App\Http\Controllers\Frontend\HomeController::class, 'downloadInvoice'])->name('order.invoice');

// Currency selection
Route::post('api/set-currency', [\App\Http\Controllers\Frontend\HomeController::class, 'setCurrency'])->name('api.set-currency');

Route::middleware(['auth:customer'])->group(function () {
    Route::get('switch-account', [\App\Http\Controllers\Frontend\AuthController::class, 'switchAccount'])->name('switch-account');
    Route::get('remove-account/{id}', [\App\Http\Controllers\Frontend\AuthController::class, 'removeAccount'])->name('remove-account');
    Route::get('add-new-account', [\App\Http\Controllers\Frontend\AuthController::class, 'addNewAccount'])->name('add-new-account');

    Route::get('wishlist', [\App\Http\Controllers\Frontend\CustomerController::class, 'wishlist'])->name('customer.wishlist');
    Route::get('addresses', [\App\Http\Controllers\Frontend\CustomerController::class, 'addresses'])->name('customer.addresses');
    Route::post('addresses', [\App\Http\Controllers\Frontend\CustomerController::class, 'storeAddress'])->name('customer.addresses.store');
    Route::put('addresses/{id}', [\App\Http\Controllers\Frontend\CustomerController::class, 'updateAddress'])->name('customer.addresses.update');
    Route::delete('addresses/{id}', [\App\Http\Controllers\Frontend\CustomerController::class, 'deleteAddress'])->name('customer.addresses.delete');
    Route::get('profile', [\App\Http\Controllers\Frontend\CustomerController::class, 'profile'])->name('customer.profile');
    Route::post('profile', [\App\Http\Controllers\Frontend\CustomerController::class, 'updateProfile'])->name('customer.profile.update');

    Route::get('dashboard', [\App\Http\Controllers\Frontend\CustomerController::class, 'dashboard'])->name('customer.dashboard');
    Route::get('api/dashboard/stats', [\App\Http\Controllers\Frontend\CustomerController::class, 'getDashboardStats'])->name('customer.dashboard.stats');
    Route::get('orders', [\App\Http\Controllers\Frontend\CustomerController::class, 'orders'])->name('customer.orders');
    Route::get('orders/data', [\App\Http\Controllers\Frontend\CustomerController::class, 'ordersData'])->name('customer.orders.data');
    Route::get('orders/{id}', [\App\Http\Controllers\Frontend\CustomerController::class, 'orderDetail'])->name('customer.order.detail');
});

Route::post('state-list', [\App\Helpers\Helper::class, 'getStatesByCountry'])->name('state-list');
Route::post('city-list', [\App\Helpers\Helper::class, 'getCitiesByState'])->name('city-list');
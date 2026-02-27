<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::redirect('admin', 'admin/login');

Route::prefix('admin')->group(function () {
    Route::match(['GET', 'POST'], 'login', [LoginController::class, 'login'])->name('admin.login');
    Route::post('logout', [LoginController::class, 'logout'])->name('admin.logout');
});

Route::prefix('admin')->middleware(['admin.auth', 'permission'])->group(function () {
    Route::get('dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('notification-templates', \App\Http\Controllers\NotificationTemplateController::class);
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::post('customers/{id}/credit', [\App\Http\Controllers\CustomerController::class, 'updateCredit'])->name('customers.credit.update');
    Route::get('customers/{id}/credit-logs', [\App\Http\Controllers\CustomerController::class, 'getCreditLogs'])->name('customers.credit.logs');
    Route::resource('suppliers', \App\Http\Controllers\SupplierController::class);
    Route::post('suppliers/import', [\App\Http\Controllers\SupplierController::class, 'import'])->name('suppliers.import');
    Route::resource('locations', \App\Http\Controllers\WarehouseLocationController::class);
    Route::resource('customer-locations', \App\Http\Controllers\LocationController::class);
    Route::resource('warehouses', \App\Http\Controllers\WarehouseController::class);
    Route::resource('categories', \App\Http\Controllers\CategoryController::class);
    Route::resource('products', \App\Http\Controllers\ProductController::class);
    Route::resource('brands', \App\Http\Controllers\BrandController::class);

    Route::resource('promotions', \App\Http\Controllers\PromotionController::class);
    Route::post('promotions/categories', [\App\Http\Controllers\PromotionController::class, 'getCategories'])->name('promotions.categories');
    Route::post('promotions/products', [\App\Http\Controllers\PromotionController::class, 'getProducts'])->name('promotions.products');
    Route::get('promotions/variants/{productId}', [\App\Http\Controllers\PromotionController::class, 'getVariants'])->name('promotions.variants');
    Route::get('promotions/units/variant/{variantId}', [\App\Http\Controllers\PromotionController::class, 'getUnits'])->name('promotions.units');
    Route::get('promotions/units/simple/{productId}', [\App\Http\Controllers\PromotionController::class, 'getSimpleProductUnits'])->name('promotions.simple-units');
    Route::get('promotions-usage-report', [\App\Http\Controllers\PromotionReportController::class, 'index'])->name('promotions.usage.report');
    Route::post('reports/promotion-usage/export', [\App\Http\Controllers\PromotionReportController::class, 'export'])->name('reports.promotion-usage.export');

    Route::resource('orders', \App\Http\Controllers\OrderController::class);
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::post('update-status/{order}', [\App\Http\Controllers\OrderController::class, 'updateStatus'])->name('update-status');
        Route::post('bulk-update-status', [\App\Http\Controllers\OrderController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
        Route::post('{order}/items', [\App\Http\Controllers\OrderController::class, 'addItem'])->name('add-item');
        Route::put('{order}/items/{item}', [\App\Http\Controllers\OrderController::class, 'updateItem'])->name('update-item');
        Route::delete('{order}/items/{item}', [\App\Http\Controllers\OrderController::class, 'removeItem'])->name('remove-item');
        Route::get('customer-locations/{customerId}', [\App\Http\Controllers\OrderController::class, 'getCustomerLocations'])->name('customer-locations');
        Route::get('export', [\App\Http\Controllers\OrderController::class, 'export'])->name('export');
    });

    Route::post('orders-customers', [\App\Http\Controllers\OrderController::class, 'getCustomers'])->name('orders.customers');
    Route::post('orders-products', [\App\Http\Controllers\OrderController::class, 'getProducts'])->name('orders.products');
    Route::get('orders/product-units/{productId}', [\App\Http\Controllers\OrderController::class, 'getProductUnits'])->name('orders.product-units');
    Route::get('orders/product-variants/{productId}', [\App\Http\Controllers\OrderController::class, 'getProductVariants'])->name('orders.product-variants');
    Route::get('orders/variant-units/{variantId}', [\App\Http\Controllers\OrderController::class, 'getVariantUnits'])->name('orders.variant-units');
    Route::get('orders/bundle-price/{productId}', [\App\Http\Controllers\OrderController::class, 'getBundlePrice'])->name('orders.bundle-price');
    Route::post('orders-warehouses', [\App\Http\Controllers\OrderController::class, 'getWarehouses'])->name('orders.warehouses');
    Route::post('orders-available-stock', [\App\Http\Controllers\OrderController::class, 'getAvailableStock'])->name('orders.available-stock');

    Route::any('product-management/{type?}/{step?}/{id?}', [\App\Http\Controllers\ProductController::class, 'steps'])->name('product-management');
    Route::match(['GET', 'POST'], 'get-variant-stock-history', [\App\Http\Controllers\VariableProductController::class, 'getVariantStockHistory'])->name('products.get-variant-stock-history');
    Route::match(['GET', 'POST'], 'adjust-stock', [\App\Http\Controllers\VariableProductController::class, 'adjustStock'])->name('products.adjust-stock');

    // Bundle product management (Step 2/3 dynamic UI)
    Route::get('bundle-products/search', [\App\Http\Controllers\BundledProductController::class, 'searchProducts'])->name('bundle-products.search');
    Route::get('bundle-products/{product}/variants', [\App\Http\Controllers\BundledProductController::class, 'variants'])->name('bundle-products.variants');
    Route::get('bundle-products/{product}/units', [\App\Http\Controllers\BundledProductController::class, 'units'])->name('bundle-products.units');
    Route::post('bundle-products/item-price', [\App\Http\Controllers\BundledProductController::class, 'itemPrice'])->name('bundle-products.item-price');

    Route::post('brand-list', [\App\Helpers\Helper::class, 'getBrands'])->name('brand-list');
    Route::post('product-image-delete', [\App\Http\Controllers\ProductController::class, 'deleteImage'])->name('product-image-delete');

    Route::get('settings', [App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Currency Management
    Route::resource('currencies', \App\Http\Controllers\CurrencyController::class);
    Route::post('currencies/{id}/toggle-status', [\App\Http\Controllers\CurrencyController::class, 'toggleStatus'])->name('currencies.toggle-status');
    
    Route::resource('tax-slabs', \App\Http\Controllers\TaxSlabController::class);

    Route::get('home-page-settings', [App\Http\Controllers\HomePageSettingController::class, 'index'])->name('home-page-settings.index');
    Route::post('home-page-settings/reorder', [App\Http\Controllers\HomePageSettingController::class, 'reorder'])->name('home-page-settings.reorder');
    Route::post('home-page-settings/{key}', [App\Http\Controllers\HomePageSettingController::class, 'update'])->name('home-page-settings.update');

    Route::get('/inventory/history/{productId}/{warehouseId}', [\App\Http\Controllers\ProductController::class, 'getHistory']);
    Route::post('/inventory/adjust', [\App\Http\Controllers\ProductController::class, 'adjust'])->name('inventory.adjust');
    Route::get('search-substitutes', [\App\Http\Controllers\ProductController::class, 'searchSubstitutes'])->name('search-substitutes');

    Route::post('products/import', [\App\Http\Controllers\ProductController::class, 'import'])->name('products.import');
    Route::post('products/importInventory', [\App\Http\Controllers\ProductController::class, 'importInventory'])->name('products.import-inventory');
    Route::get('import-history', [\App\Http\Controllers\ProductController::class, 'getImportHistory'])->name('import-history');
});

Route::post('state-list', [\App\Helpers\Helper::class, 'getStatesByCountry'])->name('state-list');
Route::post('city-list', [\App\Helpers\Helper::class, 'getCitiesByState'])->name('city-list');
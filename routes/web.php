<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ExpenseController;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PayUController;
use App\Http\Controllers\CustomerAuthController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/place-order', [HomeController::class, 'placeOrder'])->name('home.store');
Route::get('/order/{order_number}/success', [HomeController::class, 'orderSuccess'])->name('home.orderSuccess');
Route::get('/order/{order_number}/check-status', [HomeController::class, 'checkStatus'])->name('home.checkStatus');
Route::match(['get', 'post'], '/payu/pay/{order_number}', [PayUController::class, 'pay'])->name('payu.pay');
Route::match(['get', 'post'], '/payu/success', [PayUController::class, 'success'])->name('payu.success');
Route::match(['get', 'post'], '/payu/failure', [PayUController::class, 'failure'])->name('payu.failure');

Route::get('/fix-storage', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'Storage link created! You can now check your images.';
});

Route::get('/run-migrate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ["--force" => true]);
    return 'Migrations executed successfully!';
});

Route::get('/optimize', function () {
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('route:cache');
    \Illuminate\Support\Facades\Artisan::call('view:cache');
    return 'Application optimized successfully! (Config, Route, and View cache created)';
});

Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'All cache cleared successfully!';
});

// Customer Auth & History
Route::post('/customer/check-phone', [CustomerAuthController::class, 'checkPhone'])->name('customer.checkPhone');
Route::post('/customer/login', [CustomerAuthController::class, 'login'])->name('customer.login');
Route::post('/customer/auto-login', [CustomerAuthController::class, 'autoLogin'])->name('customer.autoLogin');
Route::get('/customer/reorder/{order_number}', [CustomerAuthController::class, 'reorder'])->name('customer.reorder');
Route::get('/customer/orders', [CustomerAuthController::class, 'myOrders'])->name('customer.orders');
Route::get('/customer/profile', [CustomerAuthController::class, 'profile'])->name('customer.profile');
Route::post('/customer/address/save', [CustomerAuthController::class, 'saveAddress'])->name('customer.address.save');
Route::delete('/customer/address/{id}', [CustomerAuthController::class, 'deleteAddress'])->name('customer.address.delete');
Route::post('/customer/address/{id}/default', [CustomerAuthController::class, 'setDefaultAddress'])->name('customer.address.default');
Route::get('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

Route::middleware('auth')->prefix('cp')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::post('items/bulk-upload', [ItemController::class, 'bulkUpload'])->name('items.bulkUpload');
    Route::get('items/sample-csv', [ItemController::class, 'sampleCsv'])->name('items.sampleCsv');
    
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('items', ItemController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('customers', CustomerController::class)->except(['create', 'show', 'edit']);
    Route::resource('expenses', ExpenseController::class)->except(['create', 'show', 'edit']);
    
    Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit']);
    Route::post('coupons/check', [CouponController::class, 'check'])->name('coupons.check');
    
    Route::get('pos', [OrderController::class, 'create'])->name('pos.index');
    Route::post('pos/store', [OrderController::class, 'store'])->name('pos.store');
    
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/check-pending', [OrderController::class, 'checkPending'])->name('orders.check-pending');
    Route::get('orders/{id}/invoice', [OrderController::class, 'printInvoice'])->name('orders.invoice');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    
    Route::get('payments', [OrderController::class, 'payments'])->name('payments.index');
    Route::get('payments/export', [OrderController::class, 'exportPayments'])->name('payments.export');
    Route::get('reports', [OrderController::class, 'reports'])->name('reports.index');
    Route::get('logs', [AdminController::class, 'logs'])->name('logs.index');
    Route::resource('users', UserAdminController::class)->except(['create', 'show', 'edit']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings', [\App\Http\Controllers\TenantSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\TenantSettingsController::class, 'update'])->name('settings.update');
    
    Route::get('/password', [\App\Http\Controllers\AdminProfileController::class, 'editPassword'])->name('admin.password');
});

require __DIR__.'/auth.php';
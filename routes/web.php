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
use App\Http\Controllers\PayUController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/place-order', [HomeController::class, 'placeOrder'])->name('home.store');
Route::get('/order/{order_number}/success', [HomeController::class, 'orderSuccess'])->name('home.orderSuccess');
Route::get('/order/{order_number}/check-status', [HomeController::class, 'checkStatus'])->name('home.checkStatus');
Route::post('/payu/pay/{order_number}', [PayUController::class, 'pay'])->name('payu.pay');
Route::match(['get', 'post'], '/payu/success', [PayUController::class, 'success'])->name('payu.success');
Route::match(['get', 'post'], '/payu/failure', [PayUController::class, 'failure'])->name('payu.failure');

Route::middleware('auth')->prefix('cp')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::post('items/bulk-upload', [ItemController::class, 'bulkUpload'])->name('items.bulkUpload');
    Route::get('items/sample-csv', [ItemController::class, 'sampleCsv'])->name('items.sampleCsv');
    Route::resource('items', ItemController::class)->except(['create', 'show', 'edit']);
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
    Route::get('reports', [OrderController::class, 'reports'])->name('reports.index');
    Route::get('logs', [AdminController::class, 'logs'])->name('logs.index');
    Route::resource('users', UserAdminController::class)->except(['create', 'show', 'edit']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
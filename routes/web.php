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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/place-order', [HomeController::class, 'placeOrder'])->name('home.store');

Route::middleware('auth')->prefix('cp')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    Route::resource('items', ItemController::class)->except(['create', 'show', 'edit']);
    Route::resource('customers', CustomerController::class)->except(['create', 'show', 'edit']);
    Route::resource('expenses', ExpenseController::class)->except(['create', 'show', 'edit']);
    
    Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit']);
    Route::post('coupons/check', [CouponController::class, 'check'])->name('coupons.check');
    
    Route::get('pos', [OrderController::class, 'create'])->name('pos.index');
    Route::post('pos/store', [OrderController::class, 'store'])->name('pos.store');
    
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
    Route::get('orders/{id}/invoice', [OrderController::class, 'printInvoice'])->name('orders.invoice');
    
    Route::get('payments', [OrderController::class, 'payments'])->name('payments.index');
    Route::get('reports', [OrderController::class, 'reports'])->name('reports.index');
    Route::get('orders/check-pending', [OrderController::class, 'checkPending'])->name('orders.check-pending');
    Route::get('logs', [AdminController::class, 'logs'])->name('logs.index');
    Route::resource('users', UserAdminController::class)->except(['create', 'show', 'edit']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
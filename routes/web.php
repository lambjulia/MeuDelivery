<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryDriverController;
use App\Http\Controllers\DeliveryZoneController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Customers
    Route::get('/customers/search', [CustomerController::class, 'search'])->name('customers.search');
    Route::resource('customers', CustomerController::class)->except(['show']);

    // Catalog
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('products', ProductController::class)->except(['show']);

    // Orders
    Route::get('/orders/dispatch-board', [OrderController::class, 'dispatchBoard'])->name('orders.dispatch-board');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('/orders/{order}/assign-driver', [OrderController::class, 'assignDriver'])->name('orders.assign-driver');
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::resource('orders', OrderController::class)->except(['edit', 'update', 'destroy']);

    // Delivery
    Route::resource('delivery-zones', DeliveryZoneController::class)->except(['create', 'show', 'edit']);
    Route::resource('delivery-drivers', DeliveryDriverController::class)->except(['create', 'show', 'edit']);

    // Coupons & Expenses
    Route::resource('coupons', CouponController::class)->except(['create', 'show', 'edit']);
    Route::resource('expenses', ExpenseController::class)->except(['create', 'show', 'edit']);

    // Settings
    Route::get('/settings/company', [CompanySettingsController::class, 'show'])->name('settings.company');
    Route::match(['put', 'patch'], '/settings/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');
});

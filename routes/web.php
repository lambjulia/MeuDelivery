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
use App\Http\Controllers\Store\PublicOrderController;
use App\Http\Controllers\Store\PublicProductController;
use App\Http\Controllers\Store\PublicTrackingController;
use App\Http\Controllers\Store\StorefrontController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ─── Public Store Routes ───────────────────────────────────────────────────
Route::prefix('store/{companySlug}')->name('store.')->group(function () {
    Route::get('/',                        [StorefrontController::class, 'home'])->name('home');
    Route::get('/cart',                    [StorefrontController::class, 'cart'])->name('cart');
    Route::get('/checkout',                [StorefrontController::class, 'checkout'])->name('checkout');
    Route::get('/success/{orderCode}',     [StorefrontController::class, 'success'])->name('success');
    Route::get('/track/{orderCode}',       [PublicTrackingController::class, 'show'])->name('tracking');
    Route::get('/product/{productSlug}',   [PublicProductController::class, 'show'])->name('product');
});

// Public API for store (no auth)
Route::prefix('api/store/{companySlug}')->name('api.store.')->middleware('throttle:60,1')->group(function () {
    Route::post('/orders',          [PublicOrderController::class, 'store'])->name('orders.store');
    Route::post('/validate-coupon', [PublicOrderController::class, 'validateCoupon'])->name('validate-coupon');
    Route::get('/track/{orderCode}', [PublicTrackingController::class, 'poll'])->name('track.poll');
});

// ─── Admin Routes ──────────────────────────────────────────────────────────
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


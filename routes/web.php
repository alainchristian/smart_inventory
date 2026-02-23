<?php

use App\Http\Controllers\Auth\LoginRedirectController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\ShopManager\DashboardController as ShopDashboardController;
use App\Http\Controllers\WarehouseManager\DashboardController as WarehouseDashboardController;
use App\Http\Middleware\CheckLocation;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// QR Code Test Route (for troubleshooting)
Route::get('/qr-test', function () {
    return view('qr-code-test');
})->name('qr.test');

// Mobile Scanner (public access for phone scanning)
Route::get('/scanner', function () {
    return view('scanner.mobile');
})->name('scanner.mobile');

// Authentication routes
require __DIR__.'/auth.php';

// Post-login redirect handler
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [LoginRedirectController::class, 'redirect'])->name('dashboard');
});

// Owner routes - Full system access
Route::middleware(['auth', CheckRole::class . ':owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::get('/dashboard', [OwnerDashboardController::class, 'index'])->name('dashboard');

    // User management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function () { return view('owner.users.index'); })->name('index');
        Route::get('/create', function () { return view('owner.users.create'); })->name('create');
        Route::get('/{user}/edit', function ($user) { return view('owner.users.edit', compact('user')); })->name('edit');
    });

    // Location management
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', function () { return view('owner.warehouses.index'); })->name('index');
        Route::get('/create', function () { return view('owner.warehouses.create'); })->name('create');
    });

    Route::prefix('shops')->name('shops.')->group(function () {
        Route::get('/', function () { return view('owner.shops.index'); })->name('index');
        Route::get('/create', function () { return view('owner.shops.create'); })->name('create');
    });

    // Products (owner can manage all)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', function () { return view('owner.products.index'); })->name('index');
        Route::get('/create', function () { return view('owner.products.create'); })->name('create');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/inventory', function () { return view('owner.reports.inventory'); })->name('inventory');
        Route::get('/sales', function () { return view('owner.reports.sales'); })->name('sales');
        Route::get('/transfers', function () { return view('owner.reports.transfers'); })->name('transfers');
    });

    // System settings
    Route::get('/settings', function () { return view('owner.settings'); })->name('settings');
});

// Warehouse Manager routes - Allow warehouse managers and owners
Route::middleware(['auth', CheckRole::class . ':warehouse_manager,owner', CheckLocation::class])
    ->prefix('warehouse')
    ->name('warehouse.')
    ->group(function () {
        Route::get('/dashboard', [WarehouseDashboardController::class, 'index'])->name('dashboard');

        // Inventory management
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/boxes', function () { return view('warehouse.inventory.boxes'); })->name('boxes');
            Route::get('/boxes/receive', function () { return view('warehouse.inventory.receive-boxes'); })->name('receive-boxes');
            Route::get('/stock-levels', function () { return view('warehouse.inventory.stock-levels'); })->name('stock-levels');
        });

        // Transfers
        Route::prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/', function () { return view('warehouse.transfers.index'); })->name('index');
            Route::get('/{transfer}', function (\App\Models\Transfer $transfer) { return view('warehouse.transfers.show', compact('transfer')); })->name('show');
            Route::get('/{transfer}/pack', function (\App\Models\Transfer $transfer) { return view('warehouse.transfers.pack', compact('transfer')); })->name('pack');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/inventory', function () { return view('warehouse.reports.inventory'); })->name('inventory');
            Route::get('/transfers', function () { return view('warehouse.reports.transfers'); })->name('transfers');
        });
    });

// Shop Manager routes - Allow shop managers and owners
Route::middleware(['auth', CheckRole::class . ':shop_manager,owner', CheckLocation::class])
    ->prefix('shop')
    ->name('shop.')
    ->group(function () {
        Route::get('/dashboard', [ShopDashboardController::class, 'index'])->name('dashboard');

        // Point of Sale
        Route::get('/pos', function () { return view('shop.pos'); })->name('pos');

        // Sales
        Route::prefix('sales')->name('sales.')->group(function () {
            Route::get('/', function () { return view('shop.sales.index'); })->name('index');
            Route::get('/{sale}', function ($sale) { return view('shop.sales.show', compact('sale')); })->name('show');
            Route::get('/{sale}/receipt', function ($sale) { return view('shop.sales.receipt', compact('sale')); })->name('receipt');
        });

        // Transfers
        Route::prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/', function () { return view('shop.transfers.index'); })->name('index');
            Route::get('/request', function () { return view('shop.transfers.request'); })->name('request');
            Route::get('/{transfer}', function (\App\Models\Transfer $transfer) { return view('shop.transfers.show', compact('transfer')); })->name('show');
            Route::get('/{transfer}/receive', function (\App\Models\Transfer $transfer) { return view('shop.transfers.receive', compact('transfer')); })->name('receive');
        });

        // Returns
        Route::prefix('returns')->name('returns.')->group(function () {
            Route::get('/', function () { return view('shop.returns.index'); })->name('index');
            Route::get('/create', function () { return view('shop.returns.create'); })->name('create');
        });

        // Damaged Goods
        Route::prefix('damaged-goods')->name('damaged-goods.')->group(function () {
            Route::get('/', function () { return view('shop.damaged-goods.index'); })->name('index');
        });

        // Inventory
        Route::prefix('inventory')->name('inventory.')->group(function () {
            Route::get('/stock', function () { return view('shop.inventory.stock'); })->name('stock');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales', function () { return view('shop.reports.sales'); })->name('sales');
        });
    });

// Shared routes (accessible by all authenticated users with proper permissions)
Route::middleware(['auth'])->group(function () {
    // Products (view only for non-owners)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', function () { return view('products.index'); })->name('index');
        Route::get('/{product}', function ($product) { return view('products.show', compact('product')); })->name('show');
    });

    // Profile
    Route::get('/profile', function () { return view('profile'); })->name('profile');
});

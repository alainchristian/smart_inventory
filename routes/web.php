<?php

use App\Http\Controllers\Auth\LoginRedirectController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\ShopManager\DashboardController as ShopDashboardController;
use App\Http\Controllers\WarehouseManager\DashboardController as WarehouseDashboardController;
use App\Http\Middleware\CheckLocation;
use App\Http\Middleware\CheckRole;
use App\Models\Transfer;
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

    // Forced password change (new users)
    Route::get('/change-password', function () {
        return view('auth.change-password');
    })->name('password.change');
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
        Route::get('/', function () {
            return view('owner.warehouses.index');
        })->name('index');
    });

    Route::prefix('shops')->name('shops.')->group(function () {
        Route::get('/', function () {
            return view('owner.shops.index');
        })->name('index');
    });

    // Products (owner can manage all)
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', function () { return view('owner.products.index'); })->name('index');
        Route::get('/create', function () { return view('owner.products.create'); })->name('create');
        Route::get('/purchase-prices', function () {
            return view('owner.products.purchase-prices');
        })->name('purchase-prices');
        Route::get('/{product}/edit', function (\App\Models\Product $product) {
            return view('owner.products.edit', compact('product'));
        })->name('edit');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', function () { return view('owner.categories.index'); })->name('index');
        Route::get('/create', function () { return view('owner.categories.create'); })->name('create');
    });

    // Boxes/Inventory
    Route::prefix('boxes')->name('boxes.')->group(function () {
        Route::get('/', function () { return view('owner.boxes.index'); })->name('index');
        Route::get('/{box}', function ($box) { return view('owner.boxes.show', ['boxId' => $box]); })->name('show');
    });

    // Transfers
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/', function () { return view('owner.transfers.index'); })->name('index');
        Route::get('/{transfer}', function (Transfer $transfer) { return view('owner.transfers.show', compact('transfer')); })->name('show');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', function () { return view('owner.sales.index'); })->name('index');
        Route::get('/{sale}', function ($sale) { return view('owner.sales.show', compact('sale')); })->name('show');
    });

    // Returns
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', function () { return view('owner.returns.index'); })->name('index');
        Route::get('/{return}', function ($return) { return view('owner.returns.show', compact('return')); })->name('show');
    });

    // Damaged Goods
    Route::prefix('damaged-goods')->name('damaged-goods.')->group(function () {
        Route::get('/', function () { return view('owner.damaged-goods.index'); })->name('index');
        Route::get('/{damagedGood}', function ($damagedGood) { return view('owner.damaged-goods.show', compact('damagedGood')); })->name('show');
    });

    // Transporters
    Route::prefix('transporters')->name('transporters.')->group(function () {
        Route::get('/', function () { return view('owner.transporters.index'); })->name('index');
        Route::get('/create', function () { return view('owner.transporters.create'); })->name('create');
    });

    // Alerts
    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', function () { return view('owner.alerts.index'); })->name('index');
    });

    // Activity Logs
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', function () { return view('owner.activity-logs.index'); })->name('index');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/inventory', function () { return view('owner.reports.inventory'); })->name('inventory');
        Route::get('/sales', function () { return view('owner.reports.sales'); })->name('sales');
        Route::get('/transfers', function () { return view('owner.reports.transfers'); })->name('transfers');
        Route::get('/losses', function () { return view('owner.reports.losses'); })->name('losses');
        Route::get('/payment-methods', function () { return view('owner.reports.payment-methods'); })->name('payment-methods');
        Route::get('/customer-credit', function () { return view('owner.reports.customer-credit'); })->name('customer-credit');

        // Custom report builder
        Route::get('/custom',            [\App\Http\Controllers\Owner\Reports\CustomReportController::class, 'library'])->name('custom.library');
        Route::get('/custom/builder',    [\App\Http\Controllers\Owner\Reports\CustomReportController::class, 'builder'])->name('custom.builder');
        Route::get('/custom/{report}',   [\App\Http\Controllers\Owner\Reports\CustomReportController::class, 'view'])->name('custom.view');
        Route::get('/custom/{report}/print', function (\App\Models\SavedReport $report) {
            $user = auth()->user();
            if ($report->created_by !== $user->id && !$report->is_shared) abort(403);
            $results = $report->last_results ?? [];
            $html = app(\App\Services\Reports\ExportReportAction::class)->toPrintHtml($report, $results);
            return response($html)->header('Content-Type', 'text/html');
        })->name('custom.print');
    });

    // Finance (Day Close)
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/daily',    function () { return view('owner.finance.daily'); })->name('daily');
        Route::get('/overview', function () { return view('owner.finance.overview'); })->name('overview');
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

        // Expense Requests
        Route::prefix('expense-requests')->name('expense-requests.')->group(function () {
            Route::get('/', function () { return view('warehouse.expense-requests.index'); })->name('index');
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

        // Credit Repayments
        Route::get('/credit-repayments', function () { return view('shop.credit-repayments'); })->name('credit-repayments');

        // Day Close
        Route::prefix('day-close')->name('day-close.')->group(function () {
            Route::get('/',      function () { return view('shop.day-close.index'); })->name('index');
            Route::get('/close', function () { return view('shop.day-close.close'); })->name('close');
        });

        // Session management
        Route::prefix('session')->name('session.')->group(function () {
            Route::get('/open', function () {
                return view('shop.day-close.index');
            })->name('open');

            Route::get('/close/{session?}', function (?\App\Models\DailySession $session = null) {
                return view('shop.day-close.close', compact('session'));
            })->name('close');

            Route::get('/history', function () {
                return view('shop.day-close.history');
            })->name('history');

            Route::get('/requests', function () {
                return view('shop.day-close.requests');
            })->name('requests');
        });

        // Mid-day cash actions (require open session — surfaced from SessionManager action buttons)
        Route::get('/bank-deposits', function () {
            return view('shop.day-close.bank-deposits');
        })->name('bank-deposits');

        Route::get('/expenses/add', function () {
            return view('shop.day-close.add-expense-page');
        })->name('expenses.add');

        Route::get('/withdrawals/add', function () {
            return view('shop.day-close.add-withdrawal-page');
        })->name('withdrawals.add');

        Route::get('/expense-requests', function () {
            return view('shop.day-close.requests');
        })->name('expense-requests');
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

// Debug transporters
Route::get('/debug/transporters', function () {
    $all = \App\Models\Transporter::all();
    $active = \App\Models\Transporter::active()->get();
    return response()->json([
        'total' => $all->count(),
        'active' => $active->count(),
        'all_transporters' => $all,
        'active_transporters' => $active,
    ]);
});

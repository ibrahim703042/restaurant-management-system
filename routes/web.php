<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillVerifyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\employeeController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PositionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('admin.index') : view('auth.login');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', fn () => redirect()->route('admin.index'))->name('home');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index')->middleware('permission:sales.dashboard.view');

    Route::middleware('permission:sales.pos.use')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    });

    Route::middleware('permission:sales.clients.manage')->group(function () {
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
        Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
        Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    });

    Route::middleware('permission:sales.orders.manage')->group(function () {
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    Route::middleware('permission:sales.qr.verify')->group(function () {
        Route::get('/bills/verify/{token}', [BillVerifyController::class, 'show'])->name('bills.verify');
        Route::post('/bills/verify/{token}', [BillVerifyController::class, 'confirm'])->name('bills.verify.confirm');
    });

    Route::middleware('permission:sales.bills.manage')->group(function () {
        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
        Route::get('/bills/{bill}/print', [BillController::class, 'printView'])->name('bills.print');
    });

    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index')->middleware('permission:sales.payments.manage');

    Route::get('/debts', [DebtController::class, 'index'])->name('debts.index')->middleware('permission:sales.debts.manage');
    Route::post('/debts/{debt}/pay', [DebtController::class, 'recordPayment'])->name('debts.pay')->middleware('permission:sales.debts.manage');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index')->middleware('permission:admin.settings.manage');
    Route::put('/settings/{setting}', [SettingsController::class, 'update'])->name('settings.update')->middleware('permission:admin.settings.manage');

    Route::middleware('permission:admin.users.manage')->group(function () {
        Route::get('/user', [UserController::class, 'index'])->name('users.index');
        Route::get('/create/user', [UserController::class, 'create'])->name('user.create');
        Route::post('/user', [UserController::class, 'store'])->name('users.store');
    });

    Route::middleware('permission:hr.positions.manage')->group(function () {
        Route::get('/position', [PositionController::class, 'index'])->name('positions.index');
        Route::get('/create/position', [PositionController::class, 'create'])->name('position.create');
        Route::post('/add-position', [PositionController::class, 'position'])->name('position.store');
        Route::get('/edit-position/{id}', [PositionController::class, 'edit']);
        Route::put('/update-position/{id}', [PositionController::class, 'update']);
        Route::get('/delete-position/{id}', [PositionController::class, 'destroy']);
    });

    Route::middleware('permission:hr.employees.manage')->group(function () {
        Route::get('/employee', [employeeController::class, 'index'])->name('employees.index');
        Route::get('/create/employee', [employeeController::class, 'create'])->name('employee.create');
        Route::post('/add-employee', [employeeController::class, 'store'])->name('employee.store');
        Route::get('/edit-employee/{id}', [employeeController::class, 'edit']);
        Route::put('/update-employee/{id}', [employeeController::class, 'update']);
        Route::get('/delete-employee/{id}', [employeeController::class, 'destroy']);
    });

    Route::middleware('permission:ops.stores.manage')->group(function () {
        Route::get('/store', [StoreController::class, 'index'])->name('stores.index');
        Route::get('/create/store', [StoreController::class, 'create'])->name('store.create');
        Route::post('/add-store', [StoreController::class, 'store'])->name('store.store');
        Route::get('/edit-store/{id}', [StoreController::class, 'edit']);
        Route::put('/update-store/{id}', [StoreController::class, 'update']);
        Route::get('/delete-store/{id}', [StoreController::class, 'destroy']);
    });

    Route::middleware('permission:ops.dining_tables.manage')->group(function () {
        Route::get('/table', [TableController::class, 'index'])->name('tables.index');
        Route::get('/create/table', [TableController::class, 'create'])->name('table.create');
        Route::post('/add-table', [TableController::class, 'store'])->name('table.store');
        Route::get('/edit-table/{id}', [TableController::class, 'edit']);
        Route::put('/update-table/{id}', [TableController::class, 'update']);
        Route::get('/delete-table/{id}', [TableController::class, 'destroy']);
    });

    Route::middleware('permission:ops.menu.categories.manage')->group(function () {
        Route::get('/category', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/create/category', [CategoryController::class, 'create'])->name('category.create');
        Route::post('/add-category', [CategoryController::class, 'store'])->name('category.store');
        Route::get('/edit-category/{id}', [CategoryController::class, 'edit']);
        Route::put('/update-category/{id}', [CategoryController::class, 'update']);
        Route::get('/delete-category/{id}', [CategoryController::class, 'destroy']);
    });

    Route::middleware('permission:ops.menu.products.manage')->group(function () {
        Route::get('/product', [ProductController::class, 'index'])->name('products.index');
        Route::get('/create/product', [ProductController::class, 'create'])->name('product.create');
        Route::post('/add-product', [ProductController::class, 'store'])->name('product.store');
        Route::get('/edit-product/{id}', [ProductController::class, 'edit']);
        Route::put('/update-product/{id}', [ProductController::class, 'update']);
        Route::get('/delete-product/{id}', [ProductController::class, 'destroy']);
    });

    Route::middleware('permission:inventory.stock.view')->group(function () {
        Route::get('/inventory', [InventoryStockController::class, 'index'])->name('inventory.index');
    });
    Route::post('/inventory/adjust', [InventoryStockController::class, 'adjust'])->name('inventory.adjust')->middleware('permission:inventory.stock.adjust');

    Route::middleware('permission:hr.payroll.view')->group(function () {
        Route::get('/payroll', [PayrollPeriodController::class, 'index'])->name('payroll.index');
    });
    Route::middleware('permission:hr.payroll.manage')->group(function () {
        Route::get('/payroll/create', [PayrollPeriodController::class, 'create'])->name('payroll.create');
        Route::post('/payroll', [PayrollPeriodController::class, 'store'])->name('payroll.store');
    });
});

<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillVerifyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\EmployeeController;
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
use App\Http\Controllers\AuditLogController;
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
        Route::get('/clients/list', [ClientController::class, 'listJson'])->name('clients.list');
        Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
        Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}/json', [ClientController::class, 'showJson'])->name('clients.showJson');
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

    Route::get('/audit', [AuditLogController::class, 'index'])->name('audit.index');

    Route::middleware('permission:admin.users.manage')->group(function () {
        Route::get('/user/list', [UserController::class, 'listJson'])->name('users.list');
        Route::get('/user/employees-json', [UserController::class, 'employeesAvailableJson'])->name('users.employees.available');
        Route::get('/user', [UserController::class, 'index'])->name('users.index');
        Route::post('/user', [UserController::class, 'store'])->name('users.store');
        Route::get('/user/{user}/json', [UserController::class, 'showJson'])->name('users.showJson');
        Route::get('/user/{user}/employees-json', [UserController::class, 'employeesForEditJson'])->name('users.employees.edit');
        Route::put('/user/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    Route::middleware('permission:hr.positions.manage')->group(function () {
        Route::get('/position/list', [PositionController::class, 'listJson'])->name('positions.list');
        Route::get('/position', [PositionController::class, 'index'])->name('positions.index');
        Route::post('/position', [PositionController::class, 'store'])->name('positions.store');
        Route::get('/position/{position}/json', [PositionController::class, 'showJson'])->name('positions.showJson');
        Route::post('/position/{position}/update', [PositionController::class, 'update'])->name('positions.update');
        Route::delete('/position/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
    });

    Route::middleware('permission:hr.employees.manage')->group(function () {
        Route::get('/employee/list', [EmployeeController::class, 'listJson'])->name('employees.list');
        Route::get('/employee', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employee', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employee/{employee}/json', [EmployeeController::class, 'showJson'])->name('employees.showJson');
        Route::post('/employee/{employee}/update', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employee/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    });

    Route::middleware('permission:ops.stores.manage')->group(function () {
        Route::get('/store/list', [StoreController::class, 'listJson'])->name('stores.list');
        Route::get('/store', [StoreController::class, 'index'])->name('stores.index');
        Route::post('/store', [StoreController::class, 'store'])->name('stores.store');
        Route::get('/store/{store}/json', [StoreController::class, 'showJson'])->name('stores.showJson');
        Route::post('/store/{store}/update', [StoreController::class, 'update'])->name('stores.update');
        Route::delete('/store/{store}', [StoreController::class, 'destroy'])->name('stores.destroy');
    });

    Route::middleware('permission:ops.dining_tables.manage')->group(function () {
        Route::get('/table/list', [TableController::class, 'listJson'])->name('tables.list');
        Route::get('/table', [TableController::class, 'index'])->name('tables.index');
        Route::post('/table', [TableController::class, 'store'])->name('tables.store');
        Route::get('/table/{dining_table}/json', [TableController::class, 'showJson'])->name('tables.showJson');
        Route::post('/table/{dining_table}/update', [TableController::class, 'update'])->name('tables.update');
        Route::delete('/table/{dining_table}', [TableController::class, 'destroy'])->name('tables.destroy');
    });

    Route::middleware('permission:ops.menu.categories.manage')->group(function () {
        Route::get('/category/list', [CategoryController::class, 'listJson'])->name('categories.list');
        Route::get('/category', [CategoryController::class, 'index'])->name('categories.index');
        Route::post('/category', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/category/{category}/json', [CategoryController::class, 'showJson'])->name('categories.showJson');
        Route::post('/category/{category}/update', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('/category/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    });

    Route::middleware('permission:ops.menu.products.manage')->group(function () {
        Route::get('/product/list', [ProductController::class, 'listJson'])->name('products.list');
        Route::get('/product', [ProductController::class, 'index'])->name('products.index');
        Route::post('/product', [ProductController::class, 'store'])->name('products.store');
        Route::get('/product/{product}/json', [ProductController::class, 'showJson'])->name('products.showJson');
        Route::post('/product/{product}/update', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/product/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });

    Route::middleware('permission:inventory.stock.view')->group(function () {
        Route::get('/inventory', [InventoryStockController::class, 'index'])->name('inventory.index');
    });
    Route::post('/inventory/adjust', [InventoryStockController::class, 'adjust'])->name('inventory.adjust')->middleware('permission:inventory.stock.adjust');

    Route::middleware('permission:hr.payroll.view')->group(function () {
        Route::get('/payroll/list', [PayrollPeriodController::class, 'listJson'])->name('payroll.list');
        Route::get('/payroll', [PayrollPeriodController::class, 'index'])->name('payroll.index');
    });
    Route::post('/payroll', [PayrollPeriodController::class, 'store'])->name('payroll.store')->middleware('permission:hr.payroll.manage');
});

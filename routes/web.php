<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\PasswordOtpController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\BillVerifyController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\DiningZoneController;
use App\Http\Controllers\EmployeeActivityController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeLeaveController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaiterPerformanceController;
use App\Http\Controllers\WorkShiftController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('admin.index') : view('auth.login');
});

/** Serve branding images from /storage/*.png (project storage/, not public disk). */
Route::get('/auth-media/{file}', function (string $file) {
    $allowed = ['login.png', 'back-login.png'];
    if (! in_array($file, $allowed, true)) {
        abort(404);
    }
    $path = base_path('storage/'.$file);
    if (! is_file($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('file', '[a-z0-9.-]+')->name('auth.media');

Auth::routes(['reset' => false]);

Route::middleware('guest')->group(function () {
    Route::get('password/reset', [PasswordOtpController::class, 'showRequestForm'])->name('password.request');
    Route::post('password/email', [PasswordOtpController::class, 'sendOtp'])->name('password.email');
    Route::get('password/reset/cancel', [PasswordOtpController::class, 'cancel'])->name('password.otp.cancel');
    Route::get('password/reset/otp', [PasswordOtpController::class, 'showOtpForm'])->name('password.otp.show');
    Route::post('password/reset/otp/resend', [PasswordOtpController::class, 'resendOtp'])->name('password.otp.resend');
    Route::post('password/reset/otp', [PasswordOtpController::class, 'resetWithOtp'])->name('password.otp.update');
});

Route::get('/locale/{locale}', [LocaleController::class, 'set'])->name('locale.set')->whereIn('locale', ['en', 'fr']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::get('/home', fn () => redirect()->route('admin.index'))->name('home');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index')->middleware('permission:sales.dashboard.view');

    Route::middleware('permission:sales.pos.use')->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/store', [PosController::class, 'setStore'])->name('pos.set-store');
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
        Route::get('/orders/list', [OrderController::class, 'listJson'])->name('orders.list');
        Route::get('/orders/{order}/json', [OrderController::class, 'showJson'])->name('orders.showJson');
        Route::post('/orders/{order}/update', [OrderController::class, 'update'])->name('orders.update');
        Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    });

    Route::middleware('permission:sales.qr.verify')->group(function () {
        Route::get('/bills/verify/{token}', [BillVerifyController::class, 'show'])->name('bills.verify');
        Route::post('/bills/verify/{token}', [BillVerifyController::class, 'confirm'])->name('bills.verify.confirm');
    });

    Route::middleware('permission:sales.bills.manage')->group(function () {
        Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
        Route::get('/bills/list', [BillController::class, 'listJson'])->name('bills.list');
        Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
        Route::get('/bills/{bill}/print', [BillController::class, 'printView'])->name('bills.print');
    });

    Route::middleware('permission:sales.payments.manage')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/list', [PaymentController::class, 'listJson'])->name('payments.list');
    });

    Route::middleware('permission:sales.debts.manage')->group(function () {
        Route::get('/debts', [DebtController::class, 'index'])->name('debts.index');
        Route::get('/debts/list', [DebtController::class, 'listJson'])->name('debts.list');
        Route::post('/debts/{debt}/pay', [DebtController::class, 'recordPayment'])->name('debts.pay');
    });

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

    Route::middleware('permission:ops.dining_zones.manage')->group(function () {
        Route::get('/dining-zone/list', [DiningZoneController::class, 'listJson'])->name('dining-zones.list');
        Route::get('/dining-zone', [DiningZoneController::class, 'index'])->name('dining-zones.index');
        Route::post('/dining-zone', [DiningZoneController::class, 'store'])->name('dining-zones.store');
        Route::get('/dining-zone/{dining_zone}/json', [DiningZoneController::class, 'showJson'])->name('dining-zones.showJson');
        Route::post('/dining-zone/{dining_zone}/update', [DiningZoneController::class, 'update'])->name('dining-zones.update');
        Route::delete('/dining-zone/{dining_zone}', [DiningZoneController::class, 'destroy'])->name('dining-zones.destroy');
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

    /** Legacy blades under resources/views/pages/forms — auth + Spatie permission (same as real CRUD). */
    Route::middleware(['auth', 'permission:ops.stores.manage'])->group(function () {
        Route::view('/pages/forms/store', 'pages.forms.store')->name('pages.forms.store');
        Route::view('/pages/forms/edit-store', 'pages.forms.edit_store')->name('pages.forms.edit-store');
    });
    Route::middleware(['auth', 'permission:ops.menu.categories.manage'])->group(function () {
        Route::view('/pages/forms/category', 'pages.forms.category')->name('pages.forms.category');
        Route::view('/pages/forms/edit-category', 'pages.forms.edit_category')->name('pages.forms.edit-category');
    });
    Route::middleware(['auth', 'permission:ops.menu.products.manage'])->group(function () {
        Route::view('/pages/forms/product', 'pages.forms.product')->name('pages.forms.product');
    });
    Route::middleware(['auth', 'permission:hr.positions.manage'])->group(function () {
        Route::view('/pages/forms/position', 'pages.forms.position')->name('pages.forms.position');
        Route::view('/pages/forms/edit-position', 'pages.forms.edit_position')->name('pages.forms.edit-position');
    });
    Route::middleware(['auth', 'permission:hr.employees.manage'])->group(function () {
        Route::view('/pages/forms/employee', 'pages.forms.employee')->name('pages.forms.employee');
        Route::view('/pages/forms/edit-employee', 'pages.forms.edit_employee')->name('pages.forms.edit-employee');
    });
    Route::middleware(['auth', 'permission:admin.users.manage'])->group(function () {
        Route::view('/pages/forms/user', 'pages.forms.user')->name('pages.forms.user');
    });
    Route::middleware(['auth', 'permission:ops.dining_tables.manage'])->group(function () {
        Route::view('/pages/forms/table', 'pages.forms.table')->name('pages.forms.table');
        Route::view('/pages/forms/edit-table', 'pages.forms.edit_table')->name('pages.forms.edit-table');
    });

    Route::middleware('permission:hr.payroll.view')->group(function () {
        Route::get('/payroll/list', [PayrollPeriodController::class, 'listJson'])->name('payroll.list');
        Route::get('/payroll', [PayrollPeriodController::class, 'index'])->name('payroll.index');
    });
    Route::post('/payroll', [PayrollPeriodController::class, 'store'])->name('payroll.store')->middleware('permission:hr.payroll.manage');

    Route::middleware('permission:hr.shifts.manage')->group(function () {
        Route::get('/work-shifts', [WorkShiftController::class, 'index'])->name('work-shifts.index');
        Route::get('/work-shifts/list', [WorkShiftController::class, 'listJson'])->name('work-shifts.list');
        Route::post('/work-shifts', [WorkShiftController::class, 'store'])->name('work-shifts.store');
        Route::get('/work-shifts/{work_shift}/json', [WorkShiftController::class, 'showJson'])->name('work-shifts.showJson');
        Route::post('/work-shifts/{work_shift}/update', [WorkShiftController::class, 'update'])->name('work-shifts.update');
        Route::delete('/work-shifts/{work_shift}', [WorkShiftController::class, 'destroy'])->name('work-shifts.destroy');
    });

    Route::middleware('permission:hr.leaves.manage')->group(function () {
        Route::get('/employee-leaves', [EmployeeLeaveController::class, 'index'])->name('employee-leaves.index');
        Route::get('/employee-leaves/list', [EmployeeLeaveController::class, 'listJson'])->name('employee-leaves.list');
        Route::post('/employee-leaves', [EmployeeLeaveController::class, 'store'])->name('employee-leaves.store');
        Route::get('/employee-leaves/{employee_leave}/json', [EmployeeLeaveController::class, 'showJson'])->name('employee-leaves.showJson');
        Route::post('/employee-leaves/{employee_leave}/update', [EmployeeLeaveController::class, 'update'])->name('employee-leaves.update');
        Route::post('/employee-leaves/{employee_leave}/decide', [EmployeeLeaveController::class, 'decide'])->name('employee-leaves.decide');
        Route::delete('/employee-leaves/{employee_leave}', [EmployeeLeaveController::class, 'destroy'])->name('employee-leaves.destroy');
    });

    Route::middleware('permission:hr.performance.manage')->group(function () {
        Route::get('/waiter-performance', [WaiterPerformanceController::class, 'index'])->name('waiter-performance.index');
        Route::get('/waiter-performance/list', [WaiterPerformanceController::class, 'listJson'])->name('waiter-performance.list');
        Route::post('/waiter-performance', [WaiterPerformanceController::class, 'store'])->name('waiter-performance.store');
        Route::get('/waiter-performance/{waiter_performance}/json', [WaiterPerformanceController::class, 'showJson'])->name('waiter-performance.showJson');
        Route::post('/waiter-performance/{waiter_performance}/update', [WaiterPerformanceController::class, 'update'])->name('waiter-performance.update');
        Route::delete('/waiter-performance/{waiter_performance}', [WaiterPerformanceController::class, 'destroy'])->name('waiter-performance.destroy');
    });

    Route::middleware('permission:hr.activity.view')->group(function () {
        Route::get('/employee-activities', [EmployeeActivityController::class, 'index'])->name('employee-activities.index');
        Route::get('/employee-activities/list', [EmployeeActivityController::class, 'listJson'])->name('employee-activities.list');
    });
});

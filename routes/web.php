<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\StaffOrderController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;


Route::get('/', function () {
    return redirect('/login');
});


require __DIR__ . '/auth.php';

use App\Http\Controllers\RedirectController;

Route::get('/redirect', [RedirectController::class, 'index'])->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

        // Orders
        // Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        // Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.view');
        // Route::post('/orders/update-payment/{id}', [OrderController::class, 'updatePayment'])
        //     ->name('orders.updatePayment');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders');

        Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
        Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.view');

        // ✅ NEW (EDIT PAGE)
        Route::get('/orders/edit/{id}', [OrderController::class, 'edit'])->name('orders.edit');

        // ✅ NEW (UPDATE ORDER)
        Route::put('/orders/update/{id}', [OrderController::class, 'update'])->name('orders.update');

        // ✅ EXISTING (PAYMENT UPDATE)
        Route::post('/orders/update-payment/{id}', [OrderController::class, 'updatePayment'])->name('orders.updatePayment');

        // ✅ NEW (DELETE)
        Route::delete('/orders/delete/{id}', [OrderController::class, 'destroy'])->name('orders.delete');
        Route::post('/orders/{id}/add-payment', [OrderController::class, 'addPayment'])->name('orders.addPayment');
        Route::delete('/payments/{id}', [OrderController::class, 'deletePayment'])->name('orders.deletePayment');
        Route::put('/payments/{id}', [OrderController::class, 'updatePaymentRecord'])->name('orders.updatePaymentRecord'); 
        

        // Customers
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/due', [CustomerController::class, 'dueCustomers'])->name('customers.due');
        Route::get('/customers/{mobile}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
        Route::get('/customers/{mobile}', [CustomerController::class, 'show'])->name('customers.show');
        Route::delete('/customers/{mobile}', [CustomerController::class, 'destroy'])->name('customers.destroy');

        // Staff
        Route::get('/staff', [StaffController::class, 'index'])->name('staff');
        Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
        Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
        Route::get('/staff/{id}', [StaffController::class, 'show'])->name('staff.view');
        Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
        Route::post('/staff/{id}/update', [StaffController::class, 'update'])->name('staff.update');

        Route::get('/staff-attendance', [StaffController::class, 'attendance'])->name('staff.attendance');
        Route::post('/staff-attendance', [StaffController::class, 'saveAttendance'])->name('staff.attendance.save');
        Route::get('/staff-monthly-report', [StaffController::class, 'monthlyReport'])->name('staff.monthly');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses/store', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{id}', [ExpenseController::class, 'show'])->name('expenses.view');
        Route::get('/expenses/{id}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::post('/expenses/{id}/update', [ExpenseController::class, 'update'])->name('expenses.update');
        // Route::post('/expenses/{id}/delete', [ExpenseController::class, 'destroy'])->name('expenses.delete');
        Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.delete');

        // Approve / Reject
        Route::post('/expenses/{id}/status', [ExpenseController::class, 'updateStatus'])
            ->name('expenses.status');

        // Summary (optional dashboard)
        Route::get('/expenses-summary', [ExpenseController::class, 'summary'])
            ->name('expenses.summary');


        Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
        Route::post('/categories/store', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('categories.delete');
    });


    /*
     * |--------------------------------------------------------------------------
     * | ✅ STAFF ROUTES (LIMITED ACCESS)
     * |--------------------------------------------------------------------------
     */
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])->name('dashboard');
        // Orders
        Route::get('/orders/export', [StaffOrderController::class, 'export'])->name('orders.export');
        Route::get('/orders', [StaffOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [StaffOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders/store', [StaffOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [StaffOrderController::class, 'show'])->name('orders.view');
        Route::get('/orders/edit/{id}', [StaffOrderController::class, 'edit'])->name('orders.edit');
        Route::put('/orders/update/{id}', [StaffOrderController::class, 'update'])->name('orders.update');
        // Route::post('/orders/{id}/add-payment', [StaffOrderController::class, 'addPayment'])->name('orders.addPayment');
        Route::post('/orders/{id}/add-payment', [StaffOrderController::class, 'addPayment'])->name('orders.addPayment.staff');
        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/due', [CustomerController::class, 'dueCustomers'])->name('customers.due');
        Route::get('/customers/{mobile}/ledger', [CustomerController::class, 'ledger'])->name('customers.ledger');
        Route::get('/customers/{mobile}', [CustomerController::class, 'show'])->name('customers.show');
        Route::delete('/customers/{mobile}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    });

});


<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\ReminderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\StaffController;

/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 */

// 🔓 Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔒 Protected routes (JWT required)
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user', [AuthController::class, 'me']);

    // Route::post('/create-order', [OrderController::class, 'createOrder']);
    // Route::get('/orders', [OrderController::class, 'orderList']);
    // Route::get('/order/{id}', [OrderController::class, 'orderDetails']);
    // Route::delete('/order/{id}', [OrderController::class, 'deleteOrder']);
    // Route::put('/order/{id}', [OrderController::class, 'updateOrder']);
    // Route::post('/update-order-status', [OrderController::class, 'updateStatus']);
});

// Orders APIs (without auth)
Route::post('/create-order', [OrderController::class, 'createOrder']);
Route::get('/orders', [OrderController::class, 'orderList']);
Route::get('/order/{id}', [OrderController::class, 'orderDetails']);
Route::delete('/order/{id}', [OrderController::class, 'deleteOrder']);
Route::put('/order/{id}', [OrderController::class, 'updateOrder']);
Route::post('/update-order-status', [OrderController::class, 'updateStatus']);
Route::get('/orders-by-staff/{staff_id}', [OrderController::class, 'ordersByStaff']);

Route::get('/paymentlist', [OrderController::class, 'paymentlist']);

Route::post('/mark-attendance', [AttendanceController::class, 'markAttendance']);
Route::get('/attendance-list', [AttendanceController::class, 'attendanceList']);

Route::prefix('expense')->group(function () {
    Route::post('add', [ExpenseController::class, 'store']);
    Route::post('update/{id}', [ExpenseController::class, 'update']);
    Route::delete('delete/{id}', [ExpenseController::class, 'destroy']);

    Route::get('list', [ExpenseController::class, 'index']);
    Route::get('view/{id}', [ExpenseController::class, 'show']);

    Route::get('dashboard', [ExpenseController::class, 'dashboard']);  // 🔥
});

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::get('/customers', [CustomerController::class, 'index']);

Route::get('/today-reminders', [ReminderController::class, 'todayReminders']);



// ✅ Staff APIs
Route::get('/staff', [StaffController::class, 'index']);
Route::post('/staff', [StaffController::class, 'store']);
Route::post('/staff/{id}', [StaffController::class, 'update']);
Route::delete('/staff/{id}', [StaffController::class, 'destroy']);
Route::get('/staff/{id}', [StaffController::class, 'show']);
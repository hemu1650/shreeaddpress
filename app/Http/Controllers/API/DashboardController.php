<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $today = Carbon::today();

            /* =============================
               ORDER COUNTS
            ============================= */
            $totalOrders = Order::count();
            $pending = Order::where('order_status', 'Pending')->count();
            $printing = Order::where('order_status', 'Printing')->count();
            $ready = Order::where('order_status', 'Ready')->count();
            $delivered = Order::where('order_status', 'Delivered')->count();

            /* =============================
               CUSTOMER COUNT (UNIQUE)
            ============================= */
            $totalCustomers = Order::whereNotNull('mobile')
                ->distinct('mobile')
                ->count('mobile');

            /* =============================
               PAYMENT (CORRECT LOGIC)
            ============================= */

            // ✅ Total Collection (all payments)
            $totalCollection = OrderPayment::sum('amount');

            // ✅ Total Payment Count
            $totalCollectionCount = OrderPayment::count();

            // ✅ Today Collection
            $todayCollection = OrderPayment::whereDate('payment_date', $today)
                ->sum('amount');

            /* =============================
               DUE AMOUNT
            ============================= */
            // if (\Schema::hasColumn('orders', 'total_amount')) {
            //     $dueAmount = Order::sum(DB::raw('total_amount - paid_amount'));
            // } else {
            //     $dueAmount = Order::where('payment_status', '!=', 'Paid')
            //         ->sum('paid_amount');
            // }

            $dueAmount = Order::whereColumn('total_amount', '>', 'paid_amount')->sum(DB::raw('total_amount - paid_amount'));

            /* =============================
            STAFF & ATTENDANCE
            ============================= */

            // ✅ Total Staff
            $totalStaff = DB::table('users')
                ->where('role', 'staff')
                ->count();

            // ✅ Today Present Staff
            $todayPresent = DB::table('staff_attendance')
                ->whereDate('date', $today)
                ->where('status', 'present')
                ->count();

            /* =============================
               TODAY DUE REMINDERS
            ============================= */
            // $todayDueReminders = [];

            // if (\Schema::hasColumn('orders', 'due_date')) {
            //     $todayDueReminders = Order::whereDate('due_date', $today)
            //         ->where('payment_status', '!=', 'Paid')
            //         ->select(
            //             'id',
            //             'customer_name',
            //             'mobile',
            //             'due_date',
            //             'paid_amount',
            //             'order_status'
            //         )
            //         ->orderBy('due_date', 'asc')
            //         ->get();
            // }

            $todayDueReminders = Order::whereDate('due_date', $today)
    ->whereColumn('total_amount', '>', 'paid_amount') // better than payment_status
    ->select(
        'id',
        'customer_name',
        'mobile',
        'due_date',
        'paid_amount',
        'total_amount',
        DB::raw('(total_amount - paid_amount) as due_amount')
    )
    ->orderBy('due_date', 'asc')
    ->get();

            /* =============================
               CARDS (FINAL UI FORMAT)
            ============================= */
            $cards = [
                [
                    'title' => 'Total Orders',
                    'value' => $totalOrders,
                    'icon' => 'fa-shopping-cart',
                    'color' => 'blue'
                ],
                [
                    'title' => 'Pending Orders',
                    'value' => $pending,
                    'icon' => 'fa-clock',
                    'color' => 'red'
                ],
                [
                    'title' => 'Printing',
                    'value' => $printing,
                    'icon' => 'fa-print',
                    'color' => 'blue'
                ],
                [
                    'title' => 'Ready',
                    'value' => $ready,
                    'icon' => 'fa-check-circle',
                    'color' => 'green'
                ],
                [
                    'title' => 'Delivered',
                    'value' => $delivered,
                    'icon' => 'fa-truck',
                    'color' => 'green'
                ],
                [
                    'title' => 'Total Customers',
                    'value' => $totalCustomers,
                    'icon' => 'fa-users',
                    'color' => 'blue'
                ],

                // ✅ COLLECTION CARDS
                [
                    'title' => 'Total Collection',
                    'value' => (float) $totalCollection,
                    'icon' => 'fa-wallet',
                    'color' => 'green'
                ],
                // [
                //     'title' => 'Total Payments',
                //     'value' => $totalCollectionCount,
                //     'icon' => 'fa-receipt',
                //     'color' => 'blue'
                // ],
                [
                    'title' => 'Today Collection',
                    'value' => (float) $todayCollection,
                    'icon' => 'fa-rupee-sign',
                    'color' => 'green'
                ],

                [
                    'title' => 'Due Amount',
                    'value' => (float) $dueAmount,
                    'icon' => 'fa-exclamation-circle',
                    'color' => 'red'
                ],
                [
                    'title' => 'Today Due Reminders',
                    'value' => count($todayDueReminders),
                    'icon' => 'fa-bell',
                    'color' => 'orange'
                ],
                [
                    'title' => 'Today Attendance',
                    'value' => $todayPresent,
                    'icon' => 'fa-user-check',
                    'color' => 'green'
                ],
                [
                    'title' => 'Total Staff',
                    'value' => $totalStaff,
                    'icon' => 'fa-users',
                    'color' => 'blue'
                ],
            ];

            return response()->json([
                'status' => true,
                'message' => 'Dashboard data fetched successfully',
                'data' => [
                    'cards' => $cards,
                    'today_due_reminders' => $todayDueReminders
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
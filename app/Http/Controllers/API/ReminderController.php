<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReminderController extends Controller
{
    // public function todayReminders(Request $request)
    // {
    //     try {
    //         $today = Carbon::today();

    //         $reminders = Order::whereDate('due_date', $today)
    //             ->where('payment_status', '!=', 'Paid')
    //             ->select(
    //                 'id',
    //                 'customer_name',
    //                 'mobile',
    //                 'address',
    //                 'due_date',
    //                 'paid_amount',
    //                 'order_status',
    //                 'payment_status'
    //             )
    //             ->orderBy('due_date', 'asc')
    //             ->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Today reminders list',
    //             'total' => $reminders->count(),
    //             'data' => $reminders
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function todayReminders(Request $request)
    {
        try {
            $today = Carbon::today();

            $reminders = Order::whereDate('orders.due_date', $today)
                ->where('orders.payment_status', '!=', 'Paid')
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->select(
                    'orders.id',
                    'orders.customer_name',
                    'orders.mobile',
                    'orders.address',
                    'orders.due_date',
                    'orders.paid_amount',
                    'orders.order_status',
                    'orders.payment_status',
                    // 🔥 Total Order Amount
                    \DB::raw('SUM(order_items.quantity * order_items.rate) as total_amount'),
                    // 🔥 Due Amount
                    \DB::raw('SUM(order_items.quantity * order_items.rate) - orders.paid_amount as due_amount')
                )
                ->groupBy(
                    'orders.id',
                    'orders.customer_name',
                    'orders.mobile',
                    'orders.address',
                    'orders.due_date',
                    'orders.paid_amount',
                    'orders.order_status',
                    'orders.payment_status'
                )
                ->orderBy('orders.due_date', 'asc')
                ->get();

            // 🔥 Add order items separately
            $reminders->map(function ($order) {
                $order->items = \DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->select('size', 'material', 'quantity', 'rate')
                    ->get();

                return $order;
            });

            return response()->json([
                'status' => true,
                'message' => 'Today reminders list',
                'total' => $reminders->count(),
                'data' => $reminders
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

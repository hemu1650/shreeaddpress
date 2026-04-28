<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StaffAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
    // public function adminDashboard(Request $request)
    // {
    //     // ================= DATE =================
    //     $today = Carbon::today();

    //     // ================= MONTH FILTER =================
    //     $month = $request->month ?? $today->month;
    //     $year = $request->year ?? $today->year;

    //     $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    //     $endDate = Carbon::create($year, $month, 1)->endOfMonth();

    //     // ================= BASE QUERY =================
    //     $monthOrders = Order::whereBetween('created_at', [$startDate, $endDate]);

    //     // ================= ORDERS =================
    //     $totalOrders = (clone $monthOrders)->count();

    //     $pending = (clone $monthOrders)->where('order_status', 'pending')->count();
    //     $printing = (clone $monthOrders)->where('order_status', 'printing')->count();
    //     $ready = (clone $monthOrders)->where('order_status', 'ready')->count();
    //     $delivered = (clone $monthOrders)->where('order_status', 'delivered')->count();

    //     // Today Ready
    //     $readyToday = Order::where('order_status', 'ready')
    //         ->whereDate('created_at', $today)
    //         ->count();

    //     // ================= COLLECTION =================
    //     $todayCollection = Order::whereDate('created_at', $today)
    //         ->sum('paid_amount');

    //     $totalCollection = (clone $monthOrders)
    //         ->where('payment_status', 'paid')
    //         ->sum('paid_amount');

    //     // $dueAmount = (clone $monthOrders)
    //     //     ->where('payment_status', 'pending')
    //     //     ->sum('paid_amount');

    //     $dueAmount = (clone $monthOrders)
    //         ->whereRaw('total_amount > paid_amount')
    //         ->sum(DB::raw('total_amount - paid_amount'));

    //     // $todayDue = Order::where('payment_status', 'pending')
    //     //     ->whereDate('created_at', $today)
    //     //     ->sum('paid_amount');

    //     // TODAY DUE (fixed)
    //     $todayDue = Order::whereRaw('total_amount > paid_amount')
    //         ->whereDate('created_at', $today)
    //         ->sum(DB::raw('total_amount - paid_amount'));

    //     // ================= STAFF =================
    //     $staff = User::where('role', 'staff')->count();

    //     $presentCount = StaffAttendance::whereDate('date', $today)
    //         ->where('status', 'present')
    //         ->count();

    //     $absentCount = StaffAttendance::whereDate('date', $today)
    //         ->where('status', 'absent')
    //         ->count();

    //     // ================= CUSTOMERS =================
    //     $totalCustomers = Order::distinct('mobile')->count('mobile');

    //     $newCustomers = Order::whereBetween('created_at', [$startDate, $endDate])
    //         ->distinct('mobile')
    //         ->count('mobile');

    //     $todayCustomers = Order::whereDate('created_at', $today)
    //         ->distinct('mobile')
    //         ->count('mobile');

    //     // ================= CHART DATA =================
    //     $daysInMonth = $startDate->daysInMonth;

    //     $labels = [];
    //     $dailyOrders = [];
    //     $dailyCollection = [];

    //     for ($i = 1; $i <= $daysInMonth; $i++) {
    //         $date = $startDate->copy()->day($i);

    //         $labels[] = $i;

    //         $dailyOrders[] = Order::whereDate('created_at', $date)->count();

    //         $dailyCollection[] = Order::whereDate('created_at', $date)
    //             ->sum('paid_amount');
    //     }

    //     // ================= STAFF CALENDAR =================
    //     $staffCalendar = [];

    //     for ($i = 1; $i <= $daysInMonth; $i++) {
    //         $date = $startDate->copy()->day($i)->toDateString();

    //         $present = StaffAttendance::whereDate('date', $date)
    //             ->where('status', 'present')
    //             ->count();

    //         $absent = StaffAttendance::whereDate('date', $date)
    //             ->where('status', 'absent')
    //             ->count();

    //         $staffCalendar[] = [
    //             'date' => $i,
    //             'present' => $present,
    //             'absent' => $absent
    //         ];
    //     }

    //     // ================= TODAY REMINDERS =================
    //     $todayReminders = Order::whereDate('due_date', $today)
    //         ->where('payment_status', '!=', 'paid')
    //         ->get();

    //     $todayReminderCount = $todayReminders->count();

    //     // ================= RETURN =================
    //     return view('dashboard', compact(
    //         'month',
    //         'year',
    //         // orders
    //         'totalOrders',
    //         'pending',
    //         'printing',
    //         'ready',
    //         'delivered',
    //         'readyToday',
    //         // collection
    //         'todayCollection',
    //         'totalCollection',
    //         'dueAmount',
    //         'todayDue',
    //         // staff
    //         'staff',
    //         'presentCount',
    //         'absentCount',
    //         // customers
    //         'totalCustomers',
    //         'newCustomers',
    //         'todayCustomers',
    //         // charts
    //         'labels',
    //         'dailyOrders',
    //         'dailyCollection',
    //         // calendar
    //         'staffCalendar',

    //         'todayReminders',
    //         'todayReminderCount',
    //     ));
    // }

    public function adminDashboard(Request $request)
{
    // ================= DATE =================
    $today = Carbon::today();

    // ================= MONTH (UI ke liye rehne do) =================
    $month = $request->month ?? $today->month;
    $year = $request->year ?? $today->year;

    $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    $endDate = Carbon::create($year, $month, 1)->endOfMonth();

    // ================= BASE QUERY (ALL DATA) =================
    $allOrders = Order::query();

    // ================= ORDERS =================
    $totalOrders = (clone $allOrders)->count();

    $pending   = (clone $allOrders)->where('order_status', 'pending')->count();
    $printing  = (clone $allOrders)->where('order_status', 'printing')->count();
    $ready     = (clone $allOrders)->where('order_status', 'ready')->count();
    $delivered = (clone $allOrders)->where('order_status', 'delivered')->count();

    // Today Ready
    $readyToday = Order::where('order_status', 'ready')
        ->whereDate('created_at', $today)
        ->count();

    // ================= COLLECTION =================
    // $todayCollection = Order::whereDate('created_at', $today)->sum('paid_amount');

    $todayCollection = DB::table('order_payments')
    ->whereDate('payment_date', today())
    ->sum('amount');

    $totalCollection = DB::table('order_payments')
    ->sum('amount');

    // $totalCollection = (clone $allOrders)
    //     ->where('payment_status', 'paid')
    //     ->sum('paid_amount');

    // TOTAL DUE (ALL TIME)
    // $dueAmount = (clone $allOrders)
    //     ->whereRaw('total_amount > paid_amount')
    //     ->sum(DB::raw('total_amount - paid_amount'));

    $dueAmount = (clone $allOrders)
    ->selectRaw('SUM(total_amount - paid_amount) as due')
    ->whereColumn('total_amount', '>', 'paid_amount')
    ->value('due');

    // TODAY DUE
    // $todayDue = Order::whereRaw('total_amount > paid_amount')
    //     ->whereDate('created_at', $today)
    //     ->sum(DB::raw('total_amount - paid_amount'));

    $todayDue = Order::whereDate('due_date', $today)
    ->whereColumn('total_amount', '>', 'paid_amount')
    ->sum(DB::raw('total_amount - paid_amount'));

    // ================= STAFF =================
    $staff = User::where('role', 'staff')->count();

    $presentCount = StaffAttendance::whereDate('date', $today)
        ->where('status', 'present')
        ->count();

    $absentCount = StaffAttendance::whereDate('date', $today)
        ->where('status', 'absent')
        ->count();

    // ================= CUSTOMERS =================
    $totalCustomers = Order::distinct('mobile')->count('mobile');

    // ALL TIME customers
    $newCustomers = Order::distinct('mobile')->count('mobile');

    $todayCustomers = Order::whereDate('created_at', $today)
        ->distinct('mobile')
        ->count('mobile');

    // ================= CHART DATA (UNCHANGED) =================
    $daysInMonth = $startDate->daysInMonth;

    $labels = [];
    $dailyOrders = [];
    $dailyCollection = [];

    for ($i = 1; $i <= $daysInMonth; $i++) {
        $date = $startDate->copy()->day($i);

        $labels[] = $i;

        $dailyOrders[] = Order::whereDate('created_at', $date)->count();

        $dailyCollection[] = Order::whereDate('created_at', $date)
            ->sum('paid_amount');
    }

    // ================= STAFF CALENDAR =================
    $staffCalendar = [];

    for ($i = 1; $i <= $daysInMonth; $i++) {
        $date = $startDate->copy()->day($i)->toDateString();

        $present = StaffAttendance::whereDate('date', $date)
            ->where('status', 'present')
            ->count();

        $absent = StaffAttendance::whereDate('date', $date)
            ->where('status', 'absent')
            ->count();

        $staffCalendar[] = [
            'date' => $i,
            'present' => $present,
            'absent' => $absent
        ];
    }

    // ================= TODAY REMINDERS =================
    $todayReminders = Order::whereDate('due_date', $today)
        ->where('payment_status', '!=', 'paid')
        ->get();

    $todayReminderCount = $todayReminders->count();

    // ================= RETURN =================
    return view('dashboard', compact(
        'month',
        'year',

        // orders
        'totalOrders',
        'pending',
        'printing',
        'ready',
        'delivered',
        'readyToday',

        // collection
        'todayCollection',
        'totalCollection',
        'dueAmount',
        'todayDue',

        // staff
        'staff',
        'presentCount',
        'absentCount',

        // customers
        'totalCustomers',
        'newCustomers',
        'todayCustomers',

        // charts
        'labels',
        'dailyOrders',
        'dailyCollection',

        // calendar
        'staffCalendar',

        'todayReminders',
        'todayReminderCount',
    ));
}

    // public function staffDashboard(Request $request)
    // {
    //     $today = Carbon::today();

    //     $month = $request->month ?? $today->month;
    //     $year = $request->year ?? $today->year;

    //     $startDate = Carbon::create($year, $month, 1)->startOfMonth();
    //     $endDate = Carbon::create($year, $month, 1)->endOfMonth();

    //     $userId = auth()->id();

    //     // 🔥 ONLY STAFF DATA
    //     $monthOrders = Order::where('staff_id', $userId)
    //         ->whereBetween('created_at', [$startDate, $endDate]);

    //     // ORDERS
    //     $totalOrders = (clone $monthOrders)->count();

    //     $pending = (clone $monthOrders)->where('order_status', 'pending')->count();
    //     $printing = (clone $monthOrders)->where('order_status', 'printing')->count();
    //     $ready = (clone $monthOrders)->where('order_status', 'ready')->count();
    //     $delivered = (clone $monthOrders)->where('order_status', 'delivered')->count();

    //     $readyToday = Order::where('staff_id', $userId)
    //         ->where('order_status', 'ready')
    //         ->whereDate('created_at', $today)
    //         ->count();

    //     // COLLECTION
    //     $todayCollection = Order::where('staff_id', $userId)
    //         ->whereDate('created_at', $today)
    //         ->sum('paid_amount');

    //     $totalCollection = (clone $monthOrders)
    //         ->where('payment_status', 'paid')
    //         ->sum('paid_amount');

    //     $dueAmount = (clone $monthOrders)
    //         ->where('payment_status', 'pending')
    //         ->sum('paid_amount');

    //     $todayDue = Order::where('staff_id', $userId)
    //         ->where('payment_status', 'pending')
    //         ->whereDate('created_at', $today)
    //         ->sum('paid_amount');

    //     // ❌ STAFF ko ye nahi chahiye
    //     $staff = 0;
    //     $presentCount = 0;
    //     $absentCount = 0;

    //     // CUSTOMERS (ONLY OWN)
    //     $totalCustomers = Order::where('staff_id', $userId)
    //         ->distinct('mobile')
    //         ->count('mobile');

    //     $newCustomers = Order::where('staff_id', $userId)
    //         ->whereBetween('created_at', [$startDate, $endDate])
    //         ->distinct('mobile')
    //         ->count('mobile');

    //     $todayCustomers = Order::where('staff_id', $userId)
    //         ->whereDate('created_at', $today)
    //         ->distinct('mobile')
    //         ->count('mobile');

    //     // CHART
    //     $daysInMonth = $startDate->daysInMonth;

    //     $labels = [];
    //     $dailyOrders = [];
    //     $dailyCollection = [];

    //     for ($i = 1; $i <= $daysInMonth; $i++) {
    //         $date = $startDate->copy()->day($i);

    //         $labels[] = $i;

    //         $dailyOrders[] = Order::where('staff_id', $userId)
    //             ->whereDate('created_at', $date)
    //             ->count();

    //         $dailyCollection[] = Order::where('staff_id', $userId)
    //             ->whereDate('created_at', $date)
    //             ->sum('paid_amount');
    //     }

    //     // ❌ STAFF calendar optional
    //     $staffCalendar = [];

    //     // ================= TODAY REMINDERS (STAFF) =================
    //     $todayReminders = Order::where('staff_id', $userId)
    //         ->whereDate('due_date', $today)
    //         ->where('payment_status', '!=', 'paid')
    //         ->get();

    //     $todayReminderCount = $todayReminders->count();

    //     return view('staff.dashboard', compact(
    //         'month',
    //         'year',
    //         'totalOrders',
    //         'pending',
    //         'printing',
    //         'ready',
    //         'delivered',
    //         'readyToday',
    //         'todayCollection',
    //         'totalCollection',
    //         'dueAmount',
    //         'todayDue',
    //         'totalCustomers',
    //         'newCustomers',
    //         'todayCustomers',
    //         'labels',
    //         'dailyOrders',
    //         'dailyCollection',
    //         'staffCalendar',
    //         'todayReminders',
    //         'todayReminderCount',
    //     ));
    // }

    public function staffDashboard(Request $request)
    {
        $today = Carbon::today();

        $month = $request->month ?? $today->month;
        $year = $request->year ?? $today->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $userId = auth()->id();

        // 🔥 BASE QUERY
        // $monthOrders = Order::where('staff_id', $userId)
        //     ->whereBetween('created_at', [$startDate, $endDate]);
        // // ================= ORDERS =================
        // $totalOrders = (clone $monthOrders)->count();
        // $pending = (clone $monthOrders)->where('order_status', 'pending')->count();
        // $printing = (clone $monthOrders)->where('order_status', 'printing')->count();
        // $ready = (clone $monthOrders)->where('order_status', 'ready')->count();
        // $delivered = (clone $monthOrders)->where('order_status', 'delivered')->count();
        // $readyToday = Order::where('staff_id', $userId)
        //     ->where('order_status', 'ready')
        //     ->whereDate('created_at', $today)
        //     ->count();


        // Month Orders (ALL STAFF)
        $monthOrders = Order::whereBetween('created_at', [$startDate, $endDate]);

        // ================= ORDERS =================
        $totalOrders = (clone $monthOrders)->count();

        $pending = (clone $monthOrders)->where('order_status', 'pending')->count();
        $printing = (clone $monthOrders)->where('order_status', 'printing')->count();
        $ready = (clone $monthOrders)->where('order_status', 'ready')->count();
        $delivered = (clone $monthOrders)->where('order_status', 'delivered')->count();

        // READY TODAY (ALL STAFF)
        $readyToday = Order::where('order_status', 'ready')
            ->whereDate('created_at', $today)
            ->count();
            
        // ================= COLLECTION =================
        // $todayCollection = Order::where('staff_id', $userId)
        //     ->whereDate('created_at', $today)
        //     ->sum('paid_amount');

        $todayCollection = Order::whereDate('created_at', $today)->sum('paid_amount');

        $totalCollection = (clone $monthOrders)
            ->where('payment_status', 'paid')
            ->sum('paid_amount');

        // ================= ✅ FIXED DUE =================

        // 👉 MONTH DUE (only pending)
        $dueAmount = (clone $monthOrders)
            ->whereRaw('total_amount > paid_amount')
            ->sum(DB::raw('total_amount - paid_amount'));

        // 👉 TODAY DUE (only pending)
        $todayDue = Order::where('staff_id', $userId)
    ->whereDate('created_at', $today)
    ->whereRaw('(total_amount - IFNULL(discount,0)) > IFNULL(paid_amount,0)')
    ->sum(DB::raw('(total_amount - IFNULL(discount,0)) - IFNULL(paid_amount,0)'));

        // ================= ✅ MAIN FIX =================

        // 👥 TOTAL CUSTOMERS (unique mobile)
        // $totalCustomers = Order::where('staff_id', $userId)
        //     ->distinct('mobile')
        //     ->count('mobile');

        $totalCustomers = DB::table('orders')
        ->distinct('mobile')
        ->count('mobile');

        // 💰 TOTAL DUE (ALL TIME only pending)
        // $totalDueAmount = Order::where('staff_id', $userId)
        //     ->whereRaw('total_amount > paid_amount')
        //     ->sum(DB::raw('total_amount - paid_amount'));

        // $totalDueAmount = Order::whereRaw('total_amount > paid_amount')
        // ->sum(DB::raw('total_amount - paid_amount'));

        $totalDueAmount = Order::whereRaw('(total_amount - discount) > paid_amount')
    ->sum(DB::raw('(total_amount - discount) - paid_amount'));

        // ================= OTHER CUSTOMER STATS =================
        $newCustomers = Order::where('staff_id', $userId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->distinct('mobile')
            ->count('mobile');

        $todayCustomers = Order::where('staff_id', $userId)
            ->whereDate('created_at', $today)
            ->distinct('mobile')
            ->count('mobile');

        // ================= CHART =================
        $daysInMonth = $startDate->daysInMonth;

        $labels = [];
        $dailyOrders = [];
        $dailyCollection = [];

        for ($i = 1; $i <= $daysInMonth; $i++) {
            $date = $startDate->copy()->day($i);

            $labels[] = $i;

            $dailyOrders[] = Order::where('staff_id', $userId)
                ->whereDate('created_at', $date)
                ->count();

            $dailyCollection[] = Order::where('staff_id', $userId)
                ->whereDate('created_at', $date)
                ->sum('paid_amount');
        }

        // ================= REMINDERS =================
        $todayReminders = Order::where('staff_id', $userId)
            ->whereDate('due_date', $today)
            ->where('payment_status', '!=', 'paid')
            ->get();

        $todayReminderCount = $todayReminders->count();

        return view('staff.dashboard', compact(
            'month',
            'year',
            'totalOrders',
            'pending',
            'printing',
            'ready',
            'delivered',
            'readyToday',
            'todayCollection',
            'totalCollection',
            'dueAmount',
            'todayDue',

            // ✅ FIXED MAIN
            'totalCustomers',
            'totalDueAmount',

            'newCustomers',
            'todayCustomers',
            'labels',
            'dailyOrders',
            'dailyCollection',
            'todayReminders',
            'todayReminderCount',
        ));
    }

    
}

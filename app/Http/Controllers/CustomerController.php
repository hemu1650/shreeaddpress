<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /* =============================
       📋 Customer List (FROM ORDERS)
    ============================= */
    // public function index(Request $request)
    // {
    //     $query = DB::table('orders')
    //         ->select(
    //             'customer_name',
    //             'mobile',
    //             'address',
    //             DB::raw('COUNT(*) as total_orders'),
    //             DB::raw('SUM(paid_amount) as total_paid'),
    //             DB::raw("
    //                 SUM(
    //                     CASE
    //                         WHEN payment_status != 'Paid'
    //                         THEN paid_amount
    //                         ELSE 0
    //                     END
    //                 ) as due_amount
    //             ")
    //         )
    //         ->groupBy('customer_name', 'mobile', 'address');

    //     // 🔍 Search
    //     if ($request->search) {
    //         $query->where(function ($q) use ($request) {
    //             $q
    //                 ->where('customer_name', 'like', '%' . $request->search . '%')
    //                 ->orWhere('mobile', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     $customers = $query->paginate(10);

    //     return view('admin.customers.index', compact('customers'));
    // }

    // public function index(Request $request)
    // {
    //     $query = DB::table('orders')
    //         ->select(
    //             DB::raw('MIN(customer_name) as customer_name'),
    //             'mobile',
    //             DB::raw('MIN(address) as address'),
    //             DB::raw('COUNT(*) as total_orders'),
    //             DB::raw('SUM(paid_amount) as total_paid'),
    //             DB::raw("
    //             SUM(
    //                 CASE 
    //                     WHEN payment_status != 'Paid' 
    //                     THEN paid_amount 
    //                     ELSE 0 
    //                 END
    //             ) as due_amount
    //         ")
    //         )
    //         ->groupBy('mobile');

    //     // 🔍 Search
    //     if ($request->search) {
    //         $query->where(function ($q) use ($request) {
    //             $q
    //                 ->where('customer_name', 'like', '%' . $request->search . '%')
    //                 ->orWhere('mobile', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     $customers = $query->paginate(10);

    //     return view('admin.customers.index', compact('customers'));
    // }

    // public function index(Request $request)
    // {
    //     // die("asdsdf");
    //     $query = DB::table('orders')
    //         ->select(
    //             DB::raw('MIN(customer_name) as customer_name'),
    //             'mobile',
    //             DB::raw('MIN(address) as address'),
    //             DB::raw('COUNT(*) as total_orders'),
    //             DB::raw('SUM(total_amount) as total_amount'),
    //             DB::raw('SUM(paid_amount) as total_paid'),
    //             DB::raw('SUM(total_amount - paid_amount) as due_amount')
    //         )
    //         ->groupBy('mobile');

    //     // 🔍 Search
    //     if ($request->search) {
    //         $query->where(function ($q) use ($request) {
    //             $q->where('customer_name', 'like', '%' . $request->search . '%')
    //             ->orWhere('mobile', 'like', '%' . $request->search . '%');
    //         });
    //     }

    //     $customers = $query->paginate(10);

    //     return view('admin.customers.index', compact('customers'));
    // }

public function index(Request $request)
{
    // Orders (with discount)
    $orders = DB::table('orders')
        ->select(
            'mobile',
            DB::raw('MIN(customer_name) as customer_name'),
            DB::raw('MIN(address) as address'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total_amount - COALESCE(discount,0)) as total_amount')
        )
        ->groupBy('mobile');

    // Payments
    $payments = DB::table('order_payments as p')
        ->join('orders as o', 'o.id', '=', 'p.order_id')
        ->select(
            'o.mobile',
            DB::raw('SUM(p.amount) as total_paid')
        )
        ->groupBy('o.mobile');

    // Merge
    $query = DB::table(DB::raw("({$orders->toSql()}) as o"))
        ->mergeBindings($orders)
        ->leftJoin(DB::raw("({$payments->toSql()}) as p"), 'o.mobile', '=', 'p.mobile')
        ->select(
            'o.customer_name',
            'o.mobile',
            'o.address',
            'o.total_orders',
            'o.total_amount',
            DB::raw('COALESCE(p.total_paid,0) as total_paid'),
            DB::raw('(o.total_amount - COALESCE(p.total_paid,0)) as due_amount')
        );

    // Search
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('o.customer_name', 'like', '%' . $request->search . '%')
              ->orWhere('o.mobile', 'like', '%' . $request->search . '%');
        });
    }

    $customers = $query->paginate(10);

    return view('admin.customers.index', compact('customers'));
}

    /* =============================
       ➕ Create (Optional - not needed)
    ============================= */
    public function create()
    {
        return view('admin.customers.create');
    }

    /* =============================
       💾 Store (Not recommended now)
    ============================= */
    public function store(Request $request)
    {
        return back()->with('error', 'Customer is auto-created via orders');
    }

    /* =============================
       👁 Customer Details (Orders based)
    ============================= */
    public function show($mobile)
    {
        $orders = DB::table('orders')
            ->where('mobile', $mobile)
            ->latest()
            ->get();

        $customer = $orders->first();

        return view('admin.customers.show', compact('customer', 'orders'));
    }

    /* =============================
       ✏️ Edit (Not applicable)
    ============================= */
    public function edit($id)
    {
        return back()->with('error', 'Edit not supported (order आधारित data)');
    }

    public function update(Request $request, $id)
    {
        return back()->with('error', 'Update not supported');
    }

    /* =============================
       ❌ Delete (Optional)
    ============================= */
    public function destroy($mobile)
    {
        DB::table('orders')->where('mobile', $mobile)->delete();

        return back()->with('success', 'Customer orders deleted');
    }

    /* =============================
       💰 Due Customers
    ============================= */
    // public function dueCustomers()
    // {
    //     $customers = DB::table('orders')
    //         ->select(
    //             'customer_name',
    //             'mobile',
    //             DB::raw("
    //                 SUM(
    //                     CASE 
    //                         WHEN payment_status != 'Paid' 
    //                         THEN paid_amount 
    //                         ELSE 0 
    //                     END
    //                 ) as due_amount
    //             ")
    //         )
    //         ->groupBy('customer_name', 'mobile')
    //         ->having('due_amount', '>', 0)
    //         ->get();

    //     return view('admin.customers.due', compact('customers'));
    // }

    // public function dueCustomers()
    // {
    //     $customers = DB::table('orders')
    //         ->select(
    //             DB::raw('MIN(customer_name) as customer_name'),
    //             'mobile',
    //             DB::raw('SUM(total_amount - paid_amount) as due_amount')
    //         )
    //         ->groupBy('mobile')
    //         ->having('due_amount', '>', 0)
    //         ->get();

    //     return view('admin.customers.due', compact('customers'));
    // }

    public function dueCustomers()
    {
        $customers = DB::table('orders')
            ->select(
                DB::raw('MIN(customer_name) as customer_name'),
                'mobile',
                DB::raw('SUM((total_amount - discount) - paid_amount) as due_amount')
            )
            ->groupBy('mobile')
            ->having('due_amount', '>', 0)
            ->get();

        return view('admin.customers.due', compact('customers'));
    }

    /* =============================
       📊 Ledger (All Orders)
    ============================= */
    public function ledger($mobile)
    {
        $orders = DB::table('orders')
            ->where('mobile', $mobile)
            ->latest()
            ->get();

        $customer = $orders->first();

        return view('admin.customers.ledger', compact('customer', 'orders'));
    }
}

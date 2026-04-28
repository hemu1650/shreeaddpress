<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use DB;

class CustomerController extends Controller
{
    // public function index(Request $request)
    // {
    //     $customers = Order::select(
    //         DB::raw('MIN(customer_name) as customer_name'),  // name pick once
    //         'mobile',
    //         DB::raw('COUNT(id) as total_orders'),
    //         DB::raw('SUM(paid_amount) as total_paid_amount'),
    //         DB::raw('MAX(created_at) as last_order_date')
    //     )
    //         ->whereNotNull('mobile')
    //         ->groupBy('mobile')  // 🔥 only mobile = unique customer
    //         ->orderByDesc(DB::raw('MAX(created_at)'));

    //     // 🔍 Search filter
    //     if ($request->search) {
    //         $customers->where(function ($q) use ($request) {
    //             $q
    //                 ->where('customer_name', 'like', '%' . trim($request->search) . '%')
    //                 ->orWhere('mobile', 'like', '%' . trim($request->search) . '%');
    //         });
    //     }

    //     $data = $customers->get();

    //     // 🔄 NULL → ""
    //     $data = $data->map(function ($item) {
    //         return $this->replaceNullWithEmpty($item);
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Unique customer list',
    //         'total_customers' => $data->count(),
    //         'data' => $data
    //     ]);
    // }

    public function index(Request $request)
    {
        $query = Order::select(
            DB::raw('MIN(customer_name) as customer_name'),
            'mobile',
            DB::raw('MIN(address) as address'), // 👈 ADD THIS
            DB::raw('COUNT(id) as total_orders'),
            DB::raw('SUM(paid_amount) as total_paid_amount'),
            DB::raw('MAX(created_at) as last_order_date')
        )
            ->whereNotNull('mobile');

        // 🔍 SEARCH (name OR mobile)
        if (!empty($request->search)) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q
                    ->where('customer_name', 'LIKE', "%{$search}%")
                    ->orWhere('mobile', 'LIKE', "%{$search}%");
            });
        }

        // 🔥 UNIQUE CUSTOMER (by mobile)
        $query
            ->groupBy('mobile')
            ->orderByDesc(DB::raw('MAX(created_at)'));

        $data = $query->get();

        // NULL → ""
        $data = $data->map(function ($item) {
            return $this->replaceNullWithEmpty($item);
        });

        return response()->json([
            'status' => true,
            'message' => 'Customer list',
            'total_customers' => $data->count(),
            'data' => $data
        ]);
    }

    private function replaceNullWithEmpty($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'replaceNullWithEmpty'], $data);
        }

        if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = $value === null ? '' : $value;
            }
        }

        return $data;
    }
}

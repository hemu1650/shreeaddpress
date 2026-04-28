<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class OrderController extends Controller
{
   
    // public function createOrder(Request $request)
    // {
    //     Log::info('Create Order API Hit', $request->all());

    //     try {
    //         $request->validate([
    //             'customer_name' => 'required',
    //             'mobile' => 'required',
    //             'staff_id' => 'required|exists:users,id',
    //             'items' => 'required',
    //             'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
    //             'pdf' => 'nullable|file|mimes:pdf|max:5000',
    //             'audio' => 'nullable|file|mimes:mp3,wav|max:5000'
    //         ]);

    //         Log::info('Validation Passed');

    //         $imagePath = null;
    //         $pdfPath = null;
    //         $audioPath = null;

    //         /* IMAGE */
    //         if ($request->hasFile('image')) {
    //             Log::info('Image Upload Started');

    //             $imageName = time() . '_' . $request->file('image')->getClientOriginalName();

    //             $request->file('image')->move(public_path('orders/images'), $imageName);

    //             $imagePath = url('public/orders/images/' . $imageName);

    //             Log::info('Image Uploaded', ['path' => $imagePath]);
    //         }

    //         /* PDF */
    //         if ($request->hasFile('pdf')) {
    //             Log::info('PDF Upload Started');

    //             $pdfName = time() . '_' . $request->file('pdf')->getClientOriginalName();

    //             $request->file('pdf')->move(public_path('orders/pdfs'), $pdfName);

    //             $pdfPath = url('public/orders/pdfs/' . $pdfName);

    //             Log::info('PDF Uploaded', ['path' => $pdfPath]);
    //         }

    //         /* AUDIO */
    //         if ($request->hasFile('audio')) {
    //             Log::info('Audio Upload Started');

    //             $audioName = time() . '_' . $request->file('audio')->getClientOriginalName();

    //             $request->file('audio')->move(public_path('orders/audio'), $audioName);

    //             $audioPath = url('public/orders/audio/' . $audioName);

    //             Log::info('Audio Uploaded', ['path' => $audioPath]);
    //         }

    //         Log::info('Creating Order');

    //         $order = Order::create([
    //             'customer_name' => $request->customer_name,
    //             'mobile' => $request->mobile,
    //             'address' => $request->address,
    //             'staff_id' => $request->staff_id,
    //             'image' => $imagePath,
    //             'pdf' => $pdfPath,
    //             'audio' => $audioPath
    //         ]);

    //         Log::info('Order Created', ['order_id' => $order->id]);

    //         $items = json_decode($request->items, true);

    //         foreach ($items as $item) {
    //             Log::info('Adding Item', $item);

    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'size' => $item['size'],
    //                 'material' => $item['material'],
    //                 'quantity' => $item['quantity'],
    //                 'rate' => $item['rate']
    //             ]);
    //         }

    //         Log::info('Items Inserted Successfully');

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Order created successfully',
    //             'order' => $order
    //         ]);
    //     } catch (\Exception $e) {
    //         Log::error('Create Order Error', [
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile()
    //         ]);

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function createOrder(Request $request)
{
    Log::info('Create Order API Hit', $request->all());

    try {
        $request->validate([
            'customer_name' => 'required',
            'mobile' => 'required',
            'staff_id' => 'required|exists:users,id',
            'items' => 'required',
            'image' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'pdf' => 'nullable|file|mimes:pdf|max:5000',
            'audio' => 'nullable|file|mimes:mp3,wav|max:5000'
        ]);

        Log::info('Validation Passed');

        // ✅ Decode items safely
        $items = json_decode($request->items, true);

        if (!$items || !is_array($items)) {
            throw new \Exception("Invalid items data");
        }

        // ✅ Calculate total
        $totalAmount = 0;
        foreach ($items as $item) {
            if (!isset($item['quantity'], $item['rate'])) {
                throw new \Exception("Invalid item structure");
            }

            $totalAmount += ($item['quantity'] * $item['rate']);
        }

        $discount = $request->discount ?? 0;

        $imagePath = null;
        $pdfPath = null;
        $audioPath = null;

        /* IMAGE */
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
            $request->file('image')->move(public_path('orders/images'), $imageName);
            $imagePath = url('public/orders/images/' . $imageName);
        }

        /* PDF */
        if ($request->hasFile('pdf')) {
            $pdfName = time() . '_' . $request->file('pdf')->getClientOriginalName();
            $request->file('pdf')->move(public_path('orders/pdfs'), $pdfName);
            $pdfPath = url('public/orders/pdfs/' . $pdfName);
        }

        /* AUDIO */
        if ($request->hasFile('audio')) {
            $audioName = time() . '_' . $request->file('audio')->getClientOriginalName();
            $request->file('audio')->move(public_path('orders/audio'), $audioName);
            $audioPath = url('public/orders/audio/' . $audioName);
        }

        Log::info('Creating Order');

        // ✅ FIX: total_amount + discount + paid_amount add kiya
        $order = Order::create([
            'customer_name' => $request->customer_name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'staff_id' => $request->staff_id,
            'total_amount' => $totalAmount,
            'discount' => $discount,
            'paid_amount' => 0, // always 0 on create
            'image' => $imagePath,
            'pdf' => $pdfPath,
            'audio' => $audioPath
        ]);

        Log::info('Order Created', ['order_id' => $order->id]);

        foreach ($items as $item) {
            Log::info('Adding Item', $item);

            OrderItem::create([
                'order_id' => $order->id,
                'size' => $item['size'] ?? null,
                'material' => $item['material'] ?? null,
                'quantity' => $item['quantity'],
                'rate' => $item['rate']
            ]);
        }

        Log::info('Items Inserted Successfully');

        // ❗ RESPONSE SAME rakha hai (NO CHANGE)
        return response()->json([
            'status' => true,
            'message' => 'Order created successfully',
            'order' => $order
        ]);

    } catch (\Exception $e) {
        Log::error('Create Order Error', [
            'message' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ]);
    }
}

    // public function orderList(Request $request)
    // {
    //     $query = Order::with([
    //         'items',
    //         'staff:id,name'
    //     ]);

    //     $filter = strtolower(trim($request->get('filter')));
    //     $search = trim($request->get('search'));

    //     /* =============================
    //        🔍 SEARCH
    //     ============================= */
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q
    //                 ->where('customer_name', 'like', "%{$search}%")
    //                 ->orWhere('mobile', 'like', "%{$search}%");
    //         });
    //     }

    //     /* =============================
    //        📦 ORDER STATUS
    //     ============================= */
    //     if ($request->filled('order_status')) {
    //         $query->where('order_status', $request->order_status);
    //     }

    //     /* =============================
    //        💰 PAYMENT STATUS
    //     ============================= */
    //     if ($request->filled('payment_status')) {
    //         $query->where('payment_status', $request->payment_status);
    //     }

    //     /* =============================
    //        💰 FILTER TYPE (FIXED)
    //     ============================= */
    //     if ($filter == 'due') {
    //         // ✅ Only unpaid orders
    //         $query->where('payment_status', '!=', 'Paid');
    //     } elseif ($filter == 'paid') {
    //         $query->where('payment_status', 'Paid');
    //     } elseif ($filter == 'today_due') {
    //         $query
    //             ->whereDate('due_date', now()->toDateString())
    //             ->where('payment_status', '!=', 'Paid');
    //     } elseif ($filter == 'overdue') {
    //         $query
    //             ->whereDate('due_date', '<', now()->toDateString())
    //             ->where('payment_status', '!=', 'Paid');
    //     }

    //     /* =============================
    //        👨‍💼 STAFF FILTER (FIXED)
    //     ============================= */
    //     if ($request->filled('staff_id')) {
    //         $query->where('staff_id', $request->staff_id);
    //     }

    //     /* =============================
    //        📅 DATE FILTER
    //     ============================= */
    //     if ($request->filled('date')) {
    //         $query->whereDate('created_at', $request->date);
    //     }

    //     /* =============================
    //        📅 DATE RANGE (BONUS)
    //     ============================= */
    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $query->whereBetween('created_at', [
    //             $request->from_date . ' 00:00:00',
    //             $request->to_date . ' 23:59:59'
    //         ]);
    //     }

    //     /* =============================
    //        📅 DUE DATE FILTER (FIXED)
    //     ============================= */
    //     if ($request->filled('due_date')) {
    //         $query->whereDate('due_date', $request->due_date);
    //     }

    //     /* =============================
    //        SORTING
    //     ============================= */
    //     $orders = $query->latest()->get();

    //     return response()->json([
    //         'status' => true,
    //         'filter_used' => $filter,
    //         'payment_status_used' => $request->payment_status,
    //         'total' => $orders->count(),
    //         'data' => $orders
    //     ]);
    // }

    // public function orderList(Request $request)
    // {
    //     $query = Order::with(['items', 'staff:id,name', 'payments']);

    //     $filter = strtolower(trim($request->get('filter')));
    //     $search = trim($request->get('search'));

    //     // 🔍 SEARCH
    //     if (!empty($search)) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('customer_name', 'like', "%{$search}%")
    //             ->orWhere('mobile', 'like', "%{$search}%");
    //         });
    //     }

    //     // 📦 ORDER STATUS
    //     if ($request->filled('order_status')) {
    //         $query->where('order_status', $request->order_status);
    //     }

    //     // 👨‍💼 STAFF
    //     if ($request->filled('staff_id')) {
    //         $query->where('staff_id', $request->staff_id);
    //     }

    //     // 📅 DATE
    //     if ($request->filled('date')) {
    //         $query->whereDate('created_at', $request->date);
    //     }

    //     if ($request->filled('from_date') && $request->filled('to_date')) {
    //         $query->whereBetween('created_at', [
    //             $request->from_date . ' 00:00:00',
    //             $request->to_date . ' 23:59:59'
    //         ]);
    //     }

    //     $orders = $query->latest()->get();

    //     // ✅ FIXED CALCULATION
    //     $orders->map(function ($order) {

    //         $paid = $order->payments->sum('amount');
    //         $final = $order->total_amount - $order->discount;

    //         $order->paid_amount = $paid;
    //         $order->final_amount = $final;
    //         $order->due_amount = $final - $paid;

    //         // ✅ AUTO STATUS
    //         // if ($paid == 0) {
    //         //     $order->payment_status = 'Pending';
    //         // } elseif ($paid < $final) {
    //         //     $order->payment_status = 'Partial';
    //         // } else {
    //         //     $order->payment_status = 'Paid';
    //         // }

    //         return $order;
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'total' => $orders->count(),
    //         'data' => $orders
    //     ]);
    // }

 public function orderList(Request $request)
{
    $query = Order::with(['items', 'staff:id,name'])
        ->select('orders.*')
        ->selectRaw('
            (
                SELECT COALESCE(SUM(op.amount),0)
                FROM order_payments op
                WHERE op.order_id = orders.id
            ) as paid_amount
        ');

    $filter = strtolower(trim($request->get('filter')));
    $search = trim($request->get('search'));

    // 🔍 SEARCH
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('customer_name', 'like', "%{$search}%")
              ->orWhere('mobile', 'like', "%{$search}%");
        });
    }

    // 📦 ORDER STATUS
    if ($request->filled('order_status')) {
        $query->where('order_status', $request->order_status);
    }

    // 👨‍💼 STAFF
    if ($request->filled('staff_id')) {
        $query->where('staff_id', $request->staff_id);
    }

    // 📅 DATE
    if ($request->filled('date')) {
        $query->whereDate('created_at', $request->date);
    }

    if ($request->filled('from_date') && $request->filled('to_date')) {
        $query->whereBetween('created_at', [
            $request->from_date . ' 00:00:00',
            $request->to_date . ' 23:59:59'
        ]);
    }

    // ✅ 🔥 REAL FIX (this will actually filter)
    if ($filter === 'due') {
        $query->whereRaw('
            (total_amount - COALESCE(discount,0) -
            (
                SELECT COALESCE(SUM(op.amount),0)
                FROM order_payments op
                WHERE op.order_id = orders.id
            )) > 0
        ');
    }

    $orders = $query->latest()->get();

    // ✅ FINAL CALCULATION
    $orders->map(function ($order) {
        $paid = $order->paid_amount ?? 0;
        $final = $order->total_amount - ($order->discount ?? 0);

        $order->final_amount = $final;
        $order->due_amount = $final - $paid;

        return $order;
    });

    return response()->json([
        'status' => true,
        'total' => $orders->count(),
        'data' => $orders
    ]);
}

    // 3️⃣ Order Details
    public function orderDetails($id)
    {
        // dd("sdfsf");
        // $order = Order::with('items')->find($id);
        $order = Order::with(['items', 'payments'])->find($id);

        if ($order) {
            $order->payments->transform(function ($payment) {
                $payment->payment_date = \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d');
                return $payment;
            });
        }        

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $order
        ]);
    }

    // 4️⃣ Delete Order
    public function deleteOrder($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found'
            ], 404);
        }

        $order->delete();

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully'
        ]);
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'order_status' => 'nullable|in:Pending,Printing,Ready,Delivered',

            // Payment
            'paid_amount' => 'nullable|numeric|min:1',
            'payment_mode' => 'nullable|in:Cash,UPI,CHECK,NEFT/RTGS',
            'payment_date' => 'nullable|date',

            // Manual override allowed
            'payment_status' => 'nullable|in:Pending,Advance,Paid',

            'due_date' => 'nullable|date',
        ]);

        $order = Order::with(['items', 'payments'])->findOrFail($request->order_id);

        /** ==============================
         * ✅ ORDER STATUS
         * ============================== */
        if ($request->filled('order_status')) {
            $order->order_status = $request->order_status;
        }

        /** ==============================
         * ✅ DUE DATE
         * ============================== */
        if ($request->has('due_date')) {
            $order->due_date = $request->due_date;
        }

        /** ==============================
         * ✅ PAYMENT INSERT
         * ============================== */
        if ($request->filled('paid_amount') && $request->paid_amount > 0) {

            if (!$request->filled('payment_mode')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Payment mode is required when amount is entered'
                ], 422);
            }

            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $request->paid_amount,
                'payment_mode' => $request->payment_mode,
                'payment_date' => $request->payment_date ?? now(),
            ]);
        }

        /** ==============================
         * ✅ TOTAL PAID (FRESH CALCULATION)
         * ============================== */
        $totalPaid = $order->payments()->sum('amount');

        // backward compatibility
        if ($order->paid_amount > $totalPaid) {
            $totalPaid = $order->paid_amount;
        }

        $order->paid_amount = $totalPaid;

        /** ==============================
         * ✅ ORDER TOTAL
         * ============================== */
        $orderTotal = $order->items->sum(function ($item) {
            return ($item->quantity ?? 0) * ($item->rate ?? 0);
        });

        /** ==============================
         * ✅ PAYMENT STATUS (HYBRID LOGIC)
         * ============================== */
        if ($request->filled('payment_status')) {

            // 🔥 Admin override allowed BUT safe
            if ($request->payment_status === 'Paid' && $totalPaid < $orderTotal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cannot mark as Paid. Full amount not received.'
                ], 422);
            }

            $order->payment_status = $request->payment_status;

        } else {
            // ✅ AUTO MODE
            if ($totalPaid <= 0) {
                $order->payment_status = 'Pending';
            } elseif ($totalPaid < $orderTotal) {
                $order->payment_status = 'Advance';
            } else {
                $order->payment_status = 'Paid';
            }
        }

        $order->save();

        /** ==============================
         * ✅ RESPONSE (FULL DETAILS)
         * ============================== */
        return response()->json([
            'status' => true,
            'message' => 'Order updated successfully',

            'order' => [
                'id' => $order->id,
                'customer_name' => $order->customer_name,
                'mobile' => $order->mobile,

                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,

                'total_amount' => $orderTotal,
                'paid_amount' => $totalPaid,
                'due_amount' => max(0, $orderTotal - $totalPaid),

                'due_date' => $order->due_date,
                'updated_at' => $order->updated_at,
            ],

            'payments' => $order->payments()->latest()->get()->map(function ($p) {
                return [
                    'id' => $p->id,
                    'amount' => $p->amount,
                    'payment_mode' => $p->payment_mode,
                    'payment_date' => $p->payment_date,
                ];
            }),

            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'size' => $item->size,
                    'material' => $item->material,
                    'quantity' => $item->quantity,
                    'rate' => $item->rate,
                    'total' => ($item->quantity ?? 0) * ($item->rate ?? 0),
                ];
            }),
        ]);
    }

    public function ordersByStaff($staff_id)
    {
        $orders = Order::with('items')
            ->where('staff_id', $staff_id)
            ->latest()
            ->get();

        return response()->json([
            'status' => true,
            'staff_id' => $staff_id,
            'orders' => $orders
        ]);
    }


    public function paymentlist(Request $request)
    {
        try {

            $query = OrderPayment::with('order');

            // ✅ Single Date Filter
            if ($request->filled('date')) {
                $query->whereDate('payment_date', $request->date);
            }

            // ✅ Date Range Filter
            if ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('payment_date', [
                    Carbon::parse($request->from_date)->startOfDay(),
                    Carbon::parse($request->to_date)->endOfDay()
                ]);
            }

            $payments = $query->latest()->get();

            $data = $payments->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'amount' => $payment->amount,
                    'payment_mode' => $payment->payment_mode,

                    // ✅ Force date format
                    'payment_date' => Carbon::parse($payment->payment_date)->format('Y-m-d'),

                    'notes' => $payment->notes,

                    'customer' => [
                        'customer_name' => optional($payment->order)->customer_name,
                        'mobile' => optional($payment->order)->mobile,
                        'address' => optional($payment->order)->address,
                        'order_status' => optional($payment->order)->order_status,
                        'payment_status' => optional($payment->order)->payment_status,
                        'paid_amount' => optional($payment->order)->paid_amount,
                        'due_date' => optional($payment->order)->due_date,
                    ]
                ];
            });

            return response()->json([
                'status' => true,
                'total' => $data->count(),
                'data' => $data
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

}

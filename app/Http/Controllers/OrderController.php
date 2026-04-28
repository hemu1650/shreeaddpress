<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\user;
use App\Models\OrderPayment;
use App\Models\OrderItem;


class OrderController extends Controller
{

    public function index(Request $request)
    {
        $query = Order::with([
            'staff',
            'latestPayment'
        ])->latest();

        // 🔍 SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                ->orWhere('mobile', 'like', '%' . $request->search . '%')
                ->orWhere('id', $request->search);
            });
        }

        // 📦 ORDER STATUS
        if ($request->order_status) {
            $query->where('order_status', $request->order_status);
        }

        // 💰 PAYMENT STATUS
        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        // 💳 PAYMENT MODE
        if ($request->payment_mode) {
            $query->whereHas('latestPayment', function ($q) use ($request) {
                $q->where('payment_mode', $request->payment_mode);
            });
        }

        // 📅 SINGLE DATE (existing)
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        // ✅ 📅 FROM - TO DATE (NEW)
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        // 👨‍💼 STAFF
        if ($request->staff) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->where('name', $request->staff);
            });
        }

        $orders = $query->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }


    public function show($id)
    {
        $order = Order::with(['items', 'payments'])->findOrFail($id);

        return view('admin.orders.view', compact('order'));
    }

    // public function updatePayment(Request $request, $id)
    // {
    //     // ✅ ADMIN CHECK
    //     if (auth()->user()->role !== 'admin') {
    //         abort(403, 'Only admin can update payment');
    //     }

    //     $order = Order::findOrFail($id);

    //     $order->payment_status = $request->payment_status;
    //     $order->paid_amount = $request->paid_amount;
    //     $order->save();

    //     return back()->with('success', 'Payment updated');
    // }

    public function updatePayment(Request $request, $id)
    {
        // ❌ OLD (WRONG - direct overwrite)
        /*
        $order->payment_status = $request->payment_status;
        $order->paid_amount = $request->paid_amount;
        */

        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admin can update payment');
        }

        $order = Order::findOrFail($id);

        // ✅ FIX: sync from payments table
        $totalPaid = $order->payments()->sum('amount');

        $order->paid_amount = $totalPaid;

        // ✅ FIX: proper status
        $total = $this->getOrderTotal($order);

        if ($totalPaid == 0) {
            $status = 'Pending';
        } elseif ($totalPaid < $total) {
            $status = 'Partial';
        } elseif ($totalPaid == $total) {
            $status = 'Paid';
        } else {
            $status = 'Advance';
        }

        $order->payment_status = $status;
        $order->save();

        return back()->with('success', 'Payment updated');
    }

    // EDIT PAGE

    // public function edit($id)
    // {
    //     $order = Order::findOrFail($id);
    //     return view('admin.orders.edit', compact('order'));
    // }

    public function edit($id)
    {
        $order = Order::findOrFail($id);
        $staffs = \App\Models\User::where('role', 'staff')->get(); // 🔥 ADD THIS

        return view('admin.orders.edit', compact('order','staffs'));
    }

    // UPDATE ORDER

    // public function update(Request $request, $id)
    // {
    //     $order = Order::findOrFail($id);

    //     $order->update([
    //         'customer_name' => $request->customer_name,
    //         'mobile' => $request->mobile,
    //         'address' => $request->address,
    //         'order_status' => $request->order_status,
    //         'payment_status' => $request->payment_status,
    //         'paid_amount' => $request->paid_amount,
    //         'staff_id' => $request->staff_id,
    //     ]);

    //     // ITEMS UPDATE
    //     foreach ($request->items as $itemData) {
    //         $item = \App\Models\OrderItem::find($itemData['id']);

    //         if ($item) {
    //             $item->update([
    //                 'size' => $itemData['size'],
    //                 'material' => $itemData['material'],
    //                 'quantity' => $itemData['quantity'],
    //                 'rate' => $itemData['rate'],
    //             ]);
    //         }
    //     }

    //     return redirect()->route('orders')->with('success', 'Order updated successfully');
    // }

    // public function update(Request $request, $id)
    // {
    //     $order = Order::findOrFail($id);

    //     // =============================
    //     // ✅ VALIDATION
    //     // =============================
    //     $request->validate([
    //         'items' => 'required|array',
    //     ]);

    //     // =============================
    //     // ✅ CALCULATE TOTAL
    //     // =============================

    //     $total = 0;

    //     foreach ($request->items as $item) {
    //         $quantity = $item['quantity'] ?? 0;
    //         $rate = $item['rate'] ?? 0;

    //         $total += ($quantity * $rate);
    //     }

    //     $discount = $request->discount ?? 0;
    //     $final = max($total - $discount, 0);
    //     $paid = $request->paid_amount ?? 0;

    //     // =============================
    //     // ✅ PAYMENT STATUS AUTO
    //     // =============================

    //     if ($paid == 0) {
    //         $autoStatus = 'Pending';
    //     } elseif ($paid < $final) {
    //         $autoStatus = 'Partial';
    //     } elseif ($paid == $final) {
    //         $autoStatus = 'Paid';
    //     } else {
    //         $autoStatus = 'Advance';
    //     }

    //     $paymentStatus = $request->payment_status ?: $autoStatus;

    //     // =============================
    //     // ✅ UPDATE ORDER
    //     // =============================

    //     $order->update([
    //         'customer_name' => $request->customer_name,
    //         'mobile' => $request->mobile,
    //         'address' => $request->address,
    //         'order_status' => $request->order_status,
    //         'payment_status' => $paymentStatus,
    //         'paid_amount' => $paid,
    //         'staff_id' => $request->staff_id,

    //         'total_amount' => $total,
    //         // 'discount' => $discount,
    //         // 'final_amount' => $final,
    //     ]);

    //     // =============================
    //     // ✅ UPDATE ITEMS (FIXED)
    //     // =============================

    //     foreach ($request->items as $itemData) {

    //         $item = \App\Models\OrderItem::find($itemData['id']);

    //         if ($item) {
    //             $quantity = $itemData['quantity'] ?? 0;
    //             $rate = $itemData['rate'] ?? 0;

    //             $item->update([
    //                 'size' => $itemData['size'] ?? null,
    //                 'material' => $itemData['material'] ?? null,
    //                 'quantity' => $quantity, // ✅ correct column
    //                 'rate' => $rate,
    //                 'amount' => $quantity * $rate, // ✅ correct calculation
    //             ]);
    //         }
    //     }

    //     return redirect()->route('orders')
    //         ->with('success', 'Order updated successfully');
    // }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'items' => 'required|array',
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $quantity = $item['quantity'] ?? 0;
            $rate = $item['rate'] ?? 0;
            $total += ($quantity * $rate);
        }

        $discount = $request->discount ?? 0;
        $final = max($total - $discount, 0);

        // ❌ OLD (WRONG - manual paid overwrite)
        /*
        $paid = $request->paid_amount ?? 0;
        */

        // ✅ FIX: always from payments table
        $paid = $order->payments()->sum('amount');

        // ✅ STATUS FIX
        if ($paid == 0) {
            $autoStatus = 'Pending';
        } elseif ($paid < $final) {
            $autoStatus = 'Partial';
        } elseif ($paid == $final) {
            $autoStatus = 'Paid';
        } else {
            $autoStatus = 'Advance';
        }

        $order->update([
            'customer_name' => $request->customer_name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'order_status' => $request->order_status,
            'payment_status' => $autoStatus,
            'paid_amount' => $paid,
            'staff_id' => $request->staff_id,

            'total_amount' => $total,
            'discount' => $discount,
            // 'final_amount' => $final, // ❗ only if DB column exists
        ]);

        foreach ($request->items as $itemData) {
            $item = OrderItem::find($itemData['id']);

            if ($item) {
                $quantity = $itemData['quantity'] ?? 0;
                $rate = $itemData['rate'] ?? 0;

                $item->update([
                    'size' => $itemData['size'] ?? null,
                    'material' => $itemData['material'] ?? null,
                    'quantity' => $quantity,
                    'rate' => $rate,
                ]);
            }
        }

        return redirect()->route('orders')
            ->with('success', 'Order updated successfully');
    }

    // DELETE ORDER
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return redirect()->route('orders')->with('success', 'Order deleted successfully');
    }

    // public function addPayment(Request $request, $id)
    // {
    //     if (auth()->user()->role !== 'admin') {
    //         abort(403, 'Only admin can add payment');
    //     }

    //     $order = Order::findOrFail($id);

    //     // 1. Create payment entry
    //     \App\Models\OrderPayment::create([
    //         'order_id' => $order->id,
    //         'amount' => $request->amount,
    //         'payment_mode' => $request->payment_mode,
    //         'notes' => $request->notes,
    //         'payment_date' => now(),
    //     ]);

    //     // 2. Update total paid amount
    //     $totalPaid = $order->payments()->sum('amount');

    //     $order->paid_amount = $totalPaid;

    //     // 3. Update payment status
    //     if ($totalPaid == 0) {
    //         $order->payment_status = 'Pending';
    //     } elseif ($totalPaid < $this->getOrderTotal($order)) {
    //         $order->payment_status = 'Partial';
    //     } else {
    //         $order->payment_status = 'Paid';
    //     }

    //     $order->save();

    //     return back()->with('success', 'Payment added successfully');
    // }

    public function addPayment(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Only admin can add payment');
        }

        $order = Order::findOrFail($id);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'notes' => $request->notes,
            'payment_date' => now(),
        ]);

        $totalPaid = $order->payments()->sum('amount');
        $order->paid_amount = $totalPaid;

        $total = $this->getOrderTotal($order);

        // ❌ OLD (no advance handling)
        /*
        else {
            $order->payment_status = 'Paid';
        }
        */

        // ✅ FIX
        if ($totalPaid == 0) {
            $order->payment_status = 'Pending';
        } elseif ($totalPaid < $total) {
            $order->payment_status = 'Partial';
        } elseif ($totalPaid == $total) {
            $order->payment_status = 'Paid';
        } else {
            $order->payment_status = 'Advance';
        }

        $order->save();

        return back()->with('success', 'Payment added successfully');
    }

    // private function getOrderTotal($order)
    // {
    //     return $order->items->sum(function ($item) {
    //         return $item->quantity * $item->rate;
    //     });
    // }

    private function getOrderTotal($order)
    {
        // ❌ OLD
        /*
        return $order->items->sum(function ($item) {
            return $item->quantity * $item->rate;
        });
        */

        // ✅ FIX (NULL safe)
        return $order->items->sum(function ($item) {
            return ($item->quantity ?? 0) * ($item->rate ?? 0);
        });
    }

    // public function deletePayment($id)
    // {
    //     $payment = OrderPayment::findOrFail($id);
    //     $order = $payment->order;

    //     $payment->delete();

    //     // update total
    //     $totalPaid = $order->payments()->sum('amount');
    //     $order->paid_amount = $totalPaid;

    //     $order->payment_status = $totalPaid == 0 ? 'Pending' : ($totalPaid < $this->getOrderTotal($order) ? 'Partial' : 'Paid');

    //     $order->save();

    //     return back()->with('success','Payment deleted');
    // }

    public function deletePayment($id)
    {
        $payment = OrderPayment::findOrFail($id);
        $order = $payment->order;

        $payment->delete();

        $totalPaid = $order->payments()->sum('amount');
        $order->paid_amount = $totalPaid;

        $total = $this->getOrderTotal($order);

        // ❌ OLD
        /*
        $order->payment_status = $totalPaid == 0 ? 'Pending' : ($totalPaid < $this->getOrderTotal($order) ? 'Partial' : 'Paid');
        */

        // ✅ FIX
        if ($totalPaid == 0) {
            $order->payment_status = 'Pending';
        } elseif ($totalPaid < $total) {
            $order->payment_status = 'Partial';
        } elseif ($totalPaid == $total) {
            $order->payment_status = 'Paid';
        } else {
            $order->payment_status = 'Advance';
        }

        $order->save();

        return back()->with('success','Payment deleted');
    }

    // public function updatePaymentRecord(Request $request, $id)
    // {
    //     $payment = OrderPayment::findOrFail($id);

    //     $payment->update([
    //         'amount' => $request->amount,
    //         'payment_mode' => $request->payment_mode,
    //         'notes' => $request->notes,
    //     ]);

    //     $order = $payment->order;

    //     $totalPaid = $order->payments()->sum('amount');
    //     $order->paid_amount = $totalPaid;

    //     if ($totalPaid == 0) {
    //         $order->payment_status = 'Pending';
    //     } elseif ($totalPaid < $this->getOrderTotal($order)) {
    //         $order->payment_status = 'Partial';
    //     } else {
    //         $order->payment_status = 'Paid';
    //     }

    //     $order->save();

    //     return back()->with('success','Payment updated');
    // }

    public function updatePaymentRecord(Request $request, $id)
    {
        $payment = OrderPayment::findOrFail($id);

        $payment->update([
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'notes' => $request->notes,
        ]);

        $order = $payment->order;

        $totalPaid = $order->payments()->sum('amount');
        $order->paid_amount = $totalPaid;

        $total = $this->getOrderTotal($order);

        if ($totalPaid == 0) {
            $order->payment_status = 'Pending';
        } elseif ($totalPaid < $total) {
            $order->payment_status = 'Partial';
        } elseif ($totalPaid == $total) {
            $order->payment_status = 'Paid';
        } else {
            $order->payment_status = 'Advance';
        }

        $order->save();

        return back()->with('success','Payment updated');
    }

    public function create()
    {
        $customers = Customer::latest()->get();

        // ✅ users table se staff nikalna
        $staffs = User::where('role', 'staff')->get();

        return view('admin.orders.create', compact('customers','staffs'));
    }

    // public function store(Request $request)
    // {
    //     // dd($request->all());
    //     // ✅ Validation
    //     $request->validate([
    //         'customer_name' => 'nullable|string',
    //         'mobile' => 'nullable|string',
    //         'items' => 'required|array',
    //     ]);

    //     $customer_id = null;
    //     $customer = null;

    //     // =============================
    //     // ✅ CUSTOMER LOGIC
    //     // =============================

    //     if ($request->customer_id == 'new') {

    //         $customer = Customer::create([
    //             'name' => $request->new_name,
    //             'phone' => $request->new_mobile,
    //             'address' => $request->new_address,
    //         ]);

    //         $customer_id = $customer->id;

    //     } elseif ($request->customer_id) {

    //         $customer = Customer::find($request->customer_id);
    //         $customer_id = $customer?->id;
    //     }

    //     // =============================
    //     // ✅ STAFF LOGIC
    //     // =============================

    //     $staff_id = auth()->user()->role == 'staff'
    //         ? auth()->id()
    //         : $request->staff_id;

    //     // =============================
    //     // ✅ CALCULATE TOTAL
    //     // =============================

    //     $total = 0;

    //     foreach ($request->items as $item) {
    //         $qty  = $item['qty'] ?? 0;
    //         $rate = $item['rate'] ?? 0;

    //         $total += ($qty * $rate);
    //     }

    //     $discount = $request->discount ?? 0;
    //     $finalAmount = $total - $discount;

    //     // =============================
    //     // ✅ CREATE ORDER
    //     // =============================

    //     $order = Order::create([

    //         'customer_id'   => $customer_id,
    //         'staff_id'      => $staff_id,

    //         'customer_name' => $request->customer_name ?? ($customer->name ?? ''),
    //         'mobile'        => $request->mobile ?? ($customer->phone ?? ''),
    //         'address'       => $request->address ?? ($customer->address ?? ''),

    //         // 'total_amount'  => $total,
    //         // 'discount'      => $discount,
    //         'paid_amount'   => $finalAmount,

    //         // 'payment_method'=> $request->payment_method,
    //         'payment_status'=> $request->payment_status,

    //         'order_status'  => $request->order_status,
    //         // 'notes' => $request->notes,
    //     ]);

    //     // =============================
    //     // ✅ SAVE ITEMS
    //     // =============================

    //     foreach ($request->items as $item) {

    //         if (!empty($item['qty']) && !empty($item['rate'])) {

    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'size'     => $item['size'] ?? null,
    //                 'material' => $item['material'] ?? null,
    //                 'qty'      => $item['qty'],
    //                 'rate'     => $item['rate'],
    //                 'amount'   => $item['qty'] * $item['rate'],
    //             ]);
    //         }
    //     }

    //     // 🔥 PAYMENT INSERT (IMPORTANT)
    //     OrderPayment::create([
    //         'order_id' => $order->id,
    //         'amount' => $request->paid_amount,
    //         'payment_mode' => $request->payment_method,
    //         'payment_date' => now(),
    //         'notes' => $request->notes,

    //     ]);

    //     // =============================
    //     // ✅ REDIRECT
    //     // =============================

    //     return redirect()->route('orders')
    //         ->with('success', 'Order Created Successfully');
    // }

    // public function store(Request $request)
    // {
    //     // ✅ VALIDATION
    //     $request->validate([
    //         'customer_name' => 'nullable|string',
    //         'mobile' => 'nullable|string',
    //         'items' => 'required|array',
    //         'paid_amount' => 'nullable|numeric|min:0',
    //     ]);

    //     $customer_id = null;
    //     $customer = null;

    //     // =============================
    //     // ✅ CUSTOMER LOGIC
    //     // =============================

    //     if ($request->customer_id == 'new') {

    //         $customer = Customer::create([
    //             'name' => $request->new_name,
    //             'phone' => $request->new_mobile,
    //             'address' => $request->new_address,
    //         ]);

    //         $customer_id = $customer->id;

    //     } elseif ($request->customer_id) {

    //         $customer = Customer::find($request->customer_id);
    //         $customer_id = $customer?->id;
    //     }

    //     // =============================
    //     // ✅ STAFF LOGIC
    //     // =============================

    //     $staff_id = auth()->user()->role == 'staff'
    //         ? auth()->id()
    //         : $request->staff_id;

    //     // =============================
    //     // ✅ CALCULATE TOTAL
    //     // =============================

    //     $total = 0;

    //     foreach ($request->items as $item) {
    //         $qty  = $item['qty'] ?? 0;
    //         $rate = $item['rate'] ?? 0;
    //         $total += ($qty * $rate);
    //     }

    //     $discount = $request->discount ?? 0;
    //     $finalAmount = max($total - $discount, 0);

    //     $paid = $request->paid_amount ?? 0;

    //     // =============================
    //     // ✅ AUTO PAYMENT STATUS
    //     // =============================

    //     if ($paid == 0) {
    //         $autoPaymentStatus = 'Pending';
    //     } elseif ($paid < $finalAmount) {
    //         $autoPaymentStatus = 'Partial';
    //     } elseif ($paid == $finalAmount) {
    //         $autoPaymentStatus = 'Paid';
    //     } else {
    //         $autoPaymentStatus = 'Advance';
    //     }

    //     // Admin override allowed
    //     $paymentStatus = $request->payment_status ?: $autoPaymentStatus;

    //     // =============================
    //     // ✅ AUTO ORDER STATUS
    //     // =============================

    //     if ($paymentStatus == 'Pending') {
    //         $autoOrderStatus = 'Pending';
    //     } elseif (in_array($paymentStatus, ['Partial', 'Advance'])) {
    //         $autoOrderStatus = 'Printing';
    //     } elseif ($paymentStatus == 'Paid') {
    //         $autoOrderStatus = 'Ready';
    //     } else {
    //         $autoOrderStatus = 'Pending';
    //     }

    //     $orderStatus = $request->order_status ?: $autoOrderStatus;

    //     // =============================
    //     // ✅ CREATE ORDER
    //     // =============================

    //     $order = Order::create([

    //         'customer_id'   => $customer_id,
    //         'staff_id'      => $staff_id,

    //         'customer_name' => $request->customer_name ?? ($customer->name ?? ''),
    //         'mobile'        => $request->mobile ?? ($customer->phone ?? ''),
    //         'address'       => $request->address ?? ($customer->address ?? ''),

    //         'total_amount'  => $total,
    //         'discount'      => $discount,
    //         'final_amount'  => $finalAmount,
    //         'paid_amount'   => $paid,

    //         // 'payment_method'=> $request->payment_method,
    //         'payment_status'=> $paymentStatus,
    //         'order_status'  => $orderStatus,

    //         // 'notes'         => $request->notes,
    //     ]);

    //     // =============================
    //     // ✅ SAVE ITEMS
    //     // =============================

    //     foreach ($request->items as $item) {

    //         if (!empty($item['qty']) && !empty($item['rate'])) {

    //             OrderItem::create([
    //                 'order_id' => $order->id,
    //                 'size'     => $item['size'] ?? null,
    //                 'material' => $item['material'] ?? null,
    //                 'qty'      => $item['qty'],
    //                 'rate'     => $item['rate'],
    //                 'amount'   => $item['qty'] * $item['rate'],
    //             ]);
    //         }
    //     }

    //     // =============================
    //     // ✅ PAYMENT ENTRY
    //     // =============================

    //     if ($paid > 0) {
    //         OrderPayment::create([
    //             'order_id' => $order->id,
    //             'amount' => $paid,
    //             'payment_mode' => $request->payment_method,
    //             'payment_date' => now(),
    //             'notes' => $request->notes,
    //         ]);
    //     }

    //     // =============================
    //     // ✅ REDIRECT
    //     // =============================

    //     return redirect()->route('orders')
    //         ->with('success', 'Order Created Successfully');
    // }

    public function store(Request $request)
    {
        // =============================
        // ✅ VALIDATION
        // =============================
        $request->validate([
            'customer_name' => 'nullable|string',
            'mobile' => 'nullable|string',
            'items' => 'required|array',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $customer_id = null;
        $customer = null;

        // =============================
        // ✅ CUSTOMER LOGIC
        // =============================
        if ($request->customer_id == 'new') {

            $customer = Customer::create([
                'name' => $request->new_name,
                'phone' => $request->new_mobile,
                'address' => $request->new_address,
            ]);

            $customer_id = $customer->id;

        } elseif ($request->customer_id) {

            $customer = Customer::find($request->customer_id);
            $customer_id = $customer?->id;
        }

        // =============================
        // ✅ STAFF LOGIC
        // =============================
        $staff_id = auth()->user()->role == 'staff'
            ? auth()->id()
            : $request->staff_id;

        // =============================
        // ✅ CALCULATE TOTAL
        // =============================
        $total = 0;

        // foreach ($request->items as $item) {
        //     $quantity = $item['qty'] ?? 0; // 🔥 mapping from blade
        //     $rate = $item['rate'] ?? 0;

        //     $total += ($quantity * $rate);
        // }

        foreach ($request->items as $item) {

            // ❌ OLD
            /*
            $quantity = $item['qty'] ?? 0;
            */

            // ✅ FIX
            $quantity = $item['quantity'] ?? 0;

            $rate = $item['rate'] ?? 0;

            if ($quantity > 0 && $rate > 0) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'size'     => $item['size'] ?? null,
                    'material' => $item['material'] ?? null,
                    'quantity' => $quantity,
                    'rate'     => $rate,
                ]);
            }
        }

        $discount = $request->discount ?? 0;
        $finalAmount = max($total - $discount, 0);
        $paid = $request->paid_amount ?? 0;

        // =============================
        // ✅ AUTO PAYMENT STATUS
        // =============================
        if ($paid == 0) {
            $autoPaymentStatus = 'Pending';
        } elseif ($paid < $finalAmount) {
            $autoPaymentStatus = 'Partial';
        } elseif ($paid == $finalAmount) {
            $autoPaymentStatus = 'Paid';
        } else {
            $autoPaymentStatus = 'Advance';
        }

        $paymentStatus = $request->payment_status ?: $autoPaymentStatus;

        // =============================
        // ✅ AUTO ORDER STATUS
        // =============================
        if ($paymentStatus == 'Pending') {
            $autoOrderStatus = 'Pending';
        } elseif (in_array($paymentStatus, ['Partial', 'Advance'])) {
            $autoOrderStatus = 'Printing';
        } elseif ($paymentStatus == 'Paid') {
            $autoOrderStatus = 'Ready';
        } else {
            $autoOrderStatus = 'Pending';
        }

        $orderStatus = $request->order_status ?: $autoOrderStatus;

        // =============================
        // ✅ CREATE ORDER
        // =============================
        $order = Order::create([

            'customer_id'   => $customer_id,
            'staff_id'      => $staff_id,

            'customer_name' => $request->customer_name ?? ($customer->name ?? ''),
            'mobile'        => $request->mobile ?? ($customer->phone ?? ''),
            'address'       => $request->address ?? ($customer->address ?? ''),

            'total_amount'  => $total,
            'discount'      => $discount,
            'final_amount'  => $finalAmount,
            'paid_amount'   => $paid,

            'payment_status'=> $paymentStatus,
            'order_status'  => $orderStatus,
        ]);

        // =============================
        // ✅ SAVE ITEMS (FIXED)
        // =============================
        foreach ($request->items as $item) {

            $quantity = $item['qty'] ?? 0;
            $rate = $item['rate'] ?? 0;

            if ($quantity > 0 && $rate > 0) {

                OrderItem::create([
                    'order_id' => $order->id,
                    'size'     => $item['size'] ?? null,
                    'material' => $item['material'] ?? null,
                    'quantity' => $quantity, // ✅ DB column correct
                    'rate'     => $rate,
                    'amount'   => $quantity * $rate,
                ]);
            }
        }

        // =============================
        // ✅ PAYMENT ENTRY
        // =============================
        if ($paid > 0) {
            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $paid,
                'payment_mode' => $request->payment_method,
                'payment_date' => now(),
                'notes' => $request->notes,
            ]);
        }

        // =============================
        // ✅ REDIRECT
        // =============================
        return redirect()->route('orders')
            ->with('success', 'Order Created Successfully');
    }

    public function export(Request $request)
    {
        $query = Order::with(['staff','latestPayment'])->latest();

        // 🔍 SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                ->orWhere('mobile', 'like', '%' . $request->search . '%')
                ->orWhere('id', $request->search);
            });
        }

        // 📦 FILTERS
        if ($request->order_status) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->payment_mode) {
            $query->whereHas('latestPayment', function ($q) use ($request) {
                $q->where('payment_mode', $request->payment_mode);
            });
        }

        // 📅 DATE FILTER
        if ($request->date) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        if ($request->staff) {
            $query->whereHas('staff', function ($q) use ($request) {
                $q->where('name', $request->staff);
            });
        }

        $orders = $query->get();

        // CSV DOWNLOAD
        $filename = "orders_report_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            // ✅ HEADER (updated)
            fputcsv($file, [
                'ID',
                'Staff',
                'Customer',
                'Mobile',
                'Order Status',
                'Payment Status',
                'Payment Mode',
                'Amount',
                'Date'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->id,
                    optional($order->staff)->name,
                    $order->customer_name,
                    $order->mobile,
                    $order->order_status,
                    $order->payment_status,
                    optional($order->latestPayment)->payment_mode,
                    $order->paid_amount,
                    $order->created_at->format('Y-m-d')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}

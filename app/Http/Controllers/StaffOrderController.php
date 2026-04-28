<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use Illuminate\Http\Request;

class StaffOrderController extends Controller
{

    

    public function index(Request $request)
    {

    
        $query = Order::query();

        // 🔍 SEARCH
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                ->orWhere('mobile', 'like', '%' . $request->search . '%');
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

        // 📅 DATE FILTER
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->latest()->paginate(10);

        return view('staff.orders.index', compact('orders'));
    }

    // ================= CREATE PAGE =================
    public function create()
    {
        return view('staff.orders.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'mobile' => 'required',
            'items' => 'required|array',
            'items.*.qty' => 'required|numeric|min:1',
            'items.*.rate' => 'required|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required'
        ]);

        // 🔥 TOTAL CALCULATION
        $total = 0;

        foreach ($request->items as $item) {
            $qty = $item['qty'] ?? 0;
            $rate = $item['rate'] ?? 0;
            $total += ($qty * $rate);
        }

        $paid = $request->paid_amount ?? 0;

        // 🔥 PAYMENT STATUS
        if ($paid == 0) {
            $paymentStatus = 'Pending';
        } elseif ($paid < $total) {
            $paymentStatus = 'Partial';
        } elseif ($paid == $total) {
            $paymentStatus = 'Paid';
        } else {
            $paymentStatus = 'Advance';
        }

        // 🔥 ORDER STATUS
        if ($paymentStatus == 'Pending') {
            $orderStatus = 'Pending';
        } elseif (in_array($paymentStatus, ['Partial', 'Advance'])) {
            $orderStatus = 'Printing';
        } else {
            $orderStatus = 'Ready';
        }

        $order = Order::create([
            'customer_name' => $request->customer_name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'staff_id' => auth()->id(),
        ]);

        // 🔥 ITEMS
        foreach ($request->items as $item) {

            if (empty($item['qty']) || empty($item['rate'])) {
                continue;
            }

            OrderItem::create([
                'order_id' => $order->id,
                'size' => $item['size'] ?? null,
                'material' => $item['material'] ?? null,
                'quantity' => $item['qty'],
                'rate' => $item['rate'],
            ]);
        }

        // 🔥 PAYMENT ENTRY
        if ($paid > 0) {
            OrderPayment::create([
                'order_id' => $order->id,
                'amount' => $paid,
                'payment_mode' => $request->payment_method,
                'payment_date' => now(),
                'notes' => $request->notes,
            ]);
        }

        return redirect()->route('staff.orders.index')->with('success', 'Order Added');
    }


    public function edit($id)
    {
        $order = Order::with('items')->findOrFail($id);

        return view('staff.orders.edit', compact('order'));
    }


    public function show($id)
    {
        $order = Order::with(['items','payments'])
            ->where('id', $id)
            ->firstOrFail();

        return view('staff.orders.view', compact('order'));
    }


    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_mode' => 'required'
        ]);

        // ❌ staff restriction हटाया
        $order = Order::with('items','payments')->findOrFail($id);

        OrderPayment::create([
            'order_id' => $order->id,
            'amount' => $request->amount,
            'payment_mode' => $request->payment_mode,
            'notes' => $request->notes,
            'payment_date' => now(),
        ]);

        // 🔥 RECALCULATE
        $totalPaid = $order->payments()->sum('amount');

        $total = $order->items->sum(fn($i) =>
            ($i->quantity ?? 0) * ($i->rate ?? 0)
        );

        $final = max($total - ($order->discount ?? 0), 0);

        $order->paid_amount = $totalPaid;

        if ($totalPaid == 0) {
            $status = 'Pending';
        } elseif ($totalPaid < $final) {
            $status = 'Partial';
        } elseif ($totalPaid == $final) {
            $status = 'Paid';
        } else {
            $status = 'Advance';
        }

        $order->payment_status = $status;
        $order->save();

        return back()->with('success','Payment added');
    }


    public function update(Request $request, $id)
    {
        // ❌ staff restriction हटाया
        $order = Order::with(['items','payments'])->findOrFail($id);

        $request->validate([
            'customer_name' => 'required',
            'mobile' => 'required',
            'items' => 'required|array',
        ]);

        // 🔥 TOTAL CALCULATION
        $total = 0;

        foreach ($request->items as $item) {
            $total += ($item['quantity'] * $item['rate']);
        }

        // 🔥 PAID (always from payments table)
        $paid = $order->payments->sum('amount');

        $final = max($total - ($order->discount ?? 0), 0);

        // =============================
        // ✅ MANUAL PRIORITY LOGIC
        // =============================
        $paymentStatus = $request->payment_status ?? null;

        if (!$paymentStatus) {
            if ($paid == 0) {
                $paymentStatus = 'Pending';
            } elseif ($paid < $final) {
                $paymentStatus = 'Partial';
            } elseif ($paid == $final) {
                $paymentStatus = 'Paid';
            } else {
                $paymentStatus = 'Advance';
            }
        }

        $orderStatus = $request->order_status ?? $order->order_status;

        // =============================
        // ✅ UPDATE ORDER
        // =============================
        $order->update([
            'customer_name' => $request->customer_name,
            'mobile' => $request->mobile,
            'address' => $request->address,
            'total_amount' => $total,
            'paid_amount' => $paid,
            'payment_status' => ucfirst($paymentStatus),
            'order_status' => ucfirst($orderStatus),
        ]);

        // =============================
        // ✅ ITEMS RESET
        // =============================
        $order->items()->delete();

        foreach ($request->items as $item) {
            $order->items()->create([
                'size' => $item['size'] ?? null,
                'material' => $item['material'] ?? null,
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
            ]);
        }

        return redirect()->route('staff.orders.index')
            ->with('success','Order updated successfully');
    }

    public function destroy($id)
    {
        abort(403);
    }

    public function export(Request $request)
    {
        $query = Order::with(['staff','latestPayment'])
            ->where('staff_id', auth()->id()) // ✅ IMPORTANT
            ->latest();

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
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->get();

        // CSV DOWNLOAD
        $filename = "staff_orders_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'ID',
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
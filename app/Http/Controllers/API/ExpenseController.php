<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    /*
     * |--------------------------------------------------------------------------
     * | ADD EXPENSE
     * |--------------------------------------------------------------------------
     */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'expense_name' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        $expense = Expense::create([
            'staff_id' => $request->staff_id,
            'order_id' => $request->order_id,
            'expense_name' => $request->expense_name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date ?? now(),
            'notes' => $request->notes
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Expense added successfully',
            'data' => $expense
        ]);
    }

    /*
     * |--------------------------------------------------------------------------
     * | UPDATE EXPENSE
     * |--------------------------------------------------------------------------
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json([
                'status' => false,
                'message' => 'Expense not found'
            ]);
        }

        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'expense_name' => 'required|string',
            'amount' => 'required|numeric|min:0'
        ]);

        $expense->update([
            'staff_id' => $request->staff_id,
            'order_id' => $request->order_id,
            'expense_name' => $request->expense_name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date ?? $expense->expense_date,
            'notes' => $request->notes
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Expense updated successfully',
            'data' => $expense
        ]);
    }

    /*
     * |--------------------------------------------------------------------------
     * | DELETE EXPENSE
     * |--------------------------------------------------------------------------
     */
    public function destroy($id)
    {
        $expense = Expense::find($id);

        if (!$expense) {
            return response()->json([
                'status' => false,
                'message' => 'Expense not found'
            ]);
        }

        $expense->delete();

        return response()->json([
            'status' => true,
            'message' => 'Expense deleted successfully'
        ]);
    }

    /*
     * |--------------------------------------------------------------------------
     * | EXPENSE LIST + FILTER + TOTAL
     * |--------------------------------------------------------------------------
     */
    // public function index(Request $request)
    // {
    //     $query = Expense::with(['staff', 'order']);

    //     // 🔍 Filters
    //     if ($request->staff_id) {
    //         $query->where('staff_id', $request->staff_id);
    //     }

    //     if ($request->order_id) {
    //         $query->where('order_id', $request->order_id);
    //     }

    //     if ($request->date) {
    //         $query->whereDate('expense_date', $request->date);
    //     }

    //     if ($request->month) {
    //         $query->whereMonth('expense_date', $request->month);
    //     }

    //     if ($request->search) {
    //         $query->where('expense_name', 'LIKE', '%' . $request->search . '%');
    //     }

    //     // 📊 Data
    //     $expenses = $query->latest()->get();

    //     // 💰 Total
    //     $totalAmount = $query->sum('amount');

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Expense list fetched',
    //         'total_records' => $expenses->count(),
    //         'total_amount' => $totalAmount,
    //         'data' => $expenses
    //     ]);
    // }

    public function index(Request $request)
    {
        $query = Expense::with(['staff', 'order']);

        // 🔍 Filters
        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->order_id) {
            $query->where('order_id', $request->order_id);
        }

        if ($request->date) {
            $query->whereDate('expense_date', $request->date);
        }

        if ($request->month) {
            $query->whereMonth('expense_date', $request->month);
        }

        if ($request->search) {
            $query->where('expense_name', 'LIKE', '%' . $request->search . '%');
        }

        // 📊 Data Fetch
        $expenses = $query->latest()->get();

        // 💰 Total Amount (clone जरूरी है warna query affect hogi)
        $totalAmount = (clone $query)->sum('amount');

        // 🔁 Format Data (IST + only date + null safe)
        $expenses->transform(function ($expense) {
            return [
                'id' => $expense->id,
                'staff_id' => $expense->staff_id,
                'order_id' => $expense->order_id,
                'expense_name' => $expense->expense_name ?? '',
                'amount' => $expense->amount ?? '0',
                // ✅ Only DATE in IST
                'expense_date' => $expense->expense_date
                    ? \Carbon\Carbon::parse($expense->expense_date)
                        ->timezone('Asia/Kolkata')
                        ->format('Y-m-d')
                    : '',
                'notes' => $expense->notes ?? '',
                'created_at' => $expense->created_at
                    ? \Carbon\Carbon::parse($expense->created_at)
                        ->timezone('Asia/Kolkata')
                        ->format('Y-m-d')
                    : '',
                'updated_at' => $expense->updated_at
                    ? \Carbon\Carbon::parse($expense->updated_at)
                        ->timezone('Asia/Kolkata')
                        ->format('Y-m-d')
                    : '',
                // 👤 Staff
                'staff' => $expense->staff ? [
                    'id' => $expense->staff->id,
                    'name' => $expense->staff->name ?? '',
                    'email' => $expense->staff->email ?? '',
                    'contact' => $expense->staff->contact ?? '',
                    'role' => $expense->staff->role ?? '',
                ] : null,
                // 📦 Order
                'order' => $expense->order ? $expense->order : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Expense list fetched',
            'total_records' => $expenses->count(),
            'total_amount' => (string) $totalAmount,
            'data' => $expenses
        ]);
    }

    /*
     * |--------------------------------------------------------------------------
     * | SINGLE EXPENSE VIEW
     * |--------------------------------------------------------------------------
     */
    public function show($id)
    {
        $expense = Expense::with(['staff', 'order'])->find($id);

        if (!$expense) {
            return response()->json([
                'status' => false,
                'message' => 'Expense not found'
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => $expense
        ]);
    }

    /*
     * |--------------------------------------------------------------------------
     * | DASHBOARD API (IMPORTANT 🔥)
     * |--------------------------------------------------------------------------
     */
    public function dashboard()
    {
        $today = now()->toDateString();

        $todayExpense = Expense::whereDate('expense_date', $today)->sum('amount');

        $monthlyExpense = Expense::whereMonth('expense_date', now()->month)
            ->sum('amount');

        $totalExpense = Expense::sum('amount');

        return response()->json([
            'status' => true,
            'today_expense' => $todayExpense,
            'monthly_expense' => $monthlyExpense,
            'total_expense' => $totalExpense
        ]);
    }
}

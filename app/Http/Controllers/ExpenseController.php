<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Category;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['category', 'order']);

        // Category filter
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Date range
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('expense_date', [
                $request->from_date,
                $request->to_date
            ]);
        }

        // Month filter
        if ($request->month && $request->year) {
            $query->whereMonth('expense_date', $request->month)
                  ->whereYear('expense_date', $request->year);
        }

        $expenses = $query->latest()->paginate(10);

        $categories = Category::all();

        return view('admin.expenses.index', compact('expenses', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
        ]);

        Expense::create([
            'category_id' => $request->category_id,
            'order_id' => $request->order_id,
            'expense_name' => $request->expense_name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses')
            ->with('success', 'Expense added successfully');
    }

    public function show($id)
    {
        $expense = Expense::with(['category', 'order'])->findOrFail($id);
        return view('admin.expenses.view', compact('expense'));
    }

    public function edit($id)
    {
        $expense = Expense::findOrFail($id);
        $categories = Category::all();

        return view('admin.expenses.edit', compact('expense', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'expense_name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'expense_date' => 'required|date',
        ]);

        $expense->update([
            'category_id' => $request->category_id,
            'order_id' => $request->order_id,
            'expense_name' => $request->expense_name,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses')
            ->with('success', 'Expense updated successfully');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return redirect()->route('expenses')
            ->with('success', 'Expense deleted successfully');
    }

    public function summary()
    {
        $total = Expense::sum('amount');

        $today = Expense::whereDate('expense_date', now())->sum('amount');

        $month = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        return response()->json([
            'total_expense' => $total,
            'today_expense' => $today,
            'this_month' => $month,
        ]);
    }
}
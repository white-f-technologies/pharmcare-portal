<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'user');

        if ($request->filled('category_id')) {
            $query->where('expense_category_id', $request->get('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('expense_number', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->get('from_date'));
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->get('to_date'));
        }

        $expenses = $query->latest('expense_date')->latest('id')->paginate(15);
        $categories = ExpenseCategory::where('is_active', true)->get();

        $todayTotalExpenses = (float) Expense::whereDate('expense_date', now()->toDateString())->sum('amount');
        $monthTotalExpenses = (float) Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        $todayAvailableProfit = $this->getAvailableProfitForDate(now()->toDateString());

        return view('expenses.index', compact('expenses', 'categories', 'todayTotalExpenses', 'monthTotalExpenses', 'todayAvailableProfit'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        $todayAvailableProfit = $this->getAvailableProfitForDate(now()->toDateString());

        return view('expenses.create', compact('categories', 'todayAvailableProfit'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,mobile_money,bank_transfer',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:4096',
        ]);

        $availableProfit = $this->getAvailableProfitForDate($validated['expense_date']);
        $amount = (float) $validated['amount'];

        if ($availableProfit <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Expense cannot be recorded! The pharmacy has zero (0) net sales profit available for ' . $validated['expense_date'] . '. Expenses are only allowed when net sales profit is available.'
            ]);
        }

        if ($amount > $availableProfit) {
            $curr = setting('currency_symbol', 'UGX');
            throw ValidationException::withMessages([
                'amount' => "Expense amount ({$curr} " . format_price($amount) . ") exceeds the available net sales profit ({$curr} " . format_price($availableProfit) . ") for {$validated['expense_date']}."
            ]);
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        $count = Expense::whereDate('created_at', today())->count() + 1;
        $validated['expense_number'] = 'EXP-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
        $validated['user_id'] = Auth::id();

        Expense::create($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    public function edit(Expense $expense)
    {
        $categories = ExpenseCategory::where('is_active', true)->get();
        $availableProfit = $this->getAvailableProfitForDate($expense->expense_date->format('Y-m-d'), $expense->id);

        return view('expenses.edit', compact('expense', 'categories', 'availableProfit'));
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:cash,mobile_money,bank_transfer',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,pdf,webp|max:4096',
        ]);

        $availableProfit = $this->getAvailableProfitForDate($validated['expense_date'], $expense->id);
        $amount = (float) $validated['amount'];

        if ($availableProfit <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Expense cannot be updated! The pharmacy has zero (0) net sales profit available for ' . $validated['expense_date'] . '.'
            ]);
        }

        if ($amount > $availableProfit) {
            $curr = setting('currency_symbol', 'UGX');
            throw ValidationException::withMessages([
                'amount' => "Expense amount ({$curr} " . format_price($amount) . ") exceeds available net sales profit ({$curr} " . format_price($availableProfit) . ") for {$validated['expense_date']}."
            ]);
        }

        if ($request->hasFile('attachment')) {
            if ($expense->attachment && Storage::disk('public')->exists($expense->attachment)) {
                Storage::disk('public')->delete($expense->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->attachment && Storage::disk('public')->exists($expense->attachment)) {
            Storage::disk('public')->delete($expense->attachment);
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }

    private function getAvailableProfitForDate($date, $ignoreExpenseId = null)
    {
        $dateStr = \Carbon\Carbon::parse($date)->toDateString();

        $grossSales = (float) \App\Models\Sale::whereDate('created_at', $dateStr)->sum('total');
        $refunds = (float) \App\Models\SaleReturn::whereDate('created_at', $dateStr)->sum('refund_amount');
        $netSales = max(0, $grossSales - $refunds);

        $saleItems = \App\Models\SaleItem::whereHas('sale', function ($q) use ($dateStr) {
            $q->whereDate('created_at', $dateStr);
        })->with('batch')->get();

        $saleReturns = \App\Models\SaleReturn::whereDate('created_at', $dateStr)->with('batch')->get();

        $grossCogs = (float) $saleItems->sum(function ($item) {
            $purchasePrice = $item->batch ? (float)$item->batch->purchase_price : 0;
            return (float)$item->quantity * $purchasePrice;
        });

        $refundedCogs = (float) $saleReturns->sum(function ($ret) {
            $purchasePrice = $ret->batch ? (float)$ret->batch->purchase_price : 0;
            return (float)$ret->returned_base_quantity * $purchasePrice;
        });

        $netCogs = max(0, $grossCogs - $refundedCogs);
        $grossProfit = max(0, $netSales - $netCogs);

        $expenseQuery = Expense::whereDate('expense_date', $dateStr);
        if ($ignoreExpenseId) {
            $expenseQuery->where('id', '!=', $ignoreExpenseId);
        }
        $existingExpenses = (float) $expenseQuery->sum('amount');

        return max(0, $grossProfit - $existingExpenses);
    }
}

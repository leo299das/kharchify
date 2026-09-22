<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display a listing of earnings and income sources with metrics and filters.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Income::with('category')->where('user_id', $userId);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('source', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('income_category_id', $request->input('category_id'));
        }

        // Payment Method Filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->input('payment_method'));
        }

        // Month Filter
        if ($request->filled('month')) {
            $monthDate = Carbon::parse($request->input('month'));
            $query->whereYear('income_date', $monthDate->year)
                  ->whereMonth('income_date', $monthDate->month);
        }

        // Date Range Filters
        if ($request->filled('start_date')) {
            $query->whereDate('income_date', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('income_date', '<=', $request->input('end_date'));
        }

        $incomes = $query->orderBy('income_date', 'desc')->orderBy('id', 'desc')->get();

        // Metrics for filtered view
        $filteredTotal = (float) $incomes->sum('amount');
        $filteredCount = $incomes->count();
        $averageIncome = $filteredCount > 0 ? ($filteredTotal / $filteredCount) : 0;

        // Overall month and today metrics
        $thisMonthTotal = (float) Income::where('user_id', $userId)
            ->whereMonth('income_date', Carbon::now()->month)
            ->whereYear('income_date', Carbon::now()->year)
            ->sum('amount');

        $todayTotal = (float) Income::where('user_id', $userId)
            ->whereDate('income_date', Carbon::today())
            ->sum('amount');

        $categories = IncomeCategory::getCategoriesForUser($userId);
        $paymentMethods = ['UPI / GPay / PhonePe / Paytm', 'Cash', 'Bank Transfer / IMPS / NEFT', 'Credit / Debit Card', 'Cheque / Other'];

        return view('incomes.index', [
            'incomes' => $incomes,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
            'filteredTotal' => $filteredTotal,
            'filteredCount' => $filteredCount,
            'averageIncome' => $averageIncome,
            'thisMonthTotal' => $thisMonthTotal,
            'todayTotal' => $todayTotal,
        ]);
    }

    /**
     * Show form to log a new earning / income.
     */
    public function create()
    {
        $userId = Auth::id();
        $categories = IncomeCategory::getCategoriesForUser($userId);
        $paymentMethods = ['UPI / GPay / PhonePe / Paytm', 'Cash', 'Bank Transfer / IMPS / NEFT', 'Credit / Debit Card', 'Cheque / Other'];

        return view('incomes.create', [
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Store a newly created income record.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000000',
            'source' => 'required|string|max:255',
            'income_category_id' => 'nullable|exists:income_categories,id',
            'payment_method' => 'required|string|max:100',
            'income_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $income = Income::create([
            'user_id' => Auth::id(),
            'income_category_id' => $request->input('income_category_id'),
            'source' => trim($request->input('source')),
            'amount' => (float) $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'income_date' => $request->input('income_date'),
            'note' => $request->input('note'),
            'transaction_id' => $request->input('transaction_id'),
        ]);

        return redirect()->route('incomes.index')->with('success', 'Earning of +₹' . number_format($income->amount, 2) . ' from "' . $income->source . '" recorded successfully! 💰');
    }

    /**
     * Show form to edit an existing income.
     */
    public function edit(int $id)
    {
        $income = Income::where('user_id', Auth::id())->findOrFail($id);
        $categories = IncomeCategory::getCategoriesForUser(Auth::id());
        $paymentMethods = ['UPI / GPay / PhonePe / Paytm', 'Cash', 'Bank Transfer / IMPS / NEFT', 'Credit / Debit Card', 'Cheque / Other'];

        return view('incomes.edit', [
            'income' => $income,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Update an existing income record.
     */
    public function update(Request $request, int $id)
    {
        $income = Income::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:100000000',
            'source' => 'required|string|max:255',
            'income_category_id' => 'nullable|exists:income_categories,id',
            'payment_method' => 'required|string|max:100',
            'income_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $income->update([
            'income_category_id' => $request->input('income_category_id'),
            'source' => trim($request->input('source')),
            'amount' => (float) $request->input('amount'),
            'payment_method' => $request->input('payment_method'),
            'income_date' => $request->input('income_date'),
            'note' => $request->input('note'),
            'transaction_id' => $request->input('transaction_id'),
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income entry updated successfully!');
    }

    /**
     * Delete an income record.
     */
    public function destroy(int $id)
    {
        $income = Income::where('user_id', Auth::id())->findOrFail($id);
        $source = $income->source;
        $amount = $income->amount;
        $income->delete();

        return redirect()->route('incomes.index')->with('success', 'Income entry of ₹' . number_format($amount, 2) . ' (' . $source . ') deleted.');
    }
}

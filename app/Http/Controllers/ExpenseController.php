<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $query = Expense::with('category')->where('user_id', $userId);

        // Keyword Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('paid_to', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Category Filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Payment Method Filter
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Month Filter
        if ($request->filled('month')) {
            $monthDate = Carbon::parse($request->month);
            $query->whereYear('expense_date', $monthDate->year)
                  ->whereMonth('expense_date', $monthDate->month);
        }

        // Date Range Filters
        if ($request->filled('start_date')) {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }

        // Get expenses
        $expenses = $query->orderBy('expense_date', 'desc')->get();

        // Calculate summary metrics for current filtered view
        $filteredTotal = $expenses->sum('amount');
        $filteredCount = $expenses->count();
        $averageExpense = $filteredCount > 0 ? ($filteredTotal / $filteredCount) : 0;

        $categories = Category::where('user_id', $userId)->orderBy('name')->get();
        $paymentMethods = ['UPI / GPay / PhonePe', 'Cash', 'Credit Card', 'Debit Card', 'Net Banking', 'Cheque / Other'];

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'paymentMethods',
            'filteredTotal',
            'filteredCount',
            'averageExpense'
        ));
    }

    public function create()
    {
        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();

        if ($categories->isEmpty()) {
            return redirect('/categories/create')->with('warning', 'Please create at least one category before adding an expense.');
        }

        $paymentMethods = ['UPI / GPay / PhonePe', 'Cash', 'Credit Card', 'Debit Card', 'Net Banking', 'Cheque / Other'];

        return view('expenses.create', compact('categories', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'paid_to' => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        Expense::create([
            'user_id' => auth()->id(),
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'paid_to' => $request->paid_to,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'note' => $request->note,
            'expense_date' => $request->expense_date
        ]);

        return redirect('/expenses')->with('success', 'Expense of ₹' . number_format($request->amount, 2) . ' logged successfully!');
    }

    public function edit($id)
    {
        $expense = Expense::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $categories = Category::where('user_id', auth()->id())->orderBy('name')->get();
        $paymentMethods = ['UPI / GPay / PhonePe', 'Cash', 'Credit Card', 'Debit Card', 'Net Banking', 'Cheque / Other'];

        return view('expenses.edit', compact('expense', 'categories', 'paymentMethods'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'category_id' => 'required|exists:categories,id',
            'paid_to' => 'required|string|max:255',
            'payment_method' => 'required|string|max:100',
            'expense_date' => 'required|date',
            'note' => 'nullable|string|max:1000',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $expense->update([
            'category_id' => $request->category_id,
            'amount' => $request->amount,
            'paid_to' => $request->paid_to,
            'payment_method' => $request->payment_method,
            'transaction_id' => $request->transaction_id,
            'note' => $request->note,
            'expense_date' => $request->expense_date
        ]);

        return redirect('/expenses')->with('success', 'Expense details updated successfully!');
    }

    public function destroy($id)
    {
        $expense = Expense::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $amount = $expense->amount;
        $expense->delete();

        return redirect('/expenses')->with('success', 'Expense of ₹' . number_format($amount, 2) . ' deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $user = auth()->user();

        if (!$user->canExportCsv()) {
            return redirect('/expenses')->with('info', '📊 CSV Data Export is an upcoming feature included in our Medium Plan (₹94). Access will unlock soon!');
        }

        $userId = $user->id;

        $expenses = Expense::with('category')
            ->where('user_id', $userId)
            ->orderBy('expense_date', 'desc')
            ->get();

        $filename = 'kharchify-expenses-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($expenses, $user) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header row
            fputcsv($file, ['ID', 'Paid To / Merchant', 'Category', 'Amount (INR)', 'Payment Method', 'Transaction ID', 'Expense Date', 'Notes']);

            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->id,
                    $expense->paid_to,
                    $expense->category->name ?? 'Uncategorized',
                    $expense->amount,
                    $expense->payment_method,
                    $expense->transaction_id ?? 'N/A',
                    $expense->expense_date ? Carbon::parse($expense->expense_date)->format('Y-m-d H:i') : '',
                    $expense->note ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadPDF(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $statementType = $request->get('statement_type', 'all'); // 'all', 'expenses', 'incomes'
        $preset = $request->get('preset');
        $startDate = null;
        $endDate = null;
        $dateRangeText = 'All-Time Statement';
        $filenameSuffix = 'all-time';

        // 1. Resolve date range from preset, month, or custom start/end dates
        if ($preset) {
            switch ($preset) {
                case 'this_month':
                    $startDate = Carbon::now()->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $dateRangeText = Carbon::now()->format('F Y');
                    $filenameSuffix = Carbon::now()->format('Y-m');
                    break;
                case 'last_month':
                    $startDate = Carbon::now()->subMonth()->startOfMonth();
                    $endDate = Carbon::now()->subMonth()->endOfMonth();
                    $dateRangeText = Carbon::now()->subMonth()->format('F Y');
                    $filenameSuffix = Carbon::now()->subMonth()->format('Y-m');
                    break;
                case 'last_3_months':
                    $startDate = Carbon::now()->subMonths(2)->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $dateRangeText = Carbon::now()->subMonths(2)->format('M Y') . ' to ' . Carbon::now()->format('M Y');
                    $filenameSuffix = 'last-3-months';
                    break;
                case 'last_6_months':
                    $startDate = Carbon::now()->subMonths(5)->startOfMonth();
                    $endDate = Carbon::now()->endOfMonth();
                    $dateRangeText = Carbon::now()->subMonths(5)->format('M Y') . ' to ' . Carbon::now()->format('M Y');
                    $filenameSuffix = 'last-6-months';
                    break;
                case 'this_year':
                    $startDate = Carbon::now()->startOfYear();
                    $endDate = Carbon::now()->endOfYear();
                    $dateRangeText = Carbon::now()->format('Y') . ' (Year-to-Date)';
                    $filenameSuffix = 'year-' . Carbon::now()->format('Y');
                    break;
                case 'all_time':
                default:
                    $startDate = null;
                    $endDate = null;
                    $dateRangeText = 'All-Time Statement';
                    $filenameSuffix = 'all-time';
                    break;
            }
        } elseif ($request->filled('month')) {
            try {
                $monthDate = Carbon::createFromFormat('Y-m', $request->month);
                $startDate = $monthDate->copy()->startOfMonth();
                $endDate = $monthDate->copy()->endOfMonth();
                $dateRangeText = $monthDate->format('F Y');
                $filenameSuffix = $request->month;
            } catch (\Exception $e) {
                $startDate = null;
                $endDate = null;
                $dateRangeText = 'All-Time Statement';
                $filenameSuffix = 'all-time';
            }
        } elseif ($request->filled('start_date') || $request->filled('end_date')) {
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;

            if ($startDate && $endDate) {
                $dateRangeText = $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y');
                $filenameSuffix = $startDate->format('Ymd') . '-to-' . $endDate->format('Ymd');
            } elseif ($startDate) {
                $dateRangeText = 'From ' . $startDate->format('d M Y');
                $filenameSuffix = 'from-' . $startDate->format('Ymd');
            } elseif ($endDate) {
                $dateRangeText = 'Up to ' . $endDate->format('d M Y');
                $filenameSuffix = 'upto-' . $endDate->format('Ymd');
            }
        }

        // 2. Query Expenses
        $expenses = collect();
        if ($statementType !== 'incomes') {
            $expenseQuery = Expense::with('category')->where('user_id', $userId);

            if ($startDate && $endDate) {
                $expenseQuery->whereBetween('expense_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $expenseQuery->where('expense_date', '>=', $startDate);
            } elseif ($endDate) {
                $expenseQuery->where('expense_date', '<=', $endDate);
            }

            if ($request->filled('category_id')) {
                $expenseQuery->where('category_id', $request->category_id);
            }

            $expenses = $expenseQuery->orderBy('expense_date', 'desc')->get();
        }

        // 3. Query Incomes
        $incomes = collect();
        if ($statementType !== 'expenses') {
            $incomeQuery = Income::with('category')->where('user_id', $userId);

            if ($startDate && $endDate) {
                $incomeQuery->whereBetween('income_date', [$startDate, $endDate]);
            } elseif ($startDate) {
                $incomeQuery->where('income_date', '>=', $startDate);
            } elseif ($endDate) {
                $incomeQuery->where('income_date', '<=', $endDate);
            }

            $incomes = $incomeQuery->orderBy('income_date', 'desc')->get();
        }

        // 4. Compute Period Totals
        $totalExpense = $expenses->sum('amount');
        $totalIncome = $incomes->sum('amount');
        $netSavings = $totalIncome - $totalExpense;
        $totalCount = $expenses->count() + $incomes->count();

        // 5. Combine into Unified Chronological Transactions Stream
        $unifiedTransactions = collect();

        foreach ($expenses as $expense) {
            $unifiedTransactions->push([
                'id' => 'EXP-' . $expense->id,
                'raw_date' => $expense->expense_date ? Carbon::parse($expense->expense_date) : null,
                'date' => $expense->expense_date ? Carbon::parse($expense->expense_date)->format('d-m-Y') : 'N/A',
                'time' => $expense->expense_date ? Carbon::parse($expense->expense_date)->format('h:i A') : '',
                'type' => 'expense',
                'party' => $expense->paid_to ?? 'Expense',
                'category' => $expense->category->name ?? 'General',
                'payment_method' => $expense->payment_method ?? 'UPI / Bank',
                'amount' => (float)$expense->amount,
                'note' => $expense->note,
                'transaction_id' => $expense->transaction_id,
            ]);
        }

        foreach ($incomes as $income) {
            $unifiedTransactions->push([
                'id' => 'INC-' . $income->id,
                'raw_date' => $income->income_date ? Carbon::parse($income->income_date) : null,
                'date' => $income->income_date ? Carbon::parse($income->income_date)->format('d-m-Y') : 'N/A',
                'time' => $income->income_date ? Carbon::parse($income->income_date)->format('h:i A') : '',
                'type' => 'income',
                'party' => $income->source ?? 'Earning / Inflow',
                'category' => $income->category?->name ?? 'Income',
                'payment_method' => $income->payment_method ?? 'Bank / Transfer',
                'amount' => (float)$income->amount,
                'note' => $income->note,
                'transaction_id' => $income->transaction_id,
            ]);
        }

        $transactions = $unifiedTransactions->sortByDesc(function ($item) {
            return $item['raw_date'] ? $item['raw_date']->timestamp : 0;
        })->values();

        // High-level benchmark metrics for report footer/summary
        $todayExpense = Expense::where('user_id', $userId)
            ->whereDate('expense_date', Carbon::today())
            ->sum('amount');
        $monthlyExpense = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');
        $yearlyExpense = Expense::where('user_id', $userId)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');

        $pdf = Pdf::loadView('expenses.pdf', compact(
            'user',
            'transactions',
            'expenses',
            'incomes',
            'totalExpense',
            'totalIncome',
            'netSavings',
            'totalCount',
            'dateRangeText',
            'statementType',
            'todayExpense',
            'monthlyExpense',
            'yearlyExpense',
            'startDate',
            'endDate'
        ))->setPaper('a4', 'portrait')
          ->setOption('isHtml5ParserEnabled', true)
          ->setOption('isRemoteEnabled', true);

        $fileName = 'kharchify-statement-' . $filenameSuffix . '.pdf';

        return $pdf->download($fileName);
    }
}
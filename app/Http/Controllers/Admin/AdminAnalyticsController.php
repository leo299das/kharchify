<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expense;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalExpenses = Expense::count();
        $totalVolume = Expense::sum('amount');
        $averageExpense = $totalExpenses > 0 ? ($totalVolume / $totalExpenses) : 0;
        $averagePerUser = $totalUsers > 0 ? ($totalVolume / $totalUsers) : 0;

        // Plan distribution
        $planBreakdown = User::select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->plan ?? 'free' => $item->count];
            });

        // Payment Mode Distribution Platform-wide
        $paymentMethods = Expense::select('payment_method', DB::raw('count(*) as count'), DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // 6-Month Platform Monthly Volume (Database-agnostic)
        $pastExpenses = Expense::where('expense_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->orderBy('expense_date', 'asc')
            ->get();

        $monthlyVolume = $pastExpenses->groupBy(function ($expense) {
            return Carbon::parse($expense->expense_date)->format('Y-m');
        })->map(function ($items, $key) {
            $date = Carbon::parse($key . '-01');
            return (object) [
                'month_name' => $date->format('F'),
                'year' => $date->format('Y'),
                'count' => $items->count(),
                'total' => $items->sum('amount'),
            ];
        })->values();

        // Top Spenders
        $topSpenders = User::withSum('expenses', 'amount')
            ->withCount('expenses')
            ->orderByDesc('expenses_sum_amount')
            ->take(10)
            ->get();

        return view('admin.analytics', compact(
            'totalUsers',
            'totalExpenses',
            'totalVolume',
            'averageExpense',
            'averagePerUser',
            'planBreakdown',
            'paymentMethods',
            'monthlyVolume',
            'topSpenders'
        ));
    }
}

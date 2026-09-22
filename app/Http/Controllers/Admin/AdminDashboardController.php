<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Expense;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. User Registration Metrics
        $totalUsers = User::count();
        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        $newUsersThisWeek = User::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        $newUsersThisMonth = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 2. Plan Distribution Metrics
        $planCounts = [
            User::PLAN_FREE => User::where('plan', User::PLAN_FREE)->orWhereNull('plan')->count(),
            User::PLAN_BASIC => User::where('plan', User::PLAN_BASIC)->count(),
            User::PLAN_MEDIUM => User::where('plan', User::PLAN_MEDIUM)->count(),
            User::PLAN_PRO => User::where('plan', User::PLAN_PRO)->count(),
        ];

        $availablePlans = User::getAvailablePlans();

        // Calculate potential / monthly projected plan values
        $projectedMonthlyRevenue = ($planCounts[User::PLAN_BASIC] * 49)
            + ($planCounts[User::PLAN_MEDIUM] * 94)
            + ($planCounts[User::PLAN_PRO] * 150);

        // 3. Platform Financial Metrics
        $totalMoneyTracked = Expense::sum('amount');
        $totalExpensesCount = Expense::count();
        $totalCategoriesCount = Category::count();
        $thisMonthPlatformSpent = Expense::whereMonth('expense_date', Carbon::now()->month)
            ->whereYear('expense_date', Carbon::now()->year)
            ->sum('amount');

        // 4. 30-Day User Growth Trend
        $growthLabels = [];
        $growthValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $growthLabels[] = $date->format('M d');
            $growthValues[] = User::whereDate('created_at', $date->toDateString())->count();
        }

        // 5. Recent 8 Registered Users with activity
        $recentUsers = User::withCount(['expenses', 'categories'])
            ->withSum('expenses', 'amount')
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        // 6. Recent Platform Expenses
        $recentExpenses = Expense::with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // 7. System Specs & Telemetry
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_os' => php_uname('s') . ' ' . php_uname('r'),
            'db_driver' => config('database.default'),
            'app_env' => config('app.env'),
            'app_debug' => config('app.debug') ? 'Enabled (Dev)' : 'Disabled (Production)',
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'newUsersToday',
            'newUsersThisWeek',
            'newUsersThisMonth',
            'planCounts',
            'availablePlans',
            'projectedMonthlyRevenue',
            'totalMoneyTracked',
            'totalExpensesCount',
            'totalCategoriesCount',
            'thisMonthPlatformSpent',
            'growthLabels',
            'growthValues',
            'recentUsers',
            'recentExpenses',
            'systemInfo'
        ));
    }
}

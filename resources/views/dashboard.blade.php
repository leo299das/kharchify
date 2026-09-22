@extends('layouts.app')

@section('content')

@php
    use App\Models\Expense;
    use App\Models\Income;
    use App\Models\User;
    use App\Models\SplitGroup;
    use App\Services\SplitBalanceService;
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    $user = Auth::user();
    $userId = $user->id;
    $userPlan = $user->plan ?? 'free';
    $canCharts = $user->canUseCharts();
    $canBudget = $user->canSetBudget();
    $canPdf = $user->canExportPdf();
    $canCsv = $user->canExportCsv();

    $monthlyBudget = $user->monthly_budget ?? 25000.00;

    // Splitwise / Shared Balances Summary
    $splitService = app(SplitBalanceService::class);
    $splitSummary = $splitService->getUserOverallSummary($user);

    // Income & Earning Metrics (Cash Flow Inflow)
    $totalIncome = (float) Income::where('user_id', $userId)->sum('amount');
    $todayIncome = (float) Income::where('user_id', $userId)->whereDate('income_date', Carbon::today())->sum('amount');
    $thisMonthIncome = (float) Income::where('user_id', $userId)
        ->whereMonth('income_date', Carbon::now()->month)
        ->whereYear('income_date', Carbon::now()->year)
        ->sum('amount');
    $thisMonthIncomeCount = Income::where('user_id', $userId)
        ->whereMonth('income_date', Carbon::now()->month)
        ->whereYear('income_date', Carbon::now()->year)
        ->count();

    // Spending Metrics (Cash Flow Outflow)
    $totalSpent = (float) Expense::where('user_id', $userId)->sum('amount');
    $todaySpent = (float) Expense::where('user_id', $userId)->whereDate('expense_date', Carbon::today())->sum('amount');
    
    $thisMonthExpenses = Expense::where('user_id', $userId)
        ->whereMonth('expense_date', Carbon::now()->month)
        ->whereYear('expense_date', Carbon::now()->year);
    
    $thisMonthSpent = (float) (clone $thisMonthExpenses)->sum('amount');
    $thisMonthCount = (clone $thisMonthExpenses)->count();
    
    $lastMonthSpent = (float) Expense::where('user_id', $userId)
        ->whereMonth('expense_date', Carbon::now()->subMonth()->month)
        ->whereYear('expense_date', Carbon::now()->subMonth()->year)
        ->sum('amount');

    // Net Savings & Cash Flow
    $thisMonthNetSavings = $thisMonthIncome - $thisMonthSpent;
    $allTimeNetSavings = $totalIncome - $totalSpent;
    $savingsRate = $thisMonthIncome > 0 ? round(($thisMonthNetSavings / $thisMonthIncome) * 100, 1) : 0;

    // Budget Calculation
    $budgetPercentage = $monthlyBudget > 0 ? min(round(($thisMonthSpent / $monthlyBudget) * 100, 1), 100) : 0;
    $remainingBudget = max($monthlyBudget - $thisMonthSpent, 0);
    $isOverBudget = $thisMonthSpent > $monthlyBudget;

    // Days in current month & Daily Average
    $daysPassed = max(Carbon::now()->day, 1);
    $dailyAverage = $thisMonthSpent > 0 ? round($thisMonthSpent / $daysPassed) : 0;

    // Top Category this month
    $topCategory = Expense::with('category')
        ->select('category_id', DB::raw('SUM(amount) as total'))
        ->where('user_id', $userId)
        ->whereMonth('expense_date', Carbon::now()->month)
        ->groupBy('category_id')
        ->orderByDesc('total')
        ->first();

    // Highest single transaction this month
    $highestExpense = (clone $thisMonthExpenses)->orderByDesc('amount')->first();

    // Recent 5 Incomes
    $recentIncomes = Income::with('category')
        ->where('user_id', $userId)
        ->orderBy('income_date', 'desc')
        ->orderBy('id', 'desc')
        ->take(5)
        ->get();

    // Monthly Trend Chart Data (Income vs Expense over past 6 months)
    $pastUserExpenses = Expense::where('user_id', $userId)
        ->where('expense_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
        ->orderBy('expense_date', 'asc')
        ->get();

    $pastUserIncomes = Income::where('user_id', $userId)
        ->where('income_date', '>=', Carbon::now()->subMonths(5)->startOfMonth())
        ->orderBy('income_date', 'asc')
        ->get();

    $monthKeys = collect();
    for ($i = 5; $i >= 0; $i--) {
        $monthKeys->push(Carbon::now()->subMonths($i)->format('Y-m'));
    }

    $monthlyData = $monthKeys->map(function ($ym) use ($pastUserExpenses, $pastUserIncomes) {
        $date = Carbon::parse($ym . '-01');
        $expSum = $pastUserExpenses->filter(function ($e) use ($ym) {
            return Carbon::parse($e->expense_date)->format('Y-m') === $ym;
        })->sum('amount');
        $incSum = $pastUserIncomes->filter(function ($inc) use ($ym) {
            return Carbon::parse($inc->income_date)->format('Y-m') === $ym;
        })->sum('amount');

        return (object) [
            'month' => $date->format('M'),
            'expense' => (float) $expSum,
            'income' => (float) $incSum,
        ];
    })->values();

    // Category Distribution Chart Data
    $categoryData = Expense::with('category')
        ->select('category_id', DB::raw('SUM(amount) as total'))
        ->where('user_id', $userId)
        ->whereMonth('expense_date', Carbon::now()->month)
        ->groupBy('category_id')
        ->get();

    // Recent 5 Transactions
    $recentExpenses = Expense::with('category')
        ->where('user_id', $userId)
        ->orderBy('expense_date', 'desc')
        ->take(5)
        ->get();
@endphp

<style>
    :root {
        --chaya-sm: 0 2px 8px -2px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
        --chaya-md: 0 10px 28px -8px rgba(15,23,42,0.08), 0 2px 6px rgba(15,23,42,0.04);
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.12), 0 6px 16px rgba(15,23,42,0.05);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(75px); opacity: .28; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 380px; height: 380px; background: radial-gradient(circle, #c7d2fe, transparent 70%); top: -120px; left: -80px; animation-duration: 22s; }
    .ambient-bg .blob-2 { width: 340px; height: 340px; background: radial-gradient(circle, #a7f3d0, transparent 70%); bottom: -100px; right: -80px; animation-duration: 26s; animation-delay: -6s; }
    
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(24px,-30px) scale(1.06); }
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .stagger-card { animation: fadeInUp .5s cubic-bezier(.16,1,.3,1) forwards; }

    .stat-card {
        background: #fff;
        box-shadow: var(--chaya-md);
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: transform .3s cubic-bezier(.16,1,.3,1), box-shadow .3s ease;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: var(--chaya-lg); }

    .count-up { font-variant-numeric: tabular-nums; }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="px-4 pb-16 space-y-8 max-w-6xl mx-auto">

    <!-- Top Greeting & Plan Header -->
    <div class="stagger-card flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white/70 backdrop-blur-md p-6 rounded-[2rem] border border-slate-200/80 shadow-sm">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                    Hello, {{ Auth::user()->name }} 👋
                </h1>
                
                <!-- Plan Pill -->
                @if($userPlan === 'pro')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 shadow-sm">
                    👑 Full Features (₹150)
                </span>
                @elseif($userPlan === 'medium')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                    🔥 Medium Plan (₹94)
                </span>
                @elseif($userPlan === 'basic')
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    ⚡ Basic Plan (₹49)
                </span>
                @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                    ✨ Free Plan (₹0)
                </span>
                @endif
            </div>
            <p class="text-slate-500 text-sm mt-1">Here is your live personal finance command center.</p>
        </div>

        <div class="flex items-center gap-3 flex-wrap">
            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-xl text-xs font-black bg-slate-900 text-cyan-400 hover:bg-slate-800 border border-slate-700 transition flex items-center gap-1.5 shadow-md shadow-slate-900/20">
                <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                <span>⚡ Admin Panel</span>
            </a>
            @endif

            <a href="{{ route('plans.show') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition">
                ⚡ Manage Plan
            </a>

            @if($canPdf)
            <a href="{{ route('expenses.pdf') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200 shadow-sm transition flex items-center gap-1.5">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Export PDF</span>
            </a>
            @endif

            <a href="{{ route('incomes.create') }}" class="px-5 py-2.5 rounded-xl text-xs font-black bg-emerald-600 text-white hover:bg-emerald-700 shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Earning 💰</span>
            </a>

            <a href="{{ route('expenses.create') }}" class="px-5 py-2.5 rounded-xl text-xs font-black bg-slate-900 text-white hover:bg-indigo-600 shadow-lg shadow-slate-900/10 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Expense</span>
            </a>
        </div>
    </div>

    <!-- Monthly Budget Goal & Spending Meter Widget -->
    <div class="stagger-card bg-white rounded-[2rem] p-6 sm:p-8 border border-slate-200/80 shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-black text-slate-900">Monthly Expense Budget Target</h3>
                    @if($isOverBudget)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-red-100 text-red-700">🚨 Over Budget</span>
                    @elseif($budgetPercentage >= 80)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800">⚠️ 80%+ Used</span>
                    @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800">✓ On Track</span>
                    @endif
                </div>
                <p class="text-slate-500 text-xs mt-0.5">
                    Spent ₹{{ number_format($thisMonthSpent) }} of ₹{{ number_format($monthlyBudget) }} target
                </p>
            </div>

            <!-- Quick Budget Setter -->
            <form action="{{ route('budget.update') }}" method="POST" class="flex items-center gap-2 w-full sm:w-auto">
                @csrf
                <div class="relative flex-1 sm:w-40">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">₹</span>
                    <input type="number" name="monthly_budget" value="{{ round($monthlyBudget) }}" step="500" min="500"
                           class="w-full pl-7 pr-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-slate-50"
                           placeholder="Set Budget">
                </div>
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-indigo-600 text-white text-xs font-bold rounded-xl transition shrink-0 cursor-pointer">
                    Update Goal
                </button>
            </form>
        </div>

        <!-- Visual Progress Bar -->
        <div class="w-full bg-slate-100 rounded-full h-4 overflow-hidden p-0.5 border border-slate-200/60">
            <div class="h-full rounded-full transition-all duration-1000 {{ $isOverBudget ? 'bg-gradient-to-r from-red-500 to-rose-600' : ($budgetPercentage >= 80 ? 'bg-gradient-to-r from-amber-400 to-orange-500' : 'bg-gradient-to-r from-emerald-400 to-teal-500') }}"
                 style="width: {{ $budgetPercentage }}%;">
            </div>
        </div>

        <div class="flex justify-between items-center text-xs font-bold mt-3 text-slate-600">
            <span>{{ $budgetPercentage }}% Used</span>
            <span>
                @if($isOverBudget)
                    <span class="text-red-600 font-extrabold">+₹{{ number_format($thisMonthSpent - $monthlyBudget) }} over limit</span>
                @else
                    <span class="text-emerald-600">₹{{ number_format($remainingBudget) }} remaining in budget</span>
                @endif
            </span>
        </div>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Total Earnings Inflow -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem] bg-gradient-to-br from-white to-emerald-50/40 border border-emerald-100/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Total Earnings</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                    💰
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-emerald-600 count-up" data-value="{{ $totalIncome }}">₹0</h2>
            <p class="text-[11px] text-emerald-700/80 font-semibold mt-1">This month: +₹{{ number_format($thisMonthIncome) }}</p>
        </div>

        <!-- Total Spent Outflow -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem] bg-gradient-to-br from-white to-rose-50/30 border border-rose-100/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Spent</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs">
                    💸
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 count-up" data-value="{{ $totalSpent }}">₹0</h2>
            <p class="text-[11px] text-slate-400 font-semibold mt-1">This month: -₹{{ number_format($thisMonthSpent) }}</p>
        </div>

        <!-- Net Cash Flow / Savings -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem] bg-gradient-to-br from-white to-indigo-50/30 border border-indigo-100/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Net Cash Flow</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-xs">
                    💎
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black {{ $allTimeNetSavings >= 0 ? 'text-indigo-600' : 'text-rose-600' }} count-up" data-value="{{ abs($allTimeNetSavings) }}">{{ $allTimeNetSavings >= 0 ? '+₹' : '-₹' }}0</h2>
            <p class="text-[11px] text-indigo-700/80 font-semibold mt-1">{{ $thisMonthNetSavings >= 0 ? 'Month: +₹' . number_format($thisMonthNetSavings) : 'Month: -₹' . number_format(abs($thisMonthNetSavings)) }} ({{ $savingsRate }}%)</p>
        </div>

        <!-- Daily Average Spending -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem] bg-gradient-to-br from-white to-amber-50/30 border border-amber-100/80">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Daily Avg Spend</span>
                <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                    📊
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 count-up" data-value="{{ $dailyAverage }}">₹0</h2>
            <p class="text-[11px] text-amber-800/80 font-semibold mt-1">Past {{ $daysPassed }} days</p>
        </div>

    </div>

    <!-- Smart Highlights Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white p-5 rounded-[2rem] border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Top Expense Category</p>
                <h4 class="font-black text-slate-800 text-sm mt-0.5">
                    {{ $topCategory->category->name ?? 'No entries yet' }}
                </h4>
                @if($topCategory)
                <p class="text-xs text-rose-600 font-bold">₹{{ number_format($topCategory->total) }}</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2rem] border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Highest Single Expense</p>
                <h4 class="font-black text-slate-800 text-sm mt-0.5">
                    {{ $highestExpense ? $highestExpense->paid_to : 'None yet' }}
                </h4>
                @if($highestExpense)
                <p class="text-xs text-amber-600 font-bold">₹{{ number_format($highestExpense->amount) }}</p>
                @endif
            </div>
        </div>

        <div class="bg-white p-5 rounded-[2rem] border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Net Financial Health</p>
                <h4 class="font-black text-slate-800 text-sm mt-0.5">
                    @if($thisMonthNetSavings > 0)
                        💚 Positive Inflow (+{{ $savingsRate }}%)
                    @elseif($thisMonthNetSavings < 0)
                        🚨 Negative Cash Flow
                    @else
                        ⚖️ Neutral / Balanced
                    @endif
                </h4>
                <p class="text-xs text-emerald-600 font-bold">{{ $thisMonthIncomeCount }} earnings vs {{ $thisMonthCount }} expenses</p>
            </div>
        </div>
    </div>

    <!-- Kharchify Split & Settle (Splitwise Shared Balances) Widget -->
    <div class="stagger-card bg-gradient-to-br from-white via-indigo-50/20 to-white p-6 sm:p-8 rounded-[2rem] border border-indigo-100 shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-600/20">
                    👥
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-slate-900 tracking-tight text-lg sm:text-xl">Split & Settle Balances</h3>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-100 text-indigo-800">Splitwise Engine</span>
                    </div>
                    <p class="text-xs text-slate-500">Rent, group trips, couple costs, dinners & IOUs</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('splits.groups.create') }}" class="px-4 py-2 bg-slate-900 text-white hover:bg-indigo-600 font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <span>New Group</span>
                </a>
                <a href="{{ route('splits.index') }}" class="px-4 py-2 bg-white text-indigo-600 hover:bg-indigo-50 border border-indigo-200 font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                    <span>Open Split Hub →</span>
                </a>
            </div>
        </div>

        <!-- Split Metric Chips -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">You Are Owed</p>
                    <p class="text-xl font-black text-emerald-600 mt-0.5">+₹{{ number_format($splitSummary['total_owed_to_user'], 2) }}</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                    ↓
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">You Owe</p>
                    <p class="text-xl font-black text-rose-600 mt-0.5">-₹{{ number_format($splitSummary['total_user_owes'], 2) }}</p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xs">
                    ↑
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Overall Net</p>
                    <p class="text-xl font-black {{ $splitSummary['overall_net'] >= 0 ? 'text-indigo-600' : 'text-rose-600' }} mt-0.5">
                        {{ $splitSummary['overall_net'] >= 0 ? '+₹' : '-₹' }}{{ number_format(abs($splitSummary['overall_net']), 2) }}
                    </p>
                </div>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">
                    ⚖️
                </div>
            </div>
        </div>

        <!-- Recent Groups Mini List -->
        @if(empty($splitSummary['groups']))
        <div class="p-6 text-center bg-white/80 rounded-2xl border border-dashed border-indigo-200">
            <p class="text-sm font-bold text-slate-700">No active split groups yet</p>
            <p class="text-xs text-slate-400 mt-0.5 max-w-md mx-auto">Create a group for your roommates, Goa trip, dinner split, or wedding party to simplify debts automatically.</p>
            <a href="{{ route('splits.groups.create') }}" class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition">
                <span>+ Create Your First Split Group</span>
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
            @foreach(array_slice($splitSummary['groups'], 0, 3) as $item)
            @php
                $g = $item['group'];
                $net = $item['net_balance'];
                $status = $item['status'];
            @endphp
            <a href="{{ route('splits.show', $g->id) }}" class="p-4 bg-white hover:bg-slate-50 rounded-2xl border border-slate-200/80 transition flex flex-col justify-between group">
                <div class="flex items-start justify-between gap-2">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">{{ $g->icon }}</span>
                        <div>
                            <h5 class="font-black text-slate-900 text-sm group-hover:text-indigo-600 transition">{{ $g->name }}</h5>
                            <p class="text-[11px] text-slate-400 font-medium">{{ $g->members->count() }} members • {{ ucfirst($g->type) }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 font-medium">Your status:</span>
                    @if($status === 'owed')
                        <span class="font-black text-emerald-600">+₹{{ number_format($net, 2) }}</span>
                    @elseif($status === 'owes')
                        <span class="font-black text-rose-600">-₹{{ number_format(abs($net), 2) }}</span>
                    @else
                        <span class="font-bold text-slate-500">Settled up ✓</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Visual Analytics Charts (Unlocked on Medium & Pro) -->
    @if($canCharts)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-[2rem] border border-slate-200/80 shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-900 tracking-tight text-base sm:text-lg">Monthly Cash Flow Comparison</h3>
                    <p class="text-xs text-slate-400">Income (+₹) vs Expenses (-₹) over 6 months</p>
                </div>
                <span class="bg-emerald-50 text-emerald-700 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase">Income vs Expense</span>
            </div>
            <div class="relative h-[250px]">
                <canvas id="cashflowChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-slate-200/80 shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-900 tracking-tight text-base sm:text-lg">Expense Category Split</h3>
                    <p class="text-xs text-slate-400">Current month spending distribution</p>
                </div>
                <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase">Categories</span>
            </div>
            <div class="relative h-[250px] flex justify-center">
                <canvas id="pieChart"></canvas>
            </div>
        </div>

    </div>
    @else
    <!-- Upgrade Banner for Free Users -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-8 rounded-[2rem] shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border border-indigo-700/60">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400 text-slate-950 text-[10px] font-black uppercase tracking-wider mb-2">
                🚀 Premium Tiers Coming Soon
            </div>
            <h3 class="text-2xl font-black text-white">Visual Analytics, Charts & PDF Exports Launching Soon!</h3>
            <p class="text-indigo-200 text-sm mt-1 max-w-xl">
                We are actively preparing our <strong>Basic (₹49)</strong>, <strong>Medium (₹94)</strong>, and <strong>Full Features (₹150)</strong> plans with interactive Income vs Expense comparison graphs, budget pacing meters, and formatted PDF statements.
            </p>
        </div>
        <a href="{{ route('plans.show') }}" class="px-6 py-3.5 bg-amber-400 text-slate-950 font-black rounded-2xl text-sm hover:bg-amber-300 transition shrink-0 shadow-lg flex items-center gap-2">
            <span>Preview Plans 🚀</span>
        </a>
    </div>
    @endif

    <!-- Dual Activity Section: Recent Incomes & Recent Expenses Side-by-Side -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Recent Earnings / Inflow -->
        <div class="bg-white p-6 sm:p-7 rounded-[2rem] border border-emerald-100 shadow-md">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm">
                        💰
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 tracking-tight text-base">Recent Earnings</h3>
                        <p class="text-[11px] text-slate-400">Money coming in</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('incomes.create') }}" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-xl font-bold text-xs transition">
                        + Add Inflow
                    </a>
                    <a href="{{ route('incomes.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 transition">
                        All →
                    </a>
                </div>
            </div>

            @if($recentIncomes->isEmpty())
            <div class="py-10 text-center text-slate-400">
                <span class="text-3xl">💰</span>
                <p class="font-medium text-xs mt-2 text-slate-500">No income or earnings logged yet.</p>
                <a href="{{ route('incomes.create') }}" class="inline-block mt-3 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-xl font-bold text-xs hover:bg-emerald-100 transition">
                    + Log Your First Earning
                </a>
            </div>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($recentIncomes as $inc)
                <div class="py-3 flex items-center justify-between hover:bg-emerald-50/30 px-2 rounded-xl transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-base shrink-0">
                            {{ $inc->category->icon ?? '💰' }}
                        </div>
                        <div class="min-w-0">
                            <h5 class="font-bold text-slate-900 text-xs sm:text-sm truncate">{{ $inc->source }}</h5>
                            <p class="text-[11px] text-slate-400 font-medium truncate">
                                <span class="text-emerald-600 font-semibold">{{ $inc->category->name ?? 'Income' }}</span> • 
                                {{ $inc->income_date ? Carbon::parse($inc->income_date)->format('d M') : '' }} • 
                                {{ $inc->payment_method }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <span class="font-black text-emerald-600 text-sm sm:text-base">+₹{{ number_format($inc->amount, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Recent Expenses / Outflow -->
        <div class="bg-white p-6 sm:p-7 rounded-[2rem] border border-slate-200/80 shadow-md">
            <div class="flex justify-between items-center mb-5 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-sm">
                        💸
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 tracking-tight text-base">Recent Expenses</h3>
                        <p class="text-[11px] text-slate-400">Money going out</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('expenses.create') }}" class="px-3 py-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-xl font-bold text-xs transition">
                        + Add Expense
                    </a>
                    <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                        All →
                    </a>
                </div>
            </div>

            @if($recentExpenses->isEmpty())
            <div class="py-10 text-center text-slate-400">
                <span class="text-3xl">💸</span>
                <p class="font-medium text-xs mt-2 text-slate-500">No expenses logged yet.</p>
                <a href="{{ route('expenses.create') }}" class="inline-block mt-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold text-xs">
                    + Add Your First Expense
                </a>
            </div>
            @else
            <div class="divide-y divide-slate-100">
                @foreach($recentExpenses as $exp)
                <div class="py-3 flex items-center justify-between hover:bg-slate-50 px-2 rounded-xl transition">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                            {{ substr($exp->category->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <h5 class="font-bold text-slate-900 text-xs sm:text-sm truncate">{{ $exp->paid_to }}</h5>
                            <p class="text-[11px] text-slate-400 font-medium truncate">
                                <span class="text-indigo-600 font-semibold">{{ $exp->category->name ?? 'General' }}</span> • 
                                {{ Carbon::parse($exp->expense_date)->format('d M') }} • 
                                {{ $exp->payment_method }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right shrink-0 ml-2">
                        <span class="font-black text-rose-600 text-sm sm:text-base">-₹{{ number_format($exp->amount, 2) }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>

</div>

@if($canCharts)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthly = @json($monthlyData);
    const categories = @json($categoryData);

    Chart.defaults.font.family = "'Plus Jakarta Sans', 'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';

    /* Count-up animation for stat numbers */
    document.querySelectorAll('.count-up').forEach(el => {
        const target = parseFloat(el.dataset.value) || 0;
        const duration = 800;
        const start = performance.now();
        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(target * eased);
            el.textContent = '₹' + value.toLocaleString('en-IN');
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });

    /* CASH FLOW COMPARISON BAR / LINE CHART (INCOME VS EXPENSE) */
    const cashflowCanvas = document.getElementById('cashflowChart');
    if (cashflowCanvas) {
        const ctx = cashflowCanvas.getContext('2d');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthly.length ? monthly.map(m => m.month) : ['No Data'],
                datasets: [
                    {
                        label: 'Earnings (+₹)',
                        data: monthly.length ? monthly.map(m => m.income) : [0],
                        backgroundColor: '#10b981',
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.6,
                        categoryPercentage: 0.6
                    },
                    {
                        label: 'Expenses (-₹)',
                        data: monthly.length ? monthly.map(m => m.expense) : [0],
                        backgroundColor: '#6366f1',
                        borderRadius: 8,
                        borderSkipped: false,
                        barPercentage: 0.6,
                        categoryPercentage: 0.6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 800, easing: 'easeOutCubic' },
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 12,
                            font: { size: 11, weight: 'bold' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + Number(context.raw).toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        grid: { color: '#f1f5f9' },
                        border: { dash: [4, 4] },
                        ticks: {
                            callback: function(val) { return '₹' + Number(val).toLocaleString('en-IN'); }
                        }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    /* PIE CHART WITH MODERN COLORS */
    const pieCanvas = document.getElementById('pieChart');
    if (pieCanvas) {
        new Chart(pieCanvas, {
            type: 'doughnut',
            data: {
                labels: categories.length ? categories.map(c => c.category?.name ?? 'Other') : ['No Expenses'],
                datasets: [{
                    data: categories.length ? categories.map(c => c.total) : [1],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#14b8a6'],
                    borderWidth: 0,
                    hoverOffset: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                animation: { duration: 800, easing: 'easeOutCubic' },
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 14, font: { size: 11 } } }
                }
            }
        });
    }
</script>
@else
<script>
    /* Count-up animation for stat numbers without chart library */
    document.querySelectorAll('.count-up').forEach(el => {
        const target = parseFloat(el.dataset.value) || 0;
        const duration = 800;
        const start = performance.now();
        function tick(now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.round(target * eased);
            el.textContent = '₹' + value.toLocaleString('en-IN');
            if (progress < 1) requestAnimationFrame(tick);
        }
        requestAnimationFrame(tick);
    });
</script>
@endif

@endsection

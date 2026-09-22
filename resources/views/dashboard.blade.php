@extends('layouts.app')

@section('content')

@php
    use App\Models\Expense;
    use App\Models\User;
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

    // Spending Metrics
    $totalSpent = Expense::where('user_id', $userId)->sum('amount');
    $todaySpent = Expense::where('user_id', $userId)->whereDate('expense_date', Carbon::today())->sum('amount');
    
    $thisMonthExpenses = Expense::where('user_id', $userId)
        ->whereMonth('expense_date', Carbon::now()->month)
        ->whereYear('expense_date', Carbon::now()->year);
    
    $thisMonthSpent = (clone $thisMonthExpenses)->sum('amount');
    $thisMonthCount = (clone $thisMonthExpenses)->count();
    
    $lastMonthSpent = Expense::where('user_id', $userId)
        ->whereMonth('expense_date', Carbon::now()->subMonth()->month)
        ->whereYear('expense_date', Carbon::now()->subMonth()->year)
        ->sum('amount');

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

    // Monthly Trend Chart Data (Database-agnostic)
    $pastUserExpenses = Expense::where('user_id', $userId)
        ->where('expense_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
        ->orderBy('expense_date', 'asc')
        ->get();

    $monthlyData = $pastUserExpenses->groupBy(function ($expense) {
        return Carbon::parse($expense->expense_date)->format('Y-m');
    })->map(function ($items, $key) {
        $date = Carbon::parse($key . '-01');
        return (object) [
            'month' => $date->format('M'),
            'total' => $items->sum('amount'),
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
            <a href="{{ route('expenses.pdf') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow-md shadow-emerald-600/20 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Download PDF</span>
            </a>
            @else
            <a href="{{ route('plans.show') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 text-slate-500 hover:text-indigo-600 border border-slate-200 transition flex items-center gap-1.5" title="Unlock Formatted PDF Statements with Basic Plan (₹49)">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>PDF (Basic ₹49+)</span>
            </a>
            @endif

            <a href="{{ route('expenses.create') }}" class="px-5 py-2.5 rounded-xl text-xs font-black bg-slate-900 text-white hover:bg-indigo-600 shadow-lg shadow-slate-900/10 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Expense</span>
            </a>
        </div>
    </div>

    <!-- Monthly Budget Goal & Spending Meter Widget -->
    <div class="stagger-card bg-white rounded-[2rem] p-6 sm:p-8 border border-slate-200/80 shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-5">
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-lg font-black text-slate-900">Monthly Budget Progress</h3>
                    @if($isOverBudget)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-red-100 text-red-700">🚨 Over Budget</span>
                    @elseif($budgetPercentage >= 80)
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-amber-100 text-amber-800">⚠️ 80%+ Used</span>
                    @else
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black bg-emerald-100 text-emerald-800">✓ On Track</span>
                    @endif
                </div>
                <p class="text-slate-500 text-xs mt-0.5">
                    Month of {{ Carbon::now()->format('F Y') }} • Spent ₹{{ number_format($thisMonthSpent) }} of ₹{{ number_format($monthlyBudget) }} target
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
                <button type="submit" class="px-3 py-1.5 bg-slate-800 hover:bg-indigo-600 text-white text-xs font-bold rounded-xl transition shrink-0">
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
                    <span class="text-emerald-600">₹{{ number_format($remainingBudget) }} remaining</span>
                @endif
            </span>
        </div>
    </div>

    <!-- 4 Main KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
        
        <!-- Total Spent -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total All-Time</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 count-up" data-value="{{ $totalSpent }}">₹0</h2>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Cumulative records</p>
        </div>

        <!-- This Month -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem] bg-white border border-indigo-100/90 shadow-sm relative">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 animate-pulse"></span>
                    This Month
                </span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 count-up" data-value="{{ $thisMonthSpent }}">₹0</h2>
            <p class="text-[11px] text-slate-400 font-medium mt-1">{{ $thisMonthCount }} entries this month</p>
        </div>

        <!-- Today -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Today</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 count-up" data-value="{{ $todaySpent }}">₹0</h2>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Logged today</p>
        </div>

        <!-- Daily Average -->
        <div class="stat-card p-5 sm:p-6 rounded-[2rem]">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Daily Avg</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 count-up" data-value="{{ $dailyAverage }}">₹0</h2>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Per day (past {{ $daysPassed }} days)</p>
        </div>

    </div>

    <!-- Smart Highlights Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <div class="bg-white p-5 rounded-[2rem] border border-slate-200/80 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase">Top Category This Month</p>
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
                <p class="text-xs font-bold text-slate-400 uppercase">Financial Health Score</p>
                <h4 class="font-black text-slate-800 text-sm mt-0.5">
                    @if($isOverBudget) 52 / 100 (Needs Attention)
                    @elseif($budgetPercentage >= 80) 74 / 100 (Moderate)
                    @else 94 / 100 (Excellent)
                    @endif
                </h4>
                <p class="text-xs text-emerald-600 font-bold">Based on budget pacing</p>
            </div>
        </div>
    </div>

    <!-- Visual Analytics Charts (Unlocked on Medium & Pro) -->
    @if($canCharts)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="bg-white p-6 rounded-[2rem] border border-slate-200/80 shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-900 tracking-tight text-base sm:text-lg">Monthly Trends</h3>
                    <p class="text-xs text-slate-400">Spending history over last 6 months</p>
                </div>
                <span class="bg-indigo-50 text-indigo-600 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase">Visual</span>
            </div>
            <div class="relative h-[240px]">
                <canvas id="lineChart"></canvas>
            </div>
        </div>

        <div class="bg-white p-6 rounded-[2rem] border border-slate-200/80 shadow-md">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="font-bold text-slate-900 tracking-tight text-base sm:text-lg">Category Split</h3>
                    <p class="text-xs text-slate-400">Current month distribution</p>
                </div>
                <span class="bg-emerald-50 text-emerald-600 text-[10px] font-black px-2.5 py-1 rounded-lg uppercase">Analysis</span>
            </div>
            <div class="relative h-[240px] flex justify-center">
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
                We are actively preparing our <strong>Basic (₹49)</strong>, <strong>Medium (₹94)</strong>, and <strong>Full Features (₹150)</strong> plans with interactive graphs, budget pacing meters, and formatted PDF statements.
            </p>
        </div>
        <a href="{{ route('plans.show') }}" class="px-6 py-3.5 bg-amber-400 text-slate-950 font-black rounded-2xl text-sm hover:bg-amber-300 transition shrink-0 shadow-lg flex items-center gap-2">
            <span>Preview Plans 🚀</span>
        </a>
    </div>
    @endif

    <!-- Recent Transactions -->
    <div class="bg-white p-6 sm:p-8 rounded-[2rem] border border-slate-200/80 shadow-md">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="font-bold text-slate-900 tracking-tight text-base sm:text-lg">Recent Transactions</h3>
                <p class="text-xs text-slate-400">Latest expenses logged</p>
            </div>
            <a href="{{ route('expenses.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition">
                View All Expenses →
            </a>
        </div>

        @if($recentExpenses->isEmpty())
        <div class="py-12 text-center text-slate-400">
            <p class="font-medium">No expenses logged yet.</p>
            <a href="{{ route('expenses.create') }}" class="inline-block mt-3 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-bold text-xs">
                + Add Your First Expense
            </a>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($recentExpenses as $exp)
            <div class="py-3.5 flex items-center justify-between hover:bg-slate-50/60 px-2 rounded-xl transition">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0">
                        {{ substr($exp->category->name ?? 'U', 0, 1) }}
                    </div>
                    <div>
                        <h5 class="font-bold text-slate-900 text-sm">{{ $exp->paid_to }}</h5>
                        <p class="text-[11px] text-slate-400 font-medium">
                            <span class="text-indigo-600 font-semibold">{{ $exp->category->name ?? 'General' }}</span> • 
                            {{ Carbon::parse($exp->expense_date)->format('d M, Y') }} • 
                            {{ $exp->payment_method }}
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <span class="font-black text-rose-600 text-sm sm:text-base">-₹{{ number_format($exp->amount, 2) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>

@if($canCharts)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const monthly = @json($monthlyData);
    const categories = @json($categoryData);

    Chart.defaults.font.family = "'Inter', sans-serif";
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

    /* LINE CHART WITH GRADIENT */
    const lineCanvas = document.getElementById('lineChart');
    if (lineCanvas) {
        const ctx = lineCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(79, 70, 229, 0.30)');
        gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: monthly.length ? monthly.map(m => m.month) : ['No Data'],
                datasets: [{
                    label: 'Expenses',
                    data: monthly.length ? monthly.map(m => m.total) : [0],
                    borderColor: '#4f46e5',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.35,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#4f46e5',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 800, easing: 'easeOutCubic' },
                plugins: { legend: { display: false } },
                scales: {
                    y: { grid: { color: '#f1f5f9' }, border: { dash: [4, 4] } },
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

@extends('layouts.admin')

@section('admin-content')

<div class="space-y-6">

    <!-- Header Ribbon -->
    <div class="saas-card p-6 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Platform Financials & Analytics</h1>
                <span class="px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-300 text-xs font-bold border border-indigo-500/30">
                    Aggregated Metrics
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Platform financial telemetry across all registered user accounts and category distributions.</p>
        </div>
    </div>

    <!-- 4 Key Telemetry Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Gross Platform Volume</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-sm border border-emerald-500/20">
                    💰
                </div>
            </div>
            <div class="text-3xl font-black text-emerald-400 tracking-tight">₹{{ number_format($totalVolume, 2) }}</div>
            <p class="text-xs text-slate-400 mt-3 font-medium">{{ number_format($totalExpenses) }} total logged transactions</p>
        </div>

        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg / Transaction</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-500/20">
                    📊
                </div>
            </div>
            <div class="text-3xl font-black text-indigo-400 tracking-tight">₹{{ number_format($averageExpense, 2) }}</div>
            <p class="text-xs text-slate-400 mt-3 font-medium">Average ticket size</p>
        </div>

        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Avg Spend / User</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-500/20">
                    📈
                </div>
            </div>
            <div class="text-3xl font-black text-amber-400 tracking-tight">₹{{ number_format($averagePerUser, 2) }}</div>
            <p class="text-xs text-slate-400 mt-3 font-medium">Across {{ $totalUsers }} active accounts</p>
        </div>

        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Accounts</span>
                <div class="w-9 h-9 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center font-bold text-sm border border-violet-500/20">
                    👥
                </div>
            </div>
            <div class="text-3xl font-black text-white tracking-tight">{{ $totalUsers }}</div>
            <p class="text-xs text-slate-400 mt-3 flex items-center gap-1.5 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                Active user base
            </p>
        </div>

    </div>

    <!-- 6-Month Platform Volume & Payment Mode Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- 6-Month Monthly Trajectory -->
        <div class="saas-card p-6 rounded-2xl">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-base font-black text-white tracking-tight">6-Month Spending Trajectory</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Historical monthly expense volume</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800 text-[10px] font-bold text-slate-400">Monthly</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                            <th class="p-3.5 rounded-l-xl">Month</th>
                            <th class="p-3.5 text-center">Entries</th>
                            <th class="p-3.5 text-right rounded-r-xl">Volume</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                        @forelse($monthlyVolume as $m)
                        <tr class="hover:bg-slate-800/40 transition">
                            <td class="p-3.5 font-bold text-white">{{ $m->month_name }} {{ $m->year }}</td>
                            <td class="p-3.5 text-center text-indigo-300 font-bold">{{ $m->count }} logs</td>
                            <td class="p-3.5 text-right font-bold text-emerald-400 text-sm">₹{{ number_format($m->total, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="p-6 text-center text-slate-500">No monthly volume recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Mode Breakdown -->
        <div class="saas-card p-6 rounded-2xl">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-base font-black text-white tracking-tight">Payment Method Distribution</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Volume share by payment channel</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800 text-[10px] font-bold text-slate-400">All Users</span>
            </div>

            <div class="space-y-4">
                @forelse($paymentMethods as $pm)
                @php
                    $percentage = $totalVolume > 0 ? round(($pm->total / $totalVolume) * 100, 1) : 0;
                @endphp
                <div class="p-3.5 rounded-xl bg-slate-900/70 border border-slate-800">
                    <div class="flex justify-between items-center text-xs mb-2 font-medium">
                        <span class="text-white font-bold">{{ $pm->payment_method ?: 'Uncategorized' }}</span>
                        <span class="text-indigo-300 font-bold">₹{{ number_format($pm->total) }} <span class="text-slate-500 font-normal">({{ $percentage }}%)</span></span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-indigo-500 to-violet-500 h-full rounded-full transition-all duration-700" style="width: {{ $percentage }}%;"></div>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-500 p-4 text-center">No payment method logs found.</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Top Spenders Platform Ranking -->
    <div class="saas-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-base font-black text-white tracking-tight">Top 10 Accounts By Expense Volume</h3>
                <p class="text-xs text-slate-400 mt-0.5">Users with highest recorded ledger amounts</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-amber-500/15 text-amber-300 text-xs font-bold border border-amber-500/30">
                Top Spenders
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <th class="p-4 w-14">Rank</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Assigned Plan</th>
                        <th class="p-4 text-center">Transactions</th>
                        <th class="p-4 text-right">Cumulative Spend</th>
                        <th class="p-4 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-medium text-slate-300">
                    @forelse($topSpenders as $index => $spender)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-4 font-bold {{ $index === 0 ? 'text-amber-400 text-sm' : ($index === 1 ? 'text-slate-300 text-sm' : ($index === 2 ? 'text-amber-600 text-sm' : 'text-slate-500')) }}">
                            @if($index === 0) 🥇 #1
                            @elseif($index === 1) 🥈 #2
                            @elseif($index === 2) 🥉 #3
                            @else #{{ $index + 1 }}
                            @endif
                        </td>
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-bold text-xs text-white shrink-0">
                                    {{ strtoupper(substr($spender->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white">{{ $spender->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $spender->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @php
                                $plan = $spender->plan ?? 'free';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border
                                {{ $plan === 'pro' ? 'bg-rose-500/15 text-rose-300 border-rose-500/30' : ($plan === 'medium' ? 'bg-amber-500/15 text-amber-300 border-amber-500/30' : ($plan === 'basic' ? 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30' : 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30')) }}">
                                {{ strtoupper($plan) }}
                            </span>
                        </td>
                        <td class="p-4 text-center text-slate-300">
                            {{ $spender->expenses_count }} entries
                        </td>
                        <td class="p-4 text-right font-black text-emerald-400 text-sm">
                            ₹{{ number_format($spender->expenses_sum_amount ?? 0, 2) }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('admin.users.show', $spender->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-indigo-600 hover:text-white text-indigo-400 font-bold text-xs transition border border-slate-700">
                                Inspect →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-500">No user financial activity recorded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection

@extends('layouts.admin')

@section('admin-content')

<div class="space-y-6">

    <!-- Top Overview Header -->
    <div class="saas-card p-6 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Admin Dashboard</h1>
                <span class="px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 text-xs font-bold border border-emerald-500/30 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-live"></span>
                    Live Platform
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Real-time overview of user registrations, subscription plans, and platform finances.</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center gap-2 shadow-lg shadow-indigo-600/25 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Add User</span>
            </a>
            <a href="{{ route('admin.analytics') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition flex items-center gap-2 border border-slate-700 active:scale-95">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                <span>Analytics</span>
            </a>
        </div>
    </div>

    <!-- 4 Primary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        
        <!-- Total Users -->
        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Registered Users</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-sm border border-indigo-500/20">
                    👥
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-white tracking-tight">{{ number_format($totalUsers) }}</div>
            <div class="flex items-center gap-2 text-xs text-emerald-400 mt-3 font-medium">
                <span class="px-2 py-0.5 rounded-md bg-emerald-500/10 border border-emerald-500/20 font-bold">+{{ $newUsersToday }} today</span>
                <span class="text-slate-500">•</span>
                <span class="text-slate-400">+{{ $newUsersThisWeek }} this week</span>
            </div>
        </div>

        <!-- Projected Plan Revenue -->
        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Projected Monthly MRR</span>
                <div class="w-9 h-9 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-sm border border-amber-500/20">
                    ₹
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-amber-400 tracking-tight">₹{{ number_format($projectedMonthlyRevenue) }}</div>
            <p class="text-xs text-slate-400 mt-3 flex items-center gap-1.5 font-medium">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                {{ $planCounts['basic'] + $planCounts['medium'] + $planCounts['pro'] }} paid tier subscribers
            </p>
        </div>

        <!-- Platform Total Spend Tracked -->
        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Platform Spend Logged</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-sm border border-emerald-500/20">
                    💰
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-emerald-400 tracking-tight">₹{{ number_format($totalMoneyTracked, 2) }}</div>
            <p class="text-xs text-slate-400 mt-3 font-medium">Recorded across all users</p>
        </div>

        <!-- Total System Records -->
        <div class="saas-card p-5 sm:p-6 rounded-2xl relative overflow-hidden group">
            <div class="flex justify-between items-start mb-2">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Expense Logs</span>
                <div class="w-9 h-9 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center font-bold text-sm border border-violet-500/20">
                    📝
                </div>
            </div>
            <div class="text-3xl sm:text-4xl font-black text-white tracking-tight">{{ number_format($totalExpensesCount) }}</div>
            <p class="text-xs text-slate-400 mt-3 font-medium">{{ number_format($totalCategoriesCount) }} custom categories active</p>
        </div>

    </div>

    <!-- 4 Membership Plans Status -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Free Plan -->
        <div class="saas-card p-5 rounded-2xl border-l-4 border-l-emerald-500 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-400 text-[10px] font-black border border-emerald-500/30">
                        ACTIVE NOW
                    </span>
                    <span class="text-2xl font-black text-white">₹0</span>
                </div>
                <h3 class="text-base font-black text-white mt-2">Free Plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">30 entries/mo + PDF downloads</p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-800 flex justify-between items-center text-xs">
                <span class="text-slate-400">Subscribed Users:</span>
                <span class="font-black text-emerald-400 text-sm">{{ $planCounts['free'] }}</span>
            </div>
        </div>

        <!-- Basic Plan -->
        <div class="saas-card p-5 rounded-2xl border-l-4 border-l-indigo-500 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/15 text-indigo-400 text-[10px] font-black border border-indigo-500/30">
                        COMING SOON
                    </span>
                    <span class="text-2xl font-black text-white">₹49<span class="text-xs text-slate-400 font-normal">/mo</span></span>
                </div>
                <h3 class="text-base font-black text-white mt-2">Basic Plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">150 entries/mo + custom tags</p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-800 flex justify-between items-center text-xs">
                <span class="text-slate-400">Subscribed Users:</span>
                <span class="font-black text-indigo-400 text-sm">{{ $planCounts['basic'] }}</span>
            </div>
        </div>

        <!-- Medium Plan -->
        <div class="saas-card p-5 rounded-2xl border-l-4 border-l-amber-500 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-0.5 rounded-full bg-amber-500/15 text-amber-400 text-[10px] font-black border border-amber-500/30">
                        COMING SOON
                    </span>
                    <span class="text-2xl font-black text-white">₹94<span class="text-xs text-slate-400 font-normal">/mo</span></span>
                </div>
                <h3 class="text-base font-black text-white mt-2">Medium Plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">Unlimited logs + charts + CSV</p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-800 flex justify-between items-center text-xs">
                <span class="text-slate-400">Subscribed Users:</span>
                <span class="font-black text-amber-400 text-sm">{{ $planCounts['medium'] }}</span>
            </div>
        </div>

        <!-- Full Features Plan -->
        <div class="saas-card p-5 rounded-2xl border-l-4 border-l-rose-500 flex flex-col justify-between">
            <div>
                <div class="flex justify-between items-start">
                    <span class="px-2.5 py-0.5 rounded-full bg-rose-500/15 text-rose-400 text-[10px] font-black border border-rose-500/30">
                        COMING SOON
                    </span>
                    <span class="text-2xl font-black text-white">₹150<span class="text-xs text-slate-400 font-normal">/mo</span></span>
                </div>
                <h3 class="text-base font-black text-white mt-2">Full Features Plan</h3>
                <p class="text-xs text-slate-400 mt-0.5">All features + PDF + insights</p>
            </div>
            <div class="pt-4 mt-4 border-t border-slate-800 flex justify-between items-center text-xs">
                <span class="text-slate-400">Subscribed Users:</span>
                <span class="font-black text-rose-400 text-sm">{{ $planCounts['pro'] }}</span>
            </div>
        </div>

    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- 30-Day Registration Trajectory Curve -->
        <div class="saas-card p-6 rounded-2xl lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-black text-white tracking-tight">30-Day User Registrations</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Daily account registration growth</p>
                </div>
                <span class="text-xs text-slate-400 bg-slate-800 px-3 py-1 rounded-full">Last 30 Days</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>

        <!-- Subscription Share Doughnut -->
        <div class="saas-card p-6 rounded-2xl">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-base font-black text-white tracking-tight">Plan Distribution</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Active plan breakdown</p>
                </div>
                <span class="text-xs text-slate-400 bg-slate-800 px-3 py-1 rounded-full">All Users</span>
            </div>
            <div class="relative h-48 w-full flex items-center justify-center">
                <canvas id="planShareChart"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-4 text-[11px] pt-4 border-t border-slate-800">
                <div class="flex items-center gap-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Free: {{ $planCounts['free'] }}
                </div>
                <div class="flex items-center gap-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> Basic: {{ $planCounts['basic'] }}
                </div>
                <div class="flex items-center gap-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Medium: {{ $planCounts['medium'] }}
                </div>
                <div class="flex items-center gap-1.5 text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Pro: {{ $planCounts['pro'] }}
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Registered Users Table -->
    <div class="saas-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h3 class="text-base font-black text-white tracking-tight">Recent User Registrations</h3>
                <p class="text-xs text-slate-400 mt-0.5">Latest accounts registered on the platform</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition flex items-center gap-1">
                <span>View All Users ({{ $totalUsers }})</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <th class="p-4">User</th>
                        <th class="p-4">Assigned Plan</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Joined Date</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                    @forelse($recentUsers as $u)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-bold text-xs text-white shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white">{{ $u->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @php
                                $plan = $u->plan ?? 'free';
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border
                                {{ $plan === 'pro' ? 'bg-rose-500/15 text-rose-300 border-rose-500/30' : ($plan === 'medium' ? 'bg-amber-500/15 text-amber-300 border-amber-500/30' : ($plan === 'basic' ? 'bg-indigo-500/15 text-indigo-300 border-indigo-500/30' : 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30')) }}">
                                {{ strtoupper($plan) }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($u->isAdmin())
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-[10px] font-bold border border-amber-500/30">
                                👑 Admin
                            </span>
                            @else
                            <span class="text-slate-400 text-xs">User</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-400">
                            {{ $u->created_at ? $u->created_at->format('d M Y, H:i') : 'N/A' }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('admin.users.show', $u->id) }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-indigo-600 hover:text-white text-indigo-400 font-bold text-xs transition border border-slate-700">
                                Inspect →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-slate-500">No users found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Chart.js Scripts -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    
    // User Growth Curve
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    const labels = @json($growthLabels);
    const counts = @json($growthValues);

    const gradient = userGrowthCtx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.35)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'New Registrations',
                data: counts,
                borderColor: '#6366F1',
                borderWidth: 2.5,
                backgroundColor: gradient,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#818CF8',
                pointBorderColor: '#0B0F19',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleColor: '#F8FAFC',
                    bodyColor: '#C7D2FE',
                    padding: 10,
                    borderColor: '#334155',
                    borderWidth: 1,
                    displayColors: false,
                }
            },
            scales: {
                x: {
                    grid: { display: false, color: '#1E293B' },
                    ticks: { color: '#64748B', font: { size: 10 } }
                },
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: {
                        color: '#64748B',
                        font: { size: 10 },
                        precision: 0,
                    },
                    beginAtZero: true
                }
            }
        }
    });

    // Plan Share Doughnut
    const planCtx = document.getElementById('planShareChart').getContext('2d');
    new Chart(planCtx, {
        type: 'doughnut',
        data: {
            labels: ['Free', 'Basic (₹49)', 'Medium (₹94)', 'Pro (₹150)'],
            datasets: [{
                data: [
                    {{ $planCounts['free'] }},
                    {{ $planCounts['basic'] }},
                    {{ $planCounts['medium'] }},
                    {{ $planCounts['pro'] }}
                ],
                backgroundColor: ['#10B981', '#6366F1', '#F59E0B', '#F43F5E'],
                borderWidth: 2,
                borderColor: '#111827',
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1E293B',
                    titleColor: '#F8FAFC',
                    bodyColor: '#F1F5F9',
                    padding: 10,
                    borderColor: '#334155',
                    borderWidth: 1,
                }
            }
        }
    });

});
</script>

@endsection

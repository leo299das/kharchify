@extends('layouts.admin')

@section('admin-content')

<div class="space-y-6">

    <!-- Breadcrumb & Back Action -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to User Management</span>
        </a>

        <div class="flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-slate-900 border border-slate-800">
            <span class="text-slate-400">User ID:</span>
            <span class="text-indigo-400 font-bold">#{{ $user->id }}</span>
        </div>
    </div>

    <!-- User Header Panel -->
    <div class="saas-card p-6 sm:p-7 rounded-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-black text-2xl text-white shadow-lg shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $user->name }}</h1>
                    @if($user->isAdmin())
                    <span class="px-3 py-0.5 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold border border-amber-500/30">
                        👑 Administrator
                    </span>
                    @else
                    <span class="px-3 py-0.5 rounded-full bg-slate-800 text-slate-400 text-xs font-bold border border-slate-700">
                        Standard User
                    </span>
                    @endif
                </div>
                <p class="text-xs sm:text-sm text-slate-400 mt-1 flex items-center gap-2 flex-wrap">
                    <span>{{ $user->email }}</span>
                    <span class="text-slate-600">•</span>
                    <span>Registered on {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : 'N/A' }}</span>
                </p>
            </div>
        </div>

        <!-- Quick Actions & Plan Change -->
        <div class="flex items-center gap-3 flex-wrap">
            <!-- Toggle Admin Button -->
            <form action="{{ route('admin.users.toggle-admin', $user->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold transition border active:scale-95
                    {{ $user->isAdmin() ? 'bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border-amber-500/30' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                    {{ $user->isAdmin() ? 'Demote from Admin' : 'Promote to Admin 👑' }}
                </button>
            </form>

            @if($user->id !== auth()->id())
            <!-- Delete User -->
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-500/15 hover:bg-rose-500/25 text-rose-300 border border-rose-500/30 transition active:scale-95">
                    Delete User
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- 4 Stats Cards & Plan Selector -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        
        <!-- Assigned Plan Control -->
        <div class="saas-card p-5 rounded-2xl flex flex-col justify-between">
            <span class="text-xs font-bold text-slate-400 uppercase">Assigned Plan</span>
            <form action="{{ route('admin.users.update-plan', $user->id) }}" method="POST" class="mt-2">
                @csrf
                <select name="plan" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs font-bold text-white focus:border-indigo-500 focus:outline-none cursor-pointer">
                    <option value="free" {{ ($user->plan === 'free' || empty($user->plan)) ? 'selected' : '' }}>Free Plan (₹0)</option>
                    <option value="basic" {{ $user->plan === 'basic' ? 'selected' : '' }}>Basic Plan (₹49)</option>
                    <option value="medium" {{ $user->plan === 'medium' ? 'selected' : '' }}>Medium Plan (₹94)</option>
                    <option value="pro" {{ $user->plan === 'pro' ? 'selected' : '' }}>Full Features (₹150)</option>
                </select>
            </form>
            <p class="text-[11px] text-slate-400 mt-2">Instant update on select</p>
        </div>

        <!-- Lifetime Total Spend -->
        <div class="saas-card p-5 rounded-2xl">
            <span class="text-xs font-bold text-slate-400 uppercase">Lifetime Spend</span>
            <div class="text-2xl font-black text-emerald-400 mt-2">₹{{ number_format($user->expenses_sum_amount ?? 0, 2) }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Recorded expense volume</p>
        </div>

        <!-- Total Transactions -->
        <div class="saas-card p-5 rounded-2xl">
            <span class="text-xs font-bold text-slate-400 uppercase">Total Entries</span>
            <div class="text-2xl font-black text-white mt-2">{{ number_format($user->expenses_count ?? 0) }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Logged transactions</p>
        </div>

        <!-- Custom Categories -->
        <div class="saas-card p-5 rounded-2xl">
            <span class="text-xs font-bold text-slate-400 uppercase">Categories</span>
            <div class="text-2xl font-black text-indigo-400 mt-2">{{ $categories->count() }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Custom tags created</p>
        </div>

        <!-- Monthly Budget Target -->
        <div class="saas-card p-5 rounded-2xl">
            <span class="text-xs font-bold text-slate-400 uppercase">Budget Limit</span>
            <div class="text-2xl font-black text-amber-400 mt-2">₹{{ number_format($user->monthly_budget ?? 25000) }}</div>
            <p class="text-[11px] text-slate-400 mt-1">Target monthly cap</p>
        </div>

    </div>

    <!-- Custom Categories List -->
    @if($categories->count() > 0)
    <div class="saas-card p-5 rounded-2xl">
        <h3 class="text-sm font-bold text-white mb-3">User's Expense Categories</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($categories as $cat)
            <span class="px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-700 text-xs font-medium flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $cat->color ?? '#6366F1' }};"></span>
                <span class="text-white font-bold">{{ $cat->name }}</span>
                <span class="text-slate-400 text-[10px]">({{ $cat->expenses()->where('user_id', $user->id)->count() }})</span>
            </span>
            @endforeach
        </div>
    </div>
    @endif

    <!-- User Expense History Ledger -->
    <div class="saas-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-base font-black text-white tracking-tight">Expense History Ledger</h3>
                <p class="text-xs text-slate-400 mt-0.5">All transaction records logged by {{ $user->name }}</p>
            </div>
            <span class="text-xs font-bold text-slate-400 bg-slate-900 px-3 py-1 rounded-full border border-slate-800">
                {{ $expenses->total() }} Total Entries
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <th class="p-4">Date</th>
                        <th class="p-4">Title / Description</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Payment Method</th>
                        <th class="p-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-800/40 transition">
                        <td class="p-4 text-slate-400">
                            {{ $exp->expense_date ? \Carbon\Carbon::parse($exp->expense_date)->format('d M Y') : 'N/A' }}
                        </td>
                        <td class="p-4 font-bold text-white">
                            {{ $exp->paid_to ?? 'Expense' }}
                            @if($exp->note)
                            <div class="text-[11px] text-slate-400 font-normal mt-0.5">{{ $exp->note }}</div>
                            @endif
                        </td>
                        <td class="p-4">
                            @if($exp->category)
                            <span class="px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-900 border border-slate-700 text-white flex items-center gap-1.5 w-fit">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $exp->category->color ?? '#6366F1' }};"></span>
                                {{ $exp->category->name }}
                            </span>
                            @else
                            <span class="text-slate-500">Uncategorized</span>
                            @endif
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-slate-900 text-indigo-300 border border-slate-800">
                                {{ $exp->payment_method ?? 'Cash' }}
                            </span>
                        </td>
                        <td class="p-4 text-right font-black text-emerald-400 text-sm">
                            ₹{{ number_format($exp->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-500">
                            No expenses logged by this user yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/40">
            {{ $expenses->links() }}
        </div>
        @endif
    </div>

</div>

@endsection

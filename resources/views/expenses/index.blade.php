@extends('layouts.app')

@section('content')

@php
    $user = Auth::user();
    $canPdf = $user->canExportPdf();
    $canCsv = $user->canExportCsv();
@endphp

<style>
    :root {
        --chaya-sm: 0 2px 8px -2px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
        --chaya-md: 0 10px 28px -8px rgba(15,23,42,0.08), 0 2px 6px rgba(15,23,42,0.04);
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.12), 0 6px 16px rgba(15,23,42,0.05);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(75px); opacity: .24; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 360px; height: 360px; background: radial-gradient(circle, #c7d2fe, transparent 70%); top: -110px; right: -70px; animation-duration: 24s; }
    .ambient-bg .blob-2 { width: 300px; height: 300px; background: radial-gradient(circle, #a7f3d0, transparent 70%); bottom: -90px; left: -70px; animation-duration: 20s; animation-delay: -8s; }
    
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(18px,-22px) scale(1.05); }
    }

    .card-box {
        background: #fff;
        box-shadow: var(--chaya-md);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="px-4 pb-20 max-w-6xl mx-auto space-y-6">

    <!-- Top Header & Action Row -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/70 backdrop-blur-md p-6 rounded-[2rem] border border-slate-200/80 shadow-sm">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Expense Ledger</h1>
            <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider mt-1">Transaction History & Filtering</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
            <!-- CSV Export Button -->
            @if($canCsv)
            <a href="{{ route('expenses.export-csv') }}"
               class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 border border-slate-200">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export CSV</span>
            </a>
            @else
            <a href="{{ route('plans.show') }}"
               title="Unlock with Medium (₹94) & Pro tiers"
               class="px-4 py-2.5 rounded-xl bg-slate-50 text-slate-500 text-xs font-semibold flex items-center gap-1.5 border border-slate-200 hover:text-indigo-600">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>CSV (Medium ₹94+)</span>
            </a>
            @endif

            <!-- PDF Export Button -->
            @if($canPdf)
            <a href="{{ route('expenses.pdf') }}"
               class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-emerald-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Export PDF</span>
            </a>
            @else
            <a href="{{ route('plans.show') }}"
               title="Unlock with Basic Plan (₹49)"
               class="px-4 py-2.5 rounded-xl bg-slate-50 text-slate-500 text-xs font-semibold flex items-center gap-1.5 border border-slate-200 hover:text-indigo-600">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                <span>PDF (Basic ₹49+)</span>
            </a>
            @endif

            <!-- Add Expense Button -->
            <a href="{{ route('expenses.create') }}"
               class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-black transition flex items-center gap-1.5 shadow-lg shadow-indigo-600/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add Expense</span>
            </a>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="card-box rounded-[2rem] p-5 sm:p-6">
        <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Keyword Search -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Search Merchant / Note</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="e.g. Swiggy, Uber, Rent..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Category</label>
                <select name="category_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Payment Method Filter -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Payment Method</label>
                <select name="payment_method" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50/70 focus:bg-white focus:ring-2 focus:ring-indigo-500 font-medium">
                    <option value="">All Payment Modes</option>
                    @foreach($paymentMethods as $pm)
                    <option value="{{ $pm }}" {{ request('payment_method') == $pm ? 'selected' : '' }}>
                        {{ $pm }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-indigo-600 text-white font-bold text-xs rounded-xl transition shadow">
                    Apply Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'payment_method', 'month', 'start_date', 'end_date']))
                <a href="{{ route('expenses.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs rounded-xl transition">
                    Reset
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Summary Banner for Filtered Results -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-indigo-50/60 border border-indigo-100 rounded-2xl p-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold text-xs">
                ₹
            </div>
            <div>
                <span class="text-[10px] font-bold text-indigo-900/60 uppercase">Filtered Total</span>
                <p class="font-black text-slate-900 text-lg">₹{{ number_format($filteredTotal, 2) }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold text-xs">
                #
            </div>
            <div>
                <span class="text-[10px] font-bold text-indigo-900/60 uppercase">Entries Found</span>
                <p class="font-black text-slate-900 text-lg">{{ $filteredCount }} transactions</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold text-xs">
                ⌀
            </div>
            <div>
                <span class="text-[10px] font-bold text-indigo-900/60 uppercase">Avg per Transaction</span>
                <p class="font-black text-slate-900 text-lg">₹{{ number_format($averageExpense, 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Expense List Table -->
    @if($expenses->isEmpty())
    <div class="card-box rounded-[2rem] p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        </div>
        <h4 class="text-lg font-bold text-slate-800">No Expenses Found</h4>
        <p class="text-slate-400 text-xs mt-1">Try adjusting your filters or add a new transaction.</p>
        <a href="{{ route('expenses.create') }}" class="inline-block mt-4 px-5 py-2.5 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition">
            + Record New Expense
        </a>
    </div>
    @else
    
    <!-- Desktop Table View -->
    <div class="hidden md:block card-box rounded-[2rem] overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-400 uppercase text-[11px] font-bold tracking-wider">
                    <th class="p-5">Merchant / Paid To</th>
                    <th class="p-5">Category</th>
                    <th class="p-5">Date</th>
                    <th class="p-5">Method</th>
                    <th class="p-5 text-right">Amount</th>
                    <th class="p-5 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-sm">
                @foreach($expenses as $expense)
                <tr class="hover:bg-slate-50/80 transition-colors">
                    <td class="p-5">
                        <div class="font-bold text-slate-900">{{ $expense->paid_to }}</div>
                        @if($expense->note)
                        <div class="text-xs text-slate-400 truncate max-w-xs mt-0.5">{{ $expense->note }}</div>
                        @endif
                    </td>
                    <td class="p-5">
                        <span class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-xs font-bold border border-indigo-100/60">
                            {{ $expense->category->name ?? 'General' }}
                        </span>
                    </td>
                    <td class="p-5 text-slate-600 text-xs font-medium">
                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M, Y') }}
                    </td>
                    <td class="p-5 text-slate-600 text-xs font-medium">
                        <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-semibold">{{ $expense->payment_method }}</span>
                    </td>
                    <td class="p-5 text-right font-black text-rose-600 text-base">
                        -₹{{ number_format($expense->amount, 2) }}
                    </td>
                    <td class="p-5">
                        <div class="flex justify-center gap-2">
                            <a href="{{ route('expenses.edit', $expense->id) }}"
                               class="p-2 text-amber-600 hover:bg-amber-50 rounded-xl transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-xl transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="space-y-3 md:hidden">
        @foreach($expenses as $expense)
        <div class="card-box p-4 rounded-2xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm shrink-0">
                    {{ substr($expense->category->name ?? 'U', 0, 1) }}
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">{{ $expense->paid_to }}</h4>
                    <p class="text-[11px] text-slate-400">
                        {{ \Carbon\Carbon::parse($expense->expense_date)->format('d M') }} • 
                        <span class="text-indigo-600 font-semibold">{{ $expense->category->name ?? 'General' }}</span>
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="font-black text-rose-600 text-sm">-₹{{ number_format($expense->amount, 2) }}</span>
                <div class="flex gap-2 justify-end mt-1">
                    <a href="{{ route('expenses.edit', $expense->id) }}" class="p-1 text-slate-400 hover:text-amber-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>
                    <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" onsubmit="return confirm('Delete this expense?');">
                        @csrf @method('DELETE')
                        <button class="p-1 text-slate-400 hover:text-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

@endsection

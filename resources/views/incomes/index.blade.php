@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-6xl mx-auto pb-12">

    <!-- Top Header & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-black uppercase tracking-wider border border-emerald-200 mb-2">
                💰 Earnings & Inflow
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Income & Earnings</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Track money coming in from sales, salary, freelance, business, and side hustles.</p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto flex-wrap">
            @if(Auth::user()->canExportPdf())
            <button type="button" 
                    @click="$dispatch('open-pdf-modal')" 
                    onclick="window.openExportPdfModal && window.openExportPdfModal()"
                    class="flex-1 sm:flex-initial px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                <span>Export PDF</span>
            </button>
            @endif
            <a href="{{ route('expenses.index') }}" class="flex-1 sm:flex-initial px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl font-bold text-xs sm:text-sm flex items-center justify-center gap-1.5 transition">
                <span>View Expenses</span>
            </a>
            <a href="{{ route('incomes.create') }}" class="flex-1 sm:flex-initial px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/25 active:scale-95 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Add Income</span>
            </a>
        </div>
    </div>

    <!-- 3 Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Total Earned (Filtered View) -->
        <div class="bg-white rounded-3xl border border-emerald-200/90 p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-800">Total Earning</span>
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-base">
                    💰
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">
                +₹{{ number_format($filteredTotal, 2) }}
            </h2>
            <p class="text-[11px] text-slate-400 mt-1">{{ $filteredCount }} {{ Str::plural('earning entry', $filteredCount) }} recorded</p>
        </div>

        <!-- This Month Earning -->
        <div class="bg-white rounded-3xl border border-indigo-100 p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-indigo-700">This Month Earning</span>
                <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-base">
                    📅
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 mt-2">
                +₹{{ number_format($thisMonthTotal, 2) }}
            </h2>
            <p class="text-[11px] text-slate-400 mt-1">Month of {{ now()->format('F Y') }}</p>
        </div>

        <!-- Today's Inflow -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500">Today's Inflow</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-base">
                    ⚡
                </div>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-800 mt-2">
                +₹{{ number_format($todayTotal, 2) }}
            </h2>
            <p class="text-[11px] text-slate-400 mt-1">Logged today</p>
        </div>

    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-sm">
        <form method="GET" action="{{ route('incomes.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            
            <!-- Search -->
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Source, note, ref..." 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition" />
            </div>

            <!-- Category -->
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Category</label>
                <select name="category_id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->icon }} {{ $cat->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <!-- Month -->
            <div>
                <label class="block text-[11px] font-black text-slate-400 uppercase tracking-wider mb-1">Month</label>
                <input type="month" name="month" value="{{ request('month') }}" 
                       class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-800 focus:bg-white focus:ring-2 focus:ring-emerald-500 transition" />
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition shadow-sm">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'category_id', 'month', 'start_date', 'end_date', 'payment_method']))
                <a href="{{ route('incomes.index') }}" class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition">
                    Reset
                </a>
                @endif
            </div>

        </form>
    </div>

    <!-- Income Entries List -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-4">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Earning Records</h2>
                <p class="text-xs text-slate-400">All money received and logged into your account.</p>
            </div>
            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-100">
                {{ $filteredCount }} Entries
            </span>
        </div>

        @if($incomes->isEmpty())
        <div class="text-center py-16 px-4 border-2 border-dashed border-slate-200 rounded-3xl">
            <div class="w-16 h-16 rounded-3xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-3xl mx-auto mb-4">
                💰
            </div>
            <h3 class="text-lg font-black text-slate-800">No Incomes Logged Yet</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                Log earnings when you receive salary, sell items (like old phone, clothes, goods), get freelance payouts, or receive cash to track your total money in!
            </p>
            <div class="mt-6">
                <a href="{{ route('incomes.create') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs shadow-lg shadow-emerald-600/25 active:scale-95 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Add Your First Earning</span>
                </a>
            </div>
        </div>
        @else
        <div class="divide-y divide-slate-100">
            @foreach($incomes as $income)
            <div class="py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 hover:bg-slate-50/70 px-3 rounded-2xl transition group">
                
                <div class="flex items-center gap-3.5 min-w-0">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center text-xl shrink-0 shadow-sm group-hover:scale-105 transition">
                        {{ $income->category_icon }}
                    </div>
                    <div class="min-w-0">
                        <h4 class="font-black text-slate-900 text-sm sm:text-base truncate">{{ $income->source }}</h4>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                            <span class="text-emerald-700 font-bold">{{ $income->category_name }}</span> • 
                            {{ $income->income_date->format('d M, Y') }} • 
                            {{ $income->payment_method }}
                            @if($income->note) • <span class="italic text-slate-500">"{{ $income->note }}"</span>@endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4 self-stretch sm:self-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <span class="font-black text-emerald-600 text-lg sm:text-xl">
                        +₹{{ number_format($income->amount, 2) }}
                    </span>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('incomes.edit', $income->id) }}" class="p-2 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                            ✏️
                        </a>
                        <form method="POST" action="{{ route('incomes.destroy', $income->id) }}" onsubmit="return confirm('Are you sure you want to delete this earning entry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>

            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection

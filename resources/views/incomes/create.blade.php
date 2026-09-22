@extends('layouts.app')

@section('content')

<style>
    :root {
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.16), 0 6px 16px rgba(15,23,42,0.06);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(75px); opacity: .28; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 380px; height: 380px; background: radial-gradient(circle, #a7f3d0, transparent 70%); top: -110px; left: -80px; animation-duration: 23s; }
    .ambient-bg .blob-2 { width: 300px; height: 300px; background: radial-gradient(circle, #bae6fd, transparent 70%); bottom: -90px; right: -70px; animation-duration: 27s; animation-delay: -9s; }
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(22px,-20px) scale(1.05); }
    }

    @keyframes slideUpForm {
        from { opacity: 0; transform: translateY(36px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .form-container { animation: slideUpForm 0.55s cubic-bezier(0.16, 1, 0.3, 1); }

    .custom-input { transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease; }
    .custom-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        outline: none;
        transform: translateY(-1px);
    }

    .btn-emerald { transition: transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s ease; }
    .btn-emerald:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(16,185,129,0.45); }
    .btn-emerald:active { transform: scale(0.97); }

    .quick-chip {
        transition: all 0.2s ease;
    }
    .quick-chip:hover {
        background-color: #d1fae5;
        border-color: #10b981;
        color: #065f46;
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .mobile-card { border-radius: 2.5rem 2.5rem 0 0; margin-top: 1rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ambient-bg .blob, .form-container { animation: none !important; opacity: 1 !important; }
    }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="flex justify-center md:py-10">
    <div class="w-full max-w-xl bg-white p-6 sm:p-8 md:rounded-[2.5rem] form-container mobile-card" style="box-shadow: var(--chaya-lg);">

        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('incomes.index') }}" class="p-2 bg-slate-50 rounded-full text-slate-400 hover:text-emerald-600 min-w-[40px] min-h-[40px] flex items-center justify-center transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">Record Earning / Income</h2>
                    <p class="text-xs font-semibold text-slate-400">Add money received (Sales, Salary, Gigs, Freelance)</p>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-black">
                <span>💰</span> Inflow (+)
            </span>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('incomes.store') }}" class="space-y-5">
            @csrf

            <!-- Big Emerald Amount Box -->
            <div class="bg-gradient-to-br from-emerald-50 via-teal-50/50 to-emerald-50/30 p-6 rounded-3xl border border-emerald-100">
                <label class="block text-xs font-black text-emerald-600 uppercase tracking-widest mb-1">Enter Inflow Amount</label>
                <div class="flex items-center">
                    <span class="text-3xl font-black text-emerald-600 mr-2">+₹</span>
                    <input type="number" name="amount" required step="0.01" min="0.01" max="100000000"
                           value="{{ old('amount') }}"
                           class="bg-transparent w-full text-3xl font-black text-emerald-700 focus:outline-none placeholder-emerald-200"
                           placeholder="0.00" autofocus>
                </div>
            </div>

            <!-- Quick Source Presets -->
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-wider ml-1">Quick Suggestions</label>
                <div class="flex flex-wrap gap-1.5">
                    <button type="button" onclick="setSource('Sold Item / Product', 2)" class="quick-chip text-xs font-bold px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl">📦 Sold an Item</button>
                    <button type="button" onclick="setSource('Monthly Salary', 1)" class="quick-chip text-xs font-bold px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl">💼 Monthly Salary</button>
                    <button type="button" onclick="setSource('Freelance Project Payment', 3)" class="quick-chip text-xs font-bold px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl">💻 Freelance / Gig</button>
                    <button type="button" onclick="setSource('Old Phone Sold', 2)" class="quick-chip text-xs font-bold px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl">📱 Sold Old Phone</button>
                    <button type="button" onclick="setSource('Cashback & Rewards', 7)" class="quick-chip text-xs font-bold px-3 py-1.5 bg-slate-50 border border-slate-200 text-slate-600 rounded-xl">🎁 Cashback / Gift</button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Source / Earned From <span class="text-rose-500">*</span></label>
                    <input type="text" id="income_source_input" name="source" placeholder="e.g. Sold Old Bike, Client X, Salary" required
                           value="{{ old('source') }}"
                           class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input text-slate-800 font-semibold">
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Income Category <span class="text-rose-500">*</span></label>
                    <select id="income_category_select" name="income_category_id" class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input appearance-none text-slate-800 font-semibold cursor-pointer">
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('income_category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->icon ?? '💰' }} {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Received Via</label>
                    <select name="payment_method" class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input text-slate-800 font-semibold">
                        @foreach($paymentMethods as $pm)
                            <option value="{{ $pm }}" {{ old('payment_method') == $pm ? 'selected' : '' }}>{{ $pm }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Date Received</label>
                    <input type="date" name="income_date" value="{{ old('income_date', date('Y-m-d')) }}"
                           class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input text-slate-800 font-semibold">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600 ml-1">Reference / Transaction ID <span class="text-slate-400 font-normal text-xs">(Optional)</span></label>
                <input type="text" name="transaction_id" placeholder="e.g. UPI Ref / Bank UTR / Invoice #"
                       value="{{ old('transaction_id') }}"
                       class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input text-slate-800">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600 ml-1">Note / Details <span class="text-slate-400 font-normal text-xs">(Optional)</span></label>
                <textarea name="note" rows="2" placeholder="e.g. Sold OLX buyer at ₹600 cash, salary bonus included..."
                          class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input text-slate-800">{{ old('note') }}</textarea>
            </div>

            <button type="submit"
                    class="btn-emerald w-full bg-emerald-600 text-white p-5 rounded-2xl font-bold text-lg shadow-xl shadow-emerald-200 hover:bg-emerald-700 flex justify-center items-center gap-2 min-h-[56px] transition cursor-pointer">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Record Income Inflow
            </button>
        </form>
    </div>
</div>

<script>
    function setSource(val, categoryId) {
        document.getElementById('income_source_input').value = val;
        if (categoryId) {
            const select = document.getElementById('income_category_select');
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value == categoryId) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }
        document.getElementById('income_source_input').focus();
    }
</script>

@endsection

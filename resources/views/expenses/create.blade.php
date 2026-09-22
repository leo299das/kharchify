@extends('layouts.app')

@section('content')

<style>
    :root {
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.16), 0 6px 16px rgba(15,23,42,0.06);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(75px); opacity: .28; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 380px; height: 380px; background: radial-gradient(circle, #c7d2fe, transparent 70%); top: -110px; left: -80px; animation-duration: 23s; }
    .ambient-bg .blob-2 { width: 300px; height: 300px; background: radial-gradient(circle, #a7f3d0, transparent 70%); bottom: -90px; right: -70px; animation-duration: 27s; animation-delay: -9s; }
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
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        outline: none;
        transform: translateY(-1px);
    }

    .btn-primary { transition: transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s ease; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(79,70,229,0.45); }
    .btn-primary:active { transform: scale(0.97); }

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

        <div class="flex items-center gap-4 mb-8">
            <a href="/expenses" class="p-2 bg-slate-50 rounded-full text-slate-400 hover:text-indigo-600 min-w-[40px] min-h-[40px] flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Add Expense</h2>
        </div>

        <form method="POST" action="/expenses" class="space-y-5">
            @csrf

            <div class="bg-indigo-50/50 p-6 rounded-3xl mb-6">
                <label class="block text-xs font-bold text-indigo-400 uppercase tracking-widest mb-1">Enter Amount</label>
                <div class="flex items-center">
                    <span class="text-3xl font-black text-indigo-600 mr-2">₹</span>
                    <input type="number" name="amount" required step="0.01"
                           class="bg-transparent w-full text-3xl font-black text-indigo-600 focus:outline-none placeholder-indigo-200"
                           placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Paid To</label>
                    <input type="text" name="paid_to" placeholder="e.g. Amazon, John" required
                           class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input">
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Category</label>
                    <select name="category_id" class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input appearance-none">
                        @foreach($categories as $category)
                            <option value="{{$category->id}}">{{$category->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Payment Method</label>
                    <select name="payment_method" class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input">
                        <option>UPI</option>
                        <option>Cash</option>
                        <option>Credit Card</option>
                        <option>Debit Card</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Date & Time</label>
                    <input type="datetime-local" name="expense_date" value="{{ date('Y-m-d\TH:i') }}"
                           class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600 ml-1">Transaction ID (Optional)</label>
                <input type="text" name="transaction_id" placeholder="Ref No. / Txn ID"
                       class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600 ml-1">Note</label>
                <textarea name="note" rows="2" placeholder="Kya kharcha kiya?"
                          class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl custom-input"></textarea>
            </div>

            <button type="submit"
                    class="btn-primary w-full bg-indigo-600 text-white p-5 rounded-2xl font-bold text-lg shadow-xl shadow-indigo-100 hover:bg-indigo-700 flex justify-center items-center gap-2 min-h-[56px]">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Save Transaction
            </button>
        </form>
    </div>
</div>

@endsection

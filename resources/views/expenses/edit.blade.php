@extends('layouts.app')

@section('content')

<style>
    :root {
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.16), 0 6px 16px rgba(15,23,42,0.06);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(75px); opacity: .28; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 360px; height: 360px; background: radial-gradient(circle, #fde68a, transparent 70%); top: -100px; right: -70px; animation-duration: 22s; }
    .ambient-bg .blob-2 { width: 300px; height: 300px; background: radial-gradient(circle, #c7d2fe, transparent 70%); bottom: -90px; left: -70px; animation-duration: 26s; animation-delay: -10s; }
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(-18px,20px) scale(1.05); }
    }

    @keyframes slideFromLeft {
        from { opacity: 0; transform: translateX(-30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    .edit-container { animation: slideFromLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1); }

    .edit-input { transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease; }
    .edit-input:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        outline: none;
        transform: translateY(-1px);
    }

    .btn-update { transition: transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s ease; }
    .btn-update:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(245,158,11,0.4); }
    .btn-update:active { transform: scale(0.97); }

    @media (prefers-reduced-motion: reduce) {
        .ambient-bg .blob, .edit-container { animation: none !important; opacity: 1 !important; }
    }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="flex justify-center md:py-10 px-4">
    <div class="w-full max-w-xl bg-white p-6 sm:p-8 md:rounded-[2.5rem] edit-container border-t-8 border-amber-400" style="box-shadow: var(--chaya-lg);">

        <div class="flex justify-between items-center mb-8 flex-wrap gap-3">
            <div class="flex items-center gap-4">
                <a href="/expenses" class="p-2 bg-slate-50 rounded-full text-slate-400 hover:text-amber-500 transition min-w-[40px] min-h-[40px] flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7"></path></svg>
                </a>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Expense</h2>
            </div>
            <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-3 py-1 rounded-full uppercase tracking-tighter">Modification Mode</span>
        </div>

        <form method="POST" action="/expenses/{{$expense->id}}" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="bg-amber-50/50 p-6 rounded-3xl mb-6 border border-amber-100">
                <label class="block text-xs font-bold text-amber-500 uppercase tracking-widest mb-1">Update Amount</label>
                <div class="flex items-center">
                    <span class="text-3xl font-black text-amber-600 mr-2">₹</span>
                    <input type="number" name="amount" value="{{$expense->amount}}" required step="0.01"
                           class="bg-transparent w-full text-3xl font-black text-amber-600 focus:outline-none"
                           placeholder="0.00">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Paid To</label>
                    <input type="text" name="paid_to" value="{{$expense->paid_to}}" required
                           class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input">
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Category</label>
                    <div class="relative">
                        <select name="category_id" class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input appearance-none">
                            @foreach($categories as $category)
                                <option value="{{$category->id}}" @if($expense->category_id == $category->id) selected @endif>
                                    {{$category->name}}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Payment Method</label>
                    <select name="payment_method" class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input">
                        <option {{$expense->payment_method=='UPI'?'selected':''}}>UPI</option>
                        <option {{$expense->payment_method=='Cash'?'selected':''}}>Cash</option>
                        <option {{$expense->payment_method=='Credit Card'?'selected':''}}>Credit Card</option>
                        <option {{$expense->payment_method=='Debit Card'?'selected':''}}>Debit Card</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-bold text-slate-600 ml-1">Date & Time</label>
                    <input type="datetime-local" name="expense_date"
                           value="{{ date('Y-m-d\TH:i', strtotime($expense->expense_date)) }}"
                           class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input">
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600 ml-1">Transaction ID</label>
                <input type="text" name="transaction_id" value="{{$expense->transaction_id}}"
                       class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600 ml-1">Note / Description</label>
                <textarea name="note" rows="3"
                          class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input">{{$expense->note}}</textarea>
            </div>

            <div class="flex flex-col md:flex-row gap-3 pt-4">
                <button type="submit"
                        class="btn-update flex-1 bg-amber-500 text-white p-5 rounded-2xl font-bold text-lg flex justify-center items-center gap-2 min-h-[56px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Update Changes
                </button>
                <a href="/expenses"
                   class="flex-1 bg-slate-100 text-slate-600 p-5 rounded-2xl font-bold text-lg text-center hover:bg-slate-200 transition active:scale-95 min-h-[56px] flex items-center justify-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

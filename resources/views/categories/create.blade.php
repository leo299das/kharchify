@extends('layouts.app')

@section('content')

<style>
    :root {
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.16), 0 6px 16px rgba(15,23,42,0.06);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .3; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 340px; height: 340px; background: radial-gradient(circle, #a7f3d0, transparent 70%); top: -90px; left: -70px; animation-duration: 21s; }
    .ambient-bg .blob-2 { width: 300px; height: 300px; background: radial-gradient(circle, #c7d2fe, transparent 70%); bottom: -80px; right: -60px; animation-duration: 25s; animation-delay: -7s; }
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(22px,-22px) scale(1.06); }
    }

    @keyframes popIn {
        0% { opacity: 0; transform: scale(0.85) translateY(12px); }
        70% { transform: scale(1.03); }
        100% { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-pop { animation: popIn 0.55s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

    .category-input { transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease; }
    .category-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
        outline: none;
        transform: translateY(-1px);
    }

    .btn-primary { transition: transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s ease; }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(16,185,129,0.45); }
    .btn-primary:active { transform: scale(0.97); }

    @media (prefers-reduced-motion: reduce) {
        .ambient-bg .blob, .animate-pop { animation: none !important; opacity: 1 !important; }
    }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="flex items-center justify-center min-h-[70vh] px-4">
    <div class="w-full max-w-md bg-white p-7 sm:p-8 rounded-[2.5rem] animate-pop border-b-8 border-emerald-500" style="box-shadow: var(--chaya-lg);">

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">New Category</h2>
            <p class="text-slate-400 text-sm mt-1 font-medium">Kharchon ko group karne ke liye naam dein</p>
        </div>

        <form method="POST" action="/categories" class="space-y-6">
            @csrf

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">Category Name</label>
                <input type="text"
                       name="name"
                       required
                       placeholder="e.g. Food, Gaming, Server Bills"
                       class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl category-input text-slate-700 font-semibold">
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit"
                        class="btn-primary w-full bg-emerald-500 text-white p-5 rounded-2xl font-bold text-lg shadow-xl shadow-emerald-100 hover:bg-emerald-600 flex justify-center items-center gap-2 min-h-[56px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Create Now
                </button>

                <a href="/categories"
                   class="w-full text-slate-400 font-bold text-sm py-2 hover:text-slate-600 transition text-center uppercase tracking-widest">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

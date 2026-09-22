@extends('layouts.app')

@section('content')

<style>
    :root {
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.16), 0 6px 16px rgba(15,23,42,0.06);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .3; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 340px; height: 340px; background: radial-gradient(circle, #fde68a, transparent 70%); top: -90px; right: -70px; animation-duration: 23s; }
    .ambient-bg .blob-2 { width: 280px; height: 280px; background: radial-gradient(circle, #c7d2fe, transparent 70%); bottom: -80px; left: -60px; animation-duration: 27s; animation-delay: -9s; }
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(-20px,20px) scale(1.05); }
    }

    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.93) translateY(10px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .animate-fade { animation: fadeInScale 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

    .edit-input { transition: border-color .25s ease, box-shadow .25s ease, transform .25s ease; }
    .edit-input:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        outline: none;
        transform: translateY(-1px);
    }

    .btn-update { transition: transform .25s cubic-bezier(.16,1,.3,1), box-shadow .25s ease; }
    .btn-update:hover { transform: translateY(-2px); box-shadow: 0 16px 32px -10px rgba(245,158,11,0.45); }
    .btn-update:active { transform: scale(0.97); }

    @media (prefers-reduced-motion: reduce) {
        .ambient-bg .blob, .animate-fade { animation: none !important; opacity: 1 !important; }
    }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="flex items-center justify-center min-h-[70vh] px-4">
    <div class="w-full max-w-md bg-white p-7 sm:p-8 rounded-[2.5rem] animate-fade border-b-8 border-amber-400" style="box-shadow: var(--chaya-lg);">

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">Edit Category</h2>
            <p class="text-slate-400 text-sm mt-1 font-medium italic">"{{ $category->name }}" ko update karein</p>
        </div>

        <form method="POST" action="/categories/{{$category->id}}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="space-y-2">
                <label class="text-xs font-bold text-slate-500 uppercase tracking-widest ml-1">New Category Name</label>
                <input type="text"
                       name="name"
                       required
                       value="{{ $category->name }}"
                       class="w-full bg-slate-50 border border-slate-100 p-4 rounded-2xl edit-input text-slate-700 font-semibold">
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit"
                        class="btn-update w-full bg-amber-500 text-white p-5 rounded-2xl font-bold text-lg shadow-xl shadow-amber-100 hover:bg-amber-600 flex justify-center items-center gap-2 min-h-[56px]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Update Category
                </button>

                <a href="/categories"
                   class="w-full text-slate-400 font-bold text-sm py-2 hover:text-slate-600 transition text-center uppercase tracking-widest">
                    Go Back
                </a>
            </div>
        </form>
    </div>
</div>

@endsection

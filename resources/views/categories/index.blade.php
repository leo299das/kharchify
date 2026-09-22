@extends('layouts.app')

@section('content')

<style>
    :root {
        --chaya-sm: 0 2px 8px -2px rgba(15,23,42,0.06), 0 1px 2px rgba(15,23,42,0.04);
        --chaya-md: 0 10px 28px -8px rgba(15,23,42,0.10), 0 2px 6px rgba(15,23,42,0.05);
        --chaya-lg: 0 24px 56px -12px rgba(15,23,42,0.16), 0 6px 16px rgba(15,23,42,0.06);
    }

    .ambient-bg { position: fixed; inset: 0; overflow: hidden; z-index: -1; pointer-events: none; }
    .ambient-bg .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .28; animation: floatBlob ease-in-out infinite; }
    .ambient-bg .blob-1 { width: 360px; height: 360px; background: radial-gradient(circle, #a7f3d0, transparent 70%); top: -100px; right: -60px; animation-duration: 24s; }
    .ambient-bg .blob-2 { width: 300px; height: 300px; background: radial-gradient(circle, #c7d2fe, transparent 70%); bottom: -90px; left: -60px; animation-duration: 20s; animation-delay: -8s; }
    @keyframes floatBlob {
        0%, 100% { transform: translate(0,0) scale(1); }
        50% { transform: translate(20px,-24px) scale(1.05); }
    }

    @keyframes zoomInFade {
        from { opacity: 0; transform: scale(0.92) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .category-card {
        animation: zoomInFade 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        background: #fff;
        box-shadow: var(--chaya-md);
        border: 1px solid rgba(148,163,184,0.10);
        transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s ease;
    }
    .category-card:hover { transform: translateY(-5px); box-shadow: var(--chaya-lg); }
    .category-card:active { transform: scale(0.97); }

    @for ($i = 1; $i <= 20; $i++)
    .category-card:nth-child({{ $i }}) { animation-delay: {{ $i * 0.05 }}s; }
    @endfor

    @media (prefers-reduced-motion: reduce) {
        .ambient-bg .blob, .category-card { animation: none !important; opacity: 1 !important; }
    }
</style>

<div class="ambient-bg" aria-hidden="true">
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
</div>

<div class="px-4 pb-24 md:pb-10 max-w-6xl mx-auto">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight">Categories</h2>
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mt-1">Manage Groups</p>
        </div>
        <a href="/categories/create"
           class="bg-emerald-500 text-white p-3 rounded-2xl shadow-lg shadow-emerald-100 hover:rotate-3 transition active:scale-90 flex items-center gap-2 min-w-[44px] min-h-[44px] justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span class="hidden md:inline font-bold">New Category</span>
        </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
        @foreach($categories as $category)
        <div class="category-card p-5 sm:p-6 rounded-[2rem] group relative overflow-hidden">
            <div class="absolute -top-4 -right-4 w-16 h-16 bg-slate-50 rounded-full group-hover:bg-emerald-50 transition-colors duration-300"></div>

            <div class="relative z-10 flex flex-col items-center text-center">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-slate-500 group-hover:from-emerald-500 group-hover:to-teal-600 group-hover:text-white transition-all duration-300 shadow-inner">
                    <span class="text-xl font-black uppercase">{{ substr($category->name, 0, 1) }}</span>
                </div>

                <h3 class="mt-4 font-bold text-slate-800 group-hover:text-emerald-600 transition-colors">{{ $category->name }}</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 tracking-tighter">ID: #{{ $category->id }}</p>

                <div class="flex gap-2 mt-5 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity translate-y-0 md:translate-y-2 md:group-hover:translate-y-0 duration-300">
                    <a href="/categories/{{$category->id}}/edit"
                       class="p-2.5 bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-100 transition min-w-[40px] min-h-[40px] flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    </a>

                    <form action="/categories/{{$category->id}}" method="POST" onsubmit="return confirm('Category delete karne se uske expenses par asar pad sakta hai. Confirm?');">
                        @csrf @method('DELETE')
                        <button class="p-2.5 bg-red-50 text-red-500 rounded-xl hover:bg-red-100 transition min-w-[40px] min-h-[40px] flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        <a href="/categories/create" class="category-card border-2 border-dashed border-slate-200 !shadow-none !bg-transparent rounded-[2rem] flex flex-col items-center justify-center p-6 text-slate-400 hover:border-emerald-400 hover:text-emerald-500 transition-colors group">
            <div class="w-12 h-12 rounded-full border-2 border-dashed border-slate-200 flex items-center justify-center group-hover:border-emerald-400 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            </div>
            <span class="mt-2 font-bold text-xs uppercase tracking-widest">Add New</span>
        </a>
    </div>

</div>

@endsection

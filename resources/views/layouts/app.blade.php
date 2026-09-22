<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kharchify - Smart Expense Manager</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Inter', sans-serif; }

        @keyframes slideUp {
            from { transform: translateY(16px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-main { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
        
        .glass-nav {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }

        #profileMenu {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: top right;
            pointer-events: none;
        }
        #profileMenu.active {
            display: block !important;
            transform: scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>

<body class="bg-slate-50 text-slate-900 pb-24 md:pb-0 min-h-screen flex flex-col">

    <!-- Desktop Navigation -->
    <nav class="hidden md:block bg-white/85 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-6 py-3.5 flex justify-between items-center">
            
            <!-- Logo -->
            <a href="{{ route('dashboard') }}" class="flex items-center group transition transform hover:scale-[1.02]">
                <x-application-logo />
            </a>

            <!-- Nav Links -->
            <div class="flex items-center space-x-7 text-sm font-bold">
                <a href="{{ route('dashboard') }}" class="{{ Request::is('dashboard*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }} transition">
                    Dashboard
                </a>
                <a href="{{ route('categories.index') }}" class="{{ Request::is('categories*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }} transition">
                    Categories
                </a>
                <a href="{{ route('expenses.index') }}" class="{{ Request::is('expenses*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }} transition">
                    Expenses
                </a>
                <a href="{{ route('plans.show') }}" class="{{ Request::is('choose-plan*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }} transition flex items-center gap-1">
                    <span>Plans</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-black">₹49+</span>
                </a>
            </div>

            <!-- Profile & Active Plan Badge -->
            <div class="flex items-center gap-3">
                @auth
                @php
                    $planKey = Auth::user()->plan ?? 'basic';
                @endphp

                @if(Auth::user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-slate-900 text-cyan-400 hover:bg-slate-800 border border-slate-700 transition flex items-center gap-1.5 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                    <span>Admin Panel</span>
                </a>
                @endif

                <a href="{{ route('plans.show') }}" class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-black transition border
                    {{ $planKey === 'pro' ? 'bg-amber-100 text-amber-900 border-amber-300' : ($planKey === 'medium' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : ($planKey === 'free' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-700 border-slate-200')) }}">
                    @if($planKey === 'pro') 👑 Full Pro (₹150)
                    @elseif($planKey === 'medium') 🔥 Medium (₹94)
                    @elseif($planKey === 'free') ✨ Free Plan (₹0)
                    @else ⚡ Basic (₹49)
                    @endif
                </a>

                <div class="relative group">
                    <button onclick="toggleMenu()" class="flex items-center space-x-2.5 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-2xl transition">
                        <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-[11px] text-white font-black shadow-inner">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ Auth::user()->name ?? 'User' }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    
                    <div id="profileMenu" class="hidden opacity-0 scale-95 absolute right-0 mt-3 w-56 bg-white border border-slate-200 rounded-[1.5rem] shadow-2xl py-2 z-50">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Signed in as</p>
                            <p class="text-xs font-bold text-slate-800 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        @if(Auth::user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-xs font-black text-cyan-600 bg-cyan-50/50 hover:bg-cyan-100/60 transition">
                            ⚡ Admin Control Panel
                        </a>
                        <hr class="my-1 border-slate-100">
                        @endif
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
                            👤 My Profile & Settings
                        </a>
                        <a href="{{ route('plans.show') }}" class="block px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
                            💳 Manage Plan / Upgrade
                        </a>
                        <hr class="my-1 border-slate-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-xs font-black text-rose-600 hover:bg-rose-50 transition">
                                🚪 Log Out
                            </button>
                        </form>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Mobile Header -->
    <header class="md:hidden bg-white/90 backdrop-blur-md px-5 py-3 flex justify-between items-center sticky top-0 z-40 border-b border-slate-200">
        <a href="{{ route('dashboard') }}" class="flex items-center">
            <x-application-logo class="scale-90 origin-left" />
        </a>
        <div class="flex items-center gap-2">
            @if(Auth::check() && Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-slate-900 text-cyan-400 border border-slate-700">
                Admin
            </a>
            @endif
            <a href="{{ route('plans.show') }}" class="px-2.5 py-1 rounded-lg text-[10px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200">
                Plans
            </a>
            <button onclick="toggleMenu()" class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 active:scale-95 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            </button>
        </div>
    </header>

    <!-- Success Toast Notification -->
    @if(session('success'))
    <div id="toast" class="fixed top-16 left-1/2 -translate-x-1/2 z-[100] transform transition-all duration-500 -translate-y-20 opacity-0 max-w-md w-11/12">
        <div class="bg-slate-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700">
            <div class="bg-emerald-500 p-1.5 rounded-full shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <span class="font-bold text-xs leading-snug">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Warning / Error Toast Notification -->
    @if(session('warning'))
    <div id="toast" class="fixed top-16 left-1/2 -translate-x-1/2 z-[100] transform transition-all duration-500 -translate-y-20 opacity-0 max-w-md w-11/12">
        <div class="bg-amber-900 text-white px-5 py-3.5 rounded-2xl shadow-2xl flex items-center gap-3 border border-amber-700">
            <div class="bg-amber-500 p-1.5 rounded-full shrink-0 text-amber-950 font-black text-xs">
                !
            </div>
            <span class="font-bold text-xs leading-snug">{{ session('warning') }}</span>
        </div>
    </div>
    @endif

    <!-- Main Body Content -->
    <main class="max-w-6xl mx-auto p-4 md:pt-8 animate-main flex-1 w-full">
        @yield('content')
    </main>

    <!-- Mobile Bottom Glass Bar -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 glass-nav z-50 px-6 py-3">
        <div class="flex justify-between items-center">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-1 {{ Request::is('dashboard*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[9px] font-black uppercase">Home</span>
            </a>

            <a href="{{ route('categories.index') }}" class="flex flex-col items-center gap-1 {{ Request::is('categories*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span class="text-[9px] font-black uppercase">Cats</span>
            </a>

            <a href="{{ route('expenses.create') }}" class="relative -mt-8 bg-indigo-600 p-3.5 rounded-2xl shadow-xl shadow-indigo-300 text-white transform transition active:scale-90 ring-4 ring-slate-50">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            </a>

            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center gap-1 {{ Request::is('expenses*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="text-[9px] font-black uppercase">Expenses</span>
            </a>

            <a href="{{ route('plans.show') }}" class="flex flex-col items-center gap-1 {{ Request::is('choose-plan*') ? 'text-indigo-600' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                <span class="text-[9px] font-black uppercase">Plans</span>
            </a>
        </div>
    </div>

    <script>
        function toggleMenu() {
            const menu = document.getElementById("profileMenu");
            if (!menu) return;
            menu.classList.toggle("hidden");
            setTimeout(() => {
                menu.classList.toggle("active");
            }, 10);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('toast');
            if(toast) {
                setTimeout(() => {
                    toast.classList.remove('-translate-y-20', 'opacity-0');
                    toast.classList.add('translate-y-0', 'opacity-100');
                }, 100);
                setTimeout(() => {
                    toast.classList.add('-translate-y-20', 'opacity-0');
                }, 4000);
            }
        });

        window.addEventListener('click', (e) => {
            if (!e.target.closest('.relative') && !e.target.closest('header')) {
                const menu = document.getElementById("profileMenu");
                if (menu && menu.classList.contains("active")) {
                    menu.classList.remove("active");
                    setTimeout(() => menu.classList.add("hidden"), 180);
                }
            }
        });
    </script>
</body>
</html>
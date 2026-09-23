<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>Kharchify - Smart Expense Manager</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <!-- PWA & Mobile Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kharchify">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            touch-action: manipulation;
            -webkit-text-size-adjust: 100%;
        }
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
            pointer-events: auto !important;
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
                <a href="{{ route('incomes.index') }}" class="{{ Request::is('incomes*') ? 'text-emerald-600 font-black' : 'text-slate-500 hover:text-emerald-600' }} transition flex items-center gap-1.5">
                    <span>Earnings</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black">💰 Inflow</span>
                </a>
                <a href="{{ route('splits.index') }}" class="{{ Request::is('splits*') ? 'text-indigo-600' : 'text-slate-500 hover:text-indigo-600' }} transition flex items-center gap-1.5">
                    <span>Splitwise</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-indigo-100 text-indigo-900 text-[10px] font-black">👥 Split</span>
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

                <div class="relative">
                    <button onclick="toggleMenu(event)" class="flex items-center space-x-2.5 bg-slate-100 hover:bg-slate-200 px-3.5 py-2 rounded-2xl transition">
                        <div class="w-7 h-7 bg-indigo-600 rounded-full flex items-center justify-center text-[11px] text-white font-black shadow-inner">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="text-xs font-bold text-slate-800">{{ Auth::user()->name ?? 'User' }}</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Mobile Header -->
    <header class="md:hidden bg-white/95 backdrop-blur-md px-4 py-3 flex justify-between items-center sticky top-0 z-40 border-b border-slate-200/90 shadow-sm">
        <a href="{{ route('dashboard') }}" class="flex items-center shrink-0">
            <x-application-logo size="small" />
        </a>
        
        <div class="flex items-center gap-2">
            @auth
            @php
                $mobilePlan = Auth::user()->plan ?? 'free';
            @endphp
            <a href="{{ route('plans.show') }}" class="px-2.5 py-1 rounded-full text-[10px] font-black border transition
                {{ $mobilePlan === 'pro' ? 'bg-amber-100 text-amber-900 border-amber-300' : ($mobilePlan === 'medium' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : ($mobilePlan === 'free' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200')) }}">
                {{ strtoupper($mobilePlan) }}
            </a>
            @endauth

            <a href="{{ route('incomes.index') }}" class="px-2.5 py-1 rounded-xl text-xs font-black {{ Request::is('incomes*') ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}" title="Earnings Inflow">
                💰 +₹
            </a>

            <!-- 3-Lines Hamburger Menu Button (Mobile All Features Dropdown) -->
            <button type="button" onclick="toggleMobileMenu(event)" id="mobileNavToggleBtn" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-slate-200 active:bg-slate-300 text-slate-800 flex items-center justify-center border border-slate-200 shadow-sm transition active:scale-95 cursor-pointer" aria-label="Open All Features Menu" title="Menu - All Features">
                <svg id="mobileMenuOpenIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <svg id="mobileMenuCloseIcon" class="w-5 h-5 hidden text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </header>

    <!-- Full Mobile Navigation Dropdown Menu (Opened via 3-Lines Hamburger) -->
    <div id="mobileNavDropdown" class="hidden md:hidden fixed top-14 left-0 right-0 max-h-[85vh] overflow-y-auto bg-white/98 backdrop-blur-xl border-b border-slate-200 shadow-2xl p-4 sm:p-6 space-y-4 z-50 animate-main">
        @auth
        <!-- User Info Card -->
        <div class="p-4 rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white flex items-center justify-between gap-3 shadow-md">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white font-black text-sm flex items-center justify-center shrink-0 shadow-inner">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-black truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] text-slate-300 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <a href="{{ route('plans.show') }}" class="px-2.5 py-1 rounded-xl text-[10px] font-black bg-amber-400 text-slate-950 shrink-0">
                {{ strtoupper(Auth::user()->plan ?? 'FREE') }}
            </a>
        </div>
        @endauth

        <!-- Main Features Navigation List -->
        <div class="space-y-1.5">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 px-3 pt-1">Core Features</p>
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center justify-between p-3 rounded-2xl transition font-bold text-xs {{ Request::is('dashboard*') ? 'bg-indigo-50 text-indigo-700 font-black' : 'text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">📊</span>
                    <span>Dashboard & Overview</span>
                </div>
                <span class="text-slate-400">→</span>
            </a>

            <!-- Categories -->
            <a href="{{ route('categories.index') }}" class="flex items-center justify-between p-3 rounded-2xl transition font-bold text-xs {{ Request::is('categories*') ? 'bg-indigo-50 text-indigo-700 font-black' : 'text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">🏷️</span>
                    <div>
                        <span class="block">Categories</span>
                        <span class="text-[10px] text-slate-400 font-normal">Organize and manage spend categories</span>
                    </div>
                </div>
                <span class="text-[10px] font-black bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">Manage</span>
            </a>

            <!-- Expenses -->
            <a href="{{ route('expenses.index') }}" class="flex items-center justify-between p-3 rounded-2xl transition font-bold text-xs {{ Request::is('expenses*') ? 'bg-indigo-50 text-indigo-700 font-black' : 'text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">💸</span>
                    <div>
                        <span class="block">Expenses (-₹)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Track outgoing spending & bills</span>
                    </div>
                </div>
                <span class="text-slate-400">→</span>
            </a>

            <!-- Earnings / Income -->
            <a href="{{ route('incomes.index') }}" class="flex items-center justify-between p-3 rounded-2xl transition font-bold text-xs {{ Request::is('incomes*') ? 'bg-emerald-50 text-emerald-800 font-black' : 'text-slate-700 hover:bg-emerald-50' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">💰</span>
                    <div>
                        <span class="block text-emerald-700 font-black">Earnings & Inflow (+₹)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Track sales, salary & money coming in</span>
                    </div>
                </div>
                <span class="text-[10px] font-black bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-full">+₹ Inflow</span>
            </a>

            <!-- Splitwise -->
            <a href="{{ route('splits.index') }}" class="flex items-center justify-between p-3 rounded-2xl transition font-bold text-xs {{ Request::is('splits*') ? 'bg-indigo-50 text-indigo-700 font-black' : 'text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">👥</span>
                    <div>
                        <span class="block">Splitwise (Split & Settle)</span>
                        <span class="text-[10px] text-slate-400 font-normal">Roommates, trips, dinner splitting</span>
                    </div>
                </div>
                <span class="text-[10px] font-black bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">Split</span>
            </a>

            <!-- Plans -->
            <a href="{{ route('plans.show') }}" class="flex items-center justify-between p-3 rounded-2xl transition font-bold text-xs {{ Request::is('choose-plan*') ? 'bg-amber-50 text-amber-900 font-black' : 'text-slate-700 hover:bg-slate-50' }}">
                <div class="flex items-center gap-3">
                    <span class="text-lg">⚡</span>
                    <div>
                        <span class="block">Pricing Plans & Features</span>
                        <span class="text-[10px] text-slate-400 font-normal">Upgrade for AI analytics & PDF statements</span>
                    </div>
                </div>
                <span class="text-[10px] font-black bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full">₹49+</span>
            </a>
        </div>

        <!-- Account & Support Section -->
        <div class="space-y-1.5 pt-2 border-t border-slate-100">
            <p class="text-[10px] font-black uppercase tracking-wider text-slate-400 px-3">Account & Support</p>
            
            @auth
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-2xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                <span>👤</span>
                <span>My Profile & Account Settings</span>
            </a>

            @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 p-3 rounded-2xl text-xs font-black text-cyan-600 bg-cyan-50/60 hover:bg-cyan-100 transition">
                <span>⚡</span>
                <span>Admin Control Panel</span>
            </a>
            @endif
            @endauth

            <a href="{{ asset('downloads/Kharchify.apk') }}" download="Kharchify.apk" class="flex items-center gap-3 p-3 rounded-2xl text-xs font-black text-emerald-700 bg-emerald-50 hover:bg-emerald-100 transition border border-emerald-200">
                <span class="text-lg">🤖</span>
                <div>
                    <span class="block">Download Android APK (.apk)</span>
                    <span class="text-[10px] text-emerald-600 font-normal">Install app directly on phone</span>
                </div>
            </a>

            <a href="{{ route('contact') }}" class="flex items-center gap-3 p-3 rounded-2xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition">
                <span>💬</span>
                <span>Help & Contact Support</span>
            </a>

            <div class="grid grid-cols-2 gap-2 px-1 pt-1">
                <a href="{{ route('terms') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-[11px] font-bold text-slate-600 text-center transition">
                    📜 Terms & Conditions
                </a>
                <a href="{{ route('privacy') }}" class="p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-[11px] font-bold text-slate-600 text-center transition">
                    🔒 Privacy Policy
                </a>
            </div>
        </div>

        @auth
        <!-- Logout Button -->
        <div class="pt-2 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-3 px-4 rounded-2xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-black text-xs transition flex items-center justify-center gap-2">
                    <span>🚪 Log Out</span>
                </button>
            </form>
        </div>
        @endauth
    </div>

    <!-- Unified Floating Profile Dropdown (Desktop) -->
    @auth
    <div id="profileMenu" class="hidden fixed top-14 right-3 sm:right-6 md:right-10 w-64 bg-white border border-slate-200 rounded-3xl shadow-2xl py-2 z-[100] transition-all duration-200">
        <div class="px-4 py-3 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center shadow-inner shrink-0">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-black text-slate-900 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="mt-2">
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black 
                    {{ Auth::user()->plan === 'pro' ? 'bg-amber-100 text-amber-900' : (Auth::user()->plan === 'medium' ? 'bg-emerald-100 text-emerald-900' : (Auth::user()->plan === 'free' ? 'bg-emerald-50 text-emerald-800' : 'bg-indigo-50 text-indigo-700')) }}">
                    Plan: {{ strtoupper(Auth::user()->plan ?? 'FREE') }}
                </span>
            </div>
        </div>

        @if(Auth::user()->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-cyan-600 bg-cyan-50/60 hover:bg-cyan-100 transition">
            <span>⚡ Admin Control Panel</span>
        </a>
        <hr class="my-1 border-slate-100">
        @endif

        <a href="{{ route('categories.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>🏷️ Categories</span>
        </a>
        <a href="{{ route('incomes.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-emerald-700 hover:bg-emerald-50 transition">
            <span>💰 Earnings & Incomes (+₹)</span>
        </a>
        <a href="{{ route('expenses.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>💸 My Expenses (-₹)</span>
        </a>
        <a href="{{ route('splits.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>👥 Splitwise & Group Bills</span>
        </a>
        <a href="{{ asset('downloads/Kharchify.apk') }}" download="Kharchify.apk" class="flex items-center gap-2 px-4 py-2.5 text-xs font-black text-emerald-700 hover:bg-emerald-50 transition">
            <span>🤖 Download Android APK</span>
        </a>
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>👤 My Profile & Settings</span>
        </a>
        <a href="{{ route('plans.show') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>💳 Manage Plan / Upgrade</span>
        </a>
        <a href="{{ route('contact') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>💬 Help & Contact Support</span>
        </a>
        <a href="{{ route('terms') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 transition">
            <span>📜 Terms & Privacy</span>
        </a>

        <hr class="my-1 border-slate-100">

        <!-- LOG OUT -->
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2.5 text-xs font-black text-rose-600 hover:bg-rose-50 transition">
                <span>🚪 Log Out</span>
            </button>
        </form>
    </div>
    @endauth

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
    <div class="md:hidden fixed bottom-0 left-0 right-0 glass-nav z-50 px-3 py-2">
        <div class="flex justify-around items-center">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-0.5 {{ Request::is('dashboard*') ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[8px] uppercase">Home</span>
            </a>

            <a href="{{ route('expenses.index') }}" class="flex flex-col items-center gap-0.5 {{ Request::is('expenses*') ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span class="text-[8px] uppercase">Expense</span>
            </a>

            <!-- Quick Add Center Button -->
            <a href="{{ route('expenses.create') }}" class="relative -mt-5 bg-indigo-600 p-3 rounded-2xl shadow-xl shadow-indigo-300 text-white transform transition active:scale-90 ring-4 ring-slate-50" title="Add Expense">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            </a>

            <a href="{{ route('categories.index') }}" class="flex flex-col items-center gap-0.5 {{ Request::is('categories*') ? 'text-indigo-600 font-bold' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                <span class="text-[8px] uppercase">Categories</span>
            </a>

            <a href="{{ route('incomes.index') }}" class="flex flex-col items-center gap-0.5 {{ Request::is('incomes*') ? 'text-emerald-600 font-black' : 'text-slate-400' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[8px] uppercase">Earnings</span>
            </a>
        </div>
    </div>

    <script>
        function toggleMobileMenu(e) {
            if (e) {
                e.stopPropagation();
            }
            const dropdown = document.getElementById("mobileNavDropdown");
            const openIcon = document.getElementById("mobileMenuOpenIcon");
            const closeIcon = document.getElementById("mobileMenuCloseIcon");
            if (!dropdown) return;

            dropdown.classList.toggle("hidden");
            if (dropdown.classList.contains("hidden")) {
                if (openIcon) openIcon.classList.remove("hidden");
                if (closeIcon) closeIcon.classList.add("hidden");
            } else {
                if (openIcon) openIcon.classList.add("hidden");
                if (closeIcon) closeIcon.classList.remove("hidden");
                // Also close profileMenu if open
                const profileMenu = document.getElementById("profileMenu");
                if (profileMenu) profileMenu.classList.add("hidden");
            }
        }

        function toggleMenu(e) {
            if (e) {
                e.stopPropagation();
            }
            const menu = document.getElementById("profileMenu");
            if (!menu) return;
            menu.classList.toggle("hidden");

            // Close mobileNavDropdown if open
            const mobileDropdown = document.getElementById("mobileNavDropdown");
            if (mobileDropdown && !mobileDropdown.classList.contains("hidden")) {
                mobileDropdown.classList.add("hidden");
                const openIcon = document.getElementById("mobileMenuOpenIcon");
                const closeIcon = document.getElementById("mobileMenuCloseIcon");
                if (openIcon) openIcon.classList.remove("hidden");
                if (closeIcon) closeIcon.classList.add("hidden");
            }
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
            const menu = document.getElementById("profileMenu");
            if (menu && !menu.classList.contains("hidden")) {
                if (!menu.contains(e.target) && !e.target.closest('button[onclick*="toggleMenu"]')) {
                    menu.classList.add("hidden");
                }
            }

            const mobileDropdown = document.getElementById("mobileNavDropdown");
            if (mobileDropdown && !mobileDropdown.classList.contains("hidden")) {
                if (!mobileDropdown.contains(e.target) && !e.target.closest('#mobileNavToggleBtn')) {
                    mobileDropdown.classList.add("hidden");
                    const openIcon = document.getElementById("mobileMenuOpenIcon");
                    const closeIcon = document.getElementById("mobileMenuCloseIcon");
                    if (openIcon) openIcon.classList.remove("hidden");
                    if (closeIcon) closeIcon.classList.add("hidden");
                }
            }
        });

        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('Kharchify SW active:', reg.scope))
                    .catch((err) => console.warn('Kharchify SW failed:', err));
            });
        }
    </script>

    @auth
    <x-export-pdf-modal />
    @endauth
</body>
</html>
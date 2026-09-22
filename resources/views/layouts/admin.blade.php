<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kharchify • Admin Console</title>
    
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --font-main: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            font-family: var(--font-main);
            background-color: #0B0F19;
            color: #F8FAFC;
            overflow-x: hidden;
        }

        /* Clean SaaS Glass Cards */
        .saas-card {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.5);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .saas-card:hover {
            border-color: rgba(99, 102, 241, 0.3);
        }

        .saas-card-static {
            background: #111827;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.5);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0B0F19; }
        ::-webkit-scrollbar-thumb { background: #1E293B; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #6366F1; }

        .pulse-live {
            animation: pulseDot 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.85); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased selection:bg-indigo-600 selection:text-white">

    <!-- Top Navigation Bar -->
    <header class="bg-[#111827]/90 backdrop-blur-xl border-b border-slate-800/80 sticky top-0 z-50">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            
            <!-- Left: Logo & Admin Badge -->
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 group">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 p-0.5 shadow-md shadow-indigo-600/20 group-hover:scale-105 transition transform">
                        <div class="w-full h-full bg-slate-950 rounded-[10px] flex items-center justify-center text-white font-black text-lg">
                            K
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-black tracking-tight text-white group-hover:text-indigo-400 transition">Kharchify</span>
                            <span class="px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-300 text-[10px] font-black border border-indigo-500/30">
                                ADMIN
                            </span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Management & Analytics</p>
                    </div>
                </a>
            </div>

            <!-- Center: Status Ticker -->
            <div class="hidden md:flex items-center gap-3 px-4 py-1.5 rounded-full bg-slate-900/90 border border-slate-800 text-xs text-slate-300">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-live"></span>
                    <span class="text-emerald-400 font-bold">Platform Online</span>
                </div>
                <span class="text-slate-700">•</span>
                <span>Total Users: <strong class="text-white">{{ App\Models\User::count() }}</strong></span>
                <span class="text-slate-700">•</span>
                <span>Total Expenses: <strong class="text-indigo-300">{{ App\Models\Expense::count() }}</strong></span>
            </div>

            <!-- Right: User Switcher & Profile -->
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-800/90 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-bold transition flex items-center gap-2 border border-slate-700 shadow-sm active:scale-95">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    <span>User Dashboard</span>
                </a>

                <div class="flex items-center gap-2.5 pl-3 border-l border-slate-800">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-bold text-xs text-white shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-bold text-white leading-tight flex items-center gap-1.5">
                            <span>{{ Auth::user()->name }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 truncate max-w-[130px]">{{ Auth::user()->email }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" title="Log Out" class="p-2.5 rounded-xl bg-slate-800/80 hover:bg-rose-950/80 hover:text-rose-400 text-slate-400 transition border border-slate-800 hover:border-rose-800 active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>

        </div>
    </header>

    <!-- Main Container with Sidebar -->
    <div class="flex-1 max-w-[1600px] w-full mx-auto px-4 sm:px-6 py-6 flex flex-col lg:flex-row gap-6">

        <!-- Sidebar Navigation -->
        <aside class="w-full lg:w-64 shrink-0 space-y-4">
            
            <div class="saas-card-static p-3 rounded-2xl">
                <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Main Menu
                </div>

                <nav class="space-y-1 mt-1 text-xs font-bold">
                    
                    <!-- Dashboard -->
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ Request::routeIs('admin.dashboard') ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 {{ Request::routeIs('admin.dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        <span class="text-sm">Dashboard</span>
                    </a>

                    <!-- User Management -->
                    <a href="{{ route('admin.users.index') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ Request::routeIs('admin.users.*') && !Request::routeIs('admin.users.create') ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 {{ Request::routeIs('admin.users.*') && !Request::routeIs('admin.users.create') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <div class="flex-1 flex justify-between items-center">
                            <span class="text-sm">Users & Plans</span>
                            <span class="px-2 py-0.5 rounded-full {{ Request::routeIs('admin.users.*') && !Request::routeIs('admin.users.create') ? 'bg-indigo-800 text-white' : 'bg-slate-800 text-slate-300' }} text-[10px] font-bold">{{ App\Models\User::count() }}</span>
                        </div>
                    </a>

                    <!-- Create User -->
                    <a href="{{ route('admin.users.create') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ Request::routeIs('admin.users.create') ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 {{ Request::routeIs('admin.users.create') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                        <span class="text-sm">Add New User</span>
                    </a>

                    <!-- Analytics -->
                    <a href="{{ route('admin.analytics') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ Request::routeIs('admin.analytics') ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 {{ Request::routeIs('admin.analytics') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span class="text-sm">Revenue & Analytics</span>
                    </a>

                    <!-- System Diagnostics -->
                    <a href="{{ route('admin.system') }}"
                       class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ Request::routeIs('admin.system') ? 'bg-indigo-600 text-white font-black shadow-md shadow-indigo-600/25' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                        <svg class="w-4 h-4 {{ Request::routeIs('admin.system') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-sm">System & Health</span>
                    </a>

                </nav>
            </div>

            <!-- Quick Plan Summary Box -->
            @php
                $freeCount = App\Models\User::where('plan', 'free')->orWhereNull('plan')->count();
                $basicCount = App\Models\User::where('plan', 'basic')->count();
                $mediumCount = App\Models\User::where('plan', 'medium')->count();
                $proCount = App\Models\User::where('plan', 'pro')->count();
            @endphp
            <div class="saas-card-static p-4 rounded-2xl text-xs space-y-2.5">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Plan Subscriptions</div>
                <div class="space-y-1.5 font-medium">
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Free Plan (₹0)
                        </span>
                        <strong class="text-white">{{ $freeCount }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-indigo-400"></span> Basic (₹49)
                        </span>
                        <strong class="text-white">{{ $basicCount }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Medium (₹94)
                        </span>
                        <strong class="text-white">{{ $mediumCount }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span> Full Pro (₹150)
                        </span>
                        <strong class="text-white">{{ $proCount }}</strong>
                    </div>
                </div>
            </div>

        </aside>

        <!-- Main Workspace Area -->
        <main class="flex-1 min-w-0 space-y-6">
            
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-200 flex items-center justify-between text-xs font-bold shadow-lg shadow-emerald-500/10">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-emerald-400 text-slate-950 font-black flex items-center justify-center text-xs">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('warning'))
            <div class="p-4 rounded-2xl bg-amber-950/80 border border-amber-500/40 text-amber-200 flex items-center justify-between text-xs font-bold shadow-lg shadow-amber-500/10">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-amber-400 text-slate-950 font-black flex items-center justify-center text-xs">!</span>
                    <span>{{ session('warning') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-950/80 border border-rose-500/40 text-rose-200 flex items-center justify-between text-xs font-bold shadow-lg shadow-rose-500/10">
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded-lg bg-rose-500 text-white font-black flex items-center justify-center text-xs">✕</span>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
            @endif

            @yield('admin-content')

        </main>

    </div>

    <!-- Admin Footer -->
    <footer class="mt-auto border-t border-slate-800 bg-[#0B0F19] py-5 text-center text-xs text-slate-500">
        <p>© 2026 Kharchify Admin Console • Real-Time Finance Management</p>
    </footer>

</body>
</html>

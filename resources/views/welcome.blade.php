<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kharchify | Smart Financial Freedom</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --bg-light: #FDFDFC;
            --font-main: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-light);
            color: #0f172a;
            overflow-x: hidden;
        }

        /* Text Shimmer Effect */
        .shimmer {
            background: linear-gradient(90deg, #0f172a 0%, #4f46e5 50%, #0f172a 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 4s linear infinite;
        }

        @keyframes shimmer {
            to { background-position: 200% center; }
        }

        /* Smooth Reveal Animation */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .delay-1 { animation-delay: 0.15s; }
        .delay-2 { animation-delay: 0.3s; }
        .delay-3 { animation-delay: 0.45s; }

        /* Floating Background Orbs */
        .orb {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%);
            z-index: -1;
            filter: blur(80px);
            animation: float 18s infinite alternate ease-in-out;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(80px, 40px) scale(1.15); }
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .feature-card:hover {
            transform: translateY(-8px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 25px 50px -12px rgba(79, 70, 229, 0.15);
        }

        .btn-premium {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px -8px rgba(79, 70, 229, 0.35);
        }
    </style>
</head>
<body class="relative min-h-screen flex flex-col items-center p-5 lg:p-12">
    
    <div class="orb top-[-10%] left-[-5%]"></div>
    <div class="orb bottom-[0%] right-[-5%]" style="animation-delay: -5s;"></div>

    <!-- Header -->
    <header class="w-full lg:max-w-6xl flex justify-between items-center mb-16 sm:mb-24 reveal">
        <a href="/" class="flex items-center group cursor-pointer transition transform hover:scale-[1.02]">
            <x-application-logo size="large" />
        </a>

        <nav class="flex items-center gap-4 sm:gap-8 text-xs font-bold uppercase tracking-wider text-slate-600">
            @auth
                <a href="{{ url('/dashboard') }}" class="px-7 py-3 bg-slate-950 text-white rounded-full btn-premium shadow-lg">Console →</a>
            @else
                <a href="{{ route('login') }}" class="hover:text-indigo-600 transition-colors">Log In</a>
                <a href="{{ route('register') }}" class="px-6 py-2.5 bg-indigo-600 text-white rounded-full shadow-lg shadow-indigo-600/25 btn-premium">Get Started</a>
            @endauth
        </nav>
    </header>

    <!-- Main Content -->
    <main class="w-full lg:max-w-6xl text-center space-y-28">
        
        <!-- Hero Section -->
        <section class="space-y-6">
            <div class="reveal delay-1">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-black uppercase tracking-wider border border-indigo-100">
                    🚀 Next-Gen Personal Expense Manager
                </span>
            </div>
            <h1 class="text-5xl sm:text-7xl lg:text-8xl font-black leading-[0.95] tracking-tight text-slate-950 reveal delay-1">
                Stop Guessing. <br/><span class="shimmer">Master Your Money.</span>
            </h1>
            <p class="text-lg sm:text-xl text-slate-600 max-w-2xl mx-auto reveal delay-2 leading-relaxed font-medium">
                Kharchify gives you crystal-clear control over your daily spending, monthly budgets, category trends, and PDF financial statements.
            </p>
            <div class="reveal delay-3 pt-4 flex flex-col sm:flex-row justify-center items-center gap-4">
                <a href="{{ route('register') }}" class="px-10 py-5 bg-indigo-600 text-white rounded-2xl font-black text-lg btn-premium shadow-xl shadow-indigo-600/30">
                    Get Started Free →
                </a>
                <a href="#plans" class="px-8 py-5 bg-white text-slate-800 rounded-2xl font-bold text-base border border-slate-200 hover:bg-slate-50 transition shadow-sm">
                    View Pricing (From ₹49)
                </a>
            </div>
        </section>

        <!-- Feature Pillars -->
        <section class="grid md:grid-cols-3 gap-6 text-left">
            <div class="feature-card p-8 rounded-[2.5rem] reveal delay-2">
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">Instant Expense Logging</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed font-medium">
                    Log chai breaks, dining, shopping, bills, or major investments in under 3 seconds with payment mode tags.
                </p>
            </div>

            <div class="feature-card p-8 rounded-[2.5rem] reveal delay-3">
                <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">Visual Trends & Analytics</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed font-medium">
                    Interactive 6-month curves and category donut charts reveal where your money actually flows.
                </p>
            </div>

            <div class="feature-card p-8 rounded-[2.5rem] reveal delay-3" style="animation-delay: 0.6s;">
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-xl font-black text-slate-900 mb-2">Budget Guard & PDF Reports</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed font-medium">
                    Set monthly target limits, track real-time pacing, and export official PDF & CSV statements in 1 click.
                </p>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="plans" class="space-y-12">
            <div>
                <span class="text-xs font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200">⚡ Free Starter Plan Active • Premium Tiers Coming Soon</span>
                <h2 class="text-4xl sm:text-5xl font-black text-slate-950 mt-3">Simple Plans • Built For Everyone</h2>
                <p class="text-slate-500 text-sm sm:text-base mt-2">Start free today with no card required, or preview our 3 upcoming membership tiers.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-left items-stretch">
                <!-- Free Plan ₹0 -->
                <div class="bg-white p-7 rounded-[2.5rem] border-2 border-emerald-400 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-400 flex flex-col justify-between relative">
                    <div class="absolute -top-3 right-6 bg-emerald-600 text-white text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider shadow">
                        ✓ Active Now
                    </div>

                    <div>
                        <span class="px-3 py-1 bg-emerald-50 text-emerald-800 text-[10px] font-black uppercase rounded-full border border-emerald-200">100% Free Forever</span>
                        <h3 class="text-2xl font-black text-slate-900 mt-4">Free Plan</h3>
                        <p class="text-xs text-slate-500 mt-1">Essential personal expense logging</p>
                        
                        <div class="my-6">
                            <span class="text-4xl font-black text-emerald-600">₹0</span>
                            <span class="text-xs font-bold text-slate-400 ml-1">Free Forever</span>
                        </div>

                        <ul class="space-y-3 text-xs font-medium text-slate-600 mb-8">
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Daily Expense Logging (30/mo)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Standard Category Management</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Monthly Spend Totals & Recents</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Responsive Web & Mobile App</li>
                            <li class="flex items-center gap-2 text-slate-400"><span class="text-slate-300">✕</span> PDF Export (Basic ₹49+)</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3.5 bg-emerald-600 text-white text-center rounded-xl font-black text-xs hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/25 active:scale-95 block">
                        Get Started Free →
                    </a>
                </div>

                <!-- Basic Plan ₹49 -->
                <div class="bg-white p-7 rounded-[2.5rem] border border-slate-200/90 shadow-sm flex flex-col justify-between hover:shadow-md transition relative">
                    <div class="absolute -top-3 right-6 bg-indigo-100 text-indigo-900 border border-indigo-200 text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider">
                        Starter ⭐
                    </div>

                    <div>
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase rounded-full">Budget Friendly</span>
                        <h3 class="text-2xl font-black text-slate-900 mt-4">Basic Plan</h3>
                        <p class="text-xs text-slate-500 mt-1">Expanded limits & PDF statements</p>
                        
                        <div class="my-6">
                            <span class="text-4xl font-black text-slate-900">₹49</span>
                            <span class="text-xs font-bold text-slate-400">/month</span>
                        </div>

                        <ul class="space-y-3 text-xs font-medium text-slate-600 mb-8">
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Everything in Free Plan</li>
                            <li class="flex items-center gap-2 font-bold text-emerald-700"><span class="text-emerald-500 font-bold">✓</span> Instant Formatted PDF Statements</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Higher Limits (Up to 150/mo)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Custom Categories & Colors</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Advanced Multi-Filter Search</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Expense Notes & Transaction IDs</li>
                        </ul>
                    </div>
                    <a href="{{ App\Models\User::getWhatsAppUrl('basic') }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-center rounded-xl font-black text-xs transition shadow-lg shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Buy on WhatsApp</span>
                    </a>
                </div>

                <!-- Medium Plan ₹94 -->
                <div class="bg-white p-7 rounded-[2.5rem] border border-slate-200/90 shadow-sm flex flex-col justify-between relative hover:shadow-md transition">
                    <div class="absolute -top-3 right-6 bg-emerald-500 text-white text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider shadow">
                        🔥 Most Popular
                    </div>

                    <div>
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase rounded-full">Visual Analytics</span>
                        <h3 class="text-2xl font-black text-slate-900 mt-4">Medium Plan</h3>
                        <p class="text-xs text-slate-500 mt-1">Smart budgeting, PDF & visual trends</p>
                        
                        <div class="my-6">
                            <span class="text-4xl font-black text-slate-900">₹94</span>
                            <span class="text-xs font-bold text-slate-400">/month</span>
                        </div>

                        <ul class="space-y-3 text-xs font-medium text-slate-600 mb-8">
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Everything in Basic Plan (incl. PDF)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Unlimited Monthly Expenses</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Interactive Visual Charts & Trends</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Monthly Budget Limits & Alerts</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold">✓</span> Instant CSV / Excel Export</li>
                        </ul>
                    </div>
                    <a href="{{ App\Models\User::getWhatsAppUrl('medium') }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-center rounded-xl font-black text-xs transition shadow-lg shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Buy on WhatsApp</span>
                    </a>
                </div>

                <!-- Full Features Plan ₹150 -->
                <div class="bg-gradient-to-b from-slate-900 via-indigo-950 to-slate-900 text-white p-7 rounded-[2.5rem] border border-indigo-700/80 shadow-md flex flex-col justify-between relative">
                    <div class="absolute -top-3 right-6 bg-amber-400 text-slate-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-wider shadow">
                        👑 Best Value
                    </div>

                    <div>
                        <span class="px-3 py-1 bg-amber-400/20 text-amber-300 text-[10px] font-black uppercase rounded-full">All Features</span>
                        <h3 class="text-2xl font-black text-white mt-4">Full Features Plan</h3>
                        <p class="text-xs text-indigo-200 mt-1">Ultimate financial command center</p>
                        
                        <div class="my-6">
                            <span class="text-4xl font-black text-amber-400">₹150</span>
                            <span class="text-xs font-bold text-indigo-200">/month</span>
                        </div>

                        <ul class="space-y-3 text-xs font-medium text-slate-200 mb-8">
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Everything in Medium Plan</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Instant Formatted PDF Statements</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Custom Date-Range PDF Downloads</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Financial Health Score & Insights</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Recurring Bills Tracker & Alerts</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold">✓</span> Priority Support & Cloud Backup</li>
                        </ul>
                    </div>
                    <a href="{{ App\Models\User::getWhatsAppUrl('pro') }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-center rounded-xl font-black text-xs transition shadow-lg shadow-emerald-600/20 active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Buy on WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- WhatsApp Direct Assistance Banner -->
            <div class="mt-8 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-[2rem] p-6 sm:p-8 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="space-y-1 text-center sm:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-black uppercase tracking-wider mb-1">
                        💬 Direct WhatsApp Purchase & Support
                    </div>
                    <h3 class="text-xl sm:text-2xl font-black tracking-tight">Need help choosing or want instant manual activation?</h3>
                    <p class="text-xs sm:text-sm text-emerald-100 max-w-xl font-medium">
                        Contact our team directly on WhatsApp. We answer in minutes and activate paid tiers directly for your account.
                    </p>
                </div>
                <a href="{{ App\Models\User::getWhatsAppUrl('medium') }}" target="_blank" rel="noopener noreferrer" class="px-7 py-3.5 rounded-2xl bg-white hover:bg-emerald-50 text-emerald-800 font-black text-xs flex items-center gap-2.5 shadow-lg active:scale-95 transition shrink-0">
                    <svg class="w-5 h-5 fill-[#25D366]" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                    <span>Chat on WhatsApp →</span>
                </a>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="bg-slate-950 rounded-[3rem] p-10 lg:p-20 text-white reveal relative overflow-hidden">
            <div class="relative z-10 max-w-3xl mx-auto space-y-6">
                <h2 class="text-4xl lg:text-6xl font-black tracking-tight leading-tight">
                    Take Full Control of Your Expenses Today.
                </h2>
                <p class="text-slate-400 text-base">
                    Join thousands of smart spenders who save more every month with Kharchify.
                </p>
                <div class="pt-4">
                    <a href="{{ route('register') }}" class="px-10 py-5 bg-indigo-600 text-white rounded-2xl font-black text-lg hover:bg-indigo-500 transition shadow-xl inline-block">
                        Create Your Account Now →
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="mt-28 w-full lg:max-w-6xl border-t border-slate-200 py-10 flex flex-col md:flex-row justify-between items-center text-xs font-bold text-slate-400">
        <p>© 2026 Kharchify • All Rights Reserved</p>
        <div class="flex gap-6 mt-4 md:mt-0">
            <a href="#plans" class="hover:text-indigo-600 transition">Plans (Free / ₹49 / ₹94 / ₹150)</a>
            <a href="{{ route('login') }}" class="hover:text-indigo-600 transition">Sign In</a>
            <a href="{{ route('register') }}" class="hover:text-indigo-600 transition">Register</a>
        </div>
    </footer>

</body>
</html>
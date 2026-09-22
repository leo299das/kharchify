<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>Kharchify | Smart Personal Financial Management</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --bg-light: #FDFDFC;
            --font-main: 'Plus Jakarta Sans', sans-serif;
        }

        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            position: relative;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            touch-action: manipulation;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-light);
            color: #0f172a;
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
            transform: translateY(16px);
            animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Floating Background Orbs - Constrained to prevent layout expansion */
        .ambient-layer {
            position: fixed;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
        }

        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            animation: float 20s infinite alternate ease-in-out;
        }

        .orb-1 {
            width: min(500px, 90vw);
            height: min(500px, 90vw);
            background: radial-gradient(circle, rgba(99, 102, 241, 0.18) 0%, transparent 70%);
            top: -100px;
            left: -100px;
        }

        .orb-2 {
            width: min(450px, 85vw);
            height: min(450px, 85vw);
            background: radial-gradient(circle, rgba(16, 185, 129, 0.16) 0%, transparent 70%);
            bottom: -80px;
            right: -80px;
            animation-delay: -6s;
        }

        @keyframes float {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 20px) scale(1.08); }
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            border-color: rgba(99, 102, 241, 0.4);
            box-shadow: 0 20px 40px -12px rgba(79, 70, 229, 0.12);
        }

        .btn-premium {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -6px rgba(79, 70, 229, 0.3);
        }

        .btn-premium:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body class="relative min-h-screen flex flex-col items-center px-4 py-4 sm:px-6 sm:py-8 lg:px-12 lg:py-10">
    
    <!-- Background Light Effects (Locked inside fixed viewport) -->
    <div class="ambient-layer">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <!-- Header Navigation -->
    <header class="w-full max-w-6xl relative z-10 flex justify-between items-center mb-10 sm:mb-16 lg:mb-20 reveal">
        <a href="/" class="flex items-center group cursor-pointer transition transform hover:scale-[1.02] shrink-0">
            <x-application-logo size="default" />
        </a>

        <nav class="flex items-center gap-2 sm:gap-4 text-xs font-bold uppercase tracking-wider text-slate-600">
            <a href="{{ asset('downloads/Kharchify.apk') }}" download="Kharchify.apk" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 rounded-full font-bold text-[11px] sm:text-xs transition border border-emerald-200 shadow-sm">
                <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.996-3.4572c.1568-.2716.064-.6185-.2076-.7753-.2715-.1567-.6184-.064-.7752.2076l-2.0232 3.5042c-1.4423-.658-3.056-1.0264-4.7965-1.0264s-3.3542.3684-4.7965 1.0264L5.2533 5.3006c-.1568-.2716-.5037-.3643-.7752-.2076-.2716.1568-.3644.5037-.2076.7753l1.996 3.4572C2.7093 11.2338.2577 15.269.0002 20.0002h23.9996c-.2575-4.7312-2.7091-8.7664-6.1183-10.6788"/></svg>
                <span class="hidden sm:inline">Download</span>
                <span>APK</span>
            </a>
            @auth
                <a href="{{ url('/dashboard') }}" class="px-5 py-2 sm:px-6 sm:py-2.5 bg-slate-950 text-white rounded-full text-xs font-black btn-premium shadow-md">Console →</a>
            @else
                <a href="{{ route('login') }}" class="px-2.5 py-1.5 hover:text-indigo-600 transition-colors text-xs font-bold">Log In</a>
                <a href="{{ route('register') }}" class="px-4 py-2 sm:px-5 sm:py-2.5 bg-indigo-600 text-white rounded-full text-xs font-black shadow-md shadow-indigo-600/25 btn-premium">Register</a>
            @endauth
        </nav>
    </header>

    <!-- Main Content Container -->
    <main class="w-full max-w-6xl relative z-10 text-center space-y-16 sm:space-y-24 lg:space-y-28">
        
        <!-- Hero Section -->
        <section class="space-y-4 sm:space-y-6 max-w-4xl mx-auto px-1">
            <div class="reveal delay-1">
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] sm:text-xs font-black uppercase tracking-wider border border-indigo-100/80">
                    🚀 Smart Personal Expense Tracker
                </span>
            </div>
            
            <h1 class="text-3xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-[1.05] tracking-tight text-slate-950 reveal delay-1 break-words">
                Stop Guessing. <br/><span class="shimmer">Master Your Money.</span>
            </h1>
            
            <p class="text-sm sm:text-base md:text-lg text-slate-600 max-w-2xl mx-auto reveal delay-2 leading-relaxed font-medium px-2">
                Kharchify gives you crystal-clear control over your daily spending, monthly budgets, category trends, and PDF financial statements.
            </p>
            
            <!-- Hero CTAs -->
            <div class="reveal delay-3 pt-2 sm:pt-4 flex flex-col sm:flex-row justify-center items-stretch sm:items-center gap-3 sm:gap-4 max-w-md sm:max-w-none mx-auto">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm sm:text-base btn-premium shadow-lg shadow-indigo-600/25 flex items-center justify-center">
                    Get Started Free →
                </a>
                
                <a href="{{ asset('downloads/Kharchify.apk') }}" download="Kharchify.apk" class="w-full sm:w-auto px-6 py-4 bg-slate-950 hover:bg-slate-900 text-white rounded-2xl font-bold text-sm sm:text-base transition shadow-lg inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.551 0 .9993.4482.9993.9993.0001.5511-.4482.9997-.9993.9997m-11.046 0c-.5511 0-.9993-.4486-.9993-.9997s.4482-.9993.9993-.9993c.5511 0 .9993.4482.9993.9993 0 .5511-.4482.9997-.9993.9997m11.4045-6.02l1.996-3.4572c.1568-.2716.064-.6185-.2076-.7753-.2715-.1567-.6184-.064-.7752.2076l-2.0232 3.5042c-1.4423-.658-3.056-1.0264-4.7965-1.0264s-3.3542.3684-4.7965 1.0264L5.2533 5.3006c-.1568-.2716-.5037-.3643-.7752-.2076-.2716.1568-.3644.5037-.2076.7753l1.996 3.4572C2.7093 11.2338.2577 15.269.0002 20.0002h23.9996c-.2575-4.7312-2.7091-8.7664-6.1183-10.6788"/></svg>
                    <span>Download App (.apk)</span>
                </a>
                
                <a href="#plans" class="w-full sm:w-auto px-6 py-4 bg-white text-slate-800 rounded-2xl font-bold text-sm sm:text-base border border-slate-200 hover:bg-slate-50 transition shadow-sm flex items-center justify-center">
                    Pricing (From ₹49)
                </a>
            </div>
        </section>

        <!-- Feature Pillars -->
        <section class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 text-left">
            <div class="feature-card p-6 sm:p-8 rounded-3xl reveal delay-2">
                <div class="w-11 h-11 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 mb-2">Instant Expense Logging</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed font-medium">
                    Log chai breaks, dining, shopping, bills, or major investments in under 3 seconds with payment mode tags.
                </p>
            </div>

            <div class="feature-card p-6 sm:p-8 rounded-3xl reveal delay-3">
                <div class="w-11 h-11 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 mb-2">Visual Trends & Analytics</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed font-medium">
                    Interactive 6-month curves and category donut charts reveal where your money actually flows.
                </p>
            </div>

            <div class="feature-card p-6 sm:p-8 rounded-3xl reveal delay-3">
                <div class="w-11 h-11 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h3 class="text-lg sm:text-xl font-black text-slate-900 mb-2">Budget Guard & PDF Reports</h3>
                <p class="text-slate-500 text-xs sm:text-sm leading-relaxed font-medium">
                    Set monthly target limits, track real-time pacing, and export official PDF & CSV statements in 1 click.
                </p>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="plans" class="space-y-8 sm:space-y-12">
            <div class="px-2">
                <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider text-emerald-700 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200 inline-block">⚡ Active Membership Plans</span>
                <h2 class="text-2xl sm:text-4xl md:text-5xl font-black text-slate-950 mt-3 tracking-tight">Simple Plans • Built For Everyone</h2>
                <p class="text-slate-500 text-xs sm:text-sm md:text-base mt-2 max-w-xl mx-auto">Start free today or unlock instant PDF statements & analytics with our budget-friendly plans.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 text-left items-stretch">
                <!-- Free Plan ₹0 -->
                <div class="bg-white p-6 sm:p-7 rounded-3xl border-2 border-emerald-400 shadow-lg shadow-emerald-500/10 flex flex-col justify-between relative">
                    <div class="absolute -top-3 right-6 bg-emerald-600 text-white text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider shadow">
                        ✓ Active Now
                    </div>

                    <div>
                        <span class="px-3 py-0.5 bg-emerald-50 text-emerald-800 text-[10px] font-black uppercase rounded-full border border-emerald-200">100% Free Forever</span>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 mt-3">Free Plan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Essential personal expense logging</p>
                        
                        <div class="my-5">
                            <span class="text-3xl sm:text-4xl font-black text-emerald-600">₹0</span>
                            <span class="text-xs font-bold text-slate-400 ml-1">Free Forever</span>
                        </div>

                        <ul class="space-y-2.5 text-xs font-medium text-slate-600 mb-6">
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Daily Expense Logging (30/mo)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Standard Category Management</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Monthly Spend Totals & Recents</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Responsive Web & Mobile App</li>
                            <li class="flex items-center gap-2 text-slate-400"><span class="text-slate-300 shrink-0">✕</span> PDF Export (Basic ₹49+)</li>
                        </ul>
                    </div>
                    <a href="{{ route('register') }}" class="w-full py-3.5 bg-emerald-600 text-white text-center rounded-xl font-black text-xs hover:bg-emerald-700 transition shadow-md shadow-emerald-600/20 active:scale-95 block">
                        Get Started Free →
                    </a>
                </div>

                <!-- Basic Plan ₹49 -->
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition relative">
                    <div class="absolute -top-3 right-6 bg-indigo-100 text-indigo-900 border border-indigo-200 text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider">
                        Starter ⭐
                    </div>

                    <div>
                        <span class="px-3 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-black uppercase rounded-full">Budget Friendly</span>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 mt-3">Basic Plan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Expanded limits & PDF statements</p>
                        
                        <div class="my-5">
                            <span class="text-3xl sm:text-4xl font-black text-slate-900">₹49</span>
                            <span class="text-xs font-bold text-slate-400">/month</span>
                        </div>

                        <ul class="space-y-2.5 text-xs font-medium text-slate-600 mb-6">
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Everything in Free Plan</li>
                            <li class="flex items-center gap-2 font-bold text-emerald-700"><span class="text-emerald-500 font-bold shrink-0">✓</span> Instant Formatted PDF Statements</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Higher Limits (Up to 150/mo)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Custom Categories & Colors</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Advanced Multi-Filter Search</li>
                        </ul>
                    </div>
                    <a href="{{ App\Models\User::getWhatsAppUrl('basic') }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-center rounded-xl font-black text-xs transition shadow-md active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Buy on WhatsApp</span>
                    </a>
                </div>

                <!-- Medium Plan ₹94 -->
                <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-sm flex flex-col justify-between relative hover:shadow-md transition">
                    <div class="absolute -top-3 right-6 bg-emerald-500 text-white text-[10px] font-black px-3 py-0.5 rounded-full uppercase tracking-wider shadow">
                        🔥 Popular
                    </div>

                    <div>
                        <span class="px-3 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase rounded-full">Visual Analytics</span>
                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 mt-3">Medium Plan</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Smart budgeting, PDF & visual trends</p>
                        
                        <div class="my-5">
                            <span class="text-3xl sm:text-4xl font-black text-slate-900">₹94</span>
                            <span class="text-xs font-bold text-slate-400">/month</span>
                        </div>

                        <ul class="space-y-2.5 text-xs font-medium text-slate-600 mb-6">
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Everything in Basic Plan (incl. PDF)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Unlimited Monthly Expenses</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Interactive Visual Charts & Trends</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Monthly Budget Limits & Alerts</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-500 font-bold shrink-0">✓</span> Instant CSV / Excel Export</li>
                        </ul>
                    </div>
                    <a href="{{ App\Models\User::getWhatsAppUrl('medium') }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-center rounded-xl font-black text-xs transition shadow-md active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Buy on WhatsApp</span>
                    </a>
                </div>

                <!-- Full Features Plan ₹150 -->
                <div class="bg-gradient-to-b from-slate-900 via-indigo-950 to-slate-900 text-white p-6 sm:p-7 rounded-3xl border border-indigo-700/80 shadow-lg flex flex-col justify-between relative">
                    <div class="absolute -top-3 right-6 bg-amber-400 text-slate-950 text-[10px] font-black px-3.5 py-1 rounded-full uppercase tracking-wider shadow">
                        👑 Best Value
                    </div>

                    <div>
                        <span class="px-3 py-0.5 bg-amber-400/20 text-amber-300 text-[10px] font-black uppercase rounded-full">All Features</span>
                        <h3 class="text-xl sm:text-2xl font-black text-white mt-3">Full Features Plan</h3>
                        <p class="text-xs text-indigo-200 mt-0.5">Ultimate financial command center</p>
                        
                        <div class="my-5">
                            <span class="text-3xl sm:text-4xl font-black text-amber-400">₹150</span>
                            <span class="text-xs font-bold text-indigo-200">/month</span>
                        </div>

                        <ul class="space-y-2.5 text-xs font-medium text-slate-200 mb-6">
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold shrink-0">✓</span> Everything in Medium Plan</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold shrink-0">✓</span> Instant Formatted PDF Statements</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold shrink-0">✓</span> Custom Date-Range PDF Downloads</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold shrink-0">✓</span> Financial Health Score & Insights</li>
                            <li class="flex items-center gap-2"><span class="text-amber-400 font-bold shrink-0">✓</span> Recurring Bills Tracker & Alerts</li>
                        </ul>
                    </div>
                    <a href="{{ App\Models\User::getWhatsAppUrl('pro') }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 bg-[#25D366] hover:bg-[#20bd5a] text-white text-center rounded-xl font-black text-xs transition shadow-md active:scale-95 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Buy on WhatsApp</span>
                    </a>
                </div>
            </div>

            <!-- WhatsApp Direct Assistance Banner -->
            <div class="mt-8 bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="space-y-1.5 text-center sm:text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-white/20 text-white text-[10px] sm:text-[11px] font-black uppercase tracking-wider">
                        💬 Direct WhatsApp Purchase & Support
                    </div>
                    <h3 class="text-lg sm:text-2xl font-black tracking-tight">Need help choosing or want instant manual activation?</h3>
                    <p class="text-xs sm:text-sm text-emerald-100 max-w-xl font-medium">
                        Contact our team directly on WhatsApp. We answer in minutes and activate paid tiers directly for your account.
                    </p>
                </div>
                <a href="{{ App\Models\User::getWhatsAppUrl('medium') }}" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-white hover:bg-emerald-50 text-emerald-800 font-black text-xs flex items-center justify-center gap-2.5 shadow-md active:scale-95 transition shrink-0">
                    <svg class="w-5 h-5 fill-[#25D366] shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                    <span>Chat on WhatsApp →</span>
                </a>
            </div>
        </section>

        <!-- CTA Banner -->
        <section class="bg-slate-950 rounded-3xl sm:rounded-[2.5rem] p-8 sm:p-14 lg:p-18 text-white reveal relative overflow-hidden">
            <div class="relative z-10 max-w-2xl mx-auto space-y-4 sm:space-y-6">
                <h2 class="text-2xl sm:text-4xl lg:text-5xl font-black tracking-tight leading-tight">
                    Take Full Control of Your Expenses Today.
                </h2>
                <p class="text-slate-400 text-xs sm:text-sm md:text-base">
                    Join smart spenders who save more every month with Kharchify.
                </p>
                <div class="pt-2">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm sm:text-base hover:bg-indigo-500 transition shadow-lg inline-block">
                        Create Your Account Now →
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="mt-16 sm:mt-24 w-full max-w-6xl border-t border-slate-200 py-8 flex flex-col sm:flex-row justify-between items-center text-xs font-bold text-slate-400 gap-4">
        <p>© 2026 Kharchify • All Rights Reserved</p>
        <div class="flex flex-wrap justify-center gap-4 sm:gap-6">
            <a href="#plans" class="hover:text-indigo-600 transition">Plans (Free / ₹49 / ₹94 / ₹150)</a>
            <a href="{{ route('login') }}" class="hover:text-indigo-600 transition">Sign In</a>
            <a href="{{ route('register') }}" class="hover:text-indigo-600 transition">Register</a>
        </div>
    </footer>

    <!-- Register Service Worker for PWA -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => console.log('Kharchify SW active:', reg.scope))
                    .catch((err) => console.warn('Kharchify SW failed:', err));
            });
        }
    </script>
</body>
</html>
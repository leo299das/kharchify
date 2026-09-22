<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>Terms & Conditions | Kharchify</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- PWA & Mobile Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Kharchify">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            max-width: 100vw;
            overflow-x: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-text-size-adjust: 100%;
            touch-action: manipulation;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white">

    <!-- Header Navigation -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex justify-between items-center">
            <a href="/" class="flex items-center group transition transform hover:scale-[1.02]">
                <x-application-logo size="default" />
            </a>

            <div class="flex items-center gap-3">
                <a href="/" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 transition">
                    ← Home
                </a>
                @auth
                <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition">
                    Console →
                </a>
                @else
                <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition">
                    Get Started
                </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14 w-full flex-1">
        
        <!-- Header Banner -->
        <div class="mb-10 text-left">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-[11px] font-black uppercase tracking-wider border border-emerald-200">
                📜 Legal Agreement
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight mt-3">Terms & Conditions</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-2">Last updated: September 2026 • Effective for all Kharchify users.</p>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-3xl sm:rounded-[2.5rem] border border-slate-200/90 p-6 sm:p-10 shadow-sm space-y-8 text-left text-sm leading-relaxed text-slate-600">
            
            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    1. Acceptance of Terms
                </h2>
                <p>
                    By creating an account, accessing, or using <strong>Kharchify</strong> (accessible at <a href="https://kharchify.dpdns.org" class="text-indigo-600 font-semibold underline">kharchify.dpdns.org</a> and through our Android Application), you agree to be bound by these Terms and Conditions and our Privacy Policy. If you disagree with any part of these terms, you must discontinue use of the platform immediately.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    2. Service Description & Purpose
                </h2>
                <p>
                    Kharchify is a personal finance and expense management software designed to assist individuals and small business owners in recording expenses, organizing spending categories, calculating monthly budgets, and generating downloadable financial statements (PDF & CSV).
                </p>
                <p class="text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100 text-slate-500">
                    <strong>Note:</strong> Kharchify is a financial tracking tool and does not provide certified banking services, tax auditing, or legal investment advice. Users are solely responsible for verifying the accuracy of their self-logged entries.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    3. User Accounts & Security
                </h2>
                <ul class="list-disc pl-5 space-y-1.5 text-slate-600">
                    <li>You must provide accurate, current, and complete registration information.</li>
                    <li>You are responsible for safeguarding your login password and account credentials.</li>
                    <li>You must immediately notify support if you suspect unauthorized access or security breaches.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    4. Subscription Plans, Pricing & Activation
                </h2>
                <p>Kharchify provides both free and paid subscription plans:</p>
                <div class="grid sm:grid-cols-2 gap-3 pt-1">
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200">
                        <p class="font-black text-slate-900 text-xs">✨ Free Plan (₹0)</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Core expense logging up to 30 entries/month.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                        <p class="font-black text-indigo-900 text-xs">⚡ Basic Plan (₹49/mo)</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Higher limits & instant formatted PDF statement downloads.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-100">
                        <p class="font-black text-emerald-900 text-xs">🔥 Medium Plan (₹94/mo)</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Unlimited expenses, interactive charts, and budgeting.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-900 text-white border border-slate-800">
                        <p class="font-black text-amber-400 text-xs">👑 Full Features Plan (₹150/mo)</p>
                        <p class="text-[11px] text-slate-300 mt-0.5">All features, CSV exports, priority support & cloud backup.</p>
                    </div>
                </div>
                <p class="text-xs text-slate-500 pt-1">
                    Direct plan purchases are processed securely via WhatsApp/UPI confirmation with official activation by our management team.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    5. User Content & Intellectual Property
                </h2>
                <p>
                    All trademarks, logos, visual designs, and software algorithms of Kharchify are the proprietary property of <strong>mine x 299 / Kharchify</strong>. You retain 100% full ownership over your entered financial transactions, notes, and expense history.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    6. Account Termination & Data Deletion
                </h2>
                <p>
                    Users may delete their account and all associated expense records at any time from their Profile Settings. Upon deletion, all records are permanently erased from our databases without recovery.
                </p>
            </section>

            <section class="space-y-3 pt-4 border-t border-slate-100">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    7. Contact Information
                </h2>
                <p>
                    If you have questions, feedback, or legal inquiries regarding these Terms, please contact our support team:
                </p>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Kharchify Official Support</p>
                        <p class="text-xs text-indigo-600 font-semibold mt-0.5">darakshaanhussain77@gmail.com</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">WhatsApp: +91 92095 71683</p>
                    </div>
                    <a href="mailto:darakshaanhussain77@gmail.com" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition">
                        Email Support →
                    </a>
                </div>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-5xl mx-auto px-4 py-8 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center text-xs font-bold text-slate-400 gap-4">
        <p>© 2026 Kharchify • All Rights Reserved</p>
        <div class="flex flex-wrap justify-center gap-4 sm:gap-6">
            <a href="{{ route('privacy') }}" class="hover:text-indigo-600 transition">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="text-indigo-600">Terms & Conditions</a>
            <a href="{{ route('contact') }}" class="hover:text-indigo-600 transition">Contact Us</a>
            <a href="/" class="hover:text-indigo-600 transition">Home</a>
        </div>
    </footer>

</body>
</html>

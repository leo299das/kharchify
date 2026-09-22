<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>Privacy Policy | Kharchify</title>
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
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-800 text-[11px] font-black uppercase tracking-wider border border-indigo-200">
                🔒 Privacy & Data Protection
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight mt-3">Privacy Policy</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-2">Last updated: September 2026 • Compliant with Google Play & global data protection guidelines.</p>
        </div>

        <!-- Content Card -->
        <div class="bg-white rounded-3xl sm:rounded-[2.5rem] border border-slate-200/90 p-6 sm:p-10 shadow-sm space-y-8 text-left text-sm leading-relaxed text-slate-600">
            
            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    1. Introduction
                </h2>
                <p>
                    At <strong>Kharchify</strong> (operated by <strong>mine x 299</strong>), your personal privacy and financial data confidentiality are our top priorities. This Privacy Policy explains how we collect, use, store, and protect your information when you access our web application (<a href="https://kharchify.dpdns.org" class="text-indigo-600 font-semibold underline">kharchify.dpdns.org</a>) or our Android mobile application.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    2. Information We Collect
                </h2>
                <p>We only collect the minimum data necessary to provide a seamless expense management experience:</p>
                <ul class="list-disc pl-5 space-y-2 text-slate-600">
                    <li><strong>Account Information:</strong> Name, email address, and encrypted password during registration.</li>
                    <li><strong>Financial Transaction Records:</strong> User-entered expense amounts, transaction dates, spending categories, payment methods (Cash, UPI, Card), notes, and monthly budget limits.</li>
                    <li><strong>Technical Device Telemetry:</strong> Anonymized browser type, OS version, and session tokens strictly to keep you securely signed in and support offline PWA sync.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    3. How We Use Your Information
                </h2>
                <ul class="list-disc pl-5 space-y-1.5 text-slate-600">
                    <li>To render your personal analytics, category breakdowns, and monthly spend charts.</li>
                    <li>To generate instant downloadable PDF and CSV financial statements on your request.</li>
                    <li>To calculate real-time budget pacing and send limit warnings.</li>
                    <li>To authenticate your identity securely and maintain account access.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    4. Zero Third-Party Data Selling
                </h2>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-medium">
                    🛡️ <strong>Our Ironclad Commitment:</strong> We <strong>NEVER</strong> sell, rent, monetize, or trade your personal or financial data to data brokers, advertisers, or third parties under any circumstance.
                </div>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    5. Data Security & Storage
                </h2>
                <p>
                    All passwords are encrypted with bcrypt hashing. All network transmissions between your device and our server are secured with high-grade SSL/TLS HTTPS encryption. Our database is fortified with strict firewall access rules.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    6. Your Data Rights & Deletion
                </h2>
                <p>
                    You have complete control over your data. You may edit or delete individual expense entries anytime. If you wish to delete your entire account, you can do so directly with 1-click in your Profile Settings or by emailing <a href="mailto:darakshaanhussain@gmail.com" class="text-indigo-600 font-semibold underline">darakshaanhussain@gmail.com</a>. All your data will be permanently wiped.
                </p>
            </section>

            <section class="space-y-3 pt-4 border-t border-slate-100">
                <h2 class="text-lg sm:text-xl font-black text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                    7. Privacy Questions & Officer Contact
                </h2>
                <p>
                    If you have any questions regarding this Privacy Policy or your data rights, please contact our privacy representative:
                </p>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-800">Kharchify Privacy Team</p>
                        <p class="text-xs text-indigo-600 font-semibold mt-0.5">darakshaanhussain@gmail.com</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">WhatsApp: +91 92095 71683</p>
                    </div>
                    <a href="mailto:darakshaanhussain@gmail.com" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition">
                        Contact Privacy Team →
                    </a>
                </div>
            </section>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-5xl mx-auto px-4 py-8 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center text-xs font-bold text-slate-400 gap-4">
        <p>© 2026 Kharchify • All Rights Reserved</p>
        <div class="flex flex-wrap justify-center gap-4 sm:gap-6">
            <a href="{{ route('privacy') }}" class="text-indigo-600">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-indigo-600 transition">Terms & Conditions</a>
            <a href="{{ route('contact') }}" class="hover:text-indigo-600 transition">Contact Us</a>
            <a href="/" class="hover:text-indigo-600 transition">Home</a>
        </div>
    </footer>

</body>
</html>

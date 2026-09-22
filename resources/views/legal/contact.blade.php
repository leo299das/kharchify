<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    <title>Contact Us | Kharchify Support</title>
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
        input, select, textarea {
            font-size: 16px !important; /* Prevents auto-zoom on iOS mobile */
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

            <div class="flex items-center gap-2 sm:gap-3">
                <a href="/" class="px-3 sm:px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-indigo-600 hover:bg-slate-100 transition">
                    ← Home
                </a>
                @auth
                <a href="{{ route('dashboard') }}" class="px-3.5 sm:px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition">
                    Console →
                </a>
                @else
                <a href="{{ route('login') }}" class="hidden sm:inline-block px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-indigo-600 hover:bg-slate-100 transition">
                    Sign In
                </a>
                <a href="{{ route('register') }}" class="px-3.5 sm:px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-md shadow-indigo-600/20 hover:bg-indigo-700 transition">
                    Get Started
                </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-8 sm:py-12 w-full flex-1">
        
        <!-- Header Banner -->
        <div class="text-center max-w-2xl mx-auto mb-10 sm:mb-12">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-black uppercase tracking-wider border border-indigo-200">
                💬 Help & Support Center
            </span>
            <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight mt-3">Get in Touch With Us</h1>
            <p class="text-slate-500 text-xs sm:text-sm mt-3 leading-relaxed">
                Have questions regarding plans, payments, account upgrades, or app feedback? Our team is active and ready to assist you.
            </p>
        </div>

        <!-- Success Toast / Alert -->
        @if(session('success'))
        <div class="mb-8 max-w-2xl mx-auto p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-sm animate-bounce">
            <div class="p-2 rounded-xl bg-emerald-500 text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <div>
                <p class="font-black text-sm">Message Sent Successfully!</p>
                <p class="text-xs text-emerald-700 mt-1 leading-relaxed">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-8 max-w-2xl mx-auto p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3">
            <div class="p-2 rounded-xl bg-rose-500 text-white shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
            </div>
            <div>
                <p class="font-black text-sm">Please check your inputs:</p>
                <ul class="text-xs text-rose-700 mt-1 list-disc pl-4 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- 3 Contact Channels Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-10">
            
            <!-- Email Card -->
            <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-sm hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-indigo-50 rounded-full blur-xl group-hover:bg-indigo-100 transition"></div>
                
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl mb-4 border border-indigo-100 shadow-sm">
                        ✉️
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-0.5 rounded-full border border-indigo-100">
                        Official Email
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">Email Support</h3>
                    <p class="text-xs text-slate-500 mt-1">For general inquiries, account assistance, and feedback.</p>
                    
                    <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-200/80">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Direct Inbox</p>
                        <p class="text-xs font-black text-indigo-600 break-all select-all mt-0.5">darakshaanhussain@gmail.com</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-2">
                    <a href="mailto:darakshaanhussain@gmail.com?subject=Kharchify%20Support%20Inquiry" class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs text-center shadow-md shadow-indigo-600/20 active:scale-95 transition">
                        Open Mail App →
                    </a>
                    <button type="button" onclick="copyEmail()" id="copyBtn" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center active:scale-95 transition flex items-center justify-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        <span id="copyText">Copy Email Address</span>
                    </button>
                </div>
            </div>

            <!-- WhatsApp Card -->
            <div class="bg-white rounded-3xl border border-emerald-200/90 p-6 sm:p-7 shadow-sm hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-50 rounded-full blur-xl group-hover:bg-emerald-100 transition"></div>
                
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#25D366] flex items-center justify-center text-xl mb-4 border border-emerald-100 shadow-sm">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                        Instant WhatsApp Chat
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">WhatsApp Support</h3>
                    <p class="text-xs text-slate-500 mt-1">Instant plan activations, UPI payments, and real-time chat.</p>
                    
                    <div class="mt-4 p-3 bg-emerald-50/50 rounded-2xl border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Official Number</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">+91 92095 71683</p>
                    </div>
                </div>

                <div class="mt-6">
                    <a href="https://wa.me/919209571683?text=Hi%20Kharchify%20Team%2C%20I%20have%20an%20inquiry%20regarding%20Kharchify%20expense%20tracker." target="_blank" rel="noopener noreferrer" class="w-full py-3 px-4 rounded-xl bg-[#25D366] hover:bg-[#20bd5a] text-white font-black text-xs text-center flex items-center justify-center gap-2 shadow-md shadow-emerald-600/20 active:scale-95 transition">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                        <span>Chat on WhatsApp →</span>
                    </a>
                </div>
            </div>

            <!-- Legal & Privacy Desk -->
            <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-sm hover:shadow-md transition flex flex-col justify-between relative overflow-hidden group">
                <div class="absolute -top-10 -right-10 w-24 h-24 bg-amber-50 rounded-full blur-xl group-hover:bg-amber-100 transition"></div>
                
                <div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl mb-4 border border-amber-100 shadow-sm">
                        🛡️
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                        Privacy & Data Rights
                    </span>
                    <h3 class="text-lg font-black text-slate-900 mt-2">Privacy Officer</h3>
                    <p class="text-xs text-slate-500 mt-1">Data erasure requests, GDPR inquiries, and terms verification.</p>
                    
                    <div class="mt-4 p-3 bg-slate-50 rounded-2xl border border-slate-200/80">
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Response Window</p>
                        <p class="text-xs font-black text-slate-800 mt-0.5">Within 24 Hours</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-2">
                    <a href="{{ route('privacy') }}" class="w-full py-3 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-black text-xs text-center shadow-md active:scale-95 transition">
                        Read Privacy Policy →
                    </a>
                </div>
            </div>

        </div>

        <!-- Contact Form & Quick FAQ Section -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Contact Form (7 Cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl sm:rounded-[2.5rem] border border-slate-200/90 p-6 sm:p-10 shadow-sm">
                <div class="mb-6">
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Send an Inquiry</span>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Drop Us a Quick Message</h2>
                    <p class="text-xs text-slate-500 mt-1">We'll review your submission and reply directly to your email address.</p>
                </div>

                <form method="POST" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Your Name <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required value="{{ old('name', Auth::user()->name ?? '') }}" placeholder="e.g. Rahul Sharma" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                        </div>
                        <div>
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Your Email Address <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" required value="{{ old('email', Auth::user()->email ?? '') }}" placeholder="e.g. rahul@example.com" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Subject / Category</label>
                        <select name="subject" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                            <option value="Plan Upgrade & Payment Support">💳 Plan Upgrade & Payment Support (Basic ₹49 / Medium ₹94 / Pro ₹150)</option>
                            <option value="Feature Request or Feedback">💡 Feature Request / Feedback</option>
                            <option value="Bug Report or Technical Issue">🐛 Bug Report or Technical Issue</option>
                            <option value="Account or Data Privacy Request">🔒 Account or Data Privacy Request</option>
                            <option value="General Inquiry">📝 General Inquiry</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Message Details <span class="text-rose-500">*</span></label>
                        <textarea name="message" rows="4" required placeholder="Explain your request or issue in detail..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">{{ old('message') }}</textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-indigo-600/25 active:scale-95 transition flex items-center justify-center gap-2">
                            <span>Send Message to Support</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                        <p class="text-[11px] text-slate-400 text-center mt-2.5">
                            You can also email us directly at <a href="mailto:darakshaanhussain@gmail.com" class="text-indigo-600 font-bold underline">darakshaanhussain@gmail.com</a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- Quick FAQ / Support Details (5 Cols) -->
            <div class="lg:col-span-5 space-y-4">
                
                <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <span>💡</span> Frequently Asked Questions
                    </h3>

                    <div class="space-y-3 text-xs leading-relaxed">
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="font-black text-slate-900">How do I upgrade to Basic, Medium, or Pro?</p>
                            <p class="text-slate-500 mt-1">Visit our <a href="{{ route('plans.show') }}" class="text-indigo-600 font-bold underline">Plans page</a> and tap "Buy on WhatsApp". Send a message to our WhatsApp support, make the UPI payment, and your account will be upgraded immediately.</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="font-black text-slate-900">Is my financial data kept confidential?</p>
                            <p class="text-slate-500 mt-1">Yes, 100%. We never sell or share user transaction records. All database connections and sessions are secured by high-grade SSL encryption.</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                            <p class="font-black text-slate-900">Can I request complete account deletion?</p>
                            <p class="text-slate-500 mt-1">Yes. You can delete your account and all expense history with one click in your Profile Settings or by emailing darakshaanhussain@gmail.com.</p>
                        </div>
                    </div>
                </div>

                <!-- Response Promise Banner -->
                <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white rounded-3xl p-6 shadow-md border border-indigo-800">
                    <div class="flex items-center gap-2 text-indigo-300 text-xs font-black uppercase tracking-wider">
                        ⚡ Quick Response Guarantee
                    </div>
                    <p class="text-sm font-bold text-white mt-1.5">
                        We reply to every email and message within 2 to 4 business hours.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-700/80 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Operating Hours: 9 AM - 9 PM IST</span>
                        <span class="text-emerald-400 font-black">● Online</span>
                    </div>
                </div>

            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="w-full max-w-5xl mx-auto px-4 py-8 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center text-xs font-bold text-slate-400 gap-4">
        <p>© 2026 Kharchify • All Rights Reserved</p>
        <div class="flex flex-wrap justify-center gap-4 sm:gap-6">
            <a href="{{ route('privacy') }}" class="hover:text-indigo-600 transition">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-indigo-600 transition">Terms & Conditions</a>
            <a href="{{ route('contact') }}" class="text-indigo-600">Contact Us</a>
            <a href="/" class="hover:text-indigo-600 transition">Home</a>
        </div>
    </footer>

    <!-- Copy to Clipboard Script -->
    <script>
        function copyEmail() {
            const email = "darakshaanhussain@gmail.com";
            navigator.clipboard.writeText(email).then(() => {
                const copyText = document.getElementById("copyText");
                const copyBtn = document.getElementById("copyBtn");
                copyText.innerText = "Copied to Clipboard! ✓";
                copyBtn.classList.remove('bg-slate-100', 'text-slate-700');
                copyBtn.classList.add('bg-emerald-100', 'text-emerald-800');
                setTimeout(() => {
                    copyText.innerText = "Copy Email Address";
                    copyBtn.classList.remove('bg-emerald-100', 'text-emerald-800');
                    copyBtn.classList.add('bg-slate-100', 'text-slate-700');
                }, 2500);
            }).catch(err => {
                console.error("Failed to copy", err);
            });
        }
    </script>
</body>
</html>

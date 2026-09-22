<x-guest-layout>
    <style>
        /* Smooth Entrance Animation like Categories */
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.9) translateY(20px); }
            70% { transform: scale(1.02); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        .animated-card {
            animation: popIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 2.5rem; /* Extra rounded like Category cards */
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            border-bottom: 8px solid #6366f1; /* Indigo bottom border like Category page */
        }

        /* Modern Input Styling */
        .floating-input {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0 !important;
            border-radius: 1.25rem !important; /* Circular feel */
            background: #f8fafc !important;
            padding: 1rem 1.25rem !important;
        }

        .floating-input:focus {
            background: #ffffff !important;
            border-color: #6366f1 !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1) !important;
            transform: translateY(-2px);
        }

        /* Button Styling like "Save Expense" */
        .btn-glow {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: #4f46e5;
            border-radius: 1.25rem !important;
            padding: 1rem !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .btn-glow:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
            background: #4338ca;
        }

        .btn-glow:active {
            transform: scale(0.95);
        }

        /* Simple Underline Animation for Links */
        .hover-underline {
            color: #6366f1 !important;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .hover-underline:hover {
            color: #4338ca !important;
            text-decoration: underline;
        }
    </style>

    <div class="animated-card max-w-md mx-auto">
        <div class="mb-8 text-center">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Welcome Back</h2>
            <p class="text-slate-400 text-sm mt-1 font-medium italic">Apne expenses manage karne ke liye login karein</p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" :value="__('Email Address')" class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1" />
                <x-text-input id="email" 
                    class="floating-input block mt-1 w-full" 
                    type="email" 
                    name="email" 
                    :value="old('email')" 
                    required 
                    autofocus 
                    placeholder="name@company.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-input-label for="password" :value="__('Password')" class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1" />
                <x-text-input id="password" 
                    class="floating-input block mt-1 w-full"
                    type="password"
                    name="password"
                    required 
                    placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                    <input id="remember_me" type="checkbox" class="rounded-lg border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                    <span class="ms-2 text-sm font-bold text-slate-500 group-hover:text-indigo-600 transition-colors">{{ __('Keep me logged in') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="hover-underline text-xs" href="{{ route('password.request') }}">
                        {{ __('Forgot?') }}
                    </a>
                @endif
            </div>

            <div class="mt-8">
                <x-primary-button class="btn-glow w-full justify-center text-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    {{ __('Sign In') }}
                </x-primary-button>
            </div>
            
            <p class="text-center text-sm text-slate-500 mt-8 font-medium">
                Naya account chahiye? 
                <a href="{{ route('register') }}" class="text-indigo-600 font-black hover:text-indigo-500">Register Here</a>
            </p>
        </form>
    </div>
</x-guest-layout>
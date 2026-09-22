<x-guest-layout>
    <style>
        /* Category-style Pop-in Animation */
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.9) translateY(20px); }
            70% { transform: scale(1.02); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        .animated-card {
            animation: popIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 2.5rem; /* Match category page roundness */
            padding: 2.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            border-bottom: 8px solid #6366f1; /* Indigo border like Category view */
        }

        /* Modern Input Styling */
        .floating-input {
            transition: all 0.3s ease;
            border: 1.5px solid #e2e8f0 !important;
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

        /* Button Glow Effect like "Save Category" */
        .btn-glow {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            background: #4f46e5;
            border: none !important;
            padding: 1rem 2rem !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: white !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 1.25rem !important;
            box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
        }

        .btn-glow:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.4);
            filter: brightness(1.1);
        }

        .btn-glow:active {
            transform: scale(0.95);
        }

        /* Link hover effect */
        .hover-underline {
            color: #6366f1 !important;
            font-weight: 700;
            transition: all 0.3s ease;
            text-decoration: none !important;
        }

        .hover-underline:hover {
            color: #4338ca !important;
            text-decoration: underline !important;
        }
    </style>

    <div class="animated-card max-w-lg mx-auto">
        <div class="mb-8 text-center">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight text-center">Join Kharchify</h2>
            <p class="text-slate-400 text-sm mt-1 font-medium text-center">Naya account banayein aur tracking shuru karein</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Full Name')" class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1" />
                <x-text-input id="name" class="block mt-1 w-full floating-input" type="text" name="name" :value="old('name')" required autofocus placeholder="John Doe" autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email Address')" class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1" />
                <x-text-input id="email" class="block mt-1 w-full floating-input" type="email" name="email" :value="old('email')" required placeholder="name@example.com" autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1" />
                    <x-text-input id="password" class="block mt-1 w-full floating-input"
                                    type="password"
                                    name="password"
                                    placeholder="••••••••"
                                    required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm')" class="text-xs font-bold uppercase tracking-widest text-slate-500 ml-1" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full floating-input"
                                    type="password"
                                    name="password_confirmation" 
                                    placeholder="••••••••"
                                    required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>

            <div class="flex flex-col items-center gap-4 mt-8">
                <button type="submit" class="w-full btn-glow">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    {{ __('Register Now') }}
                </button>

                <a class="hover-underline text-sm" href="{{ route('login') }}">
                    {{ __('Already have an account? Sign In') }}
                </a>
            </div>
        </form>
    </div>
</x-guest-layout>
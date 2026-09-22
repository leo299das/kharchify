@extends('layouts.app')

@section('content')

@php
    $plans = App\Models\User::getAvailablePlans();
    $currentPlanKey = Auth::user()->plan ?? 'free';
@endphp

<style>
    @keyframes cardFadeUp {
        from { opacity: 0; transform: translateY(20px) scale(0.98); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-plan-card {
        animation: cardFadeUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .plan-box {
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .plan-box:hover {
        transform: translateY(-6px);
    }
    .glow-badge {
        box-shadow: 0 0 16px rgba(16, 185, 129, 0.35);
    }
</style>

<div class="px-3 py-6 md:py-10 max-w-7xl mx-auto">
    
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-10 animate-plan-card">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-emerald-50 text-emerald-800 text-xs font-black uppercase tracking-wider mb-3 border border-emerald-200">
            <span>⚡ All Plans Active • Instant WhatsApp Activation</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-black text-slate-900 tracking-tight leading-tight">
            Kharchify <span class="text-indigo-600">Plans & Pricing</span>
        </h1>
        <p class="text-slate-500 mt-2 text-sm sm:text-base font-medium">
            Choose the ideal plan for your expense logging. Start free or upgrade to Basic (₹49), Medium (₹94), or Full Features (₹150) anytime!
        </p>
    </div>

    <!-- 4 Plans Responsive Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 items-stretch mb-14">
        @foreach($plans as $key => $plan)
        @php
            $isCurrent = ($currentPlanKey === $key);
            $isFree = ($key === 'free');
            $isBasic = ($key === 'basic');
            $isPopular = ($key === 'medium');
            $isPro = ($key === 'pro');
        @endphp

        <div class="animate-plan-card" style="animation-delay: {{ $loop->index * 0.08 }}s;">
            <div class="plan-box h-full flex flex-col justify-between rounded-[2rem] p-6 sm:p-7 relative overflow-hidden border transition-all duration-300
                {{ $isFree ? ($isCurrent ? 'bg-white border-emerald-400 shadow-xl shadow-emerald-500/10 ring-2 ring-emerald-400' : 'bg-white border-slate-200/90 shadow-sm') : ($isPro ? 'bg-gradient-to-b from-slate-900 via-indigo-950 to-slate-900 text-white border-indigo-700/80 shadow-md' : 'bg-white border-slate-200/90 shadow-sm') }}">
                
                <!-- Status Badges -->
                <div class="flex justify-between items-center mb-2">
                    <div>
                        @if($isFree)
                            @if($isCurrent)
                            <span class="bg-emerald-600 text-white text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                                ✓ Active Now
                            </span>
                            @else
                            <span class="bg-emerald-100 text-emerald-800 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                                Free Forever
                            </span>
                            @endif
                        @elseif($isPopular)
                        <span class="bg-emerald-500 text-white text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider glow-badge">
                            🔥 Popular
                        </span>
                        @elseif($isPro)
                        <span class="bg-amber-400 text-amber-950 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow">
                            👑 Best Value
                        </span>
                        @else
                        <span class="bg-indigo-50 text-indigo-700 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            Basic
                        </span>
                        @endif
                    </div>

                    @if($isCurrent)
                    <span class="bg-emerald-100 text-emerald-900 text-[9px] font-black px-2.5 py-0.5 rounded-full uppercase tracking-wider border border-emerald-300 flex items-center gap-1">
                        Current Plan
                    </span>
                    @endif
                </div>

                <div>
                    <!-- Plan Title & Price -->
                    <div class="mt-2 mb-4">
                        <h3 class="text-xl font-black {{ $isPro ? 'text-white' : 'text-slate-900' }}">{{ $plan['name'] }}</h3>
                        <p class="text-[11px] {{ $isPro ? 'text-indigo-200' : 'text-slate-500' }} mt-0.5 font-medium leading-snug">{{ $plan['tagline'] }}</p>
                        
                        <div class="flex items-baseline mt-4">
                            <span class="text-3xl sm:text-4xl font-black {{ $isPro ? 'text-amber-400' : 'text-slate-900' }}">{{ $plan['price_formatted'] }}</span>
                            <span class="text-xs font-bold {{ $isPro ? 'text-indigo-200' : 'text-slate-400' }} ml-1">{{ $plan['period'] }}</span>
                        </div>
                    </div>

                    <div class="border-t {{ $isPro ? 'border-indigo-800/60' : 'border-slate-100' }} my-4"></div>

                    <!-- Features List -->
                    <ul class="space-y-2.5 text-xs font-medium {{ $isPro ? 'text-slate-200' : 'text-slate-700' }} mb-6">
                        @foreach($plan['features'] as $feat)
                        <li class="flex items-start gap-2.5">
                            <div class="mt-0.5 w-4 h-4 rounded-full flex items-center justify-center shrink-0 {{ $isPro ? 'bg-indigo-500/30 text-amber-400' : 'bg-emerald-50 text-emerald-600' }}">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="leading-tight">{{ $feat }}</span>
                        </li>
                        @endforeach

                        @foreach($plan['unavailable'] as $unfeat)
                        <li class="flex items-start gap-2.5 opacity-35">
                            <div class="mt-0.5 w-4 h-4 rounded-full flex items-center justify-center shrink-0 bg-slate-100 text-slate-400">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </div>
                            <span class="leading-tight line-through">{{ $unfeat }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Action Button -->
                <div>
                    @if($isCurrent)
                    <button type="button" disabled class="w-full py-3.5 rounded-xl font-black text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 flex items-center justify-center gap-1.5 cursor-default">
                        <span>✓ Current Active Plan</span>
                    </button>
                    @elseif($isFree)
                    <form method="POST" action="{{ route('plans.store') }}">
                        @csrf
                        <input type="hidden" name="plan" value="free">
                        <button type="submit" class="w-full py-3.5 rounded-xl font-black text-xs bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-lg shadow-emerald-600/25 active:scale-95">
                            <span>Select Free Plan</span>
                        </button>
                    </form>
                    @else
                    <div class="space-y-2">
                        @php
                            $waUrl = App\Models\User::getWhatsAppUrl($key, Auth::user()->email ?? null);
                        @endphp
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="w-full py-3.5 px-4 rounded-xl font-black text-xs bg-[#25D366] hover:bg-[#20bd5a] text-white flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 active:scale-95 transition">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                            <span>Buy on WhatsApp</span>
                        </a>
                        <p class="text-[10px] text-center {{ $isPro ? 'text-indigo-300/80' : 'text-slate-500' }} font-medium">💬 Instant activation on WhatsApp</p>
                    </div>
                    @endif
                </div>

            </div>
        </div>
        @endforeach
    </div>

    <!-- WhatsApp Assistance Box -->
    <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white rounded-[2rem] p-6 sm:p-8 shadow-xl mb-10 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="space-y-1 text-center sm:text-left">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 text-white text-[11px] font-black uppercase tracking-wider mb-1">
                💬 Direct WhatsApp Support & Purchase
            </div>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight">Want to upgrade or have questions?</h3>
            <p class="text-xs sm:text-sm text-emerald-100 max-w-xl font-medium">
                Reach out to us directly on WhatsApp. We provide instant manual activation and personalized support for your Kharchify account.
            </p>
        </div>
        <a href="{{ App\Models\User::getWhatsAppUrl('medium', Auth::user()->email ?? null) }}" target="_blank" rel="noopener noreferrer" class="px-7 py-3.5 rounded-2xl bg-white hover:bg-emerald-50 text-emerald-800 font-black text-xs flex items-center gap-2.5 shadow-lg active:scale-95 transition shrink-0">
            <svg class="w-5 h-5 fill-[#25D366]" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
            <span>Chat on WhatsApp →</span>
        </a>
    </div>

    <!-- Feature Comparison Matrix -->
    <div class="bg-white rounded-[2rem] p-6 sm:p-8 border border-slate-200/80 shadow-md mb-10">
        <h3 class="text-xl font-black text-slate-900 mb-5 text-center">Complete Feature Comparison</h3>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 uppercase text-[10px] font-bold">
                        <th class="py-3 px-3">Feature</th>
                        <th class="py-3 px-3 text-center text-emerald-700 bg-emerald-50/50 rounded-t-lg">Free (₹0)</th>
                        <th class="py-3 px-3 text-center text-indigo-700">Basic (₹49)</th>
                        <th class="py-3 px-3 text-center text-emerald-700">Medium (₹94)</th>
                        <th class="py-3 px-3 text-center text-amber-700">Full Features (₹150)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Monthly Expense Logs</td>
                        <td class="py-3 px-3 text-center text-slate-600">30 entries</td>
                        <td class="py-3 px-3 text-center text-slate-600">150 entries</td>
                        <td class="py-3 px-3 text-center text-emerald-600 font-bold">Unlimited</td>
                        <td class="py-3 px-3 text-center text-emerald-600 font-bold">Unlimited</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Category Tagging</td>
                        <td class="py-3 px-3 text-center text-emerald-500">✓ Standard</td>
                        <td class="py-3 px-3 text-center text-emerald-500">✓ Custom</td>
                        <td class="py-3 px-3 text-center text-emerald-500">✓ Custom</td>
                        <td class="py-3 px-3 text-center text-emerald-500">✓ Custom</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Formatted PDF Statements</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓ (Instant)</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓ (Instant)</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓ (Custom Dates)</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Interactive Graphs & Trends</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Monthly Budget Goal & Alerts</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Export CSV / Excel Data</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                    </tr>
                    <tr>
                        <td class="py-3 px-3 font-bold text-slate-900">Financial Health Score & Insights</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-slate-300">✕</td>
                        <td class="py-3 px-3 text-center text-emerald-500 font-bold">✓</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bottom Help & Legal Navigation -->
    <div class="mt-8 mb-16 py-6 border-t border-slate-200/80 flex flex-col sm:flex-row justify-between items-center gap-3 text-xs font-bold text-slate-400">
        <p>© 2026 Kharchify • Direct Assistance: <a href="mailto:darakshaanhussain@gmail.com" class="text-indigo-600 hover:underline">darakshaanhussain@gmail.com</a></p>
        <div class="flex items-center gap-5">
            <a href="{{ route('contact') }}" class="text-indigo-600 hover:underline">Contact Support</a>
            <a href="{{ route('terms') }}" class="hover:text-slate-600 transition">Terms of Service</a>
            <a href="{{ route('privacy') }}" class="hover:text-slate-600 transition">Privacy Policy</a>
        </div>
    </div>

</div>

@endsection
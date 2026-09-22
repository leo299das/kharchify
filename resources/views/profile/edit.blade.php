@extends('layouts.app')

@section('content')

@php
    $user = Auth::user();
    $planDetails = $user->plan_details;
@endphp

<div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
    
    <!-- Header -->
    <div>
        <h2 class="text-3xl font-black text-slate-900 tracking-tight">
            Account Settings
        </h2>
        <p class="mt-1 text-slate-500 text-sm">
            Manage your subscription plan, budget goals, profile details, and account security.
        </p>
    </div>

    <!-- Active Subscription & Budget Settings Card -->
    <div class="bg-white rounded-[2rem] p-6 sm:p-8 border border-slate-200 shadow-md">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-6 mb-6">
            <div>
                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-md">
                    Current Plan
                </span>
                <h3 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                    <span>{{ $planDetails['name'] }}</span>
                    <span class="text-lg text-emerald-600 font-extrabold">{{ $planDetails['price_formatted'] }}/month</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">{{ $planDetails['tagline'] }}</p>
            </div>
            
            <a href="{{ route('plans.show') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-xl transition shadow-md shadow-indigo-600/20">
                ⚡ Change / Upgrade Plan
            </a>
        </div>

        <!-- Monthly Budget Setter inside Profile -->
        <div>
            <h4 class="text-sm font-bold text-slate-800 mb-1">Monthly Spending Budget Target</h4>
            <p class="text-xs text-slate-500 mb-3">Set your target monthly budget to track health score and live dashboard alerts.</p>
            
            <form action="{{ route('budget.update') }}" method="POST" class="flex items-center gap-3 max-w-md">
                @csrf
                <div class="relative flex-1">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">₹</span>
                    <input type="number" name="monthly_budget" value="{{ round($user->monthly_budget ?? 25000) }}" step="500" min="500"
                           class="w-full pl-8 pr-4 py-2 text-sm font-bold rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">
                    Save Budget
                </button>
            </form>
        </div>
    </div>

    <!-- Profile Info Section -->
    <div class="bg-white rounded-[2rem] p-6 sm:p-8 border border-slate-200 shadow-md">
        @include('profile.partials.update-profile-information-form')
    </div>

    <!-- Password Section -->
    <div class="bg-white rounded-[2rem] p-6 sm:p-8 border border-slate-200 shadow-md">
        @include('profile.partials.update-password-form')
    </div>

    <!-- Danger Zone Section -->
    <div class="bg-rose-50/50 rounded-[2rem] p-6 sm:p-8 border border-rose-100 shadow-sm">
        @include('profile.partials.delete-user-form')
    </div>

</div>

@endsection
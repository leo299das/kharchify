@extends('layouts.admin')

@section('admin-content')

<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition group">
            <svg class="w-4 h-4 transform group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to User Management</span>
        </a>
    </div>

    <!-- Form Container -->
    <div class="saas-card p-6 sm:p-8 rounded-2xl border-t-4 border-t-indigo-500">
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center font-bold text-base border border-indigo-500/20">
                    ➕
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">Add New User Account</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-0.5">Manually create a user with a membership plan and role permissions.</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. John Doe"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                @error('name')
                <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="e.g. user@example.com"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                @error('email')
                <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Initial Password *</label>
                <input type="password" name="password" required placeholder="Minimum 8 characters"
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition">
                @error('password')
                <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p>
                @enderror
            </div>

            <!-- Plan Selection & Budget -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Assigned Plan Tier *</label>
                    <select name="plan" required class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-sm font-bold text-white focus:outline-none focus:border-indigo-500 transition cursor-pointer">
                        <option value="free" {{ old('plan') === 'free' ? 'selected' : '' }}>Free Plan (₹0 / Free Forever)</option>
                        <option value="basic" {{ old('plan') === 'basic' ? 'selected' : '' }}>Basic Plan (₹49 / month)</option>
                        <option value="medium" {{ old('plan') === 'medium' ? 'selected' : '' }}>Medium Plan (₹94 / month)</option>
                        <option value="pro" {{ old('plan') === 'pro' ? 'selected' : '' }}>Full Features Plan (₹150 / month)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Monthly Budget Limit (₹)</label>
                    <input type="number" name="monthly_budget" value="{{ old('monthly_budget', 25000) }}" step="500" min="0"
                           class="w-full px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-sm text-white focus:outline-none focus:border-indigo-500 transition">
                </div>
            </div>

            <!-- Admin Privilege Checkbox -->
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-700 flex items-center gap-3">
                <input type="checkbox" name="is_admin" value="1" id="is_admin" {{ old('is_admin') ? 'checked' : '' }}
                       class="w-4 h-4 rounded text-indigo-600 focus:ring-indigo-500 bg-slate-800 border-slate-600">
                <label for="is_admin" class="text-xs font-bold text-white cursor-pointer select-none">
                    Grant Administrator Privileges (Full Admin Panel Access)
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-800">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition border border-slate-700">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-lg shadow-indigo-600/25 active:scale-95">
                    Create Account →
                </button>
            </div>

        </form>
    </div>

</div>

@endsection

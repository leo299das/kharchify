@extends('layouts.admin')

@section('admin-content')

<div class="space-y-6">

    <!-- Top Management Ribbon -->
    <div class="saas-card p-6 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">User Management</h1>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-500/15 text-indigo-300 text-xs font-bold border border-indigo-500/30">
                    {{ $users->total() }} Total Users
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage user memberships, change plan tiers, toggle administrator privileges, and view financial activity.</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition flex items-center gap-2 shadow-lg shadow-indigo-600/20 active:scale-95">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
            <span>Add New User</span>
        </a>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="saas-card p-5 rounded-2xl">
        <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5">
            
            <!-- Keyword Search -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1.5">Search Name / Email / ID</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
                           class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Plan Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1.5">Filter By Plan</label>
                <select name="plan" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white focus:border-indigo-500 focus:outline-none transition">
                    <option value="">All Membership Plans</option>
                    <option value="free" {{ request('plan') === 'free' ? 'selected' : '' }}>Free Plan (₹0)</option>
                    <option value="basic" {{ request('plan') === 'basic' ? 'selected' : '' }}>Basic Plan (₹49)</option>
                    <option value="medium" {{ request('plan') === 'medium' ? 'selected' : '' }}>Medium Plan (₹94)</option>
                    <option value="pro" {{ request('plan') === 'pro' ? 'selected' : '' }}>Full Features Plan (₹150)</option>
                </select>
            </div>

            <!-- Role Filter -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1.5">Filter By Role</label>
                <select name="role" class="w-full px-3.5 py-2 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white focus:border-indigo-500 focus:outline-none transition">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrators Only</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Standard Users Only</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-700">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span>Filter</span>
                </button>
                <a href="{{ route('admin.users.index') }}" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 text-xs font-bold transition border border-slate-700" title="Reset Filters">
                    Reset
                </a>
            </div>

        </form>
    </div>

    <!-- Users Table -->
    <div class="saas-card rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-900/80 text-slate-400 uppercase text-[10px] font-bold border-b border-slate-800">
                        <th class="p-4 w-12 text-center">#</th>
                        <th class="p-4">User</th>
                        <th class="p-4">Membership Plan</th>
                        <th class="p-4">Administrator Role</th>
                        <th class="p-4 text-center">Expenses Logged</th>
                        <th class="p-4">Joined Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 text-slate-300 font-medium">
                    @forelse($users as $u)
                    <tr class="hover:bg-slate-800/40 transition">
                        
                        <!-- ID -->
                        <td class="p-4 text-center text-slate-500 font-bold">
                            #{{ $u->id }}
                        </td>

                        <!-- Name & Email -->
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-500 to-violet-600 flex items-center justify-center font-bold text-xs text-white shrink-0">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="font-bold text-white flex items-center gap-2">
                                        <span>{{ $u->name }}</span>
                                        @if($u->id === auth()->id())
                                        <span class="px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300 text-[9px] font-bold border border-indigo-500/30">YOU</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-slate-400">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- 1-Click Plan Switcher -->
                        <td class="p-4">
                            <form action="{{ route('admin.users.update-plan', $u->id) }}" method="POST" class="inline-block">
                                @csrf
                                <select name="plan" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg text-xs font-bold cursor-pointer transition border
                                    {{ $u->plan === 'pro' ? 'bg-rose-500/10 text-rose-300 border-rose-500/30' : ($u->plan === 'medium' ? 'bg-amber-500/10 text-amber-300 border-amber-500/30' : ($u->plan === 'basic' ? 'bg-indigo-500/10 text-indigo-300 border-indigo-500/30' : 'bg-emerald-500/10 text-emerald-300 border-emerald-500/30')) }}">
                                    <option value="free" {{ ($u->plan === 'free' || empty($u->plan)) ? 'selected' : '' }} class="bg-slate-900 text-white">Free Plan (₹0)</option>
                                    <option value="basic" {{ $u->plan === 'basic' ? 'selected' : '' }} class="bg-slate-900 text-white">Basic Plan (₹49)</option>
                                    <option value="medium" {{ $u->plan === 'medium' ? 'selected' : '' }} class="bg-slate-900 text-white">Medium Plan (₹94)</option>
                                    <option value="pro" {{ $u->plan === 'pro' ? 'selected' : '' }} class="bg-slate-900 text-white">Full Features (₹150)</option>
                                </select>
                            </form>
                        </td>

                        <!-- Admin Role Toggle -->
                        <td class="p-4">
                            <form action="{{ route('admin.users.toggle-admin', $u->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-bold transition border
                                    {{ $u->isAdmin() ? 'bg-amber-500/15 text-amber-300 border-amber-500/30 hover:bg-amber-500/25' : 'bg-slate-800 text-slate-400 border-slate-700 hover:bg-slate-700 hover:text-white' }}">
                                    {{ $u->isAdmin() ? '👑 Administrator' : 'Standard User' }}
                                </button>
                            </form>
                        </td>

                        <!-- Expenses Count & Sum -->
                        <td class="p-4 text-center">
                            <div class="font-bold text-white">{{ $u->expenses_count ?? $u->expenses()->count() }} entries</div>
                            <div class="text-[11px] text-emerald-400 font-bold">₹{{ number_format($u->expenses()->sum('amount'), 2) }}</div>
                        </td>

                        <!-- Joined Date -->
                        <td class="p-4 text-slate-400 text-xs">
                            {{ $u->created_at ? $u->created_at->format('d M Y') : 'N/A' }}
                        </td>

                        <!-- Actions -->
                        <td class="p-4 text-right space-x-1.5">
                            <a href="{{ route('admin.users.show', $u->id) }}" class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-indigo-600 hover:text-white text-indigo-400 font-bold text-xs transition border border-slate-700 inline-block">
                                Inspect
                            </a>

                            @if($u->id !== auth()->id())
                            <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to permanently delete user {{ $u->name }}? All expense records will be deleted.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg bg-slate-800 hover:bg-rose-600 hover:text-white text-rose-400 transition border border-slate-700 inline-block" title="Delete User">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                            @endif
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-500">
                            No users matched your search criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
        <div class="p-4 border-t border-slate-800 bg-slate-900/40">
            {{ $users->links() }}
        </div>
        @endif
    </div>

</div>

@endsection

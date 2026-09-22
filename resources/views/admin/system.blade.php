@extends('layouts.admin')

@section('admin-content')

<div class="space-y-6">

    <!-- Header Ribbon -->
    <div class="saas-card p-6 rounded-2xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">System Health & Diagnostics</h1>
                <span class="px-3 py-1 rounded-full bg-emerald-500/15 text-emerald-400 text-xs font-bold border border-emerald-500/30 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-live"></span>
                    System Healthy
                </span>
            </div>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Database table record allocations, server configuration parameters, and cache tools.</p>
        </div>

        <!-- 1-Click Clear Cache -->
        <form action="{{ route('admin.system.clear-cache') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition flex items-center gap-2 shadow-lg shadow-amber-500/20 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span>Purge App Caches</span>
            </button>
        </form>
    </div>

    <!-- Database Tables Diagnostic Grid -->
    <div class="saas-card p-6 rounded-2xl">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-base font-black text-white tracking-tight">Database Table Record Allocations</h3>
                <p class="text-xs text-slate-400 mt-0.5">Live row count allocation across database schema</p>
            </div>
            <span class="px-2.5 py-1 rounded-full bg-slate-900 border border-slate-800 text-[10px] font-bold text-indigo-300">
                SQL Engine
            </span>
        </div>
        
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            @foreach($tables as $table => $count)
            <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 text-center hover:border-slate-700 transition">
                <span class="text-xs text-slate-400 uppercase tracking-wider block font-bold truncate">{{ $table }}</span>
                <span class="text-2xl font-black text-indigo-400 mt-1 block tracking-tight">{{ number_format($count) }}</span>
                <span class="text-[11px] text-slate-500">records</span>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Server Runtime Environment Specs -->
    <div class="saas-card rounded-2xl overflow-hidden">
        <div class="p-6 border-b border-slate-800 flex justify-between items-center">
            <div>
                <h3 class="text-base font-black text-white tracking-tight">Server Runtime Environment</h3>
                <p class="text-xs text-slate-400 mt-0.5">Underlying server specs and PHP configuration parameters</p>
            </div>
            <span class="px-3 py-1 rounded-full bg-indigo-500/15 text-indigo-300 text-xs font-bold border border-indigo-500/30">
                Specifications
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-800 text-xs">
            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Operating System</span>
                    <span class="text-white font-bold text-right truncate max-w-[240px]">{{ $serverInfo['os'] }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">PHP Version</span>
                    <span class="text-indigo-400 font-black">{{ $serverInfo['php_version'] }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Laravel Framework</span>
                    <span class="text-amber-400 font-black">{{ $serverInfo['laravel_version'] }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Database Engine</span>
                    <span class="text-emerald-400 font-black">{{ strtoupper($serverInfo['database_engine']) }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Server Software</span>
                    <span class="text-slate-300 font-medium truncate max-w-[220px]">{{ $serverInfo['server_software'] }}</span>
                </div>
            </div>

            <div class="p-6 space-y-4">
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">App Environment</span>
                    <span class="text-amber-300 font-black px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20">{{ strtoupper($serverInfo['app_env']) }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Debug Mode</span>
                    <span class="text-indigo-300 font-black">{{ $serverInfo['app_debug'] }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Memory Limit</span>
                    <span class="text-emerald-400 font-black">{{ $serverInfo['memory_limit'] }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Max Execution Time</span>
                    <span class="text-slate-300 font-medium">{{ $serverInfo['max_execution_time'] }}</span>
                </div>
                <div class="flex justify-between items-center py-1">
                    <span class="text-slate-400 font-bold">Max Upload Filesize</span>
                    <span class="text-indigo-400 font-black">{{ $serverInfo['upload_max_filesize'] }}</span>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

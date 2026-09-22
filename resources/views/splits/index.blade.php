@extends('layouts.app')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto pb-12">

    <!-- Header & Action Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl border border-slate-200/90 shadow-sm">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-black uppercase tracking-wider border border-indigo-200 mb-2">
                👥 Splitwise & Shared Expenses
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Kharchify Split & Settle</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Split rent, trips, dinners, and group bills with friends seamlessly.</p>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <button type="button" onclick="openJoinModal()" class="flex-1 sm:flex-initial px-4 py-3.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-2xl font-black text-xs sm:text-sm flex items-center justify-center gap-1.5 transition active:scale-95 shadow-sm">
                <span>🔗 Join with Code</span>
            </button>
            <a href="{{ route('splits.groups.create') }}" class="flex-1 sm:flex-initial px-5 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs sm:text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-600/25 active:scale-95 transition shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                <span>Create Group</span>
            </a>
        </div>
    </div>

    <!-- 3 Net Balance Overview Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        
        <!-- Total You Are Owed (Positive) -->
        <div class="bg-white rounded-3xl border border-emerald-200/90 p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-emerald-800">You Are Owed</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    ↓
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-emerald-600 mt-2">
                +₹{{ number_format($summary['total_owed_to_user'], 2) }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Total friends owe you across groups</p>
        </div>

        <!-- Total You Owe (Negative) -->
        <div class="bg-white rounded-3xl border border-rose-200/90 p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-rose-800">You Owe</span>
                <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-sm font-bold">
                    ↑
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-black text-rose-600 mt-2">
                -₹{{ number_format($summary['total_user_owes'], 2) }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Total you owe to friends</p>
        </div>

        <!-- Overall Net Balance -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-sm relative overflow-hidden group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-500">Overall Net Balance</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    ⚖️
                </div>
            </div>
            <p class="text-2xl sm:text-3xl font-black mt-2 {{ $summary['overall_net'] > 0.01 ? 'text-emerald-600' : ($summary['overall_net'] < -0.01 ? 'text-rose-600' : 'text-slate-700') }}">
                {{ $summary['overall_net'] > 0.01 ? '+' : '' }}₹{{ number_format($summary['overall_net'], 2) }}
            </p>
            <p class="text-[11px] text-slate-400 mt-1">Across {{ $summary['total_groups'] }} active {{ Str::plural('group', $summary['total_groups']) }}</p>
        </div>

    </div>

    <!-- Active Groups List -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">Your Split Groups</h2>
                <p class="text-xs text-slate-400 mt-0.5">Select a group to log expenses, check balances, or settle debts.</p>
            </div>
            <span class="text-xs font-bold text-slate-400 bg-slate-100 px-3 py-1 rounded-full">
                {{ count($summary['groups']) }} {{ Str::plural('Group', count($summary['groups'])) }}
            </span>
        </div>

        @if(empty($summary['groups']))
        <!-- Empty State -->
        <div class="text-center py-12 px-4 border-2 border-dashed border-slate-200 rounded-3xl">
            <div class="w-16 h-16 rounded-3xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-3xl mx-auto mb-4">
                👥
            </div>
            <h3 class="text-lg font-black text-slate-800">No Split Groups Yet</h3>
            <p class="text-xs text-slate-400 max-w-md mx-auto mt-1 leading-relaxed">
                Create a group for your roommates, upcoming trip, party, or dinner split. Or enter an invite code to join your friend's group!
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('splits.groups.create') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs shadow-lg shadow-indigo-600/25 active:scale-95 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>Create New Group</span>
                </a>
                <button type="button" onclick="openJoinModal()" class="inline-flex items-center gap-2 px-6 py-3.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-2xl font-black text-xs active:scale-95 transition">
                    <span>🔗 Join with Code / Link</span>
                </button>
            </div>
        </div>
        @else
        <!-- Groups Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($summary['groups'] as $item)
            @php
                $group = $item['group'];
                $net = $item['net_balance'];
                $badge = $group->type_badge;
                $canDelete = ($group->user_id === Auth::id() || Auth::user()->isAdmin());
            @endphp
            <div class="p-5 rounded-2xl border border-slate-200/90 hover:border-indigo-300 hover:shadow-md transition bg-slate-50/50 hover:bg-white group flex flex-col justify-between">
                <a href="{{ route('splits.show', $group->id) }}" class="block">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-2xl shadow-sm group-hover:scale-110 transition">
                                {{ $badge['icon'] }}
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 group-hover:text-indigo-600 transition flex items-center gap-2">
                                    {{ $group->name }}
                                </h3>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full border {{ $badge['bg'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                    <span class="text-[11px] font-bold text-slate-400">
                                        {{ $group->members->count() }} {{ Str::plural('member', $group->members->count()) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Status Badge -->
                        <div class="text-right shrink-0">
                            @if($net > 0.01)
                            <span class="text-[10px] font-black text-emerald-800 uppercase tracking-wider block">You get back</span>
                            <span class="text-base font-black text-emerald-600">+₹{{ number_format($net, 2) }}</span>
                            @elseif($net < -0.01)
                            <span class="text-[10px] font-black text-rose-800 uppercase tracking-wider block">You owe</span>
                            <span class="text-base font-black text-rose-600">-₹{{ number_format(abs($net), 2) }}</span>
                            @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-black text-slate-400 bg-slate-100 px-2.5 py-1 rounded-xl">
                                <span>✓ Settled</span>
                            </span>
                            @endif
                        </div>
                    </div>
                </a>

                <div class="mt-4 pt-3 border-t border-slate-200/70 flex justify-between items-center text-xs font-bold text-slate-400">
                    <span>Total Spend: ₹{{ number_format($group->total_expenses, 2) }}</span>
                    
                    <div class="flex items-center gap-3">
                        @if($canDelete)
                        <form method="POST" action="{{ route('splits.groups.destroy', $group->id) }}" onsubmit="return confirm('⚠️ Delete group \'{{ addslashes($group->name) }}\'?\n\nIf group work is finished, this will permanently remove all its records.');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-rose-500 hover:text-rose-700 hover:bg-rose-50 px-2 py-1 rounded-lg transition font-black text-[11px] flex items-center gap-1" title="Delete Group (Work Finished)">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span>Delete</span>
                            </button>
                        </form>
                        @endif

                        <a href="{{ route('splits.show', $group->id) }}" class="text-indigo-600 group-hover:translate-x-1 transition inline-flex items-center gap-1">
                            View Details →
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Quick Info Banner for Splitwise Capabilities -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-1 text-center sm:text-left">
                <span class="px-3 py-1 rounded-full bg-indigo-500/30 text-indigo-300 text-[10px] font-black uppercase tracking-wider border border-indigo-400/30">
                    💡 Split Made Simple
                </span>
                <h3 class="text-lg sm:text-xl font-black text-white mt-1">Split Expenses Effortlessly with Anyone</h3>
                <p class="text-xs sm:text-sm text-slate-300 max-w-xl font-medium">
                    Add friends even if they don't have an account. Kharchify automatically calculates the minimum transactions needed and generates 1-tap WhatsApp settlement links!
                </p>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="openJoinModal()" class="px-5 py-3.5 rounded-2xl bg-indigo-800/80 hover:bg-indigo-700 text-white font-bold text-xs border border-indigo-600 transition">
                    🔗 Join with Code
                </button>
                <a href="{{ route('splits.groups.create') }}" class="px-6 py-3.5 rounded-2xl bg-white hover:bg-slate-100 text-slate-900 font-black text-xs shrink-0 shadow-lg active:scale-95 transition">
                    + New Group
                </a>
            </div>
        </div>
    </div>

</div>

<!-- MODAL: JOIN GROUP WITH CODE OR LINK -->
<div id="joinModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 animate-fadeIn">
        <div class="flex justify-between items-center mb-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold">
                    🔗
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">Join a Split Group</h3>
                    <p class="text-xs text-slate-400">Enter group invite code or paste invite link</p>
                </div>
            </div>
            <button onclick="closeJoinModal()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('splits.join.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                    Group Invite Code or Link <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="code" required autofocus placeholder="e.g. 8NKXFCEQ or http://localhost:8000/splits/join/..." 
                       class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-bold text-slate-900 uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                <p class="text-[11px] text-slate-400 mt-1">Ask the group admin for the 8-digit code shown on their group page.</p>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeJoinModal()" class="flex-1 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs rounded-xl shadow-md shadow-indigo-600/25 transition">
                    Join Group →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openJoinModal() {
        document.getElementById('joinModal').classList.remove('hidden');
    }
    function closeJoinModal() {
        document.getElementById('joinModal').classList.add('hidden');
    }
    window.addEventListener('click', (e) => {
        const modal = document.getElementById('joinModal');
        if (e.target === modal) closeJoinModal();
    });
</script>
@endsection

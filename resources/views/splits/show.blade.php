@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-16">

    <!-- Group Header Card -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-6">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            
            <div class="flex items-start gap-3 sm:gap-4">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-3xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-3xl shrink-0 shadow-sm">
                    {{ $group->type_badge['icon'] }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full border {{ $group->type_badge['bg'] }}">
                            {{ $group->type_badge['label'] }}
                        </span>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-600">
                            <span>Code:</span>
                            <span class="text-indigo-600 font-mono font-black select-all" id="groupCodeText">{{ $group->invite_code }}</span>
                            <button type="button" onclick="copyInviteCode()" class="text-slate-400 hover:text-indigo-600 transition" title="Copy Code">
                                📋
                            </button>
                        </div>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">{{ $group->name }}</h1>
                    @if($group->description)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $group->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                
                <!-- Copy Invite Link -->
                <button type="button" onclick="copyInviteLink()" class="flex-1 md:flex-initial px-4 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-2xl font-black text-xs flex items-center justify-center gap-1.5 shadow-sm active:scale-95 transition" title="Copy group invite link to clipboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span id="copyBtnText">Copy Link</span>
                </button>

                <!-- Share on WhatsApp -->
                <a href="{{ route('splits.groups.whatsapp', $group->id) }}" target="_blank" rel="noopener noreferrer" class="flex-1 md:flex-initial px-4 py-3 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-2xl font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20 active:scale-95 transition" title="Share balance breakdown on WhatsApp">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.711 2.598 2.664-.698c.969.54 1.861.815 2.796.815 3.182 0 5.769-2.587 5.769-5.767 0-3.18-2.587-5.767-5.769-5.767zm7.399 5.767c0 4.077-3.321 7.399-7.399 7.399-1.309 0-2.529-.344-3.593-.946l-4.148 1.088 1.107-4.045c-.696-1.127-1.096-2.45-1.096-3.866 0-4.078 3.321-7.4 7.4-7.4 4.078 0 7.399 3.322 7.399 7.4z"/></svg>
                    <span>Share WhatsApp</span>
                </a>

                <!-- Settle Up Button -->
                <button type="button" onclick="openSettleModal()" class="flex-1 md:flex-initial px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20 active:scale-95 transition">
                    <span>⚡ Settle Up</span>
                </button>

                <!-- Add Split Expense Button -->
                <button type="button" onclick="openExpenseModal()" class="w-full sm:w-auto px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/25 active:scale-95 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                    <span>+ Add Expense</span>
                </button>

                <!-- Delete Group (For Finished / Settled Groups) -->
                @if($group->user_id === Auth::id() || ($userMember && $userMember->is_admin) || Auth::user()->isAdmin())
                <form method="POST" action="{{ route('splits.groups.destroy', $group->id) }}" onsubmit="return confirm('⚠️ Delete this group?\n\nIf group work is finished or settled, this will permanently remove the group and all its records.');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-3 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 hover:border-rose-600 rounded-2xl font-black text-xs flex items-center justify-center gap-1.5 shadow-sm transition active:scale-95" title="Delete Group (Work Finished)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        <span>Delete Group</span>
                    </button>
                </form>
                @endif

            </div>

        </div>

        <!-- User Balance Banner in This Group -->
        @php
            $myNet = $userMember ? $userMember->balance_details['net_balance'] : 0;
        @endphp
        <div class="p-4 sm:p-5 rounded-2xl border flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3
            {{ $myNet > 0.01 ? 'bg-emerald-50 border-emerald-200 text-emerald-950' : ($myNet < -0.01 ? 'bg-rose-50 border-rose-200 text-rose-950' : 'bg-slate-50 border-slate-200 text-slate-800') }}">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg font-black
                    {{ $myNet > 0.01 ? 'bg-emerald-500 text-white' : ($myNet < -0.01 ? 'bg-rose-500 text-white' : 'bg-slate-300 text-slate-700') }}">
                    {{ $myNet > 0.01 ? '↓' : ($myNet < -0.01 ? '↑' : '✓') }}
                </div>
                <div>
                    <p class="text-xs font-black uppercase tracking-wider opacity-80">Your Personal Standing in Group</p>
                    <p class="text-lg sm:text-xl font-black mt-0.5">
                        @if($myNet > 0.01)
                            You are owed <span class="text-emerald-700 font-extrabold">₹{{ number_format($myNet, 2) }}</span>
                        @elseif($myNet < -0.01)
                            You owe <span class="text-rose-700 font-extrabold">₹{{ number_format(abs($myNet), 2) }}</span>
                        @else
                            You are all settled up! No pending debts.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 self-end sm:self-auto text-xs font-bold">
                <span class="text-slate-500">Group Total Spend:</span>
                <span class="font-black text-slate-900 bg-white px-3 py-1 rounded-xl border border-slate-200 shadow-sm">
                    ₹{{ number_format($balances['total_spend'], 2) }}
                </span>
            </div>
        </div>

    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-slate-200 px-2 overflow-x-auto">
        <button onclick="switchTab('balances')" id="tabBtn-balances" class="tab-btn px-4 py-3 font-black text-xs border-b-2 border-indigo-600 text-indigo-600 transition flex items-center gap-2 whitespace-nowrap">
            <span>📊 Balances & Debts</span>
            @if(!empty($balances['simplified_debts']))
            <span class="px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-[10px]">
                {{ count($balances['simplified_debts']) }}
            </span>
            @endif
        </button>
        <button onclick="switchTab('expenses')" id="tabBtn-expenses" class="tab-btn px-4 py-3 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-2 whitespace-nowrap">
            <span>🧾 Group Expenses ({{ $group->expenses->count() }})</span>
        </button>
        <button onclick="switchTab('settlements')" id="tabBtn-settlements" class="tab-btn px-4 py-3 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-2 whitespace-nowrap">
            <span>⚡ Payments & Settlements ({{ $group->settlements->count() }})</span>
        </button>
        <button onclick="switchTab('members')" id="tabBtn-members" class="tab-btn px-4 py-3 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-2 whitespace-nowrap">
            <span>👥 Members ({{ $group->members->count() }})</span>
        </button>
    </div>

    <!-- TAB 1: Balances & Simplified Debts -->
    <div id="tabContent-balances" class="tab-content space-y-6">
        
        <!-- Simplified Who Owes Whom Matrix -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                        <span>⚡</span> Simplified Settlements (Who Owes Whom)
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Minimal transactions calculated automatically to settle all group debts.</p>
                </div>
                <button type="button" onclick="openSettleModal()" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 rounded-xl text-xs font-black transition border border-emerald-200">
                    + Record a Settlement
                </button>
            </div>

            @if(empty($balances['simplified_debts']))
            <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                <span class="text-3xl">🎉</span>
                <p class="font-black text-slate-800 text-sm mt-2">Everyone is all settled up!</p>
                <p class="text-xs text-slate-400 mt-0.5">No pending debts in this group right now.</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2">
                @foreach($balances['simplified_debts'] as $debt)
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/90 flex items-center justify-between gap-3 hover:bg-white hover:shadow-sm transition">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-700 font-black text-xs flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($debt['from']->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900 truncate">
                                <span class="text-rose-600">{{ $debt['from']->name }}</span>
                                <span class="text-slate-400 font-normal">owes</span>
                                <span class="text-emerald-600">{{ $debt['to']->name }}</span>
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5">Direct settlement</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5 shrink-0">
                        <span class="text-sm font-black text-slate-900">₹{{ number_format($debt['amount'], 2) }}</span>
                        <button type="button" onclick="prefillSettleModal({{ $debt['from']->id }}, {{ $debt['to']->id }}, {{ $debt['amount'] }})" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-[11px] transition shadow-sm active:scale-95">
                            Settle →
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Individual Member Balances Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-4">
            <h2 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>📋</span> Member Breakdown & Totals
            </h2>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-400 text-[10px] font-black uppercase tracking-wider">
                            <th class="py-3 px-3">Member</th>
                            <th class="py-3 px-3">Total Paid</th>
                            <th class="py-3 px-3">Share Owed</th>
                            <th class="py-3 px-3 text-right">Net Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach($balances['members'] as $item)
                        @php
                            $m = $item['member'];
                            $b = $item['balance'];
                            $net = $b['net_balance'];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-3 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-slate-100 font-black text-[11px] text-slate-700 flex items-center justify-center">
                                    {{ strtoupper(substr($m->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-black text-slate-900">
                                        {{ $m->name }}
                                        @if(Auth::id() && $m->user_id === Auth::id())
                                        <span class="text-indigo-600 font-bold text-[10px]">(You)</span>
                                        @endif
                                    </p>
                                    @if($m->email)
                                    <p class="text-[10px] text-slate-400">{{ $m->email }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-3 font-bold text-slate-700">₹{{ number_format($b['expenses_paid'], 2) }}</td>
                            <td class="py-3.5 px-3 font-bold text-slate-500">₹{{ number_format($b['expenses_share'], 2) }}</td>
                            <td class="py-3.5 px-3 text-right font-black text-sm">
                                @if($net > 0.01)
                                <span class="text-emerald-600">+₹{{ number_format($net, 2) }}</span>
                                @elseif($net < -0.01)
                                <span class="text-rose-600">-₹{{ number_format(abs($net), 2) }}</span>
                                @else
                                <span class="text-slate-400 text-xs">₹0.00 (Settled)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- TAB 2: Group Expenses Activity Feed -->
    <div id="tabContent-expenses" class="tab-content space-y-4 hidden">
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Group Expenses</h2>
                    <p class="text-xs text-slate-400">All shared expenses recorded in {{ $group->name }}.</p>
                </div>
                <button type="button" onclick="openExpenseModal()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-md active:scale-95">
                    <span>+ Add Expense</span>
                </button>
            </div>

            @if($group->expenses->isEmpty())
            <div class="text-center py-12 px-4 border-2 border-dashed border-slate-200 rounded-3xl">
                <span class="text-3xl">🧾</span>
                <h3 class="text-base font-black text-slate-800 mt-2">No Expenses Logged Yet</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">Tap the button below to add your first shared expense and split it with friends.</p>
                <button type="button" onclick="openExpenseModal()" class="mt-4 px-5 py-3 bg-indigo-600 text-white rounded-xl text-xs font-black shadow-md transition active:scale-95">
                    + Add First Split Expense
                </button>
            </div>
            @else
            <div class="space-y-3">
                @foreach($group->expenses as $exp)
                <div class="p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/50 hover:bg-white hover:shadow-sm transition flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    
                    <div class="flex items-start gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-xl shrink-0 shadow-sm">
                            {{ $exp->category_icon }}
                        </div>
                        <div>
                            <h3 class="text-sm sm:text-base font-black text-slate-900">{{ $exp->title }}</h3>
                            <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-400 mt-1">
                                <span class="font-bold text-slate-700">Paid by <strong class="text-indigo-600">{{ $exp->payer->name ?? 'Member' }}</strong></span>
                                <span>•</span>
                                <span>{{ $exp->expense_date->format('d M Y') }}</span>
                                <span>•</span>
                                <span class="bg-slate-200/70 text-slate-700 px-2 py-0.5 rounded-md font-bold uppercase text-[9px]">{{ $exp->split_type }}</span>
                            </div>

                            <!-- Participants Chips -->
                            <div class="flex flex-wrap items-center gap-1.5 mt-2">
                                @foreach($exp->participants as $p)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-lg bg-white border border-slate-200 text-slate-600">
                                    {{ $p->member->name ?? 'User' }}: ₹{{ number_format($p->share_amount, 2) }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-4 self-stretch sm:self-auto pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200/70">
                        <div class="text-left sm:text-right">
                            <span class="text-base sm:text-lg font-black text-slate-900 block">₹{{ number_format($exp->amount, 2) }}</span>
                            <span class="text-[10px] text-slate-400">{{ $exp->participants->count() }} split</span>
                        </div>

                        <!-- Delete Expense -->
                        <form method="POST" action="{{ route('splits.expenses.destroy', [$group->id, $exp->id]) }}" onsubmit="return confirm('Are you sure you want to delete this expense?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete Expense">
                                🗑️
                            </button>
                        </form>
                    </div>

                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: Settlements History -->
    <div id="tabContent-settlements" class="tab-content space-y-4 hidden">
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Payments & Settlements History</h2>
                    <p class="text-xs text-slate-400">Record of all payments made between group members to clear debts.</p>
                </div>
                <button type="button" onclick="openSettleModal()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black transition shadow-md active:scale-95">
                    + Record Settle Up
                </button>
            </div>

            @if($group->settlements->isEmpty())
            <div class="text-center py-12 px-4 border-2 border-dashed border-slate-200 rounded-3xl">
                <span class="text-3xl">💸</span>
                <h3 class="text-base font-black text-slate-800 mt-2">No Settlements Recorded Yet</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mt-1">When friends pay each other back via UPI or Cash, record it here to update balances.</p>
            </div>
            @else
            <div class="space-y-3">
                @foreach($group->settlements as $settle)
                <div class="p-4 rounded-2xl border border-emerald-100 bg-emerald-50/40 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center font-black text-sm">
                            ✓
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm font-black text-slate-900">
                                <span class="text-emerald-800">{{ $settle->fromMember->name }}</span>
                                <span class="text-slate-500 font-medium">paid</span>
                                <span class="text-emerald-800">{{ $settle->toMember->name }}</span>
                            </p>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                Mode: <strong class="text-slate-700">{{ $settle->payment_method }}</strong> • Date: {{ $settle->settled_at->format('d M Y') }}
                                @if($settle->notes) • "{{ $settle->notes }}" @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="text-base font-black text-emerald-700">₹{{ number_format($settle->amount, 2) }}</span>
                        
                        <form method="POST" action="{{ route('splits.settlements.destroy', [$group->id, $settle->id]) }}" onsubmit="return confirm('Delete this settlement record?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Delete">
                                ✕
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <!-- TAB 4: Members List -->
    <div id="tabContent-members" class="tab-content space-y-4 hidden">
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-black text-slate-900 tracking-tight">Group Members ({{ $group->members->count() }})</h2>
                    <p class="text-xs text-slate-400">All registered and offline friends participating in this group.</p>
                </div>
                <button type="button" onclick="openAddMemberModal()" class="px-4 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-black transition active:scale-95">
                    + Add New Member
                </button>
            </div>

            <!-- Single Member Warning & Quick Invite Card -->
            @if($group->members->count() <= 1)
            <div class="p-5 sm:p-6 rounded-2xl bg-amber-50/80 border border-amber-200 text-amber-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <span class="text-3xl shrink-0">👋</span>
                    <div>
                        <h4 class="font-black text-sm text-amber-900">You are the only member in this group right now</h4>
                        <p class="text-xs text-amber-800 mt-0.5 max-w-xl leading-relaxed">
                            Share this group's invite link or code <strong class="font-mono bg-white/80 px-2 py-0.5 rounded border border-amber-300 text-slate-900 select-all">{{ $group->invite_code }}</strong> with your friends so they can join and view balances on their phone or PC!
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto">
                    <button type="button" onclick="copyInviteLink()" class="flex-1 md:flex-initial px-4 py-2.5 bg-amber-800 hover:bg-amber-900 text-white rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 shadow-sm">
                        <span>📋 Copy Invite Link</span>
                    </button>
                    <button type="button" onclick="openAddMemberModal()" class="flex-1 md:flex-initial px-4 py-2.5 bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 rounded-xl text-xs font-black transition flex items-center justify-center gap-1">
                        <span>+ Add Friend</span>
                    </button>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($group->members as $member)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-white border border-slate-200 font-black text-xs text-indigo-600 flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-900 truncate">
                                {{ $member->name }}
                                @if($member->is_admin)
                                <span class="text-[9px] bg-indigo-100 text-indigo-800 font-black px-1.5 py-0.5 rounded-md">Admin</span>
                                @endif
                            </p>
                            <p class="text-[10px] text-slate-400 truncate">{{ $member->email ?? ($member->phone ?? 'Offline Member') }}</p>
                            @if($member->upi_id)
                            <p class="text-[10px] text-emerald-600 font-bold truncate">UPI: {{ $member->upi_id }}</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Group Settings & Danger Zone Card -->
    @if($group->user_id === Auth::id() || ($userMember && $userMember->is_admin) || Auth::user()->isAdmin())
    <div class="bg-rose-50/50 rounded-3xl border border-rose-200/80 p-6 sm:p-8 space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-rose-600 font-black text-xs uppercase tracking-wider">⚠️ Group Settings / Danger Zone</span>
                    <span class="text-[10px] bg-rose-100 text-rose-800 font-black px-2 py-0.5 rounded-full">Admin Only</span>
                </div>
                <h4 class="text-base sm:text-lg font-black text-slate-900">Finished with this group?</h4>
                <p class="text-xs text-slate-500 max-w-xl leading-relaxed">
                    Once group trip or bill splitting is completed and all debts are settled up, you can permanently delete this group. This will remove all group expense history, participant shares, and settlement records.
                </p>
            </div>
            <form method="POST" action="{{ route('splits.groups.destroy', $group->id) }}" onsubmit="return confirm('⚠️ Permanently delete group \'{{ addslashes($group->name) }}\'?\n\nThis will remove all group expenses and settlements. This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-5 py-3 bg-rose-600 hover:bg-rose-700 text-white font-black text-xs rounded-2xl shadow-lg shadow-rose-600/20 active:scale-95 transition flex items-center gap-2 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Delete This Group</span>
                </button>
            </form>
        </div>
    </div>
    @endif

</div>

<!-- MODAL 1: ADD SPLIT EXPENSE -->
<div id="expenseModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-[2rem] max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8 animate-fadeIn">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-xl font-black text-slate-900">Add Split Expense</h3>
                <p class="text-xs text-slate-400">Record a bill or expense for {{ $group->name }}</p>
            </div>
            <button onclick="closeExpenseModal()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('splits.expenses.store', $group->id) }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Expense Title / Description <span class="text-rose-500">*</span></label>
                <input type="text" name="title" required placeholder="e.g. Dinner at Cafe, Airbnb Stay, Cabs" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Total Amount (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="1" id="expenseAmountInput" name="amount" required placeholder="0.00" oninput="updateSplitMath()" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Expense Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="expense_date" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Paid By <span class="text-rose-500">*</span></label>
                    <select name="paid_by_member_id" required class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        @foreach($group->members as $member)
                        <option value="{{ $member->id }}" {{ $userMember && $userMember->id === $member->id ? 'selected' : '' }}>
                            {{ $member->name }} {{ $userMember && $userMember->id === $member->id ? '(You)' : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Category</label>
                    <select name="category" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="Food & Drinks">🍔 Food & Drinks</option>
                        <option value="Transport">🚕 Transport / Cabs</option>
                        <option value="Rent">🏠 Accommodation / Rent</option>
                        <option value="Tickets">🎟️ Tickets & Events</option>
                        <option value="Utilities">⚡ Utilities & Bills</option>
                        <option value="Shopping">🛒 Groceries & Shopping</option>
                        <option value="General" selected>📝 General</option>
                    </select>
                </div>
            </div>

            <!-- Split Method Radios -->
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Split Method</label>
                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="split_type" value="equal" checked onchange="toggleSplitType('equal')" class="peer sr-only" />
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-xs font-black transition">
                            🟰 Split Equally
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="split_type" value="exact" onchange="toggleSplitType('exact')" class="peer sr-only" />
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-xs font-black transition">
                            🔢 Exact Amounts
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="split_type" value="percentage" onchange="toggleSplitType('percentage')" class="peer sr-only" />
                        <div class="p-2.5 rounded-xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-900 text-xs font-black transition">
                            📊 By Percentage
                        </div>
                    </label>
                </div>
            </div>

            <!-- Participants Checkboxes & Custom Inputs -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2.5 max-h-48 overflow-y-auto">
                <p class="text-[11px] font-black uppercase text-slate-400 tracking-wider">Split Between Members</p>
                @foreach($group->members as $member)
                <div class="flex items-center justify-between gap-2 text-xs">
                    <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-800">
                        <input type="checkbox" name="participants[]" value="{{ $member->id }}" checked onchange="updateSplitMath()" class="participant-check rounded-md text-indigo-600 focus:ring-indigo-500 w-4 h-4" />
                        <span>{{ $member->name }}</span>
                    </label>

                    <div class="flex items-center gap-1">
                        <!-- Equal share display -->
                        <span id="equal-share-{{ $member->id }}" class="equal-share-display text-[11px] font-black text-indigo-600">₹0.00</span>
                        
                        <!-- Exact amount input (hidden by default) -->
                        <input type="number" step="0.01" name="shares[{{ $member->id }}]" placeholder="Amount (₹)" class="custom-share-input exact-input hidden w-24 px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold" />
                        
                        <!-- Percentage input (hidden by default) -->
                        <input type="number" step="0.1" name="shares[{{ $member->id }}]" placeholder="Share (%)" class="custom-share-input pct-input hidden w-20 px-2 py-1 bg-white border border-slate-200 rounded-lg text-xs font-bold" />
                    </div>
                </div>
                @endforeach
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-indigo-600/25 active:scale-95 transition">
                    Save & Record Expense
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: SETTLE UP -->
<div id="settleModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-[2rem] max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8 animate-fadeIn">
        <div class="flex justify-between items-center mb-5">
            <div>
                <h3 class="text-xl font-black text-slate-900">Record a Settlement</h3>
                <p class="text-xs text-slate-400">Mark a payment between friends as settled</p>
            </div>
            <button onclick="closeSettleModal()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('splits.settlements.store', $group->id) }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black text-rose-700 uppercase tracking-wider mb-1">From (Who Paid) <span class="text-rose-500">*</span></label>
                    <select id="settleFromMember" name="from_member_id" required class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($group->members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-black text-emerald-700 uppercase tracking-wider mb-1">To (Who Received) <span class="text-rose-500">*</span></label>
                    <select id="settleToMember" name="to_member_id" required class="w-full px-3.5 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        @foreach($group->members as $member)
                        <option value="{{ $member->id }}" {{ $loop->index === 1 ? 'selected' : '' }}>{{ $member->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Amount Settled (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="1" id="settleAmountInput" name="amount" required placeholder="0.00" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-black text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Date <span class="text-rose-500">*</span></label>
                    <input type="date" name="settled_at" required value="{{ date('Y-m-d') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Payment Method</label>
                <select name="payment_method" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700">
                    <option value="UPI" selected>📱 UPI (GPay / PhonePe / Paytm)</option>
                    <option value="Cash">💵 Cash</option>
                    <option value="Bank Transfer">🏦 Bank Transfer / IMPS / NEFT</option>
                    <option value="Other">📝 Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1">Transaction Ref / Notes (Optional)</label>
                <input type="text" name="notes" placeholder="e.g. Paid via PhonePe UPI ID" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium" />
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-emerald-600/25 active:scale-95 transition">
                    ✓ Confirm Settlement
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: ADD MEMBER / INVITE -->
<div id="addMemberModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-[2rem] max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8 animate-fadeIn space-y-5">
        <div class="flex justify-between items-center">
            <div>
                <h3 class="text-xl font-black text-slate-900">Add Member to Group</h3>
                <p class="text-xs text-slate-400">Invite a friend or add an offline contact</p>
            </div>
            <button onclick="closeAddMemberModal()" class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 hover:bg-slate-200 transition">✕</button>
        </div>

        <!-- 1. Instant Invite Link & Code Card -->
        <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 space-y-2.5">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-black uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">
                    <span>🔗</span> Group Invite Code & Link
                </span>
                <span class="px-2 py-0.5 rounded-full bg-indigo-200/70 text-indigo-900 font-mono font-black text-xs select-all">
                    {{ $group->invite_code }}
                </span>
            </div>
            <p class="text-xs text-indigo-800">Your friend can join directly using this link:</p>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ url('/splits/join/' . $group->invite_code) }}" id="modalInviteInput" class="flex-1 px-3 py-2 bg-white border border-indigo-200 rounded-xl text-xs font-mono text-slate-700 select-all" />
                <button type="button" onclick="copyModalInviteLink()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shrink-0 transition shadow-sm">
                    Copy Link
                </button>
            </div>
        </div>

        @if(!empty($registeredUsers) && $registeredUsers->isNotEmpty())
        <!-- 2. Fast Pick From Registered Kharchify Users -->
        <div>
            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                Quick Add Registered User:
            </label>
            <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                @foreach($registeredUsers as $regUser)
                <form method="POST" action="{{ route('splits.groups.members.store', $group->id) }}" class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                    @csrf
                    <input type="hidden" name="name" value="{{ $regUser->name }}">
                    <input type="hidden" name="email" value="{{ $regUser->email }}">
                    <input type="hidden" name="registered_user_id" value="{{ $regUser->id }}">
                    <div class="min-w-0 pr-2">
                        <p class="text-xs font-black text-slate-900 truncate">{{ $regUser->name }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ $regUser->email }}</p>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-black text-[11px] rounded-lg transition shrink-0">
                        + Add
                    </button>
                </form>
                @endforeach
            </div>
        </div>
        @endif

        <div class="relative flex py-1 items-center">
            <div class="flex-grow border-t border-slate-200"></div>
            <span class="flex-shrink mx-3 text-[11px] font-bold text-slate-400 uppercase">Or Add Manually</span>
            <div class="flex-grow border-t border-slate-200"></div>
        </div>

        <!-- 3. Manual Friend Form -->
        <form method="POST" action="{{ route('splits.groups.members.store', $group->id) }}" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Friend's Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. Aman Gupta" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold" />
            </div>
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase mb-1">Email (Optional - connects their account)</label>
                <input type="email" name="email" placeholder="e.g. aman@gmail.com" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
            </div>
            <div class="grid grid-cols-2 gap-2.5">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">Phone / WhatsApp</label>
                    <input type="tel" name="phone" placeholder="+91 9876543210" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                </div>
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase mb-1">UPI ID (Optional)</label>
                    <input type="text" name="upi_id" placeholder="aman@okhdfc" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs" />
                </div>
            </div>

            <button type="submit" class="w-full py-3 bg-slate-900 hover:bg-indigo-600 text-white rounded-xl text-xs font-black transition shadow-sm">
                + Add Member Manually
            </button>
        </form>
    </div>
</div>

<script>
    const inviteUrl = "{{ url('/splits/join/' . $group->invite_code) }}";
    const inviteCode = "{{ $group->invite_code }}";

    function copyInviteLink() {
        navigator.clipboard.writeText(inviteUrl).then(() => {
            const btn = document.getElementById('copyBtnText');
            if (btn) {
                btn.innerText = 'Copied! ✓';
                setTimeout(() => btn.innerText = 'Copy Link', 2500);
            }
            alert('Group invite link copied to clipboard!\n\nSend this link to your friend:\n' + inviteUrl);
        }).catch(err => {
            prompt('Copy this group invite link:', inviteUrl);
        });
    }

    function copyInviteCode() {
        navigator.clipboard.writeText(inviteCode).then(() => {
            alert('Group code ' + inviteCode + ' copied to clipboard!');
        }).catch(err => {
            prompt('Group Code:', inviteCode);
        });
    }

    function copyModalInviteLink() {
        const input = document.getElementById('modalInviteInput');
        if (input) {
            input.select();
            navigator.clipboard.writeText(input.value);
            alert('Invite link copied!\n' + input.value);
        }
    }

    function switchTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-indigo-600', 'text-indigo-600', 'font-black');
            el.classList.add('border-transparent', 'text-slate-500', 'font-bold');
        });

        const activeContent = document.getElementById('tabContent-' + tabName);
        const activeBtn = document.getElementById('tabBtn-' + tabName);
        if (activeContent) activeContent.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('border-transparent', 'text-slate-500', 'font-bold');
            activeBtn.classList.add('border-indigo-600', 'text-indigo-600', 'font-black');
        }
    }

    function openExpenseModal() {
        document.getElementById('expenseModal').classList.remove('hidden');
        updateSplitMath();
    }
    function closeExpenseModal() {
        document.getElementById('expenseModal').classList.add('hidden');
    }

    function openSettleModal() {
        document.getElementById('settleModal').classList.remove('hidden');
    }
    function closeSettleModal() {
        document.getElementById('settleModal').classList.add('hidden');
    }

    function openAddMemberModal() {
        document.getElementById('addMemberModal').classList.remove('hidden');
    }
    function closeAddMemberModal() {
        document.getElementById('addMemberModal').classList.add('hidden');
    }

    function prefillSettleModal(fromId, toId, amount) {
        document.getElementById('settleFromMember').value = fromId;
        document.getElementById('settleToMember').value = toId;
        document.getElementById('settleAmountInput').value = amount;
        openSettleModal();
    }

    function toggleSplitType(type) {
        const equalDisplays = document.querySelectorAll('.equal-share-display');
        const exactInputs = document.querySelectorAll('.exact-input');
        const pctInputs = document.querySelectorAll('.pct-input');

        equalDisplays.forEach(el => el.classList.add('hidden'));
        exactInputs.forEach(el => el.classList.add('hidden'));
        pctInputs.forEach(el => el.classList.add('hidden'));

        if (type === 'equal') {
            equalDisplays.forEach(el => el.classList.remove('hidden'));
            updateSplitMath();
        } else if (type === 'exact') {
            exactInputs.forEach(el => el.classList.remove('hidden'));
        } else if (type === 'percentage') {
            pctInputs.forEach(el => el.classList.remove('hidden'));
        }
    }

    function updateSplitMath() {
        const amount = parseFloat(document.getElementById('expenseAmountInput').value) || 0;
        const checkedBoxes = document.querySelectorAll('.participant-check:checked');
        const count = checkedBoxes.length;

        if (count > 0 && amount > 0) {
            const share = (amount / count).toFixed(2);
            checkedBoxes.forEach(cb => {
                const display = document.getElementById('equal-share-' + cb.value);
                if (display) display.innerText = '₹' + share;
            });
        }
    }

    window.addEventListener('click', (e) => {
        ['expenseModal', 'settleModal', 'addMemberModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (e.target === modal) modal.classList.add('hidden');
        });
    });
</script>
@endsection

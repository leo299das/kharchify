@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">

    <!-- Back Navigation & Title -->
    <div class="flex items-center gap-3">
        <a href="{{ route('splits.index') }}" class="w-10 h-10 rounded-2xl bg-white border border-slate-200 flex items-center justify-center text-slate-600 hover:text-indigo-600 hover:border-indigo-200 transition shadow-sm active:scale-95">
            ←
        </a>
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Create a Split Group</h1>
            <p class="text-xs text-slate-500">Set up a new group and add friends to start splitting expenses.</p>
        </div>
    </div>

    @if($errors->any())
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs">
        <p class="font-black">Please check your inputs:</p>
        <ul class="list-disc pl-4 mt-1 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('splits.groups.store') }}" class="space-y-6">
        @csrf

        <!-- Group Info Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-5">
            <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                <span>📁</span> Group Details
            </h2>

            <!-- Group Name -->
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">
                    Group Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Goa Trip 2026, Flat 402 Roommates, Office Lunch" class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
            </div>

            <!-- Group Type Selector (Pills) -->
            <div>
                <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-2">
                    Group Category / Type <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="trip" class="peer sr-only" {{ old('type', 'trip') === 'trip' ? 'checked' : '' }} />
                        <div class="p-3 rounded-2xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 peer-checked:text-indigo-900 transition flex flex-col items-center gap-1">
                            <span class="text-xl">✈️</span>
                            <span class="text-xs font-bold">Trip / Vacation</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="apartment" class="peer sr-only" {{ old('type') === 'apartment' ? 'checked' : '' }} />
                        <div class="p-3 rounded-2xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 peer-checked:text-indigo-900 transition flex flex-col items-center gap-1">
                            <span class="text-xl">🏠</span>
                            <span class="text-xs font-bold">Roommates</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="couple" class="peer sr-only" {{ old('type') === 'couple' ? 'checked' : '' }} />
                        <div class="p-3 rounded-2xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 peer-checked:text-indigo-900 transition flex flex-col items-center gap-1">
                            <span class="text-xl">❤️</span>
                            <span class="text-xs font-bold">Couple</span>
                        </div>
                    </label>

                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="event" class="peer sr-only" {{ old('type') === 'event' ? 'checked' : '' }} />
                        <div class="p-3 rounded-2xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 peer-checked:text-indigo-900 transition flex flex-col items-center gap-1">
                            <span class="text-xl">🎉</span>
                            <span class="text-xs font-bold">Event / Party</span>
                        </div>
                    </label>

                    <label class="cursor-pointer col-span-2 sm:col-span-1">
                        <input type="radio" name="type" value="other" class="peer sr-only" {{ old('type') === 'other' ? 'checked' : '' }} />
                        <div class="p-3 rounded-2xl border border-slate-200 text-center peer-checked:border-indigo-600 peer-checked:bg-indigo-50/60 peer-checked:text-indigo-900 transition flex flex-col items-center gap-1">
                            <span class="text-xl">👥</span>
                            <span class="text-xs font-bold">Other</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Currency & Notes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Currency</label>
                    <select name="currency" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition">
                        <option value="INR" selected>₹ INR - Indian Rupee</option>
                        <option value="USD">$ USD - US Dollar</option>
                        <option value="EUR">€ EUR - Euro</option>
                        <option value="GBP">£ GBP - British Pound</option>
                        <option value="AED">د.إ AED - UAE Dirham</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-700 uppercase tracking-wider mb-1.5">Description (Optional)</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="e.g. Splitting villa rent, cabs, food and drinks" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-2xl text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" />
                </div>
            </div>
        </div>

        <!-- Group Members Setup Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-5">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                        <span>👥</span> Group Members
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">Add people who will share expenses in this group.</p>
                </div>
                <button type="button" onclick="addMemberRow()" class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-black transition flex items-center gap-1.5 active:scale-95">
                    <span>+ Add Friend</span>
                </button>
            </div>

            <!-- Creator (Self) Row (Fixed) -->
            <div class="p-3.5 rounded-2xl bg-indigo-50/50 border border-indigo-100 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-indigo-600 text-white font-black text-xs flex items-center justify-center">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-900">{{ Auth::user()->name }} <span class="text-indigo-600 font-bold">(You)</span></p>
                        <p class="text-[10px] text-slate-400">{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-800">
                    Group Admin
                </span>
            </div>

            <!-- Dynamic Member Rows Container -->
            <div id="membersContainer" class="space-y-3">
                
                <!-- Initial Member 1 -->
                <div class="member-row p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex-1 w-full sm:w-auto">
                        <input type="text" name="members[0][name]" placeholder="Friend's Name (e.g. Rahul)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="flex-1 w-full sm:w-auto">
                        <input type="email" name="members[0][email]" placeholder="Email (Optional)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="flex-1 w-full sm:w-auto">
                        <input type="tel" name="members[0][phone]" placeholder="Phone / WhatsApp (Optional)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <button type="button" onclick="removeMemberRow(this)" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0" title="Remove">
                        ✕
                    </button>
                </div>

                <!-- Initial Member 2 -->
                <div class="member-row p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    <div class="flex-1 w-full sm:w-auto">
                        <input type="text" name="members[1][name]" placeholder="Friend's Name (e.g. Priya)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="flex-1 w-full sm:w-auto">
                        <input type="email" name="members[1][email]" placeholder="Email (Optional)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <div class="flex-1 w-full sm:w-auto">
                        <input type="tel" name="members[1][phone]" placeholder="Phone / WhatsApp (Optional)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    </div>
                    <button type="button" onclick="removeMemberRow(this)" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0" title="Remove">
                        ✕
                    </button>
                </div>

            </div>

            <button type="button" onclick="addMemberRow()" class="w-full py-3 rounded-2xl border-2 border-dashed border-slate-200 hover:border-indigo-300 text-slate-500 hover:text-indigo-600 font-bold text-xs transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ Add Another Member</span>
            </button>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-2">
            <a href="{{ route('splits.index') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs text-center transition">
                Cancel
            </a>
            <button type="submit" class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs sm:text-sm tracking-wide shadow-lg shadow-indigo-600/25 active:scale-95 transition">
                Create Group & Start Splitting →
            </button>
        </div>

    </form>

</div>

<script>
    let memberCount = 2;

    function addMemberRow(name = '', email = '') {
        const container = document.getElementById('membersContainer');
        const div = document.createElement('div');
        div.className = 'member-row p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center gap-3 animate-fadeIn';
        div.innerHTML = `
            <div class="flex-1 w-full sm:w-auto">
                <input type="text" name="members[${memberCount}][name]" value="${name}" required placeholder="Friend's Name" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div class="flex-1 w-full sm:w-auto">
                <input type="email" name="members[${memberCount}][email]" value="${email}" placeholder="Email (Optional)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <div class="flex-1 w-full sm:w-auto">
                <input type="tel" name="members[${memberCount}][phone]" placeholder="Phone / WhatsApp (Optional)" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            </div>
            <button type="button" onclick="removeMemberRow(this)" class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition shrink-0" title="Remove">
                ✕
            </button>
        `;
        container.appendChild(div);
        memberCount++;
    }

    function removeMemberRow(button) {
        button.closest('.member-row').remove();
    }
</script>
@endsection

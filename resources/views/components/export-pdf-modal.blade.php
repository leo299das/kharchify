<div x-data="{
        open: false,
        activeTab: 'preset', // 'preset', 'month', 'custom'
        selectedPreset: 'this_month',
        selectedMonth: '{{ now()->format('Y-m') }}',
        startDate: '{{ now()->startOfMonth()->format('Y-m-d') }}',
        endDate: '{{ now()->format('Y-m-d') }}',
        statementType: 'all',
        
        applyPreset(p) {
            this.selectedPreset = p;
            this.activeTab = 'preset';
        },
        setMonth(m) {
            this.selectedMonth = m;
            this.activeTab = 'month';
        }
    }"
    @open-pdf-modal.window="open = true"
    @keydown.escape.window="open = false"
    x-show="open"
    class="relative z-50"
    style="display: none;"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">

    <!-- Backdrop overlay -->
    <div x-show="open"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
         @click="open = false"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-slate-200">
                
                <!-- Modal Top Header -->
                <div class="bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600 p-6 text-white relative">
                    <button type="button" 
                            @click="open = false" 
                            class="absolute right-4 top-4 text-white/70 hover:text-white bg-white/10 hover:bg-white/20 p-2 rounded-full transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center text-2xl shadow-inner">
                            📄
                        </div>
                        <div>
                            <h3 class="text-xl font-black tracking-tight" id="modal-title">Export PDF Statement</h3>
                            <p class="text-xs text-emerald-100 mt-0.5">Download formatted financial statement with custom dates</p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('expenses.pdf') }}" method="GET" class="p-6 space-y-5" target="_blank" @submit="setTimeout(() => { open = false }, 800)">
                    
                    <!-- Date Selection Type Switcher Tabs -->
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">1. Choose Date Range Mode</label>
                        <div class="grid grid-cols-3 gap-1 bg-slate-100 p-1 rounded-2xl border border-slate-200 text-xs font-bold">
                            <button type="button" 
                                    @click="activeTab = 'preset'" 
                                    :class="activeTab === 'preset' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2 px-3 rounded-xl transition text-center">
                                ⚡ Presets
                            </button>
                            <button type="button" 
                                    @click="activeTab = 'month'" 
                                    :class="activeTab === 'month' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2 px-3 rounded-xl transition text-center">
                                📅 Single Month
                            </button>
                            <button type="button" 
                                    @click="activeTab = 'custom'" 
                                    :class="activeTab === 'custom' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                    class="py-2 px-3 rounded-xl transition text-center">
                                🛠️ Custom Range
                            </button>
                        </div>
                    </div>

                    <!-- TAB 1: PRESETS -->
                    <div x-show="activeTab === 'preset'" class="space-y-2">
                        <input type="hidden" name="preset" :value="activeTab === 'preset' ? selectedPreset : ''">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <button type="button" 
                                    @click="selectedPreset = 'this_month'"
                                    :class="selectedPreset === 'this_month' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="p-3 rounded-2xl border text-xs font-bold text-left transition flex flex-col justify-between">
                                <span class="text-slate-400 text-[10px] font-black uppercase">Current</span>
                                <span class="font-extrabold mt-1">This Month</span>
                                <span class="text-[10px] text-emerald-600 font-semibold mt-0.5">{{ now()->format('M Y') }}</span>
                            </button>

                            <button type="button" 
                                    @click="selectedPreset = 'last_month'"
                                    :class="selectedPreset === 'last_month' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="p-3 rounded-2xl border text-xs font-bold text-left transition flex flex-col justify-between">
                                <span class="text-slate-400 text-[10px] font-black uppercase">Previous</span>
                                <span class="font-extrabold mt-1">Last Month</span>
                                <span class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ now()->subMonth()->format('M Y') }}</span>
                            </button>

                            <button type="button" 
                                    @click="selectedPreset = 'last_3_months'"
                                    :class="selectedPreset === 'last_3_months' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="p-3 rounded-2xl border text-xs font-bold text-left transition flex flex-col justify-between">
                                <span class="text-slate-400 text-[10px] font-black uppercase">Quarter</span>
                                <span class="font-extrabold mt-1">Last 3 Months</span>
                                <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Past 90 days</span>
                            </button>

                            <button type="button" 
                                    @click="selectedPreset = 'last_6_months'"
                                    :class="selectedPreset === 'last_6_months' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="p-3 rounded-2xl border text-xs font-bold text-left transition flex flex-col justify-between">
                                <span class="text-slate-400 text-[10px] font-black uppercase">Half-Year</span>
                                <span class="font-extrabold mt-1">Last 6 Months</span>
                                <span class="text-[10px] text-slate-500 font-semibold mt-0.5">Past 180 days</span>
                            </button>

                            <button type="button" 
                                    @click="selectedPreset = 'this_year'"
                                    :class="selectedPreset === 'this_year' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="p-3 rounded-2xl border text-xs font-bold text-left transition flex flex-col justify-between">
                                <span class="text-slate-400 text-[10px] font-black uppercase">Yearly</span>
                                <span class="font-extrabold mt-1">This Year</span>
                                <span class="text-[10px] text-slate-500 font-semibold mt-0.5">{{ now()->format('Y') }} YTD</span>
                            </button>

                            <button type="button" 
                                    @click="selectedPreset = 'all_time'"
                                    :class="selectedPreset === 'all_time' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                                    class="p-3 rounded-2xl border text-xs font-bold text-left transition flex flex-col justify-between">
                                <span class="text-slate-400 text-[10px] font-black uppercase">Complete</span>
                                <span class="font-extrabold mt-1">All Time</span>
                                <span class="text-[10px] text-slate-500 font-semibold mt-0.5">All transactions</span>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 2: SINGLE MONTH PICKER -->
                    <div x-show="activeTab === 'month'" class="space-y-3">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Select Month & Year</label>
                            <input type="month" 
                                   name="month" 
                                   x-model="selectedMonth"
                                   :disabled="activeTab !== 'month'"
                                   class="w-full px-4 py-2.5 bg-white border border-slate-300 rounded-xl text-sm font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                            <p class="text-[11px] text-slate-500 mt-2">
                                💡 Generates an end-to-end statement for all 28-31 days of the chosen month.
                            </p>
                        </div>
                    </div>

                    <!-- TAB 3: CUSTOM DATE RANGE -->
                    <div x-show="activeTab === 'custom'" class="space-y-3">
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">From Date (Start)</label>
                                    <input type="date" 
                                           name="start_date" 
                                           x-model="startDate"
                                           :disabled="activeTab !== 'custom'"
                                           class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 uppercase mb-1">To Date (End)</label>
                                    <input type="date" 
                                           name="end_date" 
                                           x-model="endDate"
                                           :disabled="activeTab !== 'custom'"
                                           class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-xs font-bold text-slate-800 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500" />
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-500">
                                💡 Download transactions between any two arbitrary dates (e.g. from salary day to billing day).
                            </p>
                        </div>
                    </div>

                    <!-- 2. Statement Type Selector -->
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-400 mb-2">2. Statement Inclusions</label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="statement_type" value="all" x-model="statementType" class="sr-only">
                                <div :class="statementType === 'all' ? 'border-emerald-500 bg-emerald-50 text-emerald-800 ring-2 ring-emerald-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-600'"
                                     class="p-2.5 rounded-xl border text-center text-xs font-bold transition">
                                    <span>📊 All Data</span>
                                    <span class="block text-[10px] text-slate-400 font-normal">Income + Expense</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="statement_type" value="expenses" x-model="statementType" class="sr-only">
                                <div :class="statementType === 'expenses' ? 'border-red-500 bg-red-50 text-red-800 ring-2 ring-red-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-600'"
                                     class="p-2.5 rounded-xl border text-center text-xs font-bold transition">
                                    <span>💳 Expenses</span>
                                    <span class="block text-[10px] text-slate-400 font-normal">Outflow only</span>
                                </div>
                            </label>

                            <label class="cursor-pointer">
                                <input type="radio" name="statement_type" value="incomes" x-model="statementType" class="sr-only">
                                <div :class="statementType === 'incomes' ? 'border-teal-500 bg-teal-50 text-teal-800 ring-2 ring-teal-500/20' : 'border-slate-200 hover:bg-slate-50 text-slate-600'"
                                     class="p-2.5 rounded-xl border text-center text-xs font-bold transition">
                                    <span>💰 Earnings</span>
                                    <span class="block text-[10px] text-slate-400 font-normal">Inflow only</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" 
                                @click="open = false" 
                                class="flex-1 py-3 px-4 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-[2] py-3 px-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black shadow-lg shadow-emerald-600/25 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <span>Download PDF Statement</span>
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>

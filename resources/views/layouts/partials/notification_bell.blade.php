{{-- Top Navbar Interactive Approval Notification Bell --}}
<div class="relative inline-flex items-center" x-data="approvalNotificationMenu()" x-init="initComponent()">
    <!-- Notification Bell Trigger Button -->
    <button type="button"
            @click="toggleDropdown()"
            :title="'{{ __('Notifikasi Approval') }}'"
            aria-label="{{ __('Notifikasi Approval') }}"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 border border-slate-200/80 transition shadow-2xs cursor-pointer focus:outline-hidden focus:ring-2 focus:ring-blue-500/20"
            :class="{ 'bg-slate-100 text-slate-900 border-blue-300 ring-2 ring-blue-500/20': open }">
        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'scale-110': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>

        <!-- Dynamic Counter Badge strictly perched on TOP-RIGHT (or TOP-LEFT in Arabic RTL) -->
        <span x-show="totalCount > 0"
              x-cloak
              class="absolute z-20 flex items-center justify-center pointer-events-none"
              style="top: -5px; {{ app()->getLocale() === 'ar' ? 'left: -5px; right: auto;' : 'right: -5px; left: auto;' }}">
            <span class="absolute w-4 h-4 rounded-full bg-rose-500 opacity-60 animate-ping"></span>
            <span class="relative px-1.5 min-w-[19px] h-[19px] rounded-full bg-rose-600 text-white text-[10px] font-extrabold flex items-center justify-center ring-2 ring-white shadow-sm"
                  x-text="totalCount > 99 ? '99+' : totalCount">
            </span>
        </span>
    </button>

    <!-- Popover Dropdown Panel -->
    <div x-show="open"
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-1.5 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-1.5 scale-95"
         style="display: none;"
         class="absolute end-0 top-full mt-2.5 w-[390px] sm:w-[440px] max-w-[calc(100vw-1.5rem)] bg-white rounded-2xl shadow-2xl border border-slate-200/90 z-50 overflow-hidden text-start">

        <!-- Header -->
        <div class="px-4 py-3.5 bg-slate-50/90 border-b border-slate-100 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 leading-tight truncate">{{ __('Notifikasi Approval') }}</h4>
                    <p class="text-[11px] text-slate-400 truncate">{{ __('Pusat Otorisasi & Persetujuan') }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <template x-if="totalCount > 0">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200 shadow-2xs whitespace-nowrap"
                          x-text="totalCount + ' {{ __('Perlu Otorisasi') }}'">
                    </span>
                </template>

                <!-- Refresh Button -->
                <button type="button"
                        @click="fetchApprovals()"
                        :disabled="isLoading"
                        title="{{ __('Segarkan Notifikasi') }}"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-white border border-transparent hover:border-slate-200 transition cursor-pointer disabled:opacity-50">
                    <svg class="w-3.5 h-3.5" :class="{ 'animate-spin text-blue-600': isLoading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Filter Chips (Only shown when there are items) -->
        <template x-if="totalCount > 0">
            <div class="px-3.5 py-2 bg-slate-50/50 border-b border-slate-100 flex items-center gap-1.5 overflow-x-auto text-[11px] no-scrollbar">
                <button type="button"
                        @click="activeCategory = 'all'"
                        :class="activeCategory === 'all' ? 'bg-slate-900 text-white font-bold shadow-2xs' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200/80 font-medium'"
                        class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                    <span>{{ __('Semua') }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                          :class="activeCategory === 'all' ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-600'"
                          x-text="totalCount"></span>
                </button>

                <template x-if="counts.leave > 0">
                    <button type="button"
                            @click="activeCategory = 'leave'"
                            :class="activeCategory === 'leave' ? 'bg-amber-600 text-white font-bold shadow-2xs' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Cuti') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'leave' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-800'"
                              x-text="counts.leave"></span>
                    </button>
                </template>

                <template x-if="counts.overtime > 0">
                    <button type="button"
                            @click="activeCategory = 'overtime'"
                            :class="activeCategory === 'overtime' ? 'bg-blue-600 text-white font-bold shadow-2xs' : 'bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Lembur') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'overtime' ? 'bg-white/25 text-white' : 'bg-blue-100 text-blue-800'"
                              x-text="counts.overtime"></span>
                    </button>
                </template>

                <template x-if="counts.reimbursement > 0">
                    <button type="button"
                            @click="activeCategory = 'reimbursement'"
                            :class="activeCategory === 'reimbursement' ? 'bg-purple-600 text-white font-bold shadow-2xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Klaim') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'reimbursement' ? 'bg-white/25 text-white' : 'bg-purple-100 text-purple-800'"
                              x-text="counts.reimbursement"></span>
                    </button>
                </template>

                <template x-if="counts.loan > 0">
                    <button type="button"
                            @click="activeCategory = 'loan'"
                            :class="activeCategory === 'loan' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Pinjaman') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'loan' ? 'bg-white/25 text-white' : 'bg-emerald-100 text-emerald-800'"
                              x-text="counts.loan"></span>
                    </button>
                </template>

                <template x-if="counts.resignation > 0">
                    <button type="button"
                            @click="activeCategory = 'resignation'"
                            :class="activeCategory === 'resignation' ? 'bg-rose-600 text-white font-bold shadow-2xs' : 'bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Resign') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'resignation' ? 'bg-white/25 text-white' : 'bg-rose-100 text-rose-800'"
                              x-text="counts.resignation"></span>
                    </button>
                </template>

                <template x-if="counts.transition > 0">
                    <button type="button"
                            @click="activeCategory = 'transition'"
                            :class="activeCategory === 'transition' ? 'bg-teal-600 text-white font-bold shadow-2xs' : 'bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Mutasi') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'transition' ? 'bg-white/25 text-white' : 'bg-teal-100 text-teal-800'"
                              x-text="counts.transition"></span>
                    </button>
                </template>

                <template x-if="counts.adjustment > 0">
                    <button type="button"
                            @click="activeCategory = 'adjustment'"
                            :class="activeCategory === 'adjustment' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200/80 font-medium'"
                            class="px-2.5 py-1 rounded-lg transition shrink-0 cursor-pointer flex items-center gap-1">
                        <span>{{ __('Koreksi') }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full font-bold"
                              :class="activeCategory === 'adjustment' ? 'bg-white/25 text-white' : 'bg-indigo-100 text-indigo-800'"
                              x-text="counts.adjustment"></span>
                    </button>
                </template>
            </div>
        </template>

        <!-- Scrollable Notifications List -->
        <div class="max-h-[420px] overflow-y-auto divide-y divide-slate-100 bg-white">
            <!-- Loading Skeleton Indicator -->
            <template x-if="isLoading && items.length === 0">
                <div class="p-8 text-center space-y-2.5">
                    <div class="w-7 h-7 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                    <p class="text-xs text-slate-400 font-medium">{{ __('Memuat data approval...') }}</p>
                </div>
            </template>

            <!-- Notification Items -->
            <template x-for="item in filteredItems" :key="item.id">
                <a :href="item.url"
                   class="p-4 hover:bg-slate-50/80 transition-colors block cursor-pointer group">
                    <div class="flex items-start gap-3.5">
                        <!-- Left: Employee Avatar with Category Mini-Badge Icon -->
                        <div class="relative shrink-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs ring-1 ring-black/5 shadow-2xs transition-transform group-hover:scale-105"
                                 :class="{
                                     'bg-amber-100 text-amber-800': item.category === 'leave',
                                     'bg-blue-100 text-blue-800': item.category === 'overtime',
                                     'bg-purple-100 text-purple-800': item.category === 'reimbursement',
                                     'bg-emerald-100 text-emerald-800': item.category === 'loan',
                                     'bg-rose-100 text-rose-800': item.category === 'resignation',
                                     'bg-teal-100 text-teal-800': item.category === 'transition',
                                     'bg-indigo-100 text-indigo-800': item.category === 'adjustment'
                                 }">
                                <span x-text="item.employee_initials"></span>
                            </div>
                            <span class="absolute -bottom-1 -end-1 w-5 h-5 rounded-full bg-white shadow-xs border border-slate-200/80 flex items-center justify-center text-[11px] leading-none select-none"
                                  x-text="item.emoji">
                            </span>
                        </div>

                        <!-- Right: Clean, un-cramped Content -->
                        <div class="min-w-0 flex-1 space-y-1.5">
                            <!-- Row 1: Category Tag + Ticket Number + Timestamp -->
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wide uppercase whitespace-nowrap"
                                          :class="{
                                              'bg-amber-50 text-amber-700 border border-amber-200/80': item.category === 'leave',
                                              'bg-blue-50 text-blue-700 border border-blue-200/80': item.category === 'overtime',
                                              'bg-purple-50 text-purple-700 border border-purple-200/80': item.category === 'reimbursement',
                                              'bg-emerald-50 text-emerald-700 border border-emerald-200/80': item.category === 'loan',
                                              'bg-rose-50 text-rose-700 border border-rose-200/80': item.category === 'resignation',
                                              'bg-teal-50 text-teal-700 border border-teal-200/80': item.category === 'transition',
                                              'bg-indigo-50 text-indigo-700 border border-indigo-200/80': item.category === 'adjustment'
                                          }"
                                          x-text="item.category_badge || item.category_label">
                                    </span>

                                    <template x-if="item.number">
                                        <span class="text-[11px] font-mono text-slate-400 font-medium truncate"
                                              x-text="item.number">
                                        </span>
                                    </template>
                                </div>

                                <span class="text-[11px] text-slate-400 font-medium whitespace-nowrap shrink-0"
                                      x-text="item.time_ago">
                                </span>
                            </div>

                            <!-- Row 2: Title (Employee & Action) -->
                            <div>
                                <h5 class="text-xs sm:text-[13px] font-bold text-slate-900 group-hover:text-blue-600 transition leading-snug line-clamp-1"
                                    x-text="item.title">
                                </h5>
                                <template x-if="item.employee_department">
                                    <p class="text-[11px] text-slate-400 truncate mt-0.5" x-text="item.employee_department"></p>
                                </template>
                            </div>

                            <!-- Row 3: Detail Highlight Box with inline Action Link -->
                            <template x-if="item.description">
                                <div class="bg-slate-50/90 rounded-lg px-2.5 py-1.5 border border-slate-150/80 text-[11px] text-slate-700 font-medium flex items-center justify-between gap-2">
                                    <span class="truncate" x-text="item.description"></span>
                                    <span class="text-blue-600 font-semibold shrink-0 group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 transition inline-flex items-center gap-1 text-[11px]">
                                        <span x-text="item.action_label || '{{ __('Tinjau') }}'"></span>
                                        <svg class="w-3 h-3 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </span>
                                </div>
                            </template>

                            <!-- Row 4: Reason / Notes Quote -->
                            <template x-if="item.notes">
                                <div class="text-[11px] text-slate-500 italic bg-amber-50/40 border-s-2 border-amber-400/80 px-2.5 py-1 rounded-e leading-relaxed line-clamp-2"
                                     x-text="'&ldquo;' + item.notes + '&rdquo;'">
                                </div>
                            </template>
                        </div>
                    </div>
                </a>
            </template>

            <!-- Empty State -->
            <template x-if="!isLoading && filteredItems.length === 0">
                <div class="p-8 text-center space-y-2.5">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <h5 class="text-xs sm:text-sm font-bold text-slate-800">{{ __('Semua Pengajuan Telah Ditinjau') }}</h5>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto leading-relaxed">
                            {{ __('Tidak ada pengajuan yang membutuhkan persetujuan Anda saat ini.') }}
                        </p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer -->
        <div class="px-4 py-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-[11px]">
            <span class="text-slate-400 font-medium flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>{{ __('Pusat Otorisasi') }}</span>
            </span>
            <div class="flex items-center gap-2">
                @if(auth()->user()->canApprove())
                    <a href="{{ route('leaves.index', ['tab' => 'requests']) }}" class="text-blue-600 hover:text-blue-700 font-semibold transition hover:underline">
                        {{ __('Cuti') }}
                    </a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('reimbursements.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold transition hover:underline">
                        {{ __('Reimburse') }}
                    </a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('loans.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold transition hover:underline">
                        {{ __('Pinjaman') }}
                    </a>
                    <span class="text-slate-300">·</span>
                    <a href="{{ route('career-transitions.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold transition hover:underline">
                        {{ __('Mutasi') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Interactive JavaScript for Approval Notifications --}}
<script>
    function approvalNotificationMenu() {
        const initial = @js($approvalNotificationSummary ?? ['total_count' => 0, 'counts' => [], 'items' => []]);

        return {
            open: false,
            isLoading: false,
            activeCategory: 'all',
            totalCount: initial.total_count || 0,
            counts: initial.counts || {
                leave: 0,
                overtime: 0,
                adjustment: 0,
                reimbursement: 0,
                loan: 0,
                resignation: 0,
                transition: 0
            },
            items: initial.items || [],

            initComponent() {
                // Background periodic polling every 45 seconds to keep notifications fresh
                setInterval(() => {
                    if (!document.hidden) {
                        this.fetchApprovals(true);
                    }
                }, 45000);
            },

            toggleDropdown() {
                this.open = !this.open;
                if (this.open) {
                    this.fetchApprovals();
                }
            },

            get filteredItems() {
                if (this.activeCategory === 'all') {
                    return this.items;
                }
                return this.items.filter(it => it.category === this.activeCategory);
            },

            async fetchApprovals(silent = false) {
                if (!silent) this.isLoading = true;
                try {
                    const res = await fetch('{{ route('notifications.approvals') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (res.ok) {
                        const json = await res.json();
                        if (json && json.data) {
                            this.totalCount = json.data.total_count || 0;
                            this.counts = json.data.counts || this.counts;
                            this.items = json.data.items || [];
                        }
                    }
                } catch (e) {
                    console.error('Error fetching approval notifications:', e);
                } finally {
                    if (!silent) this.isLoading = false;
                }
            }
        };
    }
</script>

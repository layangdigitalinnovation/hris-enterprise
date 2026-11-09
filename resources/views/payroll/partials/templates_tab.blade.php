{{-- Tab 3: Template Payroll Dinamis & Reusable --}}
<div x-show="typeof activeTab === 'undefined' || activeTab === 'templates'" class="space-y-6" x-data="{
    templateSearch: '',
    currencyFilter: 'all',
    get filteredTemplates() {
        return @js($payrollTemplates).filter(tpl => {
            const matchesSearch = !this.templateSearch.trim() || 
                tpl.name.toLowerCase().includes(this.templateSearch.toLowerCase()) ||
                tpl.code.toLowerCase().includes(this.templateSearch.toLowerCase()) ||
                (tpl.description && tpl.description.toLowerCase().includes(this.templateSearch.toLowerCase()));
            
            const matchesCurrency = this.currencyFilter === 'all' || tpl.currency === this.currencyFilter;
            
            return matchesSearch && matchesCurrency;
        });
    }
}">

    <!-- Section Header & Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-blue-600 inline-block"></span>
                <h4 class="font-bold text-slate-900 text-base">{{ __('Template Payroll Dinamis & Komponen Gaji Reusable') }}</h4>
            </div>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('Katalog paket struktur gaji, tunjangan, dan potongan yang dapat diterapkan berulang kali ke karyawan Indonesia maupun Arab Saudi') }}</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ route('payroll.templates.create') }}" 
                style="background-color: #2563eb; color: #ffffff;"
                class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span class="text-white">{{ __('Buat Template Baru') }}</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <!-- Total Templates -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Total Template') }}</span>
                <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ $payrollTemplates->count() }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __(':active aktif digunakan', ['active' => $payrollTemplates->where('is_active', true)->count()]) }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
        </div>

        <!-- IDR Templates -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-blue-700 uppercase tracking-wider block">{{ __('Template IDR (RI)') }}</span>
                <span class="text-2xl font-bold font-mono text-blue-700 mt-1 block">{{ $payrollTemplates->where('currency', 'IDR')->count() }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Cabang Jakarta & Klinik') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center shrink-0 font-bold text-sm">
                🇮🇩
            </div>
        </div>

        <!-- SAR Templates -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">{{ __('Template SAR (KSA)') }}</span>
                <span class="text-2xl font-bold font-mono text-emerald-700 mt-1 block">{{ $payrollTemplates->where('currency', 'SAR')->count() }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Pasal 107 UU Saudi') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm">
                🇸🇦
            </div>
        </div>

        <!-- Assigned Employees -->
        @php
            $assignedEmpCount = \App\Models\Employee::whereNotNull('payroll_template_id')->count();
            $totalEmpCount = isset($employees) && method_exists($employees, 'total') 
                ? $employees->total() 
                : ($totalEmployees ?? \App\Models\Employee::where('status', 'active')->count());
        @endphp
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-purple-700 uppercase tracking-wider block">{{ __('Karyawan Terpetakan') }}</span>
                <span class="text-2xl font-bold font-mono text-purple-700 mt-1 block">{{ $assignedEmpCount }} <span class="text-xs text-slate-400 font-sans">/ {{ $totalEmpCount }}</span></span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ round(($assignedEmpCount / max(1, $totalEmpCount)) * 100) }}% terhubung template</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Search Box -->
        <div class="relative w-full sm:w-80">
            <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="templateSearch" placeholder="{{ __('Cari nama template, kode...') }}"
                class="w-full pl-10 pr-4 rtl:pr-10 rtl:pl-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
        </div>

        <!-- Currency Tabs Filter -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold self-stretch sm:self-auto justify-center">
            <button type="button" @click="currencyFilter = 'all'"
                :class="currencyFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-500 hover:text-slate-800'"
                class="px-3 py-1.5 rounded-lg transition cursor-pointer">
                {{ __('Semua Mata Uang') }}
            </button>
            <button type="button" @click="currencyFilter = 'IDR'"
                :class="currencyFilter === 'IDR' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1 cursor-pointer">
                <span>🇮🇩</span>
                <span>IDR (Rupiah)</span>
            </button>
            <button type="button" @click="currencyFilter = 'SAR'"
                :class="currencyFilter === 'SAR' ? 'bg-white text-emerald-700 shadow-2xs font-bold' : 'text-slate-500 hover:text-slate-800'"
                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1 cursor-pointer">
                <span>🇸🇦</span>
                <span>SAR (Saudi Riyal)</span>
            </button>
        </div>
    </div>

    <!-- Template Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($payrollTemplates as $template)
            @php
                $isSar = $template->currency === 'SAR';
                $sim = $template->estimated_simulation;
                $empCount = $template->employees_count;
            @endphp
            <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between relative group"
                x-show="(!templateSearch.trim() || '{{ strtolower($template->name . ' ' . $template->code . ' ' . $template->description) }}'.includes(templateSearch.toLowerCase())) && (currencyFilter === 'all' || currencyFilter === '{{ $template->currency }}')">
                
                <div>
                    <!-- Card Top Header -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold border uppercase
                                    {{ $isSar ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-blue-50 text-blue-800 border-blue-300' }}">
                                    {{ $isSar ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                                </span>
                                <span class="font-mono text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                                    {{ $template->code }}
                                </span>
                                @if($template->is_default)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ __('Default') }}
                                    </span>
                                @endif
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $template->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $template->is_active ? __('Aktif') : __('Non-Aktif') }}
                                </span>
                            </div>

                            <h4 class="font-extrabold text-slate-900 text-base group-hover:text-blue-600 transition">
                                <a href="{{ route('payroll.templates.show', $template) }}">
                                    {{ $template->name }}
                                </a>
                            </h4>
                        </div>

                        <!-- Target Badge -->
                        @if($template->branch)
                            <span class="text-[11px] px-2.5 py-1 rounded-xl bg-slate-50 border border-slate-200 font-medium text-slate-700 shrink-0 flex items-center gap-1">
                                <span>{{ $template->branch->country_flag }}</span>
                                <span class="truncate max-w-[120px]">{{ $template->branch->name }}</span>
                            </span>
                        @endif
                    </div>

                    <!-- Description -->
                    <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                        {{ $template->description ?: __('Tidak ada deskripsi khusus.') }}
                    </p>

                    <!-- Financial Breakdown Box -->
                    <div class="mt-4 p-4 rounded-2xl bg-slate-50/80 border border-slate-200/70 space-y-2.5">
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-500 font-sans">{{ __('Gaji Pokok Acuan:') }}</span>
                            <span class="font-bold text-slate-900 text-sm">
                                {{ format_currency($template->base_salary, $template->currency) }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/60 text-[11px] font-mono">
                            <div>
                                <span class="text-slate-400 font-sans block text-[10px] uppercase font-semibold">{{ __('Tunjangan Terpasang') }}</span>
                                <span class="text-emerald-700 font-bold block mt-0.5">
                                    +{{ format_currency($sim['total_allowances'], $template->currency) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-slate-400 font-sans block text-[10px] uppercase font-semibold">{{ __('Potongan Terpasang') }}</span>
                                <span class="text-rose-600 font-bold block mt-0.5">
                                    -{{ format_currency($sim['total_deductions'], $template->currency) }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/60 flex items-center justify-between text-xs">
                            <span class="text-slate-600 font-semibold font-sans">{{ __('Estimasi Gaji Bersih (THP):') }}</span>
                            <span class="font-bold font-mono text-blue-700 text-sm">
                                {{ format_currency($sim['net_salary'], $template->currency) }}
                            </span>
                        </div>
                    </div>

                    <!-- Included Components Chips -->
                    <div class="mt-4 space-y-1.5">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">
                            {{ __('Komponen Gaji (:count)', ['count' => $template->items->count()]) }}
                        </span>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($template->items->take(5) as $item)
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-medium border
                                    {{ $item->type === 'earning' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-800 border-rose-200' }}">
                                    {{ $item->component->name }}: {{ $item->formatted_rate }}
                                </span>
                            @endforeach
                            @if($template->items->count() > 5)
                                <span class="px-2 py-0.5 rounded-lg text-[10px] bg-slate-100 text-slate-600 font-medium">
                                    +{{ $template->items->count() - 5 }} {{ __('lainnya') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Bottom Footer & Actions -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-[11px]">
                            👥
                        </div>
                        <span class="text-slate-600 font-semibold text-[11px]">
                            {{ __(':count Karyawan Aktif', ['count' => $empCount]) }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <!-- Duplicate Button -->
                        <form action="{{ route('payroll.templates.duplicate', $template) }}" method="POST" onsubmit="return confirm('{{ __('Duplikasi template ini?') }}')">
                            @csrf
                            <button type="submit" title="{{ __('Duplikasi Template') }}" class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </form>

                        <!-- Edit Button -->
                        <a href="{{ route('payroll.templates.edit', $template) }}" class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition cursor-pointer" title="{{ __('Edit Template') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </a>

                        <!-- Delete Button -->
                        <form action="{{ route('payroll.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('{{ __('Hapus template :name? Karyawan yang terhubung akan dilepaskan tanpa menghapus nominal gajinya.', ['name' => addslashes($template->name)]) }}')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="redirect_to" value="{{ url()->full() }}">
                            <button type="submit" title="{{ __('Hapus Template') }}" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 hover:text-rose-700 border border-rose-200 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>

                        <!-- Detail & Apply Button -->
                        <a href="{{ route('payroll.templates.show', $template) }}" class="px-3.5 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-1 shadow-2xs">
                            <span>{{ __('Buka & Terapkan') }}</span>
                            <span class="rtl:rotate-180">&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 py-12 text-center bg-white rounded-3xl border border-slate-200/80 text-slate-400">
                <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                <h5 class="font-bold text-slate-700 text-sm">{{ __('Belum Ada Template Payroll') }}</h5>
                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">{{ __('Buat template pertama Anda untuk mempercepat penataan gaji karyawan secara standar dan reusable.') }}</p>
                <div class="mt-4">
                    <a href="{{ route('payroll.templates.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-semibold text-xs shadow-xs hover:bg-blue-700 transition">
                        {{ __('Buat Template Sekarang') }}
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>

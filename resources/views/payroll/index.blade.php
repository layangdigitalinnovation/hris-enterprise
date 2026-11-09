@extends('layouts.app', ['headerTitle' => __('Manajemen Penggajian (Payroll)'), 'title' => __('Penggajian')])

@section('content')
@php
    $currentTab = request('tab');
    if (!$currentTab) {
        if (request()->has('emp_page') || request()->has('emp_search')) {
            $currentTab = 'salaries';
        } elseif (request()->has('period_page') || request()->has('period_search')) {
            $currentTab = 'periods';
        } else {
            $currentTab = 'periods';
        }
    }
@endphp
<div class="space-y-8" x-data="{ 
    activeTab: '{{ $currentTab }}'
}">

    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Sistem Penggajian & Slip Gaji Karyawan') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Penggajian terintegrasi presensi mesin Solution, lembur, serta kebijakan tunjangan & potongan perusahaan') }}</p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('payroll.exchange-rate.edit', ['redirect_to' => url()->current()]) }}" 
                class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-emerald-50/50 text-slate-700 hover:text-emerald-800 border border-slate-200 hover:border-emerald-300 font-semibold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer group">
                <span class="text-sm">💱</span>
                <span>{{ __('Kurs SAR ⇄ IDR') }}</span>
                <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 group-hover:bg-emerald-100 transition">
                    Rp {{ number_format(\App\Helpers\CurrencyHelper::getSarToIdrRate(), 0, ',', '.') }}
                </span>
            </a>

            <a href="{{ route('payroll.components.create') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Komponen') }}
            </a>

            <a href="{{ route('payroll.periods.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Buat Periode Baru') }}
            </a>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2">
        <button type="button" @click="activeTab = 'periods'" 
            :class="activeTab === 'periods' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ __('Periode Payroll') }} ({{ $periods->total() }})
        </button>

        <button type="button" @click="activeTab = 'salaries'" 
            :class="activeTab === 'salaries' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            {{ __('Gaji & Kompensasi Karyawan') }} ({{ $employees->total() }})
        </button>

        <button type="button" @click="activeTab = 'templates'" 
            :class="activeTab === 'templates' ? 'border-b-2 border-purple-600 text-purple-700 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            {{ __('Template Payroll') }} ({{ $payrollTemplates->count() }})
        </button>

        <button type="button" @click="activeTab = 'components'" 
            :class="activeTab === 'components' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            {{ __('Master Tunjangan & Potongan') }} ({{ $salaryComponents->count() }})
        </button>
    </div>

    <!-- TAB 1: PERIODE PAYROLL -->
    <div x-show="activeTab === 'periods'" class="space-y-6">
        <!-- Payroll Periods Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse ($periods as $period)
                <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm relative overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">{{ __('Periode') }}</span>
                                <h4 class="font-extrabold text-slate-900 text-base mt-0.5">{{ $period->name }}</h4>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold border
                                {{ $period->status === 'locked' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($period->status === 'calculated' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                {{ __(ucfirst($period->status)) }}
                            </span>
                        </div>

                        <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 font-mono text-xs space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-sans">{{ __('Rentang Waktu:') }}</span>
                                <span class="text-slate-900">{{ $period->start_date->format('d/m') }} - {{ $period->end_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-sans">{{ __('Cut-Off Absensi:') }}</span>
                                <span class="text-amber-600">{{ $period->cut_off_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-sans">{{ __('Tanggal Bayar:') }}</span>
                                <span class="text-emerald-600">{{ $period->payment_date->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-200">
                                <span class="text-slate-500 font-sans">{{ __('Jumlah Karyawan:') }}</span>
                                <span class="text-slate-900 font-bold">{{ __(':count Orang', ['count' => $period->items_count]) }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                        <a href="{{ route('payroll.show', $period) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm shadow-blue-500/20 transition flex items-center gap-1.5">
                            {{ __('Buka Detail & Kalkulasi →') }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 rounded-3xl bg-white border border-slate-200/80 text-slate-500">
                    {{ __('Belum ada periode payroll yang dibuat. Klik tombol "Buat Periode Baru" di atas untuk memulai.') }}
                </div>
            @endforelse
        </div>

        <!-- Pagination for Periods -->
        @if ($periods->hasPages())
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                {{ $periods->links() }}
            </div>
        @elseif ($periods->total() > 0)
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>
                    {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $periods->total() }}</strong> {{ __('periode payroll') }}
                </span>
                <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
            </div>
        @endif
    </div>

    <!-- TAB 2: GAJI & KOMPENSASI KARYAWAN -->
    <div x-show="activeTab === 'salaries'" class="space-y-6">
        <!-- Top Toolbar & Stats Control Bar -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4">
            <!-- Left: Title, Description & Stats Badges -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="font-bold text-slate-900 text-sm tracking-tight flex items-center gap-2">
                        <span>💼</span>
                        <span>{{ __('Gaji & Kompensasi Karyawan') }}</span>
                    </h4>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ __(':count Karyawan', ['count' => $employees->total()]) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                        {{ __(':count Terhubung Template', ['count' => \App\Models\Employee::whereNotNull('payroll_template_id')->count()]) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">
                    {{ __('Kelola gaji pokok, tunjangan khusus, serta keterhubungan template penggajian per karyawan.') }}
                </p>
            </div>

            <!-- Right: Search Form & Action -->
            <div class="flex items-center gap-2.5 flex-wrap sm:flex-nowrap">
                <form method="GET" action="{{ route('payroll.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="hidden" name="tab" value="salaries">
                    @if(request('period_page'))
                        <input type="hidden" name="period_page" value="{{ request('period_page') }}">
                    @endif
                    <div class="relative w-full sm:w-72">
                        <svg style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="emp_search" value="{{ request('emp_search') }}" placeholder="{{ __('Cari nama, NIK, jabatan...') }}"
                            class="w-full pl-9 pr-9 rtl:pr-9 rtl:pl-9 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        @if(request('emp_search'))
                            <a href="{{ route('payroll.index', array_merge(request()->except('emp_search', 'emp_page'), ['tab' => 'salaries'])) }}"
                               style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                               class="text-slate-400 hover:text-slate-600 p-0.5 rounded hover:bg-slate-200 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus pencarian') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>
                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1 cursor-pointer shrink-0">
                        {{ __('Cari') }}
                    </button>
                    @if(request('emp_search'))
                        <a href="{{ route('payroll.index', array_merge(request()->except('emp_search', 'emp_page'), ['tab' => 'salaries'])) }}" class="px-3 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs border border-slate-200 shrink-0">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </form>

                <button type="button" @click="activeTab = 'templates'" 
                    class="px-3.5 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold text-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer" title="{{ __('Buka Tab Template') }}">
                    <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span class="hidden sm:inline">{{ __('Kelola Template') }}</span>
                </button>
            </div>
        </div>

        <!-- Table Karyawan & Gaji -->
        <div class="border border-slate-200 rounded-3xl overflow-hidden bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50/70 font-semibold text-[11px]">
                            <th class="py-3.5 px-4">{{ __('Karyawan') }}</th>
                            <th class="py-3.5 px-4">{{ __('Departemen & Posisi') }}</th>
                            <th class="py-3.5 px-4">{{ __('Template Payroll') }}</th>
                            <th class="py-3.5 px-4">{{ __('Gaji Pokok') }}</th>
                            <th class="py-3.5 px-4">{{ __('Tunjangan Khusus') }}</th>
                            <th class="py-3.5 px-4">{{ __('Potongan Khusus') }}</th>
                            <th class="py-3.5 px-4">{{ __('Rekening Bank') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($employees as $emp)
                            @php
                                $empCurr = $emp->effective_currency;
                                $basicRecord = $emp->compensations->firstWhere('salary_component_id', $basicComponent?->id);
                                $defaultBasic = ($empCurr === 'SAR') ? 6000 : 5000000;
                                $basicAmount = $basicRecord ? (float)$basicRecord->amount : $defaultBasic;

                                // Filter custom active earnings (> 0, non-basic)
                                $customEarnings = $emp->compensations->filter(fn($c) => 
                                    $c->component && 
                                    $c->component->type === 'earning' && 
                                    $c->component->code !== 'BASIC' &&
                                    (float)$c->amount > 0
                                );

                                // Filter custom active deductions (> 0)
                                $customDeductions = $emp->compensations->filter(fn($c) => 
                                    $c->component && 
                                    $c->component->type === 'deduction' &&
                                    (float)$c->amount > 0
                                );
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <!-- Karyawan -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-blue-100 to-indigo-100 text-blue-700 border border-blue-200/60 flex items-center justify-center font-bold text-xs shrink-0 uppercase shadow-2xs">
                                            {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name ?? '', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('employees.show', $emp) }}" class="font-bold text-slate-900 hover:text-blue-600 transition block truncate text-xs">
                                                {{ $emp->full_name }}
                                            </a>
                                            <span class="font-mono text-[10px] text-slate-400 block tracking-tight">{{ $emp->employee_code }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Departemen & Posisi -->
                                <td class="py-3.5 px-4">
                                    <span class="text-slate-800 font-semibold block text-xs truncate max-w-[170px]">{{ $emp->department->name ?? '-' }}</span>
                                    <div class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5 truncate max-w-[170px]">
                                        <span>{{ $emp->position->title ?? '-' }}</span>
                                        @if($emp->branch)
                                            <span class="text-slate-400">·</span>
                                            <span class="text-slate-500">{{ $emp->branch->name }}</span>
                                            <span class="text-[11px]">{{ $emp->branch->country_flag }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Template Payroll -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($emp->payrollTemplate)
                                        <div class="space-y-0.5">
                                            <a href="{{ route('payroll.templates.show', $emp->payrollTemplate) }}" 
                                               class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-bold border transition shadow-2xs
                                               {{ $emp->payrollTemplate->currency === 'SAR' ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-purple-50 text-purple-800 border-purple-200 hover:bg-purple-100' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $emp->payrollTemplate->currency === 'SAR' ? 'bg-emerald-500' : 'bg-purple-500' }}"></span>
                                                <span class="font-mono">{{ $emp->payrollTemplate->code }}</span>
                                            </a>
                                            <span class="block text-[10px] text-slate-400 truncate max-w-[130px]" title="{{ $emp->payrollTemplate->name }}">
                                                {{ $emp->payrollTemplate->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium bg-slate-100 text-slate-500 border border-slate-200/80">
                                            {{ __('Manual / Kustom') }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Gaji Pokok -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold border uppercase tracking-wider
                                            {{ $empCurr === 'SAR' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                            {{ $empCurr }}
                                        </span>
                                        <span class="font-mono font-bold text-slate-900 text-xs">
                                            {{ format_currency($basicAmount, $empCurr) }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Tunjangan Khusus -->
                                <td class="py-3.5 px-4">
                                    @if($customEarnings->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($customEarnings as $ce)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                                    {{ $ce->component->name }}: {{ format_currency($ce->amount, $empCurr) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($emp->payrollTemplate && $emp->payrollTemplate->earnings->count() > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-emerald-50/60 text-emerald-700 border border-emerald-200/60">
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>{{ __('Paket Template (:count)', ['count' => $emp->payrollTemplate->earnings->count()]) }}</span>
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-slate-400">{{ __('Standar Perusahaan') }}</span>
                                    @endif
                                </td>

                                <!-- Potongan Khusus -->
                                <td class="py-3.5 px-4">
                                    @if($customDeductions->count() > 0)
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($customDeductions as $cd)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-rose-50 text-rose-800 border border-rose-200/80">
                                                    {{ $cd->component->name }}: {{ format_currency($cd->amount, $empCurr) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                                            <span>{{ __('BPJS & Presensi Standar') }}</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Rekening Bank -->
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($emp->bank_account_no)
                                        <div class="space-y-0.5">
                                            <span class="font-bold text-slate-800 text-[11px] block">{{ $emp->bank_name ?? 'BCA' }}</span>
                                            <span class="font-mono text-[11px] text-slate-500 block tracking-tight">{{ $emp->bank_account_no }}</span>
                                        </div>
                                    @else
                                        <div class="space-y-0.5">
                                            <span class="font-semibold text-slate-600 text-[11px] block">{{ $emp->bank_name ?? 'BCA' }}</span>
                                            <span class="text-[10px] text-slate-400 italic block">{{ __('No Rek: -') }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="{{ route('payroll.compensations.edit', $emp) }}" 
                                        class="px-3 py-1.5 rounded-xl bg-slate-50 hover:bg-blue-600 text-slate-700 hover:text-white font-semibold text-xs border border-slate-200 hover:border-blue-600 transition flex items-center gap-1.5 ml-auto w-fit shadow-2xs group cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        <span>{{ __('Atur Gaji') }}</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400 font-sans">
                                    @if(request('emp_search'))
                                        <div class="space-y-2">
                                            <div class="text-sm font-semibold text-slate-700">{{ __('Karyawan Tidak Ditemukan') }}</div>
                                            <p class="text-xs text-slate-500">{{ __('Tidak ada data karyawan yang cocok dengan kata kunci') }} "{{ request('emp_search') }}".</p>
                                            <div class="pt-2">
                                                <a href="{{ route('payroll.index', array_merge(request()->except('emp_search', 'emp_page'), ['tab' => 'salaries'])) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    {{ __('Reset Pencarian') }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        {{ __('Belum ada data karyawan aktif.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination for Employees -->
            @if ($employees->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $employees->links() }}
                </div>
            @elseif ($employees->total() > 0)
                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium bg-slate-50/50">
                    <span>
                        {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $employees->total() }}</strong> {{ __('karyawan') }}
                        @if(request('emp_search'))
                            <span class="text-blue-700 font-normal">({{ __('hasil pencarian') }}: "{{ request('emp_search') }}")</span>
                        @endif
                    </span>
                    <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 3: TEMPLATE PAYROLL DINAMIS & REUSABLE -->
    @include('payroll.partials.templates_tab')

    <!-- TAB 4: MASTER TUNJANGAN & POTONGAN PERUSAHAAN -->
    <div x-show="activeTab === 'components'" class="space-y-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-slate-900 text-base">{{ __('Kebijakan Tunjangan & Potongan Perusahaan') }}</h4>
                <p class="text-xs text-slate-500">{{ __('Konfigurasi formula tunjangan dan potongan yang berlaku pada penggajian perusahaan') }}</p>
            </div>

            <a href="{{ route('payroll.components.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Komponen Baru') }}
            </a>
        </div>

        <!-- Section A: Tunjangan (Earnings) -->
        <div class="border border-slate-200 rounded-3xl overflow-hidden bg-white shadow-xs space-y-2">
            <div class="p-4 bg-emerald-50/50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                    <h5 class="font-bold text-slate-900 text-xs">{{ __('Komponen Tunjangan (Penghasilan / Earnings)') }}</h5>
                </div>
                <span class="text-[11px] text-emerald-800 font-semibold">{{ __(':count Komponen', ['count' => $allowances->count()]) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-white">
                            <th class="py-2.5 px-4 font-semibold">{{ __('Kode & Nama Tunjangan') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Metode Perhitungan') }}</th>
                            <th class="py-2.5 px-4 font-semibold font-mono">{{ __('Nilai / Tarif Standar') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Kena Pajak') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Status') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Keterangan') }}</th>
                            <th class="py-2.5 px-4 font-semibold text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($allowances as $c)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $c->name }}</span>
                                    <span class="font-mono text-[10px] text-blue-600 bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">{{ $c->code }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium 
                                        {{ $c->calculation_mode === 'percentage' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($c->calculation_mode === 'per_attendance' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-slate-100 text-slate-700') }}">
                                        @if($c->calculation_mode === 'percentage')
                                            {{ __('Persentase (% Gaji Pokok)') }}
                                        @elseif($c->calculation_mode === 'per_attendance')
                                            {{ __('Per Hari Kehadiran') }}
                                        @else
                                            {{ __('Nominal Tetap Bulanan') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    @if($c->calculation_mode === 'percentage')
                                        {{ number_format($c->rate_value, 0) }}%
                                    @elseif($c->calculation_mode === 'per_attendance')
                                        {{ format_currency($c->rate_value) }} {{ __('/ hari') }}
                                    @else
                                        {{ format_currency($c->rate_value > 0 ? $c->rate_value : $c->default_amount) }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-[11px] {{ $c->is_taxable ? 'text-amber-700 font-semibold' : 'text-slate-500' }}">
                                        {{ $c->is_taxable ? __('Kena PPh 21') : __('Non-Pajak') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $c->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $c->is_active ? __('Aktif') : __('Nonaktif') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px] max-w-xs truncate">
                                    {{ $c->description ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('payroll.components.edit', $c) }}" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition" title="{{ __('Edit Kebijakan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>

                                        @if($c->code !== 'BASIC')
                                            <form action="{{ route('payroll.components.destroy', $c) }}" method="POST" onsubmit="return confirm('{{ __('Hapus komponen :name?', ['name' => addslashes($c->name)]) }}');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="{{ __('Hapus') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section B: Potongan (Deductions) -->
        <div class="border border-slate-200 rounded-3xl overflow-hidden bg-white shadow-xs space-y-2">
            <div class="p-4 bg-rose-50/50 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-500 inline-block"></span>
                    <h5 class="font-bold text-slate-900 text-xs">{{ __('Komponen Potongan (Pengurangan Gaji / Deductions)') }}</h5>
                </div>
                <span class="text-[11px] text-rose-800 font-semibold">{{ __(':count Komponen', ['count' => $deductions->count()]) }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-white">
                            <th class="py-2.5 px-4 font-semibold">{{ __('Kode & Nama Potongan') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Metode Perhitungan') }}</th>
                            <th class="py-2.5 px-4 font-semibold font-mono">{{ __('Nilai / Tarif Standar') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Status') }}</th>
                            <th class="py-2.5 px-4 font-semibold">{{ __('Keterangan') }}</th>
                            <th class="py-2.5 px-4 font-semibold text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach ($deductions as $c)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $c->name }}</span>
                                    <span class="font-mono text-[10px] text-rose-600 bg-rose-50 px-1.5 py-0.2 rounded border border-rose-200">{{ $c->code }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-medium 
                                        {{ $c->calculation_mode === 'percentage' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($c->calculation_mode === 'per_late_minute' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-700') }}">
                                        @if($c->calculation_mode === 'percentage')
                                            {{ __('Persentase (% Gaji Pokok)') }}
                                        @elseif($c->calculation_mode === 'per_late_minute')
                                            {{ __('Per Menit Terlambat Presensi') }}
                                        @else
                                            {{ __('Nominal Tetap Bulanan') }}
                                        @endif
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                    @if($c->calculation_mode === 'percentage')
                                        {{ number_format($c->rate_value, 0) }}%
                                    @elseif($c->calculation_mode === 'per_late_minute')
                                        {{ format_currency($c->rate_value) }} {{ __('/ menit') }}
                                    @else
                                        {{ format_currency($c->rate_value > 0 ? $c->rate_value : $c->default_amount) }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $c->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                        {{ $c->is_active ? __('Aktif') : __('Nonaktif') }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px] max-w-xs truncate">
                                    {{ $c->description ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('payroll.components.edit', $c) }}" class="p-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition" title="{{ __('Edit Kebijakan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>

                                        <form action="{{ route('payroll.components.destroy', $c) }}" method="POST" onsubmit="return confirm('{{ __('Hapus komponen :name?', ['name' => addslashes($c->name)]) }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="{{ __('Hapus') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

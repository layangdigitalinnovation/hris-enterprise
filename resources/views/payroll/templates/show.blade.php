@extends('layouts.app', ['headerTitle' => $payrollTemplate->name, 'title' => __('Detail Template Payroll')])

@section('content')
@php
    $isSar = $payrollTemplate->currency === 'SAR';
    $sim = $payrollTemplate->estimated_simulation;
@endphp

<div class="space-y-6" x-data="{
    applySearch: '',
    deptFilter: '',
    branchFilter: '',
    selectAll: false,
    selectedEmployees: [],
    toggleAll(availableIds) {
        if (this.selectAll) {
            this.selectedEmployees = [...availableIds];
        } else {
            this.selectedEmployees = [];
        }
    }
}">

    <!-- Breadcrumb Navigation -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('payroll.index', ['tab' => 'templates']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
            <span>/</span>
            <a href="{{ route('payroll.templates.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Template Payroll') }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">{{ $payrollTemplate->name }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('payroll.index', ['tab' => 'templates']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Kembali ke Hub Penggajian') }}
            </a>
        </div>
    </div>

    <!-- Main Overview Header Banner -->
    <div class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-2.5 py-1 rounded-lg font-mono text-xs font-bold border uppercase
                    {{ $isSar ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : 'bg-blue-50 text-blue-800 border-blue-300' }}">
                    {{ $isSar ? __('🇸🇦 SAR (Saudi Riyal)') : __('🇮🇩 IDR (Rupiah)') }}
                </span>
                <span class="font-mono text-xs font-bold text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg">
                    {{ $payrollTemplate->code }}
                </span>
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $payrollTemplate->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                    {{ $payrollTemplate->is_active ? __('Aktif') : __('Non-Aktif') }}
                </span>
                @if($payrollTemplate->is_default)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                        {{ __('Default') }}
                    </span>
                @endif
                @if($payrollTemplate->branch)
                    <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 text-xs font-medium text-slate-700 flex items-center gap-1">
                        <span>{{ $payrollTemplate->branch->country_flag }}</span>
                        <span>{{ $payrollTemplate->branch->name }}</span>
                    </span>
                @endif
            </div>

            <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                {{ $payrollTemplate->name }}
            </h3>

            <p class="text-xs text-slate-500 max-w-2xl leading-relaxed">
                {{ $payrollTemplate->description ?: __('Template struktur kompensasi standar perusahaan.') }}
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <!-- Edit Template -->
            <a href="{{ route('payroll.templates.edit', $payrollTemplate) }}" 
                class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                <span>{{ __('Edit Template') }}</span>
            </a>

            <!-- Duplicate / Clone -->
            <form action="{{ route('payroll.templates.duplicate', $payrollTemplate) }}" method="POST" onsubmit="return confirm('Duplikasi template ini?')">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs border border-slate-200 shadow-2xs transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>{{ __('Duplikasi') }}</span>
                </button>
            </form>

            <!-- Delete Template -->
            <form action="{{ route('payroll.templates.destroy', $payrollTemplate) }}" method="POST" onsubmit="return confirm('Hapus template ini? Karyawan yang terhubung akan dilepaskan tanpa menghapus nominal gajinya.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs border border-rose-200 transition cursor-pointer" title="{{ __('Hapus Template') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </form>
        </div>
    </div>

    <!-- 4 Financial Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Gaji Pokok -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Gaji Pokok Acuan') }}</span>
            <div class="text-xl font-bold font-mono text-slate-900 mt-2">
                {{ format_currency($payrollTemplate->base_salary, $payrollTemplate->currency) }}
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Dasar perhitungan persentase & lembur') }}</span>
        </div>

        <!-- 2. Tunjangan Terpasang -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">{{ __('Total Tunjangan (Est. 22 Hari)') }}</span>
            <div class="text-xl font-bold font-mono text-emerald-600 mt-2">
                +{{ format_currency($sim['total_allowances'], $payrollTemplate->currency) }}
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ __(':count komponen penghasilan', ['count' => $payrollTemplate->earnings->count()]) }}</span>
        </div>

        <!-- 3. Potongan Terpasang -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
            <span class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider block">{{ __('Total Potongan Terpasang') }}</span>
            <div class="text-xl font-bold font-mono text-rose-600 mt-2">
                -{{ format_currency($sim['total_deductions'], $payrollTemplate->currency) }}
            </div>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ __(':count komponen potongan', ['count' => $payrollTemplate->deductions->count()]) }}</span>
        </div>

        <!-- 4. Estimasi Net THP -->
        <div class="p-5 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-md shadow-blue-500/20">
            <span class="text-[11px] font-semibold text-blue-100 uppercase tracking-wider block">{{ __('Estimasi Take-Home Pay (Net)') }}</span>
            <div class="text-xl font-extrabold font-mono text-white mt-2">
                {{ format_currency($sim['net_salary'], $payrollTemplate->currency) }}
            </div>
            <span class="text-[10px] text-blue-200 mt-1 block">{{ __(':count karyawan terhubung saat ini', ['count' => $payrollTemplate->employees->count()]) }}</span>
        </div>
    </div>

    <!-- Section: Rincian Komponen Gaji dalam Template -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span>📋</span>
                    <span>{{ __('Rincian Komponen Gaji Template (:count)', ['count' => $payrollTemplate->items->count()]) }}</span>
                </h4>
                <p class="text-xs text-slate-400">{{ __('Daftar tunjangan dan potongan yang otomatis dipaketkan dalam template ini') }}</p>
            </div>

            <a href="{{ route('payroll.templates.edit', $payrollTemplate) }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                {{ __('Ubah Komponen') }} &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50/60 font-semibold text-[10px]">
                        <th class="py-2.5 px-3">#</th>
                        <th class="py-2.5 px-3">{{ __('Komponen') }}</th>
                        <th class="py-2.5 px-3">{{ __('Tipe') }}</th>
                        <th class="py-2.5 px-3">{{ __('Metode Perhitungan') }}</th>
                        <th class="py-2.5 px-3 font-mono">{{ __('Tarif / Nilai') }}</th>
                        <th class="py-2.5 px-3 text-center">{{ __('Tunjangan Tetap (Lembur)') }}</th>
                        <th class="py-2.5 px-3 text-center">{{ __('Kena Pajak') }}</th>
                        <th class="py-2.5 px-3">{{ __('Catatan') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payrollTemplate->items as $idx => $item)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="py-3 px-3 font-mono text-slate-400">{{ $idx + 1 }}</td>
                            <td class="py-3 px-3">
                                <span class="font-bold text-slate-900 block">{{ $item->component->name }}</span>
                                <span class="font-mono text-[10px] text-slate-400 block">{{ $item->component->code }}</span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold border
                                    {{ $item->type === 'earning' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                    {{ $item->type === 'earning' ? __('Penghasilan (+)') : __('Potongan (-)') }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span class="font-medium text-slate-700">{{ $item->calculation_mode_label }}</span>
                            </td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                {{ $item->formatted_rate }}
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($item->type === 'earning' && $item->is_fixed)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700" title="{{ __('Diperhitungkan dalam rumus upah lembur Pasal 107 KSA & Depnaker') }}">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ __('Ya') }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-center">
                                @if($item->type === 'earning')
                                    @if($item->is_taxable)
                                        <span class="text-blue-700 font-semibold text-xs">PPh 21</span>
                                    @else
                                        <span class="text-slate-400 text-xs">{{ __('Bebas') }}</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-3 text-slate-500 text-[11px]">
                                {{ $item->notes ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-6 text-center text-slate-400 text-xs">
                                {{ __('Belum ada komponen yang ditambahkan pada template ini.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section: Karyawan yang Menggunakan Template Ini & Form Mass Assignment -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: Karyawan Terhubung Saat Ini (Col 6) -->
        <div class="lg:col-span-6 p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span>👥</span>
                        <span>{{ __('Karyawan Terhubung (:count)', ['count' => $payrollTemplate->employees->count()]) }}</span>
                    </h4>
                    <p class="text-xs text-slate-400">{{ __('Daftar pegawai yang saat ini menggunakan template kompensasi ini') }}</p>
                </div>
            </div>

            <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
                @forelse ($payrollTemplate->employees as $emp)
                    <div class="p-3 rounded-2xl bg-slate-50/70 border border-slate-200/70 flex items-center justify-between text-xs hover:bg-slate-100/70 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                                {{ substr($emp->first_name, 0, 1) }}{{ substr($emp->last_name ?? '', 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('employees.show', $emp) }}" class="font-bold text-slate-900 hover:text-blue-600 block truncate">
                                    {{ $emp->full_name }}
                                </a>
                                <p class="text-[10px] text-slate-500 truncate">
                                    {{ $emp->employee_code }} · {{ $emp->department->name ?? '-' }} ({{ $emp->branch->name ?? '-' }})
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('payroll.compensations.edit', $emp) }}" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-slate-700 hover:text-blue-600 font-semibold text-[11px] transition shadow-2xs">
                                {{ __('Atur Gaji') }}
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-xs text-slate-400">
                        <p>{{ __('Belum ada karyawan yang terhubung ke template ini.') }}</p>
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('Gunakan formulir di sebelah kanan untuk menerapkan template ke karyawan.') }}</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Wizard Terapkan Template ke Karyawan Lain (Col 6) -->
        <div class="lg:col-span-6 p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span>⚡</span>
                    <span>{{ __('Terapkan Template ke Karyawan Lain') }}</span>
                </h4>
                <p class="text-xs text-slate-400">{{ __('Pilih satu atau banyak karyawan untuk otomatis menerapkan seluruh struktur gaji dari template ini') }}</p>
            </div>

            <!-- Filter Controls for Employee Selection -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                <div>
                    <input type="text" x-model="applySearch" placeholder="{{ __('Cari nama / NIK...') }}"
                        class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <select x-model="deptFilter" class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">{{ __('-- Semua Departemen --') }}</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <form action="{{ route('payroll.templates.apply', $payrollTemplate) }}" method="POST" class="space-y-4">
                @csrf

                <!-- Select All Bar -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs font-semibold text-slate-700">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" x-model="selectAll" 
                            @change="toggleAll({{ json_encode($availableEmployees->pluck('id')) }})" 
                            class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                        <span>{{ __('Pilih Semua Karyawan') }}</span>
                    </label>
                    <span class="text-blue-700 font-mono text-[11px]" x-text="'{{ __('dipilih:') }} ' + selectedEmployees.length"></span>
                </div>

                <!-- Available Employees List Scrollable -->
                <div class="space-y-1.5 max-h-[220px] overflow-y-auto border border-slate-100 rounded-2xl p-2 bg-slate-50/40 text-xs">
                    @foreach ($availableEmployees as $emp)
                        <label class="p-2 rounded-xl bg-white border border-slate-200/60 flex items-center justify-between gap-2 hover:bg-blue-50/50 transition cursor-pointer"
                            x-show="(!applySearch.trim() || '{{ strtolower($emp->full_name . ' ' . $emp->employee_code) }}'.includes(applySearch.toLowerCase())) && (!deptFilter || '{{ $emp->department_id }}' == deptFilter)">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" x-model="selectedEmployees"
                                    class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                <div class="min-w-0">
                                    <span class="font-bold text-slate-900 block truncate">{{ $emp->full_name }}</span>
                                    <span class="text-[10px] text-slate-500 block truncate">{{ $emp->employee_code }} · {{ $emp->department->name ?? '-' }}</span>
                                </div>
                            </div>

                            <span class="text-[10px] font-mono px-2 py-0.5 rounded shrink-0 {{ $emp->payroll_template_id == $payrollTemplate->id ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold' : 'bg-slate-100 text-slate-600' }}">
                                {{ $emp->payrollTemplate ? $emp->payrollTemplate->code : __('Tanpa Template') }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <!-- Overwrite Checkbox -->
                <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-xs text-blue-800 space-y-1">
                    <label class="flex items-start gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="overwrite_existing" value="1" checked class="w-4 h-4 text-blue-600 rounded border-blue-300 focus:ring-blue-500 mt-0.5">
                        <span class="font-medium text-[11px] leading-tight">
                            {{ __('Timpa komponen gaji yang telah disetel sebelumnya dengan nilai standar dari template ini.') }}
                        </span>
                    </label>
                </div>

                <button type="submit" :disabled="selectedEmployees.length === 0" style="background-color: #2563eb; color: #ffffff;"
                    class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-white">{{ __('Terapkan Template Sekarang') }}</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection


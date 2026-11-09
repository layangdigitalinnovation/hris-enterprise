@extends('layouts.app', ['headerTitle' => __('Laporan Gaji & Rekap Payroll'), 'title' => __('Laporan Gaji')])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Breadcrumb (HRIS Blue Theme) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Laporan Rekapitulasi Gaji') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Cetak slip gaji masal, daftar transfer bank, dan rekapitulasi komponen penggajian karyawan') }}
                </p>
            </div>
        </div>

        {{-- Breadcrumb on right --}}
        <nav class="flex items-center gap-1.5 text-xs text-slate-400 self-start sm:self-auto">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-700 transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>{{ __('Dashboard') }}</span>
            </a>
            <span>/</span>
            <span class="text-slate-500 font-medium">{{ __('Laporan') }}</span>
            <span>/</span>
            <span class="text-blue-600 font-semibold">{{ __('Gaji') }}</span>
        </nav>
    </div>

    {{-- 2. Main Content: Robust Flex Row (Left Form + Right Sidebar) --}}
    <div class="flex flex-col lg:flex-row items-start gap-6 w-full">
        
        {{-- Left: Main Form Card --}}
        <div class="w-full lg:flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                
                {{-- Card Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-sm shadow-blue-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-800 tracking-tight">{{ __('Parameter Laporan Gaji') }}</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Payroll Engine') }}
                    </span>
                </div>

                {{-- Form Content --}}
                <form action="{{ route('reports.payroll.export') }}" method="POST" id="formPayroll" class="p-6 space-y-5">
                    @csrf

                    {{-- Row 1: Cabang & Departemen --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-1.5">
                            <label for="kode_cabang_pay" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>{{ __('Kantor / Cabang') }}</span>
                            </label>
                            <select name="kode_cabang" id="kode_cabang_pay" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">{{ __('Semua Cabang') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ strtoupper($b->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="kode_dept_pay" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>{{ __('Departemen / Divisi') }}</span>
                            </label>
                            <select name="kode_dept" id="kode_dept_pay" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">{{ __('Semua Departemen') }}</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}">{{ strtoupper($d->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Pilih Karyawan Target --}}
                    <div class="space-y-1.5">
                        <label for="nik_pay" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ __('Pilih Karyawan (Opsional)') }}</span>
                        </label>
                        <select name="nik" id="nik_pay" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            <option value="">{{ __('Semua Karyawan') }}</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Row 3: Master Periode Gaji (Cut-off closing) --}}
                    <div class="space-y-1.5">
                        <label for="payroll_period_id" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <span>{{ __('Master Periode Gaji Terdaftar') }}</span>
                        </label>
                        <select name="payroll_period_id" id="payroll_period_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            <option value="">{{ __('- Gunakan Filter Bulan & Tahun Kalender di Bawah -') }}</option>
                            @foreach ($payrollPeriods as $pp)
                                <option value="{{ $pp->id }}">{{ $pp->name }} ({{ $pp->start_date->format('d/m/Y') }} - {{ $pp->end_date->format('d/m/Y') }}) - [{{ strtoupper($pp->status) }}]</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Row 4: Bulan & Tahun --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-1.5">
                            <label for="bulan_pay" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Pilih Bulan') }}</span>
                            </label>
                            <select name="bulan" id="bulan_pay" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="tahun_pay" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Pilih Tahun') }}</span>
                            </label>
                            <select name="tahun" id="tahun_pay" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                @for ($t = $startYear; $t <= date('Y') + 1; $t++)
                                    <option value="{{ $t }}" {{ $currentYear == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Action Submit Buttons (HRIS Theme) --}}
                    <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3.5">
                        {{-- Cetak Laporan Button (HRIS Royal Blue) --}}
                        <button type="submit" name="action_type" value="pdf" id="btnCetakPay"
                                class="w-full sm:flex-1 py-3 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/25 transition-all duration-200 active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>{{ __('Cetak Rekapitulasi Gaji') }}</span>
                        </button>

                        {{-- Export Excel Button --}}
                        <button type="submit" name="action_type" value="excel" id="btnExportPay"
                                class="w-full sm:flex-1 py-3 px-5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs sm:text-sm shadow-sm transition-all duration-200 active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span>{{ __('Export Data ke Excel') }}</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>

        {{-- Right: Guidance & Highlights Column (HRIS Blue Theme) --}}
        <div class="w-full lg:w-[360px] shrink-0 space-y-4">
            
            {{-- Guidance Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3.5">
                <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span>{{ __('Petunjuk Komponen Gaji') }}</span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100/80">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            {{ __('Take Home Pay (THP)') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">{{ __('Hasil bersih gaji pokok ditambah seluruh tunjangan dan lembur yang telah dikurangi potongan pinjaman & denda.') }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                            {{ __('Daftar Transfer Bank') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">{{ __('Format Excel memuat nomor rekening, nama pemilik rekening, dan nominal bersih siap ekspor bank payroll.') }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                            {{ __('Otorisasi Tupoksi') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">{{ __('Tanda tangan resmi dibuat oleh Staff Payroll/HR dan disetujui Finance Manager / Direktur.') }}</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
    const branchSelectPay = document.getElementById('kode_cabang_pay');
    const deptSelectPay = document.getElementById('kode_dept_pay');
    const employeeSelectPay = document.getElementById('nik_pay');

    function filterEmployeesPay() {
        const branchId = branchSelectPay.value;
        const deptId = deptSelectPay.value;

        fetch(`{{ route('reports.employees_by_filter') }}?branch_id=${branchId}&department_id=${deptId}`)
            .then(res => res.json())
            .then(data => {
                employeeSelectPay.innerHTML = '<option value="">{{ __('Semua Karyawan') }}</option>';
                data.forEach(emp => {
                    const opt = document.createElement('option');
                    opt.value = emp.id;
                    opt.textContent = emp.name;
                    employeeSelectPay.appendChild(opt);
                });
            });
    }

    branchSelectPay.addEventListener('change', filterEmployeesPay);
    deptSelectPay.addEventListener('change', filterEmployeesPay);
</script>
@endsection

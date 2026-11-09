@extends('layouts.app', ['headerTitle' => __('Laporan & Matriks Jadwal Kerja'), 'title' => __('Laporan Jadwal')])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Breadcrumb (HRIS Blue Theme) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Laporan & Matriks Jadwal Kerja') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Cetak dan ekspor matriks penugasan jam kerja, shift, dan jadwal harian karyawan') }}
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
            <span class="text-blue-600 font-semibold">{{ __('Jadwal') }}</span>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-800 tracking-tight">{{ __('Parameter Laporan Jadwal') }}</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Jadwal Shift') }}
                    </span>
                </div>

                {{-- Form Content --}}
                <form action="{{ route('reports.schedule.export') }}" method="POST" id="formSchedule" class="p-6 space-y-5">
                    @csrf

                    {{-- Row 1: Cabang & Departemen --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-1.5">
                            <label for="kode_cabang_sch" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>{{ __('Kantor / Cabang') }}</span>
                            </label>
                            <select name="kode_cabang" id="kode_cabang_sch" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">{{ __('Semua Cabang') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ strtoupper($b->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="kode_dept_sch" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>{{ __('Departemen / Divisi') }}</span>
                            </label>
                            <select name="kode_dept" id="kode_dept_sch" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">{{ __('Semua Departemen') }}</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}">{{ strtoupper($d->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Pilih Karyawan (Opsional) --}}
                    <div class="space-y-1.5">
                        <label for="nik_sch" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ __('Pilih Karyawan (Opsional)') }}</span>
                        </label>
                        <select name="nik" id="nik_sch" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            <option value="">{{ __('Semua Karyawan') }}</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Row 3: Rentang Tanggal Matriks (Dari & Sampai) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-1.5">
                            <label for="dari_sch" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Dari Tanggal') }}</span>
                            </label>
                            <input type="date" name="dari" id="dari_sch" value="{{ $defaultDari }}" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        </div>

                        <div class="space-y-1.5">
                            <label for="sampai_sch" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Sampai Tanggal') }}</span>
                            </label>
                            <input type="date" name="sampai" id="sampai_sch" value="{{ $defaultSampai }}" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        </div>
                    </div>

                    {{-- Action Submit Buttons (HRIS Theme) --}}
                    <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3.5">
                        {{-- Cetak Matriks Jadwal Button (HRIS Royal Blue) --}}
                        <button type="submit" name="action_type" value="pdf" id="btnCetakSch"
                                class="w-full sm:flex-1 py-3 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/25 transition-all duration-200 active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>{{ __('Cetak Matriks Jadwal') }}</span>
                        </button>

                        {{-- Export Excel Button --}}
                        <button type="submit" name="action_type" value="excel" id="btnExportSch"
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
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <span>{{ __('Prioritas Jadwal Kerja') }}</span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100/80">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-5 h-5 rounded-md bg-blue-600 text-white font-bold text-[10px] flex items-center justify-center">1</span>
                            <strong class="text-slate-900 text-xs">{{ __('Jadwal By Date / Ajuan') }}</strong>
                        </div>
                        <p class="text-[11px] leading-relaxed text-slate-600 pl-7">{{ __('Jadwal spesifik per tanggal atau pertukaran shift kerja yang telah disetujui atasan.') }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-5 h-5 rounded-md bg-indigo-600 text-white font-bold text-[10px] flex items-center justify-center">2</span>
                            <strong class="text-slate-900 text-xs">{{ __('Jadwal Grup Kerja') }}</strong>
                        </div>
                        <p class="text-[11px] leading-relaxed text-slate-600 pl-7">{{ __('Jadwal pola giliran kerja regu atau tim operasional terjadwal.') }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-5 h-5 rounded-md bg-slate-600 text-white font-bold text-[10px] flex items-center justify-center">3</span>
                            <strong class="text-slate-900 text-xs">{{ __('Jadwal Harian Pegawai / Dept') }}</strong>
                        </div>
                        <p class="text-[11px] leading-relaxed text-slate-600 pl-7">{{ __('Jadwal baku per hari kerja departemen atau cabang utama.') }}</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
    const branchSelectSch = document.getElementById('kode_cabang_sch');
    const deptSelectSch = document.getElementById('kode_dept_sch');
    const employeeSelectSch = document.getElementById('nik_sch');

    function filterEmployeesSch() {
        const branchId = branchSelectSch.value;
        const deptId = deptSelectSch.value;

        fetch(`{{ route('reports.employees_by_filter') }}?branch_id=${branchId}&department_id=${deptId}`)
            .then(res => res.json())
            .then(data => {
                employeeSelectSch.innerHTML = '<option value="">{{ __('Semua Karyawan') }}</option>';
                data.forEach(emp => {
                    const opt = document.createElement('option');
                    opt.value = emp.id;
                    opt.textContent = emp.name;
                    employeeSelectSch.appendChild(opt);
                });
            });
    }

    branchSelectSch.addEventListener('change', filterEmployeesSch);
    deptSelectSch.addEventListener('change', filterEmployeesSch);
</script>
@endsection

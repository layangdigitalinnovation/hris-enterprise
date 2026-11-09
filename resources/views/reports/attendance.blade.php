@extends('layouts.app', ['headerTitle' => __('Laporan & Rekapitulasi Presensi'), 'title' => __('Laporan Presensi')])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Breadcrumb (HRIS Blue Theme) --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Laporan Rekapitulasi Presensi') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Generate rekapitulasi kehadiran, akumulasi jam kerja, denda, lembur, dan ekspor ke cetak / Excel') }}
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
            <span class="text-blue-600 font-semibold">{{ __('Presensi') }}</span>
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
                        <h2 class="text-sm font-bold text-slate-800 tracking-tight">{{ __('Parameter Laporan Presensi') }}</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ __('Attendance Engine') }}
                    </span>
                </div>

                {{-- Form Content --}}
                <form action="{{ route('reports.attendance.export') }}" method="POST" id="formPresensi" class="p-6 space-y-5">
                    @csrf

                    {{-- Row 1: Cabang & Departemen --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        {{-- Cabang --}}
                        <div class="space-y-1.5">
                            <label for="kode_cabang" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                <span>{{ __('Kantor / Cabang') }}</span>
                            </label>
                            <select name="kode_cabang" id="kode_cabang" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">{{ __('Semua Cabang') }}</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ strtoupper($b->name) }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Departemen --}}
                        <div class="space-y-1.5">
                            <label for="kode_dept" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>{{ __('Departemen / Divisi') }}</span>
                            </label>
                            <select name="kode_dept" id="kode_dept" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">{{ __('Semua Departemen') }}</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d->id }}">{{ strtoupper($d->name) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Row 2: Karyawan Target --}}
                    <div class="space-y-1.5">
                        <label for="nik" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>{{ __('Pilih Karyawan (Opsional)') }}</span>
                        </label>
                        <select name="nik" id="nik" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            <option value="">{{ __('Semua Karyawan') }}</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Row 3: Model Periode Presensi (Segment Cards) --}}
                    <div class="space-y-2 pt-1">
                        <label class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Model Periode Presensi') }}</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="periodeSelector">
                            {{-- Periode Gaji (Cut-off) --}}
                            <label class="relative flex items-center p-3.5 rounded-xl border border-slate-200 cursor-pointer transition hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                                <input type="radio" name="periode_laporan" value="1" class="sr-only" checked onchange="togglePeriodeMode(1)">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold text-slate-800">{{ __('Periode Gaji') }}</span>
                                        <span class="block text-[10px] text-slate-500">{{ __('Cut-Off Payroll') }}</span>
                                    </div>
                                </div>
                            </label>

                            {{-- Bulan Berjalan (1 - 31) --}}
                            <label class="relative flex items-center p-3.5 rounded-xl border border-slate-200 cursor-pointer transition hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                                <input type="radio" name="periode_laporan" value="2" class="sr-only" onchange="togglePeriodeMode(2)">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold text-slate-800">{{ __('Bulan Berjalan') }}</span>
                                        <span class="block text-[10px] text-slate-500">{{ __('Tgl 01 s/d 31') }}</span>
                                    </div>
                                </div>
                            </label>

                            {{-- Range Tanggal Custom --}}
                            <label class="relative flex items-center p-3.5 rounded-xl border border-slate-200 cursor-pointer transition hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50 has-[:checked]:ring-1 has-[:checked]:ring-blue-500">
                                <input type="radio" name="periode_laporan" value="3" class="sr-only" onchange="togglePeriodeMode(3)">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center">
                                        <div class="w-2 h-2 rounded-full bg-blue-600"></div>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-bold text-slate-800">{{ __('Range Tanggal') }}</span>
                                        <span class="block text-[10px] text-slate-500">{{ __('Kustom Tanggal') }}</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Dynamic Row: Bulan & Tahun (for Mode 1 & 2) --}}
                    <div id="baris_bulan_tahun" class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-1.5">
                            <label for="bulan" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Pilih Bulan') }}</span>
                            </label>
                            <select name="bulan" id="bulan" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $currentMonth == $m ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label for="tahun" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Pilih Tahun') }}</span>
                            </label>
                            <select name="tahun" id="tahun" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                @for ($t = $startYear; $t <= date('Y') + 1; $t++)
                                    <option value="{{ $t }}" {{ $currentYear == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    {{-- Dynamic Row: Range Tanggal (for Mode 3) --}}
                    <div id="baris_tanggal" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div class="space-y-1.5">
                            <label for="dari" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Dari Tanggal') }}</span>
                            </label>
                            <input type="date" name="dari" id="dari" value="{{ date('Y-m-01') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        </div>

                        <div class="space-y-1.5">
                            <label for="sampai" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ __('Sampai Tanggal') }}</span>
                            </label>
                            <input type="date" name="sampai" id="sampai" value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        </div>
                    </div>

                    {{-- Action Submit Buttons (HRIS Theme) --}}
                    <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center gap-3.5">
                        {{-- Cetak Laporan Button (HRIS Royal Blue) --}}
                        <button type="submit" name="action_type" value="pdf" id="btnCetak"
                                class="w-full sm:flex-1 py-3 px-5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs sm:text-sm shadow-md shadow-blue-500/25 transition-all duration-200 active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>{{ __('Cetak Laporan Presensi') }}</span>
                        </button>

                        {{-- Export Excel Button --}}
                        <button type="submit" name="action_type" value="excel" id="btnExport"
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
                    <span>{{ __('Panduan Parameter') }}</span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100/80">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            {{ __('Periode Gaji (Cut-off)') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">{{ __('Mengikuti rentang cut-off penggajian resmi (default tgl 26 bulan lalu s/d 25 bulan berjalan) siap proses slip gaji.') }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                            {{ __('Bulan Berjalan') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">{{ __('Menghitung akumulasi kehadiran kalender penuh masehi dari tanggal 1 sampai akhir bulan.') }}</p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                            {{ __('Range Tanggal Bebas') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">{{ __('Pilih rentang tanggal kustom untuk audit mingguan, rotasi shift, atau inspeksi presensi berkala.') }}</p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
    function togglePeriodeMode(mode) {
        const barisBulanTahun = document.getElementById('baris_bulan_tahun');
        const barisTanggal = document.getElementById('baris_tanggal');

        if (mode === 3) {
            barisBulanTahun.classList.add('hidden');
            barisTanggal.classList.remove('hidden');
        } else {
            barisBulanTahun.classList.remove('hidden');
            barisTanggal.classList.add('hidden');
        }
    }

    // Dynamic Employee Dropdown by Branch & Dept
    const branchSelect = document.getElementById('kode_cabang');
    const deptSelect = document.getElementById('kode_dept');
    const employeeSelect = document.getElementById('nik');

    function filterEmployees() {
        const branchId = branchSelect.value;
        const deptId = deptSelect.value;

        fetch(`{{ route('reports.employees_by_filter') }}?branch_id=${branchId}&department_id=${deptId}`)
            .then(res => res.json())
            .then(data => {
                employeeSelect.innerHTML = '<option value="">{{ __('Semua Karyawan') }}</option>';
                data.forEach(emp => {
                    const opt = document.createElement('option');
                    opt.value = emp.id;
                    opt.textContent = emp.name;
                    employeeSelect.appendChild(opt);
                });
            });
    }

    branchSelect.addEventListener('change', filterEmployees);
    deptSelect.addEventListener('change', filterEmployees);
</script>
@endsection

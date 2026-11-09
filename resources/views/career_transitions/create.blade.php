@extends('layouts.app', ['headerTitle' => __('Ajukan Mutasi / Promosi Karyawan'), 'title' => __('Form Mutasi & Promosi')])

@section('content')
<div class="space-y-6 max-w-5xl mx-auto w-full">

    {{-- 1. Header & Breadcrumb --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <a href="{{ route('career-transitions.index') }}"
               class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Mutasi & Promosi Karyawan') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Pengajuan perubahan jabatan, divisi, penempatan cabang, atau penyesuaian kompensasi karyawan') }}</p>
            </div>
        </div>

        <nav class="flex items-center gap-1.5 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-700 transition">{{ __('Dashboard') }}</a>
            <span>/</span>
            <a href="{{ route('career-transitions.index') }}" class="hover:text-slate-700 transition">{{ __('Mutasi & Promosi') }}</a>
            <span>/</span>
            <span class="text-blue-600 font-semibold">{{ __('Tambah') }}</span>
        </nav>
    </div>

    {{-- 2. Form Container --}}
    <form action="{{ route('career-transitions.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Section 1: Identitas & Jenis Perubahan Karir --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center">1</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">{{ __('Karyawan & Jenis Pergerakan Karir') }}</h2>
                    <p class="text-[11px] text-slate-400">{{ __('Tentukan karyawan yang diajukan dan kategori perubahan jabatan/penugasan') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Employee Select --}}
                <div class="space-y-1.5">
                    <label for="employee_id" class="block text-xs font-bold text-slate-700">
                        {{ __('Pilih Karyawan') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="employee_id" id="employee_id" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="">{{ __('-- Pilih Karyawan --') }}</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (old('employee_id', $selectedEmployee?->id) == $emp->id) ? 'selected' : '' }}>
                                {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->position?->title ?? '-' }} • {{ $emp->branch?->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type Select --}}
                <div class="space-y-1.5">
                    <label for="type" class="block text-xs font-bold text-slate-700">
                        {{ __('Kategori Pergerakan Karir') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="type" id="type" required
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="promotion" {{ old('type') == 'promotion' ? 'selected' : '' }}>{{ __('Promosi Jabatan (Promotion / ترقية)') }}</option>
                        <option value="transfer" {{ old('type') == 'transfer' ? 'selected' : '' }}>{{ __('Mutasi Lokasi / Cabang (Transfer / نقل)') }}</option>
                        <option value="rotation" {{ old('type') == 'rotation' ? 'selected' : '' }}>{{ __('Rotasi Pekerjaan / Departemen (Rotation / تدوير)') }}</option>
                        <option value="demotion" {{ old('type') == 'demotion' ? 'selected' : '' }}>{{ __('Demosi (Demotion / خفض الدرجة)') }}</option>
                    </select>
                    @error('type')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Live Employee Snapshot Card (Populated dynamically) --}}
            <div id="employee-snapshot-card" class="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-2 hidden">
                <div class="flex items-center justify-between text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <span>{{ __('Informasi Kepegawaian Saat Ini') }}</span>
                    <span id="snap-tenure" class="font-normal text-slate-600"></span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-1">
                    <div>
                        <span class="text-slate-400 block text-[10px]">{{ __('Cabang / Penempatan') }}</span>
                        <strong id="snap-branch" class="text-slate-700 font-medium">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">{{ __('Departemen') }}</span>
                        <strong id="snap-department" class="text-slate-700 font-medium">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">{{ __('Jabatan Terakhir') }}</span>
                        <strong id="snap-position" class="text-slate-700 font-medium">-</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[10px]">{{ __('Atasan Langsung') }}</span>
                        <strong id="snap-manager" class="text-slate-700 font-medium">-</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Matriks Perubahan Jabatan, Departemen, & Cabang Baru --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 font-bold text-xs flex items-center justify-center">2</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">{{ __('Tujuan Penempatan & Jabatan Baru') }}</h2>
                    <p class="text-[11px] text-slate-400">{{ __('Konfigurasi penugasan baru untuk cabang, divisi, jabatan, atasan, dan penyesuaian gaji') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- New Branch --}}
                <div class="space-y-1.5">
                    <label for="to_branch_id" class="block text-xs font-bold text-slate-700">
                        {{ __('Cabang Baru / Penempatan') }}
                    </label>
                    <select name="to_branch_id" id="to_branch_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="">{{ __('-- Tetap di Cabang Saat Ini --') }}</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('to_branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }} ({{ $b->currency }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- New Department --}}
                <div class="space-y-1.5">
                    <label for="to_department_id" class="block text-xs font-bold text-slate-700">
                        {{ __('Departemen Baru') }}
                    </label>
                    <select name="to_department_id" id="to_department_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="">{{ __('-- Tetap di Departemen Saat Ini --') }}</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ old('to_department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- New Position --}}
                <div class="space-y-1.5">
                    <label for="to_position_id" class="block text-xs font-bold text-slate-700">
                        {{ __('Jabatan / Maksud Penugasan Baru') }}
                    </label>
                    <select name="to_position_id" id="to_position_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="">{{ __('-- Tetap di Jabatan Saat Ini --') }}</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('to_position_id') == $pos->id ? 'selected' : '' }}>
                                {{ $pos->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- New Manager --}}
                <div class="space-y-1.5">
                    <label for="to_manager_id" class="block text-xs font-bold text-slate-700">
                        {{ __('Atasan Langsung Baru') }}
                    </label>
                    <select name="to_manager_id" id="to_manager_id"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="">{{ __('-- Tidak Ada / Sesuai Struktur Baru --') }}</option>
                        @foreach($managers as $m)
                            <option value="{{ $m->id }}" {{ old('to_manager_id') == $m->id ? 'selected' : '' }}>
                                {{ $m->full_name }} ({{ $m->position?->title ?? 'Manager' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- New Salary Adjustment (Optional) --}}
                <div class="space-y-1.5">
                    <label for="to_salary" class="block text-xs font-bold text-slate-700">
                        {{ __('Gaji Pokok Baru (Opsional)') }}
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" name="to_salary" id="to_salary"
                               value="{{ old('to_salary') }}"
                               placeholder="Contoh: 8500000"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    </div>
                    <p class="text-[11px] text-slate-400">{{ __('Kosongkan bila tidak terdapat penyesuaian nominal gaji pokok.') }}</p>
                </div>

                {{-- New Currency --}}
                <div class="space-y-1.5">
                    <label for="to_currency" class="block text-xs font-bold text-slate-700">
                        {{ __('Mata Uang Gaji') }}
                    </label>
                    <select name="to_currency" id="to_currency"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        <option value="IDR" {{ old('to_currency', 'IDR') == 'IDR' ? 'selected' : '' }}>IDR (Indonesian Rupiah)</option>
                        <option value="SAR" {{ old('to_currency') == 'SAR' ? 'selected' : '' }}>SAR (Saudi Riyal / ريال سعودي)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 3: Legalitas SK, Tanggal, & Alasan Pertimbangan --}}
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-5">
            <div class="flex items-center gap-2.5 pb-3 border-b border-slate-100">
                <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 font-bold text-xs flex items-center justify-center">3</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-800">{{ __('Legalitas SK, Tanggal & Dokumen Pendukung') }}</h2>
                    <p class="text-[11px] text-slate-400">{{ __('Nomor Surat Keputusan resmi, tanggal berlaku, dan alasan pertimbangan transisi') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                {{-- Letter Number / SK --}}
                <div class="space-y-1.5">
                    <label for="letter_number" class="block text-xs font-bold text-slate-700">
                        {{ __('Nomor Surat Keputusan (SK / Dokumen)') }}
                    </label>
                    <input type="text" name="letter_number" id="letter_number"
                           value="{{ old('letter_number') }}"
                           placeholder="Contoh: SK/DIR/2026/XI-089"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    <p class="text-[11px] text-slate-400">{{ __('Opsional, dapat diisi sesuai penomoran administrasi perusahaan.') }}</p>
                </div>

                {{-- Request Date --}}
                <div class="space-y-1.5">
                    <label for="request_date" class="block text-xs font-bold text-slate-700">
                        {{ __('Tanggal Pengajuan') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="request_date" id="request_date" required
                           value="{{ old('request_date', $defaultRequestDate) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    @error('request_date')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Effective Date --}}
                <div class="space-y-1.5">
                    <label for="effective_date" class="block text-xs font-bold text-slate-700">
                        {{ __('Tanggal Efektif Berlaku') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="effective_date" id="effective_date" required
                           value="{{ old('effective_date', $defaultEffectiveDate) }}"
                           class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    @error('effective_date')
                        <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Reason Textarea --}}
            <div class="space-y-1.5">
                <label for="reason" class="block text-xs font-bold text-slate-700">
                    {{ __('Alasan & Dasar Pertimbangan') }} <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" required
                          placeholder="{{ __('Jelaskan pertimbangan bisnis, hasil evaluasi performa, kebutuhan operasional cabang baru, atau rekam jejak karyawan...') }}"
                          class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Notes Textarea (Optional) --}}
            <div class="space-y-1.5">
                <label for="notes" class="block text-xs font-bold text-slate-700">
                    {{ __('Uraian Tugas Baru / Catatan Tambahan (Opsional)') }}
                </label>
                <textarea name="notes" id="notes" rows="2"
                          placeholder="{{ __('Catatan serah terima pekerjaan, target KPI baru, atau fasilitas penunjang yang disediakan...') }}"
                          class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">{{ old('notes') }}</textarea>
            </div>

            {{-- Attachment Upload --}}
            <div class="space-y-1.5">
                <label for="attachment" class="block text-xs font-bold text-slate-700">
                    {{ __('Unggah Surat Keputusan Resmi / Dokumen Pendukung (PDF/Gambar, Opsional)') }}
                </label>
                <input type="file" name="attachment" id="attachment" accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition cursor-pointer">
                <p class="text-[11px] text-slate-400">{{ __('Maksimal 5 MB dengan format PDF, JPG, atau PNG.') }}</p>
                @error('attachment')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('career-transitions.index') }}"
               class="px-5 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition">
                {{ __('Kirim Pengajuan Mutasi / Promosi') }}
            </button>
        </div>
    </form>
</div>

{{-- Interactive JavaScript to fetch and display employee snapshot --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const empSelect = document.getElementById('employee_id');
        const card = document.getElementById('employee-snapshot-card');

        function fetchSnapshot(empId) {
            if (!empId) {
                card.classList.add('hidden');
                return;
            }
            fetch(`/api/employees/${empId}/snapshot`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('snap-branch').textContent = data.branch_name || '-';
                    document.getElementById('snap-department').textContent = data.department_name || '-';
                    document.getElementById('snap-position').textContent = data.position_title || '-';
                    document.getElementById('snap-manager').textContent = data.manager_name || '-';
                    document.getElementById('snap-tenure').textContent = '{{ __("Masa Kerja:") }} ' + (data.tenure || '-');
                    card.classList.remove('hidden');
                })
                .catch(() => {
                    card.classList.add('hidden');
                });
        }

        empSelect.addEventListener('change', function () {
            fetchSnapshot(this.value);
        });

        if (empSelect.value) {
            fetchSnapshot(empSelect.value);
        }
    });
</script>
@endsection


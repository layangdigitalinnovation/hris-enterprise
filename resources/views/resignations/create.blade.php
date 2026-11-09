@extends('layouts.app', ['headerTitle' => __('Formulir Pengunduran Diri'), 'title' => __('Ajukan Resign')])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Breadcrumb --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('resignations.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Pengunduran Diri (Resign)') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Isi informasi pengajuan pengakhiran hubungan kerja, tanggal efektif hari terakhir, dan rencana serah terima tugas') }}
                </p>
            </div>
        </div>

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-1.5 text-xs text-slate-400">
            <a href="{{ route('dashboard') }}" class="hover:text-slate-700 transition">{{ __('Dashboard') }}</a>
            <span>/</span>
            <a href="{{ route('resignations.index') }}" class="hover:text-slate-700 transition">{{ __('Resign & Offboarding') }}</a>
            <span>/</span>
            <span class="text-blue-600 font-semibold">{{ __('Pengajuan Baru') }}</span>
        </nav>
    </div>

    {{-- 2. Main Grid: Form (Left) + Policy Sidebar (Right) --}}
    <div class="flex flex-col lg:flex-row items-start gap-6 w-full">
        
        {{-- Left: Main Submission Card --}}
        <div class="w-full lg:flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                
                {{-- Card Header --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-sm shadow-blue-500/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        <h2 class="text-sm font-bold text-slate-800 tracking-tight">{{ __('Rincian Permohonan Pengunduran Diri') }}</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        {{ __('Offboarding Portal') }}
                    </span>
                </div>

                {{-- Form Content --}}
                <form action="{{ route('resignations.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf

                    {{-- Admin/HR Mode: Employee Selector --}}
                    @if($canSelectEmployee)
                        <div class="space-y-1.5">
                            <label for="employee_id" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>{{ __('Pilih Karyawan Yang Mengajukan') }} <span class="text-rose-500">*</span></span>
                            </label>
                            <select name="employee_id" id="employee_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                                <option value="">-- {{ __('Pilih Karyawan') }} --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id', $currentEmployee?->id) == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }} ({{ $emp->employee_code }}) - {{ $emp->department?->name ?? 'No Dept' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        {{-- Regular Employee Info Banner --}}
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center text-sm">
                                    {{ substr($currentEmployee?->first_name ?? 'E', 0, 1) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900 text-sm">{{ $currentEmployee?->full_name }}</div>
                                    <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                        <span>{{ $currentEmployee?->employee_code }}</span>
                                        <span>•</span>
                                        <span>{{ $currentEmployee?->department?->name ?? '-' }}</span>
                                        <span>•</span>
                                        <span>{{ $currentEmployee?->branch?->name ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block">{{ __('Masa Kerja') }}</span>
                                <span class="text-xs font-bold text-blue-600">{{ $currentEmployee?->tenure_formatted ?? '-' }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Dates Row: Tanggal Pengajuan & Tanggal Hari Terakhir --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        {{-- Submission Date --}}
                        <div class="space-y-1.5">
                            <label for="submission_date" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ __('Tanggal Pengajuan') }} <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="date" name="submission_date" id="submission_date"
                                   value="{{ old('submission_date', $defaultSubmissionDate) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            @error('submission_date')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Requested Last Day --}}
                        <div class="space-y-1.5">
                            <label for="requested_last_day" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>{{ __('Permintaan Hari Terakhir Bekerja') }} <span class="text-rose-500">*</span></span>
                            </label>
                            <input type="date" name="requested_last_day" id="requested_last_day"
                                   value="{{ old('requested_last_day', $recommendedLastDay) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm font-mono focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            <p class="text-[11px] text-slate-400">{{ __('Standar notice period perusahaan adalah 30 hari kalender.') }}</p>
                            @error('requested_last_day')
                                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Reason Type --}}
                    <div class="space-y-1.5">
                        <label for="reason_type" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                            <span>{{ __('Kategori Alasan Pengunduran Diri') }} <span class="text-rose-500">*</span></span>
                        </label>
                        <select name="reason_type" id="reason_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                            <option value="career_growth" {{ old('reason_type') == 'career_growth' ? 'selected' : '' }}>{{ __('Peluang Karir Lebih Baik') }}</option>
                            <option value="relocation" {{ old('reason_type') == 'relocation' ? 'selected' : '' }}>{{ __('Pindah Domisili / Relokasi') }}</option>
                            <option value="health" {{ old('reason_type') == 'health' ? 'selected' : '' }}>{{ __('Kesehatan / Alasan Medis') }}</option>
                            <option value="personal" {{ old('reason_type') == 'personal' ? 'selected' : '' }}>{{ __('Alasan Pribadi') }}</option>
                            <option value="family" {{ old('reason_type') == 'family' ? 'selected' : '' }}>{{ __('Alasan Keluarga / Rumah Tangga') }}</option>
                            <option value="education" {{ old('reason_type') == 'education' ? 'selected' : '' }}>{{ __('Melanjutkan Pendidikan') }}</option>
                            <option value="compensation" {{ old('reason_type') == 'compensation' ? 'selected' : '' }}>{{ __('Kompensasi & Benefit') }}</option>
                            <option value="entrepreneurship" {{ old('reason_type') == 'entrepreneurship' ? 'selected' : '' }}>{{ __('Wirausaha / Usaha Mandiri') }}</option>
                            <option value="other" {{ old('reason_type') == 'other' ? 'selected' : '' }}>{{ __('Lain-lain') }}</option>
                        </select>
                        @error('reason_type')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Reason Detail --}}
                    <div class="space-y-1.5">
                        <label for="reason_detail" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
                            </svg>
                            <span>{{ __('Uraian / Penjelasan Alasan Resign') }} <span class="text-rose-500">*</span></span>
                        </label>
                        <textarea name="reason_detail" id="reason_detail" rows="4" required
                                  placeholder="{{ __('Jelaskan pertimbangan dan alasan pengunduran diri Anda secara jelas dan profesional...') }}"
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">{{ old('reason_detail') }}</textarea>
                        @error('reason_detail')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Handover Notes --}}
                    <div class="space-y-1.5">
                        <label for="handover_notes" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span>{{ __('Rencana Transisi & Serah Terima Tugas (Handover)') }}</span>
                        </label>
                        <textarea name="handover_notes" id="handover_notes" rows="3"
                                  placeholder="{{ __('Tuliskan daftar proyek/tugas berjalan, rekan kerja pengganti sementara, dan status berkas pekerjaan...') }}"
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">{{ old('handover_notes') }}</textarea>
                    </div>

                    {{-- Upload Resignation Letter --}}
                    <div class="space-y-1.5">
                        <label for="resignation_letter" class="block text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span>{{ __('Unggah Surat Pengunduran Diri Resmi (PDF/Gambar, Opsional)') }}</span>
                        </label>
                        <input type="file" name="resignation_letter" id="resignation_letter" accept=".pdf,.jpg,.jpeg,.png"
                               class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 text-xs file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition shadow-2xs">
                        <p class="text-[11px] text-slate-400">{{ __('Maksimal 5 MB dengan format PDF, JPG, atau PNG.') }}</p>
                        @error('resignation_letter')
                            <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-5 border-t border-slate-100 flex items-center justify-end gap-3">
                        <a href="{{ route('resignations.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition">
                            {{ __('Batal') }}
                        </a>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-sm shadow-blue-500/25 transition active:scale-[0.98]">
                            {{ __('Kirim Permohonan Resign') }}
                        </button>
                    </div>

                </form>

            </div>
        </div>

        {{-- Right: Guidelines & Policy Sidebar --}}
        <div class="w-full lg:w-[360px] shrink-0 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-xs space-y-3.5">
                <div class="flex items-center gap-2 text-slate-800 font-bold text-xs uppercase tracking-wider">
                    <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span>{{ __('Panduan & Ketentuan Offboarding') }}</span>
                </div>

                <div class="space-y-2.5 text-xs text-slate-600">
                    <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100/80">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                            {{ __('Aturan 30 Hari Notice Period') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">
                            {{ __('Pengajuan pengunduran diri wajib disampaikan minimal 30 hari sebelum tanggal efektif hari terakhir kerja demi kelancaran transfer ilmu dan kesinambungan tim.') }}
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                            {{ __('Proses Exit Clearance') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">
                            {{ __('Karyawan wajib mengembalikan laptop kantor, kartu akses gedung, melunasi sisa kasbon/pinjaman, dan menyelesaikan serah terima berkas.') }}
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60">
                        <strong class="text-slate-900 block text-xs mb-1 flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            {{ __('Surat Pengalaman Kerja (Paklaring)') }}
                        </strong>
                        <p class="text-[11px] leading-relaxed text-slate-600">
                            {{ __('Surat keterangan kerja dan paklaring resmi akan diterbitkan otomatis secara digital setelah seluruh tahapan clearance dinyatakan lengkap oleh HR.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection


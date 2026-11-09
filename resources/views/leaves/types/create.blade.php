@extends('layouts.app', ['headerTitle' => __('Manajemen Hak Kuota Cuti'), 'title' => __('Tambah Jenis Cuti')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('leaves.index', ['tab' => 'types']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Master Cuti') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah Jenis Cuti') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Tambah Master Jenis Cuti & Kebijakan Kuota') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Tentukan kuota standar, batasan hari berturut-turut, serta aturan pembayaran upah') }}</p>
        </div>
        <a href="{{ route('leaves.index', ['tab' => 'types']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir jenis cuti:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leaves.types.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Identitas Jenis Cuti -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Identitas & Kode Jenis Cuti') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Unik Cuti *') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="{{ __('Misal: CT_TAHUNAN, IZ_MENIKAH') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Kode referensi sistem untuk slip dan log.') }}</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Jenis Cuti *') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="{{ __('Misal: Cuti Tahunan Reguler') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Nama resmi yang tampil di akun karyawan.') }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Kuota & Aturan -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Hak Kuota & Batasan Hari') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Hak Kuota Standar (Hari/Tahun) *') }}</label>
                    <div class="relative">
                        <input type="number" name="default_quota" value="{{ old('default_quota', 12) }}" required min="0" max="365"
                            class="w-full pr-14 pl-3.5 rtl:pr-3.5 rtl:pl-14 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 rtl:right-auto rtl:left-3.5 top-2.5 text-slate-400 font-medium">{{ __('Hari') }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Hak kuota dasar yang dialokasikan per tahun berjalan.') }}</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Maksimal Hari Berturut-turut *') }}</label>
                    <div class="relative">
                        <input type="number" name="max_consecutive_days" value="{{ old('max_consecutive_days', 14) }}" required min="1" max="365"
                            class="w-full pr-14 pl-3.5 rtl:pr-3.5 rtl:pl-14 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 rtl:right-auto rtl:left-3.5 top-2.5 text-slate-400 font-medium">{{ __('Hari') }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Batas maksimal pengajuan cuti dalam satu periode berturut-turut.') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kebijakan Upah *') }}</label>
                    <select name="is_paid" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="1" {{ old('is_paid', '1') == '1' ? 'selected' : '' }}>{{ __('Cuti Berbayar (Paid)') }}</option>
                        <option value="0" {{ old('is_paid') == '0' ? 'selected' : '' }}>{{ __('Tidak Berbayar (Unpaid)') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Lampiran Dokumen *') }}</label>
                    <select name="requires_document" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="0" {{ old('requires_document', '0') == '0' ? 'selected' : '' }}>{{ __('Opsional (Tidak Wajib)') }}</option>
                        <option value="1" {{ old('requires_document', '1') == '1' ? 'selected' : '' }}>{{ __('Wajib Lampiran Surat/Dokumen') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Batasan Gender *') }}</label>
                    <select name="gender_restriction" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="all" {{ old('gender_restriction', 'all') == 'all' ? 'selected' : '' }}>{{ __('Semua Karyawan') }}</option>
                        <option value="female" {{ old('gender_restriction', 'female') == 'female' ? 'selected' : '' }}>{{ __('Khusus Wanita (Melahirkan, dll)') }}</option>
                        <option value="male" {{ old('gender_restriction', 'male') == 'male' ? 'selected' : '' }}>{{ __('Khusus Pria') }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Keaktifan *') }}</label>
                    <select name="is_active" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>{{ __('Aktif (Dapat Diajukan Karyawan)') }}</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>{{ __('Non-Aktif (Ditutup Sementara)') }}</option>
                    </select>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2.5 cursor-pointer text-slate-800 text-xs font-semibold">
                        <input type="checkbox" name="auto_allocate" value="1" checked class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        {{ __('Otomatis alokasikan kuota ke seluruh karyawan aktif') }}
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('leaves.index', ['tab' => 'types']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Jenis Cuti Baru') }}
            </button>
        </div>

    </form>

</div>
@endsection
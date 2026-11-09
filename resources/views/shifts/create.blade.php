@extends('layouts.app', ['headerTitle' => __('Manajemen Shift & Jadwal Kerja'), 'title' => __('Tambah Shift Baru')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('shifts.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Shift & Jadwal') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah Shift') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Buat Konfigurasi Shift Kerja Baru') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Tentukan jam operasional kerja, toleransi keterlambatan, dan label penanda shift') }}</p>
        </div>
        <a href="{{ route('shifts.index') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir shift:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('shifts.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Identitas Shift -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Identitas Shift Kerja') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Shift *') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Misal: SH_PAGI, SH_MALAM" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Warna Label Penanda') }}</label>
                    <input type="color" name="color" value="{{ old('color', '#3B82F6') }}"
                        class="w-full h-11 rounded-xl bg-slate-50 border border-slate-200 cursor-pointer p-1">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Shift *') }}</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Misal: Shift Pagi Operasional Reguler" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>
        </div>

        <!-- 2. Jam Operasional & Toleransi -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Waktu Operasional Kerja') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jam Mulai Masuk (HH:MM) *') }}</label>
                    <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jam Selesai Pulang (HH:MM) *') }}</label>
                    <input type="time" name="end_time" value="{{ old('end_time', '17:00') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Toleransi Keterlambatan (Menit) *') }}</label>
                    <div class="relative">
                        <input type="number" name="late_tolerance_minutes" value="{{ old('late_tolerance_minutes', 15) }}" required min="0" max="180"
                            class="w-full pr-16 pl-3.5 rtl:pl-16 rtl:pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 top-2.5 rtl:left-3.5 rtl:right-auto text-slate-400 font-medium">{{ __('Menit') }}</span>
                    </div>
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-2.5 cursor-pointer text-slate-800 text-xs font-semibold">
                        <input type="checkbox" name="is_overnight" value="1" {{ old('is_overnight') ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        {{ __('Shift Lintas Hari / Berganti Tanggal (Overnight Shift)') }}
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('shifts.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Shift Baru') }}
            </button>
        </div>

    </form>

</div>
@endsection

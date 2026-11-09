@extends('layouts.app', ['headerTitle' => __('Koreksi Presensi'), 'title' => __('Ajukan Koreksi Presensi')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('attendance.adjustments') }}" class="hover:text-blue-600 font-medium transition">{{ __('Koreksi Presensi') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Formulir Koreksi Baru') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Pengajuan Koreksi Presensi') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Koreksi ketidakhadiran, lupa fingerprint/tap masuk, atau kendala perangkat mesin absensi') }}</p>
        </div>
        <a href="{{ route('attendance.adjustments') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir koreksi:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('attendance.adjustments.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Karyawan Pemohon -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Karyawan yang Mengajukan Koreksi') }}
            </h4>

            @if(auth()->user()->isEmployee())
                <input type="hidden" name="employee_id" value="{{ auth()->user()->employee?->id }}">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <span class="text-sm font-bold text-slate-900 block">{{ auth()->user()->employee?->full_name ?? auth()->user()->name }}</span>
                        <span class="text-xs text-slate-500 font-mono">{{ auth()->user()->employee?->employee_code ?? '-' }} · {{ auth()->user()->employee?->department->name ?? 'Operasional' }}</span>
                    </div>
                </div>
            @else
                <div class="text-xs">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pilih Karyawan *') }}</label>
                    <select name="employee_id" required 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">-- {{ __('Pilih Karyawan') }} --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }}) - {{ $emp->department->name ?? 'Operasional' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- 2. Rincian Tanggal & Jam Koreksi -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Waktu Presensi yang Dikoreksi') }}
            </h4>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Presensi *') }}</label>
                <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jam Masuk Koreksi (HH:MM)') }}</label>
                    <input type="time" name="requested_clock_in" value="{{ old('requested_clock_in', '08:00') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jam Pulang Koreksi (HH:MM)') }}</label>
                    <input type="time" name="requested_clock_out" value="{{ old('requested_clock_out', '17:00') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alasan Koreksi Presensi *') }}</label>
                <textarea name="reason" rows="3" required placeholder="{{ __('Jelaskan alasan (misal: Lupa tap sidik jari saat datang, mesin offline, dinas luar, dll)...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('reason') }}</textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('attendance.adjustments') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Ajukan Koreksi Presensi') }}
            </button>
        </div>

    </form>

</div>
@endsection

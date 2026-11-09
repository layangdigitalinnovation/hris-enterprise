@extends('layouts.app', ['headerTitle' => __('Pengajuan Cuti & Izin'), 'title' => __('Ajukan Cuti Baru')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('leaves.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Cuti & Izin') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Formulir Cuti Baru') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Pengajuan Cuti & Izin') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Lengkapi rincian tanggal, jenis permohonan izin/cuti, dan alasan permohonan') }}</p>
        </div>
        <a href="{{ route('leaves.index') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir cuti:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leaves.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Data Pemohon -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Karyawan Pemohon') }}
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
                        <option value="">{{ __('-- Pilih Karyawan --') }}</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }}) - {{ $emp->department->name ?? 'Operasional' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jenis Cuti / Izin *') }}</label>
                <select name="leave_type_id" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <option value="">{{ __('-- Pilih Jenis Cuti --') }}</option>
                    @foreach ($leaveTypes as $lt)
                        <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                            {{ __($lt->name) }} ({{ __('Kuota:') }} {{ $lt->default_quota }} {{ __('Hari / Tahun') }}{{ $lt->is_paid ? ' · ' . __('Cuti Berbayar') : ' · ' . __('Tanpa Gaji') }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- 2. Periode & Alasan -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Periode Cuti & Alasan Permohonan') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Mulai Cuti *') }}</label>
                    <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Terakhir Cuti *') }}</label>
                    <input type="date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alasan Permohonan Cuti / Izin *') }}</label>
                <textarea name="reason" rows="4" required placeholder="{{ __('Jelaskan keperluan atau urgensi cuti Anda secara jelas...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('reason') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('Hari libur akhir pekan (Sabtu & Minggu) akan secara otomatis dilewati dari pemotongan hak kuota cuti.') }}</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('leaves.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Ajukan Cuti / Izin') }}
            </button>
        </div>

    </form>

</div>
@endsection

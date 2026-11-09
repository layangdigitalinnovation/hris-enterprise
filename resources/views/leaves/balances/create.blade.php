@extends('layouts.app', ['headerTitle' => __('Manajemen Hak Kuota Cuti'), 'title' => __('Tetapkan Saldo Kuota Cuti')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('leaves.index', ['tab' => 'balances']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Saldo Kuota Cuti') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tetapkan Saldo Baru') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Tetapkan Saldo Kuota Cuti Karyawan') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Alokasikan hak kuota cuti tahunan untuk karyawan tertentu secara spesifik') }}</p>
        </div>
        <a href="{{ route('leaves.index', ['tab' => 'balances']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir saldo kuota:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leaves.balances.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Karyawan & Jenis Cuti -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Penerima Alokasi Kuota') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
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

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pilih Jenis Cuti *') }}</label>
                    <select name="leave_type_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">{{ __('-- Pilih Jenis Cuti --') }}</option>
                        @foreach ($leaveTypes as $lt)
                            <option value="{{ $lt->id }}" {{ old('leave_type_id') == $lt->id ? 'selected' : '' }}>
                                {{ __($lt->name) }} ({{ __('Standar:') }} {{ $lt->default_quota }} {{ __('Hari') }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- 2. Tahun & Alokasi Kuota -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Tahun & Jumlah Hak Kuota') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tahun Berlaku *') }}</label>
                    <input type="number" name="year" value="{{ old('year', date('Y')) }}" required min="2020" max="2050"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Hak Kuota Awal (Hari) *') }}</label>
                    <div class="relative">
                        <input type="number" name="initial_quota" value="{{ old('initial_quota', 12) }}" required min="0" max="365"
                            class="w-full pr-14 pl-3.5 rtl:pr-3.5 rtl:pl-14 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 rtl:right-auto rtl:left-3.5 top-2.5 text-slate-400 font-medium">{{ __('Hari') }}</span>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Bila karyawan sudah memiliki saldo tahun ini, nilai kuota awal akan diperbarui.') }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('leaves.index', ['tab' => 'balances']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Saldo Kuota') }}
            </button>
        </div>

    </form>

</div>
@endsection
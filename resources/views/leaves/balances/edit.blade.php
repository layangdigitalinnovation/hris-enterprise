@extends('layouts.app', ['headerTitle' => __('Manajemen Hak Kuota Cuti'), 'title' => __('Sesuaikan Saldo Kuota')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    initial: {{ old('initial_quota', $leaveBalance->initial_quota) }},
    used: {{ old('used_quota', $leaveBalance->used_quota) }}
}">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('leaves.index', ['tab' => 'balances']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Saldo Kuota Cuti') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Sesuaikan Saldo') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Sesuaikan Saldo Kuota Cuti Karyawan') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Koreksi kuota awal atau riwayat pemakaian cuti tahun berjalan') }}</p>
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

    <form action="{{ route('leaves.balances.update', $leaveBalance) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Identitas Karyawan & Jenis Cuti -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Informasi Karyawan & Hak Cuti') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-[11px] text-slate-500 block">{{ __('Nama Karyawan') }}</span>
                    <span class="font-bold text-slate-900 text-sm block mt-0.5">{{ $leaveBalance->employee->full_name }}</span>
                    <span class="text-[10px] text-slate-500 font-mono">{{ $leaveBalance->employee->employee_code }} · {{ $leaveBalance->employee->department->name ?? 'Operasional' }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-[11px] text-slate-500 block">{{ __('Kategori Cuti') }}</span>
                    <span class="font-bold text-slate-900 text-sm block mt-0.5">{{ __($leaveBalance->leaveType->name) }}</span>
                    <span class="text-[10px] text-blue-600 font-mono">{{ $leaveBalance->leaveType->code }}</span>
                </div>

                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                    <span class="text-[11px] text-slate-500 block">{{ __('Tahun Kalender') }}</span>
                    <span class="font-mono font-bold text-slate-900 text-base block mt-0.5">{{ $leaveBalance->year }}</span>
                    <span class="text-[10px] text-slate-400">{{ __('Periode Berjalan') }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Penyesuaian Kuota -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Penyesuaian Hak Kuota & Pemakaian') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Hak Kuota Awal (Hari) *') }}</label>
                    <div class="relative">
                        <input type="number" name="initial_quota" x-model.number="initial" required min="0" max="365"
                            class="w-full pr-14 pl-3.5 rtl:pr-3.5 rtl:pl-14 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono font-bold text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 rtl:right-auto rtl:left-3.5 top-2.5 text-slate-400 font-medium">{{ __('Hari') }}</span>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jumlah Hari Terpakai (Hari) *') }}</label>
                    <div class="relative">
                        <input type="number" name="used_quota" x-model.number="used" required min="0" max="365"
                            class="w-full pr-14 pl-3.5 rtl:pr-3.5 rtl:pl-14 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 rtl:right-auto rtl:left-3.5 top-2.5 text-slate-400 font-medium">{{ __('Hari') }}</span>
                    </div>
                </div>
            </div>

            <!-- Kalkulasi Sisa Kuota Otomatis -->
            <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-emerald-900 block">{{ __('Sisa Hak Kuota Terhitung:') }}</span>
                    <span class="text-[11px] text-emerald-700 block mt-0.5">{{ __('Rumus: Kuota Awal dikurangi Hari Terpakai') }}</span>
                </div>
                <div class="text-right rtl:text-left">
                    <span class="text-2xl font-extrabold text-emerald-700 font-mono" x-text="Math.max(0, (initial || 0) - (used || 0)) + ' ' + '{{ __('Hari') }}'"></span>
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
                {{ __('Simpan Penyesuaian Saldo') }}
            </button>
        </div>

    </form>

</div>
@endsection
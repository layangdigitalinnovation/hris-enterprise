@extends('layouts.app', ['headerTitle' => __('Buat Periode Payroll Baru'), 'title' => __('Buat Periode')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('payroll.index', ['tab' => 'periods']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Buat Periode Baru') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Periode Penggajian Baru') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Tentukan nama siklus, rentang tanggal kerja, cut-off absensi Solution, dan jadwal tanggal transfer gaji') }}</p>
        </div>
        <a href="{{ route('payroll.index', ['tab' => 'periods']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada input formulir:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payroll.period.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Identitas Periode -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Identitas Periode Penggajian') }}
            </h4>

            <div>
                <label class="block font-semibold text-slate-700 text-xs mb-1.5">{{ __('Nama Periode Penggajian *') }}</label>
                <input type="text" name="name" 
                    value="{{ old('name', __('Periode Payroll') . ' ' . Carbon\Carbon::now()->translatedFormat('F Y')) }}" 
                    required placeholder="{{ __('Contoh: Periode Payroll Oktober 2026') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Label resmi periode yang akan tercetak pada laporan dan slip gaji karyawan.') }}</span>
            </div>
        </div>

        <!-- 2. Rentang Siklus Tanggal Kerja -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Rentang Siklus Tanggal Kerja') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Mulai *') }}</label>
                    <input type="date" name="start_date" 
                        value="{{ old('start_date', date('Y-m-01')) }}" 
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Akhir *') }}</label>
                    <input type="date" name="end_date" 
                        value="{{ old('end_date', date('Y-m-t')) }}" 
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- 3. Jadwal Cut-Off & Tanggal Pembayaran -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">3</span>
                {{ __('Jadwal Cut-Off Absensi & Pembayaran Gaji') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Cut-Off Absensi *') }}</label>
                    <input type="date" name="cut_off_date" 
                        value="{{ old('cut_off_date', sprintf('%s-%02d', date('Y-m'), (int) \App\Models\SystemSetting::get('payroll_cut_off_day', 25))) }}" 
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Batas rekapitulasi kehadiran, keterlambatan, dan lembur mesin absensi.') }}</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Pembayaran Gaji *') }}</label>
                    <input type="date" name="payment_date" 
                        value="{{ old('payment_date', date('Y-m-28')) }}" 
                        required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Tanggal pencairan upah atau transfer payroll bank ke karyawan.') }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('payroll.index', ['tab' => 'periods']) }}" 
                class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" 
                class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan & Lanjutkan') }}
            </button>
        </div>
    </form>

</div>
@endsection

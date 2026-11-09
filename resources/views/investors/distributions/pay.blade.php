@extends('layouts.app')

@section('title', __('Konfirmasi Pembayaran: :number', ['number' => $distribution->distribution_number]))

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('investors.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Investor') }}</a>
                <span>/</span>
                <a href="{{ route('investors.show', $investor) }}" class="hover:text-blue-600 font-medium transition-colors">{{ $investor->investor_code }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Konfirmasi Bayar') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{{ __('Konfirmasi Realisasi Pembayaran Dana') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Sahkan pembayaran transaksi menjadi lunas dan catat referensi transfer bank resmi.') }}</p>
        </div>

        <a href="{{ route('investors.show', $investor) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>{{ __('Kembali') }}</a>
    </div>

    <!-- Ringkasan Transaksi Box -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <span class="text-xs text-slate-400 block">{{ __('Penerima Dana') }}</span>
                <span class="text-sm font-bold text-slate-900">{{ $investor->name }} ({{ $investor->investor_code }})</span>
            </div>
            <div class="text-right">
                <span class="text-xs text-slate-400 block">{{ __('No. Transaksi') }}</span>
                <span class="text-xs font-mono font-bold text-blue-600">{{ $distribution->distribution_number }}</span>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block">{{ __('Jenis Distribusi') }}</span>
                <span class="font-semibold text-slate-800">{{ $distribution->type_label }}</span>
            </div>
            <div>
                <span class="text-slate-400 block">{{ __('Periode') }}</span>
                <span class="font-semibold text-slate-800">{{ $distribution->period_label }}</span>
            </div>
            @if($investor->bank_name || $investor->bank_account_no)
<div class="col-span-2 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                <span class="text-slate-500 block text-[11px]">{{ __('Rekening Tujuan:') }}</span>
                <strong class="text-slate-800">{{ $investor->bank_name }} - {{ $investor->bank_account_no }} ({{ __('a.n') }} {{ $investor->bank_account_holder ?? $investor->name }})</strong>
            </div>
            @endif
        </div>

        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-center justify-between">
            <span class="text-xs font-bold text-emerald-900">{{ __('Total Nominal Pembayaran:') }}</span>
            <span class="text-xl font-bold font-mono text-emerald-700">{{ format_currency($distribution->amount, $distribution->currency) }}</span>
        </div>
    </div>

    <!-- Form Pembayaran -->
    <form action="{{ route('investors.distributions.process-pay', $distribution) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h2 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">✓</span>{{ __('Data Realisasi Transfer & Bukti Pembayaran') }}</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Transfer / Pembayaran') }}<span class="text-rose-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Metode Pembayaran') }}<span class="text-rose-500">*</span></label>
                    <select name="payment_method" required class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                        <option value="bank_transfer" selected>{{ __('Transfer Bank Resmi') }}</option>
                        <option value="cash">{{ __('Tunai / Kas Proyek') }}</option>
                        <option value="cheque">{{ __('Cek / Bilyet Giro') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nomor Referensi Bank / Bukti Transfer') }}</label>
                    <input type="text" name="payment_reference" placeholder="{{ __('Contoh: REF-BCA-981244 / TX-ALRAJHI-891') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('investors.show', $investor) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">{{ __('Batal') }}</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>{{ __('Konfirmasi Pembayaran Lunas') }}</button>
        </div>
    </form>
</div>
@endsection

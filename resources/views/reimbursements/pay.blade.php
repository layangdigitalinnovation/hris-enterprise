@extends('layouts.app')

@section('title', 'Pencairan Dana Reimbursement: ' . $reimbursement->claim_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Reimbursement') }}</a>
                <span>/</span>
                <a href="{{ route('reimbursements.show', $reimbursement) }}" class="hover:text-blue-600 font-medium font-mono">{{ $reimbursement->claim_number }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Pencairan Dana') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{{ __('Realisasi Pembayaran / Transfer Dana') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Konfirmasi pencairan kas/transfer bank resmi dan sahkan status klaim menjadi Lunas.') }}</p>
        </div>

        <a href="{{ route('reimbursements.show', $reimbursement) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Batal') }}</span>
        </a>
    </div>

    <!-- Summary Box -->
    <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Nomor Klaim Terverifikasi') }}</span>
                <div class="font-mono font-bold text-lg text-white">{{ $reimbursement->claim_number }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">{{ __('Siap Dicairkan') }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-[11px] text-slate-400">{{ __('Penerima Dana (Pegawai)') }}</div>
                <div class="font-bold text-white mt-0.5">{{ $reimbursement->employee?->full_name }}</div>
                <div class="text-[11px] text-slate-400 font-mono">{{ $reimbursement->employee?->employee_code }} • {{ $reimbursement->employee?->department?->name }}</div>
            </div>

            <div class="text-right">
                <div class="text-[11px] text-slate-400">{{ __('Total Yang Harus Dibayarkan') }}</div>
                <div class="text-2xl font-bold font-mono text-emerald-400 mt-0.5">
                    {{ format_currency($reimbursement->approved_amount ?? $reimbursement->amount, $reimbursement->currency) }}
                </div>
            </div>
        </div>

        <!-- Rekening Bank Tujuan -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 text-xs space-y-1">
            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Tujuan Transfer Bank:') }}</div>
            <div class="font-mono font-bold text-white">
                {{ $reimbursement->employee?->bank_name ?? 'BCA' }} - {{ $reimbursement->employee?->bank_account_no ?? 'Belum terdaftar' }}
            </div>
            <div class="text-[11px] text-slate-300">{{ __('Atas Nama:') }}<strong class="text-white">{{ $reimbursement->employee?->bank_account_holder ?? $reimbursement->employee?->full_name }}</strong>
            </div>
        </div>
    </div>

    <!-- Form Pembayaran -->
    <form action="{{ route('reimbursements.process-pay', $reimbursement) }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-5">
        @csrf
        @method('PUT')

        <!-- Metode Pembayaran -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-2">{{ __('Metode Pembayaran / Pencairan *') }}</label>
            <div class="grid grid-cols-3 gap-3">
                <label class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-slate-200 hover:border-blue-500 hover:bg-blue-50/30 cursor-pointer text-center transition-all">
                    <input type="radio" name="payment_method" value="transfer" checked class="text-blue-600 mb-1.5">
                    <span class="text-xs font-bold text-slate-800">{{ __('Transfer Bank') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('Payroll / Rekening') }}</span>
                </label>

                <label class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-slate-200 hover:border-blue-500 hover:bg-blue-50/30 cursor-pointer text-center transition-all">
                    <input type="radio" name="payment_method" value="cash" class="text-blue-600 mb-1.5">
                    <span class="text-xs font-bold text-slate-800">{{ __('Kas Tunai') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('Petty Cash Kasir') }}</span>
                </label>

                <label class="flex flex-col items-center justify-center p-3.5 rounded-xl border border-slate-200 hover:border-blue-500 hover:bg-blue-50/30 cursor-pointer text-center transition-all">
                    <input type="radio" name="payment_method" value="payroll" class="text-blue-600 mb-1.5">
                    <span class="text-xs font-bold text-slate-800">{{ __('Gabung Slip Gaji') }}</span>
                    <span class="text-[10px] text-slate-400 mt-0.5">{{ __('Periode Bulan Ini') }}</span>
                </label>
            </div>
        </div>

        <!-- Nomor Referensi Transfer -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Nomor Referensi Bank / No. Bukti Pengeluaran Kas (Voucher)') }}</label>
            <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="{{ __('Contoh: TRX-202611-99882 / BKK-00129') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono font-medium focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
        </div>

        <!-- Upload Bukti Transfer -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Unggah Bukti Transfer / Resi Pembayaran (Opsional)') }}</label>
            <input type="file" name="payment_receipt" accept="image/jpeg,image/png,image/jpg,application/pdf"
                class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Format gambar JPG, PNG, atau PDF maksimal 5MB') }}</p>
        </div>

        <!-- Catatan Tambahan -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Catatan Kasir / Finance (Opsional)') }}</label>
            <textarea name="notes" rows="2" placeholder="{{ __('Catatan internal pencairan dana...') }}"
                class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all"></textarea>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('reimbursements.show', $reimbursement) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-all">{{ __('Batal') }}</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-500/20 transition-all">{{ __('Sahkan & Konfirmasi Pembayaran Selesai') }}</button>
        </div>
    </form>
</div>
@endsection


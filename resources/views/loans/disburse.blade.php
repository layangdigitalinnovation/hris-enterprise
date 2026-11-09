@extends('layouts.app')

@section('title', __('Pencairan Pinjaman Karyawan') . ': ' . $loan->loan_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12" x-data="{
    paymentMethod: 'bank_transfer',
    fileName: '',
    handleFile(e) {
        const file = e.target.files[0];
        if (file) this.fileName = file.name;
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('loans.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Pinjaman Karyawan') }}</a>
                <span>/</span>
                <a href="{{ route('loans.show', $loan) }}" class="hover:text-blue-600 font-medium font-mono">{{ $loan->loan_number }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Pencairan Dana') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </span>{{ __('Pencairan Dana Pinjaman (Finance)') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Konfirmasi transfer atau penyerahan kas pokok pinjaman kepada pegawai yang bersangkutan.') }}</p>
        </div>

        <a href="{{ route('loans.show', $loan) }}" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Detail') }}</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Terdapat kesalahan input:') }}</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Box -->
    <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-slate-900 text-white rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Nomor Pinjaman') }}</span>
                <div class="font-mono font-bold text-lg text-white mt-0.5">{{ $loan->loan_number }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-300 border border-blue-400/30">{{ __('Disetujui (Siap Dicairkan)') }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Penerima Dana') }}</span>
                <span class="text-sm font-bold text-white block mt-0.5">{{ $loan->employee->full_name }}</span>
                <span class="text-[11px] text-slate-300 font-mono">{{ $loan->employee->employee_code }} • {{ $loan->employee->department->name ?? 'Dept' }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Nominal Yang Dicairkan') }}</span>
                <span class="text-2xl font-bold font-mono text-emerald-400 block mt-0.5">
                    {{ format_currency($loan->effective_principal, $loan->currency) }}
                </span>
                <span class="text-[11px] text-slate-300">{{ __('Tenor:') }} {{ $loan->tenor_months }} {{ __('Bulan') }}</span>
            </div>
        </div>
    </div>

    <!-- Disbursement Form -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-6">
        <form action="{{ route('loans.disburse', $loan) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Tanggal Pencairan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Tanggal Pencairan / Transfer') }}<span class="text-rose-500">*</span>
                </label>
                <input type="date" name="disbursed_at" value="{{ old('disbursed_at', date('Y-m-d')) }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white">
            </div>

            <!-- Metode Pembayaran -->
            <div class="space-y-2">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    {{ __('Metode Pencairan Dana') }} <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all"
                           :class="paymentMethod === 'bank_transfer' ? 'border-blue-600 bg-blue-50/50 text-blue-900 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                        <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="sr-only">
                        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                        <span class="text-xs font-bold">{{ __('Transfer Bank') }}</span>
                    </label>

                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all"
                           :class="paymentMethod === 'cash' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-900 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                        <input type="radio" name="payment_method" value="cash" x-model="paymentMethod" class="sr-only">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span class="text-xs font-bold">{{ __('Kasir / Tunai') }}</span>
                    </label>

                    <label class="relative flex items-center gap-2.5 p-3 rounded-xl border-2 cursor-pointer transition-all"
                           :class="paymentMethod === 'payroll' ? 'border-purple-600 bg-purple-50/50 text-purple-900 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                        <input type="radio" name="payment_method" value="payroll" x-model="paymentMethod" class="sr-only">
                        <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-xs font-bold">{{ __('Via Payroll') }}</span>
                    </label>
                </div>
            </div>

            <!-- No. Referensi Transfer -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">{{ __('Nomor Referensi Bank / No. Bukti Kas Keluar') }}</label>
                <input type="text" name="disbursement_reference" value="{{ old('disbursement_reference') }}" class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Contoh: BNI-TRF-202611-0982 atau BKK-0012') }}">
            </div>

            <!-- Upload Bukti Transfer -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">{{ __('Unggah Bukti Transfer / Resi Pembayaran') }}</label>
                <input type="file" name="disbursement_proof" @change="handleFile" accept=".pdf,.png,.jpg,.jpeg" class="w-full text-xs rounded-xl border border-slate-200 p-2 text-slate-700 bg-white">
                <template x-if="fileName">
                    <p class="text-[11px] font-semibold text-emerald-600" x-text="'{{ __('File terpilih:') }} ' + fileName"></p>
                </template>
            </div>

            <!-- Catatan Pencairan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">{{ __('Catatan Finansial (Opsional)') }}</label>
                <textarea name="disbursement_notes" rows="3" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Informasi tambahan terkait pencairan dana...') }}"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('loans.show', $loan) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ __('Konfirmasi Pencairan Dana') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


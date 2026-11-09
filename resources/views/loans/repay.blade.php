@extends('layouts.app')

@section('title', __('Pembayaran Angsuran & Pelunasan') . ': ' . $loan->loan_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12" x-data="{
    nextInstallmentAmount: {{ $nextInstallment ? (float) $nextInstallment->amount : (float) $loan->monthly_installment }},
    remainingBalance: {{ (float) $loan->remaining_amount }},
    repayType: 'single_installment',
    amount: '{{ $nextInstallment ? (float) $nextInstallment->amount : (float) $loan->monthly_installment }}',
    paymentMethod: 'bank_transfer',
    fileName: '',

    selectType(type) {
        this.repayType = type;
        if (type === 'single_installment') {
            this.amount = this.nextInstallmentAmount;
        } else if (type === 'full_settlement') {
            this.amount = this.remainingBalance;
        } else {
            this.amount = '';
            this.$nextTick(() => {
                if (this.$refs.amountInput) this.$refs.amountInput.focus();
            });
        }
    },

    formatNumber(val) {
        if (!val && val !== 0) return '0';
        const num = Number(val);
        if ('{{ $loan->currency }}' === 'SAR') {
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return num.toLocaleString('id-ID');
    },

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
                <span class="text-slate-700 font-semibold">{{ __('Catat Pembayaran') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-slate-900 to-slate-800 text-white rounded-xl shadow-md shadow-slate-900/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>{{ __('Pencatatan Angsuran / Pelunasan') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Catat pembayaran angsuran berkala atau pelunasan sisa pinjaman secara dipercepat.') }}</p>
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
                <span>{{ __('Terdapat kesalahan input pembayaran:') }}</span>
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
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">{{ __('Pinjaman Aktif') }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Pegawai Peminjam') }}</span>
                <span class="text-sm font-bold text-white block mt-0.5">{{ $loan->employee->full_name }}</span>
                <span class="text-[11px] text-slate-300 font-mono">{{ $loan->employee->employee_code }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Sisa Pokok Tagihan') }}</span>
                <span class="text-2xl font-bold font-mono text-rose-400 block mt-0.5">
                    {{ format_currency($loan->remaining_amount, $loan->currency) }}
                </span>
                <span class="text-[11px] text-slate-300">
                    {{ __(':paid dari :total cicilan lunas', ['paid' => $loan->paid_installments_count, 'total' => $loan->tenor_months]) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Repayment Form -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-6">
        <form action="{{ route('loans.repay', $loan) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Opsi Jenis Pembayaran -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">{{ __('Pilihan Skema Pembayaran') }}<span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Option 1: Single Installment -->
                    <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="repayType === 'single_installment' ? 'border-blue-600 bg-blue-50/50 text-blue-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                           @click="selectType('single_installment')">
                        <input type="radio" name="repay_type" value="single_installment" :checked="repayType === 'single_installment'" class="sr-only">
                        <span class="text-xs font-bold">{{ __('1 Kali Cicilan') }}</span>
                        <span class="text-[11px] font-mono text-slate-500 mt-1">
                            {{ format_currency($nextInstallment ? $nextInstallment->amount : $loan->monthly_installment, $loan->currency) }}
                        </span>
                        <span class="text-[10px] text-blue-600 font-medium mt-1">{{ __('Angsuran berkala') }}</span>
                    </label>

                    <!-- Option 2: Full Settlement -->
                    <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="repayType === 'full_settlement' ? 'border-purple-600 bg-purple-50/50 text-purple-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                           @click="selectType('full_settlement')">
                        <input type="radio" name="repay_type" value="full_settlement" :checked="repayType === 'full_settlement'" class="sr-only">
                        <span class="text-xs font-bold">{{ __('Pelunasan Penuh') }}</span>
                        <span class="text-[11px] font-mono text-slate-500 mt-1">
                            {{ format_currency($loan->remaining_amount, $loan->currency) }}
                        </span>
                        <span class="text-[10px] text-purple-600 font-medium mt-1">{{ __('Tutup pinjaman (100%)') }}</span>
                    </label>

                    <!-- Option 3: Custom Amount -->
                    <label class="relative flex flex-col p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="repayType === 'custom_amount' ? 'border-emerald-600 bg-emerald-50/50 text-emerald-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                           @click="selectType('custom_amount')">
                        <input type="radio" name="repay_type" value="custom_amount" :checked="repayType === 'custom_amount'" class="sr-only">
                        <span class="text-xs font-bold">{{ __('Nominal Khusus') }}</span>
                        <span class="text-[11px] text-slate-500 mt-1">{{ __('Input bebas') }}</span>
                        <span class="text-[10px] text-emerald-600 font-medium mt-1">{{ __('Pembayaran parsial') }}</span>
                    </label>
                </div>
            </div>

            <!-- Input Nominal Yang Dibayar -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">
                    {{ __('Nominal Pembayaran') }} ({{ $loan->currency }}) <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center w-full rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-600 transition-all cursor-text"
                    @click="$refs.amountInput?.focus()" dir="ltr">
                    <span class="text-xs font-mono font-bold text-slate-500 select-none shrink-0 mr-2">
                        {{ $loan->currency_symbol }}
                    </span>
                    <input type="number"
                           name="amount"
                           x-ref="amountInput"
                           x-model="amount"
                           min="1"
                           max="{{ (float) $loan->remaining_amount }}"
                           step="any"
                           required
                           class="w-full py-1 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0 text-left"
                           placeholder="{{ __('Ketik nominal bayar...') }}">
                </div>
                <div class="flex items-center justify-between text-[11px] pt-0.5">
                    <span class="text-slate-400">{{ __('Sisa pinjaman:') }} {{ format_currency($loan->remaining_amount, $loan->currency) }}</span>
                    <template x-if="amount">
                        <span class="font-mono text-emerald-600 font-bold" x-text="'{{ __('Nominal Dibayar:') }} {{ $loan->currency_symbol }} ' + formatNumber(amount)"></span>
                    </template>
                </div>
            </div>

            <!-- {{ __('Tanggal Pembayaran') }} & Metode -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700">
                        {{ __('Tanggal Pembayaran') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="paid_date" value="{{ old('paid_date', date('Y-m-d')) }}" required class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700">
                        {{ __('Metode Pembayaran') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="payment_method" x-model="paymentMethod" required class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white">
                        <option value="bank_transfer">{{ __('Transfer Bank') }}</option>
                        <option value="cash">{{ __('Kasir / Tunai Kantor') }}</option>
                        <option value="payroll">{{ __('Potong Slip Gaji Langsung') }}</option>
                    </select>
                </div>
            </div>

            <!-- {{ __('Nomor Referensi Transaksi') }} -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">
                    {{ __('Nomor Referensi Transaksi / No. Kuitansi') }}
                </label>
                <input type="text" name="reference_number" value="{{ old('reference_number') }}" class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Contoh: TRF-BNI-892182 atau KW-0092') }}">
            </div>

            <!-- Unggah Bukti Bayar -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">{{ __('Bukti Pembayaran / Struk Transfer (Opsional)') }}</label>
                <input type="file" name="proof_file" @change="handleFile" accept=".pdf,.png,.jpg,.jpeg" class="w-full text-xs rounded-xl border border-slate-200 p-2 text-slate-700 bg-white">
                <template x-if="fileName">
                    <p class="text-[11px] font-semibold text-emerald-600" x-text="'{{ __('File terpilih:') }} ' + fileName"></p>
                </template>
            </div>

            <!-- Catatan -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">{{ __('Catatan Pembayaran (Opsional)') }}</label>
                <textarea name="notes" rows="2" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Catatan tambahan pelunasan...') }}"></textarea>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('loans.show', $loan) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-md shadow-slate-900/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ __('Konfirmasi Pembayaran') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


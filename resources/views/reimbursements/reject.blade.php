@extends('layouts.app')

@section('title', 'Tolak Klaim: ' . $reimbursement->claim_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12" x-data="{
    reason: '{{ old('rejection_reason', '') }}',
    setReason(text) {
        if (this.reason) {
            this.reason += '. ' + text;
        } else {
            this.reason = text;
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Reimbursement') }}</a>
                <span>/</span>
                <a href="{{ route('reimbursements.show', $reimbursement) }}" class="hover:text-blue-600 font-medium font-mono">{{ $reimbursement->claim_number }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Penolakan') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-rose-600 to-red-600 text-white rounded-xl shadow-md shadow-rose-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>{{ __('Penolakan Pengajuan Klaim Reimbursement') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Berikan alasan penolakan resmi agar pegawai pemohon menerima penjelasan yang transparan.') }}</p>
        </div>

        <a href="{{ route('reimbursements.show', $reimbursement) }}" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap shrink-0 self-start sm:self-auto">
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
                <span>{{ __('Terdapat kesalahan pada input:') }}</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Box -->
    <div class="bg-gradient-to-br from-slate-900 via-rose-950 to-slate-900 text-white rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Nomor Pengajuan Klaim') }}</span>
                <div class="font-mono font-bold text-lg text-white mt-0.5">{{ $reimbursement->claim_number }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-400/30">{{ __('Menunggu Approval') }}</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <div class="text-[11px] text-slate-400">{{ __('Pegawai Pemohon:') }}</div>
                <div class="font-bold text-white text-sm mt-0.5">{{ $reimbursement->employee?->full_name }}</div>
                <div class="text-[11px] text-slate-300 font-mono mt-0.5">
                    {{ $reimbursement->employee?->employee_code }} • {{ $reimbursement->employee?->department?->name }} • {{ $reimbursement->branch?->name }}
                </div>
            </div>

            <div class="sm:text-right">
                <div class="text-[11px] text-slate-400">{{ __('Nominal Yang Diajukan:') }}</div>
                <div class="text-2xl font-bold font-mono text-white mt-0.5">
                    {{ format_currency($reimbursement->amount, $reimbursement->currency) }}
                </div>
                <div class="text-[10px] text-slate-400 mt-0.5">{{ __('Kategori:') }}<strong>{{ $reimbursement->category?->name }}</strong>
                </div>
            </div>
        </div>

        <!-- Rincian Transaksi -->
        <div class="p-3.5 rounded-2xl bg-white/5 border border-white/10 text-xs space-y-2">
            <div>
                <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Keperluan / Judul:') }}</div>
                <div class="font-semibold text-white mt-0.5">{{ $reimbursement->title }}</div>
            </div>
            @if($reimbursement->description)
<div class="text-[11px] text-slate-300 leading-relaxed border-t border-white/5 pt-2">
                    {{ $reimbursement->description }}
                </div>
            @endif
        </div>
    </div>

    <!-- Form Penolakan -->
    <form action="{{ route('reimbursements.reject', $reimbursement) }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
        @csrf

        <!-- Template Pilihan Alasan Cepat -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-2">{{ __('Pilih Alasan Cepat (Klik untuk menyisipkan):') }}</label>
            <div class="flex flex-wrap gap-2 text-xs">
                <button type="button" @click="setReason('{{ __('Bukti nota/struk buram atau tidak terbaca') }}')"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-medium transition">
                    {{ __('+ Bukti nota buram/tidak terbaca') }}
                </button>
                <button type="button" @click="setReason('{{ __('Pengeluaran bukan termasuk biaya dinas resmi perusahaan') }}')"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-medium transition">
                    {{ __('+ Bukan pengeluaran dinas resmi') }}
                </button>
                <button type="button" @click="setReason('{{ __('Nominal pengajuan tidak sesuai dengan kuitansi fisik') }}')"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-medium transition">
                    {{ __('+ Nominal tidak cocok dengan fisik') }}
                </button>
                <button type="button" @click="setReason('{{ __('Tanggal transaksi telah melewati batas toleransi klaim') }}')"
                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 text-[11px] font-medium transition">
                    {{ __('+ Melewati batas toleransi waktu') }}
                </button>
            </div>
        </div>

        <!-- Input Alasan Penolakan -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Alasan Penolakan Klaim *') }}</label>
            <textarea name="rejection_reason" rows="4" x-model="reason" required
                placeholder="{{ __('Tuliskan alasan penolakan secara jelas dan rinci...') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition">{{ old('rejection_reason') }}</textarea>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Alasan ini akan dikirimkan kepada pemohon klaim dan dicatat di audit log.') }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('reimbursements.show', $reimbursement) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap">{{ __('Batal') }}</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-500/20 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ __('Konfirmasi Tolak Klaim') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


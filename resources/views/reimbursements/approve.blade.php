@extends('layouts.app')

@section('title', 'Persetujuan Klaim: ' . $reimbursement->claim_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12" x-data="{
    originalAmount: {{ (float) $reimbursement->amount }},
    approvedAmount: '{{ old('approved_amount', (float) $reimbursement->amount) }}',
    isCustomAmount: {{ old('approved_amount') && old('approved_amount') != $reimbursement->amount ? 'true' : 'false' }},
    setFullApproval() {
        this.isCustomAmount = false;
        this.approvedAmount = this.originalAmount;
    },
    setPartialApproval() {
        this.isCustomAmount = true;
        this.approvedAmount = '';
        this.$nextTick(() => {
            if (this.$refs.approvedInput) {
                this.$refs.approvedInput.focus();
            }
        });
    },
    formatNumber(val) {
        if (!val && val !== 0) return '0';
        const num = Number(val);
        if ('{{ $reimbursement->currency }}' === 'SAR') {
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return num.toLocaleString('id-ID');
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
                <span class="text-slate-700 font-semibold">{{ __('Persetujuan') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>{{ __('Persetujuan Klaim Reimbursement') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Verifikasi rincian pengeluaran dinas, tetapkan nominal yang disetujui, dan sahkan klaim.') }}</p>
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
                <span>{{ __('Terdapat kesalahan pada input persetujuan:') }}</span>
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
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Nomor Pengajuan Klaim') }}</span>
                <div class="font-mono font-bold text-lg text-white mt-0.5">{{ $reimbursement->claim_number }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-400/30">{{ __('Menunggu Approval') }}</span>
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
                <div class="text-[11px] text-slate-400">{{ __('Nominal Diajukan:') }}</div>
                <div class="text-2xl font-bold font-mono text-emerald-400 mt-0.5">
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
            @if($reimbursement->receipt_path)
<div class="pt-2 border-t border-white/5 flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">{{ __('Lampiran Bukti Nota:') }}</span>
                    <a href="{{ $reimbursement->receipt_url }}" target="_blank" class="text-blue-300 hover:text-white underline font-semibold inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('Lihat Berkas Nota') }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Form Persetujuan -->
    <form action="{{ route('reimbursements.approve', $reimbursement) }}" method="POST" class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
        @csrf

        <!-- 1. Skema Nominal Persetujuan -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-2">{{ __('Skema Nominal Persetujuan *') }}</label>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                <button type="button" @click="setFullApproval()"
                    class="p-3.5 rounded-2xl border text-left transition-all flex items-start gap-2.5 cursor-pointer"
                    :class="!isCustomAmount ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-bold shadow-xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                    <span class="w-4 h-4 rounded-full border flex items-center justify-center mt-0.5 shrink-0"
                        :class="!isCustomAmount ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300'">
                        <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="!isCustomAmount"></span>
                    </span>
                    <div>
                        <div class="font-bold">{{ __('Setujui Penuh (100%)') }}</div>
                        <div class="text-[11px] font-mono text-slate-500 font-normal mt-0.5">
                            {{ format_currency($reimbursement->amount, $reimbursement->currency) }}
                        </div>
                    </div>
                </button>

                <button type="button" @click="setPartialApproval()"
                    class="p-3.5 rounded-2xl border text-left transition-all flex items-start gap-2.5 cursor-pointer"
                    :class="isCustomAmount ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-bold shadow-xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                    <span class="w-4 h-4 rounded-full border flex items-center justify-center mt-0.5 shrink-0"
                        :class="isCustomAmount ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300'">
                        <span class="w-1.5 h-1.5 rounded-full bg-white" x-show="isCustomAmount"></span>
                    </span>
                    <div>
                        <div class="font-bold">{{ __('Setujui Sebagian') }}</div>
                        <div class="text-[11px] text-slate-500 font-normal mt-0.5">{{ __('Koreksi nominal yang disetujui (dikosongkan untuk diketik manual)') }}</div>
                    </div>
                </button>
            </div>
        </div>

        <!-- 2. Input Nominal Yang Disetujui -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                <span>{{ __('Nominal Disetujui') }} ({{ $reimbursement->currency }}) *</span>
                <span x-show="isCustomAmount" class="text-[11px] font-normal text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">{{ __('Mode Ketik Nominal Sebagian Aktif') }}</span>
            </label>
            <div class="flex items-center w-full rounded-xl border bg-white px-3.5 py-1.5 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all cursor-text"
                :class="isCustomAmount && !approvedAmount ? 'border-blue-400 bg-blue-50/20 ring-2 ring-blue-500/10' : 'border-slate-200'"
                @click="$refs.approvedInput?.focus()">
                <span class="text-xs font-mono font-bold text-slate-400 select-none shrink-0">
                    {{ $reimbursement->currency === 'SAR' ? 'SAR' : 'Rp' }}
                </span>
                <input type="number" step="0.01" min="0.01" max="{{ $reimbursement->amount }}" name="approved_amount"
                    x-ref="approvedInput"
                    x-model="approvedAmount" required
                    :placeholder="isCustomAmount ? '{{ __('Ketik nominal yang disetujui di sini...') }}' : '{{ $reimbursement->amount }}'"
                    class="w-full px-2.5 py-1 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0">
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between text-[11px] gap-1 mt-1.5">
                <span class="text-slate-400">{{ __('Maksimal yang dapat disetujui:') }}<strong class="text-slate-600 font-mono">{{ format_currency($reimbursement->amount, $reimbursement->currency) }}</strong>
                </span>
                <template x-if="isCustomAmount && approvedAmount && Number(approvedAmount) < originalAmount">
                    <span class="text-amber-600 font-semibold font-mono flex items-center gap-1">
                        <span>{{ __('Penyesuaian Potongan:') }}</span>
                        <span>-{{ $reimbursement->currency === 'SAR' ? 'SAR ' : 'Rp ' }}<span x-text="formatNumber(originalAmount - Number(approvedAmount))"></span></span>
                    </span>
                </template>
                <template x-if="isCustomAmount && !approvedAmount">
                    <span class="text-blue-600 font-medium italic">
                        {{ __('← Silakan ketik nominal yang disetujui pada kotak di atas') }}
                    </span>
                </template>
            </div>
        </div>

        <!-- 3. Catatan Persetujuan -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Catatan Persetujuan (Opsional)') }}</label>
            <textarea name="notes" rows="3" placeholder="{{ __('Tuliskan catatan verifikasi untuk bagian Finance atau penjelasan bila nominal disetujui sebagian...') }}"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ old('notes') }}</textarea>
            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Catatan ini akan tersimpan pada riwayat klaim dan dapat dibaca oleh Finance saat pencairan.') }}</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
            <a href="{{ route('reimbursements.show', $reimbursement) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap">{{ __('Batal') }}</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Konfirmasi Setujui Klaim') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


@extends('layouts.app')

@section('title', __('Persetujuan Pinjaman') . ': ' . $loan->loan_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12" x-data="{
    originalAmount: {{ (float) $loan->amount }},
    approvedAmount: '{{ old('approved_amount', (float) $loan->amount) }}',
    isCustomAmount: {{ old('approval_action') === 'partial' ? 'true' : 'false' }},
    tenor: {{ old('tenor_months', $loan->tenor_months) }},
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
    get monthlyEstimate() {
        const amt = parseFloat(this.approvedAmount) || 0;
        const ten = parseInt(this.tenor) || 1;
        if (ten <= 0) return 0;
        return Math.round(amt / ten);
    },
    formatNumber(val) {
        if (!val && val !== 0) return '0';
        const num = Number(val);
        if ('{{ $loan->currency }}' === 'SAR') {
            return num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        return num.toLocaleString('id-ID');
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
                <span class="text-slate-700 font-semibold">{{ __('Persetujuan') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-emerald-600 to-teal-600 text-white rounded-xl shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </span>
                {{ __('Persetujuan Pinjaman Karyawan') }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                {{ __('Tentukan plafon nominal yang disetujui penuh atau disetujui sebagian, sesuaikan tenor jika diperlukan, dan berikan otorisasi.') }}
            </p>
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
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Nomor Pinjaman') }}</span>
                <div class="font-mono font-bold text-lg text-white mt-0.5">{{ $loan->loan_number }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-400/30">{{ __('Menunggu Approval') }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Pegawai Pemohon') }}</span>
                <span class="text-sm font-bold text-white block mt-0.5">{{ $loan->employee->full_name }}</span>
                <span class="text-[11px] text-slate-300 font-mono">{{ $loan->employee->employee_code }} • {{ $loan->employee->department->name ?? 'Dept' }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Nominal Yang Diajukan') }}</span>
                <span class="text-xl font-bold font-mono text-emerald-400 block mt-0.5">
                    {{ format_currency($loan->amount, $loan->currency) }}
                </span>
                <span class="text-[11px] text-slate-300">{{ __('Tenor:') }} {{ $loan->tenor_months }} {{ __('Bulan') }}</span>
            </div>
        </div>

        <div class="bg-white/5 rounded-2xl p-4 border border-white/10 space-y-2">
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-semibold">{{ __('Keperluan / Alasan:') }}</span>
                <p class="text-xs text-slate-200 mt-0.5 leading-relaxed">{{ $loan->reason }}</p>
            </div>
            @if($loan->attachment_file)
<div class="pt-2 border-t border-white/10 flex items-center justify-between text-xs">
                    <span class="text-slate-400 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                        </svg>{{ __('Lampiran Permohonan') }}</span>
                    <a href="{{ Storage::url($loan->attachment_file) }}" target="_blank" class="text-blue-300 hover:text-white font-semibold underline">{{ __('Lihat Berkas') }}</a>
                </div>
            @endif
        </div>
    </div>

    <!-- Approval Form (Dedicated Page, Non-Modal) -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-6">
        <form action="{{ route('loans.approve', $loan) }}" method="POST" class="space-y-6">
            @csrf

            <!-- {{ __('Skema Nominal Persetujuan') }} -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    {{ __('Skema Nominal Persetujuan') }} <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="!isCustomAmount ? 'border-emerald-600 bg-emerald-50/50 text-emerald-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                           @click="setFullApproval()">
                        <input type="radio" name="approval_action" value="full" :checked="!isCustomAmount" class="sr-only">
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                             :class="!isCustomAmount ? 'border-emerald-600 bg-emerald-600' : 'border-slate-300'">
                            <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="!isCustomAmount"></div>
                        </div>
                        <div>
                            <span class="text-xs font-bold block">{{ __('Setujui Penuh (100%)') }}</span>
                            <span class="text-[11px] font-mono text-slate-500">{{ format_currency($loan->amount, $loan->currency) }}</span>
                        </div>
                    </label>

                    <label class="relative flex items-center gap-3 p-3.5 rounded-2xl border-2 cursor-pointer transition-all"
                           :class="isCustomAmount ? 'border-blue-600 bg-blue-50/50 text-blue-950 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'"
                           @click="setPartialApproval()">
                        <input type="radio" name="approval_action" value="partial" :checked="isCustomAmount" class="sr-only">
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center shrink-0"
                             :class="isCustomAmount ? 'border-blue-600 bg-blue-600' : 'border-slate-300'">
                            <div class="w-1.5 h-1.5 rounded-full bg-white" x-show="isCustomAmount"></div>
                        </div>
                        <div>
                            <span class="text-xs font-bold block">{{ __('Setujui Sebagian') }}</span>
                            <span class="text-[11px] text-slate-500">{{ __('Koreksi nominal yang disetujui') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Input {{ __('Nominal Disetujui') }} -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">
                    {{ __('Nominal Disetujui') }} ({{ $loan->currency }}) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute left-3.5 top-2.5 text-xs font-mono font-bold text-slate-400">
                        {{ $loan->currency_symbol }}
                    </span>
                    <input type="number"
                           name="approved_amount"
                           x-ref="approvedInput"
                           x-model="approvedAmount"
                           :readonly="!isCustomAmount"
                           min="1"
                           max="{{ (float) $loan->amount }}"
                           step="any"
                           required
                           class="w-full text-xs font-mono font-bold rounded-xl border pl-12 pr-4 py-2.5 text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition-all"
                           :class="!isCustomAmount ? 'bg-slate-50 border-slate-200 text-slate-600' : 'bg-white border-blue-500 text-slate-900 ring-2 ring-blue-500/10'"
                           placeholder="{{ __('Ketik nominal persetujuan...') }}">
                </div>
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-slate-400">Maksimal yang dapat disetujui: {{ format_currency($loan->amount, $loan->currency) }}</span>
                    <template x-if="approvedAmount">
                        <span class="font-mono text-emerald-600 font-bold" x-text="'Persetujuan: {{ $loan->currency_symbol }} ' + formatNumber(approvedAmount)"></span>
                    </template>
                </div>
            </div>

            <!-- Tenor Penyesuaian -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">
                    {{ __('Jangka Waktu Tenor Disetujui (Bulan)') }} <span class="text-rose-500">*</span>
                </label>
                <div class="flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-600 transition-all bg-white">
                    <input type="number" name="tenor_months" x-model.number="tenor" min="1" max="60" required class="w-full text-xs font-bold px-3.5 py-2.5 text-slate-900 focus:outline-none border-0">
                    <span class="inline-flex items-center px-4 bg-slate-100 text-slate-800 font-bold text-xs border-s border-slate-200 shrink-0 select-none">{{ __('Bulan') }}</span>
                </div>
                <p class="text-[11px] text-slate-500 font-mono">{{ __('Perkiraan cicilan per bulan:') }} <strong class="text-emerald-700" x-text="'{{ $loan->currency_symbol }} ' + formatNumber(monthlyEstimate) + ' {{ __('/ bln') }}'"></strong>
                </p>
            </div>

            <!-- Approval Notes -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700">
                    {{ __('Catatan Persetujuan (Opsional)') }}
                </label>
                <textarea name="approval_notes" rows="3" class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Tambahkan catatan atau pesan untuk karyawan dan bagian finance...') }}"></textarea>
                <span class="text-[11px] text-slate-400">{{ __('Catatan ini akan tersimpan pada riwayat pinjaman dan dapat dibaca oleh Finance saat pencairan.') }}</span>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('loans.show', $loan) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ __('Konfirmasi Setujui Pinjaman') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', __('Catat Transaksi Investor: :name', ['name' => $investor->name]))

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12" x-data="{
    scheme: '{{ $investor->profit_share_scheme }}',
    capitalDefault: {{ (float) $investor->initial_investment }},
    distType: '{{ old('type', 'profit_share') }}',
    basisAmount: {{ old('basis_amount', $investor->profit_share_scheme === 'fixed_monthly' ? (float) $investor->initial_investment : 0) }},
    percentageApplied: {{ old('percentage_applied', $investor->profit_share_percentage) }},
    distAmount: '{{ old('amount', '') }}',
    stipendDefault: {{ (float) $investor->fixed_stipend_amount }},
    currencyPrefix: '{{ $investor->currency === 'SAR' ? 'SAR' : 'Rp' }}',
    status: '{{ old('status', 'paid') }}',
    calcAmount() {
        if (this.distType === 'profit_share') {
            let b = parseFloat(this.basisAmount) || 0;
            let p = parseFloat(this.percentageApplied) || 0;
            if (b > 0 && p > 0) {
                this.distAmount = (b * (p / 100)).toFixed(2);
            }
        } else if (this.distType === 'stipend') {
            this.distAmount = this.stipendDefault.toFixed(2);
        }
    },
    init() {
        if (!this.distAmount) {
            if (this.distType === 'stipend') {
                this.calcAmount();
            } else if (this.distType === 'profit_share' && this.scheme === 'fixed_monthly') {
                this.calcAmount();
            }
        }
    }
}">
    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('investors.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Investor') }}</a>
                <span>/</span>
                <a href="{{ route('investors.show', $investor) }}" class="hover:text-blue-600 font-medium transition-colors">{{ $investor->investor_code }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Transaksi Baru') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{!! __('Catat Transaksi Bagi Hasil & Gaji Khusus') !!}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Pencatatan realisasi dividen berkala, imbalan tetap bulanan, atau pengembalian pokok modal investor.') }}</p>
        </div>

        <a href="{{ route('investors.show', $investor) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>{{ __('Kembali') }}</a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>{{ __('Terdapat kesalahan pengisian data:') }}</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Mini Ringkasan Investor -->
    <div class="p-5 rounded-3xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr {{ $investor->currency === 'SAR' ? 'from-emerald-600 to-teal-700' : 'from-blue-600 to-indigo-700' }} text-white font-bold flex items-center justify-center shrink-0 shadow-xs">
                {{ strtoupper(substr($investor->name, 0, 2)) }}
            </div>
            <div>
                <span class="text-sm font-bold text-slate-900 block">{{ $investor->name }}</span>
                <span class="text-xs text-slate-500 font-mono">{{ $investor->investor_code }} · {{ $investor->branch ? $investor->branch->name : 'Operasional Perusahaan' }}</span>
            </div>
        </div>
        <div class="flex items-center gap-4 text-xs font-mono">
            <div>
                <span class="text-[10px] text-slate-400 font-sans block">{{ __('Modal Tertanam') }}</span>
                <strong class="text-slate-800">{{ format_currency($investor->initial_investment, $investor->currency) }}</strong>
            </div>
            <div class="border-s border-slate-200 ps-4">
                <span class="text-[10px] text-slate-400 font-sans block">{{ __('Progres BEP') }}</span>
                <strong class="text-emerald-600">{{ $investor->bep_progress_percentage }}%</strong>
            </div>
        </div>
    </div>

    <form action="{{ route('investors.distributions.store', $investor) }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Detail Transaksi -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h2 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>{{ __('Parameter Transaksi & Jenis Distribusi') }}</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nomor Transaksi') }}</label>
                        <input type="text" name="distribution_number" value="{{ old('distribution_number', $suggestedNumber) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono font-bold border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none uppercase">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Jenis Distribusi') }}<span class="text-rose-500">*</span></label>
                        <select name="type" x-model="distType" @change="calcAmount()" required
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                            <option value="profit_share">{{ __('Bagi Hasil / Dividen Berkala (Net Profit Share)') }}</option>
                            <option value="stipend">{{ __('Gaji Khusus / Imbalan Pengawas Bulanan') }}</option>
                            <option value="capital_return">{{ __('Pengembalian Pokok Modal') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Label Periode Distribusi') }}<span class="text-rose-500">*</span></label>
                        <input type="text" name="period_label" value="{{ old('period_label', now()->locale(app()->getLocale())->translatedFormat('F Y')) }}" placeholder="{{ __('Contoh: November 2026 / Q4 2026') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Distribusi / Jatuh Tempo') }}<span class="text-rose-500">*</span></label>
                        <input type="date" name="distribution_date" value="{{ old('distribution_date', date('Y-m-d')) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                    </div>
                </div>

                <!-- Bagian Kalkulasi Simulasi Sesuai Skema Acuan Investor -->
                <div x-show="distType === 'profit_share'" x-transition class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-3">
                    <span class="text-xs font-bold text-blue-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ __('Simulasi Otomatis (Basis Acuan: :scheme)', ['scheme' => $investor->profit_share_scheme === 'gross_revenue' ? __('Omset Kotor Proyek') : ($investor->profit_share_scheme === 'fixed_monthly' ? __('Dividen Tetap Berkala dari Modal') : __('Laba Bersih Proyek'))]) }}
                    </span>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                @if($investor->profit_share_scheme === 'gross_revenue')
                                    {{ __('Omset Kotor Acuan Proyek') }}
                                @elseif($investor->profit_share_scheme === 'fixed_monthly')
                                    {{ __('Modal Awal Acuan Dividen') }}
                                @else
                                    {{ __('Laba Bersih Acuan Proyek') }}
                                @endif
                            </label>
                            <div class="flex items-center w-full rounded-xl border border-slate-200 bg-white px-3 py-1 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all cursor-text"
                                @click="$refs.basisAmountInput?.focus()">
                                <span class="text-xs font-mono font-bold text-slate-400 select-none shrink-0" x-text="currencyPrefix"></span>
                                <input type="number" step="any" min="0" name="basis_amount" x-ref="basisAmountInput" x-model="basisAmount" @input="calcAmount()"
                                    placeholder="0.00" class="w-full px-2 py-1 text-xs font-mono font-bold border-0 bg-transparent outline-none focus:ring-0">
                            </div>
                            <p class="text-[10px] text-slate-400 mt-1">
                                @if($investor->profit_share_scheme === 'gross_revenue')
                                    {{ __('Kalkulasi: Omset Kotor × :pct%', ['pct' => number_format($investor->profit_share_percentage, 2)]) }}
                                @elseif($investor->profit_share_scheme === 'fixed_monthly')
                                    {{ __('Otomatis: Modal Awal (:amount) × :pct%', ['amount' => format_currency($investor->initial_investment, $investor->currency), 'pct' => number_format($investor->profit_share_percentage, 2)]) }}
                                @else
                                    {{ __('Kalkulasi: Laba Bersih × :pct%', ['pct' => number_format($investor->profit_share_percentage, 2)]) }}
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                                {{ $investor->profit_share_scheme === 'fixed_monthly' ? __('Tingkat Dividen Tetap (%)') : __('Porsi Bagi Hasil (%)') }}
                            </label>
                            <div class="relative">
                                <input type="number" step="0.01" min="0" max="100" name="percentage_applied" x-model="percentageApplied" @input="calcAmount()"
                                    class="w-full pr-8 pl-3.5 py-2 rounded-xl text-xs font-mono font-bold border border-slate-200 bg-white focus:border-blue-500 outline-none">
                                <span class="absolute right-3 top-2 text-xs font-bold text-slate-400">%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Nominal Akhir -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nominal Yang Diberikan / Diterima Investor') }}<span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-1 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all cursor-text"
                        @click="$refs.distAmountInput?.focus()">
                        <span class="text-sm font-mono font-bold text-slate-600 select-none shrink-0" x-text="currencyPrefix"></span>
                        <input type="number" step="any" min="0.01" name="amount" x-ref="distAmountInput" x-model="distAmount" required
                            placeholder="0.00"
                            class="w-full px-2.5 py-1.5 text-base font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">{{ __('Nominal resmi yang akan dibukukan dan tercatat pada akumulasi pengembalian modal (BEP).') }}</p>
                </div>
            </div>
        </div>

        <!-- 2. Status & Detail Pembayaran -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h2 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">2</span>{{ __('Status & Realisasi Pembayaran Dana') }}</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Status Pembayaran') }}<span class="text-rose-500">*</span></label>
                        <select name="status" x-model="status" required class="w-full px-3.5 py-2.5 rounded-xl text-xs font-semibold border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                            <option value="paid">✓ {{ __('Lunas Terbayar (Paid)') }}</option>
                            <option value="approved">{{ __('Disetujui (Approved / Menunggu Transfer)') }}</option>
                            <option value="pending">{{ __('Dijadwalkan (Pending)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Metode Pembayaran') }}</label>
                        <select name="payment_method" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                            <option value="bank_transfer">{{ __('Transfer Bank Resmi') }}</option>
                            <option value="cash">{{ __('Tunai / Kas Proyek') }}</option>
                            <option value="cheque">{{ __('Cek / Bilyet Giro') }}</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Transfer / Pembayaran') }}</label>
                        <input type="date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nomor Referensi Transfer / No Slip') }}</label>
                        <input type="text" name="payment_reference" value="{{ old('payment_reference') }}" placeholder="{{ __('Contoh: REF-BCA-981244 / TX-ALRAJHI-891') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl text-xs font-mono border border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Catatan Tambahan (Opsional)') }}</label>
                    <textarea name="notes" rows="2" placeholder="{{ __('Catatan perihal distribusi dana...') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80">
            <a href="{{ route('investors.show', $investor) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">{{ __('Batal') }}</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>{{ __('Simpan & Catat Transaksi') }}</button>
        </div>
    </form>
</div>
@endsection

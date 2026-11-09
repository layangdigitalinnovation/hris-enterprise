@extends('layouts.app', ['headerTitle' => __('Pengaturan Kurs Valuta Asing'), 'title' => __('Atur Kurs SAR ⇄ IDR')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    rate: {{ (float) $currentRate }},
    testSar: 6000,
    testIdr: 5000000,
    presets: [4150, 4200, 4250, 4300, 4350, 4400, 4500],
    setPreset(val) {
        this.rate = val;
    },
    calcSarToIdr(amount) {
        return (Number(amount || 0) * Number(this.rate || 0)).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    },
    calcIdrToSar(amount) {
        if (!this.rate || this.rate <= 0) return '0.00';
        return (Number(amount || 0) / Number(this.rate)).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatNumber(val, dec = 0) {
        return Number(val || 0).toLocaleString('id-ID', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    }
}">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ $redirectTo }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Penyesuaian Kurs Valuta Asing') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Penyesuaian Kurs Valuta Asing (SAR ⇄ IDR)') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Sesuaikan nilai tukar mata uang Riyal Arab Saudi (SAR) dan Rupiah Indonesia (IDR) secara realtime saat terjadi fluktuasi nilai kurs.') }}</p>
        </div>
        <a href="{{ $redirectTo }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada input:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payroll.exchange-rate.update') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

        <!-- 1. Nilai Kurs Valuta Utama -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">1</span>
                    {{ __('Nilai Kurs Acuan Resmi (1 SAR ke IDR)') }}
                </h4>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    🇸🇦 SAR ⇄ 🇮🇩 IDR
                </span>
            </div>

            <div class="space-y-3">
                <label class="block font-semibold text-slate-700 text-xs">
                    {{ __('Nilai Tukar 1 Riyal Arab Saudi (SAR) ke Rupiah Indonesia (IDR) *') }}
                </label>

                <div class="flex items-center rounded-2xl border-2 border-emerald-500/80 bg-white shadow-xs overflow-hidden focus-within:ring-4 focus-within:ring-emerald-500/20 transition max-w-lg">
                    <span class="px-4 py-3 bg-emerald-600 text-white font-mono font-bold text-sm select-none shrink-0 flex items-center gap-1.5">
                        <span>Rp</span>
                    </span>
                    <input type="number" 
                        name="exchange_rate_sar_to_idr" 
                        x-model.number="rate" 
                        required 
                        min="1" 
                        step="1"
                        placeholder="Contoh: 4250"
                        class="w-full px-4 py-3 bg-transparent border-0 text-slate-900 font-mono font-extrabold text-xl focus:outline-none focus:ring-0">
                    <span class="px-4 py-3 text-xs font-bold text-slate-400 font-mono select-none shrink-0">
                        / 1 SAR
                    </span>
                </div>

                <!-- Pilihan Cepat Fluktuasi Kurs -->
                <div class="space-y-1.5 pt-2">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">{{ __('Pilihan Cepat Fluktuasi Kurs Riyal (Quick Presets):') }}</span>
                    <div class="flex items-center gap-2 flex-wrap">
                        <template x-for="p in presets" :key="p">
                            <button type="button" 
                                @click="setPreset(p)"
                                :class="rate == p ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'"
                                class="px-3 py-1.5 rounded-xl border text-xs font-mono font-semibold transition cursor-pointer flex items-center gap-1">
                                <span>Rp</span>
                                <span x-text="formatNumber(p)"></span>
                                <span x-show="p == 4250" class="text-[9px] font-normal opacity-80">({{ __('Standar') }})</span>
                            </button>
                        </template>
                    </div>
                    <span class="text-[11px] text-slate-400 block mt-1">{{ __('Klik tombol di atas untuk memilih nilai kurs acuan saat mata uang Riyal naik atau turun.') }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Kalkulator Simulasi Konversi Dua Arah -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                    {{ __('Kalkulator Simulasi Konversi Dua Arah (Live Calculator)') }}
                </h4>
                <span class="text-[11px] text-slate-400 font-mono">1 IDR ≈ <span x-text="calcIdrToSar(1)"></span> SAR</span>
            </div>

            <p class="text-xs text-slate-500">{{ __('Uji coba langsung perhitungan konversi nilai uang antara Riyal dan Rupiah secara instan.') }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <!-- SAR ke IDR -->
                <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-emerald-900 text-xs flex items-center gap-1">
                            <span>🇸🇦 {{ __('SAR ➔ IDR (Rupiah)') }}</span>
                        </label>
                        <span class="text-[10px] text-emerald-700 font-mono font-semibold">{{ __('Riyal ke Rupiah') }}</span>
                    </div>

                    <div class="flex items-center rounded-xl border border-emerald-300 bg-white px-3 py-2 focus-within:ring-2 focus-within:ring-emerald-500/20" dir="ltr">
                        <span class="text-emerald-700 font-mono font-bold text-xs mr-2">SAR</span>
                        <input type="number" x-model.number="testSar" min="0" step="100" class="w-full text-xs font-mono font-bold text-slate-900 border-0 p-0 focus:outline-none focus:ring-0 text-left">
                    </div>

                    <div class="pt-2 border-t border-emerald-200/60 flex items-center justify-between text-xs">
                        <span class="text-slate-600">{{ __('Hasil Ekuivalen:') }}</span>
                        <span class="font-mono font-extrabold text-emerald-700 text-sm" dir="ltr">
                            Rp <span x-text="calcSarToIdr(testSar)"></span>
                        </span>
                    </div>
                </div>

                <!-- IDR ke SAR -->
                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200/80 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-blue-900 text-xs flex items-center gap-1">
                            <span>🇮🇩 {{ __('IDR ➔ SAR (Riyal)') }}</span>
                        </label>
                        <span class="text-[10px] text-blue-700 font-mono font-semibold">{{ __('Rupiah ke Riyal') }}</span>
                    </div>

                    <div class="flex items-center rounded-xl border border-blue-300 bg-white px-3 py-2 focus-within:ring-2 focus-within:ring-blue-500/20" dir="ltr">
                        <span class="text-blue-700 font-mono font-bold text-xs mr-2">Rp</span>
                        <input type="number" x-model.number="testIdr" min="0" step="100000" class="w-full text-xs font-mono font-bold text-slate-900 border-0 p-0 focus:outline-none focus:ring-0 text-left">
                    </div>

                    <div class="pt-2 border-t border-blue-200/60 flex items-center justify-between text-xs">
                        <span class="text-slate-600">{{ __('Hasil Ekuivalen:') }}</span>
                        <span class="font-mono font-extrabold text-blue-700 text-sm" dir="ltr">
                            SAR <span x-text="calcIdrToSar(testIdr)"></span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabel Acuan Benchmark Standar Gaji -->
            <div class="pt-2 border-t border-slate-100">
                <span class="text-[11px] font-bold text-slate-700 block mb-2">{{ __('Simulasi Nilai Acuan Gaji Standar Perusahaan:') }}</span>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-slate-800 block">🇸🇦 {{ __('Acuan Gaji Pokok Saudi (SAR 6.000)') }}</span>
                            <span class="text-[10px] text-slate-400">{{ __('Standar Pasal 107 KSA') }}</span>
                        </div>
                        <span class="font-mono font-bold text-emerald-700 text-xs" dir="ltr">
                            Rp <span x-text="calcSarToIdr(6000)"></span>
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-slate-800 block">🇮🇩 {{ __('Acuan Gaji Pokok RI (Rp 5.000.000)') }}</span>
                            <span class="text-[10px] text-slate-400">{{ __('Standar Depnaker RI') }}</span>
                        </div>
                        <span class="font-mono font-bold text-blue-700 text-xs" dir="ltr">
                            SAR <span x-text="calcIdrToSar(5000000)"></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Informasi Ruang Lingkup Sistem -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1.5">
            <div class="font-bold text-slate-800 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ __('Dampak Penyesuaian Nilai Kurs ke Sistem') }}
            </div>
            <p>{{ __('Nilai kurs yang disimpan akan langsung diterapkan pada seluruh kartu ringkasan multi-mata uang payroll, estimasi konversi grand total bersih, dan laporan ekspor Excel konsolidasi.') }}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ $redirectTo }}" class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Perubahan Kurs') }}
            </button>
        </div>
    </form>

</div>
@endsection

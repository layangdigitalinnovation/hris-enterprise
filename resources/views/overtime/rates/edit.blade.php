@extends('layouts.app', ['headerTitle' => __('Edit Rate Lembur Standar'), 'title' => __('Edit Rate Lembur')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ calcType: '{{ old('calculation_type', $overtimeRate->calculation_type) }}' }">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('overtime.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Lembur') }}</a>
                <span>/</span>
                <a href="{{ route('overtime.index', ['tab' => 'rates']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Kebijakan & Rate') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit: :name', ['name' => $overtimeRate->name]) }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Master Rate Lembur') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Perbarui formula, nilai tarif dasar per jam, atau faktor pengali untuk :name', ['name' => $overtimeRate->name]) }}</p>
        </div>
        <a href="{{ route('overtime.index', ['tab' => 'rates']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada input:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('overtime.rates.update', $overtimeRate) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- 1. Identitas Aturan Rate --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Identitas & Ruang Lingkup Hari') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Aturan Rate *') }}</label>
                    <input type="text" name="name" value="{{ old('name', $overtimeRate->name) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Unik Rate *') }}</label>
                    <input type="text" value="{{ $overtimeRate->code }}" disabled
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-500 font-mono uppercase text-xs cursor-not-allowed">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Kode acuan sistem tidak dapat diubah.') }}</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tipe Hari yang Berlaku *') }}</label>
                    <select name="day_type" required 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="weekday" {{ old('day_type', $overtimeRate->day_type) === 'weekday' ? 'selected' : '' }}>{{ __('Hari Kerja Biasa (Senin - Jumat)') }}</option>
                        <option value="weekend" {{ old('day_type', $overtimeRate->day_type) === 'weekend' ? 'selected' : '' }}>{{ __('Akhir Pekan (Sabtu / Minggu)') }}</option>
                        <option value="holiday" {{ old('day_type', $overtimeRate->day_type) === 'holiday' ? 'selected' : '' }}>{{ __('Hari Libur Nasional / Cuti Bersama') }}</option>
                        <option value="all" {{ old('day_type', $overtimeRate->day_type) === 'all' ? 'selected' : '' }}>{{ __('Berlaku untuk Semua Hari') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Keberlakuan *') }}</label>
                    <select name="is_active" required 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="1" {{ old('is_active', $overtimeRate->is_active) ? 'selected' : '' }}>{{ __('Aktif (Digunakan dalam Kalkulasi)') }}</option>
                        <option value="0" {{ !old('is_active', $overtimeRate->is_active) ? 'selected' : '' }}>{{ __('Non-Aktif (Dihentikan Sementara)') }}</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_default" value="1" {{ old('is_default', $overtimeRate->is_default) ? 'checked' : '' }}
                        class="w-4 h-4 text-amber-600 rounded border-slate-300 focus:ring-amber-500">
                    <span class="text-xs font-semibold text-slate-800">{{ __('Jadikan sebagai Master Rate Lembur Default Perusahaan') }}</span>
                </label>
                <span class="text-[11px] text-slate-400 block ml-6 mt-0.5">
                    {{ __('Jika dicentang, aturan ini akan menjadi skema acuan utama perusahaan dan sinkron dengan pengaturan kebijakan lembur global.') }}
                </span>
            </div>
        </div>

        {{-- 2. Formula Perhitungan & Nilai Tarif --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Formula Perhitungan & Nilai Tarif') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Metode Perhitungan *') }}</label>
                    <select name="calculation_type" x-model="calcType" required 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="flat">{{ __('Tarif Tetap Nominal (Rp / Jam)') }}</option>
                        <option value="depnaker_formula">{{ __('Formula Resmi Depnaker (1/173 × Gaji Pokok)') }}</option>
                        <option value="percentage">{{ __('Persentase Gaji Pokok (%)') }}</option>
                        <option value="saudi_article_107">🇸🇦 {{ __('Pasal 107 UU Arab Saudi (Upah/Jam + 50% Gaji Pokok/Jam)') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">
                        <span x-show="calcType === 'flat'">{{ __('Tarif per Jam *') }}</span>
                        <span x-show="calcType !== 'flat'">{{ __('Tarif Acuan Dasar *') }}</span>
                    </label>
                    <div class="flex items-center rounded-xl border border-slate-300 bg-white shadow-2xs overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                        <span class="px-3.5 py-2.5 bg-slate-100 text-slate-600 font-mono font-bold text-xs border-r rtl:border-r-0 rtl:border-l border-slate-200 select-none shrink-0">
                            <span x-show="calcType === 'saudi_article_107'">SAR</span>
                            <span x-show="calcType !== 'saudi_article_107'">Rp</span>
                        </span>
                        <input type="number" name="rate_per_hour" value="{{ old('rate_per_hour', (float)$overtimeRate->rate_per_hour) }}" required min="0" step="any"
                            placeholder="0"
                            class="w-full px-3.5 py-2.5 bg-transparent border-0 text-slate-900 font-mono font-bold text-xs focus:outline-none focus:ring-0">
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block" x-show="calcType === 'saudi_article_107'">
                        {{ __('Formula UU Saudi menghitung dinamis dari (Gaji Pokok + Tunjangan Tetap) ÷ 240 jam + premi 50%. Tarif di atas digunakan jika gaji pokok belum diisi / pengujian flat.') }}
                    </span>
                    <span class="text-[10px] text-slate-400 mt-1 block" x-show="calcType === 'depnaker_formula'">
                        {{ __('Formula Depnaker menghitung otomatis 1/173 × Gaji Pokok × Pengali. Tarif di atas sebagai acuan/fallback.') }}
                    </span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pengali (Multiplier) *') }}</label>
                    <input type="number" name="multiplier" value="{{ old('multiplier', (float)$overtimeRate->multiplier) }}" required min="0" step="any"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Faktor pengali upah lembur per jam.') }}</span>
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Keterangan Aturan') }}</label>
                <input type="text" name="description" value="{{ old('description', $overtimeRate->description) }}" placeholder="{{ __('Catatan kebijakan lembur...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('overtime.index', ['tab' => 'rates']) }}" 
                class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" 
                class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Perubahan') }}
            </button>
        </div>
    </form>

</div>
@endsection
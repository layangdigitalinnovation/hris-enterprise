@extends('layouts.app', ['headerTitle' => __('Edit Kebijakan Komponen'), 'title' => __('Edit Komponen')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{ 
    calcMode: '{{ old('calculation_mode', $salaryComponent->calculation_mode) }}',
    compType: '{{ old('type', $salaryComponent->type) }}'
}">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('payroll.index', ['tab' => 'components']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
                <span>/</span>
                <a href="{{ route('payroll.index', ['tab' => 'components']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Master Komponen') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit Komponen: :name', ['name' => $salaryComponent->name]) }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Kebijakan Komponen') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Perbarui formula, nilai tarif acuan, dan parameter perpajakan komponen :name', ['name' => $salaryComponent->name]) }}</p>
        </div>
        <a href="{{ route('payroll.index', ['tab' => 'components']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada input formulir:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payroll.components.update', $salaryComponent) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- 1. Klasifikasi & Identitas Komponen --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Klasifikasi & Identitas Komponen') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tipe Komponen *') }}</label>
                    <select name="type" x-model="compType" required {{ $salaryComponent->code === 'BASIC' ? 'disabled' : '' }}
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition {{ $salaryComponent->code === 'BASIC' ? 'opacity-70 cursor-not-allowed' : '' }}">
                        <option value="earning">{{ __('Tunjangan (+ Penghasilan Karyawan)') }}</option>
                        <option value="deduction">{{ __('Potongan (- Pengurangan Upah)') }}</option>
                    </select>
                    @if($salaryComponent->code === 'BASIC')
                        <input type="hidden" name="type" value="earning">
                        <span class="text-[10px] text-amber-600 font-semibold mt-1 block">{{ __('Komponen Gaji Pokok terkunci permanen sebagai tipe penghasilan.') }}</span>
                    @endif
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Unik Komponen *') }}</label>
                    <input type="text" name="code" value="{{ old('code', $salaryComponent->code) }}" required {{ $salaryComponent->code === 'BASIC' ? 'readonly' : '' }}
                        style="text-transform: uppercase;"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition {{ $salaryComponent->code === 'BASIC' ? 'opacity-70 cursor-not-allowed bg-slate-100' : '' }}">
                    @if($salaryComponent->code === 'BASIC')
                        <span class="text-[10px] text-amber-600 font-semibold mt-1 block">{{ __('Kode sistem utama tidak dapat diganti.') }}</span>
                    @endif
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Komponen *') }}</label>
                <input type="text" name="name" value="{{ old('name', $salaryComponent->name) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>
        </div>

        {{-- 2. Skema & Formula Perhitungan --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Skema & Formula Nilai Tarif') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Metode Perhitungan *') }}</label>
                    <select name="calculation_mode" x-model="calcMode" required 
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="fixed">{{ __('Nominal Tetap Bulanan (Rp)') }}</option>
                        <option value="percentage">{{ __('Persentase dari Gaji Pokok (%)') }}</option>
                        <option value="per_attendance">{{ __('Tarif per Hari Kehadiran (Rp / Hari)') }}</option>
                        <option value="per_late_minute">{{ __('Tarif Potongan per Menit Terlambat (Rp / Menit)') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">
                        <span x-show="calcMode === 'percentage'">{{ __('Besaran Persentase (%) *') }}</span>
                        <span x-show="calcMode !== 'percentage'">{{ __('Nilai Nominal Tarif Standar *') }}</span>
                    </label>

                    <div class="flex items-center rounded-xl border border-slate-300 bg-white shadow-2xs overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                        <span class="px-3.5 py-2.5 bg-slate-100 text-slate-600 font-mono font-bold text-xs border-r rtl:border-r-0 rtl:border-l border-slate-200 select-none shrink-0"
                            x-text="calcMode === 'percentage' ? '%' : 'Rp'">
                            Rp
                        </span>
                        <input type="number" name="rate_value" value="{{ old('rate_value', $salaryComponent->rate_value) }}" required min="0" step="any"
                            class="w-full px-3.5 py-2.5 bg-transparent border-0 text-slate-900 font-mono font-bold text-xs focus:outline-none focus:ring-0">
                    </div>
                    <span class="text-[11px] text-slate-400 mt-1 block" x-show="calcMode === 'percentage'">{{ __('Contoh: Masukkan 5 untuk 5% dari gaji pokok karyawan.') }}</span>
                    <span class="text-[11px] text-slate-400 mt-1 block" x-show="calcMode === 'per_attendance'">{{ __('Dikalikan dengan total hari hadir sah pada mesin absensi.') }}</span>
                    <span class="text-[11px] text-slate-400 mt-1 block" x-show="calcMode === 'per_late_minute'">{{ __('Dikalikan dengan total akumulasi menit keterlambatan.') }}</span>
                </div>
            </div>
        </div>

        {{-- 3. Ketentuan Pajak & Kebijakan --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">3</span>
                {{ __('Ketentuan Pajak & Status Kebijakan') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Perlakuan Pajak PPh 21 *') }}</label>
                    <select name="is_taxable" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="1" {{ old('is_taxable', $salaryComponent->is_taxable) ? 'selected' : '' }}>{{ __('Ya, Dikenakan Pajak (Objek PPh 21)') }}</option>
                        <option value="0" {{ !old('is_taxable', $salaryComponent->is_taxable) ? 'selected' : '' }}>{{ __('Tidak Kena Pajak (Non-Taxable)') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Keberlakuan *') }}</label>
                    <select name="is_active" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="1" {{ old('is_active', $salaryComponent->is_active) ? 'selected' : '' }}>{{ __('Aktif (Otomatis Dihitung di Payroll)') }}</option>
                        <option value="0" {{ !old('is_active', $salaryComponent->is_active) ? 'selected' : '' }}>{{ __('Non-Aktif (Dihentikan Sementara)') }}</option>
                    </select>
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Keterangan / Dasar Kebijakan Perusahaan') }}</label>
                <textarea name="description" rows="3"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('description', $salaryComponent->description) }}</textarea>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('payroll.index', ['tab' => 'components']) }}" 
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
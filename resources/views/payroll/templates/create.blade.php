@extends('layouts.app', ['headerTitle' => __('Buat Template Payroll Baru'), 'title' => __('Buat Template Payroll')])

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    currency: '{{ old('currency', \App\Helpers\CurrencyHelper::defaultCurrency()) }}',
    baseSalary: {{ old('base_salary', \App\Helpers\CurrencyHelper::defaultCurrency() === 'SAR' ? 6000 : 5000000) }},
    items: [],
    availableComponents: @js($salaryComponents),
    selectedComponentId: '',

    get currencyPrefix() {
        return this.currency === 'SAR' ? 'SAR ' : 'Rp ';
    },

    addComponent() {
        if (!this.selectedComponentId) return;
        const comp = this.availableComponents.find(c => c.id == this.selectedComponentId);
        if (!comp) return;

        // Check if already in items
        if (this.items.some(i => i.salary_component_id == comp.id)) {
            alert('Komponen ' + comp.name + ' sudah ditambahkan ke dalam template ini.');
            return;
        }

        let defaultRate = (comp.rate_value && parseFloat(comp.rate_value) > 0) ? parseFloat(comp.rate_value) : 0;
        let defaultAmt = (comp.default_amount && parseFloat(comp.default_amount) > 0) ? parseFloat(comp.default_amount) : 0;

        this.items.push({
            salary_component_id: comp.id,
            name: comp.name,
            code: comp.code,
            type: comp.type,
            calculation_mode: comp.calculation_mode || 'fixed',
            rate_value: defaultRate,
            amount: defaultAmt,
            is_fixed: comp.is_fixed !== false,
            is_taxable: comp.is_taxable !== false,
            notes: ''
        });

        this.selectedComponentId = '';
    },

    removeItem(index) {
        this.items.splice(index, 1);
    },

    calculateSim() {
        let base = parseFloat(this.baseSalary) || 0;
        let totalEarnings = 0;
        let totalDeductions = 0;
        const workdays = 22;

        this.items.forEach(item => {
            if (item.code === 'BASIC') {
                return; // Gaji Pokok sudah dihitung dari Gaji Pokok Acuan
            }

            let amt = 0;
            let val = (item.rate_value !== undefined && item.rate_value !== '') 
                ? (parseFloat(item.rate_value) || 0) 
                : (parseFloat(item.amount) || 0);
            let mode = item.calculation_mode;

            if (mode === 'fixed') {
                amt = val;
            } else if (mode === 'percentage') {
                amt = base * (val / 100);
            } else if (mode === 'per_attendance') {
                amt = workdays * val;
            } else if (mode === 'per_late_minute') {
                amt = 0;
            }

            if (item.type === 'earning') {
                totalEarnings += amt;
            } else {
                totalDeductions += amt;
            }
        });

        let gross = base + totalEarnings;
        let net = Math.max(0, gross - totalDeductions);

        return {
            base: base,
            totalEarnings: totalEarnings,
            totalDeductions: totalDeductions,
            gross: gross,
            net: net
        };
    },

    formatMoney(val) {
        return this.currencyPrefix + (new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val || 0));
    }
}">

    <!-- Navigation Breadcrumb -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('payroll.index', ['tab' => 'templates']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
            <span>/</span>
            <a href="{{ route('payroll.templates.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Template Payroll') }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">{{ __('Buat Baru') }}</span>
        </div>
        <a href="{{ route('payroll.templates.index') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali ke Daftar Template') }}
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

    <form action="{{ route('payroll.templates.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Metadata Template Card -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-blue-50 text-blue-600 font-bold text-xs flex items-center justify-center">1</span>
                    <h4 class="font-bold text-slate-900 text-sm">{{ __('Identitas & Konfigurasi Template') }}</h4>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold">{{ __('Pilih Mata Uang:') }}</span>
                    <div class="flex items-center p-0.5 rounded-xl bg-slate-100 font-semibold text-xs">
                        <label class="px-3 py-1.5 rounded-lg cursor-pointer transition flex items-center gap-1.5"
                            :class="currency === 'IDR' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600'">
                            <input type="radio" name="currency" value="IDR" x-model="currency" class="sr-only">
                            <span>🇮🇩 {{ __('IDR (Rp)') }}</span>
                        </label>
                        <label class="px-3 py-1.5 rounded-lg cursor-pointer transition flex items-center gap-1.5"
                            :class="currency === 'SAR' ? 'bg-white text-emerald-700 shadow-2xs font-bold' : 'text-slate-600'">
                            <input type="radio" name="currency" value="SAR" x-model="currency" class="sr-only">
                            <span>🇸🇦 {{ __('SAR (Riyal)') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Template *') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('cth: Template Staf Operasional IDR') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Unik Template *') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="{{ __('cth: TPL-OPS-IDR') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Gaji Pokok Acuan Standar *') }}</label>
                    <div class="flex items-center rounded-xl border border-slate-300 bg-white overflow-hidden shadow-2xs focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                        <span class="px-3.5 py-2.5 bg-slate-100 text-slate-600 font-mono font-bold text-xs border-r rtl:border-r-0 rtl:border-l border-slate-200 select-none shrink-0"
                            x-text="currencyPrefix">
                        </span>
                        <input type="number" step="0.01" name="base_salary" x-model="baseSalary" required
                            class="w-full px-3 py-2 text-xs font-mono font-bold text-slate-900 focus:outline-none">
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">
                        {{ __('Nilai acuan gaji pokok awal yang diterapkan saat karyawan dimasukkan ke template ini.') }}
                    </span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Cabang Sasaran (Opsional)') }}</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">{{ __('-- Semua Cabang --') }}</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->country_flag }} {{ $branch->name }} ({{ $branch->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Deskripsi / Catatan Kebijakan Template') }}</label>
                    <textarea name="description" rows="2" placeholder="{{ __('Jelaskan kriteria penggunaan template ini, misalnya: khusus pegawai kontrak, khusus ekspatriat KSA, dsb.') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center gap-6 text-xs">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <span class="font-semibold text-slate-700">{{ __('Template Aktif') }}</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="is_default" value="1" class="w-4 h-4 text-amber-500 rounded border-slate-300 focus:ring-amber-500">
                    <span class="font-semibold text-slate-700">{{ __('Jadikan Template Default untuk Mata Uang Ini') }}</span>
                </label>
            </div>
        </div>

        <!-- 2. Dynamic Component Builder & Live Simulation Card -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Component Builder Table (Col 8) -->
            <div class="lg:col-span-8 p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-xs flex items-center justify-center">2</span>
                        <h4 class="font-bold text-slate-900 text-sm">{{ __('Komponen Tunjangan & Potongan Template') }}</h4>
                    </div>

                    <!-- Add Component Quick Selector -->
                    <div class="flex items-center gap-2">
                        <select x-model="selectedComponentId" class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="">{{ __('-- Pilih Komponen --') }}</option>
                            <template x-for="c in availableComponents" :key="c.id">
                                <option :value="c.id" x-text="(c.type === 'earning' ? '🟢 (+) ' : '🔴 (-) ') + c.name"></option>
                            </template>
                        </select>
                        <button type="button" @click="addComponent()" :disabled="!selectedComponentId"
                            class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-40 text-white font-semibold text-xs transition flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>{{ __('Tambah') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 bg-slate-50/60 font-semibold text-[10px]">
                                <th class="py-2.5 px-3">{{ __('Komponen') }}</th>
                                <th class="py-2.5 px-3">{{ __('Metode Hitung') }}</th>
                                <th class="py-2.5 px-3">{{ __('Tarif / Nilai') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Tetap') }}</th>
                                <th class="py-2.5 px-3 text-center">{{ __('Pajak') }}</th>
                                <th class="py-2.5 px-3 text-right">{{ __('Hapus') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="item.salary_component_id">
                                <tr class="hover:bg-slate-50/50 transition">
                                    <!-- Component Name & Code -->
                                    <td class="py-3 px-3">
                                        <input type="hidden" :name="'items[' + index + '][salary_component_id]'" :value="item.salary_component_id">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full shrink-0" :class="item.type === 'earning' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                            <div>
                                                <span class="font-bold text-slate-800 block text-xs" x-text="item.name"></span>
                                                <span class="font-mono text-[10px] text-slate-400 block" x-text="item.code"></span>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Calculation Mode -->
                                    <td class="py-3 px-3">
                                        <select :name="'items[' + index + '][calculation_mode]'" x-model="item.calculation_mode"
                                            class="px-2.5 py-1.5 rounded-lg bg-white border border-slate-200 text-xs text-slate-700 font-medium focus:ring-1 focus:ring-blue-500">
                                            <option value="fixed">{{ __('Nominal Tetap') }}</option>
                                            <option value="percentage">{{ __('% dari Gaji Pokok') }}</option>
                                            <option value="per_attendance" x-show="item.type === 'earning'">{{ __('Per Hari Kehadiran') }}</option>
                                            <option value="per_late_minute" x-show="item.type === 'deduction'">{{ __('Per Menit Keterlambatan') }}</option>
                                        </select>
                                    </td>

                                    <!-- Rate Value / Amount -->
                                    <td class="py-3 px-3 font-mono">
                                        <div class="flex items-center rounded-lg border border-slate-200 bg-white overflow-hidden w-36 shadow-2xs">
                                            <span class="px-2 py-1 bg-slate-100 text-slate-500 font-bold text-[10px] border-r border-slate-200 shrink-0"
                                                x-text="item.calculation_mode === 'percentage' ? '%' : currencyPrefix">
                                            </span>
                                            <input type="number" step="0.01" :name="'items[' + index + '][rate_value]'" x-model="item.rate_value"
                                                class="w-full px-2 py-1 text-xs font-mono font-bold text-slate-800 focus:outline-none">
                                        </div>
                                        <span class="text-[9px] text-slate-400 block mt-0.5" x-show="item.calculation_mode === 'per_attendance'">{{ __('per hari hadir') }}</span>
                                    </td>

                                    <!-- Fixed Allowance Toggle (Pasal 107 Saudi) -->
                                    <td class="py-3 px-3 text-center">
                                        <template x-if="item.type === 'earning'">
                                            <input type="checkbox" :name="'items[' + index + '][is_fixed]'" value="1" x-model="item.is_fixed"
                                                title="{{ __('Tunjangan Tetap (masuk dasar lembur UU Saudi Pasal 107 & Depnaker)') }}"
                                                class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500 cursor-pointer">
                                        </template>
                                        <template x-if="item.type !== 'earning'">
                                            <span class="text-slate-300 text-xs font-mono">-</span>
                                        </template>
                                    </td>

                                    <!-- Taxable Toggle -->
                                    <td class="py-3 px-3 text-center">
                                        <template x-if="item.type === 'earning'">
                                            <input type="checkbox" :name="'items[' + index + '][is_taxable]'" value="1" x-model="item.is_taxable"
                                                title="{{ __('Kena Pajak PPh 21') }}"
                                                class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                                        </template>
                                        <template x-if="item.type !== 'earning'">
                                            <span class="text-slate-300 text-xs font-mono">-</span>
                                        </template>
                                    </td>

                                    <!-- Delete Button -->
                                    <td class="py-3 px-3 text-right">
                                        <button type="button" @click="removeItem(index)" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="{{ __('Hapus dari template') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="items.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                    <p>{{ __('Belum ada komponen tunjangan/potongan yang ditambahkan ke template ini.') }}</p>
                                    <p class="text-[11px] text-slate-400 mt-1">{{ __('Gunakan dropdown di kanan atas untuk menambahkan tunjangan jabatan, uang makan, BPJS, dll.') }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Live THP Simulator Card (Col 4) -->
            <div class="lg:col-span-4 space-y-4">
                <div class="p-6 rounded-3xl bg-slate-900 text-white shadow-xl space-y-5 sticky top-24">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                            <span>🧮</span>
                            <span>{{ __('Simulasi Take-Home Pay') }}</span>
                        </span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-white/10 text-white" x-text="currency"></span>
                    </div>

                    <div class="space-y-3 font-mono text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400 font-sans">{{ __('Gaji Pokok Acuan:') }}</span>
                            <span class="font-bold text-white text-sm" x-text="formatMoney(calculateSim().base)"></span>
                        </div>

                        <div class="flex items-center justify-between text-emerald-400">
                            <span class="font-sans text-[11px]">{{ __('Total Tunjangan (Est. 22 Hari):') }}</span>
                            <span class="font-bold" x-text="'+ ' + formatMoney(calculateSim().totalEarnings)"></span>
                        </div>

                        <div class="flex items-center justify-between text-rose-400">
                            <span class="font-sans text-[11px]">{{ __('Total Potongan:') }}</span>
                            <span class="font-bold" x-text="'- ' + formatMoney(calculateSim().totalDeductions)"></span>
                        </div>

                        <div class="pt-3 border-t border-slate-800 space-y-1">
                            <span class="text-slate-400 text-[10px] uppercase font-semibold font-sans block">{{ __('Estimasi Gaji Bersih (Net THP)') }}</span>
                            <div class="text-2xl font-extrabold text-emerald-400" x-text="formatMoney(calculateSim().net)"></div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-white/5 border border-white/10 text-[11px] text-slate-300 space-y-1">
                        <p class="font-semibold text-white">💡 {{ __('Dapat Digunakan Berulang Kali') }}</p>
                        <p class="text-[10px] leading-relaxed text-slate-400">
                            {{ __('Template ini dapat langsung diterapkan ke banyak karyawan sekaligus atau saat penerimaan pegawai baru.') }}
                        </p>
                    </div>

                    <div class="pt-2">
                        <button type="submit" style="background-color: #2563eb; color: #ffffff;"
                            class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span class="text-white">{{ __('Simpan & Buat Template') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection


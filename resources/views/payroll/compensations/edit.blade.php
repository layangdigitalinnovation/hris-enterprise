@extends('layouts.app', ['headerTitle' => __('Atur Gaji & Kompensasi Karyawan'), 'title' => __('Atur Gaji :name', ['name' => $employee->full_name])])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @php
        $backUrl = request('redirect_to') === 'profile' 
            ? route('employees.show', $employee) 
            : route('payroll.index', ['tab' => 'salaries']);
    @endphp

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('payroll.index', ['tab' => 'salaries']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Struktur Gaji: :name', ['name' => $employee->full_name]) }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Gaji & Kompensasi Karyawan') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Tetapkan gaji pokok resmi kontrak kerja dan penyesuaian khusus tunjangan/potongan individu') }}</p>
        </div>
        <a href="{{ $backUrl }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    <!-- Employee Profile Banner -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-lg flex items-center justify-center shadow-md shadow-blue-500/20 shrink-0">
                {{ substr($employee->first_name, 0, 1) }}{{ substr($employee->last_name ?? '', 0, 1) }}
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-base font-bold text-slate-900">{{ $employee->full_name }}</h4>
                    <span class="font-mono text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-200">
                        {{ $employee->employee_code }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ __(ucfirst($employee->status)) }}
                    </span>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 mt-1 flex-wrap">
                    <span>{{ $employee->department->name ?? __('Belum ada departemen') }}</span>
                    <span>•</span>
                    <span>{{ $employee->position->title ?? __('Belum ada jabatan') }}</span>
                    @if($employee->branch)
                        <span>•</span>
                        <span>{{ $employee->branch->name }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="sm:text-right rtl:sm:text-left text-xs text-slate-500">
            <span class="block font-medium text-slate-400">{{ __('Rekening Payroll') }}</span>
            <span class="font-mono font-bold text-slate-800 text-sm">
                {{ $employee->bank_name ?? '-' }} {{ $employee->bank_account_no ? '· ' . $employee->bank_account_no : '' }}
            </span>
        </div>
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

    @php
        $empCurr = $employee->effective_currency;
    @endphp

    <div x-data="{
        templates: @js($availableTemplates),
        selectedTemplateId: '{{ old('payroll_template_id', $employee->payroll_template_id ?? '') }}',
        currentCurrency: '{{ old('currency', $empCurr) }}',
        appliedSuccess: false,
        appliedMessage: '',
        applyTemplate() {
            if (!this.selectedTemplateId) return;
            const tpl = this.templates.find(t => t.id == this.selectedTemplateId);
            if (!tpl) return;

            // 0. Update mata uang sesuai template yang dipilih
            if (tpl.currency) {
                this.currentCurrency = tpl.currency;
            }

            // 1. Reset seluruh input komponen (tunjangan & potongan khusus) terlebih dahulu
            // agar data dari template sebelumnya tidak nyangkut/tersisa
            document.querySelectorAll('input[name^=\'components[\']').forEach(input => {
                input.value = '';
            });

            // 2. Set gaji pokok dari base_salary template
            const baseSalary = parseFloat(tpl.base_salary) || 0;
            const basicInput = document.querySelector('input[name=\'basic_salary\']');
            if (basicInput) {
                basicInput.value = baseSalary > 0 ? baseSalary : '';
                basicInput.classList.add('ring-2', 'ring-blue-500', 'bg-blue-50/50');
                setTimeout(() => basicInput.classList.remove('ring-2', 'ring-blue-500', 'bg-blue-50/50'), 1500);
            }

            // 3. Isi komponen yang didefinisikan dalam template
            let filledCount = 0;
            if (tpl.items && tpl.items.length) {
                tpl.items.forEach(item => {
                    // Abaikan komponen BASIC jika ada di items (karena sudah diatur di basic_salary)
                    if (item.component && item.component.code === 'BASIC') {
                        return;
                    }

                    const input = document.querySelector(`input[name='components[${item.salary_component_id}]']`);
                    if (input) {
                        let amt = parseFloat(item.amount) || 0;
                        let rate = parseFloat(item.rate_value) || 0;

                        if (amt > 0) {
                            input.value = amt;
                            filledCount++;
                        } else if (item.calculation_mode === 'fixed' && rate > 0) {
                            input.value = rate;
                            filledCount++;
                        } else if (item.calculation_mode === 'percentage' && rate > 0 && baseSalary > 0) {
                            // Hitung nominal persentase terhadap gaji pokok template
                            input.value = Math.round(baseSalary * (rate / 100));
                            filledCount++;
                        } else {
                            // Untuk mode per_attendance, per_late_minute atau standar dinamis:
                            // Kosongkan agar otomatis mengikuti formula standar template
                            input.value = '';
                        }

                        if (input.value !== '') {
                            input.classList.add('ring-2', 'ring-emerald-500', 'bg-emerald-50/50');
                            setTimeout(() => input.classList.remove('ring-2', 'ring-emerald-500', 'bg-emerald-50/50'), 1500);
                        }
                    }
                });
            }

            this.appliedSuccess = true;
            this.appliedMessage = `Struktur gaji disinkronkan dengan '${tpl.name}'! (${filledCount} komponen khusus disesuaikan, komponen lain mengikuti standar)`;
            setTimeout(() => {
                this.appliedSuccess = false;
            }, 3500);
        }
    }">

    <form action="{{ route('payroll.compensations.update-salary', $employee) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">

        {{-- 0. Template Payroll Cepat --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-gradient-to-br from-blue-50/70 via-white to-indigo-50/40 border border-blue-200/80 shadow-xs space-y-3">
            <div class="flex items-center justify-between border-b border-blue-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-xl bg-blue-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                        📑
                    </span>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ __('Terapkan dari Template Payroll (Opsional)') }}</h4>
                        <p class="text-[11px] text-slate-500">{{ __('Gunakan paket template standar untuk otomatis mengisi gaji pokok dan seluruh tunjangan/potongan') }}</p>
                    </div>
                </div>

                @if($employee->payrollTemplate)
                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>{{ __('Template Aktif:') }} {{ $employee->payrollTemplate->code }}</span>
                    </span>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-1">
                <div class="flex-1">
                    <select name="payroll_template_id" x-model="selectedTemplateId"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 shadow-2xs">
                        <option value="">{{ __('-- Pilih Template Payroll --') }}</option>
                        @foreach ($availableTemplates as $tpl)
                            <option value="{{ $tpl->id }}" {{ (old('payroll_template_id', $employee->payroll_template_id) == $tpl->id) ? 'selected' : '' }}>
                                [{{ $tpl->currency }}] {{ $tpl->name }} ({{ $tpl->code }}) · Gaji Pokok: {{ format_currency($tpl->base_salary, $tpl->currency) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="button" @click="applyTemplate()" :disabled="!selectedTemplateId"
                    :class="appliedSuccess ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white'"
                    class="px-4 py-2.5 rounded-xl disabled:opacity-40 font-semibold text-xs shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer shrink-0">
                    <template x-if="!appliedSuccess">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>{{ __('Isi Otomatis dari Template') }}</span>
                        </div>
                    </template>
                    <template x-if="appliedSuccess">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ __('Berhasil Diterapkan!') }}</span>
                        </div>
                    </template>
                </button>
            </div>

            <div x-show="appliedSuccess" x-transition.opacity.duration.300ms class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span x-text="appliedMessage"></span>
            </div>
        </div>

        {{-- 1. Gaji Pokok (Basic Salary) --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-wrap gap-2">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                    {{ __('Upah Pokok Bulanan *') }}
                </h4>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-500 font-semibold">{{ __('Mata Uang Gaji:') }}</span>
                    <div class="flex items-center p-0.5 rounded-xl bg-slate-100 font-semibold text-xs">
                        <label class="px-2.5 py-1 rounded-lg cursor-pointer transition flex items-center gap-1"
                            :class="currentCurrency === 'IDR' ? 'bg-white text-blue-700 shadow-2xs font-bold' : 'text-slate-600'">
                            <input type="radio" name="currency" value="IDR" x-model="currentCurrency" class="sr-only">
                            <span>🇮🇩 {{ __('IDR (Rp)') }}</span>
                        </label>
                        <label class="px-2.5 py-1 rounded-lg cursor-pointer transition flex items-center gap-1"
                            :class="currentCurrency === 'SAR' ? 'bg-white text-emerald-700 shadow-2xs font-bold' : 'text-slate-600'">
                            <input type="radio" name="currency" value="SAR" x-model="currentCurrency" class="sr-only">
                            <span>🇸🇦 {{ __('SAR (Riyal)') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="max-w-xl">
                <label class="block font-semibold text-slate-700 text-xs mb-1.5">{{ __('Nominal Gaji Pokok *') }}</label>
                <div class="flex items-center rounded-xl border border-slate-300 bg-white shadow-2xs overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                    <span class="px-3.5 py-2.5 bg-slate-100 text-slate-600 font-mono font-bold text-xs border-r rtl:border-r-0 rtl:border-l border-slate-200 select-none shrink-0"
                        x-text="currentCurrency === 'SAR' ? 'SAR' : 'Rp'">
                        {{ $empCurr === 'SAR' ? 'SAR' : 'Rp' }}
                    </span>
                    <input type="number" name="basic_salary" value="{{ old('basic_salary', ($empCurr === 'SAR' && (float)$basicSalary <= 0 ? 6000 : (float)$basicSalary)) }}" required min="0" step="any"
                        :placeholder="currentCurrency === 'SAR' ? 'Contoh: 6000' : 'Contoh: 7000000'"
                        class="w-full px-3.5 py-2.5 bg-transparent border-0 text-slate-900 font-mono font-bold text-base focus:outline-none focus:ring-0">
                </div>
                <span class="text-[11px] text-slate-400 mt-1.5 block">
                    {{ __('Nilai dasar gaji bulanan resmi karyawan. Menjadi basis perhitungan persentase tunjangan, premi lembur standar, dan potongan BPJS.') }}
                </span>
            </div>
        </div>

        {{-- 2. Tunjangan Tambahan Khusus (Opsional / Override) --}}
        @php
            $allowances = $salaryComponents->where('type', 'earning');
            $deductions = $salaryComponents->where('type', 'deduction');
        @endphp

        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">2</span>
                    {{ __('Penetapan Tunjangan Tambahan Khusus (Opsional)') }}
                </h4>
                <span class="text-xs text-slate-400">{{ __('Kosongkan jika mengikuti tarif standar perusahaan') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($allowances as $alw)
                    @php
                        $customVal = $currentCompensations->get($alw->id)?->amount;
                    @endphp
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900 text-xs">{{ __($alw->name) }}</span>
                                <span class="font-mono text-[10px] text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">{{ $alw->code }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono block mt-1">
                                {{ __('Standar Perusahaan:') }} 
                                @if($alw->calculation_mode === 'percentage')
                                    {{ __(':val% Pokok', ['val' => number_format($alw->rate_value, 0)]) }}
                                @elseif($alw->calculation_mode === 'per_attendance')
                                    {{ format_currency($alw->rate_value, $empCurr) }} {{ __('/ hari') }}
                                @else
                                    {{ format_currency($alw->rate_value, $empCurr) }}
                                @endif
                            </span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ __('Nominal Override Khusus') }}</label>
                            <div class="flex items-center rounded-xl border border-slate-300 bg-white shadow-2xs overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                                <span class="px-3 py-2 bg-slate-100 text-slate-600 font-mono font-semibold text-xs border-r rtl:border-r-0 rtl:border-l border-slate-200 select-none shrink-0"
                                    x-text="currentCurrency === 'SAR' ? 'SAR' : 'Rp'">
                                    {{ $empCurr === 'SAR' ? 'SAR' : 'Rp' }}
                                </span>
                                <input type="number" name="components[{{ $alw->id }}]" 
                                    value="{{ old("components.{$alw->id}", $customVal !== null ? (float)$customVal : '') }}"
                                    placeholder="{{ __('Ikuti standar') }}" min="0" step="any"
                                    class="w-full px-3 py-2 bg-transparent border-0 text-slate-900 font-mono font-semibold text-xs placeholder-slate-400 focus:outline-none focus:ring-0">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-6 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                        {{ __('Belum ada master tunjangan tambahan aktif di perusahaan.') }}
                    </div>
                @endforelse
            </div>
        </div>

        {{-- 3. Potongan Khusus / Kasbon (Opsional / Override) --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold">3</span>
                    {{ __('Penetapan Potongan Khusus / Kasbon Karyawan (Opsional)') }}
                </h4>
                <span class="text-xs text-slate-400">{{ __('Kosongkan jika tidak ada pemotongan khusus') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse ($deductions as $ded)
                    @php
                        $customVal = $currentCompensations->get($ded->id)?->amount;
                    @endphp
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between gap-3">
                        <div>
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-900 text-xs">{{ __($ded->name) }}</span>
                                <span class="font-mono text-[10px] text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">{{ $ded->code }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400 font-mono block mt-1">
                                {{ __('Standar Perusahaan:') }} 
                                @if($ded->calculation_mode === 'percentage')
                                    {{ __(':val% Pokok', ['val' => number_format($ded->rate_value, 0)]) }}
                                @elseif($ded->calculation_mode === 'per_late_minute')
                                    {{ format_currency($ded->rate_value, $empCurr) }} {{ __('/ menit') }}
                                @else
                                    {{ format_currency($ded->rate_value, $empCurr) }}
                                @endif
                            </span>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ __('Nominal Potongan Khusus') }}</label>
                            <div class="flex items-center rounded-xl border border-slate-300 bg-white shadow-2xs overflow-hidden focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition">
                                <span class="px-3 py-2 bg-slate-100 text-slate-600 font-mono font-semibold text-xs border-r rtl:border-r-0 rtl:border-l border-slate-200 select-none shrink-0"
                                    x-text="currentCurrency === 'SAR' ? 'SAR' : 'Rp'">
                                    {{ $empCurr === 'SAR' ? 'SAR' : 'Rp' }}
                                </span>
                                <input type="number" name="components[{{ $ded->id }}]" 
                                    value="{{ old("components.{$ded->id}", $customVal !== null ? (float)$customVal : '') }}"
                                    placeholder="{{ __('Ikuti standar') }}" min="0" step="any"
                                    class="w-full px-3 py-2 bg-transparent border-0 text-slate-900 font-mono font-semibold text-xs placeholder-slate-400 focus:outline-none focus:ring-0">
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 p-6 rounded-2xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                        {{ __('Belum ada master potongan tambahan aktif di perusahaan.') }}
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ $backUrl }}" 
                class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" 
                class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Struktur Gaji') }}
            </button>
        </div>
    </form>
    </div>

</div>
@endsection
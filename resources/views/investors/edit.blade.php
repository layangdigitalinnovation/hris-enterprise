@extends('layouts.app')

@section('title', __('Perbarui Data Investor: :name', ['name' => $investor->name]))

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12" x-data="{
    currency: '{{ old('currency', $investor->currency) }}',
    scheme: '{{ old('profit_share_scheme', $investor->profit_share_scheme) }}',
    hasStipend: {{ old('has_fixed_stipend', $investor->has_fixed_stipend) ? 'true' : 'false' }},
    get currencyPrefix() {
        return this.currency === 'SAR' ? 'SAR' : 'Rp';
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('investors.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Investor') }}</a>
                <span>/</span>
                <a href="{{ route('investors.show', $investor) }}" class="hover:text-blue-600 font-medium transition-colors">{{ $investor->name }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit Data') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                {{ __('Perbarui Data Investor: :name', ['name' => $investor->name]) }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Perbarui informasi identitas, ketentuan modal, skema bagi hasil, dan imbalan tetap.') }}</p>
        </div>

        <a href="{{ route('investors.show', $investor) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>{{ __('Kembali ke Detail') }}</a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>{{ __('Terdapat beberapa kesalahan input data:') }}</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('investors.update', $investor) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card 1: Identitas Investor -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">1</div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Identitas & Kontak Investor') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Data pribadi resmi investor atau badan usaha penanam modal.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Kode Unik Investor') }}<span class="text-rose-500">*</span></label>
                    <input type="text" name="investor_code" value="{{ old('investor_code', $investor->investor_code) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 font-mono font-semibold bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none uppercase">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nama Lengkap Investor / Mitra') }}<span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $investor->name) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('NIK / No. Paspor (Identitas Resmi)') }}</label>
                    <input type="text" name="nik_or_passport" value="{{ old('nik_or_passport', $investor->nik_or_passport) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Email Korespondensi') }}</label>
                    <input type="email" name="email" value="{{ old('email', $investor->email) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('No. WhatsApp / Telepon') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $investor->phone) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Status Keaktifan') }}<span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                        <option value="active" {{ old('status', $investor->status) === 'active' ? 'selected' : '' }}>{{ __('Aktif Berjalan') }}</option>
                        <option value="completed" {{ old('status', $investor->status) === 'completed' ? 'selected' : '' }}>{{ __('Selesai (Modal & Laba Lunas)') }}</option>
                        <option value="paused" {{ old('status', $investor->status) === 'paused' ? 'selected' : '' }}>{{ __('Ditangguhkan / Ditunda') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Card 2: Informasi Rekening Pencairan Dividen -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">2</div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Rekening Bank Pencairan Dana') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Rekening tujuan transfer bagi hasil (dividen), gaji khusus, dan pengembalian modal.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nama Bank') }}</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $investor->bank_name) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nomor Rekening / IBAN') }}</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $investor->bank_account_no) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 font-mono bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Atas Nama Rekening') }}</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $investor->bank_account_holder) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Card 3: Skema Penanaman Modal & Bagi Hasil (BEP) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xs">3</div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Skema Modal, Proyek & Bagi Hasil (Dividen)') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Tetapkan jumlah modal disetor sebagai dasar perhitungan Break-Even Point (BEP).') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Cabang / Unit Proyek -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Cabang / Proyek Penanaman Modal') }}</label>
                    <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                        <option value="">-- {{ __('Proyek / Unit Terpusat Perusahaan') }} --</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ old('branch_id', $investor->branch_id) == $b->id ? 'selected' : '' }}>
                                {{ $b->country_code === 'SA' ? '🇸🇦' : '🇮🇩' }} {{ $b->name }} ({{ $b->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Currency Selection Switcher -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Mata Uang Investasi & Dividen') }}<span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                            :class="currency === 'SAR' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 font-bold shadow-xs' : 'border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100'">
                            <input type="radio" name="currency" value="SAR" x-model="currency" class="text-emerald-600 focus:ring-emerald-500">
                            <span class="text-xs">{{ __('🇸🇦 SAR (Saudi Riyal)') }}</span>
                        </label>
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border cursor-pointer transition-all"
                            :class="currency === 'IDR' ? 'border-blue-500 bg-blue-50 text-blue-900 font-bold shadow-xs' : 'border-slate-200 text-slate-600 bg-slate-50 hover:bg-slate-100'">
                            <input type="radio" name="currency" value="IDR" x-model="currency" class="text-blue-600 focus:ring-blue-500">
                            <span class="text-xs">{{ __('🇮🇩 IDR (Rupiah)') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Modal Disetor -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Modal Awal Disetor (Initial Investment)') }}<span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-1 focus-within:bg-white focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all cursor-text"
                        @click="$refs.initialInvestmentInput?.focus()">
                        <span class="text-xs font-mono font-bold text-slate-500 select-none shrink-0" x-text="currencyPrefix"></span>
                        <input type="number" step="any" min="0" name="initial_investment" x-ref="initialInvestmentInput" value="{{ old('initial_investment', $investor->initial_investment) }}" required
                            class="w-full px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0">
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">{{ __('Nilai patokan dasar Break-Even Point (titik impas balik modal 100%).') }}</p>
                </div>

                <!-- Tanggal Penyetoran Modal -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Penyetoran Modal') }}<span class="text-rose-500">*</span></label>
                    <input type="date" name="investment_date" value="{{ old('investment_date', $investor->investment_date?->format('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>

                <!-- Persentase Bagi Hasil -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        <span x-text="scheme === 'fixed_monthly' ? '{{ __('Tingkat Dividen Tetap dari Modal (%)') }}' : '{{ __('Persentase Porsi Bagi Hasil (%)') }}'"></span>
                        <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" name="profit_share_percentage" value="{{ old('profit_share_percentage', $investor->profit_share_percentage) }}" required
                            class="w-full pr-8 pl-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-900 border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                        <span class="absolute right-3.5 top-2.5 text-xs font-bold text-slate-400">%</span>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-1">
                        <span x-show="scheme === 'fixed_monthly'">{{ __('Otomatis: Dividen tetap dihitung dari persentase modal awal disetor.') }}</span>
                        <span x-show="scheme === 'net_profit'">{{ __('Otomatis: Bagi hasil dihitung dari laba bersih proyek setiap periode pencairan.') }}</span>
                        <span x-show="scheme === 'gross_revenue'">{{ __('Otomatis: Bagi hasil dihitung dari omset kotor proyek setiap periode pencairan.') }}</span>
                    </p>
                </div>

                <!-- Skema Acuan Bagi Hasil -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Metode Basis Acuan Bagi Hasil') }}<span class="text-rose-500">*</span></label>
                    <select name="profit_share_scheme" x-model="scheme" required class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                        <option value="net_profit" {{ old('profit_share_scheme', $investor->profit_share_scheme) === 'net_profit' ? 'selected' : '' }}>{{ __('... % Dari Laba Bersih Proyek (Net Profit Share)') }}</option>
                        <option value="gross_revenue" {{ old('profit_share_scheme', $investor->profit_share_scheme) === 'gross_revenue' ? 'selected' : '' }}>{{ __('... % Dari Omset Kotor (Gross Revenue Share)') }}</option>
                        <option value="fixed_monthly" {{ old('profit_share_scheme', $investor->profit_share_scheme) === 'fixed_monthly' ? 'selected' : '' }}>{{ __('Dividen Nominal Tetap Berkala') }}</option>
                    </select>
                </div>

                <!-- Target Akhir Kontrak -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Target Selesai / Jatuh Tempo Kontrak (Opsional)') }}</label>
                    <input type="date" name="contract_end_date" value="{{ old('contract_end_date', $investor->contract_end_date?->format('Y-m-d')) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                </div>
            </div>
        </div>

        <!-- Card 4: Gaji Khusus / Imbalan Tetap Investor -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">4</div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">{{ __('Gaji Khusus & Honorarium Investor') }}</h2>
                        <p class="text-[11px] text-slate-500">{{ __('Imbalan tetap bulanan bagi investor yang berperan aktif, dewan pengawas, atau komisaris.') }}</p>
                    </div>
                </div>

                <!-- Clear High-Contrast Toggle Switch with Active Knob & Pill -->
                <div class="flex items-center gap-3">
                    <!-- Toggle Switch Button (Hijau saat Aktif, Merah saat Nonaktif) -->
                    <button type="button" 
                        @click="hasStipend = !hasStipend" 
                        role="switch"
                        :aria-checked="hasStipend.toString()"
                        style="width: 54px; height: 28px;"
                        class="relative inline-flex shrink-0 cursor-pointer rounded-full p-0.5 transition-colors duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 select-none"
                        :class="hasStipend ? 'bg-emerald-500 focus:ring-emerald-500 shadow-sm shadow-emerald-500/30' : 'bg-red-500 focus:ring-red-500 shadow-sm shadow-red-500/30'">
                        
                        <!-- Sliding White Knob (Kiri: Nonaktif / Merah, Kanan: Aktif / Hijau) -->
                        <span aria-hidden="true" 
                            class="pointer-events-none inline-flex items-center justify-center w-6 h-6 rounded-full bg-white shadow-md transition-all duration-300 ease-in-out"
                            :class="hasStipend ? 'translate-x-6' : 'translate-x-0'"
                            :style="hasStipend ? 'transform: translateX(26px);' : 'transform: translateX(0px);'">
                            <svg x-show="hasStipend" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                            </svg>
                            <svg x-show="!hasStipend" class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                    </button>
                    <input type="hidden" name="has_fixed_stipend" :value="hasStipend ? '1' : '0'">

                    <!-- Interactive Status Badge (Clickable) -->
                    <button type="button" @click="hasStipend = !hasStipend" 
                        class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1.5 rounded-xl border transition-all duration-300 cursor-pointer select-none"
                        :class="hasStipend ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'">
                        <span class="w-2 h-2 rounded-full" :class="hasStipend ? 'bg-emerald-500 animate-pulse' : 'bg-red-500'"></span>
                        <span x-text="hasStipend ? '{{ __('✓ Aktif (Diberlakukan)') }}' : '{{ __('✕ Nonaktif (Tanpa Gaji Khusus)') }}'"></span>
                    </button>
                </div>
            </div>

            <div x-show="hasStipend" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nominal Gaji Khusus Bulanan') }}<span class="text-rose-500">*</span></label>
                    <div class="flex items-center w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-1 focus-within:bg-white focus-within:ring-2 focus-within:ring-purple-500/20 focus-within:border-purple-500 transition-all cursor-text"
                        @click="$refs.stipendAmountInput?.focus()">
                        <span class="text-xs font-mono font-bold text-purple-700 select-none shrink-0" x-text="currencyPrefix"></span>
                        <input type="number" step="any" min="0" name="fixed_stipend_amount" x-ref="stipendAmountInput" value="{{ old('fixed_stipend_amount', $investor->fixed_stipend_amount) }}"
                            class="w-full px-2.5 py-1.5 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Rutin Pembayaran Bulanan') }}</label>
                    <div class="relative">
                        <input type="number" min="1" max="31" name="stipend_payment_day" value="{{ old('stipend_payment_day', $investor->stipend_payment_day) }}"
                            class="w-full pr-14 pl-3.5 py-2.5 rounded-xl text-xs font-bold border border-slate-200 bg-slate-50 focus:bg-white focus:border-purple-500 outline-none">
                        <span class="absolute right-3.5 top-2.5 text-xs text-slate-400 font-medium">{{ __('Tiap bln') }}</span>
                    </div>
                </div>

                <div class="md:col-span-2 pt-2">
                    <label class="flex items-start gap-2.5 p-3 rounded-2xl bg-purple-50/60 border border-purple-100 cursor-pointer">
                        <input type="checkbox" name="include_stipend_in_bep" value="1" {{ old('include_stipend_in_bep', $investor->include_stipend_in_bep) ? 'checked' : '' }} class="mt-0.5 text-purple-600 rounded">
                        <div class="text-xs text-slate-700">
                            <span class="font-bold text-purple-900">{{ __('Perhitungkan Gaji Khusus ke Progres Balik Modal (BEP)') }}</span>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ __('Jika dicentang, akumulasi gaji khusus yang diterima investor akan mengurangi sisa modal BEP. Jika tidak dicentang (default), gaji khusus dianggap honorarium terpisah dari pengembalian modal.') }}
                            </p>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Card 5: Akun Login Portal Investor -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xs">5</div>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Akses Portal Mandiri Investor') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Kelola kredensial login akun portal mandiri bagi investor.') }}</p>
                </div>
            </div>

            @if($investor->user)
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-slate-600">{{ __('Akun Portal Terhubung:') }}</span>
                            <strong class="font-mono text-emerald-800">{{ $investor->user->email }}</strong>
                        </div>
                        <span class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md bg-emerald-200/60 text-emerald-800">
                            {{ __('Peran:') }} {{ strtoupper($investor->user->role) }}
                        </span>
                    </div>

                    <div class="pt-2 border-t border-emerald-200/60">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Reset / Ganti Password Portal (Opsional)') }}</label>
                        <input type="password" name="reset_portal_password" placeholder="{{ __('Kosongkan jika tidak ingin mengubah password') }}"
                            class="w-full px-3.5 py-2 rounded-xl text-xs border border-slate-200 bg-white focus:border-sky-500 outline-none">
                        <p class="text-[10px] text-slate-500 mt-1">{{ __('Isi hanya jika investor meminta reset password akun login portal.') }}</p>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3" x-data="{ createAcc: false }">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-slate-600">
                            <strong>{{ __('Investor ini belum memiliki akun login portal.') }}</strong>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ __('Aktifkan untuk membuatkan akun akses self-service investor.') }}</p>
                        </div>
                        <button type="button" @click="createAcc = !createAcc" 
                            style="width: 54px; height: 28px;"
                            class="relative inline-flex shrink-0 cursor-pointer rounded-full p-0.5 transition-colors duration-300 ease-in-out select-none"
                            :class="createAcc ? 'bg-emerald-500' : 'bg-red-500'">
                            <span class="pointer-events-none inline-flex items-center justify-center w-6 h-6 rounded-full bg-white shadow-md transition-all duration-300 ease-in-out"
                                :style="createAcc ? 'transform: translateX(26px);' : 'transform: translateX(0px);'">
                                <svg x-show="createAcc" class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                                <svg x-show="!createAcc" class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                            </span>
                        </button>
                        <input type="hidden" name="create_user_account" :value="createAcc ? '1' : '0'">
                    </div>

                    <div x-show="createAcc" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-3 border-t border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Email Login') }}</label>
                            <input type="email" name="user_email" value="{{ $investor->email }}" placeholder="{{ __('investor@domain.com') }}"
                                class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white focus:border-sky-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Password Awal') }}</label>
                            <input type="password" name="user_password" placeholder="{{ __('Minimal 6 karakter') }}"
                                class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-white focus:border-sky-500 outline-none">
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Card 6: Catatan Tambahan -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-2">
            <label class="block text-xs font-semibold text-slate-700">{{ __('Catatan Perjanjian / Ketentuan Khusus') }}</label>
            <textarea name="notes" rows="3" placeholder="{{ __('Tuliskan catatan tambahan mengenai perjanjian kerja sama...') }}"
                class="w-full px-3.5 py-2.5 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">{{ old('notes', $investor->notes) }}</textarea>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200/80">
            <a href="{{ route('investors.show', $investor) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">{{ __('Batal') }}</a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>{{ __('Simpan Perubahan') }}</button>
        </div>
    </form>
</div>
@endsection

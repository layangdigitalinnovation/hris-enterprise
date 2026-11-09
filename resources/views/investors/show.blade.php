@extends('layouts.app')

@section('title', __('Portofolio Investor: :name', ['name' => $investor->name]))

@section('content')
<div class="space-y-6 pb-12">
    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('investors.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Investor') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ $investor->investor_code }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span>{{ $investor->name }}</span>
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold {{ $investor->currency === 'SAR' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60' }}">
                    {{ $investor->currency === 'SAR' ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <a href="{{ route('investors.pdf', $investor) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Cetak Portofolio (PDF)') }}</span>
            </a>

            <a href="{{ route('investors.edit', $investor) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span>{{ __('Edit Investor') }}</span>
            </a>

            <!-- Tombol Catat Transaksi Tanpa Icon Ganda -->
            <a href="{{ route('investors.distributions.create', $investor) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 whitespace-nowrap transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Catat Bagi Hasil / Gaji') }}</span>
            </a>
        </div>
    </div>

    <!-- Profile & Investment Header Banner -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <div class="lg:col-span-8 flex flex-col sm:flex-row sm:items-center gap-5">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr {{ $investor->currency === 'SAR' ? 'from-emerald-600 to-teal-700' : 'from-blue-600 to-indigo-700' }} text-white flex items-center justify-center font-bold text-xl shrink-0 shadow-md">
                    {{ strtoupper(substr($investor->name, 0, 2)) }}
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-3 flex-wrap">
                        <span class="text-lg font-bold text-slate-900">{{ $investor->name }}</span>
                        <span class="text-xs font-mono font-semibold px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700">{{ $investor->investor_code }}</span>
                        @if($investor->status === 'active')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ __('Aktif') }}</span>
                        @elseif($investor->status === 'completed')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ __('Selesai (BEP Lunas)') }}</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ __('Ditangguhkan') }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500">
                        @if($investor->branch)
<span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>{{ __('Proyek:') }} <strong class="text-slate-700">{{ __($investor->branch->name) }}</strong>
                            </span>
                        @endif

                        @if($investor->nik_or_passport)
<span>{{ __('NIK/Paspor:') }}<strong class="text-slate-700 font-mono">{{ $investor->nik_or_passport }}</strong></span>
                        @endif

                        @if($investor->phone)
<span>{{ __('WhatsApp/HP:') }}<strong class="text-slate-700">{{ $investor->phone }}</strong></span>
                        @endif

                        @if($investor->email)
<span>{{ __('Email:') }}<strong class="text-slate-700">{{ $investor->email }}</strong></span>
                        @endif
                    </div>

                    <!-- Bank Details Pill -->
                    @if($investor->bank_name || $investor->bank_account_no)
<div class="pt-1 flex items-center gap-2 text-xs text-slate-600">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <span>{{ __('Rekening Pencairan:') }}<strong>{{ $investor->bank_name ?? 'Bank' }}</strong> - <span class="font-mono font-bold text-slate-800">{{ $investor->bank_account_no }}</span> ({{ __('a.n') }} {{ $investor->bank_account_holder ?? $investor->name }})</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tanggal Investasi & Quick Stats -->
            <div class="lg:col-span-4 lg:border-s lg:border-slate-100 lg:ps-6 space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ __('Mulai Investasi:') }}</span>
                    <span class="font-semibold text-slate-800">{{ $investor->investment_date?->translatedFormat('d F Y') ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ __('Porsi Bagi Hasil:') }}</span>
                    <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ number_format($investor->profit_share_percentage, 2) }}% ({{ $investor->profit_share_scheme === 'net_profit' ? __('Laba Bersih') : ($investor->profit_share_scheme === 'gross_revenue' ? __('Omset') : __('Tetap')) }})</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ __('Gaji Khusus:') }}</span>
                    @if($investor->has_fixed_stipend)
                        <span class="font-bold text-purple-700 font-mono">{{ format_currency($investor->fixed_stipend_amount, $investor->currency) }} {{ __('/ bln') }}</span>
                    @else
                        <span class="text-slate-400">{{ __('Tidak ada') }}</span>
                    @endif
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500">{{ __('Target Akhir:') }}</span>
                    <span class="font-semibold text-slate-800">{{ $investor->contract_end_date?->translatedFormat('d F Y') ?? __('Kerja Sama Berkelanjutan') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Executive BEP & Financial Metric Gauges -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Modal Awal Tertanam -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Modal Disetor (Capital)') }}</span>
            <div class="mt-2 text-xl font-bold text-slate-900 font-mono">
                {{ format_currency($investor->initial_investment, $investor->currency) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Target acuan pengembalian 100% BEP') }}</p>
        </div>

        <!-- 2. Akumulasi Bagi Hasil Terbayar -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Bagi Hasil Diterima') }}</span>
            <div class="mt-2 text-xl font-bold text-emerald-600 font-mono">
                {{ format_currency($investor->total_profit_share_paid, $investor->currency) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Akumulasi dividen status dibayar (Paid)') }}</p>
        </div>

        <!-- 3. Akumulasi Gaji Khusus Terbayar -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Gaji Khusus Terbayar') }}</span>
            <div class="mt-2 text-xl font-bold text-purple-600 font-mono">
                {{ format_currency($investor->total_stipend_paid, $investor->currency) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">
                {{ $investor->include_stipend_in_bep ? __('Diperhitungkan ke BEP') : __('Honorarium operasional murni') }}
            </p>
        </div>

        <!-- 4. Sisa Modal Menuju BEP -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Sisa Menuju Titik Impas (BEP)') }}</span>
            <div class="mt-2 text-xl font-bold {{ $investor->is_bep_achieved ? 'text-emerald-600' : 'text-amber-600' }} font-mono">
                {{ format_currency($investor->bep_remaining, $investor->currency) }}
            </div>
            <div class="mt-1 flex items-center justify-between text-[11px]">
                <span class="{{ $investor->is_bep_achieved ? 'text-emerald-600 font-bold' : 'text-slate-500' }}">
                    {{ $investor->is_bep_achieved ? __('BEP Tercapai (100%)') : __('Progress: :progress%', ['progress' => $investor->bep_progress_percentage]) }}
                </span>
                @if($investor->estimated_months_to_bep !== null && $investor->estimated_months_to_bep > 0)
                    <span class="text-blue-600 font-semibold">{{ __('~:months bln lagi', ['months' => $investor->estimated_months_to_bep]) }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- BEP Visual Progress Tracker Card -->
    <div class="bg-gradient-to-r {{ $investor->is_bep_achieved ? 'from-emerald-900 to-teal-900' : 'from-slate-900 via-blue-950 to-slate-900' }} text-white rounded-3xl p-6 sm:p-7 shadow-md relative overflow-hidden">
        <div class="relative z-10 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/10 text-white backdrop-blur-sm border border-white/20">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>{{ __('Break-Even Point (BEP) / Payback Tracker') }}</span>
                    <h2 class="text-lg font-bold mt-1">{{ __('Capaian Pengembalian Modal Penanam Modal') }}</h2>
                </div>

                <div class="text-right">
                    <span class="text-3xl font-extrabold font-mono text-emerald-400">{{ $investor->bep_progress_percentage }}%</span>
                    <p class="text-xs text-slate-300">
                        @if($investor->is_bep_achieved)
                            {{ __('Laba Murni (Pure Profit Phase) - ROI: +:roi%', ['roi' => $investor->roi_percentage]) }}
                        @else
                            {{ __('Akumulasi Balik Modal Berjalan') }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Big Gauge Bar -->
            <div class="w-full bg-white/10 rounded-full h-4 p-0.5 overflow-hidden backdrop-blur-sm">
                <div class="h-3 rounded-full {{ $investor->is_bep_achieved ? 'bg-gradient-to-r from-emerald-400 to-teal-300' : 'bg-gradient-to-r from-blue-500 to-emerald-400' }} transition-all duration-700 shadow-lg" style="width: {{ min(100, $investor->bep_progress_percentage) }}%"></div>
            </div>

            <!-- Milestone Points -->
            <div class="flex items-center justify-between text-xs text-slate-300 pt-1 font-mono">
                <div>
                    <span class="text-slate-400 block text-[10px] uppercase font-sans">{{ __('Total Modal Awal') }}</span>
                    <strong>{{ format_currency($investor->initial_investment, $investor->currency) }}</strong>
                </div>
                <div class="text-center">
                    <span class="text-slate-400 block text-[10px] uppercase font-sans">{{ __('Dana Diterima Pengembalian') }}</span>
                    <strong class="text-emerald-300">{{ format_currency($investor->total_return_for_bep, $investor->currency) }}</strong>
                </div>
                <div class="text-right">
                    <span class="text-slate-400 block text-[10px] uppercase font-sans">{{ __('Sisa Menuju Titik Impas') }}</span>
                    <strong class="{{ $investor->is_bep_achieved ? 'text-emerald-300' : 'text-amber-300' }}">{{ format_currency($investor->bep_remaining, $investor->currency) }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi Distribusi & Pembayaran -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">{{ __('Riwayat Distribusi Bagi Hasil, Gaji Khusus & Pengembalian Modal') }}</h3>
                <p class="text-[11px] text-slate-500">{{ __('Daftar mutasi berkala seluruh pencairan dividen dan honorarium.') }}</p>
            </div>

            <!-- Tombol Tambah Transaksi Tanpa Icon Ganda -->
            <a href="{{ route('investors.distributions.create', $investor) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition-all self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>{{ __('Tambah Transaksi') }}</a>
        </div>

        @if($investor->distributions->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h4 class="text-xs font-bold text-slate-700">{{ __('Belum Ada Transaksi Distribusi') }}</h4>
                <p class="text-[11px] text-slate-400 mt-0.5 mb-4">{{ __('Catat pembagian bagi hasil bulanan atau gaji khusus investor pertama kali.') }}</p>
                <a href="{{ route('investors.distributions.create', $investor) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 shadow-xs transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>{{ __('Catat Distribusi Baru') }}</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="px-5 py-3.5">{{ __('No. Transaksi') }}</th>
                            <th class="px-5 py-3.5">{{ __('Jenis Distribusi') }}</th>
                            <th class="px-5 py-3.5">{{ __('Periode') }}</th>
                            <th class="px-5 py-3.5">{{ __('Tanggal') }}</th>
                            <th class="px-5 py-3.5">{{ __('Dasar Acuan') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Nominal') }}</th>
                            <th class="px-5 py-3.5 text-center">{{ __('Status') }}</th>
                            <th class="px-5 py-3.5">{{ __('Bukti & Ref Transfer') }}</th>
                            <th class="px-5 py-3.5 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($investor->distributions as $dist)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="px-5 py-3.5 font-mono font-bold text-slate-800">
                                    {{ $dist->distribution_number }}
                                </td>

                                <td class="px-5 py-3.5">
                                    @if($dist->type === 'profit_share')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">{{ $dist->type_label }}</span>
                                    @elseif($dist->type === 'stipend')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/60">{{ $dist->type_label }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">{{ $dist->type_label }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 font-semibold text-slate-800">
                                    {{ $dist->period_label }}
                                </td>

                                <td class="px-5 py-3.5 text-slate-500">
                                    {{ $dist->distribution_date?->format('d/m/Y') }}
                                </td>

                                <td class="px-5 py-3.5 text-[11px] text-slate-600">
                                    @if($dist->basis_amount > 0)
                                        <span class="font-mono">{{ format_currency($dist->basis_amount, $dist->currency) }}</span>
                                        @if($dist->percentage_applied > 0)
                                            <span class="text-blue-600 font-bold">({{ $dist->percentage_applied }}%)</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 text-right font-mono font-bold text-slate-900 text-sm">
                                    {{ format_currency($dist->amount, $dist->currency) }}
                                </td>

                                <td class="px-5 py-3.5 text-center">
                                    @if($dist->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            ✓ {{ __('Lunas Terbayar') }}
                                        </span>
                                    @elseif($dist->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800">{{ __('Disetujui') }}</span>
                                    @elseif($dist->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">{{ __('Dijadwalkan') }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">{{ __('Dibatalkan') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 text-[11px]">
                                    @if($dist->payment_date)
<div class="text-slate-800 font-semibold">{{ $dist->payment_date->format('d/m/Y') }}</div>
                                        <div class="text-slate-400 text-[10px] font-mono">{{ $dist->payment_method_label }}: {{ $dist->payment_reference ?? '-' }}</div>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Belum dibayar') }}</span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($dist->status !== 'paid')<!-- Tombol Bayar Sekarang membuka Dedicated Page Bayar -->
                                            <a href="{{ route('investors.distributions.pay', $dist) }}" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold transition-all shadow-xs">{{ __('Bayar Sekarang') }}</a>
                                        @endif

                                        <!-- Edit Transaksi (Dedicated Page) -->
                                        <a href="{{ route('investors.distributions.edit', $dist) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Edit Transaksi') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>

                                        <!-- Hapus Transaksi -->
                                        <form action="{{ route('investors.distributions.destroy', $dist) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus catatan transaksi :number ini?', ['number' => $dist->distribution_number]) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Hapus Transaksi') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection

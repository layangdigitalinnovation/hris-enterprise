@extends('layouts.app')

@section('title', __('Pinjaman Karyawan'))

@section('content')
<div class="space-y-6">
    <!-- Header Banner & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">{{ __('Dashboard') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">{{ __('Pinjaman Karyawan') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
                {!! __('Pinjaman & Kasbon Karyawan') !!}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1 max-w-2xl">
                {{ __('Fasilitas pinjaman karyawan & kasbon perusahaan dengan simulasi tenor fleksibel, integrasi potong slip gaji otomatis, dan pencatatan angsuran transparan (IDR & SAR).') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 whitespace-nowrap transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Ajukan Pinjaman Baru') }}</span>
            </a>
        </div>
    </div>

    <!-- Metrik Statistik Finansial (Dual-Currency IDR & SAR) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: {{ __('Menunggu Approval') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs relative overflow-hidden group hover:border-amber-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700 bg-amber-50 px-2.5 py-1 rounded-md border border-amber-200/60">
                        {{ __('Menunggu Approval') }}
                    </span>
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['pending_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Pengajuan') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ format_currency($metrics['pending_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-amber-600">
                        {{ format_currency($metrics['pending_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: Disetujui ({{ __('Siap Dicairkan') }}) -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs relative overflow-hidden group hover:border-blue-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200/60">
                        {{ __('Disetujui (Siap Cair)') }}
                    </span>
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['approved_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Pinjaman') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ format_currency($metrics['approved_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-blue-600">
                        {{ format_currency($metrics['approved_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: Aktif Berjalan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs relative overflow-hidden group hover:border-emerald-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200/60">
                        {{ __('Pinjaman Aktif') }}
                    </span>
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['active_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Cicilan Aktif') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ format_currency($metrics['active_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-emerald-600">
                        {{ format_currency($metrics['active_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 4: {{ __('Selesai / Lunas') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs relative overflow-hidden group hover:border-purple-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-700 bg-purple-50 px-2.5 py-1 rounded-md border border-purple-200/60">{{ __('Telah Lunas') }}</span>
                    <div class="w-10 h-10 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['completed_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Lunas') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ format_currency($metrics['completed_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-purple-700">
                        {{ format_currency($metrics['completed_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs">
        <form action="{{ route('loans.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <!-- Search -->
            <div class="lg:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Cari Pinjaman / Pegawai') }}</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('No. Pinjaman, nama karyawan, kode, alasan...') }}" class="w-full text-xs rounded-xl border border-slate-200 pl-9 pr-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Status Pinjaman') }}</label>
                <select name="status" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>{{ __('Semua Status') }}</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ __('Menunggu Approval') }}</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>{{ __('Disetujui (Siap Cair)') }}</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>{{ __('Aktif Berjalan') }}</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ __('Lunas / Selesai') }}</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ __('Ditolak') }}</option>
                </select>
            </div>

            <!-- Mata Uang -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">{{ __('Mata Uang') }}</label>
                <select name="currency" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all">
                    <option value="all" {{ request('currency') == 'all' ? 'selected' : '' }}>{{ __('Semua Valuta') }}</option>
                    <option value="IDR" {{ request('currency') == 'IDR' ? 'selected' : '' }}>🇮🇩 IDR (Rupiah)</option>
                    <option value="SAR" {{ request('currency') == 'SAR' ? 'selected' : '' }}>🇸🇦 SAR (Riyal Saudi)</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold shadow-xs transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <span>{{ __('Filter') }}</span>
                </button>
                <a href="{{ route('loans.index') }}" class="px-3 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 rounded-xl text-xs font-medium transition-all" title="{{ __('Reset Filter') }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Pinjaman -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4">{{ __('No. Pinjaman & Pegawai') }}</th>
                        <th class="py-3.5 px-4">{{ __('Nominal Pokok') }}</th>
                        <th class="py-3.5 px-4">{{ __('Tenor & Cicilan') }}</th>
                        <th class="py-3.5 px-4">{{ __('Progres Pelunasan') }}</th>
                        <th class="py-3.5 px-4">{{ __('Metode Bayar') }}</th>
                        <th class="py-3.5 px-4">{{ __('Status') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-sans">
                    @forelse($loans as $loan)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <!-- Loan No & Employee -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-slate-800 to-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                                        {{ substr($loan->employee->first_name ?? 'E', 0, 1) }}{{ substr($loan->employee->last_name ?? '', 0, 1) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('loans.show', $loan) }}" class="font-bold text-slate-900 hover:text-blue-600 transition-colors flex items-center gap-1.5">
                                            <span>{{ $loan->loan_number }}</span>
                                            <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 rounded {{ $loan->currency === 'SAR' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                                                {{ $loan->currency === 'SAR' ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                                            </span>
                                        </a>
                                        <p class="text-xs text-slate-600 font-medium mt-0.5">
                                            {{ $loan->employee->full_name ?? '-' }}
                                        </p>
                                        <span class="text-[10px] text-slate-400">
                                            {{ $loan->employee->department->name ?? 'Dept' }} • {{ $loan->branch->name ?? 'Cabang' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Nominal -->
                            <td class="py-3.5 px-4">
                                <div class="font-mono">
                                    <span class="text-xs font-bold text-slate-900 block">
                                        {{ format_currency($loan->effective_principal, $loan->currency) }}
                                    </span>
                                    @if($loan->status === 'approved' && $loan->approved_amount != $loan->amount)
                                        <span class="text-[10px] text-slate-400 line-through">
                                            {{ __('Diajukan:') }} {{ format_currency($loan->amount, $loan->currency) }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-400">
                                            {{ __('Bunga:') }} {{ (float)$loan->interest_rate_percent }}%
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Tenor & Monthly Installment -->
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">
                                    {{ $loan->tenor_months }} {{ __('Bulan') }}
                                </span>
                                <span class="text-[11px] font-mono text-slate-500 font-medium">
                                    {{ format_currency($loan->monthly_installment, $loan->currency) }} {{ __('/ bln') }}
                                </span>
                            </td>

                            <!-- Progres Pelunasan -->
                            <td class="py-3.5 px-4 min-w-[170px]">
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="text-slate-600 font-medium">
                                            {{ __(':paid dari :total cicilan', ['paid' => $loan->paid_installments_count, 'total' => $loan->tenor_months]) }}
                                        </span>
                                        <span class="font-bold font-mono {{ $loan->repayment_progress_percentage == 100 ? 'text-purple-600' : 'text-blue-600' }}">
                                            {{ $loan->repayment_progress_percentage }}%
                                        </span>
                                    </div>
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r {{ $loan->repayment_progress_percentage == 100 ? 'from-purple-500 to-indigo-600' : 'from-blue-500 to-indigo-600' }} rounded-full" style="width: {{ $loan->repayment_progress_percentage }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] font-mono text-slate-400">
                                        <span>{{ __('Terbayar:') }} {{ format_currency($loan->total_repaid_amount, $loan->currency) }}</span>
                                        <span class="text-rose-600 font-medium">{{ __('Sisa:') }} {{ format_currency($loan->remaining_amount, $loan->currency) }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- Metode Bayar / Payroll Deduction -->
                            <td class="py-3.5 px-4">
                                @if($loan->payroll_deduction)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        <svg class="w-3 h-3 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        {{ __('Potong Payroll') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">{{ __('Transfer Mandiri') }}</span>
                                @endif
                            </td>

                            <!-- Status Pill -->
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $loan->status_badge_classes }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ in_array($loan->status, ['active', 'approved']) ? 'bg-blue-500 animate-pulse' : ($loan->status === 'completed' ? 'bg-purple-500' : ($loan->status === 'pending' ? 'bg-amber-500' : 'bg-rose-500')) }}"></span>
                                    {{ $loan->status_label }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Detail Button -->
                                    <a href="{{ route('loans.show', $loan) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all" title="{{ __('Lihat Rincian & Jadwal Angsuran') }}">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <span>{{ __('Detail') }}</span>
                                    </a>

                                    <!-- Quick Action Form Page based on Status -->
                                    @if($loan->status === 'pending' && auth()->user()->canApproveLoan())
                                        <a href="{{ route('loans.approve.form', $loan) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition-all" title="{{ __('Proses Persetujuan') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                            <span>{{ __('Setujui') }}</span>
                                        </a>
                                    @elseif($loan->status === 'approved' && auth()->user()->canDisburseLoan())
                                        <a href="{{ route('loans.disburse.form', $loan) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition-all" title="{{ __('Cairkan Dana Pinjaman') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <span>{{ __('Cairkan') }}</span>
                                        </a>
                                    @elseif(in_array($loan->status, ['active', 'disbursed']) && auth()->user()->canManageLoans())
                                        <a href="{{ route('loans.repay.form', $loan) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-2xs transition-all" title="{{ __('Catat Angsuran / Pelunasan') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>{{ __('Bayar') }}</span>
                                        </a>
                                    @endif

                                    <!-- Print PDF Contract -->
                                    <a href="{{ route('loans.pdf', $loan) }}" target="_blank" class="p-1.5 rounded-lg border border-slate-200 bg-white hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 text-slate-500 text-xs font-semibold transition-all" title="{{ __('Cetak Surat Perjanjian Pinjaman (PDF)') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-800">{{ __('Belum Ada Data Pinjaman') }}</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    {{ __('Tidak ada data pinjaman karyawan') }} yang sesuai dengan kriteria filter pencarian Anda saat ini.
                                </p>
                                <div class="mt-4">
                                    <a href="{{ route('loans.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-semibold shadow-xs transition-all">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>{{ __('Buat Pengajuan Pinjaman') }}</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($loans->hasPages())
            <div class="px-4 py-3 border-t border-slate-200/80 bg-slate-50/50">
                {{ $loans->links() }}
            </div>
        @endif
    </div>
</div>
@endsection


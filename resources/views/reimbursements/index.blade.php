@extends('layouts.app')

@section('title', __('Klaim & Penggantian Reimbursement'))

@section('content')
<div class="space-y-6">
    <!-- Header Banner & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">{{ __('Dashboard') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">{{ __('Reimbursement') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </span>{!! __('Klaim & Penggantian Reimbursement') !!}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1 max-w-2xl">
                {{ __('Kelola pengajuan klaim biaya operasional, verifikasi bukti nota/kuitansi, persetujuan bertingkat, dan realisasi transfer kasir/finance.') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            @if(auth()->user()->canManageReimbursements())
                <a href="{{ route('reimbursements.report.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ __('Cetak Rekap (PDF)') }}</span>
                </a>

                @if(auth()->user()->isHrAdmin())
                    <a href="{{ route('reimbursements.categories.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                        <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                        <span>{{ __('Kelola Kategori') }}</span>
                    </a>
                @endif
            @endif

            <a href="{{ route('reimbursements.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 whitespace-nowrap transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Ajukan Reimbursement') }}</span>
            </a>
        </div>
    </div>

    <!-- Metrik Statistik Finansial -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: {{ __('Menunggu Approval') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-amber-400 transition-all flex flex-col justify-between">
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
                    <span class="text-xs text-slate-400 font-medium">{{ __('Klaim') }}</span>
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
                    <span class="font-bold text-emerald-700">
                        {{ format_currency($metrics['pending_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 2: {{ __('Disetujui (Siap Bayar)') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-blue-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200/60">
                        {{ __('Disetujui (Siap Bayar)') }}
                    </span>
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['approved_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Klaim') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-blue-700">
                        {{ format_currency($metrics['approved_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-emerald-700">
                        {{ format_currency($metrics['approved_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 3: {{ __('Telah Dibayar (Lunas)') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-emerald-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200/60">
                        {{ __('Telah Dibayar (Lunas)') }}
                    </span>
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['paid_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Klaim') }}</span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-emerald-700">
                        {{ format_currency($metrics['paid_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-emerald-700">
                        {{ format_currency($metrics['paid_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Card 4: Total Seluruh Pengajuan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-slate-400 transition-all flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200/60">
                        {{ __('Total Pengajuan') }}
                    </span>
                    <div class="w-10 h-10 bg-slate-100 text-slate-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-slate-800 font-mono">{{ $metrics['total_count'] }}</h3>
                    <span class="text-xs text-slate-400 font-medium">{{ __('Klaim') }}</span>
                    <span class="text-[10px] text-slate-400 ml-auto truncate max-w-[100px]" title="{{ $isEmployeeOnly ? __('Pribadi') : ($isManager ? __('Departemen') : __('Seluruh Perusahaan')) }}">
                        ({{ $isEmployeeOnly ? __('Pribadi') : ($isManager ? __('Dept') : __('Semua')) }})
                    </span>
                </div>
            </div>

            <div class="mt-4 pt-2.5 border-t border-slate-100 text-xs space-y-1">
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇮🇩</span> <span class="font-medium">{{ __('IDR:') }}</span>
                    </span>
                    <span class="font-bold text-slate-800">
                        {{ format_currency($metrics['total_idr'], 'IDR') }}
                    </span>
                </div>
                <div class="flex items-center justify-between font-mono">
                    <span class="text-[11px] text-slate-500 font-sans flex items-center gap-1.5">
                        <span class="text-xs">🇸🇦</span> <span class="font-medium">{{ __('SAR:') }}</span>
                    </span>
                    <span class="font-bold text-emerald-700">
                        {{ format_currency($metrics['total_sar'], 'SAR') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm space-y-3">
        <form action="{{ route('reimbursements.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="md:col-span-3 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari No. Klaim, Keperluan, Pegawai...') }}" 
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-2">
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                    <option value="">{{ __('Status: Semua') }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Menunggu Approval') }}</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Disetujui (Siap Bayar)') }}</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>{{ __('Telah Dibayar (Lunas)') }}</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Ditolak') }}</option>
                </select>
            </div>

            <!-- Category Filter -->
            <div class="md:col-span-3">
                <select name="category_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                    <option value="">-- {{ __('Semua Kategori') }} --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            [{{ $cat->code }}] {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Cabang / Mata Uang -->
            <div class="md:col-span-2">
                <select name="currency" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                    <option value="">{{ __('Semua Valuta') }}</option>
                    <option value="IDR" {{ request('currency') === 'IDR' ? 'selected' : '' }}>🇮🇩 {{ __('IDR (Rupiah)') }}</option>
                    <option value="SAR" {{ request('currency') === 'SAR' ? 'selected' : '' }}>🇸🇦 {{ __('SAR (Riyal)') }}</option>
                </select>
            </div>

            <!-- Per Page & Reset Filter -->
            <div class="md:col-span-2 flex items-center gap-2 justify-end">
                <select name="per_page" onchange="this.form.submit()" title="{{ __('Jumlah data per halaman') }}" class="w-full px-2.5 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-medium">
                    <option value="5" {{ request('per_page') == '5' ? 'selected' : '' }}>{{ __('5 / hal') }}</option>
                    <option value="10" {{ request('per_page', 10) == '10' ? 'selected' : '' }}>{{ __('10 / hal') }}</option>
                    <option value="15" {{ request('per_page') == '15' ? 'selected' : '' }}>{{ __('15 / hal') }}</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>{{ __('25 / hal') }}</option>
                </select>

                @if(request()->hasAny(['search', 'status', 'category_id', 'branch_id', 'currency', 'per_page']))
<a href="{{ route('reimbursements.index') }}" title="Reset Filter" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabel Daftar Reimbursement -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        @if($reimbursements->isEmpty())
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">{{ __('Belum Ada Catatan Reimbursement') }}</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">{{ __('Pengajuan klaim penggantian biaya operasional dan perjalanan dinas akan tercatat di sini.') }}</p>
                <a href="{{ route('reimbursements.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ __('Ajukan Reimbursement Pertama') }}</span>
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3.5 px-4">{{ __('No. Klaim & Tanggal') }}</th>
                            <th class="py-3.5 px-4">{{ __('Karyawan & Unit') }}</th>
                            <th class="py-3.5 px-4">{{ __('Kategori & Keperluan') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Nominal Pengajuan') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Bukti Nota') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Status') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($reimbursements as $item)
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- No. Klaim & Tanggal -->
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-slate-800">{{ $item->claim_number }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        {{ $item->claim_date->translatedFormat('d M Y') }}
                                    </div>
                                </td>

                                <!-- Karyawan -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center shrink-0 border border-slate-200 text-xs">
                                            {{ strtoupper(substr($item->employee?->first_name ?? 'E', 0, 1)) }}{{ strtoupper(substr($item->employee?->last_name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ $item->employee?->full_name }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">
                                                {{ $item->employee?->employee_code }} • {{ $item->employee?->department?->name ?? 'Dept' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kategori & Keperluan -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $item->category?->name ?? 'Umum' }}
                                        </span>
                                        <span class="text-[10px] font-mono font-semibold {{ $item->currency === 'SAR' ? 'text-emerald-600' : 'text-blue-600' }}">
                                            {{ $item->currency === 'SAR' ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                                        </span>
                                    </div>
                                    <div class="font-semibold text-slate-800 truncate" title="{{ $item->title }}">{{ $item->title }}</div>
                                </td>

                                <!-- Nominal -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-mono font-bold text-slate-800">
                                        {{ format_currency($item->amount, $item->currency) }}
                                    </div>
                                    @if($item->approved_amount !== null && $item->approved_amount != $item->amount)
                                        <div class="text-[11px] text-emerald-600 font-mono">
                                            {{ __('Disetujui:') }} {{ format_currency($item->approved_amount, $item->currency) }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Bukti Nota -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($item->receipt_url)
<a href="{{ $item->receipt_url }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-medium transition-all" title="{{ __('Lihat Bukti Nota') }}">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                            </svg>
                                            <span>{{ __('Lihat') }}</span>
                                        </a>
                                    @else
                                        <span class="text-[11px] text-slate-300 italic">{{ __('Tanpa Nota') }}</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $item->status_badge_classes }}">
                                        {{ $item->status_label }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($item->isApproved() && auth()->user()->canPayReimbursement())
                                            <a href="{{ route('reimbursements.pay', $item) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition-all">
                                                <span>{{ __('Bayar') }}</span>
                                            </a>
                                        @endif

                                        <a href="{{ route('reimbursements.show', $item) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Detail Klaim') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>

                                        @if($item->canBeEdited())
                                            <a href="{{ route('reimbursements.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Edit Klaim') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>

                                            <form action="{{ route('reimbursements.destroy', $item) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin membatalkan pengajuan klaim ini?') }}')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Batalkan Pengajuan') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Enterprise Pagination Footer -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap justify-center sm:justify-start">
                    <span>{{ __('Menampilkan') }} <strong class="text-slate-800 font-bold">{{ $reimbursements->firstItem() ?? 1 }}</strong> {{ __('sampai') }} <strong class="text-slate-800 font-bold">{{ $reimbursements->lastItem() ?? $reimbursements->count() }}</strong> {{ __('dari') }} <strong class="text-slate-800 font-bold">{{ $reimbursements->total() }}</strong> {{ __('klaim') }}</span>
                    <span class="hidden sm:inline text-slate-300">•</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-slate-400">{{ __('Tampilkan:') }}</span>
                        <select onchange="window.location.href = this.value" class="px-2 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 text-xs font-semibold focus:outline-none focus:border-blue-500 cursor-pointer">
                            @foreach([5, 10, 15, 25, 50] as $size)
                                <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ (int) request('per_page', 10) === $size ? 'selected' : '' }}>
                                    {{ $size }} {{ __('/ hal') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    {{ $reimbursements->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection


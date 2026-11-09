@extends('layouts.app')

@section('title', __('Manajemen Investor & Bagi Hasil'))

@section('content')
<div class="space-y-6">
    <!-- Header Banner & Breadcrumb -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">{{ __('Dashboard') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">{{ __('Investor') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </span>
                {!! __('Manajemen Investor & Bagi Hasil') !!}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1">
                {{ __('Kelola portofolio penanam modal, penetapan gaji khusus bulanan, perhitungan bagi hasil dividen berkala, dan pelacakan target Break-Even Point (BEP).') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <a href="{{ route('investors.report.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Cetak Rekap Eksekutif (PDF)') }}</span>
            </a>

            <a href="{{ route('investors.portal') }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-sm whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <span>{{ __('Pratinjau Portal Investor') }}</span>
            </a>

            <a href="{{ route('investors.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 whitespace-nowrap transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Daftarkan Investor Baru') }}</span>
            </a>
        </div>
    </div>

    <!-- Top Statistical Metric Cards (Dual Currency IDR & SAR Support) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: {{ __('Total Modal Tertanam') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-blue-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Total Modal Tertanam') }}</span>
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 space-y-1">
                @if($totalCapitalSar > 0)
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">🇸🇦 SAR</span>
                    <span class="text-lg font-bold text-slate-900 font-mono">{{ format_currency($totalCapitalSar, 'SAR') }}</span>
                </div>
                @endif
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">🇮🇩 IDR</span>
                    <span class="text-base font-bold text-slate-900 font-mono">{{ format_currency($totalCapitalIdr, 'IDR') }}</span>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-100 pt-2">
                <span>{{ __('Investor Aktif') }}</span>
                <span class="font-semibold text-slate-700">{{ __(':count Orang / Mitra', ['count' => $investors->total()]) }}</span>
            </div>
        </div>

        <!-- Card 2: {{ __('Total Bagi Hasil Terbayar') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-emerald-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Bagi Hasil Terbayar') }}</span>
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 space-y-1">
                @if($totalProfitShareSar > 0)
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">🇸🇦 SAR</span>
                    <span class="text-lg font-bold text-emerald-600 font-mono">{{ format_currency($totalProfitShareSar, 'SAR') }}</span>
                </div>
                @endif
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">🇮🇩 IDR</span>
                    <span class="text-base font-bold text-emerald-600 font-mono">{{ format_currency($totalProfitShareIdr, 'IDR') }}</span>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-100 pt-2">
                <span>{{ __('Dividen Berjalan') }}</span>
                <span class="font-semibold text-emerald-600">{{ __('Disalurkan Lancar') }}</span>
            </div>
        </div>

        <!-- Card 3: Total Gaji Khusus Terbayar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-purple-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Gaji Khusus / Imbalan') }}</span>
                <span class="p-2 rounded-xl bg-purple-50 text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3 space-y-1">
                @if($totalStipendSar > 0)
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">🇸🇦 SAR</span>
                    <span class="text-lg font-bold text-purple-600 font-mono">{{ format_currency($totalStipendSar, 'SAR') }}</span>
                </div>
                @endif
                <div class="flex items-baseline justify-between">
                    <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">🇮🇩 IDR</span>
                    <span class="text-base font-bold text-purple-600 font-mono">{{ format_currency($totalStipendIdr, 'IDR') }}</span>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-100 pt-2">
                <span>{{ __('Honorarium Tetap Bulanan') }}</span>
                <span class="font-semibold text-purple-600">{{ __('Terjadwal') }}</span>
            </div>
        </div>

        <!-- Card 4: Rata-Rata Progres BEP -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm relative overflow-hidden group hover:border-amber-400 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Rata-Rata Progres BEP') }}</span>
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>
            </div>
            <div class="mt-3">
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-bold text-slate-900 font-mono">{{ $avgBepProgress }}%</span>
                    <span class="text-xs font-semibold {{ $avgBepProgress >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $avgBepProgress >= 100 ? __('BEP Tercapai') : __('Balik Modal Berjalan') }}
                    </span>
                </div>
                <!-- Progress Bar -->
                <div class="w-full bg-slate-100 rounded-full h-2 mt-2.5 overflow-hidden">
                    <div class="h-2 rounded-full {{ $avgBepProgress >= 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-blue-500 to-amber-500' }}" style="width: {{ min(100, $avgBepProgress) }}%"></div>
                </div>
            </div>
            <div class="mt-3 text-[11px] text-slate-400 flex items-center justify-between border-t border-slate-100 pt-2">
                <span>{{ __('Status Pengembalian') }}</span>
                <span class="font-semibold text-slate-700">{{ __('Akumulatif Portofolio') }}</span>
            </div>
        </div>
    </div>

    <!-- Filter & Toolbar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm space-y-3">
        <form action="{{ route('investors.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="md:col-span-3 relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nama, kode investor, email, paspor...') }}" 
                    class="w-full pl-9 pr-4 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Cabang / Proyek -->
            <div class="md:col-span-3">
                <select name="branch_id" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    <option value="">-- Semua Cabang / Proyek --</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->country_code === 'SA' ? '🇸🇦' : '🇮🇩' }} {{ $b->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Currency Filter Pills -->
            <div class="md:col-span-2 flex items-center gap-1.5">
                <a href="{{ request()->fullUrlWithQuery(['currency' => null, 'page' => 1]) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ empty(request('currency')) ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">{{ __('Semua') }}</a>
                <a href="{{ request()->fullUrlWithQuery(['currency' => 'SAR', 'page' => 1]) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ request('currency') === 'SAR' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    🇸🇦 SAR
                </a>
                <a href="{{ request()->fullUrlWithQuery(['currency' => 'IDR', 'page' => 1]) }}" class="px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all {{ request('currency') === 'IDR' ? 'bg-blue-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    🇮🇩 IDR
                </a>
            </div>

            <!-- Status Filter -->
            <div class="md:col-span-2">
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none">
                    <option value="">{{ __('Status: Semua') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Selesai') }}</option>
                    <option value="paused" {{ request('status') === 'paused' ? 'selected' : '' }}>{{ __('Ditangguhkan') }}</option>
                </select>
            </div>

            <!-- Per Page & Reset Filter -->
            <div class="md:col-span-2 flex items-center gap-2 justify-end">
                <select name="per_page" onchange="this.form.submit()" title="{{ __('Jumlah data per halaman') }}" class="w-full px-2.5 py-2 rounded-xl text-xs border border-slate-200 bg-slate-50 focus:bg-white focus:border-blue-500 outline-none font-medium">
                    <option value="3" {{ request('per_page') == '3' ? 'selected' : '' }}>{{ __('3 / hal') }}</option>
                    <option value="6" {{ request('per_page', 6) == '6' ? 'selected' : '' }}>{{ __('6 / hal') }}</option>
                    <option value="9" {{ request('per_page') == '9' ? 'selected' : '' }}>{{ __('9 / hal') }}</option>
                    <option value="12" {{ request('per_page') == '12' ? 'selected' : '' }}>{{ __('12 / hal') }}</option>
                    <option value="24" {{ request('per_page') == '24' ? 'selected' : '' }}>{{ __('24 / hal') }}</option>
                </select>

                @if(request()->hasAny(['search', 'branch_id', 'currency', 'status', 'per_page']))
<a href="{{ route('investors.index') }}" title="Reset Filter" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition-colors shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Investors Grid / Cards -->
    @if($investors->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center shadow-sm">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <h3 class="text-base font-bold text-slate-800">{{ __('Belum Ada Data Investor') }}</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1 mb-5">{{ __('Mulai catat data investor, suntikan modal proyek, persentase bagi hasil, dan gaji khusus bulanan sekarang.') }}</p>
            <a href="{{ route('investors.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>{{ __('Daftarkan Investor Pertama') }}</a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($investors as $inv)
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm hover:shadow-md hover:border-blue-400 transition-all flex flex-col justify-between overflow-hidden group">
                    <!-- Card Top -->
                    <div class="p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr {{ $inv->currency === 'SAR' ? 'from-emerald-600 to-teal-700' : 'from-blue-600 to-indigo-700' }} text-white flex items-center justify-center font-bold text-sm shrink-0 shadow-sm">
                                    {{ strtoupper(substr($inv->name, 0, 2)) }}
                                </div>
                                <div class="truncate">
                                    <h3 class="text-sm font-bold text-slate-900 truncate group-hover:text-blue-600 transition-colors">
                                        {{ $inv->name }}
                                    </h3>
                                    <p class="text-[11px] font-mono text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <span>{{ $inv->investor_code }}</span>
                                        @if($inv->user_id)
<span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] bg-blue-50 text-blue-600 font-sans font-medium" title="{{ __('Memiliki akun login portal') }}">{{ __('Akun Portal Aktif') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div>
                                @if($inv->status === 'active')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">{{ __('Aktif') }}</span>
                                @elseif($inv->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">{{ __('Selesai') }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/60">{{ __('Ditangguhkan') }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Cabang & Proyek Pill -->
                        <div class="mt-3 flex items-center gap-2 flex-wrap text-[11px]">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-medium">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                {{ $inv->branch ? $inv->branch->name : __('Seluruh Unit / Proyek Terpusat') }}
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-lg text-[10px] font-semibold {{ $inv->currency === 'SAR' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700' }}">
                                {{ $inv->currency === 'SAR' ? __('🇸🇦 SAR (Saudi Riyal)') : __('🇮🇩 IDR (Rupiah)') }}
                            </span>
                        </div>

                        <!-- Capital & Share Info Box -->
                        <div class="mt-4 p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                            <div class="flex items-baseline justify-between">
                                <span class="text-[11px] text-slate-500">{{ __('Modal Disetor:') }}</span>
                                <span class="text-sm font-bold text-slate-900 font-mono">
                                    {{ format_currency($inv->initial_investment, $inv->currency) }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-[11px]">
                                <span class="text-slate-500">{{ __('Porsi Bagi Hasil:') }}</span>
                                <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                                    {{ number_format($inv->profit_share_percentage, 2) }}% ({{ $inv->profit_share_scheme === 'net_profit' ? __('Laba Bersih') : ($inv->profit_share_scheme === 'gross_revenue' ? __('Omset') : __('Tetap')) }})
                                </span>
                            </div>

                            @if($inv->has_fixed_stipend)
                                <div class="flex items-center justify-between text-[11px] border-t border-slate-200/60 pt-1.5">
                                    <span class="text-slate-500">{{ __('Gaji Khusus Bulanan:') }}</span>
                                    <span class="font-bold text-purple-700 font-mono">
                                        {{ format_currency($inv->fixed_stipend_amount, $inv->currency) }} <span class="font-sans font-normal text-slate-400">{{ __('/ bln') }}</span>
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- BEP Tracking Gauge -->
                        <div class="mt-4 space-y-1.5">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-medium text-slate-600">{{ __('Progres Balik Modal (BEP)') }}</span>
                                <span class="font-bold {{ $inv->is_bep_achieved ? 'text-emerald-600' : 'text-slate-900' }} font-mono">
                                    {{ $inv->bep_progress_percentage }}%
                                </span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
                                <div class="h-2.5 rounded-full {{ $inv->is_bep_achieved ? 'bg-emerald-500' : 'bg-gradient-to-r from-blue-500 to-indigo-600' }} transition-all duration-500" style="width: {{ min(100, $inv->bep_progress_percentage) }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-500 pt-0.5">
                                <span>{{ __('Diterima:') }}<strong class="text-slate-700 font-mono">{{ format_currency($inv->total_return_for_bep, $inv->currency) }}</strong></span>
                                <span>{{ __('Sisa:') }}<strong class="{{ $inv->is_bep_achieved ? 'text-emerald-600' : 'text-amber-600' }} font-mono">{{ format_currency($inv->bep_remaining, $inv->currency) }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="p-3 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div class="text-[11px] text-slate-400">
                            {{ __(':count Transaksi', ['count' => $inv->distributions->count()]) }}
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('investors.show', $inv) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition-all">
                                <span>{{ __('Detail Portofolio') }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Enterprise Pagination & Info Footer -->
        <div class="mt-6 bg-white rounded-2xl border border-slate-200/80 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <!-- Info & Per-Page Controls -->
            <div class="flex items-center gap-3 text-xs text-slate-500 flex-wrap justify-center sm:justify-start">
                <div class="flex items-center gap-1.5 font-medium">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ __('Menampilkan') }}<strong class="text-slate-800 font-bold">{{ $investors->firstItem() ?? ($investors->count() > 0 ? 1 : 0) }}</strong>{{ __('sampai') }}<strong class="text-slate-800 font-bold">{{ $investors->lastItem() ?? $investors->count() }}</strong>{{ __('dari') }}<strong class="text-slate-800 font-bold">{{ $investors->total() }}</strong>{{ __('investor') }}</span>
                </div>

                <span class="hidden sm:inline text-slate-300">•</span>

                <div class="flex items-center gap-1.5">
                    <span class="text-slate-400">{{ __('Tampilkan:') }}</span>
                    <select onchange="window.location.href = this.value" class="px-2.5 py-1 rounded-xl border border-slate-200 bg-slate-50 text-slate-700 text-xs font-semibold focus:outline-none focus:border-blue-500 focus:bg-white transition-all cursor-pointer">
                        @foreach([3, 6, 9, 12, 24] as $size)
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ (int) request('per_page', 6) === $size ? 'selected' : '' }}>
                                {{ __($size . ' / hal') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Pagination Buttons -->
            <div class="flex items-center gap-1.5 flex-wrap justify-center">
                @if($investors->hasPages())
                    {{-- Tombol Sebelumnya --}}
                    @if ($investors->onFirstPage())
                        <span class="px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center gap-1.5 text-xs font-medium select-none">
                            <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>{{ __('Sebelumnya') }}</span>
                        </span>
                    @else
                        <a href="{{ $investors->previousPageUrl() }}" rel="prev" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 shadow-2xs text-xs font-medium flex items-center gap-1.5 transition">
                            <svg class="w-3.5 h-3.5 text-slate-600 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span>{{ __('Sebelumnya') }}</span>
                        </a>
                    @endif

                    {{-- Tombol Nomor Halaman --}}
                    <div class="flex items-center gap-1">
                        @for ($page = 1; $page <= $investors->lastPage(); $page++)
                            @if ($page == $investors->currentPage())
                                <span class="min-w-[32px] h-8 px-2 rounded-xl bg-blue-600 text-white text-xs font-bold flex items-center justify-center shadow-xs shadow-blue-500/25 select-none">
                                    {{ $page }}
                                </span>
                            @elseif ($page <= 2 || $page >= $investors->lastPage() - 1 || abs($page - $investors->currentPage()) <= 1)
                                <a href="{{ $investors->url($page) }}" class="min-w-[32px] h-8 px-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 text-xs font-medium flex items-center justify-center shadow-2xs transition">
                                    {{ $page }}
                                </a>
                            @elseif ($page == 3 && $investors->lastPage() > 4)
                                <span class="px-1 text-slate-400 text-xs font-semibold select-none">...</span>
                            @endif
                        @endfor
                    </div>

                    {{-- Tombol Berikutnya --}}
                    @if ($investors->hasMorePages())
                        <a href="{{ $investors->nextPageUrl() }}" rel="next" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-slate-700 shadow-2xs text-xs font-medium flex items-center gap-1.5 transition">
                            <span>{{ __('Berikutnya') }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-600 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <span class="px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center gap-1.5 text-xs font-medium select-none">
                            <span>{{ __('Berikutnya') }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </span>
                    @endif
                @else
                    {{-- 1 Halaman Tunggal --}}
                    <span class="px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center gap-1.5 text-xs font-medium select-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ __('Sebelumnya') }}</span>
                    </span>
                    <span class="min-w-[32px] h-8 px-2 rounded-xl bg-blue-600 text-white text-xs font-bold flex items-center justify-center shadow-xs shadow-blue-500/25 select-none">
                        1
                    </span>
                    <span class="px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-100 text-slate-400 cursor-not-allowed flex items-center gap-1.5 text-xs font-medium select-none">
                        <span>{{ __('Berikutnya') }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection

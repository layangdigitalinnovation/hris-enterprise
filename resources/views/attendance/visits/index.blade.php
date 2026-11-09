@extends('layouts.app', ['headerTitle' => __('Absen Visit / Kunjungan Lapangan'), 'title' => __('Absen Visit')])

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Absen Visit & Kunjungan Lapangan') }}</h3>
                    <p class="text-xs text-slate-500">{{ __('Pencatatan kunjungan klien/dinas fleksibel berkali-kali dalam sehari dengan kewajiban check-out') }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('attendance.visits.create') }}"
                style="background-color: #2563eb; color: #ffffff;"
                class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="text-white">{{ __('Check-In Kunjungan Baru') }}</span>
            </a>
        </div>
    </div>

    <!-- Statistic Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Today -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Total Kunjungan Hari Ini') }}</span>
                <span class="text-2xl font-bold font-mono text-slate-900 mt-1 block">{{ $stats['total_today'] }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Kunjungan dinas/klien') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>

        <!-- Ongoing -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">{{ __('Sedang Berlangsung') }}</span>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-2xl font-bold font-mono text-amber-600">{{ $stats['ongoing_today'] }}</span>
                    @if($stats['ongoing_today'] > 0)
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold bg-amber-100 text-amber-700 animate-pulse">{{ __('Wajib Check-Out') }}</span>
                    @endif
                </div>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Kunjungan aktif') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Completed -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">{{ __('Selesai (Checked Out)') }}</span>
                <span class="text-2xl font-bold font-mono text-emerald-600 mt-1 block">{{ $stats['completed_today'] }}</span>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Sudah check-out lengkap') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Total Time -->
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-purple-700 uppercase tracking-wider block">{{ __('Total Waktu Kunjungan') }}</span>
                @php
                    $hrs = intdiv($stats['total_minutes_today'], 60);
                    $mins = $stats['total_minutes_today'] % 60;
                @endphp
                <div class="flex items-baseline gap-1 mt-1 font-mono">
                    <span class="text-2xl font-bold text-purple-700">{{ $hrs }}</span>
                    <span class="text-xs text-purple-500 font-sans font-medium">{{ __('jam') }}</span>
                    <span class="text-2xl font-bold text-purple-700 ml-1">{{ $mins }}</span>
                    <span class="text-xs text-purple-500 font-sans font-medium">{{ __('menit') }}</span>
                </div>
                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Akumulasi kunjungan selesai') }}</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Active Ongoing Visit Notice (If Active Visit Exists) -->
    @if($activeVisit)
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-200/90 text-amber-900 text-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-start sm:items-center gap-3">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-ping mt-1 sm:mt-0 shrink-0"></span>
                <div class="space-y-0.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-slate-900">{{ __('Kunjungan Sedang Berlangsung:') }}</span>
                        <strong class="text-blue-700 font-semibold">{{ $activeVisit->client_name }}</strong>
                        <span class="font-mono text-slate-600 bg-white/80 px-2 py-0.5 rounded border border-amber-200 text-[11px]">{{ $activeVisit->visit_number }}</span>
                        <span class="text-slate-500 font-mono text-[11px]">{{ __('Check-In:') }} {{ $activeVisit->check_in_at->format('H:i') }} ({{ $activeVisit->formatted_duration }})</span>
                    </div>
                    <p class="text-[11px] text-amber-800">
                        {{ __('Klik tombol Check-Out pada tabel riwayat di bawah untuk menyelesaikan kunjungan ini.') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('attendance.visits.checkout', $activeVisit) }}"
                class="text-xs text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1 shrink-0 underline decoration-blue-300">
                <span>{{ __('Formulir Check-Out') }}</span>
                <span class="rtl:rotate-180">&rarr;</span>
            </a>
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('attendance.visits.index') }}" class="flex flex-wrap items-center gap-3">
            <!-- Search Text -->
            <div class="relative flex-1 min-w-[220px] sm:min-w-[280px]">
                <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari klien, keperluan, no kunjungan, nama...') }}"
                    class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                @if(request('search'))
                    <a href="{{ route('attendance.visits.index', request()->except('search', 'page')) }}"
                       style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                       class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus pencarian') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()"
                class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">
                <option value="">{{ __('Semua Status') }}</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>{{ __('Sedang Berlangsung') }}</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Selesai (Checked Out)') }}</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Dibatalkan') }}</option>
            </select>

            <!-- Date Filter -->
            <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()"
                class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

            <!-- Employee Filter (For Admin/Manager) -->
            @if(!$isEmployee)
                <select name="employee_id" onchange="this.form.submit()"
                    class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs max-w-[200px]">
                    <option value="">{{ __('Semua Karyawan') }}</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <!-- Submit Button -->
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <span>{{ __('Filter') }}</span>
            </button>

            @if(request('search') || request('status') || request('date') || request('employee_id'))
                <a href="{{ route('attendance.visits.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ __('Reset') }}</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Visits History Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">{{ __('Riwayat Kunjungan / Visit') }}</h4>
                <p class="text-xs text-slate-400 mt-0.5">{{ __('Log lengkap presensi kunjungan dengan koordinat GPS check-in & check-out') }}</p>
            </div>
            <span class="text-xs font-mono font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                {{ $visits->total() }} {{ __('Kunjungan') }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs rtl:text-right">
                <thead class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200/70">
                    <tr>
                        <th class="px-5 py-3.5">{{ __('No. Visit & Waktu') }}</th>
                        <th class="px-5 py-3.5">{{ __('Karyawan') }}</th>
                        <th class="px-5 py-3.5">{{ __('Klien & Keperluan') }}</th>
                        <th class="px-5 py-3.5">{{ __('Check-In (GPS)') }}</th>
                        <th class="px-5 py-3.5">{{ __('Check-Out (GPS)') }}</th>
                        <th class="px-5 py-3.5 text-center">{{ __('Durasi') }}</th>
                        <th class="px-5 py-3.5 text-center">{{ __('Status') }}</th>
                        <th class="px-5 py-3.5 text-right rtl:text-left">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $v)
                        <tr class="hover:bg-slate-50/60 transition {{ $v->isOngoing() ? 'bg-amber-50/30' : '' }}">
                            <!-- Visit Number & Date -->
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-900">{{ $v->visit_number }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $v->check_in_at->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400">{{ $v->timezone ?: 'Asia/Jakarta' }}</div>
                            </td>

                            <!-- Employee Info -->
                            <td class="px-5 py-4 align-top">
                                <div class="font-semibold text-slate-900">{{ $v->employee->full_name }}</div>
                                <div class="text-[11px] font-mono text-slate-500">{{ $v->employee->employee_code }}</div>
                                <div class="text-[10px] text-slate-400">{{ $v->employee->department?->name ?? '-' }}</div>
                            </td>

                            <!-- Client & Purpose -->
                            <td class="px-5 py-4 align-top max-w-xs">
                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>{{ $v->client_name }}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 mt-1 line-clamp-2">{{ $v->purpose }}</p>
                                @if($v->outcome)
                                    <div class="mt-1.5 p-1.5 rounded-lg bg-slate-50 border border-slate-200/60 text-[10px] text-slate-600">
                                        <span class="font-semibold text-slate-700">{{ __('Hasil:') }}</span> {{ $v->outcome }}
                                    </div>
                                @endif
                            </td>

                            <!-- Check-In GPS -->
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                <div class="font-mono font-bold text-slate-900">{{ $v->check_in_at->format('H:i:s') }}</div>
                                <a href="{{ $v->check_in_map_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-blue-600 hover:text-blue-700 font-mono mt-0.5" title="{{ __('Buka di Google Maps') }}">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    <span>{{ number_format($v->check_in_lat, 4) }}, {{ number_format($v->check_in_lon, 4) }}</span>
                                </a>
                                @if($v->check_in_address)
                                    <div class="text-[10px] text-slate-400 truncate max-w-[140px]" title="{{ $v->check_in_address }}">{{ $v->check_in_address }}</div>
                                @endif
                            </td>

                            <!-- Check-Out GPS -->
                            <td class="px-5 py-4 align-top whitespace-nowrap">
                                @if($v->check_out_at)
                                    <div class="font-mono font-bold text-emerald-700">{{ $v->check_out_at->format('H:i:s') }}</div>
                                    @if($v->check_out_map_url)
                                        <a href="{{ $v->check_out_map_url }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-emerald-600 hover:text-emerald-700 font-mono mt-0.5" title="{{ __('Buka di Google Maps') }}">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            <span>{{ number_format($v->check_out_lat, 4) }}, {{ number_format($v->check_out_lon, 4) }}</span>
                                        </a>
                                    @endif
                                    @if($v->check_out_address)
                                        <div class="text-[10px] text-slate-400 truncate max-w-[140px]" title="{{ $v->check_out_address }}">{{ $v->check_out_address }}</div>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-600 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                                        {{ __('Belum Check-Out') }}
                                    </span>
                                @endif
                            </td>

                            <!-- Duration -->
                            <td class="px-5 py-4 align-top text-center whitespace-nowrap">
                                <span class="font-mono font-semibold text-xs {{ $v->isOngoing() ? 'text-amber-600' : 'text-slate-800' }}">
                                    {{ $v->formatted_duration }}
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-5 py-4 align-top text-center whitespace-nowrap">
                                @php $badge = $v->status_badge; @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $badge['class'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
                                    <span>{{ $badge['label'] }}</span>
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 align-top text-right rtl:text-left whitespace-nowrap">
                                @if($v->isOngoing())
                                    <a href="{{ route('attendance.visits.checkout', $v) }}"
                                        style="background-color: #059669; color: #ffffff;"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[11px] shadow-2xs transition inline-flex items-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        <span class="text-white">{{ __('Check-Out') }}</span>
                                    </a>
                                @else
                                    <a href="{{ $v->check_in_map_url }}" target="_blank"
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition inline-block" title="{{ __('Lihat Lokasi GPS') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">{{ __('Belum Ada Riwayat Kunjungan') }}</p>
                                <p class="text-xs text-slate-400 mt-1">{{ __('Klik tombol "Check-In Kunjungan Baru" untuk mencatat kunjungan dinas/klien Anda.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($visits->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $visits->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

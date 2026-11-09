@extends('layouts.app', ['headerTitle' => __('Manajemen Lembur (Overtime)'), 'title' => __('Pengajuan Lembur')])

@section('content')
<div class="space-y-8" x-data="{ activeTab: '{{ request('tab', 'requests') }}' }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Pengajuan & Persetujuan Lembur') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Pengelolaan Surat Perintah Kerja Lembur (SPKL) dan kalkulasi kompensasi payroll') }}</p>
        </div>

        <div class="flex items-center gap-3">
            @if(auth()->user()->isHrAdmin())
                <a href="{{ route('overtime.rates.create') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Tambah Rate Lembur') }}
                </a>
            @endif

            <a href="{{ route('overtime.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Ajukan Lembur Baru') }}
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Total Jam Lembur Disetujui') }}</span>
            <span class="text-3xl font-extrabold text-emerald-600 block mt-2">{{ __(':hours Jam', ['hours' => $totalHoursApproved]) }}</span>
            <span class="text-[11px] text-slate-500 block mt-1">{{ __('Terkonfirmasi untuk perhitungan payroll') }}</span>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Menunggu Persetujuan Atasan') }}</span>
            <span class="text-3xl font-extrabold text-amber-500 block mt-2">{{ __(':count Pengajuan', ['count' => $pendingCount]) }}</span>
            <span class="text-[11px] text-slate-500 block mt-1">{{ __('Perlu tindakan review') }}</span>
        </div>

        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Rate Lembur Standar') }}</span>
                    @php 
                        $otPolicy = \App\Models\SystemSetting::get('overtime_calculation_type', 'flat');
                        $activeDefaultRate = $overtimeRates->firstWhere(fn($r) => $r->is_default && $r->is_active) 
                            ?? $overtimeRates->firstWhere('is_active', true);
                    @endphp
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase
                        {{ $otPolicy === 'saudi_article_107' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($otPolicy === 'auto_country' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($otPolicy === 'depnaker' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200')) }}">
                        @if($otPolicy === 'saudi_article_107')
                            🇸🇦 {{ __('Pasal 107 Arab Saudi') }}
                        @elseif($otPolicy === 'auto_country')
                            🌐 {{ __('Otomatis Sesuai Cabang') }}
                        @elseif($otPolicy === 'depnaker')
                            🇮🇩 {{ __('Depnaker 1/173') }}
                        @elseif($activeDefaultRate)
                            {{ $activeDefaultRate->name }}
                        @else
                            {{ __('Nonaktif') }}
                        @endif
                    </span>
                </div>
                <span class="text-2xl font-extrabold text-slate-900 block mt-2">
                    @if($otPolicy === 'saudi_article_107')
                        {{ __('Upah/Jam + 50% Gaji Pokok') }}
                    @elseif($otPolicy === 'auto_country')
                        {{ __('Depnaker / Pasal 107') }}
                    @elseif($otPolicy === 'depnaker')
                        1/173 × {{ __('Gaji') }}
                    @elseif($activeDefaultRate)
                        {{ format_currency($activeDefaultRate->rate_per_hour, $activeDefaultRate->currency ?? 'IDR') }} <span class="text-xs font-normal text-slate-500">{{ __('/ Jam') }}</span>
                    @else
                        {{ __('Tidak Ada Rate Aktif') }}
                    @endif
                </span>
                <span class="text-[11px] text-slate-500 block mt-1">
                    @if($otPolicy === 'saudi_article_107')
                        {{ __('Pasal 107 UU Arab Saudi: (Pokok+Tunjangan÷30÷8) + 50% Upah Pokok') }}
                    @elseif($otPolicy === 'auto_country')
                        {{ __('Cabang RI: Depnaker PP 35/2021 · Cabang SA: Pasal 107 UU Arab Saudi') }}
                    @elseif($otPolicy === 'depnaker')
                        {{ __('Formula Resmi Depnaker RI (PP 35/2021): 1/173 × Gaji Pokok × Pengali') }}
                    @elseif($activeDefaultRate)
                        {{ __('Skema Aktif:') }} {{ $activeDefaultRate->name }} ({{ $activeDefaultRate->rate_label }})
                    @else
                        {{ __('Seluruh rate lembur dinonaktifkan. Aktifkan rate di tab Kebijakan & Rate.') }}
                    @endif
                </span>
            </div>

            @if(auth()->user()->isHrAdmin())
                <div class="pt-3 border-t border-slate-100 mt-2">
                    <button type="button" @click="activeTab = 'rates'" class="text-xs font-semibold text-blue-600 hover:text-blue-700 flex items-center gap-1 cursor-pointer">
                        {{ __('⚙ Atur Kebijakan & Tarif Lembur →') }}
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2">
        <button type="button" @click="activeTab = 'requests'" 
            :class="activeTab === 'requests' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            {{ __('Daftar Pengajuan SPKL') }} ({{ $requests->total() }})
        </button>

        @if(auth()->user()->isHrAdmin())
            <button type="button" @click="activeTab = 'rates'" 
                :class="activeTab === 'rates' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ __('Kebijakan & Master Rate Lembur') }} ({{ $overtimeRates->count() }})
            </button>
        @endif
    </div>

    <!-- TAB 1: PENGAJUAN SPKL -->
    <div x-show="activeTab === 'requests'" class="space-y-6">
        <!-- Requests Table -->
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Pengajuan Lembur Karyawan') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Daftar Surat Perintah Kerja Lembur (SPKL) yang diajukan untuk ditinjau dan disetujui.') }}</p>
                    </div>
                    <a href="{{ route('overtime.create') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Ajukan SPKL Baru') }}
                    </a>
                </div>

                <!-- Search & Filters Form -->
                <form method="GET" action="{{ route('overtime.index') }}" class="flex flex-wrap items-center gap-3 pt-1">
                    <input type="hidden" name="tab" value="requests">

                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[220px] sm:min-w-[280px]">
                        <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nomor SPKL, nama karyawan, alasan, divisi...') }}"
                            class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                        @if(request('search'))
                            <a href="{{ route('overtime.index', array_merge(request()->except('search', 'page'), ['tab' => 'requests'])) }}"
                               style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                               class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus kata kunci') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </a>
                        @endif
                    </div>

                    <!-- Status Filter -->
                    <select name="status" onchange="this.form.submit()"
                        class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Menunggu Review') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Disetujui') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Ditolak') }}</option>
                    </select>

                    <!-- Month Filter -->
                    <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()"
                        class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs"
                        title="{{ __('Filter Bulan Lembur') }}">

                    @if(!auth()->user()->isEmployee() && count($employees) > 1)
                        <!-- Employee Filter -->
                        <select name="employee_id" onchange="this.form.submit()"
                            class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs max-w-[200px]">
                            <option value="">{{ __('Semua Karyawan') }}</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <!-- Submit Button -->
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <span>{{ __('Cari') }}</span>
                    </button>

                    @if(request('search') || request('status') || request('month') || request('employee_id'))
                        <a href="{{ route('overtime.index', ['tab' => 'requests']) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>{{ __('Reset') }}</span>
                        </a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-3 font-semibold">{{ __('Nomor & Karyawan') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Tanggal Lembur') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Jam Mulai - Selesai') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Durasi Lembur') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Alasan & Tugas') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Status') }}</th>
                            <th class="py-3 px-3 font-semibold text-right">{{ __('Persetujuan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $ot)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3">
                                    <span class="font-bold font-mono text-blue-600 block">{{ $ot->request_number }}</span>
                                    <span class="font-semibold text-slate-900">{{ $ot->employee->full_name }}</span>
                                    <span class="text-[10px] text-slate-500 block">{{ $ot->employee->department->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-800">
                                    {{ $ot->date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-700">
                                    {{ substr($ot->start_time, 0, 5) }} - {{ substr($ot->end_time, 0, 5) }}
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-emerald-600">
                                    {{ $ot->actual_hours }} {{ __('Jam') }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs">
                                    <span class="block">{{ $ot->reason }}</span>
                                    @if($ot->overtimeRate)
                                        <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            <span>⚡</span>
                                            <span>{{ $ot->overtimeRate->name }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    @if ($ot->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ __('Disetujui') }}
                                        </span>
                                    @elseif ($ot->status === 'rejected')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ __('Ditolak') }}
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            {{ __('Menunggu Review') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-right">
                                    @if ($ot->status === 'pending')
                                        @if(auth()->user()->canApprove())
                                            <div class="flex items-center justify-end gap-2">
                                                <form action="{{ route('overtime.approve', $ot) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        {{ __('Setujui') }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('overtime.approve', $ot) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        {{ __('Tolak') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                {{ __('Menunggu Review Atasan') }}
                                            </span>
                                        @endif
                                    @else
                                        <div class="text-[11px] text-right">
                                            @if($ot->approver)
                                                <span class="text-slate-700 block leading-tight font-semibold">{{ __('Oleh :name', ['name' => $ot->approver->name]) }}</span>
                                                <span class="text-[10px] text-slate-400 block leading-tight mt-0.5 capitalize">({{ __(str_replace('_', ' ', $ot->approver->role)) }})</span>
                                            @else
                                                <span class="text-[10px] text-slate-500">{{ __('Selesai diproses') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 font-sans">
                                    @if(request('search') || request('status') || request('month') || request('employee_id'))
                                        <div class="space-y-2">
                                            <div class="text-sm font-semibold text-slate-700">{{ __('Pengajuan Lembur Tidak Ditemukan') }}</div>
                                            <p class="text-xs text-slate-500">{{ __('Tidak ada data pengajuan lembur yang cocok dengan kriteria pencarian atau filter yang dipilih.') }}</p>
                                            <div class="pt-2">
                                                <a href="{{ route('overtime.index', ['tab' => 'requests']) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    {{ __('Reset Pencarian') }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        {{ __('Belum ada data pengajuan lembur.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($requests->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $requests->links() }}
                </div>
            @elseif($requests->total() > 0)
                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium bg-slate-50/50">
                    <span>
                        {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $requests->total() }}</strong> {{ __('pengajuan lembur') }}
                        @if(request('search'))
                            <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('search') }}")</span>
                        @endif
                    </span>
                    <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: KEBIJAKAN & MASTER RATE LEMBUR -->
    @if(auth()->user()->isHrAdmin())
        <div x-show="activeTab === 'rates'" class="space-y-8">
            <!-- Kebijakan Standar Form -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-6"
                 x-data="{
                    calcType: '{{ App\Models\SystemSetting::get('overtime_calculation_type', 'flat') }}',
                    isRamadan: {{ App\Models\SystemSetting::get('overtime_saudi_is_ramadan', '0') === '1' ? 'true' : 'false' }},
                    simBasic: 6000,
                    simAllowance: 2000,
                    simHours: 1,
                    simRamadan: false,
                    get simDailyHours() { return this.simRamadan ? 6 : 8; },
                    get simDivisor() { return 30 * this.simDailyHours; },
                    get simHourlyWage() { return (parseFloat(this.simBasic || 0) + parseFloat(this.simAllowance || 0)) / this.simDivisor; },
                    get simBasicHourly() { return parseFloat(this.simBasic || 0) / this.simDivisor; },
                    get simPremium50() { return 0.5 * this.simBasicHourly; },
                    get simOvertimePerHour() { return this.simHourlyWage + this.simPremium50; },
                    get simTotalPay() { return (this.simOvertimePerHour * parseFloat(this.simHours || 1)).toFixed(2); }
                 }">
                <div>
                    <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ __('Kebijakan Perhitungan Upah Lembur Perusahaan') }}
                    </h4>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Konfigurasi formula standar yang berlaku untuk perhitungan penggajian bulanan di Indonesia maupun Arab Saudi.') }}</p>
                </div>

                <form action="{{ route('overtime.settings.update') }}" method="POST" class="space-y-5 text-xs">
                    @csrf

                    <div>
                        <label class="block font-semibold text-slate-700 mb-2">{{ __('Metode Perhitungan Upah Lembur Perusahaan *') }}</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <!-- 1. Flat Rate -->
                            <label class="p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3 bg-slate-50 hover:bg-blue-50/50 border-slate-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-blue-600">
                                <input type="radio" name="overtime_calculation_type" value="flat" x-model="calcType" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="font-bold text-slate-900 block">{{ __('Tarif Tetap (Flat Hourly Rate)') }}</span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ __('Dihitung dari tarif nominal Rp per jam yang ditentukan perusahaan secara tetap. Cocok untuk UMKM dan perusahaan swasta.') }}</span>
                                </div>
                            </label>

                            <!-- 2. Depnaker PP 35/2021 -->
                            <label class="p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3 bg-slate-50 hover:bg-blue-50/50 border-slate-200 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/40 has-[:checked]:ring-1 has-[:checked]:ring-blue-600">
                                <input type="radio" name="overtime_calculation_type" value="depnaker" x-model="calcType" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                                <div>
                                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>🇮🇩</span>
                                        <span>{{ __('Formula Depnaker Resmi RI (PP 35/2021)') }}</span>
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ __('Upah per jam = Gaji Pokok ÷ 173 (dengan pengali 1.5x jam pertama, 2x jam berikutnya/libur).') }}</span>
                                </div>
                            </label>

                            <!-- 3. Saudi Labor Law Article 107 -->
                            <label class="p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3 bg-slate-50 hover:bg-emerald-50/50 border-slate-200 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/40 has-[:checked]:ring-1 has-[:checked]:ring-emerald-600">
                                <input type="radio" name="overtime_calculation_type" value="saudi_article_107" x-model="calcType" class="mt-0.5 text-emerald-600 focus:ring-emerald-500">
                                <div>
                                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>🇸🇦</span>
                                        <span>{{ __('Pasal 107 UU Ketenagakerjaan Arab Saudi') }}</span>
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ __('Upah per jam (Gaji Pokok + Tunjangan Tetap ÷ 30 ÷ 8) + Premi 50% Upah Pokok per jam (tanpa tunjangan).') }}</span>
                                </div>
                            </label>

                            <!-- 4. Dynamic Auto by Branch Country -->
                            <label class="p-4 rounded-2xl border cursor-pointer transition flex items-start gap-3 bg-slate-50 hover:bg-purple-50/50 border-slate-200 has-[:checked]:border-purple-600 has-[:checked]:bg-purple-50/40 has-[:checked]:ring-1 has-[:checked]:ring-purple-600">
                                <input type="radio" name="overtime_calculation_type" value="auto_country" x-model="calcType" class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                <div>
                                    <span class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>🌐</span>
                                        <span>{{ __('Otomatis Sesuai Negara Cabang Penempatan') }}</span>
                                    </span>
                                    <span class="text-[11px] text-slate-500 block mt-0.5">{{ __('Cabang Indonesia otomatis memakai Formula Depnaker RI, Cabang Arab Saudi otomatis memakai Pasal 107 UU Arab Saudi.') }}</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Parameters: Standard & Indonesia Depnaker (shown when flat/depnaker/auto_country) -->
                    <div x-show="calcType === 'flat' || calcType === 'depnaker' || calcType === 'auto_country'" class="p-5 rounded-2xl bg-slate-50/80 border border-slate-200 space-y-4">
                        <h5 class="font-bold text-slate-800 text-xs flex items-center gap-1.5">
                            <span>🇮🇩</span>
                            <span>{{ __('Parameter Lembur Standar & Indonesia (Depnaker RI)') }}</span>
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">{{ __('Tarif Hari Kerja (Rp/Jam)') }}</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 rtl:right-3.5 rtl:left-auto text-slate-400 font-semibold text-xs">Rp</span>
                                    <input type="number" name="overtime_rate_per_hour" value="{{ App\Models\SystemSetting::get('overtime_rate_per_hour', 0) }}" min="0" step="any"
                                        class="w-full pl-10 pr-3.5 rtl:pr-10 rtl:pl-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">{{ __('Tarif Akhir Pekan/Libur (Rp/Jam)') }}</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-2.5 rtl:right-3.5 rtl:left-auto text-slate-400 font-semibold text-xs">Rp</span>
                                    <input type="number" name="overtime_rate_weekend_per_hour" value="{{ App\Models\SystemSetting::get('overtime_rate_weekend_per_hour', 0) }}" min="0" step="any"
                                        class="w-full pl-10 pr-3.5 rtl:pr-10 rtl:pl-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">{{ __('Pembagi Depnaker (Jam/Bulan)') }}</label>
                                <input type="number" name="overtime_depnaker_divisor" value="{{ App\Models\SystemSetting::get('overtime_depnaker_divisor', 173) }}" min="1" max="250"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Parameters: Saudi Labor Law Article 107 (shown when saudi_article_107 or auto_country) -->
                    <div x-show="calcType === 'saudi_article_107' || calcType === 'auto_country'" class="p-5 rounded-2xl bg-emerald-50/70 border border-emerald-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <h5 class="font-bold text-emerald-950 text-xs flex items-center gap-1.5">
                                <span>🇸🇦</span>
                                <span>{{ __('Parameter Resmi Arab Saudi (Pasal 107 UU Ketenagakerjaan)') }}</span>
                            </h5>
                            <span class="text-[10px] font-mono font-semibold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-md">Royal Decree No. M/51</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">{{ __('Jam Kerja Normal Harian (Jam)') }}</label>
                                <input type="number" name="overtime_saudi_daily_hours" value="{{ App\Models\SystemSetting::get('overtime_saudi_daily_hours', 8) }}" min="4" max="12"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Standar KSA: 8 jam/hari (48 jam/minggu).') }}</span>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">{{ __('Pembagi Hari Kalender Sebulan') }}</label>
                                <input type="number" name="overtime_saudi_month_days" value="{{ App\Models\SystemSetting::get('overtime_saudi_month_days', 30) }}" min="20" max="31"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Standar Pasal 107: 30 hari.') }}</span>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-700 mb-1">{{ __('Status Khusus Bulan Ramadan') }}</label>
                                <label class="flex items-center gap-2 p-2.5 rounded-xl bg-white border border-slate-200 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="checkbox" name="overtime_saudi_is_ramadan" value="1" x-model="isRamadan" class="rounded text-emerald-600 focus:ring-emerald-500">
                                    <span class="text-[11px] font-semibold text-slate-800">🌙 {{ __('Mode Ramadan Aktif (6 Jam/Hari)') }}</span>
                                </label>
                                <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Pasal 98: Batas normal 6 jam/hari bagi pekerja Muslim.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/80 text-blue-900 text-xs space-y-1">
                        <div class="font-bold flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('Koneksi Otomatis ke Payroll & Slip Gaji:') }}
                        </div>
                        <p class="text-[11px] text-blue-800">
                            {{ __('Setiap perubahan tarif atau formula lembur di sini akan secara otomatis dijadikan parameter kalkulasi penggajian bulanan saat menekan tombol :button di menu Payroll, serta tercetak transparan pada rincian slip gaji masing-masing karyawan.', ['button' => __('Kalkulasi Otomatis Gaji')]) }}
                        </p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm shadow-blue-500/20 transition cursor-pointer">
                            {{ __('Simpan Kebijakan Rate Lembur') }}
                        </button>
                    </div>
                </form>

                <!-- Interactive Simulator Card for Saudi Overtime Article 107 -->
                <div class="p-6 rounded-3xl bg-linear-to-br from-emerald-50/70 via-white to-blue-50/40 border border-emerald-200 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-emerald-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">🧮</span>
                            <div>
                                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                    <span>{{ __('Kalkulator Simulasi Resmi Pasal 107 UU Ketenagakerjaan Arab Saudi') }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">🇸🇦 KSA Official</span>
                                </h4>
                                <p class="text-[11px] text-slate-500">{{ __('Uji coba langsung rumus resmi: Upah Lembur per Jam = Upah per Jam (Gaji Pokok + Tunjangan Tetap ÷ 30 ÷ 8) + Premi 50% Upah Pokok.') }}</p>
                            </div>
                        </div>
                        <span class="text-xs font-mono font-bold px-3 py-1 rounded-xl bg-white border border-emerald-200 text-emerald-700 shadow-2xs">
                            SAR / ريال
                        </span>
                    </div>

                    <!-- Simulator Inputs -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Gaji Pokok (SAR) *') }}</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 rtl:right-3 rtl:left-auto text-slate-400 font-bold text-xs">SAR</span>
                                <input type="number" x-model.number="simBasic" min="0" step="any"
                                    class="w-full pl-12 pr-3 rtl:pr-12 rtl:pl-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-2xs">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Contoh standar: 6.000') }}</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tunjangan Tetap (SAR)') }}</label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 rtl:right-3 rtl:left-auto text-slate-400 font-bold text-xs">SAR</span>
                                <input type="number" x-model.number="simAllowance" min="0" step="any"
                                    class="w-full pl-12 pr-3 rtl:pr-12 rtl:pl-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-2xs">
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Perumahan & transportasi (contoh: 2.000)') }}</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jumlah Jam Lembur *') }}</label>
                            <input type="number" x-model.number="simHours" min="0.5" step="0.5"
                                class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-slate-900 font-mono font-bold focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 shadow-2xs">
                            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Jam kerja di luar jam normal') }}</span>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kondisi Khusus') }}</label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-white border border-slate-200 cursor-pointer hover:bg-slate-50 shadow-2xs">
                                <input type="checkbox" x-model="simRamadan" class="rounded text-emerald-600 focus:ring-emerald-500">
                                <span class="text-[11px] font-semibold text-slate-700">🌙 {{ __('Bulan Ramadan (6 Jam/Hari)') }}</span>
                            </label>
                            <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Pasal 98: Batas normal 6 jam/hari') }}</span>
                        </div>
                    </div>

                    <!-- Live Calculation Results Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2">
                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{ __('1. Upah per Jam') }}</span>
                            <span class="text-base font-extrabold font-mono text-slate-900 mt-1 block">
                                SAR <span x-text="simHourlyWage.toFixed(2)"></span>
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">
                                (<span x-text="(parseFloat(simBasic||0)+parseFloat(simAllowance||0)).toLocaleString()"></span> ÷ 30 ÷ <span x-text="simDailyHours"></span>)
                            </span>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">{{ __('2. Upah Pokok per Jam') }}</span>
                            <span class="text-base font-extrabold font-mono text-slate-900 mt-1 block">
                                SAR <span x-text="simBasicHourly.toFixed(2)"></span>
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">
                                (<span x-text="parseFloat(simBasic||0).toLocaleString()"></span> ÷ 30 ÷ <span x-text="simDailyHours"></span>)
                            </span>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-2xs">
                            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-wider block">{{ __('3. Premi 50%') }}</span>
                            <span class="text-base font-extrabold font-mono text-amber-600 mt-1 block">
                                +SAR <span x-text="simPremium50.toFixed(2)"></span>
                            </span>
                            <span class="text-[10px] text-slate-400 block mt-0.5">
                                (50% × SAR <span x-text="simBasicHourly.toFixed(2)"></span>)
                            </span>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-emerald-600 text-white shadow-sm shadow-emerald-500/20">
                            <span class="text-[10px] font-bold text-emerald-100 uppercase tracking-wider block">{{ __('Upah Lembur / Jam') }}</span>
                            <span class="text-lg font-extrabold font-mono text-white mt-1 block">
                                SAR <span x-text="simOvertimePerHour.toFixed(2)"></span>
                            </span>
                            <span class="text-[10px] text-emerald-100 block mt-0.5">
                                {{ __('Total') }} <span x-text="simHours"></span> {{ __('Jam') }}: <strong>SAR <span x-text="simTotalPay"></span></strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Master Table -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Kategori & Aturan Rate Lembur') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Sistem akan mencocokkan tanggal lembur karyawan dengan aturan aktif di bawah ini.') }}</p>
                    </div>
                    <a href="{{ route('overtime.rates.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Tambah Kategori Rate Lembur Baru') }}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 pb-2">
                                <th class="py-3 px-3 font-semibold">{{ __('Nama & Kode') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Hari Berlaku') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Tipe Kalkulasi') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Nominal / Multiplier') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Status') }}</th>
                                <th class="py-3 px-3 font-semibold text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($overtimeRates as $r)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900">{{ __($r->name) }}</span>
                                            @if($r->is_default && $r->is_active)
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200">{{ __('Default') }}</span>
                                            @endif
                                        </div>
                                        <span class="font-mono text-[10px] text-blue-600 block mt-0.5">{{ $r->code }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium 
                                            {{ $r->day_type === 'weekday' ? 'bg-blue-50 text-blue-700 border border-blue-200' : ($r->day_type === 'weekend' ? 'bg-purple-50 text-purple-700 border border-purple-200' : ($r->day_type === 'holiday' ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200')) }}">
                                            @if($r->day_type === 'all')
                                                {{ __('Semua Hari') }}
                                            @else
                                                {{ __(ucfirst($r->day_type)) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 font-medium text-slate-700">
                                        @if($r->calculation_type === 'saudi_article_107')
                                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                                <span>🇸🇦</span>
                                                <span>{{ __('Pasal 107 UU Arab Saudi') }}</span>
                                            </span>
                                        @elseif($r->calculation_type === 'depnaker_formula')
                                            {{ __('Formula Depnaker') }}
                                        @elseif($r->calculation_type === 'percentage')
                                            {{ __('Persentase Gaji') }}
                                        @else
                                            {{ __('Tarif Tetap (Flat)') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                        @if($r->calculation_type === 'saudi_article_107')
                                            <span class="text-xs text-emerald-700 font-sans font-semibold">{{ __('Upah/Jam + 50% Gaji Pokok') }}</span>
                                        @elseif($r->calculation_type === 'flat')
                                            {{ format_currency($r->rate_per_hour, $r->currency ?? 'IDR') }} {{ __('/ Jam') }}
                                        @else
                                            {{ $r->multiplier }}x {{ __('Upah') }}
                                        @endif
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $r->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $r->is_active ? __('Aktif') : __('Nonaktif') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right space-x-2">
                                        @if($r->is_active && !$r->is_default)
                                            <form action="{{ route('overtime.rates.set-default', $r) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Jadikan :name sebagai master rate lembur default perusahaan?', ['name' => $r->name]) }}')">
                                                @csrf
                                                <button type="submit" class="text-amber-600 hover:text-amber-800 font-semibold text-[11px] cursor-pointer" title="{{ __('Jadikan Rate Default Perusahaan') }}">
                                                    ★ {{ __('Jadikan Default') }}
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('overtime.rates.edit', $r) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px] inline-flex items-center gap-1">
                                            {{ __('Edit') }}
                                        </a>

                                        @if(!$r->is_default)
                                            <form action="{{ route('overtime.rates.destroy', $r) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Hapus kategori rate lembur ini?') }}')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-[11px] cursor-pointer">{{ __('Hapus') }}</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-6 text-slate-400">{{ __('Belum ada aturan rate lembur.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

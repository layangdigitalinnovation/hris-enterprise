@extends('layouts.app', ['headerTitle' => __('Manajemen Cuti & Izin Kerja'), 'title' => __('Cuti & Persetujuan')])

@section('content')
@php
    $currentTab = request('tab');
    if (!$currentTab) {
        if (request()->has('bal_page') || request()->has('bal_search')) {
            $currentTab = 'balances';
        } elseif (request()->has('type_page')) {
            $currentTab = 'types';
        } else {
            $currentTab = 'requests';
        }
    }
@endphp
<div class="space-y-8" x-data="{ 
    activeTab: '{{ $currentTab }}'
}">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Cuti, Izin & Workflow Persetujuan') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Pengajuan cuti tahunan, sakit, izin khusus, dan integrasi saldo kehadiran') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            @if(auth()->user()->isHrAdmin())
                <a href="{{ route('leaves.balances.create') }}" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                    {{ __('Tetapkan Saldo') }}
                </a>
                <a href="{{ route('leaves.types.create') }}" class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Tambah Jenis Cuti') }}
                </a>
            @endif

            <a href="{{ route('leaves.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Ajukan Cuti Baru') }}
            </a>
        </div>
    </div>

    <!-- Leave Types Overview Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($leaveTypes as $lt)
            <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex flex-col justify-between group hover:border-blue-200 hover:shadow-md transition">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md inline-block">{{ $lt->code }}</span>
                        @if(auth()->user()->isHrAdmin())
                            <a href="{{ route('leaves.types.edit', $lt) }}" class="text-slate-400 hover:text-blue-600 transition p-1 rounded-md hover:bg-slate-50" title="{{ __('Edit Hak Kuota Cuti') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </a>
                        @endif
                    </div>
                    <h4 class="font-bold text-slate-900 text-sm mt-2">{{ __($lt->name) }}</h4>
                    <p class="text-xs text-slate-500 mt-1 font-medium">{{ __('Hak Kuota:') }} <span class="font-bold text-blue-600 font-mono">{{ $lt->default_quota }}</span> {{ __('Hari / Tahun') }}</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full {{ $lt->is_paid ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        {{ $lt->is_paid ? __('Cuti Berbayar') : __('Tidak Berbayar') }}
                    </span>
                    <span>{{ __('Maks') }} {{ $lt->max_consecutive_days }} {{ __('Hari') }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Navigation Tabs -->
    <div class="flex border-b border-slate-200 gap-2">
        <button type="button" @click="activeTab = 'requests'" 
            :class="activeTab === 'requests' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
            class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            {{ __('Daftar Pengajuan Cuti') }} ({{ $requests->total() }})
        </button>

        @if(auth()->user()->isHrAdmin())
            <button type="button" @click="activeTab = 'types'" 
                :class="activeTab === 'types' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                {{ __('Master Jenis Cuti & Kuota') }} ({{ $allLeaveTypes->count() }})
            </button>

            <button type="button" @click="activeTab = 'balances'" 
                :class="activeTab === 'balances' ? 'border-b-2 border-blue-600 text-blue-600 font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                class="pb-3 px-3 text-xs flex items-center gap-2 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                {{ __('Saldo & Alokasi Kuota Karyawan') }} ({{ $allBalances->total() }})
            </button>
        @endif
    </div>

    <!-- TAB 1: PENGAJUAN CUTI & IZIN -->
    <div x-show="activeTab === 'requests'" class="space-y-6">
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 space-y-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Pengajuan Cuti & Izin Karyawan') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Daftar permohonan cuti dan perizinan kerja yang diajukan oleh karyawan.') }}</p>
                    </div>
                    <a href="{{ route('leaves.create') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5 shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Ajukan Cuti Baru') }}
                    </a>
                </div>

                <!-- Search & Filters -->
                <form method="GET" action="{{ route('leaves.index') }}" class="flex flex-wrap items-center gap-3 pt-1">
                    <input type="hidden" name="tab" value="requests">
                    @if(request('bal_search'))
                        <input type="hidden" name="bal_search" value="{{ request('bal_search') }}">
                    @endif
                    @if(request('bal_page'))
                        <input type="hidden" name="bal_page" value="{{ request('bal_page') }}">
                    @endif

                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[220px] sm:min-w-[280px]">
                        <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>

                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nomor cuti, nama karyawan, alasan, divisi...') }}"
                            class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                        @if(request('search'))
                            <a href="{{ route('leaves.index', array_merge(request()->except('search', 'req_page'), ['tab' => 'requests'])) }}"
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
                        <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>{{ __('Menunggu Review') }}</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Disetujui') }}</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Ditolak') }}</option>
                    </select>

                    <!-- Leave Type Filter -->
                    <select name="leave_type_id" onchange="this.form.submit()"
                        class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">
                        <option value="">{{ __('Semua Jenis Cuti') }}</option>
                        @foreach($leaveTypes as $lt)
                            <option value="{{ $lt->id }}" {{ request('leave_type_id') == $lt->id ? 'selected' : '' }}>{{ __($lt->name) }} ({{ $lt->code }})</option>
                        @endforeach
                    </select>

                    <!-- Submit Button -->
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        <span>{{ __('Cari') }}</span>
                    </button>

                    @if(request('search') || request('status') || request('leave_type_id'))
                        <a href="{{ route('leaves.index', array_merge(request()->except('search', 'status', 'leave_type_id', 'req_page'), ['tab' => 'requests'])) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs">
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
                            <th class="py-3 px-3 font-semibold">{{ __('Jenis Cuti') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Rentang Tanggal') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Durasi') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Alasan Permohonan') }}</th>
                            <th class="py-3 px-3 font-semibold">{{ __('Status') }}</th>
                            <th class="py-3 px-3 font-semibold text-right">{{ __('Persetujuan') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $leave)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3">
                                    <span class="font-bold font-mono text-blue-600 block">{{ $leave->request_number }}</span>
                                    <span class="font-semibold text-slate-900">{{ $leave->employee->full_name }}</span>
                                    <span class="text-[10px] text-slate-500 block">{{ $leave->employee->department->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-800">
                                    {{ __($leave->leaveType->name) }}
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-700">
                                    {{ $leave->start_date->format('d/m/Y') }} - {{ $leave->end_date->format('d/m/Y') }}
                                </td>
                                <td class="py-3 px-3 font-mono font-bold text-slate-900">
                                    {{ $leave->total_days }} {{ __('Hari Kerja') }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs">
                                    {{ $leave->reason }}
                                </td>
                                <td class="py-3 px-3">
                                    @if ($leave->status === 'approved')
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ __('Disetujui') }}
                                        </span>
                                    @elseif ($leave->status === 'rejected')
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
                                    @if ($leave->status === 'submitted')
                                        @if(auth()->user()->canApprove())
                                            <div class="flex items-center justify-end gap-2">
                                                <form action="{{ route('leaves.approve', $leave) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs cursor-pointer">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        {{ __('Setujui') }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('leaves.approve', $leave) }}" method="POST">
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
                                            @if($leave->approver)
                                                <span class="text-slate-700 block leading-tight font-semibold">{{ __('Oleh :name', ['name' => $leave->approver->name]) }}</span>
                                                <span class="text-[10px] text-slate-400 block leading-tight mt-0.5 capitalize">({{ __(str_replace('_', ' ', $leave->approver->role)) }})</span>
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
                                    @if(request('search') || request('status') || request('leave_type_id'))
                                        <div class="space-y-2">
                                            <div class="text-sm font-semibold text-slate-700">{{ __('Pengajuan Cuti Tidak Ditemukan') }}</div>
                                            <p class="text-xs text-slate-500">{{ __('Tidak ada data pengajuan cuti yang cocok dengan kriteria pencarian atau filter yang dipilih.') }}</p>
                                            <div class="pt-2">
                                                <a href="{{ route('leaves.index', array_merge(request()->except('search', 'status', 'leave_type_id', 'req_page'), ['tab' => 'requests'])) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    {{ __('Reset Pencarian') }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        {{ __('Belum ada data pengajuan cuti.') }}
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
                        {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $requests->total() }}</strong> {{ __('pengajuan cuti') }}
                        @if(request('search'))
                            <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('search') }}")</span>
                        @endif
                    </span>
                    <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- TAB 2: MASTER JENIS CUTI & HAK KUOTA -->
    @if(auth()->user()->isHrAdmin())
        <div x-show="activeTab === 'types'" class="space-y-6">
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Master Jenis Cuti & Kebijakan Kuota Standar') }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Nilai hak kuota standar akan otomatis dijadikan dasar pemotongan dan alokasi saldo cuti karyawan.') }}</p>
                    </div>
                    <a href="{{ route('leaves.types.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Tambah Jenis Cuti Baru') }}
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200">
                                <th class="py-3 px-3 font-semibold">{{ __('Kode & Nama') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Hak Kuota') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Max Berturut') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Pembayaran') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Dokumen') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Gender') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Status') }}</th>
                                <th class="py-3 px-3 font-semibold text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($allLeaveTypes as $lt)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            <span class="font-mono text-[10px] font-bold text-blue-600 bg-blue-50 border border-blue-200 px-1.5 py-0.5 rounded">{{ $lt->code }}</span>
                                            <span class="font-bold text-slate-900">{{ __($lt->name) }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-mono font-bold text-slate-900">{{ $lt->default_quota }} {{ __('Hari') }}</span>
                                        <span class="text-[10px] text-slate-400 block">{{ __('/ Tahun') }}</span>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-slate-700">
                                        {{ $lt->max_consecutive_days }} {{ __('Hari') }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium {{ $lt->is_paid ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                            {{ $lt->is_paid ? __('Paid') : __('Unpaid') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-slate-600">
                                        {{ $lt->requires_document ? __('Wajib') : __('Opsional') }}
                                    </td>
                                    <td class="py-3 px-3 text-slate-600 capitalize">
                                        {{ $lt->gender_restriction === 'all' ? __('Semua') : ($lt->gender_restriction === 'female' ? __('Wanita') : __('Pria')) }}
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $lt->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $lt->is_active ? __('Aktif') : __('Nonaktif') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right space-x-2">
                                        <a href="{{ route('leaves.types.edit', $lt) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px] inline-flex items-center gap-1">
                                            {{ __('Edit') }}
                                        </a>

                                        <form action="{{ route('leaves.types.destroy', $lt) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Hapus master jenis cuti ini?') }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-[11px] cursor-pointer">
                                                {{ __('Hapus') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-6 text-slate-400">{{ __('Belum ada master jenis cuti.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: SALDO & ALOKASI KUOTA KARYAWAN -->
        <div x-show="activeTab === 'balances'" class="space-y-6">
            <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 space-y-4">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h4 class="font-bold text-slate-900 text-base">{{ __('Alokasi & Rekap Saldo Cuti Karyawan Tahun :year', ['year' => date('Y')]) }}</h4>
                            <p class="text-xs text-slate-500 mt-1">{{ __('Saldo kuota awal, pemakaian, dan sisa hari cuti yang tersisa untuk masing-masing karyawan.') }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <form action="{{ route('leaves.balances.sync') }}" method="POST" onsubmit="return confirm('{{ __('Jalankan alokasi otomatis kuota standar untuk seluruh karyawan aktif tahun :year?', ['year' => date('Y')]) }}');">
                                @csrf
                                <input type="hidden" name="year" value="{{ date('Y') }}">
                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    {{ __('Sinkronisasi Kuota Massal (:year)', ['year' => date('Y')]) }}
                                </button>
                            </form>

                            <a href="{{ route('leaves.balances.create') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-1.5 shrink-0">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                {{ __('Tetapkan Saldo Baru') }}
                            </a>
                        </div>
                    </div>

                    <!-- Search Input Filter for Balances -->
                    <form method="GET" action="{{ route('leaves.index') }}" class="flex flex-wrap items-center gap-3 pt-1">
                        <input type="hidden" name="tab" value="balances">
                        @if(request('search'))
                            <input type="hidden" name="search" value="{{ request('search') }}">
                        @endif
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        @if(request('leave_type_id'))
                            <input type="hidden" name="leave_type_id" value="{{ request('leave_type_id') }}">
                        @endif
                        @if(request('req_page'))
                            <input type="hidden" name="req_page" value="{{ request('req_page') }}">
                        @endif

                        <div class="relative flex-1 min-w-[220px] sm:min-w-[320px]">
                            <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>

                            <input type="text" name="bal_search" value="{{ request('bal_search') }}" placeholder="{{ __('Cari nama karyawan, kode, departemen, jenis cuti...') }}"
                                class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                            @if(request('bal_search'))
                                <a href="{{ route('leaves.index', array_merge(request()->except('bal_search', 'bal_page'), ['tab' => 'balances'])) }}"
                                   style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                                   class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus pencarian') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            <span>{{ __('Cari') }}</span>
                        </button>

                        @if(request('bal_search'))
                            <a href="{{ route('leaves.index', array_merge(request()->except('bal_search', 'bal_page'), ['tab' => 'balances'])) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs">
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
                                <th class="py-3 px-3 font-semibold">{{ __('Karyawan') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Jenis Cuti') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Tahun') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Kuota Awal') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Terpakai') }}</th>
                                <th class="py-3 px-3 font-semibold">{{ __('Sisa Saldo') }}</th>
                                <th class="py-3 px-3 font-semibold text-right">{{ __('Aksi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($allBalances as $bal)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3 px-3">
                                        <span class="font-bold text-slate-900 block">{{ $bal->employee->full_name }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $bal->employee->employee_code }} · {{ $bal->employee->department->name ?? '-' }}</span>
                                    </td>
                                    <td class="py-3 px-3">
                                        <span class="font-medium text-slate-800">{{ __($bal->leaveType->name) }}</span>
                                        <span class="text-[10px] text-blue-600 font-mono block">{{ $bal->leaveType->code }}</span>
                                    </td>
                                    <td class="py-3 px-3 font-mono text-slate-600">
                                        {{ $bal->year }}
                                    </td>
                                    <td class="py-3 px-3 font-mono font-bold text-slate-800">
                                        {{ $bal->initial_quota }} {{ __('Hari') }}
                                    </td>
                                    <td class="py-3 px-3 font-mono text-amber-600 font-bold">
                                        {{ $bal->used_quota }} {{ __('Hari') }}
                                    </td>
                                    <td class="py-3 px-3 font-mono font-bold text-emerald-600">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ $bal->remaining_quota > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $bal->remaining_quota }} {{ __('Hari') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3 text-right space-x-2">
                                        <a href="{{ route('leaves.balances.edit', $bal) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-[11px] inline-flex items-center gap-1">
                                            {{ __('Sesuaikan') }}
                                        </a>

                                        <form action="{{ route('leaves.balances.destroy', $bal) }}" method="POST" onsubmit="return confirm('{{ __('Hapus catatan saldo cuti ini?') }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-semibold text-[11px] cursor-pointer">
                                                {{ __('Hapus') }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-slate-400 font-sans">
                                        @if(request('bal_search'))
                                            <div class="space-y-2">
                                                <div class="text-sm font-semibold text-slate-700">{{ __('Catatan Saldo Tidak Ditemukan') }}</div>
                                                <p class="text-xs text-slate-500">{{ __('Tidak ada alokasi saldo cuti karyawan yang cocok dengan kata kunci') }} "{{ request('bal_search') }}".</p>
                                                <div class="pt-2">
                                                    <a href="{{ route('leaves.index', array_merge(request()->except('bal_search', 'bal_page'), ['tab' => 'balances'])) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                        {{ __('Reset Pencarian') }}
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            {{ __('Belum ada catatan saldo cuti karyawan. Gunakan tombol Sinkronisasi Kuota Massal di atas.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination for Balances -->
                @if ($allBalances->hasPages())
                    <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                        {{ $allBalances->links() }}
                    </div>
                @elseif ($allBalances->total() > 0)
                    <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium bg-slate-50/50">
                        <span>
                            {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $allBalances->total() }}</strong> {{ __('saldo cuti karyawan') }}
                            @if(request('bal_search'))
                                <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('bal_search') }}")</span>
                            @endif
                        </span>
                        <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection

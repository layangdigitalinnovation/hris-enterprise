@extends('layouts.app', ['headerTitle' => __('Manajemen Resign & Offboarding'), 'title' => __('Resign Karyawan')])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 border border-blue-100 shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Resign & Offboarding Karyawan') }}</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Kelola permohonan pengunduran diri, masa notice, checklist clearance aset & finansial, serta surat pengalaman kerja') }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('resignations.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-sm transition active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>{{ __('Ajukan Resign Baru') }}</span>
            </a>
        </div>
    </div>

    {{-- 2. KPI Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Resignations --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-medium">{{ __('Total Pengajuan') }}</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-slate-800">{{ $totalCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Total riwayat resign tercatat') }}</div>
        </div>

        {{-- Pending Review --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-medium">{{ __('Menunggu Review') }}</span>
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-amber-600">{{ $pendingCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Menunggu persetujuan manajerial') }}</div>
        </div>

        {{-- Approved Notice Period --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-medium">{{ __('Masa Notice Berjalan') }}</span>
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-blue-600">{{ $noticeCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Disetujui & proses handover') }}</div>
        </div>

        {{-- Completed Offboarding --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between text-slate-500 mb-2">
                <span class="text-xs font-medium">{{ __('Selesai Offboarding') }}</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-bold text-emerald-600">{{ $completedCount }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Clearance tuntas & nonaktif') }}</div>
        </div>
    </div>

    {{-- 3. Filter & Search Card --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-slate-100 text-xs">
            @php $currentStatus = request('status', 'all'); @endphp
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $currentStatus === 'all' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Semua Status') }} ({{ $totalCount }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $currentStatus === 'pending' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Menunggu Approval') }} ({{ $pendingCount }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $currentStatus === 'approved' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Masa Notice') }} ({{ $noticeCount }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'completed']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $currentStatus === 'completed' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Selesai') }} ({{ $completedCount }})
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'rejected']) }}"
               class="px-3.5 py-1.5 rounded-lg font-medium transition {{ $currentStatus === 'rejected' ? 'bg-blue-600 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}">
                {{ __('Ditolak') }}
            </a>
        </div>

        {{-- Filter Inputs Form --}}
        <form method="GET" action="{{ route('resignations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Search Keyword --}}
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ __('Cari No. Resign / Karyawan...') }}"
                       class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            @if(auth()->user()->canManageResignations())
                {{-- Cabang Filter --}}
                <div>
                    <select name="branch_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">{{ __('Semua Cabang') }}</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Departemen Filter --}}
                <div>
                    <select name="department_id" onchange="this.form.submit()"
                            class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-xs focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">{{ __('Semua Departemen') }}</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Submit / Reset --}}
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition">
                    {{ __('Filter') }}
                </button>
                @if(request()->hasAny(['search', 'branch_id', 'department_id', 'status']))
                    <a href="{{ route('resignations.index') }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition">
                        {{ __('Reset') }}
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- 4. Main Data Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4">{{ __('No. Resign') }}</th>
                        <th class="py-3 px-4">{{ __('Karyawan') }}</th>
                        <th class="py-3 px-4">{{ __('Tgl Pengajuan') }}</th>
                        <th class="py-3 px-4">{{ __('Hari Terakhir Kerja') }}</th>
                        <th class="py-3 px-4">{{ __('Alasan Resign') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('Status') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('Progress Clearance') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($resignations as $item)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            {{-- Resignation Code --}}
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-600 whitespace-nowrap">
                                <a href="{{ route('resignations.show', $item) }}" class="hover:underline">
                                    {{ $item->resignation_number }}
                                </a>
                            </td>

                            {{-- Employee Details --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900">{{ $item->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono">{{ $item->employee?->employee_code }}</span>
                                    <span>•</span>
                                    <span>{{ __($item->employee?->department?->name ?? '-') }}</span>
                                </div>
                            </td>

                            {{-- Submission Date --}}
                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                {{ $item->submission_date ? $item->submission_date->format('d/m/Y') : '-' }}
                            </td>

                            {{-- Last Working Day & Notice Days --}}
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-medium text-slate-900">
                                    {{ $item->effective_last_day ? $item->effective_last_day->format('d/m/Y') : '-' }}
                                </div>
                                <div class="text-[11px] {{ $item->is_standard_notice ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ $item->notice_days_count }} {{ __('hari notice') }}
                                    @if($item->approved_last_day)
                                        <span class="text-blue-600 font-semibold">({{ __('Disetujui') }})</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Reason --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[11px] font-medium">
                                    {{ $item->reason_label }}
                                </span>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border {{ $item->status_badge_classes }}">
                                    {{ $item->status_label }}
                                </span>
                            </td>

                            {{-- Clearance Progress --}}
                            <td class="py-3.5 px-4 text-center">
                                <div class="w-28 mx-auto space-y-1">
                                    <div class="flex items-center justify-between text-[10px] text-slate-500 font-medium">
                                        <span>{{ $item->clearance_percentage }}%</span>
                                        <span>
                                            @if($item->isFullyCleared())
                                                <span class="text-emerald-600 font-bold">✓ {{ __('Lengkap') }}</span>
                                            @else
                                                <span class="text-slate-400">{{ __('Checklist') }}</span>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $item->clearance_percentage == 100 ? 'bg-emerald-500' : 'bg-blue-500' }}"
                                             style="width: {{ $item->clearance_percentage }}%"></div>
                                    </div>
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('resignations.show', $item) }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition"
                                       title="{{ __('Detail & Clearance') }}">
                                        {{ __('Detail') }}
                                    </a>

                                    @if(in_array($item->status, ['approved', 'completed']))
                                        <a href="{{ route('resignations.pdf', $item) }}"
                                           class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-1"
                                           title="{{ __('Cetak Surat Clearance / Pengalaman Kerja') }}">
                                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>PDF</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-slate-700">{{ __('Belum Ada Data Pengajuan Resign') }}</div>
                                    <p class="text-xs text-slate-400">{{ __('Tidak ada catatan pengunduran diri yang cocok dengan parameter filter.') }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($resignations->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $resignations->links() }}
            </div>
        @endif
    </div>

</div>
@endsection


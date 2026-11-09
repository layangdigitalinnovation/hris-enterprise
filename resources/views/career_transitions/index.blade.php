@extends('layouts.app', ['headerTitle' => __('Manajemen Mutasi & Promosi Karyawan'), 'title' => __('Mutasi & Promosi')])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Primary Action --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Mutasi & Promosi Karyawan') }}</h1>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Kelola pergerakan karir, promosi jabatan, mutasi antar cabang/lokasi, rotasi tugas, dan penyesuaian kompensasi') }}</p>
                </div>
            </div>
        </div>

        @if(auth()->user()->canManageCareerTransitions() || auth()->user()->isManager())
            <div class="flex items-center gap-2.5">
                <a href="{{ route('career-transitions.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>{{ __('Ajukan Mutasi / Promosi') }}</span>
                </a>
            </div>
        @endif
    </div>

    {{-- 2. KPI Stat Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">{{ __('Total Pengajuan') }}</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-slate-800 mt-2">{{ number_format($totalCount) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Total riwayat pergerakan karir') }}</div>
        </div>

        {{-- Pending --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">{{ __('Menunggu Review') }}</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-amber-600 mt-2">{{ number_format($pendingCount) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Menunggu persetujuan manajemen') }}</div>
        </div>

        {{-- Approved (Ready to apply) --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">{{ __('Disetujui (Siap Berlaku)') }}</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-blue-600 mt-2">{{ number_format($approvedCount) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Menunggu jadwal tanggal efektif') }}</div>
        </div>

        {{-- Applied / Completed --}}
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500">{{ __('Telah Diterapkan') }}</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <div class="text-2xl font-bold text-emerald-600 mt-2">{{ number_format($appliedCount) }}</div>
            <div class="text-[11px] text-slate-400 mt-1">{{ __('Aktif pada master kepegawaian') }}</div>
        </div>
    </div>

    {{-- 3. Filter Bar & Search --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-4 sm:p-5 shadow-2xs space-y-4">
        {{-- Status Filter Tabs --}}
        <div class="flex flex-wrap items-center gap-1.5 pb-2 border-b border-slate-100">
            @php
                $currentStatus = request('status', '');
                $statuses = [
                    '' => __('Semua Status') . ' (' . $totalCount . ')',
                    'pending' => __('Menunggu Review') . ' (' . $pendingCount . ')',
                    'approved' => __('Disetujui') . ' (' . $approvedCount . ')',
                    'applied' => __('Telah Diterapkan') . ' (' . $appliedCount . ')',
                    'rejected' => __('Ditolak'),
                ];
            @endphp
            @foreach($statuses as $stKey => $stLabel)
                <a href="{{ request()->fullUrlWithQuery(['status' => $stKey, 'page' => 1]) }}"
                   class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $currentStatus === $stKey ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    {{ $stLabel }}
                </a>
            @endforeach
        </div>

        {{-- Form Filters --}}
        <form method="GET" action="{{ route('career-transitions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            {{-- Search Box --}}
            <div class="relative sm:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="{{ __('Cari No. SK / No. Transisi / Karyawan...') }}"
                       class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 placeholder-slate-400 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            {{-- Type Dropdown --}}
            <div>
                <select name="type" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    <option value="">{{ __('Semua Jenis Pergerakan') }}</option>
                    <option value="promotion" {{ request('type') == 'promotion' ? 'selected' : '' }}>{{ __('Promosi Jabatan') }}</option>
                    <option value="transfer" {{ request('type') == 'transfer' ? 'selected' : '' }}>{{ __('Mutasi Lokasi / Cabang') }}</option>
                    <option value="rotation" {{ request('type') == 'rotation' ? 'selected' : '' }}>{{ __('Rotasi Pekerjaan') }}</option>
                    <option value="demotion" {{ request('type') == 'demotion' ? 'selected' : '' }}>{{ __('Demosi') }}</option>
                </select>
            </div>

            {{-- Branch Dropdown --}}
            <div>
                <select name="branch_id" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    <option value="">{{ __('Semua Cabang') }}</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Department Dropdown --}}
            <div class="flex items-center gap-2">
                <select name="department_id" class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    <option value="">{{ __('Semua Departemen') }}</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition">
                    {{ __('Filter') }}
                </button>
            </div>
        </form>
    </div>

    {{-- 4. Main Transitions Table --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">{{ __('No. SK / Dokumen') }}</th>
                        <th class="py-3 px-4">{{ __('Karyawan') }}</th>
                        <th class="py-3 px-4">{{ __('Jenis') }}</th>
                        <th class="py-3 px-4">{{ __('Perubahan Karir (Sebelumnya → Baru)') }}</th>
                        <th class="py-3 px-4">{{ __('Tgl Efektif') }}</th>
                        <th class="py-3 px-4">{{ __('Status') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($transitions as $t)
                        <tr class="hover:bg-slate-50/60 transition">
                            {{-- Number & Letter --}}
                            <td class="py-3.5 px-4 font-mono">
                                <a href="{{ route('career-transitions.show', $t) }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline">
                                    {{ $t->transition_number }}
                                </a>
                                @if($t->letter_number)
                                    <div class="text-[10px] text-slate-400 font-sans mt-0.5">
                                        {{ __('SK:') }} {{ $t->letter_number }}
                                    </div>
                                @endif
                            </td>

                            {{-- Employee info --}}
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-800">{{ $t->employee?->full_name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">
                                    {{ $t->employee?->employee_code }} • {{ $t->employee?->department?->name }}
                                </div>
                            </td>

                            {{-- Transition Type --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $t->type_badge_classes }}">
                                    {{ $t->type_label }}
                                </span>
                            </td>

                            {{-- Career Change Matrix Summary --}}
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    {{-- Position change --}}
                                    @if($t->from_position_id !== $t->to_position_id)
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <span class="text-slate-500 line-through">{{ $t->fromPosition?->title ?? '-' }}</span>
                                            <svg class="w-3 h-3 text-emerald-500 shrink-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <strong class="text-slate-800">{{ $t->toPosition?->title ?? '-' }}</strong>
                                        </div>
                                    @endif

                                    {{-- Branch or Department change --}}
                                    @if($t->from_branch_id !== $t->to_branch_id || $t->from_department_id !== $t->to_department_id)
                                        <div class="text-[10px] text-slate-500 flex items-center gap-1.5">
                                            <span>{{ $t->fromBranch?->name ?? '-' }} ({{ $t->fromDepartment?->name ?? '-' }})</span>
                                            <svg class="w-3 h-3 text-blue-500 shrink-0 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                            <strong class="text-slate-700">{{ $t->toBranch?->name ?? '-' }} ({{ $t->toDepartment?->name ?? '-' }})</strong>
                                        </div>
                                    @endif

                                    @if($t->from_position_id === $t->to_position_id && $t->from_branch_id === $t->to_branch_id && $t->from_department_id === $t->to_department_id)
                                        <div class="text-[11px] text-slate-600 font-medium">
                                            {{ $t->toPosition?->title ?? $t->employee?->position?->title ?? '-' }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Effective Date --}}
                            <td class="py-3.5 px-4 font-mono">
                                <div class="font-bold text-slate-700">
                                    {{ $t->effective_date ? $t->effective_date->format('d/m/Y') : '-' }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-sans">
                                    {{ $t->is_effective ? __('Sudah Waktunya') : __('Masa Mendatang') }}
                                </div>
                            </td>

                            {{-- Status Badge --}}
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $t->status_badge_classes }}">
                                    {{ $t->status_label }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('career-transitions.show', $t) }}"
                                       class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition" title="{{ __('Lihat Detail') }}">
                                        {{ __('Detail') }}
                                    </a>
                                    @if(in_array($t->status, ['approved', 'applied']))
                                        <a href="{{ route('career-transitions.pdf', $t) }}"
                                           class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="{{ __('Unduh Surat Keputusan PDF') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">{{ __('Belum Ada Riwayat Mutasi & Promosi') }}</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">{{ __('Belum ada data pengajuan pergerakan karir pegawai yang tercatat dengan kriteria filter saat ini.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($transitions->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $transitions->links() }}
            </div>
        @endif
    </div>

</div>
@endsection


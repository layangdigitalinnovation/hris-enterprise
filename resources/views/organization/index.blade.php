@extends('layouts.app', ['headerTitle' => __('Master Struktur Organisasi'), 'title' => __('Struktur Organisasi')])

@section('content')
@php
    $activeTab = request('tab');
    if (!$activeTab) {
        if (request()->has('pos_page') || request()->has('pos_search')) {
            $activeTab = 'positions';
        } elseif (request()->has('branch_page')) {
            $activeTab = 'branches';
        } else {
            $activeTab = 'departments';
        }
    }
@endphp

<div class="space-y-8" x-data="{ tab: '{{ $activeTab }}' }">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Master Struktur Organisasi Perusahaan') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Pengaturan kantor cabang, hierarki departemen, dan level jabatan karyawan') }}</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('organization.branch.create') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Cabang') }}
            </a>
            <a href="{{ route('organization.department.create') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-sm transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Departemen') }}
            </a>
            <a href="{{ route('organization.position.create') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Jabatan') }}
            </a>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex gap-2 border-b border-slate-200 pb-3 text-xs font-semibold">
        <button @click="tab = 'departments'" :class="tab === 'departments' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
            {{ __('Departemen') }} ({{ $departments->total() }})
        </button>
        <button @click="tab = 'positions'" :class="tab === 'positions' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
            {{ __('Jabatan & Level') }} ({{ $positions->total() }})
        </button>
        <button @click="tab = 'branches'" :class="tab === 'branches' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
            {{ __('Kantor Cabang') }} ({{ $branches->total() }})
        </button>
    </div>

    <!-- TAB 1: DEPARTMENTS -->
    <div x-show="tab === 'departments'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Departemen & Divisi Perusahaan') }}</h4>
            <a href="{{ route('organization.department.create') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Departemen Baru') }}
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse ($departments as $dept)
                <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-start justify-between">
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md inline-block">{{ $dept->code }}</span>
                            @if(auth()->user()->canManageEmployees())
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('organization.department.edit', $dept) }}" class="p-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 border border-slate-200/80 transition" title="{{ __('Edit Departemen') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('organization.department.destroy', $dept) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus departemen \'{{ $dept->name }}\'?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200/80 transition" title="{{ __('Hapus Departemen') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                        <h4 class="font-bold text-slate-900 text-base mt-2">{{ $dept->name }}</h4>
                        <p class="text-xs text-slate-500 mt-1">{{ $dept->description ?? __('Tidak ada deskripsi') }}</p>
                    </div>
                    <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ __('Manager') }}: {{ $dept->manager ? $dept->manager->full_name : __('Belum ditentukan') }}</span>
                        <span class="font-bold text-slate-900">{{ __(':count Karyawan', ['count' => $dept->employees_count]) }}</span>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-3xl border border-slate-200/80 text-slate-400 text-xs">
                    {{ __('Belum ada departemen yang terdaftar.') }}
                </div>
            @endforelse
        </div>

        @if ($departments->hasPages())
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                {{ $departments->links() }}
            </div>
        @elseif ($departments->total() > 0)
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium px-4 py-2.5 bg-white rounded-2xl border border-slate-200/70 shadow-2xs">
                <span>{{ __('Menampilkan') }} <strong class="text-slate-800">{{ $departments->total() }}</strong> {{ __('departemen') }}</span>
                <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
            </div>
        @endif
    </div>

    <!-- TAB 2: POSITIONS -->
    <div x-show="tab === 'positions'" class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden p-6 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Jabatan & Posisi Karyawan') }}</h4>
                <p class="text-xs text-slate-500">{{ __('Struktur penamaan jabatan, divisi naungan, dan tingkat hierarki level') }}</p>
            </div>
            <a href="{{ route('organization.position.create') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5 self-start sm:self-auto shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Jabatan Baru') }}
            </a>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('organization.index') }}" class="flex flex-wrap items-center gap-2.5 pt-1">
            <input type="hidden" name="tab" value="positions">
            @if(request('dept_page'))
                <input type="hidden" name="dept_page" value="{{ request('dept_page') }}">
            @endif
            @if(request('branch_page'))
                <input type="hidden" name="branch_page" value="{{ request('branch_page') }}">
            @endif

            <div class="relative w-full sm:w-80 md:w-96">
                <!-- Search Icon -->
                <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                
                <input type="text" name="pos_search" value="{{ request('pos_search') }}" placeholder="{{ __('Cari nama jabatan, kode, atau departemen...') }}"
                    class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">
                
                @if(request('pos_search'))
                    <a href="{{ route('organization.index', array_merge(request()->except('pos_search', 'pos_page'), ['tab' => 'positions'])) }}"
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

            @if(request('pos_search'))
                <a href="{{ route('organization.index', array_merge(request()->except('pos_search', 'pos_page'), ['tab' => 'positions'])) }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ __('Reset') }}</span>
                </a>
            @endif
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 pb-2">
                        <th class="py-3 px-3 font-semibold">{{ __('Kode Jabatan') }}</th>
                        <th class="py-3 px-3 font-semibold">{{ __('Nama Jabatan') }}</th>
                        <th class="py-3 px-3 font-semibold">{{ __('Departemen') }}</th>
                        <th class="py-3 px-3 font-semibold">{{ __('Hierarki Level') }}</th>
                        <th class="py-3 px-3 font-semibold">{{ __('Jumlah Karyawan') }}</th>
                        @if(auth()->user()->canManageEmployees())
                        <th class="py-3 px-3 font-semibold text-right">{{ __('Aksi') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse ($positions as $pos)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-3 font-bold text-blue-600">{{ $pos->code }}</td>
                            <td class="py-3 px-3 font-sans font-bold text-slate-900">{{ $pos->title }}</td>
                            <td class="py-3 px-3 font-sans text-slate-600">{{ $pos->department->name ?? '-' }}</td>
                            <td class="py-3 px-3 font-sans">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ __('Level') }} {{ $pos->level }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-700 font-sans font-semibold">{{ __(':count Orang', ['count' => $pos->employees_count]) }}</td>
                            @if(auth()->user()->canManageEmployees())
                            <td class="py-3 px-3 font-sans text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('organization.position.edit', $pos) }}" class="p-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 border border-slate-200/80 transition" title="{{ __('Edit Jabatan') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form action="{{ route('organization.position.destroy', $pos) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan \'{{ $pos->title }}\'?');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200/80 transition" title="{{ __('Hapus Jabatan') }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->canManageEmployees() ? 6 : 5 }}" class="py-10 text-center text-slate-400 font-sans">
                                @if (request('pos_search'))
                                    <div class="space-y-2">
                                        <div class="text-sm font-semibold text-slate-700">{{ __('Jabatan Tidak Ditemukan') }}</div>
                                        <p class="text-xs text-slate-500">{{ __('Tidak ada jabatan atau posisi yang cocok dengan kata kunci') }} "<span class="font-bold text-slate-800">{{ request('pos_search') }}</span>".</p>
                                        <div class="pt-2">
                                            <a href="{{ route('organization.index', array_merge(request()->except('pos_search', 'pos_page'), ['tab' => 'positions'])) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                {{ __('Tampilkan Semua Jabatan') }}
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    {{ __('Belum ada jabatan yang terdaftar.') }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($positions->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $positions->links() }}
            </div>
        @elseif ($positions->total() > 0)
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                <span>
                    {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $positions->total() }}</strong> {{ __('jabatan') }}
                    @if(request('pos_search'))
                        <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('pos_search') }}")</span>
                    @endif
                </span>
                <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
            </div>
        @endif
    </div>

    <!-- TAB 3: BRANCHES -->
    <div x-show="tab === 'branches'" class="space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="font-bold text-slate-900 text-base">{{ __('Daftar Kantor Cabang Operasional') }}</h4>
            <a href="{{ route('organization.branch.create') }}" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Kantor Cabang') }}
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse ($branches as $br)
                <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md inline-block">{{ $br->code }}</span>
                            <h4 class="font-bold text-slate-900 text-base mt-2">{{ $br->name }}</h4>
                            <p class="text-xs text-slate-500 mt-1">{{ $br->address ?? '-' }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $br->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                {{ $br->is_active ? __('Aktif') : __('Nonaktif') }}
                            </span>
                            @if(auth()->user()->canManageEmployees())
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('organization.branch.edit', $br) }}" class="p-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 border border-slate-200/80 transition" title="{{ __('Edit Cabang') }}">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('organization.branch.destroy', $br) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kantor cabang \'{{ $br->name }}\'?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200/80 transition" title="{{ __('Hapus Cabang') }}">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 text-xs font-mono space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Negara & Zona Waktu') }}:</span>
                            <span class="text-blue-600 font-semibold font-sans flex items-center gap-1.5">
                                <span>{{ $br->country_flag }}</span>
                                <span>{{ $br->timezone_label }}</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Waktu Lokal Cabang') }}:</span>
                            <span class="text-slate-900 font-semibold flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                                <span>{{ $br->getLocalTime()->format('H:i:s') }}</span>
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Batas Radius GPS') }}:</span>
                            <span class="text-slate-900 font-semibold font-mono">{{ $br->radius_meters }} {{ __('meter') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Koordinat GPS') }}:</span>
                            @if($br->hasCoordinates())
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-700 text-[11px]">{{ number_format($br->latitude, 4) }}, {{ number_format($br->longitude, 4) }}</span>
                                    <a href="{{ $br->google_maps_url }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-[10px] font-sans font-semibold underline flex items-center gap-0.5" title="{{ __('Buka di Google Maps') }}">
                                        {{ __('Peta') }} ↗
                                    </a>
                                </div>
                            @else
                                <span class="text-amber-600 text-[11px] font-sans bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200">
                                    {{ __('Belum Diset') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-slate-200">
                            <span class="text-slate-500 font-sans">{{ __('Status Geofencing') }}:</span>
                            @if($br->hasCoordinates())
                                <span class="text-emerald-700 bg-emerald-50 text-[10px] font-sans font-bold px-2 py-0.5 rounded-md border border-emerald-200">
                                    ✓ {{ __('Siap Presensi GPS') }}
                                </span>
                            @else
                                <span class="text-slate-500 bg-slate-100 text-[10px] font-sans px-2 py-0.5 rounded-md">
                                    {{ __('Perlu Koordinat') }}
                                </span>
                            @endif
                        </div>
                        <div class="flex justify-between pt-1 border-t border-slate-200"><span class="text-slate-500 font-sans">{{ __('Karyawan') }}:</span><span class="text-emerald-700 font-bold font-sans">{{ __(':count Orang', ['count' => $br->employees_count]) }}</span></div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-white rounded-3xl border border-slate-200/80 text-slate-400 text-xs">
                    {{ __('Belum ada kantor cabang yang terdaftar.') }}
                </div>
            @endforelse
        </div>

        @if ($branches->hasPages())
            <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                {{ $branches->links() }}
            </div>
        @elseif ($branches->total() > 0)
            <div class="flex items-center justify-between text-xs text-slate-500 font-medium px-4 py-2.5 bg-white rounded-2xl border border-slate-200/70 shadow-2xs">
                <span>{{ __('Menampilkan') }} <strong class="text-slate-800">{{ $branches->total() }}</strong> {{ __('kantor cabang') }}</span>
                <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
            </div>
        @endif
    </div>

</div>
@endsection

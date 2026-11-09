@extends('layouts.app')

@section('title', __('Manajemen Role & Peran'))

@section('content')
<div class="space-y-6">

    <!-- Header Section with Breadcrumbs & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <span>{{ __('Pengaturan') }}</span>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Roles') }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Manajemen Role & Peran') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Kelola daftar peran pengguna dan pengaturan wewenang hak akses (permissions) sistem') }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('roles.create') }}"
               class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Tambah Role Baru') }}</span>
            </a>

            <!-- Table / Card View Toggle -->
            <div class="bg-slate-100 p-1 rounded-xl flex items-center border border-slate-200/80">
                <a href="{{ request()->fullUrlWithQuery(['view' => 'table']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition {{ $view === 'table' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    <span>{{ __('Tabel') }}</span>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['view' => 'card']) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1.5 transition {{ $view === 'card' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>{{ __('Kartu') }}</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('roles.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <input type="hidden" name="view" value="{{ $view }}">

            <div class="relative flex-1 w-full">
                <svg class="w-4 h-4 text-slate-400 absolute start-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="{{ __('Cari berdasarkan nama role...') }}"
                       class="w-full ps-10 pe-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>{{ __('Cari') }}</span>
                </button>
                @if($search)
                    <a href="{{ route('roles.index', ['view' => $view]) }}" title="{{ __('Reset Filter') }}" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Container -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- Table Header Banner with HRIS Pro Enterprise Slate Gradient -->
        <div class="px-5 py-3.5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5 font-bold text-xs sm:text-sm">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>{{ __('Daftar Role & Peran Sistem') }}</span>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/15 text-white border border-white/20">
                {{ $roles->count() }} {{ __('Total Role') }}
            </span>
        </div>

        @if($view === 'table')
            <!-- Table View -->
            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 text-center w-12">{{ __('NO') }}</th>
                            <th class="py-3 px-4 text-start">{{ __('NAMA ROLE') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('GUARD') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('TOTAL PERMISSION') }}</th>
                            <th class="py-3 px-4 text-center w-36">{{ __('AKSI') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($roles as $index => $role)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- No -->
                            <td class="py-3.5 px-4 text-center text-slate-400 font-medium font-mono">
                                {{ $index + 1 }}
                            </td>

                            <!-- Nama Role -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-2xs {{ $role->is_system ? 'bg-gradient-to-tr from-blue-600 to-indigo-600' : 'bg-slate-700' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 group-hover:text-blue-600 transition">{{ __($role->name) }}</span>
                                            @if($role->is_system)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200" title="{{ __('Role Sistem') }}">{{ __('System') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 font-mono mt-0.5">{{ __('Role Key:') }} {{ $role->key }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Guard -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[11px] font-mono text-slate-500 bg-slate-100 border border-slate-200">
                                    {{ $role->guard_name }}
                                </span>
                            </td>

                            <!-- Total Permission -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-2xs">
                                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                    <span>{{ $role->permissions_count }} {{ __('Permission Aktif') }}</span>
                                </span>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Configure Permissions Matrix Button -->
                                    <a href="{{ route('roles.permissions', $role) }}"
                                       title="{{ __('Kelola Hak Akses / Permissions Matrix') }}"
                                       class="p-1.5 rounded-lg text-blue-600 hover:text-blue-700 hover:bg-blue-50 border border-blue-200 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </a>

                                    <!-- Edit Button -->
                                    <a href="{{ route('roles.edit', $role) }}"
                                       title="{{ __('Edit Role') }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 border border-slate-200/80 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <!-- Delete Button -->
                                    @if(!$role->is_system)
                                        <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus role ini?') }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="{{ __('Hapus Role') }}"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200/80 transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                {{ __('Tidak ada role ditemukan.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- Card View Mode -->
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($roles as $role)
                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs hover:shadow-sm transition space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-2xs {{ $role->is_system ? 'bg-gradient-to-tr from-blue-600 to-indigo-600' : 'bg-slate-700' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ __($role->name) }}</h4>
                                <p class="text-[11px] text-slate-400 font-mono truncate">{{ __('Role Key:') }} {{ $role->key }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shrink-0">
                            {{ $role->permissions_count }} {{ __('Permission') }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">
                        {{ $role->description ? __($role->description) : __('Tidak ada keterangan deskripsi untuk peran ini.') }}
                    </p>

                    <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                        <span class="text-[11px] text-slate-400 font-mono">
                            {{ __('Guard:') }} {{ $role->guard_name }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('roles.permissions', $role) }}" class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>{{ __('Kelola Hak Akses') }}</span>
                            </a>
                            <a href="{{ route('roles.edit', $role) }}" title="{{ __('Edit Role') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 border border-slate-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center text-slate-400">
                    {{ __('Tidak ada role ditemukan.') }}
                </div>
                @endforelse
            </div>
        @endif
    </div>

</div>
@endsection

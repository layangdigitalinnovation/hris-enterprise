@extends('layouts.app')

@section('title', __('Manajemen User & Hak Akses'))

@section('content')
<div class="space-y-6" x-data="userManagementApp()">

    <!-- Header Section with Breadcrumbs & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <span>{{ __('Pengaturan') }}</span>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Users') }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Manajemen User & Hak Akses') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Kelola akun pengguna sistem, pembagian role, akses cabang & departemen, serta izin otoritas') }}</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('users.create') }}"
               class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Tambah User Baru') }}</span>
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

    <!-- Sub-Tabs: User Administrator vs User Akun Karyawan -->
    <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2">
        <a href="{{ request()->fullUrlWithQuery(['tab' => 'admin', 'page' => 1]) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $tab === 'admin' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <span>{{ __('User Administrator') }}</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'admin' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $totalAdminCount }}</span>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['tab' => 'employee', 'page' => 1]) }}"
           class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ $tab === 'employee' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span>{{ __('User Akun Karyawan') }}</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] {{ $tab === 'employee' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $totalEmployeeCount }}</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="hidden" name="view" value="{{ $view }}">

            <!-- Search Field -->
            <div class="md:col-span-7 relative">
                <svg class="w-4 h-4 text-slate-400 absolute start-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="{{ __('Cari berdasarkan nama, username, atau email...') }}"
                       class="w-full ps-10 pe-4 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
            </div>

            <!-- Role Filter -->
            <div class="md:col-span-3">
                <select name="role" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <option value="">{{ __('Semua Role') }}</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->id }}" {{ $roleFilter == $r->id || $roleFilter === $r->key ? 'selected' : '' }}>
                            {{ __($r->name) }} ({{ $r->key }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Button -->
            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>{{ __('Cari') }}</span>
                </button>
                @if($search || $roleFilter)
                    <a href="{{ route('users.index', ['tab' => $tab, 'view' => $view]) }}" title="{{ __('Reset Filter') }}" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Main Content Container (Card Header with HRIS Pro Slate-900 / Slate-800 Modern Theme) -->
    <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
        <!-- Table Header Banner -->
        <div class="px-5 py-3.5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5 font-bold text-xs sm:text-sm">
                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>{{ $tab === 'employee' ? __('Daftar Akun Karyawan') : __('Daftar User Administrator') }}</span>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/10 text-white border border-white/15">
                {{ $users->total() }} {{ __('Total Akun') }}
            </span>
        </div>

        @if($view === 'table')
            <!-- Table View -->
            <div class="overflow-x-auto">
                <table class="w-full text-start text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 text-center w-12">{{ __('NO') }}</th>
                            <th class="py-3 px-4 text-start">{{ __('IDENTITAS USER') }}</th>
                            <th class="py-3 px-4 text-start">{{ __('ROLE / PERAN') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('KONEKSI') }}</th>
                            <th class="py-3 px-4 text-start">{{ __('HAK AKSES / OTORITAS') }}</th>
                            <th class="py-3 px-4 text-center w-36">{{ __('AKSI') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $index => $u)
                        <tr class="hover:bg-slate-50/80 transition group">
                            <!-- No -->
                            <td class="py-3.5 px-4 text-center text-slate-400 font-medium font-mono">
                                {{ $users->firstItem() + $index }}
                            </td>

                            <!-- Identitas User -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0 shadow-2xs {{ $u->isSuperAdmin() ? 'bg-gradient-to-tr from-blue-600 to-indigo-600' : ($u->role === 'hr_admin' ? 'bg-indigo-600' : ($u->role === 'manager' ? 'bg-sky-600' : 'bg-slate-600')) }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="font-bold text-slate-900 group-hover:text-blue-600 transition">{{ $u->name }}</span>
                                            @if($u->username)
                                                <span class="text-[11px] text-slate-400 font-mono">({{ $u->username }})</span>
                                            @endif
                                            @if(!$u->is_active)
                                                <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">{{ __('Nonaktif') }}</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400 mt-0.5">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                                <span>{{ $u->email }}</span>
                                            </span>
                                            @if($u->phone)
                                                <span>·</span>
                                                <span>{{ $u->phone }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role / Peran -->
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold border {{ $u->isSuperAdmin() ? 'bg-blue-50 text-blue-700 border-blue-200' : ($u->role === 'hr_admin' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : ($u->role === 'manager' ? 'bg-sky-50 text-sky-700 border-sky-200' : ($u->role === 'finance' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-slate-100 text-slate-700 border-slate-200'))) }}">
                                    {{ $u->role_display_name }}
                                </span>
                            </td>

                            <!-- Koneksi Karyawan -->
                            <td class="py-3.5 px-4 text-center">
                                @if($u->employee)
                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[11px] font-semibold" title="{{ $u->employee->full_name }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 015.656 0l4 4a4 4 0 01-5.656 5.656l-1.102-1.101" />
                                        </svg>
                                        <span>{{ $u->employee->employee_code }}</span>
                                    </div>
                                @else
                                    <span class="text-slate-300 font-bold">-</span>
                                @endif
                            </td>

                            <!-- Hak Akses / Otoritas -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1 text-[11px]">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400 uppercase font-bold text-[10px] w-16">{{ __('CABANG') }}:</span>
                                        @if($u->all_branches)
                                            <span class="px-2 py-0.2 rounded-md font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                {{ __('Semua Cabang') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.2 rounded-md font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $u->branches->count() }} {{ __('Cabang') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-slate-400 uppercase font-bold text-[10px] w-16">{{ __('DEPARTEMEN') }}:</span>
                                        @if($u->all_departments)
                                            <span class="px-2 py-0.2 rounded-md font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                                {{ __('Semua Dept') }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.2 rounded-md font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                                {{ $u->departments->count() }} {{ __('Dept') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <!-- Direct Permission Shield Button -->
                                    <a href="{{ route('users.permissions', $u) }}"
                                       title="{{ __('Kelola Permission Khusus User') }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 border border-slate-200/80 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </a>

                                    <!-- View Detail Eye Button -->
                                    <button type="button"
                                            @click="viewUser({{ $u->id }})"
                                            title="{{ __('Lihat Detail User') }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 border border-slate-200/80 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>

                                    <!-- Edit Button -->
                                    <a href="{{ route('users.edit', $u) }}"
                                       title="{{ __('Edit User') }}"
                                       class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 border border-slate-200/80 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>

                                    <!-- Delete Button -->
                                    @if($u->id !== auth()->id() && $u->id !== 1)
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus user ini?') }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    title="{{ __('Hapus User') }}"
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
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto space-y-2">
                                    <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <p class="font-bold text-xs text-slate-700">{{ __('Tidak ada user ditemukan') }}</p>
                                    <p class="text-[11px] text-slate-400">{{ __('Silakan sesuaikan filter pencarian atau tambahkan user baru.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <!-- Card View Mode -->
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($users as $u)
                <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs hover:shadow-sm transition space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center font-bold text-sm text-white shrink-0 shadow-2xs {{ $u->isSuperAdmin() ? 'bg-gradient-to-tr from-blue-600 to-indigo-600' : ($u->role === 'hr_admin' ? 'bg-indigo-600' : ($u->role === 'manager' ? 'bg-sky-600' : 'bg-slate-600')) }}">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-900 truncate">{{ $u->name }}</h4>
                                <p class="text-[11px] text-slate-400 truncate font-mono">{{ $u->email }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold border shrink-0 {{ $u->isSuperAdmin() ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200' }}">
                            {{ $u->role_display_name }}
                        </span>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-2.5 space-y-1.5 text-[11px] border border-slate-100">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">{{ __('Username') }}:</span>
                            <span class="font-mono font-bold text-slate-700">{{ $u->username ?? '-' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">{{ __('Cakupan Cabang') }}:</span>
                            <span class="font-semibold text-slate-800">{{ $u->all_branches ? __('Semua Cabang') : $u->branches->count() . ' ' . __('Cabang') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-slate-400">{{ __('Cakupan Dept') }}:</span>
                            <span class="font-semibold text-slate-800">{{ $u->all_departments ? __('Semua Departemen') : $u->departments->count() . ' ' . __('Dept') }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1 border-t border-slate-100">
                        <span class="text-[10px] text-slate-400">
                            {{ $u->is_active ? __('● Akun Aktif') : __('○ Nonaktif') }}
                        </span>
                        <div class="flex items-center gap-1">
                            <a href="{{ route('users.permissions', $u) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 border border-slate-200 transition" title="{{ __('Permission Khusus') }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </a>
                            <button type="button" @click="viewUser({{ $u->id }})" class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 border border-slate-200 transition cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <a href="{{ route('users.edit', $u) }}" title="{{ __('Edit User') }}" class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 border border-slate-200 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-12 text-center text-slate-400">
                    {{ __('Tidak ada user ditemukan.') }}
                </div>
                @endforelse
            </div>
        @endif

        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200/80">
            {{ $users->links() }}
        </div>
        @endif
    </div>

    <!-- Modal View Detail User -->
    <div x-show="showDetailModal"
         x-cloak
         style="display: none;"
         class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
        <div @click.outside="showDetailModal = false"
             class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden text-start">
            
            <div class="px-6 py-4 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
                <div class="flex items-center gap-2 font-bold text-sm">
                    <div class="w-7 h-7 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <span>{{ __('Informasi Detail Pengguna') }}</span>
                </div>
                <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs" x-show="detailUser">
                <div class="flex items-center gap-4 pb-4 border-b border-slate-100">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-xl flex items-center justify-center shadow">
                        <span x-text="detailUser?.name ? detailUser.name.charAt(0).toUpperCase() : 'U'"></span>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-base text-slate-900" x-text="detailUser?.name"></h3>
                        <p class="text-slate-400 font-mono" x-text="detailUser?.email"></p>
                        <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200"
                              x-text="detailUser?.role_model?.name || detailUser?.role"></span>
                    </div>
                </div>

                <div class="space-y-2 bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">{{ __('Username (ID Login)') }}:</span>
                        <span class="font-bold text-slate-800 font-mono" x-text="detailUser?.username || '-'"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">{{ __('Nomor Telepon') }}:</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser?.phone || '-'"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">{{ __('Karyawan Terhubung') }}:</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser?.employee ? detailUser.employee.employee_code + ' - ' + detailUser.employee.first_name : '{{ __('Tidak terhubung') }}'"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">{{ __('Cakupan Cabang') }}:</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser?.all_branches ? '{{ __('Semua Cabang') }}' : (detailUser?.branches?.length || 0) + ' {{ __('Cabang') }}'"></span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-slate-200/60">
                        <span class="text-slate-500">{{ __('Cakupan Departemen') }}:</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser?.all_departments ? '{{ __('Semua Departemen') }}' : (detailUser?.departments?.length || 0) + ' {{ __('Dept') }}'"></span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-500">{{ __('Status Akun') }}:</span>
                        <span class="font-bold" :class="detailUser?.is_active ? 'text-blue-600' : 'text-rose-600'" x-text="detailUser?.is_active ? '{{ __('Aktif') }}' : '{{ __('Nonaktif') }}'"></span>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold transition">
                        {{ __('Tutup') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function userManagementApp() {
        return {
            showDetailModal: false,
            detailUser: null,

            async viewUser(userId) {
                try {
                    const res = await fetch('{{ url('users') }}/' + userId, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.detailUser = data.user;
                        this.showDetailModal = true;
                    }
                } catch (e) {
                    console.error('Error fetching user detail:', e);
                }
            }
        };
    }
</script>
@endsection

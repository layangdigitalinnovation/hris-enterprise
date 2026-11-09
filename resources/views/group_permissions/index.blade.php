@extends('layouts.app')

@section('title', __('Group Permission'))

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
                <span class="text-slate-700 font-semibold">{{ __('Group Permission') }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Group Permission') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Kelola pengelompokan modul/fitur untuk penyusunan matriks hak akses pengguna dan peran sistem') }}</p>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <a href="{{ route('group-permissions.create') }}"
           class="px-4 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 hover:shadow-lg transition flex items-center gap-2 self-start cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
            <span>{{ __('Tambah Group Baru') }}</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('group-permissions.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
            <div class="relative flex-1 w-full">
                <svg class="w-4 h-4 text-slate-400 absolute start-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="{{ __('Cari nama group permission...') }}"
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
                    <a href="{{ route('group-permissions.index') }}" title="{{ __('Reset Filter') }}" class="p-2.5 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200 transition">
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
        <!-- Banner Header with HRIS Pro Enterprise Slate Gradient -->
        <div class="px-5 py-3.5 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5 font-bold text-xs sm:text-sm">
                <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                </svg>
                <span>{{ __('Daftar Group Permission') }}</span>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/15 text-white border border-white/20">
                {{ $groups->total() }} {{ __('Total Group') }}
            </span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-start text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4 text-center w-12">{{ __('NO') }}</th>
                        <th class="py-3 px-4 text-start">{{ __('NAMA GROUP MODUL') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('TOTAL PERMISSION TERKAIT') }}</th>
                        <th class="py-3 px-4 text-center w-28">{{ __('AKSI') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($groups as $index => $g)
                    <tr class="hover:bg-slate-50/80 transition group">
                        <!-- No -->
                        <td class="py-3.5 px-4 text-center text-slate-400 font-medium font-mono">
                            {{ $groups->firstItem() + $index }}
                        </td>

                        <!-- Nama Group Modul -->
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-900 group-hover:text-blue-600 transition">{{ __($g->name) }}</span>
                                        <span class="text-[10px] text-slate-400 font-mono">{{ __('ID Group:') }} #{{ $g->id }}</span>
                                    </div>
                                    @if($g->description)
                                        <p class="text-[11px] text-slate-500 leading-tight mt-0.5 truncate">{{ __($g->description) }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Total Permission Terkait -->
                        <td class="py-3.5 px-4 text-center">
                            <a href="{{ route('permissions.index', ['group_id' => $g->id]) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition"
                               title="{{ __('Lihat semua permission pada group ini') }}">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <span>{{ $g->permissions_count }} {{ __('Permission') }}</span>
                            </a>
                        </td>

                        <!-- Aksi -->
                        <td class="py-3.5 px-4 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <!-- Edit -->
                                <a href="{{ route('group-permissions.edit', $g) }}"
                                   title="{{ __('Edit Group') }}"
                                   class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 border border-slate-200/80 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>

                                <!-- Delete -->
                                <form action="{{ route('group-permissions.destroy', $g) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus group permission ini?') }}');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            title="{{ __('Hapus Group') }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 border border-slate-200/80 transition cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400">
                            {{ __('Tidak ada group permission ditemukan.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <div class="px-5 py-3.5 bg-slate-50 border-t border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <span class="text-slate-500">
                {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $groups->firstItem() ?? 0 }}</strong> {{ __('sampai') }} <strong class="text-slate-800">{{ $groups->lastItem() ?? 0 }}</strong> {{ __('dari total') }} <strong class="text-slate-800">{{ $groups->total() }}</strong> {{ __('data') }}
            </span>
            <div>
                {{ $groups->links() }}
            </div>
        </div>
    </div>

</div>
@endsection

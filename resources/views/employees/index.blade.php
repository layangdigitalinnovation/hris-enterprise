@extends('layouts.app', ['headerTitle' => __('Manajemen Data Karyawan'), 'title' => __('Daftar Karyawan')])

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div>
            @if(auth()->user()->role === 'manager')
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Anggota Tim Departemen') }}</h3>
                <p class="text-xs text-slate-500 font-medium">{{ __('Daftar staf dan karyawan di bawah departemen Anda') }}</p>
            @else
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Direktori Data Karyawan') }}</h3>
                <p class="text-xs text-slate-500 font-medium">{{ __('Total :count karyawan terdaftar dalam sistem perusahaan', ['count' => $employees->total()]) }}</p>
            @endif
        </div>
        @if(auth()->user()->canManageEmployees())
        <a href="{{ route('employees.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Tambah Karyawan Baru') }}
        </a>
        @endif
    </div>

    <!-- Filter Form Card -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Search -->
            <div class="lg:col-span-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nama, NIK, ID, email...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>

            <!-- Department Filter -->
            <div>
                <select name="department_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <option value="">{{ __('Semua Departemen') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Branch Filter -->
            <div>
                <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <option value="">{{ __('Semua Cabang') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Submit & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                    {{ __('Filter') }}
                </button>
                <a href="{{ route('employees.index') }}" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition text-center">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Employees Table Card -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200 font-semibold">
                        <th class="py-3.5 px-4">{{ __('Karyawan') }}</th>
                        <th class="py-3.5 px-4">{{ __('ID / NIK') }}</th>
                        <th class="py-3.5 px-4">{{ __('Departemen & Jabatan') }}</th>
                        <th class="py-3.5 px-4">{{ __('Lokasi Cabang') }}</th>
                        <th class="py-3.5 px-4">{{ __('Status Kerja') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employees as $emp)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ strtoupper(substr($emp->first_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('employees.show', $emp) }}" class="font-bold text-slate-900 hover:text-blue-600 text-sm block">
                                            {{ $emp->full_name }}
                                        </a>
                                        <span class="text-slate-500 text-[11px]">{{ $emp->email }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="font-semibold text-blue-600 block">{{ $emp->employee_code }}</span>
                                <span class="text-slate-400 text-[10px]">{{ $emp->nik ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-800 font-medium block">{{ $emp->department->name ?? '-' }}</span>
                                <span class="text-slate-500 text-[11px]">{{ $emp->position->title ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $emp->branch->name ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($emp->status === 'active')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ __('Aktif') }} · {{ __(ucfirst($emp->employment_status)) }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ __(ucfirst($emp->status)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(auth()->user()->isHrAdmin())
                                        <a href="{{ route('employees.reset-password.form', $emp) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-600 border border-slate-200 transition" title="{{ __('Reset Password Login Karyawan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                        </a>
                                    @endif
                                    <a href="{{ route('employees.show', $emp) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 transition" title="{{ __('Lihat Profil') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if(auth()->user()->canManageEmployees())
                                    <a href="{{ route('employees.edit', $emp) }}" class="p-1.5 rounded-lg bg-slate-100 hover:bg-blue-50 text-slate-600 hover:text-blue-600 border border-slate-200 transition" title="{{ __('Edit Karyawan') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                {{ __('Tidak ada data karyawan yang cocok dengan kriteria pencarian.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $employees->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

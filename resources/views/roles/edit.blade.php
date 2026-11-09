@extends('layouts.app')

@section('title', __('Edit Role: :name', ['name' => $role->name]))

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 transition">{{ __('Roles') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit Role') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Edit Role: :name', ['name' => $role->name]) }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        {{ __('Role Key:') }} <strong class="text-blue-600 font-mono">{{ $role->key }}</strong> · {{ __('Perbarui identitas peran sistem dan wewenang umum') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('roles.permissions', $role) }}" class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-xs font-bold transition flex items-center gap-1.5 shadow-2xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>{{ __('Kelola Hak Akses / Matrix') }}</span>
            </a>
            <a href="{{ route('roles.index') }}" class="px-3.5 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 transition shadow-xs flex items-center gap-1.5">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>{{ __('Kembali') }}</span>
            </a>
        </div>
    </div>

    <!-- Error Alert Block -->
    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Terdapat kesalahan pada isian formulir:') }}</span>
            </div>
            <ul class="list-disc ps-5 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Card Form -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Informasi & Identitas Peran') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Nama resmi peran dan kode pengenal unik wewenang.') }}</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Role / Peran *') }}</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required placeholder="{{ __('contoh: Finance Supervisor') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Role Key (Kode Unik Slug)') }}</label>
                        <input type="text" name="key" value="{{ old('key', $role->key) }}" {{ $role->is_system ? 'disabled' : '' }}
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 disabled:bg-slate-100 disabled:text-slate-400 transition">
                        @if($role->is_system)
                            <p class="text-[11px] text-amber-600 mt-1 font-medium">{{ __('Role key untuk role sistem bawaan dilindungi dan tidak dapat diubah.') }}</p>
                        @endif
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Guard Name') }}</label>
                        <input type="text" name="guard_name" value="{{ old('guard_name', $role->guard_name) }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Keterangan / Deskripsi Tugas') }}</label>
                    <textarea name="description" rows="3" placeholder="{{ __('Tuliskan penjelasan cakupan tugas dan tanggung jawab peran ini di sistem...') }}"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ old('description', $role->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('roles.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Perbarui Role') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


@extends('layouts.app')

@section('title', __('Tambah Permission Baru'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <a href="{{ route('permissions.index') }}" class="hover:text-blue-600 transition">{{ __('Permissions') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah Permission') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Tambah Permission Baru') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Daftarkan izin akses spesifik dan tautkan ke grup modul aplikasi') }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('permissions.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 transition shadow-xs flex items-center gap-1.5 self-start sm:self-auto">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Daftar Permission') }}</span>
        </a>
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

    <form action="{{ route('permissions.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Card Form -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Informasi Wewenang / Permission') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Tentukan slug wewenang, nama tampilan yang mudah dipahami, dan grup modul terkait.') }}</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Permission (Slug) *') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('contoh: karyawan.create atau cuti.approve') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Judul / Tampilan *') }}</label>
                    <input type="text" name="display_name" value="{{ old('display_name') }}" required placeholder="{{ __('contoh: Tambah Data Karyawan Baru') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Group Modul *') }}</label>
                        <select name="permission_group_id" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">{{ __('-- Pilih Group Modul --') }}</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}" {{ old('permission_group_id', $groupId) == $g->id ? 'selected' : '' }}>
                                    {{ __($g->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Guard Name') }}</label>
                        <input type="text" name="guard_name" value="{{ old('guard_name', 'web') }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Deskripsi / Keterangan') }}</label>
                    <textarea name="description" rows="3" placeholder="{{ __('Tuliskan rincian izin fungsi wewenang ini di dalam sistem...') }}"
                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('permissions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Simpan Permission') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


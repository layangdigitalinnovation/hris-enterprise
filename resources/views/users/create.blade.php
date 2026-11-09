@extends('layouts.app')

@section('title', __('Tambah User Pengguna Baru'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12" x-data="{
    allBranches: true,
    allDepartments: true,
    selectedRole: '{{ old('role_id', '') }}'
}">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <a href="{{ route('users.index') }}" class="hover:text-blue-600 transition">{{ __('Users') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah User Baru') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Tambah User Pengguna Baru') }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">{{ __('Buat akun pengguna sistem, tentukan peran utama, dan batasi jangkauan cabang serta departemen') }}</p>
                </div>
            </div>
        </div>

        <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold border border-slate-200 transition shadow-xs flex items-center gap-1.5 self-start sm:self-auto">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Daftar User') }}</span>
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

    <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: Identitas & Kredensial Login -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Identitas & Kredensial Login') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Informasi nama akun, username, email aktif, dan kata sandi akses.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Lengkap *') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('contoh: Ahmad Fauzi, S.Kom') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Username (ID Login)') }}</label>
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="{{ __('otomatis dibuat dari email jika kosong') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 font-mono text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alamat Email Aktif *') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="{{ __('contoh: ahmad.fauzi@company.com') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kata Sandi (Password) *') }}</label>
                    <input type="password" name="password" required minlength="6" placeholder="{{ __('minimal 6 karakter') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('No. Telepon / WhatsApp') }}</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="{{ __('contoh: +62 812-3456-7890 / +966 50 123 4567') }}"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Akun') }}</label>
                    <label class="flex items-center gap-2.5 pt-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                               class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="text-xs font-semibold text-slate-800">{{ __('Akun Aktif (Dapat Login ke Sistem)') }}</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Section 2: Peran Sistem & Tautan Karyawan -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Peran Sistem & Hubungan Karyawan') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Tentukan tingkatan wewenang pengguna dan hubungkan dengan profil karyawan jika ada.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Role / Peran Utama *') }}</label>
                    <select name="role_id" required x-model="selectedRole"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">{{ __('-- Pilih Peran Pengguna --') }}</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ __($role->name) }} ({{ $role->key }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tautkan dengan Data Karyawan') }}</label>
                    <select name="employee_id"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <option value="">{{ __('-- Tidak Dihubungkan / User Khusus --') }}</option>
                        @foreach($availableEmployees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->branch->name ?? '-' }})
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Opsi ini akan menyinkronkan profil login dengan biodata kepegawaian.') }}</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Cakupan Otoritas Wilayah & Departemen -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Cakupan Otoritas Wilayah & Organisasi') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Batasi cabang kantor atau departemen apa saja yang dapat dilihat dan dikelola oleh user ini.') }}</p>
                </div>
            </div>

            <!-- Cabang Scope -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="font-semibold text-slate-800 text-xs">{{ __('Akses Kantor / Cabang') }}</label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="all_branches" value="1" x-model="allBranches"
                               class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span>{{ __('Akses Seluruh Cabang (Nasional & Internasional)') }}</span>
                    </label>
                </div>

                <div x-show="!allBranches" x-collapse class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <p class="text-[11px] font-medium text-slate-500 mb-2">{{ __('Pilih cabang-cabang yang diizinkan untuk dikelola user ini:') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                        @foreach($branches as $branch)
                            <label class="flex items-center gap-2 p-2 rounded-xl hover:bg-white transition cursor-pointer select-none">
                                <input type="checkbox" name="branch_ids[]" value="{{ $branch->id }}"
                                       class="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                                <span class="text-slate-800">{{ $branch->name }}</span>
                                <span class="text-[10px] text-slate-400 font-mono">({{ $branch->country ?? 'ID' }})</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            <!-- Departemen Scope -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="font-semibold text-slate-800 text-xs">{{ __('Akses Departemen') }}</label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="all_departments" value="1" x-model="allDepartments"
                               class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span>{{ __('Akses Seluruh Departemen') }}</span>
                    </label>
                </div>

                <div x-show="!allDepartments" x-collapse class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                    <p class="text-[11px] font-medium text-slate-500 mb-2">{{ __('Pilih departemen yang diizinkan untuk dikelola user ini:') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 text-xs">
                        @foreach($departments as $dept)
                            <label class="flex items-center gap-2 p-2 rounded-xl hover:bg-white transition cursor-pointer select-none">
                                <input type="checkbox" name="department_ids[]" value="{{ $dept->id }}"
                                       class="rounded text-blue-600 focus:ring-blue-500 w-3.5 h-3.5">
                                <span class="text-slate-800">{{ $dept->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition cursor-pointer flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Simpan Akun User') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


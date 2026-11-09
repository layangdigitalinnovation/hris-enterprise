@extends('layouts.app', ['headerTitle' => __('Reset Password Login Karyawan'), 'title' => __('Reset Password: :name', ['name' => $employee->full_name])])

@section('content')
<div class="max-w-2xl mx-auto space-y-6" x-data="{
    resetMode: 'default',
    customPassword: '',
    showPass: false,
    selectedRole: '{{ $employee->user ? $employee->user->role : 'employee' }}',
    isActive: {{ $employee->user && !$employee->user->is_active ? 'false' : 'true' }}
}">

    <!-- Breadcrumb & Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('employees.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Karyawan') }}</a>
                <span>/</span>
                <a href="{{ route('employees.show', $employee) }}" class="hover:text-blue-600 font-medium transition">{{ $employee->full_name }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Reset Password') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Reset Password Akun Login') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Bantu karyawan yang lupa atau terkendala login ke portal HRIS') }}</p>
        </div>
        <a href="{{ route('employees.show', $employee) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    <!-- Employee Profile Banner -->
    <div class="p-5 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center font-bold text-lg shrink-0 shadow-2xs">
                {{ strtoupper(substr($employee->first_name, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="font-bold text-slate-900 text-base">{{ $employee->full_name }}</span>
                    @if($employee->user)
                        @php
                            $roleName = match($employee->user->role) {
                                'super_admin' => __('Super Administrator'),
                                'hr_admin' => __('HR Administrator'),
                                'finance' => __('Finance / Keuangan'),
                                'manager' => __('Manager / Supervisor'),
                                'employee' => __('Karyawan Biasa'),
                                default => ucfirst($employee->user->role)
                            };
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ __('Akun Aktif') }} ({{ $roleName }})
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ __('Belum Memiliki Akun Login') }}
                        </span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-slate-500">
                    <span class="font-mono text-blue-600 font-semibold">{{ $employee->email }}</span>
                    <span>•</span>
                    <span class="font-mono">{{ __('NIK:') }} {{ $employee->employee_code }}</span>
                    <span>•</span>
                    <span>{{ $employee->department->name ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employees.reset-password', $employee) }}" method="POST" class="space-y-6">
        @csrf

        <!-- 1. Pilihan Mode Kata Sandi Baru -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Pilihan Kata Sandi Baru') }}
            </h4>

            <div class="grid grid-cols-1 gap-3 text-xs">
                <!-- Mode 1: Default password -->
                <label :class="resetMode === 'default' ? 'border-blue-500 bg-blue-50/40 text-blue-900 ring-1 ring-blue-500' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'" class="p-4 rounded-2xl border flex items-start gap-3.5 cursor-pointer transition">
                    <input type="radio" name="mode" value="default" x-model="resetMode" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="text-xs font-bold block">{{ __('Gunakan Password Standar') }} (<code class="font-mono text-blue-600 bg-white px-2 py-0.5 rounded border border-slate-200">password</code>)</span>
                        <span class="text-slate-500 block mt-1 leading-relaxed">{{ __('Rekomendasi tercepat. Karyawan login menggunakan kata sandi standar password dan dapat mengubahnya mandiri nanti.') }}</span>
                    </div>
                </label>

                <!-- Mode 2: Random generated -->
                <label :class="resetMode === 'random' ? 'border-blue-500 bg-blue-50/40 text-blue-900 ring-1 ring-blue-500' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'" class="p-4 rounded-2xl border flex items-start gap-3.5 cursor-pointer transition">
                    <input type="radio" name="mode" value="random" x-model="resetMode" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="text-xs font-bold block">{{ __('Generate Password Acak (Otomatis & Unik)') }}</span>
                        <span class="text-slate-500 block mt-1 leading-relaxed">{{ __('Sistem akan membuat kombinasi aman unik (contoh: Hris#8291) dan menampilkan kartu kredensial lengkap untuk disalin.') }}</span>
                    </div>
                </label>

                <!-- Mode 3: Custom Manual -->
                <label :class="resetMode === 'custom' ? 'border-blue-500 bg-blue-50/40 text-blue-900 ring-1 ring-blue-500' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50'" class="p-4 rounded-2xl border flex items-start gap-3.5 cursor-pointer transition">
                    <input type="radio" name="mode" value="custom" x-model="resetMode" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                    <div class="w-full">
                        <span class="text-xs font-bold block">{{ __('Tentukan Password Baru Manual') }}</span>
                        <span class="text-slate-500 block mt-1 leading-relaxed">{{ __('Ketikkan kata sandi baru secara khusus untuk karyawan ini (minimal 6 karakter).') }}</span>

                        <div x-show="resetMode === 'custom'" class="mt-3 relative">
                            <input :type="showPass ? 'text' : 'password'" name="custom_password" x-model="customPassword" placeholder="{{ __('Masukkan password baru minimal 6 karakter...') }}"
                                class="w-full pl-3.5 pr-10 rtl:pl-10 rtl:pr-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <button type="button" @click="showPass = !showPass" class="absolute right-3 rtl:right-auto rtl:left-3 top-3 text-slate-400 hover:text-slate-600">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        <!-- 2. Peran Hak Akses & Status Akun -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Hak Akses Portal & Status Akun') }}
            </h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-semibold text-slate-700 block mb-1.5">{{ __('Peran Hak Akses Portal *') }}</label>
                    <select name="role" x-model="selectedRole" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="employee">{{ __('Karyawan (Employee Self-Service)') }}</option>
                        <option value="manager">{{ __('Manager / Approval Departemen') }}</option>
                        <option value="finance">{{ __('Finance / Keuangan & Payroll') }}</option>
                        <option value="hr_admin">{{ __('HR Administrator') }}</option>
                        @if(auth()->user()->isSuperAdmin())
                            <option value="super_admin">{{ __('Super Administrator') }}</option>
                        @endif
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1">{{ __('Menentukan menu dan hak kelola yang bisa diakses user ini.') }}</p>
                </div>

                <div class="flex items-center pt-2 md:pt-6">
                    <label class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3 cursor-pointer w-full hover:bg-slate-100/50 transition">
                        <input type="checkbox" name="is_active" value="1" x-model="isActive" class="rounded text-blue-600 focus:ring-blue-500">
                        <div>
                            <span class="text-xs font-bold text-slate-800 block">{{ __('Akun Aktif (Bisa Login)') }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ __('Centang agar akun diizinkan login ke dalam sistem.') }}</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('employees.show', $employee) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan & Reset Password') }}
            </button>
        </div>
    </form>

</div>
@endsection

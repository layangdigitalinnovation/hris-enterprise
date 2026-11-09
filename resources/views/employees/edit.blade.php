@extends('layouts.app', ['headerTitle' => __('Edit Karyawan: :name', ['name' => $employee->full_name]), 'title' => __('Edit :name', ['name' => $employee->full_name])])

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Data Karyawan') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Perbarui data personal, status kerja, atau pemetaan mesin absensi Solution') }}</p>
        </div>
        <a href="{{ route('employees.show', $employee) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-xs space-y-2">
            <div class="flex items-center gap-2 font-bold text-rose-800">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ __('Mohon periksa dan lengkapi data berikut:') }}</span>
            </div>
            <ul class="list-disc list-inside space-y-1 pl-1 text-rose-700">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('employees.update', $employee) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Personal Info -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">{{ __('Informasi Personal') }}</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Depan *') }}</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border {{ $errors->has('first_name') ? 'border-rose-400 ring-2 ring-rose-500/10 bg-rose-50/20' : 'border-slate-200' }} text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    @error('first_name')
                        <p class="text-[11px] font-medium text-rose-600 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Belakang') }}</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor KTP (NIK)') }}</label>
                    <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border {{ $errors->has('nik') ? 'border-rose-400 ring-2 ring-rose-500/10 bg-rose-50/20' : 'border-slate-200' }} text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    @error('nik')
                        <p class="text-[11px] font-medium text-rose-600 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jenis Kelamin *') }}</label>
                    <select name="gender" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="male" {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}>{{ __('Laki-Laki') }}</option>
                        <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>{{ __('Perempuan') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tempat Lahir') }}</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place', $employee->birth_place) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Lahir') }}</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date', $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Pernikahan *') }}</label>
                    <select name="marital_status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border {{ $errors->has('marital_status') ? 'border-rose-400 ring-2 ring-rose-500/10 bg-rose-50/20' : 'border-slate-200' }} text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="single" {{ old('marital_status', $employee->marital_status) === 'single' ? 'selected' : '' }}>{{ __('Lajang (Single)') }}</option>
                        <option value="married" {{ old('marital_status', $employee->marital_status) === 'married' ? 'selected' : '' }}>{{ __('Menikah (Married)') }}</option>
                        <option value="divorced" {{ old('marital_status', $employee->marital_status) === 'divorced' ? 'selected' : '' }}>{{ __('Cerai (Divorced)') }}</option>
                        <option value="widowed" {{ old('marital_status', $employee->marital_status) === 'widowed' ? 'selected' : '' }}>{{ __('Duda / Janda (Widowed)') }}</option>
                    </select>
                    @error('marital_status')
                        <p class="text-[11px] font-medium text-rose-600 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Agama') }}</label>
                    <input type="text" name="religion" value="{{ old('religion', $employee->religion) }}" placeholder="{{ __('Islam, Kristen, dll') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alamat Email *') }}</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border {{ $errors->has('email') ? 'border-rose-400 ring-2 ring-rose-500/10 bg-rose-50/20' : 'border-slate-200' }} text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    @error('email')
                        <p class="text-[11px] font-medium text-rose-600 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor Telepon / WhatsApp') }}</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alamat Lengkap') }}</label>
                    <textarea name="address" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Job & Assignment -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">{{ __('Penugasan & Status Kerja') }}</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor Induk Karyawan (ID Karyawan) *') }}</label>
                    <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border {{ $errors->has('employee_code') ? 'border-rose-400 ring-2 ring-rose-500/10 bg-rose-50/20' : 'border-slate-200' }} text-slate-800 font-mono font-semibold focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    @error('employee_code')
                        <p class="text-[11px] font-medium text-rose-600 mt-1 flex items-center gap-1">
                            <svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Aktif Karyawan *') }}</label>
                    <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>{{ __('Aktif Bekerja') }}</option>
                        <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>{{ __('Non-Aktif') }}</option>
                        <option value="resigned" {{ old('status', $employee->status) === 'resigned' ? 'selected' : '' }}>{{ __('Resign') }}</option>
                        <option value="terminated" {{ old('status', $employee->status) === 'terminated' ? 'selected' : '' }}>{{ __('Terminasi') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Lokasi Cabang *') }}</label>
                    <select name="branch_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id', $employee->branch_id) == $branch->id ? 'selected' : '' }}>{{ $branch->name }} ({{ $branch->effective_currency }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Mata Uang Penggajian (Currency)') }}</label>
                    <select name="currency" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">{{ __('Otomatis (Ikuti Cabang Penempatan)') }}</option>
                        <option value="IDR" {{ old('currency', $employee->currency) === 'IDR' ? 'selected' : '' }}>IDR - {{ __('Rupiah Indonesia') }} (Rp)</option>
                        <option value="SAR" {{ old('currency', $employee->currency) === 'SAR' ? 'selected' : '' }}>SAR - {{ __('Riyal Arab Saudi') }} (ر.س)</option>
                    </select>
                    <span class="text-[10px] text-slate-500 mt-1 block">{{ __('Mata uang aktif saat ini:') }} <strong class="text-blue-600">{{ $employee->effective_currency }}</strong> @if(!$employee->currency) ({{ __('Mewarisi Cabang') }}) @endif</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Departemen *') }}</label>
                    <select name="department_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jabatan *') }}</label>
                    <select name="position_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" {{ old('position_id', $employee->position_id) == $pos->id ? 'selected' : '' }}>{{ $pos->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Ikatan Kerja *') }}</label>
                    <select name="employment_status" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="permanent" {{ old('employment_status', $employee->employment_status) === 'permanent' ? 'selected' : '' }}>{{ __('Karyawan Tetap (Permanent)') }}</option>
                        <option value="contract" {{ old('employment_status', $employee->employment_status) === 'contract' ? 'selected' : '' }}>{{ __('Kontrak (PKWT)') }}</option>
                        <option value="probation" {{ old('employment_status', $employee->employment_status) === 'probation' ? 'selected' : '' }}>{{ __('Masa Percobaan (Probation)') }}</option>
                        <option value="intern" {{ old('employment_status', $employee->employment_status) === 'intern' ? 'selected' : '' }}>{{ __('Magang (Intern)') }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Bergabung *') }}</label>
                    <input type="date" name="join_date" value="{{ old('join_date', $employee->join_date ? $employee->join_date->format('Y-m-d') : '') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Atasan Langsung (Manager/Supervisor)') }}</label>
                    <select name="manager_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">{{ __('-- Tidak Ada Atasan Langsung --') }}</option>
                        @foreach ($managers as $mgr)
                            <option value="{{ $mgr->id }}" {{ old('manager_id', $employee->manager_id) == $mgr->id ? 'selected' : '' }}>{{ $mgr->full_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Solution Machine Mapping -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">{{ __('Pemetaan Mesin Absensi Solution (Enrollment PIN)') }}</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pilih Mesin Solution Default') }}</label>
                    <select name="device_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">{{ __('-- Pilih Perangkat Mesin --') }}</option>
                        @foreach ($devices as $dev)
                            <option value="{{ $dev->id }}" {{ (isset($currentMapping) && $currentMapping->device_id == $dev->id) ? 'selected' : '' }}>
                                {{ $dev->name }} ({{ $dev->ip_address }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor PIN Mesin Absensi *') }}</label>
                    <input type="text" name="device_pin" value="{{ old('device_pin', $currentMapping->device_pin ?? '') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- 4. Informasi Rekening Bank & NPWP (Opsional) -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4" x-data="{ openBank: {{ $employee->bank_name || $employee->bank_account_no || $employee->npwp ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">4</span>
                    {{ __('Informasi Rekening Bank & Pajak (Opsional)') }}
                </h4>
                <button type="button" @click="openBank = !openBank" class="text-xs font-semibold text-blue-600 hover:text-blue-700 inline-flex items-center gap-1 transition">
                    <span x-show="!openBank" class="inline-flex items-center gap-1">
                        <span class="text-sm font-bold leading-none">+</span>
                        <span>{{ __('Tampilkan') }}</span>
                    </span>
                    <span x-show="openBank" x-cloak class="inline-flex items-center gap-1">
                        <span class="text-sm font-bold leading-none">−</span>
                        <span>{{ __('Sembunyikan') }}</span>
                    </span>
                </button>
            </div>

            <div x-show="openBank" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Bank') }}</label>
                    <input type="text" name="bank_name" value="{{ old('bank_name', $employee->bank_name) }}" placeholder="{{ __('Contoh: BCA / Mandiri / BRI') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor Rekening') }}</label>
                    <input type="text" name="bank_account_no" value="{{ old('bank_account_no', $employee->bank_account_no) }}" placeholder="{{ __('Contoh: 1234567890') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Pemilik Rekening') }}</label>
                    <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder', $employee->bank_account_holder) }}" placeholder="{{ __('Sesuai buku tabungan') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor Pokok Wajib Pajak (NPWP)') }}</label>
                    <input type="text" name="npwp" value="{{ old('npwp', $employee->npwp) }}" placeholder="{{ __('15/16 digit NPWP') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 font-mono placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('employees.show', $employee) }}" class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                {{ __('Simpan Perubahan') }}
            </button>
        </div>
    </form>

</div>
@endsection

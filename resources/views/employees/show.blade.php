@extends('layouts.app', ['headerTitle' => __('Profil Karyawan: :name', ['name' => $employee->full_name]), 'title' => $employee->full_name])

@section('content')
<div class="space-y-6" x-data="{ 
    tab: '{{ request('tab', 'overview') }}'
}">

    <!-- Back button & actions -->
    <div class="flex items-center justify-between">
        @if(auth()->user()->canManageEmployees() || auth()->user()->isManager())
            <a href="{{ route('employees.index') }}" class="text-xs text-slate-500 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Kembali ke Daftar Karyawan') }}
            </a>
        @else
            <a href="{{ route('dashboard') }}" class="text-xs text-slate-500 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Kembali ke Dashboard') }}
            </a>
        @endif

        <div class="flex items-center gap-3">
            @if(auth()->user()->isHrAdmin())
                <a href="{{ route('employees.reset-password.form', $employee) }}" class="px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-xs border border-amber-200/80 shadow-2xs transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    {{ __('Reset Password Akun') }}
                </a>
            @endif

            @if(auth()->user()->canManageEmployees())
                <a href="{{ route('employees.edit', $employee) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('Edit Karyawan') }}
                </a>
            @endif
        </div>
    </div>

    <!-- Employee Header Card -->
    <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs relative overflow-hidden">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
            <div class="flex items-center gap-5">
                <div class="w-18 h-18 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-2xl shadow-md shadow-blue-500/25 shrink-0">
                    {{ strtoupper(substr($employee->first_name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-900 tracking-tight">{{ $employee->full_name }}</h2>
                        <span class="px-3 py-0.5 rounded-full text-xs font-semibold {{ $employee->status === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700 border border-slate-200' }}">
                            {{ __($employee->status === 'active' ? 'Aktif' : ucfirst($employee->status)) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $employee->effective_currency === 'SAR' ? 'bg-emerald-50 text-emerald-700 border border-emerald-300' : 'bg-blue-50 text-blue-700 border border-blue-300' }}">
                            {{ $employee->effective_currency === 'SAR' ? 'SAR (ر.س)' : 'IDR (Rp)' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-600 mt-1 font-medium">{{ $employee->position->title ?? 'Staff' }} · {{ $employee->department->name ?? '-' }}</p>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-slate-500">
                        <span class="font-mono font-semibold text-blue-600">{{ $employee->employee_code }}</span>
                        <span>•</span>
                        <span>{{ $employee->branch->name ?? __('Kantor Pusat') }}</span>
                        <span>•</span>
                        <span>{{ __('Bergabung:') }} {{ $employee->join_date->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Solution Device Pin Quick Pill -->
            @php $mapping = $employee->deviceMappings->first(); @endphp
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-right rtl:text-left min-w-[180px]">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">{{ __('ID Mesin Absensi') }}</span>
                <span class="text-xl font-bold font-mono text-slate-900 block mt-0.5">
                    {{ $mapping ? $mapping->device_pin : __('Belum Dipetakan') }}
                </span>
                <span class="text-[11px] text-blue-600 font-medium block mt-0.5">
                    {{ $mapping && $mapping->device ? $mapping->device->name : 'Solution Fingerprint' }}
                </span>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="mt-6 border-t border-slate-100 pt-4 flex gap-2 overflow-x-auto text-xs font-semibold">
            <button @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
                {{ __('Data Lengkap') }}
            </button>
            <button @click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
                {{ __('Riwayat Presensi Mesin') }}
            </button>
            <button @click="tab = 'leave'" :class="tab === 'leave' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
                {{ __('Hak & Saldo Cuti') }}
            </button>
            @if(auth()->user()->canViewCompensation())
            <button @click="tab = 'compensation'" :class="tab === 'compensation' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
                {{ __('Kompensasi & Payroll') }}
            </button>
            @endif
            <button @click="tab = 'documents'" :class="tab === 'documents' ? 'bg-blue-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
                {{ __('Dokumen & Kontrak') }}
            </button>
        </div>
    </div>

    <!-- TAB 1: OVERVIEW -->
    <div x-show="tab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Personal Details -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">{{ __('Informasi Personal') }}</h4>
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Nomor KTP (NIK)') }}</span>
                    <span class="font-mono font-semibold text-slate-800 text-sm mt-0.5 block">{{ $employee->nik ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Jenis Kelamin') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block capitalize">{{ $employee->gender === 'male' ? __('Laki-Laki') : __('Perempuan') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Tempat & Tanggal Lahir') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">
                        {{ $employee->birth_place ?? '-' }}, {{ $employee->birth_date ? $employee->birth_date->format('d/m/Y') : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Status Pernikahan') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block capitalize">{{ $employee->marital_status }}</span>
                </div>
                <div class="col-span-2">
                    <span class="text-slate-400 block font-medium">{{ __('Alamat Lengkap') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">{{ $employee->address ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Nomor Telepon') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">{{ $employee->phone ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Alamat Email Resmi *') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">{{ $employee->email }}</span>
                </div>
            </div>
        </div>

        <!-- Employment & Statutory -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">{{ __('Pekerjaan & Rekening Bank') }}</h4>
            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Status Ikatan Kerja') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block capitalize">{{ $employee->employment_status }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Atasan Langsung (Manager/Supervisor)') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">{{ $employee->manager ? $employee->manager->full_name : __('Tidak Ada Atasan') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Nama Bank') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">{{ $employee->bank_name ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Nomor Rekening') }}</span>
                    <span class="font-mono font-semibold text-slate-800 text-sm mt-0.5 block">{{ $employee->bank_account_no ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Nama Pemilik Rekening') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block">{{ $employee->bank_account_holder ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Nomor Pokok Wajib Pajak (NPWP)') }}</span>
                    <span class="font-mono font-semibold text-slate-800 text-sm mt-0.5 block">{{ $employee->npwp ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Mata Uang Penggajian') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 font-semibold flex items-center gap-1.5">
                        <span class="font-mono text-xs px-2 py-0.5 rounded bg-slate-100 border border-slate-200 font-bold text-slate-700">{{ $employee->effective_currency }}</span>
                        <span>{{ $employee->effective_currency === 'SAR' ? __('Riyal Arab Saudi') : __('Rupiah Indonesia') }}</span>
                        @if(!$employee->currency)
                            <span class="text-[10px] text-slate-400 font-normal">({{ __('Ikuti Cabang') }})</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <!-- Account & Login Access Details Card -->
        <div class="col-span-1 md:col-span-2 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center font-bold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ __('Akses & Akun Login Sistem HRIS') }}</h4>
                        <p class="text-xs text-slate-500">{{ __('Kredensial dan hak akses karyawan untuk login ke aplikasi') }}</p>
                    </div>
                </div>

                @if(auth()->user()->isHrAdmin())
                    <a href="{{ route('employees.reset-password.form', $employee) }}" class="px-3.5 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 font-semibold text-xs border border-amber-200 transition flex items-center gap-2 shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        {{ $employee->user ? __('Reset Password Karyawan') : __('Buat Akun & Set Password') }}
                    </a>
                @endif
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Status Akun Login') }}</span>
                    @if($employee->user)
                        @if($employee->user->is_active)
                            <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ __('Akun Aktif') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ __('Dinonaktifkan') }}
                            </span>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1.5 mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                            {{ __('Belum Memiliki Akun') }}
                        </span>
                    @endif
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Email Akun (Username)') }}</span>
                    <span class="font-mono font-semibold text-slate-800 text-sm mt-0.5 block truncate">{{ $employee->user ? $employee->user->email : $employee->email }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Role Hak Akses') }}</span>
                    <span class="text-slate-800 text-sm mt-0.5 block font-semibold capitalize">{{ $employee->user ? str_replace('_', ' ', $employee->user->role) : __('Belum diatur') }}</span>
                </div>

                <div>
                    <span class="text-slate-400 block font-medium">{{ __('Terakhir Login') }}</span>
                    <span class="text-slate-700 text-xs mt-0.5 block font-medium">
                        {{ $employee->user && $employee->user->last_login_at ? $employee->user->last_login_at->translatedFormat('d M Y, H:i') : __('Belum pernah login') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- TAB 2: ATTENDANCE HISTORY -->
    <div x-show="tab === 'attendance'" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
        <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3">{{ __('Riwayat Kehadiran Terakhir') }}</h4>
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200 font-semibold">
                        <th class="py-2.5 px-3">{{ __('Tanggal') }}</th>
                        <th class="py-2.5 px-3">{{ __('Shift') }}</th>
                        <th class="py-2.5 px-3">{{ __('Jam Masuk') }}</th>
                        <th class="py-2.5 px-3">{{ __('Jam Pulang') }}</th>
                        <th class="py-2.5 px-3">{{ __('Keterlambatan') }}</th>
                        <th class="py-2.5 px-3">{{ __('Total Jam') }}</th>
                        <th class="py-2.5 px-3">{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentAttendance as $att)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-3 font-semibold text-slate-900">{{ $att->date->translatedFormat('d M Y') }}</td>
                            <td class="py-3 px-3 text-slate-600">{{ __($att->shift->name ?? 'Reguler') }}</td>
                            <td class="py-3 px-3 font-mono font-medium text-slate-800">{{ $att->clock_in ?? '--:--' }}</td>
                            <td class="py-3 px-3 font-mono font-medium text-slate-800">{{ $att->clock_out ?? '--:--' }}</td>
                            <td class="py-3 px-3 text-amber-600 font-mono font-semibold">{{ $att->late_minutes > 0 ? "{$att->late_minutes} " . __('mnt') : '-' }}</td>
                            <td class="py-3 px-3 font-mono text-slate-700">{{ $att->work_hours }} {{ __('jam') }}</td>
                            <td class="py-3 px-3">
                                @php $badge = $att->status_badge; @endphp
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $badge['color'] }}">
                                    {{ __($badge['label']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">{{ __('Belum ada riwayat kehadiran tercatat.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: LEAVE BALANCES -->
    <div x-show="tab === 'leave'" class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        @forelse ($employee->leaveBalances as $bal)
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __($bal->leaveType->name) }} ({{ $bal->year }})</span>
                <div class="mt-4 flex items-baseline justify-between">
                    <div>
                        <span class="text-3xl font-extrabold text-blue-600">{{ $bal->remaining_quota }}</span>
                        <span class="text-xs text-slate-500 font-medium">{{ __('hari tersisa') }}</span>
                    </div>
                    <span class="text-xs text-slate-400">{{ __('Dari :count hari', ['count' => $bal->initial_quota]) }}</span>
                </div>
                <div class="mt-3 w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    @php $usedPercent = $bal->initial_quota > 0 ? ($bal->used_quota / $bal->initial_quota) * 100 : 0; @endphp
                    <div class="bg-blue-600 h-full rounded-full" style="width: {{ $usedPercent }}%"></div>
                </div>
                <span class="text-[11px] text-slate-500 block mt-2">{{ __('Terpakai:') }} {{ $bal->used_quota }} {{ __('hari') }}</span>
            </div>
        @empty
            <div class="col-span-3 text-center py-8 text-sm text-slate-400">
                {{ __('Belum ada alokasi saldo cuti untuk tahun ini.') }}
            </div>
        @endforelse
    </div>

    <!-- TAB 4: COMPENSATION -->
    @if(auth()->user()->canViewCompensation())
    <div x-show="tab === 'compensation'" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h4 class="font-bold text-slate-900 text-base">{{ __('Komponen Kompensasi & Gaji Pokok') }}</h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Rincian standar gaji pokok, tunjangan perusahaan, dan potongan yang berlaku untuk karyawan ini') }}</p>
            </div>
            @if(auth()->user()->canManagePayroll())
                <a href="{{ route('payroll.compensations.edit', ['employee' => $employee, 'redirect_to' => 'profile']) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-2 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    {{ __('Atur Gaji & Kompensasi') }}
                </a>
            @endif
        </div>

        @php
            $curr = $employee->effective_currency;
            $basicComp = $employee->compensations->firstWhere('component.code', 'BASIC');
            $basicAmount = $basicComp ? (float)$basicComp->amount : 0;
            $displayBasic = $basicAmount > 0 ? $basicAmount : ($curr === 'SAR' ? 6000 : 5000000);
            $allowanceComps = $employee->compensations->filter(fn($c) => $c->component && $c->component->type === 'earning' && $c->component->code !== 'BASIC');
            $deductionComps = $employee->compensations->filter(fn($c) => $c->component && $c->component->type === 'deduction');
        @endphp

        <!-- Basic Salary Highlight Card -->
        <div class="p-5 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50/50 border border-blue-200/60 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                    {{ currency_symbol($curr) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-blue-900 block">{{ __('Gaji Pokok Karyawan (Basic Salary)') }}</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono bg-blue-100 text-blue-700">{{ $curr }}</span>
                    </div>
                    <span class="text-2xl font-bold font-mono text-slate-900 mt-0.5 block">
                        {{ format_currency($displayBasic, $curr) }}
                        @if($basicAmount <= 0)
                            <span class="text-xs font-medium text-slate-400 font-sans">({{ __('Standar') }})</span>
                        @endif
                    </span>
                </div>
            </div>
            <div class="text-xs text-slate-500 sm:text-right rtl:sm:text-left">
                <span class="block font-medium text-slate-700">{{ __('Mata Uang Operasional') }}</span>
                <span class="text-emerald-600 font-semibold mt-0.5 inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $curr === 'SAR' ? __('Riyal Arab Saudi (SAR)') : __('Rupiah Indonesia (IDR)') }}
                </span>
            </div>
        </div>

        <!-- Compensations List -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Allowances Box -->
            <div class="space-y-3">
                <h5 class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    {{ __('Tunjangan Khusus / Tambahan') }}
                </h5>
                <div class="space-y-2">
                    @forelse ($allowanceComps as $comp)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs hover:bg-slate-100/50 transition">
                            <div>
                                <span class="font-semibold text-slate-800 block">{{ $comp->component->name }}</span>
                                <span class="text-slate-400 text-[10px] font-mono">{{ $comp->component->code }}</span>
                            </div>
                            <span class="font-mono text-xs font-bold text-emerald-600">
                                {{ format_currency($comp->amount, $curr) }}
                            </span>
                        </div>
                    @empty
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                            {{ __('Mengikuti tarif standar perusahaan dari Master Tunjangan.') }}
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Deductions Box -->
            <div class="space-y-3">
                <h5 class="text-xs font-bold text-rose-800 uppercase tracking-wider flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    {{ __('Potongan Khusus / Tambahan') }}
                </h5>
                <div class="space-y-2">
                    @forelse ($deductionComps as $comp)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs hover:bg-slate-100/50 transition">
                            <div>
                                <span class="font-semibold text-slate-800 block">{{ $comp->component->name }}</span>
                                <span class="text-slate-400 text-[10px] font-mono">{{ $comp->component->code }}</span>
                            </div>
                            <span class="font-mono text-xs font-bold text-rose-600">
                                - {{ format_currency($comp->amount, $curr) }}
                            </span>
                        </div>
                    @empty
                        <div class="p-4 rounded-xl bg-slate-50 border border-dashed border-slate-200 text-center text-xs text-slate-400">
                            {{ __('Mengikuti tarif standar perusahaan (BPJS & keterlambatan).') }}
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- TAB 5: DOCUMENTS -->
    <div x-show="tab === 'documents'" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-6">
        <div class="border-b border-slate-100 pb-3">
            <h4 class="font-bold text-slate-900 text-sm">{{ __('Arsip Dokumen & Kontrak Kerja') }}</h4>
            <p class="text-xs text-slate-500">{{ __('Simpan berkas identitas, kontrak kerja, ijazah, dan dokumen pendukung karyawan') }}</p>
        </div>

        @if(auth()->user()->canManageEmployees())
        <!-- Embedded Form Unggah Dokumen -->
        <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
            <div class="flex items-center gap-2.5 pb-2 border-b border-slate-200/70">
                <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">{{ __('Unggah Dokumen Karyawan Baru') }}</h5>
                    <p class="text-[11px] text-slate-500">{{ __('Pilih berkas identitas atau kontrak kerja dalam format PDF, DOC, atau Gambar (maks 10MB)') }}</p>
                </div>
            </div>

            <form action="{{ route('employees.documents.upload', $employee) }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Judul Dokumen *') }}</label>
                        <input type="text" name="title" required placeholder="{{ __('Contoh: KTP Asli / Kontrak PKWT 2026') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Jenis Dokumen *') }}</label>
                        <select name="document_type" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="KTP">{{ __('KTP / Identitas') }}</option>
                            <option value="NPWP">NPWP</option>
                            <option value="KK">{{ __('Kartu Keluarga (KK)') }}</option>
                            <option value="CONTRACT">{{ __('Kontrak Kerja (PKWT/PKWTT)') }}</option>
                            <option value="CV">Curriculum Vitae (CV)</option>
                            <option value="IJAZAH">{{ __('Ijazah & Transkrip') }}</option>
                            <option value="CERTIFICATE">{{ __('Sertifikasi Keahlian') }}</option>
                            <option value="OTHER">{{ __('Lainnya') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Masa Berlaku (Opsional)') }}</label>
                        <input type="date" name="expiry_date"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div x-data="{ fileName: '' }">
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pilih Berkas *') }}</label>
                        <div class="relative flex items-center">
                            <input type="file" name="document_file" id="document_file" required
                                @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''"
                                class="sr-only">
                            <label for="document_file"
                                class="w-full px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:border-blue-400 focus-within:ring-2 focus-within:ring-blue-500/20 text-slate-600 flex items-center justify-between gap-3 cursor-pointer transition">
                                <span class="truncate text-xs" :class="fileName ? 'text-slate-800 font-medium' : 'text-slate-400'"
                                    x-text="fileName || '{{ __('Belum ada berkas dipilih') }}'">
                                    {{ __('Belum ada berkas dipilih') }}
                                </span>
                                <span class="shrink-0 px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span>{{ __('Pilih Berkas') }}</span>
                                </span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Catatan Tambahan (Opsional)') }}</label>
                        <input type="text" name="notes" placeholder="{{ __('Catatan perihal dokumen...') }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        {{ __('Unggah Dokumen') }}
                    </button>
                </div>
            </form>
        </div>
        @endif

        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200 font-semibold">
                        <th class="py-2.5 px-3">{{ __('Judul Dokumen') }}</th>
                        <th class="py-2.5 px-3">{{ __('Tipe') }}</th>
                        <th class="py-2.5 px-3">{{ __('Nama Berkas') }}</th>
                        <th class="py-2.5 px-3">{{ __('Ukuran') }}</th>
                        <th class="py-2.5 px-3">{{ __('Masa Berlaku') }}</th>
                        <th class="py-2.5 px-3">{{ __('Diunggah') }}</th>
                        <th class="py-2.5 px-3 text-right rtl:text-left">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($employee->documents as $doc)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-3 font-semibold text-slate-900">
                                {{ $doc->title }}
                                @if ($doc->notes)
                                    <span class="block text-[10px] text-slate-500 font-normal">{{ $doc->notes }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $doc->document_type }}
                                </span>
                            </td>
                            <td class="py-3 px-3 font-mono text-slate-600">{{ $doc->file_name ?? basename($doc->file_path) }}</td>
                            <td class="py-3 px-3 text-slate-500 font-mono">{{ round($doc->file_size / 1024, 1) }} KB</td>
                            <td class="py-3 px-3 text-slate-600">
                                {{ $doc->expiry_date ? $doc->expiry_date->format('d/m/Y') : __('Seumur Hidup') }}
                            </td>
                            <td class="py-3 px-3 text-slate-500">{{ $doc->created_at->translatedFormat('d M Y') }}</td>
                            <td class="py-3 px-3 text-right rtl:text-left">
                                <div class="flex items-center justify-end rtl:justify-start gap-2">
                                    <a href="{{ route('employees.documents.download', [$employee, $doc]) }}" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-blue-700 font-semibold text-[11px] transition">
                                        {{ __('Unduh') }}
                                    </a>
                                    <form action="{{ route('employees.documents.delete', [$employee, $doc]) }}" method="POST" onsubmit="return confirm('{{ __('Hapus dokumen ini?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px] transition">
                                            {{ __('Hapus') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">{{ __('Belum ada dokumen yang diunggah untuk karyawan ini.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

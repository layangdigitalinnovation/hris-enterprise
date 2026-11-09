@extends('layouts.app', ['headerTitle' => __('Detail Pengunduran Diri & Clearance'), 'title' => $resignation->resignation_number])

@section('content')
<div class="space-y-6 w-full">

    {{-- 1. Header & Quick Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('resignations.index') }}" class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $resignation->resignation_number }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $resignation->status_badge_classes }}">
                        {{ $resignation->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Diajukan oleh') }} <strong class="text-slate-700">{{ $resignation->employee?->full_name }}</strong> ({{ $resignation->employee?->employee_code }}) • {{ $resignation->submission_date ? $resignation->submission_date->translatedFormat('d F Y') : '-' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            @if(in_array($resignation->status, ['approved', 'completed']))
                <a href="{{ route('resignations.pdf', $resignation) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ __('Cetak Surat Clearance (PDF)') }}</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. Outstanding Loans Alert (Integration with Loan Feature) --}}
    @if($activeLoans->count() > 0)
        <div class="p-4 sm:p-5 rounded-2xl bg-amber-50 border border-amber-200/90 shadow-2xs space-y-2">
            <div class="flex items-center gap-2.5 text-amber-900 font-bold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>{{ __('Peringatan Kewajiban Finansial: Pinjaman / Kasbon Aktif Ditemukan') }}</span>
            </div>
            <p class="text-xs text-amber-800 leading-relaxed">
                {{ __('Karyawan ini masih memiliki :count fasilitas pinjaman aktif yang belum lunas. Mohon pastikan pelunasan saldo piutang atau pemotongan langsung pada settlement gaji terakhir sebelum menyelesaikan clearance.', ['count' => $activeLoans->count()]) }}
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2">
                @foreach($activeLoans as $loan)
                    <div class="p-3 bg-white/80 rounded-xl border border-amber-200 text-xs flex items-center justify-between">
                        <div>
                            <span class="font-mono font-bold text-slate-800">{{ $loan->loan_number }}</span>
                            <span class="text-slate-500 block text-[11px]">{{ $loan->reason }}</span>
                        </div>
                        <div class="text-right">
                            <span class="font-bold text-amber-700">
                                {{ $loan->currency_symbol }} {{ number_format($loan->remaining_amount, $loan->currency === 'SAR' ? 2 : 0, ',', '.') }}
                            </span>
                            <span class="text-[10px] text-slate-400 block">{{ __('Sisa Saldo') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 3. Workflow Step Progress Tracker --}}
    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-2xs">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Step 1: Pengajuan --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    1
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block">{{ __('Pengajuan Resign') }}</span>
                    <span class="text-[11px] text-slate-400">{{ $resignation->submission_date ? $resignation->submission_date->format('d/m/Y') : '-' }}</span>
                </div>
            </div>

            {{-- Step 2: Approval --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl {{ in_array($resignation->status, ['approved', 'completed']) ? 'bg-blue-600 text-white' : ($resignation->status === 'rejected' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    2
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block">{{ __('Persetujuan Manajemen') }}</span>
                    <span class="text-[11px] text-slate-400">
                        @if($resignation->approved_at)
                            {{ $resignation->approved_at->format('d/m/Y') }}
                        @elseif($resignation->status === 'rejected')
                            <span class="text-rose-500 font-semibold">{{ __('Ditolak') }}</span>
                        @else
                            {{ __('Menunggu Review') }}
                        @endif
                    </span>
                </div>
            </div>

            {{-- Step 3: Exit Clearance --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl {{ $resignation->clearance_percentage == 100 ? 'bg-emerald-600 text-white' : ($resignation->clearance_percentage > 0 ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-400') }} flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    3
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block">{{ __('Exit Clearance') }}</span>
                    <span class="text-[11px] {{ $resignation->isFullyCleared() ? 'text-emerald-600 font-semibold' : 'text-slate-400' }}">
                        {{ $resignation->clearance_percentage }}% {{ __('Terselesaikan') }}
                    </span>
                </div>
            </div>

            {{-- Step 4: Selesai Offboarding --}}
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl {{ $resignation->status === 'completed' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    4
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-900 block">{{ __('Selesai Offboarding') }}</span>
                    <span class="text-[11px] text-slate-400">
                        @if($resignation->completed_at)
                            {{ $resignation->completed_at->format('d/m/Y') }}
                        @else
                            {{ __('Menunggu Finalisasi') }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Main Dossier Layout: Left Details + Right Authorization Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Details & Clearance Checklist (2 Cols) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Employee Summary Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>{{ __('Informasi Kepegawaian') }}</span>
                    </h2>
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $resignation->employee?->status_badge_classes ?? 'bg-slate-100 text-slate-600' }}">
                        {{ $resignation->employee?->status_label ?? ($resignation->employee?->status === 'active' ? __('Aktif') : '-') }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Nama Lengkap') }}</span>
                        <span class="font-bold text-slate-800 text-sm">{{ $resignation->employee?->full_name }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('ID Karyawan (NIK)') }}</span>
                        <span class="font-mono font-bold text-slate-800">{{ $resignation->employee?->employee_code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Kantor / Cabang') }}</span>
                        <span class="font-semibold text-slate-800">{{ $resignation->employee?->branch?->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Departemen') }}</span>
                        <span class="font-semibold text-slate-800">{{ __($resignation->employee?->department?->name ?? '-') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Jabatan / Posisi') }}</span>
                        <span class="font-semibold text-slate-800">{{ __($resignation->employee?->position?->title ?? '-') }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Masa Kerja (Tenure)') }}</span>
                        <span class="font-bold text-blue-600">{{ $resignation->employee?->tenure_formatted ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Tanggal Masuk (Join)') }}</span>
                        <span class="font-medium text-slate-700">{{ $resignation->employee?->join_date ? $resignation->employee->join_date->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Hari Terakhir Diminta') }}</span>
                        <span class="font-mono font-medium text-slate-800">{{ $resignation->requested_last_day ? $resignation->requested_last_day->format('d/m/Y') : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Hari Terakhir Disetujui') }}</span>
                        <span class="font-mono font-bold text-emerald-600">{{ $resignation->approved_last_day ? $resignation->approved_last_day->format('d/m/Y') : __('Belum disetujui') }}</span>
                    </div>
                </div>
            </div>

            {{-- Resignation Details & Handover Plan --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>{{ __('Rincian Pengunduran Diri') }}</span>
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 text-xs font-medium">
                        {{ $resignation->reason_label }}
                    </span>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px] mb-1 font-semibold">{{ __('Alasan Lengkap Pengunduran Diri:') }}</span>
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 text-slate-700 leading-relaxed whitespace-pre-line">
                            {{ $resignation->reason_detail }}
                        </div>
                    </div>

                    @if($resignation->handover_notes)
                        <div>
                            <span class="text-slate-400 block text-[11px] mb-1 font-semibold">{{ __('Rencana Transisi & Handover Pekerjaan:') }}</span>
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/70 text-slate-700 leading-relaxed whitespace-pre-line">
                                {{ $resignation->handover_notes }}
                            </div>
                        </div>
                    @endif

                    @if($resignation->resignation_letter_path)
                        <div class="pt-2">
                            <span class="text-slate-400 block text-[11px] mb-1 font-semibold">{{ __('Berkas Surat Resign Bertanda Tangan:') }}</span>
                            <a href="{{ $resignation->resignation_letter_url }}" target="_blank"
                               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold transition">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span>{{ __('Lihat / Unduh Dokumen Lampiran') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Exit Clearance Checklist Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 sm:p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <span>{{ __('Checklist Exit Clearance & Serah Terima') }}</span>
                    </h2>
                    <span class="text-xs font-bold text-blue-600">{{ $resignation->clearance_percentage }}% {{ __('Selesai') }}</span>
                </div>

                {{-- Clearance Form --}}
                <form action="{{ route('resignations.clearance', $resignation) }}" method="POST" class="space-y-4">
                    @csrf
                    @php $canEditClearance = auth()->user()->canManageEmployees() || auth()->user()->isManager(); @endphp

                    {{-- Item 1: Serah Terima Aset --}}
                    <div class="p-3.5 rounded-xl border {{ $resignation->clearance_assets_returned ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200' }} space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="clearance_assets_returned" value="1"
                                   {{ $resignation->clearance_assets_returned ? 'checked' : '' }}
                                   {{ !$canEditClearance ? 'disabled' : '' }}
                                   class="w-4 h-4 mt-0.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800 block">{{ __('1. Serah Terima Aset & Inventaris Perusahaan') }}</span>
                                <span class="text-slate-500 text-[11px] leading-relaxed block">
                                    {{ __('Pengembalian laptop kantor, kartu akses ID Card, kunci kantor, dan inventaris operasional lainnya.') }}
                                </span>
                            </div>
                        </label>
                        @if($canEditClearance)
                            <input type="text" name="clearance_assets_notes" value="{{ old('clearance_assets_notes', $resignation->clearance_assets_notes) }}"
                                   placeholder="{{ __('Catatan kondisi aset yang dikembalikan...') }}"
                                   class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @elseif($resignation->clearance_assets_notes)
                            <p class="text-[11px] text-slate-600 italic pl-7">{{ __('Catatan:') }} {{ $resignation->clearance_assets_notes }}</p>
                        @endif
                    </div>

                    {{-- Item 2: Clearance Finansial & Kasbon --}}
                    <div class="p-3.5 rounded-xl border {{ $resignation->clearance_finance_settled ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200' }} space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="clearance_finance_settled" value="1"
                                   {{ $resignation->clearance_finance_settled ? 'checked' : '' }}
                                   {{ !$canEditClearance ? 'disabled' : '' }}
                                   class="w-4 h-4 mt-0.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800 block">{{ __('2. Rekonsiliasi Finansial & Penyelesaian Kasbon / Pinjaman') }}</span>
                                <span class="text-slate-500 text-[11px] leading-relaxed block">
                                    {{ __('Pelunasan pinjaman karyawan, pengembalian uang muka kasbon operasional, dan perhitungan gaji proporsional.') }}
                                </span>
                            </div>
                        </label>
                        @if($canEditClearance)
                            <input type="text" name="clearance_finance_notes" value="{{ old('clearance_finance_notes', $resignation->clearance_finance_notes) }}"
                                   placeholder="{{ __('Catatan pelunasan kasbon atau pemotongan payroll...') }}"
                                   class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @elseif($resignation->clearance_finance_notes)
                            <p class="text-[11px] text-slate-600 italic pl-7">{{ __('Catatan:') }} {{ $resignation->clearance_finance_notes }}</p>
                        @endif
                    </div>

                    {{-- Item 3: Handover Tugas & Akses --}}
                    <div class="p-3.5 rounded-xl border {{ $resignation->clearance_handover_completed ? 'bg-emerald-50/60 border-emerald-200' : 'bg-slate-50 border-slate-200' }} space-y-2">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="clearance_handover_completed" value="1"
                                   {{ $resignation->clearance_handover_completed ? 'checked' : '' }}
                                   {{ !$canEditClearance ? 'disabled' : '' }}
                                   class="w-4 h-4 mt-0.5 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800 block">{{ __('3. Serah Terima Tugas & Pencabutan Akses Sistem') }}</span>
                                <span class="text-slate-500 text-[11px] leading-relaxed block">
                                    {{ __('Transfer dokumentasi proyek, password/kredensial, pendelegasian wewenang, dan penonaktifan akun.') }}
                                </span>
                            </div>
                        </label>
                        @if($canEditClearance)
                            <input type="text" name="clearance_handover_notes" value="{{ old('clearance_handover_notes', $resignation->clearance_handover_notes) }}"
                                   placeholder="{{ __('Catatan serah terima pekerjaan ke PIC pengganti...') }}"
                                   class="w-full px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-800 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        @elseif($resignation->clearance_handover_notes)
                            <p class="text-[11px] text-slate-600 italic pl-7">{{ __('Catatan:') }} {{ $resignation->clearance_handover_notes }}</p>
                        @endif
                    </div>

                    @if($canEditClearance && $resignation->status !== 'completed' && $resignation->status !== 'rejected')
                        <div class="pt-2 text-right">
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold shadow-xs transition">
                                {{ __('Simpan Pembaruan Clearance') }}
                            </button>
                        </div>
                    @endif
                </form>
            </div>

        </div>

        {{-- Right: Timeline & Actions (1 Col) --}}
        <div class="space-y-6">

            {{-- Timeline & Authorization Card --}}
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-4">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ __('Riwayat & Otorisasi') }}</span>
                </h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Dibuat Pada:') }}</span>
                        <span class="font-medium text-slate-700">{{ $resignation->created_at ? $resignation->created_at->translatedFormat('d F Y H:i') : '-' }}</span>
                    </div>

                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Masa Notice Period:') }}</span>
                        <span class="font-bold {{ $resignation->is_standard_notice ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $resignation->notice_days_count }} {{ __('Hari Kalender') }}
                        </span>
                    </div>

                    @if($resignation->approved_by)
                        <div class="p-3 rounded-xl bg-blue-50/70 border border-blue-100 space-y-1">
                            <span class="text-[10px] uppercase font-bold text-blue-600 block">{{ __('Disetujui Oleh:') }}</span>
                            <span class="font-bold text-slate-800">{{ $resignation->approver?->name }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ $resignation->approved_at ? $resignation->approved_at->translatedFormat('d F Y H:i') : '-' }}</span>
                            @if($resignation->approval_notes)
                                <p class="text-[11px] text-slate-600 pt-1 italic">{{ __('Catatan:') }} {{ $resignation->approval_notes }}</p>
                            @endif
                        </div>
                    @endif

                    @if($resignation->exit_interview_notes)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80 space-y-1">
                            <span class="text-[10px] uppercase font-bold text-slate-600 block">{{ __('Catatan Wawancara Exit:') }}</span>
                            <p class="text-[11px] text-slate-700 leading-relaxed">{{ $resignation->exit_interview_notes }}</p>
                        </div>
                    @endif

                    @if($resignation->rejection_reason)
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-100 space-y-1">
                            <span class="text-[10px] uppercase font-bold text-rose-600 block">{{ __('Alasan Penolakan:') }}</span>
                            <p class="text-[11px] text-rose-800 leading-relaxed">{{ $resignation->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($resignation->completed_by)
                        <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-100 space-y-1">
                            <span class="text-[10px] uppercase font-bold text-emerald-600 block">{{ __('Diselesaikan Oleh:') }}</span>
                            <span class="font-bold text-slate-800">{{ $resignation->completer?->name }}</span>
                            <span class="text-[11px] text-slate-500 block">{{ $resignation->completed_at ? $resignation->completed_at->translatedFormat('d F Y H:i') : '-' }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Managerial Actions Panel --}}
            @if(auth()->user()->canApproveResignation() && $resignation->status === 'pending')
                {{-- Approval Card --}}
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">{{ __('Tindakan Persetujuan') }}</h3>
                    <form action="{{ route('resignations.approve', $resignation) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="space-y-1">
                            <label for="approved_last_day" class="block text-xs font-semibold text-slate-700">{{ __('Tanggal Efektif Hari Terakhir:') }}</label>
                            <input type="date" name="approved_last_day" id="approved_last_day"
                                   value="{{ old('approved_last_day', $resignation->requested_last_day ? $resignation->requested_last_day->format('Y-m-d') : date('Y-m-d')) }}" required
                                   class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono">
                        </div>
                        <div class="space-y-1">
                            <label for="approval_notes" class="block text-xs font-semibold text-slate-700">{{ __('Catatan Persetujuan:') }}</label>
                            <textarea name="approval_notes" id="approval_notes" rows="2" placeholder="{{ __('Instruksi transisi tugas...') }}"
                                      class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs"></textarea>
                        </div>
                        <div class="space-y-1">
                            <label for="exit_interview_notes" class="block text-xs font-semibold text-slate-700">{{ __('Catatan Exit Interview (Opsional):') }}</label>
                            <textarea name="exit_interview_notes" id="exit_interview_notes" rows="2" placeholder="{{ __('Masukan atau umpan balik karyawan...') }}"
                                      class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs"></textarea>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-xs transition">
                            {{ __('Setujui Pengunduran Diri') }}
                        </button>
                    </form>

                    {{-- Rejection Form --}}
                    <div class="pt-3 border-t border-slate-100">
                        <form action="{{ route('resignations.reject', $resignation) }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="text" name="rejection_reason" placeholder="{{ __('Alasan penolakan pengajuan...') }}" required
                                   class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <button type="submit" onclick="return confirm('{{ __('Apakah Anda yakin ingin menolak pengajuan ini?') }}')"
                                    class="w-full py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition">
                                {{ __('Tolak Pengajuan') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- Completion Panel (HR / Super Admin) --}}
            @if(auth()->user()->canManageEmployees() && $resignation->status === 'approved')
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-5 space-y-3.5">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-800">{{ __('Finalisasi Offboarding') }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        {{ __('Menyelesaikan proses offboarding akan secara otomatis menonaktifkan status karyawan (:name) di sistem per hari terakhir kerja (:date).', [
                            'name' => $resignation->employee?->full_name,
                            'date' => $resignation->effective_last_day ? $resignation->effective_last_day->format('d/m/Y') : '-'
                        ]) }}
                    </p>

                    <form action="{{ route('resignations.complete', $resignation) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="space-y-1">
                            <label for="completion_notes" class="block text-xs font-semibold text-slate-700">{{ __('Catatan Akhir HR:') }}</label>
                            <textarea name="completion_notes" id="completion_notes" rows="2" placeholder="{{ __('Penyelesaian dokumen paklaring dan pesangon...') }}"
                                      class="w-full px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs"></textarea>
                        </div>
                        <button type="submit" onclick="return confirm('{{ __('Selesaikan offboarding dan nonaktifkan karyawan ini?') }}')"
                                class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                            {{ __('Selesaikan Offboarding & Nonaktifkan') }}
                        </button>
                    </form>
                </div>
            @endif

            {{-- Employee Cancel Button --}}
            @if($resignation->status === 'pending' && $resignation->employee_id === auth()->user()->employee?->id)
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-2xs p-4">
                    <form action="{{ route('resignations.cancel', $resignation) }}" method="POST">
                        @csrf
                        <button type="submit" onclick="return confirm('{{ __('Apakah Anda yakin ingin membatalkan pengajuan pengunduran diri ini?') }}')"
                                class="w-full py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                            {{ __('Batalkan Permohonan Resign') }}
                        </button>
                    </form>
                </div>
            @endif

        </div>

    </div>

</div>
@endsection


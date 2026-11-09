@extends('layouts.app')

@section('title', __('Detail Pinjaman') . ' ' . $loan->loan_number)

@section('content')
<div class="space-y-6 pb-12">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition-colors">{{ __('Dashboard') }}</a>
                <span>/</span>
                <a href="{{ route('loans.index') }}" class="hover:text-blue-600 transition-colors">{{ __('Pinjaman Karyawan') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ $loan->loan_number }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    {{ $loan->loan_number }}
                </h1>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold border {{ $loan->status_badge_classes }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ in_array($loan->status, ['active', 'approved']) ? 'bg-blue-500 animate-pulse' : ($loan->status === 'completed' ? 'bg-purple-500' : ($loan->status === 'pending' ? 'bg-amber-500' : 'bg-rose-500')) }}"></span>
                    {{ $loan->status_label }}
                </span>
                <span class="text-xs font-mono font-bold px-2.5 py-0.5 rounded-md {{ $loan->currency === 'SAR' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                    {{ $loan->currency === 'SAR' ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Diajukan oleh :name (:code) pada :date.', [
                    'name' => $loan->employee->full_name,
                    'code' => $loan->employee->employee_code,
                    'date' => $loan->created_at->locale(app()->getLocale())->translatedFormat('d F Y, H:i')
                ]) }}
            </p>
        </div>

        <!-- Action Buttons (Non-Modal Dedicated Pages) -->
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            <!-- Back to list -->
            <a href="{{ route('loans.index') }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all">
                {{ __('Kembali') }}
            </a>

            <!-- Print PDF Contract -->
            <a href="{{ route('loans.pdf', $loan) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-2xs transition-all">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Unduh Surat Perjanjian (PDF)') }}</span>
            </a>

            @if($loan->status === 'pending')
                @if(auth()->user()->canApproveLoan())
                    <!-- Dedicated Approval Page Link -->
                    <a href="{{ route('loans.approve.form', $loan) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-600/20 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ __('Setujui Pinjaman') }}</span>
                    </a>

                    <!-- Dedicated Rejection Page Link -->
                    <a href="{{ route('loans.reject.form', $loan) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 text-xs font-semibold transition-all">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>{{ __('Tolak Pinjaman') }}</span>
                    </a>
                @endif

                <a href="{{ route('loans.edit', $loan) }}" class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition-all">
                    {{ __('Ubah') }}
                </a>

                <form action="{{ route('loans.destroy', $loan) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan dan menghapus pengajuan pinjaman ini?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl transition-all" title="{{ __('Hapus Pengajuan') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                    </button>
                </form>
            @elseif($loan->status === 'approved' && auth()->user()->canDisburseLoan())
                <!-- Dedicated Disbursement Page Link -->
                <a href="{{ route('loans.disburse.form', $loan) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ __('Cairkan Dana (Finance)') }}</span>
                </a>
            @elseif(in_array($loan->status, ['active', 'disbursed']) && auth()->user()->canManageLoans())
                <!-- Dedicated Repayment Page Link -->
                <a href="{{ route('loans.repay.form', $loan) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold shadow-md shadow-slate-900/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('Catat Angsuran / Pelunasan') }}</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Metrik Utama Kartu Finansial Pinjaman -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- {{ __('Pokok Pinjaman Disetujui') }} -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">
                {{ __('Pokok Pinjaman Disetujui') }}
            </span>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-bold font-mono text-slate-900">
                    {{ format_currency($loan->effective_principal, $loan->currency) }}
                </h3>
            </div>
            @if($loan->status === 'approved' && $loan->approved_amount != $loan->amount)
                <p class="text-[10px] text-slate-400 mt-1 line-through">
                    {{ __('Diajukan:') }} {{ format_currency($loan->amount, $loan->currency) }}
                </p>
            @else
                <p class="text-[11px] text-slate-500 mt-1">
                    {{ __('Bunga:') }} {{ (float)$loan->interest_rate_percent }}% ({{ $loan->interest_rate_percent > 0 ? __('Margin Perusahaan') : __('Kasbon 0%') }})
                </p>
            @endif
        </div>

        <!-- Sisa Tagihan Berjalan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-600 block mb-1">
                {{ __('Sisa Tagihan Pokok') }}
            </span>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-bold font-mono text-rose-600">
                    {{ format_currency($loan->remaining_amount, $loan->currency) }}
                </h3>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                {{ __('Total Terbayar:') }} <strong class="text-slate-800">{{ format_currency($loan->total_repaid_amount, $loan->currency) }}</strong>
            </p>
        </div>

        <!-- {{ __('Cicilan Bulanan') }} & Tenor -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-600 block mb-1">
                {{ __('Cicilan Bulanan') }}
            </span>
            <div class="flex items-baseline gap-2">
                <h3 class="text-2xl font-bold font-mono text-blue-600">
                    {{ format_currency($loan->monthly_installment, $loan->currency) }}
                </h3>
                <span class="text-xs text-slate-500">{{ __('/ bulan') }}</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-1">
                {{ __('Tenor: :count Bulan (:paid lunas)', ['count' => $loan->tenor_months, 'paid' => $loan->paid_installments_count]) }}
            </p>
        </div>

        <!-- Progress Pelunasan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    {{ __('Progres Pelunasan') }}
                </span>
                <span class="text-xs font-bold font-mono {{ $loan->repayment_progress_percentage == 100 ? 'text-purple-600' : 'text-blue-600' }}">
                    {{ $loan->repayment_progress_percentage }}%
                </span>
            </div>
            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden mt-3 mb-2">
                <div class="h-full bg-gradient-to-r {{ $loan->repayment_progress_percentage == 100 ? 'from-purple-500 to-indigo-600' : 'from-blue-500 to-indigo-600' }} rounded-full" style="width: {{ $loan->repayment_progress_percentage }}%"></div>
            </div>
            <p class="text-[11px] text-slate-500">
                {{ __(':paid dari :total angsuran selesai', ['paid' => $loan->paid_installments_count, 'total' => $loan->tenor_months]) }}
            </p>
        </div>
    </div>

    <!-- Main Content Layout (2 Columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Jadwal Cicilan & Angsuran (2 Columns Wide) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>{{ __('Jadwal Angsuran & Riwayat Pembayaran') }}</h2>
                        <p class="text-xs text-slate-500 mt-0.5">{{ __('Rincian tanggal jatuh tempo, nominal cicilan, serta konfirmasi status pelunasan slip gaji atau transfer.') }}</p>
                    </div>

                    @if(in_array($loan->status, ['active', 'disbursed']) && auth()->user()->canManageLoans())
                        <a href="{{ route('loans.repay.form', $loan) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-semibold transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>{{ __('Catat Angsuran') }}</span>
                        </a>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 border-b border-slate-200 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                <th class="py-3 px-4 text-center w-12">{{ __('Ke-') }}</th>
                                <th class="py-3 px-4">{{ __('Jatuh Tempo') }}</th>
                                <th class="py-3 px-4">{{ __('Nominal Angsuran') }}</th>
                                <th class="py-3 px-4">{{ __('Status') }}</th>
                                <th class="py-3 px-4">{{ __('Tgl & Metode Bayar') }}</th>
                                <th class="py-3 px-4">{{ __('Referensi / Slip') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-sans">
                            @forelse($loan->installments as $inst)
                                <tr class="hover:bg-slate-50/50 transition-colors {{ $inst->status === 'paid' ? 'bg-emerald-50/20' : '' }}">
                                    <!-- Installment Number -->
                                    <td class="py-3 px-4 text-center font-bold text-slate-700">
                                        {{ $inst->installment_number }}
                                    </td>

                                    <!-- Due Date -->
                                    <td class="py-3 px-4 font-medium text-slate-800">
                                        <span class="inline-block" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                                            {{ $inst->due_date ? $inst->due_date->locale(app()->getLocale())->translatedFormat('d F Y') : '-' }}
                                        </span>
                                    </td>

                                    <!-- Amount -->
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">
                                        {{ format_currency($inst->amount, $loan->currency) }}
                                    </td>

                                    <!-- Status Pill -->
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $inst->status_badge_classes }}">
                                            @if($inst->status === 'paid')
                                                <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @else
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            @endif
                                            {{ $inst->status_label }}
                                        </span>
                                    </td>

                                    <!-- Paid Date & Method -->
                                    <td class="py-3 px-4 text-slate-600">
                                        @if($inst->paid_at)
                                            <span class="font-medium text-slate-800 block" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                                                {{ $inst->paid_at->locale(app()->getLocale())->translatedFormat('d F Y') }}
                                            </span>
                                            <span class="text-[10px] text-slate-400">
                                                {{ $inst->payment_method_label }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 italic">{{ __('Belum dibayar') }}</span>
                                        @endif
                                    </td>

                                    <!-- Reference / Slip -->
                                    <td class="py-3 px-4">
                                        @if($inst->payrollPeriod)
<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-semibold">
                                                Slip: {{ $inst->payrollPeriod->name }}
                                            </span>
                                        @elseif($inst->payment_reference)
<span class="font-mono text-[11px] text-slate-700">
                                                Ref: {{ $inst->payment_reference }}
                                            </span>
                                        @elseif($inst->proof_file)
<a href="{{ Storage::url($inst->proof_file) }}" target="_blank" class="text-blue-600 hover:underline text-[11px] font-semibold flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                                </svg>{{ __('Bukti Bayar') }}</a>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400 text-xs">{{ __('Jadwal cicilan belum di-generate.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Catatan & Alasan Peminjaman -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>{{ __('Tujuan & Alasan Permohonan Pinjaman') }}</h3>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 leading-relaxed">
                    {{ $loan->reason }}
                </div>

                @if($loan->approval_notes)
<div class="pt-2">
                        <span class="text-[11px] font-bold text-emerald-700 block mb-1">{{ __('Catatan Persetujuan Manajemen:') }}</span>
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100 text-xs text-emerald-900 leading-relaxed">
                            {{ $loan->approval_notes }}
                        </div>
                    </div>
                @endif

                @if($loan->rejection_reason)
<div class="pt-2">
                        <span class="text-[11px] font-bold text-rose-700 block mb-1">{{ __('Alasan Penolakan:') }}</span>
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-100 text-xs text-rose-900 leading-relaxed">
                            {{ $loan->rejection_reason }}
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Informasi Pegawai & Berkas Lampiran -->
        <div class="space-y-6">
            <!-- Kartu Pegawai Peminjam -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Informasi Pegawai') }}</h3>

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-xs">
                        {{ substr($loan->employee->first_name ?? 'E', 0, 1) }}{{ substr($loan->employee->last_name ?? '', 0, 1) }}
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">{{ $loan->employee->full_name }}</h4>
                        <p class="text-xs text-slate-500 font-mono">{{ $loan->employee->employee_code }}</p>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Departemen') }}</span>
                        <span class="font-semibold text-slate-700">{{ __($loan->employee->department->name ?? '-') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Jabatan') }}</span>
                        <span class="font-semibold text-slate-700">{{ __($loan->employee->position->title ?? ($loan->employee->position->name ?? '-')) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Lokasi / Cabang') }}</span>
                        <span class="font-semibold text-slate-700">{{ __($loan->branch->name ?? '-') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Skema Pengembalian') }}</span>
                        <span class="font-semibold {{ $loan->payroll_deduction ? 'text-indigo-600' : 'text-slate-700' }}">
                            {{ $loan->payroll_deduction ? __('Potong Slip Gaji') : __('Transfer Mandiri') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Berkas Lampiran & Dokumen -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Dokumen & Berkas Lampiran') }}</h3>

                <!-- Dokumen Surat Permohonan Karyawan -->
                @if($loan->attachment_file)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xs font-semibold text-slate-800 truncate">{{ __('Berkas Permohonan Pinjaman') }}</span>
                        </div>
                        <a href="{{ Storage::url($loan->attachment_file) }}" target="_blank" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-200 text-blue-600 text-xs font-semibold rounded-lg shrink-0 transition-all">{{ __('Buka') }}</a>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">{{ __('Tidak ada berkas permohonan yang dilampirkan.') }}</p>
                @endif

                <!-- Dokumen Bukti Transfer Pencairan Keuangan -->
                @if($loan->disbursement_proof)
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                        <div class="flex items-center gap-2 overflow-hidden">
                            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <span class="text-xs font-semibold text-emerald-900 block truncate">{{ __('Bukti Transfer Pencairan') }}</span>
                                <span class="text-[10px] text-emerald-700">{{ __('Oleh Finance pada :date', ['date' => $loan->disbursed_at ? $loan->disbursed_at->locale(app()->getLocale())->translatedFormat('d M Y') : '-']) }}</span>
                            </div>
                        </div>
                        <a href="{{ Storage::url($loan->disbursement_proof) }}" target="_blank" class="px-2.5 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 text-emerald-700 text-xs font-semibold rounded-lg shrink-0 transition-all">{{ __('Buka') }}</a>
                    </div>
                @endif
            </div>

            <!-- Jejak Audit / Status Timeline -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ __('Jejak Aktivitas & Otorisasi') }}</h3>

                <div class="space-y-3 text-xs">
                    <!-- Pengajuan -->
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                            1
                        </div>
                        <div>
                            <span class="font-bold text-slate-800 block">{{ __('Pengajuan Dibuat') }}</span>
                            <span class="text-[11px] text-slate-400">{{ $loan->created_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <!-- Persetujuan -->
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-full {{ $loan->approved_at ? 'bg-emerald-50 text-emerald-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                            2
                        </div>
                        <div>
                            <span class="font-bold {{ $loan->approved_at ? 'text-slate-800' : 'text-slate-400' }} block">{{ __('Persetujuan Manajemen') }}</span>
                            @if($loan->approved_at)
                                <span class="text-[11px] text-slate-400">
                                    {{ __('Disetujui oleh :name pada :date', [
                                        'name' => $loan->approver->name ?? __('Manajer'),
                                        'date' => $loan->approved_at->locale(app()->getLocale())->translatedFormat('d M Y, H:i')
                                    ]) }}
                                </span>
                            @else
                                <span class="text-[11px] text-slate-400">{{ __('Menunggu persetujuan atasan/HR') }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Pencairan Dana -->
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-full {{ $loan->disbursed_at ? 'bg-blue-50 text-blue-600' : 'bg-slate-100 text-slate-400' }} flex items-center justify-center shrink-0 text-[10px] font-bold mt-0.5">
                            3
                        </div>
                        <div>
                            <span class="font-bold {{ $loan->disbursed_at ? 'text-slate-800' : 'text-slate-400' }} block">{{ __('Pencairan Dana Kasir / Finance') }}</span>
                            @if($loan->disbursed_at)
                                <span class="text-[11px] text-slate-400">
                                    {{ __('Dicairkan oleh :name pada :date', [
                                        'name' => $loan->disburser->name ?? __('Finance'),
                                        'date' => $loan->disbursed_at->locale(app()->getLocale())->translatedFormat('d M Y')
                                    ]) }}
                                </span>
                            @else
                                <span class="text-[11px] text-slate-400">{{ __('Belum dicairkan') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


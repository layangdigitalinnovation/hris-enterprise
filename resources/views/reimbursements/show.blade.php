@extends('layouts.app')

@section('title', 'Klaim Reimbursement: ' . $reimbursement->claim_number)

@section('content')
<div class="space-y-6 pb-12" x-data="{
    receiptPreviewModal: false
}">
    <!-- Top Breadcrumb & Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Reimbursement') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold font-mono">{{ $reimbursement->claim_number }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5 flex-wrap">
                <span>{{ $reimbursement->title }}</span>
                <span class="px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold {{ $reimbursement->currency === 'SAR' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-blue-50 text-blue-700 border border-blue-200/60' }}">
                    {{ $reimbursement->currency === 'SAR' ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $reimbursement->status_badge_classes }}">
                    {{ $reimbursement->status_label }}
                </span>
            </h1>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap shrink-0">
            <!-- Cetak Slip Voucher PDF -->
            <a href="{{ route('reimbursements.pdf', $reimbursement) }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Cetak Slip (PDF)') }}</span>
            </a>

            <!-- Tombol Pencairan Dana oleh Finance -->
            @if($reimbursement->isApproved() && auth()->user()->canPayReimbursement())
                <a href="{{ route('reimbursements.pay', $reimbursement) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-md shadow-emerald-500/20 whitespace-nowrap transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>{{ __('Cairkan / Bayar Sekarang') }}</span>
                </a>
            @endif

            <!-- Tombol Persetujuan oleh HR / Manager -->
            @if($reimbursement->isPending() && auth()->user()->canApproveReimbursement())
                <a href="{{ route('reimbursements.reject.form', $reimbursement) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                    <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>{{ __('Tolak Klaim') }}</span>
                </a>

                <a href="{{ route('reimbursements.approve.form', $reimbursement) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 whitespace-nowrap transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ __('Setujui Klaim') }}</span>
                </a>
            @endif

            <!-- Edit & Hapus (Hanya saat status Pending) -->
            @if($reimbursement->canBeEdited())
                <a href="{{ route('reimbursements.edit', $reimbursement) }}" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                    <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    <span>{{ __('Edit Klaim') }}</span>
                </a>

                <form action="{{ route('reimbursements.destroy', $reimbursement) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan klaim ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-rose-200 bg-white hover:bg-rose-50 text-rose-600 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span>{{ __('Batalkan') }}</span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Timeline Progress Klaim -->
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm">
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">{{ __('Status Alur Persetujuan & Pencairan') }}</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
            <!-- Step 1: Diajukan -->
            <div class="flex items-start gap-3.5">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-500/20 font-bold text-xs">
                    1
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ __('Diajukan oleh Karyawan') }}</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        {{ $reimbursement->employee?->full_name }}
                    </div>
                    <div class="text-[10px] text-slate-400 font-mono mt-1">
                        {{ $reimbursement->created_at->translatedFormat('d M Y, H:i') }}
                    </div>
                </div>
            </div>

            <!-- Step 2: Persetujuan Manager / HR -->
            <div class="flex items-start gap-3.5">
                @if($reimbursement->isApproved() || $reimbursement->isPaid())
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-500/20 font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-700">{{ __('Disetujui Manajemen') }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            {{ __('Oleh:') }} {{ $reimbursement->approver?->name ?? __('HR/Manager') }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono mt-1">
                            {{ $reimbursement->approved_at?->translatedFormat('d M Y, H:i') }}
                        </div>
                    </div>
                @elseif($reimbursement->isRejected())
                    <div class="w-9 h-9 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-rose-500/20 font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-rose-700">{{ __('Ditolak') }}</div>
                        <div class="text-[11px] text-rose-600 mt-0.5">
                            {{ __('Alasan:') }} {{ $reimbursement->rejection_reason }}
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono mt-1">
                            {{ $reimbursement->approved_at?->translatedFormat('d M Y, H:i') }}
                        </div>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-xs">
                        2
                    </div>
                    <div>
                        <div class="text-xs font-bold text-amber-700">{{ __('Menunggu Approval') }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Menunggu tinjauan Manager / HR Admin') }}</div>
                    </div>
                @endif
            </div>

            <!-- Step 3: Pencairan Dana oleh Finance -->
            <div class="flex items-start gap-3.5">
                @if($reimbursement->isPaid())
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm shadow-emerald-500/20 font-bold text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-emerald-700">{{ __('Telah Dibayar (Lunas)') }}</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            {{ __('Via') }} {{ __($reimbursement->payment_method === 'cash' ? 'Tunai / Kas' : ($reimbursement->payment_method === 'petty_cash' ? 'Kas Kecil (Petty Cash)' : 'Transfer Bank')) }}
                            @if($reimbursement->payment_reference)
                                ({{ __('Ref:') }} {{ $reimbursement->payment_reference }})
                            @endif
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono mt-1">
                            {{ $reimbursement->paid_at?->translatedFormat('d M Y, H:i') }}
                        </div>
                    </div>
                @elseif($reimbursement->isApproved())
                    <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0 font-bold text-xs animate-pulse">
                        3
                    </div>
                    <div>
                        <div class="text-xs font-bold text-blue-700">{{ __('Siap Dicairkan (Finance)') }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Menunggu proses transfer kasir / Finance') }}</div>
                    </div>
                @else
                    <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center shrink-0 font-bold text-xs">
                        3
                    </div>
                    <div>
                        <div class="text-xs font-bold text-slate-400">{{ __('Pencairan Dana') }}</div>
                        <div class="text-[11px] text-slate-300 mt-0.5">{{ __('Dilakukan setelah klaim disetujui') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Informasi Detail Klaim (Grid 2 Kolom) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Kolom Kiri: Rincian Finansial & Pengeluaran -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Card Rincian Finansial -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">{{ __('Rincian Finansial & Pengeluaran') }}</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                        <div class="text-[11px] text-slate-400">{{ __('Nominal Diajukan') }}</div>
                        <div class="text-xl font-bold font-mono text-slate-900 mt-1">
                            {{ format_currency($reimbursement->amount, $reimbursement->currency) }}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl {{ $reimbursement->isApproved() || $reimbursement->isPaid() ? 'bg-emerald-50 border border-emerald-100' : 'bg-slate-50 border border-slate-100' }}">
                        <div class="text-[11px] {{ $reimbursement->isApproved() || $reimbursement->isPaid() ? 'text-emerald-700' : 'text-slate-400' }}">
                            {{ __('Nominal Disetujui') }}
                        </div>
                        <div class="text-xl font-bold font-mono {{ $reimbursement->isApproved() || $reimbursement->isPaid() ? 'text-emerald-700' : 'text-slate-400' }} mt-1">
                            {{ format_currency($reimbursement->approved_amount ?? $reimbursement->amount, $reimbursement->currency) }}
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100 text-xs">
                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Kategori Reimbursement:') }}</span>
                        <span class="font-bold text-slate-800">
                            {{ $reimbursement->category?->name }} ({{ $reimbursement->category?->code }})
                        </span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Tanggal Pengeluaran / Nota:') }}</span>
                        <span class="font-semibold text-slate-800 font-mono">
                            {{ $reimbursement->claim_date->translatedFormat('d F Y') }}
                        </span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <span class="text-slate-400">{{ __('Cabang Proyek:') }}</span>
                        <span class="font-semibold text-slate-800">
                            {{ $reimbursement->branch?->name ?? 'Kantor Pusat' }}
                        </span>
                    </div>

                    @if($reimbursement->description)
<div class="py-2.5">
                            <span class="text-slate-400 block mb-1">{{ __('Keterangan Tambahan:') }}</span>
                            <div class="p-3 rounded-xl bg-slate-50 text-slate-700 text-xs leading-relaxed">
                                {{ $reimbursement->description }}
                            </div>
                        </div>
                    @endif

                    @if($reimbursement->notes)
<div class="py-2.5">
                            <span class="text-slate-400 block mb-1">{{ __('Catatan Manajemen / Finance:') }}</span>
                            <div class="p-3 rounded-xl bg-blue-50/60 border border-blue-100 text-blue-900 text-xs leading-relaxed">
                                {{ $reimbursement->notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Card Bukti Pembayaran Transfer (Jika status Paid) -->
            @if($reimbursement->isPaid())
                <div class="bg-white rounded-2xl border border-emerald-200/80 p-6 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 text-emerald-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h3 class="text-xs font-bold uppercase tracking-wider">{{ __('Realisasi Pembayaran / Transfer Selesai') }}</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Metode Bayar') }}</div>
                            <div class="font-bold text-slate-800 mt-0.5">{{ __($reimbursement->payment_method === 'cash' ? 'Tunai / Kas' : ($reimbursement->payment_method === 'petty_cash' ? 'Kas Kecil (Petty Cash)' : 'Transfer Bank')) }}</div>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="text-[10px] uppercase font-bold text-slate-400">{{ __('Nomor Referensi Transfer') }}</div>
                            <div class="font-bold font-mono text-slate-800 mt-0.5">{{ $reimbursement->payment_reference ?? '-' }}</div>
                        </div>
                    </div>

                    @if($reimbursement->payment_receipt_url)
<div class="pt-2">
                            <a href="{{ $reimbursement->payment_receipt_url }}" target="_blank" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-xs font-semibold border border-emerald-200 transition-all">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>{{ __('Lihat Bukti Transfer Bank') }}</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Kolom Kanan: Pegawai, Rekening, dan Bukti Kuitansi -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Profil Pegawai & Rekening Bank -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">{{ __('Karyawan & Rekening Pencairan') }}</h3>

                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white font-bold flex items-center justify-center shrink-0 text-base shadow-sm">
                        {{ strtoupper(substr($reimbursement->employee?->first_name ?? 'E', 0, 1)) }}{{ strtoupper(substr($reimbursement->employee?->last_name ?? 'M', 0, 1)) }}
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">{{ $reimbursement->employee?->full_name }}</div>
                        <div class="text-xs text-slate-500 font-mono">
                            {{ $reimbursement->employee?->employee_code }} • {{ $reimbursement->employee?->department?->name ?? 'Dept' }}
                        </div>
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/60 space-y-2 text-xs">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Rekening Transfer Payroll') }}</div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('Bank:') }}</span>
                        <span class="font-bold text-slate-800">{{ $reimbursement->employee?->bank_name ?? 'BCA' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('Nomor Rekening:') }}</span>
                        <span class="font-bold font-mono text-slate-900">{{ $reimbursement->employee?->bank_account_no ?? __('Belum Diisi') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('Atas Nama:') }}</span>
                        <span class="font-semibold text-slate-800">{{ $reimbursement->employee?->bank_account_holder ?? $reimbursement->employee?->full_name }}</span>
                    </div>
                </div>
            </div>

            <!-- {{ __('Bukti Nota / Kuitansi') }} -->
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Lampiran Bukti Nota / Kwitansi') }}</h3>
                    @if($reimbursement->receipt_url)
<a href="{{ $reimbursement->receipt_url }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline">{{ __('Buka Berkas Penuh ↗') }}</a>
                    @endif
                </div>

                @if($reimbursement->receipt_url)
                    @php
                        $ext = strtolower(pathinfo($reimbursement->receipt_path, PATHINFO_EXTENSION));
                    @endphp

                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                        <div class="rounded-xl overflow-hidden border border-slate-200 max-h-80 bg-slate-100 flex items-center justify-center cursor-pointer"
                             @click="receiptPreviewModal = true">
                            <img src="{{ $reimbursement->receipt_url }}" alt="Bukti Nota" class="w-full object-contain hover:scale-105 transition-transform duration-300">
                        </div>
                    @else
                        <!-- File Dokumen PDF -->
                        <div class="p-6 rounded-xl bg-slate-50 border border-slate-200 text-center space-y-3">
                            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-800">{{ __('Berkas Dokumen PDF') }}</div>
                            <a href="{{ $reimbursement->receipt_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 shadow-xs transition-all">
                                <span>{{ __('Unduh & Pratinjau PDF') }}</span>
                            </a>
                        </div>
                    @endif
                @else
                    <div class="p-8 text-center text-xs text-slate-400 bg-slate-50 rounded-xl border border-dashed border-slate-200">{{ __('Tidak ada berkas bukti nota yang diunggah.') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


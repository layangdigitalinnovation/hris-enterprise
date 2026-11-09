@extends('layouts.app')

@section('title', __('Tolak Pinjaman') . ': ' . $loan->loan_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-6 pb-12">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('loans.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Pinjaman Karyawan') }}</a>
                <span>/</span>
                <a href="{{ route('loans.show', $loan) }}" class="hover:text-blue-600 font-medium font-mono">{{ $loan->loan_number }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tolak Pengajuan') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-rose-600 to-red-600 text-white rounded-xl shadow-md shadow-rose-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>{{ __('Tolak Pengajuan Pinjaman') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Berikan alasan penolakan secara jelas dan transparan agar pemohon memahami pertimbangan manajemen.') }}</p>
        </div>

        <a href="{{ route('loans.show', $loan) }}" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Detail') }}</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Terdapat kesalahan:') }}</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Summary Box -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-rose-950 text-white rounded-3xl p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-4">
            <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Nomor Pinjaman') }}</span>
                <div class="font-mono font-bold text-lg text-white mt-0.5">{{ $loan->loan_number }}</div>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-400/30">{{ __('Menunggu Otorisasi') }}</span>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Pegawai Pemohon') }}</span>
                <span class="text-sm font-bold text-white block mt-0.5">{{ $loan->employee->full_name }}</span>
                <span class="text-[11px] text-slate-300 font-mono">{{ $loan->employee->employee_code }} • {{ $loan->employee->department->name ?? 'Dept' }}</span>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block uppercase font-medium">{{ __('Nominal Yang Diajukan') }}</span>
                <span class="text-xl font-bold font-mono text-rose-400 block mt-0.5">
                    {{ format_currency($loan->amount, $loan->currency) }}
                </span>
                <span class="text-[11px] text-slate-300">{{ __('Tenor:') }} {{ $loan->tenor_months }} {{ __('Bulan') }}</span>
            </div>
        </div>

        <div class="bg-white/5 rounded-2xl p-4 border border-white/10 space-y-1">
            <span class="text-[10px] text-slate-400 block uppercase font-semibold">{{ __('Keperluan / Alasan Pinjaman:') }}</span>
            <p class="text-xs text-slate-200 mt-0.5 leading-relaxed">{{ $loan->reason }}</p>
        </div>
    </div>

    <!-- Rejection Form -->
    <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-sm space-y-6">
        <form action="{{ route('loans.reject', $loan) }}" method="POST" class="space-y-6">
            @csrf

            <div class="space-y-1.5">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    {{ __('Alasan Penolakan Pinjaman') }} <span class="text-rose-500">*</span>
                </label>
                <textarea name="rejection_reason" rows="4" required class="w-full text-xs rounded-xl border border-slate-200 p-3.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Tuliskan secara jelas alasan pinjaman tidak dapat disetujui (misal: melebihi rasio cicilan maksimal gaji 30%, masa kerja belum mencukupi syarat, atau dokumen pendukung belum lengkap)...') }}">{{ old('rejection_reason') }}</textarea>
                <span class="text-[11px] text-slate-400">{{ __('Alasan ini akan tersimpan pada riwayat dan dapat dilihat oleh pemohon pinjaman.') }}</span>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <a href="{{ route('loans.show', $loan) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-md shadow-rose-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Konfirmasi {{ __('Tolak Pinjaman') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', __('Portal Portofolio & BEP Investor'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <span>{{ __('Portal Investor') }}</span>
                <span>/</span>
                <span class="text-slate-700 font-medium">{{ __('Dashboard Portofolio') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-emerald-600 to-teal-600 text-white rounded-xl shadow-md shadow-emerald-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </span>{{ __('Portofolio & Realisasi Bagi Hasil Saya') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-1 max-w-2xl">
                {{ __('Selamat datang,') }} <strong>{{ $investor->name }}</strong>. {{ __('Pantau realisasi keuntungan, pengembalian pokok modal (BEP), dan imbalan khusus secara transparan.') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <a href="{{ route('investors.pdf', $investor) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold shadow-xs whitespace-nowrap transition-all">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span>{{ __('Unduh Portofolio (PDF)') }}</span>
            </a>
            @if(auth()->user()->canManageInvestors())
                <a href="{{ route('investors.show', $investor) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-xs whitespace-nowrap transition-all">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>{{ __('Mode Admin') }}</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Big Visual BEP Hero Banner -->
    <div class="bg-gradient-to-br {{ $investor->is_bep_achieved ? 'from-emerald-900 via-teal-900 to-slate-900' : 'from-slate-900 via-blue-950 to-slate-900' }} text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-b border-white/10 pb-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-white/10 text-emerald-300 backdrop-blur-sm border border-white/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        {{ __('Status Investasi:') }} {{ $investor->status === 'active' ? __('Aktif') : ($investor->status === 'completed' ? __('Selesai') : __('Ditangguhkan')) }}
                    </span>
                    <h2 class="text-2xl font-black mt-2 font-mono tracking-tight">
                        {{ format_currency($investor->initial_investment, $investor->currency) }}
                    </h2>
                    <p class="text-xs text-slate-300 mt-0.5">
                        {{ __('Modal Disetor') }} ke Proyek: <strong class="text-white">{{ $investor->branch ? $investor->branch->name : __('Operasional Perusahaan') }}</strong>
                    </p>
                </div>

                <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-sm flex items-center gap-4">
                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold block">{{ __('Capaian Titik Impas (BEP)') }}</span>
                        <span class="text-3xl font-extrabold font-mono text-emerald-400">{{ $investor->bep_progress_percentage }}%</span>
                    </div>
                    <div class="w-14 h-14 rounded-full border-4 border-white/10 border-t-emerald-400 flex items-center justify-center font-bold text-xs">
                        {{ $investor->is_bep_achieved ? '100%' : round($investor->bep_progress_percentage) . '%' }}
                    </div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs text-slate-300 font-mono">
                    <span>{{ __('Dana Diterima:') }}<strong class="text-emerald-300">{{ format_currency($investor->total_return_for_bep, $investor->currency) }}</strong></span>
                    <span>{{ __('Sisa Menuju BEP:') }}<strong class="{{ $investor->is_bep_achieved ? 'text-emerald-300' : 'text-amber-300' }}">{{ format_currency($investor->bep_remaining, $investor->currency) }}</strong></span>
                </div>
                <div class="w-full bg-white/10 rounded-full h-4 p-0.5 overflow-hidden backdrop-blur-sm">
                    <div class="h-3 rounded-full {{ $investor->is_bep_achieved ? 'bg-gradient-to-r from-emerald-400 to-teal-300' : 'bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-400' }} transition-all duration-700 shadow-md" style="width: {{ min(100, $investor->bep_progress_percentage) }}%"></div>
                </div>
            </div>

            <!-- 3 Highlights -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">{{ __('Porsi Bagi Hasil') }}</span>
                    <span class="text-base font-bold text-white font-mono">{{ number_format($investor->profit_share_percentage, 2) }}%</span>
                    <span class="text-[10px] text-slate-300 block mt-0.5">{{ __('Metode :scheme', ['scheme' => $investor->profit_share_scheme === 'net_profit' ? __('Laba Bersih') : ($investor->profit_share_scheme === 'gross_revenue' ? __('Omset') : __('Tetap'))]) }}</span>
                </div>

                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">{{ __('Gaji Khusus Bulanan') }}</span>
                    @if($investor->has_fixed_stipend)
                        <span class="text-base font-bold text-purple-300 font-mono">{{ format_currency($investor->fixed_stipend_amount, $investor->currency) }}</span>
                        <span class="text-[10px] text-slate-300 block mt-0.5">{{ __('Jatuh tempo tiap tanggal :day', ['day' => $investor->stipend_payment_day]) }}</span>
                    @else
                        <span class="text-sm font-semibold text-slate-400">{{ __('Tidak Ada Gaji Khusus') }}</span>
                    @endif
                </div>

                <div class="bg-white/5 rounded-xl p-3 border border-white/10">
                    <span class="text-[10px] text-slate-400 uppercase font-bold block">{{ __('Total Seluruh Dana Diterima') }}</span>
                    <span class="text-base font-bold text-emerald-400 font-mono">{{ format_currency($investor->total_distributions_paid, $investor->currency) }}</span>
                    <span class="text-[10px] text-slate-300 block mt-0.5">{{ __('Dividen + Gaji + Pengembalian Pokok') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Mini Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Bagi Hasil / Dividen Paid') }}</span>
            <div class="mt-2 text-xl font-bold text-emerald-600 font-mono">
                {{ format_currency($investor->total_profit_share_paid, $investor->currency) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Total dividen yang telah masuk ke rekening') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Gaji Khusus Diterima') }}</span>
            <div class="mt-2 text-xl font-bold text-purple-600 font-mono">
                {{ format_currency($investor->total_stipend_paid, $investor->currency) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Akumulasi honorarium bulanan investor') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Pengembalian Pokok Modal') }}</span>
            <div class="mt-2 text-xl font-bold text-blue-600 font-mono">
                {{ format_currency($investor->total_capital_return_paid, $investor->currency) }}
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Penarikan modal awal yang telah cair') }}</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ __('Estimasi Balik Modal') }}</span>
            <div class="mt-2 text-xl font-bold {{ $investor->is_bep_achieved ? 'text-emerald-600' : 'text-slate-900' }} font-mono">
                @if($investor->is_bep_achieved)
                    {{ __('BEP Tercapai') }}
                @elseif($investor->estimated_months_to_bep)
                    {{ __('~:months bln lagi', ['months' => $investor->estimated_months_to_bep]) }}
                @else
                    {{ __('Sedang Dihitung') }}
                @endif
            </div>
            <p class="text-[11px] text-slate-400 mt-1">{{ __('Berdasarkan tren bagi hasil berkala') }}</p>
        </div>
    </div>

    <!-- Detail Rekening & Riwayat Transaksi -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Info Rekening -->
        <div class="lg:col-span-4 bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                </span>
                <h3 class="text-sm font-bold text-slate-900">{{ __('Rekening Tujuan Pencairan') }}</h3>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">{{ __('Bank Penerima') }}</span>
                    <span class="font-bold text-slate-800">{{ $investor->bank_name ?? __('Belum terdaftar') }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">{{ __('Nomor Rekening / IBAN') }}</span>
                    <span class="font-mono font-bold text-slate-900 text-sm">{{ $investor->bank_account_no ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">{{ __('Atas Nama Rekening') }}</span>
                    <span class="font-semibold text-slate-800">{{ $investor->bank_account_holder ?? $investor->name }}</span>
                </div>
                <div class="border-t border-slate-100 pt-3 text-[11px] text-slate-400">{{ __('Setiap pembayaran bagi hasil dan gaji khusus akan langsung ditransfer ke rekening di atas.') }}</div>
            </div>
        </div>

        <!-- Tabel Riwayat Pembayaran -->
        <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-900">{{ __('Riwayat Mutasi & Pembayaran') }}</h3>
                <p class="text-[11px] text-slate-500">{{ __('Seluruh pencairan bagi hasil, gaji khusus, dan pengembalian modal.') }}</p>
            </div>

            @if($investor->distributions->isEmpty())
                <div class="p-8 text-center text-xs text-slate-400">{{ __('Belum ada riwayat transaksi pencairan dana tercatat.') }}</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-bold text-slate-500 uppercase">
                                <th class="px-4 py-3">{{ __('No. Transaksi') }}</th>
                                <th class="px-4 py-3">{{ __('Jenis') }}</th>
                                <th class="px-4 py-3">{{ __('Periode') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Nominal') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                                <th class="px-4 py-3">{{ __('Tanggal Bayar') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($investor->distributions as $dist)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $dist->distribution_number }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold {{ $dist->type === 'profit_share' ? 'bg-emerald-50 text-emerald-700' : ($dist->type === 'stipend' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700') }}">
                                            {{ $dist->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">{{ $dist->period_label }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-slate-900">
                                        {{ format_currency($dist->amount, $dist->currency) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($dist->status === 'paid')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ✓ {{ __('Lunas') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">{{ __('Proses') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-[11px] text-slate-500">
                                        {{ $dist->payment_date?->format('d/m/Y') ?? __('Belum') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection


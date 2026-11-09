@extends('layouts.app', ['headerTitle' => __('Kalkulasi Payroll: :name', ['name' => $payrollPeriod->name]), 'title' => $payrollPeriod->name])

@section('content')
<div class="space-y-8">

    <!-- Header & Action -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __($payrollPeriod->name) }}</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border
                    {{ $payrollPeriod->status === 'locked' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($payrollPeriod->status === 'calculated' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                    {{ __('Status:') }} {{ __(strtoupper($payrollPeriod->status)) }}
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Rentang:') }} {{ $payrollPeriod->start_date->format('d/m/Y') }} {{ __('s/d') }} {{ $payrollPeriod->end_date->format('d/m/Y') }} · {{ __('Cut-Off:') }} <span dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">{{ $payrollPeriod->cut_off_date->locale(app()->getLocale())->translatedFormat('d M Y') }}</span> · {{ __('Tanggal Bayar:') }} <span dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">{{ $payrollPeriod->payment_date->locale(app()->getLocale())->translatedFormat('d M Y') }}</span>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('payroll.index') }}" class="py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Kembali') }}
            </a>

            <a href="{{ route('payroll.exchange-rate.edit', ['redirect_to' => url()->current()]) }}" 
                class="px-3.5 py-2.5 rounded-xl bg-white hover:bg-emerald-50/50 text-slate-700 hover:text-emerald-800 border border-slate-200 hover:border-emerald-300 font-semibold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer group">
                <span class="text-sm">💱</span>
                <span>{{ __('Atur Kurs SAR ⇄ IDR') }}</span>
                <span class="font-mono text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 group-hover:bg-emerald-100 transition">
                    Rp {{ number_format($exchangeRateSarToIdr, 0, ',', '.') }}
                </span>
            </a>

            @if ($items->total() > 0)
                <a href="{{ route('payroll.export', $payrollPeriod) }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 border border-emerald-300 font-semibold text-xs shadow-xs transition flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Export Excel (.xlsx)') }}
                </a>
            @endif

            @if ($payrollPeriod->status !== 'locked')
                <form action="{{ route('payroll.calculate', $payrollPeriod) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        {{ __('Kalkulasi Otomatis Gaji Semua Karyawan') }}
                    </button>
                </form>

                @if ($items->total() > 0)
                    <form action="{{ route('payroll.lock', $payrollPeriod) }}" method="POST" onsubmit="return confirm('{{ __('Kunci periode payroll ini? Setelah dikunci data tidak dapat diubah lagi.') }}')">
                        @csrf
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs shadow-sm shadow-emerald-500/20 transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            {{ __('Sahkan & Kunci Payroll') }}
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    <!-- Multi-Currency Summary Cards -->
    <div class="space-y-4">
        @if(($totalsByCurrency['SAR']['count'] ?? 0) > 0 && ($totalsByCurrency['IDR']['count'] ?? 0) > 0)
            <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50/70 via-purple-50/50 to-emerald-50/70 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="text-xl">💱</span>
                    <div>
                        <span class="font-bold text-slate-900 block">{{ __('Payroll Multi-Mata Uang Aktif (IDR & SAR)') }}</span>
                        <span class="text-slate-500 text-[11px] block">{{ __('Periode ini memproses penggajian karyawan di Indonesia (IDR) dan Arab Saudi (SAR).') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('payroll.exchange-rate.edit', ['redirect_to' => url()->current()]) }}"
                        class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-slate-50 border border-slate-300 hover:border-emerald-500 text-slate-800 font-mono text-[11px] font-bold shadow-2xs transition flex items-center gap-2 cursor-pointer group"
                        title="{{ __('Klik untuk sesuaikan kurs SAR ⇄ IDR') }}">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>1 SAR = Rp {{ number_format($exchangeRateSarToIdr, 0, ',', '.') }}</span>
                        <span class="text-[10px] text-blue-600 font-sans font-semibold group-hover:underline flex items-center gap-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            {{ __('Ubah') }}
                        </span>
                    </a>
                    <span class="px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 font-bold text-[11px]">
                        🇸🇦 {{ $totalsByCurrency['SAR']['count'] }} {{ __('Karyawan KSA') }}
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 font-bold text-[11px]">
                        🇮🇩 {{ $totalsByCurrency['IDR']['count'] }} {{ __('Karyawan RI') }}
                    </span>
                </div>
            </div>
        @endif

        {{-- IDR Totals Card --}}
        @if(($totalsByCurrency['IDR']['count'] ?? 0) > 0 || ($totalsByCurrency['SAR']['count'] ?? 0) === 0)
            <div class="space-y-2">
                @if(($totalsByCurrency['SAR']['count'] ?? 0) > 0)
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 uppercase tracking-wider">🇮🇩 {{ __('Ringkasan Cabang Indonesia (IDR)') }}</span>
                        <span class="text-[11px] text-slate-500 font-mono">({{ $totalsByCurrency['IDR']['count'] }} {{ __('karyawan') }})</span>
                    </div>
                @endif
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <span class="text-[11px] text-slate-500 font-semibold uppercase block">{{ __('Total Gaji Pokok (IDR)') }}</span>
                        <span class="text-base font-bold font-mono text-slate-900 block mt-1">Rp {{ number_format($totalsByCurrency['IDR']['basic'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <span class="text-[11px] text-slate-500 font-semibold uppercase block">{{ __('Total Tunjangan') }}</span>
                        <span class="text-base font-bold font-mono text-emerald-600 block mt-1">+Rp {{ number_format($totalsByCurrency['IDR']['allowances'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <span class="text-[11px] text-slate-500 font-semibold uppercase block">{{ __('Total Lembur') }}</span>
                        <span class="text-base font-bold font-mono text-blue-600 block mt-1">+Rp {{ number_format($totalsByCurrency['IDR']['overtime'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                        <span class="text-[11px] text-slate-500 font-semibold uppercase block">{{ __('Total Potongan') }}</span>
                        <span class="text-base font-bold font-mono text-rose-600 block mt-1">-Rp {{ number_format($totalsByCurrency['IDR']['deductions'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl text-white shadow-sm col-span-2 sm:col-span-1" style="background: linear-gradient(135deg, #2563eb 0%, #4338ca 100%) !important; color: #ffffff !important;">
                        <span class="text-[11px] font-semibold uppercase block" style="color: #dbeafe !important;">{{ __('Grand Total Net (IDR)') }}</span>
                        <span class="text-base font-extrabold font-mono block mt-1" style="color: #ffffff !important;">Rp {{ number_format($totalsByCurrency['IDR']['net'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- SAR Totals Card (shown when SAR employees exist) --}}
        @if(($totalsByCurrency['SAR']['count'] ?? 0) > 0)
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">🇸🇦 {{ __('Ringkasan Cabang Arab Saudi (SAR)') }}</span>
                    <span class="text-[11px] text-slate-500 font-mono">({{ $totalsByCurrency['SAR']['count'] }} {{ __('karyawan') }})</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                    <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-sm">
                        <span class="text-[11px] text-emerald-800 font-semibold uppercase block">{{ __('Total Gaji Pokok (SAR)') }}</span>
                        <span class="text-base font-bold font-mono text-slate-900 block mt-1">SAR {{ number_format($totalsByCurrency['SAR']['basic'] ?? 0, 2, '.', ',') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-sm">
                        <span class="text-[11px] text-emerald-800 font-semibold uppercase block">{{ __('Total Tunjangan') }}</span>
                        <span class="text-base font-bold font-mono text-emerald-600 block mt-1">+SAR {{ number_format($totalsByCurrency['SAR']['allowances'] ?? 0, 2, '.', ',') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-sm">
                        <span class="text-[11px] text-emerald-800 font-semibold uppercase block">{{ __('Total Lembur (Pasal 107)') }}</span>
                        <span class="text-base font-bold font-mono text-blue-600 block mt-1">+SAR {{ number_format($totalsByCurrency['SAR']['overtime'] ?? 0, 2, '.', ',') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-sm">
                        <span class="text-[11px] text-emerald-800 font-semibold uppercase block">{{ __('Total Potongan') }}</span>
                        <span class="text-base font-bold font-mono text-rose-600 block mt-1">-SAR {{ number_format($totalsByCurrency['SAR']['deductions'] ?? 0, 2, '.', ',') }}</span>
                    </div>
                    <div class="p-4 rounded-2xl text-white shadow-sm col-span-2 sm:col-span-1" style="background: linear-gradient(135deg, #059669 0%, #0d9488 100%) !important; color: #ffffff !important;">
                        <span class="text-[11px] font-semibold uppercase block" style="color: #d1fae5 !important;">{{ __('Grand Total Net (SAR)') }}</span>
                        <span class="text-base font-extrabold font-mono block mt-1" style="color: #ffffff !important;">SAR {{ number_format($totalsByCurrency['SAR']['net'] ?? 0, 2, '.', ',') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Items Table Card -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-slate-900 text-base">{{ __('Rincian Komponen Gaji per Karyawan') }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ __('Daftar rincian take home pay, tunjangan, lembur, dan potongan masing-masing karyawan pada periode ini.') }}</p>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('payroll.show', $payrollPeriod) }}" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nama, NIK, atau divisi...') }}"
                        class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                    @if(request('search'))
                        <a href="{{ route('payroll.show', $payrollPeriod) }}"
                           style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                           class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus kata kunci') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1 cursor-pointer shrink-0">
                    {{ __('Cari') }}
                </button>

                @if(request('search'))
                    <a href="{{ route('payroll.show', $payrollPeriod) }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs shrink-0">
                        {{ __('Reset') }}
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200 pb-2">
                        <th class="py-3 px-4 font-semibold">{{ __('Karyawan') }}</th>
                        <th class="py-3 px-4 font-semibold">{{ __('Mata Uang') }}</th>
                        <th class="py-3 px-4 font-semibold">{{ __('Gaji Pokok') }}</th>
                        <th class="py-3 px-4 font-semibold">{{ __('Tunjangan') }}</th>
                        <th class="py-3 px-4 font-semibold">{{ __('Lembur') }}</th>
                        <th class="py-3 px-4 font-semibold">{{ __('Potongan Telat & BPJS') }}</th>
                        <th class="py-3 px-4 font-semibold">{{ __('Gaji Bersih (Net)') }}</th>
                        <th class="py-3 px-4 font-semibold text-right">{{ __('Slip Gaji') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse ($items as $item)
                        @php
                            $curr = $item->currency ?: ($item->employee->effective_currency ?? 'IDR');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 font-sans">
                                <span class="font-bold text-slate-900 block">{{ $item->employee->full_name }}</span>
                                <span class="text-[10px] text-slate-500">{{ $item->employee->employee_code }} · {{ $item->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3 px-4 font-sans">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border uppercase
                                    {{ $curr === 'SAR' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                    {{ $curr === 'SAR' ? '🇸🇦 SAR' : '🇮🇩 IDR' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-900">
                                {{ format_currency($item->basic_salary, $curr) }}
                            </td>
                            <td class="py-3 px-4 text-emerald-600 font-semibold">
                                +{{ format_currency($item->total_allowances, $curr) }}
                            </td>
                            <td class="py-3 px-4 text-blue-600 font-semibold">
                                +{{ format_currency($item->total_overtime, $curr) }}
                            </td>
                            <td class="py-3 px-4 text-rose-600 font-semibold">
                                -{{ format_currency($item->total_deductions, $curr) }}
                                @if (isset($item->details['late_deduction']) && $item->details['late_deduction'] > 0)
                                    <span class="block text-[10px] text-amber-600 font-sans italic">({{ __('Termasuk telat: :amount', ['amount' => format_currency($item->details['late_deduction'], $curr)]) }})</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-900 text-sm">
                                {{ format_currency($item->net_salary, $curr) }}
                            </td>
                            <td class="py-3 px-4 text-right font-sans">
                                @if ($item->payslip)
                                    <a href="{{ route('payroll.payslip', $item->payslip) }}" target="_blank" class="px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-semibold text-[11px] transition inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        {{ __('Buka Slip') }}
                                    </a>
                                @else
                                    <span class="text-slate-400 text-[11px]">{{ __('Belum siap') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-400 font-sans">
                                @if(request('search'))
                                    <div class="space-y-2">
                                        <div class="text-sm font-semibold text-slate-700">{{ __('Karyawan Tidak Ditemukan') }}</div>
                                        <p class="text-xs text-slate-500">{{ __('Tidak ada data rincian gaji karyawan yang cocok dengan pencarian') }} "{{ request('search') }}".</p>
                                        <div class="pt-2">
                                            <a href="{{ route('payroll.show', $payrollPeriod) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                {{ __('Reset Pencarian') }}
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    {{ __('Belum ada data kalkulasi. Klik tombol "Kalkulasi Otomatis Gaji Semua Karyawan" di atas untuk memproses.') }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination for Items Table -->
        @if ($items->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $items->links() }}
            </div>
        @elseif ($items->total() > 0)
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium bg-slate-50/50">
                <span>
                    {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $items->total() }}</strong> {{ __('rincian gaji karyawan') }}
                    @if(request('search'))
                        <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('search') }}")</span>
                    @endif
                </span>
                <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
            </div>
        @endif
    </div>

</div>
@endsection

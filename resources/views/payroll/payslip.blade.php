<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Slip Gaji') }} - {{ $payslip->slip_number }} - {{ $payslip->payrollItem->employee->full_name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html[dir="rtl"], body[dir="rtl"], [dir="rtl"] {
            font-family: 'Cairo', 'Plus Jakarta Sans', sans-serif !important;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-border { border: 1px solid #ccc !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen p-4 sm:p-8 flex flex-col items-center justify-center">

    <!-- Top Action Bar -->
    <div class="w-full max-w-3xl flex items-center justify-between mb-6 no-print">
        <button onclick="window.history.back()" class="text-xs text-slate-500 hover:text-slate-900 flex items-center gap-1.5 transition cursor-pointer">
            <span class="rtl:rotate-180 inline-block">←</span> {{ __('Kembali') }}
        </button>
        <button onclick="window.print()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            {{ __('Cetak Slip Gaji (PDF / Print)') }}
        </button>
    </div>

    @php
        $item = $payslip->payrollItem;
        $employee = $item->employee;
        $period = $item->period;
        $d = $item->details ?? [];
        $curr = $item->currency ?: ($employee->effective_currency ?? 'IDR');
    @endphp

    <!-- Payslip Paper Box -->
    <div class="w-full max-w-3xl bg-white border border-slate-200/90 rounded-3xl p-8 sm:p-10 shadow-xl space-y-6 print:border print:border-gray-300 print:text-black print:bg-white">
        
        <!-- Company Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-200 pb-6 gap-4 print:border-b-2 print:border-gray-800">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight uppercase print:text-black">
                    {{ \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation') }}
                </h1>
                <p class="text-xs text-slate-500 print:text-gray-600 mt-0.5">
                    {{ \App\Models\SystemSetting::get('company_address', 'Kota Tasikmalaya') }}
                    @if (\App\Models\SystemSetting::get('company_phone'))
                        · {{ __('Telp:') }} {{ \App\Models\SystemSetting::get('company_phone') }}
                    @endif
                </p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="text-[11px] text-blue-600 print:text-gray-700 font-semibold uppercase tracking-wider">
                        {{ __('SLIP GAJI KARYAWAN (CONFIDENTIAL)') }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $curr === 'SAR' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                        {{ \App\Helpers\CurrencyHelper::label($curr) }}
                    </span>
                </div>
            </div>
            <div class="text-right rtl:text-left">
                <span class="text-xs font-mono font-bold text-slate-900 block print:text-black">{{ $payslip->slip_number }}</span>
                <span class="text-xs text-slate-600 print:text-gray-600 block mt-0.5">{{ __($period->name) }}</span>
                <span class="text-[10px] text-slate-400 print:text-gray-500">{{ __('Tgl Bayar: :date', ['date' => $period->payment_date->translatedFormat('d M Y')]) }}</span>
            </div>
        </div>

        <!-- Employee Bio Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 text-xs print:bg-gray-50 print:border-gray-200">
            <div>
                <span class="text-slate-500 print:text-gray-500 block text-[10px]">{{ __('Nama Karyawan:') }}</span>
                <span class="font-bold text-slate-900 print:text-black block mt-0.5">{{ $employee->full_name }}</span>
            </div>
            <div>
                <span class="text-slate-500 print:text-gray-500 block text-[10px]">{{ __('Nomor Induk (NIK):') }}</span>
                <span class="font-mono font-semibold text-slate-900 print:text-black block mt-0.5">{{ $employee->employee_code }}</span>
            </div>
            <div>
                <span class="text-slate-500 print:text-gray-500 block text-[10px]">{{ __('Departemen / Jabatan:') }}</span>
                <span class="text-slate-900 font-medium print:text-black block mt-0.5">
                    {{ $employee->department ? __($employee->department->name) : '-' }} / {{ $employee->position ? __($employee->position->title) : '-' }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 print:text-gray-500 block text-[10px]">{{ __('Rekening Pembayaran:') }}</span>
                <span class="font-mono text-slate-900 font-medium print:text-black block mt-0.5">{{ $employee->bank_name ?? 'BCA' }} - {{ $employee->bank_account_no ?? '-' }}</span>
            </div>
        </div>

        <!-- Earnings & Deductions Tables -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs">
            
            <!-- Earnings (Penghasilan) -->
            <div class="space-y-3">
                <h4 class="font-bold text-emerald-700 uppercase tracking-wider text-[11px] border-b border-slate-200 pb-2 print:text-black print:border-gray-400">
                    {{ __('A. Penghasilan (Earnings)') }}
                </h4>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-600 print:text-gray-700">{{ __('Gaji Pokok:') }}</span>
                        <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($item->basic_salary, $curr) }}</span>
                    </div>

                    @if(!empty($d['allowance_breakdown']))
                        @foreach($d['allowance_breakdown'] as $code => $alw)
                            <div class="flex justify-between">
                                <span class="text-slate-600 print:text-gray-700">{{ __($alw['name']) }}:</span>
                                <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($alw['amount'], $curr) }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="flex justify-between">
                            <span class="text-slate-600 print:text-gray-700">{{ __('Tunjangan Jabatan:') }}</span>
                            <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($d['position_allowance'] ?? 0, $curr) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600 print:text-gray-700">{{ __('Tunjangan Uang Makan & Transport:') }}</span>
                            <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($d['meal_transport_allowance'] ?? 0, $curr) }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between">
                        <div>
                            <span class="text-slate-600 print:text-gray-700">{{ __('Uang Lembur (:hours Jam):', ['hours' => $d['overtime_hours'] ?? 0]) }}</span>
                            @if(($d['overtime_hours'] ?? 0) > 0)
                                <span class="text-[10px] text-slate-400 print:text-gray-500 block">
                                    @if(($d['overtime_calc_type'] ?? '') === 'saudi_article_107')
                                        🇸🇦 {{ __('Pasal 107 UU Ketenagakerjaan Arab Saudi (Upah/Jam + 50% Gaji Pokok)') }}
                                    @elseif(($d['overtime_calc_type'] ?? '') === 'depnaker')
                                        🇮🇩 {{ __('Formula Depnaker 1/173') }}
                                    @else
                                        {{ __('Tarif Standar Perusahaan') }}
                                    @endif
                                </span>
                                @if(!empty($d['overtime_saudi_breakdown']['formula_display']))
                                    <span class="text-[9px] text-emerald-600 font-mono block mt-0.5">
                                        {{ $d['overtime_saudi_breakdown']['formula_display'] }}
                                    </span>
                                @endif
                            @endif
                        </div>
                        <span class="font-mono text-slate-900 font-semibold print:text-black">
                            {{ format_currency($item->total_overtime, $curr) }}
                        </span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-emerald-700 print:text-black">
                    <span>{{ __('Total Penghasilan (Bruto):') }}</span>
                    <span class="font-mono">{{ format_currency($item->basic_salary + $item->total_allowances + $item->total_overtime, $curr) }}</span>
                </div>
            </div>

            <!-- Deductions (Potongan) -->
            <div class="space-y-3">
                <h4 class="font-bold text-rose-700 uppercase tracking-wider text-[11px] border-b border-slate-200 pb-2 print:text-black print:border-gray-400">
                    {{ __('B. Potongan (Deductions)') }}
                </h4>
                <div class="space-y-2">
                    @if(!empty($d['deduction_breakdown']))
                        @foreach($d['deduction_breakdown'] as $code => $ded)
                            <div class="flex justify-between">
                                <div>
                                    <span class="text-slate-600 print:text-gray-700 block">{{ __($ded['name']) }}:</span>
                                    @if($code === 'DED_LATE' && ($d['late_minutes'] ?? 0) > 0)
                                        <span class="text-[10px] text-amber-600 print:text-gray-500 font-sans">({{ __(':minutes menit keterlambatan', ['minutes' => $d['late_minutes']]) }})</span>
                                    @endif
                                </div>
                                <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($ded['amount'], $curr) }}</span>
                            </div>
                        @endforeach
                    @else
                        <div class="flex justify-between">
                            <span class="text-slate-600 print:text-gray-700">{{ __('BPJS Kesehatan (1%):') }}</span>
                            <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($d['bpjs_kesehatan'] ?? 0, $curr) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-600 print:text-gray-700">{{ __('BPJS Ketenagakerjaan (2%):') }}</span>
                            <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($d['bpjs_ketenagakerjaan'] ?? 0, $curr) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <div>
                                <span class="text-slate-600 print:text-gray-700 block">{{ __('Potongan Telat Absensi:') }}</span>
                                <span class="text-[10px] text-amber-600 print:text-gray-500 font-sans">({{ __(':minutes menit keterlambatan', ['minutes' => $d['late_minutes'] ?? 0]) }})</span>
                            </div>
                            <span class="font-mono text-slate-900 font-semibold print:text-black">{{ format_currency($d['late_deduction'] ?? 0, $curr) }}</span>
                        </div>
                    @endif
                </div>
                <div class="pt-2 border-t border-slate-200 flex justify-between font-bold text-rose-700 print:text-black">
                    <span>{{ __('Total Potongan:') }}</span>
                    <span class="font-mono">{{ format_currency($item->total_deductions, $curr) }}</span>
                </div>
            </div>

        </div>

        <!-- Net Take Home Pay Highlight -->
        <div class="p-6 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-md print:bg-gray-100 print:border-gray-400">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-100 print:text-gray-700 block">{{ __('Gaji Bersih Diterima (Take Home Pay)') }}</span>
                <span class="text-[11px] text-blue-200 print:text-gray-500 mt-0.5 block">{{ __('Ditransfer langsung ke rekening karyawan') }}</span>
                <span class="text-[10px] text-blue-100 italic block mt-1.5 print:text-gray-600">
                    {{ __('Terbilang:') }} {{ \App\Helpers\CurrencyHelper::terbilang($item->net_salary, $curr) }}
                </span>
            </div>
            <div class="text-right rtl:text-left">
                <span class="text-2xl sm:text-3xl font-extrabold font-mono text-white print:text-black block">
                    {{ format_currency($item->net_salary, $curr) }}
                </span>
                <span class="text-[10px] text-blue-100 uppercase font-semibold tracking-wider block mt-1 print:text-gray-600">
                    {{ $curr === 'SAR' ? '🇸🇦 ' . __('Mata Uang: Riyal Arab Saudi (SAR)') : '🇮🇩 ' . __('Mata Uang: Rupiah Indonesia (IDR)') }}
                </span>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="grid grid-cols-2 pt-8 text-center text-xs print:pt-16">
            <div>
                <p class="text-slate-500 print:text-gray-600 mb-16">{{ __('Diterima oleh Karyawan,') }}</p>
                <p class="font-bold text-slate-900 print:text-black border-t border-slate-300 print:border-gray-400 pt-1 inline-block min-w-[160px]">{{ $employee->full_name }}</p>
            </div>
            <div>
                <p class="text-slate-500 print:text-gray-600 mb-16">{{ __('Bagian Payroll & Keuangan,') }}</p>
                <p class="font-bold text-slate-900 print:text-black border-t border-slate-300 print:border-gray-400 pt-1 inline-block min-w-[160px]">{{ __('Finance Manager') }}</p>
            </div>
        </div>

        <p class="text-center text-[10px] text-slate-400 print:text-gray-400 pt-4 border-t border-slate-200">
            {{ __('Dokumen ini dihasilkan secara otomatis oleh HRIS :company pada :date.', ['company' => \App\Models\SystemSetting::get('company_name', 'HRIS Solution'), 'date' => now()->translatedFormat('d F Y H:i')]) }}
        </p>

    </div>

</body>
</html>
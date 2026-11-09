<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Laporan Rekapitulasi Penggajian') }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        * { box-sizing: border-box; }
        body {
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #0f172a;
            font-size: 7.5pt;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .header-company { vertical-align: top; width: 60%; }
        .header-meta { vertical-align: top; width: 40%; font-size: 7.5pt; color: #475569; line-height: 1.35; }
        .company-name { font-size: 13pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-address { font-size: 7.5pt; color: #64748b; margin-top: 2px; margin-bottom: 4px; }
        .report-title { font-size: 9.5pt; font-weight: bold; color: #0f172a; text-transform: uppercase; }

        .summary-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .summary-bar td { padding: 4px 6px; font-size: 7.5pt; vertical-align: middle; color: #334155; }
        .summary-label { font-weight: bold; color: #1e293b; text-transform: uppercase; font-size: 7.5pt; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 7.2pt; }
        .data-table thead { display: table-header-group; }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            padding: 5px 3px;
            border: 1px solid #334155;
            text-align: center;
            vertical-align: middle;
        }
        .data-table td { padding: 3.5px 4px; border: 1px solid #cbd5e1; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .text-start { text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; }
        .text-end { text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .font-mono { font-family: 'DejaVu Sans', sans-serif; }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; }
        .signature-cell { width: 50%; text-align: center; vertical-align: top; padding: 0 40px; }
        .signature-title { font-weight: bold; font-size: 8pt; color: #1e293b; }
        .signature-space { height: 48px; }
        .signature-name { font-weight: bold; font-size: 8pt; color: #0f172a; text-decoration: underline; }
        .signature-role { font-size: 7.5pt; color: #64748b; margin-top: 2px; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
        @if(app()->getLocale() === 'ar')
            <td class="header-meta" style="text-align: left;">
                <div>{{ __('Periode:') }} <strong style="color: #0f172a;">{{ $periodLabel }}</strong></div>
                <div>{{ __('Cabang:') }} <strong style="color: #0f172a;">{{ __($selectedBranch) }}</strong> | {{ __('Dept:') }} <strong style="color: #0f172a;">{{ __($selectedDept) }}</strong></div>
                <div>{{ __('Karyawan:') }} <strong style="color: #0f172a;">{{ __($selectedEmployee) }}</strong></div>
                <div style="color: #94a3b8; font-size: 7pt; margin-top: 2px;">{{ __('Dicetak:') }} {{ $printedAt }} {{ __('oleh') }} {{ $signers['maker_name'] }}</div>
            </td>
            <td class="header-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI PENGGAJIAN & PAYROLL KARYAWAN') }}</div>
            </td>
        @else
            <td class="header-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI PENGGAJIAN & PAYROLL KARYAWAN') }}</div>
            </td>
            <td class="header-meta" style="text-align: right;">
                <div>{{ __('Periode:') }} <strong style="color: #0f172a;">{{ $periodLabel }}</strong></div>
                <div>{{ __('Cabang:') }} <strong style="color: #0f172a;">{{ __($selectedBranch) }}</strong> | {{ __('Dept:') }} <strong style="color: #0f172a;">{{ __($selectedDept) }}</strong></div>
                <div>{{ __('Karyawan:') }} <strong style="color: #0f172a;">{{ __($selectedEmployee) }}</strong></div>
                <div style="color: #94a3b8; font-size: 7pt; margin-top: 2px;">{{ __('Dicetak:') }} {{ $printedAt }} {{ __('oleh') }} {{ $signers['maker_name'] }}</div>
            </td>
        @endif
        </tr>
    </table>

    <!-- Ringkasan Gaji Bar -->
    <table class="summary-bar">
        <tr>
            <td class="summary-label" style="width: 14%;">{{ __('Total Pegawai:') }}</td>
            <td style="width: 12%;"><strong>{{ $items->count() }}</strong> {{ __('Orang') }}</td>
            <td style="width: 25%;">{{ __('Total Gaji Pokok:') }} <strong style="color: #1e293b;">{{ format_currency($totalBasic) }}</strong></td>
            <td style="width: 24%;">{{ __('Total Tunjangan:') }} <strong style="color: #15803d;">{{ format_currency($totalAllowances) }}</strong></td>
            <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; width: 25%;">
                {{ __('Total Netto (THP):') }} <strong style="color: #1e3a8a; font-size: 8.5pt;">{{ format_currency($totalNet) }}</strong>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 24px;">{{ __('No') }}</th>
                <th style="width: 55px;">{{ __('ID') }}</th>
                <th style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Nama Karyawan') }}</th>
                <th style="width: 105px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Departemen') }}</th>
                <th style="width: 95px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Jabatan') }}</th>
                <th style="width: 75px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-right: 5px; padding-left: 5px;">{{ __('Gaji Pokok') }}</th>
                <th style="width: 70px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-right: 5px; padding-left: 5px;">{{ __('Tunjangan') }}</th>
                <th style="width: 60px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-right: 5px; padding-left: 5px;">{{ __('Lembur') }}</th>
                <th style="width: 75px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-right: 5px; padding-left: 5px;">{{ __('Gaji Bruto') }}</th>
                <th style="width: 65px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-right: 5px; padding-left: 5px;">{{ __('Potongan') }}</th>
                <th style="width: 80px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-right: 5px; padding-left: 5px;">{{ __('Gaji Bersih') }}</th>
                <th style="width: 50px;">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($items as $item)
                @php
                    $gross = $item->basic_salary + $item->total_allowances + $item->total_overtime;
                @endphp
                <tr>
                    <td class="text-center font-mono text-muted">{{ $no++ }}</td>
                    <td class="text-center font-mono font-bold">{{ $item->employee->employee_code ?? '-' }}</td>
                    <td class="text-start font-bold" style="color: #0f172a; padding-left: 6px; padding-right: 6px;">{{ $item->employee->full_name ?? '-' }}</td>
                    <td class="text-start text-muted" style="padding-left: 6px; padding-right: 6px;">{{ __($item->employee->department->name ?? '-') }}</td>
                    <td class="text-start text-muted" style="padding-left: 6px; padding-right: 6px;">{{ __($item->employee->position->title ?? ($item->employee->position->name ?? '-')) }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($item->basic_salary, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($item->total_allowances, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($item->total_overtime, 0, ',', '.') }}</td>
                    <td class="text-end font-mono font-bold" style="padding-right: 5px; padding-left: 5px; color: #1e293b;">{{ number_format($gross, 0, ',', '.') }}</td>
                    <td class="text-end font-mono text-muted" style="padding-right: 5px; padding-left: 5px; color: #b91c1c;">{{ number_format($item->total_deductions, 0, ',', '.') }}</td>
                    <td class="text-end font-mono font-bold" style="padding-right: 5px; padding-left: 5px; color: #1e3a8a;">{{ number_format($item->net_salary, 0, ',', '.') }}</td>
                    <td class="text-center font-bold" style="color: #15803d;">{{ strtoupper(__($item->status ?? 'PAID')) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted" style="padding: 18px;">
                        {{ __('Tidak ada data penggajian untuk periode dan filter yang dipilih.') }}
                    </td>
                </tr>
            @endforelse
            @if ($items->count() > 0)
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" class="text-end" style="padding-right: 8px; padding-left: 8px;">{{ __('TOTAL KESELURUHAN:') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($totalBasic, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($totalAllowances, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($totalOvertime, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px;">{{ number_format($totalBasic + $totalAllowances + $totalOvertime, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px; color: #b91c1c;">{{ number_format($totalDeductions, 0, ',', '.') }}</td>
                    <td class="text-end font-mono" style="padding-right: 5px; padding-left: 5px; color: #1e3a8a;">{{ number_format($totalNet, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">{{ __('Dibuat Oleh:') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $signers['maker_name'] }}</div>
                <div class="signature-role">{{ __($signers['maker_title']) }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">{{ __('Mengetahui & Menyetujui:') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $signers['approver_name'] }}</div>
                <div class="signature-role">{{ __($signers['approver_title']) }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
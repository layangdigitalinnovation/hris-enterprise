<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Laporan Rekapitulasi Cuti Karyawan') }}</title>
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

        .data-table { width: 100%; border-collapse: collapse; font-size: 7pt; }
        .data-table thead { display: table-header-group; }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 6.8pt;
            text-transform: uppercase;
            padding: 4px 2px;
            border: 1px solid #334155;
            text-align: center;
            vertical-align: middle;
        }
        .data-table td { padding: 3px 2px; border: 1px solid #cbd5e1; vertical-align: middle; }
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
                <div>{{ __('Tahun Periode:') }} <strong style="color: #0f172a;">{{ $tahun }}</strong></div>
                <div>{{ __('Jenis Cuti:') }} <strong style="color: #0f172a;">{{ __($selectedLeaveType) }}</strong></div>
                <div>{{ __('Cabang:') }} <strong style="color: #0f172a;">{{ __($selectedBranch) }}</strong> | {{ __('Dept:') }} <strong style="color: #0f172a;">{{ __($selectedDept) }}</strong></div>
                <div style="color: #94a3b8; font-size: 7pt; margin-top: 2px;">{{ __('Dicetak:') }} {{ $printedAt }} {{ __('oleh') }} {{ $signers['maker_name'] }}</div>
            </td>
            <td class="header-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI CUTI & SALDO LEAVE LEDGER') }}</div>
            </td>
        @else
            <td class="header-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI CUTI & SALDO LEAVE LEDGER') }}</div>
            </td>
            <td class="header-meta" style="text-align: right;">
                <div>{{ __('Tahun Periode:') }} <strong style="color: #0f172a;">{{ $tahun }}</strong></div>
                <div>{{ __('Jenis Cuti:') }} <strong style="color: #0f172a;">{{ __($selectedLeaveType) }}</strong></div>
                <div>{{ __('Cabang:') }} <strong style="color: #0f172a;">{{ __($selectedBranch) }}</strong> | {{ __('Dept:') }} <strong style="color: #0f172a;">{{ __($selectedDept) }}</strong></div>
                <div style="color: #94a3b8; font-size: 7pt; margin-top: 2px;">{{ __('Dicetak:') }} {{ $printedAt }} {{ __('oleh') }} {{ $signers['maker_name'] }}</div>
            </td>
        @endif
        </tr>
    </table>

    <!-- Ringkasan Cuti Bar -->
    <table class="summary-bar">
        <tr>
            <td class="summary-label" style="width: 14%;">{{ __('Total Pegawai:') }}</td>
            <td style="width: 12%;"><strong>{{ count($reportData) }}</strong> {{ __('Orang') }}</td>
            <td style="width: 24%;">{{ __('Total Kuota Hak:') }} <strong style="color: #1e3a8a;">{{ $totalInitialQuota }}</strong> {{ __('Hari') }}</td>
            <td style="width: 24%;">{{ __('Total Cuti Diambil:') }} <strong style="color: #b45309;">{{ $totalUsedQuota }}</strong> {{ __('Hari') }}</td>
            <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; width: 26%;">
                {{ __('Sisa Saldo Cuti:') }} <strong style="color: #15803d; font-size: 8.5pt;">{{ $totalRemainingQuota }}</strong> {{ __('Hari') }}
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px;">{{ __('No') }}</th>
                <th style="width: 50px;">{{ __('ID') }}</th>
                <th style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 5px; padding-right: 5px; width: 140px;">{{ __('Nama Karyawan') }}</th>
                <th style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 5px; padding-right: 5px; width: 120px;">{{ __('Departemen') }}</th>
                <th style="width: 45px;">{{ __('Hak') }}</th>
                <th style="width: 22px;">{{ __('Jan') }}</th>
                <th style="width: 22px;">{{ __('Feb') }}</th>
                <th style="width: 22px;">{{ __('Mar') }}</th>
                <th style="width: 22px;">{{ __('Apr') }}</th>
                <th style="width: 22px;">{{ __('Mei') }}</th>
                <th style="width: 22px;">{{ __('Jun') }}</th>
                <th style="width: 22px;">{{ __('Jul') }}</th>
                <th style="width: 22px;">{{ __('Agu') }}</th>
                <th style="width: 22px;">{{ __('Sep') }}</th>
                <th style="width: 22px;">{{ __('Okt') }}</th>
                <th style="width: 22px;">{{ __('Nov') }}</th>
                <th style="width: 22px;">{{ __('Des') }}</th>
                <th style="width: 48px;">{{ __('Ambil') }}</th>
                <th style="width: 48px;">{{ __('Sisa') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($reportData as $row)
                <tr>
                    <td class="text-center font-mono text-muted">{{ $no++ }}</td>
                    <td class="text-center font-mono font-bold">{{ $row['employee']->employee_code ?? '-' }}</td>
                    <td class="text-start font-bold" style="color: #0f172a; padding-left: 5px; padding-right: 5px;">{{ $row['employee']->full_name ?? '-' }}</td>
                    <td class="text-start text-muted" style="padding-left: 5px; padding-right: 5px;">{{ __($row['employee']->department->name ?? '-') }}</td>
                    <td class="text-center font-mono font-bold">{{ $row['initial_quota'] }}</td>
                    @for ($m = 1; $m <= 12; $m++)
                        <td class="text-center font-mono {{ $row['months'][$m] > 0 ? 'font-bold' : 'text-muted' }}" style="{{ $row['months'][$m] > 0 ? 'background-color: #fef3c7; color: #b45309;' : '' }}">
                            {{ $row['months'][$m] > 0 ? $row['months'][$m] : '-' }}
                        </td>
                    @endfor
                    <td class="text-center font-mono font-bold" style="color: #b45309;">{{ $row['total_taken'] }}</td>
                    <td class="text-center font-mono font-bold" style="color: #15803d; background-color: #f0fdf4;">{{ $row['remaining'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="19" class="text-center text-muted" style="padding: 18px;">
                        {{ __('Tidak ada data cuti untuk periode dan filter yang dipilih.') }}
                    </td>
                </tr>
            @endforelse
            @if (count($reportData) > 0)
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="4" class="text-end" style="padding-right: 6px; padding-left: 6px;">{{ __('TOTAL KESELURUHAN:') }}</td>
                    <td class="text-center font-mono">{{ $totalInitialQuota }}</td>
                    <td colspan="12"></td>
                    <td class="text-center font-mono" style="color: #b45309;">{{ $totalUsedQuota }}</td>
                    <td class="text-center font-mono" style="color: #15803d;">{{ $totalRemainingQuota }}</td>
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
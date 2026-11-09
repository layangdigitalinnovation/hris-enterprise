<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Laporan Matriks Jadwal Kerja') }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 5mm 6mm 7mm 6mm;
        }
        * { box-sizing: border-box; }
        body {
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #0f172a;
            font-size: 7pt;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 5px;
            margin-bottom: 5px;
        }
        .header-company { vertical-align: top; width: 60%; }
        .header-meta { vertical-align: top; width: 40%; font-size: 7pt; color: #475569; line-height: 1.3; }
        .company-name { font-size: 12pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-address { font-size: 7pt; color: #64748b; margin-top: 2px; margin-bottom: 3px; }
        .report-title { font-size: 9pt; font-weight: bold; color: #0f172a; text-transform: uppercase; }

        .legend-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .legend-bar td { padding: 3px 6px; font-size: 6.5pt; vertical-align: middle; }

        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
        }
        .data-table thead { display: table-header-group; }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            border: 1px solid #334155;
            text-align: center;
            vertical-align: middle;
            overflow: hidden;
            white-space: nowrap;
        }
        .data-table td {
            border: 1px solid #cbd5e1;
            vertical-align: middle;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        .col-wrap {
            white-space: normal !important;
            word-wrap: break-word !important;
            word-break: break-word !important;
            line-height: 1.15;
            text-overflow: clip !important;
        }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .text-start { text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .font-mono { font-family: 'DejaVu Sans', sans-serif; }

        .cell-shift {
            font-weight: bold;
            display: block;
            text-align: center;
            line-height: 1.1;
        }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 16px; page-break-inside: avoid; }
        .signature-cell { width: 50%; text-align: center; vertical-align: top; padding: 0 40px; }
        .signature-title { font-weight: bold; font-size: 7.5pt; color: #1e293b; }
        .signature-space { height: 42px; }
        .signature-name { font-weight: bold; font-size: 7.5pt; color: #0f172a; text-decoration: underline; }
        .signature-role { font-size: 7pt; color: #64748b; margin-top: 2px; }
    </style>
</head>
<body>

    @php
        // Fallback for $pdfChunks
        if (empty($pdfChunks)) {
            $pdfChunks = [
                [
                    'dates' => $dates,
                    'label' => $periodLabel,
                ]
            ];
        }

        if (!function_exists('getCleanShiftCode')) {
            function getCleanShiftCode($raw) {
                $c = strtoupper(trim(str_replace(['SH-', 'SHIFT-'], '', $raw ?? '-')));
                if ($c === 'OFF' || $c === '-' || $c === '') return $c ?: '-';
                if ($c === 'REGULER') return 'REG';
                if ($c === 'SIANG') return 'SIA';
                if ($c === 'PAGI') return 'PAG';
                if ($c === 'MALAM') return 'MLM';
                return strlen($c) > 4 ? substr($c, 0, 3) : $c;
            }
        }

        // Helper to format distinct colors per shift
        if (!function_exists('getShiftStyle')) {
            function getShiftStyle($code) {
                return match($code) {
                    'OFF' => [
                        'bg' => '#fee2e2',
                        'color' => '#b91c1c',
                        'border' => '#fca5a5',
                    ],
                    'REG' => [
                        'bg' => '#dbeafe',
                        'color' => '#1e40af',
                        'border' => '#93c5fd',
                    ],
                    'PAG' => [
                        'bg' => '#dcfce7',
                        'color' => '#15803d',
                        'border' => '#86efac',
                    ],
                    'SIA' => [
                        'bg' => '#fef3c7',
                        'color' => '#b45309',
                        'border' => '#fde68a',
                    ],
                    'MLM' => [
                        'bg' => '#ede9fe',
                        'color' => '#6d28d9',
                        'border' => '#c4b5fd',
                    ],
                    default => [
                        'bg' => '#f8fafc',
                        'color' => '#475569',
                        'border' => '#cbd5e1',
                    ],
                };
            }
        }
    @endphp

@foreach ($pdfChunks as $chunkIndex => $chunk)
    @php
        $chunkDates = $chunk['dates'];
        $numDates = count($chunkDates);
        if ($numDates >= 28) {
            $wNo = '2%';
            $wId = '4.5%';
            $wName = '11.5%';
            $wDept = '10.5%';
            $wDate = ((100 - (2 + 4.5 + 11.5 + 10.5)) / $numDates) . '%';
            $fontSizeDayNum = '5.5pt';
            $fontSizeDayName = '4.2pt';
            $fontSizeCell = '5.2pt';
            $paddingCell = '2px 0px';
        } elseif ($numDates >= 16) {
            $wNo = '2.5%';
            $wId = '5.5%';
            $wName = '14%';
            $wDept = '12%';
            $wDate = ((100 - (2.5 + 5.5 + 14 + 12)) / $numDates) . '%';
            $fontSizeDayNum = '6pt';
            $fontSizeDayName = '4.8pt';
            $fontSizeCell = '6pt';
            $paddingCell = '2.5px 1px';
        } else {
            $wNo = '3%';
            $wId = '7%';
            $wName = '18%';
            $wDept = '15%';
            $wDate = ((100 - (3 + 7 + 18 + 15)) / $numDates) . '%';
            $fontSizeDayNum = '7pt';
            $fontSizeDayName = '5.5pt';
            $fontSizeCell = '6.8pt';
            $paddingCell = '3.5px 2px';
        }
    @endphp

    @if ($chunkIndex > 0)
        <div style="page-break-before: always;"></div>
    @endif

    <!-- Header -->
    <table class="header-table">
        <tr>
        @if(app()->getLocale() === 'ar')
            <td class="header-meta" style="text-align: left;">
                <div>{{ __('Rentang Matriks:') }} <strong style="color: #0f172a;">{{ $chunk['label'] }}</strong></div>
                <div>{{ __('Cabang:') }} <strong style="color: #0f172a;">{{ __($selectedBranch) }}</strong> | {{ __('Dept:') }} <strong style="color: #0f172a;">{{ __($selectedDept) }}</strong></div>
                <div style="color: #94a3b8; font-size: 6.5pt; margin-top: 2px;">{{ __('Dicetak:') }} {{ $printedAt }} {{ __('oleh') }} {{ $signers['maker_name'] }}</div>
            </td>
            <td class="header-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN MATRIKS PENUGASAN JADWAL & SHIFT KERJA') }}</div>
            </td>
        @else
            <td class="header-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN MATRIKS PENUGASAN JADWAL & SHIFT KERJA') }}</div>
            </td>
            <td class="header-meta" style="text-align: right;">
                <div>{{ __('Rentang Matriks:') }} <strong style="color: #0f172a;">{{ $chunk['label'] }}</strong></div>
                <div>{{ __('Cabang:') }} <strong style="color: #0f172a;">{{ __($selectedBranch) }}</strong> | {{ __('Dept:') }} <strong style="color: #0f172a;">{{ __($selectedDept) }}</strong></div>
                <div style="color: #94a3b8; font-size: 6.5pt; margin-top: 2px;">{{ __('Dicetak:') }} {{ $printedAt }} {{ __('oleh') }} {{ $signers['maker_name'] }}</div>
            </td>
        @endif
        </tr>
    </table>

    <!-- Legend Bar -->
    <table class="legend-bar">
        <tr>
            <td style="font-weight: bold; width: 85px; white-space: nowrap; color: #1e293b; vertical-align: middle;">{{ __('Keterangan:') }}</td>
            <td style="line-height: 1.4; vertical-align: middle;">
                @foreach ($shifts as $sh)
                    @php
                        $shCode = getCleanShiftCode($sh->code);
                        $st = getShiftStyle($shCode);
                        $cleanName = trim(preg_replace('/\s*\(\d{2}:\d{2}\s*-\s*\d{2}:\d{2}\)/', '', $sh->name));
                        $timeStr = substr($sh->start_time, 0, 5) . ' - ' . substr($sh->end_time, 0, 5);
                    @endphp
                    <span style="display: inline-block; margin-right: 12px; margin-left: 12px; vertical-align: middle;">
                        <span style="background-color: {{ $st['bg'] }}; color: {{ $st['color'] }}; font-weight: bold; padding: 1px 4px; border: 1px solid {{ $st['border'] }}; font-size: 6.2pt;">[{{ $shCode }}]</span>
                        <span style="color: #334155; font-size: 6.5pt;">{{ __($cleanName) }} ({{ $timeStr }})</span>
                    </span>
                @endforeach
                @php $offSt = getShiftStyle('OFF'); @endphp
                <span style="display: inline-block; margin-right: 12px; margin-left: 12px; vertical-align: middle;">
                    <span style="background-color: {{ $offSt['bg'] }}; color: {{ $offSt['color'] }}; font-weight: bold; padding: 1px 4px; border: 1px solid {{ $offSt['border'] }}; font-size: 6.2pt;">[OFF]</span>
                    <span style="color: #b91c1c; font-weight: bold; font-size: 6.5pt;">{{ __('Libur / Day Off') }}</span>
                </span>
            </td>
        </tr>
    </table>

    <!-- Matrix Data Table (Strict Fixed Layout) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: {{ $wNo }}; padding: 3px 1px; font-size: {{ $fontSizeDayNum }};">{{ __('No') }}</th>
                <th style="width: {{ $wId }}; padding: 3px 1px; font-size: {{ $fontSizeDayNum }};">{{ __('ID') }}</th>
                <th style="width: {{ $wName }}; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding: 3px 3px; font-size: {{ $fontSizeDayNum }};">{{ __('Nama Karyawan') }}</th>
                <th style="width: {{ $wDept }}; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding: 3px 3px; font-size: {{ $fontSizeDayNum }};">{{ __('Departemen') }}</th>
                @foreach ($chunkDates as $d)
                    @php
                        $dayNum = date('d', strtotime($d));
                        $dayName = Carbon\Carbon::parse($d)->locale(app()->getLocale())->translatedFormat('D');
                        $isSun = (date('w', strtotime($d)) == 0);
                    @endphp
                    <th style="width: {{ $wDate }}; {{ $isSun ? 'background-color: #991b1b;' : '' }}; padding: 2px 0px;">
                        <div style="font-size: {{ $fontSizeDayNum }}; line-height: 1.1;">{{ $dayNum }}</div>
                        <div style="font-size: {{ $fontSizeDayName }}; font-weight: normal; opacity: 0.9; line-height: 1;">{{ $dayName }}</div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($matrix as $row)
                <tr>
                    <td class="text-center font-mono text-muted" style="padding: {{ $paddingCell }}; font-size: {{ $fontSizeCell }};">{{ $no++ }}</td>
                    <td class="text-center font-mono font-bold" style="padding: {{ $paddingCell }}; font-size: {{ $fontSizeCell }};">{{ $row['employee']->employee_code ?? '-' }}</td>
                    <td class="text-start font-bold col-wrap" style="color: #0f172a; padding: 2px 3px; font-size: {{ $fontSizeCell }};">
                        {{ $row['employee']->full_name ?? '-' }}
                    </td>
                    <td class="text-start text-muted col-wrap" style="padding: 2px 3px; font-size: {{ $fontSizeCell }};">
                        {{ __($row['employee']->department->name ?? '-') }}
                    </td>
                    @foreach ($chunkDates as $d)
                        @php
                            $cell = $row['dates'][$d] ?? ['code' => '-', 'color' => '#64748b'];
                            $dispCode = getCleanShiftCode($cell['code']);
                            $isAssigned = ($dispCode !== '-' && $dispCode !== '');
                            $st = $isAssigned ? getShiftStyle($dispCode) : ['bg' => 'transparent', 'color' => '#94a3b8'];
                        @endphp
                        <td class="text-center" style="background-color: {{ $st['bg'] }}; padding: {{ $paddingCell }};">
                            <span class="cell-shift" style="color: {{ $st['color'] }}; font-size: {{ $fontSizeCell }}; font-weight: bold;">
                                {{ $dispCode }}
                            </span>
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($chunkDates) + 4 }}" class="text-center text-muted" style="padding: 16px;">
                        {{ __('Tidak ada data jadwal kerja yang sesuai dengan parameter filter.') }}
                    </td>
                </tr>
            @endforelse
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
@endforeach

</body>
</html>
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Laporan Rekapitulasi Presensi') }}</title>
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
            font-size: 8pt;
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

        .data-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; }
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

        .badge { display: inline-block; padding: 1.5px 5px; border-radius: 3px; font-size: 6.8pt; font-weight: bold; text-align: center; }
        .badge-present { background-color: #dcfce7; color: #15803d; border: 0.5px solid #86efac; }
        .badge-late { background-color: #fef3c7; color: #b45309; border: 0.5px solid #fde68a; }
        .badge-leave { background-color: #dbeafe; color: #1d4ed8; border: 0.5px solid #bfdbfe; }
        .badge-absent { background-color: #fee2e2; color: #b91c1c; border: 0.5px solid #fecaca; }

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
                <div class="report-title">{{ __('LAPORAN REKAPITULASI KEHADIRAN KARYAWAN') }}</div>
            </td>
        @else
            <td class="header-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI KEHADIRAN KARYAWAN') }}</div>
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

    <!-- Ringkasan Presensi Bar -->
    <table class="summary-bar">
        <tr>
            <td class="summary-label" style="width: 15%;">{{ __('Ringkasan Presensi:') }}</td>
            <td style="width: 17%;">{{ __('Hadir Tepat Waktu:') }} <strong style="color: #15803d;">{{ $present }}</strong> {{ __('Orang') }}</td>
            <td style="width: 15%;">{{ __('Terlambat:') }} <strong style="color: #b45309;">{{ $late }}</strong> {{ __('Orang') }}</td>
            <td style="width: 15%;">{{ __('Cuti / Izin:') }} <strong style="color: #1d4ed8;">{{ $onLeave }}</strong> {{ __('Orang') }}</td>
            <td style="width: 15%;">{{ __('Alpa:') }} <strong style="color: #b91c1c;">{{ $absent }}</strong> {{ __('Orang') }}</td>
            <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; width: 23%; color: #64748b;">
                {{ __('Total Log:') }} <strong style="color: #0f172a;">{{ $summaries->count() }}</strong> {{ __('Data') }}
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 24px;">{{ __('No') }}</th>
                <th style="width: 60px;">{{ __('Tanggal') }}</th>
                <th style="width: 65px;">{{ __('ID') }}</th>
                <th style="width: 135px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Nama Karyawan') }}</th>
                <th style="width: 120px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Departemen') }}</th>
                <th style="width: 110px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Shift') }}</th>
                <th style="width: 44px;">{{ __('Masuk') }}</th>
                <th style="width: 44px;">{{ __('Pulang') }}</th>
                <th style="width: 52px;">{{ __('Terlambat') }}</th>
                <th style="width: 55px;">{{ __('Total Jam') }}</th>
                <th style="width: 60px;">{{ __('Status') }}</th>
                <th style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 6px; padding-right: 6px;">{{ __('Catatan') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($summaries as $s)
                <tr>
                    <td class="text-center font-mono text-muted">{{ $no++ }}</td>
                    <td class="text-center font-mono">{{ $s->date ? $s->date->format('Y-m-d') : '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $s->employee->employee_code ?? '-' }}</td>
                    <td class="text-start font-bold" style="color: #0f172a; padding-left: 6px; padding-right: 6px;">{{ $s->employee->full_name ?? '-' }}</td>
                    <td class="text-start text-muted" style="padding-left: 6px; padding-right: 6px;">{{ __($s->employee->department->name ?? '-') }}</td>
                    <td class="text-start text-muted" style="padding-left: 6px; padding-right: 6px;">{{ __($s->shift->name ?? 'Reguler') }}</td>
                    <td class="text-center font-mono {{ $s->clock_in ? 'font-bold' : 'text-muted' }}">
                        {{ $s->clock_in ? substr($s->clock_in, 0, 5) : '--:--' }}
                    </td>
                    <td class="text-center font-mono {{ $s->clock_out ? 'font-bold' : 'text-muted' }}">
                        {{ $s->clock_out ? substr($s->clock_out, 0, 5) : '--:--' }}
                    </td>
                    <td class="text-center font-mono">
                        @if($s->late_minutes > 0)
                            <span style="color: #b45309; font-weight: bold;">{{ $s->late_minutes }} m</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center font-mono">
                        {{ $s->work_hours > 0 ? number_format($s->work_hours, 2) . ' j' : '-' }}
                    </td>
                    <td class="text-center">
                        @php
                            $status = $s->status;
                            $rawLabel = ($status === 'absent') ? 'Alpa' : ($s->status_badge['label'] ?? strtoupper($status));
                            $statusLabel = __($rawLabel);
                            $badgeClass = match($status) {
                                'present' => 'badge-present',
                                'late' => 'badge-late',
                                'on_leave', 'sick', 'permit' => 'badge-leave',
                                default => 'badge-absent',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-start text-muted" style="padding-left: 6px; padding-right: 6px;">{{ $s->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted" style="padding: 18px;">
                        {{ __('Tidak ada data presensi yang sesuai dengan parameter filter.') }}
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

</body>
</html>
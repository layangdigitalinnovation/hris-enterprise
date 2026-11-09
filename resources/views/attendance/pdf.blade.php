<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Presensi Harian - {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 8mm 10mm 10mm 10mm;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #0f172a;
            font-size: 8pt;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }

        /* Kop Surat & Header */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .header-left {
            vertical-align: top;
            text-align: left;
        }
        .header-right {
            vertical-align: top;
            text-align: right;
            font-size: 7.5pt;
            color: #475569;
            line-height: 1.35;
        }
        .company-name {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            padding: 0;
        }
        .company-address {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
            margin-bottom: 4px;
        }
        .report-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Ringkasan Presensi (Tabel Baris Rapih Tanpa Card / Dots Berantakan) */
        .summary-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .summary-bar td {
            padding: 4px 6px;
            font-size: 7.5pt;
            vertical-align: middle;
            color: #334155;
        }
        .summary-label {
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.3px;
        }

        /* Tabel Data Presensi */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .data-table thead {
            display: table-header-group;
        }
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
            letter-spacing: 0.2px;
        }
        .data-table td {
            padding: 3.5px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-muted { color: #64748b; }
        .font-mono { font-family: 'Courier New', monospace; }

        /* Badge Status */
        .badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6.8pt;
            font-weight: bold;
            text-align: center;
        }
        .badge-present {
            background-color: #dcfce7;
            color: #15803d;
            border: 0.5px solid #86efac;
        }
        .badge-late {
            background-color: #fef3c7;
            color: #b45309;
            border: 0.5px solid #fde68a;
        }
        .badge-leave {
            background-color: #dbeafe;
            color: #1d4ed8;
            border: 0.5px solid #bfdbfe;
        }
        .badge-absent {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 0.5px solid #fecaca;
        }

        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 40px;
        }
        .signature-title {
            font-weight: bold;
            font-size: 8pt;
            color: #1e293b;
        }
        .signature-space {
            height: 48px;
        }
        .signature-name {
            font-weight: bold;
            font-size: 8pt;
            color: #0f172a;
            text-decoration: underline;
        }
        .signature-role {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat & Header Laporan -->
    <table class="header-table">
        <tr>
            <td class="header-left">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">
                    {{ $companyAddress }}
                    @if ($companyPhone) · Telp: {{ $companyPhone }} @endif
                </div>
                <div class="report-title">
                    LAPORAN REKAPITULASI KEHADIRAN HARIAN
                </div>
            </td>
            <td class="header-right">
                <div>Tanggal Presensi: <strong style="color: #0f172a;">{{ $dateFormatted }}</strong></div>
                @if($selectedDept)
                    <div>Departemen: <strong style="color: #0f172a;">{{ $selectedDept }}</strong></div>
                @else
                    <div>Departemen: <strong style="color: #0f172a;">Semua Departemen</strong></div>
                @endif
                @if($selectedStatus)
                    <div>Filter Status: <strong style="color: #0f172a; text-transform: uppercase;">{{ $selectedStatus }}</strong></div>
                @endif
                <div style="color: #94a3b8; font-size: 7pt; margin-top: 2px;">
                    Dicetak pada: {{ $printedAt }} oleh {{ $signers['maker_name'] }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Presensi: Teks Biasa yang Tertata Rapi dalam Kolom Tanpa Titik Berantakan -->
    <table class="summary-bar">
        <tr>
            <td class="summary-label" style="width: 15%;">
                Ringkasan Presensi:
            </td>
            <td style="width: 17%;">
                Hadir Tepat Waktu: <strong style="color: #15803d;">{{ $present }}</strong> Orang
            </td>
            <td style="width: 15%;">
                Terlambat: <strong style="color: #b45309;">{{ $late }}</strong> Orang
            </td>
            <td style="width: 15%;">
                Cuti / Izin: <strong style="color: #1d4ed8;">{{ $onLeave }}</strong> Orang
            </td>
            <td style="width: 15%;">
                Alpa: <strong style="color: #b91c1c;">{{ $absent }}</strong> Orang
            </td>
            <td style="text-align: right; width: 23%; color: #64748b;">
                Total Karyawan: <strong style="color: #0f172a;">{{ $present + $late + $onLeave + $absent }}</strong> Orang
            </td>
        </tr>
    </table>

    <!-- Tabel Data Presensi (12 Kolom Lengkap Sesuai Excel) -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th style="width: 60px;">Tanggal</th>
                <th style="width: 65px;">ID Karyawan</th>
                <th style="width: 135px; text-align: left; padding-left: 6px;">Nama Karyawan</th>
                <th style="width: 120px; text-align: left; padding-left: 6px;">Departemen</th>
                <th style="width: 110px; text-align: left; padding-left: 6px;">Jadwal Shift</th>
                <th style="width: 44px;">Masuk</th>
                <th style="width: 44px;">Pulang</th>
                <th style="width: 52px;">Terlambat</th>
                <th style="width: 55px;">Total Jam</th>
                <th style="width: 60px;">Status</th>
                <th style="text-align: left; padding-left: 6px;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse ($summaries as $sum)
                <tr>
                    <td class="text-center font-mono text-muted">{{ $no++ }}</td>
                    <td class="text-center font-mono">{{ $sum->date ? $sum->date->format('Y-m-d') : '-' }}</td>
                    <td class="text-center font-mono font-bold">{{ $sum->employee->employee_code ?? '-' }}</td>
                    <td class="text-left font-bold" style="color: #0f172a; padding-left: 6px;">
                        {{ $sum->employee->full_name ?? '-' }}
                    </td>
                    <td class="text-left text-muted" style="padding-left: 6px;">
                        {{ $sum->employee->department->name ?? '-' }}
                    </td>
                    <td class="text-left text-muted" style="padding-left: 6px;">
                        {{ $sum->shift->name ?? 'Reguler' }}
                    </td>
                    <td class="text-center font-mono {{ $sum->clock_in ? 'font-bold' : 'text-muted' }}">
                        {{ $sum->clock_in ? substr($sum->clock_in, 0, 5) : '--:--' }}
                    </td>
                    <td class="text-center font-mono {{ $sum->clock_out ? 'font-bold' : 'text-muted' }}">
                        {{ $sum->clock_out ? substr($sum->clock_out, 0, 5) : '--:--' }}
                    </td>
                    <td class="text-center font-mono">
                        @if($sum->late_minutes > 0)
                            <span style="color: #b45309; font-weight: bold;">{{ $sum->late_minutes }} m</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center font-mono">
                        {{ $sum->work_hours > 0 ? number_format($sum->work_hours, 2) . ' j' : '-' }}
                    </td>
                    <td class="text-center">
                        @php
                            $status = $sum->status;
                            $statusLabel = ($status === 'absent') ? 'Alpa' : ($sum->status_badge['label'] ?? strtoupper($status));
                            $badgeClass = match($status) {
                                'present' => 'badge-present',
                                'late' => 'badge-late',
                                'on_leave', 'sick', 'permit', 'permission' => 'badge-leave',
                                default => 'badge-absent',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-left text-muted" style="padding-left: 6px;">
                        {{ $sum->notes ?: '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center text-muted" style="padding: 18px;">
                        Tidak ada data presensi yang sesuai dengan kriteria filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Kolom Pengesahan & Tanda Tangan (Sesuai Baku Tupoksi & Identik Excel) -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">Dibuat Oleh:</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $signers['maker_name'] }}</div>
                <div class="signature-role">{{ $signers['maker_title'] }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">Mengetahui & Menyetujui:</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $signers['approver_name'] }}</div>
                <div class="signature-role">{{ $signers['approver_title'] }}</div>
            </td>
        </tr>
    </table>

</body>
</html>

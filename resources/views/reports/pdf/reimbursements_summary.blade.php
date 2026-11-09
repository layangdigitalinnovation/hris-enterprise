<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('LAPORAN REKAPITULASI KLAIM REIMBURSEMENT KARYAWAN') }}</title>
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
            font-size: 7.2pt;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-col-meta { vertical-align: top; font-size: 7.2pt; color: #475569; width: 40%; }
        .header-col-company { vertical-align: top; width: 60%; }
        .company-name { font-size: 13pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }
        .company-address { font-size: 7.2pt; color: #64748b; margin-top: 2px; margin-bottom: 3px; }
        .report-title { font-size: 9.5pt; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-top: 2px; }

        .summary-bar {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
        }
        .summary-bar td { padding: 5px 8px; font-size: 7.2pt; vertical-align: middle; color: #334155; }
        .summary-label { font-weight: bold; color: #1e293b; text-transform: uppercase; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 7pt; margin-top: 4px; }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 6.8pt;
            text-transform: uppercase;
            padding: 5px 4px;
            border: 1px solid #334155;
            text-align: center;
            vertical-align: middle;
        }
        .data-table td { padding: 4px 4px; border: 1px solid #cbd5e1; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans', sans-serif; }
        .font-bold { font-weight: bold; }

        .status-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 6.2pt;
            text-transform: uppercase;
        }
        .status-pending { background-color: #fef3c7; color: #b45309; }
        .status-approved { background-color: #dbeafe; color: #1e40af; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-rejected { background-color: #ffe4e6; color: #9f1239; }

        /* Signatures */
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; }
        .signature-cell { width: 50%; text-align: center; vertical-align: top; padding: 0 40px; }
        .signature-role { font-size: 7.5pt; color: #64748b; font-weight: bold; text-transform: uppercase; margin-bottom: 45px; }
        .signature-name { font-weight: bold; font-size: 8pt; color: #0f172a; text-decoration: underline; }
        .signature-title { font-size: 7pt; color: #475569; margin-top: 2px; }

        .footer-note {
            margin-top: 15px;
            font-size: 6.8pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $companyName = $companyName ?? \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = $companyAddress ?? \App\Models\SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = $companyPhone ?? \App\Models\SystemSetting::get('company_phone', '082116925851');
        $periodLabel = $periodLabel ?? '-';
        $branch = $branch ?? __('Semua Cabang');
        $dept = $dept ?? __('Semua Departemen');
        $category = $category ?? __('Semua Kategori');
        $statusText = $statusText ?? __('Semua Status');
        $printedAt = $printedAt ?? now()->translatedFormat('d F Y H:i');
        $makerName = $signers['maker_name'] ?? (auth()->user()?->name ?? 'Administrator');
        $makerTitle = $signers['maker_title'] ?? __('Staff Administrasi HR & Operasional');
        $approverName = $signers['approver_name'] ?? 'Finance & Accounting';
        $approverTitle = $signers['approver_title'] ?? __('Manager Keuangan');

        if (!isset($totalAmountIdr) || !isset($totalApprovedIdr)) {
            $totalAmountIdr = 0;
            $totalApprovedIdr = 0;
            $totalAmountSar = 0;
            $totalApprovedSar = 0;
            if (isset($items)) {
                foreach ($items as $it) {
                    $c = $it->currency ?? 'IDR';
                    if ($c === 'SAR') {
                        $totalAmountSar += (float)$it->amount;
                        $totalApprovedSar += (float)($it->approved_amount ?? $it->amount);
                    } else {
                        $totalAmountIdr += (float)$it->amount;
                        $totalApprovedIdr += (float)($it->approved_amount ?? $it->amount);
                    }
                }
            }
        }
    @endphp

    <!-- Header & Kop Perusahaan -->
    <table class="header-table">
        <tr>
        @if(app()->getLocale() === 'ar')
            {{-- RTL: Metadata di kiri (Cell 1), Kop di kanan (Cell 2) --}}
            <td class="header-col-meta" style="text-align: left;">
                <div style="font-weight: bold; color: #1e40af; margin-bottom: 3px;">{{ __('REKAPITULASI RESMI MANAJEMEN') }}</div>
                <table style="border-collapse: collapse; font-size: 7.2pt; color: #475569; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; width: 35%;">{{ __('Periode') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;">{{ $periodLabel }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Cabang / Dept') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;">{{ __($branch) }} / {{ __($dept) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Kategori / Status') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;">{{ __($category) }} ({{ __($statusText) }})</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; color: #64748b;">{{ $printedAt }} {{ __('oleh') }} {{ $makerName }}</td>
                    </tr>
                </table>
            </td>
            <td class="header-col-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI KLAIM REIMBURSEMENT KARYAWAN') }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN REKAPITULASI KLAIM REIMBURSEMENT KARYAWAN') }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <div style="font-weight: bold; color: #1e40af; margin-bottom: 3px;">{{ __('REKAPITULASI RESMI MANAJEMEN') }}</div>
                <table style="border-collapse: collapse; font-size: 7.2pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Periode') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ $periodLabel }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Cabang / Dept') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ __($branch) }} / {{ __($dept) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Kategori / Status') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ __($category) }} ({{ __($statusText) }})</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; color: #64748b;">{{ $printedAt }} {{ __('oleh') }} {{ $makerName }}</td>
                    </tr>
                </table>
            </td>
        @endif
        </tr>
    </table>

    <!-- Ringkasan Bar -->
    <table class="summary-bar">
        <tr>
            <td class="summary-label" style="width: 14%;">{{ __('Total Data:') }}</td>
            <td style="width: 12%;"><strong>{{ $items->count() }}</strong> {{ __('Transaksi') }}</td>
            <td style="width: 37%;">
                {{ __('Total Diajukan:') }}
                <strong style="color: #1e293b;">
                    Rp {{ number_format($totalAmountIdr, 0, ',', '.') }}
                    @if($totalAmountSar > 0)
                        &nbsp;|&nbsp; SAR {{ number_format($totalAmountSar, 2, ',', '.') }}
                    @endif
                </strong>
            </td>
            <td style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; width: 37%;">
                {{ __('Total Disetujui:') }}
                <strong style="color: #1e3a8a; font-size: 8.5pt;">
                    Rp {{ number_format($totalApprovedIdr, 0, ',', '.') }}
                    @if($totalApprovedSar > 0)
                        &nbsp;|&nbsp; SAR {{ number_format($totalApprovedSar, 2, ',', '.') }}
                    @endif
                </strong>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Rekapitulasi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">{{ __('No') }}</th>
                <th style="width: 90px;">{{ __('No. Klaim') }}</th>
                <th style="width: 65px;">{{ __('Tanggal') }}</th>
                <th style="width: 55px;">{{ __('ID') }}</th>
                <th style="text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 5px; padding-right: 5px;">{{ __('Nama Karyawan') }}</th>
                <th style="width: 95px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 5px; padding-right: 5px;">{{ __('Departemen') }}</th>
                <th style="width: 85px;">{{ __('Kategori') }}</th>
                <th style="width: 130px; text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }}; padding-left: 5px; padding-right: 5px;">{{ __('Keperluan') }}</th>
                <th style="width: 75px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-left: 5px; padding-right: 5px;">{{ __('Diajukan') }}</th>
                <th style="width: 75px; text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; padding-left: 5px; padding-right: 5px;">{{ __('Disetujui') }}</th>
                <th style="width: 60px;">{{ __('Status') }}</th>
                <th style="width: 65px;">{{ __('Metode Bayar') }}</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($items as $item)
                @php
                    $isSar = ($item->currency === 'SAR');
                    $amtDecimals = $isSar ? 2 : 0;
                    $symbol = $isSar ? 'SAR' : 'Rp';
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $no++ }}</td>
                    <td class="text-center font-mono font-bold">{{ $item->claim_number }}</td>
                    <td class="text-center">{{ $item->claim_date ? $item->claim_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center font-mono">{{ $item->employee?->employee_code ?? '-' }}</td>
                    <td class="font-bold" style="padding-left: 5px; padding-right: 5px;">{{ $item->employee?->full_name ?? '-' }}</td>
                    <td style="padding-left: 5px; padding-right: 5px;">{{ __($item->employee?->department?->name ?? '-') }}</td>
                    <td class="text-center">{{ __($item->category?->name ?? '-') }}</td>
                    <td style="padding-left: 5px; padding-right: 5px;">{{ $item->title }}</td>
                    <td class="font-mono text-right" style="padding-left: 5px; padding-right: 5px;">
                        {{ $symbol }} {{ number_format((float)$item->amount, $amtDecimals, ',', '.') }}
                    </td>
                    <td class="font-mono font-bold text-right" style="color: #1e3a8a; padding-left: 5px; padding-right: 5px;">
                        {{ $symbol }} {{ number_format((float)($item->approved_amount ?? $item->amount), $amtDecimals, ',', '.') }}
                    </td>
                    <td class="text-center">
                        <span class="status-badge status-{{ $item->status }}">
                            {{ $item->status_label }}
                        </span>
                    </td>
                    <td class="text-center" style="text-transform: capitalize;">
                        {{ $item->payment_method ? ucfirst($item->payment_method) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 15px; color: #94a3b8;">
                        {{ __('Tidak ada catatan transaksi reimbursement yang sesuai dengan parameter filter.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="8" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; text-transform: uppercase;">
                    {{ __('TOTAL KESELURUHAN (IDR):') }}
                </td>
                <td class="text-right font-mono" style="padding-left: 5px; padding-right: 5px;">
                    Rp {{ number_format($totalAmountIdr, 0, ',', '.') }}
                </td>
                <td class="text-right font-mono font-bold" style="color: #1e3a8a; padding-left: 5px; padding-right: 5px;">
                    Rp {{ number_format($totalApprovedIdr, 0, ',', '.') }}
                </td>
                <td colspan="2"></td>
            </tr>
            @if($totalAmountSar > 0 || $totalApprovedSar > 0)
            <tr style="background-color: #f1f5f9; font-weight: bold;">
                <td colspan="8" style="text-align: {{ app()->getLocale() === 'ar' ? 'left' : 'right' }}; text-transform: uppercase;">
                    {{ __('TOTAL KESELURUHAN (SAR):') }}
                </td>
                <td class="text-right font-mono" style="padding-left: 5px; padding-right: 5px;">
                    SAR {{ number_format($totalAmountSar, 2, ',', '.') }}
                </td>
                <td class="text-right font-mono font-bold" style="color: #1e3a8a; padding-left: 5px; padding-right: 5px;">
                    SAR {{ number_format($totalApprovedSar, 2, ',', '.') }}
                </td>
                <td colspan="2"></td>
            </tr>
            @endif
        </tfoot>
    </table>

    <!-- Tanda Tangan Verifikasi -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-role">{{ __('Diverifikasi & Dibuat Oleh,') }}</div>
                <div class="signature-name">{{ $makerName }}</div>
                <div class="signature-title">{{ __($makerTitle) }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-role">{{ __('Mengetahui & Menyetujui,') }}</div>
                <div class="signature-name">{{ $approverName }}</div>
                <div class="signature-title">{{ __($approverTitle) }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        {{ __('Laporan rekapitulasi reimbursement ini digenerate secara otomatis oleh HRIS Solution Enterprise untuk keperluan audit keuangan internal dan pembukuan kas.') }}
    </div>

</body>
</html>

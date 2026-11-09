<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Laporan Eksekutif Rekapitulasi Portofolio Investor & BEP') }}</title>
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
        .header-col-meta { vertical-align: top; font-size: 7.2pt; color: #475569; width: 35%; }
        .header-col-company { vertical-align: top; width: 65%; }
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
            padding: 5px 3px;
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

        .status-pill {
            display: inline-block;
            padding: 2px 4px;
            font-size: 6.2pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-completed { background-color: #dbeafe; color: #1e40af; }
        .status-paused { background-color: #fef3c7; color: #92400e; }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; page-break-inside: avoid; }
        .signature-cell { width: 50%; text-align: center; vertical-align: top; padding: 0 40px; }
        .signature-title { font-weight: bold; font-size: 8pt; color: #1e293b; }
        .signature-space { height: 45px; }
        .signature-name { font-weight: bold; font-size: 8pt; color: #0f172a; text-decoration: underline; }
        .signature-role { font-size: 7pt; color: #64748b; margin-top: 2px; }
    </style>
</head>
<body>
    @php
        $companyName = $companyName ?? \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = $companyAddress ?? \App\Models\SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = $companyPhone ?? \App\Models\SystemSetting::get('company_phone', '082116925851');
        $printedAt = $printedAt ?? now()->translatedFormat('d F Y H:i:s');
        $financeManagerName = $financeManagerName ?? 'Finance & Accounting';
        $financeManagerTitle = $financeManagerTitle ?? 'Bagian Keuangan';
        $totalProfitShareIdr = $totalProfitShareIdr ?? 0;
        $totalProfitShareSar = $totalProfitShareSar ?? 0;
        $avgBepProgress = $avgBepProgress ?? 0;
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
                        <td style="padding: 1px 0; color: #64748b; width: 40%;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;"><span dir="ltr">{{ date('d/m/Y H:i') }}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Dicetak Oleh') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;">{{ auth()->user()?->name ?? 'Administrator' }}</td>
                    </tr>
                </table>
            </td>
            <td class="header-col-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN EKSEKUTIF PORTOFOLIO & REALISASI MODAL INVESTOR (BEP & BAGI HASIL)') }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN EKSEKUTIF PORTOFOLIO & REALISASI MODAL INVESTOR (BEP & BAGI HASIL)') }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <div style="font-weight: bold; color: #1e40af; margin-bottom: 3px;">{{ __('REKAPITULASI RESMI MANAJEMEN') }}</div>
                <table style="border-collapse: collapse; font-size: 7.2pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ $printedAt }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Dicetak Oleh') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ auth()->user()?->name ?? 'Administrator' }}</td>
                    </tr>
                </table>
            </td>
        @endif
        </tr>
    </table>

    <!-- Ringkasan Eksekutif Finansial Bar -->
    <table class="summary-bar">
        <tr>
            <td class="summary-label" style="width: 15%;">{{ __('Total Investor:') }}</td>
            <td style="width: 10%;"><strong>{{ $investors->count() }}</strong> {{ __('Mitra') }}</td>
            <td style="width: 25%;">
                {{ __('Modal Tertanam:') }}
                @if($totalCapitalSar > 0)
                    <strong style="color: #15803d; font-family: 'DejaVu Sans', sans-serif;">SAR {{ number_format($totalCapitalSar, 2) }}</strong>
                @endif
                @if($totalCapitalSar > 0 && $totalCapitalIdr > 0) | @endif
                @if($totalCapitalIdr > 0)
                    <strong style="color: #1d4ed8; font-family: 'DejaVu Sans', sans-serif;">Rp {{ number_format($totalCapitalIdr, 0, ',', '.') }}</strong>
                @endif
            </td>
            <td style="width: 25%;">
                {{ __('Bagi Hasil Terbayar:') }}
                @if($totalProfitShareSar > 0)
                    <strong style="color: #15803d; font-family: 'DejaVu Sans', sans-serif;">SAR {{ number_format($totalProfitShareSar, 2) }}</strong>
                @endif
                @if($totalProfitShareSar > 0 && $totalProfitShareIdr > 0) | @endif
                @if($totalProfitShareIdr > 0)
                    <strong style="color: #1d4ed8; font-family: 'DejaVu Sans', sans-serif;">Rp {{ number_format($totalProfitShareIdr, 0, ',', '.') }}</strong>
                @endif
            </td>
            <td style="width: 25%; text-align: right;">
                {{ __('Rata-rata BEP Portofolio:') }} <strong style="color: #b45309; font-size: 8.5pt;">{{ $avgBepProgress }}%</strong>
            </td>
        </tr>
    </table>

    <!-- Master Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">{{ __('No') }}</th>
                <th style="width: 9%;">{{ __('Kode') }}</th>
                <th style="width: 14%;">{{ __('Nama Investor') }}</th>
                <th style="width: 12%;">{{ __('Proyek / Cabang') }}</th>
                <th style="width: 4%;">{{ __('Mata Uang') }}</th>
                <th style="width: 10%; text-align: right;">{{ __('Modal Disetor') }}</th>
                <th style="width: 6%;">{{ __('Nisbah %') }}</th>
                <th style="width: 8%; text-align: right;">{{ __('Gaji Khusus') }}</th>
                <th style="width: 10%; text-align: right;">{{ __('Bagi Hasil Cair') }}</th>
                <th style="width: 10%; text-align: right;">{{ __('Sisa BEP') }}</th>
                <th style="width: 7%;">{{ __('Capaian BEP') }}</th>
                <th style="width: 7%;">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($investors as $idx => $inv)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-mono font-bold">{{ $inv->investor_code }}</td>
                    <td class="font-bold">{{ $inv->name }}</td>
                    <td>{{ $inv->branch ? __($inv->branch->name) : __('Unit Terpusat') }}</td>
                    <td class="text-center font-bold" style="color: {{ $inv->currency === 'SAR' ? '#15803d' : '#1d4ed8' }};">
                        {{ $inv->currency }}
                    </td>
                    <td class="text-right font-mono font-bold">{{ format_currency($inv->initial_investment, $inv->currency) }}</td>
                    <td class="text-center">{{ number_format($inv->profit_share_percentage, 1) }}%</td>
                    <td class="text-right font-mono" style="color: #7e22ce;">
                        {{ $inv->has_fixed_stipend ? format_currency($inv->fixed_stipend_amount, $inv->currency) : '-' }}
                    </td>
                    <td class="text-right font-mono font-bold" style="color: #15803d;">
                        {{ format_currency($inv->total_profit_share_paid, $inv->currency) }}
                    </td>
                    <td class="text-right font-mono" style="color: {{ $inv->is_bep_achieved ? '#15803d' : '#b45309' }};">
                        {{ format_currency($inv->bep_remaining, $inv->currency) }}
                    </td>
                    <td class="text-center font-mono font-bold" style="color: {{ $inv->is_bep_achieved ? '#15803d' : '#1d4ed8' }};">
                        {{ $inv->bep_progress_percentage }}%
                    </td>
                    <td class="text-center">
                        @if($inv->status === 'active')
                            <span class="status-pill status-active">{{ __('AKTIF') }}</span>
                        @elseif($inv->status === 'completed')
                            <span class="status-pill status-completed">{{ __('LUNAS') }}</span>
                        @else
                            <span class="status-pill status-paused">{{ __('TUNDA') }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pengesahan Dokumen -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">{{ __('Dibuat & Diverifikasi oleh,') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $financeManagerName }}</div>
                <div class="signature-role">{{ __($financeManagerTitle) }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">{{ __('Disetujui & Disahkan oleh,') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ __('Direktur Utama / Pemilik Bisnis') }}</div>
                <div class="signature-role">{{ __('Direksi Perusahaan') }}</div>
            </td>
        </tr>
    </table>

</body>
</html>

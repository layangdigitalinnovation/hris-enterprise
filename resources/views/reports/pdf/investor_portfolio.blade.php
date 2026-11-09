<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Laporan Portofolio Investor') }} - {{ $investor->investor_code }} - {{ $investor->name }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 12mm 12mm 12mm;
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
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-col-meta { vertical-align: top; font-size: 7.5pt; color: #475569; width: 38%; }
        .header-col-company { vertical-align: top; width: 62%; }
        .company-name { font-size: 13pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; letter-spacing: 0.5px; }
        .company-address { font-size: 7.5pt; color: #64748b; margin-top: 2px; margin-bottom: 4px; }
        .report-title { font-size: 10.5pt; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-top: 4px; }
        .badge-doc { display: inline-block; padding: 2px 6px; font-size: 7pt; font-weight: bold; background-color: #dbeafe; color: #1e40af; border-radius: 3px; }

        .section-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7.8pt; }
        .info-table td { padding: 3px 5px; vertical-align: top; }
        .info-label { width: 22%; color: #64748b; font-weight: normal; }
        .info-sep { width: 2%; text-align: center; color: #94a3b8; }
        .info-val { width: 26%; font-weight: bold; color: #0f172a; }

        /* Metric Highlights Box */
        .metrics-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px 0;
        }
        .metric-cell {
            padding: 8px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            text-align: center;
            vertical-align: middle;
        }
        .metric-title { font-size: 6.8pt; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .metric-value { font-size: 11pt; font-weight: bold; margin-top: 3px; font-family: 'DejaVu Sans', sans-serif; }

        .data-table { width: 100%; border-collapse: collapse; font-size: 7.5pt; margin-top: 5px; }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            font-size: 7pt;
            text-transform: uppercase;
            padding: 5px 4px;
            border: 1px solid #334155;
            text-align: center;
        }
        .data-table td { padding: 4px 5px; border: 1px solid #cbd5e1; vertical-align: middle; }
        .data-table tbody tr:nth-child(even) { background-color: #f8fafc; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'DejaVu Sans', sans-serif; }
        .font-bold { font-weight: bold; }

        .status-pill {
            display: inline-block;
            padding: 2px 5px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 3px;
            text-transform: uppercase;
        }
        .status-paid { background-color: #dcfce7; color: #166534; }
        .status-approved { background-color: #e0e7ff; color: #3730a3; }
        .status-pending { background-color: #fef3c7; color: #92400e; }

        .signature-table { width: 100%; border-collapse: collapse; margin-top: 25px; page-break-inside: avoid; }
        .signature-cell { width: 50%; text-align: center; vertical-align: top; padding: 0 30px; }
        .signature-title { font-weight: bold; font-size: 8pt; color: #1e293b; }
        .signature-space { height: 50px; }
        .signature-name { font-weight: bold; font-size: 8.5pt; color: #0f172a; text-decoration: underline; }
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
    @endphp

    <!-- Header & Kop Perusahaan -->
    <table class="header-table">
        <tr>
        @if(app()->getLocale() === 'ar')
            {{-- RTL: Metadata di kiri (Cell 1), Kop di kanan (Cell 2) --}}
            <td class="header-col-meta" style="text-align: left;">
                <div style="margin-bottom: 3px;"><span class="badge-doc">{{ __('REKAPITULASI RESMI MANAJEMEN') }}</span></div>
                <table style="border-collapse: collapse; font-size: 7.5pt; color: #475569; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; width: 38%;">{{ __('No. Dokumen') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;"><span dir="ltr">INV-STMT/{{ $investor->investor_code }}/{{ date('Y') }}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #0f172a;"><span dir="ltr">{{ date('d/m/Y H:i') }}</span></td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Mata Uang') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #1e3a8a;">{{ $investor->currency === 'SAR' ? __('SAR (Saudi Riyal)') : __('IDR (Rupiah)') }}</td>
                    </tr>
                </table>
            </td>
            <td class="header-col-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN PORTOFOLIO INVESTASI & REALISASI RETURN INVESTOR') }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} @if($companyPhone) · Telp: {{ $companyPhone }} @endif</div>
                <div class="report-title">{{ __('LAPORAN PORTOFOLIO INVESTASI & REALISASI RETURN INVESTOR') }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <div style="margin-bottom: 3px;"><span class="badge-doc">{{ __('REKAPITULASI RESMI MANAJEMEN') }}</span></div>
                <table style="border-collapse: collapse; font-size: 7.5pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('No. Dokumen') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">INV-STMT/{{ $investor->investor_code }}/{{ date('Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #0f172a;">{{ $printedAt }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Mata Uang') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #1e3a8a;">{{ $investor->currency === 'SAR' ? __('SAR (Saudi Riyal)') : __('IDR (Rupiah)') }}</td>
                    </tr>
                </table>
            </td>
        @endif
        </tr>
    </table>

    <!-- 1. Identitas Investor & Proyek -->
    <div class="section-title">{{ __('Ringkasan Investasi & Modal') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Nama Investor / Mitra') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->name }}</td>
            <td class="info-label">{{ __('Cabang / Proyek Tujuan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->branch ? $investor->branch->name : __('Unit Terpusat Perusahaan') }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Kode Unik Investor') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono">{{ $investor->investor_code }}</td>
            <td class="info-label">{{ __('Mulai Penanaman Modal') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->investment_date?->translatedFormat('d F Y') ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('NIK / No. Paspor Resmi') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono">{{ $investor->nik_or_passport ?? '-' }}</td>
            <td class="info-label">{{ __('Target Akhir / Evaluasi') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->contract_end_date?->translatedFormat('d F Y') ?? __('Berkelanjutan') }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Kontak / No. Telepon') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->phone ?? '-' }}</td>
            <td class="info-label">{{ __('Rekening Pencairan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->bank_name ? $investor->bank_name . ' - ' . $investor->bank_account_no : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Email Korespondensi') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->email ?? '-' }}</td>
            <td class="info-label">{{ __('Atas Nama Rekening') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->bank_account_holder ?? $investor->name }}</td>
        </tr>
    </table>

    <!-- 2. Skema & Ketentuan Investasi -->
    <div class="section-title">{{ __('2. Ketentuan Bagi Hasil & Gaji Khusus') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Porsi Bagi Hasil (Nisbah)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="color: #1e40af;">{{ number_format($investor->profit_share_percentage, 2) }}% ({{ $investor->profit_share_scheme === 'net_profit' ? __('Laba Bersih') : ($investor->profit_share_scheme === 'gross_revenue' ? __('Omset Kotor') : __('Tetap')) }})</td>
            <td class="info-label">{{ __('Gaji Khusus Bulanan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono" style="color: #6b21a8;">
                @if($investor->has_fixed_stipend)
                    {{ format_currency($investor->fixed_stipend_amount, $investor->currency) }} {{ __('/ bln') }} ({{ __('Tgl') }} {{ $investor->stipend_payment_day }})
                @else
                    {{ __('Tidak Ada') }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Status Keaktifan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->status === 'active' ? __('AKTIF') : ($investor->status === 'completed' ? __('LUNAS') : __('TUNDA')) }}</td>
            <td class="info-label">{{ __('Perhitungan Gaji ke BEP') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $investor->include_stipend_in_bep ? __('Diperhitungkan ke BEP') : __('Honorarium Murni (Terpisah)') }}</td>
        </tr>
    </table>

    <!-- 3. Analisis & Realisasi BEP (Break-Even Point) -->
    <div class="section-title">{{ __('3. Realisasi Finansial & Capaian Balik Modal (BEP)') }}</div>
    <table class="metrics-grid">
        <tr>
            <td class="metric-cell" style="width: 25%;">
                <div class="metric-title">{{ __('Modal Disetor (Capital)') }}</div>
                <div class="metric-value" style="color: #1e293b;">{{ format_currency($investor->initial_investment, $investor->currency) }}</div>
            </td>
            <td class="metric-cell" style="width: 25%;">
                <div class="metric-title">{{ __('Bagi Hasil Diterima') }}</div>
                <div class="metric-value" style="color: #15803d;">{{ format_currency($investor->total_profit_share_paid, $investor->currency) }}</div>
            </td>
            <td class="metric-cell" style="width: 25%;">
                <div class="metric-title">{{ __('Gaji Khusus Diterima') }}</div>
                <div class="metric-value" style="color: #7e22ce;">{{ format_currency($investor->total_stipend_paid, $investor->currency) }}</div>
            </td>
            <td class="metric-cell" style="width: 25%;">
                <div class="metric-title">{{ __('Total Seluruh Dana Diterima') }}</div>
                <div class="metric-value" style="color: #0369a1;">{{ format_currency($investor->total_distributions_paid, $investor->currency) }}</div>
            </td>
        </tr>
        <tr>
            <td class="metric-cell" style="background-color: #f1f5f9;">
                <div class="metric-title">{{ __('Akumulasi Pengembalian BEP') }}</div>
                <div class="metric-value" style="color: #0f172a;">{{ format_currency($investor->total_return_for_bep, $investor->currency) }}</div>
            </td>
            <td class="metric-cell" style="background-color: {{ $investor->is_bep_achieved ? '#dcfce7' : '#fef3c7' }};">
                <div class="metric-title">{{ __('Sisa Menuju Titik Impas (BEP)') }}</div>
                <div class="metric-value" style="color: {{ $investor->is_bep_achieved ? '#166534' : '#b45309' }};">
                    {{ format_currency($investor->bep_remaining, $investor->currency) }}
                </div>
            </td>
            <td class="metric-cell" style="background-color: {{ $investor->is_bep_achieved ? '#dcfce7' : '#eff6ff' }};">
                <div class="metric-title">{{ __('Capaian Progres BEP') }}</div>
                <div class="metric-value" style="color: {{ $investor->is_bep_achieved ? '#166534' : '#1d4ed8' }};">
                    {{ $investor->bep_progress_percentage }}%
                </div>
            </td>
            <td class="metric-cell" style="background-color: #f1f5f9;">
                <div class="metric-title">{{ __('Status Impas / ROI') }}</div>
                <div class="metric-value" style="font-size: 9.5pt; color: {{ $investor->is_bep_achieved ? '#166534' : '#475569' }};">
                    @if($investor->is_bep_achieved)
                        {{ __('BEP TERCAPAI (ROI: +:roi%)', ['roi' => $investor->roi_percentage]) }}
                    @else
                        {{ __('Balik Modal Berjalan') }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- 4. Riwayat Transaksi Distribusi -->
    <div class="section-title">{{ __('4. Rekapitulasi Riwayat Transaksi & Pembayaran') }}</div>
    @if($investor->distributions->isEmpty())
        <div style="padding: 15px; text-align: center; color: #94a3b8; font-style: italic; border: 1px solid #e2e8f0; margin-top: 5px;">
            {{ __('Belum ada transaksi distribusi bagi hasil atau gaji khusus tercatat.') }}
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 4%;">{{ __('No') }}</th>
                    <th style="width: 17%;">{{ __('No. Transaksi') }}</th>
                    <th style="width: 16%;">{{ __('Jenis Distribusi') }}</th>
                    <th style="width: 13%;">{{ __('Periode') }}</th>
                    <th style="width: 10%;">{{ __('Tgl Jatuh Tempo') }}</th>
                    <th style="width: 16%; text-align: right;">{{ __('Nominal') }}</th>
                    <th style="width: 11%;">{{ __('Status') }}</th>
                    <th style="width: 13%;">{{ __('Ref Pembayaran') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($investor->distributions as $idx => $d)
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td class="font-mono font-bold">{{ $d->distribution_number }}</td>
                        <td>{{ $d->type_label }}</td>
                        <td class="text-center">{{ $d->period_label }}</td>
                        <td class="text-center">{{ $d->distribution_date?->format('d/m/Y') }}</td>
                        <td class="text-right font-mono font-bold">{{ format_currency($d->amount, $d->currency) }}</td>
                        <td class="text-center">
                            @if($d->status === 'paid')
                                <span class="status-pill status-paid">{{ __('LUNAS') }}</span>
                            @elseif($d->status === 'approved')
                                <span class="status-pill status-approved">{{ __('DISETUJUI') }}</span>
                            @else
                                <span class="status-pill status-pending">{{ __('PROSES') }}</span>
                            @endif
                        </td>
                        <td style="font-size: 6.8pt; color: #64748b;">
                            @if($d->payment_date)
                                {{ $d->payment_date->format('d/m/Y') }} · {{ $d->payment_reference ?? $d->payment_method_label }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td colspan="5" class="text-right" style="padding: 6px;">{{ __('TOTAL DISTRIBUSI KESELURUHAN:') }}</td>
                    <td class="text-right font-mono font-bold" style="padding: 6px; color: #1e3a8a;">
                        {{ format_currency($investor->total_distributions_paid, $investor->currency) }}
                    </td>
                    <td colspan="2" style="font-size: 7pt; color: #475569; text-align: center;">
                        {{ __(':count Transaksi Lunas', ['count' => $investor->distributions->where('status', 'paid')->count()]) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    @endif

    <!-- Catatan & Pengesahan -->
    <table class="signature-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-title">{{ __('Pihak Investor / Mitra Modal,') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $investor->name }}</div>
                <div class="signature-role">{{ __('Penanam Modal (:code)', ['code' => $investor->investor_code]) }}</div>
            </td>
            <td class="signature-cell">
                <div class="signature-title">{{ __('Pihak Manajemen Perusahaan,') }}</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $financeManagerName }}</div>
                <div class="signature-role">{{ $financeManagerTitle }}</div>
            </td>
        </tr>
    </table>

</body>
</html>

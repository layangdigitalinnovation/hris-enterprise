<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Surat Perjanjian Pinjaman') }} - {{ $loan->loan_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 7mm 12mm 7mm 12mm;
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
            border-bottom: 2px solid #2563eb;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .header-col-meta { vertical-align: top; font-size: 7pt; color: #475569; width: 38%; }
        .header-col-company { vertical-align: top; width: 62%; }
        .company-name { font-size: 11pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-address { font-size: 7pt; color: #64748b; margin-top: 1px; }
        .doc-title { font-size: 9.5pt; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px; }
        .doc-number { font-size: 8pt; color: #2563eb; font-weight: bold; margin-top: 1px; }

        .section-title {
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 7.8pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
            margin-top: 7px;
            margin-bottom: 4px;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 6px; font-size: 7.5pt; }
        .info-table td { padding: 1.5px 3px; vertical-align: top; }
        .info-label { width: 25%; color: #64748b; }
        .info-sep { width: 2%; text-align: center; color: #94a3b8; }
        .info-val { width: 73%; font-weight: bold; color: #0f172a; }

        .highlight-box {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .highlight-cell {
            padding: 6px 10px;
            vertical-align: middle;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 6px;
            font-size: 7pt;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            padding: 3.5px 5px;
            border: 1px solid #cbd5e1;
            font-size: 6.8pt;
        }
        .data-table td {
            padding: 3px 5px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'DejaVu Sans', sans-serif; }

        .status-badge {
            display: inline-block;
            padding: 1.5px 5px;
            border-radius: 3px;
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-paid { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-unpaid { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        .clause-box {
            background-color: #f8fafc;
            border-left: 2.5px solid #2563eb;
            padding: 4px 8px;
            margin: 5px 0;
            font-size: 6.8pt;
            color: #334155;
            line-height: 1.3;
        }
        .clause-box ol { margin: 0; padding-left: 12px; }
        .clause-box li { margin-bottom: 1.5px; }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            vertical-align: top;
            text-align: center;
            padding: 0 6px;
            font-size: 7.2pt;
        }
        .sig-space { height: 36px; }
        .sig-name { font-weight: bold; color: #0f172a; text-decoration: underline; font-size: 7.8pt; }
        .sig-title { color: #64748b; font-size: 6.8pt; margin-top: 2px; }

        .footer {
            margin-top: 8px;
            font-size: 6pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>
    @php
        $companyNameVal = $companyName ?? \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddressVal = $companyAddress ?? \App\Models\SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhoneVal = $companyPhone ?? \App\Models\SystemSetting::get('company_phone', '082116925851');
        $hrManagerName = $hrManagerName ?? 'HR Administrator';
        $hrManagerTitle = $hrManagerTitle ?? 'HR / Dept Head';
        $financeManagerName = $financeManagerName ?? 'Finance & Accounting';
        $financeManagerTitle = $financeManagerTitle ?? 'Bagian Keuangan';
    @endphp

    <!-- Header -->
    <table class="header-table">
        <tr>
        @if(app()->getLocale() === 'ar')
            {{-- RTL: Metadata di kiri (Cell 1), Kop di kanan (Cell 2) --}}
            <td class="header-col-meta" style="text-align: left;">
                <table style="border-collapse: collapse; font-size: 7pt; color: #475569; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; width: 35%;">{{ __('Status') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #15803d;">{{ strtoupper(__($loan->status_label)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Mata Uang') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; font-weight: bold;">{{ $loan->currency === 'SAR' ? __('SAR (Saudi Riyal)') : __('IDR (Rupiah)') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('Dicetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0;">{{ date('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </td>
            <td class="header-col-company" style="text-align: right;">
                <div class="company-name">{{ $companyNameVal }}</div>
                <div class="company-address">
                    {{ $companyAddressVal }}@if($companyPhoneVal) &bull; Telp: {{ $companyPhoneVal }}@endif
                </div>
                <div class="doc-title">{{ __('Surat Perjanjian & Jadwal Pinjaman Karyawan') }}</div>
                <div class="doc-number">{{ __('Nomor Kontrak:') }} {{ $loan->loan_number }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyNameVal }}</div>
                <div class="company-address">
                    {{ $companyAddressVal }}@if($companyPhoneVal) &bull; Telp: {{ $companyPhoneVal }}@endif
                </div>
                <div class="doc-title">{{ __('Surat Perjanjian & Jadwal Pinjaman Karyawan') }}</div>
                <div class="doc-number">{{ __('Nomor Kontrak:') }} {{ $loan->loan_number }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <table style="border-collapse: collapse; font-size: 7pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Status') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #15803d;">{{ strtoupper(__($loan->status_label)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Mata Uang') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold;">{{ $loan->currency === 'SAR' ? __('SAR (Saudi Riyal)') : __('IDR (Rupiah)') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Dicetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right;">{{ date('d F Y, H:i') }} WIB</td>
                    </tr>
                </table>
            </td>
        @endif
        </tr>
    </table>

    <!-- Highlight Box -->
    <table class="highlight-box">
        <tr>
            <td class="highlight-cell" style="width: 50%;">
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase; font-weight: bold;">{{ __('Pokok Pinjaman Disetujui:') }}</div>
                <div style="font-size: 11pt; font-weight: bold; color: #1e3a8a; margin-top: 1px;">
                    {{ format_currency($loan->effective_principal, $loan->currency) }}
                </div>
                <div style="font-size: 6.8pt; color: #475569; margin-top: 1px;">
                    {{ __('Tenor:') }} <strong>{{ $loan->tenor_months }} {{ __('Bulan') }}</strong> &bull; {{ __('Suku Bunga:') }} <strong>{{ (float)$loan->interest_rate_percent }}%</strong>
                </div>
            </td>
            <td class="highlight-cell text-right" style="width: 50%; border-left: 1px solid #cbd5e1;">
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase; font-weight: bold;">{{ __('Angsuran Per Bulan:') }}</div>
                <div style="font-size: 11pt; font-weight: bold; color: #047857; margin-top: 1px;">
                    {{ format_currency($loan->monthly_installment, $loan->currency) }}
                </div>
                <div style="font-size: 6.8pt; color: #475569; margin-top: 1px;">
                    {{ __('Skema:') }} <strong>{{ $loan->payroll_deduction ? __('Potong Slip Gaji Bulanan') : __('Transfer Mandiri') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Section: Identitas Pegawai -->
    <div class="section-title">{{ __('I. Identitas Karyawan Pemohon') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Nama Lengkap Karyawan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $loan->employee->full_name }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Nomor Induk Karyawan (NIK)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono">{{ $loan->employee->employee_code }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Departemen / Posisi') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $loan->employee->department->name ?? '-' }} &bull; {{ $loan->employee->position->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Lokasi Penempatan / Cabang') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $loan->branch->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Keperluan Pinjaman') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="font-weight: normal; color: #334155;">{{ $loan->reason }}</td>
        </tr>
    </table>

    <!-- Section: Ketentuan & Jadwal Angsuran -->
    <div class="section-title">{{ __('II. Jadwal Cicilan & Rencana Angsuran') }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%;">{{ __('Ke-') }}</th>
                <th style="width: 24%;">{{ __('Tanggal Jatuh Tempo') }}</th>
                <th class="text-right" style="width: 25%;">{{ __('Nominal Cicilan') }}</th>
                <th class="text-center" style="width: 18%;">{{ __('Status') }}</th>
                <th style="width: 25%;">{{ __('Keterangan / Realisasi') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($loan->installments as $inst)
                <tr>
                    <td class="text-center font-bold">{{ $inst->installment_number }}</td>
                    <td class="font-mono">{{ $inst->due_date ? $inst->due_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-right font-mono font-bold">{{ format_currency($inst->amount, $loan->currency) }}</td>
                    <td class="text-center">
                        <span class="status-badge {{ $inst->status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                            {{ $inst->status === 'paid' ? __('LUNAS') : __('BELUM LUNAS') }}
                        </span>
                    </td>
                    <td style="font-size: 6.5pt; color: #64748b;">
                        @if($inst->paid_at)
                            {{ __('Lunas tgl :date (:method)', ['date' => $inst->paid_at->format('d/m/Y'), 'method' => $inst->payment_method_label]) }}
                        @else
                            {{ __('Menunggu jadwal payroll / transfer') }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Section: Ketentuan Pokok Perjanjian -->
    <div class="section-title">{{ __('III. Klausul & Ketentuan Kesepakatan Bersama') }}</div>
    <div class="clause-box">
        <ol>
            <li>{{ __('Karyawan sepakat bahwa pembayaran angsuran dipotong langsung dari perhitungan Gaji Bulanan (Payroll) setiap periode penutupan (cut-off).') }}</li>
            <li>{{ __('Apabila terjadi pemutusan hubungan kerja (PHK) atau pengunduran diri (resign) sebelum seluruh pokok pinjaman lunas, maka sisa saldo pinjaman akan diperhitungkan langsung dari uang pesangon, kompensasi, atau hak gaji terakhir karyawan.') }}</li>
            <li>{{ __('Pelunasan dipercepat (early settlement) diperkenankan setiap saat tanpa dikenakan biaya penalti atau denda administrasi.') }}</li>
            <li>{{ __('Perjanjian ini dibuat secara sadar, tanpa paksaan dari pihak manapun, serta mengikat kedua belah pihak sejak disetujui dan dicairkan.') }}</li>
        </ol>
    </div>

    <!-- Signatures -->
    <table class="signatures-table">
        <tr>
            <td>
                <div>{{ __('Pihak Peminjam,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $loan->employee->full_name }}</div>
                <div class="sig-title">{{ __('Karyawan Pemohon') }} (NIK: {{ $loan->employee->employee_code }})</div>
            </td>
            <td>
                <div>{{ __('Menyetujui (Manajemen),') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $hrManagerName }}</div>
                <div class="sig-title">
                    {{ $loan->approved_at ? $hrManagerTitle . ' (' . __('Tgl:') . ' ' . $loan->approved_at->format('d/m/Y') . ')' : $hrManagerTitle }}
                </div>
            </td>
            <td>
                <div>{{ __('Mengetahui (Pencairan),') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $financeManagerName }}</div>
                <div class="sig-title">
                    {{ $loan->disbursed_at ? $financeManagerTitle . ' (' . __('Tgl:') . ' ' . $loan->disbursed_at->format('d/m/Y') . ')' : $financeManagerTitle }}
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        {{ __('Dokumen ini diterbitkan secara otomatis dan sah melalui Sistem Terintegrasi HRIS :company • ID Dokumen: :id • Halaman 1/1', ['company' => $companyNameVal, 'id' => $loan->loan_number]) }}
    </div>
</body>
</html>

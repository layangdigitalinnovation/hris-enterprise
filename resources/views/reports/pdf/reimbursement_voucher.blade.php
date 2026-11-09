<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Voucher Reimbursement') }} - {{ $reimbursement->claim_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        * { box-sizing: border-box; }
        body {
            direction: {{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif;
            color: #0f172a;
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }
        .header-col-meta { vertical-align: top; font-size: 7.5pt; color: #475569; width: 38%; }
        .header-col-company { vertical-align: top; width: 62%; }
        .company-name { font-size: 13pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-address { font-size: 7.5pt; color: #64748b; margin-top: 2px; }
        .report-title { font-size: 11pt; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-top: 5px; }

        .section-title {
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 8.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 14px;
            margin-bottom: 8px;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 8.2pt; }
        .info-table td { padding: 3px 5px; vertical-align: top; }
        .info-label { width: 22%; color: #64748b; }
        .info-sep { width: 2%; text-align: center; color: #94a3b8; }
        .info-val { width: 26%; font-weight: bold; color: #0f172a; }

        /* Highlight Amount Box */
        .highlight-box {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
            background-color: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 4px;
        }
        .highlight-cell {
            padding: 10px 14px;
            vertical-align: middle;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 14px;
            font-size: 8pt;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            font-size: 7.5pt;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }

        /* Signatures */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 35px;
            font-size: 8pt;
        }
        .signature-table td {
            width: 33.33%;
            vertical-align: top;
            text-align: center;
            padding: 0 10px;
        }
        .signature-role { font-size: 7.5pt; color: #64748b; font-weight: bold; text-transform: uppercase; margin-bottom: 50px; }
        .signature-name { font-weight: bold; color: #0f172a; text-decoration: underline; }
        .signature-title { font-size: 7pt; color: #475569; margin-top: 2px; }

        .footer-note {
            margin-top: 25px;
            font-size: 7pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            padding-top: 6px;
        }
    </style>
</head>
<body>
    @php
        $companyName = $companyName ?? \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddress = $companyAddress ?? \App\Models\SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhone = $companyPhone ?? \App\Models\SystemSetting::get('company_phone', '082116925851');
        $printedAt = $printedAt ?? now()->translatedFormat('d F Y H:i:s');
        $financeManager = $financeManager ?? ['name' => 'Finance & Accounting', 'title' => 'Bagian Keuangan'];
    @endphp

    <!-- Letterhead -->
    <table class="header-table">
        <tr>
        @if(app()->getLocale() === 'ar')
            {{-- RTL: Metadata di kiri (Cell 1), Kop di kanan (Cell 2) --}}
            <td class="header-col-meta" style="text-align: left;">
                <table style="border-collapse: collapse; font-size: 7.5pt; color: #475569; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; font-weight: normal; width: 35%;">{{ __('No. Dokumen') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #2563eb;">{{ $reimbursement->claim_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; font-weight: normal;">{{ __('Status') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0;"><strong style="color: #15803d;">{{ strtoupper(__($reimbursement->status_label)) }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; font-weight: normal;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0;">{{ $printedAt }}</td>
                    </tr>
                </table>
            </td>
            <td class="header-col-company" style="text-align: right;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} | Telp: {{ $companyPhone }}</div>
                <div class="report-title">{{ __('BUKTI PENGELUARAN KAS / REIMBURSEMENT VOUCHER') }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyName }}</div>
                <div class="company-address">{{ $companyAddress }} | Telp: {{ $companyPhone }}</div>
                <div class="report-title">{{ __('BUKTI PENGELUARAN KAS / REIMBURSEMENT VOUCHER') }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <table style="border-collapse: collapse; font-size: 7.5pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('No. Dokumen') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #2563eb; text-align: right;">{{ $reimbursement->claim_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Status') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right;"><strong style="color: #15803d;">{{ strtoupper(__($reimbursement->status_label)) }}</strong></td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Tanggal Cetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right;">{{ $printedAt }}</td>
                    </tr>
                </table>
            </td>
        @endif
        </tr>
    </table>

    <!-- Informasi Pegawai & Rekening -->
    <div class="section-title">{{ __('1. Identitas Karyawan Pemohon') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Nama Karyawan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->employee?->full_name }}</td>
            <td class="info-label">{{ __('Bank Pencairan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->employee?->bank_name ?? 'BCA' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Nomor Induk Pegawai (NIP)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->employee?->employee_code }}</td>
            <td class="info-label">{{ __('Nomor Rekening') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->employee?->bank_account_no ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Departemen / Posisi') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->employee?->department?->name }} • {{ $reimbursement->employee?->position?->title ?? 'Staf' }}</td>
            <td class="info-label">{{ __('Atas Nama Rekening') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->employee?->bank_account_holder ?? $reimbursement->employee?->full_name }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Cabang Penempatan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->branch?->name ?? 'Kantor Pusat' }} ({{ $reimbursement->currency }})</td>
            <td class="info-label">{{ __('Tanggal Pengeluaran') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->claim_date->translatedFormat('d F Y') }}</td>
        </tr>
    </table>

    <!-- Rincian Pengeluaran -->
    <div class="section-title">{{ __('2. Rincian Pengeluaran Yang Diajukan') }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">{{ __('Kategori') }}</th>
                <th style="width: 45%;">{{ __('Keperluan / Deskripsi Pengeluaran') }}</th>
                <th style="width: 25%; text-align: right;">{{ __('Nominal') }} ({{ $reimbursement->currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td>
                    <strong>{{ $reimbursement->category?->name }}</strong><br>
                    <span style="font-size: 7pt; color: #64748b;">Kode: {{ $reimbursement->category?->code }}</span>
                </td>
                <td>
                    <strong>{{ $reimbursement->title }}</strong>
                    @if($reimbursement->description)
                        <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">{{ $reimbursement->description }}</div>
                    @endif
                </td>
                <td style="text-align: right; font-weight: bold; font-size: 9pt;">
                    {{ format_currency($reimbursement->amount, $reimbursement->currency) }}
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc;">
                <td colspan="3" style="text-align: right; font-weight: bold; text-transform: uppercase;">
                    {{ __('Total Nominal Pengajuan:') }}
                </td>
                <td style="text-align: right; font-weight: bold; font-size: 9.5pt;">
                    {{ format_currency($reimbursement->amount, $reimbursement->currency) }}
                </td>
            </tr>
            @if($reimbursement->approved_amount !== null)
            <tr style="background-color: #eff6ff;">
                <td colspan="3" style="text-align: right; font-weight: bold; text-transform: uppercase; color: #1e40af;">
                    {{ __('Total Nominal Disetujui (Approved):') }}
                </td>
                <td style="text-align: right; font-weight: bold; font-size: 10.5pt; color: #1e40af;">
                    {{ format_currency($reimbursement->approved_amount, $reimbursement->currency) }}
                </td>
            </tr>
            @endif
        </tfoot>
    </table>

    <!-- Realisasi Pembayaran & Verifikasi -->
    <div class="section-title">{{ __('3. Informasi Pencairan & Pembayaran Kasir/Finance') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Metode Pembayaran') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->payment_method ? __($reimbursement->payment_method === 'cash' ? 'Tunai / Kas' : ($reimbursement->payment_method === 'petty_cash' ? 'Kas Kecil (Petty Cash)' : 'Transfer Bank')) : '-' }}</td>
            <td class="info-label">{{ __('Nomor Referensi Bank') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->payment_reference ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Tanggal Pencairan Dana') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->paid_at ? $reimbursement->paid_at->translatedFormat('d F Y, H:i') : __('Belum Dicairkan') }}</td>
            <td class="info-label">{{ __('Disahkan Oleh (Finance)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $reimbursement->payer?->name ?? '-' }}</td>
        </tr>
        @if($reimbursement->notes)
        <tr>
            <td class="info-label">{{ __('Catatan Manajemen') }}</td>
            <td class="info-sep">:</td>
            <td colspan="4" class="info-val" style="font-weight: normal; color: #334155;">{{ $reimbursement->notes }}</td>
        </tr>
        @endif
    </table>

    <!-- Tanda Tangan Resmi -->
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-role">{{ __('Pemohon Klaim,') }}</div>
                <div class="signature-name">{{ $reimbursement->employee?->full_name }}</div>
                <div class="signature-title">{{ __('Karyawan Pemohon') }}</div>
            </td>
            <td>
                <div class="signature-role">{{ __('Disetujui Oleh') }},</div>
                <div class="signature-name">{{ $reimbursement->approver?->name ?? '....................................' }}</div>
                <div class="signature-title">{{ __('Atasan Langsung / HR Admin') }}</div>
            </td>
            <td>
                <div class="signature-role">{{ __('Kasir / Finance Pencairan') }},</div>
                <div class="signature-name">{{ $financeManager['name'] }}</div>
                <div class="signature-title">{{ __($financeManager['title']) }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        {{ __('Dokumen voucher klaim ini dicetak secara otomatis melalui Sistem HRIS Enterprise dan berlaku sah sebagai bukti pengeluaran kas internal perusahaan.') }}
    </div>

</body>
</html>


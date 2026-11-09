<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Surat Keterangan Pengalaman Kerja & Clearance Resign') }} - {{ $resignation->resignation_number }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 12mm 8mm 12mm;
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
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .header-col-meta { vertical-align: top; font-size: 7pt; color: #475569; width: 38%; }
        .header-col-company { vertical-align: top; width: 62%; }
        .company-name { font-size: 11pt; font-weight: bold; color: #1e3a8a; text-transform: uppercase; margin: 0; }
        .company-address { font-size: 7pt; color: #64748b; margin-top: 1px; }
        .doc-title { font-size: 9.5pt; font-weight: bold; color: #0f172a; text-transform: uppercase; margin-top: 5px; letter-spacing: 0.3px; }
        .doc-number { font-size: 8pt; color: #2563eb; font-weight: bold; margin-top: 2px; }

        .section-title {
            text-align: {{ app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-size: 7.8pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
            margin-top: 8px;
            margin-bottom: 5px;
        }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7.5pt; }
        .info-table td { padding: 2px 4px; vertical-align: top; }
        .info-label { width: 28%; color: #64748b; }
        .info-sep { width: 2%; text-align: center; color: #94a3b8; }
        .info-val { width: 70%; font-weight: bold; color: #0f172a; }

        .highlight-box {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
        }
        .highlight-cell {
            padding: 7px 12px;
            vertical-align: middle;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 8px;
            font-size: 7.2pt;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            font-size: 7pt;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-completed { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .status-approved { background-color: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .status-pending { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

        .statement-box {
            background-color: #f8fafc;
            border-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}: 3px solid #2563eb;
            padding: 6px 10px;
            margin: 8px 0;
            font-size: 7.2pt;
            color: #334155;
            line-height: 1.45;
            text-align: justify;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            vertical-align: top;
            text-align: center;
            padding: 0 8px;
            font-size: 7.2pt;
        }
        .sig-space { height: 42px; }
        .sig-name { font-weight: bold; color: #0f172a; text-decoration: underline; font-size: 7.8pt; }
        .sig-title { color: #64748b; font-size: 6.8pt; margin-top: 2px; }

        .footer {
            margin-top: 12px;
            font-size: 6.2pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    @php
        $companyNameVal = $companyName ?? \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation');
        $companyAddressVal = $companyAddress ?? \App\Models\SystemSetting::get('company_address', 'Kota Tasikmalaya, Jawa Barat, Indonesia');
        $companyPhoneVal = $companyPhone ?? \App\Models\SystemSetting::get('company_phone', '082116925851');
        $isAr = app()->getLocale() === 'ar';
        $statusClass = match($resignation->status) {
            'completed' => 'status-completed',
            'approved' => 'status-approved',
            default => 'status-pending'
        };
    @endphp

    <!-- Header / Kop Dokumen -->
    <table class="header-table">
        <tr>
        @if($isAr)
            {{-- RTL: Metadata di kiri (Cell 1), Kop di kanan (Cell 2) --}}
            <td class="header-col-meta" style="text-align: left;">
                <table style="border-collapse: collapse; font-size: 7pt; color: #475569; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; width: 35%;">{{ __('حالة الطلب') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #15803d;">{{ strtoupper(__($resignation->status_label)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('رقم الوثيقة') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #2563eb;">{{ $resignation->resignation_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('تاريخ الطباعة') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0;">{{ $printedAt }}</td>
                    </tr>
                </table>
            </td>
            <td class="header-col-company" style="text-align: right;">
                <div class="company-name">{{ $companyNameVal }}</div>
                <div class="company-address">
                    {{ $companyAddressVal }}@if($companyPhoneVal) &bull; {{ __('هاتف') }}: {{ $companyPhoneVal }}@endif
                </div>
                <div class="doc-title">{{ __('شهادة إخلاء طرف وخبرة عمل') }}</div>
                <div class="doc-number">{{ __('رمز المرجع:') }} {{ $resignation->resignation_number }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyNameVal }}</div>
                <div class="company-address">
                    {{ $companyAddressVal }}@if($companyPhoneVal) &bull; Telp: {{ $companyPhoneVal }}@endif
                </div>
                <div class="doc-title">{{ __('SURAT KETERANGAN PENGALAMAN KERJA & CLEARANCE RESIGN') }}</div>
                <div class="doc-number">{{ __('Nomor Dokumen:') }} {{ $resignation->resignation_number }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <table style="border-collapse: collapse; font-size: 7pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Status') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #15803d;">{{ strtoupper(__($resignation->status_label)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('No. Referensi') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #2563eb;">{{ $resignation->resignation_number }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Dicetak') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right;">{{ $printedAt }}</td>
                    </tr>
                </table>
            </td>
        @endif
        </tr>
    </table>

    <!-- Highlight Box Masa Kerja & Posisi -->
    <table class="highlight-box">
        <tr>
            <td class="highlight-cell" style="width: 50%;">
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase; font-weight: bold;">{{ __('Nama Karyawan:') }}</div>
                <div style="font-size: 10.5pt; font-weight: bold; color: #1e3a8a; margin-top: 1px;">
                    {{ $resignation->employee->full_name }}
                </div>
                <div style="font-size: 6.8pt; color: #475569; margin-top: 1px;">
                    {{ __('NIK / ID:') }} <strong>{{ $resignation->employee->employee_code }}</strong> &bull; {{ __('Jabatan:') }} <strong>{{ $resignation->employee->position?->name ?? '-' }}</strong>
                </div>
            </td>
            <td class="highlight-cell {{ $isAr ? 'text-left' : 'text-right' }}" style="width: 50%; border-{{ $isAr ? 'right' : 'left' }}: 1px solid #cbd5e1;">
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase; font-weight: bold;">{{ __('Masa Pengabdian / Kerja:') }}</div>
                <div style="font-size: 10.5pt; font-weight: bold; color: #047857; margin-top: 1px;">
                    {{ $resignation->tenure_at_resignation ?? $resignation->employee->tenure_formatted }}
                </div>
                <div style="font-size: 6.8pt; color: #475569; margin-top: 1px;">
                    {{ __('Periode:') }} <strong>{{ $resignation->employee->join_date ? \Carbon\Carbon::parse($resignation->employee->join_date)->locale(app()->getLocale())->translatedFormat('d M Y') : '-' }}</strong> {{ __('s/d') }} <strong>{{ \Carbon\Carbon::parse($resignation->effective_last_day)->locale(app()->getLocale())->translatedFormat('d M Y') }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian 1: Data Lengkap Karyawan & Pengunduran Diri -->
    <div class="section-title">{{ __('Informasi Pegawai & Pengunduran Diri') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Nama Lengkap') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $resignation->employee->full_name }}</td>
            <td class="info-label">{{ __('Departemen') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $resignation->employee->department?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Nomor Induk Karyawan (NIK)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $resignation->employee->employee_code }}</td>
            <td class="info-label">{{ __('Cabang / Penempatan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $resignation->employee->branch?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Tanggal Mulai Bekerja (Join Date)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $resignation->employee->join_date ? \Carbon\Carbon::parse($resignation->employee->join_date)->locale(app()->getLocale())->translatedFormat('d F Y') : '-' }}</td>
            <td class="info-label">{{ __('Tanggal Pengajuan Resign') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $resignation->submission_date ? \Carbon\Carbon::parse($resignation->submission_date)->locale(app()->getLocale())->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Hari Terakhir Bekerja (Last Working Day)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val" style="color: #1e3a8a;">
                {{ \Carbon\Carbon::parse($resignation->effective_last_day)->locale(app()->getLocale())->translatedFormat('d F Y') }}
                @if($resignation->approved_last_day)
                    <span style="font-size: 6.5pt; color: #16a34a; font-weight: normal;">({{ __('Disetujui Manajemen') }})</span>
                @endif
            </td>
            <td class="info-label">{{ __('Masa Notice Period') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">
                {{ $resignation->notice_days_count }} {{ __('Hari Kalender') }}
                @if($resignation->is_standard_notice)
                    <span style="font-size: 6.5pt; color: #16a34a; font-weight: normal;">({{ __('Memenuhi Ketentuan 30 Hari') }})</span>
                @else
                    <span style="font-size: 6.5pt; color: #d97706; font-weight: normal;">({{ __('Kurang dari 30 Hari') }})</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Alasan Pengunduran Diri') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val" colspan="4">
                <strong>{{ __($resignation->reason_label) }}</strong>
                @if($resignation->reason_detail)
                    - <span style="font-weight: normal; color: #475569;">{{ $resignation->reason_detail }}</span>
                @endif
            </td>
        </tr>
    </table>

    <!-- Bagian 2: Verifikasi Exit Clearance & Tanggungan -->
    <div class="section-title">{{ __('Status Exit Clearance & Bebas Tanggungan Perusahaan') }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">#</th>
                <th style="width: 28%; text-align: {{ $isAr ? 'right' : 'left' }};">{{ __('Komponen Clearance') }}</th>
                <th style="width: 18%; text-align: center;">{{ __('Status Verifikasi') }}</th>
                <th style="width: 48%; text-align: {{ $isAr ? 'right' : 'left' }};">{{ __('Catatan / Keterangan Serah Terima') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center font-bold">1</td>
                <td>
                    <strong>{{ __('Pengembalian Aset & Inventaris') }}</strong>
                    <div style="font-size: 6.2pt; color: #64748b;">{{ __('Laptop, ID Card, Akses Fisik, Kunci & Inventaris Kantor') }}</div>
                </td>
                <td class="text-center">
                    @if($resignation->clearance_assets_returned)
                        <span class="status-badge status-completed">{{ __('CLEAR / SUDAH KEMBALI') }}</span>
                    @else
                        <span class="status-badge status-pending">{{ __('PENDING / BELUM LENGKAP') }}</span>
                    @endif
                </td>
                <td>{{ $resignation->clearance_assets_notes ?: __('Seluruh aset inventaris operasional telah diperiksa dan diserahkan dalam kondisi baik.') }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">2</td>
                <td>
                    <strong>{{ __('Penyelesaian Finansial & Kasbon') }}</strong>
                    <div style="font-size: 6.2pt; color: #64748b;">{{ __('Kewajiban Pinjaman Karyawan, Kasbon, dan Klaim Beban') }}</div>
                </td>
                <td class="text-center">
                    @if($resignation->clearance_finance_settled)
                        <span class="status-badge status-completed">{{ __('CLEAR / LUNAS') }}</span>
                    @else
                        <span class="status-badge status-pending">{{ __('PENDING / BELUM LUNAS') }}</span>
                    @endif
                </td>
                <td>{{ $resignation->clearance_finance_notes ?: __('Tidak terdapat tunggakan kasbon atau pinjaman berjalan yang belum diselesaikan.') }}</td>
            </tr>
            <tr>
                <td class="text-center font-bold">3</td>
                <td>
                    <strong>{{ __('Serah Terima Tugas & Akses Akun') }}</strong>
                    <div style="font-size: 6.2pt; color: #64748b;">{{ __('Transfer Pengetahuan Pekerjaan, Kredensial & Akun Sistem') }}</div>
                </td>
                <td class="text-center">
                    @if($resignation->clearance_handover_completed)
                        <span class="status-badge status-completed">{{ __('CLEAR / SELESAI') }}</span>
                    @else
                        <span class="status-badge status-pending">{{ __('PENDING / BELUM SELESAI') }}</span>
                    @endif
                </td>
                <td>{{ $resignation->clearance_handover_notes ?: ($resignation->handover_notes ?: __('Serah terima tugas pekerjaan telah didelegasikan dan akun sistem dinonaktifkan.')) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Bagian 3: Pernyataan Resmi Perusahaan / Testimonial -->
    <div class="section-title">{{ __('Pernyataan Resmi & Penghargaan Perusahaan') }}</div>
    <div class="statement-box">
        @if($isAr)
            تشهد إدارة {{ $companyNameVal }} بأن الموظف الموضح بياناته أعلاه قد أتم بنجاح كافة إجراءات إخلاء الطرف، وتسليم المهام والأصول المؤسسية وتسوية الالتزامات المالية وفقاً للأنظمة واللوائح المعتمدة، اعتباراً من تاريخ {{ \Carbon\Carbon::parse($resignation->effective_last_day)->locale('ar')->translatedFormat('d F Y') }}. وقد كان طوال فترة خدمته مثالاً للموظف المتفاني والملتزم بمسؤولياته وبحسن السيرة والمهنية. وإذ تصدر هذه الشهادة بناءً على طلبه لتقديمها لمن يهمه الأمر دون أدنى مسؤولية مالية على الشركة، فإننا نتقدم إليه بخالص الشكر والتقدير لجهوده وإسهاماته القيمة طوال فترة عمله، متمنين له دوام التوفيق والنجاح في مستقبله المهني.
        @else
            Surat Keterangan ini diterbitkan untuk menerangkan bahwa <strong>{{ $resignation->employee->full_name }}</strong> (NIK: {{ $resignation->employee->employee_code }}) telah resmi mengakhiri hubungan kerja pada <strong>{{ $companyNameVal }}</strong> efektif terhitung sejak tanggal <strong>{{ \Carbon\Carbon::parse($resignation->effective_last_day)->locale('id')->translatedFormat('d F Y') }}</strong>. Selama masa kerja dan pengabdiannya, yang bersangkutan telah menunjukkan dedikasi, loyalitas, serta integritas dan kinerja yang sangat baik. Seluruh kewajiban tugas, serah terima pekerjaan, dan pengembalian inventaris aset perusahaan telah diselesaikan secara tuntas (Clear). Manajemen perusahaan menyampaikan penghargaan dan terima kasih yang setinggi-tingginya atas segala kontribusi yang telah diberikan, serta mendoakan senantiasa sukses dalam meniti karier dan tantangan profesional di masa mendatang.
        @endif
    </div>

    <!-- Bagian 4: Tanda Tangan Pengesahan -->
    <table class="signatures-table">
        <tr>
            <td>
                <div>{{ __('Karyawan Yang Bersangkutan,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $resignation->employee->full_name }}</div>
                <div class="sig-title">{{ __('ID:') }} {{ $resignation->employee->employee_code }}</div>
            </td>
            <td>
                <div>{{ __('Diverifikasi & Disetujui,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $resignation->approver?->name ?? __('HR Department Head') }}</div>
                <div class="sig-title">{{ __('Human Resources Department') }}</div>
            </td>
            <td>
                <div>{{ __('Mengetahui & Menyetujui,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $resignation->completer?->name ?? 'Direktur Operasional' }}</div>
                <div class="sig-title">{{ $companyNameVal }}</div>
            </td>
        </tr>
    </table>

    <!-- Footer -->
    <div class="footer">
        {{ $companyNameVal }} &bull; {{ __('Dokumen resmi ini dihasilkan secara elektronik melalui sistem HRIS.') }} &bull; {{ __('Halaman 1 dari 1') }}
    </div>
</body>
</html>


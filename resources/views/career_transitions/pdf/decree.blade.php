<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Surat Keputusan Mutasi & Promosi') }} - {{ $careerTransition->transition_number }}</title>
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

        .clause-box {
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
        $officialSkNumber = $careerTransition->letter_number ?: ('SK/' . strtoupper($careerTransition->type) . '/' . date('Ym') . '/' . substr($careerTransition->transition_number, -4));
    @endphp

    <!-- Header / Kop Dokumen -->
    <table class="header-table">
        <tr>
        @if($isAr)
            {{-- RTL: Metadata di kiri (Cell 1), Kop di kanan (Cell 2) --}}
            <td class="header-col-meta" style="text-align: left;">
                <table style="border-collapse: collapse; font-size: 7pt; color: #475569; width: 100%;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; width: 35%;">{{ __('نوع الإجراء') }}</td>
                        <td style="padding: 1px 3px; width: 5%;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #1e3a8a;">{{ strtoupper(__($careerTransition->type_label)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b;">{{ __('رقم القرار') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; font-weight: bold; color: #2563eb;">{{ $officialSkNumber }}</td>
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
                <div class="doc-title">{{ __('قرار إداري بنقل وترقية موظف') }}</div>
                <div class="doc-number">{{ __('رمز المرجع:') }} {{ $careerTransition->transition_number }}</div>
            </td>
        @else
            {{-- LTR: Kop di kiri (Cell 1), Metadata di kanan (Cell 2) --}}
            <td class="header-col-company" style="text-align: left;">
                <div class="company-name">{{ $companyNameVal }}</div>
                <div class="company-address">
                    {{ $companyAddressVal }}@if($companyPhoneVal) &bull; Telp: {{ $companyPhoneVal }}@endif
                </div>
                <div class="doc-title">{{ __('SURAT KEPUTUSAN (SK) MUTASI & PROMOSI KARYAWAN') }}</div>
                <div class="doc-number">{{ __('Nomor SK:') }} {{ $officialSkNumber }}</div>
            </td>
            <td class="header-col-meta" style="text-align: right;">
                <table style="border-collapse: collapse; font-size: 7pt; color: #475569; margin-left: auto;">
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('Jenis SK') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #1e3a8a;">{{ strtoupper(__($careerTransition->type_label)) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 1px 0; color: #64748b; text-align: left;">{{ __('No. Referensi') }}</td>
                        <td style="padding: 1px 3px;">:</td>
                        <td style="padding: 1px 0; text-align: right; font-weight: bold; color: #2563eb;">{{ $careerTransition->transition_number }}</td>
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

    <!-- Highlight Box -->
    <table class="highlight-box">
        <tr>
            <td class="highlight-cell" style="width: 50%;">
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase; font-weight: bold;">{{ __('Nama Karyawan:') }}</div>
                <div style="font-size: 10.5pt; font-weight: bold; color: #1e3a8a; margin-top: 1px;">
                    {{ $careerTransition->employee?->full_name }}
                </div>
                <div style="font-size: 6.8pt; color: #475569; margin-top: 1px;">
                    {{ __('NIK / ID:') }} <strong>{{ $careerTransition->employee?->employee_code }}</strong> &bull; {{ __('Jabatan Baru:') }} <strong>{{ $careerTransition->toPosition?->title ?? '-' }}</strong>
                </div>
            </td>
            <td class="highlight-cell {{ $isAr ? 'text-left' : 'text-right' }}" style="width: 50%; border-{{ $isAr ? 'right' : 'left' }}: 1px solid #cbd5e1;">
                <div style="font-size: 6.8pt; color: #64748b; text-transform: uppercase; font-weight: bold;">{{ __('Tanggal Efektif Berlaku:') }}</div>
                <div style="font-size: 10.5pt; font-weight: bold; color: #047857; margin-top: 1px;">
                    {{ $careerTransition->effective_date ? \Carbon\Carbon::parse($careerTransition->effective_date)->locale(app()->getLocale())->translatedFormat('d F Y') : '-' }}
                </div>
                <div style="font-size: 6.8pt; color: #475569; margin-top: 1px;">
                    {{ __('Status Keputusan:') }} <strong>{{ strtoupper(__($careerTransition->status_label)) }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <!-- Bagian 1: Data Identitas Karyawan -->
    <div class="section-title">{{ __('Identitas Pegawai') }}</div>
    <table class="info-table">
        <tr>
            <td class="info-label">{{ __('Nama Lengkap') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $careerTransition->employee?->full_name }}</td>
            <td class="info-label">{{ __('Departemen') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $careerTransition->toDepartment?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Nomor Induk Karyawan (NIK)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $careerTransition->employee?->employee_code }}</td>
            <td class="info-label">{{ __('Cabang / Penempatan') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $careerTransition->toBranch?->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">{{ __('Tanggal Bergabung (Join Date)') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $careerTransition->employee?->join_date ? \Carbon\Carbon::parse($careerTransition->employee->join_date)->locale(app()->getLocale())->translatedFormat('d F Y') : '-' }}</td>
            <td class="info-label">{{ __('Masa Pengabdian / Kerja') }}</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $careerTransition->employee?->tenure_formatted }}</td>
        </tr>
    </table>

    <!-- Bagian 2: Matriks Perubahan Jabatan & Penempatan (Sebelumnya vs Baru) -->
    <div class="section-title">{{ __('Matriks Komparasi Penugasan (Kondisi Semula vs Kondisi Baru)') }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%; text-align: {{ $isAr ? 'right' : 'left' }};">{{ __('Komponen Penugasan') }}</th>
                <th style="width: 37.5%; text-align: {{ $isAr ? 'right' : 'left' }};">{{ __('Kondisi Semula (Sebelum)') }}</th>
                <th style="width: 37.5%; text-align: {{ $isAr ? 'right' : 'left' }};">{{ __('Kondisi Baru (Setelah Transisi)') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ __('Cabang / Lokasi Penempatan') }}</strong></td>
                <td>{{ $careerTransition->fromBranch?->name ?? '-' }}</td>
                <td style="color: #1e3a8a; font-weight: bold;">{{ $careerTransition->toBranch?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Departemen / Divisi') }}</strong></td>
                <td>{{ $careerTransition->fromDepartment?->name ?? '-' }}</td>
                <td style="color: #1e3a8a; font-weight: bold;">{{ $careerTransition->toDepartment?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Jabatan / Maksud Peran') }}</strong></td>
                <td>{{ $careerTransition->fromPosition?->title ?? '-' }}</td>
                <td style="color: #047857; font-weight: bold;">{{ $careerTransition->toPosition?->title ?? '-' }}</td>
            </tr>
            <tr>
                <td><strong>{{ __('Atasan Langsung (Manager)') }}</strong></td>
                <td>{{ $careerTransition->fromManager?->full_name ?? __('Tidak Ada') }}</td>
                <td style="color: #1e3a8a; font-weight: bold;">{{ $careerTransition->toManager?->full_name ?? __('Tidak Ada') }}</td>
            </tr>
            @if($careerTransition->to_salary !== null)
            <tr>
                <td><strong>{{ __('Gaji Pokok') }}</strong></td>
                <td>
                    @if($careerTransition->from_salary !== null)
                        {{ format_currency($careerTransition->from_salary, $careerTransition->from_currency ?? 'IDR') }}
                    @else
                        -
                    @endif
                </td>
                <td style="color: #047857; font-weight: bold;">
                    {{ format_currency($careerTransition->to_salary, $careerTransition->to_currency ?? 'IDR') }}
                    @if($careerTransition->salary_diff !== null && $careerTransition->salary_diff > 0)
                        (+{{ format_currency($careerTransition->salary_diff, $careerTransition->to_currency ?? 'IDR') }})
                    @endif
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- Bagian 3: Pertimbangan & Ketentuan Keputusan -->
    <div class="section-title">{{ __('Konsideran & Ketentuan Surat Keputusan') }}</div>
    <div class="clause-box">
        @if($isAr)
            بناءً على الصلاحيات المخولة لإدارة {{ $companyNameVal }}، ولما تقتضيه مصلحة العمل وخطة التطوير المؤسسي، تقرر ما يلي:
            <br>
            <strong>أولاً:</strong> اعتماد {{ __($careerTransition->type_label) }} للموظف المذكور أعلاه وفقاً للبيانات الموضحة في جدول المقارنة، اعتباراً من تاريخ {{ \Carbon\Carbon::parse($careerTransition->effective_date)->locale('ar')->translatedFormat('d F Y') }}.
            <br>
            <strong>ثانياً:</strong> يلتزم الموظف باستلام مهام عمله الجديدة وإتمام كافة إجراءات التسليم والتسلم بالتنسيق مع إدارته السابقة والحالية.
            <br>
            <strong>ثالثاً:</strong> يُعمل بهذا القرار من تاريخ سريانه وتُخطر الجهات المعنية لتنفيذ مقتضاه.
        @else
            Menimbang kebutuhan operasional, pengembangan kompetensi organisasi, serta evaluasi kinerja pada {{ $companyNameVal }}, Direksi memutuskan:
            <br>
            <strong>Pertama:</strong> Menetapkan <strong>{{ __($careerTransition->type_label) }}</strong> bagi yang bersangkutan dengan penempatan dan peran baru sebagaimana tertera pada matriks penugasan di atas, terhitung efektif mulai tanggal <strong>{{ \Carbon\Carbon::parse($careerTransition->effective_date)->locale('id')->translatedFormat('d F Y') }}</strong>.
            <br>
            <strong>Kedua:</strong> Yang bersangkutan berkewajiban melakukan serah terima tugas pekerjaan secara tuntas dengan atasan dan rekan kerja terkait serta menjalankan kewajiban baru dengan penuh tanggung jawab dan integritas.
            <br>
            <strong>Ketiga:</strong> Keputusan ini berlaku sejak tanggal ditetapkan dengan ketentuan bahwa apabila terdapat kekeliruan akan diperbaiki sebagaimana mestinya.
        @endif
    </div>

    <!-- Bagian 4: Tanda Tangan Pengesahan -->
    <table class="signatures-table">
        <tr>
            <td>
                <div>{{ __('Karyawan Yang Bersangkutan,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $careerTransition->employee?->full_name }}</div>
                <div class="sig-title">{{ __('ID:') }} {{ $careerTransition->employee?->employee_code }}</div>
            </td>
            <td>
                <div>{{ __('Diverifikasi & Disetujui,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $careerTransition->approver?->name ?? __('HR Department Head') }}</div>
                <div class="sig-title">{{ __('Human Resources Department') }}</div>
            </td>
            <td>
                <div>{{ __('Mengetahui & Menetapkan,') }}</div>
                <div class="sig-space"></div>
                <div class="sig-name">{{ $careerTransition->applier?->name ?? 'Direktur Utama' }}</div>
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


<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <title>{{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'دليل تشغيل نظام إدارة الموارد البشرية HRIS Pro Enterprise' : 'Buku Panduan Operasional HRIS Pro Enterprise' }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 14mm 14mm 14mm;
        }
        * { box-sizing: border-box; }
        body {
            direction: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'right' : 'left' }};
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        
        /* Cover & Headers */
        .cover-box {
            border: 2px solid #1e3a8a;
            border-radius: 8px;
            padding: 18px 22px;
            margin-bottom: 20px;
            background-color: #f8fafc;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .company-title {
            font-size: 13pt;
            font-weight: bold;
            color: #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-title {
            font-size: 15pt;
            font-weight: bold;
            color: #0f172a;
            margin: 4px 0 2px 0;
        }
        .doc-subtitle {
            font-size: 8.5pt;
            color: #475569;
            margin-bottom: 8px;
        }
        .meta-badge {
            display: inline-block;
            background-color: #dbeafe;
            color: #1e40af;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 2px 7px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
            margin-right: 4px;
            margin-left: 4px;
        }
        .meta-badge-green {
            background-color: #dcfce7;
            color: #166534;
            border-color: #bbf7d0;
        }
        .meta-badge-amber {
            background-color: #fef3c7;
            color: #92400e;
            border-color: #fde68a;
        }

        /* Section Styling */
        .chapter-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1.5px solid #2563eb;
            padding-bottom: 4px;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        .subchapter-title {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 9px;
            margin-bottom: 4px;
        }

        /* Content Boxes */
        .step-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 7px 11px;
            margin-bottom: 7px;
            background-color: #ffffff;
        }
        .step-num {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 1px 6px;
            border-radius: 3px;
            margin-right: 5px;
            margin-left: 5px;
        }
        .formula-box {
            background-color: #f1f5f9;
            border-left: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'none' : '3px solid #2563eb' }};
            border-right: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? '3px solid #2563eb' : 'none' }};
            padding: 6px 10px;
            margin: 6px 0;
            font-family: 'DejaVu Sans', monospace;
            font-size: 8pt;
            color: #0f172a;
        }
        .formula-box-saudi {
            background-color: #f0fdf4;
            border-left: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'none' : '3px solid #16a34a' }};
            border-right: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? '3px solid #16a34a' : 'none' }};
        }
        .note-box {
            background-color: #fffbeb;
            border-left: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'none' : '3px solid #d97706' }};
            border-right: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? '3px solid #d97706' : 'none' }};
            padding: 6px 10px;
            margin: 6px 0;
            font-size: 8pt;
            color: #92400e;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.6pt;
            margin-top: 6px;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7pt;
            padding: 5px 6px;
            border: 1px solid #334155;
            text-align: center;
        }
        .data-table td {
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            vertical-align: middle;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-start { text-align: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'right' : 'left' }}; }
        .text-end { text-align: {{ (isset($isAr) && $isAr) || app()->getLocale() === 'ar' ? 'left' : 'right' }}; }
        .font-bold { font-weight: bold; }

        /* Page breaks */
        .page-break {
            page-break-after: always;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 1px solid #cbd5e1;
            padding-top: 5px;
            margin-top: 15px;
            font-size: 7.5pt;
            color: #64748b;
        }
    </style>
</head>
<body>

@php
    $ar = (isset($isAr) && $isAr) || app()->getLocale() === 'ar';
@endphp

    <!-- COVER / HEADER BANNER -->
    <div class="cover-box">
        <table class="header-table">
            <tr>
                <td style="width: 70%; vertical-align: top;">
                    <div class="company-title">{{ $companyName }}</div>
                    <div class="doc-title">{{ $ar ? 'دليل تشغيل نظام إدارة الموارد البشرية' : 'BUKU PANDUAN OPERASIONAL HRIS PRO' }}</div>
                    <div class="doc-subtitle">{{ $ar ? 'دليل تشغيلي متكامل لإدارة رأس المال البشري، الحضور والانصراف، مسير الرواتب ومحفظة المستثمرين' : 'Manual Operasional Komprehensif Sistem Tata Kelola SDM, Presensi, Penggajian & Portofolio Investor' }}</div>
                    <div style="margin-top: 8px;">
                        <span class="meta-badge">HRIS Pro {{ $version }}</span>
                        <span class="meta-badge meta-badge-green">{{ $ar ? 'متعدد الفروع: المملكة العربية السعودية وإندونيسيا' : 'Multi-Cabang: ID (WIB/WITA/WIT) & SA (AST)' }}</span>
                        <span class="meta-badge meta-badge-amber">{{ $ar ? 'عملة مزدوجة: ريال سعودي SAR وروبية إندونيسية IDR' : 'Dual Currency: IDR & SAR' }}</span>
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: {{ $ar ? 'left' : 'right' }};">
                    <div style="font-size: 8pt; color: #64748b; line-height: 1.5;">
                        <strong>{{ $ar ? 'تاريخ الطباعة:' : 'Tanggal Cetak:' }}</strong><br>{{ $generatedAt }}<br>
                        <strong>{{ $ar ? 'المستخدم:' : 'Pencetak:' }}</strong><br>{{ $printedBy }}<br>
                        <strong>{{ $ar ? 'المقر:' : 'Kantor:' }}</strong><br>{{ $companyAddress }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- DAFTAR ISI SINGKAT -->
    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px;">
        <div style="font-weight: bold; font-size: 9pt; color: #1e3a8a; text-transform: uppercase; margin-bottom: 6px;">
            {{ $ar ? 'فهرس الوحدات والمواضيع التشغيلية' : 'Ringkasan Daftar Modul & Topik Panduan' }}
        </div>
        <table style="width: 100%; font-size: 8pt; border-collapse: collapse;">
            <tr>
                <td style="width: 50%; vertical-align: top; {{ $ar ? 'padding-left: 10px;' : 'padding-right: 10px;' }}">
                    @if($ar)
                        1. <strong>الحضور والانصراف وأجهزة البصمة</strong> (السياج الجغرافي، الزيارات الخارجية وأجهزة Solution)<br>
                        2. <strong>الإجازات والإجازات المرضية</strong> (الرصيد المتاح، ومنع التداخل، والتقارير الطبية)<br>
                        3. <strong>العمل الإضافي / التكليف</strong> (المعادلة المزدوجة: إندونيسيا ونظام العمل السعودي)<br>
                        4. <strong>مطالبات واسترداد المصاريف</strong> (التشغيلية والطبية، بالريال والروبية وسندات الصرف)<br>
                        5. <strong>السلف وقروض الموظفين</strong> (محاكاة الأقساط والخصم المباشر من مسير الراتب)
                    @else
                        1. <strong>Presensi & Mesin Biometrik</strong> (GPS Geofencing, Visit & Solution)<br>
                        2. <strong>Cuti & Izin Sakit</strong> (Saldo Kuota, Overlap Check, Surat Dokter)<br>
                        3. <strong>Lembur / SPKL</strong> (Dual Formula: Depnaker RI & UU Arab Saudi)<br>
                        4. <strong>Klaim Reimbursement</strong> (Operasional & Medis, IDR/SAR & Voucher)<br>
                        5. <strong>Kasbon & Pinjaman Karyawan</strong> (Simulasi Tenor & Cicilan Gaji)
                    @endif
                </td>
                <td style="width: 50%; vertical-align: top; {{ $ar ? 'padding-right: 10px;' : 'padding-left: 10px;' }}">
                    @if($ar)
                        6. <strong>دورة مسير الرواتب المتكاملة</strong> (تحويل العملات، التأمينات، الضرائب، وإقفال الدورة)<br>
                        7. <strong>النقل، الترقية والتدرج الوظيفي</strong> (تحديث الملف الإداري والقرارات الإدارية PDF)<br>
                        8. <strong>الاستقالة وإخلاء الطرف</strong> (تسوية العهد، والمستحقات، وشهادة الخبرة PDF)<br>
                        9. <strong>المستثمرون وحصص الأرباح</strong> (رأس المال المستثمر وتتبع نقطة التعادل BEP)<br>
                        10. <strong>المستخدمون، الصلاحيات ومصفوفة الأدوار</strong> (أكثر من 263 صلاحية ومصفوفة RBAC)
                    @else
                        6. <strong>Siklus Payroll Terpadu</strong> (Kurs SAR, BPJS, PPh 21 TER, Lock Period)<br>
                        7. <strong>Mutasi, Promosi & Transisi Karir</strong> (Snapshot Karir & SK PDF)<br>
                        8. <strong>Resign & Offboarding</strong> (Asset Clearance & Paklaring PDF)<br>
                        9. <strong>Investor & Dividen</strong> (Modal Disetor & Progress BEP Portofolio)<br>
                        10. <strong>User, Hak Akses & Matriks Wewenang</strong> (263+ Permission & Role Matrix)
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- BAB 1: PRESENSI & MESIN -->
    <div class="chapter-title">{{ $ar ? '1. وحدة الحضور والانصراف، الزيارات الخارجية وأجهزة البصمة' : '1. Modul Presensi Mandiri, Absen Visit & Mesin Biometrik' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>تسجيل الحضور الذاتي عبر السياج الجغرافي (GPS) دون الحاجة لالتقاط صورة</strong>:
            يقوم الموظف بتسجيل الحضور والانصراف بضغطة زر واحدة عبر متصفح الهاتف، حيث يتحقق النظام تلقائياً من الإحداثيات ومطابقتها مع النطاق المعتمد للفرع (50 إلى 100 متر) بسرعة ودون عناء رفع الصور الشخصية.
        @else
            <strong>Presensi Mandiri GPS Geofencing (Tanpa Perlu Upload Foto)</strong>:
            Karyawan melakukan Clock-In dan Clock-Out melalui peramban ponsel. Sistem secara otomatis mendeteksi koordinat lintang/bujur dan memvalidasi apakah pengguna berada di dalam radius resmi kantor cabang (misal 50–100 meter). Presensi selesai secara 1-klik instan tanpa perlu repot mengambil selfie.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>تسجيل الزيارات الخارجية (المهام الميدانية ومقابلة العملاء)</strong>:
            مخصصة لفرق المبيعات والمهندسين والفنيين. تتيح تسجيل الحضور لعدة زيارات خلال اليوم مع توثيق اسم العميل والغرض والموقع الجغرافي، مع إلزامية إنهاء الزيارة الحالية قبل بدء زيارة جديدة.
        @else
            <strong>Absen Visit (Kunjungan Klien / Dinas Lapangan)</strong>:
            Dikhususkan bagi staf penjualan, teknisi, dan petugas lapangan. Mengizinkan check-in berkali-kali di lokasi klien dengan mencatat nama klien, agenda, alamat, dan geotag GPS. Sistem mewajibkan penyelesaian (Check-Out) sebelum kunjungan berikutnya dapat dibuat.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>المزامنة مع أجهزة البصمة Solution وتصحيح الحضور</strong>:
            سحب سجلات البصمة الحيوية للوجه والأصابع آلياً عبر الشبكة. وفي حال حدوث نسيان أو ظرف طارئ، يمكن للموظف تقديم طلب تصحيح حضور لاعتماده من المدير المباشر.
        @else
            <strong>Sinkronisasi Mesin Biometrik Solution & Koreksi Presensi</strong>:
            Penarikan log sidik jari dan wajah dari mesin fisik via jaringan IP/Port. Jika terjadi lupa absen atau tugas mendadak, karyawan dapat mengajukan formulir Koreksi Presensi dengan persetujuan Manager.
        @endif
    </div>

    <!-- BAB 2: CUTI & IZIN SAKIT -->
    <div class="chapter-title">{{ $ar ? '2. وحدة الإجازات، الإجازات المرضية ومزامنة الأرصدة' : '2. Modul Cuti, Izin Sakit & Saldo Kuota' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>تقديم طلب الإجازة والمرفقات الطبية</strong>:
            يحدد الموظف نوع الإجازة (سنوية، وضع، حج وعمرة، مرضية) وتواريخها، مع إرفاق التقرير الطبي المعتمد في حال الإجازات المرضية.
        @else
            <strong>Pengajuan Cuti & Lampiran Medis</strong>:
            Karyawan memilih jenis cuti (Tahunan, Melahirkan, Ibadah Haji/Umrah, Sakit) dan rentang tanggal. Untuk izin sakit, karyawan melampirkan foto/scan Surat Keterangan Dokter (MC).
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>ضوابط العمل والتحقق الآلي</strong>:
            يمنع النظام آلياً تقديم أي إجازة تتداخل مع إجازة سابقة معتمدة، كما لا تُحتسب عطلات نهاية الأسبوع والأعياد الرسمية خصماً من رصيد الإجازات السنوية.
        @else
            <strong>Aturan Bisnis & Validasi Sistem</strong>:
            Sistem memblokir pengajuan yang bertabrakan (<em>overlapping</em>) dengan jadwal izin yang telah disetujui. Hari libur nasional dan akhir pekan tidak mengurangi kuota saldo cuti tahunan.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>سلسلة الاعتمادات الفورية وتحديث الرصيد</strong>:
            يتلقى المدير المباشر إشعاراً فورياً للتحقق من الطلب، وبمجرد الموافقة يتم خصم الأيام مباشرة وتحديث سجل الحضور والانصراف.
        @else
            <strong>Approval Berjenjang Real-Time</strong>:
            Atasan langsung (Manager/Supervisor) menerima notifikasi lonceng dan memverifikasi pengajuan. Kuota terpotong otomatis begitu status berubah menjadi <em>Approved</em>.
        @endif
    </div>

    <!-- BAB 3: LEMBUR / SPKL DUAL FORMULA -->
    <div class="chapter-title">{{ $ar ? '3. وحدة العمل الإضافي والمعادلتان الرسميتان (إندونيسيا والمملكة العربية السعودية)' : '3. Modul Lembur / SPKL & Dual Formula Resmi Lintas Negara' }}</div>
    <div class="step-card">
        @if($ar)
            <strong>إجراءات أوامر التكليف بالعمل الإضافي</strong>: تُرفع من الموظف أو المشرف متضمنة تاريخ وساعات العمل الإضافي والمهام المستهدفة إنجازها، وتعتمد إلكترونياً من المدير المباشر.
        @else
            <strong>Alur Pengajuan SPKL</strong>: Diajukan oleh staf atau dibuatkan supervisor dengan mengisi tanggal kerja lembur, jam mulai-selesai, serta target output penugasan. Disahkan oleh Manager melalui otorisasi online.
        @endif
    </div>

    <div class="subchapter-title">
        {{ $ar ? 'أ. معادلة وزارة العمل الإندونيسية (اللائحة الحكومية رقم 35 لسنة 2021) - الفروع الإندونيسية (IDR)' : 'A. Formula Regulasi Depnaker RI (PP No. 35/2021) - Cabang Indonesia (IDR)' }}
    </div>
    <div class="formula-box">
        @if($ar)
            <strong>أجر الساعة</strong> = 1 / 173 × (الراتب الأساسي + البدلات الثابتة)<br>
            &bull; <strong>أيام العمل العادية</strong>: الساعة الأولى تُحتسب بـ <strong>1.5 ضعف</strong> أجر الساعة، والساعات التالية بـ <strong>ضعفي (2.0x)</strong> أجر الساعة.<br>
            &bull; <strong>أيام العطلات الأسبوعية والرسمية</strong>: الساعات من 1 إلى 7 بـ <strong>2.0 ضعف</strong>، والساعة التالية بـ <strong>3.0 أضعاف</strong>، وما زاد عن ذلك بـ <strong>4.0 أضعاف</strong> أجر الساعة.
        @else
            <strong>Upah Sejam</strong> = 1 / 173 &times; (Gaji Pokok + Tunjangan Tetap)<br>
            &bull; <strong>Hari Kerja Biasa</strong>: Jam ke-1 dihitung <strong>1.5x</strong> upah per jam, jam ke-2 dst dihitung <strong>2.0x</strong>.<br>
            &bull; <strong>Hari Libur / Istirahat</strong>: Jam ke 1&ndash;7/8 dihitung <strong>2.0x</strong>, jam ke-8/9 dihitung <strong>3.0x</strong>, jam ke-10+ dihitung <strong>4.0x</strong>.
        @endif
    </div>

    <div class="subchapter-title">
        {{ $ar ? 'ب. معادلة المادة 107 من نظام العمل السعودي - فروع المملكة العربية السعودية (SAR)' : 'B. Formula Pasal 107 UU Ketenagakerjaan Arab Saudi (المادة 107) - Cabang Arab Saudi (SAR)' }}
    </div>
    <div class="formula-box formula-box-saudi">
        @if($ar)
            <strong>أجر الساعة الإضافية</strong> = الأجر الفعلي للساعة + 50% من الأجر الأساسي للساعة<br>
            &bull; <strong>الأجر الفعلي للساعة</strong> = (الراتب الأساسي + سائر البدلات) ÷ 30 يوماً ÷ ساعات العمل اليومية (8 ساعات).<br>
            &bull; <strong>العلاوة الإضافية 50%</strong> = 50% × (الراتب الأساسي ÷ 30 يوماً ÷ ساعات العمل اليومية).<br>
            &bull; <strong>إجمالي بدل العمل الإضافي</strong> = إجمالي الساعات × أجر الساعة الإضافية (يعادل الأجر الفعلي كاملاً مضافاً إليه 50% من الأجر الأساسي).
        @else
            <strong>Tarif Lembur per Jam</strong> = Upah Riil per Jam + 50% Upah Pokok per Jam<br>
            &bull; <strong>Upah Riil per Jam</strong> = (Gaji Pokok + Tunjangan Tetap) &divide; 30 hari &divide; Jam Normal Harian (8 jam).<br>
            &bull; <strong>Premi Tambahan 50%</strong> = 50% &times; (Gaji Pokok &divide; 30 hari &divide; Jam Normal Harian).<br>
            &bull; <strong>Total Upah Lembur</strong> = Total Jam Lembur &times; Tarif Lembur per Jam.
        @endif
    </div>

    <div class="note-box">
        @if($ar)
            <strong>🌙 ضوابط شهر رمضان المبارك (المادة 98 من نظام العمل السعودي)</strong>: تُخفض ساعات العمل اليومية الفعلية للمسلمين إلى 6 ساعات في اليوم (180 ساعة شهرياً). ويوفر النظام خيار تفعيل شهر رمضان آلياً لإعادة احتساب أجر ساعة الإضافي بصورة تصاعدية تتوافق مع تخفيض ساعات العمل النظامية.
        @else
            <strong>🌙 Ketentuan Khusus Bulan Ramadan (Pasal 98 UU KSA)</strong>: Jam kerja harian normal berkurang dari 8 jam menjadi 6 jam/hari (pembagi bulanan 180 jam). Sistem menyediakan konfigurasi otomatis Bulan Ramadan sehingga upah lembur per jam meningkat secara proporsional sesuai hukum syariah ketenagakerjaan.
        @endif
    </div>

    <div class="page-break"></div>

    <!-- BAB 4: REIMBURSEMENT -->
    <div class="chapter-title">{{ $ar ? '4. وحدة مطالبات واسترداد المصاريف (التشغيلية والطبية)' : '4. Modul Klaim Reimbursement (Operasional & Medis)' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>تسجيل الفواتير واختيار العملة</strong>:
            دعم متعدد للعملات بالريال السعودي (SAR) للمصروفات داخل المملكة، أو بالروبية (IDR) لمصروفات إندونيسيا، مع إرفاق صور الفواتير وسندات الدفع الأصلية.
        @else
            <strong>Input Nota & Pilihan Mata Uang</strong>:
            Mendukung multi-mata uang: Rupiah (IDR) untuk pengeluaran dinas domestik atau Saudi Riyal (SAR) untuk pengeluaran di Arab Saudi. Karyawan mengunggah foto nota/kuitansi fisik asli.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>التدقيق واعتماد الصلاحيات</strong>:
            مراجعة واعتماد المدير المباشر وإدارة الموارد البشرية للتأكد من مطابقة المصروفات للائحة وسياسة الميزانية التشغيلية للشركة.
        @else
            <strong>Otorisasi & Verifikasi Keabsahan</strong>:
            Manager/HR memvalidasi bahwa pengeluaran sesuai plafon anggaran operasional perusahaan sebelum disahkan ke bagian Keuangan.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>التحويل المالي وسند الصرف (PDF)</strong>:
            تقوم الإدارة المالية بالتحويل وتوثيق رقم الحوالة، وإصدار سند صرف خزينة رسمي معتمد بصيغة PDF برقم محاسبي فريد.
        @else
            <strong>Pencairan Finance & Cetak Voucher Kas PDF</strong>:
            Finance menginput bank pengirim dan nomor referensi transfer. Sistem menyediakan cetak dokumen resmi Voucher Bukti Pengeluaran Kas format PDF lengkap dengan nomor unik akuntansi.
        @endif
    </div>

    <!-- BAB 5: KASBON & PINJAMAN KARYAWAN -->
    <div class="chapter-title">{{ $ar ? '5. وحدة سلف وقروض الموظفين (بدون فوائد 0%)' : '5. Modul Pinjaman Karyawan & Kasbon (Bunga 0%)' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>طلب السلفة وتحديد مدة الأقساط</strong>:
            خدمة تكافلية للموظفين بدون فوائد، يختار الموظف المبلغ ومدة السداد (3، 6، أو 12 شهراً).
        @else
            <strong>Pengajuan Plafon & Tenor</strong>:
            Fasilitas kesejahteraan resmi perusahaan dengan bunga 0%. Karyawan memilih nominal dan jumlah bulan angsuran (misal tenor 3, 6, atau 12 bulan).
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>دراسة الأهلية والاعتماد المالي</strong>:
            تدقيق الموارد البشرية والمالية لضمان عدم تجاوز القسط للحد الأقصى المسموح باستقطاعه نظامياً من الراتب.
        @else
            <strong>Persetujuan & Pencairan Dana</strong>:
            Finance dan HR menilai kelayakan cicilan agar tidak melampaui batas wajar kemampuan potong gaji bulanan.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>الاستقطاع الآلي من مسير الراتب</strong>:
            يخصم القسط تلقائياً من قسيمة الراتب في نهاية كل شهر حتى تمام سداد كامل أصل السلفة.
        @else
            <strong>Pemotongan Otomatis Slip Gaji</strong>:
            Setiap siklus penggajian bulanan, nilai angsuran kasbon otomatis dipotongkan dari slip gaji karyawan hingga saldo pinjaman mencapai lunas.
        @endif
    </div>

    <!-- BAB 6: SIKLUS PAYROLL TERPADU -->
    <div class="chapter-title">{{ $ar ? '6. وحدة دورة مسير الرواتب (Payroll)، أسعار الصرف، التأمينات والضرائب' : '6. Modul Siklus Penggajian (Payroll), Kurs SAR & PPh 21 TER' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>فتح دورة الرواتب وتحديد فترة الإقفال (Cut-Off)</strong>:
            بدء الشهر المالي وتحديد تاريخ حصر الحضور والعمل الإضافي والغياب.
        @else
            <strong>Buka Periode Penggajian & Cut-Off</strong>:
            Finance membuka periode penggajian baru dan menentukan tanggal batas penarikan data presensi dan lembur.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>تحديث أسعار الصرف للريال السعودي مقابل الروبية</strong>:
            مزامنة القوائم المالية بين فروع المملكة وإندونيسيا بدقة لتقارير الإدارة العليا.
        @else
            <strong>Update Kurs Konversi SAR ke IDR</strong>:
            Khusus cabang Arab Saudi, input kurs resmi mata uang Riyal (SAR) ke Rupiah (IDR) guna pelaporan keuangan konsolidasi holding.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>الاحتساب الآلي الشامل بنقرة واحدة</strong>:
            احتساب الرواتب الأساسية، والبدلات، والعمل الإضافي (بمعادلة إندونيسيا أو المادة 107 السعودية)، وخصم التأخير، والسلف، والتأمينات الاجتماعية والضرائب بدقة تامة.
        @else
            <strong>Kalkulasi Otomatis 1-Klik</strong>:
            Sistem menghitung Gaji Pokok, Tunjangan Jabatan, Upah Lembur SPKL (Formula Depnaker / Pasal 107), Potongan Telat/Alpha, Angsuran Pinjaman, BPJS Ketenagakerjaan/Kesehatan, dan tarif efektif pajak PPh 21 TER bulanan.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">4</span>
        @if($ar)
            <strong>إقفال الدورة (Lock Period) وإصدار قسائم الرواتب</strong>:
            اعتماد الدورة وتثبيتها لمنع أي تعديل يدوي، ونشر قسائم الرواتب الإلكترونية الفورية في البوابة الذاتية للموظفين.
        @else
            <strong>Kunci Payroll (Lock Period) & Distribusi E-Slip</strong>:
            Setelah draft diverifikasi, klik tombol <strong>Sahkan & Kunci Payroll</strong>. Data slip gaji terkunci permanen dari revisi manual dan langsung terbit di portal mandiri (ESS) karyawan.
        @endif
    </div>

    <!-- BAB 7: MUTASI, PROMOSI & TRANSISI KARIR -->
    <div class="chapter-title">{{ $ar ? '7. وحدة النقل والترقية والتنقلات الوظيفية' : '7. Modul Mutasi, Promosi & Transisi Karir' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>إعداد المقترح والمقارنة التلقائية</strong>:
            اختيار نوع الحركة (ترقية وظيفية، نقل بين الفروع، تعديل راتب) مع عرض مقارن للبيانات الحالية والمقترحة.
        @else
            <strong>Penyusunan Usulan Karir</strong>:
            HR/Manager memilih jenis perubahan: Promosi (naik jabatan), Mutasi Cabang (rotasi antar kantor), atau Penyesuaian Gaji. Sistem menampilkan perbandingan otomatis data lama vs usulan baru.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>التطبيق الفوري والقرار الإداري (PDF)</strong>:
            بمجرد اعتماد الإدارة، يتم تطبيق القرار وتحديث ملف الموظف فوراً وإصدار القرار الإداري الرسمي ثنائي اللغة (PDF).
        @else
            <strong>Penerapan Otomatis (Apply) & Surat Keputusan PDF</strong>:
            Setelah disahkan Direksi, klik <strong>Terapkan (Apply)</strong>. Data master karyawan langsung diperbarui dan sistem menerbitkan dokumen formal Surat Keputusan (SK) format PDF bilingual.
        @endif
    </div>

    <!-- BAB 8: RESIGN & OFFBOARDING -->
    <div class="chapter-title">{{ $ar ? '8. وحدة الاستقالة، تسوية العهد، وشهادة الخدمة (إخلاء الطرف)' : '8. Modul Resign, Asset Clearance & Surat Paklaring' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>تقديم الاستقالة ذاتياً</strong>:
            إدخال آخر يوم عمل وفق مهلة الإشعار النظامية المسبقة (One-Month Notice).
        @else
            <strong>Pengajuan Resign Mandiri</strong>:
            Karyawan mengajukan pengunduran diri dengan menginput tanggal hari kerja terakhir sesuai standar <em>one-month notice</em>.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>إخلاء الطرف المالي والعهد</strong>:
            استلام الأجهزة والمقتنيات والتأكد من عدم وجود سلف قائمة وتسوية الذمم المالية.
        @else
            <strong>Asset & Financial Clearance</strong>:
            Pemeriksaan pengembalian laptop kantor, ID card, dan kartu akses. Sistem memunculkan notifikasi peringatan jika karyawan masih memiliki sisa kasbon aktif.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>إصدار شهادة الخبرة والخدمة (PDF)</strong>:
            إيقاف صلاحية الدخول وإصدار شهادة الخبرة وإخلاء الطرف الرسمية المعتمدة بصيغة PDF.
        @else
            <strong>Penerbitan Surat Pengalaman Kerja (Paklaring PDF)</strong>:
            Setelah semua checklist selesai, akun login dinonaktifkan secara aman dan Surat Pengalaman Kerja (Paklaring) resmi PDF diterbitkan dengan tanda tangan digital direksi.
        @endif
    </div>

    <div class="page-break"></div>

    <!-- BAB 9: INVESTOR & DIVIDEN -->
    <div class="chapter-title">{{ $ar ? '9. وحدة المستثمرين، توزيع الأرباح، ونقطة التعادل (BEP)' : '9. Modul Khusus Investor, Dividen & Balik Modal (BEP)' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>تسجيل رأس المال المستثمر</strong>:
            توثيق الحصص ورأس المال بالريال أو الروبية مع نسب الأرباح المتفق عليها والشروط الاستثمارية.
        @else
            <strong>Pencatatan Modal Disetor</strong>:
            Pencatatan modal investasi pemilik/investor dalam IDR atau SAR lengkap dengan persentase bagi hasil dan klausul penarikan.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>توزيع الأرباح واحتساب نقطة التعادل</strong>:
            تسجيل دفعات الأرباح الموزعة وحساب المتبقي لاسترداد رأس المال بالكامل (Break-Even Point).
        @else
            <strong>Distribusi Dividen & Progres BEP</strong>:
            Finance mencatat dividen laba berkala. Sistem mengkalkulasi akumulasi pengembalian modal dan memproyeksikan sisa waktu titik impas (<em>Break-Even Point</em>).
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>البوابة الذاتية للمستثمر</strong>:
            متابعة المحفظة والأرباح بخصوصية تامة دون الاطلاع على رواتب الموظفين.
        @else
            <strong>Portal Investor Mandiri</strong>:
            Akses login khusus investor untuk memantau saldo modal, histori transfer bagi hasil, dan mengunduh laporan keuangan eksekutif tanpa melihat data privasi gaji staf.
        @endif
    </div>

    <!-- BAB 10: USER & RBAC -->
    <div class="chapter-title">{{ $ar ? '10. إدارة المستخدمين، نطاق الفروع، والصلاحيات المتدرجة (RBAC)' : '10. Manajemen User, Scope Cabang & Hak Akses Berjenjang' }}</div>
    <div class="step-card">
        <span class="step-num">1</span>
        @if($ar)
            <strong>الفصل بين حساب المستخدم والملف الوظيفي</strong>:
            مرونة ربط الحسابات مع حماية خصوصية بيانات الإدارة ومستويات النفاذ.
        @else
            <strong>Dual Identitas Terisolasi (User vs Employee)</strong>:
            Kredensial login akun pengguna terpisah namun tersinkronisasi dengan biodata karyawan operasional, menjamin integritas privasi akun direksi dan pihak ketiga.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">2</span>
        @if($ar)
            <strong>تحديد النطاق الجغرافي للفروع</strong>:
            قصر صلاحية منسوبي الفروع على بيانات فرعهم فقط ومنع التداخل بين الفروع في المملكة وإندونيسيا.
        @else
            <strong>Cakupan Wewenang Wilayah (Branch & Department Scope)</strong>:
            Membatasi wewenang staf cabang Surabaya agar hanya dapat mengelola data cabangnya sendiri tanpa melihat kantor Jakarta atau cabang Arab Saudi.
        @endif
    </div>
    <div class="step-card">
        <span class="step-num">3</span>
        @if($ar)
            <strong>أكثر من 263 صلاحية وسجل تدقيق أمني شامل</strong>:
            تحكم تفصيلي كامل مع تسجيل كافة الحركات وعناوين IP وسجل تدقيق دقيق لكافة الأنشطة الحساسة.
        @else
            <strong>263+ Permissions & Audit Trail</strong>:
            Pengaturan wewenang granuler (lihat, buat, ubah, hapus, setujui) berbasis Spatie Permission lengkap dengan pencatatan log jejak aktivitas pengguna.
        @endif
    </div>

    <!-- MATRIKS HAK AKSES -->
    <div class="chapter-title">{{ $ar ? '11. مصفوفة مقارنة الصلاحيات والأدوار (Role Matrix)' : '11. Matriks Hak Akses Komparatif (Role Matrix)' }}</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25%; text-align: {{ $ar ? 'right' : 'left' }};">{{ $ar ? 'الوحدة / خاصية النظام' : 'Modul Fungsional' }}</th>
                <th style="width: 12%;">{{ $ar ? 'المدير العام' : 'Super Admin' }}</th>
                <th style="width: 12%;">{{ $ar ? 'الموارد البشرية' : 'HR Admin' }}</th>
                <th style="width: 13%;">{{ $ar ? 'المدير المباشر' : 'Manager' }}</th>
                <th style="width: 13%;">{{ $ar ? 'المالية' : 'Finance' }}</th>
                <th style="width: 13%;">{{ $ar ? 'الموظف الذاتي' : 'Karyawan ESS' }}</th>
                <th style="width: 12%;">{{ $ar ? 'المستثمر' : 'Investor' }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">{{ $ar ? 'الحضور والانصراف وتصحيح البصمة' : 'Presensi & Koreksi' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'إدارة وملخص' : 'Kelola & Rekap' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'اعتماد فريقه' : 'Approve Tim' }}</td>
                <td class="text-center" style="color: #64748b;">{{ $ar ? 'عرض الملخص' : 'Lihat Rekap' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'حضور ذاتي' : 'Absen Mandiri' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'الإجازات والإجازات المرضية' : 'Cuti & Izin Sakit' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'ضبط الأرصدة' : 'Atur Kuota' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'اعتماد فريقه' : 'Approve Tim' }}</td>
                <td class="text-center" style="color: #64748b;">{{ $ar ? 'عرض التقارير' : 'Lihat Laporan' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'تقديم إجازة' : 'Ajukan Cuti' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'العمل الإضافي / التكليف' : 'Lembur / SPKL' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'ضبط التعريفات' : 'Atur Tarif' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'اعتماد التكليف' : 'Approve SPKL' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'احتساب الرواتب' : 'Kalkulasi Gaji' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'رفع تكليف' : 'Ajukan SPKL' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'مطالبات استرداد المصاريف' : 'Klaim Reimbursement' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'مراجعة الموارد' : 'Review HR' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'اعتماد فريقه' : 'Approve Tim' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صرف وسندات' : 'Cairkan & Kas' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'رفع الفواتير' : 'Ajukan Nota' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'السلف وقروض الموظفين' : 'Kasbon & Pinjaman' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'تدقيق الطلب' : 'Verifikasi' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'توصية المدير' : 'Rekomendasi' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'اعتماد وصرف' : 'Approve & Cair' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'طلب سلفة' : 'Ajukan Tenor' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'مسير الرواتب (Payroll)' : 'Siklus Penggajian (Payroll)' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center" style="color: #64748b;">{{ $ar ? 'عرض البيانات' : 'Lihat Data' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'احتساب وإقفال' : 'Hitung & Kunci' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'قسيمة راتبه' : 'E-Slip ESS' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'النقل والترقية الوظيفية' : 'Mutasi & Promosi Karir' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'إدارة والقرار' : 'Kelola & SK' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'ترشيح فريقه' : 'Usulkan Tim' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center" style="color: #64748b;">{{ $ar ? 'قراره فقط' : 'SK Sendiri' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'الاستقالة وتصفية العهد' : 'Resign & Offboarding' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'إخلاء وشهادة' : 'Clearance & SK' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'اعتماد الطلب' : 'Approve Resign' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'مراجعة السلف' : 'Cek Kasbon' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'طلب استقالة' : 'Ajukan Resign' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'رأس مال المستثمر وتوزيع الأرباح' : 'Modal Investor & Dividen' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'صلاحية كاملة' : 'Full Otoritas' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'توثيق الأرباح' : 'Catat Dividen' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'بوابة المستثمر' : 'Portal Mandiri' }}</td>
            </tr>
            <tr>
                <td class="font-bold">{{ $ar ? 'إعدادات الصلاحيات (RBAC)' : 'Konfigurasi Hak Akses (RBAC)' }}</td>
                <td class="text-center font-bold" style="color: #16a34a;">{{ $ar ? 'تحكم كامل' : 'Full Konfigurasi' }}</td>
                <td class="text-center" style="color: #2563eb;">{{ $ar ? 'إدارة المستخدمين' : 'Kelola User' }}</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
                <td class="text-center" style="color: #cbd5e1;">-</td>
            </tr>
        </tbody>
    </table>

    <!-- TANYA JAWAB (FAQ) -->
    <div class="chapter-title">{{ $ar ? '12. الأسئلة الشائعة وحلول المشكلات السريعة (FAQ)' : '12. Tanya Jawab & Pemecahan Masalah Cepat (FAQ)' }}</div>
    <div class="step-card">
        @if($ar)
            <strong>س: لماذا يظهر موقعي خارج نطاق مكتب الفرع عبر نظام GPS رغم تواجدي الفعلي داخل المقر؟</strong><br>
            <span style="color: #475569;">الإجابة:</span> تأكد من تفعيل وضع <strong>الدقة العالية (High Accuracy)</strong> في إعدادات الموقع بهاتفك والسماح للمتصفح بالوصول للموقع. وإذا كنت في مهمة خارجية لدى عميل أو موقع عمل، استخدم خاصية <strong>تسجيل الزيارات الخارجية (Absen Visit)</strong>.
        @else
            <strong>Q: Mengapa GPS browser mendeteksi di luar radius kantor padahal sudah di lokasi?</strong><br>
            <span style="color: #475569;">Jawaban:</span> Pastikan GPS ponsel dalam mode <em>High Accuracy</em> dan berikan izin akses lokasi peramban. Jika bertugas di lapangan/klien luar kantor, gunakan fitur <strong>Absen Visit</strong>.
        @endif
    </div>
    <div class="step-card">
        @if($ar)
            <strong>س: لماذا لا تظهر قسيمة الراتب الشهري في البوابة الذاتية للموظف حتى الآن؟</strong><br>
            <span style="color: #475569;">الإجابة:</span> تُتاح قسيمة الراتب آلياً فقط بعد قيام الإدارة المالية أو الموارد البشرية بالنقر على زر <strong>المصادقة وإقفال دورة الرواتب (Lock Period)</strong>. وطالما كانت الدورة في حالة <strong>مسودة (Draft)</strong> فإن البيانات تظل سرية للتدقيق الداخلي.
        @else
            <strong>Q: Mengapa slip gaji bulanan belum tampil di portal ESS karyawan?</strong><br>
            <span style="color: #475569;">Jawaban:</span> Slip gaji baru otomatis didistribusikan ke karyawan setelah tim Finance melakukan klik pada tombol <strong>Sahkan & Kunci Payroll (Lock Period)</strong>. Selama status masih <em>Draft</em>, slip dilindungi untuk evaluasi internal.
        @endif
    </div>
    <div class="step-card">
        @if($ar)
            <strong>س: ما الإجراء في حال تقدم موظف باستقالته وما زالت بذمته مبالغ سلف متبقية؟</strong><br>
            <span style="color: #475569;">الإجابة:</span> ينبه النظام في مرحلة <strong>إخلاء الطرف وتصفية العهد المالية (Financial Clearance)</strong> بوجود سلف قائمة. ويتوجب سداد المتبقي نقداً أو استقطاعه مباشرة من تصفية مستحقات مكافأة نهاية الخدمة والراتب الأخير قبل إصدار شهادة الخبرة.
        @else
            <strong>Q: Bagaimana penanganan karyawan resign yang masih memiliki sisa kasbon aktif?</strong><br>
            <span style="color: #475569;">Jawaban:</span> Sistem memberi peringatan pada tahap <em>Financial Clearance</em>. Sisa kasbon dapat dilunasi tunai atau dipotong langsung dari kompensasi gaji terakhir sebelum surat paklaring diterbitkan.
        @endif
    </div>
    <div class="step-card">
        @if($ar)
            <strong>س: كيف يميز نظام الموارد البشرية بين احتساب العمل الإضافي لفروع إندونيسيا وفروع المملكة العربية السعودية؟</strong><br>
            <span style="color: #475569;">الإجابة:</span> يتعرف النظام تلقائياً على مقر فرع الموظف: حيث يُحسب لموظفي إندونيسيا وفق لائحة العمل الإندونيسية (PP 35/2021) بالروبية (IDR)، بينما يُحسب لموظفي فروع المملكة بموجب <strong>المادة 107 من نظام العمل السعودي</strong> (أجر الساعة الفعلي + 50% من الأجر الأساسي) بالريال السعودي (SAR)، مع تخفيض ساعات العمل لـ 6 ساعات يومياً في رمضان المبارك (المادة 98).
        @else
            <strong>Q: Bagaimana sistem menghitung lembur cabang Indonesia vs Arab Saudi?</strong><br>
            <span style="color: #475569;">Jawaban:</span> Fitur <em>Auto-Country Resolution</em> otomatis menerapkan rumus Depnaker RI (PP 35/2021) dalam IDR untuk cabang Indonesia, dan Pasal 107 UU Arab Saudi (Upah Riil + Premi 50% Upah Pokok) dalam SAR untuk cabang Arab Saudi (termasuk jam kerja 6 jam/hari saat Ramadan).
        @endif
    </div>

    <!-- FOOTER -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                &copy; {{ date('Y') }} {{ $companyName }} &bull; {{ $ar ? 'منظومة HRIS Enterprise المتكاملة' : 'HRIS Enterprise Ecosystem' }}
            </td>
            <td style="width: 50%; text-align: {{ $ar ? 'left' : 'right' }};">
                {{ $ar ? 'نظام آمن ومشفر • متوافق مع الأنظمة العمالية في المملكة العربية السعودية وإندونيسيا' : 'Sistem Keamanan Terenkripsi • Standar Kepatuhan Hukum ID & KSA' }}
            </td>
        </tr>
    </table>

</body>
</html>

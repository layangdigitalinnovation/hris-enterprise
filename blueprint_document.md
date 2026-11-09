Saya akan susun dokumentasi **Human Resource Information System (HRIS) berbasis Laravel** dalam format yang bisa digunakan sebagai dokumen awal proyek, acuan development tim programmer, maupun bahan presentasi kepada klien.

Dokumentasi ini mencakup integrasi mesin absensi Solution, pengelolaan karyawan, shift, lembur, cuti, izin, persetujuan atasan, payroll, laporan HR, keamanan, arsitektur database, API, hingga tahapan implementasi.

# Dokumentasi Sistem HRIS Berbasis Laravel

  
  Human Resource Information System (HRIS)
  System Design & Technical Development Blueprint · Laravel Ecosystem

## 1. Ringkasan sistem

HRIS merupakan aplikasi berbasis web untuk mengintegrasikan seluruh proses administrasi sumber daya manusia dalam satu sistem terpusat. Sistem ini mengelola data karyawan, absensi, jadwal kerja, cuti, izin, lembur, penggajian, dokumen karyawan, dan laporan manajemen.

Sistem dirancang menggunakan Laravel sebagai backend utama, dengan integrasi mesin absensi Solution untuk mengambil data kehadiran secara otomatis atau terjadwal, bergantung pada protokol dan software yang didukung perangkat.

**Tujuan utama sistem:**

- Memusatkan data karyawan dalam satu database.
- Mengurangi input absensi dan rekapitulasi manual.
- Mengotomatisasi proses pengajuan serta persetujuan cuti dan izin.
- Menghubungkan data kehadiran dengan perhitungan payroll.
- Menyediakan dashboard HR dan laporan manajemen secara real-time atau mendekati real-time.
- Mencatat seluruh aktivitas penting melalui audit log.

## 2. Rekomendasi teknologi

  
    
    
      **Backend — Laravel**

      Laravel sebagai fondasi aplikasi, business logic, validasi, autentikasi, otorisasi, dan API.
    
  
  
---

  
    
    
      **Frontend — Vue.js + Inertia.js**

      Antarmuka dashboard interaktif dengan alur pengembangan yang terintegrasi dengan Laravel.
    
  
  
---

  
    
    
      **Database — MySQL 8**

      Penyimpanan data karyawan, absensi, transaksi cuti, penggajian, dan histori persetujuan.
    
  
  
---

  
    
    
      **Background jobs — Redis + Laravel Queue**

      Pemrosesan sinkronisasi absensi, notifikasi, rekapitulasi, dan pekerjaan terjadwal.
    
  

| Komponen | Teknologi yang disarankan |
|---|---|
| Backend | Laravel versi stabil yang kompatibel dengan lingkungan deployment |
| Bahasa | PHP 8.3+ sesuai versi Laravel yang dipilih |
| Frontend | Vue.js 3, Inertia.js, TypeScript opsional |
| UI | Tailwind CSS |
| Database | MySQL 8.x atau versi kompatibel |
| Cache & queue | Redis |
| Scheduler | Laravel Scheduler + cron |
| API | REST API, Laravel Sanctum |
| Permission | Role-Based Access Control (RBAC) |
| File storage | Local private storage atau S3-compatible storage |
| Testing | Pest atau PHPUnit |
| Deployment | Linux, Nginx/Apache, PHP-FPM, SSL, backup otomatis |

Versi final seluruh dependensi perlu dikunci berdasarkan kompatibilitas server, kebutuhan hosting, dan dukungan perangkat absensi.

## 3. Ruang lingkup modul HRIS

Sistem dibagi menjadi modul-modul yang saling terhubung. Modul dapat dikembangkan bertahap tanpa harus membangun seluruh fitur sekaligus.

  
    [users]
    
      **3.1. Employee Management**

      Master data dan administrasi karyawan.
      - Profil, NIK, nomor karyawan, kontak, dan alamat.
      - Departemen, divisi, jabatan, cabang, serta atasan langsung.
      - Status kontrak, masa kerja, tanggal masuk dan keluar.
      - Riwayat mutasi, promosi, perubahan jabatan, dan status karyawan.
      - Dokumen kontrak, identitas, dan dokumen pendukung.
    
  
  
---

  
    [fingerprint]
    
      **3.2. Attendance & Machine Integration**

      Integrasi mesin absensi Solution dan pengolahan kehadiran.
      - Registrasi mesin dan konfigurasi koneksi.
      - Pengambilan log clock-in dan clock-out.
      - Pemetaan nomor pengguna mesin ke nomor karyawan HRIS.
      - Deteksi data duplikat, log terlambat, dan log yang tidak lengkap.
      - Rekap keterlambatan, pulang awal, ketidakhadiran, dan jam kerja.
      - Monitoring sinkronisasi serta histori error.
    
  
  
---

  
    [calendar-clock]
    
      **3.3. Shift & Work Schedule**

      - Jadwal kerja reguler, shift malam, dan shift bergilir.
      - Kalender kerja per karyawan, departemen, atau lokasi.
      - Hari libur nasional dan hari libur perusahaan.
      - Toleransi keterlambatan dan aturan jam istirahat.
      - Penugasan shift dan perubahan jadwal dengan approval.
    
  
  
---

  
    [calendar-check]
    
      **3.4. Leave & Permission Management**

      - Jenis cuti: tahunan, sakit, melahirkan, alasan penting, dan jenis khusus perusahaan.
      - Saldo cuti, hak cuti, cuti terpakai, dan carry-over sesuai kebijakan.
      - Pengajuan cuti dan izin melalui portal karyawan.
      - Upload dokumen pendukung apabila diwajibkan.
      - Persetujuan atasan dan HR.
      - Pembatalan, penolakan, revisi, serta histori transaksi.
    
  
  
---

  
    [clock]
    
      **3.5. Overtime Management**

      - Pengajuan lembur sebelum atau sesudah pelaksanaan sesuai kebijakan.
      - Penentuan tanggal, durasi, alasan, dan pekerjaan lembur.
      - Approval atasan dan verifikasi HR.
      - Rekonsiliasi dengan data absensi.
      - Perhitungan nilai lembur sesuai aturan perusahaan dan ketentuan yang berlaku.
    
  
  
---

  
    [wallet]
    
      **3.6. Payroll & Compensation**

      - Gaji pokok, tunjangan, potongan, dan komponen pendapatan.
      - Periode penggajian dan payroll run.
      - Rekap kehadiran, lembur, dan potongan yang telah diverifikasi.
      - Slip gaji digital dan histori penggajian.
      - Export laporan payroll ke Excel/CSV atau format yang disepakati.
      - Penguncian periode dan approval payroll.

      Payroll perlu mengikuti kebijakan penggajian perusahaan dan regulasi yang berlaku. Integrasi pajak, BPJS, dan transfer bank dapat dijadikan submodul atau fase lanjutan.
    
  
  
---

  
    [chart-no-axes-combined]
    
      **3.7. HR Dashboard & Reporting**

      - Jumlah karyawan berdasarkan status, divisi, dan lokasi.
      - Ringkasan kehadiran harian.
      - Daftar karyawan terlambat dan tidak hadir.
      - Pengajuan cuti yang menunggu persetujuan.
      - Rekap lembur, saldo cuti, dan biaya payroll.
      - Export laporan dan filter berdasarkan periode.
    
  
  
---

  
    [shield-check]
    
      **3.8. System Administration**

      - User, role, permission, dan hak akses berdasarkan cabang.
      - Konfigurasi aturan absensi dan cuti.
      - Konfigurasi mesin dan status sinkronisasi.
      - Audit log dan histori perubahan data.
      - Pengaturan notifikasi, backup, dan retensi data.
    
  

## 4. Integrasi mesin absensi Solution

Bagian ini menjadi salah satu komponen terpenting dalam arsitektur HRIS. Integrasi harus dirancang berdasarkan model mesin yang benar-benar digunakan, bukan mengasumsikan semua perangkat memiliki protokol yang sama.

### 4.1. Arsitektur integrasi

  
    [fingerprint]
    **Mesin Absensi Solution**
    Fingerprint / Face Recognition / Card
  
  [arrow-down]
  
    **Integration Connector**
    SDK / API / Database / Software Vendor / LAN Connector
  
  [arrow-down]
  
    **Laravel Attendance Ingestion Service**
    Validasi · Normalisasi · Mapping karyawan · Deduplication
  
  [arrow-down]
  
    **Raw Attendance Logs**
    Log mentah tersimpan untuk audit dan pemrosesan ulang
  
  [arrow-down]
  
    **Attendance Processing Engine**
    Shift · Clock-in/out · Keterlambatan · Rekap harian
  

### 4.2. Metode integrasi yang didukung secara potensial

| Metode | Cara kerja | Catatan |
|---|---|---|
| API/SDK resmi | Laravel atau connector mengambil data melalui antarmuka vendor | Pilihan utama jika didukung |
| Database vendor | Connector membaca database yang disediakan software resmi | Perlu izin dan dokumentasi skema |
| Export file | CSV/TXT diekspor dan diimpor terjadwal | Alternatif jika tidak tersedia API |
| Local network connector | Service lokal membaca mesin di LAN dan mengirim data ke server | Cocok jika mesin hanya dapat dijangkau melalui jaringan lokal |
| Webhook/push | Mesin mengirim event ke endpoint aplikasi | Hanya jika perangkat mendukung |

**Catatan penting:** dukungan aktual untuk setiap metode belum bisa dipastikan tanpa mengetahui model mesin Solution dan software manajemennya. Jangan menghubungkan langsung ke database internal atau melakukan koneksi protokol yang tidak didokumentasikan sebelum kompatibilitas dan izinnya dikonfirmasi.

### 4.3. Alur sinkronisasi

1. Sistem menjalankan scheduler atau menerima event dari connector.
2. Connector mengambil log absensi baru berdasarkan cursor, waktu terakhir, atau ID log.
3. Setiap log disimpan sebagai data mentah dengan referensi mesin dan ID event.
4. Sistem memvalidasi timestamp, identitas perangkat, dan pemetaan pengguna mesin ke karyawan.
5. Sistem menolak atau menandai log duplikat menggunakan kunci idempotensi.
6. Log yang valid dimasukkan ke antrean pemrosesan absensi.
7. Attendance Engine menghitung kehadiran berdasarkan jadwal dan aturan shift.
8. Dashboard memperbarui status sinkronisasi, hasil pemrosesan, dan error.
9. Data yang tidak dapat dipetakan masuk ke daftar pengecualian untuk diperiksa HR.

### 4.4. Strategi sinkronisasi dan penanganan error

- **Incremental sync:** hanya mengambil data baru atau data yang perlu diperbarui.
- **Idempotency:** pengambilan log yang sama berkali-kali tidak boleh membuat absensi ganda.
- **Retry:** kegagalan sementara dicoba ulang dengan jeda bertahap.
- **Reconciliation:** sediakan proses mengambil ulang data untuk rentang waktu tertentu.
- **Monitoring:** simpan waktu sinkronisasi terakhir, jumlah log diterima, log gagal, dan durasi proses.
- **Offline mode:** connector lokal menyimpan antrean ketika internet terputus dan mengirimkannya saat koneksi pulih, jika kemampuan ini diimplementasikan.
- **Security:** batasi akses jaringan mesin, lindungi kredensial, dan gunakan koneksi terenkripsi untuk pengiriman data ke server.

### 4.5. Struktur data log absensi

Contoh struktur konseptual `attendance_logs`:

| Field | Fungsi |
|---|---|
| `id` | ID internal |
| `device_id` | Mesin sumber |
| `device_user_id` | ID pengguna di mesin |
| `employee_id` | Karyawan hasil pemetaan, dapat kosong jika belum cocok |
| `event_time` | Timestamp dari mesin |
| `event_type` | Check-in, check-out, atau jenis event yang didukung |
| `source_event_id` | ID event asli jika tersedia |
| `raw_payload` | Data sumber yang diperlukan untuk audit |
| `sync_status` | Pending, processed, failed, atau unmapped |
| `created_at` | Waktu log diterima sistem |

Gunakan unique constraint berdasarkan identitas event yang stabil dari perangkat. Jika perangkat tidak menyediakan event ID, strategi deduplikasi harus disesuaikan dengan karakteristik log-nya.

## 5. Attendance Engine: aturan pengolahan kehadiran

Data dari mesin absensi tidak boleh langsung dianggap sebagai hasil absensi final. Sistem perlu mengolahnya menggunakan jadwal kerja, shift, kalender libur, cuti, dan aturan perusahaan.

### 5.1. Contoh perhitungan

Misalnya seorang karyawan memiliki jadwal kerja pukul 08.00–17.00, dengan toleransi keterlambatan 10 menit.

| Kondisi | Contoh hasil |
|---|---|
| Check-in 07.55 | Tepat waktu |
| Check-in 08.07 | Tepat waktu, dalam toleransi |
| Check-in 08.18 | Terlambat 18 menit dari jadwal; 8 menit melewati toleransi |
| Check-in ada, check-out tidak ada | Perlu verifikasi |
| Tidak ada log, tetapi cuti disetujui | Dicatat sebagai cuti, bukan otomatis mangkir |
| Dua log check-in | Diproses berdasarkan aturan event dan pengecekan duplikasi |

Toleransi 10 menit di atas hanya contoh. Kebijakan perusahaan harus menentukan apakah keterlambatan dihitung sejak jam mulai kerja atau setelah toleransi.

### 5.2. Fitur yang harus tersedia

- Penentuan log masuk dan pulang berdasarkan shift.
- Shift lintas tengah malam, misalnya pukul 22.00–06.00.
- Jadwal fleksibel atau flexible working hours jika diperlukan.
- Jam istirahat dan perhitungan jam kerja efektif.
- Kalender libur dan hari kerja khusus.
- Penanganan lupa absen dan koreksi manual.
- Rekonsiliasi dengan cuti, izin, tugas luar, dan lembur.
- Penguncian periode absensi setelah diverifikasi.
- Reprocessing ketika aturan shift atau data sumber yang sah diperbaiki.

**Prinsip desain:** log mesin adalah bukti kejadian mentah, sedangkan rekap kehadiran merupakan hasil perhitungan yang dapat diperbarui melalui proses koreksi resmi dan tetap memiliki histori.

## 6. Modul cuti, izin, dan approval workflow

Modul ini perlu memiliki alur persetujuan yang dapat dikonfigurasi sesuai struktur organisasi perusahaan.

  
    **1. Karyawan mengajukan cuti/izin**
    Tanggal, jenis, alasan, lampiran
  
  [arrow-down]
  
    **2. Validasi sistem**
    Saldo cuti, benturan jadwal, hari libur, dan kelengkapan data
  
  [arrow-down]
  
    **3. Persetujuan atasan**
    Approve / reject / request revision
  
  [arrow-down]
  
    **4. Verifikasi HR**
    Opsional sesuai kebijakan perusahaan
  
  [arrow-down]
  
    **5. Cuti disahkan**
    Saldo diperbarui, kalender diperbarui, attendance engine menerima status cuti
  

### 6.1. Aturan bisnis cuti

1. Jenis cuti memiliki aturan hak, masa berlaku, dan dokumen pendukung masing-masing.
2. Pengajuan harus memvalidasi tanggal mulai dan selesai, serta mendeteksi pengajuan yang tumpang tindih.
3. Saldo cuti tidak boleh terpotong dua kali akibat proses persetujuan berulang.
4. Saldo dapat menggunakan status *reserved* saat pengajuan menunggu approval jika kebijakan perusahaan menghendakinya.
5. Pengajuan yang ditolak atau dibatalkan harus membatalkan reservasi saldo dengan aman.
6. Perubahan transaksi yang sudah disetujui harus memiliki jejak audit.
7. Cuti setengah hari, cuti lintas tahun, dan carry-over perlu aturan eksplisit.
8. Hari kerja yang dihitung sebagai cuti harus mengikuti kalender kerja karyawan, bukan sekadar jumlah hari kalender.

### 6.2. Status pengajuan

| Status | Makna |
|---|---|
| `draft` | Belum diajukan |
| `pending_approval` | Menunggu persetujuan |
| `pending_hr` | Menunggu verifikasi HR |
| `approved` | Disetujui |
| `rejected` | Ditolak |
| `revision_requested` | Memerlukan perbaikan |
| `cancelled` | Dibatalkan sesuai kewenangan dan aturan |

Status tersebut adalah rancangan awal; implementasi dapat memakai workflow yang lebih sederhana atau bertingkat.

## 7. Modul lembur dan payroll

Keduanya perlu dihubungkan, tetapi tidak boleh saling mengubah data tanpa proses validasi.

### 7.1. Lembur

Alur lembur yang direkomendasikan:

- Karyawan atau atasan mengajukan lembur.
- Atasan menyetujui kebutuhan lembur.
- Data kehadiran digunakan untuk memverifikasi waktu aktual.
- HR memvalidasi durasi yang memenuhi syarat.
- Sistem menghitung nilai lembur menggunakan aturan yang telah dikonfigurasi.
- Data lembur yang telah disahkan diteruskan ke periode payroll.

Durasi aktual di mesin tidak otomatis berarti seluruh durasi tersebut memenuhi syarat pembayaran lembur.

### 7.2. Payroll

Satu siklus payroll harus memiliki status yang jelas, misalnya `draft`, `calculated`, `reviewed`, `approved`, dan `locked`.

Komponen perhitungan dapat mencakup:

- Gaji pokok dan tunjangan tetap.
- Tunjangan tidak tetap dan lembur yang disetujui.
- Potongan berdasarkan aturan yang sah.
- Rekap kehadiran dan ketidakhadiran yang berpengaruh pada penggajian.
- Pajak dan BPJS jika modul tersebut termasuk dalam ruang lingkup.
- Slip gaji dan laporan biaya karyawan.

Sistem perlu menyimpan *payroll snapshot* agar data gaji dari periode yang sudah disahkan tidak berubah hanya karena profil karyawan atau aturan absensi diubah pada periode berikutnya.

## 8. Role dan permission pengguna

HRIS sebaiknya menggunakan RBAC dengan cakupan data berdasarkan cabang, departemen, dan hubungan atasan-bawahan.

| Role | Hak akses utama |
|---|---|
| Super Admin | Konfigurasi sistem, user, role, dan integrasi |
| HR Admin | Master karyawan, aturan HR, absensi, cuti, dan laporan |
| HR Payroll | Perhitungan dan pengesahan payroll sesuai kewenangan |
| Manager / Supervisor | Melihat tim dan menyetujui pengajuan |
| Employee | Profil sendiri, absensi, saldo cuti, pengajuan, slip gaji sendiri |
| Auditor / Management | Akses baca laporan yang diizinkan |

Hak akses harus diperiksa pada sisi server untuk setiap endpoint dan aksi. Menyembunyikan menu di frontend saja tidak cukup.

Contohnya, seorang karyawan tidak boleh dapat mengakses slip gaji karyawan lain hanya dengan mengganti ID pada URL.

## 9. Arsitektur aplikasi Laravel

Gunakan pendekatan modular agar integrasi mesin absensi, cuti, dan payroll tidak menumpuk dalam satu controller besar.

flowchart TB
    U[Employee / HR / Manager] --> FE[Vue 3 + Inertia.js]
    FE --> MW[Laravel Middleware and Policies]
    MW --> APP[Laravel Application]
    APP --> EMP[Employee Module]
    APP --> ATT[Attendance Module]
    APP --> LEAVE[Leave and Approval Module]
    APP --> PAY[Payroll Module]
    APP --> REP[Reports and Audit]
    ATT --> ING[Device Integration Service]
    ING --> DEV[Solution Attendance Device]
    APP --> DB[(MySQL)]
    ING --> Q[Laravel Queue]
    Q --> DB
    Q --> NOTIF[Notifications]
    APP --> REDIS[(Redis Cache and Queue)]
    SCH[Laravel Scheduler] --> ING
    SCH --> Q

### 9.1. Struktur folder yang direkomendasikan

```text
app/
├── Modules/
│   ├── Employees/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Actions/
│   ├── Attendance/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Actions/
│   │   └── Jobs/
│   ├── Leave/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Actions/
│   ├── Overtime/
│   ├── Payroll/
│   ├── Approvals/
│   └── Reports/
├── Integrations/
│   └── AttendanceDevices/
│       ├── Contracts/
│       ├── Connectors/
│       ├── DTOs/
│       └── Exceptions/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Policies/
├── Jobs/
├── Notifications/
└── Console/

database/
├── migrations/
├── factories/
└── seeders/

resources/
├── js/
│   ├── Pages/
│   ├── Components/
│   └── Layouts/
└── views/
```

Struktur ini merupakan usulan arsitektur, bukan struktur folder wajib Laravel. Jika tim lebih nyaman dengan struktur Laravel standar, modul dapat diorganisasi menggunakan folder `Services`, `Models`, dan `Actions` yang sudah ada.

### 9.2. Pola integrasi perangkat

Buat kontrak connector agar implementasi perangkat dapat diganti tanpa mengubah Attendance Engine.

Contoh antarmuka PHP:

```php
<?php

namespace App\Integrations\AttendanceDevices\Contracts;

interface AttendanceDeviceConnector
{
    public function testConnection(): array;

    public function fetchLogs(
        ?string $cursor = null
    ): iterable;
}
```

Connector Solution kemudian mengimplementasikan interface tersebut sesuai API, SDK, atau protokol resmi yang benar-benar tersedia.

Contoh alur pemanggilan:

```text
AttendanceSyncCommand
    -> AttendanceDeviceConnector
    -> Fetch new device logs
    -> Store raw logs
    -> Dispatch ProcessAttendanceLog job
    -> AttendanceProcessingService
    -> Update daily attendance summary
```

Keuntungannya, apabila di masa depan perusahaan menggunakan mesin merek lain, connector baru dapat ditambahkan tanpa menulis ulang modul cuti, payroll, atau laporan.

## 10. Rancangan database

Database menggunakan relasi yang menjaga konsistensi data, mencegah transaksi ganda, dan memungkinkan histori perubahan.

### 10.1. Tabel master

| Tabel | Kegunaan |
|---|---|
| `users` | Akun login |
| `employees` | Data induk karyawan |
| `departments` | Departemen dan struktur organisasi |
| `positions` | Jabatan |
| `branches` | Lokasi atau cabang |
| `employee_employments` | Histori status kerja dan kontrak |
| `employee_documents` | Dokumen karyawan |
| `devices` | Daftar mesin absensi |
| `device_employee_mappings` | Pemetaan ID pengguna mesin ke karyawan |

### 10.2. Tabel absensi

| Tabel | Kegunaan |
|---|---|
| `attendance_logs` | Log mentah dari perangkat |
| `attendance_sync_runs` | Histori proses sinkronisasi |
| `work_schedules` | Definisi jadwal kerja |
| `shifts` | Konfigurasi jam kerja |
| `employee_shift_assignments` | Penugasan shift |
| `holidays` | Kalender libur |
| `attendance_daily_summaries` | Rekap kehadiran per karyawan per tanggal |
| `attendance_adjustments` | Permohonan koreksi dan hasil persetujuan |

### 10.3. Tabel cuti, approval, dan lembur

| Tabel | Kegunaan |
|---|---|
| `leave_types` | Jenis cuti dan aturan |
| `leave_policies` | Aturan hak cuti |
| `leave_balances` | Saldo per karyawan dan periode |
| `leave_transactions` | Mutasi saldo cuti |
| `leave_requests` | Pengajuan cuti |
| `leave_request_days` | Rincian tanggal cuti |
| `approval_steps` | Tahapan persetujuan |
| `approval_actions` | Histori keputusan |
| `overtime_requests` | Pengajuan lembur |
| `overtime_records` | Lembur yang telah diverifikasi |

### 10.4. Tabel payroll dan administrasi

| Tabel | Kegunaan |
|---|---|
| `salary_components` | Master komponen gaji |
| `employee_compensations` | Komponen kompensasi karyawan |
| `payroll_periods` | Periode penggajian |
| `payroll_runs` | Proses perhitungan payroll |
| `payroll_items` | Hasil payroll per karyawan |
| `payslips` | Data slip gaji |
| `notifications` | Notifikasi aplikasi |
| `audit_logs` | Histori tindakan penting |
| `system_settings` | Pengaturan aplikasi |

### 10.5. Aturan desain database

- Gunakan foreign key dan indeks untuk relasi utama.
- Gunakan unique constraint untuk nomor karyawan, identitas event, dan transaksi tertentu.
- Simpan waktu event asli perangkat secara konsisten; konversikan ke zona waktu perusahaan saat menghitung jadwal.
- Gunakan transaksi database untuk perubahan saldo cuti dan pengesahan payroll.
- Simpan histori transaksi saldo, bukan hanya nilai saldo saat ini.
- Hindari menghapus permanen log absensi yang telah digunakan sebagai dasar laporan atau penggajian.
- Gunakan `DECIMAL` untuk nilai uang, bukan floating-point.
- Simpan data sensitif dengan pembatasan akses dan retensi yang terdefinisi.

Untuk instalasi multi-cabang, desain sejak awal apakah setiap karyawan memiliki satu perusahaan dan satu cabang utama, atau perlu mendukung banyak badan usaha dalam satu instalasi. Keputusan tersebut memengaruhi hampir seluruh tabel transaksi.

## 11. Rancangan REST API

API digunakan oleh frontend, aplikasi mobile di masa depan, dan connector eksternal bila diperlukan.

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/v1/employees` | Daftar karyawan |
| POST | `/api/v1/employees` | Tambah karyawan |
| GET | `/api/v1/employees/{id}` | Detail karyawan |
| GET | `/api/v1/attendance/daily` | Rekap absensi |
| GET | `/api/v1/attendance/logs` | Log absensi mentah sesuai permission |
| POST | `/api/v1/attendance/adjustments` | Ajukan koreksi absensi |
| GET | `/api/v1/leave/balances` | Saldo cuti |
| POST | `/api/v1/leave/requests` | Ajukan cuti |
| POST | `/api/v1/leave/requests/{id}/approve` | Setujui pengajuan |
| POST | `/api/v1/leave/requests/{id}/reject` | Tolak pengajuan |
| POST | `/api/v1/overtime/requests` | Ajukan lembur |
| GET | `/api/v1/payroll/periods` | Daftar periode payroll |
| POST | `/api/v1/payroll/runs` | Jalankan perhitungan payroll |
| GET | `/api/v1/reports/attendance` | Laporan absensi |
| GET | `/api/v1/devices` | Daftar perangkat |
| POST | `/api/v1/devices/{id}/sync` | Meminta sinkronisasi |

Endpoint di atas adalah rancangan kontrak API, bukan endpoint yang sudah dibuat atau dijamin tersedia pada Laravel.

**Ketentuan API:**

1. Gunakan Form Request untuk validasi input.
2. Gunakan Policies/Gates untuk otorisasi.
3. Gunakan pagination dan filter tanggal untuk laporan besar.
4. Terapkan idempotency pada operasi yang berpotensi dikirim berulang.
5. Gunakan queue untuk pekerjaan berat.
6. Catat hasil dan error integrasi tanpa menyimpan password atau token rahasia ke log.
7. Batasi endpoint sinkronisasi perangkat untuk pengguna yang berwenang.
8. Gunakan versioning API agar perubahan berikutnya tidak langsung merusak integrasi klien.

## 12. Keamanan dan perlindungan data karyawan

Karena HRIS menyimpan data identitas, riwayat kerja, absensi, dan informasi penggajian, keamanan harus menjadi bagian dari desain awal.

| Area | Implementasi |
|---|---|
| Authentication | Laravel session authentication untuk web; Sanctum bila diperlukan untuk API |
| Authorization | RBAC, Policies, dan pembatasan akses berdasarkan organisasi |
| Password | Hashing menggunakan mekanisme Laravel |
| Transport | HTTPS/TLS untuk akses aplikasi dan komunikasi connector |
| Database | Akun database dengan hak akses minimum dan akses jaringan terbatas |
| Sensitive files | Penyimpanan private, bukan direktori publik |
| Audit trail | Catat aktor, waktu, tindakan, dan perubahan penting |
| Backup | Backup otomatis, retensi, dan uji pemulihan berkala |
| Session security | Cookie aman, proteksi CSRF, dan pengaturan sesi |
| Rate limiting | Pembatasan permintaan login dan API sensitif |
| Payroll protection | Akses terbatas, histori persetujuan, dan penguncian periode |
| Privacy | Pembatasan akses, retensi, dan prosedur penghapusan sesuai ketentuan |

Khusus untuk mesin absensi, jangan membuka port mesin langsung ke internet. Jika perangkat berada di jaringan lokal sedangkan HRIS berada di cloud, lebih baik gunakan connector lokal yang melakukan koneksi keluar secara aman ke server.

Sistem juga perlu dirancang agar pemrosesan data karyawan sesuai dengan kebijakan perusahaan dan peraturan perlindungan data pribadi yang berlaku di Indonesia.

## 13. Dashboard berdasarkan jenis pengguna

  
    
    
      **HR Admin Dashboard**

      - Total karyawan aktif.
      - Kehadiran hari ini.
      - Karyawan terlambat dan belum memiliki log lengkap.
      - Pengajuan cuti dan koreksi absensi yang menunggu approval.
      - Status sinkronisasi mesin.
      - Rekap payroll per periode.
    
  
  
---

  
    
    
      **Employee Self-Service**

      - Profil pribadi.
      - Riwayat dan rekap absensi.
      - Saldo cuti dan kalender pengajuan.
      - Pengajuan cuti, izin, dan lembur.
      - Status approval.
      - Slip gaji pribadi.
    
  
  
---

  
    
    
      **Manager Dashboard**

      - Kehadiran anggota tim.
      - Persetujuan cuti dan lembur.
      - Permintaan koreksi absensi.
      - Kalender tim dan konflik jadwal.
      - Laporan ringkas divisi.
    
  

## 14. Tahapan pengembangan

Saya menyarankan pengembangan dilakukan bertahap agar integrasi mesin dan perhitungan absensi dapat diuji sebelum terhubung ke payroll.

  
    
      01
    
    
      **Analisis kebutuhan & validasi perangkat**

      **[Prioritas awal]**

      Identifikasi model mesin Solution, software vendor, protokol integrasi, aturan kerja, struktur organisasi, dan kebijakan cuti.
    
  
  
---

  
    
      02
    
    
      **Fondasi aplikasi dan master data**

      Setup Laravel, database, login, permission, karyawan, divisi, jabatan, cabang, dan konfigurasi sistem.
    
  
  
---

  
    
      03
    
    
      **Integrasi mesin dan attendance engine**

      Connector, sinkronisasi log, mapping karyawan, shift, kalkulasi kehadiran, koreksi absensi, dan laporan.
    
  
  
---

  
    
      04
    
    
      **Cuti, izin, approval, dan lembur**

      Saldo cuti, workflow persetujuan, notifikasi, kalender kerja, dan rekonsiliasi absensi.
    
  
  
---

  
    
      05
    
    
      **Payroll dan laporan lanjutan**

      Komponen gaji, kalkulasi payroll, slip gaji, export, serta penguncian periode.
    
  
  
---

  
    
      06
    
    
      **UAT, deployment, dan pemeliharaan**

      Uji pengguna, migrasi data, backup dan recovery, monitoring, dokumentasi operasional, serta pelatihan HR.
    
  

## 15. Pengujian dan kriteria penerimaan

Sebelum sistem dinyatakan siap digunakan, beberapa skenario wajib diuji.

| Pengujian | Kriteria penerimaan |
|---|---|
| Sinkronisasi mesin | Log yang tersedia dapat diambil sesuai metode integrasi yang disepakati |
| Duplikasi data | Sinkronisasi berulang tidak membuat log ganda |
| Offline dan recovery | Data tertunda dapat diproses kembali tanpa kehilangan atau duplikasi |
| Shift malam | Jam masuk dan pulang dikaitkan dengan shift yang benar |
| Koreksi absensi | Koreksi harus melalui approval dan memiliki histori |
| Cuti | Saldo berubah sesuai transaksi yang disahkan, tanpa pengurangan ganda |
| Approval | Pengguna hanya dapat memutuskan pengajuan sesuai kewenangannya |
| Payroll | Perhitungan cocok dengan test case yang telah disetujui HR |
| Hak akses | Karyawan tidak dapat melihat data rahasia milik karyawan lain |
| Backup | Database dapat dipulihkan dari backup yang diuji |
| Audit | Perubahan penting memiliki jejak yang dapat ditelusuri |

Gunakan automated testing untuk aturan bisnis kritis, terutama perhitungan shift, cuti, lembur, dan payroll. Selain itu, lakukan UAT bersama tim HR menggunakan data simulasi yang mencakup kasus normal maupun kasus pengecualian.

## 16. Keputusan yang perlu ditetapkan sebelum coding

Ada beberapa hal yang belum bisa dipastikan dari kebutuhan awal. Keputusan berikut akan menentukan desain final, estimasi waktu, dan biaya pengembangan.

  Konfigurasi kebutuhan HRIS
  Pilih kondisi yang paling mendekati proyek Anda. Jika belum mengetahui jawabannya, pilihan default boleh digunakan.
   GenUI.issueNewTurn("Buatkan spesifikasi teknis HRIS Laravel yang lebih final, lengkap dengan ERD, migration database, detail API, workflow, dan estimasi pengembangan berdasarkan konfigurasi berikut: Mesin absensi Solution: "+({"unknown":"Model/protokol belum diketahui","api":"Tersedia API/SDK resmi","software":"Menggunakan software manajemen vendor","file":"Hanya bisa export CSV/TXT","lan":"Mesin berada di LAN lokal"}[device])+"; Deployment: "+({"cloud":"Cloud VPS","local":"Server lokal perusahaan","hybrid":"Hybrid: connector lokal dan server cloud"}[deployment])+"; Scope: "+({"full":"HRIS lengkap termasuk absensi, cuti, lembur, dan payroll","attendance":"Fokus karyawan, mesin absensi, shift, dan cuti"}[scope])+"; Jumlah karyawan: "+employees+"; Payroll: "+({"included":"Termasuk payroll","later":"Payroll fase berikutnya"}[payroll])+"; Organisasi: "+({"single":"Satu perusahaan","multi":"Multi-perusahaan / multi-cabang"}[companies])+". Jelaskan asumsi dan hal yang perlu dikonfirmasi sebelum coding.")}>
    
      1. Mesin absensi Solution
      
        Belum tahu model atau metode integrasinya
        Tersedia API atau SDK resmi
        Menggunakan software manajemen vendor
        Hanya dapat export CSV/TXT
        Mesin berada di jaringan LAN lokal
      
    
    
---

    
      2. Lokasi server
      
        Cloud VPS / hosting
        Server lokal perusahaan
        Hybrid: mesin lokal dan HRIS di cloud
      
    
    
---

    
      3. Ruang lingkup tahap pertama
      
        HRIS lengkap termasuk payroll
        Karyawan, absensi, shift, cuti, dan approval dahulu
      
    
    
---

    
      4. Perkiraan jumlah karyawan
      
        Di bawah 100
        100–500
        500–2.000
        2000">Di atas 2.000
      
    
    
---

    
      5. Payroll
      
        Termasuk pada tahap pertama
        Dikembangkan pada fase berikutnya
      
    
    
---

    
      6. Struktur organisasi
      
        Satu perusahaan
        Multi-perusahaan atau banyak cabang
      
    
    Susun spesifikasi teknis final
  

**Rekomendasi saya:** validasi model dan protokol mesin Solution terlebih dahulu, lalu bangun master data karyawan dan attendance engine. Setelah perhitungan absensi terbukti akurat, lanjutkan cuti, approval, dan payroll. Pendekatan ini mengurangi risiko membangun integrasi yang ternyata tidak kompatibel dengan perangkat yang dimiliki perusahaan.

---

If you want, I can:

- Summarize this conversation
- Continue documentation for overtime and payroll modules
- Explain key HRIS modules and their interactions in simple terms

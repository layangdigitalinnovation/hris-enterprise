# HRIS Pro Enterprise

Sistem Tata Kelola Sumber Daya Manusia (HRIS) Terpadu untuk Perusahaan Multi-Cabang & Multi-Negara (Indonesia & Arab Saudi).

---

## 🌟 Fitur Utama & Modul Sistem

1. **Presensi & Mesin Biometrik**
   - Absensi mandiri mobile dengan validasi koordinat GPS Geofencing (radius toleransi cabang).
   - Absen Visit (kunjungan dinas & tugas luar kantor berkali-kali).
   - Integrasi langsung mesin fingerprint/face Solution via IP/Port dan pengajuan koreksi presensi berjenjang.

2. **Cuti & Izin Sakit**
   - Manajemen jatah saldo kuota cuti tahunan, cuti khusus berbayar, dan izin sakit (unggah surat dokter / MC).
   - Validasi cerdas pencegah tanggal bertabrakan (*anti-overlapping*).
   - Otorisasi berjenjang atasan langsung (Manager/Supervisor).

3. **Lembur / SPKL & Dual Formula Internasional**
   - Penerbitan Surat Perintah Kerja Lembur (SPKL) terintegrasi.
   - **Formula Depnaker RI (PP 35/2021)** untuk cabang Indonesia (Mata uang IDR).
   - **Formula Pasal 107 UU Ketenagakerjaan Arab Saudi** (Upah Riil/Jam + Premi 50% Gaji Pokok) untuk cabang Arab Saudi (Mata uang SAR).
   - Klausul khusus Bulan Suci Ramadan (pengurangan jam kerja normal menjadi 6 jam/hari sesuai Pasal 98 UU KSA).

4. **Klaim Reimbursement (Operasional & Medis)**
   - Multi-currency (IDR & SAR) dengan verifikasi bukti nota/kuitansi asli.
   - Otorisasi berjenjang HR/Manager dan pencairan dana oleh tim Keuangan (Finance).
   - Cetak dokumen resmi Voucher Bukti Pengeluaran Kas format PDF.

5. **Pinjaman Karyawan & Kasbon**
   - Fasilitas pinjaman karyawan dan kasbon (bunga 0%).
   - Simulasi tenor dan angsuran bulanan (3, 6, 12 bulan).
   - Pemotongan otomatis (*auto-deduction*) pada slip gaji bulanan hingga saldo lunas.

6. **Siklus Payroll Terpadu**
   - Kalkulasi otomatis take-home pay, tunjangan, dan upah lembur SPKL.
   - Sinkronisasi kurs harian SAR ke IDR untuk cabang Arab Saudi.
   - Iuran BPJS Ketenagakerjaan & Kesehatan, serta perhitungan PPh 21 TER (Tarif Efektif Rata-rata).
   - Penguncian periode (*Lock Period*) dan rilis e-slip gaji ke portal ESS karyawan.

7. **Mutasi, Promosi & Transisi Karir**
   - Pengajuan promosi jabatan, mutasi divisi, atau transfer cabang antar negara.
   - Perbandingan otomatis snapshot data lama vs usulan baru.
   - Penerbitan dokumen formal Surat Keputusan (SK) format PDF bilingual.

8. **Pengunduran Diri (Resign) & Offboarding**
   - Pengajuan mandiri dengan standar *one-month notice* dan wawancara akhir (*exit interview*).
   - *Asset & Financial Clearance* (pemeriksaan laptop, ID card, dan sisa kasbon).
   - Penerbitan Surat Pengalaman Kerja (Paklaring) resmi format PDF.

9. **Portal Investor & Dividen (BEP)**
   - Pencatatan modal disetor (IDR/SAR), persentase bagi hasil, dan distribusi dividen berkala.
   - Estimasi dan tracking titik impas balik modal (*Break-Even Point* / BEP).
   - Portal mandiri investor dengan proteksi privasi (tanpa akses ke dapur gaji karyawan).

10. **Manajemen Pengguna & RBAC 263+ Hak Akses**
    - Pemisahan akun *User* (login) dan *Employee* (data kepegawaian).
    - Batasan lingkup wilayah (*Branch & Department Scope*).
    - 263+ permission granuler berbasis Spatie Permission lengkap dengan *Audit Trail Log*.

---

## 🌐 Dukungan Multi-Bahasa & Dual Currency

- **Bahasa Indonesia (ID)**: Format kalender Masehi, mata uang Rupiah (IDR), dan regulasi Depnaker RI.
- **Bahasa Arab (🇸🇦 AR - Saudi Arabia)**: Tata letak *Right-to-Left* (RTL), tipografi DejaVu Sans, mata uang Saudi Riyal (SAR), dan regulasi UU Ketenagakerjaan Arab Saudi.

---

## 🛠️ Tech Stack

- **Backend**: Laravel 11 (PHP 8.2+)
- **Database**: MySQL / MariaDB
- **Frontend**: Blade Templates, Tailwind CSS, Alpine.js
- **PDF Engine**: Barryvdh DomPDF & ArPHP (Arabic text shaper & glyphs)
- **Role & Permissions**: Spatie Laravel-Permission

---

## 🚀 Setup & Instalasi

1. **Clone Repository**:
   ```bash
   git clone https://github.com/layangdigitalinnovation/hris-enterprise.git
   cd hris-enterprise
   ```

2. **Install Dependensi PHP & Frontend**:
   ```bash
   composer install
   npm install && npm run build
   ```

3. **Konfigurasi Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   *Sesuaikan konfigurasi database `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` pada file `.env`.*

4. **Migrasi & Seeder Database**:
   ```bash
   php artisan migrate --seed
   ```

5. **Storage Symlink**:
   ```bash
   php artisan storage:link
   ```

6. **Jalankan Aplikasi**:
   ```bash
   php artisan serve
   ```

---

## 📄 Lisensi

Hak Cipta &copy; 2026 PT Layang Digital Innovation. All rights reserved.

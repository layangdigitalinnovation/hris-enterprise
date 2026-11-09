<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Define Roles
        $rolesData = [
            [
                'name' => 'Super Admin',
                'key' => 'super_admin',
                'guard_name' => 'web',
                'description' => 'Akses penuh tanpa batas ke seluruh modul, konfigurasi sistem, dan manajemen hak akses.',
                'is_system' => true,
            ],
            [
                'name' => 'HRD Admin',
                'key' => 'hr_admin',
                'guard_name' => 'web',
                'description' => 'Pengelolaan data induk karyawan, struktur organisasi, presensi, cuti, mutasi, dan resign.',
                'is_system' => true,
            ],
            [
                'name' => 'Manager / Head',
                'key' => 'manager',
                'guard_name' => 'web',
                'description' => 'Persetujuan berjenjang dan pemantauan aktivitas karyawan di bawah departemen/tim terkait.',
                'is_system' => true,
            ],
            [
                'name' => 'Supervisor',
                'key' => 'supervisor',
                'guard_name' => 'web',
                'description' => 'Pengawasan operasional harian, verifikasi kehadiran shift, dan kunjungan lapangan.',
                'is_system' => false,
            ],
            [
                'name' => 'Finance',
                'key' => 'finance',
                'guard_name' => 'web',
                'description' => 'Pencairan reimbursement, validasi pinjaman, dan pemrosesan penggajian/payroll.',
                'is_system' => true,
            ],
            [
                'name' => 'Karyawan',
                'key' => 'employee',
                'guard_name' => 'web',
                'description' => 'Akses mandiri (self-service) absensi mobile/GPS, pengajuan cuti, lembur, klaim, dan slip gaji.',
                'is_system' => true,
            ],
            [
                'name' => 'Investor',
                'key' => 'investor',
                'guard_name' => 'web',
                'description' => 'Portal investor untuk melihat portofolio modal, status BEP, dan riwayat dividen.',
                'is_system' => true,
            ],
        ];

        $roles = [];
        foreach ($rolesData as $rd) {
            $roles[$rd['key']] = Role::updateOrCreate(
                ['key' => $rd['key']],
                $rd
            );
        }

        // 2. Define Groups and Permissions
        $matrix = [
            'Karyawan' => [
                'icon' => 'users',
                'description' => 'Modul data induk kepegawaian dan biodata karyawan',
                'permissions' => [
                    'karyawan.index' => 'Lihat Daftar Karyawan',
                    'karyawan.show' => 'Lihat Detail Profil Karyawan',
                    'karyawan.create' => 'Tambah Data Karyawan Baru',
                    'karyawan.edit' => 'Ubah / Edit Data Karyawan',
                    'karyawan.delete' => 'Hapus Data Karyawan',
                    'karyawan.export' => 'Export Data Karyawan (Excel/PDF)',
                    'karyawan.import' => 'Import Massal Data Karyawan',
                    'karyawan.setcabang' => 'Atur Penempatan Cabang Karyawan',
                    'karyawan.setjamkerja' => 'Atur Jam Kerja Khusus Karyawan',
                ],
            ],
            'Departemen' => [
                'icon' => 'building',
                'description' => 'Struktur departemen dan divisi organisasi',
                'permissions' => [
                    'departemen.index' => 'Lihat Daftar Departemen',
                    'departemen.create' => 'Tambah Departemen Baru',
                    'departemen.edit' => 'Ubah Data Departemen',
                    'departemen.delete' => 'Hapus Departemen',
                ],
            ],
            'Cabang' => [
                'icon' => 'map-pin',
                'description' => 'Kantor cabang, radius koordinat GPS, dan wilayah kerja',
                'permissions' => [
                    'cabang.index' => 'Lihat Daftar Cabang',
                    'cabang.create' => 'Tambah Cabang Baru',
                    'cabang.edit' => 'Ubah Lokasi / Radius Cabang',
                    'cabang.delete' => 'Hapus Cabang',
                ],
            ],
            'Jabatan & Posisi' => [
                'icon' => 'briefcase',
                'description' => 'Level jabatan, hierarki, dan deskripsi tugas',
                'permissions' => [
                    'jabatan.index' => 'Lihat Daftar Jabatan',
                    'jabatan.create' => 'Tambah Jabatan Baru',
                    'jabatan.edit' => 'Ubah Data Jabatan',
                    'jabatan.delete' => 'Hapus Jabatan',
                ],
            ],
            'Shift & Jadwal' => [
                'icon' => 'clock',
                'description' => 'Pola jam kerja, shift dinamis, dan jadwal roster karyawan',
                'permissions' => [
                    'shift.index' => 'Lihat Master Shift',
                    'shift.create' => 'Tambah Master Shift Baru',
                    'shift.edit' => 'Ubah Jam Shift',
                    'shift.delete' => 'Hapus Shift',
                    'jadwal.index' => 'Lihat Jadwal Kerja Karyawan',
                    'jadwal.assign' => 'Plotting / Roster Jadwal Karyawan',
                ],
            ],
            'Presensi & Mesin' => [
                'icon' => 'fingerprint',
                'description' => 'Pencatatan absensi harian, log mesin finger, dan rekapitulasi',
                'permissions' => [
                    'presensi.index' => 'Lihat Rekap Presensi Karyawan',
                    'presensi.self' => 'Catat Absen Masuk & Pulang Sendiri',
                    'presensi.koreksi' => 'Ajukan Koreksi Presensi',
                    'presensi.approve_koreksi' => 'Setujui / Tolak Koreksi Presensi',
                    'presensi.visit' => 'Catat Kunjungan Lapangan (Visit)',
                    'presensi.live_map' => 'Pantau Peta Monitoring GPS Langsung',
                    'mesin.index' => 'Kelola Perangkat Mesin Absensi',
                    'mesin.sync' => 'Sinkronisasi Log Mesin Absensi',
                ],
            ],
            'Cuti & Izin' => [
                'icon' => 'calendar',
                'description' => 'Pengajuan cuti tahunan, izin sakit, dan alur persetujuan',
                'permissions' => [
                    'cuti.index' => 'Lihat Pengajuan Cuti & Izin',
                    'cuti.create' => 'Ajukan Cuti / Izin',
                    'cuti.approve' => 'Setujui Pengajuan Cuti',
                    'cuti.reject' => 'Tolak Pengajuan Cuti',
                    'cuti.delete' => 'Batalkan / Hapus Pengajuan Cuti',
                    'cuti.settings' => 'Konfigurasi Kuota & Jenis Cuti',
                ],
            ],
            'Lembur (SPKL)' => [
                'icon' => 'clock',
                'description' => 'Surat perintah kerja lembur dan perhitungan upah lembur',
                'permissions' => [
                    'lembur.index' => 'Lihat Daftar Pengajuan Lembur',
                    'lembur.create' => 'Ajukan Permohonan Lembur (SPKL)',
                    'lembur.approve' => 'Setujui Pengajuan Lembur',
                    'lembur.reject' => 'Tolak Pengajuan Lembur',
                    'lembur.delete' => 'Hapus Pengajuan Lembur',
                ],
            ],
            'Reimbursement' => [
                'icon' => 'receipt',
                'description' => 'Klaim biaya operasional, medis, bensin, dan transportasi',
                'permissions' => [
                    'reimbursement.index' => 'Lihat Pengajuan Reimbursement',
                    'reimbursement.create' => 'Ajukan Klaim Reimbursement',
                    'reimbursement.approve' => 'Setujui Klaim Reimbursement',
                    'reimbursement.reject' => 'Tolak Klaim Reimbursement',
                    'reimbursement.pay' => 'Konfirmasi Pembayaran Kasir / Finance',
                    'reimbursement.delete' => 'Hapus Pengajuan Klaim',
                ],
            ],
            'Pinjaman Karyawan' => [
                'icon' => 'credit-card',
                'description' => 'Kasbon, pinjaman lunak, dan pemotongan cicilan otomatis',
                'permissions' => [
                    'pinjaman.index' => 'Lihat Daftar Pinjaman Karyawan',
                    'pinjaman.create' => 'Ajukan Pinjaman Baru',
                    'pinjaman.approve' => 'Setujui Permohonan Pinjaman',
                    'pinjaman.disburse' => 'Pencairan Dana Pinjaman',
                    'pinjaman.delete' => 'Hapus Pengajuan Pinjaman',
                ],
            ],
            'Resign & Offboarding' => [
                'icon' => 'user-minus',
                'description' => 'Pengunduran diri, checklist clearance aset, dan surat pengalaman',
                'permissions' => [
                    'resign.index' => 'Lihat Permohonan Resign',
                    'resign.create' => 'Ajukan Permohonan Resign',
                    'resign.approve' => 'Setujui Permohonan Resign',
                    'resign.clearance' => 'Update Exit Clearance Aset & Keuangan',
                    'resign.complete' => 'Selesaikan Offboarding & Nonaktifkan Akun',
                    'resign.certificate' => 'Cetak Surat Pengalaman Kerja (Paklaring)',
                ],
            ],
            'Mutasi & Promosi' => [
                'icon' => 'trending-up',
                'description' => 'Perpindahan cabang, promosi jabatan, rotasi, dan SK mutasi',
                'permissions' => [
                    'mutasi.index' => 'Lihat Riwayat Mutasi & Promosi',
                    'mutasi.create' => 'Ajukan Mutasi / Promosi Baru',
                    'mutasi.approve' => 'Setujui Usulan Mutasi / Promosi',
                    'mutasi.apply' => 'Eksekusi & Terapkan Perubahan Data Karyawan',
                    'mutasi.decree' => 'Cetak Surat Keputusan (SK) Mutasi/Promosi',
                ],
            ],
            'Gaji & Payroll' => [
                'icon' => 'cash',
                'description' => 'Kalkulasi gaji, tunjangan, potongan, dan cetak slip gaji',
                'permissions' => [
                    'payroll.index' => 'Lihat Rekapitulasi Penggajian',
                    'payroll.calculate' => 'Hitung & Proses Payroll Bulanan',
                    'payroll.lock' => 'Kunci / Finalisasi Periode Payroll',
                    'payroll.payslip' => 'Cetak Slip Gaji Karyawan',
                    'payroll.settings' => 'Kelola Komponen Gaji & Template Payroll',
                ],
            ],
            'Investor & Bagi Hasil' => [
                'icon' => 'chart-pie',
                'description' => 'Portofolio permodalan, pembagian dividen, dan return on investment',
                'permissions' => [
                    'investor.index' => 'Lihat Daftar Investor & Modal',
                    'investor.create' => 'Tambah Investor Baru',
                    'investor.edit' => 'Ubah Data & Ketentuan Investasi',
                    'investor.distribution' => 'Catat Distribusi Keuntungan / Dividen',
                    'investor.export' => 'Export Laporan Investasi (PDF/Excel)',
                    'investor.portal' => 'Akses Portal Khusus Investor',
                ],
            ],
            'Laporan' => [
                'icon' => 'document-report',
                'description' => 'Rekapitulasi dan pelaporan analitik HRIS',
                'permissions' => [
                    'laporan.presensi' => 'Laporan Rekap Presensi & Jam Kerja',
                    'laporan.cuti' => 'Laporan Penggunaan Cuti Karyawan',
                    'laporan.lembur' => 'Laporan Akumulasi Lembur (SPKL)',
                    'laporan.reimbursement' => 'Laporan Pengeluaran Reimbursement',
                    'laporan.pinjaman' => 'Laporan Saldo & Cicilan Pinjaman',
                    'laporan.payroll' => 'Laporan Biaya Payroll Perusahaan',
                ],
            ],
            'User & Hak Akses' => [
                'icon' => 'shield-check',
                'description' => 'Manajemen akun login, peran (role), dan matriks izin wewenang',
                'permissions' => [
                    'user.index' => 'Lihat Daftar Pengguna (Users)',
                    'user.create' => 'Tambah Pengguna Baru',
                    'user.edit' => 'Ubah Data Pengguna & Password',
                    'user.delete' => 'Hapus Akun Pengguna',
                    'user.permissions' => 'Atur Hak Akses Khusus Pengguna',
                    'role.index' => 'Lihat Daftar Peran (Roles)',
                    'role.create' => 'Tambah Role Baru',
                    'role.edit' => 'Ubah Nama & Keterangan Role',
                    'role.delete' => 'Hapus Role',
                    'role.permissions' => 'Konfigurasi Matriks Permission Role',
                    'permission.index' => 'Lihat Daftar Permission',
                    'permission.create' => 'Tambah Permission Baru',
                    'permission.edit' => 'Ubah Permission',
                    'permission.delete' => 'Hapus Permission',
                    'group_permission.index' => 'Lihat & Kelola Group Permission',
                ],
            ],
            'Pengaturan Sistem & Audit' => [
                'icon' => 'cog',
                'description' => 'Konfigurasi umum aplikasi, log audit, dan riwayat aktivitas',
                'permissions' => [
                    'audit.index' => 'Lihat Log Aktivitas Audit Sistem',
                    'settings.index' => 'Lihat Pengaturan Sistem',
                    'settings.edit' => 'Ubah Pengaturan Perusahaan & Aplikasi',
                ],
            ],
        ];

        $allPermissionIds = [];
        $hrAdminPermissionIds = [];
        $managerPermissionIds = [];
        $financePermissionIds = [];
        $employeePermissionIds = [];
        $investorPermissionIds = [];

        $sortOrder = 1;
        foreach ($matrix as $groupName => $groupConfig) {
            $group = PermissionGroup::updateOrCreate(
                ['name' => $groupName],
                [
                    'slug' => Str::slug($groupName),
                    'icon' => $groupConfig['icon'] ?? 'folder',
                    'description' => $groupConfig['description'] ?? null,
                    'sort_order' => $sortOrder++,
                ]
            );

            foreach ($groupConfig['permissions'] as $permKey => $permDisplay) {
                $permission = Permission::updateOrCreate(
                    ['name' => $permKey],
                    [
                        'permission_group_id' => $group->id,
                        'display_name' => $permDisplay,
                        'guard_name' => 'web',
                        'description' => $permDisplay,
                    ]
                );

                $allPermissionIds[] = $permission->id;

                // Segment permissions
                if (!str_starts_with($permKey, 'investor.') && !str_starts_with($permKey, 'user.') && !str_starts_with($permKey, 'role.')) {
                    $hrAdminPermissionIds[] = $permission->id;
                }
                if (str_starts_with($permKey, 'user.') || str_starts_with($permKey, 'role.')) {
                    $hrAdminPermissionIds[] = $permission->id;
                }

                if (in_array($permKey, [
                    'karyawan.index', 'karyawan.show',
                    'presensi.index', 'presensi.approve_koreksi', 'presensi.visit', 'presensi.live_map',
                    'cuti.index', 'cuti.approve', 'cuti.reject',
                    'lembur.index', 'lembur.approve', 'lembur.reject',
                    'reimbursement.index', 'reimbursement.approve', 'reimbursement.reject',
                    'pinjaman.index', 'pinjaman.approve',
                    'resign.index', 'resign.approve', 'resign.clearance',
                    'mutasi.index', 'mutasi.create', 'mutasi.approve',
                    'laporan.presensi', 'laporan.cuti', 'laporan.lembur',
                ])) {
                    $managerPermissionIds[] = $permission->id;
                }

                if (str_starts_with($permKey, 'reimbursement.') || str_starts_with($permKey, 'pinjaman.') || str_starts_with($permKey, 'payroll.') || str_starts_with($permKey, 'laporan.')) {
                    $financePermissionIds[] = $permission->id;
                }

                if (in_array($permKey, [
                    'presensi.self', 'presensi.koreksi', 'presensi.visit',
                    'cuti.create', 'cuti.index',
                    'lembur.create', 'lembur.index',
                    'reimbursement.create', 'reimbursement.index',
                    'pinjaman.create', 'pinjaman.index',
                    'resign.create', 'resign.index',
                    'mutasi.index',
                ])) {
                    $employeePermissionIds[] = $permission->id;
                }

                if (str_starts_with($permKey, 'investor.')) {
                    $investorPermissionIds[] = $permission->id;
                }
            }
        }

        // 3. Assign Permissions to Roles
        $roles['super_admin']->permissions()->sync($allPermissionIds);
        $roles['hr_admin']->permissions()->sync(array_unique($hrAdminPermissionIds));
        $roles['manager']->permissions()->sync(array_unique($managerPermissionIds));
        $roles['finance']->permissions()->sync(array_unique($financePermissionIds));
        $roles['employee']->permissions()->sync(array_unique($employeePermissionIds));
        $roles['investor']->permissions()->sync(array_unique($investorPermissionIds));
        $roles['supervisor']->permissions()->sync(array_unique(array_slice($managerPermissionIds, 0, 10)));

        // 4. Link existing users with roles and usernames
        $existingUsers = User::all();
        foreach ($existingUsers as $u) {
            $matchedRole = match($u->role) {
                'super_admin' => $roles['super_admin'],
                'hr_admin' => $roles['hr_admin'],
                'manager' => $roles['manager'],
                'finance' => $roles['finance'],
                'investor' => $roles['investor'],
                default => $roles['employee'],
            };

            $username = $u->username;
            if (empty($username)) {
                $base = explode('@', $u->email)[0];
                $username = Str::slug(str_replace('.', '_', $base), '_');
                // Ensure unique username
                $count = 1;
                $orig = $username;
                while (User::where('username', $username)->where('id', '!=', $u->id)->exists()) {
                    $username = $orig . '_' . $count++;
                }
            }

            $u->update([
                'role_id' => $matchedRole->id,
                'username' => $username,
                'all_branches' => true,
                'all_departments' => true,
            ]);

            $u->roles()->sync([$matchedRole->id]);
        }
    }
}


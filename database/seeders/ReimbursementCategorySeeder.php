<?php

namespace Database\Seeders;

use App\Models\ReimbursementCategory;
use Illuminate\Database\Seeder;

class ReimbursementCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'MED',
                'name' => 'Medis & Kesehatan',
                'description' => 'Klaim biaya pengobatan rawat jalan, obat dokter, dan kacamata kerja sesuai plafon kesehatan.',
                'currency' => 'ALL',
                'max_amount' => 5000000.00,
                'max_amount_sar' => 1200.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'code' => 'TRV',
                'name' => 'Transportasi & Perjalanan Dinas',
                'description' => 'Biaya tiket pesawat/kereta, bahan bakar bensin, tol, parkir, dan akomodasi dinas luar kota.',
                'currency' => 'ALL',
                'max_amount' => 10000000.00,
                'max_amount_sar' => 2500.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'code' => 'OPS',
                'name' => 'Operasional & Perlengkapan Kantor',
                'description' => 'Pengeluaran alat tulis kantor, fotokopi, materai, kurir, dan kebutuhan mendesak unit kerja.',
                'currency' => 'ALL',
                'max_amount' => 3000000.00,
                'max_amount_sar' => 750.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'code' => 'ENT',
                'name' => 'Jamuan Klien & Konsumsi',
                'description' => 'Biaya konsumsi rapat eksternal, jamuan mitra bisnis, dan makan meeting dinas resmi.',
                'currency' => 'ALL',
                'max_amount' => 5000000.00,
                'max_amount_sar' => 1200.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'code' => 'TEL',
                'name' => 'Komunikasi & Paket Data',
                'description' => 'Tunjangan penggantian pulsa, kuota internet operasional, dan komunikasi dinas.',
                'currency' => 'ALL',
                'max_amount' => 1500000.00,
                'max_amount_sar' => 350.00,
                'requires_receipt' => true,
                'is_active' => true,
            ],
            [
                'code' => 'OTH',
                'name' => 'Biaya Lain-lain',
                'description' => 'Pengeluaran dinas lain yang telah disetujui atasan langsung dan manajemen.',
                'currency' => 'ALL',
                'max_amount' => null,
                'max_amount_sar' => null,
                'requires_receipt' => true,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            ReimbursementCategory::updateOrCreate(
                ['code' => $cat['code']],
                $cat
            );
        }
    }
}


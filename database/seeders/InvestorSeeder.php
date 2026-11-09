<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Investor;
use App\Models\InvestorDistribution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InvestorSeeder extends Seeder
{
    public function run(): void
    {
        $riyadhBranch = Branch::where('country_code', 'SA')->orWhere('code', 'like', '%RUH%')->first();
        $jakartaBranch = Branch::where('country_code', 'ID')->orWhere('code', 'like', '%JKT%')->first();

        // 1. Investor Arab Saudi (SAR)
        $userSaudi = User::firstOrCreate(
            ['email' => 'investor.saudi@hris-ksa.com'],
            [
                'name' => 'Sheikh Abdulaziz Al-Ghamdi',
                'password' => Hash::make('investor12345'),
                'role' => 'investor',
                'phone' => '+966 50 123 4567',
                'is_active' => true,
            ]
        );

        $investorSaudi = Investor::updateOrCreate(
            ['investor_code' => 'INV-2026-001'],
            [
                'user_id' => $userSaudi->id,
                'branch_id' => $riyadhBranch?->id,
                'name' => 'Sheikh Abdulaziz Al-Ghamdi',
                'nik_or_passport' => 'KSA-NAT-10928374',
                'email' => 'investor.saudi@hris-ksa.com',
                'phone' => '+966 50 123 4567',
                'bank_name' => 'Al Rajhi Bank (مصرف الراجحي)',
                'bank_account_no' => 'SA44 8000 0201 9823 4810 11',
                'bank_account_holder' => 'Abdulaziz Al-Ghamdi',
                'currency' => 'SAR',
                'initial_investment' => 250000.00,
                'investment_date' => '2026-01-10',
                'contract_end_date' => '2028-12-31',
                'profit_share_percentage' => 20.00,
                'profit_share_scheme' => 'net_profit',
                'has_fixed_stipend' => true,
                'fixed_stipend_amount' => 4000.00,
                'stipend_payment_day' => 25,
                'include_stipend_in_bep' => false,
                'status' => 'active',
                'notes' => 'Mitra penanaman modal pengembangan operasional kantor cabang Riyadh. Menerima imbalan pengawas bulanan SAR 4,000.',
            ]
        );

        // Distributions for Saudi Investor
        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202603-001'],
            [
                'investor_id' => $investorSaudi->id,
                'type' => 'profit_share',
                'period_label' => 'Q1 2026',
                'distribution_date' => '2026-03-31',
                'currency' => 'SAR',
                'basis_amount' => 150000.00,
                'percentage_applied' => 20.00,
                'amount' => 30000.00,
                'status' => 'paid',
                'payment_date' => '2026-04-05',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'TX-RAJHI-9018241',
                'notes' => 'Bagi hasil kuartal 1 laba bersih cabang Riyadh',
            ]
        );

        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202606-001'],
            [
                'investor_id' => $investorSaudi->id,
                'type' => 'profit_share',
                'period_label' => 'Q2 2026',
                'distribution_date' => '2026-06-30',
                'currency' => 'SAR',
                'basis_amount' => 175000.00,
                'percentage_applied' => 20.00,
                'amount' => 35000.00,
                'status' => 'paid',
                'payment_date' => '2026-07-04',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'TX-RAJHI-9182910',
                'notes' => 'Bagi hasil kuartal 2 laba bersih cabang Riyadh',
            ]
        );

        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202609-001'],
            [
                'investor_id' => $investorSaudi->id,
                'type' => 'profit_share',
                'period_label' => 'Q3 2026',
                'distribution_date' => '2026-09-30',
                'currency' => 'SAR',
                'basis_amount' => 200000.00,
                'percentage_applied' => 20.00,
                'amount' => 40000.00,
                'status' => 'paid',
                'payment_date' => '2026-10-05',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'TX-RAJHI-9982312',
                'notes' => 'Bagi hasil kuartal 3 laba bersih cabang Riyadh',
            ]
        );

        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202610-001'],
            [
                'investor_id' => $investorSaudi->id,
                'type' => 'stipend',
                'period_label' => 'Oktober 2026',
                'distribution_date' => '2026-10-25',
                'currency' => 'SAR',
                'amount' => 4000.00,
                'status' => 'paid',
                'payment_date' => '2026-10-25',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'TX-RAJHI-STIP-1026',
                'notes' => 'Gaji khusus honorarium dewan pengawas bulan Oktober 2026',
            ]
        );

        // 2. Investor Indonesia (IDR)
        $userIndo = User::firstOrCreate(
            ['email' => 'investor.hendra@hris.id'],
            [
                'name' => 'Bpk. Hendra Wijaya',
                'password' => Hash::make('investor12345'),
                'role' => 'investor',
                'phone' => '+62 811 9876 5432',
                'is_active' => true,
            ]
        );

        $investorIndo = Investor::updateOrCreate(
            ['investor_code' => 'INV-2026-002'],
            [
                'user_id' => $userIndo->id,
                'branch_id' => $jakartaBranch?->id,
                'name' => 'Bpk. Hendra Wijaya',
                'nik_or_passport' => '3171051203800001',
                'email' => 'investor.hendra@hris.id',
                'phone' => '+62 811 9876 5432',
                'bank_name' => 'Bank Central Asia (BCA)',
                'bank_account_no' => '8830192831',
                'bank_account_holder' => 'Hendra Wijaya',
                'currency' => 'IDR',
                'initial_investment' => 500000000.00,
                'investment_date' => '2025-06-01',
                'contract_end_date' => '2027-12-31',
                'profit_share_percentage' => 15.00,
                'profit_share_scheme' => 'net_profit',
                'has_fixed_stipend' => true,
                'fixed_stipend_amount' => 15000000.00,
                'stipend_payment_day' => 25,
                'include_stipend_in_bep' => false,
                'status' => 'active',
                'notes' => 'Modal ekspansi layanan klinik & sistem presensi digital Indonesia.',
            ]
        );

        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202512-001'],
            [
                'investor_id' => $investorIndo->id,
                'type' => 'profit_share',
                'period_label' => 'Semester 2 2025',
                'distribution_date' => '2025-12-31',
                'currency' => 'IDR',
                'basis_amount' => 1000000000.00,
                'percentage_applied' => 15.00,
                'amount' => 150000000.00,
                'status' => 'paid',
                'payment_date' => '2026-01-05',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BCA-TRX-102941',
                'notes' => 'Bagi hasil semester 2 tahun 2025',
            ]
        );

        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202606-002'],
            [
                'investor_id' => $investorIndo->id,
                'type' => 'profit_share',
                'period_label' => 'Semester 1 2026',
                'distribution_date' => '2026-06-30',
                'currency' => 'IDR',
                'basis_amount' => 1200000000.00,
                'percentage_applied' => 15.00,
                'amount' => 180000000.00,
                'status' => 'paid',
                'payment_date' => '2026-07-05',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BCA-TRX-293812',
                'notes' => 'Bagi hasil semester 1 tahun 2026',
            ]
        );

        InvestorDistribution::firstOrCreate(
            ['distribution_number' => 'DST-202610-002'],
            [
                'investor_id' => $investorIndo->id,
                'type' => 'stipend',
                'period_label' => 'Oktober 2026',
                'distribution_date' => '2026-10-25',
                'currency' => 'IDR',
                'amount' => 15000000.00,
                'status' => 'paid',
                'payment_date' => '2026-10-25',
                'payment_method' => 'bank_transfer',
                'payment_reference' => 'BCA-TRX-STIP-10',
                'notes' => 'Gaji khusus dewan komisaris Oktober 2026',
            ]
        );
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Investor;
use App\Models\InvestorDistribution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestorFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $employeeUser;
    protected User $investorUser;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Cabang Riyadh KSA',
            'code' => 'BR-RUH',
            'country_code' => 'SA',
            'timezone' => 'Asia/Riyadh',
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->employeeUser = User::factory()->create([
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->investorUser = User::factory()->create([
            'role' => 'investor',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_investor_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('investors.index'));
        $response->assertStatus(200);
        $response->assertSee('Manajemen Investor & Bagi Hasil', false);

        $pagedResponse = $this->actingAs($this->superAdmin)->get(route('investors.index', ['per_page' => 3]));
        $pagedResponse->assertStatus(200);
        $pagedResponse->assertSee('3 / hal', false);
    }

    public function test_can_paginate_investor_list(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Investor::create([
                'investor_code' => "INV-PAG-$i",
                'name' => "Investor Pagination $i",
                'email' => "pag-$i@test.com",
                'branch_id' => $this->branch->id,
                'currency' => 'IDR',
                'initial_investment' => 100000000,
                'investment_date' => now(),
                'profit_share_scheme' => 'net_profit',
                'profit_share_percentage' => 10,
                'status' => 'active',
            ]);
        }

        $resPage1 = $this->actingAs($this->superAdmin)->get(route('investors.index', ['per_page' => 3, 'page' => 1]));
        $resPage1->assertStatus(200);
        $resPage1->assertSee('Menampilkan', false);
        $resPage1->assertSee('Berikutnya', false);

        $resPage2 = $this->actingAs($this->superAdmin)->get(route('investors.index', ['per_page' => 3, 'page' => 2]));
        $resPage2->assertStatus(200);
        $resPage2->assertSee('Sebelumnya', false);
    }

    public function test_employee_cannot_access_investor_management(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('investors.index'));
        $response->assertStatus(403);
    }

    public function test_can_create_new_investor_with_sar_currency_and_stipend(): void
    {
        $code = 'INV-TEST-001';

        $payload = [
            'investor_code' => $code,
            'name' => 'Investor Uji Coba KSA',
            'nik_or_passport' => 'KSA-998877',
            'email' => 'ujicoba@test.com',
            'phone' => '+966500001111',
            'branch_id' => $this->branch->id,
            'currency' => 'SAR',
            'initial_investment' => 100000.00,
            'investment_date' => '2026-01-01',
            'profit_share_percentage' => 25.00,
            'profit_share_scheme' => 'net_profit',
            'has_fixed_stipend' => '1',
            'fixed_stipend_amount' => 5000.00,
            'stipend_payment_day' => 25,
            'bank_name' => 'Alinma Bank',
            'bank_account_no' => 'SA991122334455',
            'bank_account_holder' => 'Investor Uji Coba KSA',
            'status' => 'active',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('investors.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('investors', [
            'investor_code' => $code,
            'currency' => 'SAR',
            'initial_investment' => 100000.00,
            'profit_share_percentage' => 25.00,
            'has_fixed_stipend' => 1,
            'fixed_stipend_amount' => 5000.00,
        ]);
    }

    public function test_can_record_distribution_and_update_bep(): void
    {
        $investor = Investor::create([
            'investor_code' => 'INV-TEST-002',
            'name' => 'Investor SAR Test',
            'branch_id' => $this->branch->id,
            'currency' => 'SAR',
            'initial_investment' => 50000.00,
            'investment_date' => '2026-01-01',
            'profit_share_percentage' => 20.00,
            'profit_share_scheme' => 'net_profit',
            'status' => 'active',
        ]);

        $distNumber = 'DST-TEST-001';

        $payload = [
            'distribution_number' => $distNumber,
            'type' => 'profit_share',
            'period_label' => 'Q1 2026',
            'distribution_date' => '2026-03-31',
            'basis_amount' => 50000.00,
            'percentage_applied' => 20.00,
            'amount' => 10000.00,
            'status' => 'paid',
            'payment_date' => '2026-04-05',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'TEST-REF-123',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('investors.distributions.store', $investor), $payload);
        $response->assertRedirect(route('investors.show', $investor));

        $this->assertDatabaseHas('investor_distributions', [
            'distribution_number' => $distNumber,
            'amount' => 10000.00,
            'status' => 'paid',
        ]);

        $investor->refresh();
        $this->assertEquals(20.0, $investor->bep_progress_percentage);
        $this->assertEquals(40000.0, $investor->bep_remaining);
        $this->assertFalse($investor->is_bep_achieved);

        // Record another paid distribution that completes BEP
        $distNumber2 = 'DST-TEST-002';
        $this->actingAs($this->superAdmin)->post(route('investors.distributions.store', $investor), [
            'distribution_number' => $distNumber2,
            'type' => 'profit_share',
            'period_label' => 'Q2 2026',
            'distribution_date' => '2026-06-30',
            'amount' => 45000.00,
            'status' => 'paid',
            'payment_date' => '2026-07-05',
            'payment_method' => 'bank_transfer',
        ]);

        $investor->refresh();
        $this->assertEquals(100.0, $investor->bep_progress_percentage);
        $this->assertEquals(0.0, $investor->bep_remaining);
        $this->assertTrue($investor->is_bep_achieved);
    }

    public function test_investor_can_access_self_service_portal(): void
    {
        $investor = Investor::create([
            'user_id' => $this->investorUser->id,
            'investor_code' => 'INV-TEST-PORTAL',
            'name' => $this->investorUser->name,
            'currency' => 'SAR',
            'initial_investment' => 100000.00,
            'investment_date' => '2026-01-01',
            'profit_share_percentage' => 20.00,
            'profit_share_scheme' => 'net_profit',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->investorUser)->get(route('investors.portal'));
        $response->assertStatus(200);
        $response->assertSee('Portofolio', false);
    }

    public function test_can_access_standalone_distribution_forms_without_modal(): void
    {
        $investor = Investor::create([
            'investor_code' => 'INV-TEST-FORMS',
            'name' => 'Investor Form Test',
            'branch_id' => $this->branch->id,
            'currency' => 'SAR',
            'initial_investment' => 60000.00,
            'investment_date' => '2026-01-01',
            'profit_share_percentage' => 15.00,
            'profit_share_scheme' => 'net_profit',
            'status' => 'active',
        ]);

        // Form Create Distribusi
        $createRes = $this->actingAs($this->superAdmin)->get(route('investors.distributions.create', $investor));
        $createRes->assertStatus(200);
        $createRes->assertSee('Catat Transaksi Bagi Hasil & Gaji Khusus', false);

        // Buat distribusi untuk pengujian edit & pay
        $distribution = InvestorDistribution::create([
            'investor_id' => $investor->id,
            'distribution_number' => 'DST-FORM-001',
            'type' => 'profit_share',
            'period_label' => 'Oktober 2026',
            'distribution_date' => '2026-10-31',
            'amount' => 5000.00,
            'status' => 'pending',
        ]);

        // Form Edit Distribusi
        $editRes = $this->actingAs($this->superAdmin)->get(route('investors.distributions.edit', $distribution));
        $editRes->assertStatus(200);
        $editRes->assertSee('Edit Transaksi', false);

        // Form Pelunasan / Pay Distribusi
        $payRes = $this->actingAs($this->superAdmin)->get(route('investors.distributions.pay', $distribution));
        $payRes->assertStatus(200);
        $payRes->assertSee('Konfirmasi Realisasi Pembayaran Dana', false);
    }

    public function test_can_export_investor_portfolio_pdf_and_master_report_pdf(): void
    {
        $investor = Investor::create([
            'investor_code' => 'INV-TEST-PDF',
            'name' => 'Sheikh Abdullah Al-Saud',
            'branch_id' => $this->branch->id,
            'currency' => 'SAR',
            'initial_investment' => 200000.00,
            'investment_date' => '2026-01-01',
            'profit_share_percentage' => 30.00,
            'profit_share_scheme' => 'net_profit',
            'status' => 'active',
        ]);

        // Export Portfolio Individual PDF
        $pdfRes = $this->actingAs($this->superAdmin)->get(route('investors.pdf', $investor));
        $pdfRes->assertStatus(200);
        $pdfRes->assertHeader('content-type', 'application/pdf');

        // Export Master Executive Summary PDF
        $summaryPdfRes = $this->actingAs($this->superAdmin)->get(route('investors.report.pdf'));
        $summaryPdfRes->assertStatus(200);
        $summaryPdfRes->assertHeader('content-type', 'application/pdf');
    }

    public function test_investor_login_redirects_to_portal_directly(): void
    {
        $loginRes = $this->post('/login', [
            'email' => $this->investorUser->email,
            'password' => 'password',
        ]);

        $loginRes->assertRedirect(route('investors.portal'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
use App\Models\PayrollPeriod;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LoanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrAdmin;
    protected User $financeUser;
    protected User $employeeUser;
    protected Employee $employee;
    protected Branch $branch;
    protected Department $department;
    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->superAdmin = User::factory()->create([
            'email' => 'admin_test@example.com',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->hrAdmin = User::factory()->create([
            'email' => 'hr_test@example.com',
            'role' => 'hr_admin',
            'is_active' => true,
        ]);

        $this->financeUser = User::factory()->create([
            'email' => 'finance_test@example.com',
            'role' => 'finance',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'code' => 'BR-TEST',
            'name' => 'Cabang Test',
            'timezone' => 'Asia/Jakarta',
            'country_code' => 'ID',
            'currency' => 'IDR',
            'radius_meters' => 150,
            'latitude' => -6.2,
            'longitude' => 106.8,
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'name' => 'IT Engineering',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $this->position = Position::create([
            'code' => 'SE-01',
            'title' => 'Software Engineer',
            'department_id' => $this->department->id,
            'level' => 3,
            'is_active' => true,
        ]);

        $this->employeeUser = User::factory()->create([
            'email' => 'staff_test@example.com',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'staff_test@example.com',
            'branch_id' => $this->branch->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'join_date' => now()->subYear(),
            'employment_status' => 'permanent',
            'gender' => 'male',
            'status' => 'active',
            'currency' => 'IDR',
        ]);
    }

    public function test_can_view_loans_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('loans.index'));
        $response->assertStatus(200);
        $response->assertSee('Pinjaman & Kasbon Karyawan', false);
        $response->assertSee('Ajukan Pinjaman Baru');
    }

    public function test_employee_can_submit_loan_request_with_auto_generated_schedule(): void
    {
        $file = UploadedFile::fake()->create('surat_permohonan.pdf', 300, 'application/pdf');

        $payload = [
            'amount' => 6000000,
            'currency' => 'IDR',
            'tenor_months' => 6,
            'interest_rate_percent' => 0,
            'repayment_start_date' => '2026-12-01',
            'reason' => 'Pinjaman untuk renovasi rumah mendesak.',
            'payroll_deduction' => 1,
            'attachment' => $file,
        ];

        $response = $this->actingAs($this->employeeUser)->post(route('loans.store'), $payload);

        $this->assertDatabaseHas('employee_loans', [
            'employee_id' => $this->employee->id,
            'amount' => 6000000,
            'currency' => 'IDR',
            'tenor_months' => 6,
            'status' => 'pending',
            'monthly_installment' => 1000000,
        ]);

        $loan = EmployeeLoan::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($loan);
        $this->assertCount(6, $loan->installments);

        $response->assertRedirect(route('loans.show', $loan));
    }

    public function test_can_view_dedicated_approval_page_and_approve_loan(): void
    {
        $loan = EmployeeLoan::create([
            'loan_number' => 'LOAN-202611-0001',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'currency' => 'SAR',
            'amount' => 3000,
            'approved_amount' => 3000,
            'tenor_months' => 3,
            'interest_rate_percent' => 0,
            'monthly_installment' => 1000,
            'repayment_start_date' => '2026-12-01',
            'reason' => 'Biaya kursus bahasa Arab',
            'payroll_deduction' => true,
            'status' => 'pending',
        ]);
        $loan->generateInstallments();

        // 1. Visit non-modal dedicated approval page
        $resPage = $this->actingAs($this->hrAdmin)->get(route('loans.approve.form', $loan));
        $resPage->assertStatus(200);
        $resPage->assertSee('Persetujuan Pinjaman Karyawan');
        $resPage->assertSee('LOAN-202611-0001');

        // 2. Submit partial approval
        $resApprove = $this->actingAs($this->hrAdmin)->post(route('loans.approve', $loan), [
            'approval_action' => 'partial',
            'approved_amount' => 2400,
            'tenor_months' => 4,
            'approval_notes' => 'Disetujui sebagian 2400 SAR dengan tenor 4 bulan.',
        ]);

        $resApprove->assertRedirect(route('loans.show', $loan));

        $loan->refresh();
        $this->assertEquals('approved', $loan->status);
        $this->assertEquals(2400, (float) $loan->approved_amount);
        $this->assertEquals(4, $loan->tenor_months);
        $this->assertEquals(600, (float) $loan->monthly_installment);
        $this->assertCount(4, $loan->installments);
    }

    public function test_can_view_dedicated_disburse_page_and_disburse_loan(): void
    {
        $loan = EmployeeLoan::create([
            'loan_number' => 'LOAN-202611-0002',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'currency' => 'IDR',
            'amount' => 5000000,
            'approved_amount' => 5000000,
            'tenor_months' => 5,
            'monthly_installment' => 1000000,
            'repayment_start_date' => '2026-12-01',
            'reason' => 'Kasbon keluarga',
            'status' => 'approved',
            'approved_by' => $this->hrAdmin->id,
            'approved_at' => now(),
        ]);
        $loan->generateInstallments();

        // 1. Visit non-modal disburse page
        $resPage = $this->actingAs($this->financeUser)->get(route('loans.disburse.form', $loan));
        $resPage->assertStatus(200);
        $resPage->assertSee('Pencairan Dana Pinjaman (Finance)');

        // 2. Disburse
        $proof = UploadedFile::fake()->create('bukti_transfer.jpg', 200, 'image/jpeg');
        $resDisburse = $this->actingAs($this->financeUser)->post(route('loans.disburse', $loan), [
            'disbursed_at' => '2026-11-06',
            'payment_method' => 'bank_transfer',
            'disbursement_reference' => 'TRF-BNI-9921',
            'disbursement_proof' => $proof,
            'disbursement_notes' => 'Telah ditransfer ke rekening BNI pegawai.',
        ]);

        $resDisburse->assertRedirect(route('loans.show', $loan));

        $loan->refresh();
        $this->assertEquals('active', $loan->status);
        $this->assertEquals('bank_transfer', $loan->payment_method);
        $this->assertEquals('TRF-BNI-9921', $loan->disbursement_reference);
        $this->assertNotNull($loan->disbursed_at);
        $this->assertNotNull($loan->disbursement_proof);
    }

    public function test_can_record_repayment_and_complete_loan(): void
    {
        $loan = EmployeeLoan::create([
            'loan_number' => 'LOAN-202611-0003',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'currency' => 'IDR',
            'amount' => 2000000,
            'approved_amount' => 2000000,
            'tenor_months' => 2,
            'monthly_installment' => 1000000,
            'repayment_start_date' => '2026-12-01',
            'reason' => 'Kasbon singkat',
            'status' => 'active',
            'disbursed_at' => now(),
        ]);
        $loan->generateInstallments();

        // 1. Visit non-modal repayment page
        $resPage = $this->actingAs($this->financeUser)->get(route('loans.repay.form', $loan));
        $resPage->assertStatus(200);
        $resPage->assertSee('Pencatatan Angsuran / Pelunasan');

        // 2. Pay single installment 1.000.000
        $this->actingAs($this->financeUser)->post(route('loans.repay', $loan), [
            'repay_type' => 'single_installment',
            'amount' => 1000000,
            'paid_date' => '2026-12-01',
            'payment_method' => 'bank_transfer',
            'reference_number' => 'REF-01',
        ]);

        $loan->refresh();
        $this->assertEquals('active', $loan->status);
        $this->assertEquals(1000000, (float) $loan->total_repaid_amount);
        $this->assertEquals(1000000, (float) $loan->remaining_amount);
        $this->assertEquals(1, $loan->paid_installments_count);

        // 3. Pay remaining balance (full settlement)
        $this->actingAs($this->financeUser)->post(route('loans.repay', $loan), [
            'repay_type' => 'full_settlement',
            'amount' => 1000000,
            'paid_date' => '2026-12-15',
            'payment_method' => 'cash',
            'reference_number' => 'REF-02',
        ]);

        $loan->refresh();
        $this->assertEquals('completed', $loan->status);
        $this->assertEquals(2000000, (float) $loan->total_repaid_amount);
        $this->assertEquals(0, (float) $loan->remaining_amount);
        $this->assertEquals(2, $loan->paid_installments_count);
        $this->assertEquals(100, $loan->repayment_progress_percentage);
    }

    public function test_can_export_loan_agreement_pdf(): void
    {
        $loan = EmployeeLoan::create([
            'loan_number' => 'LOAN-202611-0004',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'currency' => 'SAR',
            'amount' => 5000,
            'approved_amount' => 5000,
            'tenor_months' => 5,
            'monthly_installment' => 1000,
            'repayment_start_date' => '2026-12-01',
            'reason' => 'Biaya relokasi',
            'status' => 'approved',
        ]);
        $loan->generateInstallments();

        $response = $this->actingAs($this->employeeUser)->get(route('loans.pdf', $loan));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');

        $viewHtml = view('reports.pdf.loan_agreement', [
            'loan' => $loan,
            'hrManagerName' => 'Siti Rahmawati',
            'hrManagerTitle' => 'HR Manager',
            'financeManagerName' => 'Bambang Sudirman',
            'financeManagerTitle' => 'Finance Manager',
        ])->render();

        $this->assertStringContainsString('Siti Rahmawati', $viewHtml);
        $this->assertStringContainsString('Bambang Sudirman', $viewHtml);
    }

    public function test_active_loan_installment_is_deducted_in_payroll_and_marked_paid_on_lock(): void
    {
        \App\Models\SalaryComponent::create([
            'code' => 'BASIC',
            'name' => 'Gaji Pokok',
            'type' => 'earning',
            'calculation_mode' => 'fixed',
            'rate_value' => 5000000,
            'is_active' => true,
        ]);

        \App\Models\EmployeeCompensation::create([
            'employee_id' => $this->employee->id,
            'salary_component_id' => 1,
            'amount' => 5000000,
        ]);

        $loan = EmployeeLoan::create([
            'loan_number' => 'LOAN-202611-0005',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'currency' => 'IDR',
            'amount' => 3000000,
            'approved_amount' => 3000000,
            'tenor_months' => 3,
            'monthly_installment' => 1000000,
            'repayment_start_date' => '2026-11-01',
            'reason' => 'Pinjaman operasional',
            'payroll_deduction' => true,
            'status' => 'active',
            'disbursed_at' => now(),
        ]);
        $loan->generateInstallments();

        $period = PayrollPeriod::create([
            'name' => 'Payroll November 2026',
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'cut_off_date' => '2026-11-25',
            'payment_date' => '2026-11-30',
            'status' => 'draft',
        ]);

        // Calculate payroll
        $calcResponse = $this->actingAs($this->superAdmin)->post(route('payroll.calculate', $period));
        $calcResponse->assertSessionHas('success');

        $periodItem = \App\Models\PayrollItem::where('payroll_period_id', $period->id)
            ->where('employee_id', $this->employee->id)
            ->first();

        $this->assertNotNull($periodItem);
        $this->assertArrayHasKey('DED_LOAN', $periodItem->details['deduction_breakdown']);
        $this->assertEquals(1000000, (float) $periodItem->details['deduction_breakdown']['DED_LOAN']['amount']);
        $this->assertEquals(4000000, (float) $periodItem->net_salary); // 5.000.000 - 1.000.000 = 4.000.000

        // Lock payroll period
        $lockResponse = $this->actingAs($this->superAdmin)->post(route('payroll.lock', $period));
        $lockResponse->assertSessionHas('success');

        $firstInstallment = $loan->installments()->where('installment_number', 1)->first();
        $this->assertEquals('paid', $firstInstallment->status);
        $this->assertEquals('payroll', $firstInstallment->payment_method);
        $this->assertEquals($period->id, $firstInstallment->payroll_period_id);
    }
}

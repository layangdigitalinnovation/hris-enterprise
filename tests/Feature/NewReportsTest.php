<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\Position;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewReportsTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Employee $employee;
    protected Branch $branch;
    protected Department $department;
    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'email' => 'admin_report_test@example.com',
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'name' => 'Kantor Cabang Jakarta',
            'code' => 'JKT-01',
            'address' => 'Jl. Sudirman No. 1',
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'name' => 'Teknologi Informasi & Sistem',
            'code' => 'IT-01',
            'is_active' => true,
        ]);

        $this->position = Position::create([
            'title' => 'Software Engineer',
            'code' => 'SE-01',
            'department_id' => $this->department->id,
            'level' => 2,
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->superAdmin->id,
            'employee_code' => 'EMP-TEST-999',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi_test@example.com',
            'branch_id' => $this->branch->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'status' => 'active',
            'join_date' => '2023-01-01',
            'employment_status' => 'permanent',
        ]);
    }

    public function test_user_can_access_reimbursement_report_page(): void
    {
        // 1. Indonesian
        app()->setLocale('id');
        $res = $this->actingAs($this->superAdmin)->get(route('reports.reimbursement'));
        $res->assertStatus(200);
        $res->assertSee('Laporan Rekapitulasi Reimbursement');
        $res->assertSee('Parameter Laporan Reimbursement');

        // 2. Saudi Arabic
        app()->setLocale('ar');
        $resAr = $this->actingAs($this->superAdmin)->get(route('reports.reimbursement'));
        $resAr->assertStatus(200);
        $resAr->assertSee('تقرير ملخص مطالبات التعويضات والمصروفات');
    }

    public function test_user_can_access_loans_report_page(): void
    {
        // 1. Indonesian
        app()->setLocale('id');
        $res = $this->actingAs($this->superAdmin)->get(route('reports.loans'));
        $res->assertStatus(200);
        $res->assertSee('Laporan Rekapitulasi Pinjaman Karyawan');
        $res->assertSee('Parameter Laporan Pinjaman');

        // 2. Saudi Arabic
        app()->setLocale('ar');
        $resAr = $this->actingAs($this->superAdmin)->get(route('reports.loans'));
        $resAr->assertStatus(200);
        $resAr->assertSee('تقرير ملخص سلف وقروض الموظفين');
    }

    public function test_can_export_reimbursement_pdf_and_excel(): void
    {
        $category = ReimbursementCategory::create([
            'name' => 'Transportasi & Bensin',
            'code' => 'TRP-01',
            'is_active' => true,
        ]);

        ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-001',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $category->id,
            'title' => 'Bensin Kunjungan Client',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'IDR',
            'amount' => 150000,
            'approved_amount' => 150000,
            'status' => 'approved',
            'payment_method' => 'transfer',
        ]);

        // PDF Export - ID
        app()->setLocale('id');
        $resPdfId = $this->actingAs($this->superAdmin)->post(route('reports.reimbursement.export'), [
            'action_type' => 'pdf',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resPdfId->assertHeader('content-type', 'application/pdf');

        // PDF Export - AR
        app()->setLocale('ar');
        $resPdfAr = $this->actingAs($this->superAdmin)->post(route('reports.reimbursement.export'), [
            'action_type' => 'pdf',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resPdfAr->assertHeader('content-type', 'application/pdf');

        // Excel Export - ID
        app()->setLocale('id');
        $resXlsId = $this->actingAs($this->superAdmin)->post(route('reports.reimbursement.export'), [
            'action_type' => 'excel',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resXlsId->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Excel Export - AR
        app()->setLocale('ar');
        $resXlsAr = $this->actingAs($this->superAdmin)->post(route('reports.reimbursement.export'), [
            'action_type' => 'excel',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resXlsAr->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_can_export_loans_pdf_and_excel(): void
    {
        EmployeeLoan::create([
            'loan_number' => 'LOAN-TEST-001',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'currency' => 'IDR',
            'amount' => 5000000,
            'approved_amount' => 5000000,
            'tenor_months' => 5,
            'monthly_installment' => 1000000,
            'reason' => 'Keperluan mendesak keluarga',
            'status' => 'active',
            'payment_method' => 'transfer',
            'repayment_start_date' => now()->format('Y-m-d'),
        ]);

        // PDF Export - ID
        app()->setLocale('id');
        $resPdfId = $this->actingAs($this->superAdmin)->post(route('reports.loans.export'), [
            'action_type' => 'pdf',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resPdfId->assertHeader('content-type', 'application/pdf');

        // PDF Export - AR
        app()->setLocale('ar');
        $resPdfAr = $this->actingAs($this->superAdmin)->post(route('reports.loans.export'), [
            'action_type' => 'pdf',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resPdfAr->assertHeader('content-type', 'application/pdf');

        // Excel Export - ID
        app()->setLocale('id');
        $resXlsId = $this->actingAs($this->superAdmin)->post(route('reports.loans.export'), [
            'action_type' => 'excel',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resXlsId->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Excel Export - AR
        app()->setLocale('ar');
        $resXlsAr = $this->actingAs($this->superAdmin)->post(route('reports.loans.export'), [
            'action_type' => 'excel',
            'periode_laporan' => '2',
            'bulan' => date('m'),
            'tahun' => date('Y'),
        ]);
        $resXlsAr->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_indonesian_url_aliases(): void
    {
        $resReimburse = $this->actingAs($this->superAdmin)->get('/laporan/reimbursement');
        $resReimburse->assertStatus(200);

        $resLoan = $this->actingAs($this->superAdmin)->get('/laporan/pinjaman');
        $resLoan->assertStatus(200);
    }
}

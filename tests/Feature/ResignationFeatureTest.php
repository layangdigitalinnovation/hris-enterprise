<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\EmployeeResignation;
use App\Models\Position;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResignationFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrAdmin;
    protected User $managerUser;
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

        $this->managerUser = User::factory()->create([
            'email' => 'manager_test@example.com',
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->branch = Branch::create([
            'code' => 'BR-RSG',
            'name' => 'Kantor Pusat Jakarta',
            'timezone' => 'Asia/Jakarta',
            'country_code' => 'ID',
            'currency' => 'IDR',
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'code' => 'DEPT-ENG',
            'name' => 'Engineering',
            'is_active' => true,
        ]);

        $this->position = Position::create([
            'code' => 'POS-DEV',
            'title' => 'Software Engineer',
            'is_active' => true,
        ]);

        $this->employeeUser = User::factory()->create([
            'email' => 'emp_dev@example.com',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'employee_code' => 'EMP-TEST-001',
            'first_name' => 'Ahmad',
            'last_name' => 'Fauzi',
            'email' => 'emp_dev@example.com',
            'join_date' => Carbon::now()->subYears(2)->format('Y-m-d'),
            'employment_status' => 'permanent',
            'status' => 'active',
        ]);
    }

    public function test_admin_and_hr_can_view_resignation_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('resignations.index'));
        $response->assertStatus(200);
        $response->assertSee('Resign');

        $responseHr = $this->actingAs($this->hrAdmin)->get(route('resignations.index'));
        $responseHr->assertStatus(200);
    }

    public function test_employee_can_access_resignation_create_page(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('resignations.create'));
        $response->assertStatus(200);
        $response->assertSee($this->employee->full_name);
        $response->assertSee('Notice Period');
    }

    public function test_employee_can_submit_resignation_request(): void
    {
        $requestedLastDay = Carbon::today()->addDays(35)->format('Y-m-d');
        $letterFile = UploadedFile::fake()->create('surat_resign.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->employeeUser)->post(route('resignations.store'), [
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => $requestedLastDay,
            'reason_type' => 'career_growth',
            'reason_detail' => 'Mendapatkan kesempatan karir baru yang relevan dengan spesialisasi.',
            'handover_notes' => 'Tugas telah didokumentasikan di internal wiki dan diserahkan ke PIC pengganti.',
            'resignation_letter' => $letterFile,
        ]);

        $resignation = EmployeeResignation::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($resignation);
        $response->assertRedirect(route('resignations.show', $resignation));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('employee_resignations', [
            'employee_id' => $this->employee->id,
            'reason_type' => 'career_growth',
            'status' => 'pending',
        ]);

        $this->assertTrue($resignation->is_standard_notice);
        $this->assertEquals(35, $resignation->notice_days_count);
        $this->assertStringStartsWith('RSG-', $resignation->resignation_number);
        $this->assertNotNull($resignation->resignation_letter_path);
        Storage::disk('public')->assertExists($resignation->resignation_letter_path);
    }

    public function test_employee_cannot_submit_duplicate_active_resignation(): void
    {
        // First resignation
        EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0001',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'personal',
            'reason_detail' => 'Alasan keluarga mendesak dan relevan.',
            'status' => 'pending',
        ]);

        // Attempting to submit another
        $response = $this->actingAs($this->employeeUser)->post(route('resignations.store'), [
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => Carbon::today()->addDays(35)->format('Y-m-d'),
            'reason_type' => 'personal',
            'reason_detail' => 'Mencoba submit lagi setelah pengajuan pertama.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_resignation_show_displays_active_loans_alert(): void
    {
        $resignation = EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0002',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'compensation',
            'reason_detail' => 'Kompensasi dan benefit yang lebih memadai.',
            'status' => 'pending',
        ]);

        // Create an active loan with outstanding balance
        EmployeeLoan::create([
            'loan_number' => 'LN-TEST-001',
            'employee_id' => $this->employee->id,
            'currency' => 'IDR',
            'amount' => 10000000,
            'approved_amount' => 10000000,
            'repayment_start_date' => Carbon::now()->format('Y-m-d'),
            'tenor_months' => 10,
            'monthly_installment' => 1000000,
            'status' => 'active',
            'reason' => 'Renovasi Rumah',
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->hrAdmin)->get(route('resignations.show', $resignation));
        $response->assertStatus(200);
        $response->assertSee('LN-TEST-001');
        $response->assertSee('Pinjaman');
    }

    public function test_hr_or_manager_can_approve_resignation(): void
    {
        $resignation = EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0003',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'personal',
            'reason_detail' => 'Alasan pribadi yang valid.',
            'status' => 'pending',
        ]);

        $approvedLastDay = Carbon::today()->addDays(30)->format('Y-m-d');

        $response = $this->actingAs($this->hrAdmin)->post(route('resignations.approve', $resignation), [
            'approved_last_day' => $approvedLastDay,
            'approval_notes' => 'Disetujui setelah diskusi manajerial.',
            'exit_interview_notes' => 'Karyawan memberikan masukan konstruktif bagi tim.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resignation->refresh();
        $this->assertEquals('approved', $resignation->status);
        $this->assertEquals($this->hrAdmin->id, $resignation->approved_by);
        $this->assertNotNull($resignation->approved_at);
        $this->assertEquals($approvedLastDay, $resignation->approved_last_day->format('Y-m-d'));
    }

    public function test_hr_or_manager_can_reject_resignation(): void
    {
        $resignation = EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0004',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'personal',
            'reason_detail' => 'Alasan pribadi.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('resignations.reject', $resignation), [
            'rejection_reason' => 'Proyek kritis kuartal ini membutuhkan kehadiran yang bersangkutan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resignation->refresh();
        $this->assertEquals('rejected', $resignation->status);
        $this->assertNotNull($resignation->rejection_reason);
    }

    public function test_clearance_checklist_update(): void
    {
        $resignation = EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0005',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->format('Y-m-d'),
            'requested_last_day' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'personal',
            'reason_detail' => 'Alasan pribadi.',
            'status' => 'approved',
            'approved_last_day' => Carbon::today()->addDays(30)->format('Y-m-d'),
        ]);

        $response = $this->actingAs($this->hrAdmin)->post(route('resignations.clearance', $resignation), [
            'clearance_assets_returned' => 1,
            'clearance_assets_notes' => 'Laptop ThinkPad dan ID Card telah diterima lengkap.',
            'clearance_finance_settled' => 1,
            'clearance_finance_notes' => 'Tidak ada tunggakan kasbon.',
            'clearance_handover_completed' => 0,
            'clearance_handover_notes' => 'Dokumentasi handover masih dalam proses review.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resignation->refresh();
        $this->assertTrue((bool)$resignation->clearance_assets_returned);
        $this->assertTrue((bool)$resignation->clearance_finance_settled);
        $this->assertFalse((bool)$resignation->clearance_handover_completed);
        $this->assertEquals(67, $resignation->clearance_percentage);
    }

    public function test_complete_offboarding_deactivates_employee(): void
    {
        $lastDay = Carbon::today()->addDays(15)->format('Y-m-d');

        $resignation = EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0006',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->subDays(15)->format('Y-m-d'),
            'requested_last_day' => $lastDay,
            'approved_last_day' => $lastDay,
            'reason_type' => 'career_growth',
            'reason_detail' => 'Peningkatan karir.',
            'status' => 'approved',
            'clearance_assets_returned' => true,
            'clearance_finance_settled' => true,
            'clearance_handover_completed' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('resignations.complete', $resignation), [
            'completion_notes' => 'Seluruh proses clearance telah tuntas dan paklaring diterbitkan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $resignation->refresh();
        $this->assertEquals('completed', $resignation->status);
        $this->assertEquals($this->superAdmin->id, $resignation->completed_by);
        $this->assertNotNull($resignation->completed_at);

        // Employee status must be inactive and end_date set
        $this->employee->refresh();
        $this->assertEquals('inactive', $this->employee->status);
        $this->assertEquals($lastDay, $this->employee->end_date->format('Y-m-d'));
    }

    public function test_export_pdf_certificate_indonesian_and_arabic(): void
    {
        $lastDay = Carbon::today()->addDays(10)->format('Y-m-d');

        $resignation = EmployeeResignation::create([
            'resignation_number' => 'RSG-202611-0007',
            'employee_id' => $this->employee->id,
            'submission_date' => Carbon::today()->subDays(20)->format('Y-m-d'),
            'requested_last_day' => $lastDay,
            'approved_last_day' => $lastDay,
            'reason_type' => 'personal',
            'reason_detail' => 'Alasan pribadi.',
            'status' => 'completed',
            'clearance_assets_returned' => true,
            'clearance_finance_settled' => true,
            'clearance_handover_completed' => true,
        ]);

        // Indonesian PDF
        app()->setLocale('id');
        $responseId = $this->actingAs($this->hrAdmin)->get(route('resignations.pdf', $resignation));
        $responseId->assertStatus(200);
        $responseId->assertHeader('content-type', 'application/pdf');

        // Saudi Arabic PDF
        app()->setLocale('ar');
        $responseAr = $this->actingAs($this->hrAdmin)->get(route('resignations.pdf', $resignation));
        $responseAr->assertStatus(200);
        $responseAr->assertHeader('content-type', 'application/pdf');
    }
}

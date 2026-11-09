<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCareerTransition;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerTransitionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrAdmin;
    protected User $managerUser;
    protected User $employeeUser;
    protected Employee $employee;
    protected Employee $managerEmployee;
    protected Branch $branchJakarta;
    protected Branch $branchRiyadh;
    protected Department $deptEngineering;
    protected Department $deptOperations;
    protected Position $posJuniorDev;
    protected Position $posSeniorDev;
    protected SalaryComponent $basicSalaryComponent;

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

        $this->branchJakarta = Branch::create([
            'code' => 'BR-JKT',
            'name' => 'Kantor Pusat Jakarta',
            'timezone' => 'Asia/Jakarta',
            'country_code' => 'ID',
            'currency' => 'IDR',
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $this->branchRiyadh = Branch::create([
            'code' => 'BR-RUH',
            'name' => 'Riyadh Regional Office',
            'timezone' => 'Asia/Riyadh',
            'country_code' => 'SA',
            'currency' => 'SAR',
            'radius_meters' => 200,
            'is_active' => true,
        ]);

        $this->deptEngineering = Department::create([
            'code' => 'DEPT-ENG',
            'name' => 'Engineering',
            'is_active' => true,
        ]);

        $this->deptOperations = Department::create([
            'code' => 'DEPT-OPS',
            'name' => 'Operations & Logistics',
            'is_active' => true,
        ]);

        $this->posJuniorDev = Position::create([
            'code' => 'POS-JDEV',
            'title' => 'Junior Software Engineer',
            'is_active' => true,
        ]);

        $this->posSeniorDev = Position::create([
            'code' => 'POS-SDEV',
            'title' => 'Senior Software Engineer / Lead',
            'is_active' => true,
        ]);

        $this->managerUser = User::factory()->create([
            'email' => 'mgr_eng@example.com',
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->managerEmployee = Employee::create([
            'user_id' => $this->managerUser->id,
            'branch_id' => $this->branchJakarta->id,
            'department_id' => $this->deptEngineering->id,
            'position_id' => $this->posSeniorDev->id,
            'employee_code' => 'EMP-MGR-001',
            'first_name' => 'Bambang',
            'last_name' => 'Sudirman',
            'email' => 'mgr_eng@example.com',
            'join_date' => Carbon::now()->subYears(4)->format('Y-m-d'),
            'employment_status' => 'permanent',
            'status' => 'active',
        ]);

        $this->employeeUser = User::factory()->create([
            'email' => 'emp_dev@example.com',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branchJakarta->id,
            'department_id' => $this->deptEngineering->id,
            'position_id' => $this->posJuniorDev->id,
            'manager_id' => $this->managerEmployee->id,
            'employee_code' => 'EMP-TEST-002',
            'first_name' => 'Ahmad',
            'last_name' => 'Fauzi',
            'email' => 'emp_dev@example.com',
            'join_date' => Carbon::now()->subYears(2)->format('Y-m-d'),
            'employment_status' => 'permanent',
            'status' => 'active',
        ]);

        $this->basicSalaryComponent = SalaryComponent::create([
            'code' => 'BASIC',
            'name' => 'Gaji Pokok',
            'type' => 'earning',
            'calculation_mode' => 'fixed',
            'default_amount' => 5000000,
            'is_taxable' => true,
            'is_fixed' => true,
            'is_active' => true,
        ]);

        $this->employee->compensations()->create([
            'salary_component_id' => $this->basicSalaryComponent->id,
            'amount' => 6000000,
        ]);
    }

    public function test_admin_and_hr_can_view_career_transitions_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('career-transitions.index'));
        $response->assertStatus(200);
        $response->assertSee('Mutasi & Promosi');

        $responseHr = $this->actingAs($this->hrAdmin)->get(route('career-transitions.index'));
        $responseHr->assertStatus(200);
    }

    public function test_manager_can_access_create_and_view_form(): void
    {
        $response = $this->actingAs($this->managerUser)->get(route('career-transitions.create'));
        $response->assertStatus(200);
        $response->assertSee('Formulir Mutasi & Promosi');
    }

    public function test_admin_can_submit_promotion_request(): void
    {
        $effectiveDate = Carbon::today()->addDays(14)->format('Y-m-d');
        $skFile = UploadedFile::fake()->create('sk_promosi.pdf', 300, 'application/pdf');

        $response = $this->actingAs($this->hrAdmin)->post(route('career-transitions.store'), [
            'employee_id' => $this->employee->id,
            'type' => 'promotion',
            'to_branch_id' => $this->branchJakarta->id,
            'to_department_id' => $this->deptEngineering->id,
            'to_position_id' => $this->posSeniorDev->id,
            'to_manager_id' => $this->managerEmployee->id,
            'to_salary' => 9500000,
            'to_currency' => 'IDR',
            'request_date' => Carbon::today()->format('Y-m-d'),
            'effective_date' => $effectiveDate,
            'letter_number' => 'SK/DIR/2026/XI-001',
            'reason' => 'Peningkatan performa luar biasa dan pencapaian target proyek.',
            'notes' => 'Bertanggung jawab atas arsitektur modul enterprise.',
            'attachment' => $skFile,
        ]);

        $transition = EmployeeCareerTransition::where('employee_id', $this->employee->id)->first();
        $this->assertNotNull($transition);
        $response->assertRedirect(route('career-transitions.show', $transition));
        $response->assertSessionHas('success');

        $this->assertEquals('promotion', $transition->type);
        $this->assertEquals('pending', $transition->status);
        $this->assertEquals(6000000, (float)$transition->from_salary);
        $this->assertEquals(9500000, (float)$transition->to_salary);
        $this->assertEquals(3500000, (float)$transition->salary_diff);
        $this->assertNotNull($transition->attachment_path);
        Storage::disk('public')->assertExists($transition->attachment_path);
    }

    public function test_hr_or_manager_can_approve_transition(): void
    {
        $transition = EmployeeCareerTransition::create([
            'transition_number' => 'TRN-202611-0001',
            'employee_id' => $this->employee->id,
            'type' => 'promotion',
            'from_branch_id' => $this->branchJakarta->id,
            'to_branch_id' => $this->branchJakarta->id,
            'from_position_id' => $this->posJuniorDev->id,
            'to_position_id' => $this->posSeniorDev->id,
            'from_salary' => 6000000,
            'to_salary' => 9000000,
            'request_date' => Carbon::today()->format('Y-m-d'),
            'effective_date' => Carbon::today()->addDays(7)->format('Y-m-d'),
            'reason' => 'Promosi jabatan.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->managerUser)->post(route('career-transitions.approve', $transition), [
            'approval_notes' => 'Sangat direkomendasikan untuk promosi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transition->refresh();
        $this->assertEquals('approved', $transition->status);
        $this->assertEquals($this->managerUser->id, $transition->approved_by);
        $this->assertNotNull($transition->approved_at);
    }

    public function test_hr_or_manager_can_reject_transition(): void
    {
        $transition = EmployeeCareerTransition::create([
            'transition_number' => 'TRN-202611-0002',
            'employee_id' => $this->employee->id,
            'type' => 'transfer',
            'from_branch_id' => $this->branchJakarta->id,
            'to_branch_id' => $this->branchRiyadh->id,
            'request_date' => Carbon::today()->format('Y-m-d'),
            'effective_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'reason' => 'Mutasi cabang.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->hrAdmin)->post(route('career-transitions.reject', $transition), [
            'rejection_reason' => 'Alokasi kebutuhan cabang Riyadh telah terpenuhi.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transition->refresh();
        $this->assertEquals('rejected', $transition->status);
        $this->assertNotNull($transition->rejection_reason);
    }

    public function test_apply_transition_updates_employee_master_data_and_compensation(): void
    {
        $transition = EmployeeCareerTransition::create([
            'transition_number' => 'TRN-202611-0003',
            'employee_id' => $this->employee->id,
            'type' => 'promotion',
            'from_branch_id' => $this->branchJakarta->id,
            'to_branch_id' => $this->branchRiyadh->id,
            'from_department_id' => $this->deptEngineering->id,
            'to_department_id' => $this->deptOperations->id,
            'from_position_id' => $this->posJuniorDev->id,
            'to_position_id' => $this->posSeniorDev->id,
            'from_salary' => 6000000,
            'to_salary' => 12000000,
            'to_currency' => 'SAR',
            'request_date' => Carbon::today()->subDays(5)->format('Y-m-d'),
            'effective_date' => Carbon::today()->format('Y-m-d'),
            'reason' => 'Penugasan regional.',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('career-transitions.apply', $transition), [
            'execution_notes' => 'Telah diverifikasi dan diaktifkan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transition->refresh();
        $this->assertEquals('applied', $transition->status);
        $this->assertEquals($this->superAdmin->id, $transition->applied_by);
        $this->assertNotNull($transition->applied_at);

        // Verify master employee is updated
        $this->employee->refresh();
        $this->assertEquals($this->branchRiyadh->id, $this->employee->branch_id);
        $this->assertEquals($this->deptOperations->id, $this->employee->department_id);
        $this->assertEquals($this->posSeniorDev->id, $this->employee->position_id);
        $this->assertEquals('SAR', $this->employee->currency);

        // Verify basic salary compensation was updated to 12,000,000
        $updatedComp = $this->employee->compensations()->where('salary_component_id', $this->basicSalaryComponent->id)->first();
        $this->assertNotNull($updatedComp);
        $this->assertEquals(12000000, (float)$updatedComp->amount);
    }

    public function test_employee_snapshot_api_returns_valid_json(): void
    {
        $response = $this->actingAs($this->hrAdmin)->get(route('career-transitions.snapshot', $this->employee));
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->employee->id,
            'name' => $this->employee->full_name,
            'code' => $this->employee->employee_code,
            'branch_id' => $this->branchJakarta->id,
            'department_id' => $this->deptEngineering->id,
            'position_id' => $this->posJuniorDev->id,
            'salary' => 6000000,
        ]);
    }

    public function test_admin_can_submit_branch_transfer_across_countries(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('career-transitions.store'), [
            'employee_id' => $this->employee->id,
            'type' => 'transfer',
            'to_branch_id' => $this->branchRiyadh->id,
            'to_department_id' => $this->deptEngineering->id,
            'to_position_id' => $this->posJuniorDev->id,
            'to_salary' => 8000,
            'to_currency' => 'SAR',
            'request_date' => Carbon::today()->format('Y-m-d'),
            'effective_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
            'letter_number' => 'SK/MUT/SA/2026-001',
            'reason' => 'Relokasi tugas kerja ke Riyadh Regional Office.',
        ]);

        $transition = EmployeeCareerTransition::where('employee_id', $this->employee->id)
            ->where('type', 'transfer')
            ->first();

        $this->assertNotNull($transition);
        $this->assertEquals('transfer', $transition->type);
        $this->assertEquals($this->branchJakarta->id, $transition->from_branch_id);
        $this->assertEquals($this->branchRiyadh->id, $transition->to_branch_id);
        $this->assertEquals('SAR', $transition->to_currency);
    }

    public function test_regular_employee_cannot_create_career_transition(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('career-transitions.create'));
        $response->assertStatus(403);
    }

    public function test_employee_can_view_own_transition(): void
    {
        $transition = EmployeeCareerTransition::create([
            'transition_number' => 'TRN-202611-0005',
            'employee_id' => $this->employee->id,
            'type' => 'promotion',
            'from_branch_id' => $this->branchJakarta->id,
            'to_branch_id' => $this->branchJakarta->id,
            'from_position_id' => $this->posJuniorDev->id,
            'to_position_id' => $this->posSeniorDev->id,
            'request_date' => Carbon::today()->format('Y-m-d'),
            'effective_date' => Carbon::today()->addDays(10)->format('Y-m-d'),
            'reason' => 'Promosi saya.',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->employeeUser)->get(route('career-transitions.show', $transition));
        $response->assertStatus(200);
        $response->assertSee('TRN-202611-0005');
    }

    public function test_export_decree_pdf_indonesian_and_arabic(): void
    {
        $transition = EmployeeCareerTransition::create([
            'transition_number' => 'TRN-202611-0004',
            'letter_number' => 'SK/DIR/2026/XI-999',
            'employee_id' => $this->employee->id,
            'type' => 'promotion',
            'from_branch_id' => $this->branchJakarta->id,
            'to_branch_id' => $this->branchJakarta->id,
            'from_department_id' => $this->deptEngineering->id,
            'to_department_id' => $this->deptEngineering->id,
            'from_position_id' => $this->posJuniorDev->id,
            'to_position_id' => $this->posSeniorDev->id,
            'from_salary' => 6000000,
            'to_salary' => 9000000,
            'request_date' => Carbon::today()->subDays(5)->format('Y-m-d'),
            'effective_date' => Carbon::today()->format('Y-m-d'),
            'reason' => 'Promosi jabatan lead.',
            'status' => 'applied',
        ]);

        // Indonesian PDF
        app()->setLocale('id');
        $responseId = $this->actingAs($this->hrAdmin)->get(route('career-transitions.pdf', $transition));
        $responseId->assertStatus(200);
        $responseId->assertHeader('content-type', 'application/pdf');

        // Saudi Arabic PDF (RTL)
        app()->setLocale('ar');
        $responseAr = $this->actingAs($this->hrAdmin)->get(route('career-transitions.pdf', $transition));
        $responseAr->assertStatus(200);
        $responseAr->assertHeader('content-type', 'application/pdf');
    }
}

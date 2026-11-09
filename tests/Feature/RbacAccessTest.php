<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrAdmin;
    protected User $manager;
    protected User $employee1;
    protected User $employee2;
    protected Employee $empRecord1;
    protected Employee $empRecord2;

    protected function setUp(): void
    {
        parent::setUp();

        $branch = Branch::create([
            'name' => 'HQ Jakarta',
            'code' => 'HQ-01',
            'timezone' => 'Asia/Jakarta',
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $dept1 = Department::create([
            'name' => 'Technology',
            'code' => 'TECH',
            'is_active' => true,
        ]);

        $dept2 = Department::create([
            'name' => 'Finance',
            'code' => 'FIN',
            'is_active' => true,
        ]);

        $pos = Position::create([
            'department_id' => $dept1->id,
            'title' => 'Software Engineer',
            'code' => 'SE',
            'level' => 3,
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin_test@hris.local',
        ]);

        $this->hrAdmin = User::factory()->create([
            'role' => 'hr_admin',
            'email' => 'hr_test@hris.local',
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'email' => 'mgr_test@hris.local',
        ]);

        $mgrEmp = Employee::create([
            'user_id' => $this->manager->id,
            'branch_id' => $branch->id,
            'department_id' => $dept1->id,
            'position_id' => $pos->id,
            'employee_code' => 'MGR001',
            'first_name' => 'Manager',
            'last_name' => 'Tech',
            'email' => $this->manager->email,
            'join_date' => now(),
            'status' => 'active',
            'employment_status' => 'permanent',
        ]);

        $this->employee1 = User::factory()->create([
            'role' => 'employee',
            'email' => 'emp1_test@hris.local',
        ]);

        $this->empRecord1 = Employee::create([
            'user_id' => $this->employee1->id,
            'branch_id' => $branch->id,
            'department_id' => $dept1->id,
            'position_id' => $pos->id,
            'employee_code' => 'EMP001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => $this->employee1->email,
            'join_date' => now(),
            'status' => 'active',
            'employment_status' => 'permanent',
        ]);

        $this->employee2 = User::factory()->create([
            'role' => 'employee',
            'email' => 'emp2_test@hris.local',
        ]);

        $this->empRecord2 = Employee::create([
            'user_id' => $this->employee2->id,
            'branch_id' => $branch->id,
            'department_id' => $dept2->id,
            'position_id' => $pos->id,
            'employee_code' => 'EMP002',
            'first_name' => 'Dewi',
            'last_name' => 'Lestari',
            'email' => $this->employee2->email,
            'join_date' => now(),
            'status' => 'active',
            'employment_status' => 'permanent',
        ]);
    }

    public function test_employee_cannot_access_payroll_management(): void
    {
        $response = $this->actingAs($this->employee1)->get('/payroll');
        $response->assertStatus(403);
    }

    public function test_employee_cannot_access_biometric_devices(): void
    {
        $response = $this->actingAs($this->employee1)->get('/devices');
        $response->assertStatus(403);
    }

    public function test_employee_cannot_access_audit_logs(): void
    {
        $response = $this->actingAs($this->employee1)->get('/audit-logs');
        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_company_payroll_or_audit(): void
    {
        $response1 = $this->actingAs($this->manager)->get('/payroll');
        $response1->assertStatus(403);

        $response2 = $this->actingAs($this->manager)->get('/audit-logs');
        $response2->assertStatus(403);
    }

    public function test_hr_admin_can_access_payroll_and_devices_and_audit(): void
    {
        $responsePayroll = $this->actingAs($this->hrAdmin)->get('/payroll');
        $responsePayroll->assertStatus(200);

        $responseDevices = $this->actingAs($this->hrAdmin)->get('/devices');
        $responseDevices->assertStatus(200);

        $responseAudit = $this->actingAs($this->hrAdmin)->get('/audit-logs');
        $responseAudit->assertStatus(200);

        $responseSettings = $this->actingAs($this->hrAdmin)->get('/pengaturan-sistem');
        $responseSettings->assertStatus(200);
    }

    public function test_super_admin_has_full_access(): void
    {
        $responseAudit = $this->actingAs($this->superAdmin)->get('/audit-logs');
        $responseAudit->assertStatus(200);

        $responsePayroll = $this->actingAs($this->superAdmin)->get('/payroll');
        $responsePayroll->assertStatus(200);
    }

    public function test_employee_cannot_create_or_edit_employees(): void
    {
        $responseCreate = $this->actingAs($this->employee1)->get('/employees/create');
        $responseCreate->assertStatus(403);

        $responseEdit = $this->actingAs($this->employee1)->get("/employees/{$this->empRecord1->id}/edit");
        $responseEdit->assertStatus(403);
    }

    public function test_employee_accessing_employee_list_redirects_to_own_profile(): void
    {
        $response = $this->actingAs($this->employee1)->get('/employees');
        $response->assertRedirect(route('employees.show', $this->empRecord1));
    }

    public function test_employee_cannot_view_other_employee_profile(): void
    {
        $response = $this->actingAs($this->employee1)->get("/employees/{$this->empRecord2->id}");
        $response->assertStatus(403);
    }

    public function test_employee_can_view_own_profile_but_no_compensation_tab(): void
    {
        $response = $this->actingAs($this->employee1)->get("/employees/{$this->empRecord1->id}");
        $response->assertStatus(200);
        // Ensure compensation tab button is not visible
        $response->assertDontSee('Kompensasi & Payroll');
    }

    public function test_employee_can_access_my_payslips(): void
    {
        $response = $this->actingAs($this->employee1)->get(route('payroll.my-slips'));
        $response->assertStatus(200);
    }

    public function test_employee_cannot_view_another_employees_payslip(): void
    {
        $period = PayrollPeriod::create([
            'name' => 'Payroll Test Period',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'cut_off_date' => now()->endOfMonth(),
            'payment_date' => now(),
            'status' => 'locked',
        ]);

        $item2 = PayrollItem::create([
            'payroll_period_id' => $period->id,
            'employee_id' => $this->empRecord2->id,
            'basic_salary' => 10000000,
            'gross_salary' => 10000000,
            'total_deductions' => 0,
            'net_salary' => 10000000,
        ]);

        $slip2 = Payslip::create([
            'payroll_item_id' => $item2->id,
            'slip_number' => 'SLIP-002',
            'generated_at' => now(),
            'file_path' => 'dummy.pdf',
        ]);

        // Employee 1 attempts to view Employee 2's payslip
        $response = $this->actingAs($this->employee1)->get(route('payroll.payslip', $slip2));
        $response->assertStatus(403);
    }
}

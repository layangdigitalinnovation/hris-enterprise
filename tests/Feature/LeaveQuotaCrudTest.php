<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveQuotaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrAdmin;
    protected User $manager;
    protected User $employeeUser;
    protected Employee $employee;

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

        $dept = Department::create([
            'name' => 'Technology',
            'code' => 'TECH',
            'is_active' => true,
        ]);

        $pos = Position::create([
            'department_id' => $dept->id,
            'title' => 'Software Engineer',
            'code' => 'SE',
            'level' => 3,
            'is_active' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@hris.test',
            'role' => 'super_admin',
        ]);

        $this->hrAdmin = User::factory()->create([
            'name' => 'HR Administrator',
            'email' => 'hr@hris.test',
            'role' => 'hr_admin',
        ]);

        $this->manager = User::factory()->create([
            'name' => 'Manager Tech',
            'email' => 'manager@hris.test',
            'role' => 'manager',
        ]);

        $this->employeeUser = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@hris.test',
            'role' => 'employee',
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $branch->id,
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'employee_code' => 'EMP-TEST-001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi@hris.test',
            'gender' => 'male',
            'join_date' => now()->subMonths(6),
            'employment_status' => 'permanent',
            'basic_salary' => 10000000,
            'status' => 'active',
        ]);
    }

    public function test_super_admin_and_hr_admin_can_create_leave_type_with_quota(): void
    {
        $response = $this->actingAs($this->hrAdmin)->post('/leaves/types', [
            'code' => 'CB',
            'name' => 'Cuti Bersama Hari Raya',
            'default_quota' => 5,
            'max_consecutive_days' => 7,
            'is_paid' => 1,
            'requires_document' => 0,
            'gender_restriction' => 'all',
            'is_active' => 1,
            'auto_allocate' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_types', [
            'code' => 'CB',
            'name' => 'Cuti Bersama Hari Raya',
            'default_quota' => 5,
            'is_paid' => 1,
            'is_active' => 1,
        ]);

        // Auto allocate should have created a balance record for active employee
        $this->assertDatabaseHas('leave_balances', [
            'employee_id' => $this->employee->id,
            'initial_quota' => 5,
            'remaining_quota' => 5,
        ]);
    }

    public function test_hr_admin_can_update_leave_type_and_sync_balances(): void
    {
        $type = LeaveType::create([
            'code' => 'CH',
            'name' => 'Cuti Khusus',
            'default_quota' => 3,
            'max_consecutive_days' => 3,
            'is_paid' => true,
            'requires_document' => false,
            'gender_restriction' => 'all',
            'is_active' => true,
        ]);

        $bal = LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $type->id,
            'year' => (int)date('Y'),
            'initial_quota' => 3,
            'used_quota' => 1,
            'remaining_quota' => 2,
        ]);

        // Update quota from 3 to 6 with balance sync enabled
        $response = $this->actingAs($this->superAdmin)->put("/leaves/types/{$type->id}", [
            'code' => 'CH',
            'name' => 'Cuti Khusus Diperpanjang',
            'default_quota' => 6,
            'max_consecutive_days' => 5,
            'is_paid' => 1,
            'requires_document' => 1,
            'gender_restriction' => 'all',
            'is_active' => 1,
            'sync_existing_balances' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('leave_types', [
            'id' => $type->id,
            'name' => 'Cuti Khusus Diperpanjang',
            'default_quota' => 6,
            'requires_document' => 1,
        ]);

        $bal->refresh();
        $this->assertEquals(6, $bal->initial_quota);
        $this->assertEquals(1, $bal->used_quota);
        $this->assertEquals(5, $bal->remaining_quota); // 6 - 1 = 5
    }

    public function test_regular_employee_cannot_manage_leave_types(): void
    {
        $response = $this->actingAs($this->employeeUser)->post('/leaves/types', [
            'code' => 'HACK',
            'name' => 'Cuti Ilegal',
            'default_quota' => 100,
            'max_consecutive_days' => 30,
            'is_paid' => 1,
            'requires_document' => 0,
            'gender_restriction' => 'all',
            'is_active' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_manager_cannot_manage_leave_types(): void
    {
        $response = $this->actingAs($this->manager)->post('/leaves/types', [
            'code' => 'MGR_LEAVE',
            'name' => 'Cuti Khusus Manager',
            'default_quota' => 20,
            'max_consecutive_days' => 20,
            'is_paid' => 1,
            'requires_document' => 0,
            'gender_restriction' => 'all',
            'is_active' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_delete_leave_type_with_existing_leave_requests(): void
    {
        $type = LeaveType::create([
            'code' => 'CT',
            'name' => 'Cuti Tahunan',
            'default_quota' => 12,
            'max_consecutive_days' => 14,
            'is_paid' => true,
            'requires_document' => false,
            'gender_restriction' => 'all',
            'is_active' => true,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-TEST-001',
            'employee_id' => $this->employee->id,
            'leave_type_id' => $type->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
            'total_days' => 2,
            'reason' => 'Testing leave request',
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->hrAdmin)->delete("/leaves/types/{$type->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('leave_types', [
            'id' => $type->id,
            'code' => 'CT',
        ]);
    }

    public function test_can_delete_unused_leave_type(): void
    {
        $type = LeaveType::create([
            'code' => 'UNUSED',
            'name' => 'Cuti Cadangan',
            'default_quota' => 1,
            'max_consecutive_days' => 1,
            'is_paid' => false,
            'requires_document' => false,
            'gender_restriction' => 'all',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->hrAdmin)->delete("/leaves/types/{$type->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('leave_types', [
            'id' => $type->id,
        ]);
    }

    public function test_hr_admin_can_adjust_employee_leave_balance(): void
    {
        $type = LeaveType::create([
            'code' => 'CT',
            'name' => 'Cuti Tahunan',
            'default_quota' => 12,
            'max_consecutive_days' => 14,
            'is_paid' => true,
            'requires_document' => false,
            'gender_restriction' => 'all',
            'is_active' => true,
        ]);

        $bal = LeaveBalance::create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $type->id,
            'year' => (int)date('Y'),
            'initial_quota' => 12,
            'used_quota' => 4,
            'remaining_quota' => 8,
        ]);

        $response = $this->actingAs($this->hrAdmin)->put("/leaves/balances/{$bal->id}", [
            'initial_quota' => 15,
            'used_quota' => 4,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $bal->refresh();
        $this->assertEquals(15, $bal->initial_quota);
        $this->assertEquals(4, $bal->used_quota);
        $this->assertEquals(11, $bal->remaining_quota); // 15 - 4 = 11
    }
}

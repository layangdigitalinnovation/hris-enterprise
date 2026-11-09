<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\ReimbursementRequest;
use App\Models\ReimbursementCategory;
use App\Models\EmployeeResignation;
use App\Models\EmployeeCareerTransition;
use App\Services\ApprovalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApprovalNotificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employeeUser;
    private Branch $branch;
    private Department $dept;
    private Position $pos;
    private Employee $emp1;
    private Employee $managerEmp;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Kantor Pusat Jakarta',
            'code' => 'HQ-JKT',
            'city' => 'Jakarta',
            'country' => 'Indonesia',
            'timezone' => 'Asia/Jakarta',
            'currency' => 'IDR',
        ]);

        $this->dept = Department::create([
            'name' => 'Teknologi Informasi',
            'code' => 'IT',
        ]);

        $this->pos = Position::create([
            'title' => 'Software Engineer',
            'code' => 'SE',
            'department_id' => $this->dept->id,
        ]);

        // Admin
        $this->admin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'admin@test.com',
        ]);

        // Manager
        $this->manager = User::factory()->create([
            'role' => 'manager',
            'email' => 'manager@test.com',
        ]);

        $this->managerEmp = Employee::create([
            'user_id' => $this->manager->id,
            'branch_id' => $this->branch->id,
            'department_id' => $this->dept->id,
            'position_id' => $this->pos->id,
            'employee_code' => 'EMP-MGR',
            'first_name' => 'Manager',
            'last_name' => 'Boss',
            'email' => 'manager.emp@test.com',
            'join_date' => '2022-01-01',
            'employment_status' => 'permanent',
            'status' => 'active',
            'currency' => 'IDR',
        ]);

        // Regular Employee
        $this->employeeUser = User::factory()->create([
            'role' => 'employee',
            'email' => 'emp@test.com',
        ]);

        $this->emp1 = Employee::create([
            'user_id' => $this->employeeUser->id,
            'branch_id' => $this->branch->id,
            'department_id' => $this->dept->id,
            'position_id' => $this->pos->id,
            'manager_id' => $this->managerEmp->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@test.com',
            'join_date' => '2023-01-01',
            'employment_status' => 'permanent',
            'status' => 'active',
            'currency' => 'IDR',
        ]);
    }

    public function test_guest_cannot_access_approval_notifications(): void
    {
        $response = $this->getJson(route('notifications.approvals'));
        $response->assertUnauthorized();
    }

    public function test_super_admin_can_view_all_pending_approvals(): void
    {
        // 1. Create a pending leave
        $leaveType = LeaveType::create([
            'name' => 'Cuti Tahunan',
            'code' => 'ANNUAL',
            'days_allowed' => 12,
            'is_paid' => true,
            'is_active' => true,
        ]);

        LeaveRequest::create([
            'request_number' => 'LV-001',
            'employee_id' => $this->emp1->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(2),
            'end_date' => now()->addDays(3),
            'total_days' => 2,
            'reason' => 'Family event',
            'status' => 'submitted',
        ]);

        // 2. Create a pending resignation
        EmployeeResignation::create([
            'resignation_number' => 'RSG-001',
            'employee_id' => $this->emp1->id,
            'submission_date' => now()->format('Y-m-d'),
            'requested_last_day' => now()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'career_growth',
            'reason_detail' => 'Karir Lebih Baik',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('notifications.approvals'));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_count', 2)
            ->assertJsonPath('data.counts.leave', 1)
            ->assertJsonPath('data.counts.resignation', 1);
    }

    public function test_manager_sees_scoped_subordinate_approvals(): void
    {
        $cat = ReimbursementCategory::create([
            'name' => 'Transport',
            'code' => 'TRP',
            'is_active' => true,
        ]);

        ReimbursementRequest::create([
            'claim_number' => 'CLM-001',
            'employee_id' => $this->emp1->id,
            'category_id' => $cat->id,
            'claim_date' => now()->format('Y-m-d'),
            'expense_date' => now()->format('Y-m-d'),
            'title' => 'Grab ke klien',
            'amount' => 50000,
            'currency' => 'IDR',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->manager)->getJson(route('notifications.approvals'));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_count', 1)
            ->assertJsonPath('data.counts.reimbursement', 1);
    }

    public function test_employee_can_access_notifications_endpoint(): void
    {
        $response = $this->actingAs($this->employeeUser)->getJson(route('notifications.approvals'));

        $response->assertOk()
            ->assertJsonPath('status', 'success');
    }

    public function test_notification_service_handles_arabic_locale(): void
    {
        app()->setLocale('ar');

        $dept = \App\Models\Department::firstOrCreate(
            ['name' => 'Teknologi Informasi & Sistem'],
            ['code' => 'TIS', 'branch_id' => $this->branch->id]
        );
        $pos = \App\Models\Position::firstOrCreate(
            ['title' => 'IT Manager'],
            ['code' => 'ITM', 'department_id' => $dept->id]
        );
        $this->emp1->update(['department_id' => $dept->id]);

        EmployeeResignation::create([
            'resignation_number' => 'RSG-002',
            'employee_id' => $this->emp1->id,
            'submission_date' => now()->format('Y-m-d'),
            'requested_last_day' => now()->addDays(30)->format('Y-m-d'),
            'reason_type' => 'personal',
            'reason_detail' => 'Personal reason',
            'status' => 'pending',
        ]);

        \App\Models\EmployeeCareerTransition::create([
            'transition_number' => 'TRN-001',
            'employee_id' => $this->emp1->id,
            'type' => 'promotion',
            'request_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'to_position_id' => $pos->id,
            'to_department_id' => $dept->id,
            'reason' => 'Promotion reason',
            'status' => 'pending',
        ]);

        $service = app(ApprovalNotificationService::class);
        $summary = $service->getSummary($this->admin);

        $this->assertEquals(2, $summary['total_count']);

        $resignationItem = collect($summary['items'])->firstWhere('category', 'resignation');
        $this->assertNotNull($resignationItem);
        $this->assertEquals('الاستقالات وإخلاء الطرف', $resignationItem['category_label']);
        $this->assertEquals('استقالة', $resignationItem['category_badge']);

        $transitionItem = collect($summary['items'])->firstWhere('category', 'transition');
        $this->assertNotNull($transitionItem);
        $this->assertEquals('الترقية والنقل الوظيفي', $transitionItem['category_label']);
        $this->assertEquals('ترقية', $transitionItem['category_badge']);
        $this->assertEquals('تكنولوجيا المعلومات والأنظمة', $transitionItem['employee_department']);
        $this->assertStringContainsString('مدير تكنولوجيا المعلومات', $transitionItem['description']);
    }

    public function test_notifications_endpoint_returns_arabic_translations(): void
    {
        $dept = \App\Models\Department::firstOrCreate(
            ['name' => 'Teknologi Informasi & Sistem'],
            ['code' => 'TIS2', 'branch_id' => $this->branch->id]
        );
        $pos = \App\Models\Position::firstOrCreate(
            ['title' => 'IT Manager'],
            ['code' => 'ITM2', 'department_id' => $dept->id]
        );
        $this->emp1->update(['department_id' => $dept->id]);

        \App\Models\EmployeeCareerTransition::create([
            'transition_number' => 'TRN-002',
            'employee_id' => $this->emp1->id,
            'type' => 'promotion',
            'request_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'to_position_id' => $pos->id,
            'to_department_id' => $dept->id,
            'reason' => 'Promotion reason',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->withSession(['locale' => 'ar'])
            ->getJson(route('notifications.approvals'));

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.items.0.category_badge', 'ترقية')
            ->assertJsonPath('data.items.0.employee_department', 'تكنولوجيا المعلومات والأنظمة');
    }
}

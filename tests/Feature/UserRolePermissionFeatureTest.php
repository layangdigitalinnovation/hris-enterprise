<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolePermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $hrAdmin;
    protected User $employeeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'email' => 'superadmin@test.com',
        ]);

        $this->hrAdmin = User::factory()->create([
            'role' => 'hr_admin',
            'email' => 'hradmin@test.com',
        ]);

        $this->employeeUser = User::factory()->create([
            'role' => 'employee',
            'email' => 'empuser@test.com',
        ]);
    }

    public function test_admin_can_access_users_roles_permissions_pages(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('users.index'))
            ->assertStatus(200)
            ->assertSee('Manajemen User & Hak Akses');

        $this->actingAs($this->superAdmin)
            ->get(route('roles.index'))
            ->assertStatus(200)
            ->assertSee('Manajemen Role & Peran');

        $this->actingAs($this->superAdmin)
            ->get(route('permissions.index'))
            ->assertStatus(200)
            ->assertSee('Manajemen Permission');

        $this->actingAs($this->superAdmin)
            ->get(route('group-permissions.index'))
            ->assertStatus(200)
            ->assertSee('Group Permission');

        // Test Dedicated Create Form Pages
        $this->actingAs($this->superAdmin)
            ->get(route('users.create'))
            ->assertStatus(200)
            ->assertSee('Tambah User Pengguna Baru');

        $this->actingAs($this->superAdmin)
            ->get(route('roles.create'))
            ->assertStatus(200)
            ->assertSee('Tambah Role & Peran Baru');

        $this->actingAs($this->superAdmin)
            ->get(route('permissions.create'))
            ->assertStatus(200)
            ->assertSee('Tambah Permission Baru');

        $this->actingAs($this->superAdmin)
            ->get(route('group-permissions.create'))
            ->assertStatus(200)
            ->assertSee('Tambah Group Permission Baru');
    }

    public function test_regular_employee_is_forbidden_from_users_management(): void
    {
        $this->actingAs($this->employeeUser)
            ->get(route('users.index'))
            ->assertStatus(403);

        $this->actingAs($this->employeeUser)
            ->get(route('roles.index'))
            ->assertStatus(403);
    }

    public function test_can_create_and_update_user_with_role_and_scopes(): void
    {
        $branch = Branch::create(['name' => 'Cabang Riyadh', 'code' => 'RYD', 'country' => 'SA', 'currency' => 'SAR', 'timezone' => 'Asia/Riyadh', 'is_active' => true]);
        $dept = Department::create(['name' => 'Teknologi Informasi', 'code' => 'IT', 'is_active' => true]);
        $role = Role::where('key', 'manager')->first();

        $response = $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Farhan IT Head',
                'username' => 'farhan_it',
                'email' => 'farhan@test.com',
                'password' => 'secret123',
                'role_id' => $role->id,
                'phone' => '+628123456789',
                'all_branches' => 0,
                'branch_ids' => [$branch->id],
                'all_departments' => 0,
                'department_ids' => [$dept->id],
                'is_active' => 1,
            ]);

        $response->assertRedirect(route('users.index', ['tab' => 'admin']));

        $newUser = User::where('email', 'farhan@test.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('farhan_it', $newUser->username);
        $this->assertEquals('manager', $newUser->role);
        $this->assertFalse($newUser->all_branches);
        $this->assertTrue($newUser->canAccessBranch($branch->id));
        $this->assertTrue($newUser->canAccessDepartment($dept->id));

        // Visit dedicated user edit page
        $this->actingAs($this->superAdmin)
            ->get(route('users.edit', $newUser))
            ->assertStatus(200)
            ->assertSee($newUser->name);

        // Update user
        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $newUser), [
                'name' => 'Farhan Updated',
                'username' => 'farhan_new',
                'email' => 'farhan@test.com',
                'role_id' => $role->id,
                'all_branches' => 1,
                'all_departments' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $newUser->refresh();
        $this->assertEquals('Farhan Updated', $newUser->name);
        $this->assertTrue($newUser->all_branches);
    }

    public function test_cannot_delete_self_or_primary_superadmin(): void
    {
        $this->actingAs($this->superAdmin)
            ->delete(route('users.destroy', $this->superAdmin))
            ->assertSessionHas('error');

        $otherUser = User::factory()->create(['role' => 'employee', 'email' => 'other@test.com']);
        $this->actingAs($this->superAdmin)
            ->delete(route('users.destroy', $otherUser))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);
    }

    public function test_can_link_and_unlink_employee_in_user_form(): void
    {
        $branch = Branch::create(['name' => 'HQ Jakarta', 'code' => 'HQ-JKT', 'is_active' => true]);
        $dept = Department::create(['name' => 'IT Systems', 'code' => 'ITS', 'is_active' => true]);
        $pos = Position::create(['title' => 'Software Engineer', 'code' => 'SWE', 'department_id' => $dept->id, 'is_active' => true]);
        $role = Role::where('key', 'employee')->first() ?? Role::first();

        $employee = Employee::create([
            'employee_code' => 'EMP-TEST-99',
            'first_name' => 'Karyawan',
            'last_name' => 'Percobaan',
            'email' => 'karyawan_test99@test.com',
            'gender' => 'male',
            'birth_date' => '1995-01-01',
            'join_date' => '2025-01-01',
            'branch_id' => $branch->id,
            'department_id' => $dept->id,
            'position_id' => $pos->id,
            'employment_status' => 'permanent',
            'status' => 'active',
        ]);

        // 1. Check create form lists the active employee in dropdown
        $this->actingAs($this->superAdmin)
            ->get(route('users.create'))
            ->assertStatus(200)
            ->assertSee('EMP-TEST-99')
            ->assertSee('Karyawan Percobaan');

        // 2. Store new user linked to employee
        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Karyawan Percobaan',
                'username' => 'karyawan99',
                'email' => 'karyawan99@test.com',
                'password' => 'secret123',
                'role_id' => $role->id,
                'employee_id' => $employee->id,
                'all_branches' => 1,
                'all_departments' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $user = User::where('email', 'karyawan99@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($user->id, $employee->fresh()->user_id);

        // 3. Edit form lists and selects the linked employee
        $this->actingAs($this->superAdmin)
            ->get(route('users.edit', $user))
            ->assertStatus(200)
            ->assertSee('EMP-TEST-99')
            ->assertSee('selected');

        // 4. Update user to unlink employee
        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $user), [
                'name' => 'Karyawan Percobaan',
                'username' => 'karyawan99',
                'email' => 'karyawan99@test.com',
                'role_id' => $role->id,
                'employee_id' => '',
                'all_branches' => 1,
                'all_departments' => 1,
                'is_active' => 1,
            ])
            ->assertRedirect();

        $this->assertNull($employee->fresh()->user_id);
    }

    public function test_can_manage_role_and_permissions_matrix(): void
    {
        // 1. Create custom role
        $this->actingAs($this->superAdmin)
            ->post(route('roles.store'), [
                'name' => 'Compliance Officer',
                'key' => 'compliance_officer',
                'description' => 'Mengawasi regulasi dan audit',
            ])
            ->assertRedirect(route('roles.index'));

        $role = Role::where('key', 'compliance_officer')->first();
        $this->assertNotNull($role);
        $this->assertFalse($role->is_system);

        // Visit dedicated role edit page
        $this->actingAs($this->superAdmin)
            ->get(route('roles.edit', $role))
            ->assertStatus(200)
            ->assertSee($role->name);

        // 2. Open permissions matrix page
        $this->actingAs($this->superAdmin)
            ->get(route('roles.permissions', $role))
            ->assertStatus(200)
            ->assertSee($role->name);

        // 3. Update permissions matrix
        $p1 = Permission::where('name', 'karyawan.index')->first();
        $p2 = Permission::where('name', 'audit.index')->first();

        $this->actingAs($this->superAdmin)
            ->put(route('roles.permissions.update', $role), [
                'permission_ids' => [$p1->id, $p2->id],
            ])
            ->assertRedirect(route('roles.index'));

        $this->assertTrue($role->hasPermissionTo('karyawan.index'));
        $this->assertTrue($role->hasPermissionTo('audit.index'));
        $this->assertFalse($role->hasPermissionTo('karyawan.delete'));

        // 4. Cannot delete system role
        $systemRole = Role::where('key', 'super_admin')->first();
        $this->actingAs($this->superAdmin)
            ->delete(route('roles.destroy', $systemRole))
            ->assertSessionHas('error');
    }

    public function test_can_create_permission_and_group_permission(): void
    {
        // 1. Create Group Permission
        $this->actingAs($this->superAdmin)
            ->post(route('group-permissions.store'), [
                'name' => 'Integrasi API External',
                'slug' => 'integrasi-api-external',
                'description' => 'Akses webhook dan API gateway',
            ])
            ->assertRedirect(route('group-permissions.index'));

        $group = PermissionGroup::where('slug', 'integrasi-api-external')->first();
        $this->assertNotNull($group);

        // 2. Create Permission inside group
        $this->actingAs($this->superAdmin)
            ->post(route('permissions.store'), [
                'name' => 'api.webhook.manage',
                'display_name' => 'Kelola Webhook API',
                'permission_group_id' => $group->id,
                'description' => 'Izin mengonfigurasi endpoint webhook',
            ])
            ->assertRedirect(route('permissions.index'));

        $perm = Permission::where('name', 'api.webhook.manage')->first();
        $this->assertNotNull($perm);
        $this->assertEquals($group->id, $perm->permission_group_id);

        // Visit dedicated group permission and permission edit pages
        $this->actingAs($this->superAdmin)
            ->get(route('group-permissions.edit', $group))
            ->assertStatus(200)
            ->assertSee($group->name);

        $this->actingAs($this->superAdmin)
            ->get(route('permissions.edit', $perm))
            ->assertStatus(200)
            ->assertSee($perm->name);
    }

    public function test_arabic_locale_displays_translated_headers(): void
    {
        app()->setLocale('ar');

        // 1. Users Index & Create
        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('users.index'))
            ->assertStatus(200)
            ->assertSee('إدارة المستخدمين والصلاحيات')
            ->assertSee('إجمالي الحسابات')
            ->assertSee('بيانات المستخدم');

        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('users.create'))
            ->assertStatus(200)
            ->assertSee('إضافة مستخدم جديد للنظام')
            ->assertSee('الهوية وبيانات تسجيل الدخول')
            ->assertSee('الاسم الكامل *');

        // 2. Roles Index & Create & Matrix
        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('roles.index'))
            ->assertStatus(200)
            ->assertSee('إدارة الأدوار والرتب')
            ->assertSee('قائمة أدوار ورتب النظام')
            ->assertSee('إجمالي الصلاحيات');

        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('roles.create'))
            ->assertStatus(200)
            ->assertSee('إضافة دور ورتبة جديدة')
            ->assertSee('معلومات وهوية الدور')
            ->assertSee('اسم الدور / الرتبة *');

        $role = Role::first();
        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('roles.permissions', $role))
            ->assertStatus(200)
            ->assertSee('إعدادات الصلاحيات')
            ->assertSee('تحديد الكل في جميع الوحدات');

        // 3. Permissions Index & Create
        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('permissions.index'))
            ->assertStatus(200)
            ->assertSee('إدارة الصلاحيات والأذونات')
            ->assertSee('قائمة أذونات وصلاحيات النظام')
            ->assertSee('اسم الصلاحية');

        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('permissions.create'))
            ->assertStatus(200)
            ->assertSee('إضافة صلاحية جديدة')
            ->assertSee('معلومات الصلاحية / الإذن')
            ->assertSee('اسم الصلاحية (Slug) *');

        // 4. Group Permissions Index & Create
        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('group-permissions.index'))
            ->assertStatus(200)
            ->assertSee('مجموعات الصلاحيات')
            ->assertSee('قائمة مجموعات الصلاحيات')
            ->assertSee('اسم مجموعة الوحدة');

        $this->actingAs($this->superAdmin)
            ->withSession(['locale' => 'ar'])
            ->get(route('group-permissions.create'))
            ->assertStatus(200)
            ->assertSee('إضافة مجموعة صلاحيات جديدة')
            ->assertSee('معلومات مجموعة الوحدة')
            ->assertSee('اسم مجموعة الوحدة *');
    }
}


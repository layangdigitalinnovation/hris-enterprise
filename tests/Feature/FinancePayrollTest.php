<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\Payslip;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePayrollTest extends TestCase
{
    use RefreshDatabase;

    protected User $financeUser;
    protected User $financeManager;
    protected User $regularEmployee;
    protected Employee $employeeRecord;
    protected Department $financeDept;
    protected Branch $branch;
    protected Position $position;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'HQ Tasikmalaya',
            'code' => 'HQ-01',
            'timezone' => 'Asia/Jakarta',
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        $this->financeDept = Department::create([
            'name' => 'Finance & Accounting',
            'code' => 'FIN',
            'is_active' => true,
        ]);

        $techDept = Department::create([
            'name' => 'Teknologi Informasi',
            'code' => 'IT',
            'is_active' => true,
        ]);

        $this->position = Position::create([
            'department_id' => $this->financeDept->id,
            'title' => 'Finance Officer',
            'code' => 'FIN-OFF',
            'level' => 2,
            'is_active' => true,
        ]);

        // 1. Finance User (role: finance)
        $this->financeUser = User::factory()->create([
            'role' => 'finance',
            'email' => 'finance_staff@company.test',
        ]);

        // 2. Finance Manager (role: manager, department: FIN)
        $this->financeManager = User::factory()->create([
            'role' => 'manager',
            'email' => 'finance_mgr@company.test',
        ]);

        $finMgrEmp = Employee::create([
            'user_id' => $this->financeManager->id,
            'branch_id' => $this->branch->id,
            'department_id' => $this->financeDept->id,
            'position_id' => $this->position->id,
            'employee_code' => 'FIN-MGR',
            'first_name' => 'Bambang',
            'last_name' => 'Sudirman',
            'email' => $this->financeManager->email,
            'join_date' => now(),
            'status' => 'active',
            'employment_status' => 'permanent',
        ]);

        // 3. Regular non-finance employee
        $this->regularEmployee = User::factory()->create([
            'role' => 'employee',
            'email' => 'programmer@company.test',
        ]);

        $this->employeeRecord = Employee::create([
            'user_id' => $this->regularEmployee->id,
            'branch_id' => $this->branch->id,
            'department_id' => $techDept->id,
            'position_id' => $this->position->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => $this->regularEmployee->email,
            'join_date' => now(),
            'status' => 'active',
            'employment_status' => 'permanent',
        ]);

        // Default Basic Component
        SalaryComponent::create([
            'name' => 'Gaji Pokok',
            'code' => 'BASIC',
            'type' => 'earning',
            'calculation_mode' => 'fixed',
            'rate_value' => 0,
            'is_taxable' => true,
            'is_active' => true,
            'description' => 'Gaji pokok bulanan karyawan',
        ]);
    }

    public function test_finance_role_can_access_payroll_management(): void
    {
        $response = $this->actingAs($this->financeUser)->get(route('payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Gaji & Kompensasi');
        $response->assertSee('Master Tunjangan & Potongan');
    }

    public function test_finance_department_manager_can_access_payroll_management(): void
    {
        $response = $this->actingAs($this->financeManager)->get(route('payroll.index'));
        $response->assertStatus(200);
        $response->assertSee('Gaji & Kompensasi');
    }

    public function test_regular_employee_is_forbidden_from_payroll_management(): void
    {
        $response = $this->actingAs($this->regularEmployee)->get(route('payroll.index'));
        $response->assertStatus(403);
    }

    public function test_finance_can_input_and_update_employee_salary(): void
    {
        $response = $this->actingAs($this->financeUser)->put(
            route('payroll.compensations.update-salary', $this->employeeRecord),
            [
                'basic_salary' => 8500000,
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $basicComp = SalaryComponent::where('code', 'BASIC')->first();
        $this->assertDatabaseHas('employee_compensations', [
            'employee_id' => $this->employeeRecord->id,
            'salary_component_id' => $basicComp->id,
            'amount' => 8500000,
        ]);
    }

    public function test_finance_can_create_company_allowance(): void
    {
        $response = $this->actingAs($this->financeUser)->post(
            route('payroll.components.store'),
            [
                'name' => 'Tunjangan Komunikasi',
                'code' => 'ALW_COMM',
                'type' => 'earning',
                'calculation_mode' => 'fixed',
                'rate_value' => 250000,
                'is_taxable' => 1,
                'is_active' => 1,
                'description' => 'Paket data dan komunikasi tim',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('salary_components', [
            'code' => 'ALW_COMM',
            'name' => 'Tunjangan Komunikasi',
            'type' => 'earning',
            'rate_value' => 250000,
            'calculation_mode' => 'fixed',
        ]);
    }

    public function test_finance_can_create_company_deduction(): void
    {
        $response = $this->actingAs($this->financeUser)->post(
            route('payroll.components.store'),
            [
                'name' => 'Iuran Koperasi Karyawan',
                'code' => 'DED_KOPERASI',
                'type' => 'deduction',
                'calculation_mode' => 'fixed',
                'rate_value' => 75000,
                'is_active' => 1,
                'description' => 'Iuran simpanan wajib koperasi',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('salary_components', [
            'code' => 'DED_KOPERASI',
            'name' => 'Iuran Koperasi Karyawan',
            'type' => 'deduction',
            'rate_value' => 75000,
        ]);
    }

    public function test_finance_can_update_salary_component_policy(): void
    {
        $component = SalaryComponent::create([
            'name' => 'Tunjangan Kinerja',
            'code' => 'ALW_PERF',
            'type' => 'earning',
            'calculation_mode' => 'percentage',
            'rate_value' => 10,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->financeUser)->put(
            route('payroll.components.update', $component),
            [
                'name' => 'Tunjangan Kinerja Bulanan',
                'type' => 'earning',
                'calculation_mode' => 'percentage',
                'rate_value' => 12.5,
                'is_taxable' => 1,
                'is_active' => 1,
                'description' => 'Dinaikkan menjadi 12.5% dari gaji pokok',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('salary_components', [
            'id' => $component->id,
            'name' => 'Tunjangan Kinerja Bulanan',
            'rate_value' => 12.5,
        ]);
    }

    public function test_payroll_calculation_applies_allowances_and_deductions_correctly(): void
    {
        // 1. Set Basic Salary Rp 10.000.000
        $basicComp = SalaryComponent::where('code', 'BASIC')->first();
        EmployeeCompensation::create([
            'employee_id' => $this->employeeRecord->id,
            'salary_component_id' => $basicComp->id,
            'amount' => 10000000,
            'is_active' => true,
        ]);

        // 2. Set Percentage Allowance (10% = 1.000.000)
        SalaryComponent::create([
            'name' => 'Tunjangan Prestasi',
            'code' => 'ALW_PRES',
            'type' => 'earning',
            'calculation_mode' => 'percentage',
            'rate_value' => 10,
            'is_taxable' => true,
            'is_active' => true,
        ]);

        // 3. Set Fixed Deduction (500.000)
        SalaryComponent::create([
            'name' => 'Kasbon Rutin',
            'code' => 'DED_KASBON',
            'type' => 'deduction',
            'calculation_mode' => 'fixed',
            'rate_value' => 500000,
            'is_active' => true,
        ]);

        // 4. Create Payroll Period
        $period = PayrollPeriod::create([
            'name' => 'Periode Uji Coba',
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'cut_off_date' => now()->startOfMonth()->addDays(24)->toDateString(),
            'payment_date' => now()->startOfMonth()->addDays(27)->toDateString(),
            'status' => 'draft',
        ]);

        // 5. Calculate Payroll
        $response = $this->actingAs($this->financeUser)->post(
            route('payroll.calculate', $period)
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verify PayrollItem:
        // Basic: 10,000,000
        // Allowances: 10% of 10,000,000 = 1,000,000
        // Deductions: 500,000
        // Net: 10,000,000 + 1,000,000 - 500,000 = 10,500,000
        $this->assertDatabaseHas('payroll_items', [
            'payroll_period_id' => $period->id,
            'employee_id' => $this->employeeRecord->id,
            'basic_salary' => 10000000,
            'total_allowances' => 1000000,
            'total_deductions' => 500000,
            'net_salary' => 10500000,
        ]);

        $item = PayrollItem::where('payroll_period_id', $period->id)
            ->where('employee_id', $this->employeeRecord->id)
            ->first();

        $this->assertNotNull($item);
        $this->assertArrayHasKey('allowance_breakdown', $item->details);
        $this->assertArrayHasKey('deduction_breakdown', $item->details);
        $this->assertEquals(1000000, $item->details['allowance_breakdown']['ALW_PRES']['amount']);
        $this->assertEquals(500000, $item->details['deduction_breakdown']['DED_KASBON']['amount']);

        // Check Payslip
        $this->assertDatabaseHas('payslips', [
            'payroll_item_id' => $item->id,
        ]);
    }

    public function test_finance_can_access_dedicated_form_pages(): void
    {
        // 1. Create Period Page
        $resPeriod = $this->actingAs($this->financeUser)->get(route('payroll.periods.create'));
        $resPeriod->assertOk();
        $resPeriod->assertSee('Formulir Periode Penggajian Baru');

        // 2. Create Component Page
        $resComp = $this->actingAs($this->financeUser)->get(route('payroll.components.create'));
        $resComp->assertOk();
        $resComp->assertSee('Formulir Komponen Penggajian Baru');

        // 3. Edit Component Page
        $comp = SalaryComponent::create([
            'code' => 'ALW_MEAL',
            'name' => 'Uang Makan',
            'type' => 'earning',
            'calculation_mode' => 'per_attendance',
            'rate_value' => 25000,
            'is_taxable' => true,
            'is_active' => true,
        ]);
        $resEditComp = $this->actingAs($this->financeUser)->get(route('payroll.components.edit', $comp));
        $resEditComp->assertOk();
        $resEditComp->assertSee('Edit Kebijakan Komponen');
        $resEditComp->assertSee('Uang Makan');

        // 4. Edit Employee Compensation Page
        $resEditSalary = $this->actingAs($this->financeUser)->get(route('payroll.compensations.edit', $this->employeeRecord));
        $resEditSalary->assertOk();
        $resEditSalary->assertSee('Formulir Gaji & Kompensasi Karyawan');
        $resEditSalary->assertSee($this->employeeRecord->full_name);
    }

    public function test_regular_employee_forbidden_from_dedicated_form_pages(): void
    {
        $comp = SalaryComponent::create([
            'code' => 'DED_TEST',
            'name' => 'Potongan Uji',
            'type' => 'deduction',
            'calculation_mode' => 'fixed',
            'rate_value' => 100000,
            'is_taxable' => false,
            'is_active' => true,
        ]);

        $this->actingAs($this->regularEmployee)->get(route('payroll.periods.create'))->assertForbidden();
        $this->actingAs($this->regularEmployee)->get(route('payroll.components.create'))->assertForbidden();
        $this->actingAs($this->regularEmployee)->get(route('payroll.components.edit', $comp))->assertForbidden();
        $this->actingAs($this->regularEmployee)->get(route('payroll.compensations.edit', $this->employeeRecord))->assertForbidden();
    }

    public function test_admin_and_finance_can_access_payroll_templates_catalog_without_blank_screen(): void
    {
        $response = $this->actingAs($this->financeUser)->get(route('payroll.templates.index'));
        $response->assertOk();
        $response->assertSee('Katalog Template Payroll');
        $response->assertSee('activeTab: \'templates\'', false);
        $response->assertSee('Buat Template Baru');

        $createResponse = $this->actingAs($this->financeUser)->get(route('payroll.templates.create'));
        $createResponse->assertOk();
        $createResponse->assertSee(route('payroll.templates.index'));
    }
}

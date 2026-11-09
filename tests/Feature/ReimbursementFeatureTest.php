<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\ReimbursementCategory;
use App\Models\ReimbursementRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReimbursementFeatureTest extends TestCase
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
    protected ReimbursementCategory $category;

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
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'code' => 'OPS-TEST',
            'name' => 'Operasional Test',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);

        $this->position = Position::create([
            'code' => 'STF-TEST',
            'title' => 'Staff Operasional Test',
            'department_id' => $this->department->id,
            'level' => 1,
            'is_active' => true,
        ]);

        $this->employeeUser = User::factory()->create([
            'email' => 'emp_test@example.com',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $this->employee = Employee::create([
            'user_id' => $this->employeeUser->id,
            'employee_code' => 'EMP-TEST-99',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'emp_test@example.com',
            'branch_id' => $this->branch->id,
            'department_id' => $this->department->id,
            'position_id' => $this->position->id,
            'currency' => 'IDR',
            'status' => 'active',
            'join_date' => now()->subYear(),
            'employment_status' => 'permanent',
            'gender' => 'male',
            'bank_name' => 'BCA',
            'bank_account_no' => '5420112233',
            'bank_account_holder' => 'Budi Santoso',
        ]);

        $this->category = ReimbursementCategory::create([
            'code' => 'TRV-TEST',
            'name' => 'Transportasi Test',
            'description' => 'Kategori transportasi dinas uji coba',
            'max_amount' => 5000000,
            'requires_receipt' => true,
            'is_active' => true,
        ]);
    }

    public function test_employee_can_access_reimbursement_index(): void
    {
        $response = $this->actingAs($this->employeeUser)->get(route('reimbursements.index'));
        $response->assertStatus(200);
        $response->assertSee('Klaim & Penggantian Reimbursement', false);
    }

    public function test_employee_can_submit_reimbursement_claim(): void
    {
        $file = UploadedFile::fake()->image('nota_bensin.jpg');

        $response = $this->actingAs($this->employeeUser)->post(route('reimbursements.store'), [
            'category_id' => $this->category->id,
            'title' => 'Beli Bensin Dinas Bandung',
            'description' => 'Perjalanan dinas meeting vendor di Bandung',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'IDR',
            'amount' => 350000,
            'receipt' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursement_requests', [
            'employee_id' => $this->employee->id,
            'title' => 'Beli Bensin Dinas Bandung',
            'amount' => 350000,
            'status' => 'pending',
            'currency' => 'IDR',
        ]);
    }

    public function test_employee_can_edit_and_delete_pending_claim(): void
    {
        $claim = ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-001',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $this->category->id,
            'title' => 'Klaim Awal',
            'claim_date' => now(),
            'currency' => 'IDR',
            'amount' => 100000,
            'receipt_path' => 'reimbursements/receipts/test.jpg',
            'status' => 'pending',
        ]);

        // Edit
        $editRes = $this->actingAs($this->employeeUser)->put(route('reimbursements.update', $claim), [
            'category_id' => $this->category->id,
            'title' => 'Klaim Diperbarui',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'IDR',
            'amount' => 150000,
        ]);
        $editRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursement_requests', [
            'id' => $claim->id,
            'title' => 'Klaim Diperbarui',
            'amount' => 150000,
        ]);

        // Delete
        $delRes = $this->actingAs($this->employeeUser)->delete(route('reimbursements.destroy', $claim));
        $delRes->assertRedirect(route('reimbursements.index'));
        $this->assertSoftDeleted('reimbursement_requests', ['id' => $claim->id]);
    }

    public function test_manager_or_hr_admin_can_approve_claim(): void
    {
        $claim = ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-002',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $this->category->id,
            'title' => 'Klaim Tiket Kereta',
            'claim_date' => now(),
            'currency' => 'IDR',
            'amount' => 500000,
            'status' => 'pending',
        ]);

        // Buka form approve non-modal
        $formRes = $this->actingAs($this->hrAdmin)->get(route('reimbursements.approve.form', $claim));
        $formRes->assertStatus(200);
        $formRes->assertSee('Persetujuan Klaim Reimbursement', false);

        $response = $this->actingAs($this->hrAdmin)->post(route('reimbursements.approve', $claim), [
            'approved_amount' => 450000,
            'notes' => 'Disetujui sebagian karena ada item di luar dinas',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursement_requests', [
            'id' => $claim->id,
            'status' => 'approved',
            'approved_amount' => 450000,
            'approved_by' => $this->hrAdmin->id,
        ]);
    }

    public function test_manager_or_hr_admin_can_reject_claim(): void
    {
        $claim = ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-003',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $this->category->id,
            'title' => 'Klaim Tanpa Nota Valid',
            'claim_date' => now(),
            'currency' => 'IDR',
            'amount' => 200000,
            'status' => 'pending',
        ]);

        // Buka form reject non-modal
        $formRes = $this->actingAs($this->hrAdmin)->get(route('reimbursements.reject.form', $claim));
        $formRes->assertStatus(200);
        $formRes->assertSee('Penolakan Pengajuan Klaim Reimbursement', false);

        $response = $this->actingAs($this->hrAdmin)->post(route('reimbursements.reject', $claim), [
            'rejection_reason' => 'Nota tidak terbaca dan tanggal sudah kadaluarsa',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursement_requests', [
            'id' => $claim->id,
            'status' => 'rejected',
            'rejection_reason' => 'Nota tidak terbaca dan tanggal sudah kadaluarsa',
        ]);
    }

    public function test_finance_can_pay_approved_claim(): void
    {
        $claim = ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-004',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $this->category->id,
            'title' => 'Klaim Siap Bayar',
            'claim_date' => now(),
            'currency' => 'IDR',
            'amount' => 300000,
            'approved_amount' => 300000,
            'status' => 'approved',
            'approved_by' => $this->hrAdmin->id,
            'approved_at' => now(),
        ]);

        $response = $this->actingAs($this->financeUser)->put(route('reimbursements.process-pay', $claim), [
            'payment_method' => 'transfer',
            'payment_reference' => 'TRX-BCA-99881',
            'notes' => 'Transfer via KlikBCA Bisnis',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursement_requests', [
            'id' => $claim->id,
            'status' => 'paid',
            'payment_method' => 'transfer',
            'payment_reference' => 'TRX-BCA-99881',
            'paid_by' => $this->financeUser->id,
        ]);
    }

    public function test_cannot_edit_non_pending_claim(): void
    {
        $claim = ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-005',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $this->category->id,
            'title' => 'Klaim Sudah Approved',
            'claim_date' => now(),
            'currency' => 'IDR',
            'amount' => 300000,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->employeeUser)->put(route('reimbursements.update', $claim), [
            'category_id' => $this->category->id,
            'title' => 'Coba Ubah',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'IDR',
            'amount' => 400000,
        ]);

        $response->assertRedirect(route('reimbursements.show', $claim));
        $this->assertDatabaseHas('reimbursement_requests', [
            'id' => $claim->id,
            'title' => 'Klaim Sudah Approved',
            'amount' => 300000,
        ]);
    }

    public function test_can_export_voucher_pdf_and_summary_pdf(): void
    {
        $claim = ReimbursementRequest::create([
            'claim_number' => 'RMB-TEST-PDF-001',
            'employee_id' => $this->employee->id,
            'branch_id' => $this->branch->id,
            'category_id' => $this->category->id,
            'title' => 'Klaim Cetak PDF',
            'claim_date' => now(),
            'currency' => 'IDR',
            'amount' => 250000,
            'approved_amount' => 250000,
            'status' => 'approved',
        ]);

        $voucherRes = $this->actingAs($this->employeeUser)->get(route('reimbursements.pdf', $claim));
        $voucherRes->assertStatus(200);
        $voucherRes->assertHeader('content-type', 'application/pdf');

        $summaryRes = $this->actingAs($this->hrAdmin)->get(route('reimbursements.report.pdf'));
        $summaryRes->assertStatus(200);
        $summaryRes->assertHeader('content-type', 'application/pdf');
    }

    public function test_hr_admin_can_manage_categories(): void
    {
        // Akses halaman form tambah kategori
        $getCreateRes = $this->actingAs($this->hrAdmin)->get(route('reimbursements.categories.create'));
        $getCreateRes->assertStatus(200);
        $getCreateRes->assertSee('Tambah Kategori Reimbursement Baru', false);

        // Simpan kategori baru
        $createRes = $this->actingAs($this->hrAdmin)->post(route('reimbursements.categories.store'), [
            'name' => 'Kategori Baru Test',
            'code' => 'NEW-TEST',
            'description' => 'Deskripsi Kategori Baru',
            'currency' => 'ALL',
            'max_amount' => 1000000,
            'max_amount_sar' => 250,
            'requires_receipt' => 1,
            'is_active' => 1,
        ]);

        $createRes->assertRedirect(route('reimbursements.categories.index'));
        $this->assertDatabaseHas('reimbursement_categories', [
            'code' => 'NEW-TEST',
            'name' => 'Kategori Baru Test',
            'currency' => 'ALL',
            'max_amount' => 1000000,
            'max_amount_sar' => 250,
        ]);

        $createdCategory = ReimbursementCategory::where('code', 'NEW-TEST')->first();

        // Akses halaman form edit kategori
        $getEditRes = $this->actingAs($this->hrAdmin)->get(route('reimbursements.categories.edit', $createdCategory));
        $getEditRes->assertStatus(200);
        $getEditRes->assertSee('Perbarui Kategori: ' . $createdCategory->name, false);

        // Perbarui kategori
        $updateRes = $this->actingAs($this->hrAdmin)->put(route('reimbursements.categories.update', $createdCategory), [
            'name' => 'Kategori Diperbarui',
            'code' => 'NEW-TEST',
            'currency' => 'SAR',
            'max_amount_sar' => 750,
            'requires_receipt' => 0,
            'is_active' => 1,
        ]);
        $updateRes->assertRedirect(route('reimbursements.categories.index'));
        $this->assertDatabaseHas('reimbursement_categories', [
            'id' => $createdCategory->id,
            'name' => 'Kategori Diperbarui',
            'currency' => 'SAR',
            'max_amount_sar' => 750,
            'requires_receipt' => 0,
        ]);
    }

    public function test_claim_submission_validates_category_currency_compatibility_and_sar_limits(): void
    {
        $idrCategory = ReimbursementCategory::create([
            'code' => 'IDR-ONLY',
            'name' => 'Khusus IDR Only',
            'currency' => 'IDR',
            'max_amount' => 2000000,
            'requires_receipt' => false,
            'is_active' => true,
        ]);

        $sarCategory = ReimbursementCategory::create([
            'code' => 'SAR-CAT',
            'name' => 'Kategori Riyal SAR',
            'currency' => 'SAR',
            'max_amount_sar' => 500,
            'requires_receipt' => false,
            'is_active' => true,
        ]);

        // Coba ajukan SAR pada kategori khusus IDR -> Harus Ditolak
        $failCurrRes = $this->actingAs($this->employeeUser)->post(route('reimbursements.store'), [
            'category_id' => $idrCategory->id,
            'title' => 'Coba Klaim SAR pada Kategori IDR',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'SAR',
            'amount' => 100,
        ]);
        $failCurrRes->assertSessionHasErrors(['category_id']);

        // Coba ajukan nominal melebihi plafon SAR -> Harus Ditolak
        $failLimitRes = $this->actingAs($this->employeeUser)->post(route('reimbursements.store'), [
            'category_id' => $sarCategory->id,
            'title' => 'Klaim SAR Melebihi Plafon',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'SAR',
            'amount' => 750, // Plafon 500
        ]);
        $failLimitRes->assertSessionHasErrors(['amount']);

        // Ajukan SAR sesuai plafon -> Berhasil
        $successRes = $this->actingAs($this->employeeUser)->post(route('reimbursements.store'), [
            'category_id' => $sarCategory->id,
            'title' => 'Klaim SAR Sesuai Plafon',
            'claim_date' => now()->format('Y-m-d'),
            'currency' => 'SAR',
            'amount' => 450,
        ]);
        $successRes->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reimbursement_requests', [
            'category_id' => $sarCategory->id,
            'currency' => 'SAR',
            'amount' => 450,
            'status' => 'pending',
        ]);
    }
}

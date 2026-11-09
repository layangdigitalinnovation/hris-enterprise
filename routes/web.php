<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareerTransitionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ManualBookController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\OvertimeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\PayrollTemplateController;
use App\Http\Controllers\ReimbursementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResignationController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PermissionGroupController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

// Multi-language switcher route (accessible by guests and authenticated users)
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])->name('lang.switch');

// Guest Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes (All logged-in users)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard & Real-Time GPS Tracking Map
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/gps-map-data', [DashboardController::class, 'gpsMapData'])->name('dashboard.gps_map_data');

    // Manual Book & Panduan Operasional Sistem (Accessible by all logged-in users)
    Route::get('/manual-book', [ManualBookController::class, 'index'])->name('manual-book.index');
    Route::get('/panduan', [ManualBookController::class, 'index'])->name('manual-book.panduan');
    Route::get('/manual-book/pdf', [ManualBookController::class, 'downloadPdf'])->name('manual-book.pdf');
    Route::get('/panduan/pdf', [ManualBookController::class, 'downloadPdf'])->name('manual-book.panduan_pdf');

    // Real-Time Approval Notifications
    Route::get('/notifications/approvals', [NotificationController::class, 'approvals'])->name('notifications.approvals');

    // Personal Payslip ESS (Employee Self-Service)
    Route::get('/my-payslips', [PayrollController::class, 'myPayslips'])->name('payroll.my-slips');
    Route::get('/payroll/payslip/{payslip}', [PayrollController::class, 'payslip'])->name('payroll.payslip');

    // Employee Self-Service / Scoped Profile
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');

    // ========================================================
    // HR & PAYROLL MANAGEMENT (HR Admin & Super Admin ONLY)
    // ========================================================
    Route::middleware('role:super_admin,hr_admin')->group(function () {
        // Employee Creation & Edit
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/employees/{employee}/reset-password', [EmployeeController::class, 'showResetPasswordForm'])->name('employees.reset-password.form');
        Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');

        // Organization Master Configuration
        Route::get('/organization/branch/create', [OrganizationController::class, 'createBranch'])->name('organization.branch.create');
        Route::post('/organization/branch', [OrganizationController::class, 'storeBranch'])->name('organization.branch.store');
        Route::get('/organization/branch/{branch}/edit', [OrganizationController::class, 'editBranch'])->name('organization.branch.edit');
        Route::put('/organization/branch/{branch}', [OrganizationController::class, 'updateBranch'])->name('organization.branch.update');
        Route::delete('/organization/branch/{branch}', [OrganizationController::class, 'destroyBranch'])->name('organization.branch.destroy');

        Route::get('/organization/department/create', [OrganizationController::class, 'createDepartment'])->name('organization.department.create');
        Route::post('/organization/department', [OrganizationController::class, 'storeDepartment'])->name('organization.department.store');
        Route::get('/organization/department/{department}/edit', [OrganizationController::class, 'editDepartment'])->name('organization.department.edit');
        Route::put('/organization/department/{department}', [OrganizationController::class, 'updateDepartment'])->name('organization.department.update');
        Route::delete('/organization/department/{department}', [OrganizationController::class, 'destroyDepartment'])->name('organization.department.destroy');

        Route::get('/organization/position/create', [OrganizationController::class, 'createPosition'])->name('organization.position.create');
        Route::post('/organization/position', [OrganizationController::class, 'storePosition'])->name('organization.position.store');
        Route::get('/organization/position/{position}/edit', [OrganizationController::class, 'editPosition'])->name('organization.position.edit');
        Route::put('/organization/position/{position}', [OrganizationController::class, 'updatePosition'])->name('organization.position.update');
        Route::delete('/organization/position/{position}', [OrganizationController::class, 'destroyPosition'])->name('organization.position.destroy');

        // Shifts Master Configuration & Assign
        Route::get('/shifts/create', [ShiftController::class, 'create'])->name('shifts.create');
        Route::post('/shifts', [ShiftController::class, 'store'])->name('shifts.store');
        Route::get('/shifts/{shift}/edit', [ShiftController::class, 'edit'])->name('shifts.edit');
        Route::put('/shifts/{shift}', [ShiftController::class, 'update'])->name('shifts.update');
        Route::delete('/shifts/{shift}', [ShiftController::class, 'destroy'])->name('shifts.destroy');
        Route::get('/shifts/assign/create', [ShiftController::class, 'createAssign'])->name('shifts.assign.create');
        Route::post('/shifts/assign', [ShiftController::class, 'assignShift'])->name('shifts.assign');
        Route::get('/shifts/holiday/create', [ShiftController::class, 'createHoliday'])->name('shifts.holiday.create');
        Route::post('/shifts/holiday', [ShiftController::class, 'storeHoliday'])->name('shifts.holiday.store');

        // Biometric Devices & Attendance Recalculation & Raw Logs
        Route::resource('devices', DeviceController::class);
        Route::get('/devices/{device}/test', [DeviceController::class, 'testConnection'])->name('devices.test');
        Route::post('/devices/{device}/sync', [DeviceController::class, 'syncNow'])->name('devices.sync');
        Route::post('/devices/{device}/sync-users', [DeviceController::class, 'syncUsers'])->name('devices.sync_users');
        Route::post('/devices/{device}/clear-users', [DeviceController::class, 'clearDetectedUsers'])->name('devices.clear_users');
        Route::post('/devices/{device}/simulate', [DeviceController::class, 'simulateLog'])->name('devices.simulate');
        Route::post('/devices/import-logs', [DeviceController::class, 'importLog'])->name('devices.import');
        Route::post('/devices/mapping', [DeviceController::class, 'storeMapping'])->name('devices.mapping.store');
        Route::delete('/devices/mapping/{mapping}', [DeviceController::class, 'destroyMapping'])->name('devices.mapping.destroy');
        Route::post('/attendance/recalculate', [AttendanceController::class, 'recalculate'])->name('attendance.recalculate');
        Route::get('/attendance/logs', [DeviceController::class, 'rawLogs'])->name('attendance.logs');

        // Overtime Policy & Master Rates
        Route::post('/overtime/settings', [OvertimeController::class, 'updateSettings'])->name('overtime.settings.update');
        Route::get('/overtime/rates/create', [OvertimeController::class, 'createRate'])->name('overtime.rates.create');
        Route::post('/overtime/rates', [OvertimeController::class, 'storeRate'])->name('overtime.rates.store');
        Route::get('/overtime/rates/{overtimeRate}/edit', [OvertimeController::class, 'editRate'])->name('overtime.rates.edit');
        Route::put('/overtime/rates/{overtimeRate}', [OvertimeController::class, 'updateRate'])->name('overtime.rates.update');
        Route::post('/overtime/rates/{overtimeRate}/set-default', [OvertimeController::class, 'setDefaultRate'])->name('overtime.rates.set-default');
        Route::delete('/overtime/rates/{overtimeRate}', [OvertimeController::class, 'destroyRate'])->name('overtime.rates.destroy');

        // Hak Kuota Cuti & Master Jenis Cuti Management
        Route::get('/leaves/types/create', [LeaveController::class, 'createType'])->name('leaves.types.create');
        Route::post('/leaves/types', [LeaveController::class, 'storeType'])->name('leaves.types.store');
        Route::get('/leaves/types/{leaveType}/edit', [LeaveController::class, 'editType'])->name('leaves.types.edit');
        Route::put('/leaves/types/{leaveType}', [LeaveController::class, 'updateType'])->name('leaves.types.update');
        Route::delete('/leaves/types/{leaveType}', [LeaveController::class, 'destroyType'])->name('leaves.types.destroy');
        Route::get('/leaves/balances/create', [LeaveController::class, 'createBalance'])->name('leaves.balances.create');
        Route::post('/leaves/balances', [LeaveController::class, 'storeBalance'])->name('leaves.balances.store');
        Route::get('/leaves/balances/{leaveBalance}/edit', [LeaveController::class, 'editBalance'])->name('leaves.balances.edit');
        Route::put('/leaves/balances/{leaveBalance}', [LeaveController::class, 'updateBalance'])->name('leaves.balances.update');
        Route::delete('/leaves/balances/{leaveBalance}', [LeaveController::class, 'destroyBalance'])->name('leaves.balances.destroy');
        Route::post('/leaves/balances/sync', [LeaveController::class, 'syncBalances'])->name('leaves.balances.sync');

    });

    // ========================================================
    // PAYROLL, TUNJANGAN & POTONGAN (Finance, HR Admin, Super Admin)
    // ========================================================
    Route::middleware('role:super_admin,hr_admin,finance')->group(function () {
        // Company Payroll Periods & Calculation
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::get('/payroll/exchange-rate', [PayrollController::class, 'editExchangeRate'])->name('payroll.exchange-rate.edit');
        Route::post('/payroll/exchange-rate/update', [PayrollController::class, 'updateExchangeRate'])->name('payroll.exchange-rate.update');
        Route::get('/payroll/periods/create', [PayrollController::class, 'createPeriod'])->name('payroll.periods.create');
        Route::post('/payroll/period', [PayrollController::class, 'storePeriod'])->name('payroll.period.store');
        Route::get('/payroll/period/{payrollPeriod}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('/payroll/period/{payrollPeriod}/export', [PayrollController::class, 'exportExcel'])->name('payroll.export');
        Route::post('/payroll/period/{payrollPeriod}/calculate', [PayrollController::class, 'calculate'])->name('payroll.calculate');
        Route::post('/payroll/period/{payrollPeriod}/lock', [PayrollController::class, 'lockPeriod'])->name('payroll.lock');

        // Master Komponen Tunjangan & Potongan Perusahaan
        Route::get('/payroll/components/create', [PayrollController::class, 'createComponent'])->name('payroll.components.create');
        Route::post('/payroll/components', [PayrollController::class, 'storeComponent'])->name('payroll.components.store');
        Route::get('/payroll/components/{salaryComponent}/edit', [PayrollController::class, 'editComponent'])->name('payroll.components.edit');
        Route::put('/payroll/components/{salaryComponent}', [PayrollController::class, 'updateComponent'])->name('payroll.components.update');
        Route::delete('/payroll/components/{salaryComponent}', [PayrollController::class, 'destroyComponent'])->name('payroll.components.destroy');

        // Input & Manajemen Gaji Pokok & Kompensasi Karyawan
        Route::get('/payroll/compensations/{employee}/edit', [PayrollController::class, 'editEmployeeSalary'])->name('payroll.compensations.edit');
        Route::post('/payroll/compensations', [PayrollController::class, 'storeCompensation'])->name('payroll.compensations.store');
        Route::put('/payroll/compensations/{employee}', [PayrollController::class, 'updateEmployeeSalary'])->name('payroll.compensations.update-salary');

        // Template Payroll Dinamis & Reusable
        Route::get('/payroll/templates', [PayrollTemplateController::class, 'index'])->name('payroll.templates.index');
        Route::get('/payroll/templates/create', [PayrollTemplateController::class, 'create'])->name('payroll.templates.create');
        Route::post('/payroll/templates', [PayrollTemplateController::class, 'store'])->name('payroll.templates.store');
        Route::get('/payroll/templates/{payroll_template}', [PayrollTemplateController::class, 'show'])->name('payroll.templates.show');
        Route::get('/payroll/templates/{payroll_template}/edit', [PayrollTemplateController::class, 'edit'])->name('payroll.templates.edit');
        Route::put('/payroll/templates/{payroll_template}', [PayrollTemplateController::class, 'update'])->name('payroll.templates.update');
        Route::delete('/payroll/templates/{payroll_template}', [PayrollTemplateController::class, 'destroy'])->name('payroll.templates.destroy');
        Route::post('/payroll/templates/{payroll_template}/duplicate', [PayrollTemplateController::class, 'duplicate'])->name('payroll.templates.duplicate');
        Route::post('/payroll/templates/{payroll_template}/apply', [PayrollTemplateController::class, 'apply'])->name('payroll.templates.apply');

        // ========================================================
        // MODUL INVESTOR, MODAL, GAJI KHUSUS & BAGI HASIL (BEP)
        // ========================================================
        Route::get('/investors', [InvestorController::class, 'index'])->name('investors.index');
        Route::get('/investors/create', [InvestorController::class, 'create'])->name('investors.create');
        Route::post('/investors', [InvestorController::class, 'store'])->name('investors.store');
        Route::get('/investors/{investor}', [InvestorController::class, 'show'])->name('investors.show');
        Route::get('/investors/{investor}/edit', [InvestorController::class, 'edit'])->name('investors.edit');
        Route::put('/investors/{investor}', [InvestorController::class, 'update'])->name('investors.update');
        Route::delete('/investors/{investor}', [InvestorController::class, 'destroy'])->name('investors.destroy');

        // Transaksi Distribusi (Dedicated Pages - No Modals)
        Route::get('/investors/{investor}/distributions/create', [InvestorController::class, 'createDistribution'])->name('investors.distributions.create');
        Route::post('/investors/{investor}/distributions', [InvestorController::class, 'storeDistribution'])->name('investors.distributions.store');
        Route::get('/investor-distributions/{distribution}/edit', [InvestorController::class, 'editDistribution'])->name('investors.distributions.edit');
        Route::put('/investor-distributions/{distribution}', [InvestorController::class, 'updateDistribution'])->name('investors.distributions.update');
        Route::get('/investor-distributions/{distribution}/pay', [InvestorController::class, 'payDistributionForm'])->name('investors.distributions.pay');
        Route::put('/investor-distributions/{distribution}/pay', [InvestorController::class, 'processPayment'])->name('investors.distributions.process-pay');
        Route::delete('/investor-distributions/{distribution}', [InvestorController::class, 'destroyDistribution'])->name('investors.distributions.destroy');

        // Laporan Rekapitulasi Eksekutif Investor (PDF)
        Route::get('/investors-report/pdf', [InvestorController::class, 'exportAllPdf'])->name('investors.report.pdf');
    });

    // Laporan PDF Portofolio Investor (Bisa diakses Admin & Investor ybs)
    Route::get('/investors/{investor}/pdf', [InvestorController::class, 'exportPdf'])->name('investors.pdf');

    // Portal Mandiri Portofolio Investor (Self-Service)
    Route::get('/investor-portal', [InvestorController::class, 'portal'])->name('investors.portal');

    // Employee Profile & Documents (Placed after employees/create to prevent route collision)
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees/{employee}/documents', [EmployeeController::class, 'uploadDocument'])->name('employees.documents.upload');
    Route::delete('/employees/{employee}/documents/{document}', [EmployeeController::class, 'deleteDocument'])->name('employees.documents.delete');
    Route::get('/employees/{employee}/documents/{document}/download', [EmployeeController::class, 'downloadDocument'])->name('employees.documents.download');

    // Attendance (Index & Adjustments scoped in Controller)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/gps-clock-in', [AttendanceController::class, 'gpsClockIn'])->name('attendance.gps_clock_in');
    Route::post('/attendance/check-radius', [AttendanceController::class, 'checkRadius'])->name('attendance.check_radius');
    Route::get('/attendance/adjustments', [AttendanceController::class, 'adjustments'])->name('attendance.adjustments');
    Route::get('/attendance/adjustments/create', [AttendanceController::class, 'createAdjustment'])->name('attendance.adjustments.create');
    Route::post('/attendance/adjustments', [AttendanceController::class, 'storeAdjustment'])->name('attendance.adjustments.store');

    // Absen Visit / Kunjungan Lapangan (Bisa berkali-kali, harus checkout)
    Route::get('/attendance/visits', [VisitController::class, 'index'])->name('attendance.visits.index');
    Route::get('/attendance/visits/create', [VisitController::class, 'create'])->name('attendance.visits.create');
    Route::post('/attendance/visits', [VisitController::class, 'store'])->name('attendance.visits.store');
    Route::get('/attendance/visits/{visit}/checkout', [VisitController::class, 'checkoutForm'])->name('attendance.visits.checkout');
    Route::post('/attendance/visits/{visit}/checkout', [VisitController::class, 'checkOut'])->name('attendance.visits.checkout.process');
    Route::post('/attendance/visits/{visit}/cancel', [VisitController::class, 'cancel'])->name('attendance.visits.cancel');

    // Leaves & SPKL (Scoped in Controller)
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime.index');
    Route::get('/overtime/create', [OvertimeController::class, 'create'])->name('overtime.create');
    Route::post('/overtime', [OvertimeController::class, 'store'])->name('overtime.store');

    // Reimbursements (General & ESS, scoped in Controller)
    Route::get('/reimbursements', [ReimbursementController::class, 'index'])->name('reimbursements.index');
    Route::get('/reimbursements/create', [ReimbursementController::class, 'create'])->name('reimbursements.create');
    Route::post('/reimbursements', [ReimbursementController::class, 'store'])->name('reimbursements.store');
    Route::get('/reimbursements-report/pdf', [ReimbursementController::class, 'exportAllPdf'])->name('reimbursements.report.pdf');
    Route::get('/reimbursements/{reimbursement}', [ReimbursementController::class, 'show'])->name('reimbursements.show');
    Route::get('/reimbursements/{reimbursement}/edit', [ReimbursementController::class, 'edit'])->name('reimbursements.edit');
    Route::put('/reimbursements/{reimbursement}', [ReimbursementController::class, 'update'])->name('reimbursements.update');
    Route::delete('/reimbursements/{reimbursement}', [ReimbursementController::class, 'destroy'])->name('reimbursements.destroy');
    Route::get('/reimbursements/{reimbursement}/pdf', [ReimbursementController::class, 'exportPdf'])->name('reimbursements.pdf');

    // Approval & Rejection (Manager, HR Admin, Super Admin)
    Route::get('/reimbursements/{reimbursement}/approve', [ReimbursementController::class, 'approveForm'])->name('reimbursements.approve.form');
    Route::post('/reimbursements/{reimbursement}/approve', [ReimbursementController::class, 'approve'])->name('reimbursements.approve');
    Route::get('/reimbursements/{reimbursement}/reject', [ReimbursementController::class, 'rejectForm'])->name('reimbursements.reject.form');
    Route::post('/reimbursements/{reimbursement}/reject', [ReimbursementController::class, 'reject'])->name('reimbursements.reject');

    // Disbursement / Payment (Finance, Super Admin)
    Route::get('/reimbursements/{reimbursement}/pay', [ReimbursementController::class, 'pay'])->name('reimbursements.pay');
    Route::put('/reimbursements/{reimbursement}/pay', [ReimbursementController::class, 'processPay'])->name('reimbursements.process-pay');

    // Master Categories (HR Admin, Super Admin)
    Route::get('/reimbursements-categories', [ReimbursementController::class, 'categoriesIndex'])->name('reimbursements.categories.index');
    Route::get('/reimbursements-categories/create', [ReimbursementController::class, 'createCategory'])->name('reimbursements.categories.create');
    Route::post('/reimbursements-categories', [ReimbursementController::class, 'storeCategory'])->name('reimbursements.categories.store');
    Route::get('/reimbursements-categories/{category}/edit', [ReimbursementController::class, 'editCategory'])->name('reimbursements.categories.edit');
    Route::put('/reimbursements-categories/{category}', [ReimbursementController::class, 'updateCategory'])->name('reimbursements.categories.update');
    Route::delete('/reimbursements-categories/{category}', [ReimbursementController::class, 'destroyCategory'])->name('reimbursements.categories.destroy');

    // ========================================================
    // PINJAMAN KARYAWAN / EMPLOYEE LOANS (Kasbon & Pinjaman)
    // ========================================================
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('/loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::get('/loans/{loan}/edit', [LoanController::class, 'edit'])->name('loans.edit');
    Route::put('/loans/{loan}', [LoanController::class, 'update'])->name('loans.update');
    Route::delete('/loans/{loan}', [LoanController::class, 'destroy'])->name('loans.destroy');
    Route::get('/loans/{loan}/pdf', [LoanController::class, 'exportPdf'])->name('loans.pdf');

    // Approval & Rejection (Non-modal dedicated pages)
    Route::get('/loans/{loan}/approve', [LoanController::class, 'approveForm'])->name('loans.approve.form');
    Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::get('/loans/{loan}/reject', [LoanController::class, 'rejectForm'])->name('loans.reject.form');
    Route::post('/loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');

    // Pencairan Dana / Disbursement (Non-modal dedicated page)
    Route::get('/loans/{loan}/disburse', [LoanController::class, 'disburseForm'])->name('loans.disburse.form');
    Route::post('/loans/{loan}/disburse', [LoanController::class, 'disburse'])->name('loans.disburse');

    // Pembayaran Cicilan / Pelunasan (Non-modal dedicated page)
    Route::get('/loans/{loan}/repay', [LoanController::class, 'repayForm'])->name('loans.repay.form');
    Route::post('/loans/{loan}/repay', [LoanController::class, 'repay'])->name('loans.repay');

    // ========================================================
    // PENGUNDURAN DIRI & RESIGN KARYAWAN (Resignation & Offboarding)
    // ========================================================
    Route::get('/resignations', [ResignationController::class, 'index'])->name('resignations.index');
    Route::get('/resignations/create', [ResignationController::class, 'create'])->name('resignations.create');
    Route::post('/resignations', [ResignationController::class, 'store'])->name('resignations.store');
    Route::get('/resignations/{resignation}', [ResignationController::class, 'show'])->name('resignations.show');
    Route::post('/resignations/{resignation}/cancel', [ResignationController::class, 'cancel'])->name('resignations.cancel');
    Route::get('/resignations/{resignation}/pdf', [ResignationController::class, 'exportPdf'])->name('resignations.pdf');

    // Management & Approval Actions
    Route::middleware('role:super_admin,hr_admin,manager')->group(function () {
        Route::post('/resignations/{resignation}/approve', [ResignationController::class, 'approve'])->name('resignations.approve');
        Route::post('/resignations/{resignation}/reject', [ResignationController::class, 'reject'])->name('resignations.reject');
        Route::post('/resignations/{resignation}/clearance', [ResignationController::class, 'updateClearance'])->name('resignations.clearance');
        Route::post('/resignations/{resignation}/complete', [ResignationController::class, 'complete'])->name('resignations.complete');
    });

    // Indonesian Aliases
    Route::get('/resign', [ResignationController::class, 'index']);
    Route::get('/pengajuan-resign', [ResignationController::class, 'create']);

    // ========================================================
    // MUTASI & PROMOSI KARYAWAN (Employee Career Transitions)
    // ========================================================
    Route::get('/career-transitions', [CareerTransitionController::class, 'index'])->name('career-transitions.index');
    Route::get('/career-transitions/create', [CareerTransitionController::class, 'create'])->name('career-transitions.create');
    Route::post('/career-transitions', [CareerTransitionController::class, 'store'])->name('career-transitions.store');
    Route::get('/career-transitions/{careerTransition}', [CareerTransitionController::class, 'show'])->name('career-transitions.show');
    Route::post('/career-transitions/{careerTransition}/cancel', [CareerTransitionController::class, 'cancel'])->name('career-transitions.cancel');
    Route::get('/career-transitions/{careerTransition}/pdf', [CareerTransitionController::class, 'exportPdf'])->name('career-transitions.pdf');
    Route::get('/api/employees/{employee}/snapshot', [CareerTransitionController::class, 'getEmployeeSnapshot'])->name('career-transitions.snapshot');

    // Management & Approval Actions
    Route::middleware('role:super_admin,hr_admin,manager')->group(function () {
        Route::post('/career-transitions/{careerTransition}/approve', [CareerTransitionController::class, 'approve'])->name('career-transitions.approve');
        Route::post('/career-transitions/{careerTransition}/apply', [CareerTransitionController::class, 'apply'])->name('career-transitions.apply');
        Route::post('/career-transitions/{careerTransition}/reject', [CareerTransitionController::class, 'reject'])->name('career-transitions.reject');
    });

    // Friendly Aliases
    Route::get('/mutasi-promosi', [CareerTransitionController::class, 'index']);
    Route::get('/mutasi-promosi/tambah', [CareerTransitionController::class, 'create']);

    // ========================================================
    // APPROVALS & READ-ONLY STRUCTURE (Manager, HR Admin, Super Admin)
    // ========================================================
    Route::middleware('role:super_admin,hr_admin,manager')->group(function () {
        Route::get('/organization', [OrganizationController::class, 'index'])->name('organization.index');
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::get('/attendance/export-excel', [AttendanceController::class, 'exportExcel'])->name('attendance.export_excel');
        Route::get('/attendance/export-pdf', [AttendanceController::class, 'exportPdf'])->name('attendance.export_pdf');
        Route::get('/attendance/export', [AttendanceController::class, 'exportExcel'])->name('attendance.export');

        Route::post('/leaves/{leaveRequest}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/overtime/{overtimeRequest}/approve', [OvertimeController::class, 'approve'])->name('overtime.approve');
        Route::post('/attendance/adjustments/{adjustment}/approve', [AttendanceController::class, 'approveAdjustment'])->name('attendance.adjustments.approve');

        // ========================================================
        // MODUL LAPORAN & REKAPITULASI (Presensi, Gaji, Jadwal, Cuti, Reimbursement, Pinjaman)
        // ========================================================
        Route::prefix('reports')->name('reports.')->group(function () {
            // 1. Laporan Presensi
            Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
            Route::post('/attendance/export', [ReportController::class, 'exportAttendance'])->name('attendance.export');

            // 2. Laporan Gaji & Rekap Payroll
            Route::get('/payroll', [ReportController::class, 'payroll'])->name('payroll');
            Route::post('/payroll/export', [ReportController::class, 'exportPayroll'])->name('payroll.export');

            // 3. Laporan & Matriks Jadwal Kerja
            Route::get('/schedule', [ReportController::class, 'schedule'])->name('schedule');
            Route::post('/schedule/export', [ReportController::class, 'exportSchedule'])->name('schedule.export');

            // 4. Laporan Rekapitulasi Cuti
            Route::get('/leave', [ReportController::class, 'leave'])->name('leave');
            Route::post('/leave/export', [ReportController::class, 'exportLeave'])->name('leave.export');

            // 5. Laporan Klaim Reimbursement
            Route::get('/reimbursement', [ReportController::class, 'reimbursement'])->name('reimbursement');
            Route::post('/reimbursement/export', [ReportController::class, 'exportReimbursement'])->name('reimbursement.export');

            // 6. Laporan Pinjaman Karyawan
            Route::get('/loans', [ReportController::class, 'loans'])->name('loans');
            Route::post('/loans/export', [ReportController::class, 'exportLoans'])->name('loans.export');

            // AJAX Filter Helper
            Route::get('/employees-by-filter', [ReportController::class, 'getEmployeesByFilter'])->name('employees_by_filter');
        });

        // Aliases for Indonesian URLs (/laporan/...)
        Route::get('/laporan/presensi', [ReportController::class, 'attendance']);
        Route::post('/laporan/cetakpresensi', [ReportController::class, 'exportAttendance']);
        Route::get('/laporan/gaji', [ReportController::class, 'payroll']);
        Route::get('/laporan/jadwal', [ReportController::class, 'schedule']);
        Route::post('/laporan/cetakjadwal', [ReportController::class, 'exportSchedule']);
        Route::get('/laporan/cuti', [ReportController::class, 'leave']);
        Route::post('/laporan/cetakcuti', [ReportController::class, 'exportLeave']);
        Route::get('/laporan/reimbursement', [ReportController::class, 'reimbursement']);
        Route::post('/laporan/cetakreimbursement', [ReportController::class, 'exportReimbursement']);
        Route::get('/laporan/pinjaman', [ReportController::class, 'loans']);
        Route::post('/laporan/cetakpinjaman', [ReportController::class, 'exportLoans']);
    });

    // ========================================================
    // SYSTEM SETTINGS & AUDIT TRAIL (Super Admin & HR Admin)
    // ========================================================
    Route::middleware('role:super_admin,hr_admin')->group(function () {
        Route::get('/audit-logs', [AuditController::class, 'index'])->name('audit.index');
        Route::get('/pengaturan-sistem', [AuditController::class, 'settings'])->name('settings.index');
        Route::post('/audit-logs/settings', [AuditController::class, 'updateSettings'])->name('audit.settings.update');
        Route::post('/pengaturan-sistem', [AuditController::class, 'updateSettings'])->name('settings.update');

        // ========================================================
        // USER, ROLE, PERMISSION & GROUP PERMISSION
        // ========================================================
        // Users
        Route::get('/users/{user}/permissions', [UserController::class, 'permissions'])->name('users.permissions');
        Route::put('/users/{user}/permissions', [UserController::class, 'updatePermissions'])->name('users.permissions.update');
        Route::resource('users', UserController::class);

        // Roles
        Route::get('/roles/{role}/permissions', [RoleController::class, 'permissions'])->name('roles.permissions');
        Route::put('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');
        Route::resource('roles', RoleController::class);

        // Permissions
        Route::resource('permissions', PermissionController::class)->except(['show']);

        // Group Permissions
        Route::resource('group-permissions', PermissionGroupController::class)->except(['show']);
    });
});

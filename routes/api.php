<?php

use App\Http\Controllers\Api\ApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| HRIS REST API Routes (Blueprint Section 11)
|--------------------------------------------------------------------------
|
| Versioned REST API endpoints for employees, attendance ingestion,
| leaves, overtime, payroll processing, and device synchronization.
|
*/

Route::prefix('v1')->group(function () {
    // Employees
    Route::get('/employees', [ApiController::class, 'getEmployees']);
    Route::post('/employees', [ApiController::class, 'storeEmployee']);
    Route::get('/employees/{id}', [ApiController::class, 'getEmployee']);

    // Attendance & Daily Rekap
    Route::get('/attendance/daily', [ApiController::class, 'getDailyAttendance']);
    Route::get('/attendance/logs', [ApiController::class, 'getAttendanceLogs']);
    Route::post('/attendance/adjustments', [ApiController::class, 'storeAdjustment']);
    Route::post('/attendance/check-in', [ApiController::class, 'checkIn']);
    Route::post('/attendance/check-out', [ApiController::class, 'checkOut']);
    Route::post('/attendance/verify-location', [ApiController::class, 'verifyLocation']);

    // Absen Visit / Field Visits API
    Route::get('/visits', [ApiController::class, 'getVisits']);
    Route::get('/visits/active', [ApiController::class, 'getActiveVisit']);
    Route::post('/visits/check-in', [ApiController::class, 'visitCheckIn']);
    Route::post('/visits/{id}/check-out', [ApiController::class, 'visitCheckOut']);

    // Leaves & Approvals
    Route::get('/leave/balances', [ApiController::class, 'getLeaveBalances']);
    Route::post('/leave/requests', [ApiController::class, 'storeLeaveRequest']);
    Route::post('/leave/requests/{id}/approve', [ApiController::class, 'approveLeaveRequest']);
    Route::post('/leave/requests/{id}/reject', [ApiController::class, 'rejectLeaveRequest']);

    // Overtime
    Route::post('/overtime/requests', [ApiController::class, 'storeOvertimeRequest']);

    // Payroll & Runs
    Route::get('/payroll/periods', [ApiController::class, 'getPayrollPeriods']);
    Route::post('/payroll/runs', [ApiController::class, 'runPayroll']);

    // Reports
    Route::get('/reports/attendance', [ApiController::class, 'getAttendanceReport']);

    // Devices & Sync
    Route::get('/devices', [ApiController::class, 'getDevices']);
    Route::post('/devices/{id}/sync', [ApiController::class, 'syncDevice']);
});

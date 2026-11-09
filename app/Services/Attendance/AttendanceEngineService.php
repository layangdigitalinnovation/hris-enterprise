<?php

namespace App\Services\Attendance;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\Shift;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AttendanceEngineService
{
    /**
     * Process attendance calculations for a specific date (or range).
     */
    public function processDailyAttendance(string|Carbon $date, ?int $employeeId = null): array
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $query = Employee::with('branch')->where('status', 'active');
        if ($employeeId) {
            $query->where('id', $employeeId);
        }
        $employees = $query->get();

        $processedCount = 0;
        $presentCount = 0;
        $lateCount = 0;
        $absentCount = 0;

        $isHoliday = Holiday::where('date', $dateStr)->first();

        // Default Shift fallback
        $defaultShift = Shift::where('is_active', true)->first();

        foreach ($employees as $employee) {
            // 1. Determine Shift
            $assignment = EmployeeShiftAssignment::where('employee_id', $employee->id)
                ->where('date', $dateStr)
                ->first();

            $shift = $assignment && $assignment->shift ? $assignment->shift : $defaultShift;
            $isDayOff = $assignment ? $assignment->is_day_off : false;

            // Check Weekend default based on SystemSetting and Country/Timezone
            // Indonesia: Sun (or Sat-Sun) is off. Saudi Arabia (Asia/Riyadh): Fri (or Fri-Sat) is off, Sun is a workday!
            $dayOfWeek = Carbon::parse($dateStr)->dayOfWeek; // 0 = Sunday, 5 = Friday, 6 = Saturday
            if (!$assignment) {
                $workDays = (int) SystemSetting::get('work_days_per_week', 5);
                $isSaudi = ($employee->branch?->timezone === 'Asia/Riyadh');

                if ($isSaudi) {
                    if ($workDays === 6) {
                        if ($dayOfWeek === 5) { // Friday only is day off in Saudi 6-day week
                            $isDayOff = true;
                        }
                    } else {
                        if ($dayOfWeek === 5 || $dayOfWeek === 6) { // Friday & Saturday are weekend in Saudi Arabia
                            $isDayOff = true;
                        }
                    }
                } else {
                    if ($workDays === 6) {
                        if ($dayOfWeek === 0) { // Sunday only is day off in Indonesia 6-day week
                            $isDayOff = true;
                        }
                    } else {
                        if ($dayOfWeek === 0 || $dayOfWeek === 6) { // Saturday & Sunday are weekend in Indonesia
                            $isDayOff = true;
                        }
                    }
                }
            }

            // 2. Check Approved Leaves
            $onLeave = LeaveRequest::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where('start_date', '<=', $dateStr)
                ->where('end_date', '>=', $dateStr)
                ->with('leaveType')
                ->first();

            // 3. Fetch Raw Logs for this Employee on this date
            $logs = AttendanceLog::where('employee_id', $employee->id)
                ->whereDate('log_time', $dateStr)
                ->orderBy('log_time', 'asc')
                ->get();

            $clockIn = null;
            $clockOut = null;
            $lateMinutes = 0;
            $earlyLeaveMinutes = 0;
            $workHours = 0.00;
            $overtimeHours = 0.00;
            $status = 'absent';
            $notes = null;

            if ($onLeave) {
                $status = 'on_leave';
                $notes = "Cuti: " . ($onLeave->leaveType->name ?? 'Izin');
            } elseif ($isHoliday) {
                $status = 'holiday';
                $notes = "Hari Libur: " . $isHoliday->name;
            } elseif ($isDayOff) {
                $status = 'day_off';
                $notes = "Hari Libur Kerja / Off";
            } elseif ($logs->isNotEmpty()) {
                // Earliest log as Clock In
                $firstLog = $logs->first();
                $lastLog = $logs->last();

                $clockIn = Carbon::parse($firstLog->log_time)->format('H:i:s');
                if ($logs->count() > 1) {
                    $clockOut = Carbon::parse($lastLog->log_time)->format('H:i:s');
                }

                // Check against shift rules
                if ($shift) {
                    $shiftStart = Carbon::parse("{$dateStr} {$shift->start_time}");
                    $actualIn = Carbon::parse("{$dateStr} {$clockIn}");

                    // Late calculation with fallback to SystemSetting late_tolerance_default
                    $defaultTolerance = (int) SystemSetting::get('late_tolerance_default', 15);
                    $toleranceMins = ($shift && $shift->late_tolerance_minutes !== null) ? (int) $shift->late_tolerance_minutes : $defaultTolerance;
                    $toleranceDeadline = $shiftStart->copy()->addMinutes($toleranceMins);
                    if ($actualIn->greaterThan($toleranceDeadline)) {
                        $lateMinutes = $shiftStart->diffInMinutes($actualIn);
                        $status = 'late';
                    } else {
                        $status = 'present';
                    }

                    // Early leave calculation
                    if ($clockOut) {
                        $shiftEnd = Carbon::parse("{$dateStr} {$shift->end_time}");
                        $actualOut = Carbon::parse("{$dateStr} {$clockOut}");

                        if ($actualOut->lessThan($shiftEnd->copy()->subMinutes($shift->early_leave_tolerance_minutes))) {
                            $earlyLeaveMinutes = $actualOut->diffInMinutes($shiftEnd);
                            if ($status !== 'late') {
                                $status = 'early_leave';
                            }
                        }

                        // Work hours
                        $workHours = round($actualIn->diffInMinutes($actualOut) / 60, 2);

                        // Overtime if worked past shift end
                        if ($actualOut->greaterThan($shiftEnd)) {
                            $overtimeHours = round($shiftEnd->diffInMinutes($actualOut) / 60, 2);
                        }
                    } else {
                        // Single tap without clock out
                        $notes = 'Belum tap keluar (Single Tap)';
                    }
                } else {
                    $status = 'present';
                    if ($clockIn && $clockOut) {
                        $actualIn = Carbon::parse("{$dateStr} {$clockIn}");
                        $actualOut = Carbon::parse("{$dateStr} {$clockOut}");
                        $workHours = round($actualIn->diffInMinutes($actualOut) / 60, 2);

                        $isSaudiBranch = ($employee->branch?->country_code === 'SA' || $employee->branch?->timezone === 'Asia/Riyadh');
                        $isRamadan = (bool) SystemSetting::get('overtime_saudi_is_ramadan', false);
                        $dailyNormalHours = ($isSaudiBranch && $isRamadan) ? 6.0 : (float) SystemSetting::get('overtime_saudi_daily_hours', 8.0);

                        if ($workHours > $dailyNormalHours) {
                            $overtimeHours = round($workHours - $dailyNormalHours, 2);
                        }
                    }
                }

                // Mark raw logs as processed
                AttendanceLog::whereIn('id', $logs->pluck('id'))->update(['status' => 'processed']);
            }

            // 4. Upsert AttendanceDailySummary
            AttendanceDailySummary::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $dateStr,
                ],
                [
                    'shift_id' => $shift?->id,
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'work_hours' => $workHours,
                    'overtime_hours' => $overtimeHours,
                    'status' => $status,
                    'notes' => $notes,
                ]
            );

            $processedCount++;
            if (in_array($status, ['present', 'early_leave'])) $presentCount++;
            if ($status === 'late') $lateCount++;
            if ($status === 'absent') $absentCount++;
        }

        return [
            'date' => $dateStr,
            'total_processed' => $processedCount,
            'present' => $presentCount,
            'late' => $lateCount,
            'absent' => $absentCount,
            'message' => "Attendance engine selesai memproses {$processedCount} karyawan untuk tanggal {$dateStr}.",
        ];
    }
}

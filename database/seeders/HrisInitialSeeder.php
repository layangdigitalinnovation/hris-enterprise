<?php

namespace Database\Seeders;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceEmployeeMapping;
use App\Models\Employee;
use App\Models\EmployeeCompensation;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Attendance\AttendanceEngineService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrisInitialSeeder extends Seeder
{
    public function run(): void
    {
        // 1. System Settings
        SystemSetting::set('company_name', 'PT Solusi Teknologi Nusantara', 'general', 'Nama Perusahaan');
        SystemSetting::set('company_phone', '(021) 555-8900', 'general', 'Telepon');
        SystemSetting::set('company_address', 'Menara Satrio Lt. 18, Mega Kuningan, Jakarta Selatan', 'general', 'Alamat');
        SystemSetting::set('work_days_per_week', '5', 'attendance', 'Hari Kerja per Minggu');
        SystemSetting::set('late_tolerance_default', '15', 'attendance', 'Toleransi Keterlambatan (menit)');
        SystemSetting::set('payroll_cut_off_day', '25', 'payroll', 'Tanggal Cut-Off Payroll');

        // 2. Branches
        $branchJkt = Branch::create([
            'code' => 'BR-JKT',
            'name' => 'Kantor Pusat Jakarta',
            'phone' => '(021) 555-8900',
            'address' => 'Jl. Prof. Dr. Satrio Kav 18, Jakarta Selatan',
            'latitude' => -6.2238472,
            'longitude' => 106.8242191,
            'timezone' => 'Asia/Jakarta',
            'radius_meters' => 150,
            'is_active' => true,
        ]);

        $branchSby = Branch::create([
            'code' => 'BR-SBY',
            'name' => 'Kantor Cabang Surabaya',
            'phone' => '(031) 888-2100',
            'address' => 'Jl. Pemuda No. 45, Embong Kaliasin, Surabaya',
            'latitude' => -7.2654310,
            'longitude' => 112.7485290,
            'timezone' => 'Asia/Jakarta',
            'radius_meters' => 100,
            'is_active' => true,
        ]);

        // 3. Departments
        $deptIt = Department::create(['code' => 'IT', 'name' => 'Teknologi Informasi & Sistem', 'description' => 'Software engineering, infrastructure & support']);
        $deptHr = Department::create(['code' => 'HRGA', 'name' => 'Human Resource & General Affairs', 'description' => 'Talent management, payroll, and office administration']);
        $deptFin = Department::create(['code' => 'FIN', 'name' => 'Finance & Accounting', 'description' => 'Financial reporting, taxes and budgeting']);
        $deptOps = Department::create(['code' => 'OPS', 'name' => 'Operasional & Logistik', 'description' => 'Field operations and warehouse']);

        // 4. Positions
        $posItMgr = Position::create(['code' => 'MGR-IT', 'title' => 'IT Manager', 'department_id' => $deptIt->id, 'level' => 4]);
        $posDev = Position::create(['code' => 'SR-DEV', 'title' => 'Senior Fullstack Developer', 'department_id' => $deptIt->id, 'level' => 2]);
        $posHrMgr = Position::create(['code' => 'MGR-HR', 'title' => 'HR Manager', 'department_id' => $deptHr->id, 'level' => 4]);
        $posHrStaff = Position::create(['code' => 'STF-HR', 'title' => 'HR Specialist & Payroll', 'department_id' => $deptHr->id, 'level' => 1]);
        $posFinMgr = Position::create(['code' => 'MGR-FIN', 'title' => 'Finance Manager', 'department_id' => $deptFin->id, 'level' => 4]);
        $posOpsStaff = Position::create(['code' => 'STF-OPS', 'title' => 'Staff Operasional', 'department_id' => $deptOps->id, 'level' => 1]);

        // 5. Shifts
        $shiftReguler = Shift::create([
            'code' => 'SH-REG',
            'name' => 'Shift Reguler (08:00 - 17:00)',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'late_tolerance_minutes' => 15,
            'early_leave_tolerance_minutes' => 0,
            'is_overnight' => false,
            'color' => '#3B82F6',
            'is_active' => true,
        ]);

        $shiftPagi = Shift::create([
            'code' => 'SH-PAGI',
            'name' => 'Shift Pagi (07:00 - 15:30)',
            'start_time' => '07:00:00',
            'end_time' => '15:30:00',
            'late_tolerance_minutes' => 10,
            'early_leave_tolerance_minutes' => 0,
            'is_overnight' => false,
            'color' => '#10B981',
            'is_active' => true,
        ]);

        $shiftSiang = Shift::create([
            'code' => 'SH-SIANG',
            'name' => 'Shift Siang (15:00 - 23:00)',
            'start_time' => '15:00:00',
            'end_time' => '23:00:00',
            'late_tolerance_minutes' => 10,
            'early_leave_tolerance_minutes' => 0,
            'is_overnight' => false,
            'color' => '#F59E0B',
            'is_active' => true,
        ]);

        // 6. Devices (Solution Fingerprint / Face)
        $deviceLobi = Device::create([
            'name' => 'Solution X302 - Lobi Utama JKT',
            'device_model' => 'Solution X302 (Fingerprint & RFID)',
            'ip_address' => '192.168.1.201',
            'port' => 4370,
            'comm_key' => '0',
            'serial_number' => 'SOL-X302-JKT01',
            'branch_id' => $branchJkt->id,
            'location' => 'Lantai 1 - Pintu Masuk Karyawan',
            'status' => 'online',
            'sync_method' => 'mock',
            'auto_sync' => true,
            'sync_interval_minutes' => 15,
            'last_sync_at' => now(),
        ]);

        $deviceSby = Device::create([
            'name' => 'Solution X100-C - Pintu Masuk SBY',
            'device_model' => 'Solution X100-C (Fingerprint)',
            'ip_address' => '192.168.2.201',
            'port' => 4370,
            'comm_key' => '0',
            'serial_number' => 'SOL-X100-SBY01',
            'branch_id' => $branchSby->id,
            'location' => 'Lantai 1 Depan Resepsionis',
            'status' => 'online',
            'sync_method' => 'mock',
            'auto_sync' => true,
            'sync_interval_minutes' => 15,
            'last_sync_at' => now(),
        ]);

        // 7. Leave Types
        $leaveAnnual = LeaveType::create(['code' => 'CT', 'name' => 'Cuti Tahunan', 'default_quota' => 12, 'is_paid' => true, 'requires_document' => false]);
        $leaveSick = LeaveType::create(['code' => 'SK', 'name' => 'Cuti Sakit', 'default_quota' => 14, 'is_paid' => true, 'requires_document' => true]);
        $leaveMaternity = LeaveType::create(['code' => 'ML', 'name' => 'Cuti Melahirkan', 'default_quota' => 90, 'is_paid' => true, 'requires_document' => true, 'gender_restriction' => 'female']);
        $leaveMarriage = LeaveType::create(['code' => 'MN', 'name' => 'Cuti Menikah', 'default_quota' => 3, 'is_paid' => true, 'requires_document' => true]);

        // 8. Salary Components
        $salBasic = SalaryComponent::create(['code' => 'BASIC', 'name' => 'Gaji Pokok', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => true]);
        $salPos = SalaryComponent::create(['code' => 'ALW_POS', 'name' => 'Tunjangan Jabatan', 'type' => 'earning', 'is_taxable' => true, 'is_fixed' => true]);
        $salMeal = SalaryComponent::create(['code' => 'ALW_MEAL', 'name' => 'Tunjangan Uang Makan & Transport', 'type' => 'earning', 'is_taxable' => false, 'is_fixed' => false]);
        $dedBpjsKes = SalaryComponent::create(['code' => 'DED_BPJS_KES', 'name' => 'Potongan BPJS Kesehatan (1%)', 'type' => 'deduction', 'is_taxable' => false, 'is_fixed' => true]);
        $dedBpjsTk = SalaryComponent::create(['code' => 'DED_BPJS_TK', 'name' => 'Potongan BPJS Ketenagakerjaan (2%)', 'type' => 'deduction', 'is_taxable' => false, 'is_fixed' => true]);
        $dedLate = SalaryComponent::create(['code' => 'DED_LATE', 'name' => 'Potongan Keterlambatan Absensi', 'type' => 'deduction', 'is_taxable' => false, 'is_fixed' => false]);

        // 9. Users & Employees
        $usersData = [
            [
                'name' => 'Super Administrator',
                'email' => 'admin@hris.local',
                'role' => 'super_admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'code' => 'EMP-001',
                'dept' => $deptIt,
                'pos' => $posItMgr,
                'branch' => $branchJkt,
                'pin' => '1001',
                'gender' => 'male',
                'basic' => 20000000,
            ],
            [
                'name' => 'Siti Rahmawati (HR Admin)',
                'email' => 'hr@hris.local',
                'role' => 'hr_admin',
                'first_name' => 'Siti',
                'last_name' => 'Rahmawati',
                'code' => 'EMP-002',
                'dept' => $deptHr,
                'pos' => $posHrMgr,
                'branch' => $branchJkt,
                'pin' => '1002',
                'gender' => 'female',
                'basic' => 16000000,
            ],
            [
                'name' => 'Bambang Sudirman (Manager)',
                'email' => 'manager@hris.local',
                'role' => 'manager',
                'first_name' => 'Bambang',
                'last_name' => 'Sudirman',
                'code' => 'EMP-003',
                'dept' => $deptFin,
                'pos' => $posFinMgr,
                'branch' => $branchJkt,
                'pin' => '1003',
                'gender' => 'male',
                'basic' => 18000000,
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@hris.local',
                'role' => 'employee',
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'code' => 'EMP-004',
                'dept' => $deptIt,
                'pos' => $posDev,
                'branch' => $branchJkt,
                'pin' => '1004',
                'gender' => 'male',
                'basic' => 12500000,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@hris.local',
                'role' => 'employee',
                'first_name' => 'Dewi',
                'last_name' => 'Lestari',
                'code' => 'EMP-005',
                'dept' => $deptHr,
                'pos' => $posHrStaff,
                'branch' => $branchJkt,
                'pin' => '1005',
                'gender' => 'female',
                'basic' => 8500000,
            ],
            [
                'name' => 'Ahmad Fauzi',
                'email' => 'fauzi@hris.local',
                'role' => 'employee',
                'first_name' => 'Ahmad',
                'last_name' => 'Fauzi',
                'code' => 'EMP-006',
                'dept' => $deptOps,
                'pos' => $posOpsStaff,
                'branch' => $branchSby,
                'pin' => '1006',
                'gender' => 'male',
                'basic' => 7000000,
            ],
        ];

        $today = Carbon::today();

        foreach ($usersData as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => $data['role'],
                'phone' => '0812' . rand(10000000, 99999999),
                'is_active' => true,
                'last_login_at' => now()->subHours(rand(1, 10)),
            ]);

            $emp = Employee::create([
                'user_id' => $user->id,
                'employee_code' => $data['code'],
                'nik' => '3171' . rand(100000000000, 999999999999),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $user->phone,
                'gender' => $data['gender'],
                'birth_place' => 'Jakarta',
                'birth_date' => Carbon::now()->subYears(rand(24, 38))->subDays(rand(1, 300)),
                'marital_status' => 'married',
                'religion' => 'Islam',
                'address' => 'Jl. Kebagusan Raya No. ' . rand(10, 99) . ', Jakarta',
                'branch_id' => $data['branch']->id,
                'department_id' => $data['dept']->id,
                'position_id' => $data['pos']->id,
                'join_date' => Carbon::now()->subMonths(rand(6, 36)),
                'employment_status' => 'permanent',
                'status' => 'active',
                'bank_name' => 'BCA',
                'bank_account_no' => (string) rand(1000000000, 9999999999),
                'bank_account_holder' => $data['name'],
                'npwp' => '09.' . rand(100, 999) . '.' . rand(100, 999) . '.0-011.000',
            ]);

            // Map Employee to Solution Device
            $targetDevice = $data['branch']->id === $branchSby->id ? $deviceSby : $deviceLobi;
            DeviceEmployeeMapping::create([
                'device_id' => $targetDevice->id,
                'employee_id' => $emp->id,
                'device_pin' => $data['pin'],
                'privilege' => in_array($user->role, ['super_admin', 'hr_admin']) ? 'admin' : 'user',
                'enrolled_at' => now(),
            ]);

            // Leave Balance
            LeaveBalance::create([
                'employee_id' => $emp->id,
                'leave_type_id' => $leaveAnnual->id,
                'year' => 2026,
                'initial_quota' => 12,
                'used_quota' => rand(1, 4),
                'remaining_quota' => 12 - rand(1, 4),
            ]);

            // Salary Compensation
            EmployeeCompensation::create([
                'employee_id' => $emp->id,
                'salary_component_id' => $salBasic->id,
                'amount' => $data['basic'],
            ]);

            // Generate 7 days of attendance history for demo
            for ($dayOffset = 6; $dayOffset >= 0; $dayOffset--) {
                $dateObj = $today->copy()->subDays($dayOffset);
                if ($dateObj->isWeekend()) {
                    AttendanceDailySummary::create([
                        'employee_id' => $emp->id,
                        'date' => $dateObj->format('Y-m-d'),
                        'shift_id' => $shiftReguler->id,
                        'status' => 'day_off',
                        'notes' => 'Akhir Pekan (Weekend)',
                    ]);
                    continue;
                }

                // Normal Work Day
                $isLate = ($data['code'] === 'EMP-004' && $dayOffset === 1); // Budi late yesterday
                $minuteIn = $isLate ? rand(20, 35) : rand(-10, 10);
                $clockInTime = $dateObj->copy()->setTime(8, 0, 0)->addMinutes($minuteIn);
                $clockOutTime = $dateObj->copy()->setTime(17, 0, 0)->addMinutes(rand(5, 45));

                // Insert raw logs
                $hashIn = AttendanceLog::generateHash($targetDevice->id, $data['pin'], $clockInTime->format('Y-m-d H:i:s'));
                AttendanceLog::create([
                    'device_id' => $targetDevice->id,
                    'employee_id' => $emp->id,
                    'device_pin' => $data['pin'],
                    'log_time' => $clockInTime,
                    'verify_type' => 'fingerprint',
                    'log_type' => 'check_in',
                    'hash_signature' => $hashIn,
                    'status' => 'processed',
                ]);

                $hashOut = AttendanceLog::generateHash($targetDevice->id, $data['pin'], $clockOutTime->format('Y-m-d H:i:s'));
                AttendanceLog::create([
                    'device_id' => $targetDevice->id,
                    'employee_id' => $emp->id,
                    'device_pin' => $data['pin'],
                    'log_time' => $clockOutTime,
                    'verify_type' => 'fingerprint',
                    'log_type' => 'check_out',
                    'hash_signature' => $hashOut,
                    'status' => 'processed',
                ]);

                // Summary
                $lateMins = $isLate ? ($minuteIn - 15) : 0;
                $workHrs = round($clockInTime->diffInMinutes($clockOutTime) / 60, 2);
                $overtimeHrs = $workHrs > 9.0 ? round($workHrs - 9.0, 2) : 0;

                AttendanceDailySummary::create([
                    'employee_id' => $emp->id,
                    'date' => $dateObj->format('Y-m-d'),
                    'shift_id' => $shiftReguler->id,
                    'clock_in' => $clockInTime->format('H:i:s'),
                    'clock_out' => $clockOutTime->format('H:i:s'),
                    'late_minutes' => max(0, $lateMins),
                    'early_leave_minutes' => 0,
                    'work_hours' => $workHrs,
                    'overtime_hours' => $overtimeHrs,
                    'status' => $isLate ? 'late' : 'present',
                    'notes' => $isLate ? 'Terlambat ' . $lateMins . ' menit' : 'Kehadiran normal',
                ]);
            }
        }
    }
}

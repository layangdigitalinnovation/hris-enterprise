<?php

namespace App\Http\Controllers;

use App\Models\AttendanceDailySummary;
use App\Models\AttendanceLog;
use App\Models\AttendanceVisit;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        $isEmployee = $currentUser->isEmployee();
        $myEmp = $currentUser->employee;

        $query = AttendanceVisit::with(['employee.department', 'employee.position', 'branch']);

        // Scope by Role
        if ($isEmployee && $myEmp) {
            $query->where('employee_id', $myEmp->id);
            $activeVisit = AttendanceVisit::where('employee_id', $myEmp->id)
                ->where('status', 'ongoing')
                ->latest()
                ->first();
        } elseif ($currentUser->isManager() && $myEmp?->department_id) {
            $deptId = $myEmp->department_id;
            $query->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
            $activeVisit = $myEmp ? AttendanceVisit::where('employee_id', $myEmp->id)->where('status', 'ongoing')->latest()->first() : null;
        } else {
            // HR Admin / Super Admin
            $activeVisit = $myEmp ? AttendanceVisit::where('employee_id', $myEmp->id)->where('status', 'ongoing')->latest()->first() : null;
        }

        // Filters
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                  ->orWhere('visit_number', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($empQ) use ($search) {
                      $empQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->input('date')) {
            $query->whereDate('check_in_at', $date);
        }

        if ($empId = $request->input('employee_id')) {
            $query->where('employee_id', $empId);
        }

        $visits = $query->orderBy('check_in_at', 'desc')->paginate(15)->withQueryString();

        // Statistics Today
        $today = Carbon::today()->format('Y-m-d');
        $statQuery = AttendanceVisit::query();
        if ($isEmployee && $myEmp) {
            $statQuery->where('employee_id', $myEmp->id);
        }
        $todayVisits = (clone $statQuery)->whereDate('check_in_at', $today)->get();

        $stats = [
            'total_today' => $todayVisits->count(),
            'ongoing_today' => $todayVisits->where('status', 'ongoing')->count(),
            'completed_today' => $todayVisits->where('status', 'completed')->count(),
            'total_minutes_today' => $todayVisits->where('status', 'completed')->sum('duration_minutes'),
        ];

        $departments = Department::where('is_active', true)->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        return view('attendance.visits.index', compact(
            'visits',
            'activeVisit',
            'departments',
            'employees',
            'stats',
            'myEmp',
            'isEmployee'
        ));
    }

    public function create()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        $isEmployee = $currentUser->isEmployee();
        $myEmp = $currentUser->employee;

        // If current user already has an active ongoing visit, redirect to checkout form
        if ($myEmp) {
            $existingOngoing = AttendanceVisit::where('employee_id', $myEmp->id)
                ->where('status', 'ongoing')
                ->latest()
                ->first();

            if ($existingOngoing) {
                return redirect()->route('attendance.visits.checkout', $existingOngoing)
                    ->with('error', __('Anda masih memiliki kunjungan aktif di ":client" (Check-in: :time). Anda wajib melakukan Check-Out terlebih dahulu sebelum dapat memulai kunjungan baru!', [
                        'client' => $existingOngoing->client_name,
                        'time' => $existingOngoing->check_in_at->format('H:i'),
                    ]));
            }
        }

        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        return view('attendance.visits.create', compact('employees', 'myEmp', 'isEmployee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:150',
            'purpose' => 'required|string|max:1000',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        $currentUser = auth()->user();

        // Determine employee
        $employee = null;
        if ($request->filled('employee_id') && $currentUser->canManageEmployees()) {
            $employee = Employee::with('branch')->findOrFail($request->employee_id);
        } else {
            $employee = $currentUser->employee?->load('branch');
        }

        if (!$employee) {
            return back()->with('error', __('Akun pengguna Anda belum terhubung dengan data karyawan.'));
        }

        // CRITICAL RULE: Kunjungan bisa berkali-kali, DENGAN CATATAN HARUS CHECKOUT dari kunjungan sebelumnya
        $existingOngoing = AttendanceVisit::where('employee_id', $employee->id)
            ->where('status', 'ongoing')
            ->first();

        if ($existingOngoing) {
            return redirect()->route('attendance.visits.checkout', $existingOngoing)->with('error', __('Anda masih memiliki kunjungan aktif di ":client" (Check-in: :time). Anda wajib melakukan Check-Out terlebih dahulu sebelum dapat memulai kunjungan baru!', [
                'client' => $existingOngoing->client_name,
                'time' => $existingOngoing->check_in_at->format('H:i'),
            ]));
        }

        $branch = $employee->branch;
        $tz = $branch?->timezone ?: 'Asia/Jakarta';
        $localNow = Carbon::now($tz);

        // Generate visit number
        $todayStr = $localNow->format('Ymd');
        $countToday = AttendanceVisit::whereDate('created_at', $localNow->format('Y-m-d'))->count();
        $visitNumber = 'VST-' . $todayStr . '-' . str_pad($countToday + 1, 4, '0', STR_PAD_LEFT);

        $visit = AttendanceVisit::create([
            'employee_id' => $employee->id,
            'branch_id' => $branch?->id,
            'visit_number' => $visitNumber,
            'client_name' => $request->client_name,
            'purpose' => $request->purpose,
            'check_in_at' => $localNow->format('Y-m-d H:i:s'),
            'check_in_lat' => $request->latitude,
            'check_in_lon' => $request->longitude,
            'check_in_address' => $request->address,
            'timezone' => $tz,
            'status' => 'ongoing',
        ]);

        // Audit Log
        AttendanceLog::create([
            'employee_id' => $employee->id,
            'device_pin' => (string) ($employee->deviceMappings()->first()?->device_pin ?? $employee->employee_code),
            'log_time' => $localNow->format('Y-m-d H:i:s'),
            'verify_type' => 'visit',
            'log_type' => 'check_in',
            'raw_payload' => [
                'visit_id' => $visit->id,
                'visit_number' => $visitNumber,
                'client_name' => $visit->client_name,
                'purpose' => $visit->purpose,
                'latitude' => $visit->check_in_lat,
                'longitude' => $visit->check_in_lon,
                'address' => $visit->check_in_address,
                'timezone' => $tz,
                'country' => $branch?->country_name,
            ],
            'hash_signature' => hash('sha256', "visit_in_{$visit->id}_{$localNow->timestamp}"),
            'status' => 'processed',
        ]);

        // Auto clock-in on daily summary if not yet present today
        $summary = AttendanceDailySummary::where('employee_id', $employee->id)
            ->where('date', $localNow->format('Y-m-d'))
            ->first();

        if (!$summary || empty($summary->clock_in)) {
            AttendanceDailySummary::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $localNow->format('Y-m-d'),
                ],
                [
                    'clock_in' => $localNow->format('H:i:s'),
                    'status' => 'present',
                    'notes' => __('Kunjungan Lapangan: :client', ['client' => $visit->client_name]),
                ]
            );
        }

        return redirect()->route('attendance.visits.index')->with('success', __('Check-in kunjungan di ":client" berhasil dicatat pada :time! Jangan lupa lakukan Check-Out setelah kunjungan selesai.', [
            'client' => $visit->client_name,
            'time' => $localNow->format('H:i:s'),
        ]));
    }

    public function checkoutForm(AttendanceVisit $visit)
    {
        $currentUser = auth()->user();

        if ($currentUser->isEmployee() && $visit->employee_id !== $currentUser->employee?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($visit->status !== 'ongoing') {
            return redirect()->route('attendance.visits.index')
                ->with('error', __('Kunjungan ini sudah selesai di-checkout atau tidak berstatus aktif.'));
        }

        $visit->load(['employee.department', 'employee.position', 'branch']);

        return view('attendance.visits.checkout', compact('visit'));
    }

    public function checkOut(Request $request, AttendanceVisit $visit)
    {
        $currentUser = auth()->user();

        // Authorization check
        if ($currentUser->isEmployee() && $visit->employee_id !== $currentUser->employee?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($visit->status !== 'ongoing') {
            return redirect()->route('attendance.visits.index')
                ->with('error', __('Kunjungan ini sudah selesai di-checkout atau tidak berstatus aktif.'));
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'outcome' => 'nullable|string|max:1000',
        ]);

        $tz = $visit->timezone ?: 'Asia/Jakarta';
        $localNow = Carbon::now($tz);

        $durationMinutes = max(1, $visit->check_in_at->diffInMinutes($localNow));

        $visit->update([
            'check_out_at' => $localNow->format('Y-m-d H:i:s'),
            'check_out_lat' => $request->latitude,
            'check_out_lon' => $request->longitude,
            'check_out_address' => $request->address,
            'outcome' => $request->outcome,
            'duration_minutes' => $durationMinutes,
            'status' => 'completed',
        ]);

        // Audit Log
        AttendanceLog::create([
            'employee_id' => $visit->employee_id,
            'device_pin' => (string) ($visit->employee->deviceMappings()->first()?->device_pin ?? $visit->employee->employee_code),
            'log_time' => $localNow->format('Y-m-d H:i:s'),
            'verify_type' => 'visit',
            'log_type' => 'check_out',
            'raw_payload' => [
                'visit_id' => $visit->id,
                'visit_number' => $visit->visit_number,
                'client_name' => $visit->client_name,
                'outcome' => $visit->outcome,
                'duration_minutes' => $durationMinutes,
                'latitude' => $visit->check_out_lat,
                'longitude' => $visit->check_out_lon,
                'address' => $visit->check_out_address,
                'timezone' => $tz,
            ],
            'hash_signature' => hash('sha256', "visit_out_{$visit->id}_{$localNow->timestamp}"),
            'status' => 'processed',
        ]);

        // Update clock_out on daily summary if applicable
        $summary = AttendanceDailySummary::where('employee_id', $visit->employee_id)
            ->where('date', $localNow->format('Y-m-d'))
            ->first();

        if ($summary) {
            $summary->update([
                'clock_out' => $localNow->format('H:i:s'),
            ]);
        }

        return redirect()->route('attendance.visits.index')->with('success', __('Check-out kunjungan ":client" berhasil! Durasi kunjungan: :durasi. Anda sekarang dapat memulai kunjungan baru jika diperlukan.', [
            'client' => $visit->client_name,
            'durasi' => $visit->formatted_duration,
        ]));
    }

    public function cancel(Request $request, AttendanceVisit $visit)
    {
        $currentUser = auth()->user();

        if ($currentUser->isEmployee() && $visit->employee_id !== $currentUser->employee?->id) {
            abort(403, 'Akses ditolak.');
        }

        if ($visit->status !== 'ongoing') {
            return redirect()->route('attendance.visits.index')
                ->with('error', __('Hanya kunjungan yang sedang berlangsung yang dapat dibatalkan.'));
        }

        $visit->update([
            'status' => 'cancelled',
            'outcome' => __('Dibatalkan oleh pengguna: :reason', ['reason' => $request->input('reason', __('Dibatalkan manual'))]),
        ]);

        return redirect()->route('attendance.visits.index')->with('success', __('Kunjungan ke ":client" telah dibatalkan.', ['client' => $visit->client_name]));
    }
}


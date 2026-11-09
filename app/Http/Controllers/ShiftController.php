<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeShiftAssignment;
use App\Models\Holiday;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        // Regular employee: redirect to dashboard or scope
        if ($currentUser && $currentUser->isEmployee()) {
            abort(403, 'Akses terbatas untuk Manajemen dan HR Administrator.');
        }

        $shifts = Shift::withCount('assignments')->get();
        $holidays = Holiday::orderBy('date', 'desc')->get();
        
        $empQuery = Employee::where('status', 'active');
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $assignQuery = EmployeeShiftAssignment::with(['employee', 'shift'])
            ->where('date', 'like', "{$selectedMonth}%");

        if ($search = $request->input('search')) {
            $assignQuery->where(function ($q) use ($search) {
                $q->whereHas('employee', function ($eq) use ($search) {
                    $eq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('employee_code', 'like', "%{$search}%");
                })->orWhereHas('shift', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('code', 'like', "%{$search}%");
                })->orWhere('date', 'like', "%{$search}%");
            });
        }

        if ($shiftId = $request->input('shift_id')) {
            $assignQuery->where('shift_id', $shiftId);
        }

        if ($currentUser && $currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $deptId = $currentUser->employee->department_id;
            $empQuery->where('department_id', $deptId);
            $assignQuery->whereHas('employee', fn($q) => $q->where('department_id', $deptId));
        }

        $employees = $empQuery->get();
        $assignments = $assignQuery->orderBy('date', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('shifts.index', compact('shifts', 'holidays', 'employees', 'assignments', 'selectedMonth'));
    }

    public function create()
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas membuat master shift.');
        }

        return view('shifts.create');
    }

    public function edit(Shift $shift)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit master shift.');
        }

        return view('shifts.edit', compact('shift'));
    }

    public function createAssign()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menetapkan jadwal shift.');
        }

        $empQuery = Employee::where('status', 'active');
        if ($currentUser->role === 'manager' && $currentUser->employee?->department_id) {
            $empQuery->where('department_id', $currentUser->employee->department_id);
        }
        $employees = $empQuery->orderBy('first_name')->orderBy('last_name')->get();
        $shifts = Shift::where('is_active', true)->get();

        return view('shifts.assign.create', compact('employees', 'shifts'));
    }

    public function createHoliday()
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah hari libur.');
        }

        return view('shifts.holiday.create');
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas membuat master shift.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:shifts,code',
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0',
            'color' => 'required|string|max:20',
            'is_overnight' => 'nullable|boolean',
        ]);

        $validated['is_overnight'] = $request->boolean('is_overnight');
        $validated['is_active'] = true;

        $shift = Shift::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create_shift',
            'description' => "Membuat shift baru: {$shift->name} ({$shift->code}).",
        ]);

        return redirect()->route('shifts.index')->with('success', "Shift {$shift->name} berhasil dibuat!");
    }

    public function update(Request $request, Shift $shift)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit master shift.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'late_tolerance_minutes' => 'required|integer|min:0',
            'color' => 'required|string|max:20',
            'is_overnight' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_overnight'] = $request->boolean('is_overnight');
        $validated['is_active'] = $request->boolean('is_active');

        $shift->update($validated);

        return redirect()->route('shifts.index')->with('success', "Shift {$shift->name} berhasil diperbarui.");
    }

    public function destroy(Shift $shift)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menghapus master shift.');
        }

        $name = $shift->name;
        $shift->delete();
        return redirect()->route('shifts.index')->with('success', "Shift {$name} telah dihapus.");
    }

    public function assignShift(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menetapkan jadwal shift.');
        }

        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_day_off' => 'nullable|boolean',
        ]);

        $period = CarbonPeriod::create($validated['start_date'], $validated['end_date']);
        $count = 0;

        foreach ($period as $date) {
            EmployeeShiftAssignment::updateOrCreate(
                [
                    'employee_id' => $validated['employee_id'],
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'shift_id' => $request->boolean('is_day_off') ? null : $validated['shift_id'],
                    'is_day_off' => $request->boolean('is_day_off'),
                ]
            );
            $count++;
        }

        return redirect()->route('shifts.index')->with('success', "Jadwal shift berhasil diterapkan untuk {$count} hari.");
    }

    public function storeHoliday(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah hari libur.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'date' => 'required|date|unique:holidays,date',
            'description' => 'nullable|string',
        ]);

        $validated['is_national'] = true;
        Holiday::create($validated);

        return redirect()->route('shifts.index')->with('success', "Hari libur {$validated['name']} berhasil ditambahkan.");
    }
}

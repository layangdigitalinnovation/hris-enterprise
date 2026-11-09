<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Device;
use App\Models\DeviceEmployeeMapping;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Position;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Regular employee: redirect to own profile
        if ($currentUser && $currentUser->isEmployee()) {
            if ($currentUser->employee) {
                return redirect()->route('employees.show', $currentUser->employee);
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas melihat direktori data karyawan.');
        }

        $query = Employee::with(['department', 'position', 'branch', 'user']);

        // Manager: scope to their department team (except Finance Manager who needs access across departments for payroll)
        if ($currentUser && $currentUser->role === 'manager' && !$currentUser->isFinance() && $currentUser->employee && $currentUser->employee->department_id) {
            $query->where('department_id', $currentUser->employee->department_id);
        }

        // Search by name, code, email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($dept = $request->input('department_id')) {
            $query->where('department_id', $dept);
        }

        // Branch filter
        if ($branch = $request->input('branch_id')) {
            $query->where('branch_id', $branch);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $departments = Department::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('employees.index', compact('employees', 'departments', 'branches'));
    }

    public function create()
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Hanya HR Administrator atau Super Admin yang berhak menambahkan karyawan baru.');
        }
        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $managers = Employee::where('status', 'active')->get();
        $devices = Device::all();

        // Suggest next code
        $lastEmp = Employee::orderBy('id', 'desc')->first();
        $nextNum = $lastEmp ? ($lastEmp->id + 1) : 1;
        $suggestedCode = 'EMP-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        $suggestedPin = (string) (1000 + $nextNum);

        return view('employees.create', compact('departments', 'positions', 'branches', 'managers', 'devices', 'suggestedCode', 'suggestedPin'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Hanya HR Administrator atau Super Admin yang berhak menambahkan karyawan baru.');
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'employee_code' => 'required|string|max:50|unique:employees,employee_code',
            'nik' => 'nullable|string|max:50|unique:employees,nik',
            'email' => 'required|email|max:150|unique:employees,email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'gender' => 'required|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'marital_status' => 'required|in:single,married,widowed,divorced',
            'religion' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'currency' => ['nullable', 'string', Rule::in(['IDR', 'SAR'])],
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:permanent,contract,probation,intern',
            'status' => 'nullable|in:active,inactive,resigned,terminated',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:150',
            'npwp' => 'nullable|string|max:50',
            'device_id' => 'nullable|exists:devices,id',
            'device_pin' => 'nullable|string|max:50',
            'create_user_account' => 'nullable|boolean',
            'user_role' => 'nullable|in:super_admin,hr_admin,finance,manager,employee',
        ], [
            'nik.unique' => 'Nomor KTP (NIK) ":input" sudah terdaftar pada karyawan lain. Harap periksa kembali atau gunakan NIK yang benar.',
            'employee_code.unique' => 'ID Karyawan (Nomor Induk) ":input" sudah digunakan. Harap gunakan ID Karyawan yang berbeda.',
            'email.unique' => 'Alamat email ":input" sudah terdaftar di sistem. Harap gunakan alamat email yang berbeda.',
            'first_name.required' => 'Nama depan wajib diisi.',
            'employee_code.required' => 'Nomor Induk Karyawan wajib diisi.',
            'email.required' => 'Alamat email resmi wajib diisi.',
            'email.email' => 'Format penulisan alamat email tidak valid.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'branch_id.required' => 'Cabang / lokasi kerja wajib dipilih.',
            'department_id.required' => 'Departemen / divisi wajib dipilih.',
            'position_id.required' => 'Jabatan (posisi) wajib dipilih.',
            'join_date.required' => 'Tanggal bergabung wajib diisi.',
            'employment_status.required' => 'Status ikatan kerja wajib dipilih.',
            'marital_status.required' => 'Status pernikahan wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $userId = null;
            if ($request->boolean('create_user_account')) {
                $lastName = $validated['last_name'] ?? '';
                $name = trim($validated['first_name'] . ($lastName !== '' ? " {$lastName}" : ''));

                $user = User::create([
                    'name' => $name,
                    'email' => $validated['email'],
                    'password' => Hash::make('password'), // default password
                    'role' => $validated['user_role'] ?? 'employee',
                    'phone' => $validated['phone'] ?? null,
                    'is_active' => true,
                ]);
                $userId = $user->id;
            }

            $employee = Employee::create([
                'user_id' => $userId,
                'employee_code' => $validated['employee_code'],
                'nik' => $validated['nik'] ?? null,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'marital_status' => $validated['marital_status'],
                'religion' => $validated['religion'] ?? null,
                'address' => $validated['address'] ?? null,
                'branch_id' => $validated['branch_id'],
                'currency' => $validated['currency'] ?? null,
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'manager_id' => $validated['manager_id'] ?? null,
                'join_date' => $validated['join_date'],
                'employment_status' => $validated['employment_status'],
                'status' => $validated['status'] ?? 'active',
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'bank_account_holder' => $validated['bank_account_holder'] ?? null,
                'npwp' => $validated['npwp'] ?? null,
            ]);

            // Device Pin Mapping
            if (!empty($validated['device_id']) && !empty($validated['device_pin'])) {
                // Delete previous conflicting mapping on this device to prevent unique constraint error
                DeviceEmployeeMapping::where('device_id', $validated['device_id'])
                    ->where(function ($q) use ($validated, $employee) {
                        $q->where('device_pin', $validated['device_pin'])
                          ->orWhere('employee_id', $employee->id);
                    })
                    ->delete();

                DeviceEmployeeMapping::create([
                    'device_id' => $validated['device_id'],
                    'employee_id' => $employee->id,
                    'device_pin' => $validated['device_pin'],
                    'privilege' => 'user',
                    'enrolled_at' => now(),
                ]);

                // Retroactively link existing attendance logs that have this PIN to the new employee!
                \App\Models\AttendanceLog::where('device_id', $validated['device_id'])
                    ->where('device_pin', $validated['device_pin'])
                    ->whereNull('employee_id')
                    ->update(['employee_id' => $employee->id]);

                // Recalculate daily attendance engine
                app(\App\Services\Attendance\AttendanceEngineService::class)->processDailyAttendance(\Carbon\Carbon::today());
            }

            // Default Annual Leave Balance
            $annualLeave = LeaveType::where('code', 'CT')->first();
            if ($annualLeave) {
                LeaveBalance::create([
                    'employee_id' => $employee->id,
                    'leave_type_id' => $annualLeave->id,
                    'year' => date('Y'),
                    'initial_quota' => $annualLeave->default_quota,
                    'used_quota' => 0,
                    'remaining_quota' => $annualLeave->default_quota,
                ]);
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'create_employee',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'description' => "Menambahkan karyawan baru {$employee->full_name} ({$employee->employee_code}).",
            ]);

            DB::commit();

            return redirect()->route('employees.show', $employee)->with('success', "Karyawan {$employee->full_name} berhasil ditambahkan!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $this->formatDatabaseErrorMessage($e, 'menambahkan'));
        }
    }

    public function show(Employee $employee)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Regular employee can ONLY view own profile
        if ($currentUser && $currentUser->isEmployee()) {
            if (!$currentUser->employee || $currentUser->employee->id !== $employee->id) {
                abort(403, 'Akses ditolak. Anda hanya diizinkan melihat profil Anda sendiri.');
            }
        }

        // Manager can only view employees in their own department (or themselves), except Finance Manager
        if ($currentUser && $currentUser->role === 'manager' && !$currentUser->isFinance() && $currentUser->employee && $currentUser->employee->department_id) {
            if ($employee->department_id !== $currentUser->employee->department_id && $employee->id !== $currentUser->employee->id) {
                abort(403, 'Akses ditolak. Anda hanya dapat melihat data karyawan dalam departemen Anda.');
            }
        }

        $employee->load([
            'department',
            'position',
            'branch',
            'manager',
            'user',
            'deviceMappings.device',
            'leaveBalances.leaveType',
            'documents',
            'compensations.component'
        ]);

        $recentAttendance = $employee->attendanceSummaries()
            ->with('shift')
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        $availableSalaryComponents = ($currentUser && $currentUser->canManagePayroll())
            ? SalaryComponent::where('is_active', true)->orderBy('type')->orderBy('name')->get()
            : collect();

        return view('employees.show', compact('employee', 'recentAttendance', 'availableSalaryComponents'));
    }

    public function edit(Employee $employee)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Hanya HR Administrator atau Super Admin yang berhak mengedit data karyawan.');
        }

        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $managers = Employee::where('status', 'active')->where('id', '!=', $employee->id)->get();
        $devices = Device::all();

        $currentMapping = $employee->deviceMappings()->first();

        return view('employees.edit', compact('employee', 'departments', 'positions', 'branches', 'managers', 'devices', 'currentMapping'));
    }

    public function update(Request $request, Employee $employee)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Hanya HR Administrator atau Super Admin yang berhak memperbarui data karyawan.');
        }
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'employee_code' => ['required', 'string', 'max:50', Rule::unique('employees')->ignore($employee->id)],
            'nik' => ['nullable', 'string', 'max:50', Rule::unique('employees')->ignore($employee->id)],
            'email' => ['required', 'email', 'max:150', Rule::unique('employees')->ignore($employee->id)],
            'phone' => 'nullable|string|max:30',
            'gender' => 'required|in:male,female',
            'birth_place' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'marital_status' => 'required|in:single,married,widowed,divorced',
            'religion' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'currency' => ['nullable', 'string', Rule::in(['IDR', 'SAR'])],
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'manager_id' => 'nullable|exists:employees,id',
            'join_date' => 'required|date',
            'employment_status' => 'required|in:permanent,contract,probation,intern',
            'status' => 'required|in:active,inactive,resigned,terminated',
            'bank_name' => 'nullable|string|max:50',
            'bank_account_no' => 'nullable|string|max:50',
            'bank_account_holder' => 'nullable|string|max:150',
            'npwp' => 'nullable|string|max:50',
            'device_id' => 'nullable|exists:devices,id',
            'device_pin' => 'nullable|string|max:50',
        ], [
            'nik.unique' => 'Nomor KTP (NIK) ":input" sudah terdaftar pada karyawan lain. Harap periksa kembali atau gunakan NIK yang benar.',
            'employee_code.unique' => 'ID Karyawan (Nomor Induk) ":input" sudah digunakan oleh karyawan lain.',
            'email.unique' => 'Alamat email ":input" sudah terdaftar pada akun karyawan lain.',
            'first_name.required' => 'Nama depan wajib diisi.',
            'employee_code.required' => 'Nomor Induk Karyawan wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format penulisan alamat email tidak valid.',
            'marital_status.required' => 'Status pernikahan wajib dipilih.',
        ]);

        DB::beginTransaction();
        try {
            $employee->update($validated);

            if ($employee->user) {
                $lastName = $validated['last_name'] ?? '';
                $name = trim($validated['first_name'] . ($lastName !== '' ? " {$lastName}" : ''));
                $employee->user->update([
                    'name' => $name,
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                ]);
            }

            // Update Device mapping
            if (!empty($validated['device_id']) && !empty($validated['device_pin'])) {
                DeviceEmployeeMapping::where('device_id', $validated['device_id'])
                    ->where(function ($q) use ($validated, $employee) {
                        $q->where('device_pin', $validated['device_pin'])
                          ->where('employee_id', '!=', $employee->id);
                    })
                    ->delete();

                DeviceEmployeeMapping::updateOrCreate(
                    [
                        'device_id' => $validated['device_id'],
                        'employee_id' => $employee->id,
                    ],
                    [
                        'device_pin' => $validated['device_pin'],
                        'enrolled_at' => now(),
                    ]
                );

                // Retroactively link existing attendance logs that have this PIN to the employee!
                \App\Models\AttendanceLog::where('device_id', $validated['device_id'])
                    ->where('device_pin', $validated['device_pin'])
                    ->whereNull('employee_id')
                    ->update(['employee_id' => $employee->id]);

                app(\App\Services\Attendance\AttendanceEngineService::class)->processDailyAttendance(\Carbon\Carbon::today());
            }

            AuditLog::create([
                'user_id' => auth()->id(),
                'event' => 'update_employee',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'description' => "Memperbarui data karyawan {$employee->full_name}.",
            ]);

            DB::commit();

            return redirect()->route('employees.show', $employee)->with('success', 'Data karyawan berhasil diperbarui!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $this->formatDatabaseErrorMessage($e, 'memperbarui'));
        }
    }

    /**
     * Convert technical database exceptions into user-friendly Indonesian messages.
     */
    protected function formatDatabaseErrorMessage(\Throwable $e, string $action = 'menambahkan'): string
    {
        $message = $e->getMessage();

        // Duplicate entry (MySQL Error 1062 / SQLSTATE 23000)
        if (str_contains($message, 'Duplicate entry') || str_contains($message, '1062') || $e->getCode() == 23000) {
            if (str_contains($message, 'employees_nik_unique') || str_contains($message, 'nik')) {
                preg_match("/Duplicate entry '([^']+)'/", $message, $matches);
                $val = $matches[1] ?? '';
                return "Nomor KTP (NIK) " . ($val ? "\"{$val}\" " : '') . "sudah terdaftar pada karyawan lain. Mohon gunakan NIK yang benar atau periksa data karyawan yang sudah ada.";
            }

            if (str_contains($message, 'employees_employee_code_unique') || str_contains($message, 'employee_code')) {
                preg_match("/Duplicate entry '([^']+)'/", $message, $matches);
                $val = $matches[1] ?? '';
                return "ID Karyawan (Nomor Induk) " . ($val ? "\"{$val}\" " : '') . "sudah digunakan. Mohon gunakan ID Karyawan yang berbeda.";
            }

            if (str_contains($message, 'employees_email_unique') || str_contains($message, 'users_email_unique') || str_contains($message, 'email')) {
                preg_match("/Duplicate entry '([^']+)'/", $message, $matches);
                $val = $matches[1] ?? '';
                return "Alamat email " . ($val ? "\"{$val}\" " : '') . "sudah terdaftar di sistem. Mohon gunakan alamat email yang berbeda.";
            }

            if (str_contains($message, 'device_employee_mappings') || str_contains($message, 'device_pin')) {
                return "Nomor PIN mesin absensi ini sudah terhubung dengan data karyawan lain pada perangkat tersebut.";
            }

            preg_match("/Duplicate entry '([^']+)'/", $message, $matches);
            $val = $matches[1] ?? '';
            return "Data " . ($val ? "\"{$val}\" " : '') . "sudah terdaftar sebelumnya di dalam sistem (data duplikat). Mohon periksa kembali isian Anda.";
        }

        if ($e instanceof \Illuminate\Database\QueryException) {
            return "Terjadi kendala penyimpanan ke database. Mohon pastikan seluruh data yang dimasukkan sudah sesuai.";
        }

        return "Gagal {$action} karyawan: " . $e->getMessage();
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->full_name;
        $employee->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'delete_employee',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'description' => "Menghapus (soft delete) karyawan {$name}.",
        ]);

        return redirect()->route('employees.index')->with('success', "Karyawan {$name} berhasil dinonaktifkan.");
    }

    public function uploadDocument(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'document_type' => 'required|string|max:50',
            'document_file' => 'required|file|max:10240', // max 10MB
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $file = $request->file('document_file');
        $originalName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $path = $file->store("employee-documents/{$employee->id}", 'public');

        $doc = $employee->documents()->create([
            'title' => $validated['title'],
            'document_type' => $validated['document_type'],
            'file_path' => $path,
            'file_name' => $originalName,
            'file_size' => $fileSize,
            'expiry_date' => $validated['expiry_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'upload_document',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'description' => "Mengunggah dokumen '{$doc->title}' ({$doc->document_type}) untuk {$employee->full_name}.",
        ]);

        return redirect()->route('employees.show', ['employee' => $employee, 'tab' => 'documents'])->with('success', "Dokumen '{$doc->title}' berhasil diunggah!");
    }

    public function deleteDocument(Employee $employee, \App\Models\EmployeeDocument $document)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Hanya HR Administrator atau Super Admin yang berhak menghapus arsip dokumen.');
        }

        if ($document->employee_id !== $employee->id) {
            abort(403);
        }

        \Illuminate\Support\Facades\Storage::disk('public')->delete($document->file_path);
        $title = $document->title;
        $document->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'delete_document',
            'auditable_type' => Employee::class,
            'auditable_id' => $employee->id,
            'description' => "Menghapus dokumen '{$title}' milik {$employee->full_name}.",
        ]);

        return redirect()->route('employees.show', ['employee' => $employee, 'tab' => 'documents'])->with('success', "Dokumen '{$title}' telah dihapus.");
    }

    public function downloadDocument(Employee $employee, \App\Models\EmployeeDocument $document)
    {
        if ($document->employee_id !== $employee->id) {
            abort(403);
        }

        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Regular employee can ONLY download their own documents
        if ($currentUser && $currentUser->isEmployee()) {
            if (!$currentUser->employee || $currentUser->employee->id !== $employee->id) {
                abort(403, 'Akses ditolak. Anda hanya dapat mengunduh dokumen milik Anda sendiri.');
            }
        }

        $filePath = storage_path("app/public/{$document->file_path}");
        if (!file_exists($filePath)) {
            return back()->with('error', 'File dokumen tidak ditemukan pada penyimpanan.');
        }

        return response()->download($filePath, $document->file_name);
    }

    public function showResetPasswordForm(Employee $employee)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses terbatas untuk Administrator.');
        }

        if ($employee->user && $employee->user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Administrator yang dapat mereset akun sesama Super Admin.');
        }

        return view('employees.reset_password', compact('employee'));
    }

    public function resetPassword(Request $request, Employee $employee)
    {
        /** @var User $currentUser */
        $currentUser = auth()->user();

        // Ensure user is authorized: Super Admin or HR Admin
        if (!$currentUser || !$currentUser->isHrAdmin()) {
            abort(403, 'Akses terbatas untuk Administrator.');
        }

        // If target is super_admin and current user is not super_admin, block
        if ($employee->user && $employee->user->isSuperAdmin() && !$currentUser->isSuperAdmin()) {
            abort(403, 'Hanya Super Administrator yang dapat mereset akun sesama Super Admin.');
        }

        $validated = $request->validate([
            'mode' => 'required|in:default,random,custom',
            'custom_password' => 'nullable|string|min:6',
            'role' => 'nullable|in:super_admin,hr_admin,finance,manager,employee',
            'is_active' => 'nullable|boolean',
        ]);

        // Determine new plain password
        if ($validated['mode'] === 'default') {
            $plainPassword = 'password';
        } elseif ($validated['mode'] === 'random') {
            $plainPassword = 'Hris#' . rand(1000, 9999);
        } else {
            if (empty($validated['custom_password']) || strlen(trim($validated['custom_password'])) < 6) {
                return back()->with('error', 'Password kustom harus diisi minimal 6 karakter.');
            }
            $plainPassword = trim($validated['custom_password']);
        }

        DB::beginTransaction();
        try {
            $user = $employee->user;
            $isNewAccount = false;

            if (!$user) {
                // If user doesn't exist, check by email or create new
                $existingUserByEmail = User::where('email', $employee->email)->first();
                if ($existingUserByEmail) {
                    $user = $existingUserByEmail;
                    $user->update([
                        'password' => Hash::make($plainPassword),
                        'is_active' => true,
                        'role' => $validated['role'] ?? $user->role,
                    ]);
                } else {
                    $user = User::create([
                        'name' => $employee->full_name,
                        'email' => $employee->email,
                        'password' => Hash::make($plainPassword),
                        'role' => $validated['role'] ?? 'employee',
                        'phone' => $employee->phone,
                        'is_active' => true,
                    ]);
                    $isNewAccount = true;
                }

                $employee->update(['user_id' => $user->id]);
            } else {
                $updateData = [
                    'password' => Hash::make($plainPassword),
                    'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
                ];

                if (!empty($validated['role'])) {
                    $updateData['role'] = $validated['role'];
                }

                $user->update($updateData);
            }

            AuditLog::create([
                'user_id' => $currentUser->id,
                'event' => 'reset_password',
                'auditable_type' => User::class,
                'auditable_id' => $user->id,
                'description' => "Administrator {$currentUser->name} mereset password akun login karyawan {$employee->full_name} ({$user->email}).",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->route('employees.show', $employee)->with('success', "Password akun {$employee->full_name} berhasil direset!")
                ->with('reset_credential', [
                    'name' => $employee->full_name,
                    'email' => $user->email,
                    'password' => $plainPassword,
                    'role' => $user->role,
                    'is_new_account' => $isNewAccount,
                    'login_url' => route('login'),
                ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }
}

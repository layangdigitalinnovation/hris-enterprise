<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = auth()->user();
        if ($currentUser && $currentUser->isEmployee()) {
            abort(403, 'Akses terbatas untuk Manajemen dan HR Administrator.');
        }

        $branches = Branch::withCount('employees')
            ->orderBy('name')
            ->paginate(6, ['*'], 'branch_page')
            ->appends(array_merge($request->except('branch_page'), ['tab' => 'branches']));

        $departments = Department::withCount('employees')
            ->with(['manager', 'positions'])
            ->orderBy('name')
            ->paginate(6, ['*'], 'dept_page')
            ->appends(array_merge($request->except('dept_page'), ['tab' => 'departments']));

        $posQuery = Position::withCount('employees')->with('department');

        if ($posSearch = $request->input('pos_search')) {
            $posQuery->where(function ($q) use ($posSearch) {
                $q->where('title', 'like', "%{$posSearch}%")
                  ->orWhere('code', 'like', "%{$posSearch}%")
                  ->orWhereHas('department', function ($dq) use ($posSearch) {
                      $dq->where('name', 'like', "%{$posSearch}%")
                         ->orWhere('code', 'like', "%{$posSearch}%");
                  });
            });
        }

        $positions = $posQuery->orderBy('level')
            ->orderBy('title')
            ->paginate(5, ['*'], 'pos_page')
            ->appends(array_merge($request->except('pos_page'), ['tab' => 'positions']));

        $employees = Employee::where('status', 'active')->get();

        return view('organization.index', compact('branches', 'departments', 'positions', 'employees'));
    }

    public function createBranch()
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah cabang.');
        }

        return view('organization.branch.create');
    }

    public function storeBranch(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah cabang.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:branches,code',
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => ['required', 'string', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Riyadh'])],
            'currency' => ['nullable', 'string', Rule::in(['IDR', 'SAR'])],
            'country_code' => ['nullable', 'string', Rule::in(['ID', 'SA'])],
            'radius_meters' => 'required|integer|min:10|max:50000',
        ]);

        if (empty($validated['currency'])) {
            $validated['currency'] = ($validated['timezone'] === 'Asia/Riyadh') ? 'SAR' : 'IDR';
        }
        if (empty($validated['country_code'])) {
            $validated['country_code'] = ($validated['timezone'] === 'Asia/Riyadh') ? 'SA' : 'ID';
        }

        $validated['is_active'] = true;
        $branch = Branch::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create_branch',
            'description' => "Menambahkan kantor cabang: {$branch->name} ({$branch->code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'branches'])->with('success', "Cabang baru '{$validated['name']}' berhasil ditambahkan.");
    }

    public function editBranch(Branch $branch)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit cabang.');
        }

        return view('organization.branch.edit', compact('branch'));
    }

    public function updateBranch(Request $request, Branch $branch)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit cabang.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('branches', 'code')->ignore($branch->id)],
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'timezone' => ['required', 'string', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Riyadh'])],
            'currency' => ['nullable', 'string', Rule::in(['IDR', 'SAR'])],
            'country_code' => ['nullable', 'string', Rule::in(['ID', 'SA'])],
            'radius_meters' => 'required|integer|min:10|max:50000',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['currency'])) {
            $validated['currency'] = ($validated['timezone'] === 'Asia/Riyadh') ? 'SAR' : 'IDR';
        }
        if (empty($validated['country_code'])) {
            $validated['country_code'] = ($validated['timezone'] === 'Asia/Riyadh') ? 'SA' : 'ID';
        }

        $validated['is_active'] = $request->boolean('is_active');
        $branch->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'update_branch',
            'description' => "Memperbarui kantor cabang: {$branch->name} ({$branch->code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'branches'])->with('success', "Kantor Cabang '{$branch->name}' berhasil diperbarui.");
    }

    public function destroyBranch(Branch $branch)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menghapus cabang.');
        }

        $employeesCount = $branch->employees()->count();
        if ($employeesCount > 0) {
            return redirect()->route('organization.index', ['tab' => 'branches'])
                ->with('error', "Kantor Cabang '{$branch->name}' tidak dapat dihapus karena masih memiliki {$employeesCount} karyawan aktif yang terhubung.");
        }

        $devicesCount = $branch->devices()->count();
        if ($devicesCount > 0) {
            return redirect()->route('organization.index', ['tab' => 'branches'])
                ->with('error', "Kantor Cabang '{$branch->name}' tidak dapat dihapus karena masih terhubung dengan {$devicesCount} mesin absensi / biometric device.");
        }

        $name = $branch->name;
        $code = $branch->code;
        $branch->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'delete_branch',
            'description' => "Menghapus kantor cabang: {$name} ({$code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'branches'])->with('success', "Kantor Cabang '{$name}' berhasil dihapus.");
    }

    public function createDepartment()
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah departemen.');
        }

        $employees = Employee::where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get();

        return view('organization.department.create', compact('employees'));
    }

    public function storeDepartment(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah departemen.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:departments,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
        ]);

        $validated['is_active'] = true;
        $department = Department::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create_department',
            'description' => "Menambahkan departemen: {$department->name} ({$department->code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'departments'])->with('success', "Departemen '{$validated['name']}' berhasil ditambahkan.");
    }

    public function editDepartment(Department $department)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit departemen.');
        }

        $employees = Employee::where('status', 'active')->orderBy('first_name')->orderBy('last_name')->get();

        return view('organization.department.edit', compact('department', 'employees'));
    }

    public function updateDepartment(Request $request, Department $department)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit departemen.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('departments', 'code')->ignore($department->id)],
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'manager_id' => 'nullable|exists:employees,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $department->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'update_department',
            'description' => "Memperbarui departemen: {$department->name} ({$department->code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'departments'])->with('success', "Departemen '{$department->name}' berhasil diperbarui.");
    }

    public function destroyDepartment(Department $department)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menghapus departemen.');
        }

        $employeesCount = $department->employees()->count();
        if ($employeesCount > 0) {
            return redirect()->route('organization.index', ['tab' => 'departments'])
                ->with('error', "Departemen '{$department->name}' tidak dapat dihapus karena masih memiliki {$employeesCount} karyawan aktif yang terhubung.");
        }

        $positionsCount = $department->positions()->count();
        if ($positionsCount > 0) {
            return redirect()->route('organization.index', ['tab' => 'departments'])
                ->with('error', "Departemen '{$department->name}' tidak dapat dihapus karena masih memiliki {$positionsCount} jabatan terkait.");
        }

        $childrenCount = $department->children()->count();
        if ($childrenCount > 0) {
            return redirect()->route('organization.index', ['tab' => 'departments'])
                ->with('error', "Departemen '{$department->name}' tidak dapat dihapus karena masih memiliki {$childrenCount} sub-departemen terkait.");
        }

        $name = $department->name;
        $code = $department->code;
        $department->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'delete_department',
            'description' => "Menghapus departemen: {$name} ({$code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'departments'])->with('success', "Departemen '{$name}' berhasil dihapus.");
    }

    public function createPosition()
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah jabatan.');
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('organization.position.create', compact('departments'));
    }

    public function storePosition(Request $request)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menambah jabatan.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:positions,code',
            'title' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'level' => 'required|integer|min:1|max:5',
        ]);

        $validated['is_active'] = true;
        $position = Position::create($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'create_position',
            'description' => "Menambahkan jabatan: {$position->title} ({$position->code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'positions'])->with('success', "Jabatan '{$validated['title']}' berhasil ditambahkan.");
    }

    public function editPosition(Position $position)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit jabatan.');
        }

        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('organization.position.edit', compact('position', 'departments'));
    }

    public function updatePosition(Request $request, Position $position)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas mengedit jabatan.');
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('positions', 'code')->ignore($position->id)],
            'title' => 'required|string|max:100',
            'department_id' => 'required|exists:departments,id',
            'level' => 'required|integer|min:1|max:5',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $position->update($validated);

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'update_position',
            'description' => "Memperbarui jabatan: {$position->title} ({$position->code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'positions'])->with('success', "Jabatan '{$position->title}' berhasil diperbarui.");
    }

    public function destroyPosition(Position $position)
    {
        if (!auth()->user()->canManageEmployees()) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otoritas menghapus jabatan.');
        }

        $employeesCount = $position->employees()->count();
        if ($employeesCount > 0) {
            return redirect()->route('organization.index', ['tab' => 'positions'])
                ->with('error', "Jabatan '{$position->title}' tidak dapat dihapus karena masih digunakan oleh {$employeesCount} karyawan aktif.");
        }

        $title = $position->title;
        $code = $position->code;
        $position->delete();

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'delete_position',
            'description' => "Menghapus jabatan: {$title} ({$code}).",
        ]);

        return redirect()->route('organization.index', ['tab' => 'positions'])->with('success', "Jabatan '{$title}' berhasil dihapus.");
    }
}

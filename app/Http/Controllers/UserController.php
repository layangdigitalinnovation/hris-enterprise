<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PermissionGroup;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users with Administrator vs Employee tabs.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'admin'); // 'admin' or 'employee'
        $search = $request->query('search');
        $roleFilter = $request->query('role');
        $view = $request->query('view', 'table'); // 'table' or 'card'

        $baseQuery = User::with(['roleModel', 'employee.branch', 'employee.department', 'branches', 'departments']);

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter) {
            $baseQuery->where(function ($q) use ($roleFilter) {
                $q->where('role', $roleFilter)
                  ->orWhere('role_id', $roleFilter);
            });
        }

        // Counts for tabs
        $totalAdminCount = User::where('role', '!=', 'employee')->count();
        $totalEmployeeCount = User::where('role', 'employee')->count();

        // Filter by tab
        if ($tab === 'employee') {
            $baseQuery->where('role', 'employee');
        } else {
            $baseQuery->where('role', '!=', 'employee');
        }

        $users = $baseQuery->latest('id')->paginate(15)->withQueryString();

        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        // Employees not yet assigned to an active user (plus currently editing user's employee if applicable)
        $availableEmployees = Employee::with(['branch', 'department'])
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('users.index', compact(
            'users',
            'tab',
            'search',
            'roleFilter',
            'view',
            'totalAdminCount',
            'totalEmployeeCount',
            'roles',
            'branches',
            'departments',
            'availableEmployees'
        ));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $availableEmployees = Employee::with(['branch', 'department'])
            ->where('status', 'active')
            ->whereNull('user_id')
            ->orderBy('first_name')
            ->get();

        return view('users.create', compact('roles', 'branches', 'departments', 'availableEmployees'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'all_branches' => ['nullable', 'boolean'],
            'all_departments' => ['nullable', 'boolean'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'] ?: strtolower(explode('@', $validated['email'])[0]),
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $role->id,
            'role' => $role->key,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'all_branches' => $request->boolean('all_branches', true),
            'all_departments' => $request->boolean('all_departments', true),
        ]);

        // Sync role pivot
        $user->roles()->sync([$role->id]);

        // Branch and Department scopes
        if (!$user->all_branches && !empty($validated['branch_ids'])) {
            $user->branches()->sync($validated['branch_ids']);
        } else {
            $user->branches()->detach();
        }

        if (!$user->all_departments && !empty($validated['department_ids'])) {
            $user->departments()->sync($validated['department_ids']);
        } else {
            $user->departments()->detach();
        }

        // Connect employee if provided
        if (!empty($validated['employee_id'])) {
            Employee::where('id', $validated['employee_id'])->update(['user_id' => $user->id]);
        }

        return redirect()->route('users.index', ['tab' => $role->key === 'employee' ? 'employee' : 'admin'])
            ->with('success', __('User berhasil dibuat dan hak akses telah disinkronkan.'));
    }

    /**
     * Display the specified user as JSON for viewing/modal.
     */
    public function show(User $user)
    {
        $user->load(['roleModel', 'employee.branch', 'employee.department', 'branches', 'departments', 'permissions']);
        return response()->json([
            'success' => true,
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $user->load(['roleModel', 'employee.branch', 'employee.department', 'branches', 'departments']);
        $roles = Role::orderBy('name')->get();
        $branches = Branch::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $availableEmployees = Employee::with(['branch', 'department'])
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $user->id);
            })
            ->orderBy('first_name')
            ->get();

        return view('users.edit', compact('user', 'roles', 'branches', 'departments', 'availableEmployees'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'all_branches' => ['nullable', 'boolean'],
            'all_departments' => ['nullable', 'boolean'],
            'branch_ids' => ['nullable', 'array'],
            'branch_ids.*' => ['exists:branches,id'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['exists:departments,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        $updateData = [
            'name' => $validated['name'],
            'username' => $validated['username'] ?: strtolower(explode('@', $validated['email'])[0]),
            'email' => $validated['email'],
            'role_id' => $role->id,
            'role' => $role->key,
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'all_branches' => $request->boolean('all_branches', true),
            'all_departments' => $request->boolean('all_departments', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Sync role pivot
        $user->roles()->sync([$role->id]);

        // Branch and Department scopes
        if (!$user->all_branches && !empty($validated['branch_ids'])) {
            $user->branches()->sync($validated['branch_ids']);
        } else {
            $user->branches()->detach();
        }

        if (!$user->all_departments && !empty($validated['department_ids'])) {
            $user->departments()->sync($validated['department_ids']);
        } else {
            $user->departments()->detach();
        }

        // Manage connected employee
        if (array_key_exists('employee_id', $validated)) {
            // Disconnect old employee if any
            Employee::where('user_id', $user->id)->update(['user_id' => null]);
            if (!empty($validated['employee_id'])) {
                Employee::where('id', $validated['employee_id'])->update(['user_id' => $user->id]);
            }
        }

        return redirect()->back()
            ->with('success', __('Data user berhasil diperbarui.'));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->back()->with('error', __('Anda tidak dapat menghapus akun Anda sendiri.'));
        }

        if ($user->id === 1 || ($user->role === 'super_admin' && User::where('role', 'super_admin')->count() <= 1)) {
            return redirect()->back()->with('error', __('Akun Super Administrator utama dilindungi dan tidak dapat dihapus.'));
        }

        // Unlink employee
        Employee::where('user_id', $user->id)->update(['user_id' => null]);

        $user->delete();

        return redirect()->back()->with('success', __('Akun user berhasil dihapus.'));
    }

    /**
     * Display the custom permissions matrix for a specific user.
     */
    public function permissions(User $user)
    {
        $user->load(['roleModel.permissions', 'permissions']);
        $groups = PermissionGroup::with('permissions')->orderBy('sort_order')->get();
        $userDirectPermissionIds = $user->permissions->pluck('id')->toArray();
        $rolePermissionIds = $user->roleModel ? $user->roleModel->permissions->pluck('id')->toArray() : [];

        return view('users.permissions', compact('user', 'groups', 'userDirectPermissionIds', 'rolePermissionIds'));
    }

    /**
     * Update custom permissions for a specific user.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $permissionIds = $request->input('permission_ids', []);
        $user->permissions()->sync($permissionIds);

        return redirect()->route('users.index')
            ->with('success', __('Hak akses khusus pengguna :name berhasil disimpan.', ['name' => $user->name]));
    }
}


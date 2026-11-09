<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $view = $request->query('view', 'table'); // 'table' or 'card'

        $query = Role::withCount(['permissions', 'users']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->orderBy('is_system', 'desc')->orderBy('name')->get();
        $totalRolesCount = Role::count();
        $totalPermissionsCount = Permission::count();

        return view('roles.index', compact('roles', 'search', 'view', 'totalRolesCount', 'totalPermissionsCount'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['nullable', 'string', 'max:100', 'unique:roles,key'],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $key = $validated['key'] ?: Str::snake(strtolower($validated['name']));

        $role = Role::create([
            'name' => $validated['name'],
            'key' => $key,
            'guard_name' => $validated['guard_name'] ?? 'web',
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        return redirect()->route('roles.index')
            ->with('success', __('Role baru ":name" berhasil ditambahkan.', ['name' => $role->name]));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        return view('roles.edit', compact('role'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ];

        if (!$role->is_system) {
            $rules['key'] = ['nullable', 'string', 'max:100', Rule::unique('roles', 'key')->ignore($role->id)];
        }

        $validated = $request->validate($rules);

        $updateData = [
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
            'description' => $validated['description'] ?? null,
        ];

        if (!$role->is_system && !empty($validated['key'])) {
            $updateData['key'] = Str::snake(strtolower($validated['key']));
        }

        $role->update($updateData);

        return redirect()->route('roles.index')
            ->with('success', __('Role ":name" berhasil diperbarui.', ['name' => $role->name]));
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return redirect()->back()->with('error', __('Role sistem bawaan dilindungi dan tidak dapat dihapus.'));
        }

        if ($role->users()->count() > 0) {
            return redirect()->back()->with('error', __('Tidak dapat menghapus role yang masih digunakan oleh :count pengguna.', ['count' => $role->users()->count()]));
        }

        $roleName = $role->name;
        $role->permissions()->detach();
        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', __('Role ":name" berhasil dihapus.', ['name' => $roleName]));
    }

    /**
     * Show the permissions matrix configuration view for a specific role.
     */
    public function permissions(Role $role)
    {
        $role->load('permissions');
        $groups = PermissionGroup::with('permissions')->orderBy('sort_order')->get();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();
        $totalAvailablePermissions = Permission::count();

        return view('roles.permissions', compact('role', 'groups', 'rolePermissionIds', 'totalAvailablePermissions'));
    }

    /**
     * Save the permissions matrix for a role.
     */
    public function updatePermissions(Request $request, Role $role)
    {
        $permissionIds = $request->input('permission_ids', []);
        
        $role->permissions()->sync($permissionIds);

        return redirect()->route('roles.index')
            ->with('success', __('Wewenang hak akses untuk role ":name" berhasil diperbarui (:count permission aktif).', [
                'name' => $role->name,
                'count' => count($permissionIds),
            ]));
    }
}

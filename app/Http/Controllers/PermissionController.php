<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions with search and group filter.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $groupId = $request->query('group_id');

        $query = Permission::with('group');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($groupId) {
            $query->where('permission_group_id', $groupId);
        }

        $permissions = $query->orderBy('permission_group_id')->orderBy('name')->paginate(15)->withQueryString();
        $groups = PermissionGroup::orderBy('name')->get();
        $totalPermissionsCount = Permission::count();

        return view('permissions.index', compact('permissions', 'groups', 'search', 'groupId', 'totalPermissionsCount'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create(Request $request)
    {
        $groupId = $request->query('group_id');
        $groups = PermissionGroup::orderBy('name')->get();

        return view('permissions.create', compact('groups', 'groupId'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name'],
            'display_name' => ['required', 'string', 'max:255'],
            'permission_group_id' => ['required', 'exists:permission_groups,id'],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $permission = Permission::create([
            'name' => strtolower($validated['name']),
            'display_name' => $validated['display_name'],
            'permission_group_id' => $validated['permission_group_id'],
            'guard_name' => $validated['guard_name'] ?? 'web',
            'description' => $validated['description'] ?? $validated['display_name'],
        ]);

        return redirect()->route('permissions.index')
            ->with('success', __('Permission ":name" berhasil ditambahkan.', ['name' => $permission->name]));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        $groups = PermissionGroup::orderBy('name')->get();
        return view('permissions.edit', compact('permission', 'groups'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('permissions', 'name')->ignore($permission->id)],
            'display_name' => ['required', 'string', 'max:255'],
            'permission_group_id' => ['required', 'exists:permission_groups,id'],
            'guard_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $permission->update([
            'name' => strtolower($validated['name']),
            'display_name' => $validated['display_name'],
            'permission_group_id' => $validated['permission_group_id'],
            'guard_name' => $validated['guard_name'] ?? 'web',
            'description' => $validated['description'] ?? $validated['display_name'],
        ]);

        return redirect()->route('permissions.index')
            ->with('success', __('Permission ":name" berhasil diperbarui.', ['name' => $permission->name]));
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        $name = $permission->name;
        $permission->roles()->detach();
        $permission->users()->detach();
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', __('Permission ":name" berhasil dihapus.', ['name' => $name]));
    }
}

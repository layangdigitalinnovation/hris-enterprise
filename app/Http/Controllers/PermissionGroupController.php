<?php

namespace App\Http\Controllers;

use App\Models\PermissionGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PermissionGroupController extends Controller
{
    /**
     * Display a listing of group permissions.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $query = PermissionGroup::withCount('permissions');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $groups = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();
        $totalGroupsCount = PermissionGroup::count();

        return view('group_permissions.index', compact('groups', 'search', 'totalGroupsCount'));
    }

    /**
     * Show the form for creating a new group permission.
     */
    public function create()
    {
        return view('group_permissions.create');
    }

    /**
     * Store a newly created group permission in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permission_groups,name'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:permission_groups,slug'],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = $validated['slug'] ?: Str::slug($validated['name']);

        $group = PermissionGroup::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? 'folder',
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? (PermissionGroup::max('sort_order') + 1),
        ]);

        return redirect()->route('group-permissions.index')
            ->with('success', __('Group Permission ":name" berhasil ditambahkan.', ['name' => $group->name]));
    }

    /**
     * Show the form for editing the specified group permission.
     */
    public function edit(PermissionGroup $groupPermission)
    {
        return view('group_permissions.edit', compact('groupPermission'));
    }

    /**
     * Update the specified group permission in storage.
     */
    public function update(Request $request, PermissionGroup $groupPermission)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('permission_groups', 'name')->ignore($groupPermission->id)],
            'slug' => ['nullable', 'string', 'max:100', Rule::unique('permission_groups', 'slug')->ignore($groupPermission->id)],
            'icon' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $slug = $validated['slug'] ?: Str::slug($validated['name']);

        $groupPermission->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? 'folder',
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $groupPermission->sort_order,
        ]);

        return redirect()->route('group-permissions.index')
            ->with('success', __('Group Permission ":name" berhasil diperbarui.', ['name' => $groupPermission->name]));
    }

    /**
     * Remove the specified group permission from storage.
     */
    public function destroy(PermissionGroup $groupPermission)
    {
        if ($groupPermission->permissions()->count() > 0) {
            return redirect()->back()
                ->with('error', __('Tidak dapat menghapus group yang masih memiliki :count permission terhubung. Pindahkan atau hapus permission terkait terlebih dahulu.', [
                    'count' => $groupPermission->permissions()->count(),
                ]));
        }

        $groupName = $groupPermission->name;
        $groupPermission->delete();

        return redirect()->route('group-permissions.index')
            ->with('success', __('Group Permission ":name" berhasil dihapus.', ['name' => $groupName]));
    }
}


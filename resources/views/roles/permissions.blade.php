@extends('layouts.app')

@section('title', __('Pengaturan Hak Akses - :role', ['role' => $role->name]))

@section('content')
<div class="space-y-6 pb-20" x-data="rolePermissionsApp()">

    <!-- Header Section with Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <a href="{{ route('roles.index') }}" class="hover:text-blue-600 transition">{{ __('Roles') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Hak Akses') }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        {{ __('Pengaturan Hak Akses: :role', ['role' => $role->name]) }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        {{ __('Atur wewenang dan batasan akses modul untuk peran ini secara terstruktur berdasarkan group modul.') }}
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 self-start">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Daftar Role') }}</span>
        </a>
    </div>

    <!-- Form Matrix -->
    <form action="{{ route('roles.permissions.update', $role) }}" method="POST" id="rolePermissionsForm" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Master Toolbar Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <label class="flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer select-none">
                    <input type="checkbox"
                           @change="toggleSelectAll($event.target.checked)"
                           :checked="isAllSelected"
                           class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                    <span>{{ __('Pilih Semua Seluruh Modul') }}</span>
                </label>
                <span class="text-slate-300">|</span>
                <span class="text-xs text-slate-500">
                    <strong class="text-blue-600 font-extrabold" x-text="selectedCount"></strong> / {{ $totalAvailablePermissions }} {{ __('Permission Dipilih') }}
                </span>
            </div>

            <!-- Quick Filter Input inside matrix -->
            <div class="w-full sm:w-64">
                <input type="text"
                       x-model="searchTerm"
                       placeholder="{{ __('Saring nama permission...') }}"
                       class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>
        </div>

        <!-- Group Permission Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($groups as $group)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col"
                 x-show="isGroupVisible({{ $group->id }})">
                
                <!-- Card Header with Slate Gradient & "Pilih Semua" -->
                <div class="px-4 py-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        <h4 class="font-bold text-xs truncate leading-tight">{{ __($group->name) }}</h4>
                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-white/15 text-white">
                            {{ $group->permissions->count() }}
                        </span>
                    </div>

                    <label class="flex items-center gap-1.5 text-[10px] font-bold text-blue-200 cursor-pointer select-none shrink-0 hover:text-white transition">
                        <input type="checkbox"
                               @change="toggleGroup({{ $group->id }}, $event.target.checked)"
                               :checked="isGroupSelected({{ $group->id }})"
                               class="rounded text-blue-500 focus:ring-0 w-3.5 h-3.5">
                        <span>{{ __('Pilih Semua') }}</span>
                    </label>
                </div>

                <!-- Card Body: Permissions Checkbox List -->
                <div class="p-3 divide-y divide-slate-100 flex-1 space-y-1 text-xs">
                    @foreach($group->permissions as $p)
                        <label class="flex items-start gap-2.5 py-1.5 hover:bg-blue-50/40 px-1.5 rounded-lg transition cursor-pointer select-none"
                               x-show="isPermissionVisible('{{ strtolower($p->name) }}', '{{ strtolower($p->display_name) }}')">
                            <input type="checkbox"
                                   name="permission_ids[]"
                                   value="{{ $p->id }}"
                                   x-model="selectedIds"
                                   data-group-id="{{ $group->id }}"
                                   class="rounded text-blue-600 focus:ring-blue-500 mt-0.5 w-4 h-4">
                            <div class="min-w-0 flex-1">
                                <span class="font-mono text-[11px] font-bold text-slate-800 block truncate">{{ $p->name }}</span>
                                <span class="text-[11px] text-slate-500 leading-tight block mt-0.5">{{ __($p->display_name) }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <!-- Sticky Bottom Bar -->
        <div class="fixed bottom-0 inset-x-0 bg-white/95 backdrop-blur-md border-t border-slate-200/90 py-3.5 px-6 z-40 shadow-xl flex items-center justify-between max-w-7xl mx-auto">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <span class="text-xs font-bold text-slate-800 block leading-tight">
                        <span x-text="selectedCount" class="text-blue-600 font-extrabold"></span> {{ __('Permission Aktif Dipilih') }}
                    </span>
                    <span class="text-[10px] text-slate-400">
                        {{ __('Role:') }} {{ $role->name }} ({{ $role->key }})
                    </span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    {{ __('Batal') }}
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-extrabold shadow-md shadow-blue-500/20 hover:shadow-lg transition cursor-pointer flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>{{ __('Simpan Pengaturan Hak Akses') }}</span>
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    function rolePermissionsApp() {
        const groupMapping = {
            @foreach($groups as $g)
                {{ $g->id }}: [{{ $g->permissions->pluck('id')->join(',') }}],
            @endforeach
        };
        const allIds = [{{ $groups->flatMap->permissions->pluck('id')->join(',') }}];

        return {
            selectedIds: @json($rolePermissionIds),
            searchTerm: '',

            get selectedCount() {
                return this.selectedIds.length;
            },

            get isAllSelected() {
                return allIds.length > 0 && allIds.every(id => this.selectedIds.includes(id));
            },

            isGroupSelected(groupId) {
                const pIds = groupMapping[groupId] || [];
                return pIds.length > 0 && pIds.every(id => this.selectedIds.includes(id));
            },

            toggleSelectAll(checked) {
                if (checked) {
                    this.selectedIds = [...allIds];
                } else {
                    this.selectedIds = [];
                }
            },

            toggleGroup(groupId, checked) {
                const pIds = groupMapping[groupId] || [];
                if (checked) {
                    pIds.forEach(id => {
                        if (!this.selectedIds.includes(id)) {
                            this.selectedIds.push(id);
                        }
                    });
                } else {
                    this.selectedIds = this.selectedIds.filter(id => !pIds.includes(id));
                }
            },

            isPermissionVisible(name, displayName) {
                if (!this.searchTerm) return true;
                const term = this.searchTerm.toLowerCase().trim();
                return name.includes(term) || displayName.includes(term);
            },

            isGroupVisible(groupId) {
                if (!this.searchTerm) return true;
                return true;
            }
        };
    }
</script>
@endsection

@extends('layouts.app')

@section('title', __('Hak Akses Khusus User - :name', ['name' => $user->name]))

@section('content')
<div class="space-y-6" x-data="userPermissionsApp()">

    <!-- Header Section with Breadcrumbs -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-xs text-slate-400 mb-1.5 font-medium">
                <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('Dashboard') }}</a>
                <span>/</span>
                <a href="{{ route('users.index') }}" class="hover:text-blue-600 transition">{{ __('Users') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Permission Khusus') }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Hak Akses Khusus: :name', ['name' => $user->name]) }}</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        {{ __('Role Utama:') }} <strong class="text-blue-600 font-semibold">{{ $user->role_display_name }}</strong> · {{ __('Atur permission langsung yang melengkapi atau melampaui hak akses bawaan role.') }}
                    </p>
                </div>
            </div>
        </div>

        <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5 self-start">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Daftar User') }}</span>
        </a>
    </div>

    <!-- Form Matrix -->
    <form action="{{ route('users.permissions.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Master Toolbar -->
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
                    <strong class="text-blue-600 font-extrabold" x-text="selectedCount"></strong> {{ __('Permission Dipilih Langsung') }}
                </span>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 hover:shadow-lg transition cursor-pointer">
                    {{ __('Simpan Hak Akses User') }}
                </button>
            </div>
        </div>

        <!-- Group Permission Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($groups as $group)
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col">
                <!-- Card Header with HRIS Pro Enterprise Slate Gradient -->
                <div class="px-4 py-3 bg-gradient-to-r from-slate-900 to-slate-800 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        <span class="font-bold text-xs truncate">{{ __($group->name) }}</span>
                    </div>
                    <label class="flex items-center gap-1.5 text-[10px] font-bold text-blue-200 hover:text-white cursor-pointer select-none transition">
                        <input type="checkbox"
                               @change="toggleGroup({{ $group->id }}, $event.target.checked)"
                               :checked="isGroupSelected({{ $group->id }})"
                               class="rounded text-blue-500 focus:ring-0 w-3.5 h-3.5">
                        <span>{{ __('Pilih Semua') }}</span>
                    </label>
                </div>

                <!-- Card Body: Permissions List -->
                <div class="p-3.5 divide-y divide-slate-100 flex-1 space-y-1 text-xs">
                    @foreach($group->permissions as $p)
                        @php
                            $fromRole = in_array($p->id, $rolePermissionIds);
                        @endphp
                        <label class="flex items-start gap-2.5 py-1.5 hover:bg-blue-50/40 px-1.5 rounded-lg transition cursor-pointer select-none">
                            <input type="checkbox"
                                   name="permission_ids[]"
                                   value="{{ $p->id }}"
                                   x-model="selectedIds"
                                   data-group-id="{{ $group->id }}"
                                   class="rounded text-blue-600 focus:ring-blue-500 mt-0.5 w-4 h-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono text-[11px] font-bold text-slate-800">{{ $p->name }}</span>
                                    @if($fromRole)
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-blue-50 text-blue-700 border border-blue-200" title="{{ __('Sudah didapat dari Role') }}">
                                            {{ __('via Role') }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-500 leading-tight mt-0.5">{{ __($p->display_name) }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </form>
</div>

<script>
    function userPermissionsApp() {
        const groupMapping = {
            @foreach($groups as $g)
                {{ $g->id }}: [{{ $g->permissions->pluck('id')->join(',') }}],
            @endforeach
        };
        const allIds = [{{ $groups->flatMap->permissions->pluck('id')->join(',') }}];

        return {
            selectedIds: @json($userDirectPermissionIds),

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
            }
        };
    }
</script>
@endsection

@extends('layouts.app', ['headerTitle' => __('Master Struktur Organisasi Perusahaan'), 'title' => __('Edit Departemen')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('organization.index', ['tab' => 'departments']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Organisasi') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit Departemen') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Departemen / Divisi') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Perbarui informasi unit kerja departemen dan penugasan pimpinan (Manager)') }}</p>
        </div>
        <a href="{{ route('organization.index', ['tab' => 'departments']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir departemen:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organization.department.update', $department) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Identitas Departemen -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Informasi Departemen') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Departemen *') }}</label>
                    <input type="text" name="code" value="{{ old('code', $department->code) }}" placeholder="Misal: HRD, FIN, MKT, OPS" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Departemen *') }}</label>
                    <input type="text" name="name" value="{{ old('name', $department->name) }}" placeholder="Misal: Human Resources & GA" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Deskripsi Fungsi Kerja') }}</label>
                <textarea name="description" rows="3" placeholder="{{ __('Jelaskan peran utama dan ruang lingkup tugas departemen ini...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('description', $department->description) }}</textarea>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kepala Departemen / Manager') }}</label>
                <select name="manager_id"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <option value="">{{ __('-- Belum Ditentukan --') }}</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}" {{ old('manager_id', $department->manager_id) == $emp->id ? 'selected' : '' }}>
                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>
                <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Manager ini akan menjadi atasan langsung untuk alur persetujuan cuti & lembur di departemen bersangkutan.') }}</span>
            </div>

            <div class="pt-2 border-t border-slate-100">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $department->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <span class="text-xs font-semibold text-slate-700">{{ __('Departemen Aktif Operasional') }}</span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('organization.index', ['tab' => 'departments']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Perbarui Departemen') }}
            </button>
        </div>

    </form>

</div>
@endsection

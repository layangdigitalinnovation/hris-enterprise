@extends('layouts.app', ['headerTitle' => __('Master Struktur Organisasi'), 'title' => __('Tambah Jabatan')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('organization.index', ['tab' => 'positions']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Struktur Organisasi') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah Jabatan') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Tambah Jabatan & Posisi Kerja Baru') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Definisikan judul posisi, departemen naungan, dan tingkat level hierarki jabatan') }}</p>
        </div>
        <a href="{{ route('organization.index', ['tab' => 'positions']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir jabatan:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organization.position.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Informasi Jabatan -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Informasi Jabatan & Hierarki') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Jabatan *') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" placeholder="Misal: STF-FIN, MGR-HRD" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama / Judul Jabatan *') }}</label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="Misal: Senior Accounting Officer" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Departemen Naungan *') }}</label>
                    <select name="department_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">-- {{ __('Pilih Departemen') }} --</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }} ({{ $dept->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tingkat Level Hierarki *') }}</label>
                    <select name="level" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="1" {{ old('level', '1') == 1 ? 'selected' : '' }}>{{ __('Level 1 - Staff / Entry Level') }}</option>
                        <option value="2" {{ old('level') == 2 ? 'selected' : '' }}>{{ __('Level 2 - Senior Officer / Specialist') }}</option>
                        <option value="3" {{ old('level') == 3 ? 'selected' : '' }}>{{ __('Level 3 - Supervisor / Team Lead') }}</option>
                        <option value="4" {{ old('level') == 4 ? 'selected' : '' }}>{{ __('Level 4 - Manager / Department Head') }}</option>
                        <option value="5" {{ old('level') == 5 ? 'selected' : '' }}>{{ __('Level 5 - Director / C-Level Executive') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('organization.index', ['tab' => 'positions']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Jabatan Baru') }}
            </button>
        </div>

    </form>

</div>
@endsection

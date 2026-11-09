@extends('layouts.app', ['headerTitle' => __('Manajemen Shift & Jadwal Kerja'), 'title' => __('Tambah Hari Libur')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('shifts.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Shift & Jadwal') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah Hari Libur') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Tambah Hari Libur Nasional & Kalender Perusahaan') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Tanggal libur resmi akan secara otomatis dikecualikan dari perhitungan keterlambatan dan hari kerja wajib') }}</p>
        </div>
        <a href="{{ route('shifts.index') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir hari libur:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('shifts.holiday.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Rincian Hari Libur -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Data Hari Libur') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Hari Libur *') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Misal: Tahun Baru Masehi / Idul Fitri" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Libur *') }}</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Keterangan Tambahan') }}</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Misal: Libur Nasional SKB 3 Menteri atau Cuti Bersama Perusahaan"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('shifts.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Simpan Hari Libur') }}
            </button>
        </div>

    </form>

</div>
@endsection

@extends('layouts.app', ['headerTitle' => __('Manajemen Shift & Jadwal Kerja'), 'title' => __('Shift & Jadwal')])

@section('content')
<div class="space-y-8">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Shift, Jadwal Kerja & Kalender Libur') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Konfigurasi jam kerja, batas toleransi keterlambatan Attendance Engine, dan penugasan karyawan') }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('shifts.assign.create') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-semibold text-xs shadow-sm transition flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ __('Tugaskan Shift') }}
            </a>
            <a href="{{ route('shifts.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Shift Baru') }}
            </a>
        </div>
    </div>

    <!-- Shifts Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($shifts as $shift)
            <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm relative overflow-hidden flex flex-col justify-between" style="border-top: 4px solid {{ $shift->color }};">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">{{ $shift->code }}</span>
                            <h4 class="font-bold text-slate-900 text-base mt-0.5">{{ $shift->name }}</h4>
                        </div>
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $shift->color }};"></span>
                    </div>

                    <!-- Hours Block -->
                    <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 font-mono text-center space-y-1">
                        <span class="text-xs text-slate-500 block font-sans">{{ __('Jam Kerja Operasional:') }}</span>
                        <span class="text-xl font-extrabold text-slate-900 tracking-tight">
                            {{ substr($shift->start_time, 0, 5) }} - {{ substr($shift->end_time, 0, 5) }}
                        </span>
                        @if ($shift->is_overnight)
                            <span class="text-[10px] text-amber-600 block font-sans font-medium">{{ __('(Lintas Hari / Overnight)') }}</span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-2 text-xs text-slate-600">
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Toleransi Terlambat:') }}</span>
                            <span class="font-bold text-amber-600">{{ __(':count Menit', ['count' => $shift->late_tolerance_minutes]) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">{{ __('Total Penugasan:') }}</span>
                            <span class="text-slate-900 font-semibold">{{ __(':count Hari Terjadwal', ['count' => $shift->assignments_count]) }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-emerald-700 flex items-center gap-1.5 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ __('Aktif') }}
                    </span>

                    <div class="flex items-center gap-3">
                        <a href="{{ route('shifts.edit', $shift) }}" class="text-blue-600 hover:text-blue-800 transition text-[11px] font-semibold">
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('shifts.destroy', $shift) }}" method="POST" onsubmit="return confirm('{{ __('Hapus shift ini?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition text-[11px] cursor-pointer">
                                {{ __('Hapus') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 2 Column Section: Shift Assignments & Holiday Calendar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left 2 Cols: Shift Assignment Table -->
        <div class="lg:col-span-2 p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h4 class="font-bold text-slate-900 text-base">{{ __('Jadwal Penugasan Shift Karyawan') }}</h4>
                    <p class="text-xs text-slate-500">{{ __('Pemetaan shift per karyawan per tanggal') }}</p>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('shifts.index') }}" class="flex flex-wrap items-center gap-2.5 pt-1">
                <div class="relative flex-1 min-w-[200px] sm:min-w-[240px]">
                    <!-- Search Icon -->
                    <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>

                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nama karyawan, ID, shift...') }}"
                        class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                    @if(request('search'))
                        <a href="{{ route('shifts.index', array_merge(request()->except('search', 'page'), ['month' => $selectedMonth])) }}"
                           style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                           class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus pencarian') }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </div>

                <!-- Shift Filter -->
                <select name="shift_id" onchange="this.form.submit()"
                    class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                    <option value="">{{ __('Semua Shift') }}</option>
                    @foreach ($shifts as $s)
                        <option value="{{ $s->id }}" {{ request('shift_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>

                <!-- Month Picker -->
                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                    class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">

                <!-- Submit Button -->
                <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <span>{{ __('Cari') }}</span>
                </button>

                @if(request('search') || request('shift_id'))
                    <a href="{{ route('shifts.index', ['month' => $selectedMonth]) }}" class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        <span>{{ __('Reset') }}</span>
                    </a>
                @endif
            </form>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-500 uppercase tracking-wider border-b border-slate-200 pb-2">
                            <th class="py-3 px-3">{{ __('Tanggal') }}</th>
                            <th class="py-3 px-3">{{ __('Karyawan') }}</th>
                            <th class="py-3 px-3">{{ __('Shift Ditugaskan') }}</th>
                            <th class="py-3 px-3">{{ __('Jam Masuk - Pulang') }}</th>
                            <th class="py-3 px-3">{{ __('Status Hari') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($assignments as $asgn)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-3 font-mono font-semibold text-slate-900">{{ $asgn->date->format('d/m/Y') }}</td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 block">{{ $asgn->employee->full_name }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $asgn->employee->employee_code }}</span>
                                </td>
                                <td class="py-3 px-3">
                                    @if ($asgn->shift)
                                        <span class="font-medium text-slate-700">{{ $asgn->shift->name }}</span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-mono text-slate-600">
                                    {{ $asgn->shift ? substr($asgn->shift->start_time, 0, 5) . ' - ' . substr($asgn->shift->end_time, 0, 5) : __('Off') }}
                                </td>
                                <td class="py-3 px-3">
                                    @if ($asgn->is_day_off)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">{{ __('Libur Shift') }}</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">{{ __('Hari Kerja') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-slate-400 font-sans">
                                    @if(request('search') || request('shift_id'))
                                        <div class="space-y-2">
                                            <div class="text-sm font-semibold text-slate-700">{{ __('Penugasan Tidak Ditemukan') }}</div>
                                            <p class="text-xs text-slate-500">{{ __('Tidak ada jadwal penugasan shift yang cocok dengan filter kriteria pada periode ini.') }}</p>
                                            <div class="pt-2">
                                                <a href="{{ route('shifts.index', ['month' => $selectedMonth]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    {{ __('Reset Pencarian') }}
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        {{ __('Belum ada penugasan khusus pada bulan ini. Karyawan mengikuti shift reguler default.') }}
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($assignments->hasPages())
                <div class="pt-4 border-t border-slate-100">
                    {{ $assignments->links() }}
                </div>
            @elseif ($assignments->total() > 0)
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span>
                        {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $assignments->total() }}</strong> {{ __('jadwal penugasan') }}
                        @if(request('search'))
                            <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('search') }}")</span>
                        @endif
                    </span>
                    <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
                </div>
            @endif
        </div>

        <!-- Right 1 Col: Holidays List -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-bold text-slate-900 text-base">{{ __('Kalender Libur') }}</h4>
                    <p class="text-xs text-slate-500">{{ __('Libur nasional / cuti bersama') }}</p>
                </div>
                <a href="{{ route('shifts.holiday.create') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Tambah') }}
                </a>
            </div>

            <div class="space-y-3">
                @forelse ($holidays as $h)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between text-xs">
                        <div>
                            <span class="font-semibold text-slate-900 block">{{ $h->name }}</span>
                            <span class="text-[10px] text-slate-500">{{ $h->description ?? __('Hari Libur Nasional') }}</span>
                        </div>
                        <span class="font-mono text-xs font-bold text-rose-600">
                            {{ $h->date->format('d M Y') }}
                        </span>
                    </div>
                @empty
                    <p class="text-center py-6 text-xs text-slate-500">{{ __('Belum ada hari libur terdaftar.') }}</p>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

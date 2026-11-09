@extends('layouts.app', ['headerTitle' => __('Rekapitulasi Kehadiran Karyawan'), 'title' => __('Rekap Presensi Harian')])

@section('content')
<div class="space-y-6">

    <!-- Header & Calculation Trigger -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            @if(auth()->user()->isEmployee())
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Presensi Saya') }}</h3>
                <p class="text-xs text-slate-500">{{ __('Catatan riwayat presensi harian Anda dari mesin Solution dan jadwal shift kerja') }}</p>
            @else
                <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Rekap Kehadiran Harian') }}</h3>
                <p class="text-xs text-slate-500">{{ __('Data hasil olah Attendance Engine berdasarkan jadwal shift dan log mesin Solution') }}</p>
            @endif
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if(auth()->user()->canManageEmployees() || auth()->user()->isManager())
            <!-- Export Excel Button -->
            <a href="{{ route('attendance.export_excel', request()->all() ?: ['date' => $date]) }}" title="{{ __('Download Rekap Format Excel (.xlsx)') }}" class="px-3.5 py-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold text-xs shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>{{ __('Export Excel') }}</span>
            </a>

            <!-- Export PDF Button -->
            <a href="{{ route('attendance.export_pdf', request()->all() ?: ['date' => $date]) }}" title="{{ __('Download Rekap Format PDF (.pdf)') }}" class="px-3.5 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-xs shadow-xs transition flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h4"/></svg>
                <span>{{ __('Export PDF') }}</span>
            </a>
            @endif

            @if(auth()->user()->canManageEmployees())
            <form action="{{ route('attendance.recalculate') }}" method="POST">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    {{ __('Kalkulasi Ulang') }}
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Date KPI Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">{{ __('Hadir Tepat Waktu') }}</span>
                <span class="text-2xl font-extrabold text-emerald-600 block mt-1">{{ $present }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">{{ __('Terlambat') }}</span>
                <span class="text-2xl font-extrabold text-amber-500 block mt-1">{{ $late }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">{{ __('Sedang Cuti / Izin') }}</span>
                <span class="text-2xl font-extrabold text-blue-600 block mt-1">{{ $onLeave }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <span class="text-xs text-slate-500 font-medium block">{{ __('Alpa') }}</span>
                <span class="text-2xl font-extrabold text-rose-500 block mt-1">{{ $absent }}</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filter Bar with Date Picker -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Pilih Tanggal Presensi') }}</label>
                <input type="date" name="date" value="{{ $date }}"
                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Departemen') }}</label>
                <select name="department_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('Semua Departemen') }}</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">{{ __('Status Kehadiran') }}</label>
                <select name="status" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('Semua Status') }}</option>
                    <option value="present" {{ request('status') === 'present' ? 'selected' : '' }}>{{ __('Hadir') }}</option>
                    <option value="late" {{ request('status') === 'late' ? 'selected' : '' }}>{{ __('Terlambat') }}</option>
                    <option value="on_leave" {{ request('status') === 'on_leave' ? 'selected' : '' }}>{{ __('Cuti') }}</option>
                    <option value="absent" {{ request('status') === 'absent' ? 'selected' : '' }}>{{ __('Alpa') }}</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition cursor-pointer">
                    {{ __('Tampilkan Rekap') }}
                </button>
                <a href="{{ route('attendance.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition text-center">
                    {{ __('Hari Ini') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4 font-semibold">{{ __('Karyawan') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Jadwal Shift') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Jam Masuk') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Jam Pulang') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Keterlambatan') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Total Jam Kerja') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Status Presensi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($summaries as $sum)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $sum->employee->full_name }}</span>
                                <span class="text-slate-500 text-[11px]">{{ $sum->employee->employee_code }} · {{ $sum->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-slate-700 font-medium block">{{ $sum->shift ? $sum->shift->name : __('Reguler') }}</span>
                                <span class="text-[10px] text-slate-500">{{ $sum->shift ? substr($sum->shift->start_time, 0, 5) . ' - ' . substr($sum->shift->end_time, 0, 5) : '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold {{ $sum->clock_in ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ $sum->clock_in ?? '--:--:--' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold {{ $sum->clock_out ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ $sum->clock_out ?? '--:--:--' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if ($sum->late_minutes > 0)
                                    <span class="text-amber-600 font-semibold">{{ $sum->late_minutes }} {{ __('mnt') }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">
                                {{ $sum->work_hours }} {{ __('jam') }}
                            </td>
                            <td class="py-3.5 px-4">
                                @php $badge = $sum->status_badge; @endphp
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $badge['color'] }}">
                                    {{ __($badge['label']) }}
                                </span>
                                @if ($sum->notes)
                                    <span class="block text-[10px] text-slate-500 mt-1 italic">{{ $sum->notes }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                {{ __('Belum ada rekap absensi untuk tanggal :date. Klik "Kalkulasi Ulang" untuk memproses data log.', ['date' => Carbon\Carbon::parse($date)->translatedFormat('d F Y')]) }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($summaries->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $summaries->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

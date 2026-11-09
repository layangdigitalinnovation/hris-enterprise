@extends('layouts.app', ['headerTitle' => __('Koreksi & Penyesuaian Presensi'), 'title' => __('Koreksi Presensi')])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Koreksi Presensi Karyawan') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Pengajuan koreksi kehadiran untuk karyawan yang lupa tap mesin Solution atau mengalami kendala teknis') }}</p>
        </div>

        <a href="{{ route('attendance.adjustments.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Ajukan Koreksi Presensi') }}
        </a>
    </div>

    <!-- Search & Filter Card -->
    <div class="p-4 sm:p-5 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('attendance.adjustments') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[220px] sm:min-w-[280px]">
                <!-- Search Icon -->
                <svg style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); pointer-events: none;" class="w-4 h-4 text-slate-400 rtl:right-3.5 rtl:left-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>

                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari nama karyawan, ID, alasan koreksi...') }}"
                    class="w-full pl-11 pr-10 rtl:pr-11 rtl:pl-10 py-2.5 rounded-xl bg-slate-50/80 border border-slate-200 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

                @if(request('search'))
                    <a href="{{ route('attendance.adjustments', request()->except('search', 'page')) }}"
                       style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%);"
                       class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-200/60 transition rtl:left-2.5 rtl:right-auto" title="{{ __('Hapus pencarian') }}">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            <!-- Status Filter -->
            <select name="status" onchange="this.form.submit()"
                class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">
                <option value="">{{ __('Semua Status') }}</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>{{ __('Menunggu Persetujuan') }}</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>{{ __('Disetujui') }}</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>{{ __('Ditolak') }}</option>
            </select>

            <!-- Month Filter -->
            <input type="month" name="month" value="{{ request('month') }}" onchange="this.form.submit()"
                class="px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">

            <!-- Submit Button -->
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-semibold text-xs shadow-2xs transition flex items-center gap-1.5 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                <span>{{ __('Cari') }}</span>
            </button>

            @if(request('search') || request('status') || request('month'))
                <a href="{{ route('attendance.adjustments') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs transition flex items-center gap-1 shadow-2xs">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span>{{ __('Reset') }}</span>
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200">
                        <th class="py-3.5 px-4 font-semibold">{{ __('Karyawan') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Tanggal') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Jam Masuk Diajukan') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Jam Pulang Diajukan') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Alasan Koreksi') }}</th>
                        <th class="py-3.5 px-4 font-semibold">{{ __('Status Persetujuan') }}</th>
                        <th class="py-3.5 px-4 font-semibold text-right">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($adjustments as $adj)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-slate-900 text-sm block">{{ $adj->employee->full_name }}</span>
                                <span class="text-slate-500 text-[11px]">{{ $adj->employee->employee_code }} · {{ $adj->employee->department->name ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                {{ $adj->date->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-emerald-600">
                                {{ $adj->requested_clock_in ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-blue-600">
                                {{ $adj->requested_clock_out ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 max-w-xs">
                                {{ $adj->reason }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if ($adj->status === 'approved')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        {{ __('Disetujui') }}
                                    </span>
                                @elseif ($adj->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ __('Ditolak') }}
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        {{ __('Menunggu Persetujuan') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if ($adj->status === 'pending')
                                    @if(auth()->user()->canApprove())
                                        <div class="flex items-center justify-end gap-2">
                                            <form action="{{ route('attendance.adjustments.approve', $adj) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="approve">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    {{ __('Setujui') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('attendance.adjustments.approve', $adj) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-semibold transition flex items-center gap-1 shadow-2xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    {{ __('Tolak') }}
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ __('Menunggu Review Atasan') }}
                                        </span>
                                    @endif
                                @else
                                    <div class="text-[11px] text-right">
                                        @if($adj->approver)
                                            <span class="text-slate-700 block leading-tight font-semibold">{{ __('Oleh :name', ['name' => $adj->approver->name]) }}</span>
                                            <span class="text-[10px] text-slate-400 block leading-tight mt-0.5 capitalize">({{ __(str_replace('_', ' ', $adj->approver->role)) }})</span>
                                        @else
                                            <span class="text-[10px] text-slate-500">{{ __('Selesai diproses') }}</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-slate-400 font-sans">
                                @if(request('search') || request('status') || request('month'))
                                    <div class="space-y-2">
                                        <div class="text-sm font-semibold text-slate-700">{{ __('Koreksi Presensi Tidak Ditemukan') }}</div>
                                        <p class="text-xs text-slate-500">{{ __('Tidak ada data pengajuan koreksi presensi yang cocok dengan filter kriteria pencarian.') }}</p>
                                        <div class="pt-2">
                                            <a href="{{ route('attendance.adjustments') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 font-semibold text-xs transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                {{ __('Reset Pencarian') }}
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    {{ __('Belum ada pengajuan koreksi presensi.') }}
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($adjustments->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $adjustments->links() }}
            </div>
        @elseif ($adjustments->total() > 0)
            <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium bg-slate-50/50">
                <span>
                    {{ __('Menampilkan') }} <strong class="text-slate-800">{{ $adjustments->total() }}</strong> {{ __('pengajuan koreksi presensi') }}
                    @if(request('search'))
                        <span class="text-blue-600 font-normal">({{ __('hasil pencarian') }}: "{{ request('search') }}")</span>
                    @endif
                </span>
                <span class="text-slate-400 text-[11px]">{{ __('Halaman 1 dari 1') }}</span>
            </div>
        @endif
    </div>

</div>
@endsection

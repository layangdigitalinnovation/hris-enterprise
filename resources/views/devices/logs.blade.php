@extends('layouts.app', ['headerTitle' => __('Audit Log Absensi Mentah'), 'title' => __('Log Mentah Presensi')])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Audit Log Absensi Mentah') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Seluruh event presensi yang masuk ke sistem, baik dari mesin biometrik Solution maupun presensi GPS geofence') }}</p>
        </div>

        <a href="{{ route('devices.index') }}" class="text-xs text-slate-500 hover:text-slate-900 flex items-center gap-1.5 transition">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali ke Hub Mesin') }}
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('attendance.logs') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari PIN atau Karyawan...') }}"
                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <select name="device_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('Semua Sumber (Mesin, GPS & Visit)') }}</option>
                    <option value="gps" {{ request('device_id') === 'gps' ? 'selected' : '' }}>📍 {{ __('Hanya Presensi GPS (Mobile/Web)') }}</option>
                    <option value="visit" {{ request('device_id') === 'visit' ? 'selected' : '' }}>🚗 {{ __('Hanya Absen Visit (Kunjungan Lapangan)') }}</option>
                    <option value="biometric" {{ request('device_id') === 'biometric' ? 'selected' : '' }}>📟 {{ __('Semua Mesin Biometrik Solution') }}</option>
                    @if ($devices->isNotEmpty())
                        <optgroup label="{{ __('Pilih Perangkat Solution Spesifik') }}">
                            @foreach ($devices as $dev)
                                <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>{{ $dev->name }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            <div>
                <input type="date" name="date" value="{{ request('date') }}"
                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm shadow-blue-500/20 transition cursor-pointer">
                    {{ __('Filter Log') }}
                </button>
                <a href="{{ route('attendance.logs') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-semibold transition">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200 text-[11px]">
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">{{ __('Waktu Presensi') }}</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap text-center">{{ __('PIN Mesin') }}</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">{{ __('Karyawan Terpetakan') }}</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap">{{ __('Sumber / Mesin') }}</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap text-center">{{ __('Metode Verifikasi') }}</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap text-center">{{ __('Tipe Tap') }}</th>
                        <th class="py-3.5 px-4 font-semibold whitespace-nowrap text-center">{{ __('Status Pengolahan') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-3 px-4 text-slate-900 font-semibold font-mono">
                                {{ $log->log_time->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3 px-4 text-blue-600 font-bold font-mono">
                                {{ $log->device_pin }}
                            </td>
                            <td class="py-3 px-4 font-sans">
                                @if ($log->employee)
                                    <span class="font-bold text-slate-900 block">{{ $log->employee->full_name }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $log->employee->department->name ?? '-' }}</span>
                                @else
                                    <span class="text-amber-600 text-[11px] italic block">{{ __('Belum terdaftar di karyawan') }}</span>
                                    <a href="{{ route('devices.index', ['map_device' => $log->device_id, 'map_pin' => $log->device_pin]) }}#mapping-section" class="text-blue-600 hover:text-blue-800 text-[11px] font-medium inline-flex items-center gap-1 mt-0.5 transition">
                                        + {{ __('Petakan PIN :pin', ['pin' => $log->device_pin]) }}
                                    </a>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-sans text-slate-700">
                                @if ($log->verify_type === 'visit')
                                    <span class="inline-flex items-center gap-1.5 text-purple-700 font-bold text-xs">
                                        <span>🚗</span>
                                        <span>{{ $log->raw_payload['client_name'] ?? __('Kunjungan Lapangan') }}</span>
                                    </span>
                                    @if(!empty($log->raw_payload['visit_number']))
                                        <span class="block text-[10px] text-slate-500 font-mono mt-0.5">
                                            {{ $log->raw_payload['visit_number'] }}
                                            @if(!empty($log->raw_payload['outcome'])) · {{ Str::limit($log->raw_payload['outcome'], 28) }} @endif
                                        </span>
                                    @endif
                                @elseif ($log->verify_type === 'gps')
                                    <span class="inline-flex items-center gap-1.5 text-blue-700 font-bold text-xs">
                                        <span>📍</span>
                                        <span>{{ $log->raw_payload['branch_name'] ?? __('Cabang Operasional') }}</span>
                                    </span>
                                    @if(isset($log->raw_payload['distance_meters']))
                                        <span class="block text-[10px] text-slate-500 font-mono mt-0.5">
                                            {{ $log->raw_payload['distance_meters'] }}m {{ __('dari kantor') }}
                                            @if(!empty($log->raw_payload['country_flag'])) · {{ $log->raw_payload['country_flag'] }} {{ $log->raw_payload['branch_timezone'] ?? '' }} @endif
                                        </span>
                                    @endif
                                @else
                                    <span class="font-semibold text-slate-800 text-xs block">
                                        📟 {{ $log->device->name ?? __('File Import / Simulator') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block font-mono">
                                        {{ $log->device->branch->name ?? $log->device->location ?? __('Kantor') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-sans text-center whitespace-nowrap">
                                @if ($log->verify_type === 'visit')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        🚗 {{ __('Absen Visit') }}
                                    </span>
                                @elseif ($log->verify_type === 'gps')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                        📍 GPS Geofence
                                    </span>
                                @elseif ($log->verify_type === 'fingerprint')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        👆 Fingerprint
                                    </span>
                                @elseif ($log->verify_type === 'face')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                        👤 Face ID
                                    </span>
                                @elseif ($log->verify_type === 'rfid')
                                    <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        💳 RFID Card
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($log->verify_type ?? 'Manual') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-sans text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center whitespace-nowrap px-3 py-1 rounded-full text-[10px] font-bold border shrink-0 {{ $log->log_type === 'check_in' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                    {{ $log->type_label }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-sans text-center whitespace-nowrap">
                                <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-semibold border {{ $log->status === 'processed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ $log->status === 'processed' ? __('Diproses') : __('Tertunda') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-500 font-sans">
                                {{ __('Tidak ada log presensi ditemukan.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

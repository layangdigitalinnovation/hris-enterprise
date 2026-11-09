@extends('layouts.app', ['headerTitle' => __('Hub Integrasi Mesin Absensi Solution'), 'title' => __('Mesin Absensi Solution')])

@section('content')
<div class="space-y-8">

    <!-- Header & Quick Action -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                {{ __('Mesin Absensi Solution Manager') }}
                <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold text-xs border border-blue-200">{{ __('Universal Connector') }}</span>
            </h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Kelola koneksi TCP/IP LAN mesin sidik jari/wajah Solution, tarik log berkala, dan import manual') }}</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('devices.create') }}" class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-md shadow-blue-500/25 transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Tambah Mesin Solution') }}
            </a>
        </div>
    </div>

    <!-- Solution Device Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($devices as $device)
            <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition flex flex-col justify-between" x-data="{ testing: false, testMsg: null, testSuccess: null, currentStatus: '{{ $device->status }}' }">
                <div>
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 border border-blue-200 flex items-center justify-center font-bold shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-slate-900 text-base leading-tight">{{ $device->name }}</h4>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $device->device_model }} · {{ $device->branch->name ?? __('Kantor Pusat') }} {{ $device->location ? '('.$device->location.')' : '' }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <span class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                                  :class="currentStatus === 'online' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200'">
                                <span class="w-2 h-2 rounded-full"
                                      :class="currentStatus === 'online' ? 'bg-emerald-500 pulse-online' : 'bg-slate-400'"></span>
                                <span x-text="currentStatus === 'online' ? '{{ __('ONLINE') }}' : '{{ __('OFFLINE') }}'"></span>
                            </span>

                            <div class="h-4 w-px bg-slate-200 mx-0.5"></div>

                            <a href="{{ route('devices.edit', $device) }}" title="{{ __('Edit Pengaturan Mesin') }}" class="p-1.5 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-500 hover:text-blue-600 border border-slate-200/80 transition flex items-center gap-1 text-[11px] font-medium shadow-2xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span class="hidden sm:inline">{{ __('Edit') }}</span>
                            </a>

                            <form action="{{ route('devices.destroy', $device) }}" method="POST" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus mesin absensi :name?', ['name' => $device->name]) }}')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('Hapus Mesin Absensi') }}" class="p-1.5 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200/80 transition flex items-center gap-1 text-[11px] font-medium shadow-2xs">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span class="hidden sm:inline">{{ __('Hapus') }}</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Specs box -->
                    <div class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 font-mono text-xs space-y-2.5">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Protokol / IP:') }}</span>
                            <span class="text-blue-600 font-bold font-mono">{{ $device->ip_address ?: __('Simulator Virtual') }}:{{ $device->port }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Comm Key / Password:') }}</span>
                            <span class="text-slate-700 font-mono">•••• ({{ $device->comm_key }})</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Nomor Seri (SN):') }}</span>
                            <span class="text-slate-700 font-mono">{{ $device->serial_number ?? __('Auto Detect') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Karyawan HRIS Terhubung:') }}</span>
                            <span class="text-slate-900 font-bold font-sans">{{ __(':count Karyawan', ['count' => $device->employee_mappings_count]) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500 font-sans">{{ __('Total Log Disimpan:') }}</span>
                            <span class="text-slate-900 font-bold font-sans">{{ __(':count Log Absensi', ['count' => $device->attendance_logs_count]) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-1.5 border-t border-slate-200/70">
                            <span class="text-slate-500 font-sans">{{ __('Sinkronisasi Terakhir:') }}</span>
                            <span class="text-emerald-700 font-sans font-medium">{{ $device->last_sync_at ? $device->last_sync_at->diffForHumans() : __('Belum pernah') }}</span>
                        </div>
                    </div>

                    <!-- Test Connection AJAX Feedback Box -->
                    <div x-show="testMsg" x-cloak class="mt-3 p-3 rounded-xl text-xs border"
                         :class="testSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'"
                         x-text="testMsg"></div>
                </div>

                <!-- Action Footer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <button type="button"
                        @click="testing = true; fetch('{{ route('devices.test', $device) }}').then(r=>r.json()).then(res=>{ testMsg = res.message; testSuccess = res.success; if(res.success) currentStatus = 'online'; testing = false; })"
                        class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-semibold transition flex items-center gap-1.5 cursor-pointer"
                        :disabled="testing">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span x-text="testing ? '{{ __('Mengetes...') }}' : '{{ __('Tes Koneksi Soket') }}'">{{ __('Tes Koneksi Soket') }}</span>
                    </button>

                    <div class="flex items-center gap-2">
                        @if($device->sync_method === 'lan_tcp')
                            <form action="{{ route('devices.sync_users', $device) }}" method="POST">
                                @csrf
                                <button type="submit" title="{{ __('Tarik data user dari memori mesin') }}" class="px-3 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    {{ __('Tarik User') }}
                                </button>
                            </form>
                        @endif

                        @if($device->sync_method === 'mock')
                            <form action="{{ route('devices.simulate', $device) }}" method="POST">
                                @csrf
                                <button type="submit" title="{{ __('Generate presensi virtual') }}" class="px-3 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold transition cursor-pointer">
                                    {{ __('Simulasikan Tap') }}
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('devices.sync', $device) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                {{ __('Tarik Log Sekarang') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center py-12 bg-white rounded-3xl border border-slate-200/80 shadow-xs">
                <p class="text-sm text-slate-500">{{ __('Belum ada mesin absensi Solution terdaftar.') }}</p>
                <a href="{{ route('devices.create') }}" class="inline-block mt-3 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold">
                    {{ __('Tambah Perangkat Pertama') }}
                </a>
            </div>
        @endforelse
    </div>

    @if($devices->hasPages())
        <div class="pt-1">
            {{ $devices->links() }}
        </div>
    @endif

    <!-- Bottom Two Columns: Import Tool & Sync Run History -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Import CSV / TXT Box -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200 flex items-center justify-center font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-base">{{ __('Import File Log Mesin (CSV / TXT)') }}</h4>
                    <p class="text-xs text-slate-500">{{ __('Unggah file hasil export USB Flashdisk dari mesin Solution') }}</p>
                </div>
            </div>

            <form action="{{ route('devices.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pilih Perangkat Solution Asal') }}</label>
                    <select name="device_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">{{ __('-- Deteksi Otomatis / Universal --') }}</option>
                        @foreach (($allDevices ?? $devices) as $dev)
                            <option value="{{ $dev->id }}">{{ $dev->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-data="{ fileName: '' }">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('File Log Mesin (.csv atau .txt)') }}</label>
                    <div class="relative flex items-center">
                        <input type="file" name="file" id="device_log_file" required accept=".csv,.txt"
                            @change="fileName = $event.target.files.length ? $event.target.files[0].name : ''"
                            class="sr-only">
                        <label for="device_log_file"
                            class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 hover:border-blue-400 focus-within:ring-2 focus-within:ring-blue-500/20 text-slate-600 flex items-center justify-between gap-3 cursor-pointer transition">
                            <span class="truncate text-xs" :class="fileName ? 'text-slate-800 font-medium' : 'text-slate-400'"
                                x-text="fileName || '{{ __('Belum ada berkas dipilih') }}'">
                                {{ __('Belum ada berkas dipilih') }}
                            </span>
                            <span class="shrink-0 px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span>{{ __('Pilih Berkas') }}</span>
                            </span>
                        </label>
                    </div>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Format baris: PIN, YYYY-MM-DD HH:MM:SS, [verify_type], [log_type]') }}</span>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold transition shadow-xs cursor-pointer">
                    {{ __('Proses & Simpan Log ke Database') }}
                </button>
            </form>
        </div>

        <!-- Sync Runs History -->
        <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <h4 class="font-bold text-slate-900 text-base">{{ __('Riwayat Aktivitas Tarik Log (Sync Runs)') }}</h4>
            <div class="overflow-y-auto max-h-60 space-y-2 text-xs">
                @forelse ($syncRuns as $run)
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 flex items-center justify-between">
                        <div>
                            <span class="font-semibold text-slate-800 block">{{ $run->device->name ?? __('Perangkat Solution') }}</span>
                            <span class="text-[10px] text-slate-500 capitalize">{{ __(ucfirst($run->trigger_type)) }} · {{ $run->run_at->translatedFormat('d M Y H:i:s') }}</span>
                        </div>
                        <div class="text-right rtl:text-left font-mono">
                            <span class="text-xs font-bold text-emerald-600 block">+{{ $run->records_inserted }} {{ __('Baru') }}</span>
                            <span class="text-[10px] text-slate-400">{{ $run->records_fetched }} {{ __('Terbaca') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-center py-8 text-xs text-slate-400">{{ __('Belum ada histori sinkronisasi.') }}</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Section Pemetaan PIN Mesin ke Karyawan HRIS -->
    <div id="mapping-section" class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-6" x-data="{ showManualForm: {{ (old('device_pin') || request('map_pin') || $errors->any()) ? 'true' : 'false' }} }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div>
                <h4 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    {{ __('Pemetaan PIN Mesin Absensi ke Karyawan HRIS') }}
                    <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-semibold text-xs border border-blue-200">
                        {{ $mappings instanceof \Illuminate\Pagination\LengthAwarePaginator ? $mappings->total() : count($mappings) }} {{ __('Terhubung') }}
                    </span>
                </h4>
                <p class="text-xs text-slate-500 mt-0.5">{{ __('Memastikan data tap/log dari mesin Solution secara otomatis tercatat atas nama karyawan yang tepat') }}</p>
            </div>

            <button type="button" @click="showManualForm = !showManualForm" class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 font-semibold text-xs transition flex items-center gap-1.5 self-start sm:self-auto shadow-2xs cursor-pointer">
                <svg class="w-4 h-4 transition-transform" :class="showManualForm ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span x-text="showManualForm ? '{{ __('Tutup Formulir Manual') }}' : '{{ __('+ Tambah Pemetaan Manual') }}'">{{ __('+ Tambah Pemetaan Manual') }}</span>
            </button>
        </div>

        <!-- Embedded Form Tambah Pemetaan Baru -->
        @if(!empty($detectedUsers))
            @foreach($detectedUsers as $devId => $detected)
                @php
                    $unmappedCount = $detected['users']->whereNull('mapped_employee')->count();
                    $totalCount = count($detected['users']);
                    $mappedCount = $totalCount - $unmappedCount;
                    $allMapped = ($unmappedCount === 0);
                @endphp

                @if($allMapped)
                    <!-- Ringkasan Kompak: Semua User Mesin Telah Berhasil Terpetakan -->
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200/80 space-y-3" x-data="{ showDetails: false }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <h5 class="text-xs font-bold text-emerald-950 flex items-center gap-2">
                                        {{ __('Semua User Mesin Telah Terpetakan') }}
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-semibold text-[10px]">
                                            {{ $totalCount }} / {{ $totalCount }} {{ __('Selesai') }}
                                        </span>
                                    </h5>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">{{ __('Seluruh :total user dari mesin :device sudah terhubung otomatis ke data karyawan HRIS.', ['total' => $totalCount, 'device' => $detected['device']->name]) }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end sm:self-center">
                                <button type="button" @click="showDetails = !showDetails" class="px-3 py-1.5 rounded-xl bg-white text-emerald-800 border border-emerald-200 text-xs font-semibold hover:bg-emerald-50 transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="showDetails ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <span x-show="showDetails" x-cloak>{{ __('Sembunyikan Rincian') }}</span>
                                    <span x-show="!showDetails">{{ __('Lihat Rincian PIN') }} ({{ $totalCount }})</span>
                                </button>
                                <form action="{{ route('devices.clear_users', $detected['device']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="{{ __('Tutup pemberitahuan ini') }}" class="px-2.5 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-500 hover:text-slate-800 border border-slate-200/80 transition flex items-center gap-1 text-[11px] font-medium shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        <span>{{ __('Tutup Notif') }}</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Grid Rincian User (Default Tertutup saat semua sudah terpetakan) -->
                        <div x-show="showDetails" x-cloak class="pt-3 border-t border-emerald-200/60">
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
                                @foreach($detected['users'] as $u)
                                    <div class="p-3 rounded-xl bg-white border border-emerald-200/80 shadow-2xs flex flex-col justify-between gap-2">
                                        <div class="flex items-start justify-between">
                                            <div>
                                                <div class="flex items-center gap-1.5">
                                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 font-mono font-bold text-xs text-blue-600">PIN {{ $u['pin'] }}</span>
                                                    @if($u['role'] === 14)
                                                        <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 text-[9px] font-bold">{{ __('Admin') }}</span>
                                                    @endif
                                                </div>
                                                <div class="font-bold text-slate-900 text-xs mt-1">{{ $u['name'] ?: __('Tanpa Nama') }}</div>
                                            </div>
                                            <div class="text-right rtl:text-left">
                                                <span class="text-[10px] font-semibold text-slate-500 block">{{ $u['log_count'] }} {{ __('Log Absensi') }}</span>
                                            </div>
                                        </div>

                                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                            @php
                                                $empName = is_array($u['mapped_employee'])
                                                    ? ($u['mapped_employee']['first_name'] ?? __('Karyawan'))
                                                    : ($u['mapped_employee']->first_name ?? __('Karyawan'));
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700" title="{{ __('Terhubung ke Karyawan HRIS') }}">
                                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                {{ $empName }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Alert Kuning Jika Masih Ada User yang Belum Terpetakan -->
                    <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200/80 space-y-3" x-data="{ filterUnmappedOnly: false }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-amber-200/60">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                                <h5 class="text-xs font-bold text-slate-800">{{ __('User Terdeteksi di Mesin :device', ['device' => $detected['device']->name]) }}</h5>
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-semibold text-[10px]">
                                    {{ $unmappedCount }} {{ __('Belum Dipetakan') }} ({{ $mappedCount }} {{ __('Terhubung') }})
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <button type="button" @click="filterUnmappedOnly = !filterUnmappedOnly" class="text-[11px] font-semibold text-amber-800 hover:text-amber-900 underline cursor-pointer">
                                    <span x-show="filterUnmappedOnly" x-cloak>{{ __('Tampilkan Semua') }} ({{ $totalCount }})</span>
                                    <span x-show="!filterUnmappedOnly">{{ __('Tampilkan Hanya yang Belum Dipetakan') }} ({{ $unmappedCount }})</span>
                                </button>
                                <form action="{{ route('devices.clear_users', $detected['device']) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" title="{{ __('Tutup pemberitahuan') }}" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 transition cursor-pointer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2.5">
                            @foreach($detected['users'] as $u)
                                <div x-show="!filterUnmappedOnly || {{ $u['mapped_employee'] ? 'false' : 'true' }}"
                                     class="p-3 rounded-xl bg-white border {{ $u['mapped_employee'] ? 'border-emerald-200 bg-emerald-50/20' : 'border-amber-200/70' }} shadow-2xs flex flex-col justify-between gap-2">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 font-mono font-bold text-xs text-blue-600">PIN {{ $u['pin'] }}</span>
                                                @if($u['role'] === 14)
                                                    <span class="px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 text-[9px] font-bold">{{ __('Admin') }}</span>
                                                @endif
                                            </div>
                                            <div class="font-bold text-slate-900 text-xs mt-1">{{ $u['name'] ?: __('Tanpa Nama') }}</div>
                                        </div>
                                        <div class="text-right rtl:text-left">
                                            <span class="text-[10px] font-semibold text-slate-500 block">{{ $u['log_count'] }} {{ __('Log Absensi') }}</span>
                                        </div>
                                    </div>

                                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
                                        @if($u['mapped_employee'])
                                            @php
                                                $empName = is_array($u['mapped_employee'])
                                                    ? ($u['mapped_employee']['first_name'] ?? __('Karyawan'))
                                                    : ($u['mapped_employee']->first_name ?? __('Karyawan'));
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700" title="{{ __('Terhubung ke Karyawan HRIS') }}">
                                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                {{ $empName }}
                                            </span>
                                        @else
                                            <span class="text-[10px] font-medium text-amber-700">{{ __('Belum dipetakan') }}</span>
                                            <div class="flex items-center gap-1.5">
                                                <a href="{{ route('employees.create', ['first_name' => $u['name'], 'device_id' => $detected['device']->id, 'device_pin' => $u['pin']]) }}"
                                                    title="{{ __('Buat Karyawan Baru dengan data ini') }}"
                                                    class="px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10px] font-bold transition">
                                                    + {{ __('Karyawan') }}
                                                </a>
                                                <button type="button" 
                                                    @click="showManualForm = true"
                                                    onclick="document.querySelector('select[name=device_id]').value = '{{ $detected['device']->id }}'; document.getElementById('device_pin_input').value = '{{ $u['pin'] }}'; document.getElementById('mapping-form-card').scrollIntoView({behavior: 'smooth'}); document.querySelector('select[name=employee_id]').focus();"
                                                    title="{{ __('Hubungkan ke Karyawan yang sudah ada') }}"
                                                    class="px-2 py-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 text-[10px] font-bold transition cursor-pointer">
                                                    {{ __('Petakan') }} &rarr;
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        <div id="mapping-form-card" x-show="showManualForm" x-cloak class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-4">
            <div class="flex items-center gap-2.5 pb-2 border-b border-slate-200/70">
                <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs font-bold shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <h5 class="text-xs font-bold text-slate-800">{{ __('Hubungkan PIN Mesin ke Karyawan') }}</h5>
                    <p class="text-[11px] text-slate-500">{{ __('Petakan nomor User ID yang terdaftar di mesin Solution dengan data profil karyawan HRIS') }}</p>
                </div>
            </div>

            <form action="{{ route('devices.mapping.store') }}" method="POST" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Perangkat Mesin *') }}</label>
                        <select name="device_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            @foreach (($allDevices ?? $devices) as $d)
                                <option value="{{ $d->id }}" {{ (string)request('map_device') === (string)$d->id ? 'selected' : '' }}>
                                    {{ $d->name }} ({{ $d->device_model }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block font-semibold text-slate-700">{{ __('Karyawan HRIS *') }}</label>
                            <a href="{{ route('employees.create') }}" class="text-[11px] text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-0.5 transition">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                + {{ __('Tambah Karyawan Baru') }}
                            </a>
                        </div>
                        <select name="employee_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="">{{ __('-- Pilih Karyawan HRIS --') }}</option>
                            @foreach ($employees as $e)
                                <option value="{{ $e->id }}">{{ $e->full_name }} ({{ $e->employee_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor PIN Mesin *') }}</label>
                        <input type="text" name="device_pin" id="device_pin_input" list="device_pin_suggestions" required placeholder="{{ __('Contoh: 2') }}" value="{{ old('device_pin', request('map_pin')) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 font-mono font-semibold placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                        <datalist id="device_pin_suggestions">
                            @if(!empty($detectedUsers))
                                @foreach($detectedUsers as $detected)
                                    @foreach($detected['users'] as $u)
                                        <option value="{{ $u['pin'] }}">PIN {{ $u['pin'] }} - {{ $u['name'] }} ({{ $u['log_count'] }} log)</option>
                                    @endforeach
                                @endforeach
                            @endif
                        </datalist>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Hak Akses Mesin') }}</label>
                        <select name="privilege" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                            <option value="user">{{ __('User Standar') }}</option>
                            <option value="admin">{{ __('Administrator Mesin') }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        {{ __('Simpan Pemetaan') }}
                    </button>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left rtl:text-right text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider border-b border-slate-200 font-semibold">
                        <th class="py-2.5 px-3">{{ __('PIN Mesin') }}</th>
                        <th class="py-2.5 px-3">{{ __('Nama Karyawan') }}</th>
                        <th class="py-2.5 px-3">{{ __('Departemen') }}</th>
                        <th class="py-2.5 px-3">{{ __('Perangkat Mesin') }}</th>
                        <th class="py-2.5 px-3">{{ __('Hak Akses Mesin') }}</th>
                        <th class="py-2.5 px-3">{{ __('Terdaftar Sejak') }}</th>
                        <th class="py-2.5 px-3 text-right rtl:text-left">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mappings as $map)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-3 font-mono font-bold text-blue-600 text-sm">{{ $map->device_pin }}</td>
                            <td class="py-3 px-3 font-semibold text-slate-900">
                                <a href="{{ route('employees.show', $map->employee_id) }}" class="hover:text-blue-600 transition">
                                    {{ $map->employee->full_name ?? '-' }} ({{ $map->employee->employee_code ?? '-' }})
                                </a>
                            </td>
                            <td class="py-3 px-3 text-slate-600">{{ $map->employee->department->name ?? '-' }}</td>
                            <td class="py-3 px-3 text-slate-600">{{ $map->device->name ?? __('Universal Device') }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $map->privilege === 'admin' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $map->privilege === 'admin' ? __('Administrator') : __('User') }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-slate-500 font-mono">{{ $map->enrolled_at ? $map->enrolled_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="py-3 px-3 text-right rtl:text-left">
                                <form action="{{ route('devices.mapping.destroy', $map) }}" method="POST" onsubmit="return confirm('{{ __('Hapus pemetaan PIN :pin?', ['pin' => $map->device_pin]) }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px] transition cursor-pointer">
                                        {{ __('Lepas') }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">{{ __('Belum ada pemetaan PIN mesin terdaftar.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mappings instanceof \Illuminate\Pagination\LengthAwarePaginator && $mappings->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $mappings->links() }}
            </div>
        @endif
    </div>

</div>
@endsection

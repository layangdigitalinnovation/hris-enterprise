@extends('layouts.app', ['headerTitle' => __('HRIS Dashboard'), 'title' => __('Dashboard Utama')])

@section('content')

@if (auth()->user()->isEmployee())
    <!-- ========================================== -->
    <!-- EMPLOYEE SELF-SERVICE PORTAL (MODERN THEME) -->
    <!-- ========================================== -->
    <div class="space-y-6">
        
        <!-- Welcome Banner -->
        <div class="p-8 rounded-3xl bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white shadow-lg shadow-blue-500/20 relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 rounded-full text-[11px] font-semibold bg-white/20 text-white backdrop-blur-md inline-block mb-3">
                        {{ __('Portal Layanan Mandiri Karyawan') }}
                    </span>
                    <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                        {{ __('Selamat Datang, :name! 👋', ['name' => $myEmployee->full_name ?? auth()->user()->name]) }}
                    </h2>
                    <p class="text-xs md:text-sm text-blue-100 mt-1.5 font-medium">
                        {{ $myEmployee->position->title ?? 'Staff' }} · {{ $myEmployee->department->name ?? 'Departemen' }} ({{ $myEmployee->employee_code ?? '-' }})
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('leaves.index') }}" class="px-4 py-2.5 rounded-xl bg-white text-blue-700 font-bold text-xs shadow-md hover:bg-blue-50 transition">
                        {{ __('Ajukan Cuti') }}
                    </a>
                    <a href="{{ route('overtime.index') }}" class="px-4 py-2.5 rounded-xl bg-blue-800/60 hover:bg-blue-800 text-white border border-white/20 font-semibold text-xs transition">
                        {{ __('Ajukan Lembur') }}
                    </a>
                    @if ($myLatestPayslip)
                        <a href="{{ route('payroll.payslip', $myLatestPayslip) }}" class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            {{ __('Slip Gaji Saya') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <!-- Today Attendance -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Presensi Hari Ini') }}</span>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-2xl font-bold font-mono text-slate-900">
                        {{ $myTodayAttendance ? ($myTodayAttendance->clock_in ?? '--:--') : __('Belum Tap') }}
                    </span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $myTodayAttendance ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ $myTodayAttendance ? __($myTodayAttendance->status_badge['label'] ?? ucfirst($myTodayAttendance->status)) : __('Menunggu') }}
                    </span>
                </div>
                <span class="text-xs text-slate-500 block mt-2">
                    {{ __('Pulang:') }} <span class="font-mono font-medium text-slate-700">{{ $myTodayAttendance?->clock_out ?? '--:--' }}</span>
                </span>
            </div>

            <!-- Leave Quota -->
            @php $ct = $myLeaveBalances->where('leaveType.code', 'CT')->first(); @endphp
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Sisa Saldo Cuti') }}</span>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-blue-600">{{ $ct ? $ct->remaining_quota : 12 }}</span>
                    <span class="text-xs text-slate-500 font-medium">{{ __('hari (dari :total)', ['total' => $ct ? $ct->initial_quota : 12]) }}</span>
                </div>
                <span class="text-xs text-slate-500 block mt-2">
                    {{ __('Terpakai: :count hari tahun ini', ['count' => $ct ? $ct->used_quota : 0]) }}
                </span>
            </div>

            <!-- PIN Mesin Solution -->
            @php $mapping = $myEmployee?->deviceMappings->first(); @endphp
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('PIN Mesin Solution') }}</span>
                <div class="mt-3">
                    <span class="text-2xl font-bold font-mono text-purple-600">{{ $mapping ? $mapping->device_pin : __('Belum Terdaftar') }}</span>
                </div>
                <span class="text-xs text-slate-500 block mt-2">
                    {{ __('Fingerprint & RFID: Aktif') }}
                </span>
            </div>

            <!-- Payslip Info -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">{{ __('Gaji Periode Terakhir') }}</span>
                <div class="mt-3">
                    <span class="text-xl font-bold font-mono text-emerald-600">
                        {{ $myLatestPayslip ? format_currency($myLatestPayslip->payrollItem->net_salary, $myLatestPayslip->payrollItem->currency) : __('Tersedia di HR') }}
                    </span>
                </div>
                <span class="text-xs text-slate-500 block mt-2">
                    {{ $myLatestPayslip ? $myLatestPayslip->payrollItem->period->name : __('Gaji Diproses Tepat Waktu') }}
                </span>
            </div>
        </div>

        <!-- Presensi Mandiri GPS (Geofencing & Zona Waktu) -->
        @php
            $currentEmp = $myEmployee;
            $currentBranch = $currentEmp?->branch ?? $allBranches->first();
        @endphp
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4" x-data="{
            lat: null,
            lon: null,
            loading: false,
            verifying: false,
            clocking: false,
            verified: null,
            distance: null,
            statusMsg: '',
            branchId: {{ $currentBranch?->id ?? 1 }},
            async detectAndCheck() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung deteksi lokasi GPS.');
                    return;
                }
                this.loading = true;
                this.statusMsg = '{{ __('Mendeteksi koordinat GPS...') }}';
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        this.lat = pos.coords.latitude;
                        this.lon = pos.coords.longitude;
                        this.loading = false;
                        await this.verifyLocation();
                    },
                    (err) => {
                        this.loading = false;
                        this.statusMsg = '{{ __('Gagal mendeteksi lokasi: ') }}' + err.message;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            },
            async verifyLocation() {
                if (!this.lat || !this.lon) return;
                this.verifying = true;
                try {
                    const res = await fetch('{{ route('attendance.check_radius') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: this.lat,
                            longitude: this.lon,
                            branch_id: this.branchId
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.verified = data.is_within_radius;
                        this.distance = data.distance_meters;
                        this.statusMsg = data.message;
                    } else {
                        this.verified = false;
                        this.statusMsg = data.message || '{{ __('Gagal memverifikasi lokasi.') }}';
                    }
                } catch (e) {
                    this.statusMsg = '{{ __('Terjadi kesalahan jaringan.') }}';
                } finally {
                    this.verifying = false;
                }
            },
            async submitClock(type) {
                if (!this.lat || !this.lon) {
                    await this.detectAndCheck();
                    if (!this.verified) return;
                }
                this.clocking = true;
                try {
                    const res = await fetch('{{ route('attendance.gps_clock_in') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: this.lat,
                            longitude: this.lon,
                            log_type: type
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        alert(data.message);
                        window.location.reload();
                    } else {
                        alert(data.message || '{{ __('Presensi gagal.') }}');
                    }
                } catch (e) {
                    alert('{{ __('Terjadi kesalahan saat memproses presensi.') }}');
                } finally {
                    this.clocking = false;
                }
            }
        }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3.5">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block animate-pulse"></span>
                        {{ __('Presensi Mandiri GPS (Geofencing & Zona Waktu)') }}
                    </h4>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Verifikasi jarak koordinat terhadap radius kantor operasional di Indonesia dan Arab Saudi') }}
                    </p>
                </div>
                @if($currentBranch)
                <div class="flex items-center gap-2 text-xs font-semibold">
                    <span class="px-2.5 py-1 rounded-xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center gap-1.5">
                        <span>{{ $currentBranch->country_flag }}</span>
                        <span>{{ $currentBranch->name }}</span>
                    </span>
                    <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                        {{ $currentBranch->timezone }}
                    </span>
                </div>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <!-- Info Kantor & Waktu Lokal -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                    <span class="text-slate-500 font-medium block uppercase tracking-wider text-[10px]">{{ __('Kantor & Waktu Operasional') }}</span>
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-slate-800">{{ $currentBranch?->name ?? __('Belum Diatur') }}</span>
                        <span class="text-[11px] font-mono text-blue-600 font-bold">{{ $currentBranch?->country_flag }} {{ $currentBranch?->timezone }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                        <span class="text-slate-500">{{ __('Waktu Lokal:') }}</span>
                        <span class="font-mono font-bold text-slate-900 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ $currentBranch ? $currentBranch->getLocalTime()->format('H:i:s T') : date('H:i:s') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">{{ __('Batas Radius:') }}</span>
                        <span class="font-mono font-semibold text-slate-700">{{ $currentBranch?->radius_meters ?? 100 }} {{ __('meter') }}</span>
                    </div>
                </div>

                <!-- Status Jarak & Geolokasi -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2">
                    <span class="text-slate-500 font-medium block uppercase tracking-wider text-[10px]">{{ __('Status Verifikasi Geofence') }}</span>
                    <div class="min-h-[40px] flex items-center">
                        <template x-if="loading || verifying">
                            <span class="text-blue-600 font-medium flex items-center gap-1.5 animate-pulse">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                <span x-text="statusMsg"></span>
                            </span>
                        </template>
                        <template x-if="!loading && !verifying && verified === true">
                            <span class="text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-200 font-semibold block text-[11px]">
                                ✓ <span x-text="statusMsg"></span>
                            </span>
                        </template>
                        <template x-if="!loading && !verifying && verified === false">
                            <span class="text-rose-700 bg-rose-50 px-2.5 py-1 rounded-xl border border-rose-200 font-semibold block text-[11px]">
                                ✗ <span x-text="statusMsg"></span>
                            </span>
                        </template>
                        <template x-if="!loading && !verifying && verified === null">
                            <span class="text-slate-500 text-[11px]">
                                {{ __('Klik tombol di bawah untuk mendeteksi lokasi GPS Anda.') }}
                            </span>
                        </template>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 font-mono text-[11px] text-slate-500">
                        <span>{{ __('Koordinat Saya:') }}</span>
                        <span x-text="lat ? (lat.toFixed(4) + ', ' + lon.toFixed(4)) : '-- , --'"></span>
                    </div>
                </div>

                <!-- Tombol Aksi Presensi -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 flex flex-col justify-between space-y-2">
                    <button type="button" @click="detectAndCheck()" :disabled="loading || verifying" class="w-full py-2 px-3 rounded-xl bg-white hover:bg-slate-100 text-slate-800 border border-slate-200 font-semibold text-xs shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>{{ __('Deteksi Lokasi GPS Saya') }}</span>
                    </button>

                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" @click="submitClock('check_in')" :disabled="clocking" class="py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-xs shadow-blue-500/20 transition flex items-center justify-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                            <span>{{ __('Clock In') }}</span>
                        </button>
                        <button type="button" @click="submitClock('check_out')" :disabled="clocking" class="py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-xs transition flex items-center justify-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>{{ __('Clock Out') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget Absen Visit (Kunjungan Lapangan & Klien) -->
        <div class="p-5 sm:p-6 rounded-3xl bg-white border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl {{ $myActiveVisit ? 'bg-amber-100 text-amber-600' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-slate-900 text-sm">{{ __('Absen Visit (Kunjungan Lapangan / Klien)') }}</h4>
                        @if($myActiveVisit)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white animate-pulse">
                                {{ __('Sedang Berlangsung') }}
                            </span>
                        @elseif($myTodayVisitsCount > 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ __(':count Selesai Hari Ini', ['count' => $myTodayVisitsCount]) }}
                            </span>
                        @endif
                    </div>
                    @if($myActiveVisit)
                        <p class="text-xs text-amber-800 font-medium mt-1">
                            {{ __('Sedang kunjungan di: :client (Sejak :time · :durasi). Wajib Check-Out sebelum kunjungan berikutnya!', [
                                'client' => $myActiveVisit->client_name,
                                'time' => $myActiveVisit->check_in_at->format('H:i'),
                                'durasi' => $myActiveVisit->formatted_duration
                            ]) }}
                        </p>
                    @else
                        <p class="text-xs text-slate-500 mt-1">
                            {{ __('Dapat melakukan kunjungan berkali-kali dalam sehari dengan deteksi GPS dan wajib check-out setiap selesai kunjungan.') }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0 w-full sm:w-auto">
                <a href="{{ route('attendance.visits.index') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl {{ $myActiveVisit ? 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-500/20' : 'bg-slate-900 hover:bg-blue-600 text-white shadow-slate-900/10' }} font-bold text-xs shadow-sm transition flex items-center justify-center gap-2">
                    @if($myActiveVisit)
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>{{ __('Check-Out Kunjungan Sekarang') }}</span>
                    @else
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>{{ __('Buka Absen Visit') }}</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Recent Logs & Pending Requests for Employee -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Riwayat Tap Presensi Saya -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-900 text-sm">{{ __('Riwayat Tap Presensi Saya') }}</h4>
                    <span class="text-xs text-blue-600 font-semibold">{{ __('Terkoneksi Real-Time') }}</span>
                </div>
                <div class="space-y-2.5 text-xs">
                    @forelse ($myRecentLogs as $log)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <span class="font-semibold text-slate-800 flex items-center gap-1.5 truncate">
                                    <span>{{ $log->source_icon }}</span>
                                    <span class="truncate">{{ $log->source_name }}</span>
                                </span>
                                <span class="text-[11px] text-slate-500 font-mono block mt-0.5">
                                    {{ $log->log_time_carbon->translatedFormat('d M Y H:i:s') }}
                                    <span class="text-slate-400 font-sans">({{ $log->relative_time }})</span>
                                </span>
                            </div>
                            <span class="inline-flex items-center justify-center whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-bold border shrink-0
                                {{ $log->log_type === 'check_in' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200' }}">
                                {{ $log->type_label }}
                            </span>
                        </div>
                    @empty
                        <p class="text-center py-6 text-xs text-slate-400">{{ __('Belum ada log presensi tercatat.') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Pengajuan Terakhir Saya -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="font-bold text-slate-900 text-sm">{{ __('Pengajuan Cuti / Izin Saya') }}</h4>
                    <a href="{{ route('leaves.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                        {{ __('Lihat Semua') }} <span class="rtl:rotate-180 inline-block">&rarr;</span>
                    </a>
                </div>
                <div class="space-y-2.5 text-xs">
                    @forelse ($myPendingLeaves as $leave)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800">{{ __($leave->leaveType->name) }} ({{ $leave->total_days }} {{ __('Hari') }})</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold border
                                    {{ $leave->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($leave->status === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                    {{ $leave->status === 'approved' ? __('Disetujui') : ($leave->status === 'rejected' ? __('Ditolak') : __('Menunggu Review')) }}
                                </span>
                            </div>
                            <p class="text-slate-500 text-[11px]">{{ __('Rentang: :start s/d :end', ['start' => $leave->start_date->format('d/m/Y'), 'end' => $leave->end_date->format('d/m/Y')]) }}</p>
                        </div>
                    @empty
                        <p class="text-center py-6 text-xs text-slate-400">{{ __('Belum ada riwayat pengajuan cuti.') }}</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Real-Time GPS Tracking Map (Presensi Mandiri GPS & Absensi Visitasi) -->
        @include('dashboard.partials.gps_map')

    </div>

@else
    <!-- ============================================== -->
    <!-- HR ADMIN / MANAGEMENT DASHBOARD (EXACT MOCKUP) -->
    <!-- ============================================== -->
    <div class="space-y-6">

        <!-- Top Greeting Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    {{ __('app.good_morning') }}, {{ auth()->user()->name }} 👋
                </h2>
                <p class="text-xs text-slate-500 mt-1 font-medium">
                    {{ __('app.dashboard_summary_desc') }}
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <span class="px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-2xs flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    {{ Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>

        <!-- 4 Top KPI Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- 1. Total Karyawan -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __('app.total_employees') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $totalEmployees }}</span>
                </div>
                <div class="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-1">
                    <span>↑ +5%</span>
                    <span class="text-slate-400">{{ __('app.from_last_month') }}</span>
                </div>
            </div>

            <!-- 2. Hadir Hari Ini -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __('app.present_today') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $presentToday }}</span>
                    <span class="text-xs font-semibold text-emerald-600">{{ $attendanceRate }}%</span>
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    <span class="text-slate-700 font-medium">{{ $presentToday }}</span> {{ __('dari :total karyawan', ['total' => $totalEmployees]) }}
                </div>
            </div>

            <!-- 3. Izin & Cuti -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __('app.leave_today') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $leaveToday }}</span>
                </div>
                <div class="mt-2 text-xs text-amber-600 font-medium flex items-center gap-1">
                    <span>{{ __(':count pengajuan', ['count' => $pendingLeaves->count()]) }}</span>
                    <span class="text-slate-400">{{ __('app.awaiting_approval') }}</span>
                </div>
            </div>

            <!-- 4. Terlambat -->
            <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:shadow-md transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-slate-500">{{ __('app.late_today') }}</span>
                    <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $lateToday }}</span>
                </div>
                <div class="mt-2 text-xs text-rose-600 font-medium flex items-center gap-1">
                    <span>{{ $lateToday > 0 ? __('Denda :amount', ['amount' => format_currency($lateToday * (\App\Helpers\CurrencyHelper::defaultCurrency() === 'SAR' ? 15 : 15000))]) : __('Tepat Waktu') }}</span>
                </div>
            </div>

        </div>

        <!-- Middle Row: Data Kehadiran Hari Ini (Donut Chart) & Aktivitas Terbaru -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left: Donut Chart Breakdown (Col 7) -->
            <div class="lg:col-span-7 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm">{{ __('Data Kehadiran Hari Ini') }}</h3>
                        <p class="text-xs text-slate-400">{{ __('Persentase presensi terolah dari mesin Solution') }}</p>
                    </div>
                    <a href="{{ route('attendance.index') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                        {{ __('Lihat Detail') }} <span class="rtl:rotate-180 inline-block">&rarr;</span>
                    </a>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-around gap-6 pt-2">
                    <!-- SVG Circular Donut Chart -->
                    <div class="relative w-44 h-44 flex items-center justify-center shrink-0">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                            <!-- Background Ring -->
                            <path class="text-slate-100" stroke-width="3.8" stroke="currentColor" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <!-- Active Present Ring (Emerald #10B981) -->
                            <path class="text-emerald-500" stroke-width="3.8" stroke-dasharray="{{ $attendanceRate }}, 100" stroke-linecap="round" stroke="currentColor" fill="none"
                                d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute text-center">
                            <span class="text-2xl font-extrabold text-slate-900 block leading-tight">{{ $attendanceRate }}%</span>
                            <span class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider block">{{ __('Hadir') }}</span>
                        </div>
                    </div>

                    <!-- Donut Legend -->
                    <div class="w-full space-y-3 text-xs">
                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                <span class="text-slate-600 font-medium">{{ __('Hadir Tepat Waktu') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-900 font-mono">{{ $presentToday }}</span>
                                <span class="text-slate-400 font-mono text-[11px] w-12 text-right">{{ $attendanceRate }}%</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                                <span class="text-slate-600 font-medium">{{ __('Izin Resmi') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-900 font-mono">{{ $leaveToday }}</span>
                                <span class="text-slate-400 font-mono text-[11px] w-12 text-right">
                                    {{ $totalEmployees > 0 ? round(($leaveToday / $totalEmployees) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-1.5 border-b border-slate-100">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                                <span class="text-slate-600 font-medium">{{ __('Terlambat') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-900 font-mono">{{ $lateToday }}</span>
                                <span class="text-slate-400 font-mono text-[11px] w-12 text-right">
                                    {{ $totalEmployees > 0 ? round(($lateToday / $totalEmployees) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between py-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                                <span class="text-slate-600 font-medium">{{ __('Tanpa Keterangan') }}</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-900 font-mono">{{ $absentToday }}</span>
                                <span class="text-slate-400 font-mono text-[11px] w-12 text-right">
                                    {{ $totalEmployees > 0 ? round(($absentToday / $totalEmployees) * 100, 1) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Aktivitas Terbaru (Stream) (Col 5) -->
            <div class="lg:col-span-5 p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-900 text-sm">{{ __('Aktivitas Terbaru') }}</h3>
                    <a href="{{ route('attendance.logs') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                        {{ __('Lihat Semua') }} <span class="rtl:rotate-180 inline-block">&rarr;</span>
                    </a>
                </div>

                <div class="space-y-2.5 text-xs">
                    @forelse ($recentLogs as $log)
                        <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="relative shrink-0">
                                    <div class="w-9 h-9 rounded-full bg-slate-100 text-blue-600 font-bold text-xs flex items-center justify-center border border-slate-200">
                                        {{ strtoupper(substr($log->employee->first_name ?? 'K', 0, 1)) }}
                                    </div>
                                    <span class="absolute -bottom-0.5 -right-0.5 rtl:-left-0.5 rtl:-right-auto w-4 h-4 rounded-full flex items-center justify-center text-[9px] shadow-2xs {{ $log->log_type === 'check_in' ? 'bg-emerald-500 text-white' : 'bg-blue-600 text-white' }}" title="{{ $log->type_label }}">
                                        @if($log->log_type === 'check_in') &darr; @else &uarr; @endif
                                    </span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-slate-800 truncate leading-tight">
                                        {{ $log->employee->full_name ?? ('PIN: ' . $log->device_pin) }}
                                    </p>
                                    <div class="flex items-center gap-1.5 text-[11px] truncate leading-tight mt-1">
                                        <span class="font-bold {{ $log->log_type === 'check_in' ? 'text-emerald-600' : 'text-blue-600' }}">
                                            {{ $log->type_label }}
                                        </span>
                                        <span class="text-slate-300">·</span>
                                        <span class="text-slate-600 truncate flex items-center gap-1" title="{{ $log->source_name }}">
                                            <span>{{ $log->source_icon }}</span>
                                            <span class="truncate">{{ $log->source_name }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400 shrink-0 font-medium whitespace-nowrap font-mono" title="{{ $log->log_time_carbon->format('Y-m-d H:i:s') }} ({{ $log->timezone }})">
                                {{ $log->relative_time }}
                            </span>
                        </div>
                    @empty
                        <p class="text-center py-8 text-xs text-slate-400">{{ __('Belum ada aktivitas tercatat hari ini.') }}</p>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Real-Time GPS Tracking Map (Presensi Mandiri GPS & Absensi Visitasi) -->
        @include('dashboard.partials.gps_map')

        <!-- 4 Quick Module Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <a href="{{ route('attendance.index') }}" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-blue-500 hover:shadow-md transition group block">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-blue-600 transition">{{ __('Absensi') }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ __('Kelola kehadiran karyawan secara real-time dari mesin Solution') }}</p>
            </a>

            <a href="{{ route('leaves.index') }}" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-emerald-500 hover:shadow-md transition group block">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-emerald-600 transition">{{ __('Cuti & Izin') }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ __('Pengajuan dan persetujuan cuti/izin online secara berjenjang') }}</p>
            </a>

            <a href="{{ route('payroll.index') }}" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-indigo-500 hover:shadow-md transition group block">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-indigo-600 transition">{{ __('Payroll') }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ __('Perhitungan gaji, lembur, dan denda keterlambatan terotomatisasi') }}</p>
            </a>

            <a href="{{ route('attendance.export') }}" class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs hover:border-purple-500 hover:shadow-md transition group block">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:scale-105 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h4 class="font-bold text-slate-900 text-sm group-hover:text-purple-600 transition">{{ __('Laporan & Analitik') }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ __('Export laporan presensi dan penggajian lengkap ke format Excel/CSV') }}</p>
            </a>

        </div>

        <!-- Solution Devices Status Cards -->
        <div class="p-6 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-900 text-sm">{{ __('Status Mesin Absensi Solution Terdaftar') }}</h4>
                        <p class="text-xs text-slate-400">{{ __('Monitoring konektivitas TCP/IP LAN dan status penarikan log mesin') }}</p>
                    </div>
                </div>

                <a href="{{ route('devices.index') }}" class="px-3.5 py-1.5 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-semibold border border-blue-200 transition">
                    {{ __('Kelola Mesin') }} <span class="rtl:rotate-180 inline-block">&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($devices as $dev)
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 flex items-center justify-between text-xs">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $dev->name }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $dev->status === 'online' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-200 text-slate-600' }}">
                                    {{ $dev->status === 'online' ? __('Online') : __('Offline') }}
                                </span>
                            </div>
                            <p class="text-slate-500 font-mono text-[11px]">{{ $dev->device_model }} · IP: {{ $dev->ip_address ?: 'Simulator' }}:{{ $dev->port }}</p>
                            <p class="text-slate-400 text-[10px]">{{ __('Lokasi: :location', ['location' => $dev->location ?? __('Kantor Pusat')]) }}</p>
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('devices.simulate', $dev) }}" method="POST">
                                @csrf
                                <button type="submit" title="{{ __('Simulasikan Presensi Masuk') }}" class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs shadow-2xs transition cursor-pointer">
                                    {{ __('Simulasi Tap') }}
                                </button>
                            </form>
                            <form action="{{ route('devices.sync', $dev) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-xs transition cursor-pointer">
                                    {{ __('Sync') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endif

@endsection

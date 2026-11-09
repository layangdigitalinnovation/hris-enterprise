@extends('layouts.app', ['headerTitle' => __('Absen Visit'), 'title' => __('Formulir Check-Out Kunjungan')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="{
    lat: null,
    lon: null,
    acc: null,
    gpsLoading: false,
    gpsError: '',
    detectGps() {
        if (!navigator.geolocation) {
            this.gpsError = '{{ __('Browser Anda tidak mendukung deteksi lokasi GPS.') }}';
            return;
        }
        this.gpsLoading = true;
        this.gpsError = '';
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                this.lat = pos.coords.latitude.toFixed(7);
                this.lon = pos.coords.longitude.toFixed(7);
                this.acc = Math.round(pos.coords.accuracy);
                this.gpsLoading = false;
            },
            (err) => {
                this.gpsLoading = false;
                this.gpsError = '{{ __('Gagal mendapatkan lokasi GPS: ') }}' + err.message;
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 }
        );
    }
}" x-init="detectGps()">

    <!-- Header & Navigation (Matching Existing Form Standard) -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('attendance.visits.index') }}" class="hover:text-blue-600 font-medium transition">{{ __('Absen Visit') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Formulir Check-Out Kunjungan') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Check-Out Kunjungan') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Selesaikan kunjungan aktif dan rekam koordinat GPS kepulangan serta catatan hasil kunjungan') }}</p>
        </div>
        <a href="{{ route('attendance.visits.index') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir check-out:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 1. Rincian Kunjungan yang Sedang Berjalan -->
    <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Rincian Kunjungan Aktif') }}
            </h4>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-ping"></span>
                <span>{{ __('Sedang Berlangsung') }}</span>
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-1">
                <span class="text-slate-400 block text-[10px] font-semibold uppercase tracking-wider">{{ __('Nomor & Karyawan') }}</span>
                <span class="font-mono font-bold text-slate-900 block">{{ $visit->visit_number }}</span>
                <span class="font-semibold text-slate-700 block">{{ $visit->employee->full_name }} ({{ $visit->employee->employee_code }})</span>
                <span class="text-[11px] text-slate-500 block">{{ $visit->employee->department?->name ?? '-' }}</span>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-1">
                <span class="text-slate-400 block text-[10px] font-semibold uppercase tracking-wider">{{ __('Waktu & Durasi Berjalan') }}</span>
                <span class="text-slate-700 block">
                    {{ __('Check-In:') }} <strong class="text-slate-900 font-mono">{{ $visit->check_in_at->format('H:i:s') }}</strong> ({{ $visit->check_in_at->format('d M Y') }})
                </span>
                <span class="text-blue-700 font-bold font-mono block">
                    {{ __('Durasi:') }} {{ $visit->formatted_duration }}
                </span>
                <span class="text-[10px] text-slate-400 block">{{ $visit->timezone ?: 'Asia/Jakarta' }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 space-y-2 text-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="font-bold text-slate-900 text-sm">{{ $visit->client_name }}</span>
            </div>
            <p class="text-slate-600">{{ $visit->purpose }}</p>
            <div class="pt-2 border-t border-slate-200/60 flex flex-wrap items-center gap-x-4 gap-y-1 text-[11px] text-slate-500">
                <span>{{ __('Titik Check-In:') }}
                    <a href="{{ $visit->check_in_map_url }}" target="_blank" class="font-mono text-blue-600 hover:underline font-semibold">
                        {{ number_format($visit->check_in_lat, 5) }}, {{ number_format($visit->check_in_lon, 5) }} ↗
                    </a>
                </span>
                @if($visit->check_in_address)
                    <span>· {{ $visit->check_in_address }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Check-Out -->
    <form action="{{ route('attendance.visits.checkout.process', $visit) }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="latitude" :value="lat">
        <input type="hidden" name="longitude" :value="lon">

        {{-- 2. Deteksi Lokasi GPS Check-Out --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">2</span>
                    {{ __('Deteksi Lokasi GPS Check-Out') }}
                </h4>
                <button type="button" @click="detectGps()" class="text-xs text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1 cursor-pointer">
                    <svg class="w-3.5 h-3.5" :class="gpsLoading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>{{ __('Deteksi Ulang') }}</span>
                </button>
            </div>

            <!-- GPS Status Indicator -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                <template x-if="gpsLoading">
                    <div class="py-2 text-xs text-blue-600 flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>{{ __('Mendeteksi koordinat GPS lokasi Check-Out...') }}</span>
                    </div>
                </template>

                <template x-if="!gpsLoading && lat">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500 font-medium">{{ __('Koordinat Terdeteksi:') }}</span>
                            <span class="font-mono font-bold text-emerald-600" x-text="lat + ', ' + lon"></span>
                        </div>
                        <div class="text-[11px] text-slate-400" x-show="acc">
                            {{ __('Akurasi Geolocation:') }} ±<span x-text="acc"></span>m
                        </div>
                    </div>
                </template>

                <template x-if="!gpsLoading && !lat && gpsError">
                    <div class="text-rose-600 text-xs font-medium" x-text="gpsError"></div>
                </template>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alamat / Keterangan Titik Check-Out (Opsional)') }}</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="{{ __('Misal: Lobby kantor client, dsb...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>
        </div>

        <!-- 3. Hasil & Catatan Kunjungan -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold">3</span>
                {{ __('Hasil & Catatan Kunjungan') }}
            </h4>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Hasil & Catatan Kunjungan') }}</label>
                <textarea name="outcome" rows="4" placeholder="{{ __('Tuliskan hasil pertemuan atau pekerjaan yang telah diselesaikan (misal: Instalasi mesin selesai ditraining ke 2 operator HR, form serah terima sudah ditandatangani)...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('outcome') }}</textarea>
                <p class="text-[11px] text-slate-400 mt-1">{{ __('Setelah kunjungan ini di-checkout, status berubah menjadi selesai dan Anda dapat memulai kunjungan baru jika diperlukan.') }}</p>
            </div>
        </div>

        <!-- Action Buttons (Matching Existing Form Standard) -->
        <div class="flex items-center justify-between gap-3 pt-2">
            <!-- Cancel Visit (Direct form submission with confirmation) -->
            <button type="button" onclick="if(confirm('{{ __('Apakah Anda yakin ingin membatalkan kunjungan ini?') }}')) { document.getElementById('cancel-visit-form').submit(); }"
                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-600 text-xs font-semibold transition cursor-pointer">
                {{ __('Batalkan Kunjungan') }}
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('attendance.visits.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer">
                    {{ __('Batal') }}
                </a>
                <button type="submit" :disabled="!lat || gpsLoading"
                    style="background-color: #059669; color: #ffffff;"
                    class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs shadow-sm shadow-emerald-500/20 transition flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ __('Selesaikan & Check-Out') }}</span>
                </button>
            </div>
        </div>

    </form>

    <!-- Hidden form for Cancel Visit -->
    <form id="cancel-visit-form" action="{{ route('attendance.visits.cancel', $visit) }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="reason" value="{{ __('Dibatalkan dari formulir checkout') }}">
    </form>

</div>
@endsection

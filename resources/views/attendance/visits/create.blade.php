@extends('layouts.app', ['headerTitle' => __('Absen Visit'), 'title' => __('Formulir Check-In Kunjungan Baru')])

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
                <span class="text-slate-700 font-semibold">{{ __('Formulir Check-In Kunjungan Baru') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Formulir Check-In Kunjungan Baru') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Pencatatan awal lokasi GPS, instansi/klien tujuan, dan agenda kunjungan dinas lapangan') }}</p>
        </div>
        <a href="{{ route('attendance.visits.index') }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir kunjungan:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('attendance.visits.store') }}" method="POST" class="space-y-6">
        @csrf
        <input type="hidden" name="latitude" :value="lat">
        <input type="hidden" name="longitude" :value="lon">

        <!-- 1. Karyawan Pelaksana Kunjungan -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Karyawan Pelaksana Kunjungan') }}
            </h4>

            @if($isEmployee && $myEmp)
                <input type="hidden" name="employee_id" value="{{ $myEmp->id }}">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center shrink-0">
                        {{ strtoupper(substr($myEmp->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <span class="text-sm font-bold text-slate-900 block">{{ $myEmp->full_name }}</span>
                        <span class="text-xs text-slate-500 font-mono">{{ $myEmp->employee_code }} · {{ $myEmp->department->name ?? __('Operasional') }} · {{ $myEmp->branch?->name ?? '-' }}</span>
                    </div>
                </div>
            @else
                <div class="text-xs">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Pilih Karyawan *') }}</label>
                    <select name="employee_id" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="">-- {{ __('Pilih Karyawan') }} --</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (old('employee_id', $myEmp?->id) == $emp->id) ? 'selected' : '' }}>
                                {{ $emp->full_name }} ({{ $emp->employee_code }}) - {{ $emp->department->name ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- 2. Identitas & Agenda Kunjungan -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                {{ __('Identitas & Agenda Kunjungan') }}
            </h4>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Klien / Instansi / Tempat Tujuan *') }}</label>
                <input type="text" name="client_name" value="{{ old('client_name') }}" required placeholder="{{ __('Misal: PT Telkom Akses, RS Harapan Bunda, Cabang Riyadh...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Keperluan / Agenda Kunjungan *') }}</label>
                <textarea name="purpose" rows="3" required placeholder="{{ __('Jelaskan agenda kunjungan secara rinci (misal: Maintenance perangkat Solution X302, meeting koordinasi, penawaran solusi HR)...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('purpose') }}</textarea>
            </div>
        </div>

        {{-- 3. Deteksi Lokasi GPS Check-In --}}
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">3</span>
                    {{ __('Deteksi Lokasi GPS Check-In') }}
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
                        <span>{{ __('Mencari sinyal GPS perangkat...') }}</span>
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
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Keterangan Alamat / Patokan (Opsional)') }}</label>
                <input type="text" name="address" value="{{ old('address') }}" placeholder="{{ __('Misal: Gedung Menara Astra Lt. 15, Jl. Jend Sudirman Kav. 5...') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>

            <!-- Business Rule Notice -->
            <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ __('Aturan Kunjungan: Anda dapat melakukan kunjungan berkali-kali dalam sehari, dengan catatan wajib melakukan Check-Out setelah kunjungan ini selesai sebelum dapat memulai kunjungan baru.') }}</span>
            </div>
        </div>

        <!-- Action Buttons (Matching Existing Form Standard) -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('attendance.visits.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition cursor-pointer">
                {{ __('Batal') }}
            </a>
            <button type="submit" :disabled="!lat || gpsLoading"
                style="background-color: #2563eb; color: #ffffff;"
                class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ __('Check-In Kunjungan Sekarang') }}</span>
            </button>
        </div>

    </form>

</div>
@endsection

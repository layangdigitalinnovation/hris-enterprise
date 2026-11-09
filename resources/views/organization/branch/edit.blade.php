@extends('layouts.app', ['headerTitle' => __('Master Struktur Organisasi Perusahaan'), 'title' => __('Edit Cabang')])

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigation -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('organization.index', ['tab' => 'branches']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Organisasi') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit Cabang') }}</span>
            </div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Kantor Cabang') }}</h3>
            <p class="text-xs text-slate-500 font-medium">{{ __('Perbarui lokasi operasional, nomor kontak, zona waktu, dan radius geolokasi presensi (Indonesia & Arab Saudi)') }}</p>
        </div>
        <a href="{{ route('organization.index', ['tab' => 'branches']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium space-y-1">
            <div class="font-bold">{{ __('Terdapat kesalahan pada formulir kantor cabang:') }}</div>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('organization.branch.update', $branch) }}" method="POST" class="space-y-6" id="branchForm">
        @csrf
        @method('PUT')

        <!-- 1. Identitas Kantor Cabang -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <h4 class="font-bold text-slate-900 text-sm border-b border-slate-100 pb-3 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                {{ __('Identitas Kantor Cabang') }}
            </h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Cabang *') }}</label>
                    <input type="text" name="code" value="{{ old('code', $branch->code) }}" placeholder="Misal: BR-JKT, BR-RUH, BR-JED" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono uppercase focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Kantor Cabang *') }}</label>
                    <input type="text" name="name" id="branchNameInput" value="{{ old('name', $branch->name) }}" placeholder="Misal: Kantor Cabang Riyadh / Kantor Cabang Bandung" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor Telepon Kantor') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" placeholder="Misal: +62 21 555-8900 atau +966 11 456 7890"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
            </div>

            <div class="text-xs">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alamat Lengkap Kantor') }}</label>
                <textarea name="address" id="branchAddressInput" rows="3" placeholder="Jalan, Gedung, Distrik/Kota, Kode Pos..."
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">{{ old('address', $branch->address) }}</textarea>
            </div>
        </div>

        <!-- 2. Pengaturan Presensi & Lokasi (Geofencing & Timezone) -->
        <div class="p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <h4 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                    <span class="w-6 h-6 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                    {{ __('Zona Waktu & Batas Radius Presensi') }}
                </h4>
                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg">
                    <span>🇮🇩 {{ __('Indonesia') }}</span>
                    <span class="text-slate-300">|</span>
                    <span>🇸🇦 {{ __('Arab Saudi') }}</span>
                </div>
            </div>

            <!-- Quick Preset Helper -->
            <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50/70 to-indigo-50/70 border border-blue-100/80 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-blue-900 text-xs flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ __('Pilih Preset Kota Cepat (Otomatis Atur Koordinat & Zona Waktu)') }}
                    </label>
                    <button type="button" id="btnDetectGps" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-white px-2.5 py-1 rounded-lg border border-blue-200 shadow-2xs hover:bg-blue-50 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ __('Gunakan GPS Saya Saat Ini') }}
                    </button>
                </div>
                <select id="cityPresetSelect" class="w-full px-3.5 py-2 rounded-xl bg-white border border-blue-200 text-slate-800 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <option value="">-- {{ __('Pilih preset kota untuk mengisi otomatis...') }} --</option>
                    <optgroup label="🇸🇦 Arab Saudi (AST / UTC+3)">
                        <option value="riyadh" data-lat="24.7136" data-lon="46.6753" data-tz="Asia/Riyadh" data-addr="King Fahd Road, Olaya District, Riyadh, Saudi Arabia">🇸🇦 Riyadh (الرياض - العاصمة) [AST / UTC+3]</option>
                        <option value="jeddah" data-lat="21.5433" data-lon="39.1728" data-tz="Asia/Riyadh" data-addr="Al-Andalus District, Madinah Road, Jeddah, Saudi Arabia">🇸🇦 Jeddah (جدة) [AST / UTC+3]</option>
                        <option value="makkah" data-lat="21.3891" data-lon="39.8579" data-tz="Asia/Riyadh" data-addr="Al Aziziyah District, Makkah Al Mukarramah, Saudi Arabia">🇸🇦 Makkah (مكة المكرمة) [AST / UTC+3]</option>
                        <option value="madinah" data-lat="24.4672" data-lon="39.6111" data-tz="Asia/Riyadh" data-addr="Central Area, Madinah Al Munawwarah, Saudi Arabia">🇸🇦 Madinah (المدينة المنورة) [AST / UTC+3]</option>
                        <option value="dammam" data-lat="26.4207" data-lon="50.0888" data-tz="Asia/Riyadh" data-addr="King Saud Road, Dammam / Al-Khobar, Eastern Province, Saudi Arabia">🇸🇦 Dammam & Khobar (الدمام والخبر) [AST / UTC+3]</option>
                    </optgroup>
                    <optgroup label="🇮🇩 Indonesia - WIB (UTC+7)">
                        <option value="jakarta" data-lat="-6.2088" data-lon="106.8456" data-tz="Asia/Jakarta" data-addr="Jl. Jenderal Sudirman Kav. 21, Jakarta Pusat, DKI Jakarta">🇮🇩 Jakarta Pusat (DKI Jakarta) [WIB / UTC+7]</option>
                        <option value="surabaya" data-lat="-7.2575" data-lon="112.7521" data-tz="Asia/Jakarta" data-addr="Jl. Pemuda No. 45, Embong Kaliasin, Surabaya, Jawa Timur">🇮🇩 Surabaya (Jawa Timur) [WIB / UTC+7]</option>
                        <option value="bandung" data-lat="-6.9175" data-lon="107.6191" data-tz="Asia/Jakarta" data-addr="Jl. Asia Afrika No. 100, Bandung, Jawa Barat">🇮🇩 Bandung (Jawa Barat) [WIB / UTC+7]</option>
                        <option value="medan" data-lat="3.5952" data-lon="98.6722" data-tz="Asia/Jakarta" data-addr="Jl. Balai Kota No. 1, Kesawan, Medan, Sumatera Utara">🇮🇩 Medan (Sumatera Utara) [WIB / UTC+7]</option>
                    </optgroup>
                    <optgroup label="🇮🇩 Indonesia - WITA (UTC+8)">
                        <option value="makassar" data-lat="-5.1477" data-lon="119.4327" data-tz="Asia/Makassar" data-addr="Jl. Jend. Urip Sumoharjo No. 88, Makassar, Sulawesi Selatan">🇮🇩 Makassar (Sulawesi Selatan) [WITA / UTC+8]</option>
                        <option value="denpasar" data-lat="-8.6705" data-lon="115.2126" data-tz="Asia/Makassar" data-addr="Jl. Gatot Subroto Timur, Denpasar, Bali">🇮🇩 Denpasar / Kuta (Bali) [WITA / UTC+8]</option>
                        <option value="balikpapan" data-lat="-1.2379" data-lon="116.8289" data-tz="Asia/Makassar" data-addr="Jl. Jenderal Sudirman No. 12, Balikpapan (Kawasan IKN), Kalimantan Timur">🇮🇩 Balikpapan (IKN / Kalimantan Timur) [WITA / UTC+8]</option>
                    </optgroup>
                    <optgroup label="🇮🇩 Indonesia - WIT (UTC+9)">
                        <option value="jayapura" data-lat="-2.5489" data-lon="140.7181" data-tz="Asia/Jayapura" data-addr="Jl. Percetakan Negara No. 5, Gurabesi, Jayapura, Papua">🇮🇩 Jayapura (Papua) [WIT / UTC+9]</option>
                        <option value="ambon" data-lat="-3.6547" data-lon="128.1906" data-tz="Asia/Jayapura" data-addr="Jl. Pattimura No. 1, Sirimau, Kota Ambon, Maluku">🇮🇩 Ambon (Maluku) [WIT / UTC+9]</option>
                    </optgroup>
                </select>
            </div>

            <!-- Timezone & Radius Input -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Zona Waktu Operasional *') }}</label>
                    <select name="timezone" id="timezoneInput" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <optgroup label="🇸🇦 {{ __('Arab Saudi') }}">
                            <option value="Asia/Riyadh" {{ old('timezone', $branch->timezone) === 'Asia/Riyadh' ? 'selected' : '' }}>🇸🇦 AST - Waktu Standar Arab Saudi (UTC+3 / Riyadh, Jeddah, Makkah, Madinah)</option>
                        </optgroup>
                        <optgroup label="🇮🇩 {{ __('Indonesia') }}">
                            <option value="Asia/Jakarta" {{ old('timezone', $branch->timezone) === 'Asia/Jakarta' ? 'selected' : '' }}>🇮🇩 WIB - Waktu Indonesia Barat (UTC+7 / Jakarta, Jawa, Sumatera)</option>
                            <option value="Asia/Makassar" {{ old('timezone', $branch->timezone) === 'Asia/Makassar' ? 'selected' : '' }}>🇮🇩 WITA - Waktu Indonesia Tengah (UTC+8 / Bali, Sulawesi, Balikpapan)</option>
                            <option value="Asia/Jayapura" {{ old('timezone', $branch->timezone) === 'Asia/Jayapura' ? 'selected' : '' }}>🇮🇩 WIT - Waktu Indonesia Timur (UTC+9 / Jayapura, Maluku, Papua)</option>
                        </optgroup>
                    </select>
                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1.5" id="liveTzClock">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span id="liveTzTimeText">{{ __('Memuat waktu lokal...') }}</span>
                    </p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Batas Radius Presensi GPS (Meter) *') }}</label>
                    <div class="relative">
                        <input type="number" name="radius_meters" id="radiusInput" value="{{ old('radius_meters', $branch->radius_meters ?? 100) }}" required min="10" max="50000" step="5"
                            class="w-full pr-16 pl-3.5 rtl:pl-16 rtl:pr-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <span class="absolute right-3.5 rtl:left-3.5 rtl:right-auto top-2.5 text-slate-400 font-medium">{{ __('Meter') }}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1">{{ __('Karyawan wajib berada dalam lingkaran radius ini saat presensi via GPS') }}</p>
                </div>
            </div>

            <!-- Currency & Country Code Input -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Mata Uang Penggajian Cabang (Currency) *') }}</label>
                    <select name="currency" id="currencyInput" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="IDR" {{ old('currency', $branch->currency ?? 'IDR') === 'IDR' ? 'selected' : '' }}>🇮🇩 IDR - Rupiah Indonesia (Rp)</option>
                        <option value="SAR" {{ old('currency', $branch->currency ?? 'IDR') === 'SAR' ? 'selected' : '' }}>🇸🇦 SAR - Riyal Arab Saudi (SAR / ر.س)</option>
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Mata uang ini otomatis menjadi acuan payroll karyawan yang ditempatkan di cabang ini.') }}</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Negara Penempatan (Country) *') }}</label>
                    <select name="country_code" id="countryCodeInput" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-medium focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                        <option value="ID" {{ old('country_code', $branch->country_code ?? 'ID') === 'ID' ? 'selected' : '' }}>🇮🇩 {{ __('Indonesia') }}</option>
                        <option value="SA" {{ old('country_code', $branch->country_code ?? 'ID') === 'SA' ? 'selected' : '' }}>🇸🇦 {{ __('Arab Saudi') }} (Kingdom of Saudi Arabia)</option>
                    </select>
                    <span class="text-[11px] text-slate-400 mt-1 block">{{ __('Menentukan regulasi ketenagakerjaan resmi (Depnaker RI atau Pasal 107 UU Arab Saudi).') }}</span>
                </div>
            </div>

            <!-- GPS Coordinates (Latitude & Longitude) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Titik Koordinat Latitude (Lintang) *') }}</label>
                    <input type="number" step="0.0000001" name="latitude" id="latInput" value="{{ old('latitude', $branch->latitude ?? -6.2088) }}" placeholder="-6.2088 atau 24.7136" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Titik Koordinat Longitude (Bujur) *') }}</label>
                    <input type="number" step="0.0000001" name="longitude" id="lonInput" value="{{ old('longitude', $branch->longitude ?? 106.8456) }}" placeholder="106.8456 atau 46.6753" required
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition">
                </div>
            </div>

            <!-- Interactive Geofence Map -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold text-slate-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        {{ __('Visual Peta Interaktif & Lingkaran Geofencing') }}
                    </span>
                    <span class="text-[11px] text-slate-400 font-mono" id="mapCoordsDisplay">
                        Lat: {{ number_format($branch->latitude ?? -6.2088, 5) }}, Lon: {{ number_format($branch->longitude ?? 106.8456, 5) }}
                    </span>
                </div>

                <div id="branchMap" class="w-full h-72 rounded-2xl border border-slate-200 bg-slate-100 z-10 shadow-inner"></div>

                <div class="flex items-center justify-between text-[11px] text-slate-500 bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-100">
                    <span class="flex items-center gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500/40 border border-blue-600 inline-block"></span>
                        {{ __('Klik pada peta atau geser pin merah untuk mengubah titik koordinat secara langsung') }}
                    </span>
                    <a id="externalMapLink" href="https://www.google.com/maps?q={{ $branch->latitude ?? -6.2088 }},{{ $branch->longitude ?? 106.8456 }}" target="_blank" class="text-blue-600 hover:underline font-semibold flex items-center gap-0.5">
                        {{ __('Buka di Google Maps') }} ↗
                    </a>
                </div>
            </div>

            <!-- Active Status Toggle -->
            <div class="pt-2 border-t border-slate-100">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $branch->is_active) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                    <span class="text-xs font-semibold text-slate-700">{{ __('Kantor Cabang Aktif Operasional') }}</span>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('organization.index', ['tab' => 'branches']) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-2">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ __('Perbarui Kantor Cabang') }}
            </button>
        </div>

    </form>

</div>

{{-- Leaflet Map Assets --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const latInput = document.getElementById('latInput');
    const lonInput = document.getElementById('lonInput');
    const radiusInput = document.getElementById('radiusInput');
    const timezoneInput = document.getElementById('timezoneInput');
    const cityPresetSelect = document.getElementById('cityPresetSelect');
    const mapCoordsDisplay = document.getElementById('mapCoordsDisplay');
    const externalMapLink = document.getElementById('externalMapLink');
    const liveTzTimeText = document.getElementById('liveTzTimeText');
    const btnDetectGps = document.getElementById('btnDetectGps');
    const branchAddressInput = document.getElementById('branchAddressInput');

    let curLat = parseFloat(latInput.value) || {{ $branch->latitude ?? -6.2088 }};
    let curLon = parseFloat(lonInput.value) || {{ $branch->longitude ?? 106.8456 }};
    let curRadius = parseInt(radiusInput.value) || {{ $branch->radius_meters ?? 100 }};

    // 1. Live Timezone Clock
    function updateTzClock() {
        const tz = timezoneInput.value || 'Asia/Jakarta';
        try {
            const now = new Date();
            const timeStr = new Intl.DateTimeFormat('id-ID', {
                timeZone: tz,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            }).format(now);

            const tzNames = {
                'Asia/Jakarta': 'WIB (UTC+7)',
                'Asia/Makassar': 'WITA (UTC+8)',
                'Asia/Jayapura': 'WIT (UTC+9)',
                'Asia/Riyadh': 'AST (UTC+3 - Arab Saudi)'
            };
            const tzName = tzNames[tz] || tz;
            liveTzTimeText.textContent = `Waktu saat ini (${tzName}): ${timeStr}`;
        } catch (e) {
            liveTzTimeText.textContent = tz;
        }
    }
    updateTzClock();
    setInterval(updateTzClock, 1000);
    timezoneInput.addEventListener('change', updateTzClock);

    // 2. Leaflet Map Initialization
    let map, marker, circle;

    function initMap() {
        if (typeof L === 'undefined') {
            console.warn('Leaflet library not loaded.');
            return;
        }

        map = L.map('branchMap', {
            center: [curLat, curLon],
            zoom: 15,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        marker = L.marker([curLat, curLon], {
            draggable: true
        }).addTo(map);

        circle = L.circle([curLat, curLon], {
            color: '#2563EB',
            fillColor: '#3B82F6',
            fillOpacity: 0.25,
            radius: curRadius,
            weight: 2
        }).addTo(map);

        marker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateCoordinates(pos.lat, pos.lng, false);
        });

        map.on('click', function (e) {
            updateCoordinates(e.latlng.lat, e.latlng.lng, true);
        });

        setTimeout(() => map.invalidateSize(), 300);
    }

    function updateCoordinates(lat, lon, moveMarker = true) {
        curLat = parseFloat(lat.toFixed(7));
        curLon = parseFloat(lon.toFixed(7));

        latInput.value = curLat;
        lonInput.value = curLon;

        mapCoordsDisplay.textContent = `Lat: ${curLat.toFixed(5)}, Lon: ${curLon.toFixed(5)}`;
        externalMapLink.href = `https://www.google.com/maps?q=${curLat},${curLon}`;

        if (marker && moveMarker) {
            marker.setLatLng([curLat, curLon]);
        }
        if (circle) {
            circle.setLatLng([curLat, curLon]);
            circle.setRadius(curRadius);
        }
        if (map && moveMarker) {
            map.panTo([curLat, curLon]);
        }
    }

    function updateRadius(radius) {
        curRadius = parseInt(radius) || 100;
        if (circle) {
            circle.setRadius(curRadius);
        }
    }

    latInput.addEventListener('input', () => updateCoordinates(parseFloat(latInput.value) || 0, curLon, true));
    lonInput.addEventListener('input', () => updateCoordinates(curLat, parseFloat(lonInput.value) || 0, true));
    radiusInput.addEventListener('input', () => updateRadius(radiusInput.value));

    // 3. City Preset Handler
    cityPresetSelect.addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        if (!opt || !opt.dataset.lat) return;

        const lat = parseFloat(opt.dataset.lat);
        const lon = parseFloat(opt.dataset.lon);
        const tz = opt.dataset.tz;
        const addr = opt.dataset.addr;

        if (tz) {
            timezoneInput.value = tz;
            updateTzClock();

            const currencyInput = document.getElementById('currencyInput');
            const countryCodeInput = document.getElementById('countryCodeInput');
            if (currencyInput && countryCodeInput) {
                if (tz === 'Asia/Riyadh') {
                    currencyInput.value = 'SAR';
                    countryCodeInput.value = 'SA';
                } else {
                    currencyInput.value = 'IDR';
                    countryCodeInput.value = 'ID';
                }
            }
        }

        if (addr && !branchAddressInput.value.trim()) {
            branchAddressInput.value = addr;
        }

        updateCoordinates(lat, lon, true);
        if (map) {
            map.setView([lat, lon], 14);
        }
    });

    // 4. GPS Geolocation Detection
    btnDetectGps.addEventListener('click', function () {
        if (!navigator.geolocation) {
            alert('Browser Anda tidak mendukung deteksi geolokasi GPS.');
            return;
        }

        const originalText = btnDetectGps.innerHTML;
        btnDetectGps.innerHTML = '<span>Mendeteksi GPS...</span>';
        btnDetectGps.disabled = true;

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                updateCoordinates(lat, lon, true);
                if (map) {
                    map.setView([lat, lon], 16);
                }
                btnDetectGps.innerHTML = originalText;
                btnDetectGps.disabled = false;
            },
            function (err) {
                alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                btnDetectGps.innerHTML = originalText;
                btnDetectGps.disabled = false;
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });

    if (typeof L !== 'undefined') {
        initMap();
    } else {
        window.addEventListener('load', initMap);
    }
});
</script>
@endsection

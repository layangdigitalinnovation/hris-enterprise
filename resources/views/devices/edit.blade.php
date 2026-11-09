@extends('layouts.app', ['headerTitle' => __('Edit Mesin Absensi Solution'), 'title' => __('Edit Mesin Solution')])

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Edit Mesin Absensi Solution') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Perbarui konfigurasi alamat IP, port soket, serial number, atau metode koneksi') }}</p>
        </div>
        <a href="{{ route('devices.index') }}" class="text-xs text-slate-500 hover:text-slate-900 flex items-center gap-1.5 transition">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('devices.update', $device) }}" method="POST" class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-5 text-xs">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Label Mesin *') }}</label>
            <input type="text" name="name" value="{{ old('name', $device->name) }}" placeholder="{{ __('Contoh: Solution X105 - Klinik DilaRegi') }}" required
                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Tipe / Model Mesin Solution *') }}</label>
                <input type="text" name="device_model" value="{{ old('device_model', $device->device_model) }}" required
                    placeholder="{{ __('Contoh: Solution X105') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nomor Seri Mesin (SN)') }}</label>
                <input type="text" name="serial_number" value="{{ old('serial_number', $device->serial_number) }}" placeholder="{{ __('Contoh: NHZ4262200515') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Alamat IP Mesin di LAN (TCP/IP)') }}</label>
                <input type="text" name="ip_address" value="{{ old('ip_address', $device->ip_address) }}" placeholder="192.168.1.201"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Port Soket *') }}</label>
                <input type="number" name="port" value="{{ old('port', $device->port) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Comm Key / Password Mesin *') }}</label>
                <input type="text" name="comm_key" value="{{ old('comm_key', $device->comm_key) }}" required
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-[10px] text-slate-500 mt-1 block">{{ __('Default Solution adalah 0') }}</span>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Metode Komunikasi *') }}</label>
                <select name="sync_method" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="lan_tcp" {{ old('sync_method', $device->sync_method) === 'lan_tcp' ? 'selected' : '' }}>{{ __('Koneksi Langsung LAN TCP/IP (4370)') }}</option>
                    <option value="cloud_adms" {{ old('sync_method', $device->sync_method) === 'cloud_adms' ? 'selected' : '' }}>{{ __('Cloud ADMS / Webhook') }}</option>
                    <option value="file_import" {{ old('sync_method', $device->sync_method) === 'file_import' ? 'selected' : '' }}>{{ __('Manual Export USB Flashdisk') }}</option>
                    <option value="mock" {{ old('sync_method', $device->sync_method) === 'mock' ? 'selected' : '' }}>{{ __('Simulator Virtual (Pengujian)') }}</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Lokasi Cabang') }}</label>
                <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">{{ __('-- Semua Lokasi --') }}</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string)old('branch_id', $device->branch_id) === (string)$branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Titik / Ruangan Penempatan') }}</label>
                <input type="text" name="location" value="{{ old('location', $device->location) }}" placeholder="{{ __('Contoh: Pintu Depan / Resepsionis') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Status Perangkat') }}</label>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="online" {{ old('status', $device->status) === 'online' ? 'selected' : '' }}>{{ __('ONLINE') }}</option>
                    <option value="offline" {{ old('status', $device->status) === 'offline' ? 'selected' : '' }}>{{ __('OFFLINE') }}</option>
                    <option value="disabled" {{ old('status', $device->status) === 'disabled' ? 'selected' : '' }}>{{ __('Nonaktif (Disabled)') }}</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Interval Sinkronisasi Otomatis') }}</label>
            <select name="sync_interval_minutes" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="5" {{ old('sync_interval_minutes', $device->sync_interval_minutes) == 5 ? 'selected' : '' }}>{{ __('Setiap 5 Menit') }}</option>
                <option value="15" {{ old('sync_interval_minutes', $device->sync_interval_minutes) == 15 ? 'selected' : '' }}>{{ __('Setiap 15 Menit') }}</option>
                <option value="30" {{ old('sync_interval_minutes', $device->sync_interval_minutes) == 30 ? 'selected' : '' }}>{{ __('Setiap 30 Menit') }}</option>
                <option value="60" {{ old('sync_interval_minutes', $device->sync_interval_minutes) == 60 ? 'selected' : '' }}>{{ __('Setiap 1 Jam') }}</option>
            </select>
        </div>

        <div class="pt-2">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="auto_sync" value="1" {{ old('auto_sync', $device->auto_sync) ? 'checked' : '' }} class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                <span class="text-xs text-slate-700">{{ __('Aktifkan sinkronisasi otomatis terjadwal') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-between pt-4 border-t border-slate-100">
            <a href="{{ route('devices.index') }}" class="py-2.5 px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                {{ __('Batal') }}
            </a>
            <div class="flex items-center gap-2">
                <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm shadow-blue-500/20 transition">
                    {{ __('Simpan Perubahan') }}
                </button>
            </div>
        </div>
    </form>

</div>
@endsection

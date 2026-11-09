@extends('layouts.app', ['headerTitle' => __('Audit Trail & Pengaturan Sistem'), 'title' => __('Audit & Pengaturan')])

@section('content')
<div class="space-y-8" x-data="{ tab: '{{ $currentTab ?? request('tab', 'audit') }}' }">

    {{-- Header --}}
    <div>
        <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Audit Trail Aktivitas & Konfigurasi Sistem') }}</h3>
        <p class="text-xs text-slate-500">{{ __('Pencatatan riwayat keamanan seluruh aksi pengguna serta parameter konfigurasi aplikasi') }}</p>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 border-b border-slate-200 pb-3 text-xs font-semibold">
        <button @click="tab = 'audit'" :class="tab === 'audit' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
            {{ __('Log Aktivitas Sistem (Audit Trail)') }}
        </button>
        <button @click="tab = 'settings'" :class="tab === 'settings' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100'" class="px-4 py-2 rounded-xl transition">
            {{ __('Pengaturan Aplikasi & Perusahaan') }}
        </button>
    </div>

    {{-- TAB 1: AUDIT TRAIL --}}
    <div x-show="tab === 'audit'" class="space-y-4">
        {{-- Filter Bar --}}
        <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('audit.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('Cari deskripsi aktivitas...') }}"
                        class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <select name="event" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">{{ __('Semua Event') }}</option>
                        <option value="login" {{ request('event') === 'login' ? 'selected' : '' }}>{{ __('Login') }}</option>
                        <option value="logout" {{ request('event') === 'logout' ? 'selected' : '' }}>{{ __('Logout') }}</option>
                        <option value="create_employee" {{ request('event') === 'create_employee' ? 'selected' : '' }}>{{ __('Create Employee') }}</option>
                        <option value="update_employee" {{ request('event') === 'update_employee' ? 'selected' : '' }}>{{ __('Update Employee') }}</option>
                        <option value="calculate_payroll" {{ request('event') === 'calculate_payroll' ? 'selected' : '' }}>{{ __('Calculate Payroll') }}</option>
                        <option value="approve_overtime" {{ request('event') === 'approve_overtime' ? 'selected' : '' }}>{{ __('Approve Overtime') }}</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm shadow-blue-500/20 transition">
                        {{ __('Filter Audit') }}
                    </button>
                    <a href="{{ route('audit.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 font-semibold transition">
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full ltr:text-left rtl:text-right text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4 font-semibold">{{ __('Waktu Kejadian') }}</th>
                            <th class="py-3 px-4 font-semibold">{{ __('Pengguna') }}</th>
                            <th class="py-3 px-4 font-semibold">{{ __('Tipe Event') }}</th>
                            <th class="py-3 px-4 font-semibold">{{ __('Deskripsi Aktivitas') }}</th>
                            <th class="py-3 px-4 font-semibold">{{ __('Alamat IP') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-mono">
                        @forelse ($logs as $log)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $log->created_at->format('Y-m-d H:i:s') }}
                                </td>
                                <td class="py-3 px-4 font-sans">
                                    <span class="font-bold text-slate-900 block">{{ $log->user->name ?? __('Sistem / Anonim') }}</span>
                                    <span class="text-[10px] text-slate-500 capitalize">{{ __($log->user->role ?? '-') }}</span>
                                </td>
                                <td class="py-3 px-4 font-sans">
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 font-mono uppercase">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-sans text-slate-700">
                                    {{ $log->translated_description ?? $log->description }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ $log->ip_address ?? '127.0.0.1' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500 font-sans">
                                    {{ __('Belum ada log aktivitas tercatat.') }}
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

    {{-- TAB 2: SYSTEM SETTINGS --}}
    <div x-show="tab === 'settings'" class="max-w-3xl p-6 sm:p-7 rounded-3xl bg-white border border-slate-200/80 shadow-sm space-y-6">
        <h4 class="font-bold text-slate-900 text-base border-b border-slate-100 pb-3">{{ __('Konfigurasi Parameter Aplikasi') }}</h4>

        <form action="{{ route('audit.settings.update') }}" method="POST" class="space-y-4 text-xs">
            @csrf
            <div>
                <label class="block font-semibold text-slate-700 mb-1">{{ __('Nama Perusahaan / Organisasi') }}</label>
                <input type="text" name="company_name" value="{{ App\Models\SystemSetting::get('company_name', 'PT Solusi Teknologi Nusantara') }}"
                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">{{ __('Nomor Telepon Perusahaan') }}</label>
                    <input type="text" name="company_phone" value="{{ App\Models\SystemSetting::get('company_phone', '(021) 555-8900') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">{{ __('Tanggal Cut-Off Payroll Standar') }}</label>
                    <input type="number" name="payroll_cut_off_day" value="{{ App\Models\SystemSetting::get('payroll_cut_off_day', 25) }}" min="1" max="31"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">{{ __('Alamat Kantor Pusat') }}</label>
                <textarea name="company_address" rows="2" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ App\Models\SystemSetting::get('company_address', 'Menara Satrio Lt. 18, Mega Kuningan, Jakarta Selatan') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">{{ __('Toleransi Keterlambatan Default (Menit)') }}</label>
                    <input type="number" name="late_tolerance_default" value="{{ App\Models\SystemSetting::get('late_tolerance_default', 15) }}"
                        class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">{{ __('Hari Kerja per Minggu') }}</label>
                    <select name="work_days_per_week" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="5" {{ App\Models\SystemSetting::get('work_days_per_week') == '5' ? 'selected' : '' }}>{{ __('5 Hari (Senin - Jumat)') }}</option>
                        <option value="6" {{ App\Models\SystemSetting::get('work_days_per_week') == '6' ? 'selected' : '' }}>{{ __('6 Hari (Senin - Sabtu)') }}</option>
                    </select>
                </div>
            </div>

            {{-- Overtime Policy & Standard Rate --}}
            <div class="pt-3 border-t border-slate-100 space-y-3">
                <span class="font-bold text-slate-900 block text-xs uppercase tracking-wider text-blue-600">{{ __('Kebijakan & Rate Lembur Standar Perusahaan') }}</span>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">{{ __('Metode Perhitungan Lembur') }}</label>
                        <select name="overtime_calculation_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="flat" {{ App\Models\SystemSetting::get('overtime_calculation_type', 'flat') == 'flat' ? 'selected' : '' }}>{{ __('Tarif Tetap per Jam (Flat Rate)') }}</option>
                            <option value="depnaker" {{ App\Models\SystemSetting::get('overtime_calculation_type') == 'depnaker' ? 'selected' : '' }}>{{ __('Formula Depnaker (1/173 × Gaji Pokok)') }}</option>
                            <option value="saudi_article_107" {{ App\Models\SystemSetting::get('overtime_calculation_type') == 'saudi_article_107' ? 'selected' : '' }}>{{ __('Pasal 107 UU Arab Saudi (Upah/Jam + 50% Gaji Pokok)') }}</option>
                            <option value="auto_country" {{ App\Models\SystemSetting::get('overtime_calculation_type') == 'auto_country' ? 'selected' : '' }}>{{ __('Otomatis Sesuai Negara Cabang (Indonesia & Arab Saudi)') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">{{ __('Pembagi Formula Depnaker') }}</label>
                        <input type="number" name="overtime_depnaker_divisor" value="{{ App\Models\SystemSetting::get('overtime_depnaker_divisor', 173) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">{{ __('Rate Lembur Standar Hari Kerja (Per Jam)') }}</label>
                        <input type="number" name="overtime_rate_per_hour" value="{{ App\Models\SystemSetting::get('overtime_rate_per_hour', 35000) }}" min="0" step="any"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">{{ __('Rate Lembur Akhir Pekan / Libur (Per Jam)') }}</label>
                        <input type="number" name="overtime_rate_weekend_per_hour" value="{{ App\Models\SystemSetting::get('overtime_rate_weekend_per_hour', 50000) }}" min="0" step="any"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            {{-- Multi-Currency & Kurs Konversi (IDR & SAR) --}}
            <div class="pt-3 border-t border-slate-100 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <span class="font-bold text-slate-900 text-xs uppercase tracking-wider text-blue-600 flex items-center gap-1.5 whitespace-nowrap shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="whitespace-nowrap">{{ __('Pengaturan Multi-Mata Uang & Kurs Valuta (IDR & SAR)') }}</span>
                    </span>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('payroll.exchange-rate.edit', ['redirect_to' => url()->current()]) }}" class="px-2.5 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-[10px] font-bold transition flex items-center gap-1.5 cursor-pointer whitespace-nowrap shrink-0">
                            <span>💱</span>
                            <span class="whitespace-nowrap">{{ __('Kalkulator Simulasi Kurs') }}</span>
                        </a>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap shrink-0">
                            🇮🇩 IDR · 🇸🇦 SAR
                        </span>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">{{ __('Mata Uang Acuan Sistem (Base Currency)') }}</label>
                        <select name="base_currency" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="IDR" {{ App\Models\SystemSetting::get('base_currency', 'IDR') == 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah (Rp)</option>
                            <option value="SAR" {{ App\Models\SystemSetting::get('base_currency') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Riyal (ر.س)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">{{ __('Kurs Konversi 1 SAR ke IDR (Rupiah)') }}</label>
                        <input type="number" name="exchange_rate_sar_to_idr" value="{{ App\Models\SystemSetting::get('exchange_rate_sar_to_idr', 4250) }}" min="1" step="1"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-slate-900 font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <span class="text-[10px] text-slate-500 mt-1 block">{{ __('Kurs acuan untuk konversi ringkasan rekapitulasi penggajian antar cabang RI & SA.') }}</span>
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="py-2.5 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm shadow-blue-500/20 transition">
                    {{ __('Simpan Pengaturan') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@extends('layouts.app')

@section('title', __('Tambah Kategori Reimbursement'))

@section('content')
<div class="max-w-3xl mx-auto space-y-6 pb-12" x-data="{
    currency: '{{ old('currency', 'ALL') }}'
}">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Reimbursement') }}</a>
                <span>/</span>
                <a href="{{ route('reimbursements.categories.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Master Kategori') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Tambah Kategori') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </span>{{ __('Tambah Kategori Reimbursement Baru') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Tentukan jenis pengeluaran dinas, ketentuan mata uang (IDR/SAR), dan plafon maksimal per klaim.') }}</p>
        </div>

        <a href="{{ route('reimbursements.categories.index') }}" class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap shrink-0 self-start sm:self-auto">
            <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Master') }}</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <div class="font-bold flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ __('Terdapat kesalahan pada isian formulir:') }}</span>
            </div>
            <ul class="list-disc pl-5 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('reimbursements.categories.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Bagian 1: Identitas & Kode Kategori -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Identitas & Informasi Kategori') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Nama resmi pengeluaran dan kode unik referensi sistem.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Nama Kategori Reimbursement *') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="{{ __('Contoh: Transportasi & Perjalanan Dinas') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Nama kategori yang akan muncul pada menu pemilihan klaim karyawan.') }}</span>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Kode Unik *') }}</label>
                    <input type="text" name="code" value="{{ old('code') }}" required maxlength="10" placeholder="{{ __('Contoh: TRV') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 font-mono uppercase placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Singkatan unik (maks 10 karakter alfabet).') }}</span>
                </div>

                <div class="sm:col-span-3">
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Deskripsi & Syarat Pengeluaran (Opsional)') }}</label>
                    <textarea name="description" rows="3" placeholder="{{ __('Tuliskan panduan atau jenis pengeluaran apa saja yang diperbolehkan di bawah kategori ini...') }}"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition">{{ old('description') }}</textarea>
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Penjelasan ini akan tampil sebagai panduan saat karyawan memilih kategori ini.') }}</span>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Ketentuan Mata Uang & Batas Plafon -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-5">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Ketentuan Mata Uang & Batas Plafon Maksimal') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Atur mata uang yang diizinkan dan batasan nominal penggantian dana.') }}</p>
                </div>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Mata Uang Yang Diizinkan *') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                            :class="currency === 'ALL' ? 'border-indigo-500 bg-indigo-50/50 text-indigo-900 font-bold shadow-xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                            <input type="radio" name="currency" value="ALL" x-model="currency" class="text-indigo-600">
                            <div>
                                <div class="font-bold">🌐 {{ __('Semua Mata Uang') }}</div>
                                <div class="text-[10px] text-slate-500 font-normal">{{ __('Berlaku untuk IDR & SAR') }}</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                            :class="currency === 'IDR' ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-bold shadow-xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                            <input type="radio" name="currency" value="IDR" x-model="currency" class="text-blue-600">
                            <div>
                                <div class="font-bold">🇮🇩 {{ __('Khusus IDR') }}</div>
                                <div class="text-[10px] text-slate-500 font-normal">{{ __('Hanya transaksi Rupiah') }}</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-2.5 p-3 rounded-2xl border cursor-pointer transition-all"
                            :class="currency === 'SAR' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold shadow-xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">
                            <input type="radio" name="currency" value="SAR" x-model="currency" class="text-emerald-600">
                            <div>
                                <div class="font-bold">🇸🇦 {{ __('Khusus SAR') }}</div>
                                <div class="text-[10px] text-slate-500 font-normal">{{ __('Hanya transaksi Riyal') }}</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Input Batas Plafon Dinamis Sesuai Mata Uang -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    <div x-show="currency === 'ALL' || currency === 'IDR'" :class="currency === 'IDR' ? 'sm:col-span-2' : ''">
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Batas Plafon IDR (Rupiah)') }} <span class="text-slate-400 font-normal text-[11px]">({{ __('Kosongkan jika tanpa batas') }})</span>
                        </label>
                        <div class="flex items-center w-full rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all cursor-text"
                            @click="$refs.maxAmountIdr?.focus()">
                            <span class="text-xs font-mono font-bold text-slate-400 select-none shrink-0">{{ __('Rp') }}</span>
                            <input type="number" step="0.01" min="0" name="max_amount" x-ref="maxAmountIdr" value="{{ old('max_amount') }}" placeholder="{{ __('Contoh: 5000000') }}"
                                class="w-full px-2.5 py-1 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0">
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Batas maksimal pengeluaran per satu kali pengajuan klaim dalam Rupiah.') }}</span>
                    </div>

                    <div x-show="currency === 'ALL' || currency === 'SAR'" :class="currency === 'SAR' ? 'sm:col-span-2' : ''">
                        <label class="block font-semibold text-slate-700 mb-1.5">{{ __('Batas Plafon SAR (Saudi Riyal)') }} <span class="text-slate-400 font-normal text-[11px]">({{ __('Kosongkan jika tanpa batas') }})</span>
                        </label>
                        <div class="flex items-center w-full rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 focus-within:ring-2 focus-within:ring-emerald-500/20 focus-within:border-emerald-500 transition-all cursor-text"
                            @click="$refs.maxAmountSar?.focus()">
                            <span class="text-xs font-mono font-bold text-slate-400 select-none shrink-0">{{ __('SAR') }}</span>
                            <input type="number" step="0.01" min="0" name="max_amount_sar" x-ref="maxAmountSar" value="{{ old('max_amount_sar') }}" placeholder="{{ __('Contoh: 1200.00') }}"
                                class="w-full px-2.5 py-1 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0">
                        </div>
                        <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Batas maksimal pengeluaran per satu kali pengajuan klaim dalam Saudi Riyal.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Kebijakan Nota & Status Kategori -->
        <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Kebijakan Nota & Status Keaktifan') }}</h2>
                    <p class="text-[11px] text-slate-500">{{ __('Ketentuan lampiran bukti fisik dan ketersediaan opsi bagi karyawan.') }}</p>
                </div>
            </div>

            <div class="space-y-3 pt-1 text-xs">
                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="requires_receipt" value="1" {{ old('requires_receipt', '1') ? 'checked' : '' }} class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="font-bold text-slate-800">{{ __('Wajib Lampirkan Bukti Fisik / Nota Kuitansi') }}</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Karyawan wajib mengunggah foto kuitansi/struk atau invoice resmi saat mengajukan klaim di kategori ini.') }}</p>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 hover:bg-slate-50 cursor-pointer transition">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="mt-0.5 rounded text-blue-600 focus:ring-blue-500">
                    <div>
                        <span class="font-bold text-slate-800">{{ __('Status Kategori Aktif') }}</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ __('Jika dicentang, kategori ini dapat langsung dipilih oleh karyawan pada formulir pengajuan reimbursement.') }}</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('reimbursements.categories.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap">{{ __('Batal') }}</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ __('Simpan Kategori Baru') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


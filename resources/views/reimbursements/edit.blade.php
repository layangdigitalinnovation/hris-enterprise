@extends('layouts.app')

@section('title', 'Edit Klaim Reimbursement: ' . $reimbursement->claim_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12" x-data="{
    currency: '{{ old('currency', $reimbursement->currency) }}',
    amount: '{{ old('amount', $reimbursement->amount) }}',
    selectedCategory: '{{ old('category_id', $reimbursement->category_id) }}',
    receiptPreview: '{{ $reimbursement->receipt_url }}',
    receiptFileName: '{{ basename($reimbursement->receipt_path ?? '') }}',
    categories: {{ Js::from($categories) }},
    get activeCategory() {
        return this.categories.find(c => c.id == this.selectedCategory) || null;
    },
    get currencyPrefix() {
        return this.currency === 'SAR' ? 'SAR' : 'Rp';
    },
    isCategorySupported(cat) {
        if (!cat) return true;
        if (!cat.currency || cat.currency === 'ALL') return true;
        return cat.currency === this.currency;
    },
    get maxLimitForCurrentCurrency() {
        if (!this.activeCategory) return null;
        if (this.currency === 'SAR') {
            return (this.activeCategory.max_amount_sar !== null && this.activeCategory.max_amount_sar !== undefined)
                ? Number(this.activeCategory.max_amount_sar)
                : (this.activeCategory.currency === 'SAR' && this.activeCategory.max_amount ? Number(this.activeCategory.max_amount) : null);
        }
        return (this.activeCategory.max_amount !== null && this.activeCategory.max_amount !== undefined)
            ? Number(this.activeCategory.max_amount)
            : null;
    },
    setCurrency(val) {
        this.currency = val;
        if (this.activeCategory && !this.isCategorySupported(this.activeCategory)) {
            this.selectedCategory = '';
        }
    },
    handleFileChange(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.receiptFileName = file.name;
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.receiptPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            this.receiptPreview = null;
        }
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Reimbursement') }}</a>
                <span>/</span>
                <a href="{{ route('reimbursements.show', $reimbursement) }}" class="hover:text-blue-600 font-medium font-mono">{{ $reimbursement->claim_number }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Edit Klaim') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{{ __('Perbarui Data Klaim Reimbursement') }}</h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">{{ __('Perbaiki rincian pengeluaran atau perbarui bukti nota kuitansi yang sah.') }}</p>
        </div>

        <a href="{{ route('reimbursements.show', $reimbursement) }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali ke Detail') }}</span>
        </a>
    </div>

    <form action="{{ route('reimbursements.update', $reimbursement) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Bagian 1: Kategori & Informasi Pengeluaran -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">{{ __('Kategori & Informasi Pengeluaran') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('Rincian keperluan pengeluaran dana operasional.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Kategori -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>{{ __('Kategori Reimbursement *') }}</span>
                        <span class="text-[10px] text-slate-400 font-normal">{{ __('Sesuai mata uang:') }}<strong class="text-slate-700" x-text="currency"></strong>
                        </span>
                    </label>
                    <select name="category_id" x-model="selectedCategory" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                        <option value="">-- {{ __('Pilih Kategori') }} --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                :disabled="!isCategorySupported({{ Js::from($cat) }})"
                                {{ old('category_id', $reimbursement->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->code }})
                                @if($cat->currency === 'SAR')
                                    • [{{ __('Khusus SAR') }}]
                                @elseif($cat->currency === 'IDR')
                                    • [{{ __('Khusus IDR') }}]
                                @else
                                    • [{{ __('Semua Mata Uang') }}]
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <!-- Info Catatan Kategori Dinamis -->
                    <template x-if="activeCategory">
                        <div class="mt-2.5 p-3 rounded-xl border text-[11px] transition-all"
                            :class="currency === 'SAR' ? 'bg-emerald-50/70 border-emerald-200 text-emerald-950' : 'bg-blue-50/70 border-blue-200 text-blue-950'">
                            <div class="flex items-center justify-between font-semibold mb-1">
                                <span x-text="activeCategory.name"></span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                    :class="activeCategory.currency === 'SAR' ? 'bg-emerald-200/70 text-emerald-800' : (activeCategory.currency === 'IDR' ? 'bg-blue-200/70 text-blue-800' : 'bg-slate-200 text-slate-700')"
                                    x-text="activeCategory.currency === 'SAR' ? '🇸🇦 {{ __('Khusus SAR') }}' : (activeCategory.currency === 'IDR' ? '🇮🇩 {{ __('Khusus IDR') }}' : '🌐 {{ __('Semua (IDR & SAR)') }}')">
                                </span>
                            </div>
                            <div class="text-slate-600 text-[11px]" x-text="activeCategory.description"></div>
                            <div class="mt-2 pt-2 border-t border-slate-200/60 font-mono font-bold flex items-center justify-between text-xs">
                                <span class="text-slate-600 font-sans font-semibold">{{ __('Batas Plafon (') }}<span x-text="currency"></span>):</span>
                                <template x-if="maxLimitForCurrentCurrency">
                                    <span :class="currency === 'SAR' ? 'text-emerald-700' : 'text-blue-700'">
                                        <span x-text="currencyPrefix"></span> <span x-text="currency === 'SAR' ? Number(maxLimitForCurrentCurrency).toLocaleString('en-US', {minimumFractionDigits: 2}) : Number(maxLimitForCurrentCurrency).toLocaleString('id-ID')"></span>
                                    </span>
                                </template>
                                <template x-if="!maxLimitForCurrentCurrency">
                                    <span class="text-slate-400 font-normal italic">{{ __('Tanpa Batas (Bebas Plafon)') }}</span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Tanggal Pengeluaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Tanggal Pengeluaran / Transaksi *') }}</label>
                    <input type="date" name="claim_date" value="{{ old('claim_date', $reimbursement->claim_date->format('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <!-- Keperluan / Judul Singkat -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Keperluan Klaim (Judul Singkat) *') }}</label>
                    <input type="text" name="title" value="{{ old('title', $reimbursement->title) }}" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">
                </div>

                <!-- Deskripsi Rinci -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Keterangan Rinci / Catatan Pengeluaran') }}</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all">{{ old('description', $reimbursement->description) }}</textarea>
                </div>
            </div>
        </div>

        <!-- Bagian 2: Nominal & Mata Uang -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">{{ __('Nominal Pengeluaran & Mata Uang') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('Tentukan jumlah rupiah atau riyal yang akan diganti.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Mata Uang -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Mata Uang Pengeluaran *') }}</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                            :class="currency === 'IDR' ? 'border-blue-500 bg-blue-50/50 text-blue-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            @click="setCurrency('IDR')">
                            <input type="radio" name="currency" value="IDR" x-model="currency" class="text-blue-600">
                            <span>🇮🇩 {{ __('IDR (Rupiah)') }}</span>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border cursor-pointer transition-all"
                            :class="currency === 'SAR' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                            @click="setCurrency('SAR')">
                            <input type="radio" name="currency" value="SAR" x-model="currency" class="text-emerald-600">
                            <span>🇸🇦 {{ __('SAR (Riyal)') }}</span>
                        </label>
                    </div>
                </div>

                <!-- Nominal Pengajuan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Nominal Yang Diajukan *') }}</label>
                    <div class="flex items-center w-full rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-500 transition-all cursor-text"
                        @click="$refs.amountInput?.focus()" dir="ltr">
                        <span class="text-xs font-mono font-bold text-slate-500 select-none shrink-0 mr-2" x-text="currencyPrefix"></span>
                        <input type="number" step="0.01" min="1" name="amount" x-ref="amountInput" x-model="amount" value="{{ old('amount', $reimbursement->amount) }}" required
                            class="w-full py-1 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0 text-left">
                    </div>

                    <!-- Peringatan Batas Maksimal / Plafon -->
                    <template x-if="maxLimitForCurrentCurrency && amount && Number(amount) > maxLimitForCurrentCurrency">
                        <div class="mt-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-medium flex items-center gap-1.5">
                            <svg class="w-4 h-4 shrink-0 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>{{ __('Nominal melebihi batas plafon kategori (') }}<span x-text="currencyPrefix"></span> <span x-text="currency === 'SAR' ? Number(maxLimitForCurrentCurrency).toLocaleString('en-US', {minimumFractionDigits: 2}) : Number(maxLimitForCurrentCurrency).toLocaleString('id-ID')"></span>).
                            </span>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Bagian 3: Unggah Bukti Nota / Kwitansi -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs font-bold">3</span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800">{{ __('Bukti Nota / Kwitansi Resmi') }}</h3>
                    <p class="text-xs text-slate-400">{{ __('Pilih berkas baru jika ingin mengganti bukti yang lama.') }}</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">{{ __('Unggah Bukti Baru (Kosongkan jika tetap memakai bukti lama)') }}</label>
                <div class="border-2 border-dashed border-slate-200 hover:border-blue-400 rounded-2xl p-6 text-center transition-all cursor-pointer bg-slate-50/50"
                     @click="$refs.receiptInput.click()">
                    <input type="file" name="receipt" x-ref="receiptInput" @change="handleFileChange($event)" accept="image/jpeg,image/png,image/jpg,application/pdf" class="hidden">

                    <template x-if="receiptPreview">
                        <div class="space-y-3">
                            <img :src="receiptPreview" class="max-h-56 mx-auto rounded-xl border border-slate-200 shadow-xs">
                            <div class="text-xs font-semibold text-blue-600" x-text="receiptFileName"></div>
                            <span class="text-[11px] text-slate-400 underline">{{ __('Klik untuk mengganti gambar') }}</span>
                        </div>
                    </template>

                    <template x-if="!receiptPreview">
                        <div class="space-y-2">
                            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <div class="text-xs font-bold text-slate-700" x-text="receiptFileName || 'Klik untuk mengunggah bukti kuitansi baru'"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('reimbursements.show', $reimbursement) }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-all">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">{{ __('Simpan Perubahan Klaim') }}</button>
        </div>
    </form>
</div>
@endsection


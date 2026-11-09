@extends('layouts.app')

@section('title', __('Ajukan Pinjaman Karyawan'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6 pb-12" x-data="{
    currency: '{{ old('currency', $selectedEmployee?->effective_currency ?? 'IDR') }}',
    amount: {{ old('amount', 5000000) }},
    tenor: {{ old('tenor_months', 6) }},
    interest: {{ old('interest_rate_percent', 0) }},
    repaymentDate: '{{ old('repayment_start_date', date('Y-m-d', strtotime('+1 month'))) }}',
    payrollDeduction: true,
    filePreview: null,
    fileName: '',

    get currencySymbol() {
        return this.currency === 'SAR' ? 'SAR' : 'Rp';
    },

    get monthlyInstallment() {
        const amt = parseFloat(this.amount) || 0;
        const ten = parseInt(this.tenor) || 1;
        const intr = parseFloat(this.interest) || 0;
        if (ten <= 0) return 0;

        const totalWithInterest = amt + (amt * (intr / 100));
        return Math.round(totalWithInterest / ten);
    },

    get totalPayment() {
        const amt = parseFloat(this.amount) || 0;
        const intr = parseFloat(this.interest) || 0;
        return Math.round(amt + (amt * (intr / 100)));
    },

    formatMoney(val) {
        if (!val || isNaN(val)) return '0';
        return new Intl.NumberFormat('id-ID').format(val);
    },

    handleFileChange(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.fileName = file.name;
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                this.filePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            this.filePreview = null;
        }
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('loans.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Pinjaman Karyawan') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Formulir Pengajuan') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">
                {{ __('Pengajuan Pinjaman & Kasbon Karyawan') }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                {{ __('Pilih mata uang, tentukan nominal pinjaman, tenor cicilan bulanan, dan simulasikan jadwal angsuran secara real-time.') }}
            </p>
        </div>

        <a href="{{ route('loans.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>{{ __('Kembali') }}</span>
        </a>
    </div>

    <form action="{{ route('loans.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Section 1: Profil Peminjam -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">1</span>{{ __('Data Pegawai Pemohon Pinjaman') }}</h2>

            @if(auth()->user()->canManageLoans() && count($employees) > 0)
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('Pilih Karyawan') }} <span class="text-rose-500">*</span>
                    </label>
                    <select name="employee_id" required class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white">
                        <option value="">{{ __('-- Pilih Nama Karyawan --') }}</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employee_id', $selectedEmployee?->id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->employee_code }} - {{ $emp->full_name }} ({{ $emp->department->name ?? 'Dept' }} • {{ $emp->branch->name ?? 'Cabang' }} • {{ $emp->effective_currency }})
                            </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="w-12 h-12 rounded-xl bg-blue-600 text-white font-bold text-base flex items-center justify-center shrink-0">
                        {{ substr(auth()->user()->employee->first_name ?? 'E', 0, 1) }}{{ substr(auth()->user()->employee->last_name ?? '', 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">{{ auth()->user()->employee->full_name ?? auth()->user()->name }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ auth()->user()->employee->employee_code ?? '-' }} • {{ auth()->user()->employee->department->name ?? 'Dept' }} • {{ auth()->user()->employee->position->name ?? 'Jabatan' }}
                        </p>
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 mt-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            Cabang: {{ auth()->user()->employee->branch->name ?? 'Pusat' }} (Mata Uang Gaji: {{ auth()->user()->employee->effective_currency ?? 'IDR' }})
                        </span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Section 2: Mata Uang & Nominal Pinjaman -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-5">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">2</span>{{ __('Mata Uang & Pokok Pinjaman') }}</h2>

            <!-- Currency Selection Radio Buttons -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-2">{{ __('Mata Uang Pinjaman') }} <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all"
                           :class="currency === 'IDR' ? 'border-blue-600 bg-blue-50/40 text-blue-900 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                        <input type="radio" name="currency" value="IDR" x-model="currency" class="sr-only">
                        <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-lg shrink-0">🇮🇩</div>
                        <div>
                            <span class="text-xs font-bold block">{{ __('IDR - Indonesian Rupiah') }}</span>
                            <span class="text-[11px] text-slate-500">{{ __('Standar operasional kantor pusat Indonesia (Rp)') }}</span>
                        </div>
                    </label>

                    <label class="relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all"
                           :class="currency === 'SAR' ? 'border-emerald-600 bg-emerald-50/40 text-emerald-900 shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                        <input type="radio" name="currency" value="SAR" x-model="currency" class="sr-only">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-lg shrink-0">🇸🇦</div>
                        <div>
                            <span class="text-xs font-bold block">{{ __('SAR - Saudi Arabian Riyal') }}</span>
                            <span class="text-[11px] text-slate-500">{{ __('Standar kantor cabang Riyadh & proyek Arab Saudi (SAR)') }}</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Amount Input & Tenor -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Amount -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('Nominal Pinjaman Diajukan') }} (<span x-text="currencySymbol"></span>) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center w-full rounded-xl border border-slate-200 bg-white px-3.5 py-1.5 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-600 transition-all cursor-text"
                        @click="$refs.loanAmountInput?.focus()">
                        <span class="text-xs font-mono font-bold text-slate-400 select-none shrink-0" x-text="currencySymbol"></span>
                        <input type="number" name="amount" x-ref="loanAmountInput" x-model.number="amount" min="1" step="any" required class="w-full px-2.5 py-1 text-xs font-mono font-bold text-slate-900 border-0 bg-transparent outline-none focus:ring-0" placeholder="{{ __('Contoh: 5000000') }}">
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 font-mono">
                        {{ __('Terbilang:') }} <span class="font-bold text-slate-700" x-text="currencySymbol + ' ' + formatMoney(amount)"></span>
                    </p>
                    @error('amount')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tenor Months -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('Jangka Waktu Tenor (Bulan)') }} <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-4 gap-2 mb-2">
                        <button type="button" @click="tenor = 3" class="py-1.5 text-xs font-semibold rounded-lg border transition-all text-center" :class="tenor == 3 ? 'bg-blue-600 border-blue-600 text-white shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">{{ __('3 Bln') }}</button>
                        <button type="button" @click="tenor = 6" class="py-1.5 text-xs font-semibold rounded-lg border transition-all text-center" :class="tenor == 6 ? 'bg-blue-600 border-blue-600 text-white shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">{{ __('6 Bln') }}</button>
                        <button type="button" @click="tenor = 12" class="py-1.5 text-xs font-semibold rounded-lg border transition-all text-center" :class="tenor == 12 ? 'bg-blue-600 border-blue-600 text-white shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">{{ __('12 Bln') }}</button>
                        <button type="button" @click="tenor = 24" class="py-1.5 text-xs font-semibold rounded-lg border transition-all text-center" :class="tenor == 24 ? 'bg-blue-600 border-blue-600 text-white shadow-2xs' : 'border-slate-200 hover:bg-slate-50 text-slate-700'">{{ __('24 Bln') }}</button>
                    </div>
                    <div class="flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-600 transition-all bg-white">
                        <input type="number" name="tenor_months" x-model.number="tenor" min="1" max="60" required class="w-full text-xs font-bold px-3.5 py-2.5 text-slate-900 focus:outline-none border-0" placeholder="{{ __('Ketik jumlah bulan (1 - 60)') }}">
                        <span class="inline-flex items-center px-4 bg-slate-100 text-slate-800 font-bold text-xs border-s border-slate-200 shrink-0 select-none">{{ __('Bulan') }}</span>
                    </div>
                    @error('tenor_months')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Start Date & Interest -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('Mulai Cicilan Pertama') }} <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="repayment_start_date" x-model="repaymentDate" required class="w-full text-xs rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all bg-white">
                    <span class="text-[10px] text-slate-400 mt-1 block">{{ __('Jatuh tempo cicilan per bulan akan mengacu pada tanggal ini.') }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                        {{ __('Suku Bunga / Margin Perusahaan (%)') }}
                    </label>
                    <div class="flex rounded-xl shadow-2xs overflow-hidden border border-slate-200 focus-within:ring-2 focus-within:ring-blue-500/20 focus-within:border-blue-600 transition-all bg-white">
                        <input type="number" name="interest_rate_percent" x-model.number="interest" min="0" max="100" step="0.1" class="w-full text-xs font-bold px-3.5 py-2.5 text-slate-900 focus:outline-none border-0" placeholder="{{ __('0 (Bunga 0% / Kasbon Tanpa Riba)') }}">
                        <span class="inline-flex items-center px-4 bg-slate-100 text-slate-800 font-bold text-xs border-s border-slate-200 shrink-0 select-none">
                            %
                        </span>
                    </div>
                    <span class="text-[10px] text-emerald-600 mt-1 block font-medium">{{ __('Isi 0 untuk pinjaman qardh / kasbon tanpa bunga.') }}</span>
                </div>
            </div>

            <!-- Live Simulator Box (Clean Enterprise Card Without Gradients) -->
            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Kalkulator Simulasi Angsuran') }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>{{ __('Otomatis') }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <!-- Angsuran Per Bulan -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">{{ __('Angsuran Per Bulan') }}</span>
                        <span class="text-xl font-bold font-mono text-emerald-600 block mt-1" x-text="currencySymbol + ' ' + formatMoney(monthlyInstallment)"></span>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">{{ __('Estimasi potong gaji / transfer') }}</span>
                    </div>

                    <!-- Total Dana Pinjaman -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">{{ __('Total Dana Pinjaman') }}</span>
                        <span class="text-xl font-bold font-mono text-slate-900 block mt-1" x-text="currencySymbol + ' ' + formatMoney(totalPayment)"></span>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">{{ __('Pokok disetujui + bunga') }}</span>
                    </div>

                    <!-- Periode Angsuran -->
                    <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-2xs">
                        <span class="text-[10px] font-bold text-slate-400 block uppercase tracking-wider">{{ __('Periode Angsuran') }}</span>
                        <div class="flex items-baseline gap-1 mt-1 text-xl font-bold font-mono text-blue-600" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
                            <span x-text="tenor"></span>
                            <span>{{ __('Bulan') }}</span>
                            <span class="text-sm font-medium text-blue-500/80" dir="ltr" x-text="'(' + tenor + 'x)'"></span>
                        </div>
                        <span class="text-[10px] text-slate-500 mt-0.5 block">{{ __('Jadwal jatuh tempo per bulan') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Keperluan Pinjaman & Potong Gaji -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs space-y-4">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <span class="w-5 h-5 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center text-[10px]">3</span>{{ __('Alasan Pengajuan & Pengaturan Pelunasan') }}</h2>

            <!-- Reason -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    {{ __('Tujuan / Keperluan Pinjaman') }} <span class="text-rose-500">*</span>
                </label>
                <textarea name="reason" rows="3" required class="w-full text-xs rounded-xl border border-slate-200 p-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition-all placeholder:text-slate-400" placeholder="{{ __('Jelaskan kebutuhan pengajuan pinjaman (misal: biaya renovasi mendesak, keperluan keluarga, atau biaya pendidikan)...') }}">{{ old('reason') }}</textarea>
                @error('reason')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Payroll Deduction Checkbox -->
            <div class="p-3.5 rounded-xl border border-indigo-100 bg-indigo-50/40 flex items-start gap-3">
                <input type="checkbox" id="payroll_deduction" name="payroll_deduction" value="1" checked class="mt-0.5 w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-slate-300">
                <label for="payroll_deduction" class="text-xs cursor-pointer">
                    <span class="font-bold text-indigo-950 block">{{ __('Potong Langsung dari Slip Gaji Bulanan (Rekomendasi)') }}</span>
                    <span class="text-slate-500 text-[11px] block mt-0.5">
                        {{ __('Nilai angsuran bulanan akan secara otomatis masuk dalam komponen potongan gaji di modul Payroll setiap akhir bulan.') }}
                    </span>
                </label>
            </div>

            <!-- Attachment File -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                    {{ __('Berkas Pendukung / Surat Permohonan (Opsional)') }}
                </label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-200 border-dashed rounded-xl hover:border-blue-400 transition-all bg-slate-50/50">
                    <div class="space-y-1 text-center">
                        <svg class="mx-auto h-9 w-9 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <div class="flex text-xs text-slate-600 justify-center">
                            <label for="attachment" class="relative cursor-pointer rounded-md font-semibold text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                <span>{{ __('Unggah file') }}</span>
                                <input id="attachment" name="attachment" type="file" @change="handleFileChange" accept=".pdf,.png,.jpg,.jpeg" class="sr-only">
                            </label>
                            <p class="pl-1">{{ __('atau tarik dan lepas') }}</p>
                        </div>
                        <p class="text-[10px] text-slate-400">{{ __('PDF, PNG, JPG hingga 5MB') }}</p>
                        <template x-if="fileName">
                            <p class="text-xs font-semibold text-emerald-600 mt-2" x-text="'{{ __('File terpilih:') }} ' + fileName"></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Button Footer -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('loans.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all">
                {{ __('Batal') }}
            </a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Kirim Pengajuan Pinjaman') }}</span>
            </button>
        </div>
    </form>
</div>
@endsection


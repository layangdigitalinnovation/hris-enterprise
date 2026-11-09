@extends('layouts.app')

@section('title', __('Kategori Reimbursement'))

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('reimbursements.index') }}" class="hover:text-blue-600 font-medium transition-colors">{{ __('Reimbursement') }}</a>
                <span>/</span>
                <span class="text-slate-700 font-semibold">{{ __('Master Kategori') }}</span>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                <span class="p-2 bg-gradient-to-tr from-blue-600 to-indigo-600 text-white rounded-xl shadow-md shadow-blue-500/20">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
                {{ __('Kategori Reimbursement & Plafon') }}
            </h1>
            <p class="text-xs md:text-sm text-slate-500 mt-0.5">
                {{ __('Atur jenis pengeluaran dinas yang diizinkan untuk klaim, mata uang yang berlaku (IDR/SAR), batas maksimal nominal (plafon), dan kewajiban nota.') }}
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0 self-start md:self-auto">
            <a href="{{ route('reimbursements.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-xs transition-all whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>{{ __('Daftar Klaim') }}</span>
            </a>

            <a href="{{ route('reimbursements.categories.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition-all whitespace-nowrap shrink-0">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>{{ __('Tambah Kategori Baru') }}</span>
            </a>
        </div>
    </div>

    <!-- Tabel Kategori -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-4 whitespace-nowrap">{{ __('Kode & Nama Kategori') }}</th>
                        <th class="py-3.5 px-4">{{ __('Deskripsi / Kebijakan') }}</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">{{ __('Mata Uang') }}</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">{{ __('Batas Maks / Plafon') }}</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">{{ __('Wajib Nota') }}</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">{{ __('Status') }}</th>
                        <th class="py-3.5 px-4 text-center whitespace-nowrap">{{ __('Jumlah Klaim') }}</th>
                        <th class="py-3.5 px-4 text-right whitespace-nowrap">{{ __('Aksi') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-800">{{ $cat->name }}</div>
                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ $cat->code }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs text-slate-600 text-[11px] leading-relaxed">
                                {{ $cat->description ?? '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($cat->currency === 'SAR')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                        🇸🇦 {{ __('Khusus SAR') }}
                                    </span>
                                @elseif($cat->currency === 'IDR')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-200">
                                        🇮🇩 {{ __('Khusus IDR') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">
                                        🌐 {{ __('Semua (IDR & SAR)') }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-bold whitespace-nowrap">
                                @if($cat->currency === 'SAR')
                                    @if($cat->max_amount_sar || $cat->max_amount)
<div class="text-emerald-700">
                                            SAR {{ number_format($cat->max_amount_sar ?? $cat->max_amount, 2, '.', ',') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-normal italic">{{ __('Tanpa Batas') }}</span>
                                    @endif
                                @elseif($cat->currency === 'IDR')
                                    @if($cat->max_amount)
<div class="text-slate-800">
                                            Rp {{ number_format($cat->max_amount, 0, ',', '.') }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-normal italic">{{ __('Tanpa Batas') }}</span>
                                    @endif
                                @else
                                    @if($cat->max_amount || $cat->max_amount_sar)
<div class="space-y-0.5">
                                            @if($cat->max_amount)
<div class="text-slate-800 text-xs">
                                                    <span class="text-[10px] text-slate-400 font-sans mr-1">{{ __('IDR:') }}</span>Rp {{ number_format($cat->max_amount, 0, ',', '.') }}
                                                </div>
                                            @endif
                                            @if($cat->max_amount_sar)
<div class="text-emerald-700 text-xs">
                                                    <span class="text-[10px] text-slate-400 font-sans mr-1">{{ __('SAR:') }}</span>SAR {{ number_format($cat->max_amount_sar, 2, '.', ',') }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 font-normal italic">{{ __('Tanpa Batas') }}</span>
                                    @endif
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($cat->requires_receipt)
                                    <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                                        ✓ {{ __('Wajib') }}
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400">{{ __('Opsional') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @if($cat->is_active)
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ __('Aktif') }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500 border border-slate-200">{{ __('Nonaktif') }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-mono font-semibold text-slate-700 whitespace-nowrap">
                                {{ $cat->requests_count }} Klaim
                            </td>
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('reimbursements.categories.edit', $cat) }}" class="p-1.5 text-slate-400 hover:text-blue-600 hover:bg-slate-100 rounded-lg transition-colors inline-flex items-center justify-center" title="{{ __('Edit Kategori') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>

                                    @if($cat->requests_count == 0)
                                        <form action="{{ route('reimbursements.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('{{ __('Hapus kategori ini?') }}')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors inline-flex items-center justify-center" title="{{ __('Hapus Kategori') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-10 text-slate-400">{{ __('Belum ada data kategori reimbursement.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

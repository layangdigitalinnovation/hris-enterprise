@extends('layouts.app')

@section('title', __('Portal Investor'))

@section('content')
<div class="max-w-xl mx-auto py-12 px-4 text-center">
    <div class="bg-white rounded-3xl border border-slate-200/80 p-8 shadow-sm space-y-4">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
        </div>
        <h2 class="text-lg font-bold text-slate-800">{{ __('Akun Investor Belum Terhubung') }}</h2>
        <p class="text-xs text-slate-500 leading-relaxed">
            Akun Anda saat ini belum dihubungkan ke data portofolio penanaman modal resmi. Silakan hubungi Administrator atau Bagian Keuangan perusahaan untuk menghubungkan akun Anda ke data penanaman modal.
        </p>
        <div class="pt-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition-all">
                {{ __('Kembali') }} Utama
            </a>
        </div>
    </div>
</div>
@endsection


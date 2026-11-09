@extends('layouts.app', ['headerTitle' => __('Katalog Template Payroll'), 'title' => __('Template Payroll')])

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'templates' }">
    <!-- Back to Payroll Hub -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('payroll.index', ['tab' => 'templates']) }}" class="hover:text-blue-600 font-medium transition">{{ __('Penggajian') }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">{{ __('Katalog Template Payroll') }}</span>
        </div>
        <a href="{{ route('payroll.index', ['tab' => 'templates']) }}" class="text-xs text-slate-600 hover:text-blue-600 flex items-center gap-1.5 transition font-semibold">
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            {{ __('Kembali ke Hub Penggajian') }}
        </a>
    </div>

    @include('payroll.partials.templates_tab')
</div>
@endsection


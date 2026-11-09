@extends('layouts.app', ['headerTitle' => __('Slip Gaji Saya'), 'title' => __('Slip Gaji Saya')])

@section('content')
<div class="space-y-8">

    <!-- Header & Info -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-bold text-slate-900 tracking-tight">{{ __('Riwayat Slip Gaji Saya') }}</h3>
            <p class="text-xs text-slate-500">{{ __('Daftar arsip slip penghasilan resmi yang telah disahkan dan diterbitkan perusahaan') }}</p>
        </div>

        <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white font-bold flex items-center justify-center text-sm shadow-sm shadow-blue-500/20">
                {{ strtoupper(substr($employee->first_name, 0, 1)) }}
            </div>
            <div>
                <span class="font-bold text-slate-900 text-xs block">{{ $employee->full_name }}</span>
                <span class="font-mono text-[10px] text-slate-500">{{ $employee->employee_code }} · {{ $employee->position ? __($employee->position->title) : __('Staff') }}</span>
            </div>
        </div>
    </div>

    <!-- Payslip Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($payslips as $slip)
            @php
                $item = $slip->payrollItem;
                $period = $item->period;
            @endphp
            <div class="p-6 rounded-3xl bg-white border border-slate-200/80 shadow-sm relative overflow-hidden flex flex-col justify-between hover:border-blue-300 hover:shadow-md transition">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-blue-600">{{ __('Slip Gaji') }}</span>
                            <h4 class="font-extrabold text-slate-900 text-base mt-0.5">{{ __($period->name) }}</h4>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ __('Resmi Terbit') }}
                        </span>
                    </div>

                    <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-200/70 font-mono text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-sans">{{ __('No. Slip:') }}</span>
                            <span class="font-bold text-slate-900">{{ $slip->slip_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500 font-sans">{{ __('Tgl Pembayaran:') }}</span>
                            <span class="text-slate-700">{{ $period->payment_date->translatedFormat('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-slate-200">
                            <span class="text-slate-500 font-sans">{{ __('Gaji Bersih (THP):') }}</span>
                            <span class="text-emerald-600 font-extrabold text-sm">Rp {{ number_format($item->net_salary, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[10px] text-slate-400">{{ __('Confidential') }}</span>
                    <a href="{{ route('payroll.payslip', $slip) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs shadow-sm shadow-blue-500/20 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <span>{{ __('Buka Slip Gaji') }}</span>
                        <span class="rtl:rotate-180">→</span>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 rounded-3xl bg-white border border-slate-200/80 space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl font-bold">📄</div>
                <h4 class="font-bold text-slate-800 text-sm">{{ __('Belum Ada Slip Gaji Diterbitkan') }}</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">{{ __('Slip gaji Anda akan otomatis muncul di sini setelah bagian HRD & Finance menyelesaikan kalkulasi dan mengesahkan payroll bulanan.') }}</p>
            </div>
        @endforelse
    </div>

    @if ($payslips->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-xs">
            {{ $payslips->links() }}
        </div>
    @endif

</div>
@endsection
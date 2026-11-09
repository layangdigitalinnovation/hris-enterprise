@extends('layouts.app', ['headerTitle' => __('Detail Mutasi & Promosi Karyawan'), 'title' => $careerTransition->transition_number])

@section('content')
<div class="space-y-6 max-w-5xl mx-auto w-full">

    {{-- 1. Header & Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <a href="{{ route('career-transitions.index') }}"
               class="w-10 h-10 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center shrink-0 transition">
                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight">{{ $careerTransition->transition_number }}</h1>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $careerTransition->type_badge_classes }}">
                        {{ $careerTransition->type_label }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $careerTransition->status_badge_classes }}">
                        {{ $careerTransition->status_label }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    {{ __('Karyawan:') }} <strong class="text-slate-800">{{ $careerTransition->employee?->full_name }}</strong> ({{ $careerTransition->employee?->employee_code }})
                    @if($careerTransition->letter_number)
                        &bull; {{ __('No. SK:') }} <strong class="text-slate-700">{{ $careerTransition->letter_number }}</strong>
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            @if(in_array($careerTransition->status, ['approved', 'applied']))
                <a href="{{ route('career-transitions.pdf', $careerTransition) }}"
                   class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold shadow-2xs transition">
                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>{{ __('Cetak SK (PDF)') }}</span>
                </a>
            @endif
        </div>
    </div>

    {{-- 2. 4-Step Progress Tracker --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            {{-- Step 1: Request --}}
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                    1
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ __('Pengajuan') }}</div>
                    <div class="text-[11px] text-slate-400 font-mono">{{ $careerTransition->request_date ? $careerTransition->request_date->format('d/m/Y') : '-' }}</div>
                </div>
            </div>

            {{-- Step 2: Approval --}}
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ in_array($careerTransition->status, ['approved', 'applied']) ? 'bg-blue-600 text-white shadow-xs' : ($careerTransition->status === 'rejected' ? 'bg-rose-600 text-white' : 'bg-slate-100 text-slate-400') }}">
                    2
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ __('Persetujuan Manajemen') }}</div>
                    <div class="text-[11px] text-slate-400">
                        @if($careerTransition->approved_at)
                            {{ $careerTransition->approved_at->format('d/m/Y') }}
                        @elseif($careerTransition->status === 'rejected')
                            <span class="text-rose-500 font-semibold">{{ __('Ditolak') }}</span>
                        @else
                            {{ __('Menunggu Review') }}
                        @endif
                    </div>
                </div>
            </div>

            {{-- Step 3: Effective Date --}}
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ in_array($careerTransition->status, ['approved', 'applied']) ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400' }}">
                    3
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ __('Tanggal Efektif') }}</div>
                    <div class="text-[11px] font-mono {{ $careerTransition->is_effective ? 'text-emerald-600 font-bold' : 'text-slate-400' }}">
                        {{ $careerTransition->effective_date ? $careerTransition->effective_date->format('d/m/Y') : '-' }}
                    </div>
                </div>
            </div>

            {{-- Step 4: Applied --}}
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 {{ $careerTransition->status === 'applied' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-slate-100 text-slate-400' }}">
                    4
                </div>
                <div>
                    <div class="text-xs font-bold text-slate-800">{{ __('Penerapan Master Data') }}</div>
                    <div class="text-[11px] text-slate-400">
                        @if($careerTransition->applied_at)
                            <span class="text-emerald-600 font-bold">{{ __('Selesai & Aktif') }}</span>
                        @else
                            {{ __('Menunggu Eksekusi') }}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Applied Success Banner --}}
    @if($careerTransition->status === 'applied')
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-2.5">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <strong class="font-bold">{{ __('Status Transisi Karir Telah Aktif') }}</strong>
                    <p class="text-emerald-700 mt-0.5">{{ __('Perubahan penugasan, cabang, departemen, dan jabatan telah resmi diaplikasikan pada data master karyawan per :date oleh :user.', [
                        'date' => $careerTransition->applied_at ? $careerTransition->applied_at->format('d/m/Y H:i') : '-',
                        'user' => $careerTransition->applier?->name ?? 'Admin'
                    ]) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- 3. Before vs After Comparison Matrix --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-sm font-bold text-slate-800">{{ __('Matriks Komparasi Karir (Sebelum vs Sesudah)') }}</h2>
                <p class="text-[11px] text-slate-400">{{ __('Perbandingan kondisi penugasan sebelumnya dengan struktur baru yang ditetapkan') }}</p>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $careerTransition->type_badge_classes }}">
                {{ $careerTransition->type_label }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
            {{-- Left: State Before --}}
            <div class="rounded-xl border border-slate-200/90 bg-slate-50/50 p-4 space-y-3">
                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span>{{ __('Kondisi Semula (Sebelum Transisi)') }}</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Cabang / Penempatan') }}</span>
                        <strong class="text-slate-700 font-semibold">{{ $careerTransition->fromBranch?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Departemen') }}</span>
                        <strong class="text-slate-700 font-semibold">{{ $careerTransition->fromDepartment?->name ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Jabatan Terakhir') }}</span>
                        <strong class="text-slate-700 font-semibold">{{ $careerTransition->fromPosition?->title ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Atasan Langsung') }}</span>
                        <strong class="text-slate-700 font-semibold">{{ $careerTransition->fromManager?->full_name ?? __('Tidak Ada') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Gaji Pokok / Kompensasi') }}</span>
                        @if($careerTransition->from_salary !== null)
                            <strong class="text-slate-700 font-mono font-semibold">
                                {{ format_currency($careerTransition->from_salary, $careerTransition->from_currency ?? 'IDR') }}
                            </strong>
                        @else
                            <span class="text-slate-400 italic">{{ __('Tidak tercatat') }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: State After (Target) --}}
            <div class="rounded-xl border border-blue-200 bg-blue-50/20 p-4 space-y-3">
                <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider flex items-center gap-1.5 pb-2 border-b border-blue-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <span>{{ __('Kondisi Baru (Hasil Transisi)') }}</span>
                </div>

                <div class="space-y-2.5 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Cabang / Penempatan') }}</span>
                        <strong class="text-slate-900 font-bold {{ $careerTransition->from_branch_id !== $careerTransition->to_branch_id ? 'text-blue-700' : '' }}">
                            {{ $careerTransition->toBranch?->name ?? '-' }}
                        </strong>
                        @if($careerTransition->from_branch_id !== $careerTransition->to_branch_id)
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-blue-100 text-blue-800 font-semibold ml-1.5">{{ __('Dipindahkan') }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Departemen') }}</span>
                        <strong class="text-slate-900 font-bold {{ $careerTransition->from_department_id !== $careerTransition->to_department_id ? 'text-blue-700' : '' }}">
                            {{ $careerTransition->toDepartment?->name ?? '-' }}
                        </strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Jabatan Baru') }}</span>
                        <strong class="text-slate-900 font-bold {{ $careerTransition->from_position_id !== $careerTransition->to_position_id ? 'text-emerald-700' : '' }}">
                            {{ $careerTransition->toPosition?->title ?? '-' }}
                        </strong>
                        @if($careerTransition->from_position_id !== $careerTransition->to_position_id && $careerTransition->type === 'promotion')
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] bg-emerald-100 text-emerald-800 font-semibold ml-1.5">{{ __('Dipromosikan') }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Atasan Langsung Baru') }}</span>
                        <strong class="text-slate-900 font-bold">
                            {{ $careerTransition->toManager?->full_name ?? __('Tidak Ada') }}
                        </strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">{{ __('Gaji Pokok Baru') }}</span>
                        @if($careerTransition->to_salary !== null)
                            <div class="flex items-center gap-2 flex-wrap">
                                <strong class="text-slate-900 font-mono font-bold">
                                    {{ format_currency($careerTransition->to_salary, $careerTransition->to_currency ?? 'IDR') }}
                                </strong>
                                @if($careerTransition->salary_diff !== null && $careerTransition->salary_diff != 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $careerTransition->salary_diff > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $careerTransition->salary_diff > 0 ? '+' : '' }}{{ format_currency($careerTransition->salary_diff, $careerTransition->to_currency ?? 'IDR') }}
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-slate-500">{{ __('Tetap / Tidak Ada Perubahan Nominal') }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Alasan & Berkas Dokumen --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
        <h2 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100">{{ __('Alasan Pertimbangan & Dokumen Pendukung') }}</h2>

        <div class="space-y-3 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px] uppercase font-bold">{{ __('Alasan / Dasar Pertimbangan:') }}</span>
                <p class="text-slate-700 leading-relaxed mt-1 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $careerTransition->reason }}</p>
            </div>

            @if($careerTransition->notes)
                <div>
                    <span class="text-slate-400 block text-[11px] uppercase font-bold">{{ __('Uraian Tugas / Catatan Tambahan:') }}</span>
                    <p class="text-slate-700 leading-relaxed mt-1 bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $careerTransition->notes }}</p>
                </div>
            @endif

            @if($careerTransition->attachment_path)
                <div class="pt-2">
                    <span class="text-slate-400 block text-[11px] uppercase font-bold mb-1.5">{{ __('Berkas Lampiran SK:') }}</span>
                    <a href="{{ asset('storage/' . $careerTransition->attachment_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        <span>{{ __('Lihat / Unduh Dokumen Lampiran') }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- 5. Riwayat Approval & Otorisasi --}}
    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-3">
        <h2 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100">{{ __('Riwayat Persetujuan & Otorisasi') }}</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px]">{{ __('Diajukan Oleh:') }}</span>
                <strong class="text-slate-700 font-semibold">{{ $careerTransition->creator?->name ?? '-' }}</strong>
                <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $careerTransition->created_at ? $careerTransition->created_at->format('d/m/Y H:i') : '-' }}</div>
            </div>

            <div>
                <span class="text-slate-400 block text-[11px]">{{ __('Diverifikasi / Disetujui:') }}</span>
                @if($careerTransition->approver)
                    <strong class="text-slate-700 font-semibold">{{ $careerTransition->approver->name }}</strong>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $careerTransition->approved_at ? $careerTransition->approved_at->format('d/m/Y H:i') : '-' }}</div>
                    @if($careerTransition->approval_notes)
                        <div class="text-[11px] text-slate-500 mt-1 italic">"{{ $careerTransition->approval_notes }}"</div>
                    @endif
                @else
                    <span class="text-slate-400 italic">{{ __('Belum disetujui') }}</span>
                @endif
            </div>

            <div>
                <span class="text-slate-400 block text-[11px]">{{ __('Diterapkan Ke Master Data:') }}</span>
                @if($careerTransition->applier)
                    <strong class="text-slate-700 font-semibold">{{ $careerTransition->applier->name }}</strong>
                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">{{ $careerTransition->applied_at ? $careerTransition->applied_at->format('d/m/Y H:i') : '-' }}</div>
                    @if($careerTransition->execution_notes)
                        <div class="text-[11px] text-slate-500 mt-1 italic">"{{ $careerTransition->execution_notes }}"</div>
                    @endif
                @else
                    <span class="text-slate-400 italic">{{ __('Belum diterapkan') }}</span>
                @endif
            </div>
        </div>

        @if($careerTransition->rejection_reason)
            <div class="mt-3 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <strong>{{ __('Alasan Penolakan:') }}</strong> {{ $careerTransition->rejection_reason }}
            </div>
        @endif
    </div>

    {{-- 6. Action Panels (Approval / Execution / Rejection) --}}
    @if(auth()->user()->canManageCareerTransitions() || auth()->user()->isManager())
        <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-2xs space-y-4">
            <h2 class="text-sm font-bold text-slate-800 pb-2 border-b border-slate-100">{{ __('Tindakan & Eksekusi') }}</h2>

            {{-- Case A: Pending Review --}}
            @if($careerTransition->status === 'pending')
                <div class="space-y-4">
                    {{-- Approval Form --}}
                    <form action="{{ route('career-transitions.approve', $careerTransition) }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="space-y-1.5">
                            <label for="approval_notes" class="block text-xs font-bold text-slate-700">
                                {{ __('Catatan Persetujuan (Opsional)') }}
                            </label>
                            <input type="text" name="approval_notes" id="approval_notes"
                                   placeholder="{{ __('Catatan rekomendasi atau instruksi tambahan...') }}"
                                   class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                        </div>

                        @if(auth()->user()->canManageCareerTransitions())
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="apply_immediately" id="apply_immediately" value="1"
                                       class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <label for="apply_immediately" class="text-xs text-slate-700">
                                    {{ __('Terapkan langsung ke data master karyawan sekarang (tanpa menunggu tanggal efektif).') }}
                                </label>
                            </div>
                        @endif

                        <div class="flex items-center gap-2.5 pt-1">
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                                {{ __('Setujui Transisi') }}
                            </button>
                        </div>
                    </form>

                    {{-- Rejection Form --}}
                    <div class="pt-3 border-t border-slate-100">
                        <form action="{{ route('career-transitions.reject', $careerTransition) }}" method="POST" class="space-y-3"
                              onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menolak pengajuan ini?') }}');">
                            @csrf
                            <div class="space-y-1.5">
                                <label for="rejection_reason" class="block text-xs font-bold text-rose-700">
                                    {{ __('Tolak Pengajuan (Wajib menyertakan alasan penolakan)') }}
                                </label>
                                <textarea name="rejection_reason" id="rejection_reason" rows="2" required
                                          placeholder="{{ __('Alasan penolakan mutasi / promosi...') }}"
                                          class="w-full px-3 py-2 rounded-xl bg-white border border-rose-200 text-xs text-slate-700 focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 transition shadow-2xs"></textarea>
                            </div>
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-xs transition">
                                {{ __('Tolak Pengajuan') }}
                            </button>
                        </form>
                    </div>
                </div>

            {{-- Case B: Approved (Ready to Apply to Master Employee) --}}
            @elseif($careerTransition->status === 'approved' && auth()->user()->canManageCareerTransitions())
                <form action="{{ route('career-transitions.apply', $careerTransition) }}" method="POST" class="space-y-3"
                      onsubmit="return confirm('{{ __('Menerapkan mutasi/promosi ini akan langsung memperbarui cabang, departemen, jabatan, atasan, dan gaji karyawan di master data. Lanjutkan?') }}');">
                    @csrf
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-xs space-y-2">
                        <div class="font-bold flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>{{ __('Siap Diterapkan ke Master Data Kepegawaian') }}</span>
                        </div>
                        <p class="text-blue-700">{{ __('Pengajuan ini telah disetujui. Klik tombol di bawah untuk mengaktifkan seluruh perubahan posisi, penempatan, dan kompensasi ke profil karyawan.') }}</p>
                    </div>

                    <div class="space-y-1.5">
                        <label for="execution_notes" class="block text-xs font-bold text-slate-700">
                            {{ __('Catatan Pelaksanaan / Serah Terima (Opsional)') }}
                        </label>
                        <input type="text" name="execution_notes" id="execution_notes"
                               placeholder="{{ __('Catatan verifikasi dokumen atau nomor arsip SK...') }}"
                               class="w-full px-3 py-2 rounded-xl bg-white border border-slate-200 text-xs text-slate-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition shadow-2xs">
                    </div>

                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold shadow-md shadow-blue-500/20 transition">
                        {{ __('Terapkan ke Data Master Pegawai Sekarang') }}
                    </button>
                </form>
            @endif
        </div>
    @endif

</div>
@endsection


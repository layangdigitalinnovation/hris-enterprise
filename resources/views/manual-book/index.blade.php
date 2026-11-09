@extends('layouts.app')

@php
    $isAr = app()->getLocale() === 'ar';
@endphp

@section('title', ($isAr ? 'دليل تشغيل النظام' : __('app.manual_book')) . ' - HRIS Pro Enterprise')

@section('content')
<div class="space-y-6" x-data="manualBookApp()">

    <!-- 1. Top Enterprise Hero Banner (Clean, Bright, Professional Light UI) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200/90 shadow-xs print:hidden space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="space-y-3 max-w-3xl">
                <!-- Breadcrumbs -->
                <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-blue-600 transition">{{ __('app.dashboard') }}</a>
                    <span class="text-slate-300">/</span>
                    <span class="text-slate-500">{{ __('app.help_guide') }}</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-blue-600 font-bold">{{ $isAr ? 'دليل تشغيل النظام' : __('app.manual_book') }}</span>
                </div>

                <div class="flex items-center gap-4">
                    <div class="w-13 h-13 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200/80 shadow-2xs shrink-0">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-extrabold tracking-tight text-slate-900">
                                {{ $isAr ? 'مركز التوثيق ودليل تشغيل نظام الموارد البشرية' : 'Pusat Dokumentasi & Manual Book HRIS Pro' }}
                            </h1>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase tracking-wider">v2.4 Enterprise</span>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                            {{ $isAr 
                                ? 'دليل تشغيلي متكامل يوضح مسارات العمل المترابطة، خطوات تشغيل كل وحدة، مصفوفة الصلاحيات، وإرشادات الاستخدام خطوة بخطوة.'
                                : 'Panduan operasional terpadu memuat alur kerja (workflow pipelines), panduan langkah-demi-langkah per peran, matriks wewenang, dan tips praktis operasional HRIS.' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Header Quick Actions & View Mode Toggle -->
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
                <!-- Mode Toggle (Focus Module vs All Continuous View) -->
                <div class="bg-slate-100 p-1 rounded-2xl flex items-center border border-slate-200">
                    <button type="button" @click="setViewMode('focus')"
                            :class="viewMode === 'focus' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>{{ $isAr ? 'عرض الوحدة (تركيز)' : 'Mode Modul Fokus' }}</span>
                    </button>
                    <button type="button" @click="setViewMode('all')"
                            :class="viewMode === 'all' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <span>{{ $isAr ? 'عرض الكل' : 'Semua Modul' }}</span>
                    </button>
                </div>

                <!-- Dedicated Official PDF Download Button -->
                <a href="{{ route('manual-book.pdf', ['lang' => app()->getLocale()]) }}" target="_blank"
                   class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>{{ $isAr ? 'تحميل دليل PDF الرسمي' : 'Unduh PDF Panduan Resmi' }}</span>
                </a>

                <!-- Quick Browser Print Button -->
                <button type="button" @click="window.print()" class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span class="hidden sm:inline">{{ $isAr ? 'طباعة' : 'Cetak' }}</span>
                </button>
            </div>
        </div>

        <!-- System Architecture Metrics Strip (Clean Light Cards) -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-5 border-t border-slate-100 text-xs">
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="text-slate-500">{{ $isAr ? 'نطاق النظام:' : 'Cakupan Sistem:' }}</span>
                <span class="font-bold text-slate-800">{{ $isAr ? '10 وحدات متكاملة' : '10 Modul Terpadu' }}</span>
            </div>
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500 shrink-0"></span>
                <span class="text-slate-500">{{ $isAr ? 'مستويات الصلاحيات:' : 'Tingkat Hak Akses:' }}</span>
                <span class="font-bold text-slate-800">{{ $isAr ? '6 أدوار (RBAC)' : '6 Peran (RBAC)' }}</span>
            </div>
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 shrink-0"></span>
                <span class="text-slate-500">{{ $isAr ? 'العملة المزدوجة:' : 'Dual Currency:' }}</span>
                <span class="font-bold text-slate-800">IDR (Rp) & SAR (ر.س)</span>
            </div>
            <div class="flex items-center gap-2.5 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                <span class="text-slate-500">{{ $isAr ? 'الاعتماد والتدقيق:' : 'Otorisasi & Audit:' }}</span>
                <span class="font-bold text-slate-800">{{ $isAr ? 'فوري وسجل تدقيق شامل' : 'Real-Time & Log Audit' }}</span>
            </div>
        </div>
    </div>

    <!-- 2. Search & Role Persona Filter Bar -->
    <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-200/80 shadow-xs space-y-4 print:hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Search Input with Auto-Highlight -->
            <div class="relative flex-1">
                <svg class="w-4 h-4 text-slate-400 absolute start-3.5 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="{{ $isAr ? 'ابحث في محتوى الدليل (مثال: إجازة، بصمة، مسير رواتب، سلفة، ترقية، استقالة، أذونات)...' : 'Cari materi panduan (misal: cuti, presensi gps, payroll, lembur, kasbon, sar, resign, mutasi, hak akses)...' }}"
                       class="w-full ps-10 pe-10 py-2.5 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition placeholder:text-slate-400">
                <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute end-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Role Persona Filter Pills -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none text-xs">
                <span class="text-slate-400 font-bold text-[10px] uppercase tracking-wider pe-1 shrink-0">{{ $isAr ? 'تصفية حسب الدور:' : 'Filter Peran:' }}</span>
                <button type="button" @click="setRole('all')"
                        :class="activeRole === 'all' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0">
                    {{ $isAr ? 'جميع الأدوار' : 'Semua Peran' }}
                </button>
                <button type="button" @click="setRole('employee')"
                        :class="activeRole === 'employee' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>👤</span>
                    <span>{{ $isAr ? 'الموظف (ESS)' : 'Karyawan (ESS)' }}</span>
                </button>
                <button type="button" @click="setRole('manager')"
                        :class="activeRole === 'manager' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>👔</span>
                    <span>{{ $isAr ? 'المدير المباشر' : 'Manager / Atasan' }}</span>
                </button>
                <button type="button" @click="setRole('hr')"
                        :class="activeRole === 'hr' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>🏢</span>
                    <span>{{ $isAr ? 'الموارد البشرية' : 'HR Admin' }}</span>
                </button>
                <button type="button" @click="setRole('finance')"
                        :class="activeRole === 'finance' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>💰</span>
                    <span>{{ $isAr ? 'المالية والرواتب' : 'Finance & Payroll' }}</span>
                </button>
                <button type="button" @click="setRole('investor')"
                        :class="activeRole === 'investor' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>📈</span>
                    <span>{{ $isAr ? 'المستثمر' : 'Investor' }}</span>
                </button>
                <button type="button" @click="setRole('admin')"
                        :class="activeRole === 'admin' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0 flex items-center gap-1.5">
                    <span>🛡️</span>
                    <span>{{ $isAr ? 'مدير النظام العام' : 'Super Admin' }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- 3. Main Split Layout (Interactive Sticky Sidebar & Content Area) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Sticky Navigation Index (~3.5 cols on desktop) -->
        <aside class="lg:col-span-4 xl:col-span-3 sticky top-6 space-y-4 print:hidden">
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
                <div class="p-3.5 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span>{{ $isAr ? 'قائمة الوحدات والمواضيع' : 'Daftar Modul & Topik' }}</span>
                    </span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" x-text="filteredModuleList.length + ' {{ $isAr ? 'وحدة' : 'Modul' }}'"></span>
                </div>

                <div class="p-2 space-y-1 max-h-[calc(100vh-220px)] overflow-y-auto scrollbar-thin">
                    <template x-for="item in filteredModuleList" :key="item.id">
                        <button type="button" 
                                @click="selectModule(item.id)"
                                :class="selectedModuleId === item.id ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                class="w-full text-start px-3 py-2.5 rounded-xl text-xs transition flex items-center justify-between gap-2.5 cursor-pointer group">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="text-sm shrink-0" x-text="item.icon"></span>
                                <div class="truncate">
                                    <div class="truncate font-semibold text-xs leading-tight" x-text="item.title"></div>
                                    <div class="text-[10px] truncate opacity-75 mt-0.5" x-text="item.tagline"></div>
                                </div>
                            </div>
                            <svg class="w-3.5 h-3.5 shrink-0 opacity-40 group-hover:opacity-100 transition {{ $isAr ? 'rotate-180' : '' }}" :class="selectedModuleId === item.id ? 'text-white' : 'text-slate-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </template>

                    <!-- Divider for Reference & FAQ -->
                    <div class="pt-2 pb-1 px-3 border-t border-slate-100 my-1 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $isAr ? 'المراجع والمساعدة' : 'Referensi & Bantuan' }}</div>

                    <button type="button" @click="selectModule('module-matrix')"
                            :class="selectedModuleId === 'module-matrix' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="w-full text-start px-3 py-2 rounded-xl text-xs transition flex items-center justify-between gap-2.5 cursor-pointer">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-sm">📋</span>
                            <span class="font-semibold truncate">{{ $isAr ? 'مصفوفة الصلاحيات (Role Matrix)' : 'Matriks Hak Akses (Role Matrix)' }}</span>
                        </div>
                    </button>

                    <button type="button" @click="selectModule('module-faq')"
                            :class="selectedModuleId === 'module-faq' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                            class="w-full text-start px-3 py-2 rounded-xl text-xs transition flex items-center justify-between gap-2.5 cursor-pointer">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-sm">❓</span>
                            <span class="font-semibold truncate">{{ $isAr ? 'الأسئلة الشائعة والأجوبة السريعة' : 'Tanya Jawab & FAQ Cepat' }}</span>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Helpful Support Pill Card -->
            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-200/70 text-xs text-slate-700 space-y-2">
                <div class="flex items-center gap-2 font-bold text-blue-950">
                    <span class="w-6 h-6 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xs">ℹ️</span>
                    <span>{{ $isAr ? 'مركز المساعدة والدعم الداخلي' : 'Pusat Bantuan Internal' }}</span>
                </div>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    {{ $isAr 
                        ? 'النظام مزود بسجل تدقيق لجميع الأنشطة وحماية صلاحيات متعددة المستويات. يرجى التواصل مع فريق الموارد البشرية أو تقنية المعلومات عند الحاجة لأي صلاحيات خاصة.'
                        : 'Sistem dilengkapi audit log aktivitas dan proteksi wewenang berlapis. Hubungi Tim HRIS / IT jika membutuhkan penyesuaian hak akses khusus.' }}
                </p>
            </div>
        </aside>

        <!-- Right Main Content Area (~8.5 cols on desktop) -->
        <main class="lg:col-span-8 xl:col-span-9 space-y-6">

            <!-- Continuous All-Modules Indicator Banner (Visible only in 'all' mode) -->
            <div x-show="viewMode === 'all'" class="p-4 rounded-2xl bg-blue-50/80 border border-blue-200/80 text-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-2xs print:hidden">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg font-bold shrink-0 border border-blue-200">📖</span>
                    <div>
                        <div class="font-bold text-xs text-blue-950">{{ $isAr ? 'وضع عرض جميع الوحدات (تمرير مستمر)' : 'Mode Tampilan Semua Modul (Continuous Scroll)' }}</div>
                        <div class="text-[11px] text-slate-600">{{ $isAr ? 'يتم عرض جميع الوحدات التشغيلية بالتتابع. انقر على أي عنصر في القائمة الجانبية للانتقال إليه مباشرة.' : 'Seluruh modul terpadu ditampilkan berurutan. Klik item pada daftar modul di samping untuk langsung melompat ke bagian yang dituju.' }}</div>
                    </div>
                </div>
                <button type="button" @click="setViewMode('focus')" class="px-3.5 py-1.5 rounded-xl bg-white hover:bg-blue-50 text-blue-700 font-semibold text-xs transition border border-blue-200 shrink-0 self-start sm:self-auto cursor-pointer shadow-2xs">
                    {{ $isAr ? 'الانتقال إلى وضع التركيز' : 'Beralih ke Mode Fokus' }}
                </button>
            </div>

            <!-- MODUL 1: PRESENSI, GPS GEOFENCING, VISIT & MESIN SOLUTION -->
            <section id="module-1" x-show="isModuleVisible('module-1')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <!-- Module Header -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xl shrink-0 border border-blue-200">
                            📍
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '1. الحضور والانصراف، السياج الجغرافي (GPS)، الزيارات الخارجية وأجهزة البصمة' : '1. Presensi, GPS Geofencing, Absen Visit & Mesin Solution' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">{{ $isAr ? 'المحرك الأساسي' : 'Core Engine' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'إدارة حضور وانصراف متعددة الطرق: عبر الهاتف المحمول بنظام تحديد المواقع GPS، والمهام والزيارات الميدانية المتعددة، والربط المباشر مع أجهزة البصمة Solution.' : 'Tata kelola kehadiran multi-metode: Mobile GPS, tugas luar kantor (Visit berkali-kali), dan integrasi mesin biometrik Solution.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('attendance.index') }}" class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? 'فتح شاشة الحضور' : 'Buka Fitur Presensi' }}</span>
                            <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Visual Workflow Pipeline with Connecting Nodes -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>{{ $isAr ? 'مسار إجراءات الحضور (Workflow Pipeline)' : 'Alur Proses Presensi (Workflow Pipeline)' }}</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-medium">{{ $isAr ? '4 مراحل مترابطة' : '4 Tahap Terintegrasi' }}</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 text-xs">
                        <!-- Step 1 -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 relative group hover:border-blue-400 transition">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[11px] shadow-xs">1</span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $isAr ? 'الموظف' : 'Karyawan' }}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'تسجيل الحضور عبر GPS / البصمة' : 'Clock-In GPS / Mesin' }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يقوم الموظف بفتح شاشة الحضور عبر الهاتف مع تفعيل الـ GPS أو مسح البصمة/الوجه على جهاز Solution.' : 'Karyawan membuka menu Presensi via ponsel dengan GPS aktif atau melakukan finger/face di mesin Solution.' }}</p>
                        </div>
                        <!-- Step 2 -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 relative group hover:border-blue-400 transition">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[11px] shadow-xs">2</span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $isAr ? 'محرك النظام' : 'Sistem Engine' }}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'التحقق من النطاق والوردية' : 'Validasi Radius & Shift' }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يتحقق النظام آلياً من نطاق السياج الجغرافي لفرع الموظف (مثلاً 100م) ويطابق الوقت مع جدول وردية العمل النشطة.' : 'Sistem memverifikasi batas radius geofencing cabang (misal 100m) dan mencocokkan jam dengan jadwal shift kerja aktif.' }}</p>
                        </div>
                        <!-- Step 3 -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 relative group hover:border-blue-400 transition">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="w-6 h-6 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-[11px] shadow-xs">3</span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-purple-50 text-purple-700 border border-purple-200">{{ $isAr ? 'معالجة فورية' : 'Real-Time' }}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'احتساب فترات السماح والتأخير' : 'Rekap Toleransi Keterlambatan' }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يتم احتساب دقائق التأخير آلياً حسب سياسة الفرع. تسجل الحالة: حضور في الموعد أو متأخر.' : 'Menit keterlambatan dihitung otomatis sesuai toleransi cabang. Status tercatat: Hadir Tepat Waktu atau Terlambat.' }}</p>
                        </div>
                        <!-- Step 4 -->
                        <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200 relative group hover:border-blue-400 transition">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[11px] shadow-xs">4</span>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ $isAr ? 'جاهز للرواتب' : 'Payroll Ready' }}</span>
                            </div>
                            <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'المزامنة مع مسير الرواتب' : 'Sinkronisasi ke Payroll' }}</h4>
                            <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تُرحل بيانات الحضور وساعات العمل الإضافي آلياً إلى دورة مسير الرواتب الشهرية لاحتساب البدلات والخصومات.' : 'Data kehadiran dan jam lembur otomatis ditarik saat penggajian bulanan untuk kalkulasi tunjangan & potongan.' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Comprehensive Multi-Role Perspective Tabs -->
                <div class="space-y-3" x-data="{ roleTab: 'emp' }">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ $isAr ? 'دليل الخطوات التشغيلية بحسب الدور الوظيفي:' : 'Panduan Langkah-demi-Langkah Berdasarkan Peran:' }}</span>
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs">
                            <button type="button" @click="roleTab = 'emp'" :class="roleTab === 'emp' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1 rounded-lg transition">{{ $isAr ? 'الموظف' : 'Karyawan' }}</button>
                            <button type="button" @click="roleTab = 'mgr'" :class="roleTab === 'mgr' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1 rounded-lg transition">{{ $isAr ? 'المدير المباشر' : 'Atasan / Manager' }}</button>
                            <button type="button" @click="roleTab = 'hr'" :class="roleTab === 'hr' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-500 hover:text-slate-800'" class="px-3 py-1 rounded-lg transition">{{ $isAr ? 'الموارد البشرية والإدارة' : 'HR & Admin' }}</button>
                        </div>
                    </div>

                    <!-- Role Content: Employee -->
                    <div x-show="roleTab === 'emp'" class="space-y-3 text-xs text-slate-600">
                        <div class="p-4 rounded-2xl bg-blue-50/60 border border-blue-100 space-y-2">
                            <div class="font-bold text-blue-950 flex items-center gap-1.5">
                                <span>📱</span>
                                <span>{{ $isAr ? 'طريقة تسجيل الحضور والانصراف اليومي:' : 'Cara Clock-In & Clock-Out Harian:' }}</span>
                            </div>
                            <ol class="list-decimal list-outside space-y-2 ps-5 leading-relaxed">
                                @if($isAr)
                                    <li>افتح قائمة <strong>الحضور والملخص</strong> أو أداة <strong>تسجيل الحضور الذاتي عبر GPS</strong> في لوحة التحكم.</li>
                                    <li>تأكد من تفعيل خدمة تحديد المواقع (GPS) في هاتفك ومنح الإذن للمتصفح (<strong>السماح بالوصول للموقع</strong>).</li>
                                    <li>يقيس النظام المسافة بين موقعك ومقر فرعك المسجل آلياً (ضمن نطاق الـ 100 متر المسموح بها).</li>
                                    <li>اضغط على زر <strong>تسجيل الحضور (Clock-In)</strong> لبدء العمل، واضغط <strong>تسجيل الانصراف (Clock-Out)</strong> عند انتهاء دوامك (نقرة واحدة سريعة دون الحاجة لرفع صورة).</li>
                                @else
                                    <li>Buka menu <strong>Presensi & Rekap</strong> atau widget <strong>Presensi Mandiri GPS</strong> pada Dashboard.</li>
                                    <li>Pastikan fitur GPS ponsel Anda aktif dan berikan izin akses lokasi (<strong>Allow Location Access</strong>).</li>
                                    <li>Sistem secara otomatis mengukur jarak koordinat Anda ke kantor cabang penempatan (misal batas radius 100 meter).</li>
                                    <li>Klik tombol <strong>Clock-In</strong> saat mulai bekerja, dan klik <strong>Clock-Out</strong> saat jam kerja berakhir (1-klik instan tanpa perlu repot upload foto).</li>
                                @endif
                            </ol>
                        </div>

                        <div class="p-4 rounded-2xl bg-indigo-50/60 border border-indigo-100 space-y-2">
                            <div class="font-bold text-indigo-950 flex items-center gap-1.5">
                                <span>🚗</span>
                                <span>{{ $isAr ? 'ضوابط تسجيل الزيارات الخارجية (المهام الميدانية ومقابلة العملاء):' : 'Ketentuan Absen Visit (Kunjungan Klien / Tugas Lapangan):' }}</span>
                            </div>
                            <p class="leading-relaxed">
                                {{ $isAr ? 'إذا تم تكليفك بمهام خارج مقر الشركة (زيارات عملاء، اجتماعات خارجية، جولات تفتيشية):' : 'Jika Anda ditugaskan ke luar kantor (kunjungan dinas, meeting klien, audit site):' }}
                            </p>
                            <ul class="list-disc list-outside space-y-2 ps-5 leading-relaxed">
                                @if($isAr)
                                    <li>استخدم شاشة <strong>تسجيل الزيارات (Absen Visit)</strong> (<a href="{{ route('attendance.visits.index') }}" class="text-indigo-600 font-bold underline">فتح صفحة الزيارات</a>).</li>
                                    <li>يمكنك تسجيل الحضور لعدة زيارات خلال اليوم مع إدخال اسم العميل، الغرض من المهمة، وتحديد الموقع الجغرافي.</li>
                                    <li><strong>إلزامية تسجيل المغادرة:</strong> يجب تسجيل الخروج من الزيارة الحالية قبل بدء زيارة جديدة.</li>
                                @else
                                    <li>Gunakan menu <strong>Absen Visit</strong> (<a href="{{ route('attendance.visits.index') }}" class="text-indigo-600 font-bold underline">Buka Halaman Visit</a>).</li>
                                    <li>Anda dapat melakukan check-in berkali-kali dalam sehari dengan memasukkan nama klien, keperluan dinas, dan geotag lokasi.</li>
                                    <li><strong>Wajib Melakukan Check-Out:</strong> Sebelum dapat memulai visit baru, visit sebelumnya harus di-checkout terlebih dahulu.</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Role Content: Manager -->
                    <div x-show="roleTab === 'mgr'" class="space-y-3 text-xs text-slate-600">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                <span>👔</span>
                                <span>{{ $isAr ? 'متابعة الفريق واعتماد طلبات تصحيح الحضور:' : 'Monitoring Tim & Persetujuan Koreksi Absen:' }}</span>
                            </div>
                            <ul class="list-disc list-outside space-y-2 ps-5 leading-relaxed">
                                @if($isAr)
                                    <li>يمكن للمدير متابعة حضور وانصراف أعضاء فريقه مباشرة عبر لوحة المتابعة التشغيلية وملخص الحضور للفرع.</li>
                                    <li>في حال نسيان أحد الموظفين تسجيل الانصراف بسبب نفاد البطارية أو مهمة مفاجئة، يقدم الموظف طلب <strong>تصحيح الحضور</strong>.</li>
                                    <li>سيصل إشعار فوري في جرس الإشعارات لديك. انقر فوق <strong>مراجعة واعتماد</strong> لتوثيق وقت الحضور المصحح.</li>
                                @else
                                    <li>Manager dapat memantau kehadiran anggota tim langsung melalui dashboard operasional dan rekap kehadiran cabang.</li>
                                    <li>Jika bawahan lupa clock-out karena baterai habis atau dinas mendadak, bawahan akan mengajukan <strong>Koreksi Presensi</strong>.</li>
                                    <li>Notifikasi persetujuan akan langsung muncul di lonceng header notifikasi Anda. Klik <strong>Tinjau & Setujui</strong> untuk mengesahkan jam koreksi.</li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Role Content: HR & Admin -->
                    <div x-show="roleTab === 'hr'" class="space-y-3 text-xs text-slate-600">
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                            <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                <span>⚙️</span>
                                <span>{{ $isAr ? 'إعدادات الفروع، نطاق السياج الجغرافي وأجهزة البصمة Solution:' : 'Konfigurasi Cabang, Radius Geofence & Mesin Solution:' }}</span>
                            </div>
                            <ul class="list-disc list-outside space-y-2 ps-5 leading-relaxed">
                                @if($isAr)
                                    <li><strong>السياج الجغرافي:</strong> يتم ضبطه من شاشة الفروع (الهيكل التنظيمي -> الفروع). حدد إحداثيات خطوط الطول/العرض ونصف قطر السماح بالأمتار.</li>
                                    <li><strong>الربط مع أجهزة Solution:</strong> افتح قائمة <a href="{{ route('devices.index') }}" class="text-blue-600 font-bold underline">أجهزة البصمة</a>، واربط الأجهزة عبر عنوان IP ومنفذ الاتصال المخصص.</li>
                                    <li>انقر فوق <strong>مزامنة الأجهزة (Sync)</strong> في الشريط العلوي في أي وقت لجلب سجلات البصمة الحيوية مباشرة لقاعدة البيانات.</li>
                                @else
                                    <li><strong>Radius Geofence:</strong> Dapat dikonfigurasi pada menu Cabang (`Organization -> Branches`). Atur koordinat Latitude/Longitude dan toleransi radius meter.</li>
                                    <li><strong>Integrasi Mesin Solution:</strong> Buka menu <a href="{{ route('devices.index') }}" class="text-blue-600 font-bold underline">Mesin Solution</a>. Hubungkan perangkat fisik melalui IP Address dan Port koneksi.</li>
                                    <li>Klik <strong>Sync Mesin</strong> pada topbar header kapan saja untuk menarik log absensi biometrik secara langsung ke database.</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Alert Warning & Rules Box -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-950 flex items-start gap-3">
                    <span class="text-lg">💡</span>
                    <div class="space-y-1">
                        <div class="font-bold">{{ $isAr ? 'قواعد العمل والتحقق الأمني للنظام:' : 'Aturan Bisnis & Validasi Sistem:' }}</div>
                        <p class="leading-relaxed text-amber-900">
                            {{ $isAr 
                                ? 'تسجيل الحضور عبر الهاتف محمي بخوارزميات كشف المواقع الوهمية (Mock Location). يرفض النظام أي عملية تسجيل غير مطابقة أو خارج نطاق الحرم الجغرافي المحدد من قِبل إدارة الموارد البشرية.'
                                : 'Presensi mobile dilindungi algoritma validasi geofencing. Sistem akan menolak presensi jika GPS terdeteksi palsu (mock location) atau berada di luar batas toleransi radius kantor yang ditetapkan oleh HR Administrator.' }}
                        </p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <span class="text-slate-400 font-medium">{{ $isAr ? 'الوحدة 1: الحضور والانصراف والبصمة' : 'Modul 1: Presensi & Mesin' }}</span>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 2: الإجازات والأرصدة' : 'Lanjut ke Modul 2: Cuti & Izin Sakit' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 2: CUTI, IZIN SAKIT & SALDO KUOTA -->
            <section id="module-2" x-show="isModuleVisible('module-2')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-xl shrink-0 border border-indigo-200">
                            🌴
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '2. الإجازات، الإجازات المرضية ومزامنة رصيد الإجازات' : '2. Cuti, Izin Sakit & Sinkronisasi Saldo Kuota' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-700 border border-indigo-200">{{ $isAr ? 'الخدمة الذاتية والاعتمادات' : 'ESS & Workflow' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'تقديم الإجازات المرضية، والسنوية، والخاصة المدفوعة، والتحقق التلقائي من الرصيد المتاح، مع مسار الاعتماد المتسلسل.' : 'Pengajuan izin sakit, cuti tahunan, cuti khusus berbayar, validasi jatah hari, dan otorisasi berjenjang atasan.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('leaves.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? '+ تقديم طلب إجازة' : '+ Ajukan Cuti Baru' }}</span>
                        </a>
                    </div>
                </div>

                <!-- Pipeline -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">1</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'التحقق من الرصيد' : 'Cek Kuota Saldo' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يراجع الموظف رصيد إجازاته المتبقي في بوابة الخدمة الذاتية قبل اختيار تواريخ الإجازة المطلوبة.' : 'Karyawan memeriksa sisa jatah cuti di widget ESS sebelum memilih rentang tanggal izin.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">2</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'تقديم الطلب والمستندات' : 'Submit Form & Dokumen' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تحديد نوع الإجازة، كتابة السبب، وإرفاق التقرير الطبي المعتمد في حال طلب الإجازة المرضية.' : 'Pilih kategori cuti, isi alasan, dan unggah surat keterangan dokter jika mengajukan izin sakit.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-indigo-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">3</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'إشعار المدير المباشر' : 'Notifikasi ke Atasan' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يصل إشعار فوري لحساب المدير لمراجعة الطلب والموافقة عليه أو رفضه مع ذكر السبب.' : 'Lonceng notifikasi berdering di akun Manager. Atasan meninjau dan memilih Setujui atau Tolak.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">4</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'تحديث الرصيد وسجل الدوام' : 'Update Kuota & Rekap' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'عند الاعتماد، يُخصم الرصيد تلقائياً وتُسجل الأيام في تقويم الحضور كإجازة رسمية معتمدة.' : 'Saat disetujui, saldo cuti terpotong otomatis dan kalender absensi ditandai sebagai Izin Resmi.' }}</p>
                    </div>
                </div>

                <!-- Business Rules -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2 text-slate-600">
                    <div class="font-bold text-slate-900">{{ $isAr ? 'ضوابط وشروط هامة:' : 'Ketentuan Penting:' }}</div>
                    <ul class="list-disc list-outside space-y-2 ps-5 leading-relaxed">
                        @if($isAr)
                            <li>يرفض النظام آلياً تقديم أي إجازة في حال وجود تداخل (<strong>Overlapping</strong>) مع تواريخ إجازة أخرى مسجلة مسبقاً.</li>
                            <li>عطلات نهاية الأسبوع والأعياد والمناسبات الرسمية لا تُخصم من رصيد الإجازات السنوية.</li>
                            <li>لا يسمح النظام بتقديم إجازة تتجاوز الرصيد الفعلي المتاح للموظف.</li>
                        @else
                            <li>Sistem otomatis menolak pengajuan jika rentang tanggal bertabrakan (<strong>overlapping</strong>) dengan cuti yang sudah ada.</li>
                            <li>Hari libur akhir pekan (Sabtu-Minggu) dan hari libur nasional tidak dihitung sebagai pemotong saldo cuti tahunan.</li>
                            <li>Pengajuan cuti yang melebihi sisa kuota aktif tidak dapat diajukan secara sistem.</li>
                        @endif
                    </ul>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 1: الحضور والبصمة' : 'Modul 1: Presensi' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 3: العمل الإضافي' : 'Lanjut ke Modul 3: Lembur / SPKL' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 3: LEMBUR / SPKL (SURAT PERINTAH KERJA LEMBUR) -->
            <section id="module-3" x-show="isModuleVisible('module-3')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl shrink-0 border border-amber-200">
                            ⏱️
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '3. وحدة العمل الإضافي (التكليف) والمعادلة المزدوجة (إندونيسيا والسعودية)' : '3. Modul Lembur / SPKL & Dual Formula (Depnaker RI & Arab Saudi)' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">{{ $isAr ? 'سياسة العمل الإضافي' : 'Overtime Policy' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700 border border-blue-200">{{ $isAr ? '🇮🇩 إندونيسيا 1/173' : '🇮🇩 Depnaker 1/173' }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">{{ $isAr ? '🇸🇦 المادة 107 نظام العمل السعودي' : '🇸🇦 Pasal 107 Arab Saudi' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'إصدار أوامر التكليف بالعمل الإضافي، وتطبيق المعادلتين الرسميتين المعتمدتين نظامياً (قانون العمل الإندونيسي والمادة 107 من نظام العمل السعودي)، والاحتساب الآلي في مسير الرواتب.' : 'Penerbitan Surat Perintah Kerja Lembur (SPKL), dual formula resmi lintas negara (Depnaker RI & Pasal 107 UU Arab Saudi), serta kalkulasi slip gaji otomatis.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 flex-wrap">
                        <a href="{{ route('overtime.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center gap-1.5 shadow-xs border border-slate-200">
                            <span>{{ $isAr ? 'عرض العمل الإضافي والتعريفات' : 'Buka Menu Lembur & Tarif' }}</span>
                        </a>
                        <a href="{{ route('overtime.create') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? '+ إنشاء أمر تكليف جديد' : '+ Buat SPKL Baru' }}</span>
                        </a>
                    </div>
                </div>

                <!-- 3 Tahap Alur Kerja SPKL -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">{{ $isAr ? 'المرحلة 1: التقديم' : 'Tahap 1: Pengajuan' }}</span>
                        <h4 class="font-bold text-slate-900 mt-2 mb-1">{{ $isAr ? '1. رفع طلب التكليف بالعمل الإضافي' : '1. Pengajuan Surat Lembur (SPKL)' }}</h4>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'يُقدم ذاتياً من الموظف أو يُنشئه المشرف مع تحديد تاريخ وساعات العمل الإضافي والمهام المستهدفة إنجازها.' : 'Diajukan mandiri oleh staf pelaksana atau dibuatkan supervisor dengan mengisi tanggal kerja lembur, jam mulai, jam selesai, serta uraian target output tugas.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">{{ $isAr ? 'المرحلة 2: الاعتماد' : 'Tahap 2: Verifikasi' }}</span>
                        <h4 class="font-bold text-slate-900 mt-2 mb-1">{{ $isAr ? '2. اعتماد ومصادقة المدير المباشر' : '2. Otorisasi Atasan Langsung' }}</h4>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'يصل إشعار فوري للمدير لتقييم أهمية التكليف ومخرجاته. وبالموافقة عليه يتم توثيق الساعات نظامياً.' : 'Notifikasi real-time masuk ke portal Manager. Atasan mengevaluasi urgensi dan output pekerjaan. Setelah disetujui, jam lembur disahkan dan tercatat ke sistem.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">{{ $isAr ? 'المرحلة 3: الاحتساب' : 'Tahap 3: Kalkulasi' }}</span>
                        <h4 class="font-bold text-slate-900 mt-2 mb-1">{{ $isAr ? '3. الاحتساب الآلي في مسير الرواتب حسب الدولة' : '3. Auto-Country Payroll Integration' }}</h4>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'عند معالجة مسير الرواتب، يحدد النظام فرع الموظف تلقائياً: ويطبق لائحة العمل الإندونيسية (بالروبية IDR) أو المادة 107 من نظام العمل السعودي (بالريال SAR).' : 'Saat proses payroll bulanan dibuka, sistem otomatis mengidentifikasi cabang karyawan: menerapkan formula Depnaker RI (IDR) atau Pasal 107 UU Arab Saudi (SAR).' }}</p>
                    </div>
                </div>

                <!-- Dual Formula Breakdown Container -->
                <div class="p-5 rounded-2xl bg-amber-50/30 border border-amber-200/80 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-amber-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl">⚖️</span>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ $isAr ? 'مقارنة المعادلتين الرسميتين لاحتساب أجر العمل الإضافي بين الدولتين' : 'Perbandingan Dual Formula Upah Lembur Resmi Lintas Negara' }}</h3>
                                <p class="text-[11px] text-slate-500">{{ $isAr ? 'المعايير الحسابية المعتمدة وفقاً للأنظمة واللوائح العمالية السارية في المقرات التشغيلية للشركة.' : 'Standar perhitungan matematis sesuai hukum ketenagakerjaan di masing-masing yurisdiksi operasional perusahaan.' }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 w-fit">{{ $isAr ? 'التوافق مع الأنظمة العمالية' : 'Labor Law Compliance' }}</span>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 text-xs">
                        <!-- Formula Indonesia (Depnaker RI) -->
                        <div class="p-4 rounded-xl bg-white border border-blue-200 shadow-2xs space-y-3">
                            <div class="flex items-center justify-between border-b border-blue-100 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">🇮🇩</span>
                                    <h4 class="font-bold text-slate-900 text-xs">{{ $isAr ? 'معادلة العمل الإندونيسية (اللائحة الحكومية PP 35/2021)' : 'Formula Depnaker RI (PP 35/2021)' }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-50 text-blue-700 border border-blue-200">{{ $isAr ? 'العملة: روبية (IDR)' : 'Mata Uang: IDR (Rp)' }}</span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-blue-50/60 border border-blue-100 font-mono text-[11px] text-blue-900 leading-relaxed">
                                <strong>{{ $isAr ? 'أجر الساعة' : 'Upah Sejam' }}</strong> = 1 / 173 × ({{ $isAr ? 'الراتب الأساسي + البدلات الثابتة' : 'Gaji Pokok + Tunjangan Tetap' }})
                            </div>

                            <div class="space-y-1.5 text-slate-600 text-[11px] leading-relaxed">
                                <p class="font-semibold text-slate-800">{{ $isAr ? 'مضاعفات ساعات العمل الإضافي:' : 'Skema Pengali Jam Kerja Lembur:' }}</p>
                                <ul class="list-disc list-outside space-y-2 ps-5 leading-relaxed">
                                    @if($isAr)
                                        <li><strong>أيام العمل العادية</strong>: الساعة الأولى تُحتسب بـ <strong>1.5 ضعف</strong> أجر الساعة، والساعات التالية بـ <strong>ضعفي (2.0x)</strong> أجر الساعة.</li>
                                        <li><strong>أيام العطلات الأسبوعية والرسمية</strong>: الساعات من 1 إلى 7 بـ <strong>2.0 ضعف</strong>، والساعة التالية بـ <strong>3.0 أضعاف</strong>، وما زاد عن ذلك بـ <strong>4.0 أضعاف</strong> أجر الساعة.</li>
                                    @else
                                        <li><strong>Hari Kerja Biasa (Weekday)</strong>: Jam ke-1 dibayar <strong>1.5x</strong> upah per jam, dan jam ke-2 seterusnya dibayar <strong>2.0x</strong> upah per jam.</li>
                                        <li><strong>Hari Libur / Istirahat Mingguan (Weekend & Holiday)</strong>: Jam ke 1–7 (atau 1–8) dibayar <strong>2.0x</strong>, jam berikutnya dibayar <strong>3.0x</strong>, dan jam ke-10 ke atas dibayar <strong>4.0x</strong> upah per jam.</li>
                                    @endif
                                </ul>
                            </div>

                            <div class="pt-2 border-t border-slate-100 text-[10px] text-slate-500">
                                {{ $isAr ? 'تُطبق تلقائياً على موظفي الفروع التشغيلية في إندونيسيا (جاكرتا وغيرها).' : 'Berlaku otomatis untuk seluruh staf cabang operasional di wilayah Indonesia (Jakarta, Surabaya, dll.).' }}
                            </div>
                        </div>

                        <!-- Formula Arab Saudi (Pasal 107) -->
                        <div class="p-4 rounded-xl bg-white border border-emerald-200 shadow-2xs space-y-3">
                            <div class="flex items-center justify-between border-b border-emerald-100 pb-2">
                                <div class="flex items-center gap-2">
                                    <span class="text-base">🇸🇦</span>
                                    <h4 class="font-bold text-slate-900 text-xs">{{ $isAr ? 'المادة 107 من نظام العمل السعودي' : 'Pasal 107 UU Arab Saudi (المادة 107)' }}</h4>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">{{ $isAr ? 'العملة: ريال سعودي (SAR)' : 'Mata Uang: SAR (Riyal)' }}</span>
                            </div>

                            <div class="p-2.5 rounded-lg bg-emerald-50/60 border border-emerald-100 font-mono text-[11px] text-emerald-900 leading-relaxed">
                                <strong>{{ $isAr ? 'أجر الساعة الإضافية' : 'Upah Lembur/Jam' }}</strong> = {{ $isAr ? 'الأجر الفعلي/الساعة + 50% من الأجر الأساسي/الساعة' : 'Upah Riil/Jam + 50% Gaji Pokok/Jam' }}
                            </div>

                            <div class="space-y-1.5 text-slate-600 text-[11px] leading-relaxed">
                                <p class="font-semibold text-slate-800">{{ $isAr ? 'عناصر ومكونات الاحتساب الحسابي:' : 'Komponen Perhitungan Matematis:' }}</p>
                                <ul class="list-disc list-outside space-y-2 ps-5 leading-relaxed">
                                    @if($isAr)
                                        <li><strong>الأجر الفعلي للساعة</strong> = (الراتب الأساسي + سائر البدلات المعتادة) ÷ 30 يوماً ÷ ساعات العمل اليومية (المعتادة 8 ساعات).</li>
                                        <li><strong>علاوة الـ 50% الإضافية</strong> = 50% × الأجر الأساسي للساعة (محسوبة حصراً من الأجر الأساسي).</li>
                                        <li><strong>إجمالي أجر الإضافي</strong> = عدد الساعات × (الأجر الفعلي للساعة + 50% من الأجر الأساسي). <em>(بما يعادل 100% من الأجر الفعلي كاملاً مضافاً إليه نصف الأجر الأساسي).</em></li>
                                    @else
                                        <li><strong>Upah Riil per Jam</strong> = (Gaji Pokok + Tunjangan Tetap) ÷ 30 hari ÷ Jam Kerja Harian (normal 8 jam).</li>
                                        <li><strong>Premi Tambahan 50%</strong> = 50% × Upah Pokok per Jam (dihitung khusus dari gaji pokok).</li>
                                        <li><strong>Total Upah Lembur</strong> = Jam Lembur × (Upah Riil/Jam + Premi 50%). <em>(Setara 100% upah aktual + 50% upah pokok).</em></li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Ramadan Clause -->
                            <div class="p-2.5 rounded-lg bg-amber-50/80 border border-amber-200/80 text-[11px] space-y-1 text-amber-900">
                                <span class="font-bold flex items-center gap-1 text-[10px] uppercase tracking-wide text-amber-800">
                                    <span>🌙</span>
                                    <span>{{ $isAr ? 'تنظيم ساعات العمل في شهر رمضان المبارك (المادة 98)' : 'Ketentuan Khusus Bulan Ramadan (Pasal 98 UU KSA)' }}</span>
                                </span>
                                <p class="text-[10px] text-amber-800 leading-relaxed">
                                    {{ $isAr 
                                        ? 'تُخفض ساعات العمل اليومية الفعلية للمسلمين في شهر رمضان إلى 6 ساعات يومياً (المقسوم 30 × 6 = 180 ساعة). ويوفر النظام خيار تفعيل شهر رمضان تلقائياً لتعديل أجر ساعة الإضافي بما يتناسب مع هذا التخفيض النظامي.'
                                        : 'Jam kerja normal berkurang menjadi 6 jam/hari (pembagi 30 × 6 = 180 jam). Sistem menyediakan konfigurasi otomatis Bulan Ramadan sehingga tarif lembur per jam otomatis disesuaikan secara proporsional.' }}
                                </p>
                            </div>

                            <div class="pt-2 border-t border-slate-100 text-[10px] text-slate-500">
                                {{ $isAr ? 'تُطبق تلقائياً على منسوبي فروع المملكة (الرياض، جدة، وغيرها).' : 'Berlaku otomatis untuk staf cabang Riyadh, Jeddah, Mekkah, dan Madinah.' }}
                            </div>
                        </div>
                    </div>

                    <!-- Auto-Country Resolver Policy Note -->
                    <div class="p-3.5 rounded-xl bg-blue-50/80 border border-blue-200/80 text-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                        <div class="flex items-center gap-2.5">
                            <span class="text-xl shrink-0">🌐</span>
                            <div class="text-[11px] leading-relaxed text-slate-600">
                                @if($isAr)
                                    <strong class="text-blue-700">تحديد السياسة آلياً حسب الدولة:</strong> يتعرف النظام بذكاء على مقر فرع الموظف. ولا تحتاج إدارة الموارد البشرية لإجراء أي تعديلات يدوية لأن النظام يطبق المعادلة والعملة المقابلة بصورة تلقائية.
                                @else
                                    <strong class="text-blue-700">Auto-Country Resolution:</strong> Sistem secara cerdas mendeteksi negara cabang penempatan karyawan. Tim HR tidak perlu menghitung manual lintas negara karena kalkulator payroll akan mengaplikasikan rumus dan mata uang yang tepat secara otomatis.
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('overtime.index') }}" class="px-3.5 py-1.5 rounded-lg bg-white hover:bg-blue-50 text-blue-700 border border-blue-200 font-semibold text-[11px] transition shrink-0 whitespace-nowrap self-start sm:self-auto shadow-2xs">
                            {{ $isAr ? 'محاكاة الاحتساب' : 'Simulasi Lembur →' }}
                        </a>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 2: الإجازات والأرصدة' : 'Modul 2: Cuti' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 4: استرداد المصاريف' : 'Lanjut ke Modul 4: Reimbursement' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 4: KLAIM REIMBURSEMENT -->
            <section id="module-4" x-show="isModuleVisible('module-4')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl shrink-0 border border-emerald-200">
                            🧾
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '4. مطالبات واسترداد المصاريف (التشغيلية والطبية)' : '4. Klaim Reimbursement (Operasional & Medis)' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">{{ $isAr ? 'عملة مزدوجة (SAR وIDR)' : 'Dual Currency (IDR & SAR)' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'إدارة نفقات المهام ومطالبات الاسترداد، والتحقق من الفواتير والسندات، وصرف المبالغ عبر الإدارة المالية، وطباعة سندات الصرف (PDF).' : 'Alur klaim pengeluaran dinas, verifikasi bukti nota/kuitansi, pencairan oleh Finance, dan cetak voucher kas PDF.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('reimbursements.create') }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? '+ تقديم مطالبة استرداد' : '+ Ajukan Reimbursement' }}</span>
                        </a>
                    </div>
                </div>

                <!-- 3-Stage Pipeline -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800">{{ $isAr ? 'المرحلة 1: التقديم' : 'Tahap 1: Pengajuan' }}</span>
                        <h4 class="font-bold text-slate-900 mt-2 mb-1">{{ $isAr ? 'إدخال الفواتير والمبلغ' : 'Input Nota & Nominal' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'اختيار التصنيف (مواصلات، علاج، إعاشة، فندق)، والعملة (SAR / IDR)، والمبلغ، وإرفاق صور الفواتير الأصلية.' : 'Pilih kategori (Transportasi, Medis, Konsumsi Dinas, Penginapan), mata uang (IDR / SAR), nominal, dan upload foto kuitansi asli.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-800">{{ $isAr ? 'المرحلة 2: الاعتماد' : 'Tahap 2: Otorisasi' }}</span>
                        <h4 class="font-bold text-slate-900 mt-2 mb-1">{{ $isAr ? 'تدقيق وموافقة الإدارة' : 'Verifikasi Manager / HR' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يقوم المدير بالتحقق من مطابقة المصروفات للحدود المعتمدة وصحة الفواتير، ثم النقر على موافقة أو رفض مع تدوين الملاحظات.' : 'Atasan memeriksa kesesuaian plafon biaya dan keaslian nota. Klik Setujui (Approved) atau Tolak (Rejected) dengan catatan tertulis.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/90 border border-slate-200">
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800">{{ $isAr ? 'المرحلة 3: الصرف' : 'Tahap 3: Pembayaran' }}</span>
                        <h4 class="font-bold text-slate-900 mt-2 mb-1">{{ $isAr ? 'التحويل المالي وسند الصرف' : 'Pencairan oleh Finance' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تقوم الإدارة المالية بالتحويل البنكي، وتوثيق رقم الحوالة، وطباعة سند صرف الخزينة الرسمي بصيغة PDF.' : 'Finance melakukan transfer dana pembayaran, mencatat nomor referensi bank, dan mencetak Dokumen Voucher Kas PDF resmi.' }}</p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 3: العمل الإضافي' : 'Modul 3: Lembur' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 5: السلف وقروض الموظفين' : 'Lanjut ke Modul 5: Kasbon & Pinjaman' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 5: PINJAMAN KARYAWAN & KASBON -->
            <section id="module-5" x-show="isModuleVisible('module-5')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl shrink-0 border border-amber-200">
                            💳
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '5. السلف المالية وقروض الموظفين' : '5. Pinjaman Karyawan & Fasilitas Kasbon' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700 border border-amber-200">{{ $isAr ? 'استقطاع آلي' : 'Auto Deduction' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'تسهيلات السلف المعتمدة، ومحاكاة الأقساط الشهرية، وصرف المبالغ، والخصم التلقائي المباشر من مسير الراتب الشهري.' : 'Fasilitas kasbon resmi perusahaan, simulasi angsuran bulanan, pencairan dana, dan pemotongan otomatis pada slip gaji.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('loans.create') }}" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? '+ طلب سلفة جديدة' : '+ Ajukan Pinjaman' }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-amber-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">1</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'تحديد مدة الأقساط' : 'Pilih Tenor Angsuran' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يحدد الموظف مبلغ السلفة المطلوبة وعدد أشهر السداد (مثل 3، 6، 12 شهراً)، ويحسب النظام القسط الشهري فوراً.' : 'Karyawan menginput nominal pinjaman dan durasi cicilan (misal 3, 6, 12 bulan). Besar potongan per bulan dihitung otomatis.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">2</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'دراسة الأهلية والحدود' : 'Evaluasi Kelayakan' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تراجع الموارد البشرية نسبة القسط من إجمالي الراتب لضمان عدم تجاوز النسبة المسموح بها نظاماً.' : 'HR memeriksa perbandingan cicilan terhadap gaji pokok agar tidak melebihi batas regulasi perusahaan.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">3</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'صرف السلفة' : 'Pencairan Dana' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تفتح الإدارة المالية شاشة الصرف وتوثق تاريخ الحوالة البنكية، وتتحول حالة الطلب إلى مصروفة (Disbursed).' : 'Finance membuka form pencairan, memasukkan tanggal transfer bank, dan status pinjaman berubah menjadi Disbursed (Dicairkan).' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-purple-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">4</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'الاستقطاع الشهري الآلي' : 'Potong Slip Otomatis' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'في كل دورة مسير رواتب، يُستقطع القسط آلياً من صافي الراتب حتى تمام سداد كامل أصل السلفة (0).' : 'Setiap periode penggajian, nilai cicilan otomatis memotong gaji bersih hingga sisa pokok pinjaman lunas (0).' }}</p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 4: استرداد المصاريف' : 'Modul 4: Reimbursement' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 6: مسير الرواتب' : 'Lanjut ke Modul 6: Payroll' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 6: SIKLUS PAYROLL, BPJS & PPH 21 TER -->
            <section id="module-6" x-show="isModuleVisible('module-6')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl shrink-0 border border-purple-200">
                            💰
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '6. دورة مسير الرواتب (Payroll)، البدلات، والتأمينات، والضرائب' : '6. Siklus Penggajian (Payroll), Tunjangan, BPJS & PPh 21 TER' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200">{{ $isAr ? 'محرك متعدد العملات' : 'Multi-Currency Engine' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'احتساب صافي الراتب آلياً، وتحويل أسعار الصرف (ريال/روبية)، واستقطاعات التأمينات والضرائب، وإقفال الدورة، وإصدار قسائم الرواتب الإلكترونية.' : 'Kalkulasi take-home pay otomatis, kurs SAR ke IDR, iuran BPJS, pajak PPh 21 tarif efektif, penguncian periode, dan e-slip gaji ESS.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('payroll.index') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? 'فتح شاشة مسير الرواتب' : 'Buka Modul Payroll' }}</span>
                        </a>
                    </div>
                </div>

                <!-- 5 Steps in Clean 2-Row/Grid Format -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3.5 text-xs">
                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1">
                        <span class="font-bold text-purple-700 text-[10px] uppercase tracking-wider block">{{ $isAr ? 'الخطوة 1' : 'Langkah 1' }}</span>
                        <h4 class="font-bold text-slate-900">{{ $isAr ? 'فتح دورة رواتب جديدة' : 'Buka Periode Payroll Baru' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تقوم المالية بفتح شهر الاستحقاق الجديد وتحديد تاريخ إقفال الحضور والانصراف (Cut-off).' : 'Finance membuka periode penggajian baru (misal: November 2026) dan menetapkan tanggal cut-off absensi.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1">
                        <span class="font-bold text-purple-700 text-[10px] uppercase tracking-wider block">{{ $isAr ? 'الخطوة 2' : 'Langkah 2' }}</span>
                        <h4 class="font-bold text-slate-900">{{ $isAr ? 'تحديث سعر صرف الريال السعودي' : 'Update Kurs SAR ke IDR' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'تحديد سعر الصرف المعتمد لمزامنة رواتب وحسابات الفروع بين المملكة وإندونيسيا.' : 'Atur nilai tukar mata uang Riyal Saudi (SAR) ke Rupiah (IDR) untuk sinkronisasi penggajian cabang Arab Saudi.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1">
                        <span class="font-bold text-purple-700 text-[10px] uppercase tracking-wider block">{{ $isAr ? 'الخطوة 3' : 'Langkah 3' }}</span>
                        <h4 class="font-bold text-slate-900">{{ $isAr ? 'الاحتساب الآلي بنقرة واحدة' : 'Kalkulasi Otomatis 1-Klik' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يقوم النظام باحتساب الراتب الأساسي، والبدلات، وساعات العمل الإضافي، وخصومات التأخير، والسلف، والتأمينات بدقة.' : 'Sistem menghitung Gaji Pokok, Tunjangan, Upah Lembur SPKL (Formula Depnaker RI / Pasal 107 Arab Saudi), Potongan Telat/Alpha, Kasbon, BPJS & PPh 21 TER.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1">
                        <span class="font-bold text-purple-700 text-[10px] uppercase tracking-wider block">{{ $isAr ? 'الخطوة 4' : 'Langkah 4' }}</span>
                        <h4 class="font-bold text-slate-900">{{ $isAr ? 'مراجعة وتصدير الكشوفات' : 'Review & Ekspor Rekapitulasi' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'مراجعة المسودة وتدقيق تفاصيل الموظفين وطباعة تقارير وملخصات الرواتب بصيغتي Excel وPDF.' : 'Pemeriksaan draft gaji setiap karyawan, penyesuaian komponen khusus (bila ada), dan cetak rekap Excel/PDF.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-purple-50/40 border border-purple-100 space-y-1 sm:col-span-2 xl:col-span-2">
                        <span class="font-bold text-purple-700 text-[10px] uppercase tracking-wider block">{{ $isAr ? 'الخطوة 5 (النهائية)' : 'Langkah 5 (Final)' }}</span>
                        <h4 class="font-bold text-slate-900">{{ $isAr ? 'المصادقة والإقفال النهائي للدورة (Lock Period)' : 'Sahkan & Kunci Payroll (Lock Period)' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'النقر على زر إقفال الدورة لمنع أي تعديل يدوي، وإتاحة قسائم الرواتب الإلكترونية فوراً في البوابة الذاتية للموظفين.' : 'Klik tombol Kunci Periode. Status terkunci dari perubahan manual, dan e-slip gaji resmi langsung tampil di portal mandiri (ESS) seluruh karyawan.' }}</p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 5: السلف وقروض الموظفين' : 'Modul 5: Kasbon' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 7: النقل والترقية' : 'Lanjut ke Modul 7: Mutasi & Promosi' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 7: MUTASI, PROMOSI & TRANSISI KARIR -->
            <section id="module-7" x-show="isModuleVisible('module-7')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center font-bold text-xl shrink-0 border border-sky-200">
                            🚀
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '7. النقل، الترقية، والتنقلات الوظيفية بين الفروع' : '7. Mutasi, Promosi Jabatan & Transfer Antar Cabang' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-100 text-sky-700 border border-sky-200">{{ $isAr ? 'المسار الوظيفي' : 'Career Transitions' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'إدارة الترقيات، والنقل بين الأقسام والفروع الدولية (بين السعودية وإندونيسيا)، وإصدار القرارات الإدارية الرسمية ثنائية اللغة (PDF).' : 'Pengajuan promosi karir, mutasi divisi, transfer cabang internasional (Indonesia & Saudi Arabia), serta cetak SK resmi PDF.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('career-transitions.create') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? '+ اقتراح نقل / ترقية' : '+ Usulkan Mutasi/Promosi' }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="font-bold text-sky-700 block">{{ $isAr ? '1. لقطة البيانات الحالية' : '1. Snapshot Data Lama' }}</span>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'اختيار الموظف، ليجلب النظام مسمى وظيفته، وفرعه، وراتبه الحالي لتسهيل المقارنة.' : 'Pilih nama karyawan. Sistem otomatis menampilkan jabatan lama, cabang lama, dan gaji pokok lama untuk mempermudah komparasi.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="font-bold text-sky-700 block">{{ $isAr ? '2. الاعتماد والتطبيق المباشر' : '2. Approval & Terapkan (Apply)' }}</span>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'بعد موافقة الإدارة، يتم الضغط على تطبيق القرار (Apply) ليتحدث ملف الموظف وراتبه ومنصبه الجديد في قاعدة البيانات فوراً.' : 'Setelah disetujui atasan dan HR, klik Terapkan (Apply). Profil master data karyawan, posisi, dan gajinya langsung terupdate otomatis.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="font-bold text-sky-700 block">{{ $isAr ? '3. إصدار القرار الإداري ثنائي اللغة (PDF)' : '3. Terbit SK PDF Bilingual' }}</span>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'إمكانية طباعة القرار الإداري المعتمد برقم رسمي باللغتين العربية والإندونيسية.' : 'Surat Keputusan (SK) resmi dapat dicetak langsung dalam format PDF dengan nomor surat resmi dalam Bahasa Indonesia & Arab.' }}</p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 6: مسير الرواتب' : 'Modul 6: Payroll' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 8: الاستقالة وإخلاء الطرف' : 'Lanjut ke Modul 8: Resign' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 8: RESIGN & OFFBOARDING KARYAWAN -->
            <section id="module-8" x-show="isModuleVisible('module-8')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-xl shrink-0 border border-rose-200">
                            🚪
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '8. الاستقالة، تسوية العهد وإخلاء الطرف، وشهادة الخبرة' : '8. Pengunduran Diri (Resign), Asset Clearance & Paklaring' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 border border-rose-200">{{ $isAr ? 'إجراءات نهاية الخدمة' : 'Offboarding Workflow' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'تقديم الإشعار النظامي المسبق، ومقابلة نهاية الخدمة، ومتابعة تسليم العهد وتصفية الحسابات، وإصدار شهادة الخبرة الرسمية.' : 'Pengajuan one-month notice, wawancara akhir, checklist serah terima inventaris kantor, dan penerbitan surat paklaring resmi.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('resignations.create') }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? '+ تقديم طلب استقالة' : '+ Ajukan Pengunduran Diri' }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-rose-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">1</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'التقديم الذاتي' : 'Pengajuan Mandiri' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'يدخل الموظف تاريخ آخر يوم عمل رسمي وسبب الاستقالة عبر بوابة الخدمة الذاتية.' : 'Karyawan menginput tanggal hari kerja terakhir (last working day) serta alasan pengunduran diri via portal ESS.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">2</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'مقابلة إنهاء الخدمة والاعتماد' : 'Exit Interview & Otorisasi' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'إجراء المقابلة وتوثيق الملاحظات واعتماد طلب الاستقالة من قِبل المدير والموارد البشرية.' : 'Atasan dan HR mengadakan sesi wawancara akhir dan mengesahkan permohonan resign.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-amber-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">3</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'إخلاء الطرف والعهد' : 'Asset Clearance' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'قائمة حصر استلام الأجهزة والمعدات وبطاقات العمل وتصفية المتبقي من السلف المستحقة.' : 'Checklist pengembalian aset (laptop, ID card, serah terima berkas, serta pelunasan sisa pinjaman).' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white font-bold flex items-center justify-center text-[11px] mb-2 shadow-xs">4</span>
                        <h4 class="font-bold text-slate-900 mb-1">{{ $isAr ? 'إصدار شهادة الخدمة والخبرة' : 'Penerbitan Paklaring' }}</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $isAr ? 'إيقاف الحساب تلقائياً، وإصدار شهادة الخبرة وإخلاء الطرف الرسمية بصيغة PDF معتمدة.' : 'Status akun dinonaktifkan dan Surat Pengalaman Kerja (Paklaring) format PDF resmi diterbitkan.' }}</p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 7: النقل والترقية' : 'Modul 7: Mutasi' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 9: المستثمرون والأرباح' : 'Lanjut ke Modul 9: Investor' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 9: INVESTOR, DIVIDEN & PROGRESS BEP -->
            <section id="module-9" x-show="isModuleVisible('module-9')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-xl shrink-0 border border-cyan-200">
                            📈
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '9. وحدة المستثمرين، توزيع الأرباح، ونقطة التعادل (BEP)' : '9. Modul Khusus Investor, Dividen & Balik Modal (BEP)' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-700 border border-cyan-200">{{ $isAr ? 'بوابة المستثمرين والإدارة' : 'Executive & Investor Hub' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'توثيق رأس المال المستثمر (بالريال والروبية)، وتوزيع الأرباح الدورية، ومتابعة الوصول لنقطة التعادل واسترداد رأس المال.' : 'Pencatatan modal investasi (IDR/SAR), distribusi dividen berkala, estimasi balik modal (Break-Even Point / BEP), dan portal investor mandiri.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('investors.index') }}" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? 'فتح بوابة المستثمرين' : 'Buka Master Investor' }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="font-bold text-cyan-700 block">{{ $isAr ? '1. تسجيل رأس المال والشروط' : '1. Pendaftaran Modal & Klausul' }}</span>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'تسجيل رأس المال المدفوع والعملة وتاريخ الإيداع ونسب توزيع الأرباح وأي شروط تعاقدية خاصة.' : 'Admin mencatat nominal modal yang disetor (IDR atau SAR), tanggal setor, persentase bagi hasil, dan klausul gaji khusus bila pemodal aktif bekerja.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="font-bold text-cyan-700 block">{{ $isAr ? '2. توزيع حصص الأرباح' : '2. Distribusi Bagi Hasil (Dividen)' }}</span>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'توثيق مبالغ الأرباح الموزعة ويقوم النظام تلقائياً بتحديث نسبة استرداد رأس المال نحو نقطة التعادل.' : 'Setiap pembagian dividen periodik diinput oleh Finance. Sistem otomatis mengakumulasi nilai bagi hasil untuk mengukur progress menuju titik impas BEP.' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5">
                        <span class="font-bold text-cyan-700 block">{{ $isAr ? '3. البوابة الذاتية وتقارير المحفظة' : '3. Portal Mandiri & Portofolio PDF' }}</span>
                        <p class="text-slate-600 leading-relaxed">{{ $isAr ? 'إمكانية تسجيل دخول المستثمر بشكل مستقل لمتابعة نمو أمواله دون الاطلاع على أسرار ورواتب الموظفين.' : 'Investor dapat login sendiri ke Portal Investor untuk memantau grafik modal dan progress BEP tanpa bisa melihat data dapur internal karyawan.' }}</p>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 8: الاستقالة وإخلاء الطرف' : 'Modul 8: Resign' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الوحدة 10: المستخدمون والصلاحيات' : 'Lanjut ke Modul 10: User & RBAC' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- MODUL 10: MANAJEMEN USER, ROLE, PERMISSION & AUDIT LOG -->
            <section id="module-10" x-show="isModuleVisible('module-10')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden space-y-6 p-6 sm:p-7 scroll-mt-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5">
                    <div class="flex items-center gap-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-slate-100 text-slate-800 flex items-center justify-center font-bold text-xl shrink-0 border border-slate-300">
                            🛡️
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h2 class="text-base sm:text-lg font-bold text-slate-900">{{ $isAr ? '10. إدارة المستخدمين، الأدوار، 263+ صلاحية وسجل التدقيق' : '10. Manajemen User, Role, 263+ Permission & Audit Log' }}</h2>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $isAr ? 'الأمان والتحكم بالصلاحيات' : 'Security & RBAC' }}</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'الفصل بين حساب المستخدم وملف الموظف، والربط الذكي، وتحديد نطاق الفروع والأقسام، وأكثر من 263 صلاحية دقيقة، وسجل التدقيق الأمني.' : 'Pemisahan akun user vs biodata karyawan, fitur tautan data, pembatasan cabang/departemen, 263+ wewenang otoritas, dan rekam jejak audit.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-xs">
                            <span>{{ $isAr ? 'إدارة المستخدمين' : 'Kelola Pengguna' }}</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="font-bold text-slate-900 flex items-center gap-2">
                            <span>👥</span>
                            <span>{{ $isAr ? 'مفهوم "الربط مع ملف الموظف":' : 'Konsep "Tautkan dengan Data Karyawan":' }}</span>
                        </div>
                        <p class="text-slate-600 leading-relaxed">
                            {{ $isAr ? 'يفرق النظام بين حساب تسجيل الدخول (User) والملف المهني للموظف (Employee). وربطهما يتيح:' : 'Sistem membedakan antara entitas akun login (User) dengan profil kepegawaian (Employee). Menghubungkan user ke profil karyawan mengaktifkan:' }}
                        </p>
                        <ul class="list-disc list-outside space-y-2 text-slate-600 ps-5 leading-relaxed">
                            @if($isAr)
                                <li>الوصول لبوابة الخدمة الذاتية للموظف (<strong>Employee Self-Service / ESS</strong>).</li>
                                <li>تسجيل الحضور عبر الهاتف باسم الموظف مباشرة دون الحاجة لتدخل خارجي.</li>
                                <li>استلام قسائم الرواتب الشهرية في حسابه الشخصي.</li>
                                <li>توجيه طلبات المرؤوسين تلقائياً إلى حساب المدير المباشر للاعتماد.</li>
                            @else
                                <li>Hak akses ke portal layanan mandiri (<strong>Employee Self-Service / ESS</strong>).</li>
                                <li>Pencatatan absensi GPS mobile langsung atas nama karyawan bersangkutan.</li>
                                <li>Penerimaan e-slip gaji bulanan ke akun masing-masing.</li>
                                <li>Pengarahan notifikasi approval bawahan langsung ke akun atasan terkait.</li>
                            @endif
                        </ul>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-2">
                        <div class="font-bold text-slate-900 flex items-center gap-2">
                            <span>🔑</span>
                            <span>{{ $isAr ? 'الصلاحيات المتدرجة وسجل التدقيق الأمني:' : 'Wewenang Berjenjang & Audit Trail:' }}</span>
                        </div>
                        <p class="text-slate-600 leading-relaxed">
                            {{ $isAr ? 'إدارة أمان النظام مركزياً وفق المعايير التالية:' : 'Keamanan sistem dikelola secara terpusat:' }}
                        </p>
                        <ul class="list-disc list-outside space-y-2 text-slate-600 ps-5 leading-relaxed">
                            @if($isAr)
                                <li><strong>نطاق الفروع والأقسام:</strong> حصر صلاحية الموظف في نطاق الفرع التابع له فقط لمنع التداخل بين فروع المملكة وإندونيسيا.</li>
                                <li><strong>أكثر من 263 صلاحية:</strong> تحكم تفصيلي كامل (عرض، إضافة، تعديل، حذف، واعتماد).</li>
                                <li><strong>سجل التدقيق الشامل:</strong> توثيق كل حركة إضافة أو تعديل أو حذف مع وقت التنفيذ وعنوان IP المستخدم.</li>
                            @else
                                <li><strong>Cakupan Wilayah (Branch & Dept Scope):</strong> Batasi akses staf agar hanya dapat melihat data cabangnya sendiri.</li>
                                <li><strong>263+ Permissions:</strong> Pengaturan wewenang granuler (lihat, buat, edit, hapus, setujui).</li>
                                <li><strong>Audit Trail Log:</strong> Setiap aktivitas penambahan, pengubahan, atau penghapusan data krusial dicatat lengkap beserta waktu dan IP pengguna.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Navigation Footer (Focus Mode Only) -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 9: المستثمرون' : 'Modul 9: Investor' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى مصفوفة الصلاحيات' : 'Lanjut ke Matriks Hak Akses' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- 11. MATRIKS HAK AKSES KOMPARATIF -->
            <section id="module-matrix" x-show="isModuleVisible('module-matrix')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs overflow-hidden scroll-mt-8">
                <!-- Header Card (Clean, Bright, Professional Light UI) -->
                <div class="px-6 sm:px-8 py-6 sm:py-7 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-2xl border border-blue-200/80 shadow-2xs shrink-0">
                            📋
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap">
                                <h3 class="text-base sm:text-lg lg:text-xl font-bold text-slate-900 tracking-tight">
                                    {{ $isAr ? 'مصفوفة مقارنة الصلاحيات لجميع الأدوار (Role Matrix)' : 'Matriks Perbandingan Hak Akses Seluruh Peran (Role Matrix)' }}
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase tracking-wider">RBAC Verified</span>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                                {{ $isAr ? 'جدول ملخص للصلاحيات والمسؤوليات التشغيلية لكل دور وظيفي في نظام HRIS Pro Enterprise.' : 'Tabel ringkasan wewenang dan batasan operasional untuk setiap peran pengguna di HRIS Pro Enterprise.' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 self-start sm:self-auto">
                        <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>{{ $isAr ? '6 أدوار متكاملة' : '6 Peran Terintegrasi' }}</span>
                        </span>
                    </div>
                </div>

                <!-- Sub-Header Legend Bar -->
                <div class="px-6 sm:px-8 py-3.5 bg-slate-50/90 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-600">
                    <div class="flex items-center gap-2 font-semibold text-slate-700">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $isAr ? 'دليل الرموز والصلاحيات:' : 'Petunjuk Hak Akses:' }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-[11px]">
                        <span class="inline-flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $isAr ? 'صلاحية كاملة' : 'Otoritas Penuh' }}</span>
                        <span class="inline-flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-blue-500"></span> {{ $isAr ? 'إشراف على الفريق' : 'Pengawasan Tim' }}</span>
                        <span class="inline-flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $isAr ? 'تنفيذ وصرف مالي' : 'Finance / Eksekusi' }}</span>
                        <span class="inline-flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-slate-400"></span> {{ $isAr ? 'خدمة ذاتية للموظف' : 'Layanan Mandiri (ESS)' }}</span>
                        <span class="inline-flex items-center gap-1.5 font-medium"><span class="w-2 h-2 rounded-full bg-rose-500"></span> {{ $isAr ? 'محجوب وسري' : 'Dibatasi / Rahasia' }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto text-xs">
                    <table class="w-full text-start">
                        <thead>
                            <tr class="bg-slate-100/90 border-b border-slate-200 text-slate-700 font-extrabold uppercase tracking-wider text-[11px]">
                                <th class="py-4 px-4 sm:px-6 text-start">{{ $isAr ? 'الوحدة / خاصية النظام' : 'Modul / Fitur Sistem' }}</th>
                                <th class="py-4 px-3 text-center">{{ $isAr ? 'المدير العام' : 'Super Admin' }}</th>
                                <th class="py-4 px-3 text-center">{{ $isAr ? 'الموارد البشرية' : 'HR Admin' }}</th>
                                <th class="py-4 px-3 text-center">{{ $isAr ? 'المدير المباشر' : 'Manager' }}</th>
                                <th class="py-4 px-3 text-center">{{ $isAr ? 'المالية' : 'Finance' }}</th>
                                <th class="py-4 px-3 text-center">{{ $isAr ? 'الموظف الذاتي' : 'Employee' }}</th>
                                <th class="py-4 px-3 text-center">{{ $isAr ? 'المستثمر' : 'Investor' }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'لوحة التحكم الرئيسية ومؤشرات الأداء' : 'Dashboard Utama & KPI HR' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ وصول كامل' : '✓ Full Akses' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ وصول كامل' : '✓ Full Akses' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ فريقه فقط' : '✓ Tim Sendiri' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">{{ $isAr ? '✓ مؤشرات المالية' : '✓ Finance KPI' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'خدمة ذاتية' : 'ESS Pribadi' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'بوابة التعادل' : 'Portal BEP' }}</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'حضور GPS وأجهزة البصمة Solution' : 'Absensi GPS & Mesin Solution' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ إدارة شاملة' : '✓ Kelola Semua' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ إدارة شاملة' : '✓ Kelola Semua' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ متابعة فريقه' : '✓ Pantau Tim' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ حضور/انصراف' : '✓ Clock In/Out' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'طلب الإجازات وسلسلة الاعتمادات' : 'Pengajuan Cuti & Approval' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ اعتماد نهائي' : '✓ Otorisasi Penuh' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ اعتماد نهائي' : '✓ Otorisasi Penuh' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ اعتماد فريقه' : '✓ Approve Tim' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'تقديم ذاتي' : 'Ajukan Mandiri' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'مطالبات المصاريف (الاعتماد والصرف)' : 'Reimbursement (Otorisasi & Bayar)' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ صلاحية كاملة' : '✓ Full Otoritas' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ تدقيق الموارد' : '✓ Approve HR' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ اعتماد فريقه' : '✓ Approve Tim' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ صرف وتحويل' : '✓ Bayar / Cairkan' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'تقديم ذاتي' : 'Ajukan Mandiri' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'سلف الموظفين والأقساط' : 'Pinjaman Karyawan & Kasbon' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ صلاحية كاملة' : '✓ Full Otoritas' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ تدقيق الموارد' : '✓ Approve HR' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ اعتماد فريقه' : '✓ Approve Tim' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ صرف وتحويل' : '✓ Bayar / Cairkan' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'تقديم ذاتي' : 'Ajukan Mandiri' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'مسير الرواتب والتأمينات والضرائب' : 'Payroll, Gaji, BPJS & PPh 21' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ وصول كامل' : '✓ Full Akses' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ احتساب وإقفال' : '✓ Hitung & Kunci' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200/80">{{ $isAr ? '✕ محجوب وسري' : '✕ Rahasia' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ احتساب وصرف' : '✓ Hitung & Bayar' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'قسيمة راتبه فقط' : 'Lihat Slip Sendiri' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200/80">{{ $isAr ? '✕ محجوب وسري' : '✕ Rahasia' }}</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'الترقيات والنقل والتدرج الوظيفي' : 'Mutasi, Promosi & Transisi Karir' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ صلاحية كاملة' : '✓ Full Otoritas' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ إصدار وتطبيق' : '✓ Buat & Apply' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ ترشيح فريقه' : '✓ Usulkan Tim' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'قراره فقط' : 'Lihat SK Sendiri' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'الاستقالة وتسوية العهد وإخلاء الطرف' : 'Resign & Asset Clearance' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ صلاحية كاملة' : '✓ Full Otoritas' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ إخلاء طرف' : '✓ Clearance' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ اعتماد الاستقالة' : '✓ Approve Resign' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200/80">{{ $isAr ? '✓ مراجعة السلف' : '✓ Cek Piutang' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-slate-100 text-slate-700 border border-slate-200/80">{{ $isAr ? 'تقديم استقالة' : 'Ajukan Resign' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'رأس مال المستثمر وتوزيع الأرباح (BEP)' : 'Modal Investor & Dividen (BEP)' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ إدارة كاملة' : '✓ Full Kelola' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80">{{ $isAr ? '✓ توثيق الأرباح' : '✓ Catat Dividen' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ بوابة المستثمر' : '✓ Portal Mandiri' }}</span></td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="py-3.5 px-4 sm:px-6 font-semibold text-slate-800">{{ $isAr ? 'المستخدمون والأدوار وصلاحيات النظام' : 'User, Role & Wewenang Sistem' }}</td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80">{{ $isAr ? '✓ ضبط كامل' : '✓ Full Konfigurasi' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/80">{{ $isAr ? '✓ إدارة المستخدمين' : '✓ Kelola User' }}</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                                <td class="py-3.5 px-3 text-center"><span class="text-slate-300 font-semibold">—</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Navigation Footer -->
                <div x-show="viewMode === 'focus'" class="p-5 bg-slate-50/90 border-t border-slate-200/80 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'الوحدة 10: المستخدمون والصلاحيات' : 'Modul 10: User & Hak Akses' }}</span>
                    </button>
                    <button type="button" @click="nextModule()" class="px-4 py-2 rounded-xl bg-[#2563EB] hover:bg-blue-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <span>{{ $isAr ? 'الانتقال إلى الأسئلة الشائعة (FAQ)' : 'Lanjut ke Tanya Jawab (FAQ)' }}</span>
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
            </section>

            <!-- 12. FAQ & PEMECAHAN MASALAH CEPAT -->
            <section id="module-faq" x-show="isModuleVisible('module-faq')" class="bg-white rounded-3xl border border-slate-200/90 shadow-xs p-6 sm:p-7 space-y-5 scroll-mt-8">
                <div class="flex items-center gap-3.5 border-b border-slate-100 pb-4">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl shrink-0 border border-amber-200/80">
                        ❓
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">{{ $isAr ? 'الأسئلة الشائعة وحلول المشكلات السريعة (FAQ)' : 'Tanya Jawab & Pemecahan Masalah Cepat (FAQ)' }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $isAr ? 'دليل الحلول السريعة لأبرز الاستفسارات والمشكلات التشغيلية المتكررة لدى المستخدمين.' : 'Panduan solusi cepat atas kendala operasional yang paling sering dihadapi pengguna.' }}</p>
                    </div>
                </div>

                <div class="space-y-2.5 text-xs">
                    <!-- Q1 -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1.5" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-start font-bold text-slate-900 cursor-pointer">
                            <span>{{ $isAr ? 'س: لماذا يظهر موقعي خارج نطاق مكتب الفرع عبر نظام GPS رغم تواجدي الفعلي داخل المقر؟' : 'Q: Mengapa GPS saya mendeteksi posisi berada di luar radius kantor padahal saya sudah di lokasi?' }}</span>
                            <span class="text-slate-400 font-mono text-sm" x-text="open ? '−' : '+'"></span>
                        </button>
                        <p x-show="open" x-collapse class="text-slate-600 leading-relaxed pt-1">
                            {{ $isAr 
                                ? 'يعود ذلك غالباً إلى عدم دقة إشارة الـ GPS في متصفح الهاتف. يُرجى تفعيل وضع الدقة العالية (High Accuracy) في إعدادات الموقع بهاتفك والانتظار لثوانٍ لتحديث الإحداثيات. وفي حال تكليفك بمهام خارج مقر الشركة (مشاريع أو عملاء)، استخدم شاشة تسجيل الزيارات الخارجية (Absen Visit) المصممة خصيصاً للعمل خارج نطاق الفرع الجغرافي.'
                                : 'Hal ini biasanya disebabkan oleh akurasi GPS browser yang belum optimal. Buka pengaturan lokasi di ponsel Anda dan pilih mode Akurasi Tinggi (High Accuracy). Jika Anda memang ditugaskan bekerja di luar kantor (misal di lokasi proyek atau klien), gunakan menu Absen Visit yang mendukung presensi di luar geofencing cabang.' }}
                        </p>
                    </div>

                    <!-- Q2 -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1.5" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-start font-bold text-slate-900 cursor-pointer">
                            <span>{{ $isAr ? 'س: لماذا لا تظهر قسيمة الراتب الشهري في البوابة الذاتية للموظف حتى الآن؟' : 'Q: Mengapa karyawan belum bisa melihat slip gaji bulanan di portal ESS pribadinya?' }}</span>
                            <span class="text-slate-400 font-mono text-sm" x-text="open ? '−' : '+'"></span>
                        </button>
                        <p x-show="open" x-collapse class="text-slate-600 leading-relaxed pt-1">
                            {{ $isAr 
                                ? 'تُتاح قسيمة الراتب في البوابة الذاتية للموظف آلياً فقط بعد قيام الإدارة المالية أو الموارد البشرية بالنقر على زر المصادقة وإقفال دورة الرواتب (Lock Period). وطالما كانت الدورة في حالة مسودة (Draft) فإن البيانات تظل سرية ومخصصة لفريق الإعداد للمراجعة والتدقيق الداخلي.'
                                : 'Slip gaji baru akan dirilis secara otomatis ke portal karyawan setelah bagian Finance atau HR melakukan klik pada tombol Sahkan & Kunci Payroll (Lock Period). Selama status masih bertanda Draft, slip gaji dilindungi dan hanya dapat diakses oleh tim penggajian untuk verifikasi internal.' }}
                        </p>
                    </div>

                    <!-- Q3 -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1.5" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-start font-bold text-slate-900 cursor-pointer">
                            <span>{{ $isAr ? 'س: ما الإجراء في حال تقدم موظف باستقالته وما زالت بذمته مبالغ سلف متبقية؟' : 'Q: Bagaimana jika seorang karyawan mengajukan resign namun masih memiliki sisa pinjaman kasbon?' }}</span>
                            <span class="text-slate-400 font-mono text-sm" x-text="open ? '−' : '+'"></span>
                        </button>
                        <p x-show="open" x-collapse class="text-slate-600 leading-relaxed pt-1">
                            {{ $isAr 
                                ? 'في مرحلة إخلاء الطرف وتصفية العهد المالية (Clearance) ضمن إجراءات الاستقالة، ينبه النظام بوجود سلف غير مسددة. ويتوجب سداد المتبقي إما نقداً قبل إصدار شهادة الخبرة أو استقطاعه مباشرة من تصفية مستحقات مكافأة نهاية الخدمة والراتب الأخير.'
                                : 'Pada tahap Asset & Financial Clearance proses resign, sistem akan memberikan peringatan (warning) bahwa terdapat sisa pinjaman aktif. Sisa kasbon dapat dilunasi tunai sebelum paklaring diterbitkan atau dipotong langsung dari perhitungan kompensasi gaji terakhir.' }}
                        </p>
                    </div>

                    <!-- Q4 -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1.5" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-start font-bold text-slate-900 cursor-pointer">
                            <span>{{ $isAr ? 'س: كيف يمكن التبديل واستخدام النظام باللغة العربية (المملكة العربية السعودية)؟' : 'Q: Bagaimana cara berganti bahasa ke Bahasa Arab (Saudi Arabia)?' }}</span>
                            <span class="text-slate-400 font-mono text-sm" x-text="open ? '−' : '+'"></span>
                        </button>
                        <p x-show="open" x-collapse class="text-slate-600 leading-relaxed pt-1">
                            {{ $isAr 
                                ? 'اضغط على قائمة اختيار اللغة في الزاوية العلوية (اختر 🇸🇦 العربية). سيقوم النظام فوراً بتفعيل واجهة العرض من اليمين إلى اليسار (RTL) وترجمة جميع القوائم والشاشات والتواريخ والتقاويم وفق الأنظمة المعتمدة في المملكة العربية السعودية.'
                                : 'Klik pemilih bahasa di sudut kanan atas header (pilih 🇸🇦 العربية). Sistem akan secara otomatis mengaktifkan tata letak RTL (Right-to-Left) dan menerjemahkan seluruh label, menu, kalender, dan angka ke format Bahasa Arab Saudi.' }}
                        </p>
                    </div>

                    <!-- Q5 -->
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/80 space-y-1.5" x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="w-full flex items-center justify-between text-start font-bold text-slate-900 cursor-pointer">
                            <span>{{ $isAr ? 'س: كيف يميز نظام الموارد البشرية بين احتساب العمل الإضافي لفروع إندونيسيا وفروع المملكة العربية السعودية؟' : 'Q: Bagaimana sistem HRIS membedakan perhitungan lembur cabang Indonesia dan cabang Arab Saudi?' }}</span>
                            <span class="text-slate-400 font-mono text-sm" x-text="open ? '−' : '+'"></span>
                        </button>
                        <p x-show="open" x-collapse class="text-slate-600 leading-relaxed pt-1">
                            {{ $isAr 
                                ? 'يعتمد النظام سياسة التحديد التلقائي لفرع الموظف (Auto-Country Policy). فعند احتساب الرواتب، يحدد النظام مقر عمل الموظف: حيث يُحسب لموظفي إندونيسيا وفق نظام العمل الإندونيسي (PP 35/2021) بالروبية (IDR)، بينما يُحسب لموظفي المملكة العربية السعودية بموجب المادة 107 من نظام العمل السعودي (أجر الساعة الفعلي + 50% من الأجر الأساسي) بالريال السعودي (SAR). وخلال شهر رمضان المبارك، يخفض النظام ساعات العمل تلقائياً إلى 6 ساعات وفقاً للمادة 98 من نظام العمل السعودي.'
                                : 'Sistem menerapkan arsitektur Auto-Country Policy. Saat periode payroll diproses, sistem membaca kantor cabang penempatan karyawan: karyawan di Indonesia dihitung menggunakan regulasi Depnaker RI (PP 35/2021) dengan mata uang Rupiah (IDR), sedangkan karyawan di Arab Saudi dihitung menggunakan Pasal 107 UU Ketenagakerjaan Arab Saudi (Upah Riil per Jam + Premi 50% Gaji Pokok) dengan mata uang Saudi Riyal (SAR). Khusus saat Bulan Suci Ramadan, sistem juga mendukung penyesuaian otomatis jam kerja normal menjadi 6 jam per hari sesuai Pasal 98 UU KSA.' }}
                        </p>
                    </div>
                </div>

                <!-- Navigation Footer -->
                <div x-show="viewMode === 'focus'" class="pt-5 border-t border-slate-100 flex items-center justify-between text-xs print:hidden">
                    <button type="button" @click="prevModule()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        <span>{{ $isAr ? 'مصفوفة الصلاحيات' : 'Matriks Hak Akses' }}</span>
                    </button>
                    <a href="{{ route('manual-book.pdf', ['lang' => app()->getLocale()]) }}" target="_blank" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>{{ $isAr ? 'تحميل دليل PDF الكامل' : 'Unduh PDF Panduan Lengkap' }}</span>
                    </a>
                </div>
            </section>

        </main>
    </div>

</div>

@push('scripts')
<script>
function manualBookApp() {
    return {
        isAr: {{ $isAr ? 'true' : 'false' }},
        viewMode: 'focus', // 'focus' (interactive single module view) or 'all' (continuous scroll)
        selectedModuleId: 'module-1',
        activeRole: 'all',
        searchQuery: '',

        roleModules: {
            'all': ['module-1', 'module-2', 'module-3', 'module-4', 'module-5', 'module-6', 'module-7', 'module-8', 'module-9', 'module-10', 'module-matrix', 'module-faq'],
            'employee': ['module-1', 'module-2', 'module-3', 'module-4', 'module-5', 'module-8', 'module-matrix', 'module-faq'],
            'manager': ['module-1', 'module-2', 'module-3', 'module-4', 'module-5', 'module-7', 'module-8', 'module-matrix', 'module-faq'],
            'hr': ['module-1', 'module-2', 'module-3', 'module-4', 'module-5', 'module-6', 'module-7', 'module-8', 'module-10', 'module-matrix', 'module-faq'],
            'finance': ['module-4', 'module-5', 'module-6', 'module-9', 'module-matrix', 'module-faq'],
            'investor': ['module-9', 'module-matrix', 'module-faq'],
            'admin': ['module-1', 'module-2', 'module-3', 'module-4', 'module-5', 'module-6', 'module-7', 'module-8', 'module-9', 'module-10', 'module-matrix', 'module-faq']
        },

        moduleList: {{ $isAr ? 'true' : 'false' }} ? [
            { id: 'module-1', icon: '📍', title: '1. الحضور والانصراف والبصمة', tagline: 'تحديد المواقع GPS والزيارات وأجهزة Solution', keywords: 'حضور انصراف بصمة دوام موقع جي بي اس جهاز زيارة تأخير غياب وردية attendance gps biometric visit solution' },
            { id: 'module-2', icon: '🌴', title: '2. الإجازات والأرصدة', tagline: 'الرصيد المتاح وسلسلة الاعتمادات', keywords: 'اجازة اجازات مرضية رصيد عطلة سنوية تقرير طبي استئذان leave cuti izin sakit' },
            { id: 'module-3', icon: '⏱️', title: '3. العمل الإضافي (المادة 107)', tagline: 'تكليف إضافي والمادة 107 ونظام العمل', keywords: 'اضافي عمل اضافي اوفر تايم المادة 107 ساعات رمضان اجور بدلات lembur overtime spkl saudi' },
            { id: 'module-4', icon: '🧾', title: '4. استرداد المصاريف', tagline: 'المطالبات والنفقات وسندات الصرف', keywords: 'مصاريف استرداد فواتير تعويض نفقات سند صرف ريمبرسمنت reimbursement klaim claim sar idr' },
            { id: 'module-5', icon: '💳', title: '5. السلف وقروض الموظفين', tagline: 'محاكاة الأقساط والخصم من الراتب', keywords: 'سلفة سلف قروض اقساط استقطاع تمويل كاسبون kasbon pinjaman loan cicilan' },
            { id: 'module-6', icon: '💰', title: '6. مسير الرواتب (Payroll)', tagline: 'تحويل العملات والبدلات والتأمينات والضرائب', keywords: 'رواتب مسير راتب قسيمة تحويل عملة ريال تامينات ضريبة payroll gaji salary bpjs pph ter' },
            { id: 'module-7', icon: '🚀', title: '7. الترقية والنقل الوظيفي', tagline: 'التدرج المهني والقرارات الإدارية', keywords: 'ترقية نقل وظيفة تعيين انتقال فروع قرار اداري mutasi promosi transfer sk' },
            { id: 'module-8', icon: '🚪', title: '8. الاستقالة وإخلاء الطرف', tagline: 'تسوية العهد وشهادة الخبرة', keywords: 'استقالة اخلاء طرف عهدة شهادة خبرة نهاية خدمة resign offboarding clearance paklaring' },
            { id: 'module-9', icon: '📈', title: '9. المستثمرون والأرباح', tagline: 'رأس المال وتوزيع الحصص ونقطة التعادل', keywords: 'مستثمر ارباح حصص توزيع راس مال نقطة تعادل محفظة investor dividen bep' },
            { id: 'module-10', icon: '🛡️', title: '10. المستخدمون والصلاحيات', tagline: '263+ صلاحية وسجل التدقيق الأمني', keywords: 'مستخدم مستخدمين صلاحيات ادوار ادوار المستخدم سجل تدقيق امان rbac user permission role audit' }
        ] : [
            { id: 'module-1', icon: '📍', title: '1. Presensi & Mesin', tagline: 'GPS Geofencing, Visit & Solution', keywords: 'presensi attendance gps mesin biometric visit koreksi check in check out radius geofencing' },
            { id: 'module-2', icon: '🌴', title: '2. Cuti & Izin Sakit', tagline: 'Saldo Kuota & Approval Berjenjang', keywords: 'cuti izin leave permission saldo kuota sakit tahunan surat dokter mc sakit' },
            { id: 'module-3', icon: '⏱️', title: '3. Lembur / SPKL', tagline: 'Depnaker RI & UU Arab Saudi (Pasal 107)', keywords: 'lembur spkl overtime upah tarif jam kerja depnaker saudi sar riyal pasal 107 ramadan artikel' },
            { id: 'module-4', icon: '🧾', title: '4. Reimbursement', tagline: 'Klaim Biaya Dinas IDR/SAR & Voucher', keywords: 'reimbursement klaim claim biaya nota struk operasional medis voucher kuitansi sar idr' },
            { id: 'module-5', icon: '💳', title: '5. Kasbon & Pinjaman', tagline: 'Simulasi Tenor & Cicilan Gaji', keywords: 'pinjaman kasbon loan cicilan bunga tenor hutang potong slip gaji advance' },
            { id: 'module-6', icon: '💰', title: '6. Siklus Payroll', tagline: 'Kurs SAR, BPJS & PPh 21 TER', keywords: 'payroll gaji salary bpjs pajak pph ter slip kalkulasi lock period sar idr cut off' },
            { id: 'module-7', icon: '🚀', title: '7. Mutasi & Promosi', tagline: 'Transisi Karir & SK PDF Bilingual', keywords: 'mutasi promosi career transition demosi transfer cabang sk surat keputusan transfer' },
            { id: 'module-8', icon: '🚪', title: '8. Resign & Offboarding', tagline: 'Asset Clearance & Paklaring PDF', keywords: 'resign pengunduran diri offboarding clearance serah terima paklaring exit clearance aset' },
            { id: 'module-9', icon: '📈', title: '9. Investor & Dividen', tagline: 'Modal Disetor & Progress BEP', keywords: 'investor modal dividen bep balik modal portofolio bagi hasil break even point' },
            { id: 'module-10', icon: '🛡️', title: '10. User & Hak Akses', tagline: '263+ Permission & Scope Cabang', keywords: 'user role permission group wewenang utilities hak akses rbac audit log scope cabang' }
        ],

        get filteredModuleList() {
            if (this.activeRole === 'all') return this.moduleList;
            const allowed = this.roleModules[this.activeRole] || [];
            return this.moduleList.filter(m => allowed.includes(m.id));
        },

        setRole(role) {
            this.activeRole = role;
            const allowed = this.roleModules[role] || this.roleModules['all'];
            if (!allowed.includes(this.selectedModuleId)) {
                this.selectedModuleId = allowed[0];
            }
        },

        setViewMode(mode) {
            this.viewMode = mode;
            if (mode === 'all') {
                this.$nextTick(() => {
                    const el = document.getElementById(this.selectedModuleId);
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            }
        },

        selectModule(id) {
            this.selectedModuleId = id;
            if (this.viewMode === 'all') {
                const el = document.getElementById(id);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            } else {
                const el = document.getElementById(id);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        },

        prevModule() {
            const list = this.filteredModuleList.map(m => m.id).concat(['module-matrix', 'module-faq']);
            const idx = list.indexOf(this.selectedModuleId);
            if (idx > 0) {
                this.selectedModuleId = list[idx - 1];
                window.scrollTo({ top: 350, behavior: 'smooth' });
            }
        },

        nextModule() {
            const list = this.filteredModuleList.map(m => m.id).concat(['module-matrix', 'module-faq']);
            const idx = list.indexOf(this.selectedModuleId);
            if (idx >= 0 && idx < list.length - 1) {
                this.selectedModuleId = list[idx + 1];
                window.scrollTo({ top: 350, behavior: 'smooth' });
            }
        },

        isModuleVisible(id) {
            // When in search mode:
            if (this.searchQuery.trim().length > 0) {
                const query = this.searchQuery.toLowerCase().trim();
                const mod = this.moduleList.find(m => m.id === id);
                if (mod && (mod.title.toLowerCase().includes(query) || mod.tagline.toLowerCase().includes(query) || mod.keywords.toLowerCase().includes(query))) return true;
                if (id === 'module-matrix' && (this.isAr ? 'مصفوفة صلاحيات ادوار وصول جدول' : 'matriks hak akses role wewenang rbac izin matrix wewenang perbandingan wewenang').includes(query)) return true;
                if (id === 'module-faq' && (this.isAr ? 'اسئلة اسئلة شائعة حلول مشاكل مساعدة استفسار' : 'faq tanya jawab kendala solusi pertanyaan pemecahan masalah bantuan').includes(query)) return true;
                return false;
            }

            // Role filtering:
            const allowed = this.roleModules[this.activeRole] || this.roleModules['all'];
            if (!allowed.includes(id)) {
                return false;
            }

            // Default continuous scroll mode:
            if (this.viewMode === 'all') {
                return true;
            }

            // Focus view mode:
            return this.selectedModuleId === id;
        }
    }
}
</script>
@endpush
@endsection

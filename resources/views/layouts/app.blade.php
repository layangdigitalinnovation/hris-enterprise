<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" class="h-full bg-[#F8FAFC] text-slate-800">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'HRIS Solution Enterprise' }} - {{ __('app.app_tagline') }}</title>
    
    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#2563EB">

    <!-- Google Fonts: Plus Jakarta Sans, JetBrains Mono & Cairo (Arabic RTL) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        html[dir="rtl"], body[dir="rtl"], [dir="rtl"] {
            font-family: 'Cairo', 'Plus Jakarta Sans', sans-serif !important;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display: none !important; }
        .pulse-online {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 2s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col md:flex-row bg-[#F8FAFC] text-slate-800 antialiased selection:bg-blue-600 selection:text-white" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs md:hidden"></div>

    <!-- Sidebar Navigation (Sleek Dark Navy #0F172A from Reference Mockup) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '{{ app()->getLocale() === 'ar' ? 'translate-x-full' : '-translate-x-full' }}'" class="fixed inset-y-0 start-0 z-50 w-64 bg-[#0F172A] border-e border-slate-800/80 flex flex-col transition-transform duration-300 md:sticky md:top-0 md:h-screen md:max-h-screen md:translate-x-0 shadow-xl shrink-0">
        
        <!-- Brand Header (Biometric Fingerprint Logo: HRIS Enterprise) -->
        <div class="h-20 flex items-center gap-3 px-6 border-b border-slate-800/80 bg-[#0B1120]">
            <!-- Luxury Biometric Fingerprint Squircle Badge -->
            <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #38BDF8 0%, #2563EB 50%, #1D4ED8 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(37,99,235,0.45); border: 1px solid rgba(255,255,255,0.25); flex-shrink: 0; position: relative; overflow: hidden;" class="group">
                <!-- Specular Sheen -->
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 50%; background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, transparent 100%); pointer-events: none;"></div>
                <!-- Fingerprint Vector -->
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" style="position: relative; z-index: 2;" class="transition-transform duration-300 group-hover:scale-105">
                    <path d="M12 10a2 2 0 0 0-2 2c0 1.02-.1 2.51-.26 4"/>
                    <path d="M14 13.12c0 2.38 0 6.38-1 8.88"/>
                    <path d="M17.29 21.02c.12-.6.43-2.3.5-3.02"/>
                    <path d="M2 12a10 10 0 0 1 18-6"/>
                    <path d="M2 16h.01"/>
                    <path d="M21.8 16c.2-2 .131-5.354 0-6"/>
                    <path d="M5 19.5C5.5 18 6 15 6 12a6 6 0 0 1 .34-2"/>
                    <path d="M8.65 22c.21-.66.45-1.32.57-2"/>
                    <path d="M9 6.8a6 6 0 0 1 9 5.2v2"/>
                </svg>
                <!-- Biometric Sensor Active Pulse -->
                <div style="position: absolute; top: 13px; left: 21px; width: 3px; height: 3px; border-radius: 50%; background: #67E8F9; box-shadow: 0 0 8px #67E8F9; z-index: 3;"></div>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="font-extrabold text-xl text-white tracking-tight">HRIS</span>
                    <span class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-md tracking-wider" style="color: #38BDF8 !important; background-color: rgba(56, 189, 248, 0.15) !important; border: 1px solid rgba(56, 189, 248, 0.35) !important; display: inline-block;">PRO</span>
                </div>
                <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ __('app.app_tagline') }}</p>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto custom-sidebar-nav">
            @if(auth()->user()->isInvestor())
                <!-- INVESTOR MENU (PORTAL MANDIRI) -->
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.investor_portal') }}</div>
                
                <a href="{{ route('investors.portal') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('investors.portal') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Portofolio & BEP Saya
                </a>
            @elseif(auth()->user()->isEmployee())
                <!-- EMPLOYEE MENU (ESS ONLY) -->
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.main_menu') }}</div>
                
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('app.dashboard_ess') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.self_service') }}</div>

                <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.index') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('app.my_attendance') }}
                </a>

                <a href="{{ route('attendance.adjustments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.adjustments') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('app.attendance_adjustments') }}
                </a>

                <a href="{{ route('attendance.visits.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.visits.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('app.attendance_visits') }}
                </a>

                <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('leaves.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.leaves_permissions') }}
                </a>

                <a href="{{ route('overtime.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('overtime.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.overtime') }}
                </a>

                <a href="{{ route('reimbursements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reimbursements.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    {{ __('app.my_reimbursements') }}
                </a>

                <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('loans.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.my_loans') }}
                </a>

                <a href="{{ route('career-transitions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('career-transitions.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    {{ __('app.my_career') }}
                </a>

                <a href="{{ route('resignations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('resignations.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('app.my_resignation') }}
                </a>

                <a href="{{ route('payroll.my-slips') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('payroll.my-slips') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.my_payslips') }}
                </a>

                @if(auth()->user()->employee)
                <a href="{{ route('employees.show', auth()->user()->employee) }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('employees.show') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ __('app.profile') }}
                </a>
                @endif

            @elseif(auth()->user()->role === 'manager' && !auth()->user()->isFinance())
                <!-- MANAGER MENU (TEAM & APPROVALS ONLY, NO SALARY ACCESS) -->
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.main_menu') }}</div>
                
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('app.dashboard') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.employee_organization') }}</div>

                <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('employees.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.employees') }}
                </a>

                <a href="{{ route('career-transitions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('career-transitions.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    {{ __('app.career_transitions') }}
                </a>

                <a href="{{ route('resignations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('resignations.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('app.employee_resignations') }}
                </a>

                <a href="{{ route('organization.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('organization.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('app.organization_structure') }}
                </a>

                <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('shifts.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.shifts_schedules') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.attendance_machine') }}</div>

                <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.index') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('app.attendance_summary') }}
                </a>

                <a href="{{ route('attendance.adjustments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.adjustments') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('app.attendance_adjustments') }}
                </a>

                <a href="{{ route('attendance.visits.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.visits.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('app.attendance_visits') }}
                </a>

                <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('leaves.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.leaves_permissions') }}
                </a>

                <a href="{{ route('overtime.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('overtime.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.overtime') }}
                </a>

                <a href="{{ route('reimbursements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reimbursements.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    {{ __('app.reimbursement') }}
                </a>

                <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('loans.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.employee_loans') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.dept_reports') }}</div>

                <a href="{{ route('reports.attendance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.attendance') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('app.attendance_report') }}
                </a>

                <a href="{{ route('reports.schedule') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.schedule') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('app.schedule_report') }}
                </a>

                <a href="{{ route('reports.leave') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.leave') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    {{ __('app.leave_report') }}
                </a>

                <a href="{{ route('reports.reimbursement') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.reimbursement') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                    </svg>
                    {{ __('app.reimbursement_report') }}
                </a>

                <a href="{{ route('reports.loans') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.loans') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.loan_report') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.self_service') }}</div>

                <a href="{{ route('payroll.my-slips') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('payroll.my-slips') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.my_payslips') }}
                </a>

            @else
                <!-- HR ADMIN & SUPER ADMIN MENU (FULL MANAGEMENT) -->
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.main_menu') }}</div>
                
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    {{ __('app.dashboard') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.employee_organization') }}</div>

                <a href="{{ route('employees.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('employees.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.employees') }}
                </a>

                <a href="{{ route('career-transitions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('career-transitions.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    {{ __('app.career_transitions') }}
                </a>

                <a href="{{ route('resignations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('resignations.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('app.employee_resignations') }}
                </a>

                <a href="{{ route('organization.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('organization.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    {{ __('app.organization_structure') }}
                </a>

                <a href="{{ route('shifts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('shifts.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.shifts_schedules') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.attendance_machine') }}</div>

                <a href="{{ route('attendance.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.index') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('app.attendance_summary') }}
                </a>

                <a href="{{ route('devices.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('devices.index') || request()->routeIs('devices.create') || request()->routeIs('devices.edit') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                    {{ __('app.solution_devices') }}
                </a>

                <a href="{{ route('attendance.logs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.logs') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    {{ __('app.attendance_logs') }}
                </a>

                <a href="{{ route('attendance.adjustments') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.adjustments') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    {{ __('app.attendance_adjustments') }}
                </a>

                <a href="{{ route('attendance.visits.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('attendance.visits.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('app.attendance_visits') }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.leave_payroll') }}</div>

                <a href="{{ route('leaves.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('leaves.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.leaves_permissions') }}
                </a>

                <a href="{{ route('overtime.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('overtime.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.overtime') }}
                </a>

                <a href="{{ route('reimbursements.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reimbursements.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    {{ __('app.reimbursement') }}
                </a>

                <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('loans.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.employee_loans') }}
                </a>

                <a href="{{ route('payroll.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('payroll.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.payroll') }}
                </a>

                <a href="{{ route('investors.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('investors.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                    {{ app()->getLocale() === 'ar' ? __('app.investors') : 'Investor' }}
                </a>

                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.reports_summary') }}</div>

                <a href="{{ route('reports.attendance') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.attendance') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('app.attendance_report') }}
                </a>

                <a href="{{ route('reports.payroll') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.payroll') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ __('app.payroll_report') }}
                </a>

                <a href="{{ route('reports.schedule') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.schedule') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    {{ __('app.schedule_report') }}
                </a>

                <a href="{{ route('reports.leave') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.leave') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    {{ __('app.leave_report') }}
                </a>

                <a href="{{ route('reports.reimbursement') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.reimbursement') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                    </svg>
                    {{ __('app.reimbursement_report') }}
                </a>

                <a href="{{ route('reports.loans') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('reports.loans') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    {{ __('app.loan_report') }}
                </a>

                @if(auth()->user()->isHrAdmin())
                <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.system') }}</div>

                {{-- Utilities / Hak Akses Dropdown Menu --}}
                <div x-data="{ open: {{ request()->routeIs(['users.*', 'roles.*', 'permissions.*', 'group-permissions.*']) ? 'true' : 'false' }} }" class="space-y-1">
                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-medium transition-all cursor-pointer {{ request()->routeIs(['users.*', 'roles.*', 'permissions.*', 'group-permissions.*']) ? 'bg-slate-800/90 text-white font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <span>{{ __('Utilities') }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': open, 'rotate-90 rtl:-rotate-90': !open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-collapse class="ps-4 pe-1 py-1 space-y-1 border-s-2 border-slate-700/60 ms-5 rtl:ms-0 rtl:me-5 rtl:border-s-0 rtl:border-e-2">
                        {{-- 1. User --}}
                        <a href="{{ route('users.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-[#2563EB] text-white font-semibold shadow-xs' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>{{ __('User') }}</span>
                            </div>
                            <svg class="w-3 h-3 text-slate-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        {{-- 2. Role --}}
                        <a href="{{ route('roles.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('roles.*') ? 'bg-[#2563EB] text-white font-semibold shadow-xs' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>{{ __('Role') }}</span>
                            </div>
                            <svg class="w-3 h-3 text-slate-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        {{-- 3. Permission --}}
                        <a href="{{ route('permissions.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('permissions.*') ? 'bg-[#2563EB] text-white font-semibold shadow-xs' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                </svg>
                                <span>{{ __('Permission') }}</span>
                            </div>
                            <svg class="w-3 h-3 text-slate-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>

                        {{-- 4. Group Permission --}}
                        <a href="{{ route('group-permissions.index') }}" class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-medium transition-all {{ request()->routeIs('group-permissions.*') ? 'bg-[#2563EB] text-white font-semibold shadow-xs' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <div class="flex items-center gap-2.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                                <span>{{ __('Group Permission') }}</span>
                            </div>
                            <svg class="w-3 h-3 text-slate-500 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Audit Log --}}
                <a href="{{ route('audit.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ (request()->routeIs('audit.index') && request('tab') !== 'settings') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    {{ __('app.audit_logs') }}
                </a>

                {{-- Pengaturan Sistem --}}
                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('settings.*') || (request()->routeIs('audit.*') && request('tab') === 'settings') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    {{ __('app.system_settings') }}
                </a>
                @endif
            @endif

            <!-- Panduan Sistem & Manual Book (Semua Pengguna) -->
            <div class="pt-4 px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.help_guide') }}</div>
            <a href="{{ route('manual-book.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all {{ request()->routeIs('manual-book.*') ? 'bg-[#2563EB] text-white shadow-md shadow-blue-600/30 font-semibold' : 'text-slate-400 hover:bg-slate-800/70 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span>{{ __('app.manual_book') }}</span>
                <span class="ms-auto px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">DOCS</span>
            </a>
        </nav>

        <!-- Current User Profile Footer -->
        <div class="p-4 border-t border-slate-800/80 bg-[#0B1120]">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-xs text-white shrink-0 shadow">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-[10px] text-slate-400 truncate">
                            @if(auth()->user()->isSuperAdmin())
                                {{ __('app.role_super_admin') }}
                            @elseif(auth()->user()->isHrAdmin())
                                {{ __('app.role_hr_admin') }}
                            @elseif(auth()->user()->isFinance())
                                {{ __('app.role_finance') }}
                            @elseif(auth()->user()->isInvestor())
                                {{ __('app.role_investor') }}
                            @else
                                {{ __('app.role_employee') }}
                            @endif
                        </p>
                    </div>
                </div>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="{{ __('app.logout') }}" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800/80 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area (Clean Modern Light Theme #F8FAFC from Mockup) -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen overflow-y-auto bg-[#F8FAFC]">
        
        <!-- Top Navbar (Crisp Pure White with Search Bar from Mockup) -->
        <header class="h-20 min-h-[5rem] bg-white border-b border-slate-200/80 px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
            <div class="flex items-center gap-4 flex-1 max-w-xl">
                <button @click="sidebarOpen = true" class="p-2.5 rounded-xl text-slate-500 hover:text-slate-800 md:hidden hover:bg-slate-100 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <!-- Quick Search Input (Mockup style: "Search menu, employee, or anything... ⌘K") -->
                <div class="hidden sm:flex items-center w-full max-w-md relative">
                    <svg class="w-4 h-4 text-slate-400 absolute start-3.5 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" placeholder="{{ __('app.search_placeholder') }}"
                        class="w-full ps-10 pe-12 py-2.5 text-xs bg-slate-50/80 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white transition shadow-2xs">
                    <span class="absolute end-3 px-2 py-0.5 rounded text-[10px] font-mono font-medium text-slate-400 bg-white border border-slate-200 shadow-2xs">⌘K</span>
                </div>
            </div>

            <!-- Topbar Right Profile & Quick Actions -->
            <div class="flex items-center gap-2.5 sm:gap-3 lg:gap-4">
                <!-- Date Pill -->
                <div class="hidden xl:flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100/90 text-slate-600 text-xs font-medium border border-slate-200/60 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ Carbon\Carbon::now()->locale(app()->getLocale())->translatedFormat('l, d M Y') }}</span>
                </div>

                <!-- Multi-Language Switcher Dropdown (Indonesia 🇮🇩 & Saudi Arabia 🇸🇦) -->
                <div class="relative" x-data="{ langDropdown: false }" @click.outside="langDropdown = false">
                    <button @click="langDropdown = !langDropdown" type="button" 
                        class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold border border-slate-200/90 transition shadow-2xs hover:shadow-xs cursor-pointer"
                        title="{{ __('app.switch_language') }}">
                        @if(app()->getLocale() === 'ar')
                            <span class="text-sm leading-none">🇸🇦</span>
                            <span class="font-bold text-xs text-slate-800">العربية</span>
                        @else
                            <span class="text-sm leading-none">🇮🇩</span>
                            <span class="font-bold text-xs text-slate-800">Indonesia</span>
                        @endif
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="langDropdown ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="langDropdown" x-cloak 
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute end-0 mt-2 w-56 rounded-2xl bg-white border border-slate-200 shadow-xl py-2 z-50">
                        
                        <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1">
                            {{ __('app.switch_language') }}
                        </div>

                        <!-- Bahasa Indonesia -->
                        <a href="{{ route('lang.switch', 'id') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 text-xs text-slate-700 hover:bg-blue-50/70 hover:text-blue-700 transition font-medium {{ app()->getLocale() === 'id' ? 'bg-blue-50 text-blue-700 font-bold' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base leading-none">🇮🇩</span>
                                <div>
                                    <span class="block">Bahasa Indonesia</span>
                                    <span class="text-[10px] text-slate-400 font-normal">Indonesia</span>
                                </div>
                            </div>
                            @if(app()->getLocale() === 'id')
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </a>

                        <!-- Arabic (Saudi Arabia) -->
                        <a href="{{ route('lang.switch', 'ar') }}" 
                           class="flex items-center justify-between px-3.5 py-2.5 text-xs text-slate-700 hover:bg-blue-50/70 hover:text-blue-700 transition font-medium {{ app()->getLocale() === 'ar' ? 'bg-blue-50 text-blue-700 font-bold' : '' }}">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base leading-none">🇸🇦</span>
                                <div>
                                    <span class="block font-sans">العربية</span>
                                    <span class="text-[10px] text-slate-400 font-normal">المملكة العربية السعودية</span>
                                </div>
                            </div>
                            @if(app()->getLocale() === 'ar')
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                </svg>
                            @endif
                        </a>
                    </div>
                </div>

                <!-- Quick Link to Manual Book / Panduan -->
                <a href="{{ route('manual-book.index') }}" 
                   class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 text-xs font-semibold border border-slate-200/90 transition shadow-2xs hover:shadow-xs"
                   title="{{ __('app.manual_book') }}">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span>{{ app()->getLocale() === 'ar' ? 'الدليل' : 'Panduan' }}</span>
                </a>

                <!-- Sync Action -->
                @if(auth()->user()->canManageDevices())
                <a href="{{ route('devices.index') }}" class="px-3.5 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-semibold border border-blue-200/80 transition flex items-center gap-2 shadow-2xs hover:shadow-xs">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span class="hidden sm:inline">{{ __('app.sync_device') }}</span>
                </a>
                @endif

                <!-- Notification Bell for Approvals -->
                @include('layouts.partials.notification_bell')

                <!-- User Top Profile -->
                <div class="flex items-center gap-3 ps-3 border-s border-slate-200">
                    <div class="w-10 h-10 rounded-full bg-blue-600 text-white font-bold text-sm flex items-center justify-center shadow-xs ring-2 ring-blue-100 shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="hidden md:block text-start">
                        <span class="text-xs font-bold text-slate-900 block leading-tight">{{ auth()->user()->name }}</span>
                        <span class="text-[11px] text-slate-500 block mt-0.5 leading-tight">
                            @if(auth()->user()->isSuperAdmin())
                                {{ __('app.role_super_admin') }}
                            @elseif(auth()->user()->isHrAdmin())
                                {{ __('app.role_hr_admin') }}
                            @elseif(auth()->user()->isFinance())
                                {{ __('app.role_finance') }}
                            @elseif(auth()->user()->isInvestor())
                                {{ __('app.role_investor') }}
                            @else
                                {{ __('app.role_employee') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="flex-1 p-6 lg:p-8 space-y-6 max-w-7xl w-full mx-auto">
            
            <!-- Alert Messages (Soft pastel cards with colored borders) -->
            @if (session('reset_credential'))
                @php $cred = session('reset_credential'); @endphp
                <div x-data="{ 
                        show: true, 
                        copiedPass: false, 
                        copiedMsg: false,
                        copyPassword() {
                            navigator.clipboard.writeText('{{ $cred['password'] }}');
                            this.copiedPass = true;
                            setTimeout(() => this.copiedPass = false, 2500);
                        },
                        copyWhatsAppMessage() {
                            const text = `Halo {{ $cred['name'] }},\nPassword akun HRIS Anda telah direset oleh Super Admin.\n\n🌐 URL Login: {{ $cred['login_url'] ?? url('/login') }}\n📧 Email: {{ $cred['email'] }}\n🔑 Password Baru: {{ $cred['password'] }}\n\nSilakan segera login dan ganti password Anda demi keamanan. Terima kasih!`;
                            navigator.clipboard.writeText(text);
                            this.copiedMsg = true;
                            setTimeout(() => this.copiedMsg = false, 2500);
                        }
                    }" 
                    x-show="show" 
                    class="p-5 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50 to-emerald-50 border border-blue-200 shadow-sm relative text-slate-800">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-2xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-500/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                </svg>
                            </div>
                            <div class="space-y-2">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                        <span>{{ __('Password Karyawan Berhasil Direset!') }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">{{ __('Akun Siap Digunakan') }}</span>
                                    </h4>
                                    <p class="text-xs text-slate-600 mt-0.5">{{ __('Password baru untuk karyawan :name telah aktif di sistem. Berikan kredensial di bawah ini kepada karyawan yang bersangkutan.', ['name' => $cred['name']]) }}</p>
                                </div>
                                
                                <div class="flex flex-wrap items-center gap-3 bg-white p-3.5 rounded-xl border border-blue-200/80 text-xs shadow-2xs">
                                    <div>
                                        <span class="text-slate-400 block text-[10px] font-semibold uppercase tracking-wider">{{ __('Email Login') }}</span>
                                        <span class="font-semibold text-slate-900 font-mono">{{ $cred['email'] }}</span>
                                    </div>
                                    <div class="h-7 w-px bg-slate-200 hidden sm:block"></div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px] font-semibold uppercase tracking-wider">{{ __('Password Baru') }}</span>
                                        <span class="font-mono font-bold text-blue-600 text-sm bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-200">{{ $cred['password'] }}</span>
                                    </div>
                                    <div class="h-7 w-px bg-slate-200 hidden sm:block"></div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px] font-semibold uppercase tracking-wider">{{ __('Peran Akun') }}</span>
                                        <span class="font-semibold text-slate-700 capitalize">{{ __(str_replace('_', ' ', $cred['role'])) }}</span>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center gap-2 pt-1">
                                    <button type="button" @click="copyPassword()" class="px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        <span x-text="copiedPass ? '{{ __('Password Tersalin! ✓') }}' : '{{ __('Salin Password') }}'">{{ __('Salin Password') }}</span>
                                    </button>
                                    <button type="button" @click="copyWhatsAppMessage()" class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition flex items-center gap-1.5 shadow-2xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span x-text="copiedMsg ? '{{ __('Format WhatsApp Tersalin! ✓') }}' : '{{ __('Salin Format Pesan WhatsApp') }}'">{{ __('Salin Format Pesan WhatsApp') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg hover:bg-white/80 transition" title="{{ __('Tutup') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('success') && !session('reset_credential'))
                <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-xs font-medium shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start justify-between text-xs font-medium shadow-xs gap-3">
                    <div class="flex items-start gap-2.5">
                        <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <div class="font-bold text-rose-900 text-xs mb-0.5">Perhatian: Data Tidak Dapat Diproses</div>
                            <span class="text-rose-700 leading-relaxed">{{ session('error') }}</span>
                        </div>
                    </div>
                    <button @click="show = false" class="text-rose-400 hover:text-rose-700 p-1 transition shrink-0" title="Tutup">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="py-5 px-8 border-t border-slate-200/80 bg-white text-center text-xs text-slate-500 mt-auto">
            <p>© {{ date('Y') }} {{ \App\Models\SystemSetting::get('company_name', 'HRIS Solution Enterprise') }} · {{ __('app.footer_text') }}</p>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>

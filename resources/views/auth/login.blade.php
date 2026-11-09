<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Masuk ke Akun') }} - {{ __('HRIS Solution Enterprise') }}</title>
    
    <!-- Favicon & Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#2563EB">

    <!-- Google Fonts: Plus Jakarta Sans, JetBrains Mono, & Cairo for Arabic -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #F8FAFC;
            color: #0F172A;
            overflow-x: hidden;
        }
        html[dir="rtl"], body[dir="rtl"], [dir="rtl"] {
            font-family: 'Cairo', 'Plus Jakarta Sans', sans-serif !important;
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Desktop Layout: Fixed 100vh with ZERO SCROLLING */
        @media (min-width: 1024px) {
            html, body {
                overflow: hidden !important;
                height: 100vh !important;
            }
            .login-wrapper {
                display: flex !important;
                flex-direction: row !important;
                height: 100vh !important;
                max-height: 100vh !important;
                overflow: hidden !important;
            }
            .hero-panel {
                display: flex !important;
                flex: 1.15;
                height: 100vh;
                overflow: hidden;
                padding: 36px 44px;
                text-align: start;
            }
            .form-panel {
                flex: 0.95;
                height: 100vh;
                overflow: hidden;
                padding: 24px 36px;
            }
            .mobile-brand {
                display: none !important;
            }
        }

        /* Mobile Layout: Normal vertical scroll */
        @media (max-width: 1023px) {
            html, body {
                overflow-y: auto !important;
                min-height: 100%;
            }
            .login-wrapper {
                display: flex;
                flex-direction: column;
                min-height: 100vh;
                padding: 32px 16px;
            }
            .hero-panel {
                display: none !important;
            }
            .form-panel {
                width: 100%;
                padding: 0;
            }
            .mobile-brand {
                display: block !important;
            }
        }

        /* Scanner beam animation */
        @keyframes scanSweep {
            0% { top: 4%; opacity: 0; }
            20% { opacity: 0.9; }
            80% { opacity: 0.9; }
            100% { top: 92%; opacity: 0; }
        }
        .scanner-beam {
            position: absolute;
            left: 0;
            right: 0;
            height: 2.5px;
            background: linear-gradient(90deg, transparent 0%, #38BDF8 50%, transparent 100%);
            box-shadow: 0 0 12px #38BDF8, 0 0 20px #0284C7;
            animation: scanSweep 3s ease-in-out infinite alternate;
        }

        /* Ambient Glow Pulse */
        @keyframes softPulse {
            0%, 100% { opacity: 0.35; transform: scale(1); }
            50% { opacity: 0.65; transform: scale(1.04); }
        }
        .ambient-glow {
            animation: softPulse 6s ease-in-out infinite;
        }

        /* Form Input */
        .form-input {
            width: 100%;
            padding: 10px 14px 10px 40px;
            border-radius: 10px;
            border: 1px solid #CBD5E1;
            background: #FFFFFF;
            font-size: 13px;
            color: #0F172A;
            font-family: inherit;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #2563EB;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
            background: #FFFFFF;
        }
    </style>
</head>
<body class="min-h-full antialiased selection:bg-blue-600 selection:text-white" x-data="{
    showPassword: false
}">

<div class="login-wrapper">

    <!-- ========================================== -->
    <!-- LEFT PANEL: Hero & HRIS Platform Showcase  -->
    <!-- ========================================== -->
    <div class="hero-panel" style="background: #0A0F1D; position: relative; overflow: hidden; flex-direction: column; justify-content: space-between; border-inline-end: 1px solid #1E293B; text-align: start;">
        
        <!-- Ambient Radial Background Gradients -->
        <div style="position: absolute; top: -120px; left: -120px; width: 500px; height: 500px; border-radius: 50%; background: radial-gradient(circle, rgba(37,99,235,0.28) 0%, transparent 70%); pointer-events: none;" class="ambient-glow"></div>
        <div style="position: absolute; bottom: -120px; right: -120px; width: 550px; height: 550px; border-radius: 50%; background: radial-gradient(circle, rgba(56,189,248,0.18) 0%, transparent 70%); pointer-events: none;" class="ambient-glow"></div>
        
        <!-- Subtle High-Tech Grid Pattern -->
        <div style="position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,0.07) 1px, transparent 1px); background-size: 26px 26px; pointer-events: none;"></div>

        <!-- Top Header / Brand Logo -->
        <div style="position: relative; z-index: 10; display: flex; align-items: center; gap: 12px;">
            <div style="width: 40px; height: 40px; border-radius: 11px; background: linear-gradient(135deg, #38BDF8 0%, #2563EB 50%, #1D4ED8 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 6px 16px -3px rgba(37,99,235,0.5); border: 1px solid rgba(255,255,255,0.25); flex-shrink: 0; position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; left: 0; right: 0; height: 50%; background: linear-gradient(180deg, rgba(255,255,255,0.25) 0%, transparent 100%); pointer-events: none;"></div>
                <svg width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" style="position: relative; z-index: 2;">
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
                <div style="position: absolute; top: 12.5px; left: 20px; width: 3px; height: 3px; border-radius: 50%; background: #67E8F9; box-shadow: 0 0 8px #67E8F9; z-index: 3;"></div>
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 800; font-size: 19px; color: #FFFFFF; letter-spacing: -0.03em;">HRIS</span>
                    <span style="font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 6px; color: #38BDF8; background-color: rgba(56,189,248,0.15); border: 1px solid rgba(56,189,248,0.3); letter-spacing: 0.06em;">ENTERPRISE PRO</span>
                </div>
                <p style="font-size: 11px; color: #94A3B8; margin-top: 1px;">{{ __('Smart Workforce Management Ecosystem') }}</p>
            </div>
        </div>

        <!-- Center Feature Content -->
        <div style="position: relative; z-index: 10; max-width: 520px; margin: auto 0; padding: 12px 0;">
            
            <!-- Modern Feature Badge Card -->
            <div style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(56, 189, 248, 0.22); border-radius: 16px; padding: 16px 20px; margin-bottom: 22px; display: flex; align-items: center; gap: 16px; box-shadow: 0 16px 32px -12px rgba(0,0,0,0.5);">
                <!-- Animated Tech Icon Box -->
                <div style="width: 58px; height: 58px; border-radius: 15px; background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); border: 1px solid rgba(56, 189, 248, 0.4); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; flex-shrink: 0; box-shadow: inset 0 0 16px rgba(56, 189, 248, 0.2);">
                    <div class="scanner-beam"></div>
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
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
                </div>
                <div style="min-width: 0;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 3px;">
                        <span style="display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: #10B981; box-shadow: 0 0 8px #10B981; flex-shrink: 0;"></span>
                        <span style="font-size: 10px; font-weight: 800; color: #34D399; letter-spacing: 0.06em; text-transform: uppercase;">{{ __('HR Core Online') }}</span>
                    </div>
                    <h3 style="font-size: 14px; font-weight: 700; color: #FFFFFF; letter-spacing: -0.01em;">{{ __('Sistem Presensi & Manajemen SDM Terpadu') }}</h3>
                    <p style="font-size: 11px; color: #94A3B8; margin-top: 3px; line-height: 1.5;">{{ __('Otomasi kehadiran, toleransi keterlambatan, lembur (SPKL) hingga kalkulasi slip gaji instan.') }}</p>
                </div>
            </div>

            <!-- Big Impact Headline -->
            <h1 style="font-size: 27px; font-weight: 800; color: #FFFFFF; line-height: 1.35; letter-spacing: -0.02em; margin-bottom: 12px;">
                {{ __('Kelola Seluruh Siklus SDM Perusahaan dalam Satu Platform.') }}
            </h1>
            <p style="font-size: 13px; color: #94A3B8; line-height: 1.6; margin-bottom: 20px;">
                {{ __('Solusi enterprise menyeluruh untuk pengelolaan kehadiran karyawan, pengajuan cuti & izin, master organisasi berjenjang, dan penggajian akurat.') }}
            </p>

            <!-- Feature Checklist -->
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #CBD5E1;">
                    <div style="width: 20px; height: 20px; border-radius: 5px; background: rgba(37,99,235,0.25); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; color: #38BDF8; font-weight: bold; font-size: 11px; flex-shrink: 0;">✓</div>
                    <span>{{ __('Presensi Real-Time, Sinkronisasi Mesin Absensi & Geofencing GPS') }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #CBD5E1;">
                    <div style="width: 20px; height: 20px; border-radius: 5px; background: rgba(37,99,235,0.25); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; color: #38BDF8; font-weight: bold; font-size: 11px; flex-shrink: 0;">✓</div>
                    <span>{{ __('Payroll Otomatis: Tunjangan, Potongan, BPJS & PPh 21 TER Terpadu') }}</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #CBD5E1;">
                    <div style="width: 20px; height: 20px; border-radius: 5px; background: rgba(37,99,235,0.25); border: 1px solid rgba(56,189,248,0.3); display: flex; align-items: center; justify-content: center; color: #38BDF8; font-weight: bold; font-size: 11px; flex-shrink: 0;">✓</div>
                    <span>{{ __('Employee Self-Service (ESS): Cuti, Izin, Lembur & E-Slip Karyawan') }}</span>
                </div>
            </div>

        </div>

        <!-- Bottom Security & Trust Footer -->
        <div style="position: relative; z-index: 10; display: flex; align-items: center; gap: 18px; padding-top: 18px; border-top: 1px solid #1E293B; font-size: 11px; color: #64748B;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" fill="none" stroke="#10B981" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                <span>{{ __('SSL 256-Bit Encrypted') }}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" fill="none" stroke="#38BDF8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>{{ __('Multi-Platform Cloud Ready') }}</span>
            </div>
            <div style="margin-inline-start: auto;">
                <span>{{ __('v2.4 Enterprise') }}</span>
            </div>
        </div>

    </div>

    <!-- ========================================== -->
    <!-- RIGHT PANEL: Authentication Form           -->
    <!-- ========================================== -->
    <div class="form-panel" style="display: flex; flex-direction: column; justify-content: center; align-items: center; background: #F8FAFC;">
        
        <div style="width: 100%; max-width: 410px;">

            <!-- Mobile Brand Header -->
            <div class="mobile-brand" style="text-align: center; margin-bottom: 24px;">
                <div style="width: 48px; height: 48px; border-radius: 13px; background: linear-gradient(135deg, #38BDF8 0%, #2563EB 50%, #1D4ED8 100%); margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 18px -4px rgba(37,99,235,0.4); border: 1px solid rgba(255,255,255,0.25);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
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
                </div>
                <h2 style="font-size: 20px; font-weight: 800; color: #0F172A; letter-spacing: -0.02em;">{{ __('HRIS Enterprise') }}</h2>
                <p style="font-size: 11px; color: #64748B;">{{ __('Sistem Manajemen Kepegawaian') }}</p>
            </div>

            <!-- Language Switcher Pill -->
            <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
                <div style="display: inline-flex; align-items: center; background: #E2E8F0; padding: 3px; border-radius: 12px; font-size: 11px; font-weight: 600;">
                    <a href="{{ route('lang.switch', 'id') }}" 
                       style="display: flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 9px; text-decoration: none; transition: all 0.2s; {{ app()->getLocale() === 'id' ? 'background: #FFFFFF; color: #1E293B; box-shadow: 0 1px 3px rgba(0,0,0,0.1); font-weight: 700;' : 'color: #64748B;' }}">
                        <span>🇮🇩</span>
                        <span>Indonesia</span>
                    </a>
                    <a href="{{ route('lang.switch', 'ar') }}" 
                       style="display: flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 9px; text-decoration: none; transition: all 0.2s; {{ app()->getLocale() === 'ar' ? 'background: #FFFFFF; color: #1E293B; box-shadow: 0 1px 3px rgba(0,0,0,0.1); font-weight: 700;' : 'color: #64748B;' }}">
                        <span>🇸🇦</span>
                        <span class="font-sans">العربية</span>
                    </a>
                </div>
            </div>

            <!-- Header Above Card -->
            <div style="margin-bottom: 16px;">
                <h2 style="font-size: 23px; font-weight: 800; color: #0F172A; letter-spacing: -0.03em;">{{ __('app.login_title') }}</h2>
                <p style="font-size: 12px; color: #64748B; margin-top: 3px;">{{ __('app.login_subtitle') }}</p>
            </div>

            <!-- Login Card -->
            <div style="background: #FFFFFF; border-radius: 20px; padding: 24px 26px; border: 1px solid #E2E8F0; box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 18px 40px -10px rgba(15, 23, 42, 0.06);">
                
                <!-- Error Alert -->
                @if ($errors->any())
                    <div style="margin-bottom: 14px; padding: 9px 13px; border-radius: 10px; background: #FEF2F2; border: 1px solid #FECACA; color: #B91C1C; font-size: 11.5px; font-weight: 500; display: flex; align-items: center; gap: 9px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Info / Logout Notice Toast -->
                @if (session('info'))
                    <div style="margin-bottom: 14px; padding: 9px 13px; border-radius: 10px; background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; font-size: 11.5px; font-weight: 500; display: flex; align-items: center; gap: 9px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2" style="flex-shrink: 0;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" style="display: flex; flex-direction: column; gap: 13px;">
                    @csrf
                    
                    <!-- Email Field -->
                    <div>
                        <label for="email" style="display: block; font-size: 11.5px; font-weight: 700; color: #334155; margin-bottom: 5px;">
                            {{ __('app.email_label') }}
                        </label>
                        <div style="position: relative; display: flex; align-items: center;">
                            <div style="position: absolute; inset-inline-start: 13px; color: #94A3B8; pointer-events: none; display: flex; align-items: center;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input type="email" name="email" id="email" required value="{{ old('email') }}" autocomplete="username"
                                class="form-input" style="padding-inline-start: 38px; padding-inline-end: 14px;"
                                placeholder="{{ __('nama@perusahaan.com') }}">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 5px;">
                            <label for="password" style="font-size: 11.5px; font-weight: 700; color: #334155;">
                                {{ __('app.password_label') }}
                            </label>
                            <span class="text-[11px] text-blue-600 font-medium cursor-pointer hover:underline" style="font-size: 11px; font-weight: 600; color: #2563EB; cursor: pointer;">{{ __('Lupa sandi?') }}</span>
                        </div>
                        <div style="position: relative; display: flex; align-items: center;">
                            <div style="position: absolute; inset-inline-start: 13px; color: #94A3B8; pointer-events: none; display: flex; align-items: center;">
                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required autocomplete="current-password"
                                class="form-input" style="padding-inline-start: 38px; padding-inline-end: 42px;"
                                placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword"
                                style="position: absolute; inset-inline-end: 10px; background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px; display: flex; align-items: center;"
                                :title="showPassword ? '{{ __('Sembunyikan kata sandi') }}' : '{{ __('Tampilkan kata sandi') }}'">
                                <svg x-show="!showPassword" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1px;">
                        <label style="display: flex; align-items: center; gap: 7px; cursor: pointer; font-size: 11.5px; color: #475569;">
                            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} style="width: 15px; height: 15px; border-radius: 4px; accent-color: #2563EB;">
                            <span>{{ __('app.remember_me') }}</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" style="width: 100%; padding: 11px 18px; border-radius: 11px; background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); color: #FFFFFF; font-size: 12.5px; font-weight: 700; border: none; cursor: pointer; box-shadow: 0 6px 16px -3px rgba(37,99,235,0.45); display: flex; align-items: center; justify-content: center; gap: 7px; transition: all 0.2s;">
                        <span>{{ __('app.sign_in') }}</span>
                        <svg class="rtl:rotate-180" width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>

                </form>
            </div>

            <!-- Footer Notes -->
            <div style="margin-top: 18px; text-align: center;">
                <p style="font-size: 11px; color: #94A3B8;">
                    © {{ date('Y') }} {{ \App\Models\SystemSetting::get('company_name', 'PT Layang Digital Innovation') }} · {{ __('HRIS Enterprise Ecosystem') }}
                </p>
                <div style="display: flex; justify-content: center; gap: 14px; margin-top: 5px; font-size: 10.5px; color: #64748B;">
                    <a href="#" style="color: inherit; text-decoration: none;" class="hover:underline">{{ __('Kebijakan Privasi') }}</a>
                    <span>•</span>
                    <a href="#" style="color: inherit; text-decoration: none;" class="hover:underline">{{ __('Ketentuan Layanan') }}</a>
                    <span>•</span>
                    <a href="#" style="color: inherit; text-decoration: none;" class="hover:underline">{{ __('Pusat Bantuan') }}</a>
                </div>
            </div>

        </div>

    </div>

</div>

</body>
</html>

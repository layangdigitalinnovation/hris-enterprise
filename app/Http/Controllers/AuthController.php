<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        $targetUser = User::where('email', $credentials['email'])->first();
        if ($targetUser && !$targetUser->is_active) {
            return back()->withErrors([
                'email' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Super Admin untuk mereset password dan mengaktifkan akun Anda kembali.',
            ])->onlyInput('email');
        }

        if (Auth::attempt(array_merge($credentials, ['is_active' => true]), $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'login',
                'description' => "User {$user->name} ({$user->email}) berhasil login.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            if ($user->isInvestor()) {
                return redirect()->route('investors.portal')->with('success', "Selamat datang di Portal Investor, {$user->name}!");
            }

            return redirect()->intended(route('dashboard'))->with('success', "Selamat datang kembali, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            AuditLog::create([
                'user_id' => $user->id,
                'event' => 'logout',
                'description' => "User {$user->name} telah logout.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }
}

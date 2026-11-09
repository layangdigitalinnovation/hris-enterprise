<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SystemSetting;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(auth()->user()?->isHrAdmin(), 403, __('Akses terbatas untuk Administrator.'));

        $query = AuditLog::with('user');

        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        if ($search = $request->input('search')) {
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $settings = SystemSetting::all();
        $currentTab = $request->input('tab', 'audit');

        return view('audit.index', compact('logs', 'settings', 'currentTab'));
    }

    public function settings(Request $request)
    {
        abort_unless(auth()->user()?->isHrAdmin(), 403, __('Akses terbatas untuk Administrator.'));

        $query = AuditLog::with('user');
        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $settings = SystemSetting::all();
        $currentTab = 'settings';

        return view('audit.index', compact('logs', 'settings', 'currentTab'));
    }

    public function updateSettings(Request $request)
    {
        abort_unless(auth()->user()?->isHrAdmin(), 403, __('Akses terbatas untuk Administrator.'));

        $inputs = $request->except(['_token']);

        foreach ($inputs as $key => $val) {
            SystemSetting::set($key, $val);
        }

        if (isset($inputs['overtime_rate_per_hour'])) {
            \App\Models\OvertimeRate::where('code', 'OT_WEEKDAY')->update([
                'rate_per_hour' => $inputs['overtime_rate_per_hour'],
                'calculation_type' => ($inputs['overtime_calculation_type'] ?? 'flat') === 'depnaker' ? 'depnaker_formula' : 'flat',
            ]);
        }
        if (isset($inputs['overtime_rate_weekend_per_hour'])) {
            \App\Models\OvertimeRate::where('code', 'OT_WEEKEND')->update([
                'rate_per_hour' => $inputs['overtime_rate_weekend_per_hour'],
            ]);
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'event' => 'update_settings',
            'description' => 'Memperbarui konfigurasi sistem HRIS.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', __('Pengaturan sistem berhasil disimpan!'));
    }
}

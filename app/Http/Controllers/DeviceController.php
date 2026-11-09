<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\AttendanceSyncRun;
use App\Models\Branch;
use App\Models\Device;
use App\Services\Attendance\AttendanceEngineService;
use App\Services\Device\SolutionConnectorService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function __construct(
        protected SolutionConnectorService $connector,
        protected AttendanceEngineService $engine
    ) {}

    public function index()
    {
        $allDevices = Device::with(['branch', 'employeeMappings.employee'])
            ->withCount(['attendanceLogs', 'employeeMappings'])
            ->orderBy('id', 'asc')
            ->get();

        $devices = Device::with(['branch', 'employeeMappings.employee'])
            ->withCount(['attendanceLogs', 'employeeMappings'])
            ->orderBy('id', 'asc')
            ->paginate(4, ['*'], 'devices_page')
            ->withQueryString();

        $syncRuns = AttendanceSyncRun::with('device')
            ->orderBy('run_at', 'desc')
            ->limit(10)
            ->get();

        $recentRawLogs = AttendanceLog::with(['device', 'employee'])
            ->orderBy('log_time', 'desc')
            ->limit(15)
            ->get();

        $mappings = \App\Models\DeviceEmployeeMapping::with(['device', 'employee.department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'mappings_page')
            ->withQueryString();

        $employees = \App\Models\Employee::where('status', 'active')->get();

        $detectedUsers = [];
        foreach ($allDevices as $d) {
            $cached = \Illuminate\Support\Facades\Cache::get("device_{$d->id}_hardware_users", []);
            if (!empty($cached)) {
                $logCounts = AttendanceLog::where('device_id', $d->id)
                    ->selectRaw('device_pin, count(*) as count')
                    ->groupBy('device_pin')
                    ->pluck('count', 'device_pin')
                    ->toArray();

                $mappedPins = $d->employeeMappings->keyBy('device_pin');

                $detectedUsers[$d->id] = [
                    'device' => $d,
                    'users' => collect($cached)->map(function ($u) use ($logCounts, $mappedPins) {
                        $pin = (string)($u['userid'] ?? $u['uid'] ?? '');
                        $mapping = $mappedPins->get($pin);
                        $employee = $mapping ? $mapping->employee : null;
                        return [
                            'pin' => $pin,
                            'name' => trim($u['name'] ?? ''),
                            'role' => (int)($u['role'] ?? 0),
                            'cardno' => trim($u['cardno'] ?? ''),
                            'log_count' => $logCounts[$pin] ?? 0,
                            'mapped_employee' => $employee,
                        ];
                    })->sortBy(fn($u) => (int)$u['pin'])->values(),
                ];
            }
        }

        return view('devices.index', compact('devices', 'allDevices', 'syncRuns', 'recentRawLogs', 'mappings', 'employees', 'detectedUsers'));
    }

    public function create()
    {
        $branches = Branch::where('is_active', true)->get();
        return view('devices.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'device_model' => 'required|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'port' => 'required|integer',
            'comm_key' => 'required|string|max:50',
            'serial_number' => 'nullable|string|max:100|unique:devices,serial_number',
            'branch_id' => 'nullable|exists:branches,id',
            'location' => 'nullable|string|max:150',
            'sync_method' => 'required|in:lan_tcp,cloud_adms,file_import,mock',
            'auto_sync' => 'nullable|boolean',
            'sync_interval_minutes' => 'required|integer|min:1',
        ]);

        $validated['auto_sync'] = $request->boolean('auto_sync');
        $validated['status'] = $validated['sync_method'] === 'mock' ? 'online' : 'offline';

        $device = Device::create($validated);

        return redirect()->route('devices.index')->with('success', "Perangkat Mesin Absensi '{$device->name}' berhasil ditambahkan!");
    }

    public function edit(Device $device)
    {
        $branches = Branch::where('is_active', true)->get();
        return view('devices.edit', compact('device', 'branches'));
    }

    public function update(Request $request, Device $device)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'device_model' => 'required|string|max:100',
            'ip_address' => 'nullable|string|max:45',
            'port' => 'required|integer',
            'comm_key' => 'required|string|max:50',
            'serial_number' => "nullable|string|max:100|unique:devices,serial_number,{$device->id}",
            'branch_id' => 'nullable|exists:branches,id',
            'location' => 'nullable|string|max:150',
            'sync_method' => 'required|in:lan_tcp,cloud_adms,file_import,mock',
            'status' => 'nullable|in:online,offline,disabled',
            'auto_sync' => 'nullable|boolean',
            'sync_interval_minutes' => 'required|integer|min:1',
        ]);

        $validated['auto_sync'] = $request->boolean('auto_sync');
        $device->update($validated);

        return redirect()->route('devices.index')->with('success', "Data mesin absensi '{$device->name}' berhasil diperbarui!");
    }

    public function destroy(Device $device)
    {
        $name = $device->name;
        \Illuminate\Support\Facades\Cache::forget("device_{$device->id}_hardware_users");
        $device->delete();
        return redirect()->route('devices.index')->with('success', "Perangkat {$name} telah dihapus.");
    }

    public function clearDetectedUsers(Device $device)
    {
        \Illuminate\Support\Facades\Cache::forget("device_{$device->id}_hardware_users");
        return back()->with('success', "Daftar user deteksi mesin '{$device->name}' telah ditutup/dibersihkan.");
    }

    public function testConnection(Device $device)
    {
        $result = $this->connector->testConnection($device);
        return response()->json($result);
    }

    public function syncNow(Device $device)
    {
        $result = $this->connector->syncDeviceLogs($device, 'manual');

        if ($result['success']) {
            // Immediately run Attendance Engine for today
            $this->engine->processDailyAttendance(Carbon::today());
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function syncUsers(Device $device)
    {
        try {
            $users = $this->connector->getHardwareUsers($device);
            $count = count($users);
            $matched = 0;

            // Cache hardware users for 7 days so they can be viewed and mapped in the UI
            \Illuminate\Support\Facades\Cache::put("device_{$device->id}_hardware_users", $users, now()->addDays(7));

            foreach ($users as $u) {
                $pin = (string)($u['userid'] ?? $u['uid'] ?? '');
                $name = trim($u['name'] ?? '');
                if (!$pin) continue;

                // Match with active employee by name or employee_code
                $emp = \App\Models\Employee::where('employee_code', $pin)
                    ->orWhereRaw("LOWER(CONCAT(first_name, ' ', last_name)) LIKE ?", ['%' . strtolower($name) . '%'])
                    ->first();

                if ($emp) {
                    \App\Models\DeviceEmployeeMapping::updateOrCreate(
                        ['device_id' => $device->id, 'device_pin' => $pin],
                        [
                            'employee_id' => $emp->id,
                            'privilege' => (isset($u['role']) && (int)$u['role'] === 14) ? 'admin' : 'user',
                            'enrolled_at' => now(),
                        ]
                    );

                    // Retroactively link existing attendance logs that have this PIN to the employee
                    \App\Models\AttendanceLog::where('device_id', $device->id)
                        ->where('device_pin', $pin)
                        ->whereNull('employee_id')
                        ->update(['employee_id' => $emp->id]);

                    $matched++;
                }
            }

            if ($matched > 0) {
                $this->engine->processDailyAttendance(Carbon::today());
            }

            $message = "Berhasil membaca {$count} user dari mesin {$device->name}!";
            if ($matched > 0) {
                $message .= " ({$matched} otomatis terhubung ke profil karyawan).";
            } else {
                $message .= " Silakan lihat daftar user mesin di bawah untuk menghubungkan PIN ke karyawan HRIS.";
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            return back()->with('error', "Gagal membaca user dari mesin: " . $e->getMessage());
        }
    }

    public function simulateLog(Device $device)
    {
        $result = $this->connector->syncDeviceLogs($device, 'simulator');
        $this->engine->processDailyAttendance(Carbon::today());

        return back()->with('success', "Simulasi log absensi berhasil dijalankan! {$result['records_inserted']} log baru ditambahkan dan dihitung ke rekap hari ini.");
    }

    public function importLog(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
            'device_id' => 'nullable|exists:devices,id',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        try {
            $result = $this->connector->importLogFile($path, $request->input('device_id'));
            $this->engine->processDailyAttendance(Carbon::today());

            return back()->with('success', $result['message']);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function rawLogs(Request $request)
    {
        $query = AttendanceLog::with(['device.branch', 'employee.department', 'employee.branch']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('device_pin', 'like', "%{$search}%")
                  ->orWhereHas('employee', function ($empQ) use ($search) {
                      $empQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%")
                           ->orWhere('employee_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($deviceId = $request->input('device_id')) {
            if ($deviceId === 'gps') {
                $query->where('verify_type', 'gps');
            } elseif ($deviceId === 'visit') {
                $query->where('verify_type', 'visit');
            } elseif ($deviceId === 'biometric') {
                $query->whereNotIn('verify_type', ['gps', 'visit']);
            } else {
                $query->where('device_id', $deviceId);
            }
        }

        if ($date = $request->input('date')) {
            $query->whereDate('log_time', $date);
        }

        $logs = $query->orderBy('log_time', 'desc')->paginate(20)->withQueryString();
        $devices = Device::all();

        return view('devices.logs', compact('logs', 'devices'));
    }

    public function storeMapping(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|exists:devices,id',
            'employee_id' => 'required|exists:employees,id',
            'device_pin' => 'required|string|max:50',
            'privilege' => 'nullable|string|in:user,admin',
        ], [
            'device_id.required' => 'Perangkat mesin wajib dipilih.',
            'employee_id.required' => 'Karyawan HRIS wajib dipilih.',
            'device_pin.required' => 'Nomor PIN mesin wajib diisi.',
        ]);

        try {
            // Delete previous conflicting mapping for this pin on this device if any
            \App\Models\DeviceEmployeeMapping::where('device_id', $validated['device_id'])
                ->where('device_pin', $validated['device_pin'])
                ->where('employee_id', '!=', $validated['employee_id'])
                ->delete();

            \App\Models\DeviceEmployeeMapping::updateOrCreate(
                [
                    'device_id' => $validated['device_id'],
                    'employee_id' => $validated['employee_id'],
                ],
                [
                    'device_pin' => $validated['device_pin'],
                    'privilege' => $validated['privilege'] ?? 'user',
                    'enrolled_at' => now(),
                ]
            );

            // Retroactively link existing raw logs for this PIN to the employee
            AttendanceLog::where('device_id', $validated['device_id'])
                ->where('device_pin', $validated['device_pin'])
                ->whereNull('employee_id')
                ->update(['employee_id' => $validated['employee_id']]);

            // Re-calculate today's attendance summary
            $this->engine->processDailyAttendance(Carbon::today());

            return back()->with('success', 'Pemetaan PIN mesin ke karyawan berhasil disimpan! Log absensi terkait otomatis dihubungkan.');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (str_contains($msg, 'Duplicate entry') || $e->getCode() == 23000) {
                return back()->withInput()->with('error', 'PIN mesin atau Karyawan ini sudah dipetakan pada perangkat tersebut.');
            }
            return back()->withInput()->with('error', 'Gagal menyimpan pemetaan: ' . $msg);
        }
    }

    public function destroyMapping(\App\Models\DeviceEmployeeMapping $mapping)
    {
        $mapping->delete();
        return back()->with('success', 'Pemetaan mesin berhasil dihapus.');
    }
}

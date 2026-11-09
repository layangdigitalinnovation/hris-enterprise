<?php

namespace App\Services\Device;

use App\Models\AttendanceLog;
use App\Models\AttendanceSyncRun;
use App\Models\Device;
use App\Models\DeviceEmployeeMapping;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolutionConnectorService
{
    /**
     * Test connection to a Solution attendance device over TCP/IP LAN.
     */
    public function testConnection(Device $device): array
    {
        if ($device->sync_method === 'mock') {
            return [
                'success' => true,
                'message' => 'Koneksi ke Device Simulator Solution berhasil (Virtual Device Online).',
                'device_info' => [
                    'model' => $device->device_model,
                    'serial_number' => $device->serial_number ?? 'SIM-SOLUTION-9901',
                    'firmware' => 'Ver 8.0.4-Simulated',
                    'user_count' => DeviceEmployeeMapping::where('device_id', $device->id)->count(),
                    'log_count' => AttendanceLog::where('device_id', $device->id)->count(),
                ]
            ];
        }

        if (empty($device->ip_address)) {
            return [
                'success' => false,
                'message' => 'Alamat IP perangkat belum dikonfigurasi.',
            ];
        }

        $ip = $device->ip_address;
        $port = $device->port ?: 4370;
        $timeout = 3; // seconds

        // Socket probe to test network reachability
        $fp = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if (!$fp) {
            $device->update(['status' => 'offline', 'last_error_message' => "Socket error: {$errstr} ({$errno})"]);
            return [
                'success' => false,
                'message' => "Gagal terhubung ke {$ip}:{$port} - {$errstr} (Kode: {$errno})",
            ];
        }

        fclose($fp);

        $extraInfo = '';
        if ($device->sync_method === 'lan_tcp') {
            try {
                $zk = new \Jmrashed\Zkteco\Lib\ZKTeco($ip, $port);
                if ($zk->connect()) {
                    $sn = trim(str_replace('~SerialNumber=', '', $zk->serialNumber()));
                    $users = $zk->getUser();
                    if ($sn && empty($device->serial_number)) {
                        $device->serial_number = $sn;
                    }
                    $extraInfo = " (SN: {$sn}, Terdeteksi " . count($users) . " user di mesin)";
                    $zk->disconnect();
                }
            } catch (\Throwable $e) {
                // Ignore ZK-specific protocol errors if socket was reachable
            }
        }

        $device->update(['status' => 'online', 'last_error_message' => null]);

        return [
            'success' => true,
            'message' => "Berhasil terhubung ke Mesin Solution di {$ip}:{$port}!{$extraInfo}",
        ];
    }

    /**
     * Sync attendance logs from a device into raw attendance_logs.
     */
    public function syncDeviceLogs(Device $device, string $triggerType = 'manual'): array
    {
        $startTime = now();
        $fetched = 0;
        $inserted = 0;

        try {
            if ($device->sync_method === 'mock') {
                $rawLogs = $this->generateMockLogsForDevice($device);
            } else {
                // In production, connect via ZK protocol / SOAP / ADMS connector
                $rawLogs = $this->pullLogsFromHardware($device);
            }

            $fetched = count($rawLogs);

            foreach ($rawLogs as $logItem) {
                $pin = (string) $logItem['pin'];
                $logTime = Carbon::parse($logItem['log_time'])->format('Y-m-d H:i:s');
                $hash = AttendanceLog::generateHash($device->id, $pin, $logTime);

                // Map to employee by device pin
                $mapping = DeviceEmployeeMapping::where('device_id', $device->id)
                    ->where('device_pin', $pin)
                    ->first();

                $employeeId = $mapping ? $mapping->employee_id : null;

                // Deduplicate with unique hash signature
                $existing = AttendanceLog::where('hash_signature', $hash)->exists();
                if (!$existing) {
                    AttendanceLog::create([
                        'device_id' => $device->id,
                        'employee_id' => $employeeId,
                        'device_pin' => $pin,
                        'log_time' => $logTime,
                        'verify_type' => $logItem['verify_type'] ?? 'fingerprint',
                        'log_type' => $logItem['log_type'] ?? 'check_in',
                        'raw_payload' => $logItem,
                        'hash_signature' => $hash,
                        'status' => 'pending',
                    ]);
                    $inserted++;
                }
            }

            $device->update([
                'last_sync_at' => now(),
                'status' => 'online',
                'last_error_message' => null,
            ]);

            AttendanceSyncRun::create([
                'device_id' => $device->id,
                'run_at' => $startTime,
                'trigger_type' => $triggerType,
                'records_fetched' => $fetched,
                'records_inserted' => $inserted,
                'status' => 'success',
            ]);

            return [
                'success' => true,
                'records_fetched' => $fetched,
                'records_inserted' => $inserted,
                'message' => "Sinkronisasi selesai. {$fetched} data didapatkan, {$inserted} data log baru disimpan.",
            ];

        } catch (\Throwable $e) {
            $device->update(['last_error_message' => $e->getMessage()]);

            AttendanceSyncRun::create([
                'device_id' => $device->id,
                'run_at' => $startTime,
                'trigger_type' => $triggerType,
                'records_fetched' => $fetched,
                'records_inserted' => $inserted,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Gagal melakukan sinkronisasi: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Import attendance log from uploaded CSV/TXT or Excel file.
     */
    public function importLogFile(string $filePath, ?int $deviceId = null): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File tidak ditemukan pada path: {$filePath}");
        }

        $inserted = 0;
        $totalRows = 0;
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle, 1000, ',');

        // Expected format: pin, time, [verify_type, log_type]
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $totalRows++;
            if (count($data) < 2) continue;

            $pin = trim($data[0]);
            $timeStr = trim($data[1]);

            try {
                $logTime = Carbon::parse($timeStr)->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                continue;
            }

            $verifyType = isset($data[2]) ? trim($data[2]) : 'fingerprint';
            $logType = isset($data[3]) ? trim($data[3]) : 'check_in';
            $hash = AttendanceLog::generateHash($deviceId, $pin, $logTime);

            $mapping = DeviceEmployeeMapping::where('device_pin', $pin);
            if ($deviceId) {
                $mapping->where('device_id', $deviceId);
            }
            $map = $mapping->first();

            if (!AttendanceLog::where('hash_signature', $hash)->exists()) {
                AttendanceLog::create([
                    'device_id' => $deviceId,
                    'employee_id' => $map ? $map->employee_id : null,
                    'device_pin' => $pin,
                    'log_time' => $logTime,
                    'verify_type' => $verifyType,
                    'log_type' => $logType,
                    'raw_payload' => ['pin' => $pin, 'time' => $logTime, 'source' => 'file_import'],
                    'hash_signature' => $hash,
                    'status' => 'pending',
                ]);
                $inserted++;
            }
        }

        fclose($handle);

        return [
            'total_rows' => $totalRows,
            'inserted' => $inserted,
            'message' => "Impor file selesai. Total baris: {$totalRows}, berhasil dimasukkan: {$inserted}.",
        ];
    }

    /**
     * Generate synthetic attendance logs for testing the Attendance Engine.
     */
    public function generateMockLogsForDevice(Device $device): array
    {
        $mappings = DeviceEmployeeMapping::where('device_id', $device->id)->get();
        if ($mappings->isEmpty()) {
            // Pick all active employees and map if empty
            $employees = Employee::where('status', 'active')->limit(10)->get();
            foreach ($employees as $index => $emp) {
                $pin = (string) (1001 + $index);
                DeviceEmployeeMapping::updateOrCreate(
                    ['device_id' => $device->id, 'employee_id' => $emp->id],
                    ['device_pin' => $pin, 'enrolled_at' => now()]
                );
            }
            $mappings = DeviceEmployeeMapping::where('device_id', $device->id)->get();
        }

        $logs = [];
        $today = Carbon::today();

        foreach ($mappings as $mapping) {
            // Generate morning check-in (between 07:45 - 08:20)
            $randomMinuteIn = rand(45, 80); // 07:45 - 08:20
            $checkInTime = $today->copy()->setTime(7, 0, 0)->addMinutes($randomMinuteIn);

            $logs[] = [
                'pin' => $mapping->device_pin,
                'log_time' => $checkInTime->format('Y-m-d H:i:s'),
                'verify_type' => 'fingerprint',
                'log_type' => 'check_in',
            ];

            // Generate afternoon check-out (between 17:00 - 17:40)
            $randomMinuteOut = rand(0, 40);
            $checkOutTime = $today->copy()->setTime(17, 0, 0)->addMinutes($randomMinuteOut);

            $logs[] = [
                'pin' => $mapping->device_pin,
                'log_time' => $checkOutTime->format('Y-m-d H:i:s'),
                'verify_type' => 'fingerprint',
                'log_type' => 'check_out',
            ];
        }

        return $logs;
    }

    /**
     * Pull attendance logs directly from physical ZK/Solution hardware via socket protocol.
     */
    protected function pullLogsFromHardware(Device $device): array
    {
        if (!extension_loaded('sockets')) {
            throw new Exception("Ekstensi PHP 'sockets' belum aktif di server. Silakan aktifkan extension=sockets pada php.ini dan restart server.");
        }

        $ip = $device->ip_address;
        $port = $device->port ?: 4370;

        $zk = new \Jmrashed\Zkteco\Lib\ZKTeco($ip, $port);
        if (!$zk->connect()) {
            throw new Exception("Gagal membuka komunikasi ZK ke mesin {$ip}:{$port}");
        }

        $attendance = $zk->getAttendance();
        $zk->disconnect();

        $logs = [];
        foreach ($attendance as $record) {
            $pin = (string) ($record['id'] ?? $record['uid'] ?? '');
            if ($pin === '') continue;

            $logs[] = [
                'pin' => $pin,
                'log_time' => $record['timestamp'],
                'verify_type' => 'fingerprint',
                'log_type' => (isset($record['type']) && (int)$record['type'] === 1) ? 'check_out' : 'check_in',
                'raw_payload' => $record,
            ];
        }

        return $logs;
    }

    /**
     * Read users list enrolled directly inside the physical machine.
     */
    public function getHardwareUsers(Device $device): array
    {
        if ($device->sync_method === 'mock') {
            return [];
        }

        if (!extension_loaded('sockets')) {
            throw new Exception("Ekstensi PHP 'sockets' belum aktif di server. Silakan aktifkan extension=sockets pada php.ini dan restart server.");
        }

        $ip = $device->ip_address;
        $port = $device->port ?: 4370;

        $zk = new \Jmrashed\Zkteco\Lib\ZKTeco($ip, $port);
        if (!$zk->connect()) {
            throw new Exception("Gagal membuka koneksi ZK ke mesin {$ip}:{$port}");
        }

        $users = $zk->getUser();
        $zk->disconnect();

        return $users;
    }
}

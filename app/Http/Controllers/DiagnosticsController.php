<?php

namespace App\Http\Controllers;

use App\Services\LicenseService;
use App\Services\InstallationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiagnosticsController extends Controller
{
    public function index()
    {
        $statusInfo = LicenseService::getStatusInfo();
        $license = LicenseService::getActiveLicense();
        $lastVerification = LicenseService::getLastVerification();

        // Database health check
        $dbHealthy = false;
        $dbIntegrity = 'Unknown';
        $dbSize = 'N/A';
        $dbPath = 'N/A';

        try {
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $result = DB::select("PRAGMA integrity_check");
                $dbIntegrity = $result[0]->integrity_check ?? 'Unknown';
                $dbHealthy = ($dbIntegrity === 'ok');

                $dbPathRaw = DB::connection()->getDatabaseName();
                $dbPath = $dbPathRaw;
                if (file_exists($dbPathRaw)) {
                    $dbSize = $this->formatBytes(filesize($dbPathRaw));
                }
            } else {
                $dbHealthy = DB::connection()->getPdo() !== null;
                $dbIntegrity = $dbHealthy ? 'Connected' : 'Failed';
                $dbPath = config("database.connections.{$driver}.database");
            }
        } catch (\Throwable $e) {
            $dbIntegrity = 'Error: ' . $e->getMessage();
        }

        // Last backup info
        $lastBackup = null;
        try {
            $backupFiles = Storage::files('backups');
            if (!empty($backupFiles)) {
                usort($backupFiles, fn($a, $b) => Storage::lastModified($b) - Storage::lastModified($a));
                $lastBackup = date('Y-m-d H:i:s', Storage::lastModified($backupFiles[0]));
            }
        } catch (\Throwable $e) {
            // Ignore
        }

        // Support code (base64 of non-sensitive info)
        $supportData = [
            'inst' => InstallationService::getId(),
            'ver'  => config('license.version', '2.1.0'),
            'lic'  => $statusInfo['license_key'],
            'ed'   => $statusInfo['edition'],
            'st'   => $statusInfo['status'],
            'ts'   => date('Y-m-d H:i:s'),
        ];
        $supportCode = base64_encode(json_encode($supportData));

        $diagnostics = [
            'app_version'        => config('license.version', '2.1.0'),
            'installation_id'    => InstallationService::getId(),
            'license_key'        => $statusInfo['license_key'],
            'edition'            => $statusInfo['edition'],
            'license_status'     => $statusInfo['status'],
            'license_type'       => $license?->license_type ?? 'N/A',
            'business_name'      => $statusInfo['business_name'],
            'expiry_date'        => $statusInfo['expiry_date'],
            'days_remaining'     => $statusInfo['days_remaining'],
            'grace_days'         => $statusInfo['grace_days'],
            'max_terminals'      => LicenseService::maxTerminals(),
            'last_verification'  => $lastVerification['timestamp'] ?? null,
            'verification_stale' => LicenseService::isVerificationStale(),
            'db_engine'          => config('database.default'),
            'db_path'            => $dbPath,
            'db_size'            => $dbSize,
            'db_integrity'       => $dbIntegrity,
            'db_healthy'         => $dbHealthy,
            'last_backup'        => $lastBackup,
            'os'                 => php_uname('s') . ' ' . php_uname('r'),
            'php_version'        => PHP_VERSION,
            'server_time'        => date('Y-m-d H:i:s T'),
            'install_path'       => base_path(),
            'data_path'          => app_data_path(),
            'support_code'       => $supportCode,
        ];

        return view('admin.diagnostics', compact('diagnostics'));
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

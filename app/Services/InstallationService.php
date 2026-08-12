<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Manages a persistent, unique Installation ID for this PharmCare instance.
 *
 * The ID is generated once during first launch and stored in
 * %APPDATA%\PharmCare\install_id.txt so it survives application updates.
 *
 * Format: PHC-INST-XXXXXXXX (8 hex chars derived from a UUIDv4)
 */
class InstallationService
{
    /**
     * Cached ID for this request cycle.
     */
    private static ?string $cachedId = null;

    /**
     * Return the path where the Installation ID is persisted.
     */
    public static function idFilePath(): string
    {
        return app_data_path('install_id.txt');
    }

    /**
     * Ensure an Installation ID exists; generate one if missing.
     * Safe to call multiple times (idempotent).
     */
    public static function ensureId(): string
    {
        $existing = self::getId();
        if ($existing !== null) {
            return $existing;
        }

        return self::generate();
    }

    /**
     * Retrieve the current Installation ID, or null if not yet generated.
     */
    public static function getId(): ?string
    {
        if (self::$cachedId !== null) {
            return self::$cachedId;
        }

        $path = self::idFilePath();
        if (file_exists($path)) {
            $id = trim(file_get_contents($path));
            if ($id !== '' && str_starts_with($id, 'PHC-INST-')) {
                self::$cachedId = $id;
                return $id;
            }
        }

        return null;
    }

    /**
     * Generate a new Installation ID and persist it.
     */
    public static function generate(): string
    {
        $uuid = Str::uuid()->toString();
        // Take the first 8 hex characters (from the UUID without hyphens)
        $hex = strtoupper(substr(str_replace('-', '', $uuid), 0, 8));
        $id = 'PHC-INST-' . $hex;

        $path = self::idFilePath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, $id);
        self::$cachedId = $id;

        Log::info('Installation ID generated', ['id' => $id]);

        return $id;
    }

    /**
     * Collect non-sensitive device information for the activation record.
     * Does NOT include unique hardware serial numbers.
     */
    public static function getDeviceInfo(): array
    {
        return [
            'hostname'      => gethostname() ?: 'unknown',
            'os'            => PHP_OS_FAMILY,
            'os_detail'     => php_uname('s') . ' ' . php_uname('r'),
            'php_version'   => PHP_VERSION,
            'app_version'   => config('license.version', '2.1.0'),
            'install_path'  => base_path(),
            'data_path'     => app_data_path(),
        ];
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\InstallationService;

class UpdateService
{
    /**
     * Cache key for storing update check results.
     */
    protected const CACHE_KEY = 'pharmcare_update_check';

    /**
     * Cache duration in seconds (24 hours).
     */
    protected const CACHE_TTL = 86400;

    /**
     * Cache key for dismissed version.
     */
    protected const DISMISS_KEY = 'pharmcare_update_dismissed';

    /**
     * Check the management portal for available updates.
     * Results are cached for 24 hours to avoid spamming the server.
     *
     * @param bool $forceCheck  Bypass cache and check immediately
     * @return array{available: bool, version: ?string, download_url: ?string, release_notes: ?string, release_date: ?string, current_version: string}
     */
    public static function check(bool $forceCheck = false): array
    {
        $currentVersion = config('license.version', '2.2.0');

        $noUpdate = [
            'available'       => false,
            'version'         => null,
            'download_url'    => null,
            'release_notes'   => null,
            'release_date'    => null,
            'current_version' => $currentVersion,
        ];

        $portalUrl = config('license.portal_url');
        if (!$portalUrl) {
            return $noUpdate;
        }

        // Return cached result unless force-checking
        if (!$forceCheck && Cache::has(self::CACHE_KEY)) {
            return Cache::get(self::CACHE_KEY);
        }

        try {
            $response = Http::timeout(8)
                ->connectTimeout(5)
                ->withHeaders([
                    'X-Installation-ID' => InstallationService::getId(),
                    'X-App-Version'     => $currentVersion,
                    'Accept'            => 'application/json',
                ])
                ->get(rtrim($portalUrl, '/') . '/api/v1/releases/latest');

            if ($response->failed()) {
                Log::debug('Update check: portal returned HTTP ' . $response->status());
                Cache::put(self::CACHE_KEY, $noUpdate, self::CACHE_TTL);
                return $noUpdate;
            }

            $release = $response->json();
            $latestVersion = $release['version'] ?? null;

            if (!$latestVersion) {
                Cache::put(self::CACHE_KEY, $noUpdate, self::CACHE_TTL);
                return $noUpdate;
            }

            $isNewer = version_compare($latestVersion, $currentVersion, '>');

            $result = [
                'available'       => $isNewer,
                'version'         => $latestVersion,
                'download_url'    => $release['download_url'] ?? null,
                'release_notes'   => $release['release_notes'] ?? null,
                'release_date'    => $release['release_date'] ?? null,
                'current_version' => $currentVersion,
            ];

            Cache::put(self::CACHE_KEY, $result, self::CACHE_TTL);

            return $result;
        } catch (\Throwable $e) {
            // Silently fail — offline-first. Don't break the app.
            Log::debug('Update check failed (offline?): ' . $e->getMessage());
            Cache::put(self::CACHE_KEY, $noUpdate, 3600); // Cache failure for 1 hour
            return $noUpdate;
        }
    }

    /**
     * Dismiss update notification for a specific version.
     */
    public static function dismiss(string $version): void
    {
        Cache::put(self::DISMISS_KEY, $version, self::CACHE_TTL * 7); // Dismiss for 7 days
    }

    /**
     * Check if a version has been dismissed.
     */
    public static function isDismissed(string $version): bool
    {
        return Cache::get(self::DISMISS_KEY) === $version;
    }

    /**
     * Clear cached update check (force re-check on next request).
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::DISMISS_KEY);
    }
}

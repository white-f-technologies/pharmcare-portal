<?php

namespace App\Services;

use App\Models\License;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LicenseService
{
    private static ?License $cachedLicense = null;

    // ─── KEY MANAGEMENT ────────────────────────────────────────────────

    /**
     * Check if the Vendor Private Key exists on this machine.
     */
    public static function hasPrivateKey(): bool
    {
        return file_exists(storage_path('keys/private.key'));
    }

    /**
     * Get the public key PEM contents.
     */
    public static function getPublicKeyPem(): ?string
    {
        // Prefer file-based key (deployed with installer)
        $path = storage_path('keys/public.key');
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        // Fall back to config (embedded or env-based)
        $configKey = config('license.public_key');
        if ($configKey) {
            return $configKey;
        }

        // Dev-mode: auto-generate keypair if nothing exists
        self::ensureKeyPair();
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    /**
     * Get the private key PEM contents (Vendor Developer Key).
     */
    public static function getPrivateKeyPem(): ?string
    {
        $path = storage_path('keys/private.key');
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return null;
    }

    /**
     * Ensure RSA 2048 keypair exists in storage/keys/ (Only in developer setup)
     */
    public static function ensureKeyPair(): void
    {
        $dir = storage_path('keys');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $privatePath = storage_path('keys/private.key');
        $publicPath = storage_path('keys/public.key');

        // Only generate new keypair if BOTH keys are missing (fresh development setup)
        if (!file_exists($privatePath) && !file_exists($publicPath)) {
            $opensslCnfPaths = [
                'C:/xampp/php/extras/openssl/openssl.cnf',
                'C:/xampp/apache/conf/openssl.cnf',
                'C:/Program Files/Common Files/SSL/openssl.cnf',
            ];
            $config = [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ];
            foreach ($opensslCnfPaths as $cnf) {
                if (file_exists($cnf)) {
                    $config['config'] = $cnf;
                    break;
                }
            }

            $res = openssl_pkey_new($config);
            if ($res) {
                $privKey = null;
                openssl_pkey_export($res, $privKey, null, $config);
                $pubKeyDetails = openssl_pkey_get_details($res);
                $pubKey = $pubKeyDetails['key'] ?? null;

                if ($privKey && $pubKey) {
                    file_put_contents($privatePath, $privKey);
                    file_put_contents($publicPath, $pubKey);
                }
            }
        }
    }

    // ─── LICENSE GENERATION (VENDOR-SIDE ONLY) ─────────────────────────

    /**
     * Generate and cryptographically sign a new license payload.
     */
    public static function generateLicensePayload(
        string  $businessName,
        ?string $businessId = null,
        string  $edition = 'PREMIUM',
        string  $licenseType = 'PERPETUAL',
        ?string $expiryDate = null,
        array   $activatedModules = [],
        ?int    $maxTerminals = 1,
        ?int    $graceDays = null,
    ): array {
        self::ensureKeyPair();
        $privateKeyPem = self::getPrivateKeyPem();
        if (!$privateKeyPem) {
            throw new \RuntimeException('Private key is unavailable for signing.');
        }

        $licenseId = 'PHC-LIC-' . date('Y') . '-' . str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT);

        $payload = [
            'license_id'            => $licenseId,
            'business_name'         => trim($businessName),
            'business_id'           => $businessId ? trim($businessId) : 'PHC-UG-' . str_pad(random_int(1, 99999), 5, '0', STR_PAD_LEFT),
            'edition'               => strtoupper($edition),
            'license_type'          => strtoupper($licenseType),
            'issue_date'            => date('Y-m-d'),
            'expiry_date'           => $expiryDate ?: null,
            'grace_days'            => $graceDays ?? config('license.grace_days', 7),
            'max_terminals'         => $maxTerminals ?? 1,
            'activated_modules'     => !empty($activatedModules) ? array_values($activatedModules) : config('editions.' . strtoupper($edition), []),
            'installation_identity' => InstallationService::getId() ?? md5(php_uname('n') . gethostname()),
            'license_version'       => '2',
        ];

        // Sort payload keys canonically for deterministic signing
        ksort($payload);
        $canonicalJson = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $privKeyRes = openssl_pkey_get_private($privateKeyPem);
        if (!$privKeyRes) {
            throw new \RuntimeException('Invalid private key structure.');
        }

        $binarySignature = '';
        openssl_sign($canonicalJson, $binarySignature, $privKeyRes, OPENSSL_ALGO_SHA256);
        $payload['signature'] = base64_encode($binarySignature);

        return $payload;
    }

    // ─── SIGNATURE VERIFICATION ────────────────────────────────────────

    /**
     * Verify digital signature of a license payload.
     */
    public static function verifySignature(array $payload): bool
    {
        if (empty($payload['signature'])) {
            return false;
        }

        $publicKeyPem = self::getPublicKeyPem();
        if (!$publicKeyPem) {
            Log::warning('License verification failed: Public key missing.');
            return false;
        }

        $signatureBytes = base64_decode($payload['signature']);
        if (!$signatureBytes) {
            return false;
        }

        // Canonicalize: remove signature, sort, encode
        $dataToVerify = $payload;
        unset($dataToVerify['signature']);
        ksort($dataToVerify);
        $canonicalStr = json_encode($dataToVerify, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $pubKeyRes = openssl_pkey_get_public($publicKeyPem);
        if (!$pubKeyRes) {
            return false;
        }

        $result = openssl_verify($canonicalStr, $signatureBytes, $pubKeyRes, OPENSSL_ALGO_SHA256);
        return $result === 1;
    }

    // ─── ACTIVATION ────────────────────────────────────────────────────

    /**
     * Import and activate a license file content or array.
     */
    public static function activateLicense(string|array $licenseData): array
    {
        try {
            $payload = is_string($licenseData) ? json_decode($licenseData, true) : $licenseData;
            if (!$payload || !is_array($payload)) {
                return ['success' => false, 'error' => 'Invalid license JSON format.'];
            }

            if (!self::verifySignature($payload)) {
                return ['success' => false, 'error' => 'Digital signature verification failed. License file may be tampered with or invalid.'];
            }

            // Expiry check (allow activation during grace period)
            $graceDays = $payload['grace_days'] ?? config('license.grace_days', 7);
            if (!empty($payload['expiry_date'])) {
                $expiryTimestamp = strtotime($payload['expiry_date']);
                $graceEnd = strtotime("+{$graceDays} days", $expiryTimestamp);
                if ($graceEnd < time()) {
                    return ['success' => false, 'error' => 'License has expired on ' . $payload['expiry_date'] . ' and the grace period has ended.'];
                }
            }

            // Ensure installation ID exists
            $installId = InstallationService::ensureId();

            // Save or update license in database
            $license = License::updateOrCreate(
                ['license_key' => $payload['license_id'] ?? ('LIC-' . time())],
                [
                    'business_name'         => $payload['business_name'] ?? 'PharmCare Shop',
                    'business_id'           => $payload['business_id'] ?? null,
                    'edition'               => strtoupper($payload['edition'] ?? 'DEFAULT'),
                    'activated_modules'     => $payload['activated_modules'] ?? [],
                    'issue_date'            => $payload['issue_date'] ?? date('Y-m-d'),
                    'expiry_date'           => $payload['expiry_date'] ?? null,
                    'license_type'          => strtoupper($payload['license_type'] ?? 'PERPETUAL'),
                    'installation_identity' => $installId,
                    'status'                => 'ACTIVE',
                    'signature'             => $payload['signature'],
                    'raw_payload'           => json_encode($payload),
                ]
            );

            // Persist active license JSON locally for offline reads
            $localPayload = $payload;
            $localPayload['_activated_at']      = date('Y-m-d H:i:s');
            $localPayload['_installation_id']   = $installId;
            $localPayload['_app_version']        = config('license.version', '2.2.0');
            Storage::put('license.json', json_encode($localPayload, JSON_PRETTY_PRINT));

            // Record verification timestamp
            self::recordVerification('activation');

            self::$cachedLicense = $license;

            // Audit log
            try {
                ActivityLog::log('license_activated', "License {$license->license_key} activated. Edition: {$license->edition}");
            } catch (\Throwable $e) {
                // Don't fail activation due to logging
            }

            return [
                'success' => true,
                'license' => $license,
                'message' => 'License activated successfully! Active Edition: ' . $license->edition,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'License activation error: ' . $e->getMessage()];
        }
    }

    // ─── LICENSE STATE QUERIES ──────────────────────────────────────────

    /**
     * Retrieve the current active license or fallback to default.
     */
    public static function getActiveLicense(): ?License
    {
        if (self::$cachedLicense !== null) {
            return self::$cachedLicense;
        }

        $license = null;
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('licenses')) {
                $license = License::where('status', 'ACTIVE')->latest()->first();
            }
        } catch (\Throwable $e) {
            $license = null;
        }

        // If database record is missing, check storage/app/license.json
        if (!$license && Storage::exists('license.json')) {
            $json = Storage::get('license.json');
            $res = self::activateLicense($json);
            if (!empty($res['success'])) {
                $license = $res['license'];
            }
        }

        self::$cachedLicense = $license;
        return $license;
    }

    /**
     * Get active application edition ('DEFAULT' or 'PREMIUM').
     */
    public static function getEdition(): string
    {
        $license = self::getActiveLicense();
        if ($license && $license->edition) {
            $status = self::getLicenseStatus($license);
            if ($status === 'EXPIRED') {
                return 'DEFAULT'; // Downgrade to default after grace
            }
            return strtoupper($license->edition);
        }
        return 'DEFAULT';
    }

    /**
     * Check if active edition is PREMIUM.
     */
    public static function isPremium(): bool
    {
        return self::getEdition() === 'PREMIUM';
    }

    /**
     * Get the detailed status of a license.
     *
     * Returns one of: ACTIVE, GRACE, EXPIRED, SUSPENDED, REVOKED, PENDING, DEACTIVATED
     */
    public static function getLicenseStatus(?License $license = null): string
    {
        $license = $license ?? self::getActiveLicense();

        if (!$license) {
            return 'PENDING'; // No license at all
        }

        if (in_array($license->status, ['SUSPENDED', 'REVOKED', 'DEACTIVATED'])) {
            return $license->status;
        }

        // Perpetual licenses never expire
        if (!$license->expiry_date) {
            return 'ACTIVE';
        }

        $now = time();
        $expiry = strtotime($license->expiry_date);
        $graceDays = config('license.grace_days', 7);

        // Try to get grace_days from the payload
        if ($license->raw_payload) {
            $payload = json_decode($license->raw_payload, true);
            if (isset($payload['grace_days'])) {
                $graceDays = (int) $payload['grace_days'];
            }
        }

        $graceEnd = strtotime("+{$graceDays} days", $expiry);

        if ($now <= $expiry) {
            return 'ACTIVE';
        }

        if ($now <= $graceEnd) {
            return 'GRACE';
        }

        return 'EXPIRED';
    }

    /**
     * Get the number of days remaining on the license (negative = overdue).
     */
    public static function daysRemaining(?License $license = null): ?int
    {
        $license = $license ?? self::getActiveLicense();
        if (!$license || !$license->expiry_date) {
            return null; // Perpetual
        }

        $diff = strtotime($license->expiry_date) - time();
        return (int) ceil($diff / 86400);
    }

    /**
     * Get human-readable status info for display.
     */
    public static function getStatusInfo(): array
    {
        $license = self::getActiveLicense();
        $status = self::getLicenseStatus($license);
        $daysLeft = self::daysRemaining($license);
        $graceDays = config('license.grace_days', 7);

        $info = [
            'status'           => $status,
            'edition'          => self::getEdition(),
            'is_premium'       => self::isPremium(),
            'license_key'      => $license?->license_key ?? 'UNLICENSED',
            'business_name'    => $license?->business_name ?? 'PharmCare',
            'expiry_date'      => $license?->expiry_date?->format('Y-m-d'),
            'days_remaining'   => $daysLeft,
            'grace_days'       => $graceDays,
            'is_perpetual'     => $license && !$license->expiry_date,
            'installation_id'  => InstallationService::getId(),
            'app_version'      => config('license.version', '2.2.0'),
            'show_warning'     => false,
            'warning_message'  => null,
        ];

        if ($status === 'GRACE') {
            $graceRemaining = $graceDays + ($daysLeft ?? 0);
            $info['show_warning'] = true;
            $info['warning_message'] = "Your license expired. You have {$graceRemaining} day(s) remaining in the grace period. Please renew to avoid service interruption.";
        } elseif ($status === 'EXPIRED') {
            $info['show_warning'] = true;
            $info['warning_message'] = 'Your license has expired and the grace period has ended. Premium features are restricted. Please contact your vendor to renew.';
        } elseif ($status === 'ACTIVE' && $daysLeft !== null && $daysLeft <= 14) {
            $info['show_warning'] = true;
            $info['warning_message'] = "Your license will expire in {$daysLeft} day(s). Please arrange renewal.";
        }

        return $info;
    }

    // ─── FEATURE ENTITLEMENT ───────────────────────────────────────────

    /**
     * Check if a specific feature module is enabled.
     * Consults config/editions.php merged with per-license overrides.
     */
    public static function isModuleEnabled(string $module): bool
    {
        $edition = self::getEdition();

        // Get edition defaults from config/editions.php
        $editionModules = config('editions.' . $edition, []);

        // If the module is in the edition defaults, it's enabled
        if (in_array($module, $editionModules)) {
            return true;
        }

        // Also check per-license activated_modules overrides
        $license = self::getActiveLicense();
        if ($license && !empty($license->activated_modules) && is_array($license->activated_modules)) {
            return in_array($module, $license->activated_modules);
        }

        return false;
    }

    /**
     * Check if the current license permits a specific feature.
     * Alias for isModuleEnabled for cleaner API.
     */
    public static function hasFeature(string $feature): bool
    {
        return self::isModuleEnabled($feature);
    }

    /**
     * Get maximum terminals allowed by the current license.
     */
    public static function maxTerminals(): int
    {
        $license = self::getActiveLicense();
        if (!$license || !$license->raw_payload) {
            return 1;
        }

        $payload = json_decode($license->raw_payload, true);
        return (int) ($payload['max_terminals'] ?? 1);
    }

    // ─── OFFLINE VERIFICATION ──────────────────────────────────────────

    /**
     * Record a successful license verification timestamp.
     */
    public static function recordVerification(string $type = 'online'): void
    {
        $data = [
            'type'       => $type,
            'timestamp'  => date('Y-m-d H:i:s'),
            'unix'       => time(),
            'app_version' => config('license.version', '2.2.0'),
        ];

        $verificationPath = app_data_path('license_verification.json');
        file_put_contents($verificationPath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Get last verification info.
     */
    public static function getLastVerification(): ?array
    {
        $path = app_data_path('license_verification.json');
        if (!file_exists($path)) {
            return null;
        }

        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : null;
    }

    /**
     * Check if the offline verification window has expired.
     * Returns true if online re-verification is recommended.
     */
    public static function isVerificationStale(): bool
    {
        $last = self::getLastVerification();
        if (!$last || empty($last['unix'])) {
            return true;
        }

        $windowDays = config('license.offline_window_days', 30);
        $elapsed = time() - (int) $last['unix'];
        return $elapsed > ($windowDays * 86400);
    }

    /**
     * Detect potential system clock manipulation.
     * Returns true if the clock appears to have been rolled back.
     */
    public static function detectClockRollback(): bool
    {
        $last = self::getLastVerification();
        if (!$last || empty($last['unix'])) {
            return false;
        }

        // If current time is more than 24 hours behind last verification, suspicious
        return time() < ((int) $last['unix'] - 86400);
    }

    // ─── CLEAR CACHE ───────────────────────────────────────────────────

    /**
     * Clear the in-memory license cache (useful after changes).
     */
    public static function clearCache(): void
    {
        self::$cachedLicense = null;
    }
}

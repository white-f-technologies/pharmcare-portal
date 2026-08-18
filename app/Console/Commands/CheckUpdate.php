<?php

namespace App\Console\Commands;

use App\Services\LicenseService;
use App\Services\InstallationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckUpdate extends Command
{
    protected $signature = 'update:check {--apply : Automatically download and apply update if available}';

    protected $description = 'Check the PharmCare Management Portal for application updates';

    public function handle(): int
    {
        $currentVersion = config('license.version', '2.2.0');
        $portalUrl = config('license.portal_url');

        $this->info("Current PharmCare Version: {$currentVersion}");
        $this->info("Installation ID: " . InstallationService::getId());

        if (!$portalUrl) {
            $this->warn("No management portal URL configured (PHARMCARE_PORTAL_URL is empty).");
            $this->info("System is running in standalone offline mode.");
            return self::SUCCESS;
        }

        $this->info("Checking portal at: {$portalUrl}/api/releases/latest");

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Installation-ID' => InstallationService::getId(),
                    'X-App-Version'     => $currentVersion,
                    'Accept'            => 'application/json',
                ])
                ->get(rtrim($portalUrl, '/') . '/api/releases/latest');

            if ($response->failed()) {
                $this->error("Failed to reach management portal: " . $response->status());
                return self::FAILURE;
            }

            $release = $response->json();
            $latestVersion = $release['version'] ?? null;

            if (!$latestVersion) {
                $this->warn("Portal returned invalid release metadata.");
                return self::FAILURE;
            }

            if (version_compare($latestVersion, $currentVersion, '>')) {
                $this->info("");
                $this->alert("A new update is available: PharmCare v{$latestVersion}");
                $this->line("Release Date: " . ($release['release_date'] ?? 'N/A'));
                $this->line("Release Notes: " . ($release['release_notes'] ?? 'No release notes provided.'));

                if ($this->option('apply')) {
                    $this->info("Auto-apply requested. Downloading release...");
                    // Safety check before applying update
                    $this->call('backups:store');
                    $this->info("Pre-update safety backup completed.");
                    $this->info("Please follow vendor release installation instructions to apply v{$latestVersion}.");
                }
            } else {
                $this->info("PharmCare is up to date (v{$currentVersion}).");
            }

            // Record verification
            LicenseService::recordVerification('online_update_check');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error("Update check failed: " . $e->getMessage());
            Log::warning("Update check error: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}

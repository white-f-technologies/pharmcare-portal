<?php

namespace App\Console\Commands;

use App\Services\SetupService;
use Illuminate\Console\Command;

class BootstrapApp extends Command
{
    protected $signature = 'app:bootstrap';

    protected $description = 'Bootstrap the application on first run (creates .env, key, database, admin)';

    public function handle(SetupService $setup): int
    {
        if ($setup->isInstalled()) {
            $this->info('Application already bootstrapped. Skipping setup.');
            return self::SUCCESS;
        }

        $this->info('First-time bootstrap starting...');

        try {
            $setup->bootstrap();
        } catch (\Exception $e) {
            $this->error('Bootstrap failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        $creds = $setup->getBootstrapAdminCredentials();

        $this->info('');
        $this->info('=== Bootstrap Complete ===');
        $this->info('A temporary admin account has been created:');
        $this->info('  Email:    ' . $creds['email']);
        $this->info('  Password: ' . $creds['password']);
        $this->info('');
        $this->warn('The setup wizard will guide you through creating your real');
        $this->warn('business profile and admin account after first login.');

        return self::SUCCESS;
    }
}

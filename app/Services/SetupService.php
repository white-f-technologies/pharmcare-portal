<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class SetupService
{
    protected string $dataDir;
    protected string $envPath;
    protected string $markerFile;

    public function __construct()
    {
        $this->dataDir = app_data_path();
        $this->envPath = $this->dataDir . DIRECTORY_SEPARATOR . '.env';
        $this->markerFile = $this->dataDir . DIRECTORY_SEPARATOR . '.setup_complete';
    }

    public function isInstalled(): bool
    {
        return file_exists($this->markerFile);
    }

    public function isDatabaseReady(): bool
    {
        $dbPath = $this->dataDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
        return file_exists($dbPath) && filesize($dbPath) > 0;
    }

    public function bootstrap(): void
    {
        $this->ensureDataDirectories();
        $this->createEnvFile();
        $this->generateAppKey();
        $this->runMigrations();
        $this->seedSystemData();
        $this->createBootstrapAdmin();
    }

    protected function ensureDataDirectories(): void
    {
        $dirs = [
            $this->dataDir,
            $this->dataDir . DIRECTORY_SEPARATOR . 'database',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'settings',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'medicines',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'expenses',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'sessions',
            $this->dataDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views',
        ];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        $dbFile = $this->dataDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
        if (!file_exists($dbFile)) {
            touch($dbFile);
        }
    }

    protected function createEnvFile(): void
    {
        if (file_exists($this->envPath)) {
            return;
        }

        $examplePath = base_path('.env.example');
        if (!file_exists($examplePath)) {
            throw new \RuntimeException('.env.example not found at: ' . $examplePath);
        }

        $envContent = file_get_contents($examplePath);

        $sqlitePath = str_replace('\\', '/', $this->dataDir . '/database/database.sqlite');
        $envContent .= "\nDB_DATABASE=\"{$sqlitePath}\"\n";

        file_put_contents($this->envPath, $envContent);
    }

    public function generateAppKey(): void
    {
        if (!file_exists($this->envPath)) {
            return;
        }

        $envContent = file_get_contents($this->envPath);
        if (preg_match('/^APP_KEY=.+$/m', $envContent)) {
            return;
        }

        Artisan::call('key:generate', ['--force' => true]);
    }

    public function runMigrations(): void
    {
        $dbFile = $this->dataDir . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'database.sqlite';
        if (!file_exists($dbFile)) {
            $dir = dirname($dbFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            touch($dbFile);
        }

        Artisan::call('migrate', ['--force' => true]);
    }

    protected function seedSystemData(): void
    {
        $tablesToCheck = ['settings', 'categories', 'expense_categories'];
        foreach ($tablesToCheck as $table) {
            if (\Illuminate\Support\Facades\Schema::hasTable($table)) {
                if (\Illuminate\Support\Facades\DB::table($table)->count() > 0) {
                    return;
                }
            }
        }

        $now = now();

        $settings = [
            ['key' => 'app_name', 'value' => 'PharmCare'],
            ['key' => 'currency_symbol', 'value' => '$'],
            ['key' => 'system_email', 'value' => ''],
            ['key' => 'system_phone', 'value' => ''],
            ['key' => 'system_address', 'value' => ''],
        ];

        foreach ($settings as $s) {
            Setting::set($s['key'], $s['value']);
        }

        $categories = [
            ['name' => 'Painkillers & Analgesics', 'slug' => 'painkillers', 'description' => 'Medications for pain relief and fever reduction'],
            ['name' => 'Antibiotics & Antimicrobials', 'slug' => 'antibiotics', 'description' => 'Medications for bacterial infections'],
            ['name' => 'Vitamins & Minerals', 'slug' => 'vitamins-supplements', 'description' => 'Nutritional supplements and immune boosters'],
            ['name' => 'Cough, Cold & Flu', 'slug' => 'cough-cold', 'description' => 'Decongestants and cough syrups'],
            ['name' => 'Allergy & Antihistamines', 'slug' => 'allergy', 'description' => 'Antihistamines and allergy relief'],
            ['name' => 'Diabetes & Endocrine', 'slug' => 'diabetes', 'description' => 'Blood sugar management medications'],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::create($cat);
        }

        $expenseCategories = [
            ['name' => 'Rent', 'description' => 'Premises monthly rent'],
            ['name' => 'Utilities', 'description' => 'Utility bills (water, electricity)'],
            ['name' => 'Salaries & Wages', 'description' => 'Staff salaries and allowances'],
            ['name' => 'Transport & Logistics', 'description' => 'Delivery and transport costs'],
            ['name' => 'Maintenance & Repairs', 'description' => 'Equipment and premises repairs'],
            ['name' => 'Other Expenses', 'description' => 'Miscellaneous operational expenses'],
        ];

        foreach ($expenseCategories as $ec) {
            \App\Models\ExpenseCategory::create($ec);
        }

        \App\Models\Customer::create([
            'name' => 'Walk-in Customer',
            'email' => null,
            'phone' => '',
            'address' => '',
            'is_active' => true,
        ]);
    }

    protected function createBootstrapAdmin(): void
    {
        if (User::count() > 0) {
            return;
        }

        User::create([
            'name' => 'Administrator',
            'email' => 'admin@pharmcare.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'phone' => '',
            'is_active' => true,
        ]);
    }

    public function completeSetup(): void
    {
        file_put_contents($this->markerFile, date('Y-m-d H:i:s'));
    }

    public function getBootstrapAdminCredentials(): array
    {
        return [
            'email' => 'admin@pharmcare.local',
            'password' => 'admin123',
        ];
    }
}

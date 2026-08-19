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
        $this->clearCache();
    }

    public function ensureDataDirectories(): void
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
        $now = now();

        // 1. Settings Seeder
        $hasSettings = false;
        if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
            $hasSettings = \Illuminate\Support\Facades\DB::table('settings')->count() > 0;
        }
        if (!$hasSettings) {
            $settings = [
                ['key' => 'app_name', 'value' => 'PharmCare'],
                ['key' => 'system_name', 'value' => 'PharmCare Pharmacy'],
                ['key' => 'currency_symbol', 'value' => 'UGX'],
                ['key' => 'currency', 'value' => 'UGX'],
                ['key' => 'system_email', 'value' => 'info@pharmcare.ug'],
                ['key' => 'system_phone', 'value' => '+256 700 000 000'],
                ['key' => 'system_address', 'value' => 'Kampala, Uganda'],
            ];

            foreach ($settings as $s) {
                Setting::set($s['key'], $s['value']);
            }
        }

        // 2. Categories Seeder
        $hasCategories = false;
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $hasCategories = \Illuminate\Support\Facades\DB::table('categories')->count() > 0;
        }
        if (!$hasCategories) {
            // Seed complete pharmaceutical categories
            (new \Database\Seeders\CategorySeeder())->run();
        }

        // 3. Suppliers Seeder
        $hasSuppliers = false;
        if (\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
            $hasSuppliers = \Illuminate\Support\Facades\DB::table('suppliers')->count() > 0;
        }
        if (!$hasSuppliers) {
            // Seed default wholesale suppliers
            (new \Database\Seeders\SupplierSeeder())->run();
        }

        // 4. Medicines Seeder
        $hasMedicines = false;
        if (\Illuminate\Support\Facades\Schema::hasTable('medicines')) {
            $hasMedicines = \Illuminate\Support\Facades\DB::table('medicines')->count() > 0;
        }
        if (!$hasMedicines) {
            // Seed popular Ugandan medicines, packaging units (box, strip, etc.), and active batches
            (new \Database\Seeders\MedicineSeeder())->run();
        }

        // 5. Expense Categories Seeder
        $hasExpenseCategories = false;
        if (\Illuminate\Support\Facades\Schema::hasTable('expense_categories')) {
            $hasExpenseCategories = \Illuminate\Support\Facades\DB::table('expense_categories')->count() > 0;
        }
        if (!$hasExpenseCategories) {
            $expenseCategories = [
                ['name' => 'Rent', 'description' => 'Premises monthly pharmacy rent'],
                ['name' => 'Utilities', 'description' => 'Utility bills (water, electricity, internet)'],
                ['name' => 'Salaries & Wages', 'description' => 'Staff salaries and allowances'],
                ['name' => 'Transport & Logistics', 'description' => 'Delivery and transport costs'],
                ['name' => 'NDA & Statutory Licensing', 'description' => 'National Drug Authority and local council trading licenses'],
                ['name' => 'Medical Waste & Cleaning', 'description' => 'Clinical waste management and sanitation supplies'],
                ['name' => 'Maintenance & Repairs', 'description' => 'Equipment, cold-chain refrigeration, and premises repairs'],
                ['name' => 'Other Expenses', 'description' => 'Miscellaneous operational expenses and packaging'],
            ];

            foreach ($expenseCategories as $ec) {
                \App\Models\ExpenseCategory::firstOrCreate(['name' => $ec['name']], $ec);
            }
        }

        // 6. Default Customer Seeder
        $hasCustomer = false;
        if (\Illuminate\Support\Facades\Schema::hasTable('customers')) {
            $hasCustomer = \Illuminate\Support\Facades\DB::table('customers')->count() > 0;
        }
        if (!$hasCustomer) {
            \App\Models\Customer::firstOrCreate(
                ['name' => 'Walk-in Customer'],
                [
                    'email' => null,
                    'phone' => '+256 700 000 000',
                    'address' => 'Kampala, Uganda',
                    'is_active' => true,
                ]
            );
        }
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

    public function clearCache(): void
    {
        try {
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('cache:clear');
        } catch (\Throwable $e) {
            // Ignore cache clear errors during bootstrap if warming up
        }
    }
}

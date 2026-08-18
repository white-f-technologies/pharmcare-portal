<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Reference Data
        $this->call(CategorySeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(MedicineSeeder::class);
        $this->call(UsersSeeder::class);

        // 2. Default System Settings
        $defaultSettings = [
            ['key' => 'app_name', 'value' => 'PharmCare'],
            ['key' => 'system_name', 'value' => 'PharmCare Pharmacy'],
            ['key' => 'currency_symbol', 'value' => 'UGX'],
            ['key' => 'currency', 'value' => 'UGX'],
            ['key' => 'system_email', 'value' => 'info@pharmcare.ug'],
            ['key' => 'contact_email', 'value' => 'info@pharmcare.ug'],
            ['key' => 'system_phone', 'value' => '+256 700 000 000'],
            ['key' => 'contact_phone', 'value' => '+256 700 000 000'],
            ['key' => 'system_address', 'value' => 'Kampala, Uganda'],
            ['key' => 'address', 'value' => 'Kampala, Uganda'],
        ];

        foreach ($defaultSettings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 3. Default Walk-in Customer
        Customer::firstOrCreate(
            ['name' => 'Walk-in Customer'],
            [
                'email' => null,
                'phone' => '+256 700 000 000',
                'address' => 'Kampala, Uganda',
                'is_active' => true,
            ]
        );

        // 4. Default Pharmacy Operational Expense Categories
        $expenseCategories = [
            ['name' => 'Rent', 'description' => 'Premises monthly pharmacy rent'],
            ['name' => 'Utilities', 'description' => 'Utility bills (water, electricity, internet, backup solar/generator fuel)'],
            ['name' => 'Salaries & Wages', 'description' => 'Staff salaries, pharmacist fees, and dispenser allowances'],
            ['name' => 'Transport & Logistics', 'description' => 'Medicine procurement delivery and customer transport costs'],
            ['name' => 'NDA & Statutory Licensing', 'description' => 'National Drug Authority (NDA), municipal trading licenses, and regulatory fees'],
            ['name' => 'Medical Waste & Cleaning', 'description' => 'Clinical waste disposal, biohazard collection, and sanitation supplies'],
            ['name' => 'Maintenance & Repairs', 'description' => 'Equipment, cold-chain refrigeration, and premises repairs'],
            ['name' => 'Other Expenses', 'description' => 'Miscellaneous operational expenses and packaging bags'],
        ];

        foreach ($expenseCategories as $ec) {
            ExpenseCategory::firstOrCreate(['name' => $ec['name']], $ec);
        }

        $this->command->info('PharmCare Ugandan reference data, popular medicines, categories, and settings seeded successfully!');
    }
}

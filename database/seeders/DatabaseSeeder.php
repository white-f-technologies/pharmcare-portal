<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Painkillers & Analgesics', 'slug' => 'painkillers', 'description' => 'Medications for pain relief and fever reduction'],
            ['name' => 'Antibiotics & Antimicrobials', 'slug' => 'antibiotics', 'description' => 'Medications for bacterial infections'],
            ['name' => 'Vitamins & Minerals', 'slug' => 'vitamins-supplements', 'description' => 'Nutritional supplements and immune boosters'],
            ['name' => 'Cough, Cold & Flu', 'slug' => 'cough-cold', 'description' => 'Decongestants and cough syrups'],
            ['name' => 'Allergy & Antihistamines', 'slug' => 'allergy', 'description' => 'Antihistamines and allergy relief'],
            ['name' => 'Diabetes & Endocrine', 'slug' => 'diabetes', 'description' => 'Blood sugar management medications'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
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
            ExpenseCategory::create($ec);
        }

        $this->command->info('System reference data seeded successfully!');
    }
}

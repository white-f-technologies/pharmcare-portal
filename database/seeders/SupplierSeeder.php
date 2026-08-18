<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds for default pharmaceutical suppliers.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'National Medical Stores (NMS)',
                'company' => 'National Medical Stores',
                'phone' => '+256 414 320 089',
                'email' => 'sales@nms.go.ug',
                'address' => 'Plot 4-12 Nsamizi Road, Entebbe',
                'is_active' => true,
            ],
            [
                'name' => 'Joint Medical Store (JMS)',
                'company' => 'Joint Medical Store',
                'phone' => '+256 414 510 016',
                'email' => 'store@jms.co.ug',
                'address' => 'Plot 1828 Ggaba Road, Nsambya, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Abacus Pharma (A) Ltd',
                'company' => 'Abacus Pharma Group',
                'phone' => '+256 312 265 140',
                'email' => 'info@abacuspharma.com',
                'address' => 'Plot 28B-32B, Kibira Road, Industrial Area, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Cipla Quality Chemical Industries Ltd',
                'company' => 'Cipla QCIL',
                'phone' => '+256 312 341 100',
                'email' => 'orders@qcil.co.ug',
                'address' => 'Plot 1-7, 1st Ring Road, Luzira Industrial Park, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'AstraPharma Wholesale Ltd',
                'company' => 'AstraPharma Uganda',
                'phone' => '+256 414 250 500',
                'email' => 'wholesale@astrapharma.ug',
                'address' => 'Plot 12 Nakivubo Place, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Rene Industries Ltd',
                'company' => 'Rene Pharmaceuticals',
                'phone' => '+256 414 254 360',
                'email' => 'info@rene.co.ug',
                'address' => 'Plot 31-33, Jinja Road, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Kampala Pharmaceutical Industries Ltd (KPI)',
                'company' => 'KPI Ltd',
                'phone' => '+256 414 285 645',
                'email' => 'sales@kpi.co.ug',
                'address' => 'Plot 10, Port Bell Road, Luzira, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Surgipharm (U) Ltd',
                'company' => 'Surgipharm Uganda',
                'phone' => '+256 414 348 300',
                'email' => 'orders@surgipharm.ug',
                'address' => 'Plot 2 Kibira Road, Industrial Area, Kampala',
                'is_active' => true,
            ],
            [
                'name' => 'Generic Pharma Importers Ltd',
                'company' => 'Generic Pharma Imports',
                'phone' => '+256 414 340 220',
                'email' => 'support@genericpharma.ug',
                'address' => 'Commercial Street, Central Division, Kampala',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $sup) {
            Supplier::firstOrCreate(
                ['name' => $sup['name']],
                $sup
            );
        }
    }
}

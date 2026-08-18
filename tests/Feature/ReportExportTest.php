<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Medicine;
use App\Models\Batch;
use App\Models\Category;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $payload = LicenseService::generateLicensePayload('Test Pharmacy', 'TC-001', 'PREMIUM', 'PERPETUAL');
        LicenseService::activateLicense($payload);
    }

    public function test_inventory_excel_export_returns_200_and_neat_html_spreadsheet(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cat = Category::create(['name' => 'Antibiotics', 'slug' => 'antibiotics']);
        $med = Medicine::create([
            'name' => 'Amoxicillin 500mg',
            'generic_name' => 'Amoxicillin',
            'category_id' => $cat->id,
            'reorder_level' => 10,
            'selling_price' => 5000,
            'purchase_price' => 3000,
            'is_active' => true,
        ]);
        Batch::create([
            'medicine_id' => $med->id,
            'batch_number' => 'BT-TEST-001',
            'quantity' => 100,
            'purchase_price' => 3000,
            'selling_price' => 5000,
            'expiry_date' => now()->addYear(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/reports/inventory?export=excel');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Inventory Valuation', $response->getContent());
        $this->assertStringContainsString('Amoxicillin 500mg', $response->getContent());
    }

    public function test_sales_excel_export_returns_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/reports/sales?export=excel');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Sales Revenue', $response->getContent());
    }

    public function test_expiry_excel_export_returns_200(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/reports/expiry?export=excel');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=utf-8');
        $this->assertStringContainsString('Medicine Expiration', $response->getContent());
    }
}

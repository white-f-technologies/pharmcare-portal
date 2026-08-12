<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineUnit;
use App\Models\Sale;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use App\Services\LicenseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpgradeSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::firstOrCreate(
            ['email' => 'admin_test@pharmcare.test'],
            ['name' => 'Admin Test', 'password' => bcrypt('password'), 'role' => 'admin', 'is_active' => true]
        );
        $this->actingAs($this->user);
    }

    #[Test]
    public function medicine_unit_conversions()
    {
        $category = Category::firstOrCreate(['slug' => 'painkillers-test'], ['name' => 'Painkillers Test']);
        
        $medicine = Medicine::create([
            'name' => 'Cetirizine Test',
            'generic_name' => 'Cetirizine HCl',
            'category_id' => $category->id,
            'base_unit' => 'Tablet',
            'reorder_level' => 10,
            'requires_prescription' => false,
            'is_active' => true,
        ]);

        $medicine->units()->create([
            'unit_name' => 'Strip',
            'conversion_factor' => 10,
            'selling_price' => 5000,
        ]);

        $medicine->units()->create([
            'unit_name' => 'Box',
            'conversion_factor' => 100,
            'selling_price' => 45000,
        ]);

        $medicine->load('units');

        $this->assertEquals(1.0, $medicine->getUnitConversionFactor('Tablet'));
        $this->assertEquals(10.0, $medicine->getUnitConversionFactor('Strip'));
        $this->assertEquals(100.0, $medicine->getUnitConversionFactor('Box'));
    }

    #[Test]
    public function real_world_stock_deduction_and_ledger()
    {
        $category = Category::firstOrCreate(['slug' => 'general-test'], ['name' => 'General Test']);
        $supplier = Supplier::firstOrCreate(['name' => 'Test Supplier Co'], ['phone' => '0700000000']);

        $medicine = Medicine::create([
            'name' => 'Cetirizine RealWorld Test',
            'generic_name' => 'Cetirizine HCl',
            'category_id' => $category->id,
            'base_unit' => 'Tablet',
            'reorder_level' => 50,
            'requires_prescription' => false,
            'is_active' => true,
        ]);

        $medicine->units()->create(['unit_name' => 'Strip', 'conversion_factor' => 10, 'selling_price' => 1500]);
        $medicine->units()->create(['unit_name' => 'Box', 'conversion_factor' => 100, 'selling_price' => 14000]);

        $batch = Batch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'CTZ-RW-3000',
            'supplier_id' => $supplier->id,
            'expiry_date' => now()->addYear(),
            'purchase_price' => 100,
            'selling_price' => 150,
            'quantity' => 3000, // 3000 tablets
            'is_active' => true,
        ]);

        $customer = Customer::firstOrCreate(['name' => 'Walk-in Customer'], ['phone' => '0700000001']);

        // 1. Sell 1 Tablet
        $response1 = $this->postJson(route('sales.store'), [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'subtotal' => 150,
            'discount' => 0,
            'tax' => 0,
            'total' => 150,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'unit_name' => 'Tablet',
                    'unit_quantity' => 1,
                    'quantity' => 1,
                    'unit_price' => 150,
                ]
            ]
        ]);
        $response1->assertStatus(200);
        $batch->refresh();
        $this->assertEquals(2999, $batch->quantity); // 3000 - 1 = 2999

        // 2. Sell 1 Strip (10 Tablets)
        $response2 = $this->postJson(route('sales.store'), [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'subtotal' => 1500,
            'discount' => 0,
            'tax' => 0,
            'total' => 1500,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'unit_name' => 'Strip',
                    'unit_quantity' => 1,
                    'quantity' => 10,
                    'unit_price' => 1500,
                ]
            ]
        ]);
        $response2->assertStatus(200);
        $batch->refresh();
        $this->assertEquals(2989, $batch->quantity); // 2999 - 10 = 2989

        // 3. Sell 1 Box (100 Tablets)
        $response3 = $this->postJson(route('sales.store'), [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'subtotal' => 14000,
            'discount' => 0,
            'tax' => 0,
            'total' => 14000,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'unit_name' => 'Box',
                    'unit_quantity' => 1,
                    'quantity' => 100,
                    'unit_price' => 14000,
                ]
            ]
        ]);
        $response3->assertStatus(200);
        $batch->refresh();
        $this->assertEquals(2889, $batch->quantity); // 2989 - 100 = 2889

        // Verify Stock Ledger recorded 3 entries
        $ledgers = StockLedger::where('medicine_id', $medicine->id)->get();
        $this->assertCount(3, $ledgers);
    }

    #[Test]
    public function offline_license_activation()
    {
        $payload = LicenseService::generateLicensePayload('Test Care Pharmacy', 'TC-001', 'PREMIUM', 'PERPETUAL');
        $result = LicenseService::activateLicense($payload);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('PREMIUM', LicenseService::getEdition());
        $this->assertTrue(LicenseService::isPremium());
    }

    #[Test]
    public function license_generator_ui_and_download()
    {
        $response = $this->get(route('settings.license.generator'));
        $response->assertStatus(200);
        $response->assertSee('Offline License Key Generator');

        $postResponse = $this->post(route('settings.license.generate'), [
            'business_name' => 'Generator Test Pharmacy',
            'business_id' => 'GEN-999',
            'edition' => 'PREMIUM',
            'license_type' => 'PERPETUAL',
            'action' => 'activate_now',
        ]);

        $postResponse->assertRedirect(route('settings.license'));
        $this->assertEquals('PREMIUM', LicenseService::getEdition());
    }
}

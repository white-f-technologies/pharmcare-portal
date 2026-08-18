<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\MedicineUnit;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UgandaReferenceDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_ugandan_pharmacy_categories_and_suppliers_are_seeded(): void
    {
        $this->assertGreaterThanOrEqual(20, Category::count());
        $this->assertTrue(Category::where('name', 'Antimalarials & Antiparasitics')->exists());
        $this->assertTrue(Category::where('name', 'Antibiotics & Antimicrobials')->exists());
        $this->assertTrue(Category::where('name', 'Pain Relief & Analgesics')->exists());

        $this->assertGreaterThanOrEqual(6, Supplier::count());
        $this->assertTrue(Supplier::where('name', 'like', '%Joint Medical Store%')->exists());
        $this->assertTrue(Supplier::where('name', 'like', '%Cipla Quality Chemical%')->exists());
        $this->assertTrue(Supplier::where('name', 'like', '%Rene Industries%')->exists());
    }

    public function test_popular_ugandan_medicines_seeded_with_boxes_and_active_batches(): void
    {
        $this->assertGreaterThanOrEqual(30, Medicine::count());

        // Check Coartem
        $coartem = Medicine::where('name', 'like', 'Coartem 80/480%')->first();
        $this->assertNotNull($coartem);
        $this->assertEquals('Tablet', $coartem->base_unit);
        $this->assertTrue($coartem->units()->where('unit_name', 'Box')->exists());
        $this->assertTrue($coartem->units()->where('unit_name', 'Strip')->exists());
        $this->assertGreaterThan(0, $coartem->batches()->sum('quantity'));

        // Check Panadol Extra
        $panadol = Medicine::where('name', 'like', 'Panadol Extra%')->first();
        $this->assertNotNull($panadol);
        $this->assertTrue($panadol->units()->where('unit_name', 'Box')->exists());

        // Check Augmentin
        $augmentin = Medicine::where('name', 'like', 'Augmentin%')->first();
        $this->assertNotNull($augmentin);
        $this->assertTrue($augmentin->units()->where('unit_name', 'Box')->exists());

        // Check Ventolin Inhaler
        $ventolin = Medicine::where('name', 'like', 'Ventolin%')->first();
        $this->assertNotNull($ventolin);
        $this->assertEquals('Inhaler', $ventolin->base_unit);
    }

    public function test_default_settings_and_customer_are_configured_for_uganda(): void
    {
        $this->assertEquals('UGX', Setting::get('currency_symbol'));
        $this->assertTrue(Customer::where('name', 'Walk-in Customer')->exists());
    }

    public function test_pos_sale_using_box_unit_correctly_deducts_base_stock(): void
    {
        $user = User::factory()->create(['role' => 'cashier', 'is_active' => true]);
        $customer = Customer::where('name', 'Walk-in Customer')->first();
        $medicine = Medicine::where('name', 'like', 'Panadol Extra%')->first();
        $batch = $medicine->batches()->first();

        $initialStock = $batch->quantity; // in tablets
        $boxUnit = $medicine->units()->where('unit_name', 'Box')->first();
        $this->assertNotNull($boxUnit);
        $boxFactor = (int) $boxUnit->conversion_factor; // e.g. 100 tablets

        $saleData = [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'quantity' => 1,
                    'unit_name' => 'Box',
                    'unit_quantity' => 1,
                    'unit_price' => $boxUnit->selling_price,
                ],
            ],
            'subtotal' => $boxUnit->selling_price,
            'tax' => 0,
            'discount' => 0,
            'total' => $boxUnit->selling_price,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ];

        $response = $this->actingAs($user)->post(route('sales.store'), $saleData);
        $response->assertRedirect(route('sales.index'));

        // Verify batch stock decremented by 1 Box (100 base tablets)
        $batch->refresh();
        $this->assertEquals($initialStock - $boxFactor, $batch->quantity);

        // Verify sale item recorded
        $this->assertDatabaseHas('sale_items', [
            'medicine_id' => $medicine->id,
            'unit_name' => 'Box',
            'unit_quantity' => 1,
            'quantity' => $boxFactor,
        ]);
    }
}

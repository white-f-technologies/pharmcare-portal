<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'pharmacist_test@pharmcare.test'],
            ['name' => 'Pharmacist Test', 'password' => bcrypt('password'), 'role' => 'pharmacist', 'is_active' => true]
        );
        $this->actingAs($this->user);
    }

    #[Test]
    public function pharmacist_can_record_stock_damage()
    {
        $category = Category::create(['name' => 'Antibiotics', 'slug' => 'antibiotics']);
        $supplier = Supplier::create(['name' => 'PharmaDist', 'phone' => '0700000000']);

        $medicine = Medicine::create([
            'name' => 'Amoxicillin 500mg',
            'generic_name' => 'Amoxicillin',
            'category_id' => $category->id,
            'base_unit' => 'Tablet',
            'reorder_level' => 10,
            'is_active' => true,
        ]);

        $medicine->units()->create([
            'unit_name' => 'Box',
            'conversion_factor' => 100,
            'selling_price' => 20000,
        ]);

        $batch = Batch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'AMX-DMG-001',
            'supplier_id' => $supplier->id,
            'expiry_date' => now()->addMonths(6),
            'purchase_price' => 100,
            'selling_price' => 200,
            'quantity' => 500, // 500 tablets
            'is_active' => true,
        ]);

        // Record 1 Box Damaged (100 tablets)
        $response = $this->post(route('stock.adjustments.store'), [
            'medicine_id' => $medicine->id,
            'batch_id' => $batch->id,
            'movement_type' => 'damage',
            'unit_name' => 'Box',
            'unit_quantity' => 1,
            'notes' => 'Box crushed during transport',
        ]);

        $response->assertRedirect(route('stock.adjustments.index'));
        $response->assertSessionHas('success');

        $batch->refresh();
        $this->assertEquals(400, $batch->quantity); // 500 - 100 = 400

        $ledger = StockLedger::where('medicine_id', $medicine->id)->first();
        $this->assertNotNull($ledger);
        $this->assertEquals('damage', $ledger->movement_type);
        $this->assertEquals(-100, $ledger->quantity_change);
        $this->assertEquals(500, $ledger->quantity_before);
        $this->assertEquals(400, $ledger->quantity_after);
    }
}

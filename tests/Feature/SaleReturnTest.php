<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\StockLedger;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleReturnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::firstOrCreate(
            ['email' => 'pharmacist_return@pharmcare.test'],
            ['name' => 'Pharmacist Return Test', 'password' => bcrypt('password'), 'role' => 'pharmacist', 'is_active' => true]
        );
        $this->actingAs($this->user);
    }

    #[Test]
    public function system_prevents_returning_more_than_sold_quantity()
    {
        $category = Category::create(['name' => 'Pain Relief', 'slug' => 'pain-relief']);
        $supplier = Supplier::create(['name' => 'MediSupply Co', 'phone' => '0700000000']);
        $customer = Customer::create(['name' => 'John Doe', 'phone' => '0711111111']);

        $medicine = Medicine::create([
            'name' => 'Paracetamol 500mg',
            'generic_name' => 'Paracetamol',
            'category_id' => $category->id,
            'base_unit' => 'Tablet',
            'reorder_level' => 10,
            'is_active' => true,
        ]);

        $batch = Batch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'PARA-RET-001',
            'supplier_id' => $supplier->id,
            'expiry_date' => now()->addYear(),
            'purchase_price' => 50,
            'selling_price' => 100,
            'quantity' => 100, // 100 tablets initial stock
            'is_active' => true,
        ]);

        // 1. Sell 3 Tablets
        $saleResponse = $this->postJson(route('sales.store'), [
            'customer_id' => $customer->id,
            'payment_method' => 'cash',
            'subtotal' => 300,
            'discount' => 0,
            'tax' => 0,
            'total' => 300,
            'items' => [
                [
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'unit_name' => 'Tablet',
                    'unit_quantity' => 3,
                    'quantity' => 3,
                    'unit_price' => 100,
                ]
            ]
        ]);
        $saleResponse->assertStatus(200);

        $sale = Sale::latest()->first();
        $saleItem = $sale->items->first();

        $batch->refresh();
        $this->assertEquals(97, $batch->quantity); // 100 - 3 = 97

        // 2. Try returning 4 Tablets (EXCEEDS 3 SOLD -> MUST BE REJECTED)
        $invalidReturnResponse = $this->post(route('sales.returns.store'), [
            'sale_id' => $sale->id,
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'unit_name' => 'Tablet',
                    'unit_quantity' => 4, // 4 > 3 -> Invalid!
                ]
            ],
            'reason' => 'Customer changed mind',
        ]);

        $invalidReturnResponse->assertSessionHasErrors(['items.0.unit_quantity']);
        $batch->refresh();
        $this->assertEquals(97, $batch->quantity); // Stock remains unchanged!

        // 3. Return 3 Tablets (VALID -> EXACTLY MATCHES 3 SOLD)
        $validReturnResponse = $this->post(route('sales.returns.store'), [
            'sale_id' => $sale->id,
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'unit_name' => 'Tablet',
                    'unit_quantity' => 3,
                ]
            ],
            'reason' => 'Defective packaging',
        ]);

        $validReturnResponse->assertRedirect(route('sales.returns.index'));
        $validReturnResponse->assertSessionHas('success');

        $batch->refresh();
        $this->assertEquals(100, $batch->quantity); // Stock restored from 97 back to 100!

        // 4. Try returning another 1 Tablet (EXCEEDS REMAINING 0 RETURNABLE -> REJECTED)
        $secondReturnResponse = $this->post(route('sales.returns.store'), [
            'sale_id' => $sale->id,
            'items' => [
                [
                    'sale_item_id' => $saleItem->id,
                    'unit_name' => 'Tablet',
                    'unit_quantity' => 1,
                ]
            ],
            'reason' => 'Another return',
        ]);
        $secondReturnResponse->assertSessionHasErrors(['items.0.unit_quantity']);
    }
}

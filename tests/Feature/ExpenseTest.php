<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $category = ExpenseCategory::first() ?? ExpenseCategory::create([
            'name' => 'General',
            'description' => 'General expenses',
        ]);

        $medCategory = \App\Models\Category::first();

        $medicine = \App\Models\Medicine::create([
            'name' => 'Paracetamol 500mg',
            'category_id' => $medCategory?->id,
            'code' => 'MED-001',
            'reorder_level' => 10,
            'is_active' => true,
        ]);

        \App\Models\Batch::create([
            'medicine_id' => $medicine->id,
            'batch_number' => 'BAT-001',
            'expiry_date' => now()->addYear(),
            'quantity' => 500,
            'purchase_price' => 500,
            'selling_price' => 1000,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_expenses_page(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/expenses');
        $response->assertStatus(200);
        $response->assertSee('Pharmacy Expenses');
    }

    public function test_user_can_create_expense_within_available_profit(): void
    {
        $admin = User::where('role', 'admin')->first();
        $category = ExpenseCategory::first();
        $batch = \App\Models\Batch::first();

        // Create a sale with 50,000 profit today
        $sale = \App\Models\Sale::create([
            'invoice_no' => 'INV-TEST-PROFIT',
            'user_id' => $admin->id,
            'subtotal' => 100000,
            'tax' => 0,
            'discount' => 0,
            'total' => 100000,
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'created_at' => now(),
        ]);
        \App\Models\SaleItem::create([
            'sale_id' => $sale->id,
            'medicine_id' => $batch->medicine_id,
            'batch_id' => $batch->id,
            'quantity' => 100,
            'unit_name' => 'Tablet',
            'unit_quantity' => 100,
            'unit_price' => 1000,
            'total' => 100000,
        ]);

        $response = $this->actingAs($admin)->post('/expenses', [
            'expense_category_id' => $category->id,
            'title' => 'Tea & Water',
            'amount' => 1000,
            'payment_method' => 'cash',
            'expense_date' => now()->toDateString(),
            'notes' => 'Tea allowance',
        ]);

        $response->assertRedirect('/expenses');
        $this->assertDatabaseHas('expenses', [
            'title' => 'Tea & Water',
            'amount' => 1000.00,
        ]);
    }

    public function test_system_blocks_expense_exceeding_available_profit(): void
    {
        $admin = User::where('role', 'admin')->first();
        $category = ExpenseCategory::first();

        // Attempting to record an expense of 500,000 when available profit today is only 2,200
        $response = $this->actingAs($admin)->post('/expenses', [
            'expense_category_id' => $category->id,
            'title' => 'Excessive Rent Expense',
            'amount' => 500000,
            'payment_method' => 'cash',
            'expense_date' => now()->toDateString(),
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseMissing('expenses', [
            'title' => 'Excessive Rent Expense',
        ]);
    }

    public function test_system_blocks_expense_when_date_has_no_profit(): void
    {
        $admin = User::where('role', 'admin')->first();
        $category = ExpenseCategory::first();

        // Yesterday has 0 sales and 0 profit
        $yesterday = now()->subDays(2)->toDateString();

        $response = $this->actingAs($admin)->post('/expenses', [
            'expense_category_id' => $category->id,
            'title' => 'Yesterday Food Expense',
            'amount' => 5000,
            'payment_method' => 'cash',
            'expense_date' => $yesterday,
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseMissing('expenses', [
            'title' => 'Yesterday Food Expense',
        ]);
    }
}

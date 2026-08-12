<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('stock_ledgers')) {
            Schema::create('stock_ledgers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
                $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
                $table->string('movement_type', 30); // purchase, sale, damage, adjustment, return, initial
                $table->integer('quantity_change'); // Signed base quantity change, e.g. -10 or +100
                $table->integer('quantity_before')->default(0);
                $table->integer('quantity_after')->default(0);
                $table->string('unit_name', 50)->nullable(); // e.g. Box, Strip, Tablet
                $table->decimal('unit_quantity', 10, 2)->nullable(); // Quantity in selected unit
                $table->string('reference_type', 100)->nullable(); // e.g. App\Models\Sale
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('sale_items') && !Schema::hasColumn('sale_items', 'unit_name')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->string('unit_name', 50)->nullable()->after('quantity');
                $table->decimal('unit_quantity', 10, 2)->nullable()->after('unit_name');
            });
        }

        if (Schema::hasTable('purchase_items') && !Schema::hasColumn('purchase_items', 'unit_name')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->string('unit_name', 50)->nullable()->after('quantity');
                $table->decimal('unit_quantity', 10, 2)->nullable()->after('unit_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ledgers');

        if (Schema::hasTable('sale_items') && Schema::hasColumn('sale_items', 'unit_name')) {
            Schema::table('sale_items', function (Blueprint $table) {
                $table->dropColumn(['unit_name', 'unit_quantity']);
            });
        }

        if (Schema::hasTable('purchase_items') && Schema::hasColumn('purchase_items', 'unit_name')) {
            Schema::table('purchase_items', function (Blueprint $table) {
                $table->dropColumn(['unit_name', 'unit_quantity']);
            });
        }
    }
};

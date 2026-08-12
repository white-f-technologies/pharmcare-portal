<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medicines') && !Schema::hasColumn('medicines', 'base_unit')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('base_unit', 50)->default('Tablet')->after('manufacturer');
            });
        }

        if (!Schema::hasTable('medicine_units')) {
            Schema::create('medicine_units', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->constrained()->cascadeOnDelete();
                $table->string('unit_name', 50); // e.g. Box, Strip, Bottle, Pack, Sachet
                $table->decimal('conversion_factor', 12, 4)->default(1); // How many base units are in this unit? e.g. 1 Strip = 10 Tablets -> conversion_factor = 10
                $table->decimal('selling_price', 10, 2)->nullable(); // Custom fixed price for this unit, if null calculated dynamically
                $table->boolean('is_default_sale')->default(false);
                $table->boolean('is_default_purchase')->default(false);
                $table->timestamps();

                $table->unique(['medicine_id', 'unit_name']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_units');
        if (Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'base_unit')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('base_unit');
            });
        }
    }
};

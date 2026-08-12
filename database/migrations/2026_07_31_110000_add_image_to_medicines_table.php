<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('medicines') && !Schema::hasColumn('medicines', 'image')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('image')->nullable()->after('manufacturer');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('medicines') && Schema::hasColumn('medicines', 'image')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }
};

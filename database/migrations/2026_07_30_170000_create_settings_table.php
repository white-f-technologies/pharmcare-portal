<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });

            // Insert default system settings (empty - setup wizard populates these)
            $now = now();
            DB::table('settings')->insert([
                ['key' => 'app_name', 'value' => 'PharmCare', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'app_logo', 'value' => null, 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'currency_symbol', 'value' => 'UGX', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'system_email', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'system_phone', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
                ['key' => 'system_address', 'value' => '', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

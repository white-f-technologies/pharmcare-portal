<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('licenses')) {
            Schema::create('licenses', function (Blueprint $table) {
                $table->id();
                $table->string('license_key')->unique();
                $table->string('business_name');
                $table->string('business_id')->nullable();
                $table->string('edition', 20)->default('DEFAULT'); // DEFAULT or PREMIUM
                $table->json('activated_modules')->nullable();
                $table->date('issue_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->string('license_type', 30)->default('PERPETUAL'); // PERPETUAL, SUBSCRIPTION, TRIAL
                $table->string('installation_identity')->nullable();
                $table->string('status', 20)->default('ACTIVE'); // ACTIVE, EXPIRED, REVOKED, INVALID
                $table->text('signature');
                $table->longText('raw_payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};

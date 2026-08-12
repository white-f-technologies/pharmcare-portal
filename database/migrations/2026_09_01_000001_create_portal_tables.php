<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Portal Clients Table
        if (!Schema::hasTable('portal_clients')) {
            Schema::create('portal_clients', function (Blueprint $table) {
                $table->id();
                $table->string('client_id')->unique(); // e.g. PHC-UG-00001
                $table->string('pharmacy_name');
                $table->string('owner_name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('location')->nullable();
                $table->string('status', 20)->default('ACTIVE'); // ACTIVE, SUSPENDED, INACTIVE
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Portal Installations Table
        if (!Schema::hasTable('portal_installations')) {
            Schema::create('portal_installations', function (Blueprint $table) {
                $table->id();
                $table->string('installation_id')->unique(); // e.g. PHC-INST-7F82A1C9
                $table->string('client_id')->nullable();
                $table->string('license_key')->nullable();
                $table->string('app_version')->nullable();
                $table->string('hostname')->nullable();
                $table->string('os_info')->nullable();
                $table->timestamp('first_activated_at')->nullable();
                $table->timestamp('last_verified_at')->nullable();
                $table->string('status', 20)->default('ACTIVE');
                $table->timestamps();
            });
        }

        // 3. Portal Releases Table
        if (!Schema::hasTable('portal_releases')) {
            Schema::create('portal_releases', function (Blueprint $table) {
                $table->id();
                $table->string('version')->unique(); // e.g. 2.1.0
                $table->date('release_date');
                $table->string('download_url');
                $table->text('release_notes')->nullable();
                $table->string('min_supported_version')->default('2.0.0');
                $table->boolean('requires_db_migration')->default(false);
                $table->string('status', 20)->default('PUBLISHED'); // DRAFT, PUBLISHED, DEPRECATED
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_releases');
        Schema::dropIfExists('portal_installations');
        Schema::dropIfExists('portal_clients');
    }
};

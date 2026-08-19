<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('portal_releases')) {
            Schema::table('portal_releases', function (Blueprint $table) {
                if (!Schema::hasColumn('portal_releases', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('download_url');
                }
                if (!Schema::hasColumn('portal_releases', 'file_hash')) {
                    $table->string('file_hash')->nullable()->after('file_size');
                }
                if (!Schema::hasColumn('portal_releases', 'download_count')) {
                    $table->unsignedInteger('download_count')->default(0)->after('file_hash');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('portal_releases')) {
            Schema::table('portal_releases', function (Blueprint $table) {
                $columns = ['file_size', 'file_hash', 'download_count'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('portal_releases', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

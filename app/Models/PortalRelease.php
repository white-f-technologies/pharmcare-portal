<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalRelease extends Model
{
    protected $fillable = [
        'version',
        'release_date',
        'download_url',
        'file_size',
        'file_hash',
        'download_count',
        'release_notes',
        'min_supported_version',
        'requires_db_migration',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'requires_db_migration' => 'boolean',
        ];
    }
}

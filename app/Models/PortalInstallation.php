<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalInstallation extends Model
{
    protected $fillable = [
        'installation_id',
        'client_id',
        'license_key',
        'app_version',
        'hostname',
        'os_info',
        'first_activated_at',
        'last_verified_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'first_activated_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }
}

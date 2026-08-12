<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $fillable = [
        'license_key',
        'business_name',
        'business_id',
        'edition',
        'activated_modules',
        'issue_date',
        'expiry_date',
        'license_type',
        'installation_identity',
        'status',
        'signature',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'activated_modules' => 'array',
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }
}

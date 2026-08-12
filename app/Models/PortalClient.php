<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortalClient extends Model
{
    protected $fillable = [
        'client_id',
        'pharmacy_name',
        'owner_name',
        'phone',
        'email',
        'location',
        'status',
        'notes',
    ];

    public static function generateClientId(): string
    {
        $count = static::count() + 1;
        return 'PHC-UG-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}

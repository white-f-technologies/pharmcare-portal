<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'loyalty_points', 'is_active'];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineUnit extends Model
{
    protected $fillable = [
        'medicine_id',
        'unit_name',
        'conversion_factor',
        'selling_price',
        'is_default_sale',
        'is_default_purchase',
    ];

    protected function casts(): array
    {
        return [
            'conversion_factor' => 'decimal:4',
            'selling_price' => 'decimal:2',
            'is_default_sale' => 'boolean',
            'is_default_purchase' => 'boolean',
        ];
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}

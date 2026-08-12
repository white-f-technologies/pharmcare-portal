<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = ['medicine_id', 'batch_number', 'supplier_id', 'expiry_date', 'mfg_date', 'purchase_price', 'selling_price', 'quantity', 'is_active'];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'mfg_date' => 'date',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0)->where('is_active', true);
    }
}

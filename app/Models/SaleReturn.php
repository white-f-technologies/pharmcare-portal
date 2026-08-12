<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $fillable = [
        'sale_id',
        'sale_item_id',
        'medicine_id',
        'batch_id',
        'returned_unit_name',
        'returned_unit_quantity',
        'returned_base_quantity',
        'refund_amount',
        'user_id',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'returned_unit_quantity' => 'decimal:4',
            'refund_amount' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function saleItem()
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

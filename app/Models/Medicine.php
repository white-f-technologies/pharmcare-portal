<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Medicine extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'category_id',
        'manufacturer',
        'base_unit',
        'image',
        'description',
        'reorder_level',
        'requires_prescription',
        'is_active',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
            'reorder_level' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image && Storage::disk('public')->exists($this->image)) {
            return asset('media/' . $this->image);
        }
        return null;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function units()
    {
        return $this->hasMany(MedicineUnit::class);
    }

    public function stockLedgers()
    {
        return $this->hasMany(StockLedger::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get conversion factor for a unit name relative to base unit.
     * Base unit factor is always 1.
     */
    public function getUnitConversionFactor(?string $unitName): float
    {
        if (!$unitName || strtolower(trim($unitName)) === strtolower(trim($this->base_unit ?? 'Tablet'))) {
            return 1.0;
        }

        $unit = $this->units->first(fn($u) => strtolower(trim($u->unit_name)) === strtolower(trim($unitName)));
        return $unit ? (float) $unit->conversion_factor : 1.0;
    }

    /**
     * Calculate price per unit given base price and unit name.
     */
    public function getUnitSellingPrice(?string $unitName, float $baseSellingPrice): float
    {
        if (!$unitName || strtolower(trim($unitName)) === strtolower(trim($this->base_unit ?? 'Tablet'))) {
            return $baseSellingPrice;
        }

        $unit = $this->units->first(fn($u) => strtolower(trim($u->unit_name)) === strtolower(trim($unitName)));
        if ($unit && $unit->selling_price !== null && (float)$unit->selling_price > 0) {
            return (float) $unit->selling_price;
        }

        $factor = $unit ? (float) $unit->conversion_factor : 1.0;
        return round($baseSellingPrice * $factor, 2);
    }
}

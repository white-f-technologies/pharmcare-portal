<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'address', 'company', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }
}

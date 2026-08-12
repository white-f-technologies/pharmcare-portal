<?php
$models = [
    'Category' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected \$fillable = ['name', 'slug', 'description'];

    public function medicines()
    {
        return \$this->hasMany(Medicine::class);
    }
}
",
    'Supplier' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected \$fillable = ['name', 'email', 'phone', 'address', 'company', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function purchases()
    {
        return \$this->hasMany(Purchase::class);
    }

    public function batches()
    {
        return \$this->hasMany(Batch::class);
    }
}
",
    'Medicine' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    protected \$fillable = ['name', 'generic_name', 'category_id', 'manufacturer', 'description', 'reorder_level', 'requires_prescription', 'is_active'];

    protected function casts(): array
    {
        return [
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
            'reorder_level' => 'integer',
        ];
    }

    public function category()
    {
        return \$this->belongsTo(Category::class);
    }

    public function batches()
    {
        return \$this->hasMany(Batch::class);
    }

    public function saleItems()
    {
        return \$this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return \$this->hasMany(PurchaseItem::class);
    }
}
",
    'Batch' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected \$fillable = ['medicine_id', 'batch_number', 'supplier_id', 'expiry_date', 'mfg_date', 'purchase_price', 'selling_price', 'quantity', 'is_active'];

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
        return \$this->belongsTo(Medicine::class);
    }

    public function supplier()
    {
        return \$this->belongsTo(Supplier::class);
    }

    public function saleItems()
    {
        return \$this->hasMany(SaleItem::class);
    }

    public function scopeInStock(\$query)
    {
        return \$query->where('quantity', '>', 0)->where('is_active', true);
    }
}
",
    'Customer' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected \$fillable = ['name', 'email', 'phone', 'address', 'loyalty_points', 'is_active'];

    protected function casts(): array
    {
        return [
            'loyalty_points' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function sales()
    {
        return \$this->hasMany(Sale::class);
    }

    public function prescriptions()
    {
        return \$this->hasMany(Prescription::class);
    }
}
",
    'Purchase' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected \$fillable = ['invoice_no', 'supplier_id', 'user_id', 'subtotal', 'tax', 'discount', 'total', 'status', 'notes'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return \$this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return \$this->belongsTo(User::class);
    }

    public function items()
    {
        return \$this->hasMany(PurchaseItem::class);
    }
}
",
    'PurchaseItem' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected \$fillable = ['purchase_id', 'medicine_id', 'batch_id', 'quantity', 'unit_price', 'total'];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function purchase()
    {
        return \$this->belongsTo(Purchase::class);
    }

    public function medicine()
    {
        return \$this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return \$this->belongsTo(Batch::class);
    }
}
",
    'Sale' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected \$fillable = ['invoice_no', 'customer_id', 'user_id', 'subtotal', 'tax', 'discount', 'total', 'payment_method', 'payment_status', 'notes'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function customer()
    {
        return \$this->belongsTo(Customer::class);
    }

    public function user()
    {
        return \$this->belongsTo(User::class);
    }

    public function items()
    {
        return \$this->hasMany(SaleItem::class);
    }
}
",
    'SaleItem' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected \$fillable = ['sale_id', 'medicine_id', 'batch_id', 'quantity', 'unit_price', 'total'];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return \$this->belongsTo(Sale::class);
    }

    public function medicine()
    {
        return \$this->belongsTo(Medicine::class);
    }

    public function batch()
    {
        return \$this->belongsTo(Batch::class);
    }
}
",
    'Prescription' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected \$fillable = ['customer_id', 'doctor_name', 'hospital', 'diagnosis', 'notes', 'user_id'];

    public function customer()
    {
        return \$this->belongsTo(Customer::class);
    }

    public function user()
    {
        return \$this->belongsTo(User::class);
    }

    public function items()
    {
        return \$this->hasMany(PrescriptionItem::class);
    }
}
",
    'PrescriptionItem' => "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    protected \$fillable = ['prescription_id', 'medicine_id', 'dosage', 'duration', 'notes'];

    public function prescription()
    {
        return \$this->belongsTo(Prescription::class);
    }

    public function medicine()
    {
        return \$this->belongsTo(Medicine::class);
    }
}
",
];

$dir = __DIR__ . '/app/Models';
foreach ($models as $name => $content) {
    $path = $dir . '/' . $name . '.php';
    file_put_contents($path, $content);
    echo "Created: $name\n";
}

echo "\nAll models created successfully!\n";

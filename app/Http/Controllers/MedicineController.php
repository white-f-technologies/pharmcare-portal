<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $query = Medicine::with(['category', 'batches' => function ($q) {
            $q->where('is_active', true)->orderBy('created_at', 'desc');
        }])->withSum('batches', 'quantity');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('generic_name', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        $medicines = $query->paginate(10);
        return view('medicines.index', compact('medicines'));
    }

    public function create()
    {
        $categories = Category::pluck('name', 'id');
        return view('medicines.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'base_unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'reorder_level' => 'integer|min:0',
            'selling_price' => 'nullable|numeric|min:0|gte:purchase_price',
            'purchase_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'units' => 'nullable|array',
            'units.*.unit_name' => 'required_with:units|string|max:50',
            'units.*.conversion_factor' => 'required_with:units|numeric|min:0.0001',
            'units.*.selling_price' => 'nullable|numeric|min:0',
        ], [
            'selling_price.gte' => 'The selling price cannot be less than the buying/purchase price.',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('medicines', 'public');
        }

        $validated['requires_prescription'] = $request->boolean('requires_prescription');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['base_unit'] = $request->input('base_unit', 'Tablet');

        $medicine = Medicine::create($validated);

        if ($request->has('units') && is_array($request->input('units'))) {
            foreach ($request->input('units') as $u) {
                if (!empty($u['unit_name']) && !empty($u['conversion_factor'])) {
                    $medicine->units()->create([
                        'unit_name' => trim($u['unit_name']),
                        'conversion_factor' => (float)$u['conversion_factor'],
                        'selling_price' => !empty($u['selling_price']) ? (float)$u['selling_price'] : null,
                    ]);
                }
            }
        }

        if ($request->filled('selling_price') || $request->filled('purchase_price')) {
            $defaultSupplierId = \App\Models\Supplier::value('id') ?? 1;
            $medicine->batches()->create([
                'batch_number' => 'BATCH-' . strtoupper(substr(uniqid(), -6)),
                'supplier_id' => $request->input('supplier_id', $defaultSupplierId),
                'purchase_price' => $request->input('purchase_price', 0),
                'selling_price' => $request->input('selling_price', 0),
                'quantity' => 0,
                'expiry_date' => now()->addYear(),
                'is_active' => true,
            ]);
        }

        $this->logActivity('medicine_created', 'Medicine', $medicine->id, "Medicine '{$medicine->name}' created.");

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine)
    {
        $medicine->load('category', 'batches.supplier', 'units');

        // 1. Calculate Realized Sales Financial Data (From completed sales and returns)
        $saleItems = \App\Models\SaleItem::where('medicine_id', $medicine->id)->with('batch')->get();
        $saleReturns = \App\Models\SaleReturn::where('medicine_id', $medicine->id)->with('batch')->get();

        $grossSalesRevenue = (float) $saleItems->sum('total');
        $refundsAmount = (float) $saleReturns->sum('refund_amount');
        $netSalesRevenue = max(0, $grossSalesRevenue - $refundsAmount);

        $grossCogs = (float) $saleItems->sum(function ($item) {
            $purchasePrice = $item->batch ? (float)$item->batch->purchase_price : 0;
            return (float)$item->quantity * $purchasePrice;
        });

        $refundedCogs = (float) $saleReturns->sum(function ($ret) {
            $purchasePrice = $ret->batch ? (float)$ret->batch->purchase_price : 0;
            return (float)$ret->returned_base_quantity * $purchasePrice;
        });

        $netCogs = max(0, $grossCogs - $refundedCogs);
        $totalRealizedProfit = $netSalesRevenue - $netCogs;

        // 2. Calculate Potential Inventory Profit (From remaining active stock)
        $totalPotentialInventoryProfit = (float) $medicine->batches->where('is_active', true)->sum(function ($batch) {
            $unitProfit = max(0, (float)$batch->selling_price - (float)$batch->purchase_price);
            return (float)$batch->quantity * $unitProfit;
        });

        $totalRemainingStockBaseUnits = (int) $medicine->batches->where('is_active', true)->sum('quantity');

        return view('medicines.show', compact(
            'medicine',
            'netSalesRevenue',
            'netCogs',
            'totalRealizedProfit',
            'totalPotentialInventoryProfit',
            'totalRemainingStockBaseUnits'
        ));
    }

    public function edit(Medicine $medicine)
    {
        $medicine->load('units');
        $categories = Category::pluck('name', 'id');
        $latestBatch = $medicine->batches()->latest()->first();
        $sellingPrice = $latestBatch ? $latestBatch->selling_price : 0;
        $purchasePrice = $latestBatch ? $latestBatch->purchase_price : 0;

        return view('medicines.edit', compact('medicine', 'categories', 'sellingPrice', 'purchasePrice'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'manufacturer' => 'nullable|string|max:255',
            'base_unit' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'reorder_level' => 'integer|min:0',
            'selling_price' => 'nullable|numeric|min:0|gte:purchase_price',
            'purchase_price' => 'nullable|numeric|min:0',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'units' => 'nullable|array',
            'units.*.unit_name' => 'nullable|string|max:50',
            'units.*.conversion_factor' => 'nullable|numeric|min:0.0001',
            'units.*.selling_price' => 'nullable|numeric|min:0',
        ], [
            'selling_price.gte' => 'The selling price cannot be less than the buying/purchase price.',
        ]);

        if ($request->hasFile('image')) {
            if ($medicine->image && Storage::disk('public')->exists($medicine->image)) {
                Storage::disk('public')->delete($medicine->image);
            }
            $validated['image'] = $request->file('image')->store('medicines', 'public');
        }

        $validated['requires_prescription'] = $request->boolean('requires_prescription');
        $validated['is_active'] = $request->boolean('is_active');
        $validated['base_unit'] = $request->input('base_unit', $medicine->base_unit ?? 'Tablet');

        $medicine->update($validated);

        if ($request->has('units') && is_array($request->input('units'))) {
            $medicine->units()->delete();
            foreach ($request->input('units') as $u) {
                if (!empty($u['unit_name']) && !empty($u['conversion_factor'])) {
                    $medicine->units()->create([
                        'unit_name' => trim($u['unit_name']),
                        'conversion_factor' => (float)$u['conversion_factor'],
                        'selling_price' => !empty($u['selling_price']) ? (float)$u['selling_price'] : null,
                    ]);
                }
            }
        }

        if ($request->filled('selling_price') || $request->filled('purchase_price')) {
            $batches = $medicine->batches()->where('is_active', true)->get();
            if ($batches->isEmpty()) {
                $medicine->batches()->create([
                    'batch_number' => 'BATCH-' . strtoupper(substr(uniqid(), -6)),
                    'purchase_price' => $request->input('purchase_price', 0),
                    'selling_price' => $request->input('selling_price', 0),
                    'quantity' => 0,
                    'expiry_date' => now()->addYear(),
                    'is_active' => true,
                ]);
            } else {
                foreach ($batches as $batch) {
                    $updateData = [];
                    if ($request->filled('selling_price')) {
                        $updateData['selling_price'] = $request->input('selling_price');
                    }
                    if ($request->filled('purchase_price')) {
                        $updateData['purchase_price'] = $request->input('purchase_price');
                    }
                    $batch->update($updateData);
                }
            }
        }

        $this->logActivity('medicine_updated', 'Medicine', $medicine->id, "Medicine '{$medicine->name}' updated.");

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        if ($medicine->saleItems()->exists() || $medicine->purchaseItems()->exists()) {
            return redirect()->route('medicines.index')
                ->with('error', 'Cannot delete medicine because it has sale or purchase transactions.');
        }

        if ($medicine->image && Storage::disk('public')->exists($medicine->image)) {
            Storage::disk('public')->delete($medicine->image);
        }

        $this->logActivity('medicine_deleted', 'Medicine', $medicine->id, "Medicine '{$medicine->name}' deleted.");
        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', 'Medicine deleted successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $medicines = Medicine::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('generic_name', 'like', "%{$query}%");
            })
            ->with(['units', 'batches' => function ($q) {
                $q->where('quantity', '>', 0)
                  ->where('is_active', true)
                  ->whereDate('expiry_date', '>', now()->toDateString())
                  ->orderBy('expiry_date');
            }])
            ->get()
            ->flatMap(function ($medicine) {
                if ($medicine->batches->isEmpty()) {
                    return [];
                }
                return $medicine->batches->map(function ($batch) use ($medicine) {
                    $unitList = [
                        [
                            'unit_name' => $medicine->base_unit ?? 'Tablet',
                            'conversion_factor' => 1.0,
                            'price' => (float)$batch->selling_price,
                            'is_base' => true
                        ]
                    ];
                    foreach ($medicine->units as $u) {
                        $unitPrice = $u->selling_price !== null && (float)$u->selling_price > 0
                            ? (float)$u->selling_price
                            : round((float)$batch->selling_price * (float)$u->conversion_factor, 2);

                        $unitList[] = [
                            'unit_name' => $u->unit_name,
                            'conversion_factor' => (float)$u->conversion_factor,
                            'price' => $unitPrice,
                            'is_base' => false
                        ];
                    }

                    return [
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'name' => $medicine->name,
                        'generic_name' => $medicine->generic_name,
                        'base_unit' => $medicine->base_unit ?? 'Tablet',
                        'selling_price' => (float)$batch->selling_price,
                        'stock_qty' => (int)$batch->quantity,
                        'image_url' => $medicine->image_url,
                        'units' => $unitList,
                    ];
                });
            })
            ->take(20);

        return response()->json($medicines);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with('supplier', 'user')->latest()->paginate(10);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $medicines = Medicine::with(['units', 'batches' => function ($q) {
            $q->where('is_active', true)->orderBy('created_at', 'desc');
        }])->where('is_active', true)->get()->map(function ($med) {
            $units = [];
            $baseUnit = $med->base_unit ?? 'Tablet';
            $basePurchase = (float) ($med->purchase_price ?? 0);
            $baseSelling = (float) ($med->selling_price ?? 0);

            $units[] = [
                'unit_name' => $baseUnit,
                'conversion_factor' => 1.0,
                'purchase_price' => $basePurchase,
                'selling_price' => $baseSelling,
            ];
            foreach ($med->units as $u) {
                if ($u->unit_name !== $baseUnit) {
                    $factor = (float) $u->conversion_factor;
                    $unitSell = $u->selling_price !== null ? (float) $u->selling_price : round($baseSelling * $factor, 2);
                    $unitCost = round($basePurchase * $factor, 2);
                    $units[] = [
                        'unit_name' => $u->unit_name,
                        'conversion_factor' => $factor,
                        'purchase_price' => $unitCost,
                        'selling_price' => $unitSell,
                    ];
                }
            }
            return [
                'id' => $med->id,
                'name' => $med->name,
                'base_unit' => $baseUnit,
                'purchase_price' => $basePurchase,
                'selling_price' => $baseSelling,
                'units' => $units,
            ];
        });

        return view('purchases.create', compact('suppliers', 'medicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'required',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_name' => 'nullable|string|max:50',
            'items.*.unit_quantity' => 'nullable|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
        ]);

        foreach ($request->input('items', []) as $index => $item) {
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $sellingPrice = floatval($item['selling_price'] ?? 0);
            if ($sellingPrice < $unitPrice) {
                $itemNum = $index + 1;
                $msg = "Selling price cannot be less than buying price for item #{$itemNum}.";
                if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                        'errors' => ["items.{$index}.selling_price" => [$msg]]
                    ], 422);
                }
                return back()->withInput()->withErrors([
                    "items.{$index}.selling_price" => $msg
                ]);
            }
        }

        $purchase = null;
        $invoiceNo = '';

        DB::transaction(function () use ($validated, &$invoiceNo, &$purchase) {
            $attempts = 0;
            do {
                $count = Purchase::whereDate('created_at', today())->count() + 1 + $attempts;
                $invoiceNo = 'PO-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                $exists = Purchase::where('invoice_no', $invoiceNo)->exists();
                $attempts++;
            } while ($exists && $attempts < 10);

            $purchase = Purchase::create([
                'invoice_no' => $invoiceNo,
                'supplier_id' => $validated['supplier_id'],
                'user_id' => Auth::id(),
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'total' => $validated['total'],
                'status' => 'completed',
            ]);

            foreach ($validated['items'] as $item) {
                $medicine = Medicine::with('units')->findOrFail($item['medicine_id']);
                $unitName = $item['unit_name'] ?? $medicine->base_unit ?? 'Tablet';
                $unitQty = isset($item['unit_quantity']) && $item['unit_quantity'] > 0 ? (float)$item['unit_quantity'] : (int)$item['quantity'];

                $factor = $medicine->getUnitConversionFactor($unitName);
                $baseQuantityAdded = (int) ceil($unitQty * $factor);

                $batch = Batch::firstOrCreate(
                    ['medicine_id' => $item['medicine_id'], 'batch_number' => $item['batch_number']],
                    [
                        'supplier_id' => $validated['supplier_id'],
                        'expiry_date' => $item['expiry_date'],
                        'purchase_price' => $item['unit_price'],
                        'selling_price' => $item['selling_price'],
                        'quantity' => 0,
                    ]
                );

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'quantity' => $baseQuantityAdded,
                    'unit_name' => $unitName,
                    'unit_quantity' => $unitQty,
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);

                $qtyBefore = $batch->quantity;
                $batch->increment('quantity', $baseQuantityAdded);
                $qtyAfter = $batch->quantity;

                \App\Models\StockLedger::create([
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'purchase',
                    'quantity_change' => $baseQuantityAdded,
                    'quantity_before' => $qtyBefore,
                    'quantity_after' => $qtyAfter,
                    'unit_name' => $unitName,
                    'unit_quantity' => $unitQty,
                    'reference_type' => Purchase::class,
                    'reference_id' => $purchase->id,
                    'user_id' => Auth::id(),
                    'notes' => "Purchase invoice {$invoiceNo}",
                ]);
            }
        });

        $this->logActivity('purchase_created', 'Purchase', $purchase->id, "Purchase {$invoiceNo} created. Total: " . setting('currency_symbol', 'UGX') . " " . number_format($validated['total'], 2));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase created successfully.',
                'purchase' => $purchase->load('supplier', 'items.medicine'),
                'redirect' => route('purchases.index')
            ]);
        }

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase created successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('items.medicine', 'items.batch', 'supplier', 'user');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $medicines = Medicine::with('batches')->where('is_active', true)->get();
        $purchase->load('items.medicine', 'items.batch');
        return view('purchases.edit', compact('purchase', 'suppliers', 'medicines'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_number' => 'required',
            'items.*.expiry_date' => 'required|date',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.selling_price' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
        ]);

        foreach ($request->input('items', []) as $index => $item) {
            $unitPrice = floatval($item['unit_price'] ?? 0);
            $sellingPrice = floatval($item['selling_price'] ?? 0);
            if ($sellingPrice < $unitPrice) {
                $itemNum = $index + 1;
                $msg = "Selling price cannot be less than buying price for item #{$itemNum}.";
                if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                        'errors' => ["items.{$index}.selling_price" => [$msg]]
                    ], 422);
                }
                return back()->withInput()->withErrors([
                    "items.{$index}.selling_price" => $msg
                ]);
            }
        }

        DB::transaction(function () use ($validated, $purchase) {
            foreach ($purchase->items as $oldItem) {
                Batch::where('id', $oldItem->batch_id)
                    ->decrement('quantity', $oldItem->quantity);
            }

            $purchase->items()->delete();

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'subtotal' => $validated['subtotal'],
                'tax' => $validated['tax'] ?? 0,
                'discount' => $validated['discount'] ?? 0,
                'total' => $validated['total'],
            ]);

            foreach ($validated['items'] as $item) {
                $batch = Batch::firstOrCreate(
                    ['medicine_id' => $item['medicine_id'], 'batch_number' => $item['batch_number']],
                    [
                        'supplier_id' => $validated['supplier_id'],
                        'expiry_date' => $item['expiry_date'],
                        'purchase_price' => $item['unit_price'],
                        'selling_price' => $item['selling_price'],
                        'quantity' => 0,
                    ]
                );

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'medicine_id' => $item['medicine_id'],
                    'batch_id' => $batch->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['quantity'] * $item['unit_price'],
                ]);

                $batch->increment('quantity', $item['quantity']);
            }
        });

        $this->logActivity('purchase_updated', 'Purchase', $purchase->id, "Purchase {$purchase->invoice_no} updated.");

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Purchase updated successfully.',
                'purchase' => $purchase->load('supplier', 'items.medicine'),
                'redirect' => route('purchases.index')
            ]);
        }

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                Batch::where('id', $item->batch_id)
                    ->decrement('quantity', $item->quantity);
            }
            $purchase->items()->delete();
            $purchase->delete();
        });

        $this->logActivity('purchase_deleted', 'Purchase', $purchase->id, "Purchase {$purchase->invoice_no} deleted.");

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }
}

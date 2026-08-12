<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index()
    {
        $returns = SaleReturn::with(['sale', 'medicine', 'batch', 'user'])
            ->latest()
            ->paginate(15);

        return view('sales.returns.index', compact('returns'));
    }

    public function create(Sale $sale)
    {
        $sale->load(['customer', 'items.medicine.units', 'items.batch']);

        $itemsWithReturnLimits = $sale->items->map(function ($item) {
            $alreadyReturnedBase = (int) SaleReturn::where('sale_item_id', $item->id)->sum('returned_base_quantity');
            $maxReturnableBase = max(0, (int)$item->quantity - $alreadyReturnedBase);

            $medicine = $item->medicine;
            $baseUnit = $medicine->base_unit ?? 'Tablet';

            $units = [];
            $units[] = ['unit_name' => $baseUnit, 'conversion_factor' => 1.0];
            if ($medicine && $medicine->units) {
                foreach ($medicine->units as $u) {
                    if ($u->unit_name !== $baseUnit) {
                        $units[] = ['unit_name' => $u->unit_name, 'conversion_factor' => (float)$u->conversion_factor];
                    }
                }
            }

            return [
                'sale_item_id' => $item->id,
                'medicine_id' => $item->medicine_id,
                'medicine_name' => $medicine?->name,
                'batch_id' => $item->batch_id,
                'batch_number' => $item->batch?->batch_number,
                'sold_unit_name' => $item->unit_name ?? $baseUnit,
                'sold_unit_quantity' => (float)($item->unit_quantity ?? $item->quantity),
                'sold_base_quantity' => (int) $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'already_returned_base' => $alreadyReturnedBase,
                'max_returnable_base' => $maxReturnableBase,
                'base_unit' => $baseUnit,
                'available_units' => $units,
            ];
        });

        return view('sales.returns.create', compact('sale', 'itemsWithReturnLimits'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.unit_name' => 'required|string|max:50',
            'items.*.unit_quantity' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $sale = Sale::findOrFail($validated['sale_id']);

        DB::transaction(function () use ($validated, $sale) {
            foreach ($validated['items'] as $index => $itemData) {
                $saleItem = SaleItem::with('medicine.units')->where('sale_id', $sale->id)->findOrFail($itemData['sale_item_id']);
                $medicine = $saleItem->medicine;
                $batch = Batch::where('id', $saleItem->batch_id)->lockForUpdate()->firstOrFail();

                $unitName = $itemData['unit_name'];
                $unitQty = (float) $itemData['unit_quantity'];
                $factor = $medicine ? $medicine->getUnitConversionFactor($unitName) : 1.0;
                $requestedBaseQty = (int) ceil($unitQty * $factor);

                $alreadyReturnedBase = (int) SaleReturn::where('sale_item_id', $saleItem->id)->sum('returned_base_quantity');
                $maxReturnableBase = (int) $saleItem->quantity - $alreadyReturnedBase;

                if ($requestedBaseQty > $maxReturnableBase) {
                    $itemNum = $index + 1;
                    $msg = "Item #{$itemNum} ({$medicine->name}): Cannot return {$unitQty} {$unitName}(s) ({$requestedBaseQty} base units). Maximum returnable quantity is {$maxReturnableBase} {$medicine->base_unit}(s). (Sold: {$saleItem->quantity} base units, Already Returned: {$alreadyReturnedBase}).";
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "items.{$index}.unit_quantity" => $msg
                    ]);
                }

                $qtyBefore = (int) $batch->quantity;
                $batch->increment('quantity', $requestedBaseQty);
                $qtyAfter = (int) $batch->quantity;

                $unitPrice = (float) $saleItem->unit_price;
                $refundAmount = round($unitQty * $unitPrice, 2);

                $saleReturn = SaleReturn::create([
                    'sale_id' => $sale->id,
                    'sale_item_id' => $saleItem->id,
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'returned_unit_name' => $unitName,
                    'returned_unit_quantity' => $unitQty,
                    'returned_base_quantity' => $requestedBaseQty,
                    'refund_amount' => $refundAmount,
                    'user_id' => Auth::id(),
                    'reason' => $validated['reason'],
                ]);

                StockLedger::create([
                    'medicine_id' => $medicine->id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'return',
                    'quantity_change' => $requestedBaseQty,
                    'quantity_before' => $qtyBefore,
                    'quantity_after' => $qtyAfter,
                    'unit_name' => $unitName,
                    'unit_quantity' => $unitQty,
                    'reference_type' => 'SaleReturn',
                    'reference_id' => $saleReturn->id,
                    'user_id' => Auth::id(),
                    'notes' => "Customer Return for Invoice #{$sale->invoice_no}. Reason: {$validated['reason']}",
                ]);
            }
        });

        return redirect()->route('sales.returns.index')
            ->with('success', "Customer return for Invoice #{$sale->invoice_no} processed and stock restored successfully.");
    }
}

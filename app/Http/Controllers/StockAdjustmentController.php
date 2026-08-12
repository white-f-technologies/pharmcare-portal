<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockLedger::with(['medicine', 'batch', 'user'])
            ->whereIn('movement_type', ['damage', 'return', 'adjustment'])
            ->latest()
            ->paginate(15);

        return view('stock.adjustments.index', compact('adjustments'));
    }

    public function create()
    {
        $medicines = Medicine::with(['units', 'batches' => function ($q) {
            $q->where('is_active', true)->orderBy('expiry_date');
        }])->where('is_active', true)->get()->map(function ($med) {
            $baseUnit = $med->base_unit ?? 'Tablet';
            $units = [];
            $units[] = ['unit_name' => $baseUnit, 'conversion_factor' => 1.0];
            foreach ($med->units as $u) {
                if ($u->unit_name !== $baseUnit) {
                    $units[] = ['unit_name' => $u->unit_name, 'conversion_factor' => (float)$u->conversion_factor];
                }
            }
            return [
                'id' => $med->id,
                'name' => $med->name,
                'generic_name' => $med->generic_name,
                'base_unit' => $baseUnit,
                'units' => $units,
                'batches' => $med->batches->map(fn($b) => [
                    'id' => $b->id,
                    'batch_number' => $b->batch_number,
                    'quantity' => $b->quantity,
                    'expiry_date' => $b->expiry_date ? $b->expiry_date->format('Y-m-d') : null,
                ])
            ];
        });

        return view('stock.adjustments.create', compact('medicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'batch_id' => 'required|exists:batches,id',
            'movement_type' => 'required|in:damage,return,adjustment',
            'adjustment_direction' => 'required_if:movement_type,adjustment|in:add,subtract',
            'unit_name' => 'required|string|max:50',
            'unit_quantity' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:500',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $medicine = Medicine::with('units')->findOrFail($validated['medicine_id']);
            $batch = Batch::where('medicine_id', $medicine->id)->findOrFail($validated['batch_id']);

            $factor = $medicine->getUnitConversionFactor($validated['unit_name']);
            $baseQtyChange = (int) ceil((float)$validated['unit_quantity'] * $factor);

            $qtyBefore = (int) $batch->quantity;
            $movementType = $validated['movement_type'];

            if ($movementType === 'damage') {
                if ($baseQtyChange > $qtyBefore) {
                    throw new \Exception("Cannot record damaged quantity ({$baseQtyChange} {$medicine->base_unit}s) greater than current stock ({$qtyBefore} {$medicine->base_unit}s).");
                }
                $qtyAfter = $qtyBefore - $baseQtyChange;
                $changeForLedger = -$baseQtyChange;
            } elseif ($movementType === 'return') {
                // Return: customer return (adds back to stock) or supplier return (deducts from stock)
                $direction = $request->input('return_type', 'customer_return');
                if ($direction === 'supplier_return') {
                    if ($baseQtyChange > $qtyBefore) {
                        throw new \Exception("Cannot return to supplier more stock ({$baseQtyChange}) than available ({$qtyBefore}).");
                    }
                    $qtyAfter = $qtyBefore - $baseQtyChange;
                    $changeForLedger = -$baseQtyChange;
                } else {
                    $qtyAfter = $qtyBefore + $baseQtyChange;
                    $changeForLedger = $baseQtyChange;
                }
            } else {
                // Adjustment
                if ($validated['adjustment_direction'] === 'subtract') {
                    if ($baseQtyChange > $qtyBefore) {
                        throw new \Exception("Cannot subtract more stock ({$baseQtyChange}) than available ({$qtyBefore}).");
                    }
                    $qtyAfter = $qtyBefore - $baseQtyChange;
                    $changeForLedger = -$baseQtyChange;
                } else {
                    $qtyAfter = $qtyBefore + $baseQtyChange;
                    $changeForLedger = $baseQtyChange;
                }
            }

            $batch->update(['quantity' => $qtyAfter]);

            StockLedger::create([
                'medicine_id' => $medicine->id,
                'batch_id' => $batch->id,
                'movement_type' => $movementType,
                'quantity_change' => $changeForLedger,
                'quantity_before' => $qtyBefore,
                'quantity_after' => $qtyAfter,
                'unit_name' => $validated['unit_name'],
                'unit_quantity' => (float)$validated['unit_quantity'],
                'reference_type' => 'StockAdjustment',
                'reference_id' => null,
                'user_id' => Auth::id(),
                'notes' => $validated['notes'],
            ]);
        });

        return redirect()->route('stock.adjustments.index')
            ->with('success', 'Stock adjustment recorded successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('customer', 'user')->latest()->paginate(10);
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $categories = Category::withCount(['medicines' => function ($q) {
            $q->where('is_active', true)->whereHas('batches', function ($bq) {
                $bq->where('quantity', '>', 0)->where('is_active', true);
            });
        }])->orderBy('name')->get();
        $popularMedicines = Medicine::where('is_active', true)
            ->with(['category', 'units', 'batches' => function ($q) {
                $q->where('quantity', '>', 0)
                  ->where('is_active', true)
                  ->whereDate('expiry_date', '>', now()->toDateString())
                  ->orderBy('expiry_date');
            }])
            ->get()
            ->filter(fn($med) => $med->batches->isNotEmpty())
            ->map(function ($med) {
                $batch = $med->batches->first();
                $basePrice = (float) $batch->selling_price;
                $baseUnitName = $med->base_unit ?? 'Tablet';

                $units = [];
                $units[] = [
                    'unit_name' => $baseUnitName,
                    'conversion_factor' => 1.0,
                    'price' => $basePrice,
                    'is_base' => true,
                ];

                foreach ($med->units as $u) {
                    if ($u->unit_name !== $baseUnitName) {
                        $units[] = [
                            'unit_name' => $u->unit_name,
                            'conversion_factor' => (float) $u->conversion_factor,
                            'price' => (float) ($u->selling_price ?? ($basePrice * $u->conversion_factor)),
                            'is_base' => false,
                        ];
                    }
                }

                return [
                    'medicine_id' => $med->id,
                    'category_id' => $med->category_id,
                    'category_name' => $med->category?->name ?? 'General',
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'name' => $med->name,
                    'generic_name' => $med->generic_name,
                    'base_unit' => $baseUnitName,
                    'selling_price' => $basePrice,
                    'stock_qty' => (int) $batch->quantity,
                    'image_url' => $med->image_url,
                    'units' => $units,
                ];
            })
            ->values();

        return view('sales.create', compact('customers', 'categories', 'popularMedicines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_name' => 'nullable|string|max:50',
            'items.*.unit_quantity' => 'nullable|numeric|min:0.0001',
            'items.*.unit_price' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
            'payment_method' => 'nullable',
            'payment_status' => 'nullable',
            'notes' => 'nullable',
        ]);

        $sale = null;
        $invoiceNo = '';

        try {
            DB::transaction(function () use ($validated, &$invoiceNo, &$sale) {
                $attempts = 0;
                do {
                    $count = Sale::whereDate('created_at', today())->count() + 1 + $attempts;
                    $invoiceNo = 'INV-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                    $exists = Sale::where('invoice_no', $invoiceNo)->exists();
                    $attempts++;
                } while ($exists && $attempts < 10);

                $sale = Sale::create([
                    'invoice_no' => $invoiceNo,
                    'customer_id' => $validated['customer_id'],
                    'user_id' => Auth::id(),
                    'subtotal' => $validated['subtotal'],
                    'tax' => $validated['tax'] ?? 0,
                    'discount' => $validated['discount'] ?? 0,
                    'total' => $validated['total'],
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'payment_status' => $validated['payment_status'] ?? 'paid',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $batch = Batch::where('id', $item['batch_id'])->lockForUpdate()->firstOrFail();
                    $medicine = Medicine::with('units')->findOrFail($item['medicine_id']);
                    
                    $unitName = $item['unit_name'] ?? $medicine->base_unit ?? 'Tablet';
                    $unitQty = isset($item['unit_quantity']) && $item['unit_quantity'] > 0 ? (float)$item['unit_quantity'] : (int)$item['quantity'];

                    $factor = $medicine->getUnitConversionFactor($unitName);
                    $baseQuantityDeducted = (int) ceil($unitQty * $factor);

                    if ($batch->quantity < $baseQuantityDeducted) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => "Insufficient stock for {$medicine->name} (Batch: {$batch->batch_number}). Required {$baseQuantityDeducted} base units ({$unitQty} {$unitName}), but only {$batch->quantity} left in stock."
                        ]);
                    }

                    $qtyBefore = $batch->quantity;
                    $batch->decrement('quantity', $baseQuantityDeducted);
                    $qtyAfter = $batch->quantity;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'quantity' => $baseQuantityDeducted,
                        'unit_name' => $unitName,
                        'unit_quantity' => $unitQty,
                        'unit_price' => $item['unit_price'],
                        'total' => $unitQty * $item['unit_price'],
                    ]);

                    \App\Models\StockLedger::create([
                        'medicine_id' => $medicine->id,
                        'batch_id' => $batch->id,
                        'movement_type' => 'sale',
                        'quantity_change' => -$baseQuantityDeducted,
                        'quantity_before' => $qtyBefore,
                        'quantity_after' => $qtyAfter,
                        'unit_name' => $unitName,
                        'unit_quantity' => $unitQty,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'user_id' => Auth::id(),
                        'notes' => "Sale invoice {$invoiceNo}",
                    ]);
                }
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save sale: ' . $e->getMessage()
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to save sale: ' . $e->getMessage());
        }

        $this->logActivity('sale_created', 'Sale', $sale->id, "Sale {$invoiceNo} created. Total: UGX " . number_format($validated['total'], 2));

        if ($request->expectsJson() || $request->ajax() || $request->header('Accept') === 'application/json' || $request->wantsJson()) {
            $updatedMedicines = Medicine::whereIn('id', collect($validated['items'])->pluck('medicine_id'))->with(['batches' => function($q) {
                $q->where('quantity', '>', 0)->where('is_active', true)->whereDate('expiry_date', '>', now());
            }])->get()->map(fn($m) => [
                'medicine_id' => $m->id,
                'stock_qty' => (int)$m->batches->sum('quantity'),
            ]);

            return response()->json([
                'success' => true,
                'message' => "Sale {$invoiceNo} created successfully.",
                'sale_id' => $sale->id,
                'invoice_no' => $invoiceNo,
                'invoice_url' => route('sales.invoice', $sale),
                'total' => (float)$sale->total,
                'updated_medicines' => $updatedMedicines,
            ]);
        }

        return redirect()->route('sales.index')
            ->with('success', 'Sale created successfully.');
    }

    public function show(Sale $sale)
    {
        $sale->load('items.medicine', 'items.batch', 'customer', 'user');
        return view('sales.show', compact('sale'));
    }

    public function edit(Sale $sale)
    {
        $customers = Customer::where('is_active', true)->pluck('name', 'id');
        $medicines = Medicine::with(['batches' => function ($query) {
            $query->inStock();
        }])->where('is_active', true)->get();
        $sale->load('items.medicine', 'items.batch');
        return view('sales.edit', compact('sale', 'customers', 'medicines'));
    }

    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array',
            'items.*.medicine_id' => 'required|exists:medicines,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric',
            'subtotal' => 'required|numeric',
            'tax' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
            'payment_method' => 'nullable',
            'payment_status' => 'nullable',
            'notes' => 'nullable',
        ]);

        try {
            DB::transaction(function () use ($validated, $sale) {
                foreach ($sale->items as $oldItem) {
                    Batch::where('id', $oldItem->batch_id)
                        ->increment('quantity', $oldItem->quantity);
                }

                $sale->items()->delete();

                $sale->update([
                    'customer_id' => $validated['customer_id'],
                    'subtotal' => $validated['subtotal'],
                    'tax' => $validated['tax'] ?? 0,
                    'discount' => $validated['discount'] ?? 0,
                    'total' => $validated['total'],
                    'payment_method' => $validated['payment_method'] ?? 'cash',
                    'payment_status' => $validated['payment_status'] ?? 'paid',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $batch = Batch::where('id', $item['batch_id'])->lockForUpdate()->firstOrFail();
                    if ($batch->quantity < $item['quantity']) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'items' => "Insufficient stock for {$batch->medicine->name} (Batch: {$batch->batch_number}). Only {$batch->quantity} left."
                        ]);
                    }

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'medicine_id' => $item['medicine_id'],
                        'batch_id' => $item['batch_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                    ]);

                    Batch::where('id', $item['batch_id'])
                        ->decrement('quantity', $item['quantity']);
                }
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to update sale: ' . $e->getMessage());
        }

        $this->logActivity('sale_updated', 'Sale', $sale->id, "Sale {$sale->invoice_no} updated.");

        return redirect()->route('sales.index')
            ->with('success', 'Sale updated successfully.');
    }

    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                Batch::where('id', $item->batch_id)
                    ->increment('quantity', $item->quantity);
            }
            $sale->items()->delete();
            $sale->delete();
        });

        $this->logActivity('sale_deleted', 'Sale', $sale->id, "Sale {$sale->invoice_no} deleted.");

        return redirect()->route('sales.index')
            ->with('success', 'Sale deleted successfully.');
    }

    public function invoice(Sale $sale)
    {
        $sale->load('items.medicine', 'items.batch', 'customer', 'user');
        return view('sales.invoice', compact('sale'));
    }
}

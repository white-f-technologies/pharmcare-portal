<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function index(Medicine $medicine)
    {
        $batches = $medicine->batches()->with('supplier')->paginate(10);
        return view('batches.index', compact('medicine', 'batches'));
    }

    public function create(Medicine $medicine)
    {
        $suppliers = Supplier::pluck('name', 'id');
        return view('batches.create', compact('medicine', 'suppliers'));
    }

    public function store(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'batch_number' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'expiry_date' => 'required|date',
            'mfg_date' => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:purchase_price',
            'quantity' => 'required|integer|min:0',
        ], [
            'selling_price.gte' => 'The selling price cannot be less than the buying/purchase price.',
        ]);

        $validated['medicine_id'] = $medicine->id;
        $batch = $medicine->batches()->create($validated);

        $this->logActivity('batch_created', 'Batch', $batch->id, "Batch '{$batch->batch_number}' for '{$medicine->name}' created.");

        return redirect()->route('medicines.batches.index', $medicine)
            ->with('success', 'Batch created successfully.');
    }

    public function show(Batch $batch)
    {
        $batch->load('medicine', 'supplier');
        return view('batches.show', compact('batch'));
    }

    public function edit(Batch $batch)
    {
        $medicine = $batch->medicine;
        $suppliers = Supplier::pluck('name', 'id');
        return view('batches.edit', compact('medicine', 'batch', 'suppliers'));
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'batch_number' => 'required',
            'supplier_id' => 'required|exists:suppliers,id',
            'expiry_date' => 'required|date',
            'mfg_date' => 'nullable|date',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0|gte:purchase_price',
            'quantity' => 'required|integer|min:0',
        ], [
            'selling_price.gte' => 'The selling price cannot be less than the buying/purchase price.',
        ]);

        $batch->update($validated);

        $this->logActivity('batch_updated', 'Batch', $batch->id, "Batch '{$batch->batch_number}' updated.");

        return redirect()->route('medicines.batches.index', $batch->medicine)
            ->with('success', 'Batch updated successfully.');
    }

    public function destroy(Batch $batch)
    {
        $medicine = $batch->medicine;
        $this->logActivity('batch_deleted', 'Batch', $batch->id, "Batch '{$batch->batch_number}' deleted.");
        $batch->delete();

        return redirect()->route('medicines.batches.index', $medicine)
            ->with('success', 'Batch deleted successfully.');
    }
}

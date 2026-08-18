<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'address' => 'nullable',
            'company' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier = Supplier::create($validated);

        $this->logActivity('supplier_created', 'Supplier', $supplier->id, "Supplier '{$supplier->name}' created.");

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = true;
        if (empty($validated['phone'])) {
            $validated['phone'] = 'N/A';
        }

        $supplier = Supplier::create($validated);
        $this->logActivity('supplier_created', 'Supplier', $supplier->id, "Supplier '{$supplier->name}' created quickly.");

        return response()->json([
            'success' => true,
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ],
            'message' => 'Supplier added successfully!'
        ]);
    }

    public function show(Supplier $supplier)
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone' => 'required',
            'address' => 'nullable',
            'company' => 'nullable',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        $this->logActivity('supplier_updated', 'Supplier', $supplier->id, "Supplier '{$supplier->name}' updated.");

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists() || $supplier->batches()->exists()) {
            return redirect()->route('suppliers.index')
                ->with('error', 'Cannot delete supplier because they have batch or purchase records.');
        }

        $this->logActivity('supplier_deleted', 'Supplier', $supplier->id, "Supplier '{$supplier->name}' deleted.");
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}
